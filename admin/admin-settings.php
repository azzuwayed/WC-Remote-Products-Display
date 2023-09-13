<?php

defined('ABSPATH') or die('We\'re sorry, but you cannot directly access this file.');

define('WOORPD_OPTIONS_GROUP', 'woorpd_options_group');

// Global settings array
global $woorpd_settings;
$woorpd_settings = [
    'woorpd_api_woo_url' => 'sanitize_text_field',
    'woorpd_api_woo_ck' => 'sanitize_text_field',
    'woorpd_api_woo_cs' => 'sanitize_text_field',
    'woorpd_display_image' => 'sanitize_text_field',
    'woorpd_display_name' => 'sanitize_text_field',
    'woorpd_display_category' => 'sanitize_text_field',
    'woorpd_display_price' => 'sanitize_text_field',
    'woorpd_display_description' => 'sanitize_text_field',
    'woorpd_display_button' => 'sanitize_text_field',
    'woorpd_display_count_limit' => 'intval',
    'woorpd_display_filtered_categories' => 'sanitize_text_field',
    'woorpd_display_filtered_categories_ids' => 'intval',
    'woorpd_debug_cache_duration' => 'intval',
    'woorpd_debug_rate_limit' => 'intval',
    'woorpd_debug_timeout' => 'intval',
    'woorpd_debug_enable_logging' => 'sanitize_text_field',
];

add_action('admin_init', function(){
    global $woorpd_settings;

    // Check if $woorpd_settings is not null and is an array
    if (is_array($woorpd_settings)) {
        foreach ($woorpd_settings as $setting_name => $sanitize_callback) {
            register_setting(WOORPD_OPTIONS_GROUP, $setting_name, $sanitize_callback);
        }
    } else {
        // Handle the case where $woorpd_settings is null or not an array
        // You can log an error message or take other appropriate actions
        error_log('Warning: $woorpd_settings is not an array.');
    }
});

function woorpd_admin_page_callback() 
{
    $nonce = wp_create_nonce('woorpd_save_options_nonce');
    include plugin_dir_path(__FILE__) . 'admin-template.php';
}

// Check if function exists to avoid conflicts
if (!function_exists('woorpd_add_admin_menu')) {
    function woorpd_add_admin_menu() {
        add_menu_page(
            'WooRPD Settings', // Page title
            'WooRPD', // Menu title
            'manage_options', // Capability
            'woorpd_settings', // Menu slug
            'woorpd_admin_page_callback', // Callback function
            'dashicons-cover-image', // Icon URL
            100 // Position
        );
    }
    add_action('admin_menu', 'woorpd_add_admin_menu');
}

function save_woorpd_options()
{
    // Access the global variable
    global $woorpd_settings;

    error_log(print_r($woorpd_settings, true));
    
    // Validate that $woorpd_settings is an array
    /*
    if (!is_array($woorpd_settings)) {
        wp_send_json_error('Invalid settings');
        exit;
    }
    */

    // Check the nonce for security
    /*
    if (!isset($_POST['woorpd_save_options_nonce']) || !wp_verify_nonce($_POST['woorpd_save_options_nonce'], 'woorpd_save_options_nonce')) {
        wp_send_json_error('Nonce verification failed');
        exit;
    }
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have sufficient permissions to access this page.');
        return;
    }
    */
    //check_admin_referer('woorpd_save_options_nonce', 'nonce');

    // Loop through each option to update it

    foreach ($woorpd_settings as $option_name => $sanitize_callback) {
        if (isset($_POST[$option_name])) {
            $value = $_POST[$option_name] ?? null;
            update_option($option_name, call_user_func($sanitize_callback, $value));
        } else {

            wp_send_json_error("Option name $option_name is missing");
            wp_die();  // Stop further execution
        }
    }

    // If everything went well, send a success response
    wp_send_json_success();
}

// Attach the function to the wp_ajax_{action} hook
add_action('wp_ajax_save_woorpd_options', 'save_woorpd_options');


function woorpd_delete_options()
{
    global $woorpd_settings;

    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have sufficient permissions to access this page.');
        return;
    }

    check_admin_referer('woorpd_save_options_nonce', 'nonce');

    foreach (array_keys($woorpd_settings) as $option_name) {
        delete_option($option_name);
    }

    wp_send_json_success('Options reset successfully.');
}

add_action('wp_ajax_woorpd_delete_options', 'woorpd_delete_options');

