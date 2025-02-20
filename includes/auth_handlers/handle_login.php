<?php
// wp-content/plugins/Biwillz-Auth/includes/auth_handlers/handle_login.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Biwillz_Auth_Login_Handler {
    /**
     * Maximum number of login attempts before lockout
     */
    private const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lockout duration in seconds (30 minutes)
     */
    private const LOCKOUT_DURATION = 1800;

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize if needed
    }

    /**
     * Main login handler method
     */
    public function handle_login() {
        try {
            // Verify nonce
            if (!$this->verify_security_nonce()) {
                return;
            }

            // Get and sanitize input data
            $input = $this->get_sanitized_input();

            // Verify reCAPTCHA
            if (!$this->verify_recaptcha()) {
                return;
            }

            // Validate credentials
            if (!$this->validate_credentials($input)) {
                return;
            }

            // Check if user is locked out
            if ($this->is_user_locked_out($input['username'])) {
                return;
            }

            // Attempt login
            $login_result = $this->attempt_login($input);
            if (is_wp_error($login_result)) {
                return;
            }

            // Process successful login
            $this->handle_successful_login($input['username'], $login_result);

        } catch (Exception $e) {
            $this->handle_system_error($e);
        }
    }

    /**
     * Verify security nonce
     */
    private function verify_security_nonce() {
        if (!check_ajax_referer('biwillz_auth_nonce', 'nonce', false)) {
            wp_send_json_error([
                'message' => __('Security check failed', 'biwillz-auth'),
                'code' => 'security_failed'
            ]);
            return false;
        }
        return true;
    }

    /**
     * Get and sanitize input data
     */
    private function get_sanitized_input() {
        return [
            'username' => isset($_POST['username']) ? sanitize_user($_POST['username']) : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : '',
            'remember' => isset($_POST['remember']) ? (bool)$_POST['remember'] : false
        ];
    }

    /**
     * Verify reCAPTCHA response
     */
    private function verify_recaptcha() {
        $recaptcha_response = isset($_POST['g-recaptcha-response']) 
            ? sanitize_text_field($_POST['g-recaptcha-response']) 
            : '';

        if (!$this->verify_recaptcha_response($recaptcha_response)) {
            wp_send_json_error([
                'message' => __('Please complete the reCAPTCHA verification', 'biwillz-auth'),
                'code' => 'recaptcha_failed'
            ]);
            return false;
        }
        return true;
    }

    /**
     * Validate reCAPTCHA response
     */
    private function verify_recaptcha_response($response) {
        // Implement your reCAPTCHA verification logic here
        // You might want to make an API call to Google's reCAPTCHA service
        return true; // Replace with actual verification
    }

    /**
     * Validate login credentials
     */
    private function validate_credentials($input) {
        if (empty($input['username']) || empty($input['password'])) {
            wp_send_json_error([
                'message' => __('Username and password are required', 'biwillz-auth'),
                'code' => 'empty_credentials'
            ]);
            return false;
        }
        return true;
    }

    /**
     * Check if user is locked out
     */
    private function is_user_locked_out($username) {
        $failed_attempts = get_transient('biwillz_failed_login_' . $username);
        if ($failed_attempts && $failed_attempts >= self::MAX_LOGIN_ATTEMPTS) {
            wp_send_json_error([
                'message' => __('Too many failed attempts. Please try again later.', 'biwillz-auth'),
                'code' => 'locked_out'
            ]);
            return true;
        }
        return false;
    }

    /**
     * Attempt user login
     */
    private function attempt_login($input) {
        $user = wp_signon([
            'user_login' => $input['username'],
            'user_password' => $input['password'],
            'remember' => $input['remember']
        ], is_ssl());

        if (is_wp_error($user)) {
            $this->handle_failed_login($input['username']);
            wp_send_json_error([
                'message' => __('Invalid credentials', 'biwillz-auth'),
                'code' => 'invalid_credentials'
            ]);
            return $user;
        }

        return $user;
    }

    /**
     * Handle failed login attempt
     */
    private function handle_failed_login($username) {
        $failed_attempts = get_transient('biwillz_failed_login_' . $username);
        $failed_attempts = ($failed_attempts) ? $failed_attempts + 1 : 1;
        set_transient('biwillz_failed_login_' . $username, $failed_attempts, self::LOCKOUT_DURATION);
    }

    /**
     * Clear failed login attempts
     */
    private function clear_failed_attempts($username) {
        delete_transient('biwillz_failed_login_' . $username);
    }

    /**
     * Get redirect URL after successful login
     */
    private function get_redirect_url($user) {
        $redirect_url = admin_url();
        if (in_array('subscriber', $user->roles)) {
            $redirect_url = home_url();
        }
        return apply_filters('biwillz_auth_login_redirect', $redirect_url, $user);
    }

    /**
     * Handle successful login
     */
    private function handle_successful_login($username, $user) {
        $this->clear_failed_attempts($username);
        $redirect_url = $this->get_redirect_url($user);

        wp_send_json_success([
            'message' => __('Login successful', 'biwillz-auth'),
            'redirect_url' => esc_url_raw($redirect_url)
        ]);
    }

    /**
     * Handle system error
     */
    private function handle_system_error($exception) {
        error_log('Biwillz Auth Login Error: ' . $exception->getMessage());
        wp_send_json_error([
            'message' => __('An unexpected error occurred', 'biwillz-auth'),
            'code' => 'system_error'
        ]);
    }
}