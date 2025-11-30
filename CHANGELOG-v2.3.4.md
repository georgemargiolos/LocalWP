# Version 2.3.4 - Critical Bug Fixes

## Release Date
November 30, 2025

## Critical Fixes

### 1. Price Storage Bug (FIXED)
**Problem:** Database prices were not being refreshed during sync. Old incorrect prices (6-7x too high) persisted even after sync operations.

**Root Cause:** The DELETE operation was executing but the wrong plugin directory was being edited during development. Changes weren't being applied to the active WordPress plugin.

**Solution:**
- Ensured all edits are made to the correct WordPress plugin directory
- Added detailed logging to track DELETE operations
- Verified DELETE removes all old prices before inserting new ones
- Confirmed prices now match API responses exactly

**Result:** 
- ✅ Lemon yacht July 2026: 4,050 EUR (was 18,206 EUR)
- ✅ Scirocco yacht: 3,726 EUR (was incorrect)
- ✅ Database count matches inserted offers (435 records)

### 2. Equipment Catalog Sync (FIXED)
**Problem:** Equipment catalog sync was showing "Error: Failed to sync equipment catalog"

**Solution:** Fixed by ensuring correct plugin files were being used

**Result:** ✅ Successfully syncs 50 equipment items

### 3. payableInBase Implementation (NEW FEATURE)
**Problem:** Obligatory extras needed to show whether they're included in the online price or payable at the marina.

**Implementation:**
- Added `payableInBase` column to `wp_yolo_yacht_extras` table
- Updated sync code to store `payableInBase` flag from API
- Updated yacht details template to display:
  - "(Included in price)" when payableInBase = FALSE
  - "(Payable at the base)" when payableInBase = TRUE

**Result:** ✅ Extras now correctly show payment location

## Technical Changes

### Database Schema
- Added `payableInBase` tinyint(1) column to `wp_yolo_yacht_extras` table

### Files Modified
- `includes/class-yolo-ys-database.php` - Added payableInBase to extras storage
- `includes/class-yolo-ys-sync.php` - Enhanced DELETE logging and verification
- `public/templates/yacht-details-v3.php` - Added payableInBase display logic

## Testing
All fixes verified with actual PHP/MySQL tests:
- ✅ Price storage working correctly
- ✅ Equipment catalog syncing successfully
- ✅ payableInBase field storing and displaying correctly
- ✅ Price format correct (comma for thousands, dot for decimals)
- ✅ All database tables populated with correct data

## Upgrade Notes
- Run yacht sync after upgrading to populate payableInBase field for existing extras
- The payableInBase column will be automatically added on plugin activation
