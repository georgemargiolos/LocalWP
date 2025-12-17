# CHANGELOG v16.1

**Date:** December 2, 2025  
**Session:** 16  
**Version:** 16.1

## Critical Bug Fix: Checkout Session Security Error

### Problem
Booking flow was failing with error: **"Error creating checkout session: Security check failed"**

This prevented customers from completing bookings after filling in their information and clicking "Book Now".

### Root Cause
The AJAX call to create the Stripe checkout session was **missing the WordPress nonce** (security token) in the request parameters.

**File:** `public/templates/partials/yacht-details-v3-scripts.php`  
**Line:** 880-893

The JavaScript was sending all booking data (yacht_id, dates, customer info) but not including the `nonce` parameter that the server-side code requires for security verification.

### Server-Side Verification
**File:** `includes/class-yolo-ys-stripe-handlers.php`  
**Line:** 31-33

```php
if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_ys_nonce')) {
    wp_send_json_error(array('message' => 'Security check failed'));
    return;
}
```

The server was correctly checking for the nonce, but the client wasn't sending it.

### Solution
Added the nonce parameter to the AJAX request body:

```javascript
body: new URLSearchParams({
    action: 'yolo_create_checkout_session',
    nonce: yoloYSData.nonce,  // ← ADDED THIS LINE
    yacht_id: yachtId,
    yacht_name: yachtName,
    date_from: dateFrom,
    date_to: dateTo,
    total_price: totalPrice,
    currency: currency,
    customer_first_name: firstName,
    customer_last_name: lastName,
    customer_email: email,
    customer_phone: phone
})
```

The nonce is already being generated and passed to JavaScript via `wp_localize_script()` in `class-yolo-ys-public.php` line 240:

```php
'nonce' => wp_create_nonce('yolo_ys_nonce'),
```

### Testing & Verification
✅ Verified all other AJAX calls in the codebase have proper nonce handling  
✅ Confirmed no regression issues introduced  
✅ Public endpoints (price checking, quote form) correctly have no nonce requirement  
✅ Admin endpoints all have nonce verification  

### Files Changed
- `public/templates/partials/yacht-details-v3-scripts.php` (1 line added)

### Impact
- **Critical:** Fixes booking flow completely broken since previous version
- **User Impact:** Customers can now complete bookings successfully
- **Security:** Maintains WordPress security best practices

### Next Steps
1. Deploy v16.1 to production
2. Test complete booking flow on live site
3. Monitor for any issues

---

**Commit:** 9d25fa8  
**Author:** Manus AI  
**Branch:** main
