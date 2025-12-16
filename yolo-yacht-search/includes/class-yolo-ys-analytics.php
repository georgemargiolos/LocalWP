<?php
/**
 * YOLO Yacht Search - Analytics & Tracking
 * @since 41.19
 * @updated 41.25 - Removed GA4/FB Pixel base tracking (handled by site-wide plugin)
 *                  Kept custom yacht booking events that integrate with existing tracking
 * @updated 41.27 - Added true server-side Facebook Conversions API implementation
 *                  Following Facebook's official best practices for event matching and deduplication
 */

if (!defined('ABSPATH')) exit;

class YOLO_YS_Analytics {
    
    private static $instance = null;
    private $debug_mode;
    private $fb_pixel_id;
    private $fb_access_token;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->debug_mode = get_option('yolo_enable_debug_mode', '0') === '1';
        // SECURITY: Tokens should only come from database options, not hardcoded defaults
        $this->fb_pixel_id = get_option('yolo_ys_fb_pixel_id', '');
        $this->fb_access_token = get_option('yolo_ys_fb_access_token', '');
        
        // Enqueue client-side analytics script
        add_action('wp_enqueue_scripts', array($this, 'enqueue_analytics_script'));
    }
    
    /**
     * Enqueue client-side analytics JS with custom yacht booking events
     * Note: Assumes GA4 gtag() and Facebook Pixel fbq() are loaded by site-wide analytics plugin
     */
    public function enqueue_analytics_script() {
        if (is_admin()) return;
        
        wp_enqueue_script('yolo-analytics', 
            plugin_dir_url(dirname(__FILE__)) . 'public/js/yolo-analytics.js',
            array('jquery'), YOLO_YS_VERSION, true);
        
        wp_localize_script('yolo-analytics', 'yoloAnalyticsConfig', array(
            'debug_mode' => $this->debug_mode,
            'currency' => 'EUR',
            'fb_pixel_id' => $this->fb_pixel_id,
            'enable_fb_capi' => !empty($this->fb_pixel_id) && !empty($this->fb_access_token)
        ));
    }
    
    /**
     * Send event to Facebook Conversions API (server-side)
     * 
     * Implements Facebook's best practices:
     * - Event deduplication via event_id
     * - User data for event matching (fbp, fbc, IP, user agent)
     * - Hashed PII (email, phone)
     * - Non-blocking requests
     * - Proper error handling
     * 
     * @param string $event_name Facebook event name (e.g., 'ViewContent', 'Purchase')
     * @param array $custom_data Event-specific data (currency, value, content_ids, etc.)
     * @param array $user_data Optional user data (email, phone, first_name, last_name)
     * @return string|false Event ID for deduplication, or false on failure
     */
    public function send_facebook_capi_event($event_name, $custom_data = array(), $user_data = array()) {
        // Check if CAPI is configured
        if (empty($this->fb_pixel_id) || empty($this->fb_access_token)) {
            if ($this->debug_mode) {
                error_log('YOLO Analytics: Facebook CAPI not configured');
            }
            return false;
        }
        
        // Generate unique event ID for deduplication
        $event_id = 'evt_' . time() . '_' . wp_generate_password(12, false, false);
        
        // Build user data following Facebook best practices
        $fb_user_data = $this->build_user_data($user_data);
        
        // Build event payload
        $event_payload = array(
            'event_name' => $event_name,
            'event_time' => time(),
            'event_id' => $event_id,
            'action_source' => 'website',
            'event_source_url' => $this->get_current_url(),
            'user_data' => $fb_user_data,
            'custom_data' => $custom_data
        );
        
        // Send to Facebook Conversions API
        $this->send_to_facebook_api(array($event_payload));
        
        // Store event_id in session for client-side deduplication
        // FIXED: Check headers_sent() to prevent PHP warnings that break JavaScript output
        if (!session_id() && !headers_sent()) {
            @session_start();
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['yolo_last_fb_event_id'] = $event_id;
        }
        
        if ($this->debug_mode) {
            error_log('YOLO Analytics: Sent ' . $event_name . ' to Facebook CAPI (event_id: ' . $event_id . ')');
        }
        
        return $event_id;
    }
    
    /**
     * Build user data for Facebook Conversions API
     * Implements Facebook's best practices for event matching quality
     * 
     * @param array $user_data Optional user-provided data
     * @return array Formatted user data for Facebook
     */
    private function build_user_data($user_data = array()) {
        $fb_user_data = array();
        
        // Client IP address (required for good matching)
        $fb_user_data['client_ip_address'] = $this->get_client_ip();
        
        // User agent (required for good matching)
        $fb_user_data['client_user_agent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        
        // Facebook browser ID (fbp cookie) - critical for attribution
        if (isset($_COOKIE['_fbp'])) {
            $fb_user_data['fbp'] = $_COOKIE['_fbp'];
        }
        
        // Facebook click ID (fbc cookie) - critical for ad attribution
        if (isset($_COOKIE['_fbc'])) {
            $fb_user_data['fbc'] = $_COOKIE['_fbc'];
        }
        
        // Hashed email (if available from booking form)
        if (!empty($user_data['email'])) {
            $fb_user_data['em'] = array($this->hash_pii($user_data['email']));
        }
        
        // Hashed phone (if available from booking form)
        if (!empty($user_data['phone'])) {
            $fb_user_data['ph'] = array($this->hash_pii($user_data['phone']));
        }
        
        // Hashed first name (if available)
        if (!empty($user_data['first_name'])) {
            $fb_user_data['fn'] = array($this->hash_pii($user_data['first_name']));
        }
        
        // Hashed last name (if available)
        if (!empty($user_data['last_name'])) {
            $fb_user_data['ln'] = array($this->hash_pii($user_data['last_name']));
        }
        
        // City (if available)
        if (!empty($user_data['city'])) {
            $fb_user_data['ct'] = array($this->hash_pii($user_data['city']));
        }
        
        // Country (if available)
        if (!empty($user_data['country'])) {
            $fb_user_data['country'] = array($this->hash_pii($user_data['country']));
        }
        
        return $fb_user_data;
    }
    
    /**
     * Hash PII data following Facebook's requirements
     * 
     * @param string $value Value to hash
     * @return string SHA256 hash of normalized value
     */
    private function hash_pii($value) {
        // Normalize: lowercase, trim whitespace
        $normalized = strtolower(trim($value));
        
        // Hash with SHA256
        return hash('sha256', $normalized);
    }
    
    /**
     * Get client IP address
     * Handles proxies and load balancers
     * 
     * @return string Client IP address
     */
    private function get_client_ip() {
        $ip_keys = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle multiple IPs (take first one)
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }
        
        return '';
    }
    
    /**
     * Get current page URL
     * 
     * @return string Current URL
     */
    private function get_current_url() {
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        return $protocol . '://' . $host . $uri;
    }
    
    /**
     * Send events to Facebook Conversions API
     * Uses non-blocking request to avoid slowing down page load
     * 
     * @param array $events Array of event payloads
     * @return void
     */
    private function send_to_facebook_api($events) {
        $url = 'https://graph.facebook.com/v22.0/' . $this->fb_pixel_id . '/events';
        
        $payload = array(
            'data' => $events,
            'access_token' => $this->fb_access_token
        );
        
        // Add test event code if in debug mode
        if ($this->debug_mode && defined('YOLO_FB_TEST_EVENT_CODE')) {
            $payload['test_event_code'] = YOLO_FB_TEST_EVENT_CODE;
        }
        
        // Send non-blocking request (don't wait for response)
        $args = array(
            'method' => 'POST',
            'timeout' => 15,
            'blocking' => false,  // Non-blocking to avoid slowing down page
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'body' => json_encode($payload)
        );
        
        $response = wp_remote_post($url, $args);
        
        // Log errors in debug mode
        if ($this->debug_mode && is_wp_error($response)) {
            error_log('YOLO Analytics: Facebook CAPI error - ' . $response->get_error_message());
        }
    }
    
    /**
     * Track yacht view event
     * Called when user views a yacht details page
     * 
     * @param int $yacht_id Yacht ID
     * @param float $price Yacht price
     * @param string $yacht_name Yacht name
     * @return string|false Event ID
     */
    public function track_yacht_view($yacht_id, $price, $yacht_name = '') {
        $custom_data = array(
            'content_type' => 'product',
            'content_ids' => array((string)$yacht_id),
            'content_name' => $yacht_name,
            'currency' => 'EUR',
            'value' => (float)$price
        );
        
        return $this->send_facebook_capi_event('ViewContent', $custom_data);
    }
    
    /**
     * Track add to cart event
     * Called when user selects a week/price from carousel
     * 
     * @param int $yacht_id Yacht ID
     * @param float $price Selected price
     * @param string $yacht_name Yacht name
     * @return string|false Event ID
     */
    public function track_add_to_cart($yacht_id, $price, $yacht_name = '') {
        $custom_data = array(
            'content_type' => 'product',
            'content_ids' => array((string)$yacht_id),
            'content_name' => $yacht_name,
            'currency' => 'EUR',
            'value' => (float)$price
        );
        
        return $this->send_facebook_capi_event('AddToCart', $custom_data);
    }
    
    /**
     * Track checkout initiation
     * Called when user clicks "Book Now" button
     * 
     * @param int $yacht_id Yacht ID
     * @param float $price Booking price
     * @param string $yacht_name Yacht name
     * @return string|false Event ID
     */
    public function track_begin_checkout($yacht_id, $price, $yacht_name = '') {
        $custom_data = array(
            'content_type' => 'product',
            'content_ids' => array((string)$yacht_id),
            'content_name' => $yacht_name,
            'currency' => 'EUR',
            'value' => (float)$price
        );
        
        return $this->send_facebook_capi_event('InitiateCheckout', $custom_data);
    }
    
    /**
     * Track payment info submission
     * Called when user submits booking form
     * 
     * @param int $yacht_id Yacht ID
     * @param float $price Booking price
     * @param string $yacht_name Yacht name
     * @param array $user_data User information (email, phone, name)
     * @return string|false Event ID
     */
    public function track_add_payment_info($yacht_id, $price, $yacht_name = '', $user_data = array()) {
        $custom_data = array(
            'content_type' => 'product',
            'content_ids' => array((string)$yacht_id),
            'content_name' => $yacht_name,
            'currency' => 'EUR',
            'value' => (float)$price
        );
        
        return $this->send_facebook_capi_event('AddPaymentInfo', $custom_data, $user_data);
    }
    
    /**
     * Track lead generation
     * Called when user submits quote request form
     * 
     * @param float $value Estimated value
     * @param array $user_data User information (email, phone, name)
     * @return string|false Event ID
     */
    public function track_generate_lead($value = 0, $user_data = array()) {
        $custom_data = array(
            'currency' => 'EUR',
            'value' => (float)$value
        );
        
        return $this->send_facebook_capi_event('Lead', $custom_data, $user_data);
    }
    
    /**
     * Track purchase completion
     * Called when booking is confirmed (from Stripe webhook)
     * 
     * @param string $transaction_id Transaction/booking ID
     * @param int $yacht_id Yacht ID
     * @param float $price Final price
     * @param string $yacht_name Yacht name
     * @param array $user_data User information (email, phone, name)
     * @return string|false Event ID
     */
    public function track_purchase($transaction_id, $yacht_id, $price, $yacht_name = '', $user_data = array()) {
        $custom_data = array(
            'content_type' => 'product',
            'content_ids' => array((string)$yacht_id),
            'content_name' => $yacht_name,
            'currency' => 'EUR',
            'value' => (float)$price,
            'order_id' => $transaction_id
        );
        
        return $this->send_facebook_capi_event('Purchase', $custom_data, $user_data);
    }
    
    /**
     * Track search event
     * Called when user submits search form
     * 
     * @param string $search_term Search query
     * @return string|false Event ID
     */
    public function track_search($search_term) {
        $custom_data = array(
            'search_string' => $search_term
        );
        
        return $this->send_facebook_capi_event('Search', $custom_data);
    }
}

function yolo_analytics() {
    return YOLO_YS_Analytics::get_instance();
}
