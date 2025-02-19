<?php
require_once('../../../../wp-load.php');

// Check if user is logged in
echo "User login status: " . (is_user_logged_in() ? "Logged in" : "Not logged in") . "\n";

// Get the logout URL and its components
$logout_url = wp_logout_url(home_url());
echo "Base logout URL: " . $logout_url . "\n";

// Parse the URL
$parsed_url = parse_url($logout_url);
echo "\nParsed URL components:\n";
print_r($parsed_url);

// Get the query parameters
if (isset($parsed_url['query'])) {
    parse_str($parsed_url['query'], $query_params);
    echo "\nQuery parameters:\n";
    print_r($query_params);
}

// Verify nonce
if (isset($query_params['_wpnonce'])) {
    echo "\nNonce verification result: " . 
         (wp_verify_nonce($query_params['_wpnonce'], 'log-out') ? "Valid" : "Invalid") . "\n";
}
