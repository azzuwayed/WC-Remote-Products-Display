<?php
// Prevent direct access to the file
defined('ABSPATH') or die('We\'re sorry, but you cannot directly access this file.');

// Constants
define('WOORPD_OPTIONS_GROUP_API', 'woorpd_options_group_api');
define('WOORPD_OPTIONS_GROUP_DISPLAY', 'woorpd_options_group_display');
define('WOORPD_OPTIONS_GROUP_DEBUG', 'woorpd_options_group_debug');

// Global Variables for API Connection
global $woorpd_api_settings;
$woorpd_api_settings = [
    'woorpd_api_woo_url' => 'sanitize_text_field',
    'woorpd_api_woo_ck' => 'sanitize_text_field',
    'woorpd_api_woo_cs' => 'sanitize_text_field'
];

// Global Variables for Display Settings
global $woorpd_display_settings, $woorpd_display_checkbox_options;
$woorpd_display_settings = [
    'woorpd_display_count_limit' => 'intval',
    'woorpd_display_filtered_categories_ids' => 'sanitize_text_field'
];
$woorpd_display_checkbox_options = [
    'woorpd_display_image',
    'woorpd_display_name',
    'woorpd_display_category',
    'woorpd_display_price',
    'woorpd_display_description',
    'woorpd_display_button',
    'woorpd_display_filtered_categories'
];

// Global Variables for Debug Settings
global $woorpd_debug_settings, $woorpd_debug_checkbox_options;
$woorpd_debug_settings = [
    'woorpd_debug_cache_duration' => 'intval',
    'woorpd_debug_rate_limit' => 'intval',
    'woorpd_debug_timeout' => 'intval'
];
$woorpd_debug_checkbox_options = [
    'woorpd_debug_enable_logging'
];

/**
 * Register settings with WordPress for API Connection.
 */
function woorpd_register_api_settings() {
    global $woorpd_api_settings;
    woorpd_register_settings(WOORPD_OPTIONS_GROUP_API, $woorpd_api_settings);
}
add_action('admin_init', 'woorpd_register_api_settings');

/**
 * Register settings with WordPress for Display Settings.
 */
function woorpd_register_display_settings() {
    global $woorpd_display_settings;
    woorpd_register_settings(WOORPD_OPTIONS_GROUP_DISPLAY, $woorpd_display_settings);
}
add_action('admin_init', 'woorpd_register_display_settings');

/**
 * Register settings with WordPress for Debug Settings.
 */
function woorpd_register_debug_settings() {
    global $woorpd_debug_settings;
    woorpd_register_settings(WOORPD_OPTIONS_GROUP_DEBUG, $woorpd_debug_settings);
}
add_action('admin_init', 'woorpd_register_debug_settings');

/**
 * General function to register settings.
 *
 * @param string $group_name The name of the option group.
 * @param array $settings The settings to register.
 */
function woorpd_register_settings($group_name, $settings) {
    if (is_array($settings)) {
        foreach ($settings as $setting_name => $sanitize_callback) {
            register_setting($group_name, $setting_name, $sanitize_callback);
        }
    } else {
        add_settings_error('woorpd', 'woorpd_settings_error', 'Warning: Settings are not an array.', 'error');
    }
}

//----------------------------------------------------------------
//----------------------------------------------------------------
//----------------------------------------------------------------
//----------------------------------------------------------------

/**
 * Handles the submission of the API Connection form.
 */
function handle_api_form_submission() {
    global $woorpd_api_settings;

    // Validate nonce
    if (isset($_POST['woorpd_save_api_nonce']) && wp_verify_nonce($_POST['woorpd_save_api_nonce'], 'woorpd_save_api_nonce')) {
        // Save or delete options
        foreach ($woorpd_api_settings as $option_name => $sanitize_callback) {
            if (isset($_POST[$option_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$option_name]);
                update_option($option_name, $value);
            }
        }
    } else {
        add_settings_error('woorpd', 'woorpd_nonce_verification', 'Nonce verification failed.', 'error');
    }
}

//----------------------------------------------------------------

/**
 * Handles the submission of the Display Settings form.
 */
function handle_display_form_submission() {
    global $woorpd_display_settings, $woorpd_display_checkbox_options;

    // Validate nonce
    if (isset($_POST['woorpd_save_display_nonce']) && wp_verify_nonce($_POST['woorpd_save_display_nonce'], 'woorpd_save_display_nonce')) {
        // Save or delete options
        foreach ($woorpd_display_settings as $option_name => $sanitize_callback) {
            if (isset($_POST[$option_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$option_name]);
                update_option($option_name, $value);
            }
        }

        // Handle checkboxes separately
        foreach ($woorpd_display_checkbox_options as $checkbox_option) {
            if (isset($_POST[$checkbox_option])) {
                update_option($checkbox_option, 'yes');
            } else {
                update_option($checkbox_option, '');
            }
        }
    } else {
        add_settings_error('woorpd', 'woorpd_nonce_verification', 'Nonce verification failed.', 'error');
    }
}

//----------------------------------------------------------------

/**
 * Resets all plugin options and flushes the cache.
 */
function woorpd_reset_plugin() {
do_action('woorpd_reset_everything');
WooRPDSettings::woorpd_initialize_settings();
add_action('admin_notices', static function (): void {
    echo wp_kses_post(
        sprintf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            __('All cache, transients, and options have been reset.', 'woorpd'))
    );
});
}

/**
 * Flushes the cache.
 */
function woorpd_flush_cache() {
do_action('woorpd_flush_cache');
add_action('admin_notices', static function (): void {
    echo wp_kses_post(
        sprintf('<div class="notice notice-success is-dismissible"><p>%s</p></div>',
            __('All cache and transients have been flushed.', 'woorpd'))
    );
});
}

/**
 * Handles the submission of the Debug Settings form.
 */
function handle_debug_form_submission() {
    global $woorpd_debug_settings, $woorpd_debug_checkbox_options;

    // Validate nonce
    if (isset($_POST['woorpd_save_debug_nonce']) && wp_verify_nonce($_POST['woorpd_save_debug_nonce'], 'woorpd_save_debug_nonce')) {

        // Handle Reset Plugin button
        if (isset($_POST['reset'])) {
            woorpd_reset_plugin();
            return;
        }

        // Handle Flush Cache button
        if (isset($_POST['flush'])) {
            woorpd_flush_cache();
            return;
        }
        
        // Save or delete options
        foreach ($woorpd_debug_settings as $option_name => $sanitize_callback) {
            if (isset($_POST[$option_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$option_name]);
                update_option($option_name, $value);
            }
        }

        // Handle checkboxes separately
        foreach ($woorpd_debug_checkbox_options as $checkbox_option) {
            if (isset($_POST[$checkbox_option])) {
                update_option($checkbox_option, 'yes');
            } else {
                update_option($checkbox_option, '');
            }
        }
    } else {
        add_settings_error('woorpd', 'woorpd_nonce_verification', 'Nonce verification failed.', 'error');
    }
}

//----------------------------------------------------------------

/**
 * Main function to handle form submissions.
 */
function handle_form_submissions() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['form_id'])) {
            switch ($_POST['form_id']) {
                case 'api-connection-settings-form':
                    handle_api_form_submission();
                    break;
                case 'display-settings-form':
                    handle_display_form_submission();
                    break;
                case 'debug-settings-form':
                    handle_debug_form_submission();
                    break;
            }
        }
    }
}
add_action('admin_init', 'handle_form_submissions');


//----------------------------------------------------------------
//----------------------------------------------------------------
//----------------------------------------------------------------
//----------------------------------------------------------------

/**
 * Generate HTML for a checkbox.
 *
 * @param string $name  The name attribute of the checkbox.
 * @param string $label The label text for the checkbox.
 * @param mixed  $value The current value of the checkbox.
 */
function generate_checkbox($name, $label, $value) {
    $checked = ($value === 'yes') ? 'checked' : '';
    echo '<label for="' . esc_attr($name) . '">';
    echo '<input type="checkbox" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="1" ' . $checked . ' />';
    echo esc_html($label);
    echo '</label>';
}

/**
 * Callback function for the admin settings page.
 */
function woorpd_admin_page_callback() {
    // Nonce for each form
    $api_nonce = wp_create_nonce('woorpd_save_api_nonce');
    $display_nonce = wp_create_nonce('woorpd_save_display_nonce');
    $debug_nonce = wp_create_nonce('woorpd_save_debug_nonce');

    // Admin settings form template.
    include plugin_dir_path(__FILE__) . 'admin-template.php';

}

/**
 * Add admin menu page.
 */
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
