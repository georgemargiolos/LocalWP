# YOLO Yacht Search & Booking Plugin

**Complete yacht charter booking system** with Booking Manager API v2 integration, Stripe payments, customer management, and automated email notifications.

## Current Version: v2.3.0 ✅

**Status**: Production Ready - All Features Complete!

---

## 🎉 What's New in v2.3.0

### Complete Booking System
✅ **Customer Booking Form** - Collect customer data before payment  
✅ **Admin Booking Management** - Complete dashboard with statistics  
✅ **Balance Payment System** - Customers can pay remaining 50% online  
✅ **HTML Email Templates** - Professional branded emails  
✅ **CSS Optimization** - Conditional loading for better performance

---

## Features Overview

### Core Features (v1.x)
✅ **Search Widget** - Yacht search form styled like yolo-charters.com  
✅ **Search Results** - Display results with YOLO boats prioritized  
✅ **Our Fleet** - Beautiful grid display of all yachts  
✅ **Yacht Details** - Individual yacht pages with image carousel  
✅ **Database Storage** - All yacht data stored in WordPress database  
✅ **Booking Manager Integration** - Real-time sync with API  
✅ **Live Pricing** - Dynamic price updates based on dates  
✅ **Equipment Icons** - FontAwesome 6.4.0 icons for all equipment

### Booking Features (v2.x)
✅ **Customer Information Form** - Collect name, email, phone before payment  
✅ **Stripe Payment Integration** - Secure 50% deposit payment  
✅ **Booking Confirmation** - Professional confirmation page  
✅ **Balance Payment** - Customers pay remaining 50% via email link  
✅ **Admin Booking Dashboard** - View all bookings with statistics  
✅ **Payment Reminders** - Send HTML email reminders  
✅ **CSV Export** - Export bookings for accounting  
✅ **Booking Manager Sync** - Auto-create reservations in BM

### Email System (v2.2.4)
✅ **HTML Email Templates** - Professional responsive design  
✅ **Booking Confirmation Email** - Sent after deposit payment  
✅ **Payment Reminder Email** - Manual send from admin  
✅ **Payment Received Email** - Sent after balance payment  
✅ **Admin Notifications** - Alert on new bookings

---

## Quick Start Guide

### 1. Installation

**Upload Plugin:**
1. Download `yolo-yacht-search.zip`
2. Go to WordPress → Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Click "Activate Plugin"

**Database Setup:**
- Database tables are created automatically
- Current database version: 1.4
- Auto-migrates on activation

### 2. Initial Configuration

**Sync Data (3 Buttons):**
1. Go to **YOLO Yacht Search** in WordPress admin
2. Click **"Sync Equipment Catalog"** (green) - syncs ~50 equipment items
3. Click **"Sync Yachts"** (red) - syncs all yacht data
4. Click **"Sync Weekly Offers"** (blue) - syncs pricing for the year

**Configure API Settings:**
- Booking Manager API Key
- Company ID: 7850 (YOLO)
- Stripe Publishable Key
- Stripe Secret Key

### 3. Create Pages

Create WordPress pages with these shortcodes:

| Page | Shortcode | Purpose |
|------|-----------|---------|
| Search Results | `[yolo_search_results]` | Display search results |
| Yacht Details | `[yolo_yacht_details]` | Single yacht page |
| Our Fleet | `[yolo_our_fleet]` | All yachts grid |
| Booking Confirmation | `[yolo_booking_confirmation]` | After deposit payment |
| Balance Payment | `[yolo_balance_payment]` | Pay remaining balance |
| Balance Confirmation | `[yolo_balance_confirmation]` | After balance payment |

**Add Search Widget:**
- Edit your homepage
- Add shortcode: `[yolo_search_widget]`
- Publish

### 4. Configure Page Settings

1. Go to **YOLO Yacht Search → Settings**
2. Select pages from dropdowns:
   - Search Results Page
   - Yacht Details Page
   - Booking Confirmation Page
   - Balance Payment Page
   - Balance Confirmation Page
3. Save settings

**Done!** Your booking system is ready! 🎉

---

## Booking Flow

### Customer Journey

1. **Search** → Customer uses search widget on homepage
2. **Browse Results** → YOLO boats appear first (red badges)
3. **View Details** → Click yacht to see full information
4. **Select Dates** → Use date picker (Saturday to Saturday)
5. **Click "BOOK NOW"** → Booking form modal appears
6. **Fill Information** → Name, email, phone number
7. **Review Summary** → Yacht, dates, total price
8. **Pay Deposit** → Redirected to Stripe (50% payment)
9. **Confirmation** → Returns to confirmation page
10. **Receive Email** → HTML email with booking details
11. **Pay Balance** → Click link in email to pay remaining 50%
12. **Final Confirmation** → Fully paid status, ready to sail!

### Admin Workflow

1. **View Dashboard** → YOLO Yacht Search → Bookings
2. **See Statistics:**
   - Total bookings
   - Pending balance count
   - Fully paid count
   - Total revenue
3. **Manage Bookings:**
   - Sort by any column
   - Filter by status, yacht, date
   - Search by customer name/email
4. **Take Actions:**
   - View booking details
   - Send payment reminder
   - Mark as fully paid
   - Email customer
   - Call customer
5. **Export Data** → Download CSV for accounting

---

## Shortcodes Reference

### `[yolo_search_widget]`
**Purpose:** Display yacht search form  
**Where:** Homepage or any search page  
**Features:**
- Boat type dropdown
- Date range picker (Litepicker)
- Search button
- Styled like yolo-charters.com

### `[yolo_search_results]`
**Purpose:** Display search results  
**Where:** Dedicated search results page  
**Features:**
- YOLO boats first (red badges)
- Partner boats second
- Yacht cards with specs
- "View Details" buttons

### `[yolo_our_fleet]`
**Purpose:** Display all yachts  
**Where:** Fleet browsing page  
**Features:**
- Grid layout (responsive)
- Yacht images
- Specs (year, cabins, berths, length)
- Descriptions
- "View Details" buttons

### `[yolo_yacht_details]`
**Purpose:** Single yacht page  
**Where:** Dedicated yacht details page  
**Features:**
- Image carousel (auto-advance)
- Complete specifications
- Equipment list with icons
- Available extras with pricing
- Date picker
- Live price updates
- "BOOK NOW" button

### `[yolo_booking_confirmation]`
**Purpose:** Booking confirmation after deposit  
**Where:** Dedicated confirmation page  
**Features:**
- Success message
- Booking reference
- Booking summary
- Payment details
- Next steps

### `[yolo_balance_payment]`
**Purpose:** Pay remaining balance  
**Where:** Dedicated balance payment page  
**Features:**
- Booking summary
- Payment breakdown
- "Pay Now" button
- Secure Stripe checkout

### `[yolo_balance_confirmation]`
**Purpose:** Confirmation after balance payment  
**Where:** Dedicated balance confirmation page  
**Features:**
- Success message
- Fully paid status
- What's next information

---

## Admin Features

### Bookings Dashboard

**Access:** YOLO Yacht Search → Bookings

**Statistics:**
- Total Bookings
- Pending Balance (deposit paid, balance due)
- Fully Paid (100% paid)
- Total Revenue

**Bookings Table:**
- Sortable columns
- Status badges (color-coded)
- Customer information
- Yacht and dates
- Payment details
- Booking Manager sync status
- Quick actions

**Filters:**
- Payment status (all, deposit_paid, fully_paid)
- Yacht (dropdown)
- Date range
- Search (customer name/email)

**Actions:**
- View booking details
- Send payment reminder
- Mark as fully paid
- Email customer (mailto link)
- Call customer (tel link)
- Export to CSV

### Booking Detail Page

**Information Displayed:**
- Booking reference
- Customer information (name, email, phone)
- Yacht details
- Charter dates
- Payment breakdown
- Booking status
- Payment status
- Stripe session ID
- Booking Manager reservation ID
- Created/updated timestamps

**Available Actions:**
- Send Payment Reminder (HTML email)
- Mark as Fully Paid (updates status + sends email)
- Email Customer
- Call Customer

---

## Email Templates

### Booking Confirmation Email
**Sent:** After deposit payment  
**Includes:**
- Booking summary
- Payment breakdown
- "Pay Remaining Balance" button
- What's next information

### Payment Reminder Email
**Sent:** Manually from admin  
**Includes:**
- Booking summary
- Payment information (highlighted)
- "Pay Now" button
- Days until charter

### Payment Received Email
**Sent:** After balance payment  
**Includes:**
- Success message
- Fully paid confirmation
- What happens next
- Charter preparation tips

### Admin Notification Email
**Sent:** On new booking  
**Includes:**
- Customer information
- Booking details
- Link to admin booking page

**Email Features:**
- Responsive HTML design
- Mobile-friendly
- YOLO Charters branding
- Payment action buttons
- Professional styling

---

## Database Schema

### Tables (v1.4)

**`wp_yolo_bookings`**
- Booking information
- Customer data (name, email, phone)
- Payment details
- Stripe session IDs
- Booking Manager reservation IDs
- Status tracking

**`wp_yolo_yachts`**
- Yacht specifications
- Raw API data

**`wp_yolo_yacht_images`**
- Image URLs
- Sort order

**`wp_yolo_yacht_products`**
- Charter types
- Base prices

**`wp_yolo_yacht_equipment`**
- Equipment IDs per yacht
- Categories

**`wp_yolo_yacht_extras`**
- Available extras
- Pricing

**`wp_yolo_yacht_prices`**
- Weekly pricing
- Availability

**`wp_yolo_equipment_catalog`**
- Equipment master list
- ID to name mapping

---

## API Integration

### Booking Manager API v2

**Base URL:** `https://api.booking-manager.com/v2/`

**Endpoints Used:**
- `GET /equipment` - Equipment catalog
- `GET /yachts?companyId={id}` - Yacht data
- `GET /offers` - Weekly pricing
- `POST /reservation` - Create reservation
- `POST /reservation/{id}/payments` - Record payment

### Stripe Integration

**Features:**
- Stripe Checkout Session
- Metadata storage
- Customer email pre-fill
- Multi-currency support
- Test mode available

**Payment Flow:**
1. Customer fills booking form
2. Create Checkout Session with metadata
3. Redirect to Stripe
4. Customer completes payment
5. Return to confirmation page
6. Retrieve session and create booking
7. Send confirmation email

---

## Settings

### API Settings
- **Booking Manager API Key** - Your API key
- **Company ID** - 7850 (YOLO)
- **Friend Companies** - 4366, 3604, 6711

### Stripe Settings
- **Publishable Key** - Stripe public key
- **Secret Key** - Stripe secret key
- **Webhook Secret** - For webhook verification
- **Deposit Percentage** - Default: 50%

### Page Settings
- **Search Results Page** - Select page with `[yolo_search_results]`
- **Yacht Details Page** - Select page with `[yolo_yacht_details]`
- **Booking Confirmation Page** - Select page with `[yolo_booking_confirmation]`
- **Balance Payment Page** - Select page with `[yolo_balance_payment]`
- **Balance Confirmation Page** - Select page with `[yolo_balance_confirmation]`

### Email Settings
- **Email Logo URL** - Logo for email header (optional)
- **From Email** - Sender email address

### General Settings
- **Cache Duration** - 1-168 hours (default: 24)
- **Currency** - EUR, USD, GBP
- **FontAwesome CDN** - Enable/disable (default: enabled)

### Styling Settings
- **Primary Color** - #1e3a8a (blue)
- **Button Background** - #dc2626 (red)
- **Button Text** - #ffffff (white)

---

## Troubleshooting

### Plugin Installation

**Error: "No valid plugins were found"**
- Ensure ZIP file has `yolo-yacht-search/` folder at root
- Do NOT have parent `LocalWP/` folder in ZIP
- Download correct ZIP from GitHub or attachments

### Booking Form

**Modal doesn't appear**
- Check JavaScript console for errors
- Ensure jQuery is loaded
- Clear browser cache

**Form validation fails**
- All fields are required
- Email must be valid format
- Phone accepts international format

### Payments

**Stripe checkout fails**
- Verify Stripe keys are correct
- Check Stripe account is active
- Use test card: 4242 4242 4242 4242

**Balance payment link doesn't work**
- Ensure Balance Payment page exists
- Check shortcode is correct
- Verify booking reference format

### Admin Dashboard

**Bookings page doesn't load**
- Check for PHP errors
- Verify database tables exist
- Clear WordPress cache

**Statistics not showing**
- Ensure bookings exist in database
- Check table name prefix

### Emails

**Emails not sending**
- Configure SMTP plugin (WP Mail SMTP)
- Check spam folder
- Verify sender email address

**HTML not rendering**
- Some email clients block HTML
- Test in Gmail, Outlook
- Check mobile devices

---

## Version History

### v2.3.0 (November 30, 2025) - Final Release
✅ All features complete and production-ready

### v2.2.4 - HTML Email Templates
- Professional branded HTML emails
- Responsive design
- Payment action buttons

### v2.2.3 - Remaining Balance Payment
- Balance payment page
- Secure payment links
- Automatic status updates

### v2.2.2 - Admin Booking Management
- Complete admin dashboard
- Statistics and filters
- CSV export

### v2.2.1 - CSS Refactoring
- Conditional CSS loading
- Performance optimization

### v2.2.0 - Customer Booking Form
- Collect customer data before payment
- Booking form modal
- Database schema v1.4

### v2.1.0 - Booking Manager Integration
- Live price updates
- Saturday validation
- Reservation creation

### v1.9.4 - Bug Fixes
- Yacht sync improvements
- Equipment catalog fixes

---

## Support & Documentation

### Documentation Files
- `README.md` - This file
- `COMPLETE-FEATURES-v2.3.0.md` - Comprehensive feature documentation
- `BOOKING-FORM-IMPLEMENTATION.md` - Technical implementation details
- `SESSION-SUMMARY-v2.2.0.md` - Development session summary

### GitHub Repository
**URL:** https://github.com/georgemargiolos/LocalWP

### Requirements
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.6+
- Booking Manager API key
- Stripe account

---

## Credits

**Developed for:** YOLO Charters  
**Version:** 2.3.0  
**Database Version:** 1.4  
**Status:** Production Ready ✅

---

**Ready to launch your yacht charter booking system! 🚀⛵**
