# Common Errors and Fixes

This document tracks recurring errors and their solutions to prevent reintroduction.

---

## ❌ Fatal Error: Plugin Could Not Be Activated

### Error Description
```
Plugin could not be activated because it triggered a fatal error.
```

### Root Cause
**Duplicate class initialization** - Classes being instantiated both:
1. Automatically at the end of their class files (`new ClassName()`)
2. In the main plugin class (`class-yolo-ys-yacht-search.php`)

This causes WordPress to attempt initialization before WordPress core is fully loaded, resulting in fatal errors.

### Solution
**ALWAYS follow this pattern for new classes:**

#### ❌ WRONG - Do NOT auto-initialize in class file:
```php
// At end of includes/class-yolo-ys-example.php
class YOLO_YS_Example {
    // ... class code ...
}

// Initialize the example system
new YOLO_YS_Example();  // ← NEVER DO THIS!
```

#### ✅ CORRECT - Initialize in main plugin class:
```php
// In includes/class-yolo-ys-example.php
class YOLO_YS_Example {
    // ... class code ...
}
// No initialization here - just close the class!

// Then in includes/class-yolo-ys-yacht-search.php
private function define_admin_hooks() {
    // ... other initializations ...
    
    // Initialize example system
    new YOLO_YS_Example();  // ← Initialize HERE instead
}
```

### Why This Happens
- `require_once` in main plugin file loads the class immediately
- If class auto-initializes, it runs before WordPress hooks are ready
- WordPress functions called in `__construct()` don't exist yet
- Results in fatal error during plugin activation

### Classes Affected (Fixed in v17.6)
- `YOLO_YS_Base_Manager` - Fixed ✅
- `YOLO_YS_Quote_Requests` - Fixed ✅
- `YOLO_YS_Contact_Messages` - Fixed ✅

### Prevention Checklist
When creating new classes:
- [ ] Class file contains ONLY the class definition
- [ ] No `new ClassName()` at end of class file
- [ ] Initialization added to `class-yolo-ys-yacht-search.php`
- [ ] Initialization in appropriate method (`define_admin_hooks` or `define_public_hooks`)
- [ ] Test plugin activation after adding new class

### Git Commits
- v17.5.1 (c236225): First attempt to fix contact messages
- v17.6 (2f798f0): Complete fix for all three classes

---

## 📝 Notes for Future Development

### Class Initialization Best Practices
1. **Never auto-initialize** classes in their own files
2. **Always initialize** in main plugin class
3. **Use appropriate hook** - admin hooks for admin features, public hooks for frontend
4. **Test activation** immediately after adding new class
5. **Check for duplicates** - search for `new ClassName()` across all files

### Quick Test
After adding a new class, run:
```bash
grep -r "new YOLO_YS_YourClassName" includes/ admin/ public/
```

Should only appear ONCE in `class-yolo-ys-yacht-search.php`!

---

---

## ❌ Fatal Error: Class Not Found

### Error Description
```
Fatal error: Uncaught Error: Class 'YOLO_YS_ClassName' not found
Plugin could not be activated because it triggered a fatal error.
```

### Root Cause
**Missing `require_once` statements** - Class files exist and are initialized, but never loaded into memory.

When creating new classes, forgot to add `require_once` in main plugin file before initialization.

### Solution
**ALWAYS add require_once BEFORE initialization:**

#### ❌ WRONG - Class initialized but not loaded:
```php
// yolo-yacht-search.php
// (Missing require_once for class-yolo-ys-example.php!)

// includes/class-yolo-ys-yacht-search.php
private function define_admin_hooks() {
    new YOLO_YS_Example();  // ← Fatal error: Class not found!
}
```

#### ✅ CORRECT - Load THEN initialize:
```php
// yolo-yacht-search.php
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-example.php';  // ← Load first!

// includes/class-yolo-ys-yacht-search.php
private function define_admin_hooks() {
    new YOLO_YS_Example();  // ← Now it works!
}
```

### Why This Happens
- Created new class file
- Added initialization in main class
- **Forgot to add `require_once` in main plugin file**
- PHP can't find the class when trying to instantiate
- Results in fatal error

### Classes Affected (Fixed in v17.8.1)
- `YOLO_YS_PDF_Generator` - Missing require ✅ Fixed
- `YOLO_YS_Shortcodes` - Missing require ✅ Fixed
- `YOLO_YS_Quote_Handler` - Missing require ✅ Fixed

### Prevention Checklist
When creating new classes:
- [ ] Create class file in `includes/` (or appropriate directory)
- [ ] **Add `require_once` in `yolo-yacht-search.php`** ← CRITICAL!
- [ ] Add initialization in `class-yolo-ys-yacht-search.php`
- [ ] Test plugin activation
- [ ] Verify no fatal errors

### Load Order Matters
Load dependencies BEFORE classes that use them:
```php
// CORRECT order:
require_once 'class-yolo-ys-pdf-generator.php';  // Dependency
require_once 'class-yolo-ys-base-manager.php';   // Uses PDF generator

// WRONG order:
require_once 'class-yolo-ys-base-manager.php';   // Will fail!
require_once 'class-yolo-ys-pdf-generator.php';  // Too late
```

### Quick Diagnostic
Check if all initialized classes are loaded:
```bash
cd /path/to/plugin
grep "new YOLO_YS_" includes/class-yolo-ys-yacht-search.php
# For each class found, verify require_once exists in yolo-yacht-search.php
```

### Git Commit
- v17.8.1 (7e00ab6): Added missing require statements

---

**Last Updated:** December 3, 2025  
**Version:** 17.8.1
