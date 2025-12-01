# Version History

Complete version history of the YOLO Yacht Search & Booking Plugin.

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
**Total Versions:** 25+  
**Current Version:** 2.5.9  
**Database Version:** 1.5  
**Status:** Production Ready ✅

---

## Key Milestones

1. **v1.0.0** - Initial release with search and display
2. **v2.0.0** - Booking system added
3. **v2.3.0** - Complete booking flow with emails
4. **v2.5.0** - UI improvements and bug fixes
5. **v2.5.6** - Guest user system added
6. **v2.5.9** - Custom guest login completed

---

## Next Steps

- Monitor guest system usage
- Gather user feedback
- Optimize performance
- Add more features as needed

---

**Last Updated:** December 1, 2025  
**Current Version:** 2.5.9  
**Status:** Production Ready ✅
