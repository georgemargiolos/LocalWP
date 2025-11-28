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
     * Sync all yachts from all companies
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
                
                if (is_array($yachts)) {
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
        
        // Sync prices for next 12 months
        if ($results['success']) {
            $this->sync_prices($all_companies);
        }
        
        // Update last sync time
        update_option('yolo_ys_last_sync', current_time('mysql'));
        
        return $results;
    }
    
    /**
     * Get sync status
     */
    public function get_sync_status() {
        $stats = $this->db->get_sync_stats();
        $last_sync = get_option('yolo_ys_last_sync', null);
        
        return array(
            'total_yachts' => $stats['total_yachts'],
            'yolo_yachts' => $stats['yolo_yachts'],
            'partner_yachts' => $stats['total_yachts'] - $stats['yolo_yachts'],
            'last_sync' => $last_sync,
            'last_sync_human' => $last_sync ? human_time_diff(strtotime($last_sync), current_time('timestamp')) . ' ago' : 'Never'
        );
    }
    
    /**
     * Sync prices for all companies
     */
    private function sync_prices($company_ids) {
        // Sync next 3 months to avoid timeout (was 12 months)
        $date_from = date('Y-m-d') . 'T00:00:00';
        $date_to = date('Y-m-d', strtotime('+3 months')) . 'T23:59:59';
        
        foreach ($company_ids as $company_id) {
            if (empty($company_id)) continue;
            
            try {
                $prices = $this->api->get_prices($company_id, $date_from, $date_to);
                
                if (!empty($prices) && is_array($prices)) {
                    foreach ($prices as $price) {
                        YOLO_YS_Database_Prices::store_price($price);
                    }
                }
            } catch (Exception $e) {
                error_log('YOLO YS: Failed to sync prices for company ' . $company_id . ': ' . $e->getMessage());
            }
        }
        
        // Delete old prices
        YOLO_YS_Database_Prices::delete_old_prices();
    }
}
