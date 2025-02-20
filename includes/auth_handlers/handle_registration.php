<?php
// wp-content/plugins/Biwillz-Auth/includes/auth_handlers/class-registration-handler.php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Biwillz_Auth_Registration_Handler {
    /**
     * Constants for validation
     */
    private const PHONE_PATTERN = '/^\+?[1-9]\d{1,14}$/';
    private const MIN_PASSWORD_LENGTH = 8;
    private const MIN_USERNAME_LENGTH = 3;
    private const MAX_USERNAME_LENGTH = 20;
    private const RESERVED_USERNAMES = [
        'admin', 'administrator', 'webmaster', 'support',
        'wordpress', 'wp', 'test', 'demo', 'root'
    ];

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize if needed
    }

    /**
     * Handle username availability check (AJAX)
     */
    public function check_username_availability() {
        try {
            check_ajax_referer('biwillz_auth_nonce', 'nonce');

            $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
            
            if (empty($username)) {
                wp_send_json_error([
                    'message' => __('Username is required.', 'biwillz-auth')
                ]);
            }

            try {
                $this->validate_username($username);
                wp_send_json_success([
                    'message' => __('Username is available.', 'biwillz-auth')
                ]);
            } catch (Exception $e) {
                wp_send_json_error([
                    'message' => $e->getMessage()
                ]);
            }

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'biwillz-auth')
            ]);
        }
    }

    /**
     * Handle email availability check (AJAX)
     */
    public function check_email_availability() {
        try {
            check_ajax_referer('biwillz_auth_nonce', 'nonce');

            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            
            if (empty($email)) {
                wp_send_json_error([
                    'message' => __('Email address is required.', 'biwillz-auth')
                ]);
            }

            try {
                $this->validate_email($email);
                wp_send_json_success([
                    'message' => __('Email address is available.', 'biwillz-auth')
                ]);
            } catch (Exception $e) {
                wp_send_json_error([
                    'message' => $e->getMessage()
                ]);
            }

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => __('Security check failed.', 'biwillz-auth')
            ]);
        }
    }

    /**
     * Handle registration (AJAX)
     */
    public function handle_registration() {
        try {
            check_ajax_referer('biwillz_auth_nonce', 'nonce');

            // Get and validate input
            $input = $this->get_registration_input();
            $this->validate_registration_input($input);

            // Check reCAPTCHA if enabled
            if ($this->is_recaptcha_enabled()) {
                $this->verify_recaptcha();
            }

            // Create user
            $user_id = $this->create_user($input);

            // Update user meta
            $this->update_user_meta($user_id, $input);

            // Auto login
            $this->auto_login_user($input);

            // Send success response
            wp_send_json_success([
                'message' => __('Registration successful! You are now logged in.', 'biwillz-auth'),
                'redirect_url' => home_url()
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get registration input
     */
    private function get_registration_input() {
        return [
            'username' => isset($_POST['username']) ? sanitize_user($_POST['username']) : '',
            'email' => isset($_POST['email']) ? sanitize_email($_POST['email']) : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : '',
            'phone' => isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '',
            'terms_accepted' => isset($_POST['terms_accepted']) ? $_POST['terms_accepted'] : ''
        ];
    }

    /**
     * Validate registration input
     */
    private function validate_registration_input($input) {
        // Check for empty fields
        foreach (['username', 'email', 'password', 'phone'] as $field) {
            if (empty($input[$field])) {
                throw new Exception(__('All fields are required.', 'biwillz-auth'));
            }
        }

        // Validate each field
        $this->validate_username($input['username']);
        $this->validate_email($input['email']);
        $this->validate_password($input['password']);
        $this->validate_phone($input['phone']);
        $this->validate_terms($input['terms_accepted']);
    }

    /**
     * Validate username
     */
    private function validate_username($username) {
        $username = trim($username);

        if (strlen($username) < self::MIN_USERNAME_LENGTH || strlen($username) > self::MAX_USERNAME_LENGTH) {
            throw new Exception(
                sprintf(__('Username must be between %d and %d characters.', 'biwillz-auth'),
                self::MIN_USERNAME_LENGTH,
                self::MAX_USERNAME_LENGTH)
            );
        }

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $username)) {
            throw new Exception(__('Username must start with a letter and can only contain letters, numbers, underscores, and hyphens.', 'biwillz-auth'));
        }

        if (in_array(strtolower($username), self::RESERVED_USERNAMES)) {
            throw new Exception(__('This username is not allowed.', 'biwillz-auth'));
        }

        if (username_exists($username)) {
            throw new Exception(__('This username is already taken.', 'biwillz-auth'));
        }
    }

    /**
     * Validate email
     */
    private function validate_email($email) {
        if (!is_email($email)) {
            throw new Exception(__('Please enter a valid email address.', 'biwillz-auth'));
        }

        if (email_exists($email)) {
            throw new Exception(__('This email address is already registered.', 'biwillz-auth'));
        }
    }

    /**
     * Validate password
     */
    private function validate_password($password) {
        if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new Exception(
                sprintf(__('Password must be at least %d characters long.', 'biwillz-auth'),
                self::MIN_PASSWORD_LENGTH)
            );
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception(__('Password must contain at least one uppercase letter.', 'biwillz-auth'));
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception(__('Password must contain at least one lowercase letter.', 'biwillz-auth'));
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception(__('Password must contain at least one number.', 'biwillz-auth'));
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            throw new Exception(__('Password must contain at least one special character.', 'biwillz-auth'));
        }
    }

    /**
     * Validate phone number
     */
    private function validate_phone($phone) {
        if (!preg_match(self::PHONE_PATTERN, $phone)) {
            throw new Exception(__('Please enter a valid phone number.', 'biwillz-auth'));
        }
    }

    /**
     * Validate terms acceptance
     */
    private function validate_terms($terms_accepted) {
        if ($terms_accepted !== 'yes') {
            throw new Exception(__('You must accept the terms and conditions.', 'biwillz-auth'));
        }
    }

    /**
     * Check if reCAPTCHA is enabled
     */
    private function is_recaptcha_enabled() {
        return Biwillz_Auth_Settings::get_option('enable_recaptcha', false);
    }

    /**
     * Verify reCAPTCHA
     */
    private function verify_recaptcha() {
        if (!isset($_POST['g-recaptcha-response'])) {
            throw new Exception(__('Please complete the reCAPTCHA verification.', 'biwillz-auth'));
        }

        $recaptcha_secret = Biwillz_Auth_Settings::get_option('recaptcha_secret_key');
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $recaptcha_secret,
                'response' => $_POST['g-recaptcha-response']
            ]
        ]);

        if (is_wp_error($response)) {
            throw new Exception(__('Failed to verify reCAPTCHA. Please try again.', 'biwillz-auth'));
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);
        if (!$body['success']) {
            throw new Exception(__('reCAPTCHA verification failed. Please try again.', 'biwillz-auth'));
        }
    }

    /**
     * Create user
     */
    private function create_user($input) {
        $userdata = [
            'user_login' => $input['username'],
            'user_email' => $input['email'],
            'user_pass' => $input['password'],
            'role' => 'customer',
            'show_admin_bar_front' => false,
            'user_status' => 0,
            'user_registered' => current_time('mysql')
        ];

        $user_id = wp_insert_user($userdata);
        if (is_wp_error($user_id)) {
            throw new Exception($user_id->get_error_message());
        }

        return $user_id;
    }

    /**
     * Update user meta
     */
    private function update_user_meta($user_id, $input) {
        update_user_meta($user_id, 'wp_user_level', 0);
        update_user_meta($user_id, 'pw_user_status', true);
        update_user_meta($user_id, 'phone', $input['phone']);
        update_user_meta($user_id, 'terms_accepted_at', current_time('mysql'));
        delete_user_meta($user_id, 'default_password_nag');
    }

    /**
     * Auto login user
     */
    private function auto_login_user($input) {
        $credentials = [
            'user_login' => $input['username'],
            'user_password' => $input['password'],
            'remember' => true
        ];

        $user = wp_signon($credentials, is_ssl());
        if (is_wp_error($user)) {
            throw new Exception($user->get_error_message());
        }
    }
}