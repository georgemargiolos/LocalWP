<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Yacht sync functionality
 */
class YOLO_YS_Sync {
    
    private $api;
    private $db;
    
    public function __construct() {
        if (class_exists('YOLO_YS_Booking_Manager_API')) {
            $this->api = new YOLO_YS_Booking_Manager_API();
        } else {
            error_log('YOLO YS: YOLO_YS_Booking_Manager_API class not found');
        }
        
        if (class_exists('YOLO_YS_Database')) {
            $this->db = new YOLO_YS_Database();
        } else {
            error_log('YOLO YS: YOLO_YS_Database class not found');
        }
    }
    
    /**
     * Sync equipment catalog from API
     */
    public function sync_equipment_catalog() {
        $results = array(
            'success' => false,
            'message' => '',
            'equipment_synced' => 0,
            'errors' => array()
        );
        
        try {
            // Fetch equipment catalog from API
            $equipment = $this->api->get_equipment_catalog();
            
            if (!is_array($equipment)) {
                $results['errors'][] = 'Unexpected response format (not an array)';
                $results['message'] = 'Failed to sync equipment catalog';
                return $results;
            }
            
            // Store in database
            $this->db->store_equipment_catalog($equipment);
            $results['equipment_synced'] = count($equipment);
            
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d equipment items',
                $results['equipment_synced']
            );
            
            // Update last sync time
            update_option('yolo_ys_last_equipment_sync', current_time('mysql'));
            
        } catch (Exception $e) {
            $results['errors'][] = $e->getMessage();
            $results['message'] = 'Failed to sync equipment catalog: ' . $e->getMessage();
            error_log('YOLO YS: Equipment catalog sync failed - ' . $e->getMessage());
        }
        
        return $results;
    }
    
    /**
     * Sync all yachts from all companies (WITHOUT prices)
     */
    /**
     * Sync all yachts (with details, images, extras, equipment) from all companies
     * 
     * CRITICAL: API returns data wrapped in 'value' property!
     * The get_yachts_by_company() method must extract the 'value' array
     * 
     * Bug history:
     * - v2.3.5 and earlier: API returned {"value": [...], "Count": N} but code
     *   tried to iterate over the whole object, causing yacht sync to fail
     * - v2.3.6: Fixed get_yachts_by_company() to extract 'value' array
     * 
     * PROCESS:
     * 1. Fetch yachts for each company from API
     * 2. For each yacht, store: basic info, products, images, extras, equipment
     * 3. Old data is deleted before storing new data (prevents duplicates)
     * 
     * IMPORTANT:
     * - Can take 2-3 minutes for all companies (20+ yachts)
     * - Each yacht has 10-20 images, 5-15 extras, 10-20 equipment items
     * - WordPress admin may timeout, but CLI works fine
     * 
     * @return array Result with success status, counts, and errors
     */
    public function sync_all_yachts() {
        // Increase time limit for sync (can take 2-3 minutes)
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
            
            error_log('YOLO YS: Starting sync for company ID: ' . $company_id);
            
            try {
                // Fetch yachts for this company
                error_log('YOLO YS: Fetching yachts from API for company ' . $company_id);
                $yachts = $this->api->get_yachts_by_company($company_id);
                error_log('YOLO YS: Received ' . (is_array($yachts) ? count($yachts) : 'invalid') . ' yachts from API');
                
                // Validate response is an array
                if (!is_array($yachts)) {
                    $results['errors'][] = "Company $company_id: Unexpected response format (not an array)";
                    error_log('YOLO YS: Unexpected yacht response for company ' . $company_id);
                    continue;
                }
                
                if (count($yachts) > 0) {
                    foreach ($yachts as $yacht) {
                        error_log('YOLO YS: Processing yacht: ' . $yacht['name'] . ' (ID: ' . $yacht['id'] . ')');
                        $this->db->store_yacht($yacht, $company_id);
                        $results['yachts_synced']++;
                        error_log('YOLO YS: Yacht stored successfully. Total synced: ' . $results['yachts_synced']);
                    }
                    $results['companies_synced']++;
                    error_log('YOLO YS: Completed sync for company ' . $company_id);
                } else {
                    $results['errors'][] = "Company $company_id: No yachts returned";
                }
                
            } catch (Exception $e) {
                $results['errors'][] = "Company $company_id: " . $e->getMessage();
                error_log('YOLO YS: EXCEPTION - Failed to sync yachts for company ' . $company_id . ': ' . $e->getMessage());
                error_log('YOLO YS: Exception trace: ' . $e->getTraceAsString());
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
    /**
     * Sync all weekly offers from Booking Manager API
     * 
     * v80.3 FIX: Per-company delete - only delete prices for companies that successfully fetched
     * This prevents data loss when one company's API call fails but others succeed.
     * 
     * This method:
     * 1. Fetch offers from API for each company separately (PHASE 1: FETCH ALL)
     * 2. Track which companies succeeded
     * 3. Delete prices ONLY for successful companies' yachts (PHASE 2: DELETE)
     * 4. Store new offers for successful companies (PHASE 2: STORE)
     * 5. Preserve existing prices for failed companies
     * 
     * Bug history:
     * - v2.3.3 and earlier: DELETE was not working, prices accumulated
     * - v2.3.4: Fixed DELETE to properly clear old prices before sync
     * - v72.9: Added fetch-first pattern to prevent data loss on API failure
     * - v80.3: Per-company delete - only delete prices for companies that succeeded
     * 
     * @param int|null $year Year to sync (defaults to next year)
     * @return array Result with success status, counts, and errors
     */
    public function sync_all_offers($year = null) {
        // Increase time limit for sync (can take 2-3 minutes for all companies)
        set_time_limit(300); // 5 minutes should be enough
        ini_set('max_execution_time', 300);
        
        $results = array(
            'success' => false,
            'message' => '',
            'offers_synced' => 0,
            'yachts_with_offers' => 0,
            'year' => $year,
            'errors' => array()
        );
        
        // Default to next year if not specified
        if ($year === null) {
            $year = (int)date('Y') + 1;
        }
        
        // Get company IDs - ensure they are integers for consistent handling
        $my_company_id = (int) get_option('yolo_ys_my_company_id', 7850);
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $friend_ids = array_map('intval', array_map('trim', explode(',', $friend_companies)));
        
        // Combine all companies
        $all_companies = array_merge(array($my_company_id), $friend_ids);
        $all_companies = array_filter($all_companies); // Remove empty/zero values
        
        if (empty($all_companies)) {
            $results['message'] = 'No companies configured. Please check plugin settings.';
            return $results;
        }
        
        // Date range: entire year (Jan 1 - Dec 31)
        $dateFrom = "{$year}-01-01T00:00:00";
        $dateTo = "{$year}-12-31T23:59:59";
        
        global $wpdb;
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
        
        // Track unique yachts with offers
        $yachtOffersMap = array();
        
        // v80.3 FIX: Track offers PER COMPANY for selective delete
        $offers_by_company = array();
        $successful_companies = array();
        
        error_log("YOLO YS: ===== FETCHING OFFERS FOR YEAR {$year} (per-company pattern v80.3) =====");
        error_log("YOLO YS: Companies to sync: " . implode(', ', $all_companies));
        
        // ============================================
        // PHASE 1: FETCH ALL - Collect offers from all companies first
        // ============================================
        foreach ($all_companies as $company_id) {
            if (empty($company_id)) continue;
            
            try {
                error_log('YOLO YS: Fetching offers for company ' . $company_id . ' for year ' . $year);
                
                // Call /offers endpoint for this company
                // flexibility=6 means "in year" - returns all available Saturday departures
                $offers = $this->api->get_offers(array(
                    'companyId' => array($company_id),  // Single company as array
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'flexibility' => 6,                 // In year (all Saturday departures)
                    'productName' => 'bareboat'         // Focus on bareboat charters
                ));
                
                // Validate response is an array
                if (!is_array($offers)) {
                    $error_msg = "Company $company_id: Unexpected response format (not an array)";
                    $results['errors'][] = $error_msg;
                    error_log('YOLO YS: ' . $error_msg);
                    continue;
                }
                
                // Check if response is empty
                if (empty($offers)) {
                    $error_msg = "Company $company_id: No offers returned for year $year - PRESERVING EXISTING PRICES";
                    $results['errors'][] = $error_msg;
                    error_log('YOLO YS: ' . $error_msg);
                    // v80.3: Company failed - do NOT add to successful_companies
                    // This company's existing prices will be preserved
                    continue;
                }
                
                // v80.3: Track this company as successful
                $successful_companies[] = (int) $company_id;
                $offers_by_company[$company_id] = array();
                
                // Collect offers for this company
                foreach ($offers as $offer) {
                    $offer['_company_id'] = $company_id;
                    $offers_by_company[$company_id][] = $offer;
                    
                    // Track yacht
                    if (isset($offer['yachtId'])) {
                        $yachtOffersMap[$offer['yachtId']] = true;
                    }
                }
                
                error_log('YOLO YS: SUCCESS - Fetched ' . count($offers) . ' offers for company ' . $company_id);
                
            } catch (Exception $e) {
                $error_msg = 'Company ' . $company_id . ': ' . $e->getMessage() . ' - PRESERVING EXISTING PRICES';
                $results['errors'][] = $error_msg;
                error_log('YOLO YS: FAILED to fetch offers - ' . $error_msg);
                // v80.3: Company failed - existing prices will be preserved
            }
        }
        
        // ============================================
        // PHASE 2: DELETE + STORE - Only for companies that successfully fetched
        // ============================================
        if (!empty($successful_companies)) {
            error_log("YOLO YS: ===== " . count($successful_companies) . "/" . count($all_companies) . " COMPANIES SUCCEEDED =====");
            error_log("YOLO YS: Successful companies: " . implode(', ', $successful_companies));
            
            // v80.3 FIX: Delete prices ONLY for yachts belonging to successful companies
            // Get yacht IDs for successful companies
            $placeholders = implode(',', array_fill(0, count($successful_companies), '%d'));
            $yacht_ids_query = $wpdb->prepare(
                "SELECT id FROM {$yachts_table} WHERE company_id IN ($placeholders)",
                $successful_companies
            );
            $yacht_ids = $wpdb->get_col($yacht_ids_query);
            
            if (!empty($yacht_ids)) {
                error_log("YOLO YS: Found " . count($yacht_ids) . " yachts for successful companies");
                
                // Delete prices only for these yachts for this year
                $yacht_placeholders = implode(',', array_fill(0, count($yacht_ids), '%d'));
                $delete_params = array_merge($yacht_ids, array($year));
                
                $deleted = $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$prices_table} WHERE yacht_id IN ($yacht_placeholders) AND YEAR(date_from) = %d",
                    $delete_params
                ));
                
                error_log("YOLO YS: ===== DELETED {$deleted} prices for " . count($successful_companies) . " successful companies =====");
                
                if ($deleted === false) {
                    error_log("YOLO YS: DELETE FAILED! wpdb->last_error: " . $wpdb->last_error);
                }
            } else {
                error_log("YOLO YS: No yachts found in database for successful companies - skipping delete");
            }
            
            // Now store all the fetched offers
            error_log("YOLO YS: ===== STORING OFFERS FOR SUCCESSFUL COMPANIES =====");
            
            foreach ($offers_by_company as $company_id => $company_offers) {
                foreach ($company_offers as $offer) {
                    unset($offer['_company_id']); // Remove our internal tag
                    
                    YOLO_YS_Database_Prices::store_offer($offer, $company_id);
                    $results['offers_synced']++;
                }
                error_log("YOLO YS: Stored " . count($company_offers) . " offers for company " . $company_id);
            }
            
            error_log("YOLO YS: ===== STORAGE COMPLETED: {$results['offers_synced']} offers stored =====");
            
            // Log which companies failed (if any)
            $failed_companies = array_diff($all_companies, $successful_companies);
            if (!empty($failed_companies)) {
                error_log("YOLO YS: ===== PRESERVED EXISTING PRICES FOR FAILED COMPANIES: " . implode(', ', $failed_companies) . " =====");
            }
            
        } else {
            // ALL companies failed - preserve all existing prices
            error_log("YOLO YS: ===== ALL COMPANIES FAILED - KEEPING ALL EXISTING PRICES =====");
            $results['errors'][] = "All API calls failed - existing prices preserved to prevent data loss";
        }
        
        // Calculate yachts with offers
        $results['yachts_with_offers'] = count($yachtOffersMap);
        
        if ($results['offers_synced'] > 0) {
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d weekly offers for year %d (%d/%d companies, %d yachts)',
                $results['offers_synced'],
                $year,
                count($successful_companies),
                count($all_companies),
                $results['yachts_with_offers']
            );
        } else {
            $results['message'] = 'No offers were synced. Existing prices preserved. Check errors.';
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
        $last_offer_sync = get_option('yolo_ys_last_offer_sync', null);
        
        return array(
            'total_yachts' => $stats['total_yachts'],
            'yolo_yachts' => $stats['yolo_yachts'],
            'partner_yachts' => $stats['total_yachts'] - $stats['yolo_yachts'],
            'last_sync' => $last_sync,
            'last_sync_human' => $last_sync ? human_time_diff(strtotime($last_sync), current_time('timestamp')) . ' ago' : 'Never',
            'last_price_sync' => $last_offer_sync,
            'last_price_sync_human' => $last_offer_sync ? human_time_diff(strtotime($last_offer_sync), current_time('timestamp')) . ' ago' : 'Never'
        );
    }
}
