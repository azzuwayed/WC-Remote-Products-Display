<?php

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

/**
 * Class WooRPDLogger
 *
 * Logs messages for the WooRPD plugin.
 */
class WooRPDLogger
{
    /**
     * Centralized logging and error handling method.
     *
     * @param string $message The message to log.
     * @param string $type The type of log entry (e.g., 'ERROR', 'INFO'). Default is 'INFO'.
     * @param array $data Additional data to log.
     * @return array|null The error response if type is 'ERROR', null otherwise.
     */
    public static function log(string $message, string $type = 'INFO', array $data = []): ?array
    {
        if (WP_DEBUG && WP_DEBUG_LOG) {
            $message = ($type === 'INFO' && !empty($data)) ? "$message " . json_encode($data) : $message;
            error_log("WooRPD [$type]: $message");
        }
        return $type === 'ERROR' ? ['error' => $message] : null;
    }
}

/**
 * Class WooRPDRemoteAPI
 *
 * Manages WooCommerce API connections and requests for the WooRPD plugin.
 */
class WooRPDRemoteAPI
{
    private const API_ENDPOINT_PRODUCTS = "wp-json/wc/v3/products";
    private const WOORPD_API_RATE_LIMIT = "woorpd_api_rate_limit";

    private $website_url;
    private $consumer_key;
    private $consumer_secret;
    private $logger;

    private $cache_duration = 21600; // 6 hours (21600)
    private $timeout = 20; // 20 seconds
    private $rate_limit = 10; // 10 requests per minute

    /**
     * WooRPDRemoteAPI constructor.
     *
     * @param WooRPDLogger|null $logger The logger instance (optional).
     */
    public function __construct(WooRPDLogger $logger = null)
    {
        $this->logger = $logger;
        $this->initializeRateLimit();
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

        // Ensure the URL uses HTTPS
        if (strpos($this->website_url, 'https://') === 0) {
            // The URL already uses HTTPS, no action needed
        } elseif (
            strpos($this->website_url, 'http://') === 0
        ) {
            // Replace 'http://' with 'https://'
            $this->website_url = str_replace('http://', 'https://', $this->website_url);
        } else {
            // Prepend 'https://' to the URL
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
     * Reset the rate limit count after a minute.
     */
    private function initializeRateLimit(): void
    {
        if (!get_transient(self::WOORPD_API_RATE_LIMIT)) {
            set_transient(self::WOORPD_API_RATE_LIMIT, 0, $this->cache_duration);
        }
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
        $this->logger->log("Cached is flushed", 'INFO');
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
        $cache_key = "woorpd_api_" . md5($constructed_url);

        // Check if the response is cached
        if ($cached_response = get_transient($cache_key)) {
            $this->logger?->log("Transient $cache_key RETRIEVED successfully.", 'INFO');
            return $cached_response;
        }

        // If the transient doesn't exist, log the new connection
        $this->logger?->log("Connecting to $this->website_url", 'INFO');

        // Rate limiting
        $current_requests = get_transient(self::WOORPD_API_RATE_LIMIT) ?: 0;
        if ($current_requests >= $this->rate_limit) {
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
            return $this->handleError("Empty or invalid response from the remote API.");
        }

        // Handle potential API errors
        if (isset($decoded_response["code"])) {
            $error_message = $decoded_response["code"] === "woocommerce_rest_cannot_view"
                ? "Failed to authorize with the WooCommerce API. Check your consumer key and secret."
                : "Consumer key is found but " . esc_html($decoded_response["message"]);
            return $this->handleError($error_message);
        }

        // Cache the response and update the rate limiter count, fluch old cache if it exists
        if ($this->isCachingEnabled()) {
            $this->logger?->log("Transient $cache_key SET successfully.", 'INFO');
            set_transient($cache_key, $decoded_response, $this->cache_duration);
        }
        set_transient(self::WOORPD_API_RATE_LIMIT, $current_requests + 1, $this->rate_limit);

        return $decoded_response;
    }

    public function fetchProducts(int $count_limit = 3, array $filtered_categories = []): array
    {
        $args = [
            'per_page' => 100, // Max allowed by WooCommerce by default (100)
            'orderby' => 'date',
            'order' => 'desc',
            'status' => 'publish',
            'consumer_key' => $this->consumer_key,
            'consumer_secret' => $this->consumer_secret
        ];

        if (!empty($filtered_categories)) {
            $args['category'] = implode(',', array_map('intval', $filtered_categories));
        }

        $all_products = [];
        $page = 1;
        $max_pages = 10; // Maximum limit to prevent infinite loops

        do {
            $args['page'] = $page;
            $products = $this->makeRequest(self::API_ENDPOINT_PRODUCTS, $args);

            if (isset($products['error'])) {
                return $products;
            }

            // Log the INFO messages only if there is no error and if the logger is enabled
            $logMessage = "[Shortcode] found pages: $page, products limit: $count_limit";
            if (!empty($filtered_categories)) {
                $logMessage .= ", filtered category IDs: " . implode(',', $filtered_categories);
            }
            $this->logger?->log($logMessage, 'INFO');

            // Filter products based on catalog_visibility 
            $filtered_visibility = array_filter($products, fn ($product) => !isset($product['catalog_visibility']) || $product['catalog_visibility'] === 'visible');
            $all_products = array_merge($all_products, $filtered_visibility);
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
