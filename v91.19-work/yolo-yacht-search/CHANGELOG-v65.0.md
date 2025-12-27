# YOLO Yacht Search Plugin - Changelog v65.0

**Date:** December 16, 2025  
**Version:** 65.0  
**Previous Version:** 60.6.17

---

## Summary

Major update to the email system with improved styling, guest login information, and full mobile responsiveness.

---

## New Features

### 1. Guest Login Information in Booking Confirmation Email

Added a new "Your Guest Account" section to the booking confirmation email that includes:
- Login URL (yolo-charters.com/guest-login)
- Customer email
- Auto-generated password (Booking Reference + "YoLo")
- "Access Guest Portal" button

This helps customers easily access their guest dashboard to manage bookings, view documents, and track charter details.

### 2. Secondary Button Style

Added a new `.button-secondary` CSS class for secondary call-to-action buttons (blue color) to differentiate from primary actions.

### 3. Info Box Style

Added a new `.info-box` CSS class for informational content boxes (blue theme) to complement the existing highlight (yellow/warning) and success (green) boxes.

---

## Improvements

### Email Template Enhancements

| Feature | Description |
|---------|-------------|
| Mobile Responsiveness | Full responsive design with breakpoints at 600px and 400px |
| Flexbox Layout | Switched from table-based to flexbox layout for detail rows |
| Text Size Adjustment | Added `-webkit-text-size-adjust` and `-ms-text-size-adjust` for consistent mobile rendering |
| Button Responsiveness | Buttons become full-width on mobile devices |
| Padding Adjustments | Dynamic padding that reduces on smaller screens |

### Mobile-Specific Styles

| Element | Desktop | Mobile (≤600px) | Small Mobile (≤400px) |
|---------|---------|-----------------|----------------------|
| Header padding | 50px 30px | 40px 20px | 32px 16px |
| Body padding | 50px 40px | 32px 20px | 24px 16px |
| Logo max-width | 180px | 150px | 130px |
| Heading font-size | 28px | 24px | 24px |
| Booking card padding | 32px | 20px | 16px |

---

## Files Modified

| File | Changes |
|------|---------|
| `yolo-yacht-search.php` | Version bump to 65.0 |
| `includes/emails/email-template.php` | Added info-box and button-secondary styles, improved mobile responsiveness |
| `includes/emails/booking-confirmation.php` | Added guest login section with credentials |
| `includes/class-yolo-ys-email.php` | Added customer_email to booking confirmation variables |

---

## Technical Details

### Guest Password Generation

The guest password is automatically generated using the formula:
```
password = booking_reference + "YoLo"
```

Example: If booking reference is `BM-7329603270000107850`, the password is `BM-7329603270000107850YoLo`

### CSS Classes Added

```css
.info-box {
    background: #dbeafe;
    border-left: 4px solid #3b82f6;
    padding: 24px;
    margin: 32px 0;
    border-radius: 6px;
}

.button-secondary {
    display: inline-block;
    padding: 14px 32px;
    background: #3b82f6;
    color: #ffffff !important;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
}
```

---

## Testing Checklist

- [ ] Send a test booking confirmation email
- [ ] Verify guest login section appears with correct credentials
- [ ] Test login URL works correctly
- [ ] Verify email renders correctly on desktop
- [ ] Verify email renders correctly on mobile devices
- [ ] Test all buttons are clickable and properly styled
- [ ] Verify logo displays correctly

---

## Breaking Changes

None. This is a backward-compatible update.
