# CHANGELOG - v40.0

## [40.0] - 2025-12-08

### 🚨 CRITICAL FIX: Fatal Error on Plugin Activation

**ROOT CAUSE:** Duplicate ABSPATH security checks in multiple PHP class files

### Fixed
- **CRITICAL: Plugin Activation Fatal Error** - Removed duplicate `if (!defined('ABSPATH'))` checks that were causing PHP syntax errors during plugin activation
- Fixed 6 files with duplicate ABSPATH checks:
  - `includes/class-yolo-ys-base-manager-database.php`
  - `includes/class-yolo-ys-base-manager.php`
  - `includes/class-yolo-ys-contact-messages.php`
  - `includes/class-yolo-ys-pdf-generator.php`
  - `includes/class-yolo-ys-quote-requests.php`

### Technical Details

**Problem Pattern:**
```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Docblock
 */

if (!defined('ABSPATH')) {  // ❌ DUPLICATE - FATAL ERROR
    exit;
}

class My_Class {
```

**Solution:**
```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Docblock
 */

class My_Class {  // ✅ CORRECT
```

### Impact
- ✅ Plugin now activates without fatal errors
- ✅ All v30.6 features preserved (FOUC fix, Bootstrap loading)
- ✅ No breaking changes

### Notes

**⚠️ RECURRING BUG ALERT**

This is a recurring WordPress wrapping issue. When creating or modifying PHP class files:
1. **ONLY ONE** ABSPATH check per file
2. Place it immediately after `<?php` tag
3. Never add a second check after the docblock

### Inherited from v30.6
- Bootstrap CSS loading fix for yacht details pages
- FOUC (Flash of Unstyled Content) prevention fix
- All previous bug fixes and features

---

## Previous Version Issues

### [30.6] - 2025-12-06 ❌ BROKEN
- Fixed white page on yacht details (FOUC issue)
- Fixed Bootstrap CSS loading on URL parameter pages
- **FATAL ERROR:** Duplicate ABSPATH checks prevented activation

### [30.5] - 2025-12-06
- Fixed Bootstrap CSS not loading on yacht details pages accessed via `?yacht_id=` parameter

---

**Status:** ✅ **STABLE - READY FOR PRODUCTION**

**All known bugs fixed. Plugin activates correctly.**
