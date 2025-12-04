# CHANGELOG - v16.4

**Version:** 16.4  
**Date:** December 2, 2025  
**Session:** 16  

## Critical Bug Fixes

### 1. Fixed Guest License Upload - Missing File Types

**Problem:**
- Guests could not upload National ID/Passport documents
- Error: "Upload failed" or "Invalid parameters or file type"
- Server was rejecting `id_front` and `id_back` file types

**Root Cause:**
- The allowed file types array in `class-yolo-ys-guest-users.php` was missing `id_front` and `id_back`
- Template was sending these file types, but server was rejecting them

**Fix:**
```php
// BEFORE (line 284)
$allowed_file_types = array('front', 'back', 'skipper2_front', 'skipper2_back');

// AFTER
$allowed_file_types = array('front', 'back', 'skipper2_front', 'skipper2_back', 'id_front', 'id_back');
```

**Files Changed:**
- `includes/class-yolo-ys-guest-users.php` (line 284)

**Impact:**
- ✅ Guests can now upload National ID/Passport documents
- ✅ All 6 document types now work: sailing license front/back, skipper 2 license front/back, national ID front/back

---

### 2. Removed Guest Dashboard Width Restriction

**Problem:**
- Guest dashboard was confined to 900px max-width
- Content didn't utilize full screen width on larger displays
- WordPress theme width handling was overridden

**Fix:**
```css
/* BEFORE */
.yolo-guest-dashboard {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

/* AFTER */
.yolo-guest-dashboard {
    margin: 0 auto;
    padding: 20px;
}
```

**Files Changed:**
- `public/css/guest-dashboard.css` (line 35)

**Impact:**
- ✅ Guest dashboard now uses full available width
- ✅ WordPress theme controls the layout width naturally
- ✅ Better responsive behavior on all screen sizes

---

## Summary of Changes

**Files Modified:**
1. `yolo-yacht-search.php` - Version bump to 16.4
2. `includes/class-yolo-ys-guest-users.php` - Added id_front and id_back to allowed file types
3. `public/css/guest-dashboard.css` - Removed max-width restriction
4. `README.md` - Updated to v16.4

**Fixes from Previous Versions (Still Included):**
- v16.1: Fixed checkout session nonce error
- v16.2: Removed nonce verification from license uploads
- v16.3: Fixed plugin zip structure

---

## Testing Checklist

- [ ] Guest can upload Sailing License Front
- [ ] Guest can upload Sailing License Back
- [ ] Guest can upload Skipper 2 License Front
- [ ] Guest can upload Skipper 2 License Back
- [ ] Guest can upload National ID/Passport Front ✅ **FIXED**
- [ ] Guest can upload National ID/Passport Back ✅ **FIXED**
- [ ] Guest dashboard displays full-width on desktop ✅ **FIXED**
- [ ] Guest dashboard remains responsive on mobile
- [ ] Checkout booking flow works end-to-end

---

## Deployment Instructions

1. Deactivate current plugin
2. Delete old plugin files
3. Upload `yolo-yacht-search-v16.4.zip`
4. Activate plugin
5. Clear WordPress cache (if using caching plugin)
6. Hard refresh browser (Ctrl+F5) to clear CSS cache
7. Test guest document upload

---

## Technical Notes

**Why the upload was failing:**
1. Template HTML sends `data-file-type="id_front"` and `data-file-type="id_back"`
2. JavaScript reads this and sends it in AJAX call
3. Server validates against allowed array
4. `id_front` and `id_back` were NOT in the array
5. Server returned "Invalid parameters or file type" error
6. JavaScript showed generic "Upload failed" message

**Why width restriction was removed:**
- WordPress themes (like Twenty Twenty-Five) have their own content width management
- Plugin CSS was overriding theme behavior
- Removing max-width lets the theme handle layout naturally
- Improves compatibility with all WordPress themes
