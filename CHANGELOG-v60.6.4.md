# YOLO Yacht Search v60.6.4 - Facebook Event Tracking with Full Deduplication

**Release Date:** December 16, 2024

## 🎯 Overview

This release implements **complete Facebook event tracking** with proper **deduplication** between server-side (CAPI) and client-side (Pixel) for all major conversion events. Each event is now sent from BOTH server-side and client-side with matching `event_id` to ensure Facebook can properly deduplicate and attribute conversions.

---

## ✅ What's New

### 1. **AddToCart Event - BOOK NOW Button Click**
- **Trigger:** When user clicks "BOOK NOW" button after selecting dates
- **Server-side:** PHP AJAX handler calls `track_add_to_cart()` via CAPI
- **Client-side:** JavaScript sends same event to Facebook Pixel with matching `event_id`
- **Deduplication:** ✅ Both sides use same `event_id` generated server-side
- **Data sent:** Yacht ID, yacht name, price, currency

### 2. **InitiateCheckout Event - Booking Form Submission**
- **Trigger:** When user submits booking form (proceeds to payment)
- **Server-side:** PHP AJAX handler calls `track_initiate_checkout()` via CAPI
- **Client-side:** JavaScript sends same event to Facebook Pixel with matching `event_id`
- **Deduplication:** ✅ Both sides use same `event_id` generated server-side
- **Data sent:** Yacht ID, yacht name, price, currency, check-in date, check-out date

### 3. **Lead Event - Quote Request Submission**
- **Trigger:** When user submits quote request form
- **Server-side:** PHP calls `track_generate_lead()` via CAPI (already implemented, now returns `event_id`)
- **Client-side:** JavaScript sends same event to Facebook Pixel with matching `event_id`
- **Deduplication:** ✅ Both sides use same `event_id` generated server-side
- **Data sent:** Yacht name, user email, phone, name (server-side only)

---

## 📋 Event Flow Summary

| User Action | Event Name | Server-side (CAPI) | Client-side (Pixel) | Deduplication |
|-------------|------------|-------------------|---------------------|---------------|
| Views yacht details | ViewContent | ✅ | ✅ | ✅ |
| Clicks "BOOK NOW" | AddToCart | ✅ NEW | ✅ | ✅ NEW |
| Submits booking form | InitiateCheckout | ✅ NEW | ✅ | ✅ NEW |
| Submits quote request | Lead | ✅ | ✅ NEW | ✅ NEW |
| Completes payment | Purchase | ✅ | ✅ | ✅ |

---

## 🔧 Technical Changes

### Files Modified

1. **`public/class-yolo-ys-public.php`**
   - Added `ajax_track_add_to_cart()` method
   - Added `ajax_track_initiate_checkout()` method

2. **`includes/class-yolo-ys-yacht-search.php`**
   - Registered AJAX handlers for `yolo_track_add_to_cart`
   - Registered AJAX handlers for `yolo_track_initiate_checkout`

3. **`includes/class-yolo-ys-quote-handler.php`**
   - Updated to return `event_id` in AJAX response for Lead event deduplication

4. **`public/templates/partials/yacht-details-v3-scripts.php`**
   - Added AJAX call to track AddToCart when BOOK NOW button is clicked
   - Added AJAX call to track InitiateCheckout when booking form is submitted
   - Added client-side Lead tracking when quote request succeeds

5. **`public/js/yolo-analytics.js`**
   - Updated `trackSelectWeek()` to accept `eventId` parameter for deduplication
   - Updated `trackBeginCheckout()` to accept `eventId` parameter for deduplication
   - Added new `trackLead()` method with `eventId` parameter for deduplication
   - All methods now support fallback for `yacht_id`/`yacht_name` parameters

---

## 🎯 How Deduplication Works

1. **Server-side generates unique `event_id`:**
   ```php
   $event_id = uniqid('yolo_', true) . '_' . time();
   ```

2. **Server-side sends event to Facebook CAPI with `event_id`:**
   ```php
   yolo_analytics()->track_add_to_cart($yacht_id, $yacht_name, $price);
   // Returns $event_id
   ```

3. **Server-side returns `event_id` to JavaScript via AJAX:**
   ```php
   wp_send_json_success(array('event_id' => $event_id));
   ```

4. **Client-side sends same event to Facebook Pixel with same `event_id`:**
   ```javascript
   YoloAnalytics.trackSelectWeek(data, response.data.event_id);
   ```

5. **Facebook deduplicates the events:**
   - Facebook receives 2 events with same `event_id`
   - Facebook keeps the server-side event (more accurate attribution)
   - Facebook marks the event as "Deduplicated" in Events Manager

---

## 🧪 Testing Checklist

- [ ] Click "BOOK NOW" button → Check Facebook Events Manager for AddToCart event
- [ ] Submit booking form → Check Facebook Events Manager for InitiateCheckout event
- [ ] Submit quote request → Check Facebook Events Manager for Lead event
- [ ] Verify all events show "Deduplicated" badge in Events Manager
- [ ] Check browser console for event_id matching between CAPI and Pixel
- [ ] Verify GA4 dataLayer events are still working

---

## 📊 Expected Results in Facebook Events Manager

Each event should appear with:
- ✅ **"Deduplicated"** badge (indicates both server-side and client-side events were received)
- ✅ **Server-side** origin (Facebook prioritizes server-side for attribution)
- ✅ **User data** (email, phone, name) from server-side
- ✅ **Browser context** (user agent, referrer, fbp cookie) from client-side

---

## 🔄 Migration Notes

**From v60.6.3 to v60.6.4:**
- No database changes required
- No settings changes required
- JavaScript and CSS files will auto-update due to version bump
- Clear WordPress cache if using caching plugins

---

## 🐛 Bug Fixes

None in this release (pure feature addition).

---

## 📝 Notes

- **Event flow changed:** BOOK NOW button now triggers AddToCart (was InitiateCheckout)
- **Booking form submission** now triggers InitiateCheckout (was not tracked server-side)
- **Quote request** now sends Lead to both CAPI and Pixel (was only server-side)
- All events maintain backward compatibility with GA4 dataLayer tracking

---

## 🚀 Next Steps

1. Deploy to production
2. Monitor Facebook Events Manager for deduplication
3. Verify conversion tracking in Facebook Ads Manager
4. Test Facebook attribution with real conversions

---

**Version:** 60.6.4  
**Base Version:** 60.6.1 (text customization + no nested containers)  
**Previous Version:** 60.6.3 (ViewContent and Purchase deduplication)
