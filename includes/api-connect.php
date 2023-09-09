<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

/**
 * Class WOORPD_Logger
 *
 * Logs messages for the WooRPD plugin.
 */
class WOORPD_Logger
{
    /**
     * Centralized logging and error handling method.
     *
     * @param string $message The message to log.
     * @param string $type The type of log entry (e.g., 'ERROR', 'INFO'). Default is 'INFO'.
     * @param array $data Additional data to log.
     * @return array|null The error response if type is 'ERROR', null otherwise.
     */
    public function log($message, $type = 'INFO', $data = array())
    {
        // Check if WP_DEBUG and WP_DEBUG_LOG are enabled
        if (defined('WP_DEBUG') && WP_DEBUG === true && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true) {
            if ($type === 'INFO' && !empty($data)) {
                $message .= ' ' . json_encode($data);
            }
            error_log("WooRPD [$type]: $message");
        }

        if ($type === 'ERROR') {
            return array('error' => $message);
        }
    }
}

/**
 * Class WOORPD_WooCommerceAPI
 *
 * Manages WooCommerce API connections and requests for the WooRPD plugin.
 */
class WOORPD_WooCommerceAPI
{
    const API_ENDPOINT_PRODUCTS = "wp-json/wc/v3/products";
    const API_ENDPOINT_CATEGORIES = "wp-json/wc/v3/products/categories";
    const MAX_PER_PAGE = 100; // Max allowed by WooCommerce by default
    const WOORPD_KEY = "woorpd_key";
    const MINUTE_IN_SECONDS = 60; // Define a constant for 60 seconds

    private $website_url;
    private $consumer_key;
    private $consumer_secret;
    private $logger;

    private $cache_duration = 3600; // 60 minutes
    private $rate_limit = 5; // 5 requests per minute
    private $timeout = 15; // 15 seconds

    /**
     * WOORPD_WooCommerceAPI constructor.
     *
     * @param WOORPD_Logger|null $logger The logger instance (optional).
     */
    public function __construct(WOORPD_Logger $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Connect to the WooCommerce API.
     *
     * @param string $website_url     The WooCommerce website URL.
     * @param string $consumer_key    The API consumer key.
     * @param string $consumer_secret The API consumer secret.
     */
    public function wooRPD_apiConnect($website_url, $consumer_key, $consumer_secret)
    {
        // Trim and sanitize the website URL. Set the website_url first, even if it's invalid.
        $this->website_url = rtrim(sanitize_text_field($website_url), '/');

        // Ensure the URL uses https when protocol is not set
        if (strpos($this->website_url, 'https://') !== 0) {
            $this->website_url = 'https://' . $this->website_url;
        }

        // Validate the website URL
        if (!filter_var($this->website_url, FILTER_VALIDATE_URL)) {
            $this->logger?->log("Invalid website URL provided.", 'ERROR');
            return false; // Stop further execution
        }

        $this->consumer_key = sanitize_text_field($consumer_key);
        $this->consumer_secret = sanitize_text_field($consumer_secret);
    }

    /**
     * Check if caching is enabled.
     *
     * @return bool True if caching is enabled, false otherwise.
     */
    private function isCachingEnabled(): bool
    {
        return $this->cache_duration > 0;
    }

    /**
     * Reset the rate limit count after a minute.
     */
    private function resetRateLimit()
    {
        if (!get_transient(self::WOORPD_KEY)) {
            set_transient(self::WOORPD_KEY, 0, self::MINUTE_IN_SECONDS);
        }
    }

    public function setCacheDuration(int $seconds)
    {
        $this->cache_duration = $seconds;
    }

    public function setTimeout(int $seconds)
    {
        $this->timeout = $seconds;
    }

    public function setRateLimit(int $requests_per_minute)
    {
        $this->rate_limit = $requests_per_minute;
    }

    public function flushCache()
    {
        global $wpdb;
        $like_pattern = $wpdb->esc_like('_transient_woorpd_') . '%';
        $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s", $like_pattern));
    }

    /**
     * Handle errors and logging.
     *
     * @param string $message The error message.
     * @param string $type The type of log (default is 'ERROR').
     * @return array The error response.
     */
    private function handleError(string $message, string $type = 'ERROR'): array
    {
        if ($this->logger) {
            return $this->logger->log($message, $type);
        }
        return array('error' => __("An error occurred while processing your request. Please check your API credentials and ensure your WooCommerce store is accessible.", "woorpd"));
    }

    /**
     * Make a request to the WooCommerce API.
     *
     * @param string $endpoint The API endpoint.
     * @param array  $args     The request arguments.
     *
     * @return array The API response.
     */
    private function makeRequest(string $endpoint, array $args = []): array
    {
        $constructed_url = esc_url_raw($this->website_url . "/" . $endpoint . "?" . http_build_query($args));

        // Generate a cache key based on the URL
        $cache_key = "woorpd_" . md5($constructed_url);

        // Check if the response is cached
        if ($cached_response = get_transient($cache_key)) {
            return $cached_response;
        }

        // Rate limiting
        $current_requests = get_transient(self::WOORPD_KEY) ?: 0;
        if ($current_requests >= $this->rate_limit) {
            $this->resetRateLimit();
            return $this->handleError("Rate limit exceeded. Please wait a moment and try again.", 'ERROR');
        }

        // Make the API request
        $response = wp_remote_get($constructed_url, ["timeout" => $this->timeout]);
        if (is_wp_error($response)) {
            return $this->handleError("API request timed out: " . $response->get_error_message());
        }

        // Decode the response
        $decoded_response = json_decode(wp_remote_retrieve_body($response), true);
        if (!$decoded_response) {
            return $this->handleError("Empty or invalid response from API. Is WooCommerce installed?");
        }

        // Handle potential API errors
        if (isset($decoded_response["code"])) {
            $error_message = $decoded_response["code"] === "woocommerce_rest_cannot_view"
                ? "Failed to authorize with the WooCommerce API. Check your consumer key and secret."
                : "Consumer key is found but " . esc_html($decoded_response["message"]);
            return $this->handleError($error_message);
        }

        // Cache the response and update the rate limiter count
        if ($this->isCachingEnabled()) {
            set_transient($cache_key, $decoded_response, $this->cache_duration);
        }
        set_transient(self::WOORPD_KEY, $current_requests + 1, 60); // 1 minute

        return $decoded_response;
    }

    public function fetchProducts(int $count_limit = 1, array $filtered_categories = []): array
    {
        $args = [
            'per_page' => self::MAX_PER_PAGE,
            'category' => implode(',', array_map('intval', $filtered_categories)),
            'orderby' => 'date',
            'order' => 'desc',
            'status' => 'publish',
            'consumer_key' => $this->consumer_key,
            'consumer_secret' => $this->consumer_secret
        ];

        $all_products = [];
        $page = 1;
        $max_pages = 10; // Maximum limit to prevent infinite loops

        do {
            $args['page'] = $page;
            $products = $this->makeRequest(self::API_ENDPOINT_PRODUCTS, $args);

            if (isset($products['error'])) {
                return $products;
            }

            // Log the INFO messages only if there's no error and if the logger is set
            $this->logger?->log("Successfully connected to the WooCommerce API at $this->website_url", 'INFO');
            $this->logger?->log("Fetching products", 'INFO', ['limit' => $count_limit, 'categories' => $filtered_categories]);

            // Filter products based on catalog_visibility when using Dokan plugin
            $filtered_products = array_filter($products, fn ($product) => !isset($product['catalog_visibility']) || $product['catalog_visibility'] === 'visible');
            $all_products = array_merge($all_products, $filtered_products);
            $page++;

            // Break the loop if we've fetched enough products or if there are no more products to fetch
        } while (count($all_products) < $count_limit && count($products) > 0 && $page <= $max_pages);

        // Return only the number of products specified by $count_limit, or error if no products were found
        if (count($all_products)) {
            return ['data' => array_slice($all_products, 0, $count_limit)];
        } else {
            return ['error' => __("No products found matching the criteria.", "woorpd")];
        }
    }
}
