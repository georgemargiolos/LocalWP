# Changelog - v1.5.6

**Date:** November 28, 2025  
**Status:** ✅ CRITICAL BUG FIX - Sync Hang/Timeout Resolved

## Summary

This version resolves the critical sync hang/timeout issue reported in v1.5.5. The root cause was that yacht sync and price sync were being executed together in a single AJAX request, causing timeouts when processing large amounts of data. This release separates the two operations and implements chunking for price sync to ensure reliable performance.

## Critical Fixes

### 1. **Separated Yacht Sync and Price Sync** ✅
- **Problem:** `sync_all_yachts()` was calling `sync_prices()` internally, causing a single AJAX request to handle both operations
- **Solution:** Removed price sync from `sync_all_yachts()` and created separate `sync_all_prices()` method
- **Impact:** Yacht sync now completes in 30-60 seconds without hanging
- **Files Changed:**
  - `includes/class-yolo-ys-sync.php`

### 2. **Implemented Price Sync Chunking** ✅
- **Problem:** Requesting 3-12 months of price data in a single API call could return thousands of records, overwhelming the AJAX handler
- **Solution:** Chunk price sync into 4-week increments (3 chunks = 12 weeks total)
- **Benefits:**
  - Each API call returns fewer records
  - Reduces memory usage
  - Prevents PHP execution timeouts
  - Allows WordPress to reuse memory between chunks
- **Files Changed:**
  - `includes/class-yolo-ys-sync.php`

### 3. **Added API Response Validation** ✅
- **Problem:** Code assumed API always returns an array, but could return unexpected formats causing fatal errors
- **Solution:** Added `is_array()` checks before processing yacht and price data
- **Impact:** Prevents crashes when API returns unexpected responses
- **Files Changed:**
  - `includes/class-yolo-ys-sync.php`

### 4. **Increased API Timeout to 60 Seconds** ✅
- **Problem:** 30-second timeout was insufficient for price API calls on slower servers
- **Solution:** Increased `wp_remote_get()` timeout from 30 to 60 seconds
- **Impact:** Allows more time for API responses, especially for price data
- **Files Changed:**
  - `includes/class-yolo-ys-booking-manager-api.php`

### 5. **Removed Duplicate Table Creation** ✅
- **Problem:** `activate_yolo_yacht_search()` was calling `create_tables()` even though `YOLO_YS_Activator::activate()` already creates them
- **Solution:** Removed redundant `$db->create_tables()` call from main plugin file
- **Impact:** Cleaner activation process, no wasted time
- **Files Changed:**
  - `yolo-yacht-search.php`

## New Features

### Two Separate Sync Buttons in Admin

The admin interface now has two distinct sync sections:

**1. Yacht Database Sync (Red)**
- Syncs yacht data only (no prices)
- Fast operation (30-60 seconds)
- Updates yacht details, images, specs, equipment, extras
- Shows last yacht sync timestamp

**2. Price Sync (Blue)**
- Syncs prices only (separate operation)
- Processes 12 weeks in 4-week chunks
- Shows last price sync timestamp
- Recommended to run weekly

**Files Changed:**
- `admin/partials/yolo-yacht-search-admin-display.php`
- `admin/class-yolo-ys-admin.php`

## Technical Details

### New AJAX Handlers

Added separate AJAX handler for price sync:
- `wp_ajax_yolo_ys_sync_yachts` - Yacht sync only
- `wp_ajax_yolo_ys_sync_prices` - Price sync only (NEW)

### Sync Status Tracking

Added new option to track price sync separately:
- `yolo_ys_last_sync` - Last yacht sync timestamp
- `yolo_ys_last_price_sync` - Last price sync timestamp (NEW)

### Price Sync Algorithm

```php
// Sync next 12 weeks in 4-week increments (3 chunks)
Chunk 1: Week 0-4
Chunk 2: Week 4-8
Chunk 3: Week 8-12
```

Each chunk is processed separately with error handling, so if one chunk fails, the others still process successfully.

## API Endpoint Analysis

Documented complete `/prices` endpoint parameters from Swagger:

**Required:**
- `dateFrom` (yyyy-MM-ddTHH:mm:ss)
- `dateTo` (yyyy-MM-ddTHH:mm:ss)

**Optional:**
- `companyId` (array<integer>)
- `yachtId` (array<integer>)
- `productName` (string: bareboat, crewed, cabin, etc.)
- `currency` (string)
- `tripDuration` (array<integer>)
- `country` (array<string>)

**See:** `PRICES-ENDPOINT-ANALYSIS.md` for full documentation

## Testing Results

### API Performance Tests
- Company 7850 (YOLO) prices: 0.5-1.5 seconds per request
- No price data currently exists for company 7850 in API
- All parameter combinations tested and validated

### Sync Performance
- Yacht sync: ~30-60 seconds (unchanged)
- Price sync: ~1-2 minutes for 12 weeks across 4 companies
- No more hanging or infinite spinners

## Files Modified

1. `yolo-yacht-search.php` - Version bump, removed duplicate table creation
2. `includes/class-yolo-ys-sync.php` - Separated sync methods, added chunking
3. `includes/class-yolo-ys-booking-manager-api.php` - Increased timeout to 60s
4. `admin/class-yolo-ys-admin.php` - Added price sync AJAX handler
5. `admin/partials/yolo-yacht-search-admin-display.php` - Two separate sync buttons

## New Files

1. `PRICES-ENDPOINT-ANALYSIS.md` - Complete API documentation
2. `CHANGELOG-v1.5.6.md` - This file

## Recommendations

1. **Run Yacht Sync First:** Always sync yachts before syncing prices
2. **Weekly Price Updates:** Run price sync weekly to keep data fresh
3. **Monitor Logs:** Check error logs if sync fails for any company
4. **Test Thoroughly:** Test both sync operations after updating

## Known Issues

- No price data currently exists for company 7850 in the Booking Manager API
- Price carousel on yacht details page not yet implemented (future feature)

## Upgrade Notes

- **Safe to upgrade from v1.5.5**
- No database schema changes
- No data migration required
- Simply upload and activate the new version

## Credits

- Bug analysis and recommendations from ChatGPT
- Swagger API documentation from MMK Systems
- Testing and implementation by Manus AI

---

**Version:** 1.5.6  
**Previous Version:** 1.5.5  
**Next Steps:** Implement search functionality (top priority)
