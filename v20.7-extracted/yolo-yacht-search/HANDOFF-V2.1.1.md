# YOLO Yacht Search Plugin - Development Handoff Document v2.1.1

**Generated:** November 30, 2025 - GMT+2  
**Plugin Version:** 2.1.1  
**Status:** In Progress - Admin Features & Payment System

---

## Executive Summary

The YOLO Yacht Search WordPress plugin is a yacht charter search and booking system with Stripe payment integration and Booking Manager API synchronization. Version 2.1.0 successfully implemented the core booking flow with deposit payments. Version 2.1.1 is currently in progress, focusing on admin booking management, CSS refactoring, remaining balance payments, and HTML email templates.

---

## Current Status

### ✅ Completed Features (v2.1.0)

1. **Stripe Checkout Integration**
   - Full Stripe Checkout Session implementation
   - Configurable deposit percentage (default 50%)
   - No webhook required - bookings created on return from Stripe
   - Test keys prefilled in admin settings

2. **Booking Manager API Integration**
   - POST /reservation endpoint integration
   - POST /reservation/{id}/payments endpoint integration
   - Reservation created after successful payment
   - Booking reference uses Booking Manager reservation ID

3. **Frontend Features**
   - Live price checking when dates change
   - Saturday-to-Saturday validation with quote request fallback
   - European price formatting (18.681,00 EUR)
   - Litepicker date picker integration
   - FontAwesome CDN toggle in admin settings

4. **Database Schema**
   - 8 custom tables including wp_yolo_bookings
   - Complete booking data storage
   - Stripe session ID and Booking Manager reservation ID tracking

### 🔄 In Progress (v2.1.1)

1. **Task 1: Booking Manager ID as Reference** ✅ COMPLETE
   - Changed booking reference from WordPress ID to Booking Manager reservation ID
   - Updated booking confirmation template
   - Updated email notifications

2. **Task 2: CSS Refactoring** 🔄 PARTIALLY DONE
   - Extracted inline CSS from 3 templates:
     - `public/css/booking-confirmation.css` (created)
     - `public/css/our-fleet.css` (created)
     - `public/css/search-results.css` (created)
   - **PENDING:** Remove inline `<style>` tags from templates
   - **PENDING:** Enqueue CSS files properly in WordPress
   - **PENDING:** Extract CSS from remaining templates

3. **Task 3: Admin Booking Management** 🔄 STARTED
   - Created `admin/class-yolo-ys-admin-bookings.php` (WP_List_Table class)
   - **PENDING:** Create admin template files
   - **PENDING:** Create booking detail page
   - **PENDING:** Add admin menu integration
   - **PENDING:** Create admin CSS file
   - **PENDING:** Implement actions (view, email, mark paid, cancel)
   - **PENDING:** Add export to CSV functionality
   - **PENDING:** Create dashboard widget

4. **Task 4: Remaining Balance Payment** ⏳ PENDING
   - Not started yet

5. **Task 5: HTML Email Templates** ⏳ PENDING
   - Not started yet

---

## Technical Architecture

### Tech Stack
- **WordPress:** Plugin architecture (PHP 7.4+)
- **Payment Gateway:** Stripe PHP SDK v13.16.0 (690 files)
- **External API:** Booking Manager REST API v2
- **Database:** MySQL with 8 custom tables
- **Frontend:** JavaScript (Litepicker, Stripe.js), FontAwesome icons

### Payment Flow
```
User selects dates → Clicks "BOOK NOW" → Stripe Checkout → Payment Success
→ Returns to confirmation page → Creates BM reservation → Records payment → Sends email
```

### API Integrations

**1. Stripe Checkout**
- Hosted payment page (no PCI compliance needed)
- Session-based checkout
- No webhook required (bookings created on return)

**2. Booking Manager API**
- **GET /equipment, /yachts, /offers:** Sync operations
- **POST /reservation:** Create booking after payment
- **POST /reservation/{id}/payments:** Record deposit payment

### Database Tables

```
wp_yolo_bookings          - Customer bookings with Stripe and BM IDs
wp_yolo_yachts            - Yacht inventory
wp_yolo_equipment         - Equipment catalog
wp_yolo_offers            - Special offers
wp_yolo_yacht_equipment   - Yacht-equipment relationships
wp_yolo_yacht_offers      - Yacht-offer relationships
wp_yolo_sync_log          - API synchronization log
wp_yolo_settings          - Plugin settings
```

### Key Files

**Core Plugin:**
- `yolo-yacht-search.php` - Main plugin file, version 2.1.1

**Stripe Integration:**
- `includes/class-yolo-ys-stripe.php` - Stripe Checkout Session creation
- `includes/class-yolo-ys-stripe-handlers.php` - AJAX handlers for Stripe

**Booking Manager Integration:**
- `includes/class-yolo-ys-booking-manager-api.php` - API integration

**Frontend:**
- `public/templates/booking-confirmation.php` - Booking confirmation page
- `public/templates/partials/yacht-details-v3-scripts.php` - Frontend JS
- `public/css/booking-confirmation.css` - Extracted CSS (NEW)
- `public/css/our-fleet.css` - Extracted CSS (NEW)
- `public/css/search-results.css` - Extracted CSS (NEW)

**Admin:**
- `admin/class-yolo-ys-admin.php` - Admin settings panel
- `admin/class-yolo-ys-admin-bookings.php` - Bookings list table (NEW)

**Database:**
- `includes/class-yolo-ys-database.php` - Database schema

---

## Next Session Tasks

### Priority 1: Complete Admin Booking Management (Task 3)

**1.1 Create Admin Template Files**

Create `admin/partials/bookings-list.php`:
```php
<div class="wrap">
    <h1 class="wp-heading-inline">Bookings</h1>
    <hr class="wp-header-end">
    
    <form method="get">
        <input type="hidden" name="page" value="yolo-ys-bookings">
        <?php
        $bookings_table = new YOLO_YS_Admin_Bookings();
        $bookings_table->prepare_items();
        $bookings_table->search_box('Search Bookings', 'booking');
        $bookings_table->display();
        ?>
    </form>
</div>
```

Create `admin/partials/booking-detail.php`:
- Display full booking information
- Show customer details
- Show yacht details
- Show payment information
- Show charter dates
- Action buttons (send email, mark paid, cancel)

**1.2 Create Admin CSS File**

Create `admin/css/admin-bookings.css`:
- Styles for booking list table
- Status badge styles
- Action button styles
- Booking detail page styles

**1.3 Add Admin Menu Integration**

In `admin/class-yolo-ys-admin.php`, add:
```php
add_submenu_page(
    'yolo-ys-settings',
    'Bookings',
    'Bookings',
    'manage_options',
    'yolo-ys-bookings',
    array($this, 'display_bookings_page')
);
```

**1.4 Implement Actions**

- **View:** Display booking detail page
- **Send Email:** Resend booking confirmation
- **Send Reminder:** Send payment reminder for remaining balance
- **Mark Paid:** Update payment status to fully_paid
- **Cancel:** Cancel booking and update Booking Manager

**1.5 Export to CSV**

Create export functionality:
- Export all bookings or filtered bookings
- Include all relevant fields
- Proper CSV formatting with European locale

**1.6 Dashboard Widget**

Create dashboard widget showing:
- Total bookings this month
- Pending payments
- Recent bookings
- Revenue summary

### Priority 2: Complete CSS Refactoring (Task 2)

**2.1 Enqueue CSS Files**

In `public/class-yolo-ys-public.php`, add enqueue function:
```php
public function enqueue_styles() {
    // Existing styles...
    
    // Enqueue template-specific CSS
    if (is_page()) {
        global $post;
        
        if (has_shortcode($post->post_content, 'yolo_yacht_search')) {
            wp_enqueue_style('yolo-ys-search-results', 
                YOLO_YS_PLUGIN_URL . 'public/css/search-results.css', 
                array(), YOLO_YS_VERSION);
        }
        
        if (has_shortcode($post->post_content, 'yolo_our_fleet')) {
            wp_enqueue_style('yolo-ys-our-fleet', 
                YOLO_YS_PLUGIN_URL . 'public/css/our-fleet.css', 
                array(), YOLO_YS_VERSION);
        }
        
        // Check for booking confirmation page
        if (isset($_GET['session_id'])) {
            wp_enqueue_style('yolo-ys-booking-confirmation', 
                YOLO_YS_PLUGIN_URL . 'public/css/booking-confirmation.css', 
                array(), YOLO_YS_VERSION);
        }
    }
}
```

**2.2 Remove Inline Styles**

Remove `<style>` tags from:
- `public/templates/booking-confirmation.php`
- `public/templates/our-fleet.php`
- `public/templates/search-results.php`

**2.3 Extract CSS from Remaining Templates**

Check and extract CSS from:
- `public/templates/yacht-details.php`
- `public/templates/partials/yacht-card.php`
- Any other templates with inline styles

### Priority 3: Remaining Balance Payment (Task 4)

**3.1 Add Payment Link Generation**

Create function to generate Stripe Checkout Session for remaining balance:
```php
public function create_balance_payment_session($booking_id) {
    // Get booking details
    // Calculate remaining balance
    // Create Stripe Checkout Session
    // Return session URL
}
```

**3.2 Add "Pay Balance" Button**

In booking confirmation email and customer dashboard:
- Add "Pay Remaining Balance" button
- Link to Stripe Checkout Session
- Only show if payment_status = 'deposit_paid'

**3.3 Handle Balance Payment Return**

Create handler for balance payment return:
- Update booking payment_status to 'fully_paid'
- Update Booking Manager with final payment
- Send payment confirmation email

**3.4 Update Booking Manager**

POST to `/reservation/{id}/payments` with final payment:
```json
{
    "amount": remaining_balance,
    "payment_date": "YYYY-MM-DD",
    "payment_method": "stripe"
}
```

### Priority 4: HTML Email Templates (Task 5)

**4.1 Create Email Template Files**

Create `includes/emails/` directory with:
- `booking-confirmation.html` - Booking confirmation email
- `payment-reminder.html` - Payment reminder email
- `payment-received.html` - Final payment confirmation
- `booking-cancelled.html` - Cancellation email

**4.2 Email Template Features**

- Professional HTML design
- Responsive layout
- Include yacht images
- Branding (logo, colors)
- Clear call-to-action buttons
- Booking summary table
- Payment information

**4.3 Create Email Sender Class**

Create `includes/class-yolo-ys-email.php`:
```php
class YOLO_YS_Email {
    public function send_booking_confirmation($booking_id) {}
    public function send_payment_reminder($booking_id) {}
    public function send_payment_received($booking_id) {}
    public function send_cancellation($booking_id) {}
}
```

**4.4 Replace Plain Text Emails**

Update all email sending code to use HTML templates:
- Booking confirmation (after payment)
- Payment reminder (manual trigger from admin)
- Final payment confirmation (after balance paid)
- Cancellation notification

---

## Testing Checklist

### Admin Booking Management
- [ ] Bookings list displays correctly
- [ ] Filters work (status, yacht, date range)
- [ ] Search works (name, email)
- [ ] Sorting works (ID, date, yacht)
- [ ] Pagination works
- [ ] Booking detail page displays correctly
- [ ] View action works
- [ ] Send email action works
- [ ] Send reminder action works
- [ ] Mark paid action works
- [ ] Cancel action works
- [ ] Export to CSV works
- [ ] Dashboard widget displays correctly

### CSS Refactoring
- [ ] Booking confirmation page styles correctly
- [ ] Our fleet page styles correctly
- [ ] Search results page styles correctly
- [ ] No inline styles remain in templates
- [ ] All CSS files enqueued properly
- [ ] No style conflicts or broken layouts

### Remaining Balance Payment
- [ ] Payment link generates correctly
- [ ] Stripe Checkout Session created for balance
- [ ] Payment button shows only for deposit_paid bookings
- [ ] Balance payment completes successfully
- [ ] Booking status updates to fully_paid
- [ ] Booking Manager updated with final payment
- [ ] Payment confirmation email sent

### HTML Email Templates
- [ ] Booking confirmation email renders correctly
- [ ] Payment reminder email renders correctly
- [ ] Payment received email renders correctly
- [ ] Cancellation email renders correctly
- [ ] Emails display correctly in Gmail
- [ ] Emails display correctly in Outlook
- [ ] Emails display correctly on mobile
- [ ] Images load correctly
- [ ] Links work correctly
- [ ] Branding consistent

---

## Known Issues

### CSS Refactoring
- **Issue:** CSS files extracted but not yet enqueued
- **Impact:** Inline styles still present in templates
- **Solution:** Complete steps in Priority 2 above

### Admin Booking Management
- **Issue:** Only WP_List_Table class created, no UI integration
- **Impact:** Admin page not accessible yet
- **Solution:** Complete steps in Priority 1 above

### Price Formatter Class
- **Issue:** `YOLO_YS_Price_Formatter::format_price()` used in admin bookings class but may not exist
- **Impact:** Potential fatal error if class doesn't exist
- **Solution:** Verify class exists in `includes/class-yolo-ys-price-formatter.php` or create it

---

## Configuration

### Stripe Test Keys (Prefilled)
```
Publishable Key: pk_test_51ST5sKEqtLDG25BL...
Secret Key: sk_test_51ST5sKEqtLDG25BLF...
```

### Booking Manager API
```
Base URL: https://api.bookingmanager.com/v2
API Key: (configured in admin settings)
```

### Plugin Settings
- Deposit Percentage: 50% (configurable)
- FontAwesome CDN: Enabled (toggle available)
- Currency: EUR (default)
- Date Format: European (DD/MM/YYYY)

---

## File Structure

```
yolo-yacht-search/
├── yolo-yacht-search.php (v2.1.1)
├── admin/
│   ├── class-yolo-ys-admin.php
│   ├── class-yolo-ys-admin-bookings.php (NEW)
│   ├── css/
│   │   └── admin-bookings.css (TO CREATE)
│   └── partials/
│       ├── bookings-list.php (TO CREATE)
│       └── booking-detail.php (TO CREATE)
├── includes/
│   ├── class-yolo-ys-stripe.php
│   ├── class-yolo-ys-stripe-handlers.php
│   ├── class-yolo-ys-booking-manager-api.php
│   ├── class-yolo-ys-database.php
│   ├── class-yolo-ys-price-formatter.php (VERIFY EXISTS)
│   ├── class-yolo-ys-email.php (TO CREATE)
│   └── emails/ (TO CREATE)
│       ├── booking-confirmation.html
│       ├── payment-reminder.html
│       ├── payment-received.html
│       └── booking-cancelled.html
├── public/
│   ├── css/
│   │   ├── booking-confirmation.css (NEW)
│   │   ├── our-fleet.css (NEW)
│   │   └── search-results.css (NEW)
│   └── templates/
│       ├── booking-confirmation.php
│       ├── our-fleet.php
│       └── search-results.php
└── vendor/
    └── stripe/ (690 files)
```

---

## Development Notes

### Stripe Integration
- Using Stripe Checkout (hosted page) - no webhook required
- Bookings created on successful return from Stripe
- Session ID stored in database for reference
- Deposit percentage configurable in admin settings

### Booking Manager Integration
- Reservation created AFTER successful payment
- Reservation ID used as booking reference
- Payment recorded in Booking Manager
- No real-time sync - triggered by booking completion

### Database Design
- `wp_yolo_bookings` table stores all booking data
- Includes both Stripe session ID and BM reservation ID
- Payment status tracked: deposit_paid, fully_paid, cancelled
- Remaining balance calculated: total_price - deposit_paid

### Email System
- Currently using plain text emails
- Need to implement HTML templates
- Should include yacht images and branding
- Payment reminder functionality needed

---

## Version History

### v2.1.1 (In Progress)
- Changed booking reference to use Booking Manager reservation ID
- Extracted CSS from 3 templates to separate files
- Created admin bookings WP_List_Table class
- Pending: Complete admin UI, balance payments, HTML emails

### v2.1.0 (Completed)
- Full Stripe Checkout integration with deposit system
- Booking Manager API integration (POST /reservation and /payments)
- Live price checking when dates change
- Saturday-to-Saturday validation with quote request fallback
- FontAwesome CDN toggle in admin settings
- Price formatting fixed (European format: 18.681,00 EUR)
- 8 database tables including wp_yolo_bookings

---

## Next Session Action Items

1. **Verify Price Formatter Class Exists**
   - Check if `includes/class-yolo-ys-price-formatter.php` exists
   - If not, create it with `format_price()` method
   - Ensure European price formatting (18.681,00 EUR)

2. **Complete Admin Booking Management**
   - Create admin template files
   - Create admin CSS file
   - Add menu integration
   - Implement all actions
   - Add export to CSV
   - Create dashboard widget

3. **Complete CSS Refactoring**
   - Enqueue CSS files properly
   - Remove inline styles from templates
   - Extract CSS from remaining templates
   - Test all pages

4. **Implement Remaining Balance Payment**
   - Create payment link generation
   - Add "Pay Balance" button
   - Handle balance payment return
   - Update Booking Manager

5. **Create HTML Email Templates**
   - Design professional HTML templates
   - Create email sender class
   - Replace plain text emails
   - Test email rendering

6. **Testing**
   - Test all admin features
   - Test CSS refactoring
   - Test balance payment flow
   - Test email templates
   - Test export to CSV

7. **Update Version to 2.2.0**
   - Update version in main plugin file
   - Update readme.txt
   - Create changelog entry
   - Commit and push to GitHub

---

## Contact & Support

For questions or issues, refer to:
- Plugin documentation
- Stripe API documentation: https://stripe.com/docs/api
- Booking Manager API documentation
- WordPress Codex for WP_List_Table

---

**End of Handoff Document**
