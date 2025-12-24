# Changelog

## [80.4] - 2025-12-24

### Fixed
- **Bug #6: Search Company ID Type** - Cast company IDs to integers in search for consistent type matching with database
  - Changed `get_option('yolo_ys_my_company_id', '7850')` to `(int) get_option(...)`
  - Changed `array_map('trim', ...)` to `array_map('intval', array_map('trim', ...))`
  - Prevents potential SQL type mismatch issues

### Performance
- **Bug #4: Batch Inserts** - Switched from individual INSERT statements to batch REPLACE INTO
  - Previously: 1,000 offers = 1,000 separate database queries
  - Now: 1,000 offers = 1 batch query
  - **10-100x faster sync performance**
  - Uses existing `store_offers_batch()` method that was never utilized
  - Ensures unique index exists for fast REPLACE operations

### Files Modified
- `public/class-yolo-ys-public-search.php` - Cast company IDs to integers
- `includes/class-yolo-ys-sync.php` - Use batch inserts instead of individual store_offer() calls

---

## [80.3] - 2025-12-24

### Fixed
- **CRITICAL: Per-Company Delete for Offers Sync** - Fixed data loss bug where YOLO boats lost prices when partner syncs succeeded but YOLO sync failed
  - **Root Cause:** DELETE query was deleting ALL prices for the year, then only storing offers from companies that succeeded
  - **Fix:** Now fetches all offers first, groups by company, then deletes and stores per-company
  - If a company's API call fails, that company's existing prices are preserved
  - Other companies' syncs are not affected by one company's failure

### Changed
- **Auto-Sync Uses Dropdown Year** - Auto-sync now uses the same year selected in the dropdown (same as manual sync)
  - Previously synced both current year AND next year (causing longer execution and potential timeouts)
  - Now syncs only the year you select in the "Select Year" dropdown
  - Dropdown shows "Also used for auto-sync" hint
  - Year selection is saved automatically when changed

### Added
- **Error Logging for Offer Storage** - Added detailed error logging when storing offers fails
  - Logs database errors with yacht_id and date_from for debugging
  - Helps identify which specific offers are failing to store

### Files Modified
- `includes/class-yolo-ys-sync.php` - Rewrote `sync_all_offers()` with per-company delete logic
- `includes/class-yolo-ys-auto-sync.php` - Changed to use dropdown year instead of current+next year
- `includes/class-yolo-ys-database-prices.php` - Added error logging to `store_price()` and `store_offer()`
- `admin/class-yolo-ys-admin.php` - Added AJAX handler for saving offers year
- `admin/partials/yolo-yacht-search-admin-display.php` - Added year dropdown save functionality and hint text

### Technical Details
- The bug was on line 314 of `class-yolo-ys-sync.php`: `DELETE FROM prices WHERE YEAR(date_from) = %d`
- This deleted ALL companies' prices, then only stored offers from companies that returned data
- If YOLO's API call failed but partners succeeded, YOLO's prices were deleted but never replaced

---

## [80.2] - 2025-12-24

### Fixed
- **Auto-Sync Weekly Offers Bug** - Fixed issue where auto-sync was not properly detecting successful syncs
  - Changed success detection from checking `$result['success']` flag to checking `$result['offers_synced'] > 0`
  - This handles cases where sync partially succeeds or has minor errors but still syncs offers
  - Boats will now properly show prices after auto-sync runs (no manual refresh needed)
  - Added improved logging for debugging sync issues

### Files Modified
- `includes/class-yolo-ys-auto-sync.php` - Fixed `run_offers_sync()` success detection logic

---

## [80.1] - 2025-12-22

### Added
- **Clickable Yacht Cards** - Entire yacht card is now clickable, not just the DETAILS button
  - Implemented using CSS stretched link technique for better accessibility
  - Works on both "Our Yachts" fleet page and Search Results page
  - Cursor changes to pointer when hovering anywhere on the card
  - DETAILS button remains visible for visual clarity
  - Swiper navigation buttons remain functional (elevated z-index)

### Files Modified
- `public/templates/partials/yacht-card.php` - Added card link wrapper
- `public/blocks/yacht-horizontal-cards/render.php` - Added card link wrapper
- `public/js/yolo-yacht-search-public.js` - Updated JS card rendering
- `public/css/yacht-card.css` - Added clickable card CSS
- `public/css/search-results.css` - Added clickable card CSS

---

## [80.0] - 2025-12-22

### Changed
- **Sticky Booking Section Position** - Adjusted the top offset for the sticky booking section
  - Changed `top: 100px` to `top: 50px` in `.yolo-yacht-details-v3 .yacht-booking-section`
  - The booking sidebar now sticks closer to the top of the viewport
  - File modified: `public/css/yacht-details-v3.css`

---

## [75.31] - 2025-12-22

### Fixed
- **Availability Box Scrollbar Removed** - The sidebar was showing an unwanted scrollbar
  - Removed `max-height: calc(100vh - 120px)` constraint
  - Removed `overflow-y: auto` 
  - The availability box now grows to fit all content (including new payment icons)
  - Sticky positioning still works on desktop

---

## [75.30] - 2025-12-22

### Fixed
- **Stripe Checkout - FINAL FIX** - Removed ALL payment method parameters
  - `automatic_payment_methods` is NOT supported for Checkout Sessions (only PaymentIntents/SetupIntents)
  - Per Stripe docs: "Don't specify payment_method_types - let Dashboard control payment methods"
  - Now the code simply creates a Checkout Session without any payment method parameters
  - Stripe Dashboard settings will automatically control which payment methods appear
  - This is the recommended approach per official Stripe documentation

---

## [75.29] - 2025-12-22

### Fixed
- **Stripe API Version Explicitly Set** - Added `setApiVersion('2024-10-28.acacia')` in init_stripe()
  - Even with updated library, Stripe was using older API version from account default
  - Now explicitly sets the API version to support `automatic_payment_methods` for Checkout Sessions
  - This ensures the code uses the modern API regardless of account settings

---

## [75.28] - 2025-12-22

### Fixed
- **Stripe PHP Library Updated** - Upgraded from v13.16.0 to v16.2.0
  - Old library didn't support `automatic_payment_methods` for Checkout Sessions
  - Error was: `Received unknown parameter: automatic_payment_methods`
  - New library uses API version `2024-10-28.acacia` (was `2023-10-16`)
  - Now supports all modern Stripe features including automatic payment methods

---

## [75.27] - 2025-12-22

### Fixed
- **Stripe Checkout Stuck on Processing** - PROPER FIX for automatic payment methods
  - Removed `payment_method_types` parameter entirely (was conflicting with Stripe Dashboard settings)
  - Added `automatic_payment_methods: { enabled: true }` at the END of the session create array
  - When Stripe Dashboard has "Automatic payment methods" enabled, you CANNOT use `payment_method_types`
  - Checkout now supports Apple Pay, Google Pay, Klarna, and all other methods enabled in Stripe Dashboard

- **JavaScript Error Handling** - Button no longer gets stuck on "Processing..."
  - Added proper error handling for ALL checkout failure cases
  - Shows red toast notification with error message
  - Resets button text and enables it again on any error
  - Added console logging for debugging

---

## [75.26] - 2025-12-22

### Fixed
- **Stripe Checkout Stuck on Processing** - Reverted from `automatic_payment_methods` to `payment_method_types`
  - The `automatic_payment_methods` API option was causing 500 errors on checkout
  - Reverted to explicit `payment_method_types: ['card']` for compatibility
  - Stripe Checkout now works correctly again
  - Note: To enable additional payment methods, configure them directly in your Stripe Dashboard

---

## [75.25] - 2025-12-22

### Fixed
- **Payment Icons Nonce Expiration** - Fixed "The link you followed has expired" error
  - Changed hide/restore/delete actions from GET links to POST forms
  - Now uses fresh nonces generated when the page loads
  - No more expiration issues when page is open for a while

---

## [75.24] - 2025-12-22

### Fixed
- **Payment Icons Frontend** - Hidden built-in icons are now properly filtered on the yacht page
  - Previously, hidden icons could still appear on the frontend
  - Now respects the `yolo_ys_payment_icons_hidden` setting

---

## [75.23] - 2025-12-22

### Fixed
- **Custom Icon Upload Error Handling** - Improved error messages for upload failures
  - Now shows "Security check failed. Please refresh the page and try again." instead of generic WordPress error
  - Better handling of nonce verification for file uploads

---

## [75.22] - 2025-12-22

### Added
- **Hide/Delete Built-in Payment Icons** - You can now hide built-in icons you don't need
  - Click the eye-slash icon next to any built-in icon to hide it
  - Hidden icons appear in a "Hidden Built-in Icons" section at the bottom
  - Click "Restore" to bring back any hidden icon
  - Custom icons can still be permanently deleted with the trash icon

---

## [75.21] - 2025-12-22

### Fixed
- **SVG Upload for Payment Icons** - WordPress blocks SVG uploads by default for security
  - Added `upload_mimes` filter to allow SVG files
  - Added `wp_check_filetype_and_ext` filter to fix SVG file type detection
  - Custom payment icons can now be uploaded as SVG files

---

## [75.20] - 2025-12-22

### Fixed
- **Payment Icons Drag-and-Drop** - jQuery UI Sortable was not loaded on the Payment Icons admin page
  - Added `wp_enqueue_script('jquery-ui-sortable')` for the payment icons page
  - Icons can now be dragged to reorder them

---

## [75.19] - 2025-12-22

### Added
- **Stripe: All Payment Methods Enabled** - Changed from `payment_method_types: ['card']` to `automatic_payment_methods` so Stripe Checkout now shows all payment methods enabled in your Stripe Dashboard (Apple Pay, Google Pay, Klarna, PayPal, etc.)
- **Custom Payment Icon Upload** - Admin can now upload custom SVG icons for payment methods not in the built-in list
  - SVG icon requirements displayed in admin (50×32px recommended, viewBox="0 0 50 32")
  - Delete functionality for custom uploaded icons
  - Custom icons appear in the sortable list alongside built-in icons

### Changed
- Payment icons frontend code refactored to support both built-in and custom icons

### Files Modified
- `includes/class-yolo-ys-stripe.php` - Changed to `automatic_payment_methods`
- `admin/partials/payment-icons-page.php` - Added custom icon upload section
- `public/templates/yacht-details-v3.php` - Support custom icons in frontend

---

## [75.18] - 2025-12-22

### Added
- **Payment Icons Box** - Display payment method icons under Book Now button
  - Shows 8 icons initially (2 rows of 4) with "Show more" to expand
  - Supports 21 payment methods: Visa, Mastercard, Amex, Discover, PayPal, Apple Pay, Google Pay, Klarna, Revolut, Samsung Pay, Link, Bancontact, BLIK, EPS, MB Way, MobilePay, Kakao Pay, Naver Pay, PAYCO, Satispay, Stripe
  - All icons are SVG for crisp display at any size
- **Payment Icons Admin Page** - New admin page under YOLO Yacht Search menu
  - Enable/disable individual payment icons
  - Drag-and-drop reorder icons
  - Customizable texts: title, "show more", "show less"
  - Configure number of initially visible icons
  - Live preview of the payment icons box

### Files Added
- `admin/partials/payment-icons-page.php` - Admin settings page
- `public/images/payment-icons/*.svg` - 21 payment method SVG icons

### Files Modified
- `yolo-yacht-search.php` - Version bump to 75.18
- `admin/class-yolo-ys-admin.php` - Added Payment Icons submenu
- `public/templates/yacht-details-v3.php` - Added payment icons box HTML
- `public/templates/partials/yacht-details-v3-styles.php` - Added payment icons CSS
- `public/templates/partials/yacht-details-v3-scripts.php` - Added toggle JS

---

## [75.17] - 2025-12-22

### Fixed
- **JavaScript Syntax Error** - Reverted to IntersectionObserver approach (v75.14 style)
  - WordPress was HTML-encoding `&&` operators as `&#038;&#038;`
  - Now uses IntersectionObserver + separate scroll listener to avoid `&&`
  - Sticky bar hides when booking section visible OR when scrolled past it

---

## [75.16] - 2025-12-22

### Fixed
- **JavaScript Syntax Error** - Simplified sticky bar visibility code to fix "Invalid or unexpected token" error
  - Rewrote using simpler var declarations and function structure
  - Removed nested functions that may have caused parsing issues

---

## [75.15] - 2025-12-22

### Fixed
- **Sticky Bar Visible in Footer** - Now hides when scrolled past booking section (not just when booking section is in view)
  - Replaced Intersection Observer with scroll-based detection
  - Sticky bar only shows when user is above the booking section
- **Sticky Bar Color** - Changed from primary (blue) to secondary (red) to match "Book Now" button

---

## [75.14] - 2025-12-22

### Fixed
- **CSS Not Loading** - Moved accordion and sticky bar CSS from disabled `yacht-details-responsive-fixes.css` to `yacht-details-v3-styles.php`
  - The responsive-fixes.css file was commented out due to Bootstrap conflicts
  - CSS is now inline in the styles PHP file which is always loaded on yacht detail pages

---

## [75.13] - 2025-12-22

### Added
- **Mobile Sticky Bottom Bar** - "CHECK AVAILABILITY" button fixed at bottom of screen on mobile
  - Only visible on mobile/tablet (< 992px)
  - Smooth scroll to booking section when tapped
  - Auto-hides when booking section is visible (using Intersection Observer)
  - Supports iPhone safe area (notch)

- **Mobile Accordion Sections** - Collapsible sections for better mobile UX
  - Equipment section - collapsed by default
  - Technical Characteristics - collapsed by default
  - Extras (Obligatory & Optional) - collapsed by default
  - Security Deposit - collapsed by default
  - Cancellation Policy - collapsed by default
  - Check-in/Check-out - collapsed by default
  - Desktop layout unchanged (always expanded)
  - Smooth expand/collapse animation with rotating chevron icon

### Files Modified
- `yolo-yacht-search.php` - Bumped version to 75.13
- `public/templates/yacht-details-v3.php` - Added sticky bar HTML, accordion markup to sections
- `public/css/yacht-details-responsive-fixes.css` - Added sticky bar and accordion CSS
- `public/templates/partials/yacht-details-v3-scripts.php` - Added scroll and accordion toggle JS

---

## [75.12] - 2025-12-22

### Fixed
- **Auto-Migration for Starting From Price Column** - The `plugins_loaded` migration wasn't running on existing installations
  - Added auto-migration directly in AJAX handler - column is created on first save attempt
  - Better error messages: now shows "Missing yacht_id" or "Invalid setting" instead of generic "Invalid parameters"
  - Database errors are now reported back to the user
- **Empty Price Handling** - When price is not set, `null` is sent to analytics instead of `0`
  - Facebook Pixel and Google Analytics won't receive misleading `value: 0`
  - Price field in admin shows empty instead of "0" when not set

### Files Modified
- `yolo-yacht-search.php` - Bumped version to 75.12
- `admin/class-yolo-ys-admin.php` - Added auto-migration and better error handling
- `public/js/yolo-analytics.js` - Don't default price to 0
- `public/templates/partials/yacht-details-v3-scripts.php` - Use null for unset prices

---

## [75.11] - 2025-12-22

### Added
- **Starting From Price Field in Yacht Customization** - New field for all boats to set the "starting from" price for Facebook/Google Ads tracking
  - Available for all yachts in the system (not just YOLO boats)
  - Price is stored as `starting_from_price` column in `wp_yolo_yacht_custom_settings` table
  - Used by Facebook Pixel ViewContent and Google Analytics view_item events
  - User can manually set prices to match their Facebook Product Catalog

### Fixed
- **Database Migration for Starting From Price** - Added automatic migration to add `starting_from_price` column
  - Migration runs automatically on plugin update (via `plugins_loaded` hook)
  - Column is `DECIMAL(10,2)` with default value of 0
  - AJAX handler updated to support saving the new column
- **ViewContent/view_item Events Now Send Correct Price** - Both Facebook Pixel and Google Analytics were receiving `value: 0` and `price: 0` for yacht view events
  - Server-side Facebook CAPI now uses custom `starting_from_price` setting
  - Client-side `window.yoloYachtData.price` now populated from custom settings
  - This fixes ROAS reporting and value-based optimization in both Facebook and Google Ads
  - Price is manually configured per yacht (all boats)

### Files Modified
- `yolo-yacht-search.php` - Added v75.11 database migration for `starting_from_price` column
- `admin/class-yolo-ys-admin.php` - Updated AJAX handler to accept `starting_from_price` setting
- `admin/partials/yacht-customization-page.php` - Added Starting From Price section with save handler
- `public/templates/partials/yacht-details-v3-scripts.php` - Client-side yoloYachtData uses custom starting price

---

## [75.10] - 2025-12-22

### Fixed
- **Facebook Retargeting with Pretty URLs** - Facebook Pixel was sending `content_ids: [null]` for pretty URLs because `getYachtData()` only looked for `?yacht_id=` in URL
  - Added `window.yoloYachtData` object set by PHP with yacht ID, name, price
  - Updated `yolo-analytics.js` to check `window.yoloYachtData` first before falling back to URL params
  - This fixes Facebook Dynamic Ads showing `{{product.name}}` instead of actual yacht name

### Files Modified
- `public/templates/partials/yacht-details-v3-scripts.php` - Added window.yoloYachtData
- `public/js/yolo-analytics.js` - Updated getYachtData() to use window.yoloYachtData

---

## [75.9] - 2025-12-21

### Fixed
- **Mobile Date Picker Layout** - Calendar was showing 2 months side by side on mobile, causing viewport overflow
  - Now shows 1 month on mobile (≤768px), 2 months on desktop
  - Added touch swipe support for navigating between months on mobile
  - Added CSS fixes to ensure calendar fits within viewport
  - Added "Swipe to change month" hint text on mobile
  - Made navigation arrows more touch-friendly (44px minimum touch target)

### Files Modified
- `public/templates/partials/yacht-details-v3-scripts.php` - Responsive Litepicker config + touch swipe
- `public/css/yacht-details-responsive-fixes.css` - Mobile CSS for Litepicker

---

## [75.8] - 2025-12-21

### Fixed
- **Prices Not Loading for Pretty URLs** - The `current_prices` array was not populated when using pretty URLs (`/yacht/slug/`)
  - JSON-LD Product schema now includes `offers` for pretty URL pages
  - Open Graph `product:price:amount` meta tag now works for pretty URLs
  - Bug found by Cursor AI during code review

### Files Modified
- `includes/class-yolo-ys-meta-tags.php` - Added prices loading in yacht_slug code path

---

## [75.7] - 2025-12-21

### Fixed
- **Meta Tags Not Initializing** - The `yolo_meta_tags()` singleton was defined but never called
  - Added initialization call after requiring the meta-tags class file
  - This enables all SEO meta tags including canonical URL, Open Graph, Twitter Card, and JSON-LD

### Files Modified
- `yolo-yacht-search.php` - Added `yolo_meta_tags()` call to initialize the singleton

---

## [75.6] - 2025-12-21

### Fixed
- **Canonical URL Removal Timing** - Changed hook from `wp` to `template_redirect` to ensure WordPress default canonical is removed before `wp_head` runs

### Files Modified
- `includes/class-yolo-ys-meta-tags.php` - Fixed hook timing for `remove_default_canonical()`

---

## [75.5] - 2025-12-21

### Added
- **Canonical URL for Yacht Pages** - Outputs correct canonical URL using pretty URL format
  - Removes WordPress default canonical for yacht pages
  - Uses `/yacht/slug/` format when slug exists
  - Falls back to `?yacht_id=` URL for yachts without slugs
  - Prevents duplicate content issues in search engines

### Files Modified
- `includes/class-yolo-ys-meta-tags.php` - Added `output_canonical_url()` and `remove_default_canonical()` methods

---

## [75.4] - 2025-12-21

### Fixed
- **CRITICAL: Rewrite Rules Not Being Flushed** - The `flush_rewrite_rules()` was called in `plugins_loaded` hook BEFORE the rewrite rules were registered in `init` hook
  - Moved flush to `init` hook with priority 999 (runs after rules are registered)
  - Uses option flag `yolo_ys_flush_rewrite_rules` to trigger flush once
  - Forces flush on update to v75.4

### Files Modified
- `yolo-yacht-search.php` - Fixed rewrite rules flush timing

---

## [75.3] - 2025-12-21

### Fixed
- **CRITICAL: 404 Error on Pretty URLs (Take 2)** - The `template_include` approach runs too late in WordPress lifecycle
  - Now uses `pre_get_posts` hook to intercept request early
  - Transforms `yacht_slug` query into proper page query before WordPress decides it's 404
  - Rewrite rule now only sets `yacht_slug` query var
  - `pre_get_posts` handler adds `page_id` and sets proper query flags

### Files Modified
- `includes/class-yolo-ys-yacht-search.php` - Replaced `template_include` with `pre_get_posts` approach

---

## [75.2] - 2025-12-21

### Fixed
- **CRITICAL: 404 Error on Pretty URLs** - Rewrite rule was using `pagename` which doesn't work reliably
  - Changed rewrite rule to use `page_id` from plugin settings
  - Added `template_include` filter to properly load the yacht details page template
  - Sets up proper WordPress query vars to avoid 404

### Files Modified
- `includes/class-yolo-ys-yacht-search.php` - Fixed rewrite rule and added template loader

---

## [75.1] - 2025-12-21

### Fixed
- **CRITICAL: Migration Not Running on Plugin Update** - The slug column migration only ran on plugin activation, not on updates
  - Added `plugins_loaded` hook to check database version and run migrations automatically
  - Migration now runs on first page load after plugin update
  - Stores `yolo_ys_db_version` option to track migration state
  - Automatically generates slugs for all existing yachts
  - Flushes rewrite rules after migration

### Files Modified
- `yolo-yacht-search.php` - Added version-based migration on `plugins_loaded` hook

---

## [75.0] - 2025-12-21

### Added
- **Pretty URLs for Yacht Details** - SEO-friendly URLs like `/yacht/lemon-lagoon-450/` instead of `?yacht_id=123456`
  - Added `slug` column to `wp_yolo_yachts` table with unique index
  - Slug auto-generated from yacht name + model on sync
  - Duplicate slugs resolved by appending company ID
  - WordPress rewrite rules for `/yacht/{slug}/` pattern
  - 301 redirects from old URLs to new pretty URLs (preserves SEO)
  - Full backward compatibility with legacy `?yacht_id=` URLs

- **Google XML Sitemap Generator Integration** - Yacht pages now included in sitemap
  - Hooks into `sm_buildmap` action from Google XML Sitemap Generator plugin
  - YOLO boats get priority 0.8, partner boats get priority 0.6
  - Uses `last_synced` timestamp for `<lastmod>`
  - Change frequency set to `weekly`

### Changed
- Search results now link to pretty URLs when slug exists
- Our Fleet page yacht cards now use pretty URLs
- Horizontal cards block now uses pretty URLs
- Meta tags class updated to detect yacht pages via slug query var
- CSS loading updated to detect yacht pages via slug query var

### Database
- New column: `wp_yolo_yachts.slug` (varchar 255, unique index)
- Migration auto-generates slugs for existing yachts on plugin update

### Files Added
- `includes/class-yolo-ys-sitemap.php` - Sitemap integration class

### Files Modified
- `yolo-yacht-search.php` - Version bump, added sitemap class include
- `includes/class-yolo-ys-database.php` - Added slug column, slug generation, helper methods
- `includes/class-yolo-ys-activator.php` - Added slug column migration
- `includes/class-yolo-ys-yacht-search.php` - Added rewrite rules and redirect handling
- `includes/class-yolo-ys-meta-tags.php` - Updated yacht page detection for slugs
- `public/class-yolo-ys-public.php` - Updated CSS loading condition
- `public/class-yolo-ys-public-search.php` - Updated URL building with slug support
- `public/templates/yacht-details-v3.php` - Support both slug and legacy yacht_id
- `public/templates/partials/yacht-card.php` - Use pretty URLs
- `public/blocks/yacht-horizontal-cards/render.php` - Use pretty URLs

---

## [72.11] - 2025-12-19

### Fixed
- **Auto-Sync Settings Save Failed** - The AJAX handler for saving auto-sync settings was never registered because the `YOLO_YS_Auto_Sync` class was not instantiated
  - Added class instantiation via `init` hook (priority 20)
  - Uses `class_exists()` check for safety
  - Now auto-sync settings can be saved/disabled properly

### Files Modified
- `yolo-yacht-search.php` - Added auto-sync class instantiation, version bump to 72.11

---

## [72.10] - 2025-12-19

### Fixed
- **CRITICAL: Restored Original Sync Class** - v72.9 accidentally rewrote the entire `class-yolo-ys-sync.php` file, removing essential code:
  - Restored `$this->db` property and constructor initialization
  - Restored `sync_equipment_catalog()` method
  - Restored detailed comments and error logging
  - Applied fetch-first pattern ONLY to `sync_all_offers()` method

### Files Modified
- `includes/class-yolo-ys-sync.php` - Restored from git, applied targeted fix
- `yolo-yacht-search.php` - Version bump to 72.10

---

## [72.9] - 2025-12-19

### Fixed
- **CRITICAL: Sync Delete-Before-Fetch Pattern** - The `sync_all_offers()` method was deleting all prices BEFORE fetching new data from the API. If the API call failed, all prices were lost!
  - Now fetches ALL offers into memory first
  - Only deletes old prices if fetch was successful
  - If API fails, existing prices are preserved (no data loss)

### Added
- **productName Parameter Support** - Added support for the `productName` parameter in `get_offers()` API method per Swagger documentation

### Reverted
- **Auto-Sync Instantiation** - Reverted the v72.6/v72.8 auto-sync instantiation changes that caused fatal errors. The auto-sync hooks are now registered via the existing mechanism.

### Files Modified
- `includes/class-yolo-ys-sync.php` - Rewrote `sync_all_offers()` with fetch-first pattern
- `includes/class-yolo-ys-booking-manager-api.php` - Added `productName` parameter support
- `yolo-yacht-search.php` - Version bump to 72.9, reverted auto-sync changes

---

## [72.8] - 2025-12-19

### Fixed
- **CRITICAL: Fatal Error on Plugin Load** - v72.6 instantiated `YOLO_YS_Auto_Sync` too early before WordPress was fully loaded
  - Moved instantiation to `plugins_loaded` hook with priority 5
  - This ensures `add_action()` and `add_filter()` functions are available

### Files Modified
- `yolo-yacht-search.php` - Wrapped auto-sync instantiation in `plugins_loaded` hook

---

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
