<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class BiWillz_Password_Reset_Handlers {
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
        $reset_link = 'https://biwillzcomputers.com/auth_reset/?key=' . $key . '&login=' . rawurlencode($user->user_login);

        // Prepare email HTML content
        require_once plugin_dir_path(__FILE__) . '../email-templates/password-reset.php';
        $email_template = get_password_reset_email_template($user, $reset_link);

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
}
