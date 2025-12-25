<?php
if (!defined('ABSPATH')) {
    exit;
}

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
        // ALWAYS load Bootstrap 5 CSS on frontend (bundled locally for reliability)
        // Our plugin CSS depends on Bootstrap, so it must always be available
        // Local hosting = no CDN dependency, works offline, guaranteed availability
        wp_enqueue_style(
            'bootstrap',
            YOLO_YS_PLUGIN_URL . 'vendor/bootstrap/css/bootstrap.min.css',
            array(),
            '5.3.2'
        );
        
        // Litepicker CSS
        wp_enqueue_style(
            'litepicker',
            YOLO_YS_PLUGIN_URL . 'assets/css/litepicker.css',
            array(),
            $this->version
        );
        
        // Swiper CSS (bundled locally for carousels)
        wp_enqueue_style(
            'swiper',
            YOLO_YS_PLUGIN_URL . 'vendor/swiper/swiper-bundle.min.css',
            array(),
            '11.0.0'
        );
        
        // Toastify CSS (bundled locally for notifications)
        wp_enqueue_style(
            'toastify',
            YOLO_YS_PLUGIN_URL . 'vendor/toastify/toastify.min.css',
            array(),
            '1.12.0'
        );
        
        // Plugin public CSS
        wp_enqueue_style(
            $this->plugin_name,
            YOLO_YS_PLUGIN_URL . 'public/css/yolo-yacht-search-public.css',
            array(),
            $this->version
        );
        
        // Yacht card CSS (unified styles for all yacht cards)
        wp_enqueue_style(
            'yolo-ys-yacht-card',
            YOLO_YS_PLUGIN_URL . 'public/css/yacht-card.css',
            array('bootstrap'),
            $this->version
        );
        
        // Booking modal CSS (replaces inline styles)
        wp_enqueue_style(
            'yolo-ys-booking-modal',
            YOLO_YS_PLUGIN_URL . 'public/css/booking-modal.css',
            array('bootstrap'),
            $this->version
        );
        
        // Bootstrap 5 mobile best practices for ALL frontend templates
        wp_enqueue_style(
            'yolo-ys-bootstrap-mobile',
            YOLO_YS_PLUGIN_URL . 'public/css/bootstrap-mobile-fixes.css',
            array('bootstrap'),
            $this->version
        );
        
        // REMOVED: emergency-override.css and global-mobile-responsive.css
        // Bootstrap 5 Grid handles responsive layout correctly without overrides!
        // Those files were breaking position:sticky with overflow:hidden
        
        // Template-specific CSS
        global $post;
        if (is_a($post, 'WP_Post')) {
            // Search results page CSS
            if (has_shortcode($post->post_content, 'yolo_search_results')) {
                wp_enqueue_style(
                    'yolo-ys-search-results',
                    YOLO_YS_PLUGIN_URL . 'public/css/search-results.css',
                    array('yolo-ys-bootstrap-mobile'),  // Load AFTER bootstrap-mobile-fixes to ensure max-width:none !important wins
                    $this->version
                );
                
                // Search widget icon fixes
                wp_enqueue_style(
                    'yolo-ys-search-widget-icons',
                    YOLO_YS_PLUGIN_URL . 'public/blocks/yacht-search/search-widget-icon-fixes.css',
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
            
            // Guest login page CSS
            if (has_shortcode($post->post_content, 'yolo_guest_login')) {
                wp_enqueue_style(
                    'yolo-ys-guest-login',
                    YOLO_YS_PLUGIN_URL . 'public/css/guest-login.css',
                    array(),
                    $this->version
                );
            }
            
            // Yacht details page CSS (v30.5 FIX)
            // Load when shortcode is present OR when yacht_id/yacht_slug URL parameter is present
            if (has_shortcode($post->post_content, 'yolo_yacht_details') || isset($_GET['yacht_id']) || get_query_var('yacht_slug', '')) {
                wp_enqueue_style(
                    'yolo-ys-yacht-details-v3',
                    YOLO_YS_PLUGIN_URL . 'public/css/yacht-details-v3.css',
                    array('bootstrap'),
                    $this->version
                );
            }
        }
        
        // Yacht details page responsive CSS
        // NOTE: Main yacht details styles come from yacht-details-v3-styles.php (PHP include)
        // which supports dynamic color customization from admin settings
        // DISABLED: yacht-details-responsive-fixes.css conflicts with Bootstrap Grid!
        // It adds display:flex and gap properties to elements that already have Bootstrap .row class,
        // causing double spacing and layout breaks. All necessary styles are in yacht-details-v3-styles.php
        // if (isset($_GET['yacht_id'])) {
        //     wp_enqueue_style(
        //         'yolo-ys-yacht-details-responsive',
        //         YOLO_YS_PLUGIN_URL . 'public/css/yacht-details-responsive-fixes.css',
        //         array('bootstrap-grid'),
        //         $this->version
        //     );
        // }
        
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
        
        // Contact Form 7 Custom Styling
        if (class_exists('WPCF7')) {
            wp_enqueue_style(
                'yolo-ys-contact-form',
                YOLO_YS_PLUGIN_URL . 'public/css/contact-form-style.css',
                array(),
                $this->version
            );
        }
    }
    
    /**
     * Enqueue public scripts
     */
    public function enqueue_scripts() {
        // FontAwesome 6 CDN (conditional based on setting)
        if (get_option('yolo_ys_load_fontawesome', '0') === '1') {
            wp_enqueue_style(
                'fontawesome-6',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
                array(),
                '6.5.1'
            );
        }
        
        // ALWAYS load Bootstrap 5 JS on frontend (bundled locally for reliability)
        wp_enqueue_script(
            'bootstrap-5-bundle',
            YOLO_YS_PLUGIN_URL . 'vendor/bootstrap/js/bootstrap.bundle.min.js',
            array(),
            '5.3.2',
            true
        );
        
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
        
        // Swiper JS (bundled locally for carousels)
        wp_enqueue_script(
            'swiper',
            YOLO_YS_PLUGIN_URL . 'vendor/swiper/swiper-bundle.min.js',
            array(),
            '11.0.0',
            true
        );
        
        // Toastify JS (bundled locally for notifications)
        wp_enqueue_script(
            'toastify',
            YOLO_YS_PLUGIN_URL . 'vendor/toastify/toastify.min.js',
            array(),
            '1.12.0',
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
        // Nonce check removed for public search - search is public functionality
        // check_ajax_referer('yolo_ys_search_nonce', 'nonce');
        
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
        $yachts_table = $wpdb->prefix . 'yolo_yachts';  // FIXED: was 'yolo_yacht_yachts'
        
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
                'yacht' => $row->yacht,
                'model' => $row->model,
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
    
    /**
     * AJAX handler for tracking AddToCart event (BOOK NOW button click)
     */
    public function ajax_track_add_to_cart() {
        // Get yacht data from request
        $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
        $yacht_name = isset($_POST['yacht_name']) ? sanitize_text_field($_POST['yacht_name']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        
        // Track AddToCart event via Facebook CAPI
        // Note: track_add_to_cart($yacht_id, $price, $yacht_name) - price is 2nd param
        $event_id = false;
        if (function_exists('yolo_analytics')) {
            $event_id = yolo_analytics()->track_add_to_cart($yacht_id, $price, $yacht_name);
        }
        
        wp_send_json_success(array(
            'event_id' => $event_id
        ));
    }
    
    /**
     * AJAX handler for tracking InitiateCheckout event (booking form submission)
     */
    public function ajax_track_initiate_checkout() {
        // Get booking data from request
        $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
        $yacht_name = isset($_POST['yacht_name']) ? sanitize_text_field($_POST['yacht_name']) : '';
        $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
        
        // Track InitiateCheckout event via Facebook CAPI
        // Note: track_begin_checkout($yacht_id, $price, $yacht_name) - correct method name
        $event_id = false;
        if (function_exists('yolo_analytics')) {
            $event_id = yolo_analytics()->track_begin_checkout($yacht_id, $price, $yacht_name);
        }
        
        wp_send_json_success(array(
            'event_id' => $event_id
        ));
    }
}

