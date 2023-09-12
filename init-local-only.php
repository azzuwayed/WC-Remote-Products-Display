<?php

add_action('init', 'custom_dev_init');
function custom_dev_init() {
    $host = $_SERVER['HTTP_HOST'];

    // Disable SSL verification if the domain ends with '.local'
    if (substr($host, -6) === '.local') {
        add_filter('https_ssl_verify', '__return_false');
    }

    // Auto-login functionality
    if ( ! is_admin() || is_user_logged_in() ) return;

    // Only proceed if the domain ends with '.local'
    if (substr($host, -6) === '.local') {
        $creds = array();
        $creds['user_login'] = 'admin';
        $creds['user_password'] = 'admin';
        $creds['remember'] = true;
        $user = wp_signon( $creds );

        if ( is_wp_error( $user ) ) {
            echo $user->get_error_message();
        }

        wp_redirect( esc_url( get_admin_url() ) ); 
        exit;
    }
}
