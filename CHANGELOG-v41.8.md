# CHANGELOG - v41.8

**Date:** December 8, 2025  
**Type:** Bug Fix

---

## Fixed

### FontAwesome Setting Not Working

**Problem:** The "Load FontAwesome from CDN" plugin setting was being ignored. FontAwesome 6 was loading even when the setting was unticked.

**Root Cause:** Two locations had hardcoded FontAwesome loading that bypassed the plugin setting:
1. Yacht details template (`public/templates/yacht-details-v3.php`)
2. Base Manager admin pages (`includes/class-yolo-ys-base-manager.php`)

**Solution:**
- **Removed hardcoded FontAwesome link** from yacht details template (line 137)
- **Made Base Manager FontAwesome loading conditional** based on plugin setting (lines 280-287)

**Files Modified:**
- `public/templates/yacht-details-v3.php` - Removed hardcoded `<link>` tag
- `includes/class-yolo-ys-base-manager.php` - Added conditional check for setting
- `yolo-yacht-search.php` - Updated version to 41.8

---

## Impact

✅ **Plugin setting now works correctly**
- When "Load FontAwesome from CDN" is **unchecked**: No FontAwesome loads from plugin
- When "Load FontAwesome from CDN" is **checked**: FontAwesome 6 loads on all pages

✅ **Allows use of theme's FontAwesome**
- If your theme loads FontAwesome 7 Kit, you can now untick the plugin setting to avoid conflicts

✅ **Reduces HTTP requests**
- When unticked, eliminates unnecessary FontAwesome CDN requests

---

## Testing Checklist

- [ ] Untick "Load FontAwesome from CDN" in plugin settings
- [ ] Visit yacht details page - verify no FontAwesome 6 loads
- [ ] Visit Base Manager pages - verify no FontAwesome 6 loads
- [ ] Check "Load FontAwesome from CDN" in plugin settings
- [ ] Visit yacht details page - verify FontAwesome 6 loads
- [ ] Visit Base Manager pages - verify FontAwesome 6 loads

---

## Technical Details

### Before (Broken):
```php
// yacht-details-v3.php - ALWAYS loaded
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

// class-yolo-ys-base-manager.php - ALWAYS loaded
wp_enqueue_style('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
```

### After (Fixed):
```php
// yacht-details-v3.php - Removed, relies on public class
<!-- FontAwesome loaded conditionally via plugin settings (public/class-yolo-ys-public.php) -->

// class-yolo-ys-base-manager.php - Now conditional
if (get_option('yolo_ys_load_fontawesome', '0') === '1') {
    wp_enqueue_style('font-awesome-6', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
}
```

---

## Version History

| Version | Date | Key Change |
|---------|------|------------|
| v41.8 | Dec 8, 2025 | Fixed FontAwesome setting not working |
| v41.7 | Dec 8, 2025 | (Previous version) |
| v41.6 | Dec 8, 2025 | Fixed yacht details page padding |

---

**Status:** ✅ Ready for Testing  
**Priority:** Medium (Performance & Flexibility)  
**Breaking Changes:** None
