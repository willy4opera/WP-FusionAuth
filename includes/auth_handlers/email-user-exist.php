<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
/**
 * Class BiWillz_Validation_Handler
 * Handles validation checks for usernames, emails, and passwords
 */
class BiWillz_Validation_Handler {
   
    /**
     * Check username availability
     */
    public function check_username_availability() {
        try {
            $this->verify_nonce();
            $username = $this->validate_username();
            $this->check_username_exists($username);
        } catch (ValidationException $e) {
            $this->handle_error($e->getMessage(), $e->getCode());
        }
        wp_die();
    }

    /**
     * Check email availability
     */
    public function check_email_availability() {
        try {
            $this->verify_nonce();
            $email = $this->validate_email();
            $this->check_email_exists($email);
        } catch (ValidationException $e) {
            $this->handle_error($e->getMessage(), $e->getCode());
        }
        wp_die();
    }

    /**
     * Check password strength
     */
    public function check_password_strength() {
        try {
            $this->verify_nonce();
            $password = $this->validate_password();
            $strength = $this->assess_password_strength($password);
            wp_send_json_success(array(
                'strength' => $strength,
                'message' => $this->get_strength_message($strength)
            ));
        } catch (ValidationException $e) {
            $this->handle_error($e->getMessage(), $e->getCode());
        }
        wp_die();
    }

    /**
     * Verify nonce
     * @throws ValidationException
     */
    private function verify_nonce() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'biwillz_auth_nonce')) {
            throw new ValidationException('Security check failed. Please refresh the page and try again.', 403);
        }
    }

    /**
     * Validate username
     * @return string
     * @throws ValidationException
     */
    private function validate_username() {
        if (!isset($_POST['username']) || empty($_POST['username'])) {
            throw new ValidationException('Username is required.', 400);
        }

        $username = sanitize_user(wp_unslash($_POST['username']), true);

        if (strlen($username) < 3 || strlen($username) > 20) {
            throw new ValidationException('Username must be between 3 and 20 characters.', 400);
        }

        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            throw new ValidationException('Username can only contain letters, numbers, and underscores.', 400);
        }

        // Check reserved words
        $reserved = array('admin', 'administrator', 'webmaster', 'root');
        if (in_array(strtolower($username), $reserved)) {
            throw new ValidationException('This username is not available.', 400);
        }

        return $username;
    }

    /**
     * Validate email
     * @return string
     * @throws ValidationException
     */
    private function validate_email() {
        if (!isset($_POST['email']) || empty($_POST['email'])) {
            throw new ValidationException('Email address is required.', 400);
        }

        $email = sanitize_email(wp_unslash($_POST['email']));

        if (!is_email($email)) {
            throw new ValidationException('Please enter a valid email address.', 400);
        }

        return $email;
    }

    /**
     * Validate password
     * @return string
     * @throws ValidationException
     */
    private function validate_password() {
        if (!isset($_POST['password']) || empty($_POST['password'])) {
            throw new ValidationException('Password is required.', 400);
        }

        return wp_unslash($_POST['password']);
    }

    /**
     * Check if username exists
     * @param string $username
     */
    private function check_username_exists($username) {
        if (username_exists($username)) {
            wp_send_json_error(array(
                'message' => 'This username is already taken.',
                'code' => 'username_exists'
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'Username is available.',
                'code' => 'username_available'
            ));
        }
    }

    /**
     * Check if email exists
     * @param string $email
     */
    private function check_email_exists($email) {
        if (email_exists($email)) {
            wp_send_json_error(array(
                'message' => 'This email address is already registered.',
                'code' => 'email_exists'
            ));
        } else {
            wp_send_json_success(array(
                'message' => 'Email address is available.',
                'code' => 'email_available'
            ));
        }
    }

    /**
     * Assess password strength
     * @param string $password
     * @return int Strength score (1-4)
     */
    private function assess_password_strength($password) {
        $strength = 0;
        
        // Length check
        if (strlen($password) >= 8) $strength++;
        if (strlen($password) >= 12) $strength++;
        
        // Character variety checks
        if (preg_match('/[A-Z]/', $password)) $strength++;
        if (preg_match('/[a-z]/', $password)) $strength++;
        if (preg_match('/[0-9]/', $password)) $strength++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $strength++;

        return min(4, ceil($strength / 1.5));
    }

    /**
     * Get password strength message
     * @param int $strength
     * @return string
     */
    private function get_strength_message($strength) {
        $messages = array(
            1 => 'Very weak - Please use a stronger password',
            2 => 'Weak - Consider adding more variety',
            3 => 'Medium - Getting better',
            4 => 'Strong - Good job!'
        );
        return $messages[$strength];
    }

    /**
     * Handle error responses
     * @param string $message
     * @param int $status
     */
    private function handle_error($message, $status = 400) {
        error_log('Validation error in ' . __CLASS__ . ': ' . $message);
        status_header($status);
        wp_send_json_error(array(
            'message' => $message,
            'code' => 'validation_error',
            'status' => $status
        ));
    }
}

/**
 * Custom exception for validation errors
 */
class ValidationException extends Exception {
    public function __construct($message, $code = 400) {
        parent::__construct($message, $code);
    }
}
