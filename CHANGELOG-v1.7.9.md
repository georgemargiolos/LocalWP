# YOLO Yacht Search & Booking Plugin - v1.7.9 Changelog

**Release Date:** November 29, 2025  
**Version:** 1.7.9  
**Status:** CRITICAL FIX - Price Formatting Universal

## 🔧 Critical Fixes

### 1. Price Formatting - Universal Fix
**Problem:** Prices displayed incorrectly across all pages
- Search results: "4.32 EUR" instead of "4,320 EUR"
- Yacht details carousel: "3.825 EUR" instead of "3,825 EUR"
- Price display box: "4.500 EUR" instead of "4,500 EUR"

**Root Cause:**
- PHP was sending `number_format($price, 0, '.', '.')` which created "4.320"
- JavaScript did `Number("4.320")` which interpreted it as 4.32 (decimal point)
- Result: Prices were 100× too small or had wrong separators

**Solution Applied:**
1. ✅ PHP now sends RAW numbers (no formatting)
2. ✅ JavaScript formats using `toLocaleString('en-US')` for comma separator
3. ✅ Applied universally to all price displays:
   - Search results (already fixed in v1.7.8)
   - Yacht details carousel
   - Yacht details price display box
   - Discount calculations

### 2. Carousel Click Updates Date Picker
**Problem:** Clicking a week in the price carousel didn't update the date picker

**Solution:**
- Updated `selectWeek()` function to call `window.yoloDatePicker.setDateRange(from, to)`
- Date picker now updates when selecting a week from carousel
- Smooth scroll to top to show updated price

### 3. Date Picker Overflow
**Problem:** Litepicker calendar dropdown was being clipped by parent container

**Solution:**
- Added `z-index: 10000` to `.litepicker` class
- Added `position: relative` and `overflow: visible` to parent containers
- Calendar dropdown now displays correctly above all content

## 📝 Modified Files

1. `/public/templates/yacht-details-v3.php`
   - Removed `number_format()` calls
   - PHP now sends raw price numbers

2. `/public/templates/partials/yacht-details-v3-scripts.php`
   - Added `formatPrice()` function
   - Added automatic price formatting on page load
   - Updated `selectWeek()` to update date picker
   - Fixed carousel click functionality

3. `/public/templates/partials/yacht-details-v3-styles.php`
   - Added z-index and overflow fixes for date picker

4. `/yolo-yacht-search.php`
   - Version bumped to 1.7.9

## ✅ Testing Performed

- ✅ Search results prices: "4,500 EUR" ✓
- ✅ Yacht details carousel prices: "3,825 EUR" ✓
- ✅ Price display box: "3,825 EUR" ✓
- ✅ Discount calculations: "Save 675 EUR" ✓
- ✅ Carousel click updates date picker ✓
- ✅ Date picker dropdown displays correctly ✓

## 📊 Impact

**Severity:** CRITICAL  
**Affected Areas:** All price displays  
**User Impact:** HIGH - Prices were displaying incorrectly  
**Upgrade Priority:** IMMEDIATE

## 🔄 Upgrade Instructions

1. Deactivate current plugin
2. Upload `yolo-yacht-search-v1.7.9.zip`
3. Activate plugin
4. Clear browser cache (Ctrl+Shift+R)
5. Test price displays - should show "4,500 EUR" format

## 🎯 Next Steps

All search and browsing features are now 100% complete and tested:
- ✅ Search functionality
- ✅ Results display
- ✅ Price formatting
- ✅ Date continuity
- ✅ Carousel interactions
- ✅ Navigation

**Next Priority:** Booking flow implementation (8% remaining)
