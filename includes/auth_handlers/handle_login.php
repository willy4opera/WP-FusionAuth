<?php
// wp-content/plugins/Biwillz-Auth/includes/auth_handlers/handle_login.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Biwillz_Auth_Login_Handler {
    /**
     * Constructor - Initialize hooks
     */
    public function __construct() {
        // Ajax hooks for both logged in and non-logged in users
        //add_action('wp_ajax_biwillz_login', array($this, 'handle_login'));
        //add_action('wp_ajax_nopriv_biwillz_login', array($this, 'handle_login'));
        
        // Enqueue scripts and localize data
        add_action('wp_enqueue_scripts', array($this, 'enqueue_login_scripts'));
    }

    /**
     * Enqueue necessary scripts and localize data
     */
    public function enqueue_login_scripts() {
        // Enqueue Sweet Alert
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), null, true);
        
        // Enqueue the login script
        wp_enqueue_script(
            'biwillz-auth-login',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/login.js',
            array('jquery', 'sweetalert2'),
            '1.0.0',
            true
        );

        // Localize script with necessary data
        wp_localize_script('biwillz-auth-login', 'biwillzAuth', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biwillz_auth_nonce'),
            'recaptcha_site_key' => Biwillz_Auth_Settings::get_option('recaptcha_site_key'),
            'min_username_length' => 3,
            'min_password_length' => 6
        ));
    }

    /**
     * Handle login request
     */
    public function handle_login() {
        try {
            // Verify security and get credentials
            if (!$this->verify_security()) {
                return;
            }

            // Get and validate input data
            $input = $this->get_login_data();

            // Verify reCAPTCHA if enabled
            if (!$this->handle_recaptcha_verification()) {
                return;
            }

            // Perform login
            $user = $this->perform_login($input);
            if (is_wp_error($user)) {
                $this->handle_login_error($user);
                return;
            }

            // Handle successful login
            $this->handle_successful_login($user, $input);

        } catch (Exception $e) {
            $this->handle_exception($e);
        }
    }

    /**
     * Verify security nonce
     */
    private function verify_security() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'biwillz_auth_nonce')) {
            wp_send_json_error([
                'message' => 'Invalid security token',
                'code' => 'invalid_nonce'
            ]);
            return false;
        }
        return true;
    }

    /**
     * Get and sanitize login data
     */
    private function get_login_data() {
        return [
            'username' => isset($_POST['username']) ? sanitize_user($_POST['username']) : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : '',
            'remember' => isset($_POST['remember']) ? (bool)$_POST['remember'] : false,
            'redirect_url' => $this->get_redirect_url()
        ];
    }

    /**
     * Get and validate redirect URL
     */
    private function get_redirect_url() {
        $redirect_url = isset($_POST['current_page']) ? esc_url_raw($_POST['current_page']) : '';
        
        // Fallback to HTTP_REFERER if current_page is empty
        if (empty($redirect_url)) {
            $redirect_url = wp_get_referer();
        }
        
        // Fallback to home URL if still empty
        if (empty($redirect_url)) {
            $redirect_url = home_url();
        }
        
        // Clean the URL
        return $this->clean_redirect_url($redirect_url);
    }

    /**
     * Clean redirect URL by removing login-related parameters
     */
    private function clean_redirect_url($url) {
        return remove_query_arg([
            'login', 
            'failed', 
            'action', 
            'custom_logout', 
            '_wpnonce', 
            'redirect_to'
        ], $url);
    }

    /**
     * Handle reCAPTCHA verification
     */
    private function handle_recaptcha_verification() {
        if (!Biwillz_Auth_Settings::get_option('enable_recaptcha')) {
            return true;
        }

        if (!isset($_POST['g-recaptcha-response'])) {
            wp_send_json_error([
                'message' => __('reCAPTCHA verification is required', 'biwillz-auth'),
                'code' => 'recaptcha_missing'
            ]);
            return false;
        }

        if (!$this->verify_recaptcha()) {
            wp_send_json_error([
                'message' => __('Please complete the reCAPTCHA verification', 'biwillz-auth'),
                'code' => 'recaptcha_failed'
            ]);
            return false;
        }

        return true;
    }

    /**
     * Verify reCAPTCHA response
     */
    private function verify_recaptcha() {
        $secret_key = Biwillz_Auth_Settings::get_option('recaptcha_secret_key');
        $response = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        
        $verify = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $secret_key,
                'response' => $response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            ]
        ]);

        if (is_wp_error($verify)) {
            return false;
        }

        $verify = json_decode(wp_remote_retrieve_body($verify));
        return isset($verify->success) && $verify->success === true;
    }

    /**
     * Perform the login operation
     */
    private function perform_login($input) {
        return wp_signon([
            'user_login' => $input['username'],
            'user_password' => $input['password'],
            'remember' => $input['remember']
        ], is_ssl());
    }

    /**
     * Handle login errors with detailed messages and attempt tracking
     *
     * @param WP_Error $user WordPress error object
     * @return void
     */
    protected function handle_login_error($user) {
        if (!is_wp_error($user)) {
            return;
        }
    
        $error_code = $user->get_error_code();
        $message = '';
        
        // Get username from request data
        $request_data = $this->get_login_data();
        $username = isset($request_data['username']) ? sanitize_user($request_data['username']) : '';
        
        // Get the IP address
        $ip_address = $_SERVER['REMOTE_ADDR'];
        
        // Generate unique keys for tracking attempts
        $ip_key = 'login_attempts_ip_' . md5($ip_address);
        $username_key = 'login_attempts_username_' . md5($username);
        
        // Get current attempts
        $ip_attempts = (int) get_transient($ip_key);
        $username_attempts = (int) get_transient($username_key);
        
        // Increment attempts
        $ip_attempts++;
        $username_attempts++;
        
        // Set or update the transients (lock for 30 minutes = 1800 seconds)
        set_transient($ip_key, $ip_attempts, 1800);
        set_transient($username_key, $username_attempts, 1800);
        
        $max_attempts = apply_filters('biwillz_max_login_attempts', 5);
        $attempts_remaining = max(0, $max_attempts - max($ip_attempts, $username_attempts));
        
        // Check if user is locked out
        if ($ip_attempts >= $max_attempts || $username_attempts >= $max_attempts) {
            $lockout_message = sprintf(
                /* translators: %d: minutes until lockout expires */
                esc_html__('Too many failed login attempts. Please try again in %d minutes.', 'biwillz-auth'),
                30
            );
            
            wp_send_json_error([
                'message' => $lockout_message,
                'code' => 'locked_out',
                'attempts_remaining' => 0
            ]);
            return;
        }
    
        // For security, use a generic message that doesn't reveal if the username exists
        $message = sprintf(
            /* translators: %d: number of attempts remaining */
            esc_html__('Invalid username or password. %d attempts remaining before lockout.', 'biwillz-auth'),
            $attempts_remaining
        );
    
        // Only show specific messages for empty fields
        if ($error_code === 'empty_username') {
            $message = esc_html__('Error: The username field is empty.', 'biwillz-auth');
        } elseif ($error_code === 'empty_password') {
            $message = esc_html__('Error: The password field is empty.', 'biwillz-auth');
        }
    
        // Log the error if debug is enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(
                sprintf(
                    'Biwillz Auth: Login error for user %s: %s (Code: %s, Attempts: IP=%d, Username=%d)',
                    esc_html($username),
                    esc_html($message),
                    esc_html($error_code),
                    $ip_attempts,
                    $username_attempts
                )
            );
        }
    
        wp_send_json_error([
            'message' => $message,
            'code' => $error_code,
            'attempts_remaining' => $attempts_remaining
        ]);
    }

    /**
     * Handle successful login
     */
    private function handle_successful_login($user, $input) {
        // Prepare success response data
        $response_data = [
            'message' => 'Login successful! Redirecting...',
            'redirect_url' => $input['redirect_url']
        ];

        // Set user and cookies
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $input['remember']);

        // Send success response
        wp_send_json_success($response_data);
    }

    /**
     * Handle exceptions
     */
    private function handle_exception($exception) {
        error_log('Login exception: ' . $exception->getMessage());
        wp_send_json_error([
            'message' => 'An unexpected error occurred',
            'code' => 'unexpected_error'
        ]);
    }
}

// Initialize the handler
//global $biwillz_auth_login_handler;
//$biwillz_auth_login_handler = new Biwillz_Auth_Login_Handler();
