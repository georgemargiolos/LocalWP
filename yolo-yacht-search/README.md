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

### v1.5.5 (Latest)
- **UI/UX Enhancement:** Redesigned the price carousel on yacht detail pages.
- Shows only peak season (May-Sept) in a 4-week grid.
- Smart navigation to browse by groups of 4 weeks.
- Matches professional design of sites like Boataround.com.

### v1.5.4
- **Performance Fix:** Reduced price sync period from 12 months to 3 months.
- **Result:** Sync time improved from ~5 minutes to **under 60 seconds**.

### v1.5.3
- **CRITICAL BUG FIX:** Fixed the sync hanging issue.
- **Cause:** Incorrect API parameter (`company` vs `companyId`) was causing the API to fetch prices for ALL boats in the Booking Manager system.
- **Result:** Sync now correctly fetches data for only the 4 specified companies.

### v1.5.2
- **Feature:** Added a complete quote request system with email notifications.
- **Feature:** Added Google Maps integration to show yacht home base.
- **Feature:** Implemented a price carousel on detail pages.
- **Bug:** Introduced the critical sync performance bug (fixed in v1.5.3).

### v1.1.0 - v1.5.1
- **Fix:** Resolved a fatal error on activation (`Non-static method create_tables()`).
- Added various UI improvements and minor bug fixes.

### v1.0.2
- Added database storage for all yacht data.
- Implemented the initial "Sync All Yachts Now" functionality.
- Created `[yolo_our_fleet]` and `[yolo_yacht_details]` shortcodes.

### v1.0.1
- Converted initial block-based design to more flexible shortcodes.
- Integrated Litepicker.js for date selection.

### v1.0.0
- Initial plugin structure and setup.

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


---

# YOLO Yacht Search & Booking WordPress Plugin (v1.5.5)

**Version:** 1.5.5  
**Author:** Manus AI for George Margiolos  
**Last Updated:** November 28, 2025

## 🚀 Overview

YOLO Yacht Search is a powerful WordPress plugin designed for yacht charter businesses. It allows you to display your fleet, show real-time pricing from Booking Manager API, and capture leads with a professional quote request system.

### Key Features:

- **Database Caching:** Fetches all yacht data and prices from Booking Manager API and stores it locally for fast performance.
- **YOLO First Display:** Prioritizes your company's boats (YOLO) in all displays with special styling.
- **Our Fleet Page:** Showcase your entire fleet (YOLO + partners) with beautiful, responsive yacht cards.
- **Yacht Details Page:** Individual pages for each yacht with image carousels, weekly price carousels, full specifications, and Google Maps integration.
- **Quote Request System:** Capture leads with a professional quote request form that sends email notifications and stores requests in the database.
- **Booking System UI:** Complete user interface for selecting dates, viewing prices with discounts, and proceeding to book.
- **Shortcode-Based:** Easy to use shortcodes for displaying search widgets, fleet, and yacht details.
- **Admin Panel:** Simple admin page to sync data and configure settings.

## 📋 Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Booking Manager API Key
- Google Maps API Key

## ⚙️ Installation

1. **Download:** Get the latest `yolo-yacht-search-vX.X.X.zip` file.
2. **Upload:** In WordPress admin, go to `Plugins > Add New > Upload Plugin`.
3. **Activate:** Click "Activate Plugin".

## 🚀 Quick Start Guide

### 1. Sync Data

- Go to **YOLO Yacht Search** in the admin menu.
- Click the **"Sync All Yachts Now"** button. This will fetch all yacht data and prices from the Booking Manager API. This may take a few minutes.

### 2. Create Pages

Create the following pages in WordPress:

- **Our Fleet:** Add the shortcode `[yolo_our_fleet]`
- **Yacht Details:** Add the shortcode `[yolo_yacht_details]`
- **Search Results:** Add the shortcode `[yolo_search_results]`

### 3. Configure Settings

- Go to **YOLO Yacht Search** in the admin menu.
- **Select the pages** you just created in the dropdowns.
- **Add your Google Maps API Key** in `public/templates/yacht-details-v3.php` (line 295).
- All other settings (company IDs, API key) are pre-filled.

### 4. Add Search Widget

- On your homepage or any other page, add the shortcode `[yolo_search_widget]`.

## 📚 Shortcodes

- `[yolo_our_fleet]` - Displays the entire fleet of yachts.
- `[yolo_yacht_details]` - Displays the details for a single yacht (used on the Yacht Details page).
- `[yolo_search_widget]` - Displays the yacht search form.
- `[yolo_search_results]` - Displays the search results (used on the Search Results page).

## 🎨 Customization

### Company IDs

- **Your Company ID (YOLO):** 7850
- **Partner Company IDs:** 4366, 3604, 6711

These are pre-filled in `admin/class-yolo-ys-admin.php`.

### Styling

- All CSS is located in `public/css/yolo-yacht-search-public.css`.
- Yacht card styles are in `public/templates/partials/yacht-card.php`.
- Yacht details styles are in `public/templates/partials/yacht-details-v3-styles.php`.

## 🔧 Troubleshooting

### Fatal Error on Activation

**Error:** `Fatal error: Uncaught Error: Non-static method YOLO_YS_Database::create_tables() cannot be called statically`

**Cause:** This was a bug in versions v1.1.0 - v1.5.1 where the activator was calling a non-static method statically.

**Solution:** This is **FIXED in v1.5.2** and later. Please ensure you are using the latest version.

### Sync Hanging or Taking Too Long (v1.5.2 ONLY)

**Error:** The "Sync All Yachts Now" button runs for 1+ hours or appears to hang.

**Cause:** A critical bug in v1.5.2 where the API parameter was `company` instead of `companyId`, causing the API to return prices for ALL companies in the entire Booking Manager system instead of just your 4 companies.

**Solution:** This is **FIXED in v1.5.3**. Upgrade immediately if you're on v1.5.2:
1. Deactivate and delete v1.5.2
2. Upload and activate v1.5.3
3. Click "Sync All Yachts Now" - should complete in 1-2 minutes
4. (Optional) Truncate `wp_yolo_yacht_prices` table to remove incorrect data from v1.5.2

### Sync Not Working

- Check that your Booking Manager API key is correct in `admin/class-yolo-ys-admin.php`.
- Check your server's firewall is not blocking outbound requests to `https://www.booking-manager.com`.

### Google Maps Not Showing

- Ensure you have a valid Google Maps API key.
- Add the key to `public/templates/yacht-details-v3.php` (line 295).

## ⏭️ Future Development

- **Search Functionality:** Implement the backend logic for the search widget.
- **Stripe Integration:** Connect the "Book Now" button to Stripe for payments.
- **Booking Creation:** Create actual bookings in Booking Manager API after successful payment.

This plugin was custom-built by Manus AI. For support or feature requests, please contact George Margiolos.
