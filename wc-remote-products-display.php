<?php

/*
 * Plugin Name:     WC Remote Products Display
 * Plugin URI:      https://azzuwayed.com/wcrpd
 * Description:     Showcase WooCommerce products on another WordPress website effortlessly. Configure your settings, and then use the `[wcrpd]` shortcode to display products in posts, pages, or widgets.
 * Version:         1.0.0
 * Author:          Abdullah Alzuwayed
 * Author URI:      https://azzuwayed.com 
 * Text Domain:     wcrpd
 * Domain Path:     /languages
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

define('WCRPD_VERSION', '1.0.0');
define('WCRPD_NAME', 'wcrpd');
define('WCRPD_PLUGIN_ROOT', plugin_dir_path(__FILE__));
define('WCRPD_PLUGIN_ABSOLUTE', __FILE__);
define('WCRPD_MIN_PHP_VERSION', '7.4');
define('WCRPD_WP_VERSION', '6.0');

add_action(
    'init',
    static function () {
        load_plugin_textdomain('wcrpd', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
);

if (version_compare(PHP_VERSION, WCRPD_MIN_PHP_VERSION, '<=')) {
    add_action(
        'admin_init',
        static function () {
            deactivate_plugins(plugin_basename(__FILE__));
        }
    );
    add_action(
        'admin_notices',
        static function () {
            echo wp_kses_post(
                sprintf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    __('Remote Prod Display WC plugin requires PHP 7.4 or newer.', 'wcrpd')
                )
            );
        }
    );

    // Return early to prevent loading the plugin
    return;
}

// Initialize default settings;
class WCRPDSettings
{
    public static function wcrpd_initialize_settings()
    {
        $defaults = [
            'wcrpd_api_connection_status' => false,
            'wcrpd_display_image' => 'yes',
            'wcrpd_display_name' => 'yes',
            'wcrpd_display_category' => 'yes',
            'wcrpd_display_price' => 'yes',
            'wcrpd_display_description' => 'yes',
            'wcrpd_display_button' => 'yes',
            'wcrpd_display_count_limit' => 3,
            'wcrpd_debug_cache_duration' => 21600,
            'wcrpd_debug_timeout' => 10,
            'wcrpd_debug_rate_limit' => 30,
        ];

        // Loop through each setting to update it
        foreach ($defaults as $key => $default_value) {
            $current_value = get_option($key, $default_value);
            update_option($key, $current_value);
        }
    }
}
register_activation_hook(__FILE__, [WCRPDSettings::class, 'wcrpd_initialize_settings']);

// Function to include required files
function wcrpd_include_files($filename)
{
    $file_path = WCRPD_PLUGIN_ROOT . $filename;
    if (file_exists($file_path)) {
        require_once($file_path);
    } else {
        error_log("WC Remote Products Display: Failed to load $filename");
    }
}

// Initialization and Setup
wcrpd_include_files('includes/utilities.php');
wcrpd_include_files('initialize.php');

// Function to run when the plugin is deactivated
function wcrpd_deactivation_hook()
{
    do_action('wcrpd_flush_cache');
}
// Register the deactivation hook
register_deactivation_hook(__FILE__, 'wcrpd_deactivation_hook');
