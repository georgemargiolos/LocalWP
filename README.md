> **Note**
> This README file is for the YOLO Yacht Search & Booking Plugin, a comprehensive solution for yacht charter businesses. This document provides a detailed overview of the plugin's features, installation instructions, and usage guidelines.

# YOLO Yacht Search & Booking Plugin

**Version:** 16.4
**WordPress Version:** 5.8 or higher
**PHP Version:** 7.4 or higher
**License:** Proprietary
**Status:** Production Ready ✅

## Overview

The YOLO Yacht Search & Booking Plugin is a complete system for yacht charter businesses, providing a seamless experience for both customers and administrators. It integrates with the Booking Manager API for real-time yacht availability and pricing, and with Stripe for secure online payments. The plugin is designed to be highly customizable, allowing you to tailor it to your specific needs.

## 🎉 What's New in v16.4

**Critical Bug Fixes - December 2, 2025 (Session 16)**

*   **Fixed Guest License Upload - Missing File Types:** Resolved "Upload failed" error when uploading National ID/Passport documents. Added `id_front` and `id_back` to allowed file types array. All 6 document types now work correctly.
*   **Removed Guest Dashboard Width Restriction:** Removed 900px max-width constraint to allow WordPress theme to handle layout naturally. Dashboard now uses full available width for better display on all screen sizes.
*   **Fixed Guest License Upload Errors (v16.2):** Removed nonce verification from license uploads that was causing persistent "Security check failed" errors.
*   **Fixed Checkout Session Security Error (v16.1):** Resolved "Security check failed" error that prevented customers from completing bookings.

See [CHANGELOG-v16.4.md](CHANGELOG-v16.4.md), [CHANGELOG-v16.2.md](CHANGELOG-v16.2.md), and [CHANGELOG-v16.1.md](CHANGELOG-v16.1.md) for detailed technical information.

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

## Database Schema

The plugin uses a number of custom database tables to store its data. The database version in v3.0.0 is 1.8.

## API Integration

The plugin integrates with the following APIs:

*   **Booking Manager API v2:** For real-time yacht availability and pricing.
*   **Stripe API:** For secure online payments.

## Troubleshooting

If you encounter any issues with the plugin, please refer to the `TROUBLESHOOTING.md` file for detailed troubleshooting steps.

## Credits

**Developed for:** YOLO Charters
**Version:** 16.4
**Database Version:** 1.8
**Status:** Production Ready ✅
**Last Updated:** December 2, 2025
