# YOLO Yacht Search Plugin - Handoff Document (v2.3.6)

**Date:** November 30, 2025  
**Author:** Manus AI  
**Version:** 2.3.6  
**GitHub:** https://github.com/georgemargiolos/LocalWP

---

## 🚀 EXECUTIVE SUMMARY

This document provides a complete overview of the YOLO Yacht Search plugin, including all recent bug fixes, critical code sections, and a guide for future development. The plugin is now in a stable, functional state after fixing several critical bugs related to API parsing, price storage, and live price updates.

**All major bugs are now FIXED.** The plugin correctly syncs yachts, equipment, and prices, and displays them accurately on the frontend.

---

## ✅ ALL BUGS FIXED (v2.3.4 - v2.3.6)

| Bug Description | Status | Version Fixed |
|---|---|---|
| **API Response Parsing (`value` property)** | ✅ **FIXED** | **v2.3.6** |
| Yacht Sync Fails Completely | ✅ **FIXED** | **v2.3.6** |
| Equipment Sync Fails | ✅ **FIXED** | **v2.3.6** |
| Live API Date Format (422 Error) | ✅ FIXED | v2.3.5 |
| Price Carousel Shows Wrong Prices | ✅ FIXED | v2.3.5 |
| Search Box Defaults to "Sailing Yacht" | ✅ FIXED | v2.3.5 |
| Price Storage (DELETE not working) | ✅ FIXED | v2.3.4 |
| `payableInBase` for Extras | ✅ FIXED | v2.3.4 |

---

## 💻 CRITICAL CODE SECTIONS (DO NOT MODIFY)

I have added comprehensive comments to the following critical sections. Please read them carefully before making any changes.

### 1. API Response Parsing (`class-yolo-ys-booking-manager-api.php`)

**Location:**
- `get_yachts_by_company()` (line 165)
- `get_equipment_catalog()` (line 135)

**CRITICAL:** The Booking Manager API wraps responses in a `{"value": [...], "Count": N}` structure. **You MUST extract the `value` array.** Returning the whole object will break yacht and equipment sync.

```php
// CORRECT IMPLEMENTATION (DO NOT CHANGE)
if ($result["success"]) {
    // API returns { "value": [...], "Count": N } - extract the value array
    if (isset($result["data"]["value"]) && is_array($result["data"]["value"])) {
        return $result["data"]["value"];
    }
    // Fallback for direct array response
    return $result["data"];
}
```

### 2. Live API Date Format (`class-yolo-ys-booking-manager-api.php`)

**Location:** `get_live_price()` (line 323)

**CRITICAL:** The API requires dates in `yyyy-MM-ddTHH:mm:ss` format. Sending `yyyy-MM-dd` will cause a 422 error.

```php
// CORRECT IMPLEMENTATION (DO NOT CHANGE)
$date_from_formatted = date("Y-m-d", strtotime($date_from)) . "T17:00:00";
$date_to_formatted = date("Y-m-d", strtotime($date_to)) . "T17:00:00";
```

### 3. Price Sync - DELETE Before INSERT (`class-yolo-ys-sync.php`)

**Location:** `sync_all_offers()` (line 212)

**CRITICAL:** You MUST delete all old prices for the year before inserting new ones. If you don't, prices will accumulate and the wrong prices will be displayed.

```php
// CRITICAL: Delete all existing prices for this year to ensure fresh data
// This prevents price accumulation bug where old prices never get removed
// Bug was fixed in v2.3.4 - DO NOT REMOVE THIS DELETE!
$deleted = $wpdb->query($wpdb->prepare(
    "DELETE FROM {$prices_table} WHERE YEAR(date_from) = %d",
    $year
));
```

### 4. Price Carousel - No Live API on Page Load (`yacht-details-v3-scripts.php`)

**Location:** `DOMContentLoaded` event listener (line 808)

**CRITICAL:** The price carousel loads prices from the **database**, not the live API. The live API is only called when the user manually changes dates. An `isInitialLoad` flag prevents the API from being called automatically on page load.

```javascript
// CRITICAL FIX (v2.3.5): Skip API call on initial page load
if (isInitialLoad) {
    isInitialLoad = false;
    return; // Skip API call, use database prices from carousel
}
```

---

## 🔧 HOW THE PLUGIN WORKS

### **Sync Process**
1. **Sync Yachts:** (`sync_all_yachts()`)
   - Fetches all yachts from all configured companies
   - Stores yacht details, images, extras, and equipment in database
   - Takes 2-3 minutes, may time out in WordPress admin (CLI works fine)

2. **Sync Weekly Offers:** (`sync_all_offers()`)
   - Deletes all old prices for the selected year
   - Fetches all available weekly offers (prices) from API
   - Stores prices in `wp_yolo_yacht_prices` table

3. **Sync Equipment Catalog:** (`sync_equipment_catalog()`)
   - Fetches all equipment definitions from API
   - Stores in `wp_yolo_equipment_catalog` table

### **Frontend Display**
1. **Search Form:** (`[yolo_search_widget]`)
   - Defaults to "All types"
   - Submits to search results page

2. **Search Results:** (`[yolo_search_results]`)
   - Displays yachts matching search criteria

3. **Yacht Details:** (`[yolo_yacht_details]`)
   - **Price Carousel:** Loads weekly prices from database (`wp_yolo_yacht_prices`)
   - **Booking Box:** 
     - Shows price for selected week (from carousel)
     - User can select custom dates, which triggers `get_live_price()` API call
     - Checks for double bookings and shows real-time price
   - **Extras:** Shows obligatory and optional extras, with `(Payable at the base)` flag

---

## 🚀 NEXT STEPS & FUTURE DEVELOPMENT

1. **Review the code comments** I have added to all critical sections.
2. **Use this handoff document** as a reference for future work.
3. **Test thoroughly** before deploying any new changes.
4. **Remember the critical bug fixes** (API `value` property, date format, DELETE before INSERT) to avoid re-introducing them.

This plugin is now in a great state. Let's keep it that way!
