# YOLO Yacht Search & Booking Plugin

WordPress plugin for YOLO Charters with Booking Manager API integration, featuring search widget, fleet display, yacht details, and database storage.

## Features

✅ **Search Widget** - Yacht search form styled like yolo-charters.com  
✅ **Search Results** - Display results with YOLO boats prioritized  
✅ **Our Fleet** - Beautiful grid display of all yachts  
✅ **Yacht Details** - Individual yacht pages with image carousel  
✅ **Database Storage** - All yacht data stored in WordPress database  
✅ **One-Click Sync** - Fetch and update yacht data from Booking Manager API  
✅ **Litepicker Integration** - Beautiful date range picker with mobile support  
✅ **Company Prioritization** - YOLO boats (7850) shown first, then partner companies  
✅ **Admin Dashboard** - Sync statistics and easy configuration  
✅ **Responsive Design** - Mobile-friendly interface  
✅ **Easy Shortcodes** - No block editor needed!

## Installation

1. Upload `yolo-yacht-search.zip` to WordPress → Plugins → Add New
2. Activate the plugin
3. Go to **YOLO Yacht Search** in the admin menu
4. Click **"Sync All Yachts Now"** to fetch yacht data
5. Configure page settings (already prefilled!)

## Quick Setup (4 Steps!)

### Step 1: Sync Yacht Data
1. Go to **YOLO Yacht Search** in WordPress admin
2. Click **"Sync All Yachts Now"** button
3. Wait 1-2 minutes for sync to complete
4. See statistics: Total yachts, YOLO yachts, Partner yachts

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
- Equipment list
- Available extras with pricing
- Back button to fleet

---

## How It Works

### Customer Journey

1. **Homepage** → See search widget
2. **Select boat type & dates** → Click "SEARCH"
3. **Search Results** → YOLO boats appear first (red badges)
4. **Click "View Details"** on any yacht
5. **Yacht Details** → Image carousel, specs, equipment, extras
6. **Back to Fleet** or browser back

### Database Sync

The plugin stores ALL yacht data in WordPress database:
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

### Sync Section
Shows real-time statistics:
- **Total Yachts**: All yachts in database
- **YOLO Yachts**: Your boats (7850)
- **Partner Yachts**: Partner boats
- **Last Sync**: Time since last sync

### Sync Button
Click **"Sync All Yachts Now"** to:
1. Fetch all yachts from API
2. Store in WordPress database
3. Update images, specs, equipment
4. Show success message with count

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

## Yacht Card Design

### In Fleet Display
- Yacht image (220px height)
- Name in large blue text
- Model in gray
- Specs: Year, Cabins, Berths, Length
- Short description (20 words)
- Charter type badge
- **"View Details" button** (blue for partners, red for YOLO)

### YOLO Boats Special Styling
- Red "YOLO" badge in corner
- Red border (2px)
- Red "View Details" button
- Displayed in first section

---

## Database Structure

### Tables Created

**`wp_yolo_yachts`**
- Main yacht info (name, model, year, specs, description)

**`wp_yolo_yacht_images`**
- Image URLs with sort order

**`wp_yolo_yacht_products`**
- Charter types (Bareboat, Crewed, etc.)

**`wp_yolo_yacht_equipment`**
- Equipment list per yacht

**`wp_yolo_yacht_extras`**
- Available extras with pricing

---

## Troubleshooting

### No yachts displayed
✅ Click "Sync All Yachts Now" in admin  
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

### Sync fails
✅ Check API key  
✅ Verify internet connection  
✅ Check PHP error logs

---

## Technical Details

### Requirements
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+
- Active Booking Manager API key

### API Integration
- **Base URL**: `https://www.booking-manager.com/api/v2`
- **Endpoint**: `/yachts?companyId={id}`
- **Auth**: API key in Authorization header

### JavaScript Libraries
- **Litepicker**: Date range picker (MIT)
- **jQuery**: AJAX and DOM manipulation

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

### v1.0.2 (Current)
- ✅ Added database storage for yacht data
- ✅ Added yacht sync with admin button
- ✅ Added `[yolo_our_fleet]` shortcode
- ✅ Added `[yolo_yacht_details]` shortcode
- ✅ Added image carousel
- ✅ Added "View Details" buttons
- ✅ Added sync statistics dashboard

### v1.0.1
- Converted blocks to shortcodes
- Added Litepicker integration
- Added yolo-charters.com styling

### v1.0.0
- Initial release

---

## Support

For issues or questions:
- GitHub: https://github.com/georgemargiolos/LocalWP

---

## License

GPL v2 or later

---

## Credits

- **Author**: George Margiolos
- **API**: Booking Manager (MMK Systems)
- **Litepicker**: MIT License
- **Inspired by**: yolo-charters.com
