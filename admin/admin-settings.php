<?php
// Prevent direct access to the file
defined('ABSPATH') or die('We\'re sorry, but you cannot directly access this file.');

// Constants
define('WCRPD_OPTIONS_GROUP_API', 'wcrpd_options_group_api');
define('WCRPD_OPTIONS_GROUP_DISPLAY', 'wcrpd_options_group_display');
define('WCRPD_OPTIONS_GROUP_DEBUG', 'wcrpd_options_group_debug');

// Global Variables for API Connection
global $wcrpd_api_settings;
$wcrpd_api_settings = [
    'wcrpd_api_woo_url' => 'sanitize_text_field',
    'wcrpd_api_woo_ck' => 'sanitize_text_field',
    'wcrpd_api_woo_cs' => 'sanitize_text_field'
];

// Global Variables for Display Settings
global $wcrpd_display_settings, $wcrpd_display_checkbox_options;
$wcrpd_display_settings = [
    'wcrpd_display_count_limit' => 'intval',
    'wcrpd_display_filtered_categories_ids' => 'sanitize_text_field'
];
$wcrpd_display_checkbox_options = [
    'wcrpd_display_image',
    'wcrpd_display_name',
    'wcrpd_display_category',
    'wcrpd_display_price',
    'wcrpd_display_description',
    'wcrpd_display_button',
    'wcrpd_display_filtered_categories'
];

// Global Variables for Debug Settings
global $wcrpd_debug_settings, $wcrpd_debug_checkbox_options;
$wcrpd_debug_settings = [
    'wcrpd_debug_cache_duration' => 'intval',
    'wcrpd_debug_rate_limit' => 'intval',
    'wcrpd_debug_timeout' => 'intval'
];
$wcrpd_debug_checkbox_options = [
    'wcrpd_debug_enable_logging'
];

/**
 * Register settings with WordPress for API Connection.
 */
function wcrpd_register_api_settings()
{
    global $wcrpd_api_settings;
    wcrpd_register_settings(WCRPD_OPTIONS_GROUP_API, $wcrpd_api_settings);
}
add_action('admin_init', 'wcrpd_register_api_settings');

/**
 * Register settings with WordPress for Display Settings.
 */
function wcrpd_register_display_settings()
{
    global $wcrpd_display_settings;
    wcrpd_register_settings(WCRPD_OPTIONS_GROUP_DISPLAY, $wcrpd_display_settings);
}
add_action('admin_init', 'wcrpd_register_display_settings');

/**
 * Register settings with WordPress for Debug Settings.
 */
function wcrpd_register_debug_settings()
{
    global $wcrpd_debug_settings;
    wcrpd_register_settings(WCRPD_OPTIONS_GROUP_DEBUG, $wcrpd_debug_settings);
}
add_action('admin_init', 'wcrpd_register_debug_settings');

/**
 * General function to register settings.
 *
 * @param string $group_name The name of the option group.
 * @param array $settings The settings to register.
 */
function wcrpd_register_settings($group_name, $settings)
{
    if (is_array($settings)) {
        foreach ($settings as $setting_name => $sanitize_callback) {
            register_setting($group_name, $setting_name, $sanitize_callback);
        }
    } else {
        add_settings_error('wcrpd', 'wcrpd_settings_error', 'Warning: Settings are not an array.', 'error');
    }
}

//----------------------------------------------------------------
//----------------------------------------------------------------
//----------------------------------------------------------------
//----------------------------------------------------------------

/**
 * Handles the submission of the API Connection form.
 */
function handle_api_form_submission()
{
    global $wcrpd_api_settings;

    // Validate nonce
    if (isset($_POST['wcrpd_save_api_nonce']) && wp_verify_nonce($_POST['wcrpd_save_api_nonce'], 'wcrpd_save_api_nonce')) {

        $logger = new WCRPDLogger();
        $api    = new WCRPDRemoteAPI($logger);

        // Sanitize and Connect to the WooCommerce API
        $apiwoourl = sanitize_text_field($_POST['wcrpd_api_woo_url'] ?? '');
        $apiwoock  = sanitize_text_field($_POST['wcrpd_api_woo_ck'] ?? '');
        $apiwoocs  = sanitize_text_field($_POST['wcrpd_api_woo_cs'] ?? '');
        $api->wcrpd_apiConnect($apiwoourl, $apiwoock, $apiwoocs);

        // Fetch categories
        $categories = $api->fetchCategories();

        // Check for errors and update options accordingly
        if (isset($categories['error'])) {
            //add_settings_error('wcrpd', 'wcrpd_api_error', esc_html($categories['error']), 'error');
            update_option('wcrpd_api_connection_status', false);
        } else {
            update_option('wcrpd_api_connection_status', true);

            // Save API settings
            foreach ($wcrpd_api_settings as $option_name => $sanitize_callback) {
                $value = sanitize_text_field($_POST[$option_name] ?? '');
                update_option($option_name, $value);
            }

            $all_categories = $categories['data'];
            $category_pairs = [];
            // Loop through each category to create pairs
            foreach ($all_categories as $category) {
                $pair = $category['name'] . ': ' . $category['id'];
                $category_pairs[] = $pair;
            }
            // Save the category pairs to WordPress options
            update_option('wcrpd_all_categories', $category_pairs);
        }
    } else {
        add_settings_error('wcrpd', 'wcrpd_nonce_verification', 'Nonce verification failed.', 'error');
    }
}


//----------------------------------------------------------------

/**
 * Handles the submission of the Display Settings form.
 */
function handle_display_form_submission()
{
    global $wcrpd_display_settings, $wcrpd_display_checkbox_options;

    // Validate nonce
    if (isset($_POST['wcrpd_save_display_nonce']) && wp_verify_nonce($_POST['wcrpd_save_display_nonce'], 'wcrpd_save_display_nonce')) {

        // Save options
        foreach ($wcrpd_display_settings as $option_name => $sanitize_callback) {
            if (isset($_POST[$option_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$option_name]);
                update_option($option_name, $value);
            }
        }

        // Handle checkboxes separately
        foreach ($wcrpd_display_checkbox_options as $checkbox_option) {
            if (isset($_POST[$checkbox_option])) {
                update_option($checkbox_option, 'yes');
            } else {
                update_option($checkbox_option, '');
            }
        }
    } else {
        add_settings_error('wcrpd', 'wcrpd_nonce_verification', 'Nonce verification failed.', 'error');
    }
}

//----------------------------------------------------------------

/**
 * Resets all plugin options and flushes the cache.
 */
function wcrpd_reset_plugin()
{
    do_action('wcrpd_reset_everything');
    WCRPDSettings::wcrpd_initialize_settings();
    add_action('admin_notices', static function (): void {
        echo wp_kses_post(
            sprintf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                __('All options and cache have been reset. First page load will be slower in order to cache the initial request.', 'wcrpd')
            )
        );
    });
}

/**
 * Flushes the cache.
 */
function wcrpd_flush_cache()
{
    do_action('wcrpd_flush_cache');
    add_action('admin_notices', static function (): void {
        echo wp_kses_post(
            sprintf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                __('All cache and transients have been flushed. First page load will be slower in order to cache the initial request.', 'wcrpd')
            )
        );
    });
}

/**
 * Handles the submission of the Debug Settings form.
 */
function handle_debug_form_submission()
{
    global $wcrpd_debug_settings, $wcrpd_debug_checkbox_options;

    // Validate nonce
    if (isset($_POST['wcrpd_save_debug_nonce']) && wp_verify_nonce($_POST['wcrpd_save_debug_nonce'], 'wcrpd_save_debug_nonce')) {

        // Handle Reset Plugin button
        if (isset($_POST['reset'])) {
            wcrpd_reset_plugin();
            return;
        }

        // Handle Flush Cache button
        if (isset($_POST['flush'])) {
            wcrpd_flush_cache();
            return;
        }

        // Save or delete options
        foreach ($wcrpd_debug_settings as $option_name => $sanitize_callback) {
            if (isset($_POST[$option_name])) {
                $value = call_user_func($sanitize_callback, $_POST[$option_name]);
                update_option($option_name, $value);
            }
        }

        // Handle checkboxes separately
        foreach ($wcrpd_debug_checkbox_options as $checkbox_option) {
            if (isset($_POST[$checkbox_option])) {
                update_option($checkbox_option, 'yes');
            } else {
                update_option($checkbox_option, '');
            }
        }
    } else {
        add_settings_error('wcrpd', 'wcrpd_nonce_verification', 'Nonce verification failed.', 'error');
    }
}

//----------------------------------------------------------------

/**
 * Main function to handle form submissions.
 */
function handle_form_submissions()
{
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
function generate_checkbox($name, $label, $value)
{
    $checked = ($value === 'yes') ? 'checked' : '';
    echo '<label for="' . esc_attr($name) . '">';
    echo '<input type="checkbox" id="' . esc_attr($name) . '" name="' . esc_attr($name) . '" value="1" ' . $checked . ' />';
    echo esc_html($label);
    echo '</label>';
}

/**
 * Callback function for the admin settings page.
 */
function wcrpd_admin_page_callback()
{
    // Nonce for each form
    $api_nonce = wp_create_nonce('wcrpd_save_api_nonce');
    $display_nonce = wp_create_nonce('wcrpd_save_display_nonce');
    $debug_nonce = wp_create_nonce('wcrpd_save_debug_nonce');

    // Admin settings form template.
    include plugin_dir_path(__FILE__) . 'admin-template.php';
}

/**
 * Add admin menu page.
 */
if (!function_exists('wcrpd_add_admin_menu')) {
    function wcrpd_add_admin_menu()
    {
        add_menu_page(
            'WCRPD Settings', // Page title
            'WCRPD', // Menu title
            'manage_options', // Capability
            'wcrpd', // Menu slug
            'wcrpd_admin_page_callback', // Callback function
            'dashicons-cover-image', // Icon URL
            100 // Position
        );
    }
    add_action('admin_menu', 'wcrpd_add_admin_menu');
}

//----------------------------------------------------------------

/**
 * Function to print saved categories.
 */
function print_saved_categories()
{
    $api_connection_status = get_option('wcrpd_api_connection_status');

    // Check API connection status first to avoid unnecessary operations
    if ($api_connection_status === false) {
        return 'API connection failed';
    }

    // Retrieve the saved category pairs from WordPress options
    $saved_category_pairs = get_option('wcrpd_all_categories', []);

    if (empty($saved_category_pairs)) {
        return 'No categories found';
    }

    // Convert the category pairs array to a string, wrapping each pair in square brackets
    $category_pairs_str = '[' . implode('], [', $saved_category_pairs) . ']';

    // Return the formatted string wrapped in a span with a green-color class
    return sprintf('<span class="green-color">[Category Name: Category ID] => %s</span>', esc_html($category_pairs_str));
}
