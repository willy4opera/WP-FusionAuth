<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once dirname(__FILE__) . '/auth_handlers/password-reset-handlers.php';
require_once plugin_dir_path(__FILE__) . 'auth_handlers/handle_login.php';
require_once plugin_dir_path(__FILE__) . 'auth_handlers/handle_modal_login.php';
require_once plugin_dir_path(__FILE__) . 'auth_handlers/handle_registration.php';

class Biwillz_Auth_Handler {
    private $password_reset_handler;
    private $login_handler;
    private $modal_login_handler;
    private $registration_handler;

    public function __construct() {
        
        $this->password_reset_handler = new BiWillz_Password_Reset_Handlers();
        $this->login_handler = new Biwillz_Auth_Login_Handler();
        $this->modal_login_handler = new Biwillz_Auth_Modal_Login_Handler();
        $this->registration_handler = new Biwillz_Auth_Registration_Handler();

        // Remove user moderation checks
        add_filter('pre_option_users_can_register', '__return_true');
        add_filter('wpmu_signup_user_notification', '__return_false');
        add_filter('wpmu_welcome_user_notification', '__return_false');
        add_filter('wp_pre_insert_user_data', array($this, 'set_user_active'), 10, 1);

        add_action('init', array($this, 'handle_logout_request'), 1); // Priority 1 to run early
        remove_action('wp_logout', 'wp_safe_redirect'); // Remove WordPress's default redirect
        remove_action('wp_logout', 'wp_destroy_current_session'); // Remove WordPress's session destruction
        add_action('wp_logout', array($this, 'custom_logout_redirect')); // Add our custom redirect

        add_action('wp_enqueue_scripts', array($this, 'enqueue_logout_scripts'));
        // Bypass any pending approval status
        add_filter('registration_error', array($this, 'bypass_pending_approval'), 10, 3);

        // Add new authentication routing
      
        add_filter('login_url', array($this, 'custom_login_url'), 10, 3);
        add_filter('register_url', array($this, 'custom_register_url'));
        add_filter('logout_url', array($this, 'custom_logout_url'), 10, 2);
        add_filter('lostpassword_url', array($this, 'custom_lostpassword_url'), 10, 2);

        // Disable admin bar for non-admins
        add_action('after_setup_theme', array($this, 'disable_admin_bar'));

        // Restrict admin access
        add_action('admin_init', array($this, 'restrict_admin_access'));

        // Initialize the plugin
        $this->init();
    }


    public function init() {


            // Registration endpoints
        add_action('wp_ajax_nopriv_biwillz_register', [$this->registration_handler, 'handle_registration']);
        add_action('wp_ajax_nopriv_biwillz_check_username', [$this->registration_handler, 'check_username_availability']);
        add_action('wp_ajax_nopriv_biwillz_check_email', [$this->registration_handler, 'check_email_availability']);
        
        // Also allow logged-in users to check availability (might be needed for profile updates)
        add_action('wp_ajax_biwillz_check_username', [$this->registration_handler, 'check_username_availability']);
        add_action('wp_ajax_biwillz_check_email', [$this->registration_handler, 'check_email_availability']);


            add_action('wp_ajax_nopriv_biwillz_modal_login', [$this->modal_login_handler, 'handle_modal_login']);
            //Handle Login
            add_action('wp_ajax_nopriv_biwillz_login', [$this->login_handler, 'handle_login']);
            add_action('wp_ajax_biwillz_login', [$this->login_handler, 'handle_login']);
                 // Hook into AJAX actions for passwrd reset handler
             add_action('wp_ajax_nopriv_biwillz_reset_password', array($this->password_reset_handler, 'handle_password_reset'));
             add_action('wp_ajax_nopriv_biwillz_do_password_reset', array($this->password_reset_handler, 'handle_do_password_reset'));
             
             // Also allow logged-in users to reset password
             add_action('wp_ajax_biwillz_reset_password', array($this->password_reset_handler, 'handle_password_reset'));
             add_action('wp_ajax_biwillz_do_password_reset', array($this->password_reset_handler, 'handle_do_password_reset'));

        add_shortcode('biwillz_password_reset', array($this, 'render_request_reset_form'));
        add_shortcode('biwillz_reset_password', array($this, 'render_reset_password_form'));


        add_shortcode('biwillz_modal_login', array($this, 'render_modal_login')); // Add this line
        //add_action('wp_ajax_nopriv_biwillz_register', array($this, 'handle_registration'));
        add_action('init', array($this, 'handle_logout_request'));
        //add_action('wp_ajax_biwillz_logout', array($this, 'handle_logout'));
        add_shortcode('biwillz_login_form', array($this, 'render_login_form'));
        add_shortcode('biwillz_register_form', array($this, 'render_register_form'));
        add_shortcode('biwillz_auth_form', array($this, 'render_auth_form'));
    }

    public function render_request_reset_form() {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_alert();
        }
        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/request-password-reset-form.php';
        return ob_get_clean();
    }
    
    public function render_reset_password_form() {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_alert();
        }
        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/reset-password-form.php';
        return ob_get_clean();
    }

    private function verify_recaptcha() {
        if (!Biwillz_Auth_Settings::get_option('enable_recaptcha')) {
            return true;
        }
    
        $recaptcha_response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        if (empty($recaptcha_response)) {
            return false;
        }
    
        $secret_key = Biwillz_Auth_Settings::get_option('recaptcha_secret_key');
        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $recaptcha_response
            ]
        ]);
    
        if (is_wp_error($verify)) {
            return false;
        }
    
        $verify = json_decode(wp_remote_retrieve_body($verify));
        return isset($verify->success) && $verify->success;
    }

    public function render_modal_login($atts = []) {
        
        // Parse shortcode attributes
        $default_atts = array(
            'show_button' => 'true',
            'button_text' => 'Login',
            'button_class' => '',
            'icon' => 'true'
        );
        $atts = shortcode_atts($default_atts, $atts);
    
        // Generate unique ID for this instance
        $modal_id = 'loginModal_' . uniqid();
        
        // Pass attributes to template
        $template_args = array(
            'modal_id' => $modal_id,
            'show_button' => filter_var($atts['show_button'], FILTER_VALIDATE_BOOLEAN),
            'button_text' => sanitize_text_field($atts['button_text']),
            'button_class' => sanitize_html_class($atts['button_class']),
            'show_icon' => filter_var($atts['icon'], FILTER_VALIDATE_BOOLEAN)
        );
    
        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/modal-login.php';
        return ob_get_clean();
    }

    public function custom_logout_redirect() {
        // This function will be empty as we handle the redirect in our JavaScript
        return;
    }
    public function handle_logout_request() {
        // Check for our custom logout action
        if (isset($_GET['action']) && $_GET['action'] === 'biwillz_logout' && 
            isset($_GET['custom_logout']) && $_GET['custom_logout'] === 'true') {
            
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'biwillz-logout-nonce')) {
                wp_die('Security check failed');
            }
            
            // Output the custom logout confirmation
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Logout - Biwillz Computers</title>
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
                <style>
                    body {
                        margin: 0;
                        padding: 0;
                        background: #f5f5f5;
                        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                    }
                </style>
            </head>
            <body>
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Confirm Logout',
                        text: 'Are you sure you want to logout from Biwillz Computers?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Logout',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#2e08f4',
                        cancelButtonColor: '#cf13e4',
                        reverseButtons: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading state
                            Swal.fire({
                                title: 'Logging Out',
                                text: 'Please wait...',
                                icon: 'info',
                                showConfirmButton: false,
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                    // Perform the actual logout
                                    fetch('<?php echo wp_nonce_url(add_query_arg(array(
                                        "action" => "biwillz_logout",
                                        "perform" => "true"
                                    ), home_url()), "biwillz-logout-nonce"); ?>')
                                    .then(() => {
                                        performLogout();
                                    });
                                }
                            });
                        } else {
                            // User cancelled, redirect back to home
                            window.location.href = '<?php echo esc_js(home_url()); ?>';
                        }
                    });
    
                    function performLogout() {
                        // Perform the actual logout
                        <?php wp_logout(); ?>
                        Swal.fire({
                            title: 'Success!',
                            text: 'You have been successfully logged out.',
                            icon: 'success',
                            confirmButtonColor: '#2e08f4',
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '<?php echo esc_js(home_url()); ?>';
                        });
                    }
                });
                </script>
            </body>
            </html>
            <?php
            exit();
        }
    
        // Handle the actual logout
        if (isset($_GET['action']) && $_GET['action'] === 'biwillz_logout' && 
            isset($_GET['perform']) && $_GET['perform'] === 'true') {
            
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'biwillz-logout-nonce')) {
                wp_die('Security check failed');
            }
    
            wp_logout();
            wp_send_json_success(array('message' => 'Logged out successfully'));
            exit();
        }
    }
    

    private function render_already_logged_in_alert() {
        ob_start();
        ?>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <script>
        Swal.fire({
            title: 'Already Logged In',
            text: 'You are currently logged into your account',
            icon: 'info',
            showDenyButton: true,
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="fas fa-home"></i> Home',
            denyButtonText: '<i class="fas fa-user"></i> Account',
            cancelButtonText: '<i class="fas fa-shopping-cart"></i> Cart',
            footer: '<div class="swal2-footer-buttons"><a href="https://biwillzcomputers.com/shop-2" class="swal2-shop-btn"><i class="fas fa-store"></i> Shop</a><a href="<?php echo wp_logout_url(home_url()); ?>" class="swal2-logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>',
            customClass: {
                confirmButton: 'swal2-custom-button',
                denyButton: 'swal2-custom-button',
                cancelButton: 'swal2-custom-button',
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '<?php echo home_url(); ?>';
            } else if (result.isDenied) {
                window.location.href = '<?php echo home_url("/my-account-2"); ?>';
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                window.location.href = '<?php echo home_url("/cart-2"); ?>';
            }
        });
        </script>
    
        <style>
        .swal2-custom-button {
            background-color: #2e08f4 !important;
            color: white !important;
        }
        .swal2-custom-button:hover {
            background-color: #cf13e4 !important;
        }
        .swal2-footer-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
        }
        .swal2-shop-btn,
        .swal2-logout-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #2e08f4;
            color: white !important;
            text-decoration: none;
            border-radius: 4px;
        }
        .swal2-shop-btn:hover,
        .swal2-logout-btn:hover {
            background-color: #cf13e4;
            text-decoration: none;
        }
        .swal2-shop-btn i,
        .swal2-logout-btn i,
        .swal2-custom-button i {
            margin-right: 5px;
        }
        </style>
        <?php
        return ob_get_clean();
    }



    public function custom_login_url($login_url, $redirect, $force_reauth) {
        $login_page = home_url('/login/');
        if (!empty($redirect)) {
            $login_page = add_query_arg('redirect_to', urlencode($redirect), $login_page);
        }
        if ($force_reauth) {
            $login_page = add_query_arg('reauth', '1', $login_page);
        }
        return $login_page;
    }

    public function custom_register_url($register_url) {
        return home_url('/register/');
    }
    
    public function custom_logout_url($logout_url, $redirect) {
        // Remove WordPress's confirm=true parameter and use our own parameter
        $logout_url = add_query_arg(array(
            'action' => 'biwillz_logout',
            'custom_logout' => 'true'
        ), home_url());
        return wp_nonce_url($logout_url, 'biwillz-logout-nonce');
    }

    public function enqueue_logout_scripts() {
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
        wp_enqueue_script('biwillz-auth-logout', BIWILLZ_AUTH_URL . 'assets/js/logout.js', array('jquery', 'sweetalert2'), '1.0.0', true);
        
        wp_localize_script('biwillz-auth-logout', 'biwillz_auth', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biwillz_auth_nonce'),
            'strings' => array(
                'logging_out' => __('Logging Out', 'biwillz-auth'),
                'please_wait' => __('Please wait...', 'biwillz-auth'),
                'error' => __('Error', 'biwillz-auth'),
                'ok' => __('OK', 'biwillz-auth'),
                'error_message' => __('An error occurred. Please try again.', 'biwillz-auth'),
                'confirm_logout' => __('Confirm Logout', 'biwillz-auth'),
                'confirm_logout_message' => __('Are you sure you want to logout?', 'biwillz-auth'),
                'yes_logout' => __('Yes, Logout', 'biwillz-auth'),
                'cancel' => __('Cancel', 'biwillz-auth')
            )
        ));
    }


    public function custom_lostpassword_url($lostpassword_url, $redirect) {
        $url = home_url('/lost-password/');
        if (!empty($redirect)) {
            $url = add_query_arg('redirect_to', $redirect, $url);
        }
        return $url;
    }

    public function disable_admin_bar() {
        if (!current_user_can('administrator')) {
            show_admin_bar(false);
        }
    }

    public function restrict_admin_access() {
        if (!current_user_can('administrator') && (!defined('DOING_AJAX') || !DOING_AJAX)) {
            wp_redirect(home_url());
            exit;
        }
    }

    public function set_user_active($userdata) {
        if (!empty($userdata['user_status'])) {
            $userdata['user_status'] = 0;
        }
        return $userdata;
    }

    public function bypass_pending_approval($errors, $sanitized_user_login, $user_email) {
        return new WP_Error();
    }


    private function get_sanitized_input() {
        return [
            'username' => isset($_POST['username']) ? sanitize_user($_POST['username']) : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : '',
            'remember' => filter_var(
                isset($_POST['remember']) ? $_POST['remember'] : false,
                FILTER_VALIDATE_BOOLEAN
            )
        ];
    }

    private function is_user_locked_out($username) {
        $attempts = get_transient('login_attempts_' . md5($username));
        $max_attempts = apply_filters('biwillz_max_login_attempts', 5);
        return $attempts && $attempts >= $max_attempts;
    }

    private function handle_failed_login($username) {
        $attempts = (int)get_transient('login_attempts_' . md5($username));
        $attempts++;
        
        set_transient(
            'login_attempts_' . md5($username),
            $attempts,
            HOUR_IN_SECONDS
        );
    
        error_log(sprintf(
            'Failed login attempt for user: %s from IP: %s. Attempt %d',
            $username,
            wp_privacy_anonymize_ip($_SERVER['REMOTE_ADDR']),
            $attempts
        ));
    }
    private function clear_failed_attempts($username) {
        delete_transient('login_attempts_' . md5($username));
    }
      
    
    private function get_redirect_url($user) {
        // Check for redirect URL from frontend first
        $redirect_url = isset($_POST['redirect_to']) ? esc_url_raw($_POST['redirect_to']) : '';
        
        // If no frontend redirect, use role-based redirect
        if (empty($redirect_url)) {
            if (in_array('administrator', $user->roles)) {
                $redirect_url = admin_url();
            } elseif (in_array('subscriber', $user->roles)) {
                $redirect_url = home_url('/dashboard/');
            } else {
                $redirect_url = wc_get_checkout_url(); // or home_url('/checkout/');
            }
        }
        
        // Maintain the existing filter for compatibility
        return apply_filters('biwillz_auth_login_redirect', $redirect_url, $user);
    }


    public function render_login_form() {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_alert();
        }

        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/login-form.php';
        return ob_get_clean();
    }

    
}