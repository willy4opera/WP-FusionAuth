<?php
if (!defined('ABSPATH')) {
    exit;
}

class Biwillz_Google_Auth {
    private $client_id;
    private $client_secret;
    private $redirect_uri;

    public function __construct() {
       
            $this->client_id = Biwillz_Auth_Settings::get_option('google_client_id', '');
            $this->client_secret = Biwillz_Auth_Settings::get_option('google_client_secret', '');
            $this->redirect_uri = site_url('wp-json/biwillz-auth/v1/google-callback');
        
    }

    public function init() {
        add_action('rest_api_init', array($this, 'register_rest_routes'));
    }

    public function register_rest_routes() {
        register_rest_route('biwillz-auth/v1', '/google-callback', array(
            'methods' => 'GET',
            'callback' => array($this, 'handle_google_callback'),
            'permission_callback' => '__return_true'
        ));
    }

   

    public function get_auth_url() {
        $base_url = 'https://accounts.google.com/o/oauth2/v2/auth';
        $params = array(
            'client_id' => $this->client_id,
            'redirect_uri' => $this->redirect_uri,
            'response_type' => 'code',
            'scope' => 'email profile',
            'access_type' => 'online',
            'state' => wp_create_nonce('google-auth')
        );

        return $base_url . '?' . http_build_query($params);
    }

    public function handle_google_callback($request) {
        $code = $request->get_param('code');
        $state = $request->get_param('state');

        if (!wp_verify_nonce($state, 'google-auth')) {
            wp_redirect(home_url('?login=failed'));
            exit;
        }

        // Exchange code for access token
        $token_response = wp_remote_post('https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code' => $code,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'redirect_uri' => $this->redirect_uri,
                'grant_type' => 'authorization_code'
            )
        ));

        if (is_wp_error($token_response)) {
            wp_redirect(home_url('?login=failed'));
            exit;
        }

        $token_body = json_decode(wp_remote_retrieve_body($token_response), true);
        $access_token = $token_body['access_token'];

        // Get user info
        $user_info_response = wp_remote_get('https://www.googleapis.com/oauth2/v2/userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token
            )
        ));

        if (is_wp_error($user_info_response)) {
            wp_redirect(home_url('?login=failed'));
            exit;
        }

        $user_info = json_decode(wp_remote_retrieve_body($user_info_response), true);
        $email = $user_info['email'];

        // Check if user exists
        $user = get_user_by('email', $email);

        if (!$user) {
            // Create new user
            $username = $this->generate_unique_username($user_info['given_name']);
            $random_password = wp_generate_password();
            
            $user_id = wp_create_user($username, $random_password, $email);
            
            if (is_wp_error($user_id)) {
                wp_redirect(home_url('?login=failed'));
                exit;
            }

            $user = get_user_by('ID', $user_id);
            update_user_meta($user_id, 'google_user_id', $user_info['id']);
        }

        // Log the user in
        wp_set_auth_cookie($user->ID, true);
        wp_redirect(home_url());
        exit;
    }

    private function generate_unique_username($base) {
        $username = sanitize_user($base);
        $index = 1;

        while (username_exists($username)) {
            $username = sanitize_user($base . $index);
            $index++;
        }

        return $username;
    }
}
