# Version 2.3.7 - Critical API Response Parsing Fixes

## Release Date
November 30, 2025

## Critical Fixes

### 🐛 Bug #1: `get_offers()` API Response Parsing (FIXED)
**Problem:** The `/offers` endpoint returns data wrapped in a `value` property, but `get_offers()` was returning the whole object.

**Impact:**
- Weekly offers sync could fail or store incorrect data
- Price carousel might show wrong prices

**Solution:**
```php
if ($result['success']) {
    // API returns { "value": [...], "Count": N } - extract the value array
    if (isset($result['data']['value']) && is_array($result['data']['value'])) {
        return $result['data']['value'];
    }
    // Fallback for direct array response
    return $result['data'];
}
```

---

### 🐛 Bug #2: `get_live_price()` API Response Parsing (FIXED)
**Problem:** The `/offers` endpoint returns `{"value": [...], "Count": N}`, but the code was trying to access `$result['data'][0]` directly.

**Impact:**
- ❌ **Price carousel flashing wrong prices** - This was the root cause!
- Live price checks returned wrong data or NULL
- "Book Now" button showed incorrect prices

**Solution:**
```php
// Extract value array first
$offers_array = array();
if ($result['success'] && isset($result['data'])) {
    if (isset($result['data']['value']) && is_array($result['data']['value'])) {
        $offers_array = $result['data']['value'];
    } elseif (is_array($result['data']) && isset($result['data'][0])) {
        $offers_array = $result['data'];
    }
}

if ($result['success'] && count($offers_array) > 0) {
    $offer = $offers_array[0];
    // ... rest of processing
}
```

---

### 🐛 Bug #3: Duplicate Doc Comment (FIXED)
**Problem:** Orphaned doc comment "Get all yachts for a company" before `get_equipment_catalog()` method.

**Impact:**
- Minor: Confusing documentation

**Solution:** Removed the orphaned comment block.

---

### 🐛 Bug #4: `search_offers()` Inconsistent Return Format (FIXED)
**Problem:** `search_offers()` returned the full result object `{ 'success' => true, 'data' => {...} }`, while other methods like `get_offers()` and `get_yachts_by_company()` return just the data array.

**Impact:**
- Inconsistent API - callers need to handle different return formats
- Could cause confusion and bugs in calling code

**Solution:** Made `search_offers()` consistent with other methods:
```php
$result = $this->make_request($endpoint, $query_params);

if ($result['success']) {
    // Extract value array
    if (isset($result['data']['value']) && is_array($result['data']['value'])) {
        return $result['data']['value'];
    }
    return $result['data'];
}

throw new Exception(isset($result['error']) ? $result['error'] : 'Failed to search offers');
```

Also updated `get_offers_cached()` to handle the new return format.

---

### 🐛 Bug #5: Search Box Defaulting to "Sailing Yacht" (FIXED)
**Problem:** Search box was showing "Sailing yacht" as the default selection instead of "All types" due to browser autocomplete.

**Impact:**
- User confusion - searches were filtered to sailing yachts only
- Inconsistent with intended default behavior

**Solution:**
- Added `autocomplete="off"` to both search form select elements
- Fixed block editor preview to show "All types" first

---

## Technical Changes

### Files Modified
1. **`includes/class-yolo-ys-booking-manager-api.php`**
   - Fixed `get_offers()` to extract `value` array (lines 91-98)
   - Fixed `get_live_price()` to extract `value` array (lines 339-353)
   - Removed duplicate doc comment (line 124-126)
   - Fixed `search_offers()` for consistency (lines 21-51)
   - Fixed `get_offers_cached()` to handle new return format (lines 445-454)

2. **`public/templates/search-form.php`**
   - Added `autocomplete="off"` to boat type select (line 13)

3. **`public/templates/search-results.php`**
   - Added `autocomplete="off"` to boat type select (line 15)

4. **`public/blocks/yacht-search/index.js`**
   - Fixed block editor preview to show "All types" first (line 16)

5. **`yolo-yacht-search.php`**
   - Updated version to 2.3.7 (lines 6, 23)

---

## Testing

### Verified Fixes
- ✅ `get_offers()` correctly extracts `value` array from API response
- ✅ `get_live_price()` correctly extracts `value` array from API response
- ✅ **Price carousel no longer flashes wrong prices**
- ✅ Search box defaults to "All types" (no autocomplete)
- ✅ All API methods now consistent in return format

### Recommended Testing
1. **Price Carousel:**
   - Visit yacht details page with URL params: `?yacht_id=XXX&dateFrom=2026-10-03&dateTo=2026-10-10`
   - Verify price carousel shows correct prices without flashing
   - Verify prices match the selected week

2. **Search Box:**
   - Clear browser cache/autocomplete
   - Visit search page
   - Verify "All types" is selected by default
   - Perform search and verify URL has empty `kind` parameter

3. **Weekly Offers Sync:**
   - Run "Sync Prices" from admin
   - Verify prices sync correctly without errors

4. **Live Price API:**
   - Visit yacht details page
   - Select custom dates
   - Verify correct price is displayed in booking box

---

## API Response Format Reference

All Booking Manager API endpoints return data in this format:

```json
{
    "value": [
        { ... item 1 ... },
        { ... item 2 ... }
    ],
    "Count": 2
}
```

### Endpoints Fixed

| Endpoint | Method | Status |
|----------|--------|--------|
| `/equipment` | `get_equipment_catalog()` | ✅ Fixed in v2.3.6 |
| `/yachts` | `get_yachts_by_company()` | ✅ Fixed in v2.3.6 |
| `/offers` | `get_offers()` | ✅ **Fixed in v2.3.7** |
| `/offers` | `get_live_price()` | ✅ **Fixed in v2.3.7** |
| `/offers` | `search_offers()` | ✅ **Fixed in v2.3.7** |

---

## Credits

**Bugs identified by:** Cursor AI  
**Fixed by:** Manus AI  
**Version:** 2.3.7  
**Date:** November 30, 2025

---

## Previous Fixes (v2.3.6)
- Fixed `get_yachts_by_company()` to extract `value` array
- Fixed `get_equipment_catalog()` to extract `value` array

## Previous Fixes (v2.3.5)
- Fixed live API date format (yyyy-MM-ddTHH:mm:ss)
- Fixed price carousel auto-update on page load
- Fixed search box default to "All types"

## Previous Fixes (v2.3.4)
- Fixed price storage (DELETE before INSERT)
- Fixed `payableInBase` for extras
