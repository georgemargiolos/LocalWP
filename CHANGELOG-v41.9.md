# CHANGELOG - v41.9

**Date:** December 8, 2025  
**Type:** Bug Fix + Simplification

---

## Fixed

### 1. FontAwesome Setting Not Working (from v41.8)

**Problem:** The "Load FontAwesome from CDN" plugin setting was being ignored. FontAwesome 6 was loading even when the setting was unticked.

**Solution:**
- Removed hardcoded FontAwesome link from yacht details template
- Made Base Manager FontAwesome loading conditional based on plugin setting

**Files Modified:**
- `public/templates/yacht-details-v3.php` - Removed hardcoded `<link>` tag
- `includes/class-yolo-ys-base-manager.php` - Added conditional check

---

### 2. Stripe Test Mode Setting Removed

**Problem:** The "Enable test mode" checkbox didn't actually do anything. The plugin used whatever keys were entered, regardless of the checkbox state.

**Solution:** Removed the confusing test mode checkbox entirely. Now:
- Use `pk_test_` and `sk_test_` keys for testing (Stripe won't charge)
- Use `pk_live_` and `sk_live_` keys for live payments (Stripe will charge)
- Stripe automatically handles test vs live based on the key prefix

**Files Modified:**
- `admin/class-yolo-ys-admin.php` - Removed test mode setting registration and callback
- Updated key field descriptions to clarify test/live usage

---

## Impact

✅ **FontAwesome setting now works correctly**
- When unchecked: No FontAwesome loads from plugin (use theme's FontAwesome)
- When checked: FontAwesome 6 loads from CDN

✅ **Stripe settings simplified**
- No more confusing test mode checkbox
- Just enter the appropriate keys for your environment
- Stripe handles test vs live automatically

✅ **Cleaner admin interface**
- One less setting to manage
- Clearer instructions on key usage

---

## Upgrade Notes

### For Existing Users:

**FontAwesome:**
- If you had the setting unchecked, it will now actually work
- If your theme loads FontAwesome 7, keep it unchecked

**Stripe:**
- The test mode checkbox has been removed
- Your existing keys will continue to work
- If you're testing: Make sure you're using `pk_test_` and `sk_test_` keys
- If you're live: Make sure you're using `pk_live_` and `sk_live_` keys

---

## Files Modified

1. `public/templates/yacht-details-v3.php` - Removed hardcoded FontAwesome
2. `includes/class-yolo-ys-base-manager.php` - Made FontAwesome conditional
3. `admin/class-yolo-ys-admin.php` - Removed test mode setting
4. `yolo-yacht-search.php` - Updated version to 41.9

---

## Version History

| Version | Date | Key Changes |
|---------|------|-------------|
| v41.9 | Dec 8, 2025 | Fixed FontAwesome setting + Removed Stripe test mode |
| v41.8 | Dec 8, 2025 | Fixed FontAwesome setting (incomplete) |
| v41.7 | Dec 8, 2025 | (Previous version) |
| v41.6 | Dec 8, 2025 | Fixed yacht details page padding |

---

**Status:** ✅ Ready for Production  
**Priority:** Medium (Settings Fixes)  
**Breaking Changes:** None (Stripe test mode removal is backward compatible)
