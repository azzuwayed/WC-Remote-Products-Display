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
