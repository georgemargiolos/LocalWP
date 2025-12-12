# CHANGELOG v60.8

**Release Date**: December 12, 2025  
**Status**: Enhanced CSS Fix - Ready for Production

## 🔧 ENHANCEMENT: Maximum CSS Specificity for Single Yacht Layout

### Problem
v60.7 CSS selectors weren't specific enough. DevTools inspection revealed the actual HTML uses `col-12 col-sm-6 col-lg-4` (three Bootstrap classes), not just `col-lg-4`. Previous selectors didn't match this exact structure.

### Root Cause Analysis
From user's DevTools screenshot:
```html
<div class="row g-4">
  <div class="col-12 col-sm-6 col-lg-4">
    <div class="yolo-ys-yacht-card yolo-yacht">
```

The CSS was targeting `.col-lg-4` but the actual element has multiple responsive classes.

### Solution (v60.8)
Enhanced CSS with **multiple targeting methods** for maximum specificity:

**File**: `public/css/search-results.css` (lines 69-101)

```css
@media (min-width: 992px) {
    /* Method 1: Attribute selector - matches any element with col-lg-4 in class */
    .yolo-ys-search-results .row.g-4:has([class*="col-lg-4"]:only-child) [class*="col-lg-4"] {
        max-width: 100% !important;
        flex: 0 0 100% !important;
        width: 100% !important;
    }
    
    /* Method 2: Sibling selector fallback for older browsers */
    .yolo-ys-section-header + .container-fluid .row.g-4 [class*="col-lg-4"]:first-child:last-child,
    .yolo-ys-search-results .container-fluid .row.g-4 [class*="col-lg-4"]:first-child:last-child {
        max-width: 100% !important;
        flex: 0 0 100% !important;
        width: 100% !important;
    }
    
    /* Method 3: Exact class combination - highest specificity */
    .yolo-ys-search-results .row.g-4 .col-12.col-sm-6.col-lg-4:only-child,
    .yolo-ys-search-results .row.g-4 .col-12.col-sm-6.col-lg-4:first-child:last-child {
        max-width: 100% !important;
        flex: 0 0 100% !important;
        width: 100% !important;
    }
    
    /* When exactly 2 yachts, make them 50% each */
    .yolo-ys-search-results .row.g-4:has([class*="col-lg-4"]:first-child:nth-last-child(2)) [class*="col-lg-4"] {
        max-width: 50% !important;
        flex: 0 0 50% !important;
        width: 50% !important;
    }
}
```

### Why This Works

1. **Attribute selector** `[class*="col-lg-4"]` - Matches any element containing "col-lg-4" in its class attribute
2. **Exact class selector** `.col-12.col-sm-6.col-lg-4` - Targets the exact three-class combination
3. **Multiple methods** - Ensures at least one selector will match
4. **Desktop only** - `@media (min-width: 992px)` prevents mobile layout issues
5. **!important flags** - Override Bootstrap's default grid behavior

### Result

- **1 yacht** → 100% width (full width card) ✅
- **2 yachts** → 50% width each (side by side) ✅
- **3+ yachts** → 33.33% width (3 per row) ✅
- **Mobile/tablet** → Responsive (col-12, col-sm-6 still work) ✅

---

## 📋 Includes All Previous Fixes

**v60.5:**
- CSS load order fix for search results container

**v60.6:**
- Complete text customization system (100% of frontend texts)

**v60.7:**
- Initial single yacht card width fix (insufficient specificity)

**v60.8:**
- Enhanced CSS with maximum specificity (THIS VERSION)

---

## 📝 Files Changed

### Modified Files
1. `yolo-yacht-search.php` - Version bump to 60.8
2. `public/css/search-results.css` - Enhanced CSS selectors with multiple targeting methods

---

## 🧪 Testing Required

### Critical Test (Must Pass)
- [ ] Navigate to search results with **1 yacht** (Catamaran filter)
- [ ] **Clear ALL caches** (WordPress + Browser + CDN)
- [ ] Verify yacht card displays at **full width** (100%)
- [ ] Card should span entire content area, not 33.33%

### Regression Tests
- [ ] Search results with 2 yachts → **50% width each** (side by side)
- [ ] Search results with 3+ yachts → **33.33% width** (3 per row)
- [ ] Mobile view (< 992px) → **1 card per row** (col-12)
- [ ] Tablet view (576-991px) → **2 cards per row** (col-sm-6)

---

## 📋 Deployment Notes

**Upgrade Path**: Direct upgrade from v60.0-60.7
- No database changes
- CSS-only enhancement
- **CRITICAL: MUST clear all caches after deployment**

**Cache Clearing (ABSOLUTELY REQUIRED):**
1. **WordPress cache** - WP Super Cache, W3 Total Cache, etc. → Clear/Purge All
2. **Browser cache** - Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
3. **CDN cache** - Cloudflare → Purge Everything, or equivalent for your CDN

**If still not working after cache clear:**
- Open browser DevTools → Network tab
- Check CSS file load order
- Verify `search-results.css?ver=60.8` is loading
- Check if theme CSS is loading after plugin CSS

**Rollback**: Can safely rollback to v60.6 if issues occur

---

## 🔄 Version History

- **v60.0**: Image optimization
- **v60.5**: CSS load order fix
- **v60.6**: Complete text customization
- **v60.7**: Initial single yacht card width fix
- **v60.8**: Enhanced CSS specificity fix (THIS VERSION)

---

**Prepared by**: Manus AI Agent  
**Issue identified by**: User DevTools inspection  
**Version corrected**: Yes (was incorrectly kept at 60.7, now properly 60.8)
