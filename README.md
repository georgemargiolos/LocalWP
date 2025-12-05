# YOLO Yacht Search & Booking Plugin

**Version:** 22C.2 🎉  
**Last Updated:** December 5, 2025  
**WordPress Plugin for Yacht Charter Search and Booking**

---

## 🚀 What's New in v22C.2 - Layout Fix!

This version fixes a critical layout issue introduced in v22C.0 where the search widget and yacht details page were broken. The font inheritance CSS has been corrected to be less aggressive, ensuring layouts are stable while still inheriting WordPress theme fonts.

### Handoff Documentation

For a complete overview of the plugin, bug fixes, and critical code sections, please see:
- **[Handoff Document for Dec 5, 2025](yolo-yacht-search/HANDOFF-SESSION-December5-2025.md)** ← **LATEST** ✅
- [Handoff Document for v2.5.5](HANDOFF-v2.5.5-COMPLETE.md)
- [Changelog](yolo-yacht-search/CHANGELOG.md)

---

## ✅ All Critical Bugs Fixed!

| Bug Description | Status | Version Fixed |
|---|---|---|
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
   Select: yolo-yacht-search-v22C.2.zip
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

See: [CHANGELOG.md](yolo-yacht-search/CHANGELOG.md) for complete history

---

## 👨‍💻 Credits

**Author:** George Margiolos  
**Layout Fix Session (v22C.2):** Manus AI (December 5, 2025)  
**Bug Identification:** Cursor AI  
**Version:** 22C.2  
**License:** GPL v2 or later  
**Last Updated:** December 5, 2025

---

## 🔗 Links

- **GitHub:** https://github.com/georgemargiolos/LocalWP
- **API:** Booking Manager API v2.0.2
- **Payment:** Stripe
- **Icons:** FontAwesome 7 Kit

---

**Status:** ✅ **STABLE**

**All known bugs fixed. Ready for production.**
