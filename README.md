# YOLO Yacht Search & Booking Plugin

**WordPress Plugin for Yacht Charter Businesses**  
**Current Version:** 1.8.2  
**Status:** ✅ STABLE - Equipment Sync & All Fixes Implemented!  
**Last Updated:** November 29, 2025

---

## 🎉 Version 1.8.2 - STABLE RELEASE: EQUIPMENT SYNC & ALL FIXES!

**This version implements a critical fix for the equipment data sync, along with all other fixes from the API documentation.**

### What's New in v1.8.2

1. **Equipment Data Sync (CRITICAL FIX)**
   - Implemented a robust equipment catalog sync process to fetch equipment names from the `/equipment` endpoint.
   - Created a new `wp_yolo_equipment_catalog` table to store the master list of all equipment.
   - Updated the yacht sync process to use the new catalog for name mapping.
   - **Result:** The equipment for all yachts is now correctly synced and displayed on the yacht details page.

2. **Date Picker ID Mismatch Fix**
   - Fixed the date picker initialization issue as described in the API documentation.
   - Changed the date picker input ID from `yolo-ys-yacht-dates` to `dateRangePicker`.
   - **Result:** The date picker now correctly initializes and displays the dates passed from the search results page.

3. **July Week Default Selection**
   - Implemented server-side and client-side logic to default to the first available week in July when no dates are provided.
   - **Result:** Users now see a default price and date selection when visiting a yacht details page without search parameters.

### What Was New in v1.8.0

- Added Litepicker initialization to show search dates on page load
- Date picker now updates when clicking price carousel weeks
- Combined extras into one row (obligatory + optional side-by-side)
- Responsive two-column layout for extras (stacks on mobile)
- Color-coded extras headings (red for obligatory, blue for optional)

### What Was New in v1.7.9

- **Universal Price Formatting Fix:** Applied the price formatting fix universally across ALL pages (search results, yacht details carousel, price display box).
- **Carousel Click Updates Date Picker:** Clicking a week in the price carousel now updates the date picker.

---

## 📦 Quick Start

### Installation

1. Download `yolo-yacht-search-v1.8.2.zip`
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
- ✅ **Weekly offers sync** (FIXED in v1.6.3!)
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
- ✅ **Extras section** (obligatory + optional)
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

### v1.8.2 (November 29, 2025) - CURRENT ✅
**EQUIPMENT SYNC & ALL FIXES!**
- ✅ Implemented equipment catalog sync to fix empty equipment section.
- ✅ Fixed date picker ID mismatch as described in API documentation.
- ✅ Implemented default July week selection on yacht details page.

### v1.8.0 (November 29, 2025)
- ✅ Added Litepicker initialization to show search dates on page load
- ✅ Date picker now updates when clicking price carousel weeks
- ✅ Combined extras into one row (obligatory + optional side-by-side)

### v1.7.9 (November 29, 2025)
- ✅ Universal price formatting fix across all pages
- ✅ Carousel click updates date picker

---

## 🔗 Resources

- [Booking Manager API Documentation](https://support.booking-manager.com/hc/en-us/articles/360015601200)
- [Swagger UI](https://api.booking-manager.com/swagger-ui.html)
- GitHub Repository: `georgemargiolos/LocalWP`
