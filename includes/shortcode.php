<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

function get_woocommerce_products($count = 10, $filtered_categories = [])
{
    $apiwoourl = get_option('api-woo-url');
    $apiwoock  = get_option('api-woo-ck');
    $apiwoocs  = get_option('api-woo-cs');

    // Create a logger instance
    $logger = new WOORPD_Logger();

    // Create an API instance with the logger
    $api = new WOORPD_WooCommerceAPI($logger);

    // Connect to the WooCommerce API
    $api->wooRPD_apiConnect($apiwoourl, $apiwoock, $apiwoocs);

    // Set cache duration, timeout, and rate limit (optional)
    $api->setCacheDuration(3600); // Cache for 1 hour
    $api->setTimeout(20); // Set timeout to 20 seconds
    $api->setRateLimit(10); // Allow 10 requests per minute

    // Fetch products, the passed argument is the count limit
    $products = $api->fetchProducts($count, $filtered_categories);

    // Check if there's an error in the fetched products
    if (isset($products['error']) || isset($products['message'])) {
        return $products;
    }

    // Return products
    return $products;
}


// Shortcode to loop and display fetched products as cards (only show the product image, title, and description)
function woorpd_display_products($atts = [])
{
    // Extract the attributes
    $attributes = shortcode_atts([
        'count_limit' => 10, // Default value
        'filtered_categories' => '' // Default value (empty string)
    ], $atts);

    $count_limit = intval($attributes['count_limit']);
    $filtered_categories = array_map('intval', explode(',', $attributes['filtered_categories']));

    // Fetch products
    $response = get_woocommerce_products($count_limit, $filtered_categories);

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
        echo '<a href="' . esc_url($product["permalink"]) . '">';

        // Check if the product has an image, if not use a placeholder
        echo '<div class="image-container">';
        if (isset($product['images'][0]['src'])) {
            $img_src = $product['images'][0]['src'];
            echo '<img src="' . esc_url($img_src) . '" alt="' . esc_attr($product["name"]) . '" class="woorpd-product-image">';
        } else {
            $img_src = plugin_dir_url(__FILE__) . 'images/woorpd-placeholder.png';
            echo '<img src="' . esc_url($img_src) . '" alt="' . esc_attr($product["name"]) . '" class="woorpd-product-image">';
        }
        echo '</div>';

        echo '</a>';
        echo '<div class="woorpd-product-title">' . esc_html($product["name"]) . '</div>';

        // Limit description to 150 characters
        $description = (strlen($product["description"]) > 150) ? substr($product["description"], 0, 147) . '...' : $product["description"];
        echo '<div class="woorpd-product-description">' . wp_kses_post($description) . '</div>';
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
