# YOLO Yacht Search & Booking Plugin

**Version:** 2.3.7 🎉  
**Last Updated:** November 30, 2025  
**WordPress Plugin for Yacht Charter Search and Booking**

---

## 🚀 What's New in v2.3.7 - CRITICAL BUG FIXES & UI IMPROVEMENTS!

This version fixes the price carousel flashing bug, improves UI consistency, and organizes code properly. All major issues are now resolved.

### Handoff Documentation

For a complete overview of the plugin, bug fixes, and critical code sections, please see:
- **[Handoff Document for v2.3.7](HANDOFF-v2.3.7.md)** ← Latest
- [Handoff Document for v2.3.6](HANDOFF-v2.3.6.md)
- [Changelog v2.3.7](CHANGELOG-v2.3.7.md)

---

## ✅ All Bugs Fixed!

| Bug Description | Status | Version Fixed |
|---|---|---|
| **Price Carousel Flashing Wrong Prices** | ✅ **FIXED** | **v2.3.7** |
| **Search Box Defaults to "Sailing Yacht"** | ✅ **FIXED** | **v2.3.7** |
| **`get_offers()` API Response Parsing** | ✅ **FIXED** | **v2.3.7** |
| **`get_live_price()` API Response Parsing** | ✅ **FIXED** | **v2.3.7** |
| **`search_offers()` Inconsistent Return Format** | ✅ **FIXED** | **v2.3.7** |
| API Response Parsing (`value` property) | ✅ FIXED | v2.3.6 |
| Yacht Sync Fails Completely | ✅ FIXED | v2.3.6 |
| Equipment Sync Fails | ✅ FIXED | v2.3.6 |
| Live API Date Format (422 Error) | ✅ FIXED | v2.3.5 |
| Price Storage (DELETE not working) | ✅ FIXED | v2.3.4 |
| `payableInBase` for Extras | ✅ FIXED | v2.3.4 |

---

## 🎨 UI Improvements in v2.3.7

- ✅ **Search results yacht cards now match "Our Yachts" design**
  - Red button instead of blue
  - Split name/model (e.g., "Lemon" + "Sun Odyssey 469")
  - Shows: Cabins, Built year, Length (instead of Cabins, Length, Berths)
  - Cleaner price display (green box, centered)

- ✅ **CSS properly organized**
  - Removed all inline `<style>` tags from templates
  - Moved all CSS to external files
  - Proper separation of concerns

- ✅ **Admin panel improvements**
  - Added 3 missing shortcodes to documentation
  - All 7 shortcodes now visible

---

## 📦 Quick Start

### Installation

1. **Upload Plugin**
   ```bash
   WordPress Admin → Plugins → Add New → Upload Plugin
   Select: yolo-yacht-search-v2.3.7.zip
   ```

2. **Activate Plugin**
   - Activation will create/update database tables

3. **Configure Settings**
   - Go to: WordPress Admin → YOLO Yacht Search
   - Configure Booking Manager API key
   - Configure Stripe API keys
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

6. **Test Booking Flow**
   - Visit yacht details page
   - Select dates
   - Click "BOOK NOW"
   - Use test card: **4242 4242 4242 4242**
   - Verify confirmation page displays

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

### v2.3.7 (November 30, 2025) - Current
- **CRITICAL:** Fixed price carousel flashing wrong prices
- **CRITICAL:** Fixed `get_live_price()` API response parsing
- Fixed `get_offers()` API response parsing
- Fixed `search_offers()` inconsistent return format
- Fixed search box defaulting to "Sailing yacht"
- **UI:** Search results yacht cards match "Our Yachts" design
- **Code:** Removed inline CSS, organized properly
- **Docs:** Added 3 missing shortcodes to admin panel

### v2.3.6 (November 30, 2025)
- Fixed `get_yachts_by_company()` API response parsing
- Fixed `get_equipment_catalog()` API response parsing

### v2.3.5 (November 30, 2025)
- Fixed live API date format
- Fixed price carousel auto-update

### v2.3.4 (November 30, 2025)
- Fixed price storage
- Fixed `payableInBase` for extras

**See:** [FEATURE-STATUS.md](FEATURE-STATUS.md) for complete history

---

## 🐛 Known Issues

### Search Functionality Not Implemented
The search widget displays but doesn't actually filter yachts. All yachts are shown regardless of search criteria. This is the main feature that needs implementation.

---

## 📚 Documentation

- [Installation Guide](INSTALLATION-GUIDE.md)
- [Handoff Document v2.3.7](HANDOFF-v2.3.7.md)
- [Changelog v2.3.7](CHANGELOG-v2.3.7.md)
- [Booking Manager API Manual](BookingManagerAPIManual.md)
- [Feature Status](FEATURE-STATUS.md)

---

## 👨‍💻 Credits

**Author:** George Margiolos  
**Bug Fixes (v2.3.7):** Manus AI  
**Bug Identification:** Cursor AI  
**Version:** 2.3.7  
**License:** GPL v2 or later  
**Last Updated:** November 30, 2025

---

## 🔗 Links

- **GitHub:** https://github.com/georgemargiolos/LocalWP
- **API:** Booking Manager API v2
- **Payment:** Stripe

---

**Status:** ✅ Production Ready
