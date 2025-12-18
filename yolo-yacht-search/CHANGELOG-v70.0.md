# Changelog - Version 70.0

**Date:** December 17, 2025  
**Version:** 70.0  
**Priority:** 🔴 CRITICAL BUG FIX  

---

## Summary

This release fixes a **critical bug** where guest users could not log in because the password stored in WordPress did not match the password shown in the booking confirmation email.

---

## 🔴 Bug Fixed

### Guest Login Password Mismatch

**Problem:**
- Email showed password as: `BM-7333050630000107850YoLo`
- Actual password in WordPress was: `123YoLo` (using booking ID instead of BM reservation ID)
- **Result:** Customers could not log in to their guest accounts!

**Root Cause:**
The `create_guest_user()` function was passing `$booking_id` (e.g., `123`) as the password base, but the email template was generating the password using `$booking_reference` (e.g., `BM-7333050630000107850`).

**Solution:**
Updated all password generation code to use the same `$booking_reference` formula:
```php
$booking_reference = !empty($booking->bm_reservation_id) 
    ? 'BM-' . $booking->bm_reservation_id 
    : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
```

---

## 📝 Changes Made

### 1. `yolo-yacht-search.php`
- **Version bump** from 65.23 to 70.0
- Updated `YOLO_YS_VERSION` constant

### 2. `includes/class-yolo-ys-stripe-handlers.php`
- **Fixed AJAX handler** (`ajax_process_stripe_booking`)
- Added booking reference generation before creating guest user
- Password now uses `BM-{bm_reservation_id}YoLo` format

### 3. `includes/class-yolo-ys-stripe.php`
- **Reordered operations:** BM reservation is now created BEFORE guest user (so BM ID is available)
- **Updated `create_guest_user()` method** to fetch booking and generate correct reference
- Added logging for debugging: `YOLO YS: Using booking reference for password: BM-...`

### 4. `public/templates/booking-confirmation.php`
- **Fixed booking reference display** to include `BM-` prefix
- Now consistent with email template format

---

## 📊 Before vs After

### BEFORE (v65.23):
| Location | Booking Reference | Password |
|----------|------------------|----------|
| Confirmation Page | `7333050630000107850` | - |
| Email | `BM-7333050630000107850` | `BM-7333050630000107850YoLo` |
| WordPress | - | `123YoLo` |
| **Login Result** | | ❌ **FAIL** |

### AFTER (v70.0):
| Location | Booking Reference | Password |
|----------|------------------|----------|
| Confirmation Page | `BM-7333050630000107850` | - |
| Email | `BM-7333050630000107850` | `BM-7333050630000107850YoLo` |
| WordPress | - | `BM-7333050630000107850YoLo` |
| **Login Result** | | ✅ **SUCCESS** |

---

## 📁 Files Modified

| File | Changes |
|------|---------|
| `yolo-yacht-search.php` | Version bump to 70.0 |
| `includes/class-yolo-ys-stripe-handlers.php` | Fixed password generation in AJAX handler |
| `includes/class-yolo-ys-stripe.php` | Reordered operations, fixed password generation |
| `public/templates/booking-confirmation.php` | Fixed booking reference display |

---

## 🧪 Testing Checklist

After deployment:

1. **Test New Booking:**
   - [ ] Make a test booking on the website
   - [ ] Complete Stripe payment
   - [ ] Check confirmation page shows `BM-{id}` format
   - [ ] Check email shows same `BM-{id}` format
   - [ ] Try to login with password from email
   - [ ] Login should work! ✅

2. **Check Error Logs:**
   - [ ] Look for: `YOLO YS: Using booking reference for password: BM-...`
   - [ ] Confirm no PHP errors

---

## ⚠️ Existing Users

Users who booked BEFORE this fix have incorrect passwords. Options:

### Option 1: Manual Password Reset
1. Go to WordPress Admin → Users
2. Find the guest user by email
3. Set password to: `BM-{their_bm_reservation_id}YoLo`

### Option 2: Run Migration Script
See `HANDOFF.md` for the migration script to fix all existing users.

---

## 🔄 Order of Operations (After Fix)

1. ✅ Stripe payment completes
2. ✅ Booking created in WordPress DB
3. ✅ **BM reservation created** (get `bm_reservation_id`)
4. ✅ **Guest user created** with password = `BM-{bm_reservation_id}YoLo`
5. ✅ Confirmation emails sent (with matching password)
6. ✅ Analytics tracked (Purchase event)
7. ✅ Page shows confirmation with `BM-{id}` format

---

**Implementation Status:** ✅ Complete  
**Tested:** Pending deployment  
**Backward Compatible:** Yes  
**Breaking Changes:** None (existing bookings unaffected, but existing users need password fix)
