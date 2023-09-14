<?php
// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove all traces of the plugin
function woorpd_uninstall_hook()
{
    do_action('woorpd_reset_everything');
}

// Register the uninstall hook
register_uninstall_hook(__FILE__, 'woorpd_uninstall');
