<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

// Add the admin page
add_action('admin_menu', 'woorpd_add_admin_page');
function woorpd_add_admin_page()
{
    add_menu_page('WooRPD Settings', 'WooRPD', 'manage_options', 'woorpd_settings', 'woorpd_admin_page_callback', 'dashicons-cover-image', 100);
}

// Admin page callback
function woorpd_admin_page_callback()
{
    // Create a nonce for our AJAX request
    $nonce = wp_create_nonce('woorpd_save_options_nonce');
    include plugin_dir_path(__FILE__) . 'admin-template.php';
}

// Register the options
add_action('admin_init', 'woorpd_register_options');
function woorpd_register_options()
{
    register_setting('woorpd_options_group', 'api-woo-url', 'sanitize_text_field');
    register_setting('woorpd_options_group', 'api-woo-ck', 'sanitize_text_field');
    register_setting('woorpd_options_group', 'api-woo-cs', 'sanitize_text_field');
}

//----------------------------------------------------------------
// AJAX callback for saving options
add_action('wp_ajax_save_woorpd_options', 'save_woorpd_options');
function save_woorpd_options()
{
    // Check if the user has the right capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have sufficient permissions to access this page.');
        return;
    }

    // Check for nonce validation
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woorpd_save_options_nonce')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }

    // Save the options
    update_option('api-woo-url', sanitize_text_field($_POST['api-woo-url']));
    update_option('api-woo-ck', sanitize_text_field($_POST['api-woo-ck']));
    update_option('api-woo-cs', sanitize_text_field($_POST['api-woo-cs']));

    // Send a success response
    wp_send_json_success();
}

// AJAX callback for resetting options
add_action('wp_ajax_reset_woorpd_options', 'reset_woorpd_options');
function reset_woorpd_options()
{
    // Check if the user has the right capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have sufficient permissions to access this page.');
        return;
    }

    // Check for nonce validation
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woorpd_save_options_nonce')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }

    // Delete the options
    delete_option('api-woo-url');
    delete_option('api-woo-ck');
    delete_option('api-woo-cs');

    // Send a success response
    wp_send_json_success('Options reset successfully.');
}
//----------------------------------------------------------------

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'woorpd_enqueue_admin_scripts');
function woorpd_enqueue_admin_scripts()
{
    wp_enqueue_style('woorpd-admin-style', plugins_url('css/admin-style.css', __FILE__));
    wp_enqueue_script('woorpd-admin-script', plugins_url('js/admin-script.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('woorpd-admin-script', 'woorpd_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
}
