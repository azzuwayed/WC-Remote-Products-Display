<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

// Admin Settings
if (is_admin()) {
    woorpd_include_files('admin/admin-settings.php');
}

// Include Functionality
woorpd_include_files('includes/api-connect.php');
woorpd_include_files('includes/shortcode.php');


/**
 * Adds a "Settings" link on the plugin page.
 * This link redirects to the WooCommerce Remote Products Display settings page.
 */
add_filter('plugin_action_links_' . plugin_basename(WOORPD_PLUGIN_ROOT . 'woorpd.php'), 'woorpd_add_settings_link');
function woorpd_add_settings_link($links)
{
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=woorpd')) . '" title="' . esc_attr__('Go to WooCommerce Remote Products Display settings', 'woorpd') . '">' . esc_html__('Settings', 'woorpd') . '</a>';
    array_unshift($links, $settings_link);  // This places the "Settings" link at the beginning
    return $links;
}

/**
 * Enable SSL certificate verification filter on local development environment with .local domains.
 */
function enable_ssl_certificate_verification_on_local()
{
    // Get the current host name
    $host = $_SERVER['HTTP_HOST'];

    // Check if the host name ends with '.local'
    if (substr($host, -6) === '.local') {
        add_filter('https_ssl_verify', '__return_false');
    }
}
add_action('init', 'enable_ssl_certificate_verification_on_local');
