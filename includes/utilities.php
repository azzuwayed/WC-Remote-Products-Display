<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

add_action('woorpd_reset_everything', [WooRPDUtilities::class, 'resetEverything']);
add_action('woorpd_flush_cache', [WooRPDUtilities::class, 'flushCache']);

/**
 * Class WooRPDUtilities
 *
 * Utility functions for WooRPD.
 */
class WooRPDUtilities
{
    /**
     * Reset everything (Flush cache and delete options).
     *
     */
    public static function resetEverything(): void
    {
        self::flushCache();
        self::deleteAllOptions();
    }

    /**
     * Flush all cache and transients (Excludes options).
     *
     */
    public static function flushCache(): void
    {
        delete_transient('woorpd_api_rate_limit');
        self::deleteAPIRequestCache();
        self::deleteRateLimitAndTimeoutTransients();
    }

    /**
     * Delete API request cache.
     */
    private static function deleteAPIRequestCache(): void
    {
    global $wpdb;
    $like_pattern = $wpdb->esc_like('_transient_woorpd_api_') . '%';
    $sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s";
    $wpdb->query($wpdb->prepare($sql, $like_pattern));
    }

    /**
     * Delete all plugin options.
     */
    private static function deleteAllOptions(): void
    {
        $all_options = array_merge(
            array_keys($GLOBALS['woorpd_api_settings']),
            array_keys($GLOBALS['woorpd_display_settings']),
            array_keys($GLOBALS['woorpd_debug_settings']),
            $GLOBALS['woorpd_display_checkbox_options'],
            $GLOBALS['woorpd_debug_checkbox_options']
        );

        array_walk($all_options, 'delete_option');
    }

    /**
     * Delete rate limit and timeout transients.
     */
    private static function deleteRateLimitAndTimeoutTransients(): void
    {
        global $wpdb;
        $sql = "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_woorpd_api_%'";
        $transients = $wpdb->get_col($sql);

        array_walk($transients, static function ($transient): void {
            $transient_name = str_replace(['_transient_', '_transient_timeout_'], '', $transient);
            delete_transient($transient_name);
        });
    }

}
