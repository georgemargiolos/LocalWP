# YOLO Yacht Search & Booking Plugin - Project Summary

**Date:** December 11, 2025  
**Repository:** https://github.com/georgemargiolos/LocalWP  
**Current Version:** 55.10 (v50.0 in README)  
**Author:** George Margiolos

---

## 🎯 PROJECT OVERVIEW

The YOLO Yacht Search & Booking Plugin is a comprehensive WordPress plugin designed for yacht charter companies. It integrates with the Booking Manager API (by MMK Systems) to provide yacht search, booking, and management capabilities.

### Core Purpose
- Search and display available yachts from Booking Manager API
- Enable online bookings with Stripe payment integration
- Manage yacht operations (check-in/check-out, equipment tracking)
- Track conversions with GA4 and Facebook Pixel
- Provide guest dashboard for document management

---

## 🏗️ ARCHITECTURE

### Technology Stack
- **Platform:** WordPress 5.8+
- **PHP:** 7.4+
- **Database:** MySQL 8.0+
- **Payment:** Stripe API
- **Analytics:** Google Analytics 4 (GA4), Facebook Pixel, Facebook Conversions API
- **External API:** Booking Manager API v2

### Key Dependencies
- Stripe PHP Library (v13.16.0)
- Toastify (notifications)
- Swiper.js (carousels)
- Flatpickr/Litepicker (date picker)
- FPDF (PDF generation)
- FontAwesome (icons)

---

## 📦 MAIN FEATURES

### 1. Yacht Search & Display
- Search widget with date picker
- Fleet display (YOLO boats prioritized, partner boats below)
- Yacht details page with pricing carousel
- Image galleries with Swiper.js
- Equipment and extras display
- Technical specifications
- Google Maps integration

### 2. Booking System
- Quote request form
- Stripe payment integration (50% deposit)
- Booking confirmation emails
- Guest user system
- Guest dashboard for document management
- Balance payment functionality

### 3. Base Manager System
- Check-in/Check-out forms
- Equipment checklists
- Damage reporting
- Signature capture
- PDF generation (check-in/check-out reports)
- Email to guests
- Warehouse inventory management

### 4. Analytics & Tracking (v41.19-v41.28)
- **7 Custom Events Tracked:**
  1. `search` - User searches for yachts
  2. `view_item` - User views yacht details
  3. `add_to_cart` - User selects a week/price
  4. `begin_checkout` - User clicks "Book Now"
  5. `add_payment_info` - User submits booking form
  6. `generate_lead` - User requests a quote
  7. `purchase` - Booking completed

- **Google Tag Manager Integration (v41.26)**
  - All events use `dataLayer.push()`
  - Compatible with GTM triggers and tags
  - Flexible routing to multiple platforms

- **Facebook Conversions API (v41.27)**
  - Server-side tracking for ViewContent, Lead, Purchase
  - Client-side tracking for Search, AddToCart, InitiateCheckout, AddPaymentInfo
  - Event deduplication with unique event_id
  - User data hashing (SHA-256) for privacy
  - High event match quality (8-10/10)

- **SEO Features**
  - Open Graph tags for social sharing
  - Twitter Cards
  - Schema.org structured data

### 5. Custom Gutenberg Blocks (v50.0)
- **Horizontal Yacht Cards Block**
  - Displays YOLO yachts in horizontal cards
  - Swiper.js image carousel
  - Responsive design
  - Smart linking to yacht details
  
- **Blog Posts Grid Block**
  - Adjustable post count (1-12)
  - Featured images with hover effects
  - Category badges
  - Responsive 3-column grid

### 6. Customization System (v41.21)
- **16 Color Settings** - All UI elements customizable
- **71 Text Settings** - All visible text customizable
- Icon management system
- Document templates

### 7. Admin Features
- Booking Manager API sync (hourly auto-sync)
- Bookings management
- Quote requests management
- Contact messages system
- Guest licenses management
- Warehouse notifications
- Admin documents system
- Color and text customization

---

## 🗂️ PROJECT STRUCTURE

```
LocalWP/
├── yolo-yacht-search/                    # Main plugin directory
│   ├── yolo-yacht-search.php            # Main plugin file (v55.10)
│   ├── admin/                           # Admin interface
│   │   ├── class-yolo-ys-admin.php
│   │   ├── class-yolo-ys-admin-bookings.php
│   │   ├── class-yolo-ys-admin-bookings-manager.php
│   │   ├── class-yolo-ys-admin-colors.php
│   │   ├── class-yolo-ys-admin-documents.php
│   │   ├── class-yolo-ys-admin-guest-licenses.php
│   │   ├── class-yolo-ys-icons-admin.php
│   │   └── partials/                    # Admin templates
│   ├── includes/                        # Core classes
│   │   ├── class-yolo-ys-yacht-search.php
│   │   ├── class-yolo-ys-booking-manager-api.php
│   │   ├── class-yolo-ys-database.php
│   │   ├── class-yolo-ys-database-prices.php
│   │   ├── class-yolo-ys-sync.php
│   │   ├── class-yolo-ys-stripe.php
│   │   ├── class-yolo-ys-stripe-handlers.php
│   │   ├── class-yolo-ys-analytics.php       # GA4 + Facebook CAPI
│   │   ├── class-yolo-ys-meta-tags.php       # SEO
│   │   ├── class-yolo-ys-base-manager.php
│   │   ├── class-yolo-ys-guest-users.php
│   │   ├── class-yolo-ys-quote-handler.php
│   │   ├── class-yolo-ys-quote-requests.php
│   │   ├── class-yolo-ys-contact-messages.php
│   │   ├── class-yolo-ys-email.php
│   │   ├── class-yolo-ys-pdf-generator.php
│   │   ├── class-yolo-ys-warehouse-notifications.php
│   │   ├── class-yolo-ys-auto-sync.php
│   │   └── text-helpers.php
│   ├── public/                          # Frontend
│   │   ├── templates/
│   │   │   ├── yacht-details-v3.php
│   │   │   ├── booking-confirmation.php
│   │   │   └── guest-dashboard.php
│   │   ├── blocks/                      # Gutenberg blocks
│   │   ├── css/
│   │   └── js/
│   │       └── yolo-analytics.js        # Client-side tracking
│   ├── stripe-php/                      # Stripe library
│   └── migrations/                      # Database migrations
├── yolo-horizontal-yacht-cards/         # Standalone block plugin
└── yolo-horizontal-blog-posts/          # Standalone block plugin
```

---

## 💾 DATABASE SCHEMA

### Main Tables
- `wp_yolo_yachts` - Yacht information from Booking Manager API
- `wp_yolo_yacht_images` - Yacht images
- `wp_yolo_yacht_equipment` - Equipment items
- `wp_yolo_yacht_extras` - Extras/add-ons
- `wp_yolo_yacht_prices` - Weekly pricing data (52 weeks cached)
- `wp_yolo_bookings` - Booking records
- `wp_yolo_quote_requests` - Quote requests
- `wp_yolo_contact_messages` - Contact form submissions
- `wp_yolo_guest_users` - Guest user accounts
- `wp_yolo_guest_documents` - Guest uploaded documents

### Base Manager Tables
- `wp_yolo_bm_yachts` - Base Manager yacht records
- `wp_yolo_bm_equipment_categories` - Equipment categories
- `wp_yolo_bm_checkins` - Check-in records
- `wp_yolo_bm_checkouts` - Check-out records
- `wp_yolo_bm_warehouse` - Warehouse inventory

---

## 🔑 KEY INTEGRATIONS

### 1. Booking Manager API
**Base URL:** `https://www.booking-manager.com/api/v2`

**Key Endpoints Used:**
- `GET /yachts` - Fetch yacht data
- `GET /offers` - Search available yachts
- `GET /prices` - Get pricing data
- `GET /equipment` - Equipment catalog
- `POST /reservation` - Create booking (planned)

**Authentication:** API Key in Authorization header

### 2. Stripe Payment
- Checkout Sessions API
- Webhook handling
- 50% deposit at booking
- Balance payment functionality
- Test and live mode support

### 3. Google Analytics 4
- Measurement Protocol API
- Custom event tracking via GTM
- E-commerce tracking
- Conversion tracking

### 4. Facebook Pixel & Conversions API
- **Pixel ID:** 1896226957957033 (pre-configured)
- Server-side events via Graph API v22.0
- Client-side pixel tracking
- Event deduplication
- User data hashing for privacy

---

## 🔄 RECENT VERSION HISTORY

### v50.0 (December 9, 2025) - Custom Gutenberg Blocks
- Created Horizontal Yacht Cards Block
- Created Blog Posts Grid Block
- Removed Contact Form 7 dependency
- Added standalone CSS for contact forms
- Theme font inheritance for all blocks

### v41.28 (December 9, 2025) - Purchase Event Fix
- Fixed Purchase event tracking on confirmation page
- Added server-side CAPI tracking for purchases
- Complete event funnel now working

### v41.27 (December 9, 2025) - Facebook Conversions API
- Implemented true server-side Facebook CAPI
- 3 events server-side (ViewContent, Lead, Purchase)
- Event deduplication with unique event_id
- User data hashing (SHA-256)
- High event match quality (8-10/10)

### v41.26 (December 9, 2025) - Google Tag Manager
- Switched to dataLayer.push() for all events
- Removed direct gtag()/fbq() calls
- Created comprehensive GTM setup guide

### v41.25 (December 9, 2025) - Analytics Cleanup
- Removed duplicate GA4/FB Pixel base tracking
- Kept all 7 custom yacht booking events
- Integration with external analytics plugins

### v41.21 (December 8, 2025) - Text & Color Settings Audit
- Fixed color loading bug
- Added 5 new color settings (16 total)
- Added 35 new text settings (71 total)
- 87 total customization settings (+85% increase)

### v41.19 - Analytics & SEO System
- Google Analytics 4 integration
- Facebook Pixel tracking
- Open Graph tags
- Twitter Cards
- Schema.org structured data

---

## 🚨 KNOWN ISSUES & FIXES

### ✅ Resolved Issues

1. **Yacht Sync Hanging (v1.9.4)**
   - Fixed equipment_name column NULL constraint
   - Fixed extras table primary key (composite key)
   - Fixed boolean to integer conversion

2. **Fatal Error on Activation (v1.5.0)**
   - Removed redundant require_once statements

3. **Color Loading Bug (v41.21)**
   - Fixed colors loading from individual options instead of array

4. **Purchase Event Not Tracking (v41.28)**
   - Added Purchase event to confirmation page
   - Added server-side CAPI tracking

### Current Status
No critical issues in latest version (v55.10 / v50.0)

---

## 📋 FEATURE STATUS

### ✅ Completed (80%)
- Database structure
- Price fetching and display
- Fleet display with prioritization
- Yacht details page
- Quote requests
- Stripe payment integration
- Booking system
- Guest user system
- Base Manager system
- Analytics tracking (GA4 + Facebook)
- Custom Gutenberg blocks
- Customization system (colors/texts)

### ⚠️ Needs Work (20%)
- **Search Functionality** - Search widget exists but doesn't function yet
- Multi-currency support
- Multi-language support (WPML)
- Advanced search filters
- Yacht comparison feature
- Customer reviews system

---

## 🔧 CONFIGURATION

### Required Settings
- **Booking Manager API:** Base URL, Username, Password, API Key
- **Stripe:** Publishable Key, Secret Key (test or live)
- **Google Maps:** API Key

### Optional Settings
- **Analytics:** GA4 Measurement ID, Facebook Pixel ID, Facebook Access Token
- **Colors:** 16 customizable colors
- **Texts:** 71 customizable text strings
- **Email:** SMTP settings for notifications

---

## 📚 DOCUMENTATION FILES

### Essential Documents
- `README.md` - Main project overview
- `FEATURE-STATUS.md` - Feature implementation status
- `BookingManagerAPIManual.md` - API integration guide
- `PROJECT_LIBRARY.md` - Code library and patterns

### Handoff Documents (Session Notes)
- `HANDOFF-DECEMBER-9-2025-v41.27.md` - Latest session
- `HANDOFF-v50.0.md` - Custom blocks implementation
- Multiple version-specific handoff files

### Technical Documentation
- `ANALYTICS-SEO-SETUP-GUIDE.md` - GA4 and Facebook setup
- `GTM_SETUP_GUIDE.md` - Google Tag Manager configuration
- `BLOCKS-TRACKING.md` - Custom blocks documentation
- `GUEST-SYSTEM-README.md` - Guest user system
- `BOOKING-FORM-IMPLEMENTATION.md` - Booking form details

### Changelogs
- `CHANGELOG.md` - Complete version history
- Multiple version-specific changelog files (v1.x, v2.x, v17.x, v41.x)

### Troubleshooting
- `KNOWN-ISSUES.md` - Known issues and fixes
- `COMMON-ERRORS.md` - Common error patterns
- `TROUBLESHOOTING.md` - Debug guide
- `RECURRING_ERRORS_DOCUMENTATION.md` - Recurring issues

---

## 🎯 DEVELOPMENT WORKFLOW

### User Roles
- **Administrator** - Full access to all features
- **Base Manager** - Limited access to base operations only
- **Guest** - Access to guest dashboard only

### Testing Checklist
1. Plugin activation/deactivation
2. Yacht sync from Booking Manager API
3. Search and display yachts
4. View yacht details
5. Submit quote request
6. Complete booking with Stripe test card (4242 4242 4242 4242)
7. Check-in/Check-out operations
8. PDF generation
9. Guest dashboard access
10. Analytics event tracking (GTM Preview, Facebook Test Events)

### Deployment Process
1. Update version number in main plugin file
2. Test all functionality
3. Create changelog
4. Create handoff document
5. Commit to GitHub
6. Create ZIP package
7. Upload to WordPress site

---

## 🔗 IMPORTANT LINKS

- **GitHub Repository:** https://github.com/georgemargiolos/LocalWP
- **Booking Manager API Docs:** https://support.booking-manager.com/
- **Swagger API Docs:** https://app.swaggerhub.com/apis-docs/mmksystems/bm-api/
- **Live Site:** yolo-charters.com
- **Test Server:** mytestserver.gr

---

## 💡 KEY INSIGHTS

### Project Strengths
1. **Comprehensive Integration** - Deep Booking Manager API integration
2. **Professional Analytics** - Server-side tracking with Facebook CAPI
3. **Flexible Customization** - 87 customization settings
4. **Modern Architecture** - GTM integration, custom blocks
5. **Complete Booking Flow** - From search to payment to check-in/out

### Technical Highlights
1. **Event Deduplication** - Prevents double-counting in Facebook
2. **High Match Quality** - 8-10/10 score with comprehensive user data
3. **Non-blocking Tracking** - Async requests don't slow down site
4. **Gutenberg Blocks** - Modern WordPress block editor support
5. **Guest System** - Complete document management for guests

### Areas for Improvement
1. **Search Functionality** - Core feature not yet implemented
2. **Multi-language** - Currently English only
3. **Multi-currency** - Currently EUR only
4. **Advanced Filters** - Limited search filtering options

---

## 🚀 NEXT STEPS (Recommended)

### Priority 1: Implement Search Functionality
The plugin is called "Yacht Search & Booking" but search doesn't work yet!
- Implement search form submission
- Query database for available yachts
- Filter by dates, boat type, cabins, price
- Display results with YOLO boats first

### Priority 2: Testing & Optimization
- Test all analytics events in production
- Verify Facebook Event Match Quality
- Optimize database queries
- Performance testing

### Priority 3: Feature Enhancements
- Multi-currency support
- Multi-language support (WPML)
- Advanced search filters
- Yacht comparison feature
- Customer reviews system

---

**Last Updated:** December 11, 2025  
**Document Created By:** AI Assistant (Manus)  
**Purpose:** Comprehensive project familiarization and reference
