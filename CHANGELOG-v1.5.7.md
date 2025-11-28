# YOLO Yacht Search & Booking - Version 1.5.7

**Release Date:** November 28, 2025  
**Type:** Bug Fix + UI Improvement

---

## 🔧 Critical Fixes

### 1. **Fixed Price Sync Date Range (CRITICAL)**
**Problem:** Price sync was looking at the wrong time period (next 12 weeks from today: Nov 2025 - Feb 2026) instead of the peak charter season where actual price data exists (May-September 2026).

**Solution:**
- Changed price sync to target **peak season (May-September)** of the next charter year
- Automatically determines the correct year based on current date:
  - If current month > September → targets next year's peak season
  - If current month < May → targets current year's peak season
  - If in peak season → targets current year
- Syncs in **monthly chunks** (5 months: May, June, July, August, September)
- More efficient than weekly chunks for seasonal pricing

**Impact:** Price sync will now actually retrieve and display prices since it's looking at the correct date range where data exists.

---

## 🎨 UI Improvements

### 2. **Moved Price Carousel to Full Width Below Images**
**Change:** Restructured yacht details page layout:
- **Before:** Price carousel was in right sidebar with booking section
- **After:** Price carousel now displays at full width below the image carousel
- Booking section (date picker, buttons, quote form) remains in right sidebar

**Benefits:**
- Better visual hierarchy
- More space to display weekly prices
- Cleaner separation between pricing display and booking actions

---

## 📝 Admin Interface Updates

### 3. **Updated Price Sync Description**
Changed admin panel text to accurately reflect what the price sync does:
- **Old:** "Fetches prices for the next 12 weeks in 4-week chunks"
- **New:** "Fetches prices for peak season (May-September) in monthly chunks"
- Updated recommendation: "Run this before peak season and monthly during season"

---

## 📋 Technical Details

### Files Modified:
1. `includes/class-yolo-ys-sync.php` - Updated `sync_all_prices()` method
   - Lines 112-143: New date range logic for peak season
   - Lines 185-200: Updated success messages to show target year
   
2. `public/templates/yacht-details-v3.php` - Restructured layout
   - Moved price carousel from sidebar to full-width section below images
   - Lines 115-159: Price carousel now in separate full-width section
   
3. `admin/partials/yolo-yacht-search-admin-display.php` - Updated descriptions
   - Lines 72-77: Updated price sync description text

4. `yolo-yacht-search.php` - Version bump to 1.5.7

---

## 🧪 Testing Performed

### API Testing:
- ✅ Tested `/prices` endpoint with July 2026 dates
- ✅ Confirmed 3 price records exist for company 7850
- ✅ Verified API returns data in ~1 second
- ✅ Tested various date ranges to identify the issue

### Date Range Logic:
- ✅ Verified peak season detection works correctly
- ✅ Confirmed monthly chunking (5 chunks for May-Sep)
- ✅ Tested year rollover logic (current month > 9)

---

## 📦 Deployment

**File:** `yolo-yacht-search-v1.5.7.zip`

**Installation:**
1. Deactivate current plugin version
2. Delete old plugin folder
3. Upload and extract v1.5.7
4. Activate plugin
5. **Run "Sync Prices Now"** to populate price database with peak season data

---

## 🔍 What to Expect After Upgrade

1. **Price Sync Will Work:** When you click "Sync Prices Now", it will fetch prices for May-September 2026
2. **Prices Will Display:** The weekly price carousel will show on yacht details pages
3. **Correct Date Range:** Admin messages will show "peak season 2026" instead of "12 weeks"

---

## 🚨 Known Issues

1. **Google Maps API Key:** Still shows placeholder `YOUR_GOOGLE_MAPS_API_KEY` in template
   - Location: `yacht-details-v3.php` line 353
   - **Action Needed:** Replace with actual Google Maps API key

2. **Yacht Sync May Be Broken:** User reported yacht sync stopped working (needs investigation)
   - This was not addressed in v1.5.7
   - **Priority:** Fix in next version

---

## 📊 Version Comparison

| Feature | v1.5.6 | v1.5.7 |
|---------|--------|--------|
| Price Sync Date Range | Next 12 weeks from today | Peak season (May-Sep) |
| Price Sync Chunks | 3 chunks (4 weeks each) | 5 chunks (1 month each) |
| Price Carousel Location | Right sidebar | Full width below images |
| Admin Description | "Next 12 weeks" | "Peak season (May-September)" |
| Prices Display | ❌ (wrong date range) | ✅ (correct date range) |

---

## 🎯 Next Steps

1. **Test price sync** on live site
2. **Verify prices display** on yacht details pages
3. **Fix yacht sync** if still broken
4. **Add Google Maps API key** for location display
5. **Implement search functionality** (still top priority)

---

**Generated:** November 28, 2025 22:03 GMT+2
