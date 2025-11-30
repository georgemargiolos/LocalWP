# Version 2.3.5 - Bug Fixes

## Release Date
November 30, 2025

## Bug Fixes

### 1. Live API Date Format (FIXED)
**Problem:** The `get_live_price` API call was sending dates in wrong format (`2026-04-18` instead of `2026-04-18T17:00:00`), causing 422 errors from Booking Manager API.

**Solution:**
- Fixed date format in `get_live_price` method to include time component
- API now sends dates as `yyyy-MM-ddTHH:mm:ss` as required

**Result:** ✅ Live price API calls now work correctly

### 2. Price Carousel Auto-Update (FIXED)
**Problem:** Price carousel was automatically calling live API on page load, overwriting correct database prices with failed/wrong API responses.

**Solution:**
- Added `isInitialLoad` flag to prevent automatic API call when page first loads
- Carousel now displays database prices correctly
- Live API only called when user manually changes dates

**Result:** ✅ Carousel shows correct prices from database

### 3. Search Box Default (FIXED)
**Problem:** Search box defaulted to "Sailing yacht" instead of "All types", limiting search results.

**Solution:**
- Changed default selection from "Sailing yacht" to "All types"
- Users now see all boat types by default

**Result:** ✅ Search shows all yachts by default

## Technical Changes

### Files Modified
- `includes/class-yolo-ys-booking-manager-api.php` - Fixed date format in get_live_price
- `public/templates/partials/yacht-details-v3-scripts.php` - Added flag to prevent initial API call
- `public/templates/search-form.php` - Changed default selection to "All types"

## Testing
- ✅ Live API date format tested and working
- ✅ Price carousel displays database prices correctly
- ✅ Search box defaults to "All types"
- ✅ Live availability check works when user manually selects dates

## Notes
- Yacht sync works correctly via CLI (20 yachts synced successfully)
- WordPress admin timeout for yacht sync is a server configuration issue, not a plugin bug
- Database prices remain accurate and match API responses
