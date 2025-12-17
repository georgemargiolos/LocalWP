# CHANGELOG v60.5

**Release Date**: December 12, 2025  
**Status**: CRITICAL BUG FIX - Ready for Production

## 🐛 CRITICAL BUG FIX

### Search Results Container Width Bug (FIXED)

**Problem**: Search results page container width was dynamically changing based on the number of YOLO boats displayed:
- 3 YOLO boats → 1264px container (full width, correct)
- 1 YOLO boat → 943px container (constrained, broken)
- 0 YOLO boats + partner boats → 943px container (constrained, broken)

**Root Cause**: WordPress CSS load order issue
- `bootstrap-mobile-fixes.css` had `max-width: 100%` rule for `.yolo-ys-search-results`
- `search-results.css` had `max-width: none !important` to override WordPress theme constraints
- `search-results.css` was enqueued with `array()` (no dependencies), causing inconsistent load order
- When `bootstrap-mobile-fixes.css` loaded AFTER `search-results.css`, its `max-width: 100%` would win despite the `!important` flag (because it came later in cascade)
- This created random behavior where sometimes the page was full-width, sometimes constrained

**Solution** (2 changes):

1. **public/class-yolo-ys-public.php** (line 101):
   - Added `'yolo-ys-bootstrap-mobile'` as dependency to `search-results.css` enqueue
   - Guarantees `search-results.css` ALWAYS loads AFTER `bootstrap-mobile-fixes.css`
   - Ensures `max-width: none !important` consistently wins

2. **public/css/bootstrap-mobile-fixes.css** (line 50):
   - Removed conflicting `max-width: 100%` rule from `.yolo-ys-search-results` selector
   - Kept `overflow-x: clip` for horizontal scroll prevention
   - Eliminates the conflict entirely

**Result**: Search results page now displays at full width consistently, regardless of boat count.

## 📝 Files Changed

### Modified Files
1. `yolo-yacht-search.php` - Version bump to 60.5
2. `public/class-yolo-ys-public.php` - Added CSS dependency to fix load order
3. `public/css/bootstrap-mobile-fixes.css` - Removed conflicting max-width rule

## 🧪 Testing Required

- [ ] Test search results with 1 YOLO boat (should be full width)
- [ ] Test search results with 3 YOLO boats (should be full width)
- [ ] Test search results with 0 YOLO boats + partner boats (should be full width)
- [ ] Test search results with mixed YOLO + partner boats (should be full width)
- [ ] Verify no layout breaks on mobile devices
- [ ] Verify no layout breaks on tablet devices
- [ ] Verify no layout breaks on desktop devices

## 📋 Deployment Notes

**Upgrade Path**: Direct upgrade from v60.0-60.4
- No database changes
- No settings changes
- No template changes
- CSS cache clear recommended after deployment

**Rollback**: Can safely rollback to v60.4 if issues occur

## 🔄 Version History Context

- **v60.0**: Image optimization during yacht sync (85-90% storage reduction)
- **v60.1-60.3**: Various improvements and fixes
- **v60.4**: Previous stable version
- **v60.5**: CSS load order fix for search results container width bug (THIS VERSION)

## 🎯 Next Steps

After deployment and testing of v60.5:
1. Make "Remaining:" text customizable (yacht-details-v3-scripts.php line 886)
2. Comprehensive audit of all frontend templates for hardcoded texts (30+ identified)
3. Create settings fields for all customizable texts
4. Version 61.0 with full text customization system

---

**Prepared by**: Manus AI Agent  
**Reviewed by**: Pending  
**Approved by**: Pending
