# Known Issues and Fixes

## Fatal Error on Plugin Activation

### Problem
Plugin fails to activate with "Plugin could not be activated because it triggered a fatal error"

### Root Cause
Redundant `require_once` statements in the activator class were trying to load database classes that were already loaded in the main plugin file, potentially causing "class already defined" errors.

### Fix Applied in v1.5.0
Removed redundant `require_once` from `class-yolo-ys-activator.php`:

**Before (BROKEN):**
```php
// Create database tables
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database.php';
YOLO_YS_Database::create_tables();

// Create prices table
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database-prices.php';
YOLO_YS_Database_Prices::create_prices_table();
```

**After (FIXED):**
```php
// Create database tables (classes already loaded in main plugin file)
YOLO_YS_Database::create_tables();

// Create prices table
YOLO_YS_Database_Prices::create_prices_table();
```

### Why This Works
- Database classes are already loaded at the top of `yolo-yacht-search.php` (lines 35-38)
- The activator runs AFTER the main plugin file loads
- Classes are already available in memory
- No need to `require_once` again
- Prevents potential "Cannot redeclare class" errors

### Affected Versions
- v1.1.0 - v1.4.0: BROKEN (activation fails)
- v1.0.4 and earlier: WORKING (no prices feature)
- v1.5.0+: FIXED

### Prevention
**Rule:** Never `require_once` a class file in the activator if it's already loaded in the main plugin file.

**Check before activation:**
1. Main plugin file loads classes → ✅
2. Activator uses those classes → ✅
3. Activator loads them again → ❌ WRONG!

### Testing
To verify the fix:
1. Deactivate and delete old plugin
2. Upload v1.5.0
3. Activate
4. Should activate without errors
5. Check database for `wp_yolo_yachts` and `wp_yolo_yacht_prices` tables

---

**Last Updated:** Nov 28, 2025  
**Fixed In:** v1.5.0
