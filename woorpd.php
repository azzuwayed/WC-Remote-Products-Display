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
 * Description:     WooRPD fetches products from a remote website using WooCommerce webhooks then displays them using the shortcode [woorpdd] anywhere in your posts and pages.
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
define('WOORPD_MIN_PHP_VERSION', '7.4');
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
                    __('WooCommerce Remote Products Display plugin requires PHP 7.4 or newer.', WOORPD_TEXTDOMAIN)
                )
            );
        }
    );

    // Return early to prevent loading the plugin.
    return;
}

// Initialize default settings. To call it globally: WooRPDSettings::woorpd_initialize_settings();
class WooRPDSettings
{
    public static function woorpd_initialize_settings()
    {
        $defaults = [
            'woorpd_api_woo_url' => '',
            'woorpd_api_woo_ck' => '',
            'woorpd_api_woo_cs' => '',
            'woorpd_display_image' => 'yes',
            'woorpd_display_name' => 'yes',
            'woorpd_display_category' => 'yes',
            'woorpd_display_price' => 'yes',
            'woorpd_display_description' => 'yes',
            'woorpd_display_button' => 'yes',
            'woorpd_display_count_limit' => 3,
            'woorpd_display_filtered_categories' => '',
            'woorpd_display_filtered_categories_ids' => '',
            'woorpd_debug_cache_duration' => 21600,
            'woorpd_debug_timeout' => 10,
            'woorpd_debug_rate_limit' => 30,
            'woorpd_debug_enable_logging' => ''
        ];

        // Loop through each setting to update it
        foreach ($defaults as $key => $default_value) {
            $current_value = get_option($key, $default_value);
            update_option($key, $current_value);
        }
    }
}
register_activation_hook(__FILE__, [WooRPDSettings::class, 'woorpd_initialize_settings']);

// Function to include required files
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
woorpd_include_files('includes/utilities.php');
woorpd_include_files('initialize.php');

// Function to run when the plugin is deactivated
function woorpd_deactivation_hook()
{
    do_action('woorpd_flush_cache');
}
// Register the deactivation hook
register_deactivation_hook(__FILE__, 'woorpd_deactivation_hook');
