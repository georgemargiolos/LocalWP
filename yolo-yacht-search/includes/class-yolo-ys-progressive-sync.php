<?php
/**
 * Progressive Sync - Boat-by-boat sync with live progress
 * 
 * v81.0 - Complete rewrite for timeout-proof syncing
 * 
 * Features:
 * - Syncs one yacht at a time (2-5 seconds per request)
 * - Live progress updates via AJAX
 * - Works on any hosting (no timeout issues)
 * - Supports both manual and auto-sync
 * - Resume capability if interrupted
 * 
 * @package YOLO_Yacht_Search
 * @since 81.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Progressive_Sync {
    
    private $api;
    private $db;
    
    // Sync state option keys
    const STATE_OPTION = 'yolo_ys_progressive_sync_state';
    const YACHT_QUEUE_OPTION = 'yolo_ys_sync_yacht_queue';
    const PRICE_QUEUE_OPTION = 'yolo_ys_sync_price_queue';
    
    public function __construct() {
        if (class_exists('YOLO_YS_Booking_Manager_API')) {
            $this->api = new YOLO_YS_Booking_Manager_API();
        }
        
        if (class_exists('YOLO_YS_Database')) {
            $this->db = new YOLO_YS_Database();
        }
        
        // Register cron hooks for auto-sync
        add_action('yolo_progressive_sync_yacht', array($this, 'cron_sync_next_yacht'));
        add_action('yolo_progressive_sync_price', array($this, 'cron_sync_next_price'));
        add_action('yolo_progressive_sync_start', array($this, 'cron_start_sync'));
    }
    
    /**
     * Get all company IDs configured in settings
     */
    public function get_all_company_ids() {
        $my_company_id = (int) get_option('yolo_ys_my_company_id', 7850);
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $friend_ids = array_filter(array_map('intval', array_map('trim', explode(',', $friend_companies))));
        
        return array_merge(array($my_company_id), $friend_ids);
    }
    
    /**
     * Initialize yacht sync - fetches yacht list and creates queue
     * 
     * @return array Initial state with yacht queue
     */
    public function init_yacht_sync() {
        $companies = $this->get_all_company_ids();
        $yacht_queue = array();
        $company_stats = array();
        
        error_log("YOLO Progressive Sync: Initializing yacht sync for " . count($companies) . " companies");
        
        foreach ($companies as $company_id) {
            if (empty($company_id)) continue;
            
            try {
                // Fetch yacht list for this company (just IDs and names, not full data)
                $yachts = $this->api->get_yachts_by_company($company_id);
                
                if (is_array($yachts) && !empty($yachts)) {
                    $company_stats[$company_id] = array(
                        'total' => count($yachts),
                        'synced' => 0,
                        'status' => 'pending'
                    );
                    
                    foreach ($yachts as $yacht) {
                        $yacht_queue[] = array(
                            'yacht_id' => $yacht['id'],
                            'yacht_name' => $yacht['name'],
                            'company_id' => $company_id,
                            'yacht_data' => $yacht // Store full data to avoid re-fetching
                        );
                    }
                    
                    error_log("YOLO Progressive Sync: Company {$company_id} has " . count($yachts) . " yachts");
                }
            } catch (Exception $e) {
                error_log("YOLO Progressive Sync: Failed to get yachts for company {$company_id}: " . $e->getMessage());
            }
        }
        
        // Create initial state
        $state = array(
            'type' => 'yachts',
            'status' => 'ready',
            'total_yachts' => count($yacht_queue),
            'synced_yachts' => 0,
            'current_index' => 0,
            'companies' => $company_stats,
            'started_at' => null,
            'completed_at' => null,
            'errors' => array(),
            'stats' => array(
                'images' => 0,
                'extras' => 0,
                'equipment' => 0
            )
        );
        
        // Store queue and state
        update_option(self::YACHT_QUEUE_OPTION, $yacht_queue, false);
        update_option(self::STATE_OPTION, $state, false);
        
        return array(
            'success' => true,
            'state' => $state,
            'message' => "Ready to sync {$state['total_yachts']} yachts from " . count($companies) . " companies"
        );
    }
    
    /**
     * Sync a single yacht (called repeatedly via AJAX)
     * 
     * @return array Result with updated state
     */
    public function sync_next_yacht() {
        $state = get_option(self::STATE_OPTION, null);
        $queue = get_option(self::YACHT_QUEUE_OPTION, array());
        
        if (!$state || empty($queue)) {
            return array(
                'success' => false,
                'message' => 'No sync in progress',
                'done' => true
            );
        }
        
        // Mark as running if first yacht
        if ($state['status'] === 'ready') {
            $state['status'] = 'running';
            $state['started_at'] = current_time('mysql');
        }
        
        $current_index = $state['current_index'];
        
        // Check if done
        if ($current_index >= count($queue)) {
            return $this->complete_yacht_sync($state);
        }
        
        $yacht_item = $queue[$current_index];
        $yacht_id = $yacht_item['yacht_id'];
        $yacht_name = $yacht_item['yacht_name'];
        $company_id = $yacht_item['company_id'];
        $yacht_data = $yacht_item['yacht_data'];
        
        $start_time = microtime(true);
        
        try {
            // Store yacht data (already have it from init)
            $this->db->store_yacht($yacht_data, $company_id);
            
            // Count stats from yacht data
            $images_count = isset($yacht_data['images']) ? count($yacht_data['images']) : 0;
            $extras_count = isset($yacht_data['extras']) ? count($yacht_data['extras']) : 0;
            $equipment_count = isset($yacht_data['equipment']) ? count($yacht_data['equipment']) : 0;
            
            // Update state
            $state['synced_yachts']++;
            $state['current_index']++;
            $state['stats']['images'] += $images_count;
            $state['stats']['extras'] += $extras_count;
            $state['stats']['equipment'] += $equipment_count;
            
            // Update company stats
            if (isset($state['companies'][$company_id])) {
                $state['companies'][$company_id]['synced']++;
                if ($state['companies'][$company_id]['synced'] >= $state['companies'][$company_id]['total']) {
                    $state['companies'][$company_id]['status'] = 'complete';
                } else {
                    $state['companies'][$company_id]['status'] = 'syncing';
                }
            }
            
            $elapsed = round((microtime(true) - $start_time) * 1000);
            
            error_log("YOLO Progressive Sync: Synced yacht {$yacht_name} ({$yacht_id}) in {$elapsed}ms");
            
        } catch (Exception $e) {
            $state['errors'][] = array(
                'yacht_id' => $yacht_id,
                'yacht_name' => $yacht_name,
                'error' => $e->getMessage()
            );
            $state['current_index']++; // Skip this yacht
            error_log("YOLO Progressive Sync: ERROR syncing yacht {$yacht_name}: " . $e->getMessage());
        }
        
        // Save state
        update_option(self::STATE_OPTION, $state, false);
        
        // Calculate progress
        $progress = ($state['total_yachts'] > 0) 
            ? round(($state['synced_yachts'] / $state['total_yachts']) * 100, 1) 
            : 0;
        
        // Check if this was the last one
        $done = ($state['current_index'] >= count($queue));
        
        if ($done) {
            return $this->complete_yacht_sync($state);
        }
        
        return array(
            'success' => true,
            'done' => false,
            'yacht_synced' => $yacht_name,
            'company_id' => $company_id,
            'progress' => $progress,
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'elapsed_ms' => isset($elapsed) ? $elapsed : 0
        );
    }
    
    /**
     * Complete yacht sync - cleanup and final stats
     */
    private function complete_yacht_sync($state) {
        $state['status'] = 'complete';
        $state['completed_at'] = current_time('mysql');
        
        // Activate/deactivate yachts based on sync
        $queue = get_option(self::YACHT_QUEUE_OPTION, array());
        $synced_yacht_ids = array_column($queue, 'yacht_id');
        
        if (!empty($synced_yacht_ids)) {
            $this->db->activate_yachts($synced_yacht_ids);
            
            // Deactivate missing yachts per company
            $companies = $this->get_all_company_ids();
            foreach ($companies as $company_id) {
                $company_yacht_ids = array_column(
                    array_filter($queue, function($item) use ($company_id) {
                        return $item['company_id'] == $company_id;
                    }),
                    'yacht_id'
                );
                if (!empty($company_yacht_ids)) {
                    $this->db->deactivate_missing_yachts($company_id, $company_yacht_ids);
                }
            }
        }
        
        // Update last sync time
        update_option('yolo_ys_last_sync', current_time('mysql'));
        
        // Save final state
        update_option(self::STATE_OPTION, $state, false);
        
        // Calculate duration
        $duration = '';
        if ($state['started_at'] && $state['completed_at']) {
            $start = strtotime($state['started_at']);
            $end = strtotime($state['completed_at']);
            $seconds = $end - $start;
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;
            $duration = $minutes > 0 ? "{$minutes}m {$secs}s" : "{$secs}s";
        }
        
        error_log("YOLO Progressive Sync: COMPLETE - {$state['synced_yachts']} yachts in {$duration}");
        
        return array(
            'success' => true,
            'done' => true,
            'message' => "Successfully synced {$state['synced_yachts']} yachts",
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'duration' => $duration,
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'errors' => $state['errors']
        );
    }
    
    /**
     * Initialize price/offers sync - creates queue of yachts to sync prices for
     * 
     * @param int $year Year to sync prices for
     * @return array Initial state
     */
    public function init_price_sync($year = null) {
        if ($year === null) {
            $year = (int) date('Y') + 1;
        }
        
        global $wpdb;
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        
        // Get all active yachts from database
        $yachts = $wpdb->get_results(
            "SELECT id, name, company_id FROM {$yachts_table} WHERE is_active = 1 ORDER BY company_id, name",
            ARRAY_A
        );
        
        if (empty($yachts)) {
            return array(
                'success' => false,
                'message' => 'No yachts found in database. Please sync yachts first.'
            );
        }
        
        // Build queue
        $price_queue = array();
        $company_stats = array();
        
        foreach ($yachts as $yacht) {
            $company_id = $yacht['company_id'];
            
            if (!isset($company_stats[$company_id])) {
                $company_stats[$company_id] = array(
                    'total' => 0,
                    'synced' => 0,
                    'status' => 'pending'
                );
            }
            $company_stats[$company_id]['total']++;
            
            $price_queue[] = array(
                'yacht_id' => $yacht['id'],
                'yacht_name' => $yacht['name'],
                'company_id' => $company_id
            );
        }
        
        // Create initial state
        $state = array(
            'type' => 'prices',
            'status' => 'ready',
            'year' => $year,
            'total_yachts' => count($price_queue),
            'synced_yachts' => 0,
            'current_index' => 0,
            'companies' => $company_stats,
            'started_at' => null,
            'completed_at' => null,
            'errors' => array(),
            'stats' => array(
                'offers' => 0,
                'weeks' => 0
            )
        );
        
        // Store queue and state
        update_option(self::PRICE_QUEUE_OPTION, $price_queue, false);
        update_option(self::STATE_OPTION, $state, false);
        
        error_log("YOLO Progressive Sync: Initialized price sync for {$year} - " . count($price_queue) . " yachts");
        
        return array(
            'success' => true,
            'state' => $state,
            'message' => "Ready to sync prices for {$state['total_yachts']} yachts for year {$year}"
        );
    }
    
    /**
     * Sync prices for a single yacht
     * 
     * @return array Result with updated state
     */
    public function sync_next_price() {
        $state = get_option(self::STATE_OPTION, null);
        $queue = get_option(self::PRICE_QUEUE_OPTION, array());
        
        if (!$state || empty($queue) || $state['type'] !== 'prices') {
            return array(
                'success' => false,
                'message' => 'No price sync in progress',
                'done' => true
            );
        }
        
        // Mark as running if first yacht
        if ($state['status'] === 'ready') {
            $state['status'] = 'running';
            $state['started_at'] = current_time('mysql');
        }
        
        $current_index = $state['current_index'];
        $year = $state['year'];
        
        // Check if done
        if ($current_index >= count($queue)) {
            return $this->complete_price_sync($state);
        }
        
        $yacht_item = $queue[$current_index];
        $yacht_id = $yacht_item['yacht_id'];
        $yacht_name = $yacht_item['yacht_name'];
        $company_id = $yacht_item['company_id'];
        
        $start_time = microtime(true);
        
        try {
            // Fetch offers for this yacht for the entire year
            $dateFrom = "{$year}-01-01T00:00:00";
            $dateTo = "{$year}-12-31T23:59:59";
            
            $offers = $this->api->get_offers(array(
                'yachtId' => $yacht_id,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'flexibility' => 6,
                'productName' => 'bareboat'
            ));
            
            if (is_array($offers) && !empty($offers)) {
                // Delete existing prices for this yacht/year
                global $wpdb;
                $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$prices_table} WHERE yacht_id = %s AND YEAR(date_from) = %d",
                    $yacht_id, $year
                ));
                
                // Store new offers using batch insert
                YOLO_YS_Database_Prices::store_offers_batch($offers, $company_id);
                
                $state['stats']['offers'] += count($offers);
                $state['stats']['weeks'] += count($offers);
            }
            
            // Update state
            $state['synced_yachts']++;
            $state['current_index']++;
            
            // Update company stats
            if (isset($state['companies'][$company_id])) {
                $state['companies'][$company_id]['synced']++;
                if ($state['companies'][$company_id]['synced'] >= $state['companies'][$company_id]['total']) {
                    $state['companies'][$company_id]['status'] = 'complete';
                } else {
                    $state['companies'][$company_id]['status'] = 'syncing';
                }
            }
            
            $elapsed = round((microtime(true) - $start_time) * 1000);
            $offers_count = is_array($offers) ? count($offers) : 0;
            
            error_log("YOLO Progressive Sync: Synced {$offers_count} prices for {$yacht_name} in {$elapsed}ms");
            
        } catch (Exception $e) {
            $state['errors'][] = array(
                'yacht_id' => $yacht_id,
                'yacht_name' => $yacht_name,
                'error' => $e->getMessage()
            );
            $state['current_index']++; // Skip this yacht
            error_log("YOLO Progressive Sync: ERROR syncing prices for {$yacht_name}: " . $e->getMessage());
        }
        
        // Save state
        update_option(self::STATE_OPTION, $state, false);
        
        // Calculate progress
        $progress = ($state['total_yachts'] > 0) 
            ? round(($state['synced_yachts'] / $state['total_yachts']) * 100, 1) 
            : 0;
        
        // Check if this was the last one
        $done = ($state['current_index'] >= count($queue));
        
        if ($done) {
            return $this->complete_price_sync($state);
        }
        
        return array(
            'success' => true,
            'done' => false,
            'yacht_synced' => $yacht_name,
            'company_id' => $company_id,
            'offers_count' => isset($offers_count) ? $offers_count : 0,
            'progress' => $progress,
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'elapsed_ms' => isset($elapsed) ? $elapsed : 0
        );
    }
    
    /**
     * Complete price sync - cleanup and final stats
     */
    private function complete_price_sync($state) {
        $state['status'] = 'complete';
        $state['completed_at'] = current_time('mysql');
        
        // Delete old offers
        YOLO_YS_Database_Prices::delete_old_offers();
        
        // Update last sync time
        update_option('yolo_ys_last_offer_sync', current_time('mysql'));
        update_option('yolo_ys_last_offer_sync_year', $state['year']);
        
        // Save final state
        update_option(self::STATE_OPTION, $state, false);
        
        // Calculate duration
        $duration = '';
        if ($state['started_at'] && $state['completed_at']) {
            $start = strtotime($state['started_at']);
            $end = strtotime($state['completed_at']);
            $seconds = $end - $start;
            $minutes = floor($seconds / 60);
            $secs = $seconds % 60;
            $duration = $minutes > 0 ? "{$minutes}m {$secs}s" : "{$secs}s";
        }
        
        error_log("YOLO Progressive Sync: PRICE SYNC COMPLETE - {$state['synced_yachts']} yachts, {$state['stats']['offers']} offers in {$duration}");
        
        return array(
            'success' => true,
            'done' => true,
            'message' => "Successfully synced prices for {$state['synced_yachts']} yachts ({$state['stats']['offers']} weekly offers)",
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'year' => $state['year'],
            'duration' => $duration,
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'errors' => $state['errors']
        );
    }
    
    /**
     * Get current sync state
     */
    public function get_state() {
        return get_option(self::STATE_OPTION, null);
    }
    
    /**
     * Cancel current sync
     */
    public function cancel_sync() {
        $state = get_option(self::STATE_OPTION, null);
        
        if ($state) {
            $state['status'] = 'cancelled';
            $state['completed_at'] = current_time('mysql');
            update_option(self::STATE_OPTION, $state, false);
        }
        
        // Clear any scheduled cron events
        wp_clear_scheduled_hook('yolo_progressive_sync_yacht');
        wp_clear_scheduled_hook('yolo_progressive_sync_price');
        
        return array(
            'success' => true,
            'message' => 'Sync cancelled'
        );
    }
    
    /**
     * Clear sync state (reset)
     */
    public function clear_state() {
        delete_option(self::STATE_OPTION);
        delete_option(self::YACHT_QUEUE_OPTION);
        delete_option(self::PRICE_QUEUE_OPTION);
        
        return array('success' => true);
    }
    
    // ==========================================
    // AUTO-SYNC (WP-CRON) METHODS
    // ==========================================
    
    /**
     * Start auto-sync via cron
     * 
     * @param string $type 'yachts' or 'prices'
     * @param int $year Year for price sync (optional)
     */
    public function start_auto_sync($type, $year = null) {
        error_log("YOLO Progressive Sync: Starting auto-sync for {$type}");
        
        if ($type === 'yachts') {
            $result = $this->init_yacht_sync();
            if ($result['success']) {
                // Schedule first yacht sync immediately
                wp_schedule_single_event(time(), 'yolo_progressive_sync_yacht');
            }
        } else if ($type === 'prices') {
            $result = $this->init_price_sync($year);
            if ($result['success']) {
                // Schedule first price sync immediately
                wp_schedule_single_event(time(), 'yolo_progressive_sync_price');
            }
        }
        
        return $result;
    }
    
    /**
     * Cron handler - sync next yacht and schedule next
     */
    public function cron_sync_next_yacht() {
        $result = $this->sync_next_yacht();
        
        if (!$result['done']) {
            // Schedule next yacht sync in 1 second
            wp_schedule_single_event(time() + 1, 'yolo_progressive_sync_yacht');
        } else {
            error_log("YOLO Progressive Sync: Auto yacht sync complete");
        }
    }
    
    /**
     * Cron handler - sync next price and schedule next
     */
    public function cron_sync_next_price() {
        $result = $this->sync_next_price();
        
        if (!$result['done']) {
            // Schedule next price sync in 1 second
            wp_schedule_single_event(time() + 1, 'yolo_progressive_sync_price');
        } else {
            error_log("YOLO Progressive Sync: Auto price sync complete");
        }
    }
    
    /**
     * Cron handler - start scheduled sync
     * 
     * @param array $args Array with 'type' and optionally 'year'
     */
    public function cron_start_sync($args = array()) {
        $type = isset($args['type']) ? $args['type'] : 'yachts';
        $year = isset($args['year']) ? $args['year'] : null;
        
        $this->start_auto_sync($type, $year);
    }
}
