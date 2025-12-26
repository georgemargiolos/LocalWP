# Changelog v60.6.3 - Facebook Event Deduplication

**Date:** December 14, 2025  
**Type:** Analytics Enhancement

---

## What Changed

Implemented proper Facebook event deduplication by sending **ViewContent** and **Purchase** events from BOTH server-side (Conversions API) and client-side (Facebook Pixel) with matching `event_id`.

### Why This Matters

Facebook's best practice is to send the same event from both server and client with the same `event_id`. This allows Facebook to:

1. **Deduplicate** - Count as 1 event instead of 2
2. **Use best data** - Combine server-side user data with client-side browser data
3. **Improve attribution** - Better track conversions even with ad blockers

### Before v60.6.3

- ❌ **ViewContent**: Only sent server-side (CAPI)
- ❌ **Purchase**: Only sent server-side (CAPI)
- Result: Missing client-side browser context, no deduplication

### After v60.6.3

- ✅ **ViewContent**: Sent from BOTH server + client with same `event_id`
- ✅ **Purchase**: Sent from BOTH server + client with same `event_id`
- Result: Facebook deduplicates and uses best data from both sources

---

## Technical Implementation

### 1. Pass event_id to JavaScript

**File**: `public/templates/yacht-details-v3.php`
```php
$fb_event_id = yolo_analytics()->track_yacht_view($yacht_id, $yacht_price, $yacht_name);
```

**File**: `public/templates/partials/yacht-details-v3-scripts.php`
```javascript
const fbViewContentEventId = '<?php echo esc_js($fb_event_id); ?>';
```

### 2. Send ViewContent from client-side

**File**: `public/js/yolo-analytics.js`
```javascript
trackViewYacht: function(p) {
    // Send to GA4
    pushToDataLayer('view_item', {...});
    
    // Send to Facebook Pixel with server-side event_id
    const eventId = window.fbViewContentEventId || null;
    sendToFacebookPixel('ViewContent', {...}, eventId);
}
```

### 3. Send Purchase from client-side

**File**: `public/templates/booking-confirmation.php`
```php
$fb_purchase_event_id = yolo_analytics()->track_purchase(...);
echo '<script>window.fbPurchaseEventId = "' . esc_js($fb_purchase_event_id) . '";</script>';
```

```javascript
const eventId = window.fbPurchaseEventId || 'purchase_...';
fbq('track', 'Purchase', {...}, {eventID: eventId});
```

---

## Files Modified

1. `public/templates/yacht-details-v3.php` - Capture ViewContent event_id
2. `public/templates/partials/yacht-details-v3-scripts.php` - Pass event_id to JS
3. `public/js/yolo-analytics.js` - Send ViewContent from client-side
4. `public/templates/booking-confirmation.php` - Send Purchase from client-side with event_id
5. `yolo-yacht-search.php` - Version bump to 60.6.3

---

## Event Tracking Summary

### Server-Side (CAPI)
1. ✅ **ViewContent** - With user IP, user agent, fbp/fbc cookies
2. ✅ **Purchase** - With user email, phone, name

### Client-Side (Pixel)
1. ✅ **ViewContent** - With browser context, same event_id
2. ✅ **Purchase** - With browser context, same event_id
3. ✅ **Search** - Client-side only
4. ✅ **AddToCart** - Client-side only
5. ✅ **InitiateCheckout** - Client-side only
6. ✅ **AddPaymentInfo** - Client-side only

### Google Analytics 4
- All events sent to `dataLayer` for GTM/GA4

---

## Testing

After deploying v60.6.3:

1. **View a yacht details page**
   - Open browser DevTools Console
   - Should see: `[YOLO Analytics] fbq track: ViewContent ... eventID: evt_...`
   - Check Facebook Events Manager - should show 1 ViewContent (not 2)

2. **Complete a booking**
   - Check console for Purchase event with event_id
   - Check Facebook Events Manager - should show 1 Purchase (not 2)

3. **Verify deduplication**
   - Facebook Events Manager → Event Details
   - Should show "Deduplicated" badge if both server + client sent same event_id

---

**Status:** Ready for deployment

**Upgrade from v60.6.2:** Safe, no breaking changes
