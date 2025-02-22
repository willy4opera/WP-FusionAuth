<?php
// wp-content/plugins/Biwillz-Auth/includes/auth_handlers/handle_modal_login.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Biwillz_Auth_Modal_Login_Handler {
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize if needed
    }

    /**
     * Handle modal login request
     */
    public function handle_modal_login() {
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
        
        // Fallback to checkout page if still empty
        if (empty($redirect_url)) {
            $redirect_url = wc_get_checkout_url();
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
        // Implement your reCAPTCHA verification logic here
        // This method should be implemented based on your reCAPTCHA configuration
        return true; // Replace with actual verification
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
     * Handle login error
     */
    private function handle_login_error($user) {
        error_log('Login error: ' . $user->get_error_message());
        wp_send_json_error([
            'message' => $user->get_error_message(),
            'code' => $user->get_error_code()
        ]);
    }

    /**
     * Handle successful login
     */
    private function handle_successful_login($user, $input) {
        // Prepare success response data
        $response_data = [
            'message' => 'Login successful Proceed with Order!!',
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