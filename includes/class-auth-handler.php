<?php
if (!defined('ABSPATH')) {
    exit;
}

class Biwillz_Auth_Handler {
    public function __construct() {
        $this->handle_username_check();
        $this->handle_email_check();


        add_action('wp_ajax_nopriv_biwillz_do_password_reset', array($this, 'handle_do_password_reset'));
        add_action('wp_ajax_nopriv_biwillz_reset_password', array($this, 'handle_password_reset'));
        add_action('wp_ajax_biwillz_reset_password', array($this, 'handle_password_reset'));

        add_action('wp_ajax_nopriv_biwillz_modal_login', array($this, 'handle_modal_login'));
         add_action('wp_ajax_biwillz_modal_login', array($this, 'handle_modal_login'));
       // add_action('wp_ajax_nopriv_handle_modal_login', array($this, 'handle_modal_login'));
        //add_action('wp_ajax_handle_modal_login', array($this, 'handle_modal_login'));

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
        add_action('init', array($this, 'redirect_auth_pages'));
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
/**
 * Handle username availability check
 */
public function handle_username_check() {
    // Add the AJAX actions for both logged-in and non-logged-in users
    add_action('wp_ajax_biwillz_check_username', array($this, 'check_username_availability'));
    add_action('wp_ajax_nopriv_biwillz_check_username', array($this, 'check_username_availability'));
}

/**
 * Check if a username is available
 */
public function check_username_availability() {
    try {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'biwillz_auth_nonce')) {
            wp_send_json_error(array(
                'message' => 'Security check failed. Please refresh the page and try again.'
            ));
            wp_die();
        }

        // Validate username presence
        if (!isset($_POST['username']) || empty($_POST['username'])) {
            wp_send_json_error(array(
                'message' => 'Username is required.'
            ));
            wp_die();
        }

        $username = sanitize_user($_POST['username']);

        // Validate username format
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            wp_send_json_error(array(
                'message' => 'Username must be 3-20 characters long and can only contain letters, numbers, and underscores.'
            ));
            wp_die();
        }

        // Check if username exists
        if (username_exists($username)) {
            wp_send_json_error(array(
                'message' => 'Sorry, that username already exists!'
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'Username is available.'
            ));
        }

    } catch (Exception $e) {
        error_log('Username check error: ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => 'An unexpected error occurred. Please try again.'
        ));
    }

    wp_die();
}

    public function init() {

        add_shortcode('biwillz_password_reset', array($this, 'render_request_reset_form'));
        add_shortcode('biwillz_reset_password', array($this, 'render_reset_password_form'));


        add_shortcode('biwillz_modal_login', array($this, 'render_modal_login')); // Add this line
        add_action('wp_ajax_nopriv_biwillz_login', array($this, 'handle_login'));
        add_action('wp_ajax_nopriv_biwillz_register', array($this, 'handle_registration'));
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


    // public function handle_password_reset() {
    //     // Verify nonce
    //     if (!check_ajax_referer('biwillz_auth_nonce', 'nonce', false)) {
    //         wp_send_json_error(array('message' => 'Invalid security token. Please refresh the page and try again.'));
    //     }
    
    //     // Get and validate email
    //     $email = sanitize_email($_POST['email']);
    //     if (!is_email($email)) {
    //         wp_send_json_error(array('message' => 'Please provide a valid email address.'));
    //     }
    
    //     // Check if user exists
    //     $user = get_user_by('email', $email);
    //     if (!$user) {
    //         wp_send_json_error(array('message' => 'No account found with this email address.'));
    //     }
    
    //     // Generate reset key and login
    //     $key = get_password_reset_key($user);
    //     if (is_wp_error($key)) {
    //         wp_send_json_error(array('message' => 'Unable to generate password reset link. Please try again later.'));
    //     }
    
    //     // Build reset link
    //     $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
    
    //     // Prepare email content
    //     $to = $email;
    //     $subject = 'Password Reset Request';
    //     $message = 'Someone has requested a password reset for your account. If this was not you, please ignore this email. Otherwise, click the link below to reset your password:' . "\r\n\r\n";
    //     $message .= $reset_link . "\r\n\r\n";
    //     $message .= 'This link will expire in 24 hours.';
    //     $headers = array('Content-Type: text/html; charset=UTF-8');
    
    //     // Send email
    //     $sent = wp_mail($to, $subject, $message, $headers);
    
    //     if ($sent) {
    //         wp_send_json_success(array('message' => 'Password reset instructions have been sent to your email address.'));
    //     } else {
    //         wp_send_json_error(array('message' => 'Failed to send reset email. Please try again later or contact support.'));
    //     }
    // }



    public function handle_password_reset() {
        // Verify nonce
        if (!check_ajax_referer('biwillz_auth_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token. Please refresh the page and try again.'));
        }
    
        // Get and validate email
        $email = sanitize_email($_POST['email']);
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please provide a valid email address.'));
        }
    
        // Check if user exists
        $user = get_user_by('email', $email);
        if (!$user) {
            wp_send_json_error(array('message' => 'No account found with this email address.'));
        }
    
        // Generate reset key and login
        $key = get_password_reset_key($user);
        if (is_wp_error($key)) {
            wp_send_json_error(array('message' => 'Unable to generate password reset link. Please try again later.'));
        }
    
        // Build reset link
       // $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
    
        // Replace this line:
$reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');

// With this:
$reset_link = 'https://biwillzcomputers.com/auth_reset/?key=' . $key . '&login=' . rawurlencode($user->user_login);

        // Prepare email HTML content
        $email_template = '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Password Reset - Biwillz Computers</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                    padding: 20px;
                }
                .header-image {
                    width: 100%;
                    height: auto;
                }
                .content {
                    padding: 20px 0;
                    color: #333333;
                }
                .button {
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #2e08f4;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .contact-info {
                    margin: 20px 0;
                    font-size: 14px;
                }
                .footer-image {
                    width: 100%;
                    height: auto;
                }
            </style>
        </head>
        <body>
            <div class="email-container">
                <p>
                    <a title="Biwillz Computers" href="https://biwillzcomputers.com" rel="noopener">
                        <img class="header-image" src="https://biwillzcomputers.com/_-Flashware_-/wp-content/uploads/2024/11/EmailH1.png" alt="Biwillz Computers Header" />
                    </a>
                </p>
                
                <div class="content">
                    <p>Hello ' . esc_html($user->display_name) . ',</p>
                    <p>We received a request to reset the password for your account. If you did not make this request, please ignore this email.</p>
                    <p>To reset your password, click the button below:</p>
                    <p style="text-align: center;">
                        <a href="' . esc_url($reset_link) . '" class="button" style="color: #ffffff;">Reset Password</a>
                    </p>
                    <p>If the button above doesn\'t work, copy and paste this link into your browser:</p>
                    <p style="word-break: break-all;">' . esc_url($reset_link) . '</p>
                    <p>This link will expire in 24 hours for security reasons.</p>
                    <p>If you need any assistance, please don\'t hesitate to contact our support team.</p>
                </div>
    
                <div class="contact-info">
                    <p style="margin: 0; padding: 0; line-height: 1;"><strong>Support Team</strong></p>
                    <p style="margin: 0; padding: 0; line-height: 1;"><em>Biwillz Computers</em></p>
                    <p style="margin: 0; padding: 0; line-height: 1;"><em>Dev</em></p>
                </div>
    
                <p>
                    <a title="Biwillz Computers" href="https://biwillzcomputers.com" rel="noopener">
                        <img class="footer-image" src="https://biwillzcomputers.com/_-Flashware_-/wp-content/uploads/2024/11/Email-Footer1.png" alt="Biwillz Computers Footer" />
                    </a>
                </p>
            </div>
        </body>
        </html>';
    
        // Prepare email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: Biwillz Computers <noreply@biwillzcomputers.com>',
            'Reply-To: support@biwillzcomputers.com'
        );
    
        // Send email
        $subject = 'Password Reset Request - Biwillz Computers';
        $sent = wp_mail($email, $subject, $email_template, $headers);
    
        if ($sent) {
            wp_send_json_success(array('message' => 'Password reset instructions have been sent to your email address.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send reset email. Please try again later or contact support.'));
        }
    }

    public function handle_do_password_reset() {
        // Verify nonce
        if (!check_ajax_referer('biwillz_auth_nonce', 'nonce', false)) {
            wp_send_json_error(array('message' => 'Invalid security token. Please refresh the page and try again.'));
        }
    
        $key = sanitize_text_field($_POST['key']);
        $login = sanitize_text_field($_POST['login']);
        $new_password = $_POST['new_password'];
    
        // Validate password
        if (strlen($new_password) < 8 || 
            !preg_match('/[A-Z]/', $new_password) || 
            !preg_match('/[a-z]/', $new_password) || 
            !preg_match('/[0-9]/', $new_password)) {
            wp_send_json_error(array('message' => 'Password does not meet the requirements.'));
        }
    
        // Check the reset key
        $user = check_password_reset_key($key, $login);
        if (is_wp_error($user)) {
            wp_send_json_error(array('message' => 'This password reset link has expired or is invalid.'));
        }
    
        // Reset the password
        reset_password($user, $new_password);
    
        wp_send_json_success(array(
            'message' => 'Your password has been successfully reset.',
            'redirect_url' => wp_login_url()
        ));
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



/**
 * Handle email availability check
 */
public function handle_email_check() {
    // Add the AJAX actions for both logged-in and non-logged-in users
    add_action('wp_ajax_biwillz_check_email', array($this, 'check_email_availability'));
    add_action('wp_ajax_nopriv_biwillz_check_email', array($this, 'check_email_availability'));
}

/**
 * Check if an email is available
 */
public function check_email_availability() {
    try {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'biwillz_auth_nonce')) {
            wp_send_json_error(array(
                'message' => 'Security check failed. Please refresh the page and try again.'
            ));
            wp_die();
        }

        // Validate email presence
        if (!isset($_POST['email']) || empty($_POST['email'])) {
            wp_send_json_error(array(
                'message' => 'Email address is required.'
            ));
            wp_die();
        }

        $email = sanitize_email($_POST['email']);

        // Validate email format
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => 'Please enter a valid email address.'
            ));
            wp_die();
        }

        // Check if email exists
        if (email_exists($email)) {
            wp_send_json_error(array(
                'message' => 'Sorry, that email address is already registered!'
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'Email address is available.'
            ));
        }

    } catch (Exception $e) {
        error_log('Email check error: ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => 'An unexpected error occurred. Please try again.'
        ));
    }

    wp_die();
}


    
    public function handle_modal_login() {
        try {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'biwillz_auth_nonce')) {
                wp_send_json_error([
                    'message' => 'Invalid security token',
                    'code' => 'invalid_nonce'
                ]);
            }
    
            // Get credentials
            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
            $password = isset($_POST['password']) ? $_POST['password'] : '';
            $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;
            
            // Get the current page URL from the form
            $redirect_url = isset($_POST['current_page']) ? esc_url_raw($_POST['current_page']) : '';
            
            // Fallback to HTTP_REFERER if current_page is empty
            if (empty($redirect_url)) {
                $redirect_url = wp_get_referer();
            }
            
            // Redirect to checkout page
            if (empty($redirect_url)) {
                $redirect_url = wc_get_checkout_url();
            }
            
            // Strip any login-related parameters
            $redirect_url = remove_query_arg([
                'login', 
                'failed', 
                'action', 
                'custom_logout', 
                '_wpnonce', 
                'redirect_to'
            ], $redirect_url);
    
            // Only verify reCAPTCHA if it's enabled in settings
            if (Biwillz_Auth_Settings::get_option('enable_recaptcha')) {
                // Check if reCAPTCHA response exists
                if (!isset($_POST['g-recaptcha-response'])) {
                    wp_send_json_error([
                        'message' => __('reCAPTCHA verification is required', 'biwillz-auth'),
                        'code' => 'recaptcha_missing'
                    ]);
                    return;
                }
    
                // Verify reCAPTCHA
                if (!$this->verify_recaptcha()) {
                    wp_send_json_error([
                        'message' => __('Please complete the reCAPTCHA verification', 'biwillz-auth'),
                        'code' => 'recaptcha_failed'
                    ]);
                    return;
                }
            }
    
            // Perform login
            $user = wp_signon([
                'user_login' => $username,
                'user_password' => $password,
                'remember' => $remember
            ], is_ssl());
    
            if (is_wp_error($user)) {
                error_log('Login error: ' . $user->get_error_message());
                wp_send_json_error([
                    'message' => $user->get_error_message(),
                    'code' => $user->get_error_code()
                ]);
                return;
            }
    
            // Prepare success response data before setting user and cookies
            $response_data = [
                'message' => 'Login successful Proceed with Order!!',
                'redirect_url' => $redirect_url
            ];
    
            // Set user and cookies
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID, $remember);
    
            // Send the response last
            wp_send_json_success($response_data);
    
        } catch (Exception $e) {
            error_log('Login exception: ' . $e->getMessage());
            wp_send_json_error([
                'message' => 'An unexpected error occurred',
                'code' => 'unexpected_error'
            ]);
        }
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

    public function redirect_auth_pages() {
        global $pagenow;
        
        if ($pagenow == 'wp-login.php' && !is_user_logged_in()) {
            $redirect = '';
            
            if (isset($_GET['action'])) {
                if ($_GET['action'] == 'register') {
                    $redirect = home_url('/register/');
                } elseif ($_GET['action'] == 'lostpassword') {
                    $redirect = home_url('/lost-password/');
                }
            } else {
                $redirect = home_url('/login/');
            }
            
            if ($redirect) {
                if (!empty($_SERVER['QUERY_STRING'])) {
                    $redirect .= '?' . $_SERVER['QUERY_STRING'];
                }
                wp_redirect($redirect);
                exit();
            }
        }
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

    //public function custom_logout_url($logout_url, $redirect) {
        //$args = array('action' => 'logout');
        //if (!empty($redirect)) {
          //  $args['redirect_to'] = urlencode($redirect);
        //}
      //  return add_query_arg($args, home_url());
    //}

    
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

    public function handle_login() {
        try {
            // Verify nonce
            if (!check_ajax_referer('biwillz_auth_nonce', 'nonce', false)) {
                wp_send_json_error([
                    'message' => __('Security check failed', 'biwillz-auth'),
                    'code' => 'security_failed'
                ]);
                return;
            }
    
            // Get and sanitize input data
            $input = $this->get_sanitized_input();
    
            // Verify reCAPTCHA
            $recaptcha_response = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';
            if (!$this->verify_recaptcha($recaptcha_response)) {
                wp_send_json_error([
                    'message' => __('Please complete the reCAPTCHA verification', 'biwillz-auth'),
                    'code' => 'recaptcha_failed'
                ]);
                return;
            }
    
            // Validate credentials
            if (empty($input['username']) || empty($input['password'])) {
                wp_send_json_error([
                    'message' => __('Username and password are required', 'biwillz-auth'),
                    'code' => 'empty_credentials'
                ]);
                return;
            }
    
            // Check if user is locked out
            if ($this->is_user_locked_out($input['username'])) {
                wp_send_json_error([
                    'message' => __('Too many failed attempts. Please try again later.', 'biwillz-auth'),
                    'code' => 'locked_out'
                ]);
                return;
            }
    
            // Attempt login
            $user = wp_signon([
                'user_login' => $input['username'],
                'user_password' => $input['password'],
                'remember' => $input['remember']
            ], is_ssl());
    
            // Handle login error
            if (is_wp_error($user)) {
                $this->handle_failed_login($input['username']);
                wp_send_json_error([
                    'message' => __('Invalid credentials', 'biwillz-auth'),
                    'code' => 'invalid_credentials'
                ]);
                return;
            }
    
            // Clear failed attempts on successful login
            $this->clear_failed_attempts($input['username']);
    
            // Get redirect URL
            $redirect_url = $this->get_redirect_url($user);
    
            // Send success response
            wp_send_json_success([
                'message' => __('Login successful', 'biwillz-auth'),
                'redirect_url' => esc_url_raw($redirect_url)
            ]);
    
        } catch (Exception $e) {
            error_log('Biwillz Auth Login Error: ' . $e->getMessage());
            wp_send_json_error([
                'message' => __('An unexpected error occurred', 'biwillz-auth'),
                'code' => 'system_error'
            ]);
        }
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
    // private function get_redirect_url($user) {
    //     $default_redirect = home_url();
        
    //     // Role-based redirect
    //     if (in_array('administrator', $user->roles)) {
    //         $default_redirect = admin_url();
    //     } elseif (in_array('subscriber', $user->roles)) {
    //         $default_redirect = home_url('/dashboard/');
    //     }
    
    //     return apply_filters('biwillz_auth_login_redirect', $default_redirect, $user);
    // }   
    
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





    

    public function handle_registration() {
        check_ajax_referer('biwillz_auth_nonce', 'nonce');

        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $phone = sanitize_text_field($_POST['phone']);
        $terms_accepted = isset($_POST['terms_accepted']) ? $_POST['terms_accepted'] : '';

        if (empty($username) || empty($email) || empty($password) || empty($phone)) {
            wp_send_json_error(array(
                'message' => __('All fields are required', 'biwillz-auth')
            ));
        }

        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
            wp_send_json_error(array(
                'message' => __('Invalid phone number format', 'biwillz-auth')
            ));
        }

        if ($terms_accepted !== 'yes') {
            wp_send_json_error(array(
                'message' => __('You must accept the terms and conditions', 'biwillz-auth')
            ));
        }

        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => __('Invalid email address', 'biwillz-auth')
            ));
        }

        $userdata = array(
            'user_login'           => $username,
            'user_email'           => $email,
            'user_pass'            => $password,
            'role'                 => 'customer',
            'show_admin_bar_front' => false,
            'user_status'          => 0,
            'user_registered'      => current_time('mysql'),
            'user_activation_key'  => ''
        );

        $user_id = wp_insert_user($userdata);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array(
                'message' => $user_id->get_error_message()
            ));
        }

        update_user_meta($user_id, 'wp_user_level', 0);
        delete_user_meta($user_id, 'default_password_nag');
        update_user_meta($user_id, 'pw_user_status', true);
        update_user_meta($user_id, 'phone', $phone);
        update_user_meta($user_id, 'terms_accepted_at', current_time('mysql'));

        $user_signon = wp_signon(array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => true
        ), is_ssl());

        if (is_wp_error($user_signon)) {
            wp_send_json_error(array(
                'message' => $user_signon->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message' => __('Registration successful! You are now logged in.', 'biwillz-auth'),
            'redirect_url' => home_url()
        ));
    }

    public function handle_logout() {
        check_ajax_referer('biwillz_auth_nonce', 'nonce');
        wp_logout();
        wp_send_json_success(array(
            'message' => __('Logout successful', 'biwillz-auth'),
            'redirect_url' => home_url()
        ));
    }

    public function render_login_form() {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_alert();
        }

        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/login-form.php';
        return ob_get_clean();
    }

    public function render_register_form() {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_alert();
        }

        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/register-form.php';
        return ob_get_clean();
    }

    public function render_auth_form() {
        if (is_user_logged_in()) {
            return $this->render_already_logged_in_alert();
        }

        ob_start();
        include BIWILLZ_AUTH_PATH . 'templates/auth-form.php';
        return ob_get_clean();
    }
}