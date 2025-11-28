# YOLO Yacht Search Plugin - v1.0.3 Changelog

**Release Date:** November 28, 2025

## 🎨 Major Updates

### Yacht Card Design - Matching yolo-charters.com

Updated yacht cards to match the exact design of yolo-charters.com:

#### ✅ New Card Layout
- **Location display** with 📍 pin icon (e.g., "Preveza Main Port")
- **3-column specs grid** layout:
  - Cabins count
  - Built year with refit info
  - Length in feet
- **Removed description** from cards (was too long)
- **Cleaner, more compact design**

#### ✅ Database Schema Updates
- Added `home_base` field to store yacht location
- Added `refit_year` field to store refit year
- Added `parse_refit_year()` method to extract year from API's `yearNote` field
  - Parses "Refit 2026" → 2026
  - Parses "Refit: 2025" → 2025

#### ✅ API Field Mapping Fixes
- Fixed `year_of_build`: now uses `year` from API (was `yearOfBuild`)
- Fixed `draft`: now uses `draught` from API (was `draft`)
- Added `home_base`: uses `homeBase` from API
- Added `refit_year`: parsed from `yearNote` field

#### ✅ Display Improvements
- **Location**: Shows at top of card with pin icon
- **Year display**: Shows built year + refit info on same line
  - Example: "2008 Refit: 2026"
- **Length conversion**: Converts meters to feet automatically
  - Example: 13.61m → 45 ft
- **Specs grid**: Clean 3-column layout matching yolo-charters.com
- **DETAILS button**: Red button matching yolo-charters.com style

---

## 📋 What Changed

### Files Modified

1. **`includes/class-yolo-ys-database.php`**
   - Added `home_base` field to table schema
   - Fixed `year_of_build` mapping (year → year)
   - Fixed `draft` mapping (draught → draft)
   - Added `parse_refit_year()` helper method
   - Updated `store_yacht()` to save location and refit

2. **`public/templates/partials/yacht-card.php`**
   - Complete redesign matching yolo-charters.com
   - Added location display
   - Changed to 3-column specs grid
   - Removed description text
   - Added refit year display
   - Convert length to feet
   - Updated button styling

3. **`yolo-yacht-search.php`**
   - Updated version to 1.0.3

---

## 🔄 Migration Notes

### For Existing Installations

If you're upgrading from v1.0.2:

1. **Re-sync yacht data** to populate new fields:
   - Go to **YOLO Yacht Search** admin
   - Click **"Sync All Yachts Now"**
   - This will populate `home_base` and `refit_year` fields

2. **Database changes** are automatic:
   - Plugin will add `home_base` column on activation
   - No manual database changes needed

---

## 🎯 Card Display Comparison

### Before (v1.0.2)
```
┌─────────────────────┐
│     [Image]         │
├─────────────────────┤
│ Strawberry          │
│ Lagoon 440          │
│                     │
│ Year: 2008          │
│ Cabins: 4           │
│ Berths: 10          │
│ Length: 13.6m       │
│                     │
│ Solar Panels,       │
│ Espresso Coffee...  │
│                     │
│ [View Details →]    │
└─────────────────────┘
```

### After (v1.0.3) - Matches yolo-charters.com
```
┌─────────────────────┐
│     [Image]         │
├─────────────────────┤
│ 📍 Preveza Main Port│
│                     │
│ Strawberry          │
│ LAGOON 440          │
│                     │
│  4      2008 Refit  45 ft │
│Cabins  Built year  Length│
│                     │
│    [DETAILS]        │
└─────────────────────┘
```

---

## 🐛 Bug Fixes

1. **API field mapping errors** - Fixed incorrect field names
2. **Missing location data** - Now displays yacht base location
3. **Refit year not shown** - Now parses and displays refit information
4. **Length in wrong units** - Now converts meters to feet

---

## 📊 Data Now Stored

Each yacht card now displays:
- ✅ Location (homeBase from API)
- ✅ Yacht name
- ✅ Model name
- ✅ Number of cabins
- ✅ Built year
- ✅ Refit year (if available)
- ✅ Length in feet

---

## 🚀 Next Steps

After updating to v1.0.3:

1. Upload new plugin zip
2. Click "Sync All Yachts Now"
3. View "Our Fleet" page to see new card design
4. Verify location and refit info appears correctly

---

## 📝 Notes

- Description removed from cards to match yolo-charters.com
- Full description still available on yacht details page
- Cards are now more compact and scannable
- Design matches industry standard (yolo-charters.com)

---

**Version:** 1.0.3  
**Previous Version:** 1.0.2  
**Upgrade Required:** Yes (re-sync needed)  
**Breaking Changes:** No
