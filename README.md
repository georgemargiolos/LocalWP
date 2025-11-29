# YOLO Yacht Search & Booking Plugin

**WordPress Plugin for Yacht Charter Businesses**  
**Current Version:** 1.8.8  
**Status:** ✅ STABLE - All Critical Fixes Implemented!  
**Last Updated:** November 29, 2025

---

## 🎉 Version 1.8.8 - STABLE RELEASE: ALL CRITICAL FIXES!

**This version implements all critical fixes for yacht sync, date picker, and extras display.**

### What's New in v1.8.8

-   **FIX:** Extras now aggregate from ALL products, not just products[0]

### What Was New in v1.8.7

-   **FIX:** Yacht sync infinite loop with equipment caching
-   **FIX:** Date picker initialization from URL parameters

### What Was New in v1.8.6

-   **FEAT:** Added equipment catalog sync button to admin panel

---

## 📦 Quick Start

### Installation

1. Download `yolo-yacht-search-v1.8.8.zip`
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. Activate plugin
4. Configure settings (company IDs, Google Maps API key)
5. **Sync Equipment Catalog** (Admin → YOLO Yacht Search → Sync Equipment Catalog)
6. **Sync yachts** (Admin → YOLO Yacht Search → Sync All Yachts Now)
7. **Sync offers** (Admin → YOLO Yacht Search → Sync Weekly Offers)
8. Test search on home page

### Configuration

**Admin Panel:** WordPress Admin → YOLO Yacht Search

**Required Settings:**
- API Key (Booking Manager)
- My Company ID (default: 7850)
- Friend Companies (default: 4366,3604,6711)
- Google Maps API Key (for location maps)

---

## ✅ Features

### Completed ✅ (95%)
- ✅ Booking Manager API integration (GET endpoints)
- ✅ Database caching system (7 custom tables)
- ✅ Yacht sync functionality
- ✅ **Equipment catalog sync** (NEW in v1.8.2!)
- ✅ **Weekly offers sync** (FIXED in v1.8.4!)
- ✅ **Search functionality** (COMPLETE in v1.7.2!)
- ✅ **Search results with yacht cards** (NEW in v1.7.2!)
- ✅ **Date-specific pricing display** (NEW in v1.7.2!)
- ✅ Our Fleet page
- ✅ Yacht details page with image carousel
- ✅ **4-week price carousel** (horizontal scroll)
- ✅ **Week selection updates date picker** (NEW in v1.6.4!)
- ✅ **Price display above Book Now** (NEW in v1.6.4!)
- ✅ **Description accordion** (collapsible, NEW in v1.6.6!)
- ✅ **Equipment section** (FIXED in v1.8.2!)
- ✅ **Extras section** (FIXED in v1.8.8!)
- ✅ Google Maps integration (iframe embed)
- ✅ Date picker (Litepicker)
- ✅ Quote request form
- ✅ Admin dashboard with sync buttons

### Pending 🚧 (5%)
- 🔨 Booking flow ("Book Now" button functionality)
- 🔨 Stripe payment integration
- 🔨 Booking creation via API POST
- 🔨 Automated sync scheduling
- 🔨 Email notifications

---

## 🔧 Technical Details

### Architecture

**Frontend:** WordPress pages with shortcodes  
**Backend:** PHP with WordPress database  
**API:** Booking Manager REST API v2  
**Caching:** Local WordPress database (7 tables)  
**Search:** Database-first queries (fast!)

### Database Tables

- `wp_yolo_yachts` - Yacht data
- `wp_yolo_yacht_images` - Yacht images
- `wp_yolo_yacht_equipment` - Equipment lists
- `wp_yolo_equipment_catalog` - Master equipment list (NEW in v1.8.2!)
- `wp_yolo_yacht_extras` - Extras/add-ons
- `wp_yolo_yacht_prices` - Weekly offers (Saturday-to-Saturday)
- `wp_yolo_yacht_companies` - Company information

### API Endpoints Used

- ✅ `GET /companies` - Fetch company data
- ✅ `GET /yachts` - Fetch yacht data
- ✅ `GET /equipment` - Fetch equipment catalog (NEW in v1.8.2!)
- ✅ `GET /offers` - Fetch weekly offers (per company)
- ❌ `POST /bookings` - Create booking (not implemented yet)

---

## 📝 Complete Version History

### v1.8.8 (November 29, 2025) - CURRENT ✅
**EXTRAS FIX!**
- ✅ Extras now aggregate from ALL products, not just products[0].

### v1.8.7 (November 29, 2025)
**CRITICAL SYNC & DATE PICKER FIXES!**
- ✅ Fixed yacht sync infinite loop with equipment caching.
- ✅ Fixed date picker initialization from URL parameters.

### v1.8.6 (November 29, 2025)
**EQUIPMENT SYNC BUTTON!**
- ✅ Added equipment catalog sync button to admin panel.

---

## 🔗 Resources

- [Booking Manager API Documentation](https://support.booking-manager.com/hc/en-us/articles/360015601200)
- [Swagger UI](https://api.booking-manager.com/swagger-ui.html)
- GitHub Repository: `georgemargiolos/LocalWP`
