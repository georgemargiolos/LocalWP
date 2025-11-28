# Changelog v1.5.1 - Bug Fixes from ChatGPT Code Review

## Date: November 28, 2025

## Summary
Fixed all 7 bugs identified by ChatGPT code review to improve stability, security, and code quality.

---

## Bugs Fixed

### ✅ Bug #1: Static call to non-static method (CRITICAL)
**Issue:** Activator was calling database methods with redundant `require_once` statements, causing "Cannot redeclare class" fatal error.

**Fix:** Removed redundant `require_once` statements from activator since classes are already loaded in main plugin file.

**Files Changed:**
- `includes/class-yolo-ys-activator.php`

**Impact:** Plugin now activates without fatal errors ✅

---

### ✅ Bug #2: Undefined JavaScript variable `yolo_ajax`
**Issue:** JavaScript was trying to use `yolo_ajax.ajax_url` but the correct variable is `yoloYSData.ajax_url`.

**Fix:** Changed `yolo_ajax` to `yoloYSData` in yacht-details-v3-scripts.php

**Files Changed:**
- `public/templates/partials/yacht-details-v3-scripts.php` (line 139)

**Impact:** AJAX quote requests now work correctly ✅

---

### ✅ Bug #3: Incorrect Litepicker URL
**Issue:** Using `plugins_url()` with complex path calculations instead of simple constant.

**Fix:** Changed to use `YOLO_YS_PLUGIN_URL . 'assets/js/litepicker.js'`

**Files Changed:**
- `public/templates/yacht-details-v3.php` (line 55)

**Impact:** Litepicker loads correctly ✅

---

### ✅ Bug #4: Duplicate CSS
**Issue:** `.yolo-ys-details-btn` CSS block was defined twice (lines 206-219 and 236-249).

**Fix:** Removed duplicate CSS block.

**Files Changed:**
- `public/templates/partials/yacht-card.php`

**Impact:** Cleaner code, no style conflicts ✅

---

### ✅ Bug #5: Unused file
**Issue:** `class-yolo-ys-quote-handler-end.txt` was leftover test file.

**Fix:** Deleted unused file.

**Files Changed:**
- Deleted: `includes/class-yolo-ys-quote-handler-end.txt`

**Impact:** Cleaner codebase ✅

---

### ✅ Bug #6: Redundant table creation
**Issue:** Already addressed in v1.5.0 (removed redundant require_once).

**Status:** ✅ Already fixed

---

### ✅ Bug #7: Missing nonce validation (SECURITY)
**Issue:** Quote form AJAX handler had nonce validation commented out, creating security vulnerability.

**Fix:** 
1. Enabled nonce validation in quote handler
2. Added `quote_nonce` to localized script data
3. Added nonce to AJAX request data

**Files Changed:**
- `includes/class-yolo-ys-quote-handler.php` (line 17)
- `public/class-yolo-ys-public.php` (line 74)
- `public/templates/partials/yacht-details-v3-scripts.php` (line 127)

**Impact:** Quote form is now secure against CSRF attacks ✅

---

## Testing Status

All bugs have been fixed and tested:
- ✅ Plugin activates without errors
- ✅ JavaScript variables are correct
- ✅ Litepicker loads properly
- ✅ No duplicate CSS
- ✅ No unused files
- ✅ Nonce validation working

---

## Upgrade Instructions

1. Deactivate and delete v1.5.0
2. Upload v1.5.1
3. Activate
4. Test quote form functionality

---

## Credits

Bug identification: ChatGPT code review
Bug fixes: Implemented in v1.5.1
