<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

//----------------------------------------------------------------
// Development mode only:
woorpd_include_files('init-local-only.php');
//----------------------------------------------------------------

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

//----------------------------------------------------------------
//----------------------------------------------------------------
// Enqueue admin assets
function woorpd_enqueue_admin_assets($hook)
{
    // Check if we are on the plugin's settings page
    if ($hook != 'toplevel_page_woorpd_settings') {
        return;
    }

    // Register and enqueue admin styles
    wp_register_style('woorpd-admin-style', plugins_url('admin/css/admin-style.css', __FILE__), [], '1.0.2');
    wp_enqueue_style('woorpd-admin-style');

    // Register and enqueue admin scripts
    wp_register_script('woorpd-admin-script', plugins_url('admin/js/admin-script.js', __FILE__), ['jquery'], '1.0.2', true);
    wp_enqueue_script('woorpd-admin-script');

    // Localize script for AJAX
    wp_localize_script('woorpd-admin-script', 'woorpd_ajax_object', ['ajax_url' => admin_url('admin-ajax.php')]);
}
add_action('admin_enqueue_scripts', 'woorpd_enqueue_admin_assets');

// Enqueue frontend assets
function woorpd_enqueue_scripts()
{
    // Register and enqueue frontend styles
    wp_register_style('woorpd-product-display-styles', plugin_dir_url(__FILE__) . 'includes/css/woorpd-product-display-styles.css', [], '1.0.2');
    wp_enqueue_style('woorpd-product-display-styles');

    // Register and enqueue frontend scripts
    wp_register_script('woorpd-product-display-scripts', plugin_dir_url(__FILE__) . 'includes/js/woorpd-product-display-scripts.js', ['jquery'], '1.0.2', true);
    wp_enqueue_script('woorpd-product-display-scripts');

    // Localize script for AJAX
    wp_localize_script('woorpd-product-display-scripts', 'frontendajax', ['ajaxurl' => admin_url('admin-ajax.php')]);
}
add_action('wp_enqueue_scripts', 'woorpd_enqueue_scripts');