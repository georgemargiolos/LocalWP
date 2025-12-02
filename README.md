> **Note**
> This README file is for the YOLO Yacht Search & Booking Plugin, a comprehensive solution for yacht charter businesses. This document provides a detailed overview of the plugin's features, installation instructions, and usage guidelines.

# YOLO Yacht Search & Booking Plugin

**Version:** 17.0
**WordPress Version:** 5.8 or higher
**PHP Version:** 7.4 or higher
**License:** Proprietary
**Status:** Ready for Production Testing ✅

## Overview

The YOLO Yacht Search & Booking Plugin is a complete system for yacht charter businesses, providing a seamless experience for both customers and administrators. It integrates with the Booking Manager API for real-time yacht availability and pricing, and with Stripe for secure online payments. The plugin is designed to be highly customizable, allowing you to tailor it to your specific needs.

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
**Version:** 17.0
**Database Version:** 1.6
**Status:** Ready for Production Testing ✅
**Last Updated:** December 3, 2025

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
