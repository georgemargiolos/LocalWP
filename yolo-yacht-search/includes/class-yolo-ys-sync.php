<?php
/**
 * YOLO Yacht Search - Sync Handler
 *
 * Handles synchronization of yacht data from Booking Manager API.
 *
 * @package    YOLO_Yacht_Search
 * @subpackage Includes
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class YOLO_YS_Sync
 *
 * Manages yacht data synchronization from Booking Manager API.
 */
class YOLO_YS_Sync {

    /**
     * API instance
     *
     * @var YOLO_YS_Booking_Manager_API
     */
    private $api;

    /**
     * Constructor
     */
    public function __construct() {
        $this->api = new YOLO_YS_Booking_Manager_API();
    }

    /**
     * Sync all yachts from configured companies
     *
     * @return array Results of the sync operation
     */
    public function sync_all_yachts() {
        // Increase time limit for sync
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        
        $results = array(
            'success' => false,
            'message' => '',
            'yachts_synced' => 0,
            'errors' => array()
        );
        
        // Get company IDs from settings
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
        
        // Sync yachts for each company
        foreach ($all_companies as $company_id) {
            if (empty($company_id)) continue;
            
            try {
                $yachts = $this->api->get_yachts_by_company($company_id);
                
                if (!is_array($yachts)) {
                    $results['errors'][] = "Company $company_id: Invalid response format";
                    continue;
                }
                
                foreach ($yachts as $yacht) {
                    YOLO_YS_Database::store_yacht($yacht, $company_id);
                    $results['yachts_synced']++;
                }
                
            } catch (Exception $e) {
                $results['errors'][] = 'Company ' . $company_id . ': ' . $e->getMessage();
                error_log('YOLO YS: Failed to sync yachts - ' . $e->getMessage());
            }
        }
        
        if ($results['yachts_synced'] > 0) {
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d yachts from %d companies',
                $results['yachts_synced'],
                count($all_companies)
            );
        } else {
            $results['message'] = 'No yachts were synced. Check errors.';
        }
        
        // Update last sync time
        if ($results['success']) {
            update_option('yolo_ys_last_yacht_sync', current_time('mysql'));
        }
        
        return $results;
    }

    /**
     * Sync yacht by ID
     *
     * @param int $yacht_id The yacht ID to sync
     * @return array Results of the sync operation
     */
    public function sync_yacht_by_id($yacht_id) {
        $results = array(
            'success' => false,
            'message' => '',
            'yacht' => null
        );
        
        try {
            $yacht = $this->api->get_yacht($yacht_id);
            
            if (!$yacht) {
                $results['message'] = 'Yacht not found in API';
                return $results;
            }
            
            // Get company ID from yacht data
            $company_id = isset($yacht['companyId']) ? $yacht['companyId'] : null;
            
            YOLO_YS_Database::store_yacht($yacht, $company_id);
            
            $results['success'] = true;
            $results['message'] = 'Yacht synced successfully';
            $results['yacht'] = $yacht;
            
        } catch (Exception $e) {
            $results['message'] = 'Failed to sync yacht: ' . $e->getMessage();
            error_log('YOLO YS: Failed to sync yacht - ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Sync equipment catalog
     *
     * @return array Results of the sync operation
     */
    public function sync_equipment() {
        $results = array(
            'success' => false,
            'message' => '',
            'equipment_synced' => 0
        );
        
        try {
            $equipment = $this->api->get_equipment_catalog();
            
            if (!is_array($equipment)) {
                $results['message'] = 'Invalid equipment response format';
                return $results;
            }
            
            foreach ($equipment as $item) {
                YOLO_YS_Database::store_equipment($item);
                $results['equipment_synced']++;
            }
            
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d equipment items',
                $results['equipment_synced']
            );
            
        } catch (Exception $e) {
            $results['message'] = 'Failed to sync equipment: ' . $e->getMessage();
            error_log('YOLO YS: Failed to sync equipment - ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Sync all offers for a given year
     * 
     * v72.9 FIX: Fetch all offers FIRST, only delete old prices if fetch succeeds.
     * This prevents data loss when API fails.
     *
     * @param int|null $year The year to sync offers for (defaults to next year)
     * @return array Results of the sync operation
     */
    public function sync_all_offers($year = null) {
        // Increase time limit for sync (can take 2-3 minutes for all companies)
        set_time_limit(300); // 5 minutes should be enough for single API call
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
        
        global $wpdb;
        
        // Track unique yachts with offers
        $yachtOffersMap = array();
        
        // v72.9 FIX: Collect ALL offers in memory FIRST before deleting anything
        $all_offers = array();
        
        error_log("YOLO YS: ===== FETCHING OFFERS FOR YEAR {$year} (fetch-first pattern) =====");
        
        // Call API once per company to avoid HTTP 500 error
        // The API fails when multiple companies are passed with array syntax companyId[0]=...
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
                    $error_msg = "Company $company_id: Unexpected response format (not an array): " . print_r($offers, true);
                    $results['errors'][] = $error_msg;
                    error_log('YOLO YS: ' . $error_msg);
                    continue;
                }
                
                // Check if response is empty
                if (empty($offers)) {
                    $error_msg = "Company $company_id: No offers returned for year $year";
                    $results['errors'][] = $error_msg;
                    error_log('YOLO YS: ' . $error_msg);
                    continue;
                }
                
                // Collect offers with company ID for later storage
                foreach ($offers as $offer) {
                    $offer['_company_id'] = $company_id; // Tag with company ID
                    $all_offers[] = $offer;
                    
                    // Track yacht
                    if (isset($offer['yachtId'])) {
                        $yachtOffersMap[$offer['yachtId']] = true;
                    }
                }
                
                error_log('YOLO YS: Fetched ' . count($offers) . ' offers for company ' . $company_id);
                
            } catch (Exception $e) {
                $error_msg = 'Company ' . $company_id . ': ' . $e->getMessage();
                $results['errors'][] = $error_msg;
                error_log('YOLO YS: Failed to fetch offers - ' . $error_msg);
            }
        }
        
        // v72.9 FIX: Only delete old prices if we successfully fetched new data
        if (!empty($all_offers)) {
            error_log("YOLO YS: ===== FETCH SUCCESSFUL: " . count($all_offers) . " offers fetched =====");
            error_log("YOLO YS: ===== NOW DELETING OLD PRICES FOR YEAR {$year} =====");
            
            // Delete all existing prices for this year
            $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
            $deleted = $wpdb->query($wpdb->prepare(
                "DELETE FROM {$prices_table} WHERE YEAR(date_from) = %d",
                $year
            ));
            
            error_log("YOLO YS: ===== DELETE COMPLETED: {$deleted} records deleted =====");
            
            if ($deleted === false) {
                error_log("YOLO YS: DELETE FAILED! wpdb->last_error: " . $wpdb->last_error);
            }
            
            // Now store all the fetched offers
            error_log("YOLO YS: ===== STORING " . count($all_offers) . " NEW OFFERS =====");
            
            foreach ($all_offers as $offer) {
                $company_id = isset($offer['_company_id']) ? $offer['_company_id'] : null;
                unset($offer['_company_id']); // Remove our internal tag
                
                YOLO_YS_Database_Prices::store_offer($offer, $company_id);
                $results['offers_synced']++;
            }
            
            error_log("YOLO YS: ===== STORAGE COMPLETED: {$results['offers_synced']} offers stored =====");
            
        } else {
            // v72.9 FIX: If fetch failed or returned empty, DO NOT delete existing prices
            error_log("YOLO YS: ===== FETCH RETURNED EMPTY - KEEPING EXISTING PRICES =====");
            $results['errors'][] = "No offers fetched from API - existing prices preserved to prevent data loss";
        }
        
        // Calculate yachts with offers
        $results['yachts_with_offers'] = count($yachtOffersMap);
        
        if ($results['offers_synced'] > 0) {
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d weekly offers for year %d (%d companies, %d yachts)',
                $results['offers_synced'],
                $year,
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
     * Sync bases from API
     *
     * @return array Results of the sync operation
     */
    public function sync_bases() {
        $results = array(
            'success' => false,
            'message' => '',
            'bases_synced' => 0
        );
        
        try {
            $bases = $this->api->get_bases();
            
            if (!is_array($bases)) {
                $results['message'] = 'Invalid bases response format';
                return $results;
            }
            
            foreach ($bases as $base) {
                YOLO_YS_Database::store_base($base);
                $results['bases_synced']++;
            }
            
            $results['success'] = true;
            $results['message'] = sprintf(
                'Successfully synced %d bases',
                $results['bases_synced']
            );
            
        } catch (Exception $e) {
            $results['message'] = 'Failed to sync bases: ' . $e->getMessage();
            error_log('YOLO YS: Failed to sync bases - ' . $e->getMessage());
        }
        
        return $results;
    }

    /**
     * Get API instance
     *
     * @return YOLO_YS_Booking_Manager_API
     */
    public function get_api() {
        return $this->api;
    }
}
