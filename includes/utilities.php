<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

add_action('wcrpd_reset_everything', [WCRPDUtilities::class, 'resetEverything']);
add_action('wcrpd_flush_cache', [WCRPDUtilities::class, 'flushCache']);

/**
 * Class WCRPDUtilities
 *
 * Utility functions for WCRPD.
 */
class WCRPDUtilities
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
        delete_transient('wcrpd_api_rate_limit');
        self::deleteAPIRequestCache();
        self::deleteRateLimitAndTimeoutTransients();
    }

    /**
     * Delete API request cache.
     */
    private static function deleteAPIRequestCache(): void
    {
        global $wpdb;

        // Delete transients starting with '_transient_wcrpd_'
        $like_pattern1 = $wpdb->esc_like('_transient_wcrpd_') . '%';
        $sql1 = "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s";
        $wpdb->query($wpdb->prepare($sql1, $like_pattern1));

        // Delete transients starting with '_transient_timeout_wcrpd_'
        $like_pattern2 = $wpdb->esc_like('_transient_timeout_wcrpd_') . '%';
        $sql2 = "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s";
        $wpdb->query($wpdb->prepare($sql2, $like_pattern2));
    }



    /**
     * Delete all plugin options.
     */
    private static function deleteAllOptions(): void
    {
        // Combine all option keys into a single array
        $all_options = array_merge(
            array_keys($GLOBALS['wcrpd_api_settings']),
            array_keys($GLOBALS['wcrpd_display_settings']),
            array_keys($GLOBALS['wcrpd_debug_settings']),
            $GLOBALS['wcrpd_display_checkbox_options'],
            $GLOBALS['wcrpd_debug_checkbox_options']
        );

        // Add additional options to the list
        $all_options[] = 'wcrpd_api_connection_status';
        $all_options[] = 'wcrpd_all_categories';

        // Loop through each option and delete it
        foreach ($all_options as $option) {
            delete_option($option);
        }
    }

    /**
     * Delete rate limit and timeout transients.
     */
    private static function deleteRateLimitAndTimeoutTransients(): void
    {
        global $wpdb;
        $sql = "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wcrpd_api_%'";
        $transients = $wpdb->get_col($sql);

        array_walk($transients, static function ($transient): void {
            $transient_name = str_replace(['_transient_', '_transient_timeout_'], '', $transient);
            delete_transient($transient_name);
        });
    }
}
