<?php
/**
* Plugin Name: Biwillz Authentication
* Plugin URI: https://biwillzcomputers.com
* Description: Custom authentication system with Google OAuth and traditional login
* Version: 2.0.0
* Author: Biwillz Computers
* Author URI: https://biwillzcomputers.com
* Text Domain: biwillz-auth
*/

if (!defined('ABSPATH')) {
    exit;
}

define('BIWILLZ_AUTH_PATH', plugin_dir_path(__FILE__));
define('BIWILLZ_AUTH_URL', plugin_dir_url(__FILE__));
define('BIWILLZ_AUTH_VERSION', '1.0.0');

require_once BIWILLZ_AUTH_PATH . 'includes/class-auth-handler.php';
require_once BIWILLZ_AUTH_PATH . 'includes/class-google-auth.php';
require_once BIWILLZ_AUTH_PATH . 'includes/class-user-auth.php';
require_once BIWILLZ_AUTH_PATH . 'includes/class-auth-settings.php';

class Biwillz_Auth {
    private static $instance = null;
    private $auth_handler;
    private $google_auth;
    private $user_auth;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'init_handlers'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        $this->init();
    }

    public function init() {
        $this->auth_handler = new Biwillz_Auth_Handler();
        $this->google_auth = new Biwillz_Google_Auth();
        $this->user_auth = new Biwillz_User_Auth();
    }

    public function enqueue_scripts() {

        wp_localize_script('your-script-handle', 'biwillzAuth', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('biwillz_auth_nonce')
        ));
        // Enqueue the main CSS file
        wp_enqueue_style(
            'biwillz-auth', 
            BIWILLZ_AUTH_URL . 'assets/css/auth.css', 
            array(), 
            BIWILLZ_AUTH_VERSION
        );

        // Enqueue jQuery as a dependency
        wp_enqueue_script('jquery');

        // Enqueue SweetAlert2 from CDN
        wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11',
            array(),
            '11.0.0',
            true
        );

        // Enqueue the main JavaScript file
        wp_enqueue_script(
            'biwillz-auth',
            BIWILLZ_AUTH_URL . 'assets/js/auth.js',
            array('jquery', 'sweetalert2'),
            BIWILLZ_AUTH_VERSION,
            true
        );

        // Localize the script
        wp_localize_script(
            'biwillz-auth',
            'biwillzAuth',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('biwillz_auth_nonce'),
                'password_requirements_message' => __('Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.', 'biwillz-auth'),
                'terms_message' => __('You must accept the terms and conditions to continue.', 'biwillz-auth')
            )
        );
    }

    public function init_handlers() {
        $this->auth_handler->init();
        $this->google_auth->init();
        $this->user_auth->init();
    }
}

function biwillz_auth() {
    return Biwillz_Auth::get_instance();
}

// Initialize the plugin
biwillz_auth();