# YOLO Yacht Search & Booking Plugin

WordPress plugin for YOLO Charters with Booking Manager API v2 integration, featuring search widget, fleet display, yacht details, and database storage.

## Current Version: v1.9.4 ✅

**Status**: All critical bugs fixed! Yacht sync working perfectly.

## Features

✅ **Search Widget** - Yacht search form styled like yolo-charters.com  
✅ **Search Results** - Display results with YOLO boats prioritized  
✅ **Our Fleet** - Beautiful grid display of all yachts  
✅ **Yacht Details** - Individual yacht pages with image carousel  
✅ **Database Storage** - All yacht data stored in WordPress database  
✅ **Three-Button Sync** - Separate sync for equipment catalog, yachts, and weekly offers  
✅ **Equipment Icons** - FontAwesome 6.4.0 icons for all equipment  
✅ **Litepicker Integration** - Beautiful date range picker with mobile support  
✅ **Company Prioritization** - YOLO boats (7850) shown first, then partner companies  
✅ **Admin Dashboard** - Sync statistics and easy configuration  
✅ **Responsive Design** - Mobile-friendly interface  
✅ **Easy Shortcodes** - No block editor needed!

## Installation

1. Upload `yolo-yacht-search.zip` to WordPress → Plugins → Add New
2. Activate the plugin
3. Go to **YOLO Yacht Search** in the admin menu
4. Click **"Sync Equipment Catalog"** (green button) first
5. Click **"Sync Yachts"** (red button) to fetch yacht data
6. Click **"Sync Weekly Offers"** (blue button) for pricing
7. Configure page settings (already prefilled!)

## Quick Setup (5 Steps!)

### Step 1: Sync Data (3 Buttons)
1. Go to **YOLO Yacht Search** in WordPress admin
2. Click **"Sync Equipment Catalog"** (green) - syncs ~50 equipment items
3. Click **"Sync Yachts"** (red) - syncs all yacht data
4. Click **"Sync Weekly Offers"** (blue) - syncs pricing for the year
5. See statistics: Total yachts, YOLO yachts, Partner yachts

### Step 2: Create Required Pages

**Search Results Page:**
1. Create new page: "Search Results"
2. Add shortcode: `[yolo_search_results]`
3. Publish

**Yacht Details Page:**
1. Create new page: "Yacht Details"
2. Add shortcode: `[yolo_yacht_details]`
3. Publish

**Our Fleet Page (Optional):**
1. Create new page: "Our Fleet"
2. Add shortcode: `[yolo_our_fleet]`
3. Publish

### Step 3: Configure Settings
1. Go to **YOLO Yacht Search** settings
2. Select "Search Results" page in dropdown
3. Select "Yacht Details" page in dropdown
4. Save settings (all other settings prefilled!)

### Step 4: Add Search Widget
1. Edit your homepage
2. Add shortcode: `[yolo_search_widget]`
3. Publish

**Done!** 🎉

## Shortcodes

### `[yolo_search_widget]`
Displays the yacht search form with boat type dropdown and date picker.

**Usage:**
```
[yolo_search_widget]
```

**Where:** Homepage or any search page

---

### `[yolo_search_results]`
Displays search results with YOLO boats first, then partner boats.

**Usage:**
```
[yolo_search_results]
```

**Where:** Dedicated search results page (select in settings)

---

### `[yolo_our_fleet]`
Displays all yachts in beautiful cards - YOLO boats first, then partners.

**Usage:**
```
[yolo_our_fleet]`
```

**Where:** Fleet browsing page

**Features:**
- Grid layout (3 columns → 2 → 1 responsive)
- Yacht image or placeholder
- Name, model, specs (year, cabins, berths, length)
- Short description
- "View Details" button

---

### `[yolo_yacht_details]`
Displays individual yacht with complete information and image carousel.

**Usage:**
```
[yolo_yacht_details]
```

**Where:** Dedicated yacht details page (select in settings)

**Features:**
- Image carousel with navigation arrows
- Auto-advance every 5 seconds
- Dots navigation
- Complete specifications grid
- Full description
- Charter types (Bareboat, Crewed, etc.)
- Equipment list with FontAwesome icons
- Available extras with pricing (from all products)
- Date picker with July week defaults
- Yacht details page width: 1500px, centered
- Back button to fleet

---

## How It Works

### Customer Journey

1. **Homepage** → See search widget
2. **Select boat type & dates** → Click "SEARCH"
3. **Search Results** → YOLO boats appear first (red badges)
4. **Click "View Details"** on any yacht
5. **Yacht Details** → Image carousel, specs, equipment with icons, extras
6. **Back to Fleet** or browser back

### Database Sync

The plugin stores ALL yacht data in WordPress database:
- Fetches equipment catalog (50 items with IDs and names)
- Fetches from YOLO (Company 7850)
- Fetches from partners (4366, 3604, 6711)
- Stores: images, specs, equipment, extras, products
- Makes search and display super fast!

**When to sync:**
- First time setup
- When yachts are added/updated in Booking Manager
- Recommended: Once per week

---

## Admin Dashboard

### Three Sync Buttons

1. **Sync Equipment Catalog** (Green)
   - Syncs ~50 equipment items with IDs and names
   - Creates lookup table for equipment display
   - Run this FIRST before syncing yachts

2. **Sync Yachts** (Red)
   - Syncs all yacht data (specs, images, equipment, extras)
   - Fetches from YOLO + partner companies
   - Completes in seconds

3. **Sync Weekly Offers** (Blue)
   - Syncs pricing and availability
   - Uses /offers endpoint with flexibility=6
   - Gets all Saturday departures for the year

### Sync Statistics
Shows real-time statistics:
- **Total Yachts**: All yachts in database
- **YOLO Yachts**: Your boats (7850)
- **Partner Yachts**: Partner boats
- **Last Sync**: Time since last sync

---

## Settings

### API Settings
- **API Key**: Your Booking Manager API key (prefilled)

### Company Settings
- **My Company ID**: 7850 (YOLO) - boats appear first
- **Friend Companies**: 4366, 3604, 6711 - partner companies
- **Search Results Page**: Select page with `[yolo_search_results]`
- **Yacht Details Page**: Select page with `[yolo_yacht_details]`

### General Settings
- **Cache Duration**: 1-168 hours (default: 24)
- **Currency**: EUR, USD, GBP

### Styling Settings
- **Primary Color**: #1e3a8a (blue)
- **Button Background**: #dc2626 (red)
- **Button Text**: #ffffff (white)

---

## Database Structure

### Tables Created

**`wp_yolo_yachts`**
- Main yacht info (name, model, year, specs, description)
- Stores raw API response in `raw_data` column

**`wp_yolo_yacht_images`**
- Image URLs with sort order
- Thumbnail URLs for optimization

**`wp_yolo_yacht_products`**
- Charter types (Bareboat, Crewed, etc.)
- Base prices and currency

**`wp_yolo_yacht_equipment`**
- Equipment IDs per yacht
- Equipment names looked up from catalog on display
- Category information

**`wp_yolo_yacht_extras`**
- Available extras with pricing
- Collected from ALL products (not just products[0])
- Composite primary key (id, yacht_id) to allow duplicate extras across yachts
- Obligatory flag (0/1)

**`wp_yolo_equipment_catalog`**
- Master list of all equipment items
- Maps equipment IDs to names
- Synced separately via green button

---

## API Documentation

### Booking Manager API v2

**Base URL**: `https://api.booking-manager.com/v2/`

**Authentication**: API key in `Authorization` header

**Full API Documentation**: https://app.swaggerhub.com/apis/mmksystems/bm-api/2.2.0

### Endpoints Used

#### 1. Get Equipment Catalog
```
GET /equipment
```
Returns list of all equipment items with IDs and names.

**Response**: Array of equipment objects
```json
[
  {
    "id": 1,
    "name": "GPS"
  },
  {
    "id": 2,
    "name": "Autopilot"
  }
]
```

#### 2. Get Yachts by Company
```
GET /yachts?companyId={companyId}
```
Returns all yachts for a specific company.

**Parameters**:
- `companyId` (required): Company ID (e.g., 7850)

**Response**: Array of yacht objects with:
- Basic info (id, name, model, year, type)
- Specifications (length, beam, draft, cabins, berths, etc.)
- Images array
- Equipment array (IDs only)
- Products array (charter types with extras)
- Descriptions

**API Reference**: https://app.swaggerhub.com/apis/mmksystems/bm-api/2.2.0#/Booking/getYachts

#### 3. Get Weekly Offers
```
GET /offers?companyId={companyId}&flexibility=6&year={year}
```
Returns all available Saturday departures for the year.

**Parameters**:
- `companyId` (required): Company ID
- `flexibility`: 6 (gets all Saturday departures)
- `year`: Year to fetch (e.g., 2026)

**Response**: Array of offer objects with pricing and availability

---

## Troubleshooting

### No yachts displayed
✅ Click "Sync Equipment Catalog" (green) first  
✅ Click "Sync Yachts" (red) second  
✅ Check API key is correct  
✅ Verify company IDs: 7850, 4366, 3604, 6711

### Search not working
✅ Select "Search Results Page" in settings  
✅ Ensure page has `[yolo_search_results]` shortcode

### View Details button not working
✅ Select "Yacht Details Page" in settings  
✅ Ensure page has `[yolo_yacht_details]` shortcode

### Images not loading
✅ Run sync again  
✅ Check image URLs in database  
✅ Verify Booking Manager API is accessible

### Sync fails or hangs
✅ Check API key  
✅ Verify internet connection  
✅ Check WordPress debug log at `wp-content/debug.log`  
✅ Ensure database tables exist  
✅ Run equipment catalog sync first

### Equipment not displaying
✅ Sync equipment catalog (green button) first  
✅ Then sync yachts (red button)  
✅ Equipment names are looked up from catalog on display

### Extras missing or incomplete
✅ Plugin now collects extras from ALL products  
✅ Re-sync yachts to update extras  
✅ Check database table `wp_yolo_yacht_extras`

---

## Technical Details

### Requirements
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+
- Active Booking Manager API key

### API Integration
- **Base URL**: `https://api.booking-manager.com/v2/`
- **Endpoints**: `/equipment`, `/yachts`, `/offers`
- **Auth**: API key in Authorization header
- **Documentation**: https://app.swaggerhub.com/apis/mmksystems/bm-api/2.2.0

### JavaScript Libraries
- **Litepicker**: Date range picker (MIT)
- **jQuery**: AJAX and DOM manipulation
- **FontAwesome 6.4.0**: Equipment icons

### Database Version
Current: **1.2**

Migrations handled automatically on plugin load.

---

## What's Prefilled

✅ **Booking Manager API Key**: Your full API key  
✅ **YOLO Company ID**: 7850  
✅ **Partner Companies**: 4366, 3604, 6711  
✅ **Cache Duration**: 24 hours  
✅ **Currency**: EUR  
✅ **Primary Color**: #1e3a8a (blue)  
✅ **Button Color**: #dc2626 (red)

---

## Version History

### v1.9.4 (Current - Nov 29, 2024) ✅
**CRITICAL FIXES - Yacht Sync Now Working!**

**Bugs Fixed:**
1. **Extras Table Primary Key** (Main Issue)
   - Changed PRIMARY KEY from `(id)` to `(id, yacht_id)` composite key
   - Multiple yachts share same extras (e.g., "Skipper")
   - Old schema caused duplicate key errors → sync hung silently
   
2. **Equipment Name Column**
   - Changed from `NOT NULL` to `DEFAULT NULL`
   - Code inserts NULL, names looked up from catalog on display
   
3. **Boolean Conversion**
   - Fixed `obligatory` field conversion from boolean to integer (0/1)

**Testing:**
- Tested with real MySQL database
- 3 yachts synced in 0.07 seconds
- 45 images, 51 equipment items, 34 extras stored
- No errors, no hanging!

**Database:**
- Version bumped to 1.2
- Automatic migration on plugin load
- Fixes existing installations

### v1.9.3 (Nov 29, 2024)
- Added comprehensive logging for debugging
- Equipment name column fix
- Database migration system

### v1.9.2 (Nov 29, 2024)
- Changed equipment sync approach
- Store NULL for equipment names during sync
- Lookup names from catalog on display

### v1.8.7 (Earlier)
- Attempted equipment caching (didn't fix sync)

### v1.8.2 (Earlier)
- **BREAKING**: Added equipment catalog sync
- Modified store_yacht() to call get_equipment_name()
- Created N+1 query problem (caused hanging)

### v1.8.0 (Last Known Working)
- Date picker fixes
- Extras layout improvements
- Equipment catalog sync button added

### v1.7.9
- Search flow fixes
- Boat type filtering
- Price formatting

### v1.5.5
- Price carousel redesign
- Peak season display

### v1.5.4
- Performance improvements
- Reduced sync time to under 60 seconds

### v1.5.3
- Fixed sync hanging (API parameter bug)

### v1.5.2
- Quote request system
- Google Maps integration
- Price carousel

### v1.0.2
- Initial database storage
- Sync functionality
- Fleet and details shortcodes

---

## Support

For issues or questions:
- Check `wp-content/debug.log` for errors
- Review KNOWN-ISSUES.md for common problems
- Check GitHub repository: https://github.com/georgemargiolos/LocalWP

---

## License

GPL v2 or later

---

## Credits

Developed for YOLO Charters  
Booking Manager API Integration  
FontAwesome Icons  
Litepicker Date Picker
