# RECURRING ERRORS DOCUMENTATION

**Purpose:** Document recurring bugs and their fixes to prevent reintroduction  
**Last Updated:** December 3, 2025  
**Version:** 17.11.1

---

## ⚠️ CRITICAL: READ BEFORE EDITING CODE

This document tracks **recurring errors** that have been fixed multiple times. If you're working on this codebase, **READ THIS FIRST** to avoid reintroducing these bugs.

---

## 🔴 RECURRING ERROR #1: PHP Syntax Error in Admin Partials

### Pattern
Admin partial files (check-in, check-out, etc.) have broken PHP syntax in the ABSPATH check.

### Symptoms
- Page shows blank white screen
- PHP fatal error in logs
- "Cannot access this page" error

### Root Cause
```php
// WRONG (BROKEN):
if (!defined('ABSPATH')) {

// Permission check
if (!current_user_can('manage_options')) {
    wp_die(__('Sorry, you are not allowed to access this page.', 'yolo-yacht-search'));
}
    exit;  // <-- This exit is in the wrong place!
}
```

### Why It's Wrong
1. The `exit;` is **inside** the ABSPATH block but **after** the permission check
2. The permission check is **inside** the ABSPATH block (should be outside)
3. Missing closing brace for the inner `if` statement
4. Using wrong capability (`manage_options` instead of `edit_posts`)

### Correct Pattern
```php
// CORRECT:
if (!defined('ABSPATH')) {
    exit;
}

// Permission check
if (!current_user_can('edit_posts')) {
    wp_die(__('Sorry, you are not allowed to access this page.', 'yolo-yacht-search'));
}
```

### Files to Check
- `/admin/partials/base-manager-checkin.php`
- `/admin/partials/base-manager-checkout.php`
- `/admin/partials/base-manager-yacht-management.php`
- `/admin/partials/base-manager-warehouse.php`
- Any new admin partial files

### Prevention
1. **Always** use this exact pattern at the top of admin partials
2. **Never** put permission checks inside the ABSPATH block
3. **Always** use `edit_posts` capability for Base Manager pages
4. **Test** the page loads before committing

---

## 🔴 RECURRING ERROR #2: AJAX Response Structure Mismatch

### Pattern
PHP sends data wrapped in an object, but JavaScript expects it directly.

### Symptoms
- Dropdowns remain empty
- Console shows "Cannot read property 'forEach' of undefined"
- Data is in `response.data.yachts` instead of `response.data`

### Root Cause
```php
// PHP - WRONG:
wp_send_json_success(array('yachts' => $yachts));

// JavaScript expects:
response.data.forEach(function(yacht) { ... });

// But data is actually at:
response.data.yachts.forEach(function(yacht) { ... });
```

### Why It's Wrong
1. `wp_send_json_success()` already wraps data in `{success: true, data: ...}`
2. Adding another level (`{yachts: [...]}`) creates double nesting
3. JavaScript expects `response.data` to be the array directly

### Correct Pattern
```php
// PHP - CORRECT:
$yachts = $wpdb->get_results("SELECT * FROM $table_name");
wp_send_json_success($yachts);  // <-- Send array directly

// JavaScript - CORRECT:
response.data.forEach(function(yacht) {
    // yacht.id, yacht.yacht_name, etc.
});
```

### Files to Check
- `/includes/class-yolo-ys-base-manager.php` - All AJAX handlers
- Any new AJAX endpoints

### Prevention
1. **Always** send arrays directly to `wp_send_json_success()`
2. **Never** wrap in another object unless absolutely necessary
3. **Test** AJAX endpoints in browser console
4. **Check** JavaScript expects `response.data` to be the array

---

## 🔴 RECURRING ERROR #3: Signature Pad Not Visible

### Pattern
Signature pad canvas is initialized before the form is visible, causing dimension issues.

### Symptoms
- Canvas appears but is too small
- Signature doesn't draw properly
- Canvas is blank or invisible

### Root Cause
```javascript
// WRONG:
$('#new-checkin-btn').on('click', function() {
    $('#checkin-form-container').slideDown();
    initializeSignaturePad();  // <-- Canvas not visible yet!
});
```

### Why It's Wrong
1. `slideDown()` is **asynchronous** - it takes time to complete
2. Canvas dimensions are calculated when it's **not yet visible**
3. Results in 0x0 or incorrect dimensions

### Correct Pattern
```javascript
// CORRECT:
$('#new-checkin-btn').on('click', function() {
    $('#checkin-form-container').slideDown(function() {
        // Callback runs AFTER slideDown completes
        initializeSignaturePad();
    });
});

function initializeSignaturePad() {
    const canvas = document.getElementById('checkin-signature-pad');
    if (canvas) {
        // Always recreate to ensure proper dimensions
        if (checkinSignaturePad) {
            checkinSignaturePad.clear();
        }
        checkinSignaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });
        // Resize canvas to match display size
        resizeCanvas(canvas);
    }
}

function resizeCanvas(canvas) {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
}
```

### Files to Check
- `/admin/partials/base-manager-checkin.php`
- `/admin/partials/base-manager-checkout.php`
- Any page with signature pads

### Prevention
1. **Always** initialize signature pad in slideDown **callback**
2. **Always** resize canvas to match display dimensions
3. **Always** account for high-DPI displays (devicePixelRatio)
4. **Test** on both desktop and mobile devices

---

## 🟡 RECURRING ERROR #4: Database Column Name Mismatches

### Pattern
SQL queries use wrong column names that don't match the database schema.

### Symptoms
- SQL error in logs: "Unknown column 'check_in_date'"
- Empty results when data should exist
- PHP warnings about undefined properties

### Root Cause
```php
// WRONG:
$bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY check_in_date ASC");
// Column is actually named 'date_from' in the database!
```

### Why It's Wrong
1. Column names in queries don't match actual database schema
2. Assumes column names without checking schema
3. No error handling for SQL errors

### Correct Pattern
```php
// CORRECT:
// 1. Check the actual database schema first!
// 2. Use correct column names
$bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_from ASC");

// 3. Always check for errors
if ($wpdb->last_error) {
    error_log('YOLO YS: Database error: ' . $wpdb->last_error);
    wp_send_json_error(array('message' => 'Database error'));
    return;
}
```

### Files to Check
- `/includes/class-yolo-ys-base-manager.php` - All database queries
- `/includes/class-yolo-ys-base-manager-database.php` - Schema definitions

### Prevention
1. **Always** check database schema before writing queries
2. **Always** use exact column names from schema
3. **Always** check `$wpdb->last_error` after queries
4. **Document** schema in database class

---

## 🟡 RECURRING ERROR #5: Missing Data Validation

### Pattern
Functions don't validate input parameters before using them.

### Symptoms
- PHP warnings: "Trying to get property of non-object"
- Crashes when data is invalid or missing
- Silent failures with no error messages

### Root Cause
```php
// WRONG:
public static function send_payment_reminder($booking) {
    // No validation!
    $booking_reference = 'BM-' . $booking->bm_reservation_id;  // <-- Crash if $booking is null!
    $to = $booking->customer_email;  // <-- Crash if property doesn't exist!
}
```

### Why It's Wrong
1. No check if `$booking` is null or empty
2. No check if required properties exist
3. No error logging for debugging
4. No graceful failure handling

### Correct Pattern
```php
// CORRECT:
public static function send_payment_reminder($booking) {
    // Validate booking object
    if (!$booking) {
        error_log('YOLO YS: send_payment_reminder - booking object is null');
        return false;
    }
    
    if (empty($booking->customer_email)) {
        error_log('YOLO YS: send_payment_reminder - customer email is empty');
        return false;
    }
    
    // Ensure dependencies are loaded
    if (!class_exists('YOLO_YS_Price_Formatter')) {
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-price-formatter.php';
    }
    
    // Now safe to use
    $to = $booking->customer_email;
    // ...
}
```

### Files to Check
- `/includes/class-yolo-ys-email.php` - All email functions
- `/includes/class-yolo-ys-base-manager.php` - All AJAX handlers
- Any function that accepts objects as parameters

### Prevention
1. **Always** validate function parameters at the start
2. **Always** check required properties exist
3. **Always** log errors for debugging
4. **Always** return false or error on invalid data
5. **Test** with invalid/missing data

---

## 🔵 RECURRING ERROR #6: Accordion Flash/Collapse Bug

### Pattern
Accordion sections flash open then immediately close when clicked, requiring double-click to open.

### Symptoms
- Click accordion → flashes open → closes immediately
- Have to click twice to open accordion
- Accordion animation looks broken

### Root Cause
```javascript
// WRONG:
$('.yolo-section-toggle').on('click', function() {
    var section = $(this).closest('.yolo-accordion-section');
    section.toggleClass('open');  // CSS shows it instantly
    section.find('.yolo-section-content').slideToggle(300);  // jQuery sees it's visible → hides it!
});
```

```css
/* WRONG:
.yolo-section-content {
    display: none;
}

.yolo-accordion-section.open .yolo-section-content {
    display: block;  /* Shows instantly when class is added */
}
```

### Why It's Wrong
1. CSS changes `display` **instantly** when `.open` class is added
2. jQuery's `slideToggle()` checks if element is visible
3. Sees it's visible (from CSS) → decides to hide it
4. Results in flash/collapse behavior

### Correct Pattern
```javascript
// CORRECT: Only toggle class, let CSS handle animation
$('.yolo-section-toggle').on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    var section = $(this).closest('.yolo-accordion-section');
    section.toggleClass('open');
});
```

```css
/* CORRECT: Use max-height transition instead of display */
.yolo-section-content {
    max-height: 0;
    overflow: hidden;
    padding: 0 20px;
    transition: max-height 0.3s ease-out, padding 0.3s ease-out;
}

.yolo-accordion-section.open .yolo-section-content {
    max-height: 5000px; /* Large enough to fit any content */
    padding: 20px;
    transition: max-height 0.4s ease-in, padding 0.3s ease-in;
}
```

### Files to Check
- `/public/js/yolo-guest-dashboard.js`
- `/public/css/guest-dashboard.css`
- `/public/partials/yolo-ys-guest-dashboard.php`
- Any page with accordion components

### Prevention
1. **Never** mix CSS `display` toggle with jQuery `slideToggle()`
2. **Always** use either CSS-only OR JavaScript-only for animations
3. **Use** `max-height` transition for CSS-based accordions
4. **Remove** inline `style="display:block;"` from templates
5. **Test** accordion behavior after any changes

---

## 🔵 RECURRING ERROR #7: Mobile Responsive Issues (100vw Problem)

### Pattern
Content overflows horizontally on mobile devices, requiring horizontal scrolling.

### Symptoms
- User has to scroll left/right on mobile
- Content is cut off on small screens
- Horizontal scrollbar appears on mobile

### Root Cause
```css
/* WRONG: */
max-width: 100vw !important;  /* 100vw INCLUDES scrollbar width! */
```

### Why It's Wrong
1. `100vw` = 100% of **viewport width** INCLUDING scrollbar
2. On mobile with scrollbar, `100vw` is wider than visible area
3. Causes horizontal overflow
4. Missing `overflow-x: hidden` on html/body

### Correct Pattern
```css
/* CORRECT: */
html, body {
    overflow-x: hidden !important;
    max-width: 100% !important;  /* Use 100% NOT 100vw */
}

.container {
    max-width: 100% !important;  /* Use 100% NOT 100vw */
    overflow-x: hidden !important;
}

/* WordPress alignfull fix */
.alignfull {
    max-width: 100% !important;
    width: 100% !important;
}
```

### Files to Check
- `/public/css/emergency-override.css`
- `/public/css/global-mobile-responsive.css`
- All page-specific CSS files

### Prevention
1. **Never** use `100vw` for max-width (use `100%` instead)
2. **Always** add `overflow-x: hidden` to html/body
3. **Always** test on real mobile devices
4. **Check** for WordPress theme classes like `alignfull`
5. **Use** `100%` for widths, not `100vw`

---

## 🔵 RECURRING ERROR #8: Mobile Responsive Issues

### Pattern
Content overflows horizontally on mobile devices, requiring horizontal scrolling.

### Symptoms
- User has to scroll left/right on mobile
- Content is cut off on small screens
- Images or tables break layout

### Root Cause
1. **Fixed widths** instead of responsive widths
2. **Images without max-width: 100%**
3. **Tables without overflow handling**
4. **Overriding Bootstrap's mobile-first design**

### Correct Pattern
```css
/* CORRECT: Let Bootstrap 5 handle responsive layout */

/* Only fix actual overflow issues */
html, body {
    overflow-x: hidden !important;
    max-width: 100vw !important;
}

/* Make images responsive */
img {
    max-width: 100% !important;
    height: auto !important;
}

/* Make tables scrollable if needed */
table {
    display: block !important;
    overflow-x: auto !important;
    max-width: 100% !important;
}

/* DON'T override Bootstrap grid spacing! */
/* Bootstrap 5 is already mobile-first */
```

### Files to Check
- `/public/css/global-mobile-responsive.css` - Universal mobile fixes
- All page-specific CSS files
- Any new CSS files

### Prevention
1. **Never** use fixed widths (use percentages or max-width)
2. **Always** test on real mobile devices
3. **Never** override Bootstrap 5 grid spacing
4. **Always** make images responsive
5. **Test** on multiple screen sizes (320px, 375px, 414px, 768px)

---

## 📋 CHECKLIST: Before Committing Code

### PHP Files
- [ ] ABSPATH check is correct (exit immediately)
- [ ] Permission checks use correct capability (`edit_posts`)
- [ ] All function parameters are validated
- [ ] Database queries use correct column names
- [ ] `$wpdb->last_error` is checked after queries
- [ ] Error logging is in place
- [ ] No PHP warnings or notices

### JavaScript Files
- [ ] AJAX responses are handled correctly
- [ ] Signature pads initialize AFTER form is visible
- [ ] Canvas resizing accounts for high-DPI displays
- [ ] No console errors
- [ ] Touch events work on mobile

### CSS Files
- [ ] No fixed widths (use max-width instead)
- [ ] Images are responsive (max-width: 100%)
- [ ] Tables handle overflow properly
- [ ] Bootstrap 5 grid is not overridden
- [ ] Tested on mobile devices

### Testing
- [ ] Page loads without errors
- [ ] Functionality works as expected
- [ ] Mobile responsive (no horizontal scroll)
- [ ] Tested on multiple browsers
- [ ] Tested on multiple devices

---

## 🎯 GOLDEN RULES

1. **Test everything** before committing
2. **Validate all inputs** before using them
3. **Check database schema** before writing queries
4. **Respect Bootstrap 5** mobile-first design
5. **Log errors** for debugging
6. **Document changes** in CHANGELOG
7. **Update this file** if you find new recurring errors

---

**Remember:** These errors have occurred multiple times. Following these patterns will prevent them from happening again!

---

**End of Recurring Errors Documentation**

*Last Updated: December 3, 2025 - v17.11.1*
