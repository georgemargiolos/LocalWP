# CHANGELOG - Version 41.26

**Release Date:** December 9, 2025  
**Commit:** 891d1f4

## Summary

Switched from direct gtag()/fbq() calls to dataLayer.push() for proper Google Tag Manager integration. All 7 custom yacht booking events now flow through GTM for full visibility, control, and flexibility.

---

## Changes

### Analytics Architecture Update

**Removed:**
- ❌ Direct `gtag('event', ...)` calls
- ❌ Direct `fbq('track', ...)` calls
- ❌ Facebook Pixel event tracking (GTM will handle all destinations)

**Added:**
- ✅ `dataLayer.push()` for all 7 custom events
- ✅ dataLayer initialization check
- ✅ GTM-compatible event structure
- ✅ Improved event data formatting

**Updated:**
- 📝 Event tracking to use standard dataLayer format
- 📝 Code comments to reflect GTM integration
- 📝 Debug logging to show dataLayer pushes

---

## Event Structure

All events now use this format:

```javascript
window.dataLayer.push({
    event: 'event_name',
    parameter1: 'value1',
    parameter2: 'value2'
});
```

### Example: view_item Event

**Before (v41.25):**
```javascript
gtag('event', 'view_item', {
    currency: 'EUR',
    value: 5000,
    items: [{item_id: '123', item_name: 'Yacht', price: 5000}]
});
```

**After (v41.26):**
```javascript
window.dataLayer.push({
    event: 'view_item',
    currency: 'EUR',
    value: 5000,
    items: [{item_id: '123', item_name: 'Yacht', price: 5000}]
});
```

---

## Events Pushed to dataLayer

### 1. search
```javascript
{
    event: 'search',
    search_term: 'catamaran'
}
```

### 2. view_item
```javascript
{
    event: 'view_item',
    currency: 'EUR',
    value: 5000,
    items: [{
        item_id: '12345',
        item_name: 'Luxury Catamaran 50ft',
        price: 5000
    }]
}
```

### 3. add_to_cart
```javascript
{
    event: 'add_to_cart',
    currency: 'EUR',
    value: 5000,
    items: [{...}]
}
```

### 4. begin_checkout
```javascript
{
    event: 'begin_checkout',
    currency: 'EUR',
    value: 5000,
    items: [{...}]
}
```

### 5. add_payment_info
```javascript
{
    event: 'add_payment_info',
    currency: 'EUR',
    value: 5000,
    items: [{...}]
}
```

### 6. generate_lead
```javascript
{
    event: 'generate_lead',
    currency: 'EUR',
    value: 5000
}
```

### 7. purchase
```javascript
{
    event: 'purchase',
    transaction_id: 'txn_abc123',
    currency: 'EUR',
    value: 5000,
    items: [{...}]
}
```

---

## Google Tag Manager Setup Required

### GTM Configuration Steps

1. **Create 7 Custom Event Triggers** (one for each event name)
2. **Create Data Layer Variables** (currency, value, items, search_term, transaction_id)
3. **Create GA4 Event Tags** (7 tags, one per event)
4. **Optional: Create Facebook Pixel Tags** (if not using PixelYourSite)

See `/GTM_SETUP_GUIDE.md` for detailed instructions.

---

## Benefits

### ✅ Full GTM Visibility
- See all events in GTM Preview mode
- Debug event firing in real-time
- Inspect dataLayer structure

### ✅ Flexible Routing
- Send events to GA4, Facebook Pixel, or any platform
- Route different events to different destinations
- Add custom logic and conditions

### ✅ Easy Management
- All tracking configuration in GTM
- No code changes needed to add new destinations
- Centralized tag management

### ✅ Better Debugging
- Inspect dataLayer in browser console: `console.log(dataLayer)`
- Use GTM Preview mode to test
- See exactly what data is being sent

### ✅ Future-Proof
- Easy to add new analytics platforms
- No plugin updates needed for new destinations
- Industry-standard approach

---

## Files Modified

- `/public/js/yolo-analytics.js` - Complete rewrite for dataLayer integration
- `/yolo-yacht-search.php` - Version bump to 41.26

---

## Migration from v41.25

### If Using PixelYourSite or Site Kit

**Option 1: Keep Current Setup (Recommended)**
- PixelYourSite can detect dataLayer events automatically
- No GTM configuration needed
- Events will flow to GA4/Facebook automatically

**Option 2: Switch to GTM**
1. Disable GA4 in PixelYourSite (keep Facebook Pixel if desired)
2. Set up GTM tags following the setup guide
3. Publish GTM container

### If Using GTM

1. Follow the GTM Setup Guide to create triggers and tags
2. Test in GTM Preview mode
3. Publish container

---

## Testing

### Browser Console Test
```javascript
// Check if dataLayer exists
console.log(window.dataLayer);

// Should show array with your events
```

### GTM Preview Mode Test
1. Enable Preview mode in GTM
2. Navigate through booking flow
3. Verify all 7 events fire
4. Check parameters are captured

### GA4 Real-Time Test
1. Go to GA4 → Reports → Real-time
2. Navigate through booking flow
3. Verify events appear in real-time report

---

## Compatibility

### Works With:
- ✅ Google Tag Manager (recommended)
- ✅ PixelYourSite (auto-detects dataLayer events)
- ✅ Google Site Kit
- ✅ Any GTM-compatible analytics setup

### Does NOT Work With:
- ❌ Direct gtag.js implementation without GTM (use v41.25 instead)
- ❌ Hardcoded analytics without dataLayer support

---

## Known Issues

None

---

## Documentation

- **GTM Setup Guide:** `/GTM_SETUP_GUIDE.md` (created in this release)
- **Event Specifications:** See "Events Pushed to dataLayer" section above
- **Troubleshooting:** Check browser console for dataLayer output

---

## Next Steps

1. Set up GTM triggers and tags (see GTM_SETUP_GUIDE.md)
2. Test events in GTM Preview mode
3. Create remarketing audiences in GA4 and Facebook
4. Add Google Ads Remarketing Tag (optional)
5. Add Google Ads Conversion Tag (optional)

---

## Breaking Changes

⚠️ **If you were relying on direct gtag()/fbq() calls:**

This version removes direct analytics function calls. You must either:
1. Use GTM to capture dataLayer events, OR
2. Use PixelYourSite/Site Kit which auto-detect dataLayer events, OR
3. Revert to v41.25

---

## Developer Notes

### Custom Event Integration

If you need to trigger custom events programmatically:

```javascript
// Trigger a custom yacht event
window.dataLayer.push({
    event: 'custom_yacht_event',
    yacht_id: '12345',
    custom_parameter: 'value'
});
```

### Debug Mode

Enable debug mode in YOLO Yacht Settings to see console logs:
```
[YOLO Analytics] dataLayer.push: {event: 'view_item', ...}
```

---

## Support

For GTM setup assistance, see the comprehensive guide at `/GTM_SETUP_GUIDE.md`
