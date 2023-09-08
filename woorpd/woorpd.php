<?php
/**
 * Plugin Name: WooCommerce Remote Products Display
 * Description: A settings plugin with AJAX functionality and tabbed interface.
 * Version: 1.0
 * Author: Your Name
 */

// Add the admin page
add_action('admin_menu', 'woorpd_add_admin_page');
function woorpd_add_admin_page() {
    add_menu_page('WooRPD Settings', 'WooRPD', 'manage_options', 'woorpd_settings', 'woorpd_admin_page_callback', 'dashicons-cover-image', 100);
}

// Admin page callback
function woorpd_admin_page_callback() {
    // Create a nonce for our AJAX request
    $nonce = wp_create_nonce('woorpd_save_options_nonce');
    include plugin_dir_path(__FILE__) . 'admin-page.php';
}

// Register the options
add_action('admin_init', 'woorpd_register_options');
function woorpd_register_options() {
    register_setting('woorpd_options_group', 'woorpd_option1', 'sanitize_text_field');
    register_setting('woorpd_options_group', 'woorpd_option2', 'sanitize_text_field');
    register_setting('woorpd_options_group', 'woorpd_option3', 'sanitize_text_field');
}

// AJAX callback for saving options
add_action('wp_ajax_save_woorpd_options', 'save_woorpd_options');
function save_woorpd_options() {
    // Check if the user has the right capabilities
    if (!current_user_can('manage_options')) {
        wp_send_json_error('You do not have sufficient permissions to access this page.');
        return;
    }

    // Check for nonce validation
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'woorpd_save_options_nonce')) {
        wp_send_json_error('Nonce verification failed.');
        return;
    }

    // Save the options
    update_option('woorpd_option1', sanitize_text_field($_POST['option1']));
    update_option('woorpd_option2', sanitize_text_field($_POST['option2']));
    update_option('woorpd_option3', sanitize_text_field($_POST['option3']));

    // Send a success response
    wp_send_json_success('Options saved successfully.');
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'woorpd_enqueue_admin_scripts');
function woorpd_enqueue_admin_scripts() {
    wp_enqueue_style('woorpd-admin-style', plugins_url('admin-style.css', __FILE__));
    wp_enqueue_script('woorpd-admin-script', plugins_url('admin-script.js', __FILE__), array('jquery'), '1.0.0', true);
    wp_localize_script('woorpd-admin-script', 'woorpd_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));

}

// Shortcode to display saved options
add_shortcode('woorpd_data', 'woorpd_display_saved_data');
function woorpd_display_saved_data() {
    $option1 = get_option('woorpd_option1');
    $option2 = get_option('woorpd_option2');
    $option3 = get_option('woorpd_option3');

    return esc_html($option1) . '<br>' . esc_html($option2) . '<br>' . esc_html($option3);
}

