<?php

add_action('init', 'custom_dev_init');
function custom_dev_init() {
    $host = $_SERVER['HTTP_HOST'];

    // Disable SSL verification if the domain ends with '.local'
    if (substr($host, -6) === '.local') {
        add_filter('https_ssl_verify', '__return_false');
    }

}
