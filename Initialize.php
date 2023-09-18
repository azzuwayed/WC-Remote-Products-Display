<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

//----------------------------------------------------------------
// Development mode only:
wcrpd_include_files('init-local-only.php');
//----------------------------------------------------------------

// General includes
//wcrpd_include_files('includes/enqueue.php');

// Admin Settings
if (is_admin()) {
    wcrpd_include_files('admin/admin-settings.php');
}

// Main functions
wcrpd_include_files('includes/api-connect.php');
wcrpd_include_files('includes/shortcode.php');

// Enqueue admin assets
function wcrpd_enqueue_admin_assets($hook)
{
    // Check if we are on the plugin's settings page
    if ($hook != 'toplevel_page_wcrpd') {
        return;
    }

    // Register and enqueue admin styles
    wp_register_style('wcrpd-admin-style', plugins_url('admin/css/admin-style.css', __FILE__), [], '1.0.0');
    wp_enqueue_style('wcrpd-admin-style');

    // Register and enqueue admin scripts
    wp_register_script('wcrpd-admin-script', plugins_url('admin/js/admin-script.js', __FILE__), ['jquery'], '1.0.0', true);
    wp_enqueue_script('wcrpd-admin-script');

    // Localize script for AJAX
    wp_localize_script('wcrpd-admin-script', 'wcrpd_ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('admin_enqueue_scripts', 'wcrpd_enqueue_admin_assets');

// Enqueue frontend assets
function wcrpd_enqueue_scripts()
{
    // Register and enqueue frontend styles
    wp_register_style('wcrpd-styles', plugin_dir_url(__FILE__) . 'includes/css/wcrpd-styles.css', [], '1.0.0');
    wp_enqueue_style('wcrpd-styles');

    // Register and enqueue frontend scripts
    wp_register_script('wcrpd-scripts', plugin_dir_url(__FILE__) . 'includes/js/wcrpd-scripts.js', ['jquery'], '1.0.0', true);
    wp_enqueue_script('wcrpd-scripts');

    // Localize script for AJAX
    wp_localize_script('wcrpd-scripts', 'frontendajax', ['ajaxurl' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'wcrpd_enqueue_scripts');

/**
 * Adds a "Settings" link on the plugin page.
 * This link redirects to the WC Remote Products Display settings page.
 */
add_filter('plugin_action_links_' . plugin_basename(WCRPD_PLUGIN_ROOT . 'wc-remote-products-display.php'), 'wcrpd_add_settings_link');
function wcrpd_add_settings_link($links)
{
    $settings_link = '<a href="' . esc_url(admin_url('options-general.php?page=wcrpd')) . '" title="' . esc_attr__('Go to WC Remote Products Display settings', 'wcrpd') . '">' . esc_html__('Settings', 'wcrpd') . '</a>';
    array_unshift($links, $settings_link);  // This places the "Settings" link at the beginning
    return $links;
}
