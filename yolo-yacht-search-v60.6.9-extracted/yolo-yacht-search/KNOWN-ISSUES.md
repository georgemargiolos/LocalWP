# Known Issues and Fixes

## ✅ RESOLVED: Yacht Sync Hanging Forever (v1.9.4)

### Problem
Yacht sync (red button) would hang indefinitely and never complete, even though it "used to work" in earlier versions.

### Root Cause Analysis

**Three Critical Bugs Found:**

#### Bug #1: Equipment Name Column (v1.8.2 - v1.9.2)
- `equipment_name` column was defined as `NOT NULL`
- Code tried to insert `NULL` values
- Database silently rejected INSERTs
- Sync appeared to hang on first yacht's equipment

#### Bug #2: Extras Table Primary Key (v1.0.0 - v1.9.3) **MAIN CULPRIT**
- Extras table used `id` as PRIMARY KEY
- Multiple yachts share the same extras (e.g., "Skipper" with ID `6390133980000108000`)
- When syncing yacht #2, tried to insert duplicate extra → **Duplicate key error**
- Sync failed silently and appeared to hang

#### Bug #3: Boolean to Integer Conversion
- API returns `true`/`false` for `obligatory` field
- WordPress `$wpdb->insert()` converted `false` to empty string `''`
- Database column `tinyint(1)` rejected empty strings
- INSERT failed with "Incorrect integer value" error

### Fix Applied in v1.9.4

**1. Equipment Table:**
```sql
-- Before
equipment_name varchar(255) NOT NULL

-- After
equipment_name varchar(255) DEFAULT NULL
```

**2. Extras Table:**
```sql
-- Before
PRIMARY KEY (id)

-- After  
PRIMARY KEY (id, yacht_id)  -- Composite key allows duplicate extras across yachts
```

**3. Boolean Conversion:**
```php
// Before
'obligatory' => isset($extra['obligatory']) ? $extra['obligatory'] : 0

// After
'obligatory' => !empty($extra['obligatory']) ? 1 : 0
```

### Testing Results

Tested with real MySQL database:
- ✅ 3 yachts synced in **0.07 seconds**
- ✅ 45 images stored
- ✅ 51 equipment items stored
- ✅ 34 extras stored (including duplicate extras across yachts)
- ✅ No errors, no hanging!

### Database Migration

**Automatic Migration:**
- Runs on plugin load when database version < 1.2
- Fixes equipment_name column to allow NULL
- Fixes extras table primary key to composite (id, yacht_id)
- No manual intervention required

### Affected Versions
- v1.8.2 - v1.9.3: **BROKEN** (sync hangs)
- v1.8.0 and earlier: **WORKING** (before equipment catalog changes)
- v1.9.4+: **FIXED** ✅

### Prevention
**Database Design Rules:**
1. Use composite primary keys when same entity can belong to multiple parents
2. Always allow NULL for optional fields
3. Convert boolean values to integers (0/1) before database insert
4. Test with real database operations, not just data processing

---

## ✅ RESOLVED: Fatal Error on Plugin Activation

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
- v1.5.0+: FIXED ✅

### Prevention
**Rule:** Never `require_once` a class file in the activator if it's already loaded in the main plugin file.

**Check before activation:**
1. Main plugin file loads classes → ✅
2. Activator uses those classes → ✅
3. Activator loads them again → ❌ WRONG!

---

## Current Status (v1.9.4)

### ✅ Working Features
- Equipment catalog sync (green button)
- Yacht sync (red button) - **NOW FIXED!**
- Weekly offers sync (blue button)
- Equipment display with FontAwesome icons
- Extras from all products
- Date picker with July week defaults
- Yacht details page (1500px width, centered)
- Automatic database migrations

### 🔍 Known Limitations
- None currently identified

### 📋 Testing Checklist
To verify everything works:
1. ✅ Activate plugin without errors
2. ✅ Click "Sync Equipment Catalog" (green) - completes in seconds
3. ✅ Click "Sync Yachts" (red) - completes in seconds
4. ✅ Click "Sync Weekly Offers" (blue) - completes in minutes
5. ✅ View yacht details page - equipment shows with icons
6. ✅ Check extras - all extras from all products displayed
7. ✅ Date picker shows dates from URL or defaults to July week

---

**Last Updated:** Nov 29, 2024  
**Current Version:** v1.9.4  
**Database Version:** 1.2
