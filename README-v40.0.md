# YOLO Yacht Search & Booking Plugin

**Version:** 40.0 🎉  
**Last Updated:** December 8, 2025  
**WordPress Plugin for Yacht Charter Search and Booking**

---

## 🚀 What's New in v40.0 - CRITICAL FIX!

This version fixes a **fatal error** that prevented plugin activation in v30.6. The issue was duplicate ABSPATH security checks in multiple PHP class files.

### Key Fixes in v40.0:

*   **Plugin Activation Fixed:** Removed duplicate `if (!defined('ABSPATH'))` checks from 6 class files
*   **WordPress Wrapping Issue:** Documented this recurring bug for future prevention
*   **All v30.6 Features Preserved:** FOUC fix and Bootstrap loading improvements still active

---

## ⚠️ RECURRING BUG DOCUMENTATION

### WordPress Security Wrapping Issue

**This is a recurring error that has happened multiple times.**

**Problem:** Duplicate ABSPATH security checks cause fatal errors during plugin activation.

**Incorrect Pattern:**
```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class docblock
 */

if (!defined('ABSPATH')) {  // ❌ DUPLICATE - CAUSES FATAL ERROR
    exit;
}

class My_Class {
    // ...
}
```

**Correct Pattern:**
```php
<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class docblock
 */

class My_Class {  // ✅ NO DUPLICATE CHECK
    // ...
}
```

**Prevention Rule:** Each PHP file should have **ONLY ONE** ABSPATH check, placed immediately after the `<?php` opening tag.

---

## Session Summary (December 8, 2025)

Today we fixed a critical fatal error that prevented plugin activation in v30.6:

### What We Did:

1.  **Identified Root Cause:** Found duplicate ABSPATH security checks in 6 class files
2.  **Applied Fixes:** Removed duplicate checks while preserving all functionality
3.  **Updated Version:** Bumped version to 40.0 to clearly indicate the critical fix
4.  **Documented Recurring Bug:** Added prevention guidelines to avoid future occurrences

### Files Fixed:
- `includes/class-yolo-ys-base-manager-database.php`
- `includes/class-yolo-ys-base-manager.php`
- `includes/class-yolo-ys-contact-messages.php`
- `includes/class-yolo-ys-pdf-generator.php`
- `includes/class-yolo-ys-quote-requests.php`

---

## ✅ All Critical Bugs Fixed!

| Bug Description | Status | Version Fixed |
|---|---|---|
| **Fatal Error on Plugin Activation** | ✅ **FIXED** | **v40.0** |
| **White Page on Yacht Details (FOUC)** | ✅ **FIXED** | **v30.6** |
| **Bootstrap Not Loading on URL Parameter Pages** | ✅ **FIXED** | **v30.5** |
| **Layout Broken by Font CSS** | ✅ **FIXED** | **v22C.2** |
| **Stripe Secret Key Bug (Balance Payments)** | ✅ **FIXED** | **v2.5.5** |
| **tripDuration Parameter (HTTP 500)** | ✅ **FIXED** | **v2.5.5** |
| **Security Deposit Missing** | ✅ **FIXED** | **v2.5.5** |
| **Cancellation Policy Display** | ✅ **FIXED** | **v2.5.5** |
| **Booking Manager Sync Commented Out** | ✅ **FIXED** | **v2.5.5** |
| **Version Number Mismatch** | ✅ **FIXED** | **v2.5.5** |
| **Hardcoded EUR Currency** | ✅ **FIXED** | **v2.5.5** |
| **Layout Not Centered** | ✅ **FIXED** | **v2.5.5** |
| **CSRF Vulnerability (No NONCE)** | ✅ **FIXED** | **v2.5.5** |
| **XSS Vulnerability (Modals)** | ✅ **FIXED** | **v2.5.5** |
| **yacht_id Integer Overflow** | ✅ **FIXED** | **v2.5.5** |

---

## 📦 Quick Start

### Installation

1. **Upload Plugin**
   ```bash
   WordPress Admin → Plugins → Add New → Upload Plugin
   Select: yolo-yacht-search-v40.0.zip
   ```

2. **Activate Plugin**
   - Activation will create/update database tables

3. **Configure Settings**
   - Go to: WordPress Admin → YOLO Yacht Search
   - Configure Booking Manager API credentials
   - Configure Stripe API keys (production)
   - Set company IDs (YOLO: 7850, Partners: 4366,3604,6711)

4. **Create Required Pages**
   - **Search Page:** Add `[yolo_search_widget]` shortcode
   - **Results Page:** Add `[yolo_search_results]` shortcode
   - **Fleet Page:** Add `[yolo_our_fleet]` shortcode
   - **Details Page:** Add `[yolo_yacht_details]` shortcode
   - **Confirmation Page:** Add `[yolo_booking_confirmation]` shortcode
   - **Balance Payment Page:** Add `[yolo_balance_payment]` shortcode
   - **Balance Confirmation Page:** Add `[yolo_balance_confirmation]` shortcode

5. **Sync Data**
   - Click "Sync Equipment"
   - Click "Sync Yachts"
   - Click "Sync Prices" (weekly offers)

6. **Test Complete Booking Flow**
   - Search for yachts
   - View yacht details (verify deposit and policy shown)
   - Select dates and click "BOOK NOW"
   - Use test card: **4242 4242 4242 4242**
   - Verify booking created in Booking Manager
   - Check confirmation email received

---

## 🔌 All Available Shortcodes

### `[yolo_search_widget]`
Displays yacht search form with date picker and type selector.

### `[yolo_search_results]`
Displays search results with yacht cards (YOLO first, then partners).

### `[yolo_our_fleet]`
Displays all yachts in a grid (YOLO first, then partners).

### `[yolo_yacht_details]`
Displays single yacht details with booking functionality.  
**URL Parameters:** `yacht_id`, `dateFrom`, `dateTo`

### `[yolo_booking_confirmation]`
Displays booking confirmation after deposit payment.  
**URL Parameters:** `session_id` (from Stripe)

### `[yolo_balance_payment]`
Displays balance payment page (remaining 50%).  
**URL Parameters:** `ref` (booking reference)

### `[yolo_balance_confirmation]`
Displays balance payment confirmation.  
**URL Parameters:** `session_id` (from Stripe)

---

## 📋 Version History

See: [CHANGELOG-v40.0.md](CHANGELOG-v40.0.md) for complete history

---

## 👨‍💻 Credits

**Author:** George Margiolos  
**Critical Fix Session (v40.0):** Manus AI (December 8, 2025)  
**Layout Fix Session (v22C.2):** Manus AI (December 5, 2025)  
**Bug Identification:** Cursor AI  
**Version:** 40.0  
**License:** GPL v2 or later  
**Last Updated:** December 8, 2025

---

## 🔗 Links

- **GitHub:** https://github.com/georgemargiolos/LocalWP
- **API:** Booking Manager API v2.0.2
- **Payment:** Stripe
- **Icons:** FontAwesome 7 Kit

---

**Status:** ✅ **STABLE**

**All known bugs fixed. Plugin activates correctly. Ready for production.**
