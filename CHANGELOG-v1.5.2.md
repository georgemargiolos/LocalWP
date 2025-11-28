# Changelog v1.5.2

## Critical Fix: Plugin Activation Error

**Release Date:** November 28, 2025

---

## 🔧 Bug Fixes

### Fatal Error on Activation (FIXED!)

**Error Message:**
```
Fatal error: Uncaught Error: Non-static method YOLO_YS_Database::create_tables() 
cannot be called statically in class-yolo-ys-activator.php:33
```

**Root Cause:**
- The `YOLO_YS_Activator::activate()` method was calling `YOLO_YS_Database::create_tables()` statically
- But `create_tables()` was defined as a regular instance method (not static)
- The method also used `$this->table_*` properties which don't exist in static context

**Solution:**
- Made `create_tables()` method `static`
- Replaced all `$this->table_*` references with local variables
- Table names now defined directly in the method using `$wpdb->prefix`

**Files Changed:**
- `includes/class-yolo-ys-database.php` - Made `create_tables()` static and fixed table name references

---

## ✅ Verification

- ✅ PHP syntax check passed
- ✅ No `$this` references in static method
- ✅ Table creation logic preserved
- ✅ Compatible with activator's static call

---

## 📝 Notes

This fix resolves the persistent activation error that has been occurring since v1.1.0 when the prices database class was introduced.

**Previous Working Version:** v1.0.4  
**First Broken Version:** v1.1.0  
**Fixed in:** v1.5.2

---

## 🚀 Upgrade Instructions

1. Delete v1.5.1 (or any previous version)
2. Upload `yolo-yacht-search-v1.5.2.zip`
3. Activate plugin
4. Plugin should activate successfully!

---

## 📋 Full Feature List (Still Included)

All Phase 1-3 features remain intact:
- ✅ Price fetching & display
- ✅ Image & price carousels
- ✅ Quote request form
- ✅ Booking system UI
- ✅ Google Maps integration
- ✅ Database caching
- ✅ YOLO boat prioritization
- ✅ Nonce validation (security)
