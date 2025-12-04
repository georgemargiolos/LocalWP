# Changelog - Version 17.14

**Release Date:** December 3, 2025  
**Commit:** 84cda10  
**Status:** Production Ready

---

## 🎯 Critical Fixes

### Date Picker Booking Validation Error
**Impact:** HIGH - Users couldn't book when using date picker  
**File:** `public/templates/partials/yacht-details-v3-scripts.php`

**Problem:**
- Clicking "Book Now" after selecting dates via date picker showed "Please select a week first"
- Validation only checked for active carousel slide, not date picker selection

**Solution:**
- Added fallback to check `window.yoloLivePrice` (set by date picker)
- Now supports BOTH carousel selection AND custom date range booking

**Code Changes:**
```javascript
// Before: Only checked carousel
const activeSlide = document.querySelector('.price-slide.active');
if (!activeSlide) {
    alert('Please select a week first');
    return;
}

// After: Checks both carousel AND date picker
let totalPrice, currency;
const activeSlide = document.querySelector('.price-slide.active');
if (activeSlide) {
    totalPrice = parseFloat(activeSlide.dataset.price);
    currency = activeSlide.dataset.currency || 'EUR';
} else if (window.yoloLivePrice) {
    totalPrice = parseFloat(window.yoloLivePrice.price);
    currency = window.yoloLivePrice.currency || 'EUR';
} else {
    alert('Please select your charter dates first');
    return;
}
```

---

## 🔒 Security Enhancements

### Added ABSPATH Security Checks
**Impact:** MEDIUM - Prevents direct file access  
**Files:** 9 template and email files

**Files Modified:**
1. `public/templates/booking-confirmation.php`
2. `public/templates/balance-payment.php`
3. `public/templates/search-results.php`
4. `public/partials/yolo-ys-guest-dashboard.php`
5. `public/partials/yolo-ys-guest-login.php`
6. `includes/emails/booking-confirmation.php`
7. `includes/emails/email-template.php`
8. `includes/emails/payment-received.php`
9. `includes/emails/payment-reminder.php`

**Added to each file:**
```php
<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
```

---

## 🎨 UI/UX Improvements

### Search Box Styling Enhancement
**Impact:** LOW - Visual improvement  
**File:** `public/css/yolo-yacht-search-public.css`

**Changes:**
- Added visible border and background to search widget
- Clean, minimal design with subtle shadow
- Better visual separation on results page

**CSS Added:**
```css
.yolo-ys-search-widget {
    background: #ffffff;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
```

### Red Border Removed from YOLO Fleet Boats
**Impact:** LOW - Visual improvement  
**File:** `public/css/global-mobile-responsive.css`

**Changes:**
- Removed red border from YOLO Fleet boats in high contrast mode
- Cleaner appearance on mobile devices

**CSS Added:**
```css
@media (prefers-contrast: high) {
    .yolo-ys-yacht-card.yolo-yacht {
        border: none !important;
    }
}
```

---

## 📊 Files Changed

| File | Lines Changed | Type |
|------|---------------|------|
| `yacht-details-v3-scripts.php` | +15, -6 | Fix |
| `booking-confirmation.php` | +5 | Security |
| `balance-payment.php` | +5 | Security |
| `search-results.php` | +5 | Security |
| `yolo-ys-guest-dashboard.php` | +5 | Security |
| `yolo-ys-guest-login.php` | +5 | Security |
| `emails/booking-confirmation.php` | +5 | Security |
| `emails/email-template.php` | +5 | Security |
| `emails/payment-received.php` | +5 | Security |
| `emails/payment-reminder.php` | +5 | Security |
| `yolo-yacht-search-public.css` | +8 | Enhancement |
| `global-mobile-responsive.css` | +5 | Enhancement |

**Total:** 12 files modified, 97 insertions, 9 deletions

---

## 🔄 Upgrade Notes

### From v17.13 to v17.14

**Breaking Changes:** None  
**Database Changes:** None  
**Configuration Changes:** None

**Upgrade Steps:**
1. Deactivate plugin
2. Upload new version
3. Activate plugin
4. Clear all caches (WordPress, browser, CDN)
5. Test booking flow with date picker

**Rollback:** Safe to rollback to v17.13 if needed

---

## ✅ Testing Performed

- [x] Date picker booking flow
- [x] Carousel booking flow
- [x] Search box display on results page
- [x] YOLO Fleet boats display (mobile)
- [x] Template file direct access blocked
- [x] All v17.13 fixes still working
- [x] No JavaScript console errors
- [x] No PHP errors in logs

---

## 🐛 Known Issues

None identified in this release.

---

## 📝 Notes

This release focuses on fixing the critical date picker booking validation bug that prevented users from completing bookings when using custom date ranges. All security enhancements are backward compatible and don't affect existing functionality.

The search box styling and YOLO Fleet border fixes are purely cosmetic improvements that enhance the user experience without changing any functionality.

---

## 🔗 Related Issues

- Date picker booking validation: Reported December 3, 2025
- Search box styling: Requested December 3, 2025
- YOLO Fleet red border: Reported December 3, 2025

---

**Next Version:** 17.15 (TBD)
