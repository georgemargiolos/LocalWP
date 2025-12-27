# YOLO Yacht Search - Auto-Sync Fixes for Manus
## Date: December 18, 2025 @ 10:15 AM
## Version: 70.9

---

# Fixes to Apply (3 bugs)

---

## Fix #1: Wrong Cron Schedule Name (v70.8)

### Problem
WordPress cron uses `twicedaily` (no underscore), but code used `twice_daily`. This caused cron to **fail silently** and show "Next: Not scheduled".

### Files to Change

#### File: `includes/class-yolo-ys-auto-sync.php`

Find line ~207:
```php
// OLD (broken)
$valid_frequencies = array('disabled', 'twice_daily', 'daily', 'weekly');
```

Change to:
```php
// NEW (fixed)
$valid_frequencies = array('disabled', 'twicedaily', 'daily', 'weekly');
```

#### File: `admin/partials/yolo-yacht-search-admin-display.php`

Find line ~76 (Yacht sync dropdown):
```php
// OLD
<option value="twice_daily" <?php selected(..., 'twice_daily'); ?>>Twice Daily</option>
```

Change to:
```php
// NEW
<option value="twicedaily" <?php selected(..., 'twicedaily'); ?>>Twice Daily</option>
```

Find line ~147 (Offers sync dropdown):
```php
// OLD
<option value="twice_daily" <?php selected(..., 'twice_daily'); ?>>Twice Daily</option>
```

Change to:
```php
// NEW
<option value="twicedaily" <?php selected(..., 'twicedaily'); ?>>Twice Daily</option>
```

---

## Fix #2: Timeout Issues (v70.9)

### Problem
Syncing could take 10-15 minutes, hitting PHP/hosting timeouts.

### File: `includes/class-yolo-ys-auto-sync.php`

Add these lines at the START of both `run_yacht_sync()` and `run_offers_sync()` methods:

```php
// Add at start of run_yacht_sync() and run_offers_sync()
@set_time_limit(900); // 15 minutes
@ini_set('max_execution_time', 900);
@ini_set('memory_limit', '512M');
```

---

## Fix #3: Sync Both Years (v70.9)

### Problem
Need to sync both current year AND next year offers.

### File: `includes/class-yolo-ys-auto-sync.php`

Replace the entire `run_offers_sync()` method with:

```php
/**
 * Run offers synchronization
 * FIX v70.9: Sync BOTH current year AND next year
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
```

---

## Version Bump

### File: `yolo-yacht-search.php`

```php
* Version: 70.9
```
and
```php
define('YOLO_YS_VERSION', '70.9');
```

---

# After Deployment

1. Go to YOLO Yacht Search → Settings
2. Change "Auto-Sync" dropdown to "Disabled" then back to "Daily"
3. You should see "Next: Dec 19, 3:00 AM" instead of "Not scheduled"

---

# Author
Cursor AI Assistant  
December 18, 2025

