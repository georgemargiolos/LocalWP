<?php
/**
 * YOLO Yacht Search - Auto Sync
 * 
 * Handles automatic synchronization of yachts and offers using WordPress cron.
 * 
 * @package YOLO_Yacht_Search
 * @since 30.0
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

    /**
     * Initialize the auto-sync functionality
     */
    public function __construct() {
        // Register cron hooks
        add_action(self::YACHT_SYNC_HOOK, array($this, 'run_yacht_sync'));
        add_action(self::OFFERS_SYNC_HOOK, array($this, 'run_offers_sync'));
        
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
     * Run yacht synchronization
     * FIX v70.6: Call correct method sync_all_yachts() and use correct option key
     * FIX v70.9: Improved timeout and memory handling
     */
    public function run_yacht_sync() {
        // Increase time limit for auto-sync (can take 5-10 minutes for all yachts + images)
        @set_time_limit(900); // 15 minutes
        @ini_set('max_execution_time', 900);
        @ini_set('memory_limit', '512M'); // Increase memory for image processing
        
        error_log('[YOLO Auto-Sync] Starting yacht sync at ' . current_time('mysql'));
        
        try {
            $sync = new YOLO_YS_Sync();
            $result = $sync->sync_all_yachts(); // FIX: Was sync_yachts() which doesn't exist!
            
            // Log result
            if ($result && isset($result['success']) && $result['success']) {
                error_log('[YOLO Auto-Sync] Yacht sync completed successfully');
                // FIX: Use same option key as manual sync so display shows correctly
                update_option('yolo_ys_last_sync', current_time('mysql'));
                update_option('yolo_ys_last_yacht_sync_status', 'success');
            } else {
                error_log('[YOLO Auto-Sync] Yacht sync completed with issues: ' . json_encode($result));
                update_option('yolo_ys_last_yacht_sync_status', 'warning');
            }
        } catch (Exception $e) {
            error_log('[YOLO Auto-Sync] Yacht sync failed: ' . $e->getMessage());
            update_option('yolo_ys_last_yacht_sync_status', 'error');
            update_option('yolo_ys_last_yacht_sync_error', $e->getMessage());
        }
    }

    /**
     * Run offers synchronization
     * FIX v70.6: Call correct method sync_all_offers() and use correct option key
     * FIX v70.9: Sync BOTH current year AND next year (user requirement)
     * FIX v80.2: Check offers_synced > 0 instead of success flag (handles partial successes)
     */
    public function run_offers_sync() {
        // Increase time limit significantly for syncing 2 years (can take 10-15 minutes)
        @set_time_limit(900); // 15 minutes
        @ini_set('max_execution_time', 900);
        @ini_set('memory_limit', '512M'); // Increase memory for large offer sets
        
        error_log('[YOLO Auto-Sync] Starting offers sync at ' . current_time('mysql'));
        
        // v80.3 FIX: Use the year selected in the dropdown (same as manual sync)
        // This is simpler, more reliable, and matches user expectations
        // The year is stored when user selects it in the admin dropdown
        $selected_year = (int) get_option('yolo_ys_offers_sync_year', date('Y') + 1);
        $total_offers = 0;
        
        try {
            $sync = new YOLO_YS_Sync();
            
            // Sync the selected year only (same as manual sync)
            error_log('[YOLO Auto-Sync] Syncing offers for year ' . $selected_year . ' (from dropdown)...');
            $result = $sync->sync_all_offers($selected_year);
            
            // Check if offers were actually synced
            if (isset($result['offers_synced']) && $result['offers_synced'] > 0) {
                $total_offers = $result['offers_synced'];
                error_log('[YOLO Auto-Sync] Year ' . $selected_year . ' sync complete: ' . $total_offers . ' offers');
                
                // Log per-company results if available
                if (isset($result['message'])) {
                    error_log('[YOLO Auto-Sync] Result: ' . $result['message']);
                }
                if (!empty($result['errors'])) {
                    error_log('[YOLO Auto-Sync] Warnings: ' . implode('; ', $result['errors']));
                }
            } else {
                error_log('[YOLO Auto-Sync] Year ' . $selected_year . ' sync issues: ' . json_encode($result));
            }
            
            // Log final result
            if ($total_offers > 0) {
                error_log('[YOLO Auto-Sync] Offers sync completed: ' . $total_offers . ' total offers for year ' . $selected_year);
                // Update last sync time
                update_option('yolo_ys_last_offer_sync', current_time('mysql'));
                update_option('yolo_ys_last_offers_sync_status', 'success');
            } else {
                error_log('[YOLO Auto-Sync] Offers sync failed - no offers synced for year ' . $selected_year);
                update_option('yolo_ys_last_offers_sync_status', 'warning');
                // Don't update last_offer_sync if nothing was synced
            }
        } catch (Exception $e) {
            error_log('[YOLO Auto-Sync] Offers sync failed: ' . $e->getMessage());
            update_option('yolo_ys_last_offers_sync_status', 'error');
            update_option('yolo_ys_last_offers_sync_error', $e->getMessage());
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
        
        error_log('[YOLO Auto-Sync] Scheduled ' . $hook . ' with frequency ' . $frequency . ' starting at ' . $scheduled->format('Y-m-d H:i:s'));
    }

    /**
     * Unschedule all sync events (called on plugin deactivation)
     */
    public static function unschedule_all_events() {
        $yacht_timestamp = wp_next_scheduled(self::YACHT_SYNC_HOOK);
        if ($yacht_timestamp) {
            wp_unschedule_event($yacht_timestamp, self::YACHT_SYNC_HOOK);
        }
        
        $offers_timestamp = wp_next_scheduled(self::OFFERS_SYNC_HOOK);
        if ($offers_timestamp) {
            wp_unschedule_event($offers_timestamp, self::OFFERS_SYNC_HOOK);
        }
        
        error_log('[YOLO Auto-Sync] All sync events unscheduled');
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
