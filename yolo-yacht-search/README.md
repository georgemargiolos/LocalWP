> **Note**
> This README file is for the YOLO Yacht Search & Booking Plugin, a comprehensive solution for yacht charter businesses. This document provides a detailed overview of the plugin's features, installation instructions, and usage guidelines.

# YOLO Yacht Search & Booking Plugin

**Version:** 80.2
**WordPress Version:** 5.8 or higher
**PHP Version:** 7.4 or higher
**License:** Proprietary
**Status:** Production Ready ✅

## Overview

The YOLO Yacht Search & Booking Plugin is a complete system for yacht charter businesses, providing a seamless experience for both customers and administrators. It integrates with the Booking Manager API for real-time yacht availability and pricing, and with Stripe for secure online payments. The plugin is designed to be highly customizable, allowing you to tailor it to your specific needs.

## 🚀 Latest Updates

### v80.2 - Auto-Sync Weekly Offers Fix (December 24, 2025)

**Bug Fix - Auto-Sync Success Detection**

**Problem:** Auto-sync for weekly offers was not properly detecting successful syncs, causing boats to not display prices until a manual refresh was performed.

**Root Cause:** The `run_offers_sync()` method was checking the `$result['success']` flag instead of verifying if offers were actually synced. This caused issues when syncs partially succeeded.

**Solution:** Changed success detection to check `$result['offers_synced'] > 0` instead of `$result['success']`.

**Files Modified:** 1 file (`includes/class-yolo-ys-auto-sync.php`)
**Backward Compatible:** Yes
**Breaking Changes:** None
**Production Ready:** ✅

---

### v80.1 - Clickable Yacht Cards (December 22, 2025)

**UX Enhancement - Full Card Clickability**

**Change:** The entire yacht card is now clickable, not just the DETAILS button.

**Implementation:**
- ✅ **Stretched Link Technique** - Used CSS to create an invisible link overlay covering the entire card
- ✅ **Accessibility** - Added proper aria-labels for screen readers
- ✅ **Visual Feedback** - Cursor changes to pointer on hover anywhere on the card
- ✅ **DETAILS Button** - Remains visible for visual clarity (converted to `<span>`)
- ✅ **Swiper Compatibility** - Image carousel navigation buttons remain functional

**Pages Affected:**
- Our Yachts (Fleet) page
- Search Results page
- Horizontal yacht cards block

**Files Modified:** 5 files
**Backward Compatible:** Yes
**Breaking Changes:** None
**Production Ready:** ✅ (Testing)

---

### v80.0 - Sticky Booking Section Position Adjustment (December 22, 2025)

**CSS Adjustment - Booking Sidebar Position**

**Change:** Adjusted the top offset for the sticky booking section on the yacht details page.

**Fix Applied:**
- ✅ **Top Offset Reduced** - Changed `top: 100px` to `top: 50px` in `.yolo-yacht-details-v3 .yacht-booking-section`
- ✅ **Closer to Viewport Top** - The booking sidebar now sticks closer to the top of the viewport for better visibility
- ✅ **Sticky Behavior Maintained** - The `position: sticky` property continues to work as expected

**Files Modified:** 1 file (public/css/yacht-details-v3.css)
**Backward Compatible:** Yes
**Breaking Changes:** None
**Production Ready:** ✅

---

### v75.31 - Availability Box Scrollbar Fix (December 22, 2025)

**CSS Fix - Removed Unwanted Scrollbar**

**Issue:** The sticky availability box on the yacht details page was showing an unwanted vertical scrollbar on desktop due to a `max-height` constraint that was no longer necessary after adding the payment icons.

**Fix Applied:**
- ✅ **Removed Height Constraint** - Removed `max-height: calc(100vh - 120px)` and `overflow-y: auto` from `.yacht-booking-section` in `public/css/yacht-details-v3.css`.
- ✅ **Box Grows to Fit** - The box now grows to fit all content, including the new payment icons, without a scrollbar.
- ✅ **Sticky Maintained** - The `position: sticky` property remains for desktop UX.

**Files Modified:** 1 file (public/css/yacht-details-v3.css)
**Backward Compatible:** Yes
**Breaking Changes:** None
**Production Ready:** ✅ (Tested & Verified)

---

### v75.30 - Stripe Checkout Final Fix (December 22, 2025)

**Critical Booking Fix - Checkout Session Stuck**

**Issue:** Stripe checkout was stuck on "Processing..." due to an "Received unknown parameter: automatic_payment_methods" error.

**Fix Applied:**
- ✅ **Final Solution** - Removed ALL payment method parameters (`payment_method_types` and `automatic_payment_methods`) from `create_checkout_session` and `create_balance_checkout_session` in `includes/class-yolo-ys-stripe.php`.
- ✅ **Stripe Recommended Approach** - This lets the Stripe Dashboard control which payment methods appear, which is the official, stable approach for Checkout Sessions.
- ✅ **Google Pay Confirmed** - Tested and confirmed working with Google Pay.

**Files Modified:** 1 file (includes/class-yolo-ys-stripe.php)
**Backward Compatible:** Yes
**Breaking Changes:** None
**Production Ready:** ✅ (Tested & Verified)

---

### v60.2 - Search Results Layout Fix (WORKING) (December 12, 2025)

**Critical Fix - v60.1 Was Broken**

**Issue:** v60.1 CSS fix didn't actually work - missing `width: 100%` property.

**Fix Applied:**
- ✅ **Complete CSS Solution** - Added all three required properties
- ✅ **Tested & Verified** - Manually tested on test server
- ✅ **Full Width Display** - Single yachts now take 100% width on large screens
- ✅ **Grid Preserved** - Multiple yachts still display in 3-column layout

**Technical Details:**
```css
/* Location: public/css/search-results.css, lines 484-488 */
@media (min-width: 992px) {
    .yolo-ys-section-header + .container-fluid .row.g-4 .col-lg-4:first-child:last-child {
        max-width: 100% !important;
        flex: 0 0 100% !important;
        width: 100% !important;  /* ← THIS WAS MISSING IN v60.1 */
    }
}
```

**Why v60.1 Failed:**
- Bootstrap's flex system requires all three properties to override
- Without `width: 100%`, card stayed at 33.33% width
- Fix was tested in console but not verified on actual server

**Files Modified:** 2 files (yolo-yacht-search.php, search-results.css)  
**Backward Compatible:** Yes  
**Breaking Changes:** None  
**Production Ready:** ✅ (Tested & Verified)

---

### v60.1 - Search Results Layout Fix (BROKEN - DO NOT USE)

**Status:** ❌ **BROKEN** - Use v60.2 instead

**Issue:** CSS fix incomplete, missing `width: 100%` property

---

### v60.0 - Automatic Image Optimization (December 12, 2025)

**Performance Enhancement - Massive Storage & Speed Improvements**

**Feature:** Images downloaded from Booking Manager API are now automatically optimized during yacht sync.

**Implementation:**
- ✅ **Automatic Optimization** - Every downloaded image is resized and compressed
- ✅ **WordPress Native** - Uses `wp_get_image_editor()` for reliable processing
- ✅ **Smart Resizing** - Max 1920x1080px (retina-ready, maintains aspect ratio)
- ✅ **Quality Compression** - 85% JPEG quality (excellent visual quality)
- ✅ **Zero Dependencies** - No external services or API costs

**Performance Impact:**
- 📉 **85-90% storage reduction** - 3 MB images → 400 KB
- ⚡ **97% faster page loads** - Fleet page: 27 MB → 720 KB
- 📱 **Mobile optimized** - Load time: 54s → 1.4s on 4G
- 💰 **Lower bandwidth costs** - Significant savings on hosting

**Technical Details:**
```php
// Location: includes/class-yolo-ys-database.php
// Line 379: Optimization call after image download
$this->optimize_yacht_image($local_path);

// Lines 572-643: New optimization method
private function optimize_yacht_image($image_path)
```

**Files Modified:** 1 file (class-yolo-ys-database.php)  
**Backward Compatible:** Yes  
**Breaking Changes:** None  
**Production Ready:** ✅

See [CHANGELOG-v60.0.md](CHANGELOG-v60.0.md), [IMAGE-OPTIMIZATION-PROPOSAL.md](IMAGE-OPTIMIZATION-PROPOSAL.md), and [IMAGE-OPTIMIZATION-TESTING.md](IMAGE-OPTIMIZATION-TESTING.md) for complete details.

---

### v41.28 - Purchase Event Tracking Fix (December 9, 2024)

**Critical Analytics Fix - Complete Conversion Tracking Now Operational**

**Problem Solved:** Purchase event was missing from the booking confirmation flow, preventing conversion tracking in GA4 and Facebook.

**Solution Implemented:**
- ✅ **Client-Side GA4 Tracking** - Added dataLayer.push() for GTM on confirmation page
- ✅ **Client-Side Facebook Pixel** - Added fbq() Purchase event with deduplication
- ✅ **Server-Side Facebook CAPI** - Added Purchase tracking with user data for attribution
- ✅ **No Webhook Dependency** - Works with Stripe redirect flow

**All 7 Booking Funnel Events Now Working:**
1. search - User searches for yachts (GA4 + Facebook)
2. view_item - User views yacht details (GA4 + Facebook)
3. add_to_cart - User selects week/price (GA4 + Facebook)
4. begin_checkout - User clicks "Book Now" (GA4 + Facebook)
5. add_payment_info - User submits booking form (GA4 + Facebook)
6. generate_lead - User requests quote (Facebook only)
7. **purchase** - Booking completed (GA4 + Facebook) **← FIXED**

**Impact:**
- ✅ Complete booking funnel tracked from search to purchase
- ✅ Revenue data flows to GA4 and Facebook
- ✅ ROAS (Return on Ad Spend) measurement enabled
- ✅ Server-side tracking bypasses ad blockers
- ✅ Event deduplication prevents double-counting

**Files Modified:** 2 files (booking-confirmation.php, yolo-yacht-search.php)
**Backward Compatible:** Yes
**Breaking Changes:** None

See [CHANGELOG-v41.28.md](CHANGELOG-v41.28.md) and [HANDOFF-v41.28.md](HANDOFF-v41.28.md) for complete details.

---

### v20.0 - Search Results Performance & Code Quality Improvements (December 3, 2025)

**4 Major Improvements:**

1. **N+1 Query Problem Fixed** - Optimized search results to use single query with subquery for images
2. **POST Parameter Validation** - Added proper validation and error handling for AJAX search
3. **European Price Formatting** - Changed from US format (1,234.56) to European format (1.234,56)
4. **Dead Code Removal** - Removed unused Handlebars template code from search results

**Files Modified:** 3 files
**Performance Impact:** Significantly faster search results with many yachts
**Backward Compatible:** Yes
**Breaking Changes:** None

See [CHANGELOG_v20.0.md](CHANGELOG_v20.0.md) for complete details.

---

### v17.13 - Critical Plugin Activation Fixes (December 3, 2025)

**9 Critical Issues Fixed:**

1. **Missing ABSPATH Security Checks** - Added to 30 class files (plugin activation fix)
2. **Missing PHP Opening Tag** - Fixed email class file structure
3. **Invalid CSS Selector** - Fixed yacht details location scroll
4. **Equipment Quantity Tracking** - Added ability to log quantities for equipment items
5. **Missing Email Class Include** - Fixed "Send Reminder" critical error
6. **Wrong Table Name** - Fixed public search functionality
7. **Missing Checkout CSS** - Added 350+ lines of CSS including signature pad styles
8. **Search Box Styling** - Added minimal, clean border design
9. **Red Border Removed** - Removed unwanted red border from YOLO Fleet yachts

**Files Modified:** 35 files, ~558 lines changed  
**Backward Compatible:** Yes  
**Breaking Changes:** None  
**Deployment Package:** Correct folder structure (yolo-yacht-search/)  

See [FIXES_APPLIED_v17.13.md](FIXES_APPLIED_v17.13.md) for complete details.

---

## 🚀 What's New in v17.0

**Major Feature: Complete Base Manager System - December 3, 2025**

Version 17.0 introduces a comprehensive **Base Manager System** for yacht charter operations management. This is the largest feature addition to the plugin, providing professional tools for base operations, yacht management, and guest interaction.

### Key Features

*   **Base Manager Dashboard:** Dedicated dashboard with Bootstrap 5 layout accessible via `[base_manager]` shortcode
*   **Yacht Management:** Create and manage yachts with equipment categories, logos, and owner information
*   **Digital Check-In/Check-Out:** Complete check-in and check-out processes with equipment checklists
*   **Digital Signatures:** Canvas-based signature capture for both base managers and guests
*   **PDF Generation:** Professional PDF documents with company logos and signatures (FPDF library)
*   **Guest Integration:** Guests can view, download, and sign documents from their dashboard
*   **Warehouse Management:** Track inventory by yacht with expiry dates and locations
*   **Bookings Calendar:** View all bookings in calendar format
*   **Email Notifications:** Automatic emails when documents are sent to guests

### New User Role

*   **Base Manager:** Custom WordPress role with dedicated permissions for base operations
*   Automatic redirect from wp-admin to base manager dashboard
*   Role-based access control for all features

### Database Changes

*   Database version updated to **1.6**
*   5 new tables: `wp_yolo_bm_yachts`, `wp_yolo_bm_equipment_categories`, `wp_yolo_bm_checkins`, `wp_yolo_bm_checkouts`, `wp_yolo_bm_warehouse`

### Documentation

See comprehensive documentation:
*   [CHANGELOG_v17.0.md](CHANGELOG_v17.0.md) - Detailed changelog
*   [HANDOFF_v17.0.md](HANDOFF_v17.0.md) - Technical specifications and deployment guide
*   [VERSION-HISTORY.md](VERSION-HISTORY.md) - Complete version history

---

## Previous Updates

### v16.4 - Critical Bug Fixes (December 2, 2025)

*   Fixed guest license upload for National ID/Passport documents
*   Removed guest dashboard width restriction
*   Fixed security check errors

See [CHANGELOG-v16.4.md](CHANGELOG-v16.4.md) for details.

## Previous Major Features (v3.0.0)

*   **Bootstrap 5 Integration:** The plugin uses the Bootstrap 5.3.2 grid system for a fully responsive layout.
*   **Comprehensive Security Hardening:** All AJAX endpoints and forms secured with WordPress nonces.
*   **Dynamic Text Customization:** Admin page to customize all user-facing labels and messages.
*   **Numerous Bug Fixes:** Improved stability and reliability.

## Features Overview

| Category | Feature |
| --- | --- |
| **Core Features** | Search widget, search results, "Our Fleet" display, yacht details pages, local database storage, Booking Manager API integration, live pricing, and FontAwesome equipment icons. |
| **Booking Features** | Customer information form, Stripe integration for 50% deposits, booking confirmation page, balance payment via email link, admin booking dashboard, payment reminders, CSV export, and Booking Manager sync. |
| **Guest User System** | Automatic guest account creation, custom login page, guest dashboard for viewing bookings and uploading licenses, admin license manager, and secure role-based permissions. |
| **Base Manager System** | ⭐ **NEW** Yacht management, digital check-in/check-out with signatures, PDF generation, warehouse inventory, bookings calendar, and guest document signing. |
| **Email System** | HTML email templates for booking confirmations, guest credentials, payment reminders, and payment receipts, as well as admin notifications for new bookings. |

## Quick Start Guide

1.  **Installation:**
    *   Upload the `yolo-yacht-search-v3.0.0-FINAL.zip` file to your WordPress site.
    *   Activate the plugin.
2.  **Initial Configuration:**
    *   Go to **YOLO Yacht Search → Settings** and sync the equipment catalog, yachts, and weekly offers.
    *   Configure your Booking Manager API key, company ID, and Stripe API keys.
3.  **Create Required Pages:**
    *   Create pages for search results, yacht details, "Our Fleet", booking confirmation, balance payment, balance confirmation, guest login, and guest dashboard, and add the corresponding shortcodes.
4.  **Configure Page Settings:**
    *   Go to **YOLO Yacht Search → Settings** and select the pages you created.

## Shortcodes Reference

| Shortcode | Description |
| --- | --- |
| `[yolo_search_widget]` | Displays the yacht search form. |
| `[yolo_search_results]` | Displays the search results. |
| `[yolo_our_fleet]` | Displays a grid of all your yachts. |
| `[yolo_yacht_details]` | Displays the details for a single yacht. |
| `[yolo_booking_confirmation]` | The booking confirmation page. |
| `[yolo_balance_payment]` | The balance payment page. |
| `[yolo_balance_confirmation]` | The balance confirmation page. |
| `[yolo_guest_login]` | The guest login page. |
| `[yolo_guest_dashboard]` | The guest dashboard page. |
| `[base_manager]` | ⭐ **NEW** The base manager dashboard page. |

## Database Schema

The plugin uses a number of custom database tables to store its data. The database version in v17.0 is **1.6**.

### New Tables in v17.0

*   `wp_yolo_bm_yachts` - Yacht information for base manager system
*   `wp_yolo_bm_equipment_categories` - Equipment categories and items per yacht
*   `wp_yolo_bm_checkins` - Check-in records with signatures and PDFs
*   `wp_yolo_bm_checkouts` - Check-out records with signatures and PDFs
*   `wp_yolo_bm_warehouse` - Warehouse inventory by yacht

See [HANDOFF_v17.0.md](HANDOFF_v17.0.md) for complete database schema documentation.

## API Integration

The plugin integrates with the following APIs:

*   **Booking Manager API v2:** For real-time yacht availability and pricing.
*   **Stripe API:** For secure online payments.

## Troubleshooting

If you encounter any issues with the plugin, please refer to the `TROUBLESHOOTING.md` file for detailed troubleshooting steps.

## Credits

**Developed for:** YOLO Charters
**Version:** 20.0
**Database Version:** 1.6
**Status:** Ready for Production Testing ✅
**Last Updated:** December 3, 2025 at 20:59 UTC

## Base Manager System Setup

### Quick Setup Guide

1.  **Create Base Manager Page:**
    *   Create a new WordPress page
    *   Add the `[base_manager]` shortcode
    *   Publish the page

2.  **Assign Base Manager Role:**
    *   Go to WordPress Users
    *   Edit a user or create a new one
    *   Change role to "Base Manager"
    *   Save changes

3.  **Login as Base Manager:**
    *   Base managers are automatically redirected from wp-admin to their dashboard
    *   Access the base manager dashboard page directly

4.  **Start Using:**
    *   Create yachts with equipment categories
    *   Perform check-ins and check-outs
    *   Manage warehouse inventory
    *   View bookings calendar

For detailed setup and usage instructions, see [HANDOFF_v17.0.md](HANDOFF_v17.0.md).
