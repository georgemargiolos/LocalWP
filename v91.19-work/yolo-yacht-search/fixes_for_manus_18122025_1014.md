# YOLO Yacht Search - All Fixes for Manus
## Date: December 18, 2025 @ 10:14 AM
## Version: 70.9

---

# 🚨 CRITICAL: Auto-Sync Was Completely Broken

The auto-sync feature (added in v30.0) **NEVER WORKED** due to multiple bugs. Here are ALL the fixes applied:

---

## Bug #1: Calling Non-Existent Methods (v70.6)

### Problem
The auto-sync class was calling methods that don't exist:

```php
// BROKEN CODE
$sync->sync_yachts();          // ❌ Method doesn't exist!
$sync->sync_weekly_offers();   // ❌ Method doesn't exist!
```

### Fix
```php
// FIXED CODE
$sync->sync_all_yachts();      // ✅ Correct method
$sync->sync_all_offers();      // ✅ Correct method
```

---

## Bug #2: Option Key Mismatch (v70.6)

### Problem
Auto-sync saved to different option keys than what the admin display reads:

| Component | Yacht Key (broken) | Offers Key (broken) |
|-----------|-------------------|---------------------|
| Manual sync | `yolo_ys_last_sync` | `yolo_ys_last_offer_sync` |
| Auto-sync | `yolo_ys_last_yacht_sync` ❌ | `yolo_ys_last_offers_sync` ❌ |
| Display reads | `yolo_ys_last_sync` | `yolo_ys_last_offer_sync` |

### Fix
Changed auto-sync to use the same option keys as manual sync.

---

## Bug #3: Wrong Cron Schedule Name (v70.8)

### Problem
WordPress cron uses `twicedaily` (no underscore), but code used `twice_daily`:

```php
// BROKEN - WordPress doesn't recognize this schedule
wp_schedule_event($next_run, 'twice_daily', $hook);  // ❌ Fails silently!
```

### Fix
Changed all occurrences from `twice_daily` to `twicedaily`.

**Files changed:**
- `admin/partials/yolo-yacht-search-admin-display.php` - Both dropdown option values
- `includes/class-yolo-ys-auto-sync.php` - Validation array

---

## Bug #4: Timeout Issues (v70.9)

### Problem
Syncing could take 10-15 minutes for all companies across 2 years, hitting PHP/hosting timeouts.

### Fix
```php
@set_time_limit(900);           // 15 minutes
@ini_set('max_execution_time', 900);
@ini_set('memory_limit', '512M');
```

---

## Bug #5: Only Syncing One Year (v70.9)

### Problem
v70.7 only synced next year to avoid timeout.

### Fix
Now syncs **BOTH current year AND next year** with proper timeout handling.

---

# Complete Fixed File

## File: `includes/class-yolo-ys-auto-sync.php`

```php
<?php
/**
 * YOLO Yacht Search - Auto Sync
 * 
 * Handles automatic synchronization of yachts and offers using WordPress cron.
 * 
 * @package YOLO_Yacht_Search
 * @since 30.0
 * @version 70.9
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
     */
    public function run_offers_sync() {
        // Increase time limit significantly for syncing 2 years (can take 10-15 minutes)
        @set_time_limit(900); // 15 minutes
        @ini_set('max_execution_time', 900);
        @ini_set('memory_limit', '512M'); // Increase memory for large offer sets
        
        error_log('[YOLO Auto-Sync] Starting offers sync at ' . current_time('mysql'));
        
        $current_year = (int) date('Y');
        $next_year = $current_year + 1;
        $total_offers = 0;
        $success_count = 0;
        
        try {
            $sync = new YOLO_YS_Sync();
            
            // Sync CURRENT year
            error_log('[YOLO Auto-Sync] Syncing offers for CURRENT year ' . $current_year . '...');
            $result1 = $sync->sync_all_offers($current_year);
            if (isset($result1['success']) && $result1['success']) {
                $total_offers += $result1['offers_synced'];
                $success_count++;
                error_log('[YOLO Auto-Sync] Current year sync complete: ' . $result1['offers_synced'] . ' offers');
            } else {
                error_log('[YOLO Auto-Sync] Current year sync issues: ' . json_encode($result1));
            }
            
            // Sync NEXT year
            error_log('[YOLO Auto-Sync] Syncing offers for NEXT year ' . $next_year . '...');
            $result2 = $sync->sync_all_offers($next_year);
            if (isset($result2['success']) && $result2['success']) {
                $total_offers += $result2['offers_synced'];
                $success_count++;
                error_log('[YOLO Auto-Sync] Next year sync complete: ' . $result2['offers_synced'] . ' offers');
            } else {
                error_log('[YOLO Auto-Sync] Next year sync issues: ' . json_encode($result2));
            }
            
            // Log final result
            if ($success_count > 0) {
                error_log('[YOLO Auto-Sync] Offers sync completed: ' . $total_offers . ' total offers for ' . $current_year . ' and ' . $next_year);
                update_option('yolo_ys_last_offer_sync', current_time('mysql'));
                update_option('yolo_ys_last_offers_sync_status', 'success');
            } else {
                error_log('[YOLO Auto-Sync] Offers sync failed for both years');
                update_option('yolo_ys_last_offers_sync_status', 'warning');
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
```

---

## File: `admin/partials/yolo-yacht-search-admin-display.php`

**Change the dropdown option values from `twice_daily` to `twicedaily`:**

Find these lines and change `twice_daily` to `twicedaily`:

```php
// Line ~76 (Yacht sync dropdown)
<option value="twicedaily" <?php selected(..., 'twicedaily'); ?>>Twice Daily</option>

// Line ~147 (Offers sync dropdown)  
<option value="twicedaily" <?php selected(..., 'twicedaily'); ?>>Twice Daily</option>
```

---

## File: `yolo-yacht-search.php`

Update version to 70.9:
```php
* Version: 70.9
```
and
```php
define('YOLO_YS_VERSION', '70.9');
```

---

# Timezone Information

The 3:00 AM time uses **WordPress timezone** (Settings → General → Timezone).

If set to `Europe/Athens`, then 3:00 AM = 3:00 AM Greek time (EET/EEST).

---

# How to Verify After Deployment

## Step 1: Re-trigger the cron schedule
1. Go to YOLO Yacht Search → Settings
2. Change "Auto-Sync" dropdown from "Daily" to "Disabled"
3. Change it back to "Daily" (or desired frequency)
4. You should see "Next: Dec 19, 3:00 AM" instead of "Not scheduled"

## Step 2: Check debug logs after 3:00 AM
Look in `/wp-content/debug.log` for:
```
[YOLO Auto-Sync] Starting yacht sync at 2025-12-19 03:00:00
[YOLO Auto-Sync] Yacht sync completed successfully
[YOLO Auto-Sync] Starting offers sync at 2025-12-19 03:05:00
[YOLO Auto-Sync] Syncing offers for CURRENT year 2025...
[YOLO Auto-Sync] Current year sync complete: 1847 offers
[YOLO Auto-Sync] Syncing offers for NEXT year 2026...
[YOLO Auto-Sync] Next year sync complete: 2156 offers
[YOLO Auto-Sync] Offers sync completed: 4003 total offers for 2025 and 2026
```

## Step 3: Verify admin display updates
The "Last Yacht Sync" and "Last Offers Sync" timestamps in the admin panel should now update after auto-sync runs.

---

# Summary of All Bugs Fixed

| Bug | Version | Description |
|-----|---------|-------------|
| #1 | v70.6 | Calling non-existent methods (`sync_yachts` → `sync_all_yachts`) |
| #2 | v70.6 | Option key mismatch (auto-sync saved to different key than display reads) |
| #3 | v70.8 | Wrong cron schedule name (`twice_daily` → `twicedaily`) |
| #4 | v70.9 | Timeout issues (increased to 15 min, 512M memory) |
| #5 | v70.9 | Only syncing one year (now syncs both current + next year) |

---

# Author
Cursor AI Assistant  
December 18, 2025
