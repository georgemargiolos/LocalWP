# CHANGELOG - v41.10

**Date:** December 8, 2025  
**Status:** Critical Bug Fix

---

## 🐛 Bug Fixes

### Check-In/Check-Out Dropdown Issue (CRITICAL)

**Problem:** Yachts and bookings were not appearing in check-in/checkout dropdowns despite data existing in the database.

**Root Causes Identified:**

1. **Nonce Mismatch:** Check-in and checkout templates were using hardcoded PHP nonces instead of the JavaScript variable
2. **JavaScript Conflict:** `base-manager.js` was loading on admin pages and causing errors

**Files Modified:**

1. `/admin/partials/base-manager-checkin.php`
   - Line 617: Changed nonce from hardcoded PHP to `yoloBaseManager.nonce`
   - Line 671: Changed nonce from hardcoded PHP to `yoloBaseManager.nonce`

2. `/admin/partials/base-manager-checkout.php`
   - Line 595: Changed nonce from hardcoded PHP to `yoloBaseManager.nonce`
   - Line 660: Changed nonce from hardcoded PHP to `yoloBaseManager.nonce`
   - Line 714: Changed nonce from hardcoded PHP to `yoloBaseManager.nonce`
   - Line 764: Changed nonce from hardcoded PHP to `yoloBaseManager.nonce`

3. `/includes/class-yolo-ys-base-manager.php`
   - Removed `wp_enqueue_script('yolo-base-manager', ...)` from admin pages
   - Removed `wp_localize_script('yolo-base-manager', ...)` from admin pages
   - Added `wp_add_inline_script()` to provide nonce variable for admin pages
   - Added comment explaining why base-manager.js should not load on admin

4. `/yolo-yacht-search.php`
   - Updated version to 41.10

---

## 🔧 Technical Details

### Before (Broken):

**Nonce Issue:**
```javascript
// Hardcoded PHP nonce in JavaScript
nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
```
- Nonce could expire or become invalid
- Caused "Security check failed" errors
- Dropdowns remained empty

**JavaScript Conflict:**
```php
// base-manager.js loaded on admin pages
wp_enqueue_script('yolo-base-manager', ...);
```
- Expected `response.data.bookings` format
- But AJAX returned `response.data` directly
- Caused TypeError and broke page functionality

### After (Fixed):

**Nonce Solution:**
```javascript
// Use JavaScript variable
nonce: yoloBaseManager.nonce
```
- Fresh nonce on every page load
- No expiration issues
- Security check passes

**JavaScript Solution:**
```php
// Inline script provides nonce without loading base-manager.js
wp_add_inline_script(
    'signature-pad',
    'var yoloBaseManager = {' .
    '    ajaxurl: "' . admin_url('admin-ajax.php') . '",' .
    '    nonce: "' . wp_create_nonce('yolo_base_manager_nonce') . '"' .
    '};',
    'before'
);
```
- No JavaScript conflicts
- Nonce still available
- Admin pages have their own inline JS

---

## ✅ Testing Checklist

- [x] Check-in dropdown loads yachts
- [x] Check-in dropdown loads bookings
- [x] Check-out dropdown loads yachts
- [x] Check-out dropdown loads bookings
- [x] No JavaScript errors in console
- [x] AJAX calls succeed with valid nonce
- [ ] Test on live site (user to verify)

---

## 📊 Impact

**Severity:** CRITICAL  
**Affected Features:** Check-In, Check-Out  
**User Impact:** Base Manager could not create check-in/checkout documents  
**Fix Priority:** IMMEDIATE

---

## 🔄 Upgrade Notes

1. Upload `yolo-yacht-search-v41.10.zip` to WordPress
2. Deactivate old plugin
3. Delete old plugin files
4. Upload and activate new plugin
5. Clear browser cache (Ctrl+Shift+Delete)
6. Test check-in and check-out dropdowns

---

## 📝 Version History

| Version | Date | Key Changes |
|---------|------|-------------|
| v41.10 | Dec 8, 2025 | Fixed check-in/checkout dropdown issue |
| v41.9 | Dec 8, 2025 | Fixed FontAwesome + Removed Stripe test mode |
| v41.8 | Dec 8, 2025 | Fixed FontAwesome setting (incomplete) |
| v41.7 | Dec 8, 2025 | Previous version |

---

**Status:** ✅ Ready for Production  
**Package:** yolo-yacht-search-v41.10.zip (2.2 MB)
