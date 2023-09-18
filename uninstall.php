<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all traces of the plugin
function wcrpd_uninstall_hook()
{
    do_action('wcrpd_reset_everything');
}

// Register the uninstall hook
register_uninstall_hook(__FILE__, 'wcrpd_uninstall_hook');
