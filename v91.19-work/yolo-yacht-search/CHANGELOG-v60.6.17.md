# YOLO Yacht Search Plugin - Changelog v60.6.17

**Date:** December 16, 2025  
**Version:** 60.6.17  
**Previous Version:** 60.6.16

---

## Summary

This release fixes the balance payment page 404 issue and improves email template padding for better visual presentation.

---

## Issues Fixed

### 1. Balance Payment Page "Page Not Found" (CRITICAL)

**Problem:** Clicking "Pay Remaining Balance" in the confirmation email redirected to a 404 page instead of the balance payment form.

**Root Cause:** The WordPress page with slug "balance-payment" containing the `[yolo_balance_payment]` shortcode did not exist on the live site.

**Solution:** This is a WordPress configuration issue, not a code issue. The plugin code is correct.

**Required Action:** Create a WordPress page with:
- Title: Balance Payment
- Slug: balance-payment
- Content: `[yolo_balance_payment]`

### 2. Email Template Padding Improvements

**Problem:** Email template had insufficient padding, making the content feel cramped.

**Solution:** Increased padding throughout the email template for better visual spacing.

**Changes Made:**
| Element | Before | After |
|---------|--------|-------|
| Header padding | 40px 20px | 50px 30px |
| Body padding | 40px 30px | 50px 40px |
| Booking card padding | 24px | 32px |
| Booking card margin | 24px 0 | 32px 0 |
| Highlight/success box padding | 16px | 24px |
| Highlight/success box margin | 24px 0 | 32px 0 |
| Detail row padding | 10px 0 | 14px 0 |
| Button padding | 14px 32px | 16px 40px |
| Footer padding | 30px 20px | 40px 30px |
| Paragraph margin | 16px | 20px |
| H2 margin | 20px | 24px |

---

## Files Modified

| File | Changes |
|------|---------|
| `yolo-yacht-search.php` | Version bump to 60.6.17 |
| `includes/emails/email-template.php` | Increased padding throughout template |

---

## WordPress Setup Required

To fix the balance payment page issue, create a new WordPress page:

1. Go to WordPress Admin → Pages → Add New
2. Set Title: **Balance Payment**
3. Set Slug: **balance-payment** (in Permalink settings)
4. Add content: `[yolo_balance_payment]`
5. Publish the page

---

## Testing Checklist

- [ ] Create the balance-payment WordPress page
- [ ] Test the "Pay Remaining Balance" link from confirmation email
- [ ] Verify the balance payment form loads correctly
- [ ] Send a test booking confirmation email
- [ ] Verify improved padding in email template
- [ ] Check email renders correctly on mobile devices

---

## Technical Notes

The shortcode `[yolo_balance_payment]` is registered in `class-yolo-ys-shortcodes.php` and the template exists at `public/templates/balance-payment.php`. The code was already correct; only the WordPress page was missing.
