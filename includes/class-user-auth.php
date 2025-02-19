<?php
if (!defined('ABSPATH')) {
    exit;
}

class Biwillz_User_Auth {
    public function __construct() {
        // Constructor
    }

    public function init() {
        add_filter('authenticate', array($this, 'custom_authenticate'), 30, 3);
        add_action('wp_login', array($this, 'track_user_login'), 10, 2);
        add_action('wp_login_failed', array($this, 'track_failed_login'));
        add_filter('registration_errors', array($this, 'custom_registration_validation'), 10, 3);
    }

    public function custom_authenticate($user, $username, $password) {
        if (!empty($username) && !empty($password)) {
            // Check if user is locked out
            $lockout_duration = $this->check_login_lockout($username);
            if ($lockout_duration > 0) {
                return new WP_Error(
                    'locked_out',
                    sprintf(
                        __('Too many failed login attempts. Please try again in %d minutes.', 'biwillz-auth'),
                        ceil($lockout_duration / 60)
                    )
                );
            }
        }
        
        return $user;
    }

    public function track_user_login($user_login, $user) {
        // Reset failed login attempts on successful login
        delete_user_meta($user->ID, '_failed_login_attempts');
        delete_user_meta($user->ID, '_last_failed_login');

        // Log successful login
        $this->log_login_activity($user->ID, true);
    }

    public function track_failed_login($username) {
        $user = get_user_by('login', $username);
        if ($user) {
            $failed_attempts = (int) get_user_meta($user->ID, '_failed_login_attempts', true);
            update_user_meta($user->ID, '_failed_login_attempts', $failed_attempts + 1);
            update_user_meta($user->ID, '_last_failed_login', time());

            // Log failed login
            $this->log_login_activity($user->ID, false);
        }
    }

    private function check_login_lockout($username) {
        $user = get_user_by('login', $username);
        if (!$user) {
            return 0;
        }

        $failed_attempts = (int) get_user_meta($user->ID, '_failed_login_attempts', true);
        $last_failed = (int) get_user_meta($user->ID, '_last_failed_login', true);

        if ($failed_attempts >= 5) {
            $lockout_duration = 900; // 15 minutes in seconds
            $time_passed = time() - $last_failed;
            
            if ($time_passed < $lockout_duration) {
                return $lockout_duration - $time_passed;
            } else {
                // Reset if lockout period has passed
                delete_user_meta($user->ID, '_failed_login_attempts');
                delete_user_meta($user->ID, '_last_failed_login');
            }
        }

        return 0;
    }

    private function log_login_activity($user_id, $success) {
        $log_entry = array(
            'time' => current_time('mysql'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'success' => $success,
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        );

        $login_history = get_user_meta($user_id, '_login_history', true);
        if (!is_array($login_history)) {
            $login_history = array();
        }

        // Keep only last 10 entries
        array_unshift($login_history, $log_entry);
        $login_history = array_slice($login_history, 0, 10);

        update_user_meta($user_id, '_login_history', $login_history);
    }

    public function custom_registration_validation($errors, $sanitized_user_login, $user_email) {
        // Password strength validation
        if (isset($_POST['password']) && !empty($_POST['password'])) {
            $password = $_POST['password'];
            
            if (strlen($password) < 8) {
                $errors->add('password_too_short', __('Password must be at least 8 characters long.', 'biwillz-auth'));
            }
            
            if (!preg_match('/[A-Z]/', $password)) {
                $errors->add('password_no_upper', __('Password must contain at least one uppercase letter.', 'biwillz-auth'));
            }
            
            if (!preg_match('/[a-z]/', $password)) {
                $errors->add('password_no_lower', __('Password must contain at least one lowercase letter.', 'biwillz-auth'));
            }
            
            if (!preg_match('/[0-9]/', $password)) {
                $errors->add('password_no_number', __('Password must contain at least one number.', 'biwillz-auth'));
            }
        }

        // Email domain validation
        $email_domain = substr(strrchr($user_email, "@"), 1);
        $blocked_domains = $this->get_blocked_email_domains();
        
        if (in_array($email_domain, $blocked_domains)) {
            $errors->add('email_domain_blocked', __('This email domain is not allowed for registration.', 'biwillz-auth'));
        }

        return $errors;
    }

    private function get_blocked_email_domains() {
        // Add commonly blocked disposable email domains
        return array(
            'tempmail.com',
            'throwawaymail.com',
            'tempinbox.com',
            'fakeinbox.com',
            // Add more as needed
        );
    }
}
