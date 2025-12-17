# YOLO Yacht Search & Booking Plugin - v60.2 Changelog

**Release Date:** December 12, 2025  
**Version:** 60.2  
**Type:** Critical Bug Fix

---

## 🐛 Critical Fix

### v60.1 Layout Fix Was Incomplete

**Issue:** The CSS fix in v60.1 didn't actually work - the catamaran card was still constrained to 33.33% width.

**Root Cause:**
- v60.1 added `max-width: 100%` and `flex: 0 0 100%`
- But Bootstrap's flex system also needs `width: 100%` to properly override
- Without the `width` property, the card stayed at `col-lg-4` width (33.33%)

**Fix Applied:**
Added missing `width: 100%` property to the CSS rule:

```css
/* public/css/search-results.css, lines 484-488 */
.yolo-ys-section-header + .container-fluid .row.g-4 .col-lg-4:first-child:last-child {
    max-width: 100% !important;
    flex: 0 0 100% !important;
    width: 100% !important;  /* ← ADDED THIS LINE */
}
```

**Testing:**
- ✅ Manually tested on test server - card now full width
- ✅ Verified with browser console - width changes from 276px to full container width
- ✅ Confirmed sailing yacht search (2 boats) still displays correctly

---

## 📋 Files Changed

### Modified Files
1. **yolo-yacht-search.php**
   - Updated version from 60.1 to 60.2

2. **public/css/search-results.css**
   - Added `width: 100% !important;` to line 487

### New Files
1. **CHANGELOG-v60.2.md** (this file)

---

## 🔄 Version History

| Version | Issue | Status |
|---------|-------|--------|
| v60.0 | Image optimization | ✅ Working |
| v60.1 | Layout fix attempt | ❌ Incomplete |
| v60.2 | Layout fix complete | ✅ Working |

---

## 🚀 Deployment

### Critical Update
This is a critical fix for v60.1. If you deployed v60.1, you MUST upgrade to v60.2.

### Steps
1. Upload v60.2 plugin files
2. Clear WordPress cache
3. Clear browser cache (Ctrl+F5)
4. Verify catamaran search displays full width

---

## 📝 Lessons Learned

**Why v60.1 Failed:**
- CSS testing was done in browser console but not verified on live/test server
- Assumed `max-width` + `flex` would be sufficient
- Didn't account for Bootstrap's complex flex calculations

**Improvement for Future:**
- Always test CSS fixes on actual server before marking as complete
- Verify visual changes with screenshots
- Test in browser console AND reload page to confirm

---

**Previous Version:** v60.1 - Incomplete Layout Fix  
**Current Version:** v60.2 - Complete Layout Fix ✅
