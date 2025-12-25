<?php
/**
 * Progressive Sync - Two-Phase Sync with Image Batching
 * 
 * v81.1 - Two-phase sync to prevent timeout/memory issues
 * 
 * Phase 1: Sync yacht data only (fast, ~500ms per yacht)
 * Phase 2: Download images in batches of 2-3 per request
 * 
 * Features:
 * - No timeout risk - each request is short
 * - Live progress updates via AJAX
 * - Works on any hosting
 * - Supports both manual and auto-sync
 * - Resume capability if interrupted
 * 
 * @package YOLO_Yacht_Search
 * @since 81.1
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
    const IMAGE_QUEUE_OPTION = 'yolo_ys_sync_image_queue';
    const PRICE_QUEUE_OPTION = 'yolo_ys_sync_price_queue';
    
    // Batch sizes
    const IMAGES_PER_BATCH = 3;
    
    public function __construct() {
        if (class_exists('YOLO_YS_Booking_Manager_API')) {
            $this->api = new YOLO_YS_Booking_Manager_API();
        }
        
        if (class_exists('YOLO_YS_Database')) {
            $this->db = new YOLO_YS_Database();
        }
        
        // Register cron hooks for auto-sync
        add_action('yolo_progressive_sync_yacht', array($this, 'cron_sync_next_yacht'));
        add_action('yolo_progressive_sync_image', array($this, 'cron_sync_next_image_batch'));
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
     * Get Greek Ionian base IDs for filtering friend company yachts
     * v81.13: Uses HARDCODED base IDs for reliable filtering
     * 
     * The /yachts API endpoint ignores sailingAreaId parameter,
     * and the /bases API can be unreliable. Using hardcoded IDs
     * ensures consistent filtering regardless of API status.
     * 
     * Includes: Lefkada, Corfu, Kefalonia, Zakynthos, Ithaca, Preveza, Paxos
     * 
     * @return array Array of base IDs that are in Greek Ionian Sea
     */
    public function get_greek_ionian_base_ids() {
        // v81.13: Use hardcoded constant instead of API lookup
        // This is more reliable and doesn't depend on API availability
        if (defined('YOLO_YS_GREEK_IONIAN_BASE_IDS')) {
            error_log('YOLO Progressive Sync v81.13: Using hardcoded Greek Ionian base IDs (' . count(YOLO_YS_GREEK_IONIAN_BASE_IDS) . ' bases)');
            return YOLO_YS_GREEK_IONIAN_BASE_IDS;
        }
        
        // Fallback: return empty array (will skip friend companies)
        error_log('YOLO Progressive Sync v81.13 ERROR: YOLO_YS_GREEK_IONIAN_BASE_IDS constant not defined!');
        return array();
    }
    
    /**
     * Check if a yacht is based in Greek Ionian
     * v81.13: Helper method for filtering
     * 
     * @param array $yacht Yacht data from API
     * @return bool True if yacht is in Greek Ionian
     */
    private function is_greek_ionian_yacht($yacht) {
        if (!isset($yacht['homeBaseId'])) {
            return false;
        }
        $greek_ionian_base_ids = $this->get_greek_ionian_base_ids();
        return in_array($yacht['homeBaseId'], $greek_ionian_base_ids);
    }
    
    /**
     * Initialize yacht sync - Phase 1: Yacht data (no images)
     * 
     * @return array Initial state with yacht queue
     */
    public function init_yacht_sync() {
        $companies = $this->get_all_company_ids();
        $yacht_queue = array();
        $company_stats = array();
        
        // Get YOLO's company ID - their boats are NOT filtered
        $my_company_id = (int) get_option('yolo_ys_my_company_id', 7850);
        
        // v81.13: Get HARDCODED Greek Ionian base IDs for client-side filtering
        // Uses constant YOLO_YS_GREEK_IONIAN_BASE_IDS (41 verified bases)
        // FAIL-SAFE: If constant not defined, skip friend companies entirely
        $greek_ionian_base_ids = $this->get_greek_ionian_base_ids();
        $can_filter_friends = !empty($greek_ionian_base_ids);
        
        if (!$can_filter_friends) {
            error_log("YOLO Progressive Sync v81.13 WARNING: Greek Ionian base IDs not available - friend companies will be SKIPPED");
        }
        
        error_log("YOLO Progressive Sync v81.13: Initializing yacht sync for " . count($companies) . " companies (hardcoded Greek Ionian filter: " . count($greek_ionian_base_ids) . " bases)");
        
        foreach ($companies as $company_id) {
            if (empty($company_id)) continue;
            
            $is_friend_company = ((int)$company_id !== $my_company_id);
            
            // v81.13 FAIL-SAFE: Skip friend companies if we can't filter them
            if ($is_friend_company && !$can_filter_friends) {
                error_log("YOLO Progressive Sync: SKIPPING company {$company_id} - no Greek Ionian base IDs available for filtering");
                continue;
            }
            
            try {
                // Fetch ALL yachts for this company (API ignores sailingAreaId)
                $yachts = $this->api->get_yachts_by_company($company_id);
                
                if (is_array($yachts) && !empty($yachts)) {
                    $original_count = count($yachts);
                    
                    // v81.13: For friend companies, filter to Greek Ionian bases only
                    if ($is_friend_company) {
                        $yachts = array_filter($yachts, function($yacht) use ($greek_ionian_base_ids) {
                            return isset($yacht['homeBaseId']) && in_array($yacht['homeBaseId'], $greek_ionian_base_ids);
                        });
                        $yachts = array_values($yachts); // Re-index array
                        
                        error_log("YOLO Progressive Sync: Company {$company_id} filtered from {$original_count} to " . count($yachts) . " Greek Ionian yachts");
                    }
                    
                    if (!empty($yachts)) {
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
                                // v81.6: Don't store full yacht_data to avoid MySQL size limits
                                // Yacht data will be fetched fresh from API during sync
                                'image_count' => isset($yacht['images']) ? count($yacht['images']) : 0
                            );
                        }
                        
                        error_log("YOLO Progressive Sync: Company {$company_id} queued " . count($yachts) . " yachts");
                    }
                }
            } catch (Exception $e) {
                error_log("YOLO Progressive Sync: Failed to get yachts for company {$company_id}: " . $e->getMessage());
            }
        }
        
        // Count total images
        $total_images = array_sum(array_column($yacht_queue, 'image_count'));
        
        // Create initial state
        $state = array(
            'type' => 'yachts',
            'phase' => 1, // Phase 1: yacht data
            'status' => 'ready',
            'total_yachts' => count($yacht_queue),
            'synced_yachts' => 0,
            'current_index' => 0,
            'total_images' => $total_images,
            'synced_images' => 0,
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
            'message' => "Ready to sync {$state['total_yachts']} yachts ({$total_images} images) from " . count($companies) . " companies"
        );
    }
    
    /**
     * Sync a single yacht - DATA ONLY (Phase 1)
     * Images are synced separately in Phase 2
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
        
        // Check if Phase 1 is done
        if ($current_index >= count($queue)) {
            // Move to Phase 2: Image sync
            return $this->start_image_sync_phase($state, $queue);
        }
        
        $yacht_item = $queue[$current_index];
        $yacht_id = $yacht_item['yacht_id'];
        $yacht_name = $yacht_item['yacht_name'];
        $company_id = $yacht_item['company_id'];
        
        $start_time = microtime(true);
        
        try {
            // v81.6: Fetch fresh yacht data from API (not stored in queue to avoid MySQL size limits)
            $yacht_result = $this->api->get_yacht($yacht_id);
            if (!$yacht_result['success'] || empty($yacht_result['data'])) {
                throw new Exception('Failed to fetch yacht data from API');
            }
            $yacht_data = $yacht_result['data'];
            
            // Store yacht data WITHOUT images (Phase 1)
            $this->db->store_yacht_data_only($yacht_data, $company_id);
            
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
            
            error_log("YOLO Progressive Sync: Phase 1 - Synced yacht data {$yacht_name} ({$yacht_id}) in {$elapsed}ms");
            
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
        
        // Calculate progress (Phase 1 is 50% of total)
        $phase1_progress = ($state['total_yachts'] > 0) 
            ? ($state['synced_yachts'] / $state['total_yachts']) * 50 
            : 0;
        
        return array(
            'success' => true,
            'done' => false,
            'phase' => 1,
            'phase_name' => 'Syncing yacht data',
            'yacht_synced' => $yacht_name,
            'company_id' => $company_id,
            'progress' => round($phase1_progress, 1),
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'elapsed_ms' => isset($elapsed) ? $elapsed : 0
        );
    }
    
    /**
     * Start Phase 2: Image sync
     * v81.14 FIX: Don't make API calls here - just create a simple yacht queue
     * Images will be fetched one yacht at a time in sync_next_image_batch()
     * This prevents timeout when transitioning from Phase 1 to Phase 2
     */
    private function start_image_sync_phase($state, $yacht_queue) {
        // v81.14: Create simple image queue with just yacht IDs
        // Don't fetch images here - that caused 500 errors due to timeout
        $image_queue = array();
        
        foreach ($yacht_queue as $yacht_item) {
            // Only store yacht reference - images fetched during sync_next_image_batch
            $image_queue[] = array(
                'yacht_id' => $yacht_item['yacht_id'],
                'yacht_name' => $yacht_item['yacht_name'],
                'image_count' => isset($yacht_item['image_count']) ? $yacht_item['image_count'] : 0,
                'images_synced' => false  // Flag to track if this yacht's images are done
            );
        }
        
        // Update state for Phase 2
        $state['phase'] = 2;
        $state['image_queue_size'] = count($image_queue);
        $state['image_batch_index'] = 0;
        // v81.15: Track position within yacht images
        $state['current_yacht_index'] = 0;
        $state['current_image_offset'] = 0;
        
        // Store image queue
        update_option(self::IMAGE_QUEUE_OPTION, $image_queue, false);
        update_option(self::STATE_OPTION, $state, false);
        
        error_log("YOLO Progressive Sync: Phase 1 complete. Starting Phase 2 with " . count($image_queue) . " image batches");
        
        return array(
            'success' => true,
            'done' => false,
            'phase' => 2,
            'phase_name' => 'Downloading images',
            'phase_changed' => true,
            'progress' => 50, // Phase 1 complete = 50%
            'synced' => 0,  // v81.3: Starting Phase 2 at 0
            'total' => count($image_queue),  // v81.3: Total image batches
            'image_batches' => count($image_queue),
            'stats' => $state['stats'],
            'companies' => $state['companies']
        );
    }
    
    /**
     * Sync next batch of images (Phase 2)
     * v81.16: Downloads IMAGES_PER_BATCH (3) images per request
     * Caches yacht images to avoid redundant API calls
     * 
     * @return array Result with updated state
     */
    public function sync_next_image_batch() {
        $state = get_option(self::STATE_OPTION, null);
        $image_queue = get_option(self::IMAGE_QUEUE_OPTION, array());
        
        if (!$state || $state['phase'] !== 2) {
            return array(
                'success' => false,
                'message' => 'Not in image sync phase',
                'done' => true
            );
        }
        
        // Track yacht index AND image offset within yacht
        $yacht_index = isset($state['current_yacht_index']) ? $state['current_yacht_index'] : 0;
        $image_offset = isset($state['current_image_offset']) ? $state['current_image_offset'] : 0;
        
        // Check if all yachts done
        if ($yacht_index >= count($image_queue)) {
            return $this->complete_yacht_sync($state);
        }
        
        $yacht_item = $image_queue[$yacht_index];
        $yacht_id = $yacht_item['yacht_id'];
        $yacht_name = $yacht_item['yacht_name'];
        
        $start_time = microtime(true);
        $images_downloaded = 0;
        $total_images = 0;
        $is_first_batch = ($image_offset === 0);
        $yacht_complete = false;
        $all_images = array();
        
        try {
            // v81.16: Check if we have cached images for this yacht
            $cached_yacht_id = isset($state['cached_yacht_id']) ? $state['cached_yacht_id'] : null;
            $cached_images = isset($state['cached_images']) ? $state['cached_images'] : array();
            
            if ($cached_yacht_id === $yacht_id && !empty($cached_images)) {
                // Use cached images - no API call needed!
                $all_images = $cached_images;
            } else {
                // First batch for this yacht - fetch from API and cache
                $yacht_result = $this->api->get_yacht($yacht_id);
                
                if (!$yacht_result['success'] || empty($yacht_result['data'])) {
                    throw new Exception('Failed to fetch yacht data from API');
                }
                
                $yacht_data = $yacht_result['data'];
                $all_images = isset($yacht_data['images']) ? $yacht_data['images'] : array();
                
                // Cache images in state for subsequent batches
                $state['cached_yacht_id'] = $yacht_id;
                $state['cached_images'] = $all_images;
            }
            
            $total_images = count($all_images);
            
            // If first batch for this yacht, clear old images from DB
            if ($is_first_batch) {
                $this->db->clear_yacht_images($yacht_id);
            }
            
            // Get only the batch of images we need (3 at a time)
            $batch_images = array_slice($all_images, $image_offset, self::IMAGES_PER_BATCH);
            
            // Download this batch of images
            foreach ($batch_images as $batch_idx => $image) {
                $actual_index = $image_offset + $batch_idx;
                $result = $this->db->download_and_store_single_image($yacht_id, $image, $actual_index);
                if ($result) {
                    $images_downloaded++;
                    $state['synced_images']++;
                }
            }
            
            // Update offset
            $new_offset = $image_offset + count($batch_images);
            
            // Check if this yacht is complete
            if ($new_offset >= $total_images) {
                // Move to next yacht - clear cache
                $state['current_yacht_index'] = $yacht_index + 1;
                $state['current_image_offset'] = 0;
                $state['image_batch_index']++;  // For progress tracking
                $state['cached_yacht_id'] = null;  // Clear cache
                $state['cached_images'] = array();
                $yacht_complete = true;
            } else {
                // Continue with same yacht, next batch (cache remains)
                $state['current_image_offset'] = $new_offset;
            }
            
            $elapsed = round((microtime(true) - $start_time) * 1000);
            
            $batch_num = ceil($new_offset / self::IMAGES_PER_BATCH);
            $total_batches = ceil($total_images / self::IMAGES_PER_BATCH);
            error_log("YOLO Progressive Sync: Phase 2 - {$yacht_name} batch {$batch_num}/{$total_batches}: {$images_downloaded} images in {$elapsed}ms");
            
        } catch (Exception $e) {
            $state['errors'][] = array(
                'yacht_id' => $yacht_id,
                'yacht_name' => $yacht_name,
                'error' => 'Image sync failed: ' . $e->getMessage()
            );
            // Skip to next yacht on error - clear cache
            $state['current_yacht_index'] = $yacht_index + 1;
            $state['current_image_offset'] = 0;
            $state['image_batch_index']++;
            $state['cached_yacht_id'] = null;
            $state['cached_images'] = array();
            $yacht_complete = true;
            error_log("YOLO Progressive Sync: ERROR downloading images for {$yacht_name}: " . $e->getMessage());
        }
        
        // Save state
        update_option(self::STATE_OPTION, $state, false);
        
        // Calculate progress (Phase 2 is 50-100%)
        $yachts_done = isset($state['current_yacht_index']) ? $state['current_yacht_index'] : 0;
        $phase2_progress = ($state['image_queue_size'] > 0)
            ? ($yachts_done / $state['image_queue_size']) * 50
            : 0;
        $total_progress = 50 + $phase2_progress;
        
        // Check if all yachts done
        if ($state['current_yacht_index'] >= count($image_queue)) {
            return $this->complete_yacht_sync($state);
        }
        
        return array(
            'success' => true,
            'done' => false,
            'phase' => 2,
            'phase_name' => 'Downloading images',
            'yacht_name' => $yacht_name,
            'yacht_complete' => $yacht_complete,
            'images_downloaded' => $images_downloaded,
            'yacht_images_progress' => (isset($new_offset) ? $new_offset : 0) . '/' . $total_images,
            'progress' => round($total_progress, 1),
            'synced' => $state['current_yacht_index'],
            'total' => $state['image_queue_size'],
            'synced_images' => $state['synced_images'],
            'total_images' => $state['total_images'],
            'batch_progress' => $state['current_yacht_index'] . '/' . $state['image_queue_size'],
            'stats' => $state['stats'],
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
        
        // v81.15: Get current company list (YOLO + Friend Companies)
        $current_companies = $this->get_all_company_ids();
        
        if (!empty($synced_yacht_ids)) {
            $this->db->activate_yachts($synced_yacht_ids);
            
            // Deactivate missing yachts per company (within current companies)
            foreach ($current_companies as $company_id) {
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
        
        // v81.15 FIX: Deactivate ALL boats from companies that are no longer in the list
        // This handles the case when a company is removed from Friend Companies
        $this->db->deactivate_removed_company_yachts($current_companies);
        
        // Update last sync time
        update_option('yolo_ys_last_sync', current_time('mysql'));
        
        // Clean up queues
        delete_option(self::IMAGE_QUEUE_OPTION);
        delete_option(self::YACHT_QUEUE_OPTION);
        
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
            $duration = ($minutes > 0 ? "{$minutes}m " : '') . "{$secs}s";
        }
        
        error_log("YOLO Progressive Sync: COMPLETE - {$state['synced_yachts']} yachts, {$state['synced_images']} images in {$duration}");
        
        return array(
            'success' => true,
            'done' => true,
            'message' => "Successfully synced {$state['synced_yachts']} yachts and {$state['synced_images']} images",
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'synced_images' => $state['synced_images'],
            'total_images' => $state['total_images'],
            'duration' => $duration,
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'errors' => $state['errors']
        );
    }
    
    /**
     * Cancel running sync
     */
    public function cancel_sync() {
        $state = get_option(self::STATE_OPTION, null);
        
        if ($state) {
            $state['status'] = 'cancelled';
            $state['completed_at'] = current_time('mysql');
            update_option(self::STATE_OPTION, $state, false);
        }
        
        // Clear scheduled cron events
        wp_clear_scheduled_hook('yolo_progressive_sync_yacht');
        wp_clear_scheduled_hook('yolo_progressive_sync_image');
        wp_clear_scheduled_hook('yolo_progressive_sync_price');
        
        return array(
            'success' => true,
            'message' => 'Sync cancelled'
        );
    }
    
    /**
     * Get current sync state
     */
    public function get_state() {
        return get_option(self::STATE_OPTION, null);
    }
    
    // =========================================
    // PRICE SYNC
    // =========================================
    
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
        
        // Get all active yachts from database (fixed: use status column)
        $yachts = $wpdb->get_results(
            "SELECT id, name, company_id FROM {$yachts_table} WHERE (status = 'active' OR status IS NULL) ORDER BY company_id, name",
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
        
        // Check if done
        if ($current_index >= count($queue)) {
            return $this->complete_price_sync($state);
        }
        
        $yacht_item = $queue[$current_index];
        $yacht_id = $yacht_item['yacht_id'];
        $yacht_name = $yacht_item['yacht_name'];
        $company_id = $yacht_item['company_id'];
        $year = $state['year'];
        
        $start_time = microtime(true);
        $offers_count = 0;
        
        try {
            // Fetch offers for this yacht using correct array parameter format
            $offers = $this->api->get_offers(array(
                'yachtId' => $yacht_id,
                'dateFrom' => "{$year}-01-01T00:00:00",
                'dateTo' => "{$year}-12-31T23:59:59",
                'flexibility' => 6,
                'productName' => 'bareboat'
            ));
            
            if (is_array($offers) && !empty($offers)) {
                // Delete existing offers for this yacht/year first
                global $wpdb;
                $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$prices_table} WHERE yacht_id = %s AND YEAR(date_from) = %d",
                    $yacht_id, $year
                ));
                
                // Store new offers using batch insert (correct method)
                if (!class_exists('YOLO_YS_Database_Prices')) {
                    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database-prices.php';
                }
                YOLO_YS_Database_Prices::store_offers_batch($offers, $company_id);
                $offers_count = count($offers);
                $state['stats']['offers'] += $offers_count;
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
            
            error_log("YOLO Progressive Sync: Synced {$offers_count} offers for {$yacht_name} in {$elapsed}ms");
            
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
        
        // Check if done
        if ($state['current_index'] >= count($queue)) {
            return $this->complete_price_sync($state);
        }
        
        return array(
            'success' => true,
            'done' => false,
            'yacht_synced' => $yacht_name,
            'company_id' => $company_id,
            'offers_synced' => $offers_count,
            'progress' => $progress,
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'elapsed_ms' => isset($elapsed) ? $elapsed : 0
        );
    }
    
    /**
     * Complete price sync
     */
    private function complete_price_sync($state) {
        $state['status'] = 'complete';
        $state['completed_at'] = current_time('mysql');
        
        // Update last offers sync time
        // v85.4 FIX: Use correct option key (was 'yolo_ys_last_offers_sync', display reads 'yolo_ys_last_offer_sync')
        update_option('yolo_ys_last_offer_sync', current_time('mysql'));
        
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
            $duration = ($minutes > 0 ? "{$minutes}m " : '') . "{$secs}s";
        }
        
        error_log("YOLO Progressive Sync: Price sync COMPLETE - {$state['synced_yachts']} yachts, {$state['stats']['offers']} offers in {$duration}");
        
        return array(
            'success' => true,
            'done' => true,
            'message' => "Successfully synced prices for {$state['synced_yachts']} yachts ({$state['stats']['offers']} offers)",
            'synced' => $state['synced_yachts'],
            'total' => $state['total_yachts'],
            'duration' => $duration,
            'stats' => $state['stats'],
            'companies' => $state['companies'],
            'errors' => $state['errors']
        );
    }
    
    // =========================================
    // AUTO-SYNC (WP-CRON)
    // =========================================
    
    /**
     * Start auto-sync via cron
     */
    public function cron_start_sync($type = 'yachts') {
        error_log("YOLO Auto-Sync: Starting {$type} sync via cron");
        
        if ($type === 'yachts') {
            $result = $this->init_yacht_sync();
            if ($result['success']) {
                // Schedule first yacht sync
                wp_schedule_single_event(time() + 2, 'yolo_progressive_sync_yacht');
            }
        } else if ($type === 'prices') {
            $year = get_option('yolo_ys_sync_year', date('Y') + 1);
            $result = $this->init_price_sync($year);
            if ($result['success']) {
                // Schedule first price sync
                wp_schedule_single_event(time() + 2, 'yolo_progressive_sync_price');
            }
        }
    }
    
    /**
     * Cron handler: Sync next yacht (Phase 1)
     */
    public function cron_sync_next_yacht() {
        $state = get_option(self::STATE_OPTION, null);
        
        if (!$state || $state['status'] === 'cancelled') {
            error_log("YOLO Auto-Sync: Yacht sync cancelled or no state");
            return;
        }
        
        // Check if we're in Phase 1 or Phase 2
        if (isset($state['phase']) && $state['phase'] === 2) {
            // Phase 2: Image sync
            $result = $this->sync_next_image_batch();
        } else {
            // Phase 1: Yacht data sync
            $result = $this->sync_next_yacht();
        }
        
        if (!$result['done']) {
            // Check if phase changed to 2
            if (isset($result['phase']) && $result['phase'] === 2) {
                // Schedule image batch sync
                wp_schedule_single_event(time() + 1, 'yolo_progressive_sync_yacht');
            } else {
                // Schedule next yacht
                wp_schedule_single_event(time() + 1, 'yolo_progressive_sync_yacht');
            }
        } else {
            error_log("YOLO Auto-Sync: Yacht sync complete");
        }
        
        // Log progress every 10 items
        if (isset($result['synced']) && $result['synced'] % 10 === 0) {
            error_log("YOLO Auto-Sync Progress: {$result['synced']}/{$result['total']} - {$result['progress']}%");
        }
    }
    
    /**
     * Cron handler: Sync next image batch (Phase 2)
     */
    public function cron_sync_next_image_batch() {
        $result = $this->sync_next_image_batch();
        
        if (!$result['done']) {
            // Schedule next batch
            wp_schedule_single_event(time() + 1, 'yolo_progressive_sync_image');
        } else {
            error_log("YOLO Auto-Sync: Image sync complete");
        }
    }
    
    /**
     * Cron handler: Sync next price
     */
    public function cron_sync_next_price() {
        $state = get_option(self::STATE_OPTION, null);
        
        if (!$state || $state['status'] === 'cancelled' || $state['type'] !== 'prices') {
            error_log("YOLO Auto-Sync: Price sync cancelled or wrong type");
            return;
        }
        
        $result = $this->sync_next_price();
        
        if (!$result['done']) {
            // Schedule next yacht price sync
            wp_schedule_single_event(time() + 1, 'yolo_progressive_sync_price');
        } else {
            error_log("YOLO Auto-Sync: Price sync complete");
        }
        
        // Log progress every 10 yachts
        if (isset($result['synced']) && $result['synced'] % 10 === 0) {
            error_log("YOLO Auto-Sync Price Progress: {$result['synced']}/{$result['total']} - {$result['progress']}%");
        }
    }
}
