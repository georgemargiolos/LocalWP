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

**Last Updated:** December 3, 2025  
**Version:** 17.6
