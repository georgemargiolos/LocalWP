# Booking Form Implementation - v2.2.0

**Date:** November 30, 2025  
**Feature:** Customer Information Collection Before Payment  
**Version:** 2.2.0  
**Database Version:** 1.4

---

## Overview

Implemented a professional booking form that collects customer information (name, email, phone) **before** redirecting to Stripe payment. This ensures we have customer data even if they abandon the payment process, and provides a better user experience.

---

## What Was Implemented

### 1. Booking Form Modal

**File:** `public/templates/partials/yacht-details-v3-scripts.php`

**Features:**
- Professional modal overlay with modern design
- Booking summary section showing:
  - Yacht name
  - Check-in and check-out dates
  - Total price (formatted)
- Customer information form with fields:
  - First Name (required)
  - Last Name (required)
  - Email Address (required)
  - Mobile Number (required)
- Form validation
- Responsive design
- Smooth animations and transitions
- Focus states for better UX

**User Flow:**
1. Customer selects dates on yacht details page
2. Customer clicks "BOOK NOW" button
3. Modal appears with booking summary and form
4. Customer fills in their information
5. Customer clicks "PROCEED TO PAYMENT →"
6. Form data is sent to Stripe Checkout Session
7. Customer is redirected to Stripe payment page

### 2. Stripe Integration Updates

**File:** `includes/class-yolo-ys-stripe-handlers.php`

**Changes:**
- Added customer information parameters to AJAX handler:
  - `customer_first_name`
  - `customer_last_name`
  - `customer_email`
  - `customer_phone`
  - `currency`
- Added validation for customer information
- Pass customer data to Stripe class

**File:** `includes/class-yolo-ys-stripe.php`

**Changes:**
- Updated `create_checkout_session()` method signature to accept customer data
- Added customer information to Stripe metadata:
  - `customer_first_name`
  - `customer_last_name`
  - `customer_name` (full name)
  - `customer_email`
  - `customer_phone`
- Pre-fill customer email in Stripe Checkout using `customer_email` parameter
- Support for dynamic currency (not just EUR)

### 3. Database Schema Update

**File:** `includes/class-yolo-ys-database.php`

**Changes:**
- Added `customer_phone` field to `wp_yolo_bookings` table
- Added `bm_reservation_id` field (for Booking Manager reservation ID)
- Added index on `bm_reservation_id` for faster lookups
- Updated database version to 1.4

**New Schema:**
```sql
CREATE TABLE wp_yolo_bookings (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    yacht_id bigint(20) NOT NULL,
    yacht_name varchar(255) NOT NULL,
    date_from date NOT NULL,
    date_to date NOT NULL,
    total_price decimal(10,2) NOT NULL,
    deposit_paid decimal(10,2) NOT NULL,
    remaining_balance decimal(10,2) NOT NULL,
    currency varchar(10) DEFAULT 'EUR',
    customer_email varchar(255) NOT NULL,
    customer_name varchar(255) NOT NULL,
    customer_phone varchar(50) DEFAULT NULL,  -- NEW
    stripe_session_id varchar(255) DEFAULT NULL,
    stripe_payment_intent varchar(255) DEFAULT NULL,
    payment_status varchar(50) DEFAULT 'pending',
    booking_status varchar(50) DEFAULT 'pending',
    booking_manager_id varchar(255) DEFAULT NULL,
    bm_reservation_id varchar(255) DEFAULT NULL,  -- NEW
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY yacht_id (yacht_id),
    KEY customer_email (customer_email),
    KEY stripe_session_id (stripe_session_id),
    KEY bm_reservation_id (bm_reservation_id)  -- NEW
);
```

### 4. Booking Confirmation Update

**File:** `public/templates/booking-confirmation.php`

**Changes:**
- Retrieve customer information from Stripe metadata (preferred method)
- Fallback to `customer_details` if metadata is empty
- Store `customer_phone` in database
- Extract all customer fields from metadata:
  - `customer_first_name`
  - `customer_last_name`
  - `customer_name`
  - `customer_email`
  - `customer_phone`

---

## Benefits

### For Business
1. **Customer Data Collection** - We now have customer contact information even if payment fails
2. **Better Communication** - Phone number allows for SMS notifications and direct contact
3. **Reduced Abandonment** - Professional form builds trust before payment
4. **Marketing Data** - Email and phone for future marketing campaigns
5. **Customer Service** - Can contact customers if issues arise

### For Customers
1. **Better UX** - Clear booking summary before payment
2. **Trust Building** - Professional form increases confidence
3. **Pre-filled Data** - Email pre-filled in Stripe checkout
4. **Transparency** - See exactly what they're booking
5. **Mobile Friendly** - Responsive design works on all devices

---

## Technical Details

### Frontend (JavaScript)

**Function:** `showBookingFormModal()`
- Creates modal dynamically
- Formats dates and prices for display
- Handles form submission
- Validates required fields
- Shows loading state during submission
- Sends AJAX request to create Stripe session

**Function:** `closeBookingFormModal()`
- Removes modal from DOM
- Allows user to cancel booking

**Function:** `bookNow()`
- Validates date selection
- Validates price selection
- Calls `showBookingFormModal()` instead of directly creating Stripe session

### Backend (PHP)

**AJAX Handler:** `ajax_create_checkout_session()`
- Sanitizes all customer input
- Validates required booking fields
- Validates required customer fields
- Creates Stripe Checkout Session with customer data

**Stripe Session Creation:** `create_checkout_session()`
- Accepts customer data as parameters
- Stores customer data in Stripe metadata
- Pre-fills customer email in Stripe checkout
- Supports dynamic currency

**Booking Creation:** `booking-confirmation.php`
- Retrieves customer data from Stripe metadata
- Stores customer phone in database
- Creates booking record with full customer information

---

## Data Flow

```
1. Customer clicks "BOOK NOW"
   ↓
2. Modal appears with booking form
   ↓
3. Customer fills in: First Name, Last Name, Email, Phone
   ↓
4. Customer clicks "PROCEED TO PAYMENT"
   ↓
5. JavaScript validates form
   ↓
6. AJAX request to WordPress:
   - yacht_id, yacht_name, date_from, date_to, total_price, currency
   - customer_first_name, customer_last_name, customer_email, customer_phone
   ↓
7. PHP creates Stripe Checkout Session:
   - Stores customer data in metadata
   - Pre-fills customer email
   ↓
8. Customer redirected to Stripe payment page
   ↓
9. Customer completes payment
   ↓
10. Customer redirected back to confirmation page
   ↓
11. PHP retrieves Stripe session:
    - Extracts customer data from metadata
    - Creates booking in database with customer phone
    - Creates reservation in Booking Manager
    ↓
12. Booking confirmation displayed with customer information
```

---

## Files Modified

1. **public/templates/partials/yacht-details-v3-scripts.php**
   - Replaced `bookNow()` function to show modal instead of direct Stripe redirect
   - Added `showBookingFormModal()` function
   - Added `closeBookingFormModal()` function

2. **includes/class-yolo-ys-stripe-handlers.php**
   - Updated `ajax_create_checkout_session()` to accept customer data
   - Added customer data validation
   - Pass customer data to Stripe class

3. **includes/class-yolo-ys-stripe.php**
   - Updated `create_checkout_session()` method signature
   - Added customer data to Stripe metadata
   - Pre-fill customer email in Stripe checkout
   - Support dynamic currency

4. **includes/class-yolo-ys-database.php**
   - Added `customer_phone` field to bookings table
   - Added `bm_reservation_id` field
   - Updated database version to 1.4

5. **public/templates/booking-confirmation.php**
   - Retrieve customer data from Stripe metadata
   - Store customer phone in database

6. **yolo-yacht-search.php**
   - Updated plugin version to 2.2.0

---

## Database Migration

**Automatic Migration:**
The database will automatically upgrade when the plugin is activated or when any admin page is visited. The migration adds:
- `customer_phone` field to `wp_yolo_bookings` table
- `bm_reservation_id` field to `wp_yolo_bookings` table
- Index on `bm_reservation_id`

**Manual Migration (if needed):**
```sql
ALTER TABLE wp_yolo_bookings 
ADD COLUMN customer_phone varchar(50) DEFAULT NULL AFTER customer_name,
ADD COLUMN bm_reservation_id varchar(255) DEFAULT NULL AFTER booking_manager_id,
ADD KEY bm_reservation_id (bm_reservation_id);
```

---

## Testing Checklist

### Frontend
- [ ] Click "BOOK NOW" button - modal appears
- [ ] Modal shows correct yacht name
- [ ] Modal shows correct dates
- [ ] Modal shows correct price
- [ ] All form fields are present
- [ ] Required field validation works
- [ ] Email validation works
- [ ] Phone field accepts international format
- [ ] Cancel button closes modal
- [ ] Form submission shows loading state
- [ ] Modal is responsive on mobile

### Backend
- [ ] Customer data is sent to Stripe
- [ ] Customer email is pre-filled in Stripe checkout
- [ ] Customer data is stored in Stripe metadata
- [ ] Payment completes successfully
- [ ] Booking is created in database
- [ ] Customer phone is stored in database
- [ ] Customer name is stored correctly
- [ ] Customer email is stored correctly

### Integration
- [ ] Booking Manager reservation is created
- [ ] Customer data is sent to Booking Manager
- [ ] Email confirmation is sent to customer
- [ ] Email contains customer information
- [ ] Admin can see customer phone in bookings

---

## Future Enhancements

1. **Phone Number Validation**
   - Add international phone number validation
   - Use library like libphonenumber

2. **Additional Fields**
   - Add optional fields (company, address, special requests)
   - Add checkbox for terms and conditions
   - Add newsletter opt-in

3. **Save for Later**
   - Allow customers to save booking and return later
   - Send email with booking link

4. **Guest Checkout**
   - Option to create account during booking
   - Save customer information for future bookings

5. **SMS Notifications**
   - Send SMS confirmation using customer phone
   - Send SMS reminders for remaining balance

---

## Known Issues

None at this time.

---

## Version History

### v2.2.0 (November 30, 2025)
- ✅ Added booking form modal before payment
- ✅ Collect customer information (name, email, phone)
- ✅ Pre-fill customer email in Stripe checkout
- ✅ Store customer phone in database
- ✅ Updated database schema to v1.4
- ✅ Support dynamic currency

### v2.1.1 (Previous)
- Changed booking reference to use Booking Manager reservation ID
- Extracted CSS from templates
- Started admin booking management

### v2.1.0 (Previous)
- Full Stripe Checkout integration
- Booking Manager API integration
- Deposit payment system

---

**End of Document**
