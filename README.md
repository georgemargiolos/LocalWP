# LocalWP - WordPress Charter Booking Plugin

WordPress plugin for yacht charter businesses integrating with Booking Manager API and Stripe payments.

## Repository Contents

### Documentation
- **BookingManagerAPIManual.md** - Complete API documentation for Booking Manager REST API
- **yolo-search-analysis.md** - Analysis of yolo-charters.com search interface
- **javascript-libraries-analysis.md** - Detailed breakdown of JavaScript libraries used

### JavaScript Libraries (Extracted from yolo-charters.com)
- **litepicker.js** (63KB) - Date range picker library (MIT License)
- **mobilefriendly.js** (12KB) - Litepicker mobile plugin
- **plugins.min.js** (558KB) - Bundled libraries (jQuery, PhotoSwipe, Select2, etc.)
- **custom.min.js** (9KB) - Custom UI/UX logic

### Reference Files
- **yolo-homepage.html** (71KB) - Full HTML source of yolo-charters.com for reference

## Project Goal

Create a WordPress plugin that provides:

1. **Search Functionality** - Query Booking Manager API for available boats
2. **Database Caching** - Store search results in WordPress database for performance
3. **Booking System** - Allow customers to book yachts through the plugin
4. **Payment Integration** - Process payments via Stripe
5. **Admin Dashboard** - Manage bookings, settings, and API credentials

## Technology Stack

- **Backend**: PHP (WordPress standards)
- **Frontend**: JavaScript (Litepicker, jQuery)
- **API**: Booking Manager REST API v2
- **Payment**: Stripe API
- **Database**: WordPress wpdb (MySQL)

## API Credentials

API Key and Base URL are documented in BookingManagerAPIManual.md

## Development Status

🚧 **In Progress** - Currently gathering requirements and analyzing reference implementations

## Repository

GitHub: https://github.com/georgemargiolos/LocalWP

## License

To be determined
