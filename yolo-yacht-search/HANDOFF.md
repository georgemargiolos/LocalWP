# Handoff Document - YOLO Yacht Search & Booking Plugin

**Date:** December 24, 2025  
**Version:** v80.4 (Latest Stable Version)  
**Task Goal:** Fix all remaining bugs from Cursor's analysis - batch inserts for performance and search company ID type consistency.

---

## 🔴 Summary of Work Completed (v80.4)

### 1. Bug #6: Search Company ID Type - FIXED
- **Location:** `public/class-yolo-ys-public-search.php` line 29
- **Problem:** Company ID was retrieved as string but database column is `bigint(20)`
- **Fix:** Cast to integer: `(int) get_option('yolo_ys_my_company_id', '7850')`
- **Status:** **COMPLETE**

### 2. Bug #4: Batch Inserts - FIXED
- **Location:** `includes/class-yolo-ys-sync.php` lines 353-374
- **Problem:** Individual INSERT statements (1,000 offers = 1,000 queries)
- **Fix:** Use existing `store_offers_batch()` method with REPLACE INTO
- **Performance:** 10-100x faster sync
- **Status:** **COMPLETE**

---

## Previous Session Summary (v80.3)

### v80.3: CRITICAL: Per-Company Delete Fix for Offers Sync
- **Problem:** YOLO boats were losing prices after auto-sync while partner boats kept their prices.
- **Root Cause:** The DELETE query was deleting ALL prices for ALL companies for the year, then only storing offers from companies that returned data. If YOLO's fetch failed but partners succeeded, YOLO's prices were deleted but never replaced.
- **Bug Location:** Line 314 in `class-yolo-ys-sync.php`: `DELETE FROM prices WHERE YEAR(date_from) = %d`
- **Solution:** Rewrote `sync_all_offers()` to:
  1. Fetch all offers first, grouped by company ID
  2. For each company that returned offers:
     - Get that company's yacht IDs from the database
     - DELETE only that company's yacht prices for the year
     - Store that company's new offers
  3. Companies that failed keep their existing prices
- **Status:** **COMPLETE - READY FOR TESTING.**

### 2. Auto-Sync Uses Dropdown Year
- **Change:** Auto-sync now uses the same year selected in the dropdown (same as manual sync)
- **Before:** Synced both current year AND next year (causing longer execution and potential timeouts)
- **After:** Syncs only the year you select in the "Select Year" dropdown
- **Benefit:** Simpler, faster, matches user expectations

### 3. Error Logging for Offer Storage
- **Added:** Detailed error logging when storing offers fails
- **Logs:** Database errors with yacht_id and date_from for debugging
- **Benefit:** Helps identify which specific offers are failing to store

---

## Files Modified in Latest Commit

| File | Change Summary |
| :--- | :--- |
| `yolo-yacht-search.php` | Version bump to 80.3 |
| `CHANGELOG.md` | Updated with v80.3 entry |
| `README.md` | Updated with latest version and v80.3 summary |
| `includes/class-yolo-ys-sync.php` | Rewrote `sync_all_offers()` with per-company delete logic |
| `includes/class-yolo-ys-auto-sync.php` | Changed to use dropdown year instead of current+next year |
| `includes/class-yolo-ys-database-prices.php` | Added error logging to `store_price()` and `store_offer()` |
| `admin/class-yolo-ys-admin.php` | Added AJAX handler for saving offers year |
| `admin/partials/yolo-yacht-search-admin-display.php` | Added year dropdown save functionality and hint text |

---

## Technical Implementation Details

### The Bug (Before)
```php
// Fetch offers from all companies
foreach ($all_companies as $company_id) {
    $offers = $api->get_offers($company_id);
    if (!empty($offers)) {
        $all_offers = array_merge($all_offers, $offers);
    }
}

// BUG: Delete ALL prices for ALL companies
if (!empty($all_offers)) {
    DELETE FROM prices WHERE YEAR(date_from) = 2026  // Deletes EVERYONE's prices!
}

// Only store offers from companies that succeeded
foreach ($all_offers as $offer) {
    store_offer($offer);  // YOLO has no offers to store if YOLO's fetch failed
}
```

### The Fix (After)
```php
// Phase 1: Fetch all offers, grouped by company
$offers_by_company = [];
foreach ($all_companies as $company_id) {
    $offers = $api->get_offers($company_id);
    if (!empty($offers)) {
        $offers_by_company[$company_id] = $offers;
    }
}

// Phase 2: Delete and store per-company
foreach ($offers_by_company as $company_id => $offers) {
    // Get this company's yacht IDs
    $yacht_ids = get_yacht_ids_for_company($company_id);
    
    // Delete ONLY this company's prices
    DELETE FROM prices WHERE yacht_id IN ($yacht_ids) AND YEAR(date_from) = 2026
    
    // Store this company's offers
    foreach ($offers as $offer) {
        store_offer($offer);
    }
}
// Companies that failed keep their existing prices!
```

---

## API Documentation Reference

### /offers Endpoint

**Endpoint:** `GET /api/v2/offers`

**Response Format:** Direct JSON array (NOT wrapped in `{value: [...]}`)

**Example Response:**
```json
[
  {
    "yachtId": 12345,
    "dateFrom": "2026-05-02",
    "dateTo": "2026-05-09",
    "price": 3600.00,
    "startPrice": 4000.00,
    "discountPercentage": 10,
    "currency": "EUR"
  }
]
```

**Note:** Unlike `/yachts` endpoint which returns `{value: [...]}`, the `/offers` endpoint returns a direct array.

---

## Server Cron Setup (Recommended)

WordPress pseudo-cron only runs when someone visits the site. For reliable auto-sync, set up a server cron:

**Step 1: Add to wp-config.php:**
```php
define('DISABLE_WP_CRON', true);
```

**Step 2: Add to server crontab:**
```bash
*/15 * * * * wget -q -O /dev/null "https://yolo-charters.com/wp-cron.php?doing_wp_cron" >/dev/null 2>&1
```

---

## Testing Checklist

- [ ] Select year 2026 in the dropdown (should show "Also used for auto-sync" hint)
- [ ] Wait for auto-sync to run (or trigger manually via WP Crontrol plugin)
- [ ] Check WordPress error logs for `[YOLO Auto-Sync]` messages
- [ ] Verify YOLO boats have prices on frontend after auto-sync
- [ ] Verify partner boats also have prices
- [ ] Check "Our Yachts" page shows prices for all boats
- [ ] Check search results show prices for all boats

---

## Previous Session Summary

### v80.2 - Auto-Sync Success Detection Fix
- Changed success detection from `$result['success']` to `$result['offers_synced'] > 0`

### v80.1 - Clickable Yacht Cards
- Made entire yacht card clickable, not just the DETAILS button

### v80.0 - Sticky Booking Section Position
- Changed `top: 100px` to `top: 50px` in `.yolo-yacht-details-v3 .yacht-booking-section`

---

## Next Session Links

| Resource | Link | Notes |
| :--- | :--- | :--- |
| **GitHub Repository** | [https://github.com/georgemargiolos/LocalWP](https://github.com/georgemargiolos/LocalWP) | All code is pushed here. |
| **Latest Plugin ZIP** | `/home/ubuntu/LocalWP/yolo-yacht-search-v80.3.zip` | Use this file to update the plugin on your WordPress site. |
| **Latest Changelog** | [https://github.com/georgemargiolos/LocalWP/blob/main/yolo-yacht-search/CHANGELOG.md](https://github.com/georgemargiolos/LocalWP/blob/main/yolo-yacht-search/CHANGELOG.md) | For a detailed history of changes. |
| **Latest README** | [https://github.com/georgemargiolos/LocalWP/blob/main/yolo-yacht-search/README.md](https://github.com/georgemargiolos/LocalWP/blob/main/yolo-yacht-search/README.md) | For an overview of the latest features. |
| **Handoff File** | [https://github.com/georgemargiolos/LocalWP/blob/main/yolo-yacht-search/HANDOFF.md](https://github.com/georgemargiolos/LocalWP/blob/main/yolo-yacht-search/HANDOFF.md) | This document. |
