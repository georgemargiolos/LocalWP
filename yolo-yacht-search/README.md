# YOLO Yacht Search & Booking Plugin

**Version:** 2.5.9  
**WordPress Version:** 5.0 or higher  
**PHP Version:** 7.4 or higher  
**License:** Proprietary  
**Status:** Production Ready ✅

## Overview

Complete yacht charter search, booking, and management system for YOLO Charters. Integrates with Booking Manager API and Stripe for payments.

## 🎉 What's New in v2.5.9

### Custom Guest Login System
✅ **Custom Frontend Login** - `[yolo_guest_login]` shortcode for branded login page  
✅ **Auto Guest Creation** - WordPress users created automatically after booking  
✅ **Credentials Email** - Login details sent to customers  
✅ **Guest Dashboard** - Customers can view bookings and upload licenses  
✅ **No wp-admin Access** - Guests use custom pages only

### Bug Fixes
✅ **Race Condition Fixed** - Booking confirmation loads properly  
✅ **Contact Info Updated** - Correct email and phone throughout  
✅ **Booking Reference** - Shows Booking Manager ID instead of database ID  
✅ **Customer Details Fallback** - Better handling of Stripe customer data

---

## Features Overview

### Core Features
✅ **Search Widget** - Yacht search form with boat type and dates  
✅ **Search Results** - Display results with YOLO boats prioritized  
✅ **Our Fleet** - Beautiful grid display of all yachts  
✅ **Yacht Details** - Individual yacht pages with image carousel  
✅ **Database Storage** - All yacht data stored locally  
✅ **Booking Manager Integration** - Real-time sync with API  
✅ **Live Pricing** - Dynamic price updates based on dates  
✅ **Equipment Icons** - FontAwesome icons for all equipment

### Booking Features
✅ **Customer Information Form** - Collect data before payment  
✅ **Stripe Payment Integration** - Secure 50% deposit payment  
✅ **Booking Confirmation** - Professional confirmation page  
✅ **Balance Payment** - Pay remaining 50% via email link  
✅ **Admin Booking Dashboard** - View all bookings with statistics  
✅ **Payment Reminders** - Send HTML email reminders  
✅ **CSV Export** - Export bookings for accounting  
✅ **Booking Manager Sync** - Auto-create reservations

### Guest User System (v2.5.6+)
✅ **Automatic Guest Accounts** - Created after successful booking  
✅ **Custom Login Page** - Branded frontend login (no wp-admin)  
✅ **Guest Dashboard** - View bookings and upload licenses  
✅ **License Upload** - Front and back sailing license images  
✅ **Admin License Manager** - View and download all uploads  
✅ **Secure Access** - Role-based permissions, isolated data

### Email System
✅ **HTML Email Templates** - Professional responsive design  
✅ **Booking Confirmation** - Sent after deposit payment  
✅ **Guest Credentials** - Login details for new guests  
✅ **Payment Reminder** - Manual send from admin  
✅ **Payment Received** - Sent after balance payment  
✅ **Admin Notifications** - Alert on new bookings

---

## Quick Start Guide

### 1. Installation

1. Upload `yolo-yacht-search-v2.5.9-COMPLETE.zip`
2. Go to WordPress → Plugins → Add New → Upload Plugin
3. Activate the plugin
4. Database tables created automatically

### 2. Initial Configuration

**Sync Data (3 Steps):**
1. Go to **YOLO Yacht Search → Settings**
2. Click **"Sync Equipment Catalog"** (green button)
3. Click **"Sync Yachts"** (red button)
4. Click **"Sync Weekly Offers"** (blue button)

**Configure API Settings:**
- Booking Manager API Key
- Company ID: 7850
- Stripe Publishable Key
- Stripe Secret Key
- Deposit Percentage: 50%

### 3. Create Required Pages

| Page Title | Shortcode | Slug (Important) |
|------------|-----------|------------------|
| Search Results | `[yolo_search_results]` | any |
| Yacht Details | `[yolo_yacht_details]` | any |
| Our Fleet | `[yolo_our_fleet]` | any |
| Booking Confirmation | `[yolo_booking_confirmation]` | any |
| Balance Payment | `[yolo_balance_payment]` | any |
| Balance Confirmation | `[yolo_balance_confirmation]` | any |
| **Guest Login** | `[yolo_guest_login]` | `guest-login` |
| **Guest Dashboard** | `[yolo_guest_dashboard]` | `guest-dashboard` |

**Note:** Guest pages must use exact slugs shown above!

### 4. Configure Page Settings

1. Go to **YOLO Yacht Search → Settings**
2. Select pages from dropdowns
3. Save settings

**Done!** Your booking system is ready! 🎉

---

## Shortcodes Reference

### Search & Fleet

**`[yolo_search_widget]`**
- Yacht search form with boat type and date picker
- Place on homepage or search page

**`[yolo_search_results]`**
- Display search results (YOLO boats first)
- Requires dedicated page

**`[yolo_our_fleet]`**
- Grid display of all yachts
- Responsive layout

**`[yolo_yacht_details]`**
- Single yacht page with carousel
- Live price updates

### Booking Pages

**`[yolo_booking_confirmation]`**
- Shown after deposit payment
- Booking summary and next steps

**`[yolo_balance_payment]`**
- Pay remaining 50% balance
- Accessed via email link

**`[yolo_balance_confirmation]`**
- Shown after balance payment
- Fully paid confirmation

### Guest System (NEW)

**`[yolo_guest_login]`** ⭐ v2.5.9
- Custom frontend login form
- No wp-admin access
- Auto-redirect to dashboard
- Must use slug: `guest-login`

**`[yolo_guest_dashboard]`** ⭐ v2.5.6
- View bookings
- Upload sailing license (front + back)
- View uploaded licenses
- Must use slug: `guest-dashboard`

---

## Guest System Setup

### How It Works

1. **Customer Books Yacht**
   - Completes booking form
   - Pays 50% deposit via Stripe
   - Redirected to confirmation page

2. **Guest Account Created**
   - WordPress user created automatically
   - Role: 'guest'
   - Username: customer email
   - Password: `[booking_id]YoLo` (e.g., `5YoLo`)

3. **Emails Sent**
   - Booking confirmation email
   - Guest credentials email (separate)

4. **Guest Login**
   - Visits `/guest-login` page
   - Enters email and password
   - Redirected to `/guest-dashboard`

5. **Guest Dashboard**
   - Views booking details
   - Uploads sailing license
   - Downloads booking info

### Admin Management

**View Guest Users:**
- Go to **Users**
- Filter by role: "Guest"

**View License Uploads:**
- Go to **YOLO Yacht Search → Guest Licenses**
- See all uploaded licenses
- Download images

**Manage Bookings:**
- Go to **YOLO Yacht Search → Bookings**
- View bookings with linked users
- Send payment reminders

---

## Booking Flow

### Customer Journey

1. **Search** → Use search widget
2. **Browse Results** → YOLO boats first
3. **View Details** → Click yacht
4. **Select Dates** → Saturday to Saturday
5. **Click "BOOK NOW"** → Booking form appears
6. **Fill Information** → Name, email, phone
7. **Pay Deposit** → Stripe checkout (50%)
8. **Confirmation** → Booking confirmed
9. **Receive Emails:**
   - Booking confirmation
   - Guest login credentials
10. **Login** → Visit `/guest-login`
11. **Upload License** → Sailing license images
12. **Pay Balance** → Click link in email (50%)
13. **Final Confirmation** → Fully paid!

### Admin Workflow

1. **View Dashboard** → YOLO Yacht Search → Bookings
2. **See Statistics:**
   - Total bookings
   - Pending balance
   - Fully paid
   - Total revenue
3. **Manage Bookings:**
   - View details
   - Send reminders
   - Mark as paid
   - Contact customers
4. **View Licenses** → Guest Licenses page
5. **Export Data** → CSV download

---

## Database Schema

### Tables

**`wp_yolo_bookings`** (v1.5)
- Booking information
- Customer data
- Payment details
- **user_id** (links to WordPress user)
- Stripe session IDs
- Booking Manager IDs

**`wp_yolo_license_uploads`** (NEW)
- Guest user ID
- Booking ID
- License type (front/back)
- File path
- Upload timestamp

**`wp_yolo_yachts`**
- Yacht specifications
- Raw API data

**`wp_yolo_yacht_images`**
- Image URLs and order

**`wp_yolo_yacht_equipment`**
- Equipment per yacht

**`wp_yolo_yacht_extras`**
- Available extras with pricing

**`wp_yolo_yacht_prices`**
- Weekly offers and availability

**`wp_yolo_equipment_catalog`**
- Equipment master list

---

## API Integration

### Booking Manager API v2

**Base URL:** `https://api.booking-manager.com/v2/`

**Endpoints:**
- `GET /equipment` - Equipment catalog
- `GET /yachts` - Yacht data
- `GET /offers` - Weekly pricing
- `POST /reservation` - Create booking
- `POST /reservation/{id}/payments` - Record payment

### Stripe API

**Features:**
- Checkout sessions
- Payment intents
- Customer creation
- Metadata storage
- Webhook support (optional)

---

## Settings

### API Settings
- Booking Manager API Key
- Company ID: 7850
- Friend Companies: 4366, 3604, 6711

### Stripe Settings
- Publishable Key
- Secret Key
- Deposit Percentage: 50%

### Page Settings
- Search Results Page
- Yacht Details Page
- Booking Confirmation Page
- Balance Payment Page
- Balance Confirmation Page

### Email Settings
- From Email
- Email Logo URL (optional)

### General Settings
- Cache Duration: 24 hours
- Currency: EUR
- FontAwesome CDN: Enabled

---

## Troubleshooting

### Guest User Not Created
- Check **Users** page for guest role
- Verify booking has `user_id` in database
- Check email was sent
- Look for PHP errors

### Login Issues
- Verify password: `[booking_id]YoLo`
- Check page slug is `guest-login`
- Clear browser cache
- Verify user role is 'guest'

### Dashboard Not Showing
- Check page slug is `guest-dashboard`
- Verify user is logged in
- Check `user_id` in bookings table
- Look for JavaScript errors

### License Upload Fails
- Check file size (max 5MB)
- Verify file type (JPG, PNG, PDF)
- Check upload directory permissions
- Look for PHP errors

### Sync Failures
- Verify API key is correct
- Sync equipment catalog FIRST
- Check internet connection
- Increase PHP memory limit

---

## File Structure

```
yolo-yacht-search/
├── admin/
│   ├── class-yolo-ys-admin.php
│   ├── class-yolo-ys-admin-guest-licenses.php (NEW)
│   ├── css/
│   │   └── yolo-ys-admin-guest-licenses.css (NEW)
│   └── partials/
├── includes/
│   ├── class-yolo-ys-yacht-search.php
│   ├── class-yolo-ys-database.php
│   ├── class-yolo-ys-sync.php
│   ├── class-yolo-ys-stripe.php
│   ├── class-yolo-ys-stripe-handlers.php
│   ├── class-yolo-ys-guest-users.php (NEW)
│   └── class-yolo-ys-booking-manager.php
├── public/
│   ├── class-yolo-ys-public.php
│   ├── css/
│   │   ├── guest-dashboard.css (NEW)
│   │   └── guest-login.css (NEW)
│   ├── js/
│   ├── emails/
│   │   └── guest-credentials.php (NEW)
│   ├── partials/
│   │   ├── yolo-ys-guest-login.php (NEW)
│   │   └── yolo-ys-guest-dashboard.php (NEW)
│   └── templates/
├── stripe-php/
├── README.md
├── CHANGELOG.md
├── VERSION-HISTORY.md
├── GUEST-SYSTEM-README.md
└── yolo-yacht-search.php
```

---

## Security

- Nonce verification on all forms
- Role-based access control
- Guests cannot access wp-admin
- Secure file uploads (5MB limit)
- Sanitized inputs
- Escaped outputs
- Password hashing
- Stripe webhook verification (optional)

---

## Support & Documentation

### Documentation Files
- `README.md` - This file
- `CHANGELOG.md` - Version changelog
- `VERSION-HISTORY.md` - Complete version history
- `GUEST-SYSTEM-README.md` - Guest system guide
- `GUEST-SYSTEM-SETUP-v2.5.9.md` - Setup instructions

### Contact
- **Email:** info@yolo-charters.com
- **Phone:** +30 698 506 4875
- **GitHub:** https://github.com/georgemargiolos/LocalWP

---

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- Booking Manager API key
- Stripe account
- 256MB PHP memory (recommended)

---

## Credits

**Developed for:** YOLO Charters  
**Version:** 2.5.9  
**Database Version:** 1.5  
**Status:** Production Ready ✅  
**Last Updated:** December 1, 2025

---

**Ready to launch your yacht charter booking system with guest management! 🚀⛵**
