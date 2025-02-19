<?php
if (!defined('ABSPATH')) {
    exit;
}

class Biwillz_Auth_Shortcodes {
    private $is_standalone = false;
    
    public function __construct() {
        add_shortcode('biwillz_login_form', array($this, 'render_login_form'));
        add_shortcode('biwillz_register_form', array($this, 'render_register_form'));
        add_shortcode('biwillz_auth_status', array($this, 'render_auth_status'));
        add_shortcode('biwillz_password_reset', array($this, 'render_password_reset_form'));
        add_shortcode('biwillz_auth_form', array($this, 'render_auth_form')); // Add this line
        
        // Add early action to prevent theme loading if standalone
        add_action('template_redirect', array($this, 'handle_standalone_template'), 1);
    }

    /**
     * Handle standalone template loading
     */
    public function handle_standalone_template() {
        if ($this->is_standalone) {
            // Prevent theme template from loading
            define('IFRAME_REQUEST', true);
            remove_all_actions('wp_head');
            remove_all_actions('wp_footer');
            remove_all_actions('wp_print_styles');
            remove_all_actions('wp_print_head_scripts');
            
            // Only load essential styles and scripts
            add_action('wp_print_styles', array($this, 'print_standalone_styles'));
            add_action('wp_print_head_scripts', array($this, 'print_standalone_scripts'));
            
            // Display the form
            status_header(200);
            die($this->standalone_output);
        }
    }

    /**
     * Print standalone styles
     */
    public function print_standalone_styles() {
        wp_enqueue_style('biwillz-auth', BIWILLZ_AUTH_URL . 'assets/css/auth.css', array(), BIWILLZ_AUTH_VERSION);
    }

    /**
     * Print standalone scripts
     */
    public function print_standalone_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_script('biwillz-auth', BIWILLZ_AUTH_URL . 'assets/js/auth.js', array('jquery'), BIWILLZ_AUTH_VERSION, true);
        wp_print_scripts('jquery');
        wp_print_scripts('biwillz-auth');
    }


    public function render_auth_form($atts) {
        // If user is already logged in, show logged-in message
        if (is_user_logged_in()) {
            return $this->get_logged_in_message();
        }
    
        $atts = shortcode_atts(array(
            'redirect' => '',
            'standalone' => 'false'
        ), $atts, 'biwillz_auth_form');
    
        ob_start();
    
        // Include the auth form template
        $template_path = BIWILLZ_AUTH_PATH . 'templates/auth-form.php';
        if (file_exists($template_path)) {
            if ($atts['standalone'] === 'true') {
                $this->is_standalone = true;
                echo '<!DOCTYPE html><html><head>';
                echo '<meta charset="UTF-8">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                wp_print_styles();
                wp_print_head_scripts();
                echo '</head><body class="biwillz-standalone-form">';
            }
    
            include $template_path;
    
            if ($atts['standalone'] === 'true') {
                wp_print_footer_scripts();
                echo '</body></html>';
                $this->standalone_output = ob_get_clean();
                return '';
            }
        } else {
            return '<p>' . __('Error: Auth form template not found.', 'biwillz-auth') . '</p>';
        }
    
        return ob_get_clean();
    }
    
    /**
     * Render login form shortcode
     */
    public function render_login_form($atts) {
        // If user is already logged in, show logged-in message or redirect
        if (is_user_logged_in()) {
            return $this->get_logged_in_message();
        }

        $atts = shortcode_atts(array(
            'redirect' => '',
            'show_register' => 'true',
            'show_forgot_password' => 'true',
            'standalone' => 'false'
        ), $atts, 'biwillz_login_form');

        ob_start();

        // Include the login form template
        $template_path = BIWILLZ_AUTH_PATH . 'templates/login-form.php';
        if (file_exists($template_path)) {
            if ($atts['standalone'] === 'true') {
                $this->is_standalone = true;
                echo '<!DOCTYPE html><html><head>';
                echo '<meta charset="UTF-8">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                wp_print_styles();
                wp_print_head_scripts();
                echo '</head><body class="biwillz-standalone-form">';
            }

            include $template_path;

            if ($atts['standalone'] === 'true') {
                wp_print_footer_scripts();
                echo '</body></html>';
                $this->standalone_output = ob_get_clean();
                return '';
            }
        } else {
            return '<p>' . __('Error: Login form template not found.', 'biwillz-auth') . '</p>';
        }

        return ob_get_clean();
    }

    /**
     * Render registration form shortcode
     */
    public function render_register_form($atts) {
        // If user is already logged in, show logged-in message
        if (is_user_logged_in()) {
            return $this->get_logged_in_message();
        }

        // If registration is disabled, show message
        if (!get_option('users_can_register') || !Biwillz_Auth_Settings::get_option('enable_registration')) {
            return '<p>' . __('Registration is currently disabled.', 'biwillz-auth') . '</p>';
        }

        $atts = shortcode_atts(array(
            'redirect' => '',
            'show_login' => 'true',
            'standalone' => 'false'
        ), $atts, 'biwillz_register_form');

        ob_start();

        // Include the registration form template
        $template_path = BIWILLZ_AUTH_PATH . 'templates/register-form.php';
        if (file_exists($template_path)) {
            if ($atts['standalone'] === 'true') {
                $this->is_standalone = true;
                echo '<!DOCTYPE html><html><head>';
                echo '<meta charset="UTF-8">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                wp_print_styles();
                wp_print_head_scripts();
                echo '</head><body class="biwillz-standalone-form">';
            }

            include $template_path;

            if ($atts['standalone'] === 'true') {
                wp_print_footer_scripts();
                echo '</body></html>';
                $this->standalone_output = ob_get_clean();
                return '';
            }
        } else {
            return '<p>' . __('Error: Registration form template not found.', 'biwillz-auth') . '</p>';
        }

        return ob_get_clean();
    }

    // /**
    //  * Render password reset form shortcode
    //  */
    // public function render_password_reset_form($atts) {
    //     if (is_user_logged_in()) {
    //         return $this->get_logged_in_message();
    //     }

    //     $atts = shortcode_atts(array(
    //         'redirect' => '',
    //         'show_login' => 'true',
    //         'standalone' => 'false'
    //     ), $atts, 'biwillz_password_reset');

    //     ob_start();

    //     // Include the password reset form template
    //     $template_path = BIWILLZ_AUTH_PATH . 'templates/reset-password-form.php';
    //     if (file_exists($template_path)) {
    //         if ($atts['standalone'] === 'true') {
    //             $this->is_standalone = true;
    //             echo '<!DOCTYPE html><html><head>';
    //             echo '<meta charset="UTF-8">';
    //             echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    //             wp_print_styles();
    //             wp_print_head_scripts();
    //             echo '</head><body class="biwillz-standalone-form">';
    //         }

    //         include $template_path;

    //         if ($atts['standalone'] === 'true') {
    //             wp_print_footer_scripts();
    //             echo '</body></html>';
    //             $this->standalone_output = ob_get_clean();
    //             return '';
    //         }
    //     } else {
    //         return '<p>' . __('Error: Password reset form template not found.', 'biwillz-auth') . '</p>';
    //     }

    //     return ob_get_clean();
    // }


    public function render_password_reset_form($atts) {
        if (is_user_logged_in()) {
            return $this->get_logged_in_message();
        }
    
        // If we have key and login parameters, show the reset form
        if (isset($_GET['key']) && isset($_GET['login'])) {
            $template_path = BIWILLZ_AUTH_PATH . 'templates/reset-password-form.php';
        } else {
            // Otherwise show the request form
            $template_path = BIWILLZ_AUTH_PATH . 'templates/request-password-reset-form.php';
        }
    
        $atts = shortcode_atts(array(
            'redirect' => '',
            'show_login' => 'true',
            'standalone' => 'false'
        ), $atts, 'biwillz_password_reset');
    
        ob_start();
    
        if (file_exists($template_path)) {
            if ($atts['standalone'] === 'true') {
                $this->is_standalone = true;
                echo '<!DOCTYPE html><html><head>';
                echo '<meta charset="UTF-8">';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
                wp_print_styles();
                wp_print_head_scripts();
                echo '</head><body class="biwillz-standalone-form">';
            }
    
            include $template_path;
    
            if ($atts['standalone'] === 'true') {
                wp_print_footer_scripts();
                echo '</body></html>';
                $this->standalone_output = ob_get_clean();
                return '';
            }
        } else {
            return '<p>' . __('Error: Password reset form template not found.', 'biwillz-auth') . '</p>';
        }
    
        return ob_get_clean();
    }





    /**
     * Render authentication status shortcode
     */
    public function render_auth_status($atts) {
        $atts = shortcode_atts(array(
            'show_name' => 'true',
            'show_logout' => 'true'
        ), $atts, 'biwillz_auth_status');

        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view this content.', 'biwillz-auth') . '</p>';
        }

        $current_user = wp_get_current_user();
        $output = '<div class="biwillz-auth-status">';

        if ($atts['show_name'] === 'true') {
            $output .= sprintf(
                '<p>' . __('Welcome, %s!', 'biwillz-auth') . '</p>',
                esc_html($current_user->display_name)
            );
        }

        if ($atts['show_logout'] === 'true') {
            $output .= sprintf(
                '<p><a href="%s" class="biwillz-logout">%s</a></p>',
                wp_logout_url(home_url()),
                __('Logout', 'biwillz-auth')
            );
        }

        $output .= '</div>';
        return $output;
    }

    /**
     * Get logged-in message
     */
    private function get_logged_in_message() {
        $current_user = wp_get_current_user();
        return sprintf(
            '<div class="biwillz-logged-in-message">' .
            '<p>' . __('You are already logged in as %s.', 'biwillz-auth') . '</p>' .
            '<p><a href="%s" class="biwillz-logout">%s</a></p>' .
            '</div>',
            esc_html($current_user->display_name),
            wp_logout_url(home_url()),
            __('Logout', 'biwillz-auth')
        );
    }

    /**
     * Register and enqueue required scripts
     */
    public static function enqueue_scripts() {
        // Only enqueue if shortcode is present
        global $post;
        if (is_a($post, 'WP_Post') && (
            has_shortcode($post->post_content, 'biwillz_login_form') ||
            has_shortcode($post->post_content, 'biwillz_register_form') ||
            has_shortcode($post->post_content, 'biwillz_password_reset') ||
            has_shortcode($post->post_content, 'biwillz_auth_form')
        )) {
            wp_enqueue_style('biwillz-auth', BIWILLZ_AUTH_URL . 'assets/css/auth.css', array(), BIWILLZ_AUTH_VERSION);
            wp_enqueue_script('biwillz-auth', BIWILLZ_AUTH_URL . 'assets/js/auth.js', array('jquery'), BIWILLZ_AUTH_VERSION, true);
             // Add SweetAlert2
        wp_enqueue_style('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css', array(), '11.0.0');
        wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js', array(), '11.0.0', true);
            wp_localize_script('biwillz-auth', 'biwillzAuth', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('biwillz_auth_nonce'),
                'error_message' => __('An error occurred. Please try again.', 'biwillz-auth'),
                'password_requirements_message' => __('Please ensure your password meets all requirements.', 'biwillz-auth'),
                'terms_message' => __('Please accept the terms and conditions.', 'biwillz-auth'),
                'loading_text' => __('Creating Account...', 'biwillz-auth'),
                'default_button_text' => __('Create Account', 'biwillz-auth')
            ));
        }
    }
}

// Initialize the shortcodes
new Biwillz_Auth_Shortcodes();

// Register script enqueuing
add_action('wp_enqueue_scripts', array('Biwillz_Auth_Shortcodes', 'enqueue_scripts'));
