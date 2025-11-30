<?php
/**
 * The public-facing functionality of the plugin
 */
class YOLO_YS_Public {
    
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Enqueue public styles
     */
    public function enqueue_styles() {
        // Litepicker CSS
        wp_enqueue_style(
            'litepicker',
            YOLO_YS_PLUGIN_URL . 'assets/css/litepicker.css',
            array(),
            $this->version
        );
        
        // Plugin public CSS
        wp_enqueue_style(
            $this->plugin_name,
            YOLO_YS_PLUGIN_URL . 'public/css/yolo-yacht-search-public.css',
            array(),
            $this->version
        );
        
        // Template-specific CSS
        global $post;
        if (is_a($post, 'WP_Post')) {
            // Search results page CSS
            if (has_shortcode($post->post_content, 'yolo_yacht_search')) {
                wp_enqueue_style(
                    'yolo-ys-search-results',
                    YOLO_YS_PLUGIN_URL . 'public/css/search-results.css',
                    array(),
                    $this->version
                );
            }
            
            // Our fleet page CSS
            if (has_shortcode($post->post_content, 'yolo_our_fleet')) {
                wp_enqueue_style(
                    'yolo-ys-our-fleet',
                    YOLO_YS_PLUGIN_URL . 'public/css/our-fleet.css',
                    array(),
                    $this->version
                );
            }
            
            // Guest dashboard page CSS
            if (has_shortcode($post->post_content, 'yolo_guest_dashboard')) {
                wp_enqueue_style(
                    'yolo-ys-guest-dashboard',
                    YOLO_YS_PLUGIN_URL . 'public/css/guest-dashboard.css',
                    array(),
                    $this->version
                );
            }
        }
        
        // Booking confirmation page CSS
        if (isset($_GET['session_id'])) {
            wp_enqueue_style(
                'yolo-ys-booking-confirmation',
                YOLO_YS_PLUGIN_URL . 'public/css/booking-confirmation.css',
                array(),
                $this->version
            );
        }
        
        // Balance payment page CSS
        if (isset($_GET['ref']) || (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'yolo_balance_payment') || has_shortcode($post->post_content, 'yolo_balance_confirmation')))) {
            wp_enqueue_style(
                'yolo-ys-balance-payment',
                YOLO_YS_PLUGIN_URL . 'public/css/balance-payment.css',
                array(),
                $this->version
            );
        }
    }
    
    /**
     * Enqueue public scripts
     */
    public function enqueue_scripts() {
        // Litepicker JS
        wp_enqueue_script(
            'litepicker',
            YOLO_YS_PLUGIN_URL . 'assets/js/litepicker.js',
            array(),
            $this->version,
            true
        );
        
        // Litepicker Mobile Plugin
        wp_enqueue_script(
            'litepicker-mobile',
            YOLO_YS_PLUGIN_URL . 'assets/js/mobilefriendly.js',
            array('litepicker'),
            $this->version,
            true
        );
        
        // Plugin public JS
        wp_enqueue_script(
            $this->plugin_name,
            YOLO_YS_PLUGIN_URL . 'public/js/yolo-yacht-search-public.js',
            array('jquery', 'litepicker', 'litepicker-mobile'),
            $this->version,
            true
        );
        
        // Localize script
        wp_localize_script(
            $this->plugin_name,
            'yoloYSData',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('yolo_ys_nonce'),
                'search_nonce' => wp_create_nonce('yolo_ys_search_nonce'),
                'quote_nonce' => wp_create_nonce('yolo_quote_nonce'),
                'results_page_url' => $this->get_results_page_url(),
            )
        );
    }
    
    /**
     * Get results page URL
     */
    private function get_results_page_url() {
        $results_page_id = get_option('yolo_ys_results_page', '');
        if ($results_page_id) {
            return get_permalink($results_page_id);
        }
        return '';
    }
    
    /**
     * AJAX handler for yacht search - Query from DATABASE first
     */
    public function ajax_search_yachts() {
        check_ajax_referer('yolo_ys_search_nonce', 'nonce');
        
        global $wpdb;
        
        $date_from = sanitize_text_field($_POST['dateFrom']);
        $date_to = sanitize_text_field($_POST['dateTo']);
        $kind = sanitize_text_field($_POST['kind']);
        
        // Get company IDs
        $my_company_id = get_option('yolo_ys_my_company_id', '7850');
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $friend_ids = array_map('trim', explode(',', $friend_companies));
        
        // Extract dates (format: 2026-05-01T00:00:00)
        $search_date_from = substr($date_from, 0, 10); // Get YYYY-MM-DD
        $search_date_to = substr($date_to, 0, 10);
        
        // Query database for available yachts
        $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
        $yachts_table = $wpdb->prefix . 'yolo_yacht_yachts';
        
        // Build SQL query
        $sql = "SELECT DISTINCT 
                    y.id as yacht_id,
                    y.name as yacht,
                    y.model,
                    y.company_id,
                    y.home_base as startBase,
                    y.length,
                    y.cabins,
                    y.berths,
                    p.date_from,
                    p.date_to,
                    p.price,
                    p.start_price,
                    p.currency,
                    p.discount,
                    'Bareboat' as product
                FROM {$yachts_table} y
                INNER JOIN {$prices_table} p ON y.id = p.yacht_id
                WHERE p.date_from >= %s 
                AND p.date_from <= %s";
        
        $params = array($search_date_from, $search_date_to);
        
        // Filter by boat type if specified
        if (!empty($kind)) {
            $sql .= " AND y.model LIKE %s";
            $params[] = '%' . $wpdb->esc_like($kind) . '%';
        }
        
        $sql .= " ORDER BY y.company_id = %d DESC, p.price ASC";
        $params[] = $my_company_id;
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $params));
        
        // Separate YOLO boats from partner boats
        $yolo_boats = array();
        $friend_boats = array();
        
        foreach ($results as $row) {
            $boat = array(
                'yacht_id' => $row->yacht_id,
                'yacht' => $row->yacht . ' ' . $row->model,
                'product' => $row->product,
                'startBase' => $row->startBase,
                'price' => number_format($row->price, 0, '.', '.'),
                'start_price' => $row->start_price,
                'currency' => $row->currency,
                'discount' => $row->discount,
                'length' => $row->length,
                'cabins' => $row->cabins,
                'berths' => $row->berths,
                'date_from' => $row->date_from,
                'date_to' => $row->date_to
            );
            
            if ($row->company_id == $my_company_id) {
                $yolo_boats[] = $boat;
            } else {
                $friend_boats[] = $boat;
            }
        }
        
        // Prepare response
        $response = array(
            'success' => true,
            'yolo_boats' => $yolo_boats,
            'friend_boats' => $friend_boats,
            'total_count' => count($yolo_boats) + count($friend_boats),
        );
        
        wp_send_json($response);
    }
}
