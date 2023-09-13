<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// delete_option('xxxxx'); 

// Function to run when the plugin is uninstalled
function woordp_uninstall_hook() {
    // Log an informational message to the WordPress error log
    error_log("WooRPD plugin has been uninstalled successfully.");

}

// Register the uninstall hook
register_uninstall_hook(__FILE__, 'woordp_uninstall');