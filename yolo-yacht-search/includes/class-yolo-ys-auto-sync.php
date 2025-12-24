<?php
/**
 * YOLO Yacht Search - Auto Sync
 * 
 * Handles automatic synchronization of yachts and offers using WordPress cron.
 * v81.0 - Updated to use progressive sync (boat-by-boat) for reliability
 * 
 * @package YOLO_Yacht_Search
 * @since 30.0
 * @updated 81.0 - Progressive sync integration
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Auto_Sync {

    /**
     * Cron hook names
     */
    const YACHT_SYNC_HOOK = 'yolo_ys_auto_sync_yachts';
    const OFFERS_SYNC_HOOK = 'yolo_ys_auto_sync_offers';
    
    // v81.0: Progressive sync hooks
    const PROGRESSIVE_YACHT_HOOK = 'yolo_ys_progressive_yacht_sync';
    const PROGRESSIVE_PRICE_HOOK = 'yolo_ys_progressive_price_sync';

    /**
     * Initialize the auto-sync functionality
     */
    public function __construct() {
        // Register main cron hooks (these trigger the progressive sync)
        add_action(self::YACHT_SYNC_HOOK, array($this, 'start_progressive_yacht_sync'));
        add_action(self::OFFERS_SYNC_HOOK, array($this, 'start_progressive_offers_sync'));
        
        // v81.0: Register progressive sync step hooks
        add_action(self::PROGRESSIVE_YACHT_HOOK, array($this, 'progressive_yacht_step'));
        add_action(self::PROGRESSIVE_PRICE_HOOK, array($this, 'progressive_price_step'));
        
        // Register custom cron schedules
        add_filter('cron_schedules', array($this, 'add_cron_schedules'));
        
        // AJAX handler for saving auto-sync settings
        add_action('wp_ajax_yolo_ys_save_auto_sync_settings', array($this, 'ajax_save_auto_sync_settings'));
    }

    /**
     * Add custom cron schedules
     */
    public function add_cron_schedules($schedules) {
        $schedules['weekly'] = array(
            'interval' => 604800, // 7 days in seconds
            'display' => __('Once Weekly', 'yolo-yacht-search')
        );
        return $schedules;
    }

    /**
     * v81.0: Start progressive yacht sync
     * This initializes the sync and schedules the first step
     */
    public function start_progressive_yacht_sync() {
        error_log('[YOLO Auto-Sync v81] Starting progressive yacht sync at ' . current_time('mysql'));
        
        try {
            // Load progressive sync class
            if (!class_exists('YOLO_YS_Progressive_Sync')) {
                require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-progressive-sync.php';
            }
            
            $sync = new YOLO_YS_Progressive_Sync();
            $result = $sync->init_yacht_sync();
            
            if ($result['success']) {
                error_log('[YOLO Auto-Sync v81] Yacht sync initialized: ' . $result['state']['total_yachts'] . ' yachts to sync');
                
                // Schedule first step immediately
                wp_schedule_single_event(time() + 1, self::PROGRESSIVE_YACHT_HOOK);
            } else {
                error_log('[YOLO Auto-Sync v81] Failed to initialize yacht sync: ' . $result['message']);
                update_option('yolo_ys_last_yacht_sync_status', 'error');
            }
        } catch (Exception $e) {
            error_log('[YOLO Auto-Sync v81] Yacht sync init failed: ' . $e->getMessage());
            update_option('yolo_ys_last_yacht_sync_status', 'error');
            update_option('yolo_ys_last_yacht_sync_error', $e->getMessage());
        }
    }
    
    /**
     * v81.0: Progressive yacht sync step
     * Syncs one yacht and schedules the next step
     */
    public function progressive_yacht_step() {
        try {
            if (!class_exists('YOLO_YS_Progressive_Sync')) {
                require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-progressive-sync.php';
            }
            
            $sync = new YOLO_YS_Progressive_Sync();
            $result = $sync->sync_next_yacht();
            
            if (!$result['done']) {
                // More yachts to sync - schedule next step in 1 second
                wp_schedule_single_event(time() + 1, self::PROGRESSIVE_YACHT_HOOK);
                
                // Log progress every 10 yachts
                if (isset($result['synced']) && $result['synced'] % 10 === 0) {
                    error_log('[YOLO Auto-Sync v81] Yacht sync progress: ' . $result['synced'] . '/' . $result['total']);
                }
            } else {
                // Sync complete
                error_log('[YOLO Auto-Sync v81] Yacht sync COMPLETE: ' . $result['synced'] . ' yachts in ' . $result['duration']);
                update_option('yolo_ys_last_sync', current_time('mysql'));
                update_option('yolo_ys_last_yacht_sync_status', 'success');
                
                // Log stats
                if (isset($result['stats'])) {
                    error_log('[YOLO Auto-Sync v81] Stats - Images: ' . $result['stats']['images'] . 
                              ', Extras: ' . $result['stats']['extras'] . 
                              ', Equipment: ' . $result['stats']['equipment']);
                }
            }
        } catch (Exception $e) {
            error_log('[YOLO Auto-Sync v81] Yacht sync step failed: ' . $e->getMessage());
            // Don't stop - try to continue with next yacht
            wp_schedule_single_event(time() + 5, self::PROGRESSIVE_YACHT_HOOK);
        }
    }

    /**
     * v81.0: Start progressive offers sync
     * This initializes the sync and schedules the first step
     */
    public function start_progressive_offers_sync() {
        error_log('[YOLO Auto-Sync v81] Starting progressive offers sync at ' . current_time('mysql'));
        
        // Get the year from settings (same as manual sync)
        $year = (int) get_option('yolo_ys_offers_sync_year', date('Y') + 1);
        
        try {
            if (!class_exists('YOLO_YS_Progressive_Sync')) {
                require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-progressive-sync.php';
            }
            
            $sync = new YOLO_YS_Progressive_Sync();
            $result = $sync->init_price_sync($year);
            
            if ($result['success']) {
                error_log('[YOLO Auto-Sync v81] Offers sync initialized: ' . $result['state']['total_yachts'] . ' yachts for year ' . $year);
                
                // Schedule first step immediately
                wp_schedule_single_event(time() + 1, self::PROGRESSIVE_PRICE_HOOK);
            } else {
                error_log('[YOLO Auto-Sync v81] Failed to initialize offers sync: ' . $result['message']);
                update_option('yolo_ys_last_offers_sync_status', 'error');
            }
        } catch (Exception $e) {
            error_log('[YOLO Auto-Sync v81] Offers sync init failed: ' . $e->getMessage());
            update_option('yolo_ys_last_offers_sync_status', 'error');
            update_option('yolo_ys_last_offers_sync_error', $e->getMessage());
        }
    }
    
    /**
     * v81.0: Progressive price sync step
     * Syncs prices for one yacht and schedules the next step
     */
    public function progressive_price_step() {
        try {
            if (!class_exists('YOLO_YS_Progressive_Sync')) {
                require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-progressive-sync.php';
            }
            
            $sync = new YOLO_YS_Progressive_Sync();
            $result = $sync->sync_next_price();
            
            if (!$result['done']) {
                // More yachts to sync - schedule next step in 1 second
                wp_schedule_single_event(time() + 1, self::PROGRESSIVE_PRICE_HOOK);
                
                // Log progress every 10 yachts
                if (isset($result['synced']) && $result['synced'] % 10 === 0) {
                    error_log('[YOLO Auto-Sync v81] Price sync progress: ' . $result['synced'] . '/' . $result['total'] . 
                              ' (' . $result['stats']['offers'] . ' offers)');
                }
            } else {
                // Sync complete
                error_log('[YOLO Auto-Sync v81] Price sync COMPLETE: ' . $result['synced'] . ' yachts, ' . 
                          $result['stats']['offers'] . ' offers in ' . $result['duration']);
                update_option('yolo_ys_last_offer_sync', current_time('mysql'));
                update_option('yolo_ys_last_offer_sync_year', $result['year']);
                update_option('yolo_ys_last_offers_sync_status', 'success');
            }
        } catch (Exception $e) {
            error_log('[YOLO Auto-Sync v81] Price sync step failed: ' . $e->getMessage());
            // Don't stop - try to continue with next yacht
            wp_schedule_single_event(time() + 5, self::PROGRESSIVE_PRICE_HOOK);
        }
    }

    /**
     * Schedule sync events based on frequency setting
     */
    public static function schedule_events() {
        // Schedule yacht sync
        $yacht_freq = get_option('yolo_ys_auto_sync_yachts_frequency', 'disabled');
        $yacht_time = get_option('yolo_ys_auto_sync_yachts_time', '03:00');
        self::schedule_single_event(self::YACHT_SYNC_HOOK, $yacht_freq, $yacht_time);
        
        // Schedule offers sync
        $offers_freq = get_option('yolo_ys_auto_sync_offers_frequency', 'disabled');
        $offers_time = get_option('yolo_ys_auto_sync_offers_time', '03:00');
        self::schedule_single_event(self::OFFERS_SYNC_HOOK, $offers_freq, $offers_time);
    }

    /**
     * Schedule a single cron event
     */
    private static function schedule_single_event($hook, $frequency, $time) {
        // Clear existing schedule
        $timestamp = wp_next_scheduled($hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $hook);
        }
        
        // Don't schedule if disabled
        if ($frequency === 'disabled') {
            return;
        }
        
        // Calculate next run time based on selected time
        $timezone = wp_timezone();
        $now = new DateTime('now', $timezone);
        $scheduled = new DateTime('today ' . $time, $timezone);
        
        // If the time has passed today, schedule for tomorrow
        if ($scheduled <= $now) {
            $scheduled->modify('+1 day');
        }
        
        $next_run = $scheduled->getTimestamp();
        
        // Schedule the event
        wp_schedule_event($next_run, $frequency, $hook);
        
        error_log('[YOLO Auto-Sync v81] Scheduled ' . $hook . ' with frequency ' . $frequency . ' starting at ' . $scheduled->format('Y-m-d H:i:s'));
    }

    /**
     * Unschedule all sync events (called on plugin deactivation)
     */
    public static function unschedule_all_events() {
        // Main sync hooks
        $yacht_timestamp = wp_next_scheduled(self::YACHT_SYNC_HOOK);
        if ($yacht_timestamp) {
            wp_unschedule_event($yacht_timestamp, self::YACHT_SYNC_HOOK);
        }
        
        $offers_timestamp = wp_next_scheduled(self::OFFERS_SYNC_HOOK);
        if ($offers_timestamp) {
            wp_unschedule_event($offers_timestamp, self::OFFERS_SYNC_HOOK);
        }
        
        // v81.0: Clear progressive sync hooks too
        wp_clear_scheduled_hook(self::PROGRESSIVE_YACHT_HOOK);
        wp_clear_scheduled_hook(self::PROGRESSIVE_PRICE_HOOK);
        
        error_log('[YOLO Auto-Sync v81] All sync events unscheduled');
    }

    /**
     * AJAX handler for saving auto-sync settings
     */
    public function ajax_save_auto_sync_settings() {
        // Verify nonce
        if (!check_ajax_referer('yolo_ys_admin_nonce', 'nonce', false)) {
            wp_send_json_error('Invalid nonce');
            return;
        }
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
            return;
        }
        
        $setting = sanitize_text_field($_POST['setting']);
        $frequency = sanitize_text_field($_POST['frequency']);
        $time = sanitize_text_field($_POST['time']);
        
        // Validate frequency (use WordPress standard cron names: twicedaily NOT twice_daily)
        $valid_frequencies = array('disabled', 'twicedaily', 'daily', 'weekly');
        if (!in_array($frequency, $valid_frequencies)) {
            wp_send_json_error('Invalid frequency');
            return;
        }
        
        // Validate time format (HH:00)
        if (!preg_match('/^([01]?[0-9]|2[0-3]):00$/', $time)) {
            wp_send_json_error('Invalid time format');
            return;
        }
        
        // Determine which setting to update
        if ($setting === 'yolo-ys-auto-sync-yachts') {
            update_option('yolo_ys_auto_sync_yachts_frequency', $frequency);
            update_option('yolo_ys_auto_sync_yachts_time', $time);
            $hook = self::YACHT_SYNC_HOOK;
        } elseif ($setting === 'yolo-ys-auto-sync-offers') {
            update_option('yolo_ys_auto_sync_offers_frequency', $frequency);
            update_option('yolo_ys_auto_sync_offers_time', $time);
            $hook = self::OFFERS_SYNC_HOOK;
        } else {
            wp_send_json_error('Invalid setting');
            return;
        }
        
        // Reschedule the event
        self::schedule_single_event($hook, $frequency, $time);
        
        // Get next scheduled time for response
        $next_run = wp_next_scheduled($hook);
        $next_run_formatted = $next_run ? date('M d, Y g:i A', $next_run) : 'Not scheduled';
        
        wp_send_json_success(array(
            'message' => 'Auto-sync setting saved',
            'next_run' => $next_run_formatted
        ));
    }

    /**
     * Get status information for display
     */
    public static function get_sync_status($type = 'yachts') {
        $hook = ($type === 'yachts') ? self::YACHT_SYNC_HOOK : self::OFFERS_SYNC_HOOK;
        $next_run = wp_next_scheduled($hook);
        
        $status = array(
            'enabled' => $next_run !== false,
            'next_run' => $next_run ? date('M d, Y g:i A', $next_run) : null,
            'frequency' => get_option('yolo_ys_auto_sync_' . $type . '_frequency', 'disabled'),
            'time' => get_option('yolo_ys_auto_sync_' . $type . '_time', '03:00'),
            'last_sync' => get_option('yolo_ys_last_' . $type . '_sync', null),
            'last_status' => get_option('yolo_ys_last_' . $type . '_sync_status', null)
        );
        
        return $status;
    }
}
