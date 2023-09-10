<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

function woorpd_get_products($count = null, $filtered_categories = null)
{
    $apiwoourl = get_option('api-woo-url');
    $apiwoock  = get_option('api-woo-ck');
    $apiwoocs  = get_option('api-woo-cs');

    // Create a logger instance
    $logger = new WooRPDLogger();

    // Create an API instance with the logger
    $api = new WooRPDRemoteAPI($logger);

    // Connect to the WooCommerce API
    $api->wooRPD_apiConnect($apiwoourl, $apiwoock, $apiwoocs);

    // Set cache duration, timeout, and rate limit (optional)
    $api->setCacheDuration(3600); // Cache for 1 hour (3600)
    $api->setTimeout(20); // Set timeout to 20 seconds
    $api->setRateLimit(10); // Allow 10 requests per minute

    //----------------------------------------------------------------
    // During Development ONLY
    $woorpd_dev  = 0;
    $host = $_SERVER['HTTP_HOST'];
    if (substr($host, -6) === '.local' && $woorpd_dev = 1) {
        $api->setCacheDuration(0);
        $api->flushCache();
    }
    //----------------------------------------------------------------

    // If $filtered_categories is not provided, use the global settings
    $filtered_categories_str = get_option('woorpd_global_filtered_categories', []); // Default to empty string if not set
    $filtered_categories = $filtered_categories_str ? array_map('intval', explode(',', $filtered_categories_str)) : [];

    // If $count is not provided, use the global settings
    if ($count === null) {
        $count = get_option('woorpd_global_count_limit', 3); // Default to 3 if not set
    }

    // Fetch products, the passed argument is the count limit and filtered categories
    $products = $api->fetchProducts($count, $filtered_categories);

    // Return products
    return $products;
}


// Shortcode to loop and display fetched products as cards 
function woorpd_display_products($atts = [])
{
    // Retrieve global visibility settings
    $display_image = get_option('woorpd_display_image', true);
    $display_name = get_option('woorpd_display_name', true);
    $display_category = get_option('woorpd_display_category', true);
    $display_price = get_option('woorpd_display_price', true);
    $display_description = get_option('woorpd_display_description', true);
    $display_url = get_option('woorpd_display_url', true);

    // Extract the shortcode attributes
    $attributes = shortcode_atts([
        'count_limit' => get_option('woorpd_global_count_limit', 3), // Default to global setting or 3
        'filtered_categories' => get_option('woorpd_global_filtered_categories', '') // Default to global setting or empty string
    ], $atts);

    // Fetch products using the shortcode attributes to override the global settings
    $response = woorpd_get_products($attributes['count_limit'], explode(',', $attributes['filtered_categories']));

    // Check for errors
    if (isset($response['error'])) {
        return '<div class="woorpd-error">' . esc_html($response['error']) . '</div>';
    }

    $products = $response['data'];

    // Start buffering the output
    ob_start();

    echo '<div class="woorpd-products-wrapper">';

    foreach ($products as $product) {
        echo '<div class="woorpd-product-card">';

        if ($display_url) {
            echo '<a href="' . esc_url($product["permalink"]) . '">';
        }

        if ($display_image && isset($product['images'][0]['src'])) {
            $img_src = $product['images'][0]['src'];
            echo '<div class="image-container">';
            echo '<img src="' . esc_url($img_src) . '" alt="' . esc_attr($product["name"]) . '" class="woorpd-product-image">';
            echo '</div>';
        }

        if ($display_url) {
            echo '</a>';
        }

        if ($display_name) {
            echo '<div class="woorpd-product-title">' . esc_html($product["name"]) . '</div>';
        }

        if ($display_category && isset($product["categories"][0]["name"])) {
            $category_label = __('Category:', 'woorpd');
            echo '<div class="woorpd-product-category">' . esc_html($category_label) . ' ' . esc_html($product["categories"][0]["name"]) . '</div>';
        }

        // Extract the currency symbol from price_html
        $price_html = $product['price_html'];
        $currency_pattern = '/<span class="woocommerce-Price-currencySymbol">(.+?)<\/span>/';
        preg_match($currency_pattern, $price_html, $currency_matches);
        $currency_symbol = isset($currency_matches[1]) ? html_entity_decode($currency_matches[1]) : '';

        if ($display_price && isset($product["price"])) {
            echo '<div class="woorpd-product-price">' . esc_html($currency_symbol . $product["price"]) . '</div>';
        }

        if ($display_description) {
            $description = (strlen($product["description"]) > 140) ? substr($product["description"], 0, 137) . '...' : $product["description"];
            echo '<div class="woorpd-product-description">' . wp_kses_post($description) . '</div>';
        }

        // Add "Shop Now" button with an attractive style
        if ($display_url) {
            $shop_now_label = __('Shop Now', 'woordp');
            echo '<a href="' . esc_url($product["permalink"]) . '" class="woorpd-shop-now-button">' . esc_html($shop_now_label) . '</a>';
        }

        echo '</div>';
    }

    echo '</div>';

    // Return the buffered output
    return ob_get_clean();
}


add_shortcode('woorpd', 'woorpd_display_products');

// Enqueue the CSS and JS files
function woorpd_enqueue_scripts()
{
    wp_enqueue_style('woorpd-product-display-styles', plugin_dir_url(__FILE__) . 'css/woorpd-product-display-styles.css');
    wp_enqueue_script('woorpd-product-display-scripts', plugin_dir_url(__FILE__) . 'js/woorpd-product-display-scripts.js', array('jquery'), null, true);
    wp_localize_script('woorpd-product-display-scripts', 'frontendajax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'woorpd_enqueue_scripts');
