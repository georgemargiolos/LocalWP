<?php
/**
 * Yacht sync functionality
 */
class YOLO_YS_Sync {
    
    private $api;
    private $db;
    
    public function __construct() {
        $this->api = new YOLO_YS_Booking_Manager_API();
        $this->db = new YOLO_YS_Database();
    }
    
    /**
     * Sync all yachts from all companies (WITHOUT prices)
     */
    public function sync_all_yachts() {
        // Increase time limit for sync
        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', 300);
        
        $results = array(
            'success' => false,
            'message' => '',
            'companies_synced' => 0,
            'yachts_synced' => 0,
            'errors' => array()
        );
        
        // Get company IDs
        $my_company_id = get_option('yolo_ys_my_company_id', 7850);
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $friend_ids = array_map('trim', explode(',', $friend_companies));
        
        // Combine all companies
        $all_companies = array_merge(array($my_company_id), $friend_ids);
        
        foreach ($all_companies as $company_id) {
            if (empty($company_id)) continue;
            
            try {
                // Fetch yachts for this company
                $yachts = $this->api->get_yachts_by_company($company_id);
                
                // Validate response is an array
                if (!is_array($yachts)) {
                    $results['errors'][] = "Company $company_id: Unexpected response format (not an array)";
                    error_log('YOLO YS: Unexpected yacht response for company ' . $company_id);
                    continue;
                }
                
                if (count($yachts) > 0) {
                    foreach ($yachts as $yacht) {
                        $this->db->store_yacht($yacht, $company_id);
                        $results['yachts_synced']++;
                    }
                    $results['companies_synced']++;
                } else {
                    $results['errors'][] = "Company $company_id: No yachts returned";
                }
                
            } catch (Exception $e) {
                $results['errors'][] = "Company $company_id: " . $e->getMessage();
                error_log('YOLO YS: Failed to sync yachts for company ' . $company_id . ': ' . $e->getMessage());
            }
        }
        
        if ($results['yachts_synced'] > 0) {
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d yachts from %d companies',
                $results['yachts_synced'],
                $results['companies_synced']
            );
        } else {
            $results['message'] = 'No yachts were synced. Check errors.';
        }
        
        // Update last sync time
        update_option('yolo_ys_last_sync', current_time('mysql'));
        
        return $results;
    }
    
    /**
     * Sync weekly offers (availability + prices) for all companies
     * Uses /offers endpoint with flexibility=6 to get all Saturday departures for entire year
     * This replaces the old sync_all_prices() method
     * 
     * @param int $year Year to sync (e.g., 2026)
     * @return array Results with success status, message, and statistics
     */
    public function sync_all_offers($year = null) {
        // Increase time limit for sync
        set_time_limit(300); // 5 minutes should be enough for single API call
        ini_set('max_execution_time', 300);
        
        $results = array(
            'success' => false,
            'message' => '',
            'offers_synced' => 0,
            'errors' => array()
        );
        
        // Default to next year if not specified
        if ($year === null) {
            $year = (int)date('Y') + 1;
        }
        
        // Get company IDs
        $my_company_id = get_option('yolo_ys_my_company_id', 7850);
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $friend_ids = array_map('trim', explode(',', $friend_companies));
        
        // Combine all companies
        $all_companies = array_merge(array($my_company_id), $friend_ids);
        $all_companies = array_filter($all_companies); // Remove empty values
        
        if (empty($all_companies)) {
            $results['message'] = 'No companies configured. Please check plugin settings.';
            return $results;
        }
        
        // Date range: entire year (Jan 1 - Dec 31)
        $dateFrom = "{$year}-01-01T00:00:00";
        $dateTo = "{$year}-12-31T23:59:59";
        
        try {
            // Call /offers endpoint with ALL companies at once
            // flexibility=6 means "in year" - returns all available Saturday departures
            // tripDuration=7 means weekly charters (Saturday to Saturday)
            $offers = $this->api->get_offers(array(
                'companyId' => $all_companies,  // Array of all company IDs
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'tripDuration' => 7,            // Weekly charters
                'flexibility' => 6,             // In year (all Saturday departures)
                'productName' => 'bareboat'     // Focus on bareboat charters
            ));
            
            // Validate response is an array
            if (!is_array($offers)) {
                $results['errors'][] = "Unexpected response format from /offers endpoint";
                error_log('YOLO YS: Unexpected offers response: ' . print_r($offers, true));
                $results['message'] = 'Failed to sync offers: Unexpected API response';
                return $results;
            }
            
            // Get yacht-to-company mapping from database
            global $wpdb;
            $yachts_table = $wpdb->prefix . 'yolo_yachts';
            
            // Store each offer in database
            foreach ($offers as $offer) {
                // Determine company ID from yacht ID
                $company_id = null;
                if (isset($offer['yachtId'])) {
                    $yacht = $wpdb->get_row($wpdb->prepare(
                        "SELECT company_id FROM $yachts_table WHERE id = %s",
                        $offer['yachtId']
                    ));
                    if ($yacht) {
                        $company_id = $yacht->company_id;
                    }
                }
                
                // Store offer
                YOLO_YS_Database_Prices::store_offer($offer, $company_id);
                $results['offers_synced']++;
            }
            
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d weekly offers for year %d (%d companies)',
                $results['offers_synced'],
                $year,
                count($all_companies)
            );
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $results['message'] = 'Failed to sync offers: ' . $e->getMessage();
            error_log('YOLO YS: Failed to sync offers: ' . $e->getMessage());
        }
        
        // Delete old offers (older than 60 days in the past)
        YOLO_YS_Database_Prices::delete_old_offers();
        
        // Update last offer sync time
        if ($results['success']) {
            update_option('yolo_ys_last_offer_sync', current_time('mysql'));
            update_option('yolo_ys_last_offer_sync_year', $year);
        }
        
        return $results;
    }
    
    /**
     * Get sync status
     */
    public function get_sync_status() {
        $stats = $this->db->get_sync_stats();
        $last_sync = get_option('yolo_ys_last_sync', null);
        $last_price_sync = get_option('yolo_ys_last_price_sync', null);
        
        return array(
            'total_yachts' => $stats['total_yachts'],
            'yolo_yachts' => $stats['yolo_yachts'],
            'partner_yachts' => $stats['total_yachts'] - $stats['yolo_yachts'],
            'last_sync' => $last_sync,
            'last_sync_human' => $last_sync ? human_time_diff(strtotime($last_sync), current_time('timestamp')) . ' ago' : 'Never',
            'last_price_sync' => $last_price_sync,
            'last_price_sync_human' => $last_price_sync ? human_time_diff(strtotime($last_price_sync), current_time('timestamp')) . ' ago' : 'Never'
        );
    }
}
