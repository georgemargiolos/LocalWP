# YOLO Yacht Search - Version 1.5.3 Changelog

**Release Date:** November 28, 2025  
**Status:** CRITICAL BUG FIX

## 🚨 Critical Fix: Sync Performance Issue

### Problem
The yacht sync operation in v1.5.2 would hang or take extremely long (1+ hours) when clicking "Sync All Yachts Now". The sync appeared to freeze with the message "Syncing yachts... This may take a minute."

### Root Cause
The `get_prices()` method in `includes/class-yolo-ys-booking-manager-api.php` was using the wrong API parameter name:

**INCORRECT (v1.5.2):**
```php
$params = array(
    'company'  => $company_id,  // ❌ WRONG PARAMETER NAME
    'dateFrom' => $date_from,
    'dateTo'   => $date_to,
);
```

**CORRECT (v1.5.3):**
```php
$params = array(
    'companyId' => $company_id,  // ✅ CORRECT PARAMETER NAME
    'dateFrom'  => $date_from,
    'dateTo'    => $date_to,
);
```

### Impact
Using `company` instead of `companyId` caused the Booking Manager API to **ignore the company filter** and return price data for **EVERY COMPANY** in their entire system (potentially thousands of boats). This resulted in:

- Massive data downloads (gigabytes instead of megabytes)
- Sync operations taking 1+ hours or timing out
- Potential PHP memory exhaustion
- Database bloat from storing irrelevant price data

### Solution
Changed the parameter name from `company` to `companyId` to match the API specification and other API calls in the plugin (like `search_offers()` and `get_yachts_by_company()`).

Now the sync:
- Only fetches prices for the 4 configured companies (7850, 4366, 3604, 6711)
- Completes in 1-2 minutes instead of 1+ hours
- Uses minimal bandwidth and memory

## 🔧 Additional Improvements

### Timeout Protection
Added timeout extension to `sync_all_yachts()` method:
```php
set_time_limit(300); // 5 minutes
ini_set('max_execution_time', 300);
```

This ensures the sync has enough time to complete even on slower servers.

## 📝 Files Changed

1. **includes/class-yolo-ys-booking-manager-api.php**
   - Line 60: Changed `'company'` to `'companyId'`

2. **includes/class-yolo-ys-sync.php**
   - Lines 19-21: Added timeout extension

3. **yolo-yacht-search.php**
   - Line 6: Updated version to 1.5.3
   - Line 23: Updated version constant to 1.5.3

## ⚠️ Upgrade Instructions

### For Users Currently Stuck on v1.5.2:

1. **Stop the current sync** by refreshing the admin page
2. **Deactivate** the plugin
3. **Delete** the plugin files
4. **Upload** v1.5.3
5. **Activate** the plugin
6. **Click "Sync All Yachts Now"** - it should complete in 1-2 minutes

### Clean Database (Optional but Recommended):

If you ran the sync in v1.5.2, your database may contain price data for thousands of boats you don't need. To clean it:

```sql
-- Backup first!
-- Then run in phpMyAdmin or MySQL:
TRUNCATE TABLE wp_yolo_yacht_prices;
```

Then run the sync again with v1.5.3 to fetch only the correct prices.

## 🧪 Testing Performed

- ✅ Sync completes in under 2 minutes
- ✅ Only 4 companies' data is fetched
- ✅ Prices display correctly on yacht details pages
- ✅ No timeout errors
- ✅ Memory usage remains normal

## 🙏 Credits

Bug identified and documented by **George Margiolos** - excellent debugging work!

## 📊 Version Comparison

| Version | Sync Time | Companies Fetched | Status |
|---------|-----------|-------------------|--------|
| v1.5.2 | 1+ hours (timeout) | ALL companies (thousands) | ❌ BROKEN |
| v1.5.3 | 1-2 minutes | 4 companies (20 boats) | ✅ FIXED |

## 🔜 Next Steps

With the sync now working properly, we can proceed with:
1. Implementing search functionality
2. Adding Stripe payment integration
3. Creating booking API integration
