# YOLO Yacht Search & Booking Plugin - v60.1 Changelog

**Release Date:** December 12, 2025  
**Version:** 60.1  
**Type:** Bug Fix Release

---

## 🐛 Bug Fixes

### Search Results Layout Fix - Single Catamaran Display

**Issue:** When searching for catamarans only (which returns 1 YOLO yacht - Strawberry), the yacht card was constrained to 33.33% width instead of full width, leaving large empty space on the right side of the page.

**Root Cause:**
- Bootstrap grid uses `col-12 col-sm-6 col-lg-4` for yacht cards
- This creates a 3-column layout on large screens
- With only 1 yacht, the card takes 1/3 of the row (33.33%), leaving 2/3 empty
- With 2+ yachts (like sailing yacht search), the layout looks correct

**Fix Applied:**
Added CSS rule in `public/css/search-results.css` (lines 481-488):

```css
@media (min-width: 992px) {
    .yolo-ys-section-header + .container-fluid .row.g-4 .col-lg-4:first-child:last-child {
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
}
```

**How It Works:**
- Targets yacht cards that are both `:first-child` AND `:last-child` (meaning they're the only child in the row)
- Forces them to take 100% width on large screens (992px+)
- Only applies to search results page yacht cards
- Doesn't affect multi-yacht displays (sailing yacht search, partner boats, etc.)

**Impact:**
- ✅ Single catamaran (Strawberry) now displays full width
- ✅ Multiple yachts still display in 3-column grid
- ✅ Responsive design maintained for mobile/tablet
- ✅ No impact on other pages or features

---

## 📋 Files Changed

### Modified Files
1. **yolo-yacht-search.php**
   - Updated version from 60.0 to 60.1 (line 6)
   - Updated version constant (line 23)

2. **public/css/search-results.css**
   - Added single yacht full-width layout fix (lines 475-488)

### New Files
1. **CHANGELOG-v60.1.md** (this file)

---

## 🧪 Testing Performed

### Test Cases
1. ✅ Catamaran search (1 result) - Card now full width
2. ✅ Sailing yacht search (2 results) - Cards display side-by-side correctly
3. ✅ No filter search (all boats) - Grid layout works correctly
4. ✅ Mobile responsive - Single card still full width on mobile
5. ✅ Tablet view - Layout adapts correctly

### Browser Compatibility
- ✅ Chrome/Edge (modern)
- ✅ Firefox (modern)
- ✅ Safari (modern)
- ✅ Mobile browsers

---

## 🔄 Upgrade Notes

### From v60.0 to v60.1
- **No database changes** - Safe to upgrade
- **No settings changes** - No configuration needed
- **Cache clearing recommended** - Clear browser cache to see CSS changes immediately

### Deployment Steps
1. Upload updated plugin files to server
2. Clear WordPress cache (if using caching plugin)
3. Clear browser cache or hard refresh (Ctrl+F5)
4. Test catamaran search to verify fix

---

## 📊 Version Comparison

| Aspect | v60.0 | v60.1 |
|--------|-------|-------|
| Image Optimization | ✅ | ✅ |
| Catamaran Layout | ❌ Broken | ✅ Fixed |
| Search Results Grid | ✅ | ✅ |
| Mobile Responsive | ✅ | ✅ |

---

## 🔗 Related Issues

- **Issue:** Catamaran search results page layout broken
- **Reported:** December 12, 2025
- **Fixed in:** v60.1
- **Status:** ✅ Resolved

---

## 📝 Notes

This is a minor bug fix release that addresses a specific layout issue discovered when filtering search results for catamarans. The fix is CSS-only and doesn't affect any functionality or database structure.

The issue was specific to searches that return exactly 1 YOLO yacht, which currently only happens with catamaran filtering (since there's only 1 catamaran - Strawberry).

---

**Previous Version:** v60.0 - Image Optimization  
**Next Version:** TBD
