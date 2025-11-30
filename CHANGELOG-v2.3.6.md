# Version 2.3.6 - Critical API Parsing Fix

## Release Date
November 30, 2025

## Critical Fix

### API Response Parsing Bug (FIXED)
**Problem:** The Booking Manager API returns data wrapped in a `value` property:
```json
{
    "value": [
        { "id": 123, "name": "Strawberry", ... },
        { "id": 456, "name": "Lemon", ... }
    ],
    "Count": 3
}
```

But the code was returning the WHOLE object (`$result['data']`), not just the `value` array. This caused:
- ❌ Yacht sync to fail completely (iterating over wrong data structure)
- ❌ Equipment sync to fail
- ❌ Error message: "Failed to sync yachts"

**Solution:**
- Fixed `get_yachts_by_company()` to extract `value` array from API response
- Fixed `get_equipment_catalog()` to extract `value` array from API response
- Added fallback for direct array responses

**Result:** 
- ✅ Yacht sync now works! (Successfully synced 20 yachts from 4 companies)
- ✅ Equipment sync now works!
- ✅ All API calls parse responses correctly

## Technical Changes

### Files Modified
- `includes/class-yolo-ys-booking-manager-api.php`
  - Fixed `get_yachts_by_company()` method (lines 142-149)
  - Fixed `get_equipment_catalog()` method (lines 129-136)

## Testing
- ✅ Yacht sync: 20 yachts synced successfully from 4 companies
- ✅ Equipment sync: Working correctly
- ✅ All API responses parsed correctly

## Credits
Bug identified by Cursor AI analysis - thank you!

## Previous Fixes (v2.3.5)
- Fixed live API date format (yyyy-MM-ddTHH:mm:ss)
- Fixed price carousel auto-update on page load
- Fixed search box default to "All types"
