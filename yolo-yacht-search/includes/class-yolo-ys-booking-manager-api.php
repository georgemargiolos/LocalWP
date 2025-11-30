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
     * 
     * CRITICAL FIX (v2.3.7): Made consistent with other methods
     * Now returns just the data array (with 'value' extracted), not full result object
     * This matches the behavior of get_offers(), get_yachts_by_company(), etc.
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
        
        $result = $this->make_request($endpoint, $query_params);
        
        if ($result['success']) {
            // API returns direct array, make_request() wraps it in ['success' => true, 'data' => [...]]
            return $result['data'];
        }
        
        throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to search offers');
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
            // API returns direct array, make_request() wraps it in ['success' => true, 'data' => [...]]
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
     * Get all equipment definitions from Booking Manager API
     * 
     * CRITICAL: Booking Manager API wraps responses in a 'value' property!
     * Response format: { "value": [...equipment array...], "Count": N }
     * 
     * DO NOT CHANGE: Must extract 'value' array, not return whole response
     * Bug history: Returning $result['data'] directly broke equipment sync
     * Fixed in: v2.3.6 (Nov 30, 2025)
     * 
     * @return array Array of equipment objects
     * @throws Exception if API call fails
     */
    public function get_equipment_catalog() {
        $endpoint = '/equipment';
        $result = $this->make_request($endpoint);
        
        if ($result['success']) {
            // API returns { "value": [...], "Count": N } - extract the value array
            if (isset($result['data']['value']) && is_array($result['data']['value'])) {
                return $result['data']['value'];
            }
            // Fallback for direct array response
            return $result['data'];
        }
        
        throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to fetch equipment catalog');
    }

    /**
     * Get all yachts for a specific company from Booking Manager API
     * 
     * CRITICAL: Booking Manager API wraps responses in a 'value' property!
     * Response format: { "value": [...yacht array...], "Count": N }
     * 
     * DO NOT CHANGE: Must extract 'value' array, not return whole response
     * Bug history: Returning $result['data'] directly broke yacht sync completely
     * Fixed in: v2.3.6 (Nov 30, 2025)
     * 
     * @param string $company_id The company ID to fetch yachts for
     * @return array Array of yacht objects
     * @throws Exception if API call fails
     */
    public function get_yachts_by_company($company_id) {
        $endpoint = '/yachts';
        $params = array('companyId' => $company_id);
        
        $result = $this->make_request($endpoint, $params);
        
        if ($result['success']) {
            // API returns { "value": [...], "Count": N } - extract the value array
            if (isset($result['data']['value']) && is_array($result['data']['value'])) {
                return $result['data']['value'];
            }
            // Fallback for direct array response
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
                'Authorization' => $this->api_key,  // Booking Manager API expects raw API key, NOT Bearer token
                'Accept' => 'application/json',
            ),
            'timeout' => 180,  // Increased to 180 seconds for large data sets
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
     * Create a reservation in Booking Manager
     * POST /reservation
     */
    public function create_reservation($reservation_data) {
        $endpoint = '/reservation';
        return $this->make_post_request($endpoint, $reservation_data);
    }
    
    /**
     * Record a payment for a reservation
     * POST /reservation/{id}/payments
     */
    public function create_payment($reservation_id, $payment_data) {
        $endpoint = '/reservation/' . $reservation_id . '/payments';
        return $this->make_post_request($endpoint, $payment_data);
    }
    
    /**
     * Make POST API request
     */
    private function make_post_request($endpoint, $data = array()) {
        $url = $this->base_url . $endpoint;
        
        $args = array(
            'headers' => array(
                'Authorization' => $this->api_key,  // Booking Manager API expects raw API key, NOT Bearer token
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($data),
            'method' => 'POST',
            'timeout' => 180,
        );
        
        $response = wp_remote_post($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'error' => $response->get_error_message(),
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        $code = wp_remote_retrieve_response_code($response);
        
        // Accept both 200 and 201 (Created) as success
        if ($code !== 200 && $code !== 201) {
            return array(
                'success' => false,
                'error' => isset($data['message']) ? $data['message'] : 'API request failed',
                'code' => $code,
            );
        }
        
        return array(
            'success' => true,
            'data' => $data,
        );
    }
    
    /**
     * Get live price and availability for specific yacht and dates
     * Used for real-time price checking before booking
     * 
     * CRITICAL DATE FORMAT: Booking Manager API requires yyyy-MM-ddTHH:mm:ss format!
     * DO NOT CHANGE: Date format must include 'T' separator and time component
     * Bug history: Using yyyy-MM-dd caused 422 errors from API
     * Fixed in: v2.3.5 (Nov 30, 2025)
     * 
     * USAGE:
     * - Called when user selects custom dates in yacht details page
     * - Checks real-time availability to prevent double bookings
     * - Returns price including non-payableInBase extras
     * 
     * @param string $yacht_id The yacht ID
     * @param string $date_from Start date (any format parseable by strtotime)
     * @param string $date_to End date (any format parseable by strtotime)
     * @return array Result with success, available, price, discount, etc.
     */
    public function get_live_price($yacht_id, $date_from, $date_to) {
        // CRITICAL: Convert dates to required format: yyyy-MM-ddTHH:mm:ss
        // API returns 422 error if format is wrong (e.g., yyyy-MM-dd without time)
        $date_from_formatted = date('Y-m-d', strtotime($date_from)) . 'T17:00:00';
        $date_to_formatted = date('Y-m-d', strtotime($date_to)) . 'T17:00:00';
        
        $params = array(
            'yachtId' => $yacht_id,
            'dateFrom' => $date_from_formatted,
            'dateTo' => $date_to_formatted,
            'tripDuration' => 7, // Weekly charters
        );
        
        $endpoint = '/offers';
        $result = $this->make_request($endpoint, $params);
        
        // DEBUG: Log API request and response
        error_log('YOLO DEBUG - get_live_price called with yacht_id: ' . $yacht_id);
        error_log('YOLO DEBUG - Formatted dates: ' . $date_from_formatted . ' to ' . $date_to_formatted);
        error_log('YOLO DEBUG - API result success: ' . ($result['success'] ? 'true' : 'false'));
        if (isset($result['data'])) {
            error_log('YOLO DEBUG - API data type: ' . gettype($result['data']));
            error_log('YOLO DEBUG - API data count: ' . (is_array($result['data']) ? count($result['data']) : 'N/A'));
        }
        if (isset($result['error'])) {
            error_log('YOLO DEBUG - API error: ' . $result['error']);
        }
        
        // NOTE: API returns direct array, not wrapped in {"value": [...], "Count": N}
        // The make_request() method wraps it in ['success' => true, 'data' => [...]]
        // So we access $result['data'] directly
        if ($result['success'] && isset($result['data']) && is_array($result['data']) && count($result['data']) > 0) {
            $offer = $result['data'][0];
            
            $base_price = isset($offer['price']) ? $offer['price'] : 0;
            $start_price = isset($offer['startPrice']) ? $offer['startPrice'] : $base_price;
            $discount = isset($offer['discountPercentage']) ? $offer['discountPercentage'] : 0;
            
            // Calculate obligatory extras that must be paid online (payableInBase = false)
            $included_extras = 0;
            $extras_at_base = 0;
            $extras_details = array();
            
            if (isset($offer['obligatoryExtras']) && is_array($offer['obligatoryExtras'])) {
                foreach ($offer['obligatoryExtras'] as $extra) {
                    $extra_price = isset($extra['price']) ? floatval($extra['price']) : 0;
                    $payable_in_base = isset($extra['payableInBase']) ? $extra['payableInBase'] : true;
                    
                    $extras_details[] = array(
                        'name' => isset($extra['name']) ? $extra['name'] : '',
                        'price' => $extra_price,
                        'currency' => isset($extra['currency']) ? $extra['currency'] : 'EUR',
                        'payableInBase' => $payable_in_base,
                    );
                    
                    if (!$payable_in_base) {
                        $included_extras += $extra_price;
                    } else {
                        $extras_at_base += $extra_price;
                    }
                }
            }
            
            // Total price = base price + included extras
            $total_price = $base_price + $included_extras;
            
            return array(
                'success' => true,
                'available' => true,
                'price' => $start_price,
                'discount' => $discount,
                'final_price' => $total_price,
                'base_price' => $base_price,
                'included_extras' => $included_extras,
                'extras_at_base' => $extras_at_base,
                'extras_details' => $extras_details,
                'currency' => isset($offer['currency']) ? $offer['currency'] : 'EUR',
            );
        } else if ($result['success'] && isset($result['data']) && is_array($result['data']) && count($result['data']) === 0) {
            // No offers found = yacht not available
            return array(
                'success' => true,
                'available' => false,
                'error' => 'Yacht not available for selected dates',
            );
        }
        
        return array(
            'success' => false,
            'available' => false,
            'error' => isset($result['error']) ? $result['error'] : 'Failed to fetch live price',
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
        
        // CRITICAL FIX (v2.3.7): search_offers now returns data array directly, not result object
        // It throws exception on failure, so no need to check ['success']
        try {
            $data = $this->search_offers($params);
            set_transient($cache_key, $data, $cache_duration);
            return $data;
        } catch (Exception $e) {
            // Return empty array on error
            return array();
        }
    }
}
