<?php
/**
 * Booking Manager API Integration
 */
class YOLO_YS_Booking_Manager_API {
    
    private $api_key;
    private $base_url = 'https://www.booking-manager.com/api/v2';
    
    public function __construct() {
        $this->api_key = get_option('yolo_ys_api_key', '');
    }
    
    /**
     * Search for available yachts
     */
    public function search_offers($params) {
        $endpoint = '/offers';
        
        // Build query parameters
        $query_params = array(
            'dateFrom' => $params['dateFrom'],
            'dateTo' => $params['dateTo'],
        );
        
        // Add optional parameters
        if (!empty($params['kind'])) {
            $query_params['kind'] = $params['kind'];
        }
        
        if (!empty($params['companyId'])) {
            $query_params['companyId'] = $params['companyId'];
        }
        
        return $this->make_request($endpoint, $query_params);
    }
    
    /**
     * Get yacht details
     */
    public function get_yacht($yacht_id) {
        $endpoint = '/yacht/' . $yacht_id;
        return $this->make_request($endpoint);
    }
    
    /**
     * Get company details
     */
    public function get_company($company_id) {
        $endpoint = '/company/' . $company_id;
        return $this->make_request($endpoint);
    }
    
    /**
     * Get weekly offers (availability + prices) for specific parameters
     * This is the preferred method for getting weekly charter prices
     */
    public function get_offers($params) {
        $endpoint = '/offers';
        
        // Build query parameters
        $query_params = array();
        
        // Required parameters
        if (isset($params['dateFrom'])) {
            $query_params['dateFrom'] = $params['dateFrom'];
        }
        if (isset($params['dateTo'])) {
            $query_params['dateTo'] = $params['dateTo'];
        }
        
        // Optional parameters
        if (isset($params['companyId'])) {
            $query_params['companyId'] = $params['companyId'];
        }
        if (isset($params['yachtId'])) {
            $query_params['yachtId'] = $params['yachtId'];
        }
        if (isset($params['tripDuration'])) {
            $query_params['tripDuration'] = $params['tripDuration'];
        }
        if (isset($params['flexibility'])) {
            $query_params['flexibility'] = $params['flexibility'];
        }
        if (isset($params['product'])) {
            $query_params['product'] = $params['product'];
        }
        
        $result = $this->make_request($endpoint, $query_params);
        
        if ($result['success']) {
            return $result['data'];
        }
        
        throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to fetch offers');
    }
    
    /**
     * Get prices for company
     * @deprecated Use get_offers() instead for weekly charter prices
     */
    public function get_prices($company_id, $date_from, $date_to) {
        $endpoint = '/prices';
        $params = array(
            'companyId' => $company_id,
            'dateFrom' => $date_from,
            'dateTo' => $date_to
        );
        
        $result = $this->make_request($endpoint, $params);
        
        if ($result['success']) {
            return $result['data'];
        }
        
        throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to fetch prices');
    }
    
    /**
     * Get all yachts for a company
     */
    /**
     * Get all equipment definitions
     */
    public function get_equipment_catalog() {
        $endpoint = '/equipment';
        $result = $this->make_request($endpoint);
        
        if ($result['success']) {
            return $result['data'];
        }
        
        throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to fetch equipment catalog');
    }

    public function get_yachts_by_company($company_id) {
        $endpoint = '/yachts';
        $params = array('companyId' => $company_id);
        
        $result = $this->make_request($endpoint, $params);
        
        if ($result['success']) {
            return $result['data'];
        }
        
        throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to fetch yachts');
    }
    
    /**
     * Make API request
     */
    private function make_request($endpoint, $params = array()) {
        $url = $this->base_url . $endpoint;
        
        if (!empty($params)) {
            // Custom query encoding to handle arrays properly
            // Booking Manager API expects repeated parameters (companyId=1&companyId=2)
            // not bracketed arrays (companyId[0]=1&companyId[1]=2)
            $query_parts = array();
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    // For arrays, add repeated parameters
                    foreach ($value as $item) {
                        $query_parts[] = urlencode($key) . '=' . urlencode($item);
                    }
                } else {
                    // For scalars, add single parameter
                    $query_parts[] = urlencode($key) . '=' . urlencode($value);
                }
            }
            $url .= '?' . implode('&', $query_parts);
        }
        
        $args = array(
            'headers' => array(
                'Authorization' => $this->api_key,
                'Accept' => 'application/json',
            ),
            'timeout' => 60,  // Increased from 30 to 60 seconds for price sync
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message(),
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return array(
                'success' => false,
                'error' => isset($data['message']) ? $data['message'] : 'API request failed',
            );
        }
        
        return array(
            'success' => true,
            'data' => $data,
        );
    }
    
    /**
     * Get cached or fresh offers
     */
    public function get_offers_cached($params) {
        $cache_key = 'yolo_ys_offers_' . md5(serialize($params));
        $cache_duration = (int) get_option('yolo_ys_cache_duration', 24) * HOUR_IN_SECONDS;
        
        $cached_data = get_transient($cache_key);
        
        if ($cached_data !== false) {
            return $cached_data;
        }
        
        $result = $this->search_offers($params);
        
        if ($result['success']) {
            set_transient($cache_key, $result, $cache_duration);
        }
        
        return $result;
    }
}
