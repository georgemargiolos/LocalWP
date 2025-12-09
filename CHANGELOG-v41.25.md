# CHANGELOG - Version 41.25

**Release Date:** December 9, 2025  
**Commit:** 67bc5d2

## Summary

Removed GA4 and Facebook Pixel base tracking initialization from the plugin while preserving all 7 custom yacht booking funnel events. Events now integrate with external site-wide analytics installation (PixelYourSite, Site Kit, MonsterInsights, etc.).

---

## Changes

### Analytics System Refactoring

**Removed:**
- ❌ GA4 Measurement ID setting field
- ❌ GA4 API Secret setting field
- ❌ Facebook Pixel ID setting field
- ❌ Facebook Access Token setting field
- ❌ GA4 base script loading (gtag config initialization)
- ❌ Facebook Pixel base script loading (fbq init)
- ❌ Server-side GA4 tracking (Measurement Protocol)
- ❌ Server-side Facebook tracking (Conversions API)

**Preserved:**
- ✅ All 7 custom yacht booking funnel events
- ✅ Event tracking via gtag() and fbq() functions
- ✅ Client-side event firing
- ✅ Debug mode functionality

**Updated:**
- 📝 Analytics settings section description to explain new approach
- 📝 Code comments to clarify integration requirements

---

## Custom Events (Preserved)

The following 7 events continue to track the yacht booking funnel:

1. **search** - User searches for yachts
   - Parameters: `search_term`

2. **view_item** - User views yacht details page
   - Parameters: `currency`, `value`, `items` (with `item_id`, `item_name`, `price`)

3. **add_to_cart** - User selects a week/price
   - Parameters: `currency`, `value`, `items`

4. **begin_checkout** - User clicks "Book Now"
   - Parameters: `currency`, `value`, `items`

5. **add_payment_info** - User submits booking form
   - Parameters: `currency`, `value`, `items`

6. **generate_lead** - User requests a quote
   - Parameters: `currency`, `value`

7. **purchase** - Booking completed (triggered from Stripe webhook)
   - Parameters: `transaction_id`, `currency`, `value`, `items`

---

## Integration Requirements

### Site-Wide Analytics Plugin Required

The plugin now expects GA4 and/or Facebook Pixel to be loaded by:
- PixelYourSite (recommended)
- Google Site Kit
- MonsterInsights
- Manual GTM implementation
- Any other analytics plugin that provides `gtag()` and `fbq()` functions

### How It Works

1. Site-wide plugin loads base tracking code (gtag.js, fbq.js)
2. YOLO plugin fires custom events using those functions
3. Events automatically flow to GA4 and Facebook Pixel
4. No duplicate tracking, no conflicts

---

## Files Modified

- `/admin/class-yolo-ys-admin.php` - Removed 4 settings fields and callbacks
- `/includes/class-yolo-ys-analytics.php` - Simplified to only enqueue custom events
- `/public/js/yolo-analytics.js` - Removed initialization, kept all 7 custom events

---

## Benefits

✅ **No Duplicate Tracking** - Works with existing site-wide analytics  
✅ **Cleaner Code** - Removed 200+ lines of initialization code  
✅ **Easier Management** - All analytics settings in one place (PixelYourSite/Site Kit)  
✅ **Better Compatibility** - No conflicts with other analytics plugins  
✅ **Preserved Functionality** - All custom yacht events still tracked  

---

## Migration Notes

### For Existing Installations

If you were using the plugin's built-in GA4/Facebook Pixel tracking:

1. Install and configure a site-wide analytics plugin (e.g., PixelYourSite)
2. Enter your GA4 Measurement ID and Facebook Pixel ID there
3. Update to v41.25
4. Custom events will automatically integrate with your site-wide tracking

### Settings Removed

The following settings are no longer available in YOLO Yacht Settings → Analytics:
- GA4 Measurement ID
- GA4 API Secret
- Facebook Pixel ID
- Facebook Access Token

Configure these in your site-wide analytics plugin instead.

---

## Technical Details

### Before (v41.24 and earlier)
```javascript
// Plugin loaded gtag.js and fbq.js
gtag('config', 'G-XXXXXXXXXX');
fbq('init', '1234567890');
gtag('event', 'view_item', {...});
```

### After (v41.25)
```javascript
// External plugin loads gtag.js and fbq.js
// YOLO plugin only fires events
if (typeof gtag === 'function') {
    gtag('event', 'view_item', {...});
}
```

---

## Testing

Verified that custom events fire correctly when:
- ✅ PixelYourSite loads GA4 and Facebook Pixel
- ✅ Google Site Kit loads GA4
- ✅ GTM loads analytics tags
- ✅ Events appear in GA4 Real-Time reports
- ✅ Events appear in Facebook Events Manager

---

## Known Issues

None

---

## Next Steps

See v41.26 for dataLayer.push() integration with Google Tag Manager.
