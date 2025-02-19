<?php
if (!defined('ABSPATH')) {
    exit;
}

class Biwillz_Auth_Settings {
    private $options;
    private $settings_page = 'biwillz-auth-settings';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        $this->options = get_option('biwillz_auth_settings');
    }

    public function add_facebook_settings($settings) {
        $settings['facebook'] = array(
            'title' => __('Facebook Authentication', 'biwillz-auth'),
            'fields' => array(
                'biwillz_fb_app_id' => array(
                    'title' => __('Facebook App ID', 'biwillz-auth'),
                    'type' => 'text',
                    'description' => __('Enter your Facebook App ID', 'biwillz-auth')
                ),
                'biwillz_fb_app_secret' => array(
                    'title' => __('Facebook App Secret', 'biwillz-auth'),
                    'type' => 'password',
                    'description' => __('Enter your Facebook App Secret', 'biwillz-auth')
                )
            )
        );
        return $settings;
    }

    public function add_settings_page() {
        add_menu_page(
            __('BiWillz Auth Settings', 'biwillz-auth'),
            __('BiWillz Auth', 'biwillz-auth'),
            'manage_options',
            $this->settings_page,
            array($this, 'render_settings_page'),
            'dashicons-lock',
            55
        );
    }

    public function enqueue_admin_scripts($hook) {
        if ($hook != 'toplevel_page_' . $this->settings_page) {
            return;
        }

        wp_enqueue_style('biwillz-auth-admin', BIWILLZ_AUTH_URL . 'assets/css/admin.css', array(), BIWILLZ_AUTH_VERSION);
        wp_enqueue_script('biwillz-auth-admin', BIWILLZ_AUTH_URL . 'assets/js/admin.js', array('jquery'), BIWILLZ_AUTH_VERSION, true);
    }

    public function register_settings() {
        register_setting(
            'biwillz_auth_settings',
            'biwillz_auth_settings',
            array($this, 'sanitize_settings')
        );

        // General Settings Section
        add_settings_section(
            'general_settings',
            __('General Settings', 'biwillz-auth'),
            array($this, 'render_general_section'),
            $this->settings_page
        );

        add_settings_field(
            'enable_registration',
            __('Enable Registration', 'biwillz-auth'),
            array($this, 'render_checkbox_field'),
            $this->settings_page,
            'general_settings',
            array(
                'id' => 'enable_registration',
                'description' => __('Allow new users to register', 'biwillz-auth')
            )
        );

        add_settings_field(
            'default_redirect',
            __('Default Redirect', 'biwillz-auth'),
            array($this, 'render_text_field'),
            $this->settings_page,
            'general_settings',
            array(
                'id' => 'default_redirect',
                'description' => __('Default URL to redirect after login (leave empty for home page)', 'biwillz-auth')
            )
        );

        // Google OAuth Settings Section
        add_settings_section(
            'google_settings',
            __('Google OAuth Settings', 'biwillz-auth'),
            array($this, 'render_google_section'),
            $this->settings_page
        );

        add_settings_field(
            'enable_google_auth',
            __('Enable Google Authentication', 'biwillz-auth'),
            array($this, 'render_checkbox_field'),
            $this->settings_page,
            'google_settings',
            array(
                'id' => 'enable_google_auth',
                'description' => __('Allow users to sign in with Google', 'biwillz-auth')
            )
        );

        add_settings_field(
            'google_client_id',
            __('Google Client ID', 'biwillz-auth'),
            array($this, 'render_text_field'),
            $this->settings_page,
            'google_settings',
            array(
                'id' => 'google_client_id',
                'description' => __('Enter your Google OAuth Client ID', 'biwillz-auth')
            )
        );

        add_settings_field(
            'google_client_secret',
            __('Google Client Secret', 'biwillz-auth'),
            array($this, 'render_password_field'),
            $this->settings_page,
            'google_settings',
            array(
                'id' => 'google_client_secret',
                'description' => __('Enter your Google OAuth Client Secret', 'biwillz-auth')
            )
        );

        // reCAPTCHA Settings Section
add_settings_section(
    'recaptcha_settings',
    __('reCAPTCHA Settings', 'biwillz-auth'),
    array($this, 'render_recaptcha_section'),
    $this->settings_page
);

add_settings_field(
    'enable_recaptcha',
    __('Enable reCAPTCHA', 'biwillz-auth'),
    array($this, 'render_checkbox_field'),
    $this->settings_page,
    'recaptcha_settings',
    array(
        'id' => 'enable_recaptcha',
        'description' => __('Enable reCAPTCHA protection for login forms', 'biwillz-auth')
    )
);

add_settings_field(
    'recaptcha_site_key',
    __('reCAPTCHA Site Key', 'biwillz-auth'),
    array($this, 'render_text_field'),
    $this->settings_page,
    'recaptcha_settings',
    array(
        'id' => 'recaptcha_site_key',
        'description' => __('Enter your reCAPTCHA Site Key', 'biwillz-auth')
    )
);

add_settings_field(
    'recaptcha_secret_key',
    __('reCAPTCHA Secret Key', 'biwillz-auth'),
    array($this, 'render_password_field'),
    $this->settings_page,
    'recaptcha_settings',
    array(
        'id' => 'recaptcha_secret_key',
        'description' => __('Enter your reCAPTCHA Secret Key', 'biwillz-auth')
    )
);
    }

    

    public function sanitize_settings($input) {
        $sanitized = array();

        // Sanitize reCAPTCHA settings
        $sanitized['enable_recaptcha'] = isset($input['enable_recaptcha']) ? 1 : 0;
        $sanitized['recaptcha_site_key'] = sanitize_text_field($input['recaptcha_site_key']);

        // Preserve the secret key if it's not being updated
        if (empty($input['recaptcha_secret_key'])) {
            $sanitized['recaptcha_secret_key'] = $this->options['recaptcha_secret_key'] ?? '';
        } else {
            $sanitized['recaptcha_secret_key'] = sanitize_text_field($input['recaptcha_secret_key']);
        }

        // Sanitize checkboxes
        $sanitized['enable_registration'] = isset($input['enable_registration']) ? 1 : 0;
        $sanitized['enable_google_auth'] = isset($input['enable_google_auth']) ? 1 : 0;

        // Sanitize text fields
        $sanitized['default_redirect'] = esc_url_raw($input['default_redirect']);
        $sanitized['google_client_id'] = sanitize_text_field($input['google_client_id']);
        
        // Preserve the client secret if it's not being updated
        if (empty($input['google_client_secret'])) {
            $sanitized['google_client_secret'] = $this->options['google_client_secret'] ?? '';
        } else {
            $sanitized['google_client_secret'] = sanitize_text_field($input['google_client_secret']);
        }

        return $sanitized;
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'biwillz-auth'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <?php settings_errors(); ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('biwillz_auth_settings');
                do_settings_sections($this->settings_page);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_recaptcha_section() {
        echo '<p>' . esc_html__('Configure Google reCAPTCHA v2 settings. Get your keys from https://www.google.com/recaptcha/admin', 'biwillz-auth') . '</p>';
    }

    public function render_general_section() {
        echo '<p>' . esc_html__('Configure general authentication settings.', 'biwillz-auth') . '</p>';
    }

    public function render_google_section() {
        echo '<p>' . esc_html__('Configure Google OAuth authentication settings.', 'biwillz-auth') . '</p>';
    }

    public function render_checkbox_field($args) {
        $id = $args['id'];
        $checked = isset($this->options[$id]) ? checked($this->options[$id], 1, false) : '';
        ?>
        <label>
            <input type="checkbox" name="biwillz_auth_settings[<?php echo esc_attr($id); ?>]" value="1" <?php echo $checked; ?>>
            <?php echo esc_html($args['description']); ?>
        </label>
        <?php
    }

    public function render_text_field($args) {
        $id = $args['id'];
        $value = isset($this->options[$id]) ? $this->options[$id] : '';
        ?>
        <input type="text" 
               name="biwillz_auth_settings[<?php echo esc_attr($id); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }

    public function render_password_field($args) {
        $id = $args['id'];
        $value = isset($this->options[$id]) ? $this->options[$id] : '';
        ?>
        <input type="password" 
               name="biwillz_auth_settings[<?php echo esc_attr($id); ?>]" 
               value="<?php echo esc_attr($value); ?>" 
               class="regular-text">
        <p class="description"><?php echo esc_html($args['description']); ?></p>
        <?php
    }

    public static function get_option($key, $default = '') {
        $options = get_option('biwillz_auth_settings');
        return isset($options[$key]) ? $options[$key] : $default;
    }
}

// Initialize the settings
new Biwillz_Auth_Settings();
