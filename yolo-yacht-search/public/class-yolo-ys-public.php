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
                'nonce' => wp_create_nonce('yolo_ys_search_nonce'),
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
     * AJAX handler for yacht search
     */
    public function ajax_search_yachts() {
        check_ajax_referer('yolo_ys_search_nonce', 'nonce');
        
        $date_from = sanitize_text_field($_POST['dateFrom']);
        $date_to = sanitize_text_field($_POST['dateTo']);
        $kind = sanitize_text_field($_POST['kind']);
        
        // Get company IDs
        $my_company_id = get_option('yolo_ys_my_company_id', '7850');
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $friend_ids = array_map('trim', explode(',', $friend_companies));
        
        $api = new YOLO_YS_Booking_Manager_API();
        
        // Search YOLO boats first
        $yolo_results = $api->get_offers_cached(array(
            'dateFrom' => $date_from,
            'dateTo' => $date_to,
            'kind' => $kind,
            'companyId' => $my_company_id,
        ));
        
        // Search friend companies boats
        $friend_results = array();
        foreach ($friend_ids as $friend_id) {
            if (empty($friend_id)) continue;
            
            $result = $api->get_offers_cached(array(
                'dateFrom' => $date_from,
                'dateTo' => $date_to,
                'kind' => $kind,
                'companyId' => $friend_id,
            ));
            
            if ($result['success'] && !empty($result['data'])) {
                $friend_results = array_merge($friend_results, $result['data']);
            }
        }
        
        // Prepare response
        $response = array(
            'success' => true,
            'yolo_boats' => $yolo_results['success'] ? $yolo_results['data'] : array(),
            'friend_boats' => $friend_results,
            'total_count' => count($yolo_results['success'] ? $yolo_results['data'] : array()) + count($friend_results),
        );
        
        wp_send_json($response);
    }
}
