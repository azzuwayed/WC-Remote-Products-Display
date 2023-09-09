<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

// Shortcode to display products
add_shortcode('woorpd', 'woorpd_display_products');
function woorpd_display_products()
{
    $apiwoourl = get_option('api-woo-url');
    $apiwoock = get_option('api-woo-ck');
    $apiwoocs = get_option('api-woo-cs');

    // Create a logger instance
    $logger = new WOORPD_Logger();

    // Create an API instance with the logger
    $api = new WOORPD_WooCommerceAPI($logger);

    // Create an API instance without the logger
    //$api = new WOORPD_WooCommerceAPI();

    // Connect to the WooCommerce API
    // app.haracat.com
    // ck_4cc51d58e231a46d9bbe45ce8710bf7ac69decdc
    // cs_ae5609313e7cd111f569a9286ef4de74af8e3171
    $api->wooRPD_apiConnect($apiwoourl, $apiwoock, $apiwoocs);

    // Set cache duration, timeout, and rate limit (optional)
    $api->setCacheDuration(3600); // Cache for 1 hour
    $api->setTimeout(20); // Set timeout to 20 seconds
    $api->setRateLimit(10); // Allow 10 requests per minute

    // Fetch products
    $products = $api->fetchProducts(10); // Fetch 10 products

    // If there's an error, it will be in the 'error' key
    if (isset($products['error'])) {
        echo $products['error'];
    } else {
        // Process the fetched products
        foreach ($products as $product) {
            echo $product['name'] . "<br>";
        }
    }
}
