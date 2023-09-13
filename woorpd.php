<?php

/**
 * @package   WooCommerce Remote Products Display
 * @author    Abdullah Alzuwayed <support@azzuwayed.com>
 * @copyright {{author_copyright}}
 * @license   GNU General Public License v3.0
 * @link      https://azzuwayed.com
 *
 * Plugin Name:     WooCommerce Remote Products Display
 * Plugin URI:      https://azzuwayed.com/woorpd
 * Description:     WooRPD fetches products from a remote website using WooCommerce webhooks then displays them using the shortcode [woordpd] anywhere in your posts and pages.
 * Version:         1.0.0
 * Author:          Abdullah Alzuwayed
 * Author URI:      https://azzuwayed.com 
 * Text Domain:     woorpd
 * Domain Path:     /languages
 * 
 * License:         GNU General Public License v3.0
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.html
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

define('WOORPD_VERSION', '1.0.0');
define('WOORPD_TEXTDOMAIN', 'woorpd');
define('WOORPD_NAME', 'woorpd');
define('WOORPD_PLUGIN_ROOT', plugin_dir_path(__FILE__));
define('WOORPD_PLUGIN_ABSOLUTE', __FILE__);
define('WOORPD_MIN_PHP_VERSION', '8.0');
define('WOORPD_WP_VERSION', '6.0');

add_action(
    'init',
    static function () {
        load_plugin_textdomain(WOORPD_TEXTDOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
);

if (version_compare(PHP_VERSION, WOORPD_MIN_PHP_VERSION, '<=')) {
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
                    __('"{{plugin_name}}" requires PHP 8.0 or newer.', WOORPD_TEXTDOMAIN)
                )
            );
        }
    );

    // Return early to prevent loading the plugin.
    return;
}

// Include required files
function woorpd_include_files($filename)
{
    $file_path = WOORPD_PLUGIN_ROOT . $filename;
    if (file_exists($file_path)) {
        require_once($file_path);
    } else {
        error_log("WooCommerce Remote Products Display: Failed to load $filename");
    }
}

// Initialization and Setup
woorpd_include_files('Initialize.php');

// Initialize default settings
function woorpd_initialize_settings() {
    // Log the start of the function
    error_log("Initializing WooRPD plugin settings...");

    // Your default settings
    $defaults = [
        'woorpd_api_woo_url' => 'app.haracat.com',
        'woorpd_api_woo_ck' => 'ck_4cc51d58e231a46d9bbe45ce8710bf7ac69decdc',
        'woorpd_api_woo_cs' => 'cs_ae5609313e7cd111f569a9286ef4de74af8e3171',
        'woorpd_display_image' => 'yes',
        'woorpd_display_name' => 'yes',
        'woorpd_display_category' => 'no',
        'woorpd_display_price' => 'no',
        'woorpd_display_description' => 'no',
        'woorpd_display_button' => 'no',
        'woorpd_display_count_limit' => 10,
        'woorpd_display_filtered_categories' => 'no',
        'woorpd_display_filtered_categories_ids' => '',
        'woorpd_debug_cache_duration' => 3600,
        'woorpd_debug_rate_limit' => 60,
        'woorpd_debug_timeout' => 30,
        'woorpd_debug_enable_logging' => 'no'
    ];

    // Loop through each setting to update it
    foreach ($defaults as $key => $default_value) {
        $current_value = get_option($key, $default_value);
        update_option($key, $current_value);
    }

    // Log the end of the function
    error_log("WooRPD plugin settings initialized.");
}
register_activation_hook(__FILE__, 'woorpd_initialize_settings');


// Function to run when the plugin is deactivated
function woorpd_deactivation_hook() {
    // Log an informational message to the WordPress error log
    error_log("WooRPD plugin has been deactivated.");
}
// Register the deactivation hook
register_deactivation_hook(__FILE__, 'woorpd_deactivation_hook');