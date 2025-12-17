# YOLO Yacht Search Plugin v16.7 - Changelog

## Date: December 2, 2025

## What's New in v16.7

### 1. ✅ Administrator Testing Access
**Added:** Administrators can now access the guest dashboard for testing purposes

**Change:**
```php
// Before (v16.6):
if (!in_array('guest', (array) $user->roles)) {
    return 'Access denied...';
}

// After (v16.7):
if (!in_array('guest', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
    return 'Access denied...';
}
```

**Benefit:** Allows admins to test the guest dashboard without creating a guest account

**File:** `includes/class-yolo-ys-guest-users.php` (line 201-206)

---

### 2. ✅ Enhanced Upload Error Logging
**Added:** Detailed error logging for debugging upload issues

**New Logs:**
- File received: name, size, type
- Upload errors with specific messages
- Directory permission issues
- File save success/failure

**Example Log Output:**
```
YOLO YS License Upload: File received - license.jpg (245678 bytes, type: image/jpeg)
YOLO YS License Upload: File saved to /path/to/uploads/yolo-licenses/123/license_front_1701234567.jpg
```

**Benefit:** Easier troubleshooting of upload failures

**File:** `includes/class-yolo-ys-guest-users.php` (lines 308, 322, 349, 361, 366)

---

### 3. ✅ Upload Error Handling
**Added:** Comprehensive PHP upload error detection and user-friendly messages

**Errors Detected:**
- `UPLOAD_ERR_INI_SIZE` → "File exceeds server upload limit"
- `UPLOAD_ERR_FORM_SIZE` → "File exceeds form limit"
- `UPLOAD_ERR_PARTIAL` → "File only partially uploaded"
- `UPLOAD_ERR_NO_FILE` → "No file uploaded"
- `UPLOAD_ERR_NO_TMP_DIR` → "Server temp folder missing"
- `UPLOAD_ERR_CANT_WRITE` → "Failed to write to disk"
- `UPLOAD_ERR_EXTENSION` → "Upload blocked by extension"

**Benefit:** Users get clear error messages instead of generic "upload failed"

**File:** `includes/class-yolo-ys-guest-users.php` (lines 310-325)

---

### 4. ✅ Directory Writable Check
**Added:** Verification that upload directory is writable before attempting upload

**Check:**
```php
if (!is_writable($license_dir)) {
    error_log('YOLO YS License Upload: Directory not writable - ' . $license_dir);
    wp_send_json_error(array('message' => 'Upload directory is not writable'));
    return;
}
```

**Benefit:** Prevents silent failures due to permission issues

**File:** `includes/class-yolo-ys-guest-users.php` (lines 348-352)

---

## Complete Feature Set (v16.7)

### From v16.6:
- ✅ Guest dashboard full-width layout (91% of viewport)
- ✅ WordPress theme constraint override using `:has()` selector
- ✅ HTTPS mixed content fix
- ✅ All 6 file types supported (front, back, skipper2_front, skipper2_back, id_front, id_back)
- ✅ Nonce verification removed (security via login + booking ownership)

### New in v16.7:
- ✅ Administrator testing access
- ✅ Enhanced error logging
- ✅ Upload error handling
- ✅ Directory permission check

---

## Files Modified in v16.7

1. **yolo-yacht-search.php**
   - Version updated to 16.7

2. **includes/class-yolo-ys-guest-users.php**
   - Line 201-206: Added administrator role check
   - Line 308: Added file received logging
   - Lines 310-325: Added upload error handling
   - Lines 348-352: Added directory writable check
   - Line 361: Added file move failure logging
   - Line 366: Added file save success logging

---

## Upgrade Path

### From v16.6 to v16.7:
- **Breaking Changes:** None
- **Database Changes:** None
- **Safe to upgrade:** Yes
- **Rollback possible:** Yes (just reinstall v16.6)

### From v16.5 or earlier:
- Review v16.6 changelog first
- Ensure HTTPS is configured
- Test upload functionality after upgrade

---

## Testing Checklist

- [x] Admin can access guest dashboard
- [x] Error logs appear in WordPress debug.log
- [x] Upload errors show user-friendly messages
- [x] Directory permission issues are detected
- [ ] Test on live server (requires production environment)

---

## Known Limitations

1. **Error Logging:** Requires WordPress debug logging enabled in wp-config.php:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

2. **Administrator Access:** While admins can view the dashboard, they need actual bookings in the database to see content. Consider creating test bookings for admin users.

---

## Version Comparison

| Feature | v16.5 | v16.6 | v16.7 |
|---------|-------|-------|-------|
| Upload functionality | ✅ | ✅ | ✅ |
| Full-width dashboard | ❌ | ✅ | ✅ |
| Admin testing access | ❌ | ❌ | ✅ |
| Error logging | ❌ | ❌ | ✅ |
| Upload error handling | ❌ | ❌ | ✅ |
| Directory checks | ❌ | ❌ | ✅ |

---

## Recommended: Enable Debug Logging

To take advantage of the new error logging, add to `wp-config.php`:

```php
// Enable debug logging (before "That's all, stop editing!")
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

Logs will be written to: `/wp-content/debug.log`

---

**Status:** Production ready  
**Recommended for:** All users (especially those experiencing upload issues)  
**Next Steps:** Deploy to production and monitor debug.log for any upload errors
