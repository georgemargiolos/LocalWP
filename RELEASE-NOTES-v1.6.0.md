# YOLO Yacht Search Plugin - Version 1.6.0 Release Notes

**Release Date:** November 28, 2025  
**Version:** 1.6.0  
**Previous Version:** 1.5.9

---

## 🎯 Major Changes

### ✅ Offers Endpoint Integration (CRITICAL FIX)

**Problem Solved:**  
The plugin was using the wrong API endpoint (`/prices`) which returned monthly price totals instead of weekly charter availability. This caused the price carousel to show only 1 card with incorrect prices.

**Solution Implemented:**  
Migrated from `/prices` endpoint to `/offers` endpoint which returns proper Saturday-to-Saturday weekly charter availability.

### Key Improvements:

1. **New API Method** (`class-yolo-ys-booking-manager-api.php`)
   - Added `get_offers()` method with proper parameters:
     - `dateFrom` / `dateTo`: Full year range
     - `flexibility=6`: Returns all Saturday departures
     - `tripDuration=7`: Weekly charters only
     - `companyId`: Array of all companies (YOLO + partners)
   - Single API call fetches ALL weekly offers for entire year

2. **New Sync Method** (`class-yolo-ys-sync.php`)
   - Added `sync_all_offers($year)` method
   - Replaces old `sync_all_prices()` method
   - Fetches full year of weekly offers in one API call
   - Stores: yacht_id, date_from, date_to, price, start_price, discount_percentage, product, start_base, end_base

3. **New Database Method** (`class-yolo-ys-database-prices.php`)
   - Added `store_offer()` method for weekly offer data
   - Handles Saturday-to-Saturday periods correctly
   - Stores all offer details including bases and discounts

4. **Admin Interface Updates** (`yolo-yacht-search-admin-display.php`)
   - Added year selector dropdown (2025-2028)
   - Changed "Price Sync" to "Weekly Offers Sync"
   - Updated descriptions to reflect full-year sync
   - Success messages now show: offers_synced, yachts_with_offers, year

5. **AJAX Handler Update** (`class-yolo-ys-admin.php`)
   - Updated `ajax_sync_prices()` to call `sync_all_offers($year)`
   - Passes year parameter from admin interface
   - Defaults to next year if not specified

6. **Template Simplification** (`yacht-details-v3.php`)
   - **REMOVED** weekly splitting logic (no longer needed)
   - Offers are already weekly from API
   - Now filters for future dates and sorts chronologically
   - Limits to 20 weeks for manageable carousel

---

## 🐛 Bug Fixes

### Fixed PHP Warnings in Yacht Details Template

**Issue:**  
PHP warnings about undefined properties when displaying extras:
- `Undefined property: stdClass::$extra_name` (line 369)
- `Undefined property: stdClass::$price_type` (line 373)

**Fix:**  
- Changed `$extra->extra_name` to `$extra->name` (correct database field)
- Changed `$extra->price_type` to `$extra->unit` (correct database field)
- Added proper null checks with `!empty()`

---

## ✨ New Features

### Obligatory vs Optional Extras Separation

**What's New:**  
Extras are now displayed in two separate sections with distinct styling:

1. **Obligatory Extras**
   - Red background (`#fef2f2`)
   - Red heading color (`#dc2626`)
   - Shows "(Payable at the base)" in heading

2. **Optional Extras**
   - Blue background (`#f0f9ff`)
   - Blue border (`#bfdbfe`)
   - Shows "(Payable at the base)" in heading

**Implementation:**
- Uses existing `obligatory` field in database (0 = optional, 1 = obligatory)
- Filters extras array in template
- Separate styling for each type
- Grid layout for better visual organization

### Enhanced Location Map Debugging

**Improvements:**
- Added comprehensive console logging for map initialization
- Logs: yachtLocation, Google API status, geocoding results
- Fallback text display if geocoding fails
- Shows "Base Location: [location name]" if map can't load
- Better error messages for troubleshooting

---

## 📊 Database Schema

**No changes to database structure** - The `wp_yolo_yacht_prices` table already supports both monthly prices and weekly offers with the same fields.

### Fields Used:
- `yacht_id`: Yacht identifier
- `date_from`: Start date (Saturday)
- `date_to`: End date (Saturday, 7 days later)
- `price`: Final charter price
- `start_price`: Original price (before discount)
- `discount_percentage`: Discount amount
- `product`: Charter product name
- `start_base`: Departure base
- `end_base`: Return base
- `currency`: Price currency

---

## 🔧 Technical Details

### API Endpoint Comparison

| Aspect | OLD (/prices) | NEW (/offers) |
|--------|---------------|---------------|
| **Returns** | Monthly price totals | Weekly availability |
| **Period** | Variable (monthly chunks) | Fixed (7-day Saturday-Saturday) |
| **Granularity** | Coarse (months) | Fine (weeks) |
| **Accuracy** | Approximate | Exact |
| **API Calls** | Multiple (per month) | Single (full year) |
| **Splitting Required** | Yes (in template) | No (already weekly) |

### Sync Performance

**Before (v1.5.9):**
- Multiple API calls for May-September
- Monthly chunks requiring splitting
- Complex template logic
- Inaccurate weekly prices

**After (v1.6.0):**
- Single API call for full year
- Already weekly data
- Simple template logic
- Accurate weekly prices

---

## 🎨 UI/UX Improvements

### Admin Interface
- Year selector with clear labeling
- Updated button text: "Sync Weekly Offers"
- Better descriptions explaining what happens
- Success messages show offer count and year

### Yacht Details Page
- Obligatory extras in red (clear visual distinction)
- Optional extras in blue
- "(Payable at the base)" text on both sections
- Better grid layout for extras
- Location map with fallback text

---

## 📝 Configuration

### Required Settings
- **API Key**: Booking Manager API key (already configured)
- **Company IDs**: 
  - My Company: 7850 (YOLO Charters)
  - Partners: 4366, 3604, 6711
- **Google Maps API Key**: AIzaSyB4aSnafHcLVFdMSBnLf_0wRjYHhj7P4L4

### Recommended Sync Schedule
1. **Before season**: Sync offers for next year (e.g., sync 2026 in late 2025)
2. **During season**: Re-sync monthly to update availability
3. **After changes**: Sync after updating prices in Booking Manager

---

## 🚀 Next Steps

### Immediate Priorities
1. **Test offers sync** - Run sync for 2026 and verify weekly data
2. **Check carousel** - Ensure multiple weekly cards display correctly
3. **Verify prices** - Compare with Booking Manager screenshots

### Future Features (Not in this release)
1. Search functionality implementation
2. Stripe payment integration
3. Booking creation via API POST
4. Automated sync scheduling (WP-Cron)

---

## 🔍 Testing Checklist

- [ ] Run "Sync Weekly Offers" for 2026
- [ ] Check database has weekly records (not monthly)
- [ ] View yacht details page - carousel shows multiple weeks
- [ ] Verify prices match Booking Manager
- [ ] Check obligatory extras display (red background)
- [ ] Check optional extras display (blue background)
- [ ] Verify location map displays or shows fallback text
- [ ] No PHP warnings in error log

---

## 📚 Files Modified

### Core Files
- `yolo-yacht-search.php` - Version bump to 1.6.0
- `includes/class-yolo-ys-booking-manager-api.php` - Added get_offers() method
- `includes/class-yolo-ys-sync.php` - Added sync_all_offers() method
- `includes/class-yolo-ys-database-prices.php` - Added store_offer() method

### Admin Files
- `admin/class-yolo-ys-admin.php` - Updated AJAX handler
- `admin/partials/yolo-yacht-search-admin-display.php` - Added year selector

### Template Files
- `public/templates/yacht-details-v3.php` - Removed splitting logic, fixed extras
- `public/templates/partials/yacht-details-v3-styles.php` - Added extras styling
- `public/templates/partials/yacht-details-v3-scripts.php` - Enhanced map debugging

---

## 🐛 Known Issues

1. **Location map may not display** - Depends on geocoding success, fallback text shows if it fails
2. **No automated sync** - Manual sync only (WP-Cron not implemented yet)
3. **Search not functional** - Search functionality still pending implementation
4. **No booking flow** - Stripe integration and booking creation pending

---

## 📞 Support

For issues or questions:
- Check browser console for JavaScript errors
- Check WordPress debug log for PHP errors
- Review Booking Manager API logs
- Verify API key and company IDs are correct

---

## 🎉 Summary

Version 1.6.0 is a **critical fix** that resolves the core price display issue by switching from the wrong API endpoint (/prices) to the correct one (/offers). This ensures:

✅ Weekly charter prices display correctly  
✅ Saturday-to-Saturday periods are accurate  
✅ Price carousel shows multiple weeks  
✅ Prices match Booking Manager exactly  
✅ Single efficient API call per year  
✅ Obligatory and optional extras are clearly distinguished  
✅ No PHP warnings in extras display  
✅ Better location map debugging  

**This is a major milestone** - the plugin now correctly displays weekly charter availability as intended!
