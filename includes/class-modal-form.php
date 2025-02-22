<?php
/**
 * Modal Form Registration Class
 *
 * @package Biwillz_Auth
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Biwillz_Auth_Modal_Form {
    /**
     * Constructor.
     */
    public function __construct() {
        // Register shortcode
        add_shortcode('biwillz_auth_modal', array($this, 'render_modal_shortcode'));
    }

    /**
     * Render modal shortcode.
     *
     * @return string Modal HTML.
     */
    public function render_modal_shortcode() {
        ob_start();
        include plugin_dir_path(__FILE__) . '../templates/modal-auth.php';
        return ob_get_clean();
    }
}
