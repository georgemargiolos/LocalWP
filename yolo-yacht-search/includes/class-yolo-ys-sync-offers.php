<?php
/**
 * OPTIMIZED sync_all_offers method using single API call
 * Uses /offers endpoint with flexibility=6 to get all weekly offers for entire year
 */

/**
 * Sync weekly offers (availability + prices) for all yachts and companies
 * Uses /offers endpoint with flexibility=6 to get all Saturday departures for the year
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
    
    // Get all company IDs from database
    global $wpdb;
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    $companies = $wpdb->get_col("SELECT DISTINCT company_id FROM $yachts_table WHERE company_id IS NOT NULL AND company_id != ''");
    
    if (empty($companies)) {
        $results['message'] = 'No companies found in database. Please sync yachts first.';
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
            'companyId' => $companies,  // Array of all company IDs
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'tripDuration' => 7,        // Weekly charters
            'flexibility' => 6,         // In year (all Saturday departures)
            'productName' => 'bareboat' // Focus on bareboat charters
        ));
        
        // Validate response is an array
        if (!is_array($offers)) {
            $results['errors'][] = "Unexpected response format from /offers endpoint";
            error_log('YOLO YS: Unexpected offers response: ' . print_r($offers, true));
            $results['message'] = 'Failed to sync offers: Unexpected API response';
            return $results;
        }
        
        // Store each offer in database
        foreach ($offers as $offer) {
            // Determine company ID from yacht ID or offer data
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
        
        // Delete old offers (older than 60 days in the past)
        YOLO_YS_Database_Prices::delete_old_offers();
        
        $results['success'] = true;
        $results['message'] = sprintf(
            'Successfully synced %d weekly offers for year %d (%d companies)',
            $results['offers_synced'],
            $year,
            count($companies)
        );
        
    } catch (Exception $e) {
        $results['errors'][] = $e->getMessage();
        $results['message'] = 'Failed to sync offers: ' . $e->getMessage();
        error_log('YOLO YS: Failed to sync offers: ' . $e->getMessage());
    }
    
    // Update last offer sync time
    if ($results['success']) {
        update_option('yolo_ys_last_offer_sync', current_time('mysql'));
        update_option('yolo_ys_last_offer_sync_year', $year);
    }
    
    return $results;
}
