# Handoff Document - YOLO Yacht Search & Booking Plugin

**Date:** December 17, 2025  
**Version:** v70.0 (Last Stable Version)  
**Task Goal:** Fix critical guest login password mismatch bug and ensure booking reference consistency.

---

## 🔴 Summary of Work Completed (v65.23 → v70.0)

### Critical Bug Fixed: Guest Login Password Mismatch

**Problem:**
- Email showed password as: `BM-7333050630000107850YoLo`
- Actual password in WordPress was: `123YoLo` (using booking ID instead of BM reservation ID)
- **Result:** Customers could not log in to their guest accounts!

**Solution:**
1. Updated all password generation code to use the same `$booking_reference` formula
2. Reordered operations so BM reservation is created BEFORE guest user
3. Fixed confirmation page to show booking reference with `BM-` prefix

### Files Modified in v70.0:

| File | Changes |
|------|--------|
| `yolo-yacht-search.php` | Version bump from 65.23 to 70.0 |
| `includes/class-yolo-ys-stripe-handlers.php` | Fixed AJAX handler - password now uses `BM-{bm_reservation_id}YoLo` |
| `includes/class-yolo-ys-stripe.php` | Reordered operations, fixed webhook handler password generation |
| `public/templates/booking-confirmation.php` | Fixed booking reference display to include `BM-` prefix |

---

## Order of Operations After Stripe Payment (v70.0)

1. ✅ Stripe payment completes
2. ✅ Redirect to confirmation page with spinner
3. ✅ AJAX call to `yolo_process_stripe_booking`
4. ✅ Retrieve Stripe session and verify payment
5. ✅ **Create booking in WordPress DB**
6. ✅ **Create BM reservation** (get `bm_reservation_id`)
7. ✅ **Create guest user** with password = `BM-{bm_reservation_id}YoLo`
8. ✅ Send confirmation emails (with matching password)
9. ✅ Track Purchase event (FB CAPI + GA4)
10. ✅ Page reloads with confirmation showing `BM-{id}` format

---

## Password Format

| Scenario | Booking Reference | Password |
|----------|------------------|----------|
| With BM reservation | `BM-7333050630000107850` | `BM-7333050630000107850YoLo` |
| Without BM reservation | `YOLO-2025-0123` | `YOLO-2025-0123YoLo` |

---

## ⚠️ Existing Users Need Password Fix

Users who booked BEFORE v70.0 have incorrect passwords.

### Option 1: Manual Password Reset
1. Go to WordPress Admin → Users
2. Find the guest user by email
3. Set password to: `BM-{their_bm_reservation_id}YoLo`

### Option 2: Run Migration Script

Create `fix-guest-passwords.php` in WordPress root:

```php
<?php
require_once('wp-load.php');
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Access denied.');
}
global $wpdb;
$query = "SELECT b.id, b.customer_email, b.bm_reservation_id, u.ID as user_id
          FROM {$wpdb->prefix}yolo_bookings b
          INNER JOIN {$wpdb->users} u ON u.user_email = b.customer_email
          WHERE b.bm_reservation_id IS NOT NULL";
$bookings = $wpdb->get_results($query);
foreach ($bookings as $booking) {
    $password = 'BM-' . $booking->bm_reservation_id . 'YoLo';
    wp_set_password($password, $booking->user_id);
    echo "Fixed: {$booking->customer_email}<br>";
}
echo "Done! DELETE THIS FILE NOW!";
?>
```

**DELETE the file immediately after running!**

---

## Testing Checklist

- [ ] Make a test booking
- [ ] Complete Stripe payment
- [ ] Verify confirmation page shows `BM-{id}` format
- [ ] Verify email shows same `BM-{id}` format
- [ ] Try to login with password from email
- [ ] Login should work! ✅

---

## Suggested Next Steps

1. **Deploy v70.0** to production
2. **Test with a real booking** to verify fix works
3. **Run migration script** to fix existing users (if needed)
4. **Monitor error logs** for 24 hours

---

## Previous Work (v65.21 - v65.23)

The v65.x series implemented:
- AJAX-based booking confirmation flow with immediate spinner display
- Customizable progressive spinner texts (0s, 10s, 35s, 45s)
- Removed debug code from yacht-details-v3.php
