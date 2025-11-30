# YOLO Yacht Search & Booking Plugin

**Version:** 2.3.6 🎉  
**Last Updated:** November 30, 2025  
**WordPress Plugin for Yacht Charter Search and Booking**

---

## 🚀 What's New in v2.3.6 - CRITICAL BUG FIXES!

This version fixes several critical bugs that were preventing the plugin from working correctly. All major issues are now resolved.

### Handoff Documentation

For a complete overview of the plugin, bug fixes, and critical code sections, please see the [Handoff Document for v2.3.6](HANDOFF-v2.3.6.md).

---

## ✅ All Bugs Fixed!

| Bug Description | Status | Version Fixed |
|---|---|---|
| **API Response Parsing (`value` property)** | ✅ **FIXED** | **v2.3.6** |
| Yacht Sync Fails Completely | ✅ **FIXED** | **v2.3.6** |
| Equipment Sync Fails | ✅ **FIXED** | **v2.3.6** |
| Live API Date Format (422 Error) | ✅ FIXED | v2.3.5 |
| Price Carousel Shows Wrong Prices | ✅ FIXED | v2.3.5 |
| Search Box Defaults to "Sailing Yacht" | ✅ FIXED | v2.3.5 |
| Price Storage (DELETE not working) | ✅ FIXED | v2.3.4 |
| `payableInBase` for Extras | ✅ FIXED | v2.3.4 |

---

## 📦 Quick Start

### Installation

1. **Upload Plugin**
   ```bash
   WordPress Admin → Plugins → Add New → Upload Plugin
   Select: yolo-yacht-search-v2.3.6.zip
   ```

2. **Activate Plugin**
   - Activation will create/update database tables

3. **Configure Settings**
   - Go to: WordPress Admin → YOLO Yacht Search
   - Configure Booking Manager API key
   - Set company IDs (YOLO: 7850, Partners: 4366,3604,6711)

4. **Create Required Pages**
   - **Search Page:** Add `[yolo_search_widget]` shortcode
   - **Results Page:** Add `[yolo_search_results]` shortcode
   - **Fleet Page:** Add `[yolo_our_fleet]` shortcode
   - **Details Page:** Add `[yolo_yacht_details]` shortcode
   - **Confirmation Page:** Add `[yolo_booking_confirmation]` shortcode

5. **Sync Data**
   - Click "Sync Equipment" (will now work!)
   - Click "Sync Yachts" (will now work!)
   - Click "Sync Prices" (weekly offers)

6. **Test Booking Flow**
   - Visit yacht details page
   - Select dates
   - Click "BOOK NOW"
   - Use test card: **4242 4242 4242 4242**
   - Verify confirmation page displays

---

## 🔌 Shortcodes

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
Displays booking confirmation after payment.
**URL Parameters:** `session_id` (from Stripe)

---

## 👨‍💻 Credits

**Author:** George Margiolos & Manus AI  
**Version:** 2.3.6  
**License:** GPL v2 or later  
**Last Updated:** November 30, 2025
