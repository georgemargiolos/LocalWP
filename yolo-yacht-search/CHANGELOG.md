## [72.7] - 2025-12-19

### Improved
- **Yacht Details Default Date Selection** - When visiting a yacht from "Our Yachts" page (without search dates), the date picker now defaults to the **first available future week** instead of specifically looking for a July week
  - Previous behavior: Only pre-selected dates if a July week was available (problematic in Dec-June)
  - New behavior: Pre-selects the earliest available week regardless of month
  - If no prices exist, shows "Select dates" placeholder as before

### Files Modified
- `yolo-yacht-search.php` - Version bump to 72.7
- `public/templates/yacht-details-v3.php` - Updated date selection logic

---

## [72.6] - 2025-12-18

### Fixed
- **CRITICAL: Auto-Sync Never Running** - The `YOLO_YS_Auto_Sync` class was loaded but never instantiated, meaning the WordPress cron hooks were never registered. Auto-sync was completely broken since v30.0!
  - Added class instantiation in main plugin file
  - Added `schedule_events()` call on plugin activation
  - This explains why yachts showed "No pricing available" until manual sync was run

### Technical Details
- The class constructor registers `add_action()` hooks for cron events
- Without instantiation, `run_yacht_sync()` and `run_offers_sync()` were never called by WordPress cron
- Users must deactivate and reactivate the plugin (or save auto-sync settings) to schedule the cron events

### Files Modified
- `yolo-yacht-search.php` - Added `new YOLO_YS_Auto_Sync()` instantiation, version bump to 72.6
- `includes/class-yolo-ys-activator.php` - Added `YOLO_YS_Auto_Sync::schedule_events()` call

---

## [72.5] - 2025-12-18

### Fixed
- **Removed Dead Code in admin-documents.php** - Removed 150+ lines of unreachable code after return statement in render_admin_page() method
- **Type Casting: total_pages** - Fixed float to int type issue in admin-bookings.php pagination (ceil() returns float, now cast to int)
- **Type Casting: esc_attr()** - Fixed int to string type issues in admin.php for page ID attributes

### Code Quality
- Analyzed 3,355+ issues from PHPStan, Psalm, PHPMD, and PHPCS reports
- Most issues were false positives due to WordPress plugin architecture (snake_case naming, dynamic method calls via hooks, cross-file variable usage)
- Defensive `return` statements after `wp_send_json_*()` calls are intentional and follow WordPress best practices

### Files Modified
- `yolo-yacht-search.php` - Version bump to 72.5
- `admin/class-yolo-ys-admin-documents.php` - Removed unreachable code
- `admin/class-yolo-ys-admin-bookings.php` - Fixed total_pages type casting
- `admin/class-yolo-ys-admin.php` - Fixed esc_attr type casting

---

## [72.4] - 2025-12-18

### Added
- **Equipment Notes Feature** - Added ability to add notes to individual equipment items in Check-In/Check-Out forms
  - Notes icon button next to each equipment checkbox
  - Expandable textarea for notes input
  - Notes saved with equipment checklist data
  - Notes displayed in PDF documents with highlighted styling

### Fixed
- **Undefined Variable: $email_match** - Fixed potential undefined variable in CRM duplicate detection (class-yolo-ys-crm.php)
- **Undefined Variables in Document Viewer** - Added initialization for $licenses, $booking, $crew_list variables (yolo-admin-document-viewer.php)
- **Undefined Variable: $customer** - Added initialization in CRM page to prevent warnings (crm-page.php)

### Files Modified
- `yolo-yacht-search.php` - Version bump to 72.4
- `admin/partials/base-manager-checkin.php` - Added equipment notes UI
- `admin/partials/base-manager-checkout.php` - Added equipment notes UI
- `admin/css/base-manager.css` - Added equipment notes styling
- `admin/js/base-manager.js` - Added equipment notes JavaScript functionality
- `includes/class-yolo-ys-pdf-generator.php` - Added equipment notes display in PDF
- `includes/class-yolo-ys-crm.php` - Fixed undefined $email_match variable
- `admin/partials/crm-page.php` - Fixed undefined $customer variable
- `admin/partials/yolo-admin-document-viewer.php` - Fixed undefined variables

---

## [70.3] - 2025-12-17

### Added
- **Delete Booking** - Admin can now delete bookings from the Bookings page with confirmation dialog

### Fixed
- **HTML Tags in Login Error** - Guest login page now properly renders HTML tags in error messages instead of showing raw tags

### Files Modified
- `admin/class-yolo-ys-admin.php` - Added delete booking AJAX handler
- `admin/class-yolo-ys-admin-bookings.php` - Added Delete link to Actions column
- `admin/js/yolo-yacht-search-admin.js` - Added JavaScript for delete confirmation and AJAX
- `public/partials/yolo-ys-guest-login.php` - Fixed HTML escaping (esc_html → wp_kses_post)
- `yolo-yacht-search.php` - Version bump to 70.3

---

## [70.2] - 2025-12-17

### Fixed
- **CRITICAL: Existing User Password Not Updated** - When a returning customer made a new booking, their password was NOT updated to match the new booking reference shown in the email. Now the password is always updated to match the latest booking.
- **Password Source Mismatch** - Fixed AJAX handler to use `$booking->bm_reservation_id` from database instead of local variable, ensuring consistency with email template.

### Files Modified
- `includes/class-yolo-ys-guest-users.php` - Now updates password for existing users
- `includes/class-yolo-ys-stripe-handlers.php` - Fixed booking reference source
- `yolo-yacht-search.php` - Version bump to 70.2

---

## [70.1] - 2025-12-17

### Changed
- **Removed BM- Prefix** - Booking reference now displays the raw `bm_reservation_id` without any prefix.
- **Password Format** - Password is now `{bm_reservation_id}YoLo` (e.g., `7333050630000107850YoLo`).
- **Booking Flow Refactored** - BM reservation is now created FIRST, before WordPress booking insert.
- **Version Bump** - Updated plugin version from 70.0 to 70.1.

### Files Modified
- `yolo-yacht-search.php` - Version bump
- `includes/class-yolo-ys-email.php` - Removed BM- prefix (4 occurrences)
- `includes/class-yolo-ys-stripe.php` - Removed BM- prefix
- `includes/class-yolo-ys-stripe-handlers.php` - Refactored booking flow
- `public/templates/booking-confirmation.php` - Removed BM- prefix
- `public/templates/balance-confirmation.php` - Removed BM- prefix
- `public/templates/balance-payment.php` - Updated lookup logic
- `admin/class-yolo-ys-admin-bookings.php` - Removed BM- prefix
- `admin/class-yolo-ys-admin-bookings-manager.php` - Removed BM- prefix
- `admin/partials/booking-detail.php` - Removed BM- prefix (2 occurrences)
- `admin/partials/base-manager-checkin.php` - Removed BM- prefix
- `admin/partials/base-manager-checkout.php` - Removed BM- prefix

---

## [70.0] - 2025-12-17

### Fixed
- **CRITICAL: Guest Login Password Mismatch** - Fixed issue where guest users could not log in because the password stored in WordPress did not match the password shown in the booking confirmation email.
- **Booking Reference Display Inconsistency** - Fixed confirmation page to show booking reference with `BM-` prefix, matching the email format.

### Changed
- **Password Generation Logic** - Updated all password generation code to use the correct `$booking_reference` formula (`BM-{bm_reservation_id}YoLo`).
- **Operation Order** - Reordered webhook handler to create BM reservation BEFORE guest user (so BM ID is available for password).
- **Version Bump** - Updated plugin version from 65.23 to 70.0.

### Files Modified
- `yolo-yacht-search.php` - Version bump
- `includes/class-yolo-ys-stripe-handlers.php` - Fixed AJAX handler password generation
- `includes/class-yolo-ys-stripe.php` - Reordered operations, fixed webhook handler password generation
- `public/templates/booking-confirmation.php` - Fixed booking reference display

---

## [65.23] - 2025-12-17

### Fixed
- **CRITICAL: Immediate Spinner Display** - Fixed issue where the loading spinner did not show immediately after returning from Stripe due to server-side output buffering.

### Added
- **AJAX-Based Booking Creation** - Implemented a robust AJAX-based flow to ensure the spinner is displayed instantly, regardless of server configuration or caching.
- **New AJAX Handler** - Added `yolo_process_stripe_booking` to `class-yolo-ys-stripe-handlers.php` to handle the synchronous booking creation process (Stripe, BM, emails, analytics) in the background.
- **Customizable Spinner Texts** - Added 8 new text fields to the Settings page for all 4 stages of the progressive loading spinner.

### Changed
- **Booking Confirmation Flow** - Rewrote `public/templates/booking-confirmation.php` to:
    1. Check for existing booking.
    2. If not found, display spinner immediately.
    3. Use JavaScript to call the new AJAX handler.
    4. Reload page on successful booking creation.
- **Spinner Text Logic** - Updated spinner JavaScript to use the new customizable texts from settings and maintain the progressive timing (0s, 10s, 35s, 45s).
- **Version Bump** - Updated plugin version from 65.22 to 65.23.

### Removed
- **Debug Code** - Removed the accidental DEBUG section from `public/templates/yacht-details-v3.php` (introduced in v65.19 and missed in v65.21/v65.22).

---

## [65.22] - 2025-12-17

### Fixed
- **Debug Code Removal** - Removed the accidental DEBUG section from `public/templates/yacht-details-v3.php`.

### Changed
- **Version Bump** - Updated plugin version from 65.21 to 65.22.

---

## [65.21] - 2025-12-17

### Added
- **Immediate Spinner Display (Attempt 1)** - Implemented output buffering flush to attempt to show the spinner immediately after Stripe redirect.
- **Progressive Spinner Text** - Added timed JavaScript logic to update spinner text at 10s, 35s, and 45s intervals.
- **Responsive Spinner Design** - Added CSS for a responsive spinner that scales correctly on mobile devices.

### Changed
- **Version Bump** - Updated plugin version from 65.20 to 65.21.

---

## [41.28] - 2024-12-09

### Fixed
- **CRITICAL: Purchase Event Tracking** - Purchase event now fires correctly on booking confirmation page for both GA4 and Facebook

### Added
- **Client-Side GA4 Purchase Tracking** - Added dataLayer.push() for GTM on confirmation page with transaction_id, currency, value, and items
- **Client-Side Facebook Pixel Purchase Tracking** - Added fbq() Purchase event with eventID for deduplication
- **Server-Side Facebook CAPI Purchase Tracking** - Added Purchase tracking via CAPI with user data (email, phone, name) for better attribution

### Changed
- Modified `/public/templates/booking-confirmation.php` - Added Purchase event tracking in `yolo_show_booking_confirmation()` function (lines 197-230)
- Modified `/public/templates/booking-confirmation.php` - Added Facebook CAPI Purchase tracking in `yolo_create_booking_from_stripe()` function (lines 339-358)
- Updated plugin version from 41.27 to 41.28 in `yolo-yacht-search.php`

### Technical Details
- **Problem:** Purchase event was only in webhook handler (optional/not configured), so conversions weren't tracked
- **Solution:** Added Purchase tracking to confirmation page where booking is actually created
- **Event Flow:** Stripe payment → confirmation page → booking created → Purchase event fires (server-side CAPI) → confirmation displayed → Purchase event fires (client-side GA4 + Pixel)
- **Deduplication:** eventID used to prevent double-counting between Pixel and CAPI
- **User Data:** Email, phone, first name, last name sent to CAPI for better Facebook attribution

### Impact
- ✅ All 7 booking funnel events now working (search, view_item, add_to_cart, begin_checkout, add_payment_info, generate_lead, purchase)
- ✅ Complete conversion tracking from search to purchase
- ✅ Revenue data flows to GA4 and Facebook
- ✅ ROAS (Return on Ad Spend) measurement enabled
- ✅ Server-side tracking bypasses ad blockers
- ✅ Event deduplication prevents double-counting

### Files Modified
- `/public/templates/booking-confirmation.php` - Added Purchase event tracking (client-side + server-side)
- `/yolo-yacht-search.php` - Version bump to 41.28

### Documentation
- See [CHANGELOG-v41.28.md](CHANGELOG-v41.28.md) for detailed changelog
- See [TESTING-GUIDE-v41.28.md](TESTING-GUIDE-v41.28.md) for testing instructions
- See [HANDOFF-v41.28.md](HANDOFF-v41.28.md) for comprehensive handoff document

---

## [30.6] - 2025-12-06

### Fixed
- Fixed an issue where the yacht search form would not submit correctly on certain mobile browsers.
- Corrected a bug in the price calculation logic for long-term charters.

### Changed
- Updated the default currency symbol to use the WordPress setting instead of hardcoding EUR.

---

## [30.5] - 2025-12-05

### Added
- Implemented a new feature to allow custom descriptions for yachts via the admin panel.

### Fixed
- Resolved a conflict with a popular caching plugin that was preventing the availability calendar from loading.

---

## [30.4] - 2025-12-04

### Fixed
- Minor CSS adjustments for better alignment of the booking widget on tablet devices.

---

## [30.3] - 2025-12-03

### Fixed
- Fixed a critical security vulnerability related to un-sanitized user input in the quote request form.

---

## [30.2] - 2025-12-02

### Changed
- Refactored the main plugin class for better maintainability and adherence to WordPress coding standards.

---

## [30.1] - 2025-12-01

### Added
- Initial release of the YOLO Yacht Search & Booking plugin.
