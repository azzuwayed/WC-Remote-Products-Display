<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

function wcrpd_get_products($count = null, $filtered_categories = null)
{
    $apiwoourl = get_option('wcrpd_api_woo_url');
    $apiwoock  = get_option('wcrpd_api_woo_ck');
    $apiwoocs  = get_option('wcrpd_api_woo_cs');

    $debug_enabled = get_option('wcrpd_debug_enable_logging', '');
    if ($debug_enabled == 'yes') {
        $logger = new WCRPDLogger();
        $api = new WCRPDRemoteAPI($logger);
    } else {
        $api = new WCRPDRemoteAPI();
    }

    // Connect to the WooCommerce API
    $api->wcrpd_apiConnect($apiwoourl, $apiwoock, $apiwoocs);

    // Validate and set cache duration 
    $cacheDuration = get_option('wcrpd_debug_cache_duration', 21600);
    if ($cacheDuration !== null && $cacheDuration !== 0) {
        $api->setCacheDuration(intval($cacheDuration));
    } else {
        $api->setCacheDuration(21600);
    }

    // Validate and set timeout 
    $timeout = get_option('wcrpd_debug_timeout', 10);
    if ($timeout !== null && $timeout !== 0) {
        $api->setTimeout(intval($timeout));
    } else {
        $api->setTimeout(10);
    }

    // Validate and set rate limit
    $rateLimit = get_option('wcrpd_debug_rate_limit', 30);
    if ($rateLimit !== null && $rateLimit !== 0) {
        $api->setRateLimit(intval($rateLimit));
    } else {
        $api->setRateLimit(10);
    }

    $products = [];

    // Fetch enable filter flag
    $enable_category_filter = get_option('wcrpd_display_filtered_categories', '');

    // Determine the count limit
    $count = $count ?? get_option('wcrpd_display_count_limit', 3);

    // Fetch products based on the conditions
    if (!empty($enable_category_filter) && !empty($filtered_categories)) {
        $products = $api->fetchProducts($count, $filtered_categories);
    } else {
        // Fetch products without category filtration
        $products = $api->fetchProducts($count);
    }

    // Return products
    return $products;
}


// Shortcode to loop and display fetched products as cards 
function wcrpd_display_products($atts = [])
{

    // Retrieve global visibility settings
    $display_image = get_option('wcrpd_display_image');
    $display_name = get_option('wcrpd_display_name');
    $display_category = get_option('wcrpd_display_category');
    $display_price = get_option('wcrpd_display_price');
    $display_description = get_option('wcrpd_display_description');
    $display_url = get_option('wcrpd_display_button');

    // Retrieve user-defined currency symbol option (add this part)
    // $user_currency_symbol = get_option('wcrpd_currency_symbol', '$'); // Replace with the actual option name

    // Extract the shortcode attributes
    $attributes = shortcode_atts([
        'count_limit' => null,
        'filtered_categories' => ''
    ], $atts);

    // Fetch products using the shortcode attributes to override the global settings
    $response = wcrpd_get_products($attributes['count_limit'], explode(',', $attributes['filtered_categories']));

    // Check for errors
    if (isset($response['error'])) {
        return '<div class="wcrpd-error">' . esc_html($response['error']) . '</div>';
    }

    $products = $response['data'];

    // Start buffering the output
    ob_start();

    echo '<div class="wcrpd-products-wrapper">';

    foreach ($products as $product) {
        echo '<div class="wcrpd-product-card">';

        echo '<a href="' . esc_url($product["permalink"]) . '">';
        if ($display_image) {
            if (isset($product['images'][0]['src']) && !empty($product['images'][0]['src'])) {
                $img_src = $product['images'][0]['src'];
            } else {
                // Use the placeholder image if no image is available
                $img_src = plugin_dir_url(__FILE__) . 'images/wcrpd-placeholder.png';
            }
            echo '<div class="image-container">';
            echo '<img src="' . esc_url($img_src) . '" alt="' . esc_attr($product["name"]) . '" class="wcrpd-product-image">';
            echo '</div>';
        }
        echo '</a>';

        if ($display_name) {
            echo '<div class="wcrpd-product-title">' . esc_html($product["name"]) . '</div>';
        }

        if ($display_category && isset($product["categories"][0]["name"])) {
            $category_label = __('Category:', 'wcrpd');
            echo '<div class="wcrpd-product-category">' . esc_html($category_label) . ' ' . esc_html($product["categories"][0]["name"]) . '</div>';
        }

        // Extract the currency symbol from price_html
        $price_html = $product['price_html'];
        $currency_pattern = '/<span class="woocommerce-Price-currencySymbol">(.+?)<\/span>/';
        preg_match($currency_pattern, $price_html, $currency_matches);
        $currency_symbol = isset($currency_matches[1]) ? html_entity_decode($currency_matches[1]) : '';

        // Check if price exists and show currency symbol accordingly
        if ($display_price && isset($product["price"])) {

            $price_output = $product["price"] ? esc_html($product["price"]) : '';

            // Adjust the position of the currency symbol based on its value
            if ($currency_symbol === 'ر.س') {
                $price_output = $price_output . ' ' . esc_html($currency_symbol);
            } else {
                $price_output = esc_html($currency_symbol) . $price_output;
            }

            // Only echo the div if $price_output is not empty
            if (!empty($price_output)) {
                echo '<div class="wcrpd-product-price">' . $price_output . '</div>';
            }
        }

        if ($display_description) {
            $description = (strlen($product["description"]) > 140) ? substr($product["description"], 0, 137) . '...' : $product["description"];
            echo '<div class="wcrpd-product-description">' . wp_kses_post($description) . '</div>';
        }

        // Add "Shop Now" button with an attractive style
        if ($display_url) {
            $shop_now_label = __('Shop Now', 'wcrpd');
            echo '<a href="' . esc_url($product["permalink"]) . '" class="wcrpd-shop-now-button">' . esc_html($shop_now_label) . '</a>';
        }

        echo '</div>';
    }

    echo '</div>';

    // Return the buffered output
    return ob_get_clean();
}

add_shortcode('wcrpd', 'wcrpd_display_products');
