<?php
/**
 * NEW sync_all_prices method using /offers endpoint
 * This replaces the old method that used /prices endpoint
 */

/**
 * Sync weekly offers (availability + prices) for all yachts
 * Uses /offers endpoint with tripDuration=7 to get Saturday-to-Saturday weekly charters
 * 
 * @param int $year Year to sync (e.g., 2026)
 * @return array Results with success status, message, and statistics
 */
public function sync_all_offers($year = null) {
    // Increase time limit for sync
    set_time_limit(600); // 10 minutes for full year sync
    ini_set('max_execution_time', 600);
    
    $results = array(
        'success' => false,
        'message' => '',
        'yachts_processed' => 0,
        'offers_synced' => 0,
        'errors' => array()
    );
    
    // Default to next year if not specified
    if ($year === null) {
        $year = (int)date('Y') + 1;
    }
    
    // Get all yachts from database
    global $wpdb;
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    $yachts = $wpdb->get_results("SELECT id, company_id FROM $yachts_table WHERE id IS NOT NULL AND id != ''", ARRAY_A);
    
    if (empty($yachts)) {
        $results['message'] = 'No yachts found in database. Please sync yachts first.';
        return $results;
    }
    
    // Calculate date range for the entire year
    // Start from first Saturday of April (or April 1 if it's Saturday)
    $startDate = new DateTime("$year-04-01");
    $dayOfWeek = $startDate->format('w'); // 0 (Sunday) through 6 (Saturday)
    
    // Adjust to next Saturday if not already Saturday
    if ($dayOfWeek != 6) {
        $daysUntilSaturday = (6 - $dayOfWeek + 7) % 7;
        if ($daysUntilSaturday > 0) {
            $startDate->modify("+{$daysUntilSaturday} days");
        }
    }
    
    // End date: 52 weeks later (364 days)
    $endDate = clone $startDate;
    $endDate->modify('+364 days');
    
    $dateFrom = $startDate->format('Y-m-d');
    $dateTo = $endDate->format('Y-m-d');
    
    // Process each yacht
    foreach ($yachts as $yacht) {
        $yacht_id = $yacht['id'];
        $company_id = $yacht['company_id'];
        
        try {
            // Call /offers endpoint with tripDuration=7 for weekly charters
            $offers = $this->api->get_offers(array(
                'yachtId' => $yacht_id,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'tripDuration' => 7,
                'flexibility' => 6  // Allow ±6 days flexibility
            ));
            
            // Validate response is an array
            if (!is_array($offers)) {
                $results['errors'][] = "Yacht $yacht_id: Unexpected response format";
                error_log('YOLO YS: Unexpected offers response for yacht ' . $yacht_id);
                continue;
            }
            
            $yacht_offers = 0;
            
            if (count($offers) > 0) {
                foreach ($offers as $offer) {
                    // Store offer in database
                    YOLO_YS_Database_Prices::store_offer($offer, $company_id);
                    $yacht_offers++;
                }
            }
            
            if ($yacht_offers > 0) {
                $results['yachts_processed']++;
                $results['offers_synced'] += $yacht_offers;
            }
            
        } catch (Exception $e) {
            $results['errors'][] = "Yacht $yacht_id: " . $e->getMessage();
            error_log('YOLO YS: Failed to sync offers for yacht ' . $yacht_id . ': ' . $e->getMessage());
        }
    }
    
    // Delete old offers (older than 60 days)
    YOLO_YS_Database_Prices::delete_old_offers();
    
    if ($results['offers_synced'] > 0) {
        $results['success'] = true;
        $results['message'] = sprintf(
            'Successfully synced %d weekly offers for %d yachts (Year: %d, %s to %s)',
            $results['offers_synced'],
            $results['yachts_processed'],
            $year,
            $dateFrom,
            $dateTo
        );
    } else {
        $results['success'] = true; // Still success even if no offers found
        $results['message'] = sprintf(
            'Offer sync completed for year %d (%s to %s). No offers found.',
            $year,
            $dateFrom,
            $dateTo
        );
    }
    
    // Update last offer sync time
    update_option('yolo_ys_last_offer_sync', current_time('mysql'));
    update_option('yolo_ys_last_offer_sync_year', $year);
    
    return $results;
}
