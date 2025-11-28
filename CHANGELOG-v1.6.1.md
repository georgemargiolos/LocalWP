# Changelog - Version 1.6.1

**Release Date:** November 28, 2025  
**Type:** Critical Bug Fixes (Based on ChatGPT Analysis)  
**Previous Version:** 1.6.0

---

## 📋 Overview

Version 1.6.1 applies all fixes recommended by ChatGPT's analysis of v1.6.0 to resolve the "Failed to sync prices/offers" error and other critical issues. This release also fixes UI bugs from user screenshots (Google Maps, price carousel, missing description).

---

## 🔧 Fixes Applied

### 1. **Missing Response Fields in sync_all_offers()** ✅

**Issue:** JavaScript expected `year` and `yachts_with_offers` in AJAX response but they were missing, causing undefined errors.

**Fix:**
- Added `year` and `yachts_with_offers` to the `$results` array initialization
- Implemented `$yachtOffersMap` array to track unique yachts with offers
- Updated success message to include yacht count

**Files Changed:**
- `includes/class-yolo-ys-sync.php` (lines 99-106, 157-164, 183-192)

**Code:**
```php
$results = array(
    'success' => false,
    'message' => '',
    'offers_synced' => 0,
    'yachts_with_offers' => 0,  // Added
    'year' => $year,             // Added
    'errors' => array()
);

// Track unique yachts
$yachtOffersMap = array();
foreach ($offers as $offer) {
    if (isset($offer['yachtId'])) {
        $yachtOffersMap[$offer['yachtId']] = true;
    }
    // ... store offer
}

$results['yachts_with_offers'] = count($yachtOffersMap);
```

---

### 2. **Outdated Option Name for Last Sync Time** ✅

**Issue:** `get_sync_status()` was reading `yolo_ys_last_price_sync` but `sync_all_offers()` was updating `yolo_ys_last_offer_sync`, so the "Last Offers Sync" timestamp never updated.

**Fix:**
- Changed `get_sync_status()` to read `yolo_ys_last_offer_sync` instead of `yolo_ys_last_price_sync`

**Files Changed:**
- `includes/class-yolo-ys-sync.php` (line 218)

**Code:**
```php
public function get_sync_status() {
    // ...
    $last_offer_sync = get_option('yolo_ys_last_offer_sync', null);  // Fixed
    
    return array(
        // ...
        'last_price_sync' => $last_offer_sync,
        'last_price_sync_human' => $last_offer_sync ? human_time_diff(...) : 'Never'
    );
}
```

---

### 3. **Incorrect tripDuration Parameter Format** ✅

**Issue:** `tripDuration` was passed as bare integer `7` instead of array `[7]` as required by API specification, potentially causing API errors.

**Fix:**
- Changed `tripDuration` from `7` to `array(7)` to match Swagger spec

**Files Changed:**
- `includes/class-yolo-ys-sync.php` (line 139)

**Code:**
```php
$offers = $this->api->get_offers(array(
    'companyId' => $all_companies,
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'tripDuration' => array(7),     // Fixed: now array instead of bare int
    'flexibility' => 6,
    'productName' => 'bareboat'
));
```

---

### 4. **Removed Unused Prototype Files** ✅

**Issue:** Files `class-yolo-ys-sync-new.php` and `class-yolo-ys-sync-offers.php` existed in `includes/` directory. If accidentally loaded, they would cause fatal parse errors.

**Fix:**
- Deleted both unused prototype files

**Files Removed:**
- `includes/class-yolo-ys-sync-new.php`
- `includes/class-yolo-ys-sync-offers.php`

---

### 5. **Updated Error Messages: "Prices" → "Offers"** ✅

**Issue:** Admin error message still said "Failed to sync prices" even though v1.6.0 uses offers endpoint.

**Fix:**
- Changed error message to "Failed to sync offers. Please try again."

**Files Changed:**
- `admin/partials/yolo-yacht-search-admin-display.php` (line 214)

**Code:**
```javascript
error: function() {
    $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> Failed to sync offers. Please try again.</p></div>');
}
```

---

## 🎨 UI Fixes (From User Screenshots)

### 6. **Price Carousel Showing Only 1 Week** ✅

**Issue:** CSS set `.price-slide` to `display: none` by default, only showing `.price-slide.active`, resulting in only 1 week visible instead of 4.

**Fix:**
- Changed `.price-slide` to `display: block`
- Removed `display: block` from `.price-slide.active`, keeping only border color change

**Files Changed:**
- `public/templates/partials/yacht-details-v3-styles.php` (lines 190-207)

**Result:** Now shows 4 weeks on desktop, 2 on tablet, 1 on mobile as designed.

---

### 7. **Missing Boat Description Section** ✅

**Issue:** Yacht details page had no description section at all.

**Fix:**
- Added description section after Quick Specs and before Location
- Added corresponding CSS styling

**Files Changed:**
- `public/templates/yacht-details-v3.php` (lines 262-270)
- `public/templates/partials/yacht-details-v3-styles.php` (lines 491-511)

**New Section Order:**
1. Yacht Header
2. Image Carousel + Booking Section
3. Weekly Offers Carousel
4. Quick Specs
5. **Description** ← Added here
6. Location (Google Maps)
7. Technical Characteristics
8. Equipment
9. Extras

**CSS Added:**
```css
.yacht-description-section {
    margin-bottom: 40px;
}

.yacht-description-content {
    font-size: 16px;
    line-height: 1.8;
    color: #374151;
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #1e3a8a;
}
```

---

### 8. **Google Maps Already Fixed in v1.6.0** ℹ️

**Status:** Google Maps API key is already configurable in admin settings (v1.6.0 feature).

**No changes needed** - Maps should work if API key is set in admin panel.

---

## 📊 Technical Summary

### ChatGPT's Recommendations Status

| Recommendation | Status | Line(s) |
|----------------|--------|---------|
| Add `year` and `yachts_with_offers` to response | ✅ Fixed | sync.php:99-106, 157-164, 183-192 |
| Fix option name `yolo_ys_last_offer_sync` | ✅ Fixed | sync.php:218 |
| Pass `tripDuration` as array | ✅ Fixed | sync.php:139 |
| Remove unused prototype files | ✅ Fixed | Deleted 2 files |
| Update error messages to "Offers" | ✅ Fixed | admin-display.php:214 |

### User-Reported Issues Status

| Issue | Status | Fix Location |
|-------|--------|--------------|
| Price carousel shows only 1 week | ✅ Fixed | yacht-details-v3-styles.php:190-207 |
| Missing boat description | ✅ Fixed | yacht-details-v3.php:262-270 |
| Google Maps not loading | ℹ️ Already fixed in v1.6.0 | Configurable in admin |

---

## 🚀 Installation Instructions

### Fresh Install
1. Upload `yolo-yacht-search-v1.6.1.zip` via WordPress Admin
2. Activate plugin
3. Configure Google Maps API key in admin settings
4. Run yacht sync
5. Run offers sync for desired year

### Upgrade from v1.6.0
1. Deactivate v1.6.0
2. Delete old plugin files
3. Upload and activate v1.6.1
4. Clear browser cache
5. Test offers sync

---

## ✅ Testing Checklist

### Critical Tests
- [ ] **Offers Sync:** Admin → Sync Weekly Offers → Should complete without "Failed to sync" error
- [ ] **Response Fields:** Check browser console - no "undefined" errors for `year` or `yachts_with_offers`
- [ ] **Last Sync Time:** After offers sync, "Last Offers Sync" timestamp should update
- [ ] **Price Carousel:** Yacht details page shows 4 weeks in grid (desktop)
- [ ] **Description:** Description section appears after Quick Specs
- [ ] **Google Maps:** Maps load on yacht details page (if API key configured)

### Regression Tests
- [ ] Yacht sync still works
- [ ] Image carousel works
- [ ] Date picker works
- [ ] Quote form works
- [ ] Equipment/extras display correctly

---

## 🐛 Known Limitations

1. **Search functionality not implemented** - Search widget is UI only
2. **Book Now button is placeholder** - Needs Stripe integration
3. **No actual booking creation** - Needs Booking Manager POST endpoint

---

## 📝 Next Steps

**Priority 1:** Implement search backend logic
- Filter yachts by boat type, dates, location
- Use local database for fast queries

**Priority 2:** Stripe payment integration

**Priority 3:** Booking creation via API POST

---

## 🔗 Related Documents

- `HANDOFF-NEXT-SESSION.md` - Complete project documentation
- `RELEASE-NOTES-v1.6.0.md` - Previous version notes
- ChatGPT Analysis PDF - Original bug report

---

## 📞 Support

For issues or questions:
- Check `HANDOFF-NEXT-SESSION.md` for troubleshooting
- Review WordPress debug log for errors
- GitHub: georgemargiolos/LocalWP

---

**End of Changelog v1.6.1**

*All ChatGPT recommendations applied. All user-reported UI bugs fixed. Ready for testing.*
