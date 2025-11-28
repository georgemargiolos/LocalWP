# YOLO Yacht Search & Booking Plugin

WordPress plugin for YOLO Charters with Booking Manager API integration, featuring search widget and results blocks with company prioritization.

## Features

✅ **Search Widget Block** - Yacht search form styled like yolo-charters.com  
✅ **Search Results Block** - Display results with YOLO boats prioritized  
✅ **Litepicker Integration** - Beautiful date range picker with mobile support  
✅ **Booking Manager API** - Real-time yacht availability from Booking Manager  
✅ **Company Prioritization** - YOLO boats (7850) shown first, then partner companies  
✅ **Database Caching** - Configurable cache duration for API responses  
✅ **Admin Settings** - Easy configuration via WordPress admin panel  
✅ **Responsive Design** - Mobile-friendly interface  

## Installation

1. Upload the `yolo-yacht-search` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **YOLO Yacht Search** in the admin menu to configure settings

## Configuration

### Admin Settings

Navigate to **YOLO Yacht Search** in WordPress admin:

#### API & Company Settings
- **API Key**: Your Booking Manager API key (prefilled)
- **My Company ID**: YOLO company ID - 7850 (prefilled)
- **Friend Companies IDs**: Partner company IDs - 4366, 3604, 6711 (prefilled)
- **Search Results Page**: Select the page where results will be displayed

#### General Settings
- **Cache Duration**: How long to cache API results (default: 24 hours)
- **Currency**: Display currency (EUR, USD, GBP)

#### Styling Settings
- **Primary Color**: Main theme color
- **Button Colors**: Search button styling
- **Custom CSS**: Additional styling options

## Usage

### 1. Create Results Page

1. Create a new page (e.g., "Search Results")
2. Add the **YOLO Search Results** block to the page
3. Publish the page
4. Go to plugin settings and select this page as the "Search Results Page"

### 2. Add Search Widget

1. Edit any page where you want the search form
2. Add the **YOLO Search Widget** block
3. Publish the page

### 3. How It Works

- Users fill out the search form (boat type, dates)
- Form submits to the results page with search parameters
- Results are fetched from Booking Manager API
- **YOLO boats** (Company ID: 7850) are displayed first
- **Partner boats** (Companies: 4366, 3604, 6711) are displayed below
- Results are cached for better performance

## Blocks

### YOLO Search Widget
- Boat type dropdown (Sailing yacht, Catamaran, Motor yacht)
- Date range picker (Litepicker with Saturday-to-Saturday booking)
- Search button
- Optional: Include unconfirmed availability checkbox

### YOLO Search Results
- Results header with count
- YOLO boats section (highlighted with badge)
- Partner boats section
- Yacht cards with:
  - Yacht name and type
  - Location (base/marina)
  - Price and currency
  - View details button

## API Integration

The plugin integrates with Booking Manager REST API v2:

- **Base URL**: `https://www.booking-manager.com/api/v2`
- **Endpoints Used**:
  - `/offers` - Search for available yachts
  - `/yacht/{id}` - Get yacht details
  - `/company/{id}` - Get company details

### Search Flow

1. User submits search form
2. AJAX request to WordPress backend
3. Backend makes API call to Booking Manager for YOLO boats (7850)
4. Backend makes API calls for each partner company (4366, 3604, 6711)
5. Results are combined and cached
6. Results are returned to frontend
7. Frontend renders results with YOLO boats first

## Customization

### Date Picker

The Litepicker is configured to:
- Allow only Saturday-to-Saturday bookings (configurable)
- Show 2 months at a time
- Mobile-responsive (breakpoint: 480px)
- Calculate nights automatically
- Prevent past date selection

### Styling

All styles can be customized via:
- Admin settings (colors)
- Custom CSS in theme
- Modifying plugin CSS files

## Technical Details

### File Structure

```
yolo-yacht-search/
├── admin/
│   ├── css/
│   ├── js/
│   ├── partials/
│   └── class-yolo-ys-admin.php
├── assets/
│   ├── css/
│   │   └── litepicker.css
│   └── js/
│       ├── litepicker.js
│       └── mobilefriendly.js
├── includes/
│   ├── class-yolo-ys-activator.php
│   ├── class-yolo-ys-deactivator.php
│   ├── class-yolo-ys-loader.php
│   ├── class-yolo-ys-booking-manager-api.php
│   └── class-yolo-ys-yacht-search.php
├── public/
│   ├── blocks/
│   │   ├── yacht-search/
│   │   └── yacht-results/
│   ├── css/
│   ├── js/
│   ├── templates/
│   └── class-yolo-ys-public.php
└── yolo-yacht-search.php
```

### Requirements

- WordPress 5.8+
- PHP 7.4+
- Active Booking Manager API key

## Support

For issues or questions, contact: george@yolocharters.com

## License

GPL v2 or later

## Credits

- **Author**: George Margiolos
- **Litepicker**: MIT License (https://github.com/wakirin/Litepicker)
- **Booking Manager API**: MMK Systems

## Changelog

### Version 1.0.0
- Initial release
- Search widget block with Litepicker
- Search results block with company prioritization
- Booking Manager API integration
- Admin settings page
- Database caching
