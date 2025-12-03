# Version History

Complete version history of the YOLO Yacht Search & Booking Plugin.

---

## v17.5 (December 3, 2025) - Contact Form System

**Status:** Production Ready ✅

### Major Features
- **Complete In-House Contact Form System** with real-time notifications
- Contact form shortcode `[yolo_contact_form]` matching CF7 styling
- Database storage for all contact messages
- Admin interface with list and detail views
- WordPress admin bar notification badges
- Browser push notifications
- Status tracking (new, reviewed, responded)
- Internal notes system for team collaboration

### New Capabilities
- Display contact form on any page via shortcode
- Store contact messages in database permanently
- View and manage messages in admin interface
- Filter messages by status
- Update message status
- Add internal notes to messages
- Receive real-time notifications for new messages
- Admin bar badge shows unread message count
- Browser push notifications for new messages
- Quick contact actions (email, phone)

### Database Changes
- Added `wp_yolo_contact_messages` table
- User meta for notification preferences

### Technical Improvements
- AJAX-powered contact form submission
- Real-time notification system
- Matches Contact Form 7 styling for seamless integration
- Security: Nonce verification, capability checks, input sanitization
- Efficient database queries with indexes
- Client-side and server-side validation

### Files Added
- `includes/class-yolo-ys-contact-messages.php`
- `admin/partials/contact-messages-list.php`
- `admin/partials/contact-message-detail.php`
- `public/partials/contact-form.php`
- `admin/js/contact-notifications.js`
- `CHANGELOG_v17.5.md`
- `HANDOFF_v17.5.md`

### Files Modified
- `yolo-yacht-search.php` - Added contact messages initialization
- `admin/class-yolo-ys-admin.php` - Added contact messages menu

### Documentation
- Comprehensive changelog created
- Technical handoff document with full specifications
- Database schema documentation
- AJAX API reference
- Notification system documentation

### Shortcodes
- `[yolo_contact_form]` - Contact form with CF7 styling

**See:** `CHANGELOG_v17.5.md` for detailed changes  
**See:** `HANDOFF_v17.5.md` for technical documentation

---

## v17.4 (December 3, 2025) - In-House Quote Request System

**Status:** Production Ready ✅

### Major Features
- **Complete In-House Quote Request System** with real-time notifications
- Quote requests database storage (no email dependency)
- Admin interface for quote management
- WordPress admin bar notification badges
- Browser push notifications
- Notification settings page for base managers
- Status tracking (new, reviewed, responded)
- Internal notes system

### New Capabilities
- Store quote requests in database
- View and manage quotes in admin interface
- Filter quotes by status
- Update quote status
- Add internal notes to quotes
- Receive real-time notifications for new quotes
- Configure which base managers receive notifications
- Admin bar badge shows unread quote count
- Browser push notifications for new quotes

### Database Changes
- Added `wp_yolo_quote_requests` table
- User meta for notification preferences

### Technical Improvements
- AJAX-powered quote management interface
- Real-time notification system
- Granular notification control per user
- Security: Nonce verification, capability checks, input sanitization
- Efficient database queries with indexes

### Files Added
- `includes/class-yolo-ys-quote-requests.php`
- `admin/partials/quote-requests-list.php`
- `admin/partials/quote-request-detail.php`
- `admin/partials/quote-notification-settings.php`
- `admin/css/quote-notifications.css`
- `admin/js/quote-notifications.js`
- `CHANGELOG_v17.4.md`
- `HANDOFF_v17.4.md`

### Files Modified
- `yolo-yacht-search.php` - Added quote requests initialization
- `admin/class-yolo-ys-admin.php` - Added quote menus
- `includes/class-yolo-ys-quote-handler.php` - Updated to use database

### Documentation
- Comprehensive changelog created
- Technical handoff document with full specifications
- Database schema documentation
- AJAX API reference
- Notification system documentation

**See:** `CHANGELOG_v17.4.md` for detailed changes  
**See:** `HANDOFF_v17.4.md` for technical documentation

---

## v17.0 (December 3, 2025) - Base Manager System

**Status:** Ready for Production Testing ✅

### Major Features
- **Complete Base Manager System** with dedicated dashboard
- Base manager user role and permissions
- Yacht management with equipment categories
- Check-in/check-out processes with digital signatures
- PDF generation for check-in/check-out documents
- Guest signature integration in dashboard
- Warehouse inventory management
- Bookings calendar view for base managers

### New Capabilities
- Create and manage yachts with logos and owner info
- Define equipment categories and items per yacht
- Perform digital check-ins with signature capture
- Perform digital check-outs with signature capture
- Generate professional PDF documents
- Send documents to guests via email
- Guest can sign documents digitally
- Track warehouse inventory by yacht
- View all bookings in calendar format

### Database Changes
- Version 1.6
- Added `wp_yolo_bm_yachts` table
- Added `wp_yolo_bm_equipment_categories` table
- Added `wp_yolo_bm_checkins` table
- Added `wp_yolo_bm_checkouts` table
- Added `wp_yolo_bm_warehouse` table

### Technical Improvements
- FPDF library integration for PDF generation
- Signature pad library for digital signatures
- Bootstrap 5 dashboard layout
- Comprehensive AJAX API for base manager operations
- Security: Nonce verification, capability checks, booking ownership validation
- PDF signature placement: Base Manager (bottom-left), Guest (bottom-right)

### Files Added
- `includes/class-yolo-ys-base-manager.php`
- `includes/class-yolo-ys-base-manager-database.php`
- `includes/class-yolo-ys-pdf-generator.php`
- `public/partials/base-manager-dashboard.php`
- `public/css/base-manager.css`
- `public/js/base-manager.js`
- `vendor/fpdf/fpdf.php`
- `CHANGELOG_v17.0.md`
- `HANDOFF_v17.0.md`

### Documentation
- Comprehensive changelog created
- Technical handoff document with full specifications
- Database schema documentation
- AJAX API reference
- Deployment and testing guides

### Shortcodes
- `[base_manager]` - Base manager dashboard

**See:** `CHANGELOG_v17.0.md` for detailed changes  
**See:** `HANDOFF_v17.0.md` for technical documentation

---

## v2.5.9 (December 1, 2025) - Custom Guest Login

**Status:** Production Ready ✅

### Major Features
- Custom guest login page with `[yolo_guest_login]` shortcode
- Branded frontend login (no wp-admin for guests)
- Auto-redirect to dashboard after login
- Guest credentials email template

### Bug Fixes
- Guest user creation finally working (moved from webhook to confirmation page)
- Booking confirmation race condition fixed
- Customer details fallback improved
- Contact info updated throughout

### Database Version
- 1.5 (no changes from v2.5.6)

---

## v2.5.8 (December 1, 2025) - Email Fixes

**Status:** Production Ready ✅

### Bug Fixes
- Email null booking error fixed
- Customer details fallback logic improved
- Better error handling for emails

---

## v2.5.7 (December 1, 2025) - Guest Role Fix

**Status:** Production Ready ✅

### Bug Fixes
- Guest role registration timing issue fixed
- Enhanced error logging

---

## v2.5.6 (November 30, 2025) - Guest User System

**Status:** Production Ready ✅

### Major Features
- Complete guest user system
- Guest dashboard with `[yolo_guest_dashboard]` shortcode
- License upload functionality
- Admin license management panel
- Auto guest creation after booking

### Database Changes
- Version 1.5
- Added `user_id` to bookings table
- Added `wp_yolo_license_uploads` table

---

## v2.5.5 (November 29, 2025) - UI Improvements

**Status:** Production Ready ✅

### Changes
- Search results max-width increased to 1600px

---

## v2.5.4 (November 30, 2025) - Search Results Redesign

**Status:** Production Ready ✅

### Major Features
- Beautiful 3-column grid layout
- Strikethrough original prices
- Red discount badges
- Modern card design with hover effects
- Responsive design

### Added
- `original_price` field
- `discount_percentage` field
- `year_of_build` and `refit_year` fields
- Gradient backgrounds
- Image lazy loading

---

## v2.5.3 (November 30, 2025) - Critical Bug Fix

**Status:** Production Ready ✅

### Critical Fix
- Integer overflow bug for large yacht IDs
- Changed yacht_id from integer to string throughout
- Fixed API HTTP 500 errors

---

## v2.5.2 (November 30, 2025) - Database Fallback

**Status:** Production Ready ✅

### Added
- Database fallback for prices when API fails
- Multi-format API response handling
- Better error handling

---

## v2.5.1 (November 30, 2025) - API Fix

**Status:** Production Ready ✅

### Fixed
- Removed tripDuration parameter from API calls
- Fixed HTTP 500 errors

---

## v2.5.0 (November 30, 2025) - Date Picker Fix

**Status:** Production Ready ✅

### Fixed
- isInitialLoad flag bug
- Changed to time-based detection
- Date picker triggers availability checks correctly

---

## v2.3.9 (November 29, 2025) - Debug Logging

**Status:** Production Ready ✅

### Added
- Enhanced debug logging for API calls
- Detailed error messages

---

## v2.3.8 (November 29, 2025) - Carousel Fix

**Status:** Production Ready ✅

### Fixed
- Carousel price loading uses cached prices
- Removed unnecessary API calls

---

## v2.3.7 (November 29, 2025) - Card Design Update

**Status:** Production Ready ✅

### Changed
- Updated search results card design
- Split yacht name and model
- Changed specs display
- Red DETAILS button
- Cleaner price display

---

## v2.3.0 (November 30, 2025) - Complete Booking System

**Status:** Production Ready ✅

### Major Features
- Complete booking system with customer form
- Admin booking management dashboard
- Balance payment system
- HTML email templates
- CSS optimization

### Database Version
- 1.4

---

## v2.2.4 (November 29, 2025) - HTML Emails

**Status:** Production Ready ✅

### Added
- Professional HTML email templates
- Responsive email design
- Payment action buttons

---

## v2.2.3 (November 28, 2025) - Balance Payment

**Status:** Production Ready ✅

### Added
- Balance payment page
- Secure payment links
- Automatic status updates

---

## v2.2.2 (November 27, 2025) - Admin Dashboard

**Status:** Production Ready ✅

### Added
- Complete admin booking dashboard
- Statistics and filters
- CSV export

---

## v2.2.1 (November 26, 2025) - CSS Refactoring

**Status:** Production Ready ✅

### Changed
- CSS refactoring for performance
- Conditional CSS loading

---

## v2.2.0 (November 25, 2025) - Customer Form

**Status:** Production Ready ✅

### Added
- Customer booking form modal
- Collect customer data before payment

### Database Version
- 1.4

---

## v2.1.0 (November 24, 2025) - Booking Manager Integration

**Status:** Production Ready ✅

### Added
- Booking Manager integration
- Live price updates
- Saturday validation
- Reservation creation

---

## v1.9.4 (November 23, 2025) - Bug Fixes

**Status:** Production Ready ✅

### Fixed
- Yacht sync improvements
- Equipment catalog fixes

---

## v1.0.0 (November 1, 2025) - Initial Release

**Status:** Production Ready ✅

### Features
- Search widget
- Search results display
- Our fleet page
- Yacht details page
- Database storage
- Booking Manager API integration
- Equipment icons

### Database Version
- 1.0

---

## Development Timeline

**Total Development Time:** ~2 months  
**Total Versions:** 26+  
**Current Version:** 17.0  
**Database Version:** 1.6  
**Status:** Ready for Production Testing ✅

---

## Key Milestones

1. **v1.0.0** - Initial release with search and display
2. **v2.0.0** - Booking system added
3. **v2.3.0** - Complete booking flow with emails
4. **v2.5.0** - UI improvements and bug fixes
5. **v2.5.6** - Guest user system added
6. **v2.5.9** - Custom guest login completed
7. **v17.0** - Complete Base Manager System with digital signatures and PDF generation

---

## Next Steps

- Monitor guest system usage
- Gather user feedback
- Optimize performance
- Add more features as needed

---

**Last Updated:** December 3, 2025  
**Current Version:** 17.0  
**Status:** Ready for Production Testing ✅
