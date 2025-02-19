<?php
if (!defined('ABSPATH')) {
    exit;
}

class Biwillz_Ajax_Handlers {
    public function __construct() {
        // Login handlers
        add_action('wp_ajax_nopriv_biwillz_login', array($this, 'handle_login'));
        
        // Registration handlers
        add_action('wp_ajax_nopriv_biwillz_register', array($this, 'handle_registration'));
        
        // Logout handler
        add_action('wp_ajax_biwillz_logout', array($this, 'handle_logout'));
    }

    public function handle_login() {
        check_ajax_referer('biwillz_auth_nonce', 'nonce');

        $username = sanitize_user($_POST['username']);
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) ? (bool) $_POST['remember'] : false;

        if (empty($username) || empty($password)) {
            wp_send_json_error(array(
                'message' => __('Please fill in all required fields.', 'biwillz-auth')
            ));
        }

        $credentials = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => $remember
        );

        $user = wp_signon($credentials);

        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => $user->get_error_message()
            ));
        }

        wp_send_json_success(array(
            'message'      => __('Login successful! Redirecting...', 'biwillz-auth'),
            'redirect_url' => apply_filters('biwillz_login_redirect', home_url(), $user)
        ));
    }

    public function handle_registration() {
        check_ajax_referer('biwillz_auth_nonce', 'nonce');

        $username = sanitize_user($_POST['username']);
        $email    = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error(array(
                'message' => __('Please fill in all required fields.', 'biwillz-auth')
            ));
        }

        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => __('Please enter a valid email address.', 'biwillz-auth')
            ));
        }

        // Check if username exists
        if (username_exists($username)) {
            wp_send_json_error(array(
                'message' => __('This username is already taken.', 'biwillz-auth')
            ));
        }

        // Check if email exists
        if (email_exists($email)) {
            wp_send_json_error(array(
                'message' => __('This email address is already registered.', 'biwillz-auth')
            ));
        }

        // Validate password strength
        if (
            strlen($password) < 8 ||
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password)
        ) {
            wp_send_json_error(array(
                'message' => __('Password does not meet the requirements.', 'biwillz-auth')
            ));
        }

        // Create user
        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            wp_send_json_error(array(
                'message' => $user_id->get_error_message()
            ));
        }

        // Set default role
        $user = new WP_User($user_id);
        $user->set_role(apply_filters('biwillz_default_user_role', 'subscriber'));

        // Auto login after registration
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);

        do_action('biwillz_after_registration', $user_id);

        wp_send_json_success(array(
            'message'      => __('Registration successful! Redirecting...', 'biwillz-auth'),
            'redirect_url' => apply_filters('biwillz_registration_redirect', home_url(), $user_id)
        ));
    }

    public function handle_logout() {
        check_ajax_referer('biwillz_auth_nonce', 'nonce');

        wp_logout();

        wp_send_json_success(array(
            'message'      => __('Logout successful!', 'biwillz-auth'),
            'redirect_url' => home_url('/login')
        ));
    }
}

// Initialize the class
new Biwillz_Ajax_Handlers();
