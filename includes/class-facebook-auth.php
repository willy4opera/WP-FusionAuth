<?php
class Biwillz_Facebook_Auth {
    private $app_id;
    private $app_secret;
    private $redirect_uri;

    public function __construct() {
        $this->app_id = get_option('biwillz_fb_app_id');
        $this->app_secret = get_option('biwillz_fb_app_secret');
        $this->redirect_uri = home_url('wp-login.php?action=biwillz_facebook_callback');

        // Initialize the Facebook login functionality
        add_action('init', array($this, 'init_facebook_login'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_facebook_scripts'));
        
        // Add Facebook login button to the login form
        add_action('biwillz_auth_social_buttons', array($this, 'add_facebook_button'));
        
        // Handle the callback
        add_action('init', array($this, 'handle_facebook_callback'));
    }

    public function init_facebook_login() {
        if (isset($_GET['action']) && $_GET['action'] === 'facebook_login') {
            $this->redirect_to_facebook();
        }
    }

    public function enqueue_facebook_scripts() {
        wp_enqueue_script('biwillz-facebook-auth', BIWILLZ_AUTH_URL . 'assets/js/facebook-auth.js', array('jquery'), '1.0.0', true);
        wp_localize_script('biwillz-facebook-auth', 'biwillzFacebookAuth', array(
            'appId' => $this->app_id,
            'redirectUri' => $this->redirect_uri
        ));
    }

    public function add_facebook_button() {
        ?>
        <div class="social-login-button facebook-login">
            <a href="<?php echo esc_url(home_url('wp-login.php?action=facebook_login')); ?>" class="facebook-button">
                <i class="fab fa-facebook"></i>
                <?php _e('Continue with Facebook', 'biwillz-auth'); ?>
            </a>
        </div>
        <?php
    }

    private function redirect_to_facebook() {
        $params = array(
            'client_id' => $this->app_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => 'email',
            'response_type' => 'code',
            'state' => wp_create_nonce('facebook_auth')
        );

        $login_url = 'https://www.facebook.com/v12.0/dialog/oauth?' . http_build_query($params);
        wp_redirect($login_url);
        exit;
    }

    public function handle_facebook_callback() {
        if (isset($_GET['action']) && $_GET['action'] === 'biwillz_facebook_callback') {
            // Verify state to prevent CSRF
            if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'facebook_auth')) {
                wp_die('Invalid request');
            }

            // Get the access token
            $token_url = 'https://graph.facebook.com/v12.0/oauth/access_token';
            $token_params = array(
                'client_id' => $this->app_id,
                'client_secret' => $this->app_secret,
                'redirect_uri' => $this->redirect_uri,
                'code' => $_GET['code']
            );

            $response = wp_remote_post($token_url, array(
                'body' => $token_params
            ));

            if (is_wp_error($response)) {
                wp_die('Failed to get access token');
            }

            $token_data = json_decode(wp_remote_retrieve_body($response), true);
            
            // Get user data from Facebook
            $graph_url = 'https://graph.facebook.com/v12.0/me';
            $graph_params = array(
                'access_token' => $token_data['access_token'],
                'fields' => 'id,email,first_name,last_name'
            );

            $user_response = wp_remote_get(add_query_arg($graph_params, $graph_url));
            
            if (is_wp_error($user_response)) {
                wp_die('Failed to get user data');
            }

            $user_data = json_decode(wp_remote_retrieve_body($user_response), true);
            
            // Login or register the user
            $this->login_or_register_user($user_data);
        }
    }

    private function login_or_register_user($fb_user) {
        $user_email = $fb_user['email'];
        $user = get_user_by('email', $user_email);

        if (!$user) {
            // Create new user
            $username = $this->generate_unique_username($fb_user['first_name'], $fb_user['last_name']);
            $random_password = wp_generate_password();
            
            $user_id = wp_create_user($username, $random_password, $user_email);
            
            if (is_wp_error($user_id)) {
                wp_die('Failed to create user account');
            }

            // Update user meta
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $fb_user['first_name'],
                'last_name' => $fb_user['last_name'],
            ));

            // Store Facebook ID in user meta
            update_user_meta($user_id, 'facebook_id', $fb_user['id']);
            
            $user = get_user_by('id', $user_id);
        }

        // Log the user in
        wp_set_auth_cookie($user->ID, true);
        
        // Redirect to home page with success message
        $redirect_url = add_query_arg('login', 'facebook-success', home_url());
        wp_redirect($redirect_url);
        exit;
    }

    private function generate_unique_username($first_name, $last_name) {
        $base_username = sanitize_user(strtolower($first_name . $last_name));
        $username = $base_username;
        $counter = 1;

        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        return $username;
    }
}