<?php

defined('ABSPATH') or die('We\'re sorry, but you cannot directly access this file.');

define('WOORPD_OPTIONS_GROUP', 'woorpd_options_group');

// List of checkbox options
global $woorpd_checkbox_options;
$woorpd_checkbox_options = [
    'woorpd_display_image',
    'woorpd_display_name',
    'woorpd_display_category',
    'woorpd_display_price',
    'woorpd_display_description',
    'woorpd_display_button',
    'woorpd_display_filtered_categories',
    'woorpd_debug_enable_logging',
];

// Global settings array
global $woorpd_settings;
$woorpd_settings = [
    'woorpd_api_woo_url' => 'sanitize_text_field', //string
    'woorpd_api_woo_ck' => 'sanitize_text_field', //string
    'woorpd_api_woo_cs' => 'sanitize_text_field', //string
    'woorpd_display_image' => 'sanitize_text_field', //checkbox
    'woorpd_display_name' => 'sanitize_text_field', //checkbox
    'woorpd_display_category' => 'sanitize_text_field', //checkbox
    'woorpd_display_price' => 'sanitize_text_field', //checkbox
    'woorpd_display_description' => 'sanitize_text_field', //checkbox
    'woorpd_display_button' => 'sanitize_text_field', //checkbox
    'woorpd_display_count_limit' => 'intval', //integer
    'woorpd_display_filtered_categories' => 'sanitize_text_field', //checkbox
    'woorpd_display_filtered_categories_ids' => 'intval', //integer
    'woorpd_debug_cache_duration' => 'intval', //integer
    'woorpd_debug_rate_limit' => 'intval', //integer
    'woorpd_debug_timeout' => 'intval', //integer
    'woorpd_debug_enable_logging' => 'sanitize_text_field', //checkbox
];

// Register settings
add_action('admin_init', function () {
    global $woorpd_settings;

    // Check if $woorpd_settings is not null and is an array
    if (is_array($woorpd_settings)) {
        foreach ($woorpd_settings as $setting_name => $sanitize_callback) {
            register_setting(WOORPD_OPTIONS_GROUP, $setting_name, $sanitize_callback);
        }
    } else {
        add_settings_error('woorpd', 'woorpd_settings_error', 'Warning: $woorpd_settings is not an array.', 'error');
    }
});


// Function to generate checkbox HTML
function generate_checkbox($name, $label, $value)
{
    echo '<label for="' . esc_attr($name) . '" class="woordp-mn-top">';
    echo '<input type="checkbox" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="1" ' . checked(1, $value, false) . ' />';
    echo esc_html($label);
    echo '</label>';
}

// Admin Page Callback
function woorpd_admin_page_callback()
{
    global $woorpd_settings, $woorpd_checkbox_options;

    // Create a nonce field
    $nonce = wp_create_nonce('woorpd_save_options_nonce');

    // Additional check to ensure $woorpd_checkbox_options is an array
    if (!is_array($woorpd_checkbox_options)) {
        // Log the error or display a message
        add_settings_error('woorpd', 'woorpd_checkbox_options_error', 'Warning: $woorpd_checkbox_options is not an array.', 'error');
        return;
    }

    // Handling form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate nonce
        if (!isset($_POST['woorpd_save_options_nonce']) || !wp_verify_nonce($_POST['woorpd_save_options_nonce'], 'woorpd_save_options_nonce')) {
            add_settings_error('woorpd', 'woorpd_nonce_verification', 'Nonce verification failed.', 'error');
            return;
        }

        // Save or delete options
        foreach ($woorpd_settings as $option_name => $sanitize_callback) {
            if (isset($_POST[$option_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$option_name]);
                update_option($option_name, $value);
            } elseif (in_array($option_name, $woorpd_checkbox_options)) {
                delete_option($option_name);
            }
        }

        // Reset options if the reset button is clicked
        if (isset($_POST['reset'])) {
            foreach (array_keys($woorpd_settings) as $option_name) {
                delete_option($option_name);
            }
        }
    }

    // Retrieve settings
    $retrieved_settings = [];
    foreach ($woorpd_settings as $key => $sanitize_callback) {
        $retrieved_settings[$key] = get_option($key, '');
    }

    // Admin settings form template.
    include plugin_dir_path(__FILE__) . 'admin-template.php';
}

// Menu Page Registration
// Check if function exists to avoid conflicts
if (!function_exists('woorpd_add_admin_menu')) {
    function woorpd_add_admin_menu()
    {
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
