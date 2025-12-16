# 🧪 Testing Guide - YOLO Yacht Search v60.6.4

**Version:** 60.6.4  
**Feature:** Facebook Event Tracking with Deduplication

---

## 🎯 What to Test

This release adds **3 new Facebook events** with proper deduplication:

1. **AddToCart** - Triggered when user clicks "BOOK NOW" button
2. **InitiateCheckout** - Triggered when user submits booking form
3. **Lead** - Triggered when user submits quote request

All events should appear in Facebook Events Manager with **"Deduplicated"** badge.

---

## 🔧 Setup Before Testing

### 1. Enable Facebook Test Events

1. Go to **Facebook Events Manager**: https://business.facebook.com/events_manager2
2. Click on your Pixel
3. Click **"Test Events"** tab
4. Keep this tab open during testing

### 2. Open Browser Developer Tools

1. Press **F12** (or **Cmd+Option+I** on Mac)
2. Go to **Network** tab
3. Filter by "XHR" or "Fetch"
4. Keep this open during testing

### 3. Open Browser Console

1. Press **F12** (or **Cmd+Option+I** on Mac)
2. Go to **Console** tab
3. Keep this open to see any errors

---

## ✅ Test 1: AddToCart Event (BOOK NOW Button)

### Steps

1. Go to a yacht details page (e.g., `/yacht-details/?yacht_id=123`)
2. Select dates from the calendar
3. Wait for price to load
4. Click **"BOOK NOW"** button

### Expected Results

#### In Browser Network Tab
- [ ] AJAX call to `admin-ajax.php` with `action=yolo_track_add_to_cart`
- [ ] Response contains `"success":true`
- [ ] Response contains `"event_id":"yolo_..."` (unique ID)

#### In Browser Console
- [ ] No JavaScript errors
- [ ] Facebook Pixel event logged: `fbq('track', 'AddToCart', {...})`

#### In Facebook Test Events
- [ ] Event **"AddToCart"** appears within 20 seconds
- [ ] Event shows **"Deduplicated"** badge (may take 1-2 minutes)
- [ ] Event shows **2 sources**: Browser + Server
- [ ] Event data includes:
  - `content_name`: Yacht name
  - `value`: Price
  - `currency`: EUR
  - `content_ids`: Yacht ID

### Troubleshooting

**Event not appearing?**
- Check browser console for errors
- Verify Facebook Pixel ID in WordPress settings
- Check PHP error logs for CAPI errors

**Event not deduplicated?**
- Wait 1-2 minutes (deduplication takes time)
- Check that `event_id` matches between server and client
- Verify both events have same timestamp (within 48 hours)

---

## ✅ Test 2: InitiateCheckout Event (Booking Form)

### Steps

1. Continue from Test 1 (after clicking BOOK NOW)
2. Fill in booking form:
   - First Name: Test
   - Last Name: User
   - Email: test@example.com
   - Phone: +1234567890
3. Click **"PROCEED TO PAYMENT"**

### Expected Results

#### In Browser Network Tab
- [ ] AJAX call to `admin-ajax.php` with `action=yolo_track_initiate_checkout`
- [ ] Response contains `"success":true`
- [ ] Response contains `"event_id":"yolo_..."` (unique ID)
- [ ] AJAX call to `admin-ajax.php` with `action=yolo_create_checkout_session` (Stripe)

#### In Browser Console
- [ ] No JavaScript errors
- [ ] Facebook Pixel event logged: `fbq('track', 'InitiateCheckout', {...})`

#### In Facebook Test Events
- [ ] Event **"InitiateCheckout"** appears within 20 seconds
- [ ] Event shows **"Deduplicated"** badge (may take 1-2 minutes)
- [ ] Event shows **2 sources**: Browser + Server
- [ ] Event data includes:
  - `content_name`: Yacht name
  - `value`: Price
  - `currency`: EUR
  - `content_ids`: Yacht ID

### Troubleshooting

**Form not submitting?**
- Check that all required fields are filled
- Check browser console for validation errors
- Verify Stripe publishable key is set in WordPress settings

**Event not appearing?**
- Same as Test 1 troubleshooting

---

## ✅ Test 3: Lead Event (Quote Request)

### Steps

1. Go to a yacht details page (e.g., `/yacht-details/?yacht_id=123`)
2. Scroll to **"Request a Quote"** section
3. Click **"Request a Quote"** to expand form
4. Fill in quote form:
   - First Name: Test
   - Last Name: User
   - Email: test@example.com
   - Phone: +1234567890
   - Special Requests: Test quote request
5. Click **"SEND REQUEST"**

### Expected Results

#### In Browser Network Tab
- [ ] AJAX call to `admin-ajax.php` with `action=yolo_submit_quote_request`
- [ ] Response contains `"success":true`
- [ ] Response contains `"event_id":"yolo_..."` (unique ID)
- [ ] Response contains `"quote_id":123` (database ID)

#### In Browser Console
- [ ] No JavaScript errors
- [ ] Facebook Pixel event logged: `fbq('track', 'Lead', {...})`
- [ ] Success toast notification appears

#### In Facebook Test Events
- [ ] Event **"Lead"** appears within 20 seconds
- [ ] Event shows **"Deduplicated"** badge (may take 1-2 minutes)
- [ ] Event shows **2 sources**: Browser + Server
- [ ] Event data includes:
  - `content_name`: Yacht name
  - `value`: 0 (no price for quote requests)
  - `currency`: EUR

#### In WordPress Admin
- [ ] Go to **YOLO → Quote Requests**
- [ ] New quote request appears with status "New"
- [ ] Quote request shows correct customer details

### Troubleshooting

**Form not submitting?**
- Check that all required fields are filled
- Check browser console for validation errors
- Verify nonce is valid (refresh page if expired)

**Event not appearing?**
- Same as Test 1 troubleshooting

**Quote not appearing in admin?**
- Check database table `wp_yolo_quote_requests`
- Check PHP error logs for database errors

---

## ✅ Test 4: Event Deduplication Verification

### Steps

1. Go to **Facebook Events Manager** → Your Pixel → **Overview**
2. Look at recent events (last hour)
3. Click on any event (AddToCart, InitiateCheckout, or Lead)

### Expected Results

- [ ] Event shows **"Deduplicated"** badge
- [ ] Event shows **2 sources**: "Browser" and "Server"
- [ ] Event Match Quality score is **7.0+** (improved from 5.0-6.0)
- [ ] Server event has more user data (IP, user agent, etc.)
- [ ] Browser event has fbp cookie and referrer

### What Deduplication Means

When you see **"Deduplicated"** badge:
- ✅ Facebook received the event from BOTH server and browser
- ✅ Facebook matched the events using `event_id`
- ✅ Facebook kept the server-side event (better attribution)
- ✅ Facebook discarded the browser-side event (to avoid duplication)
- ✅ You're only charged for 1 event (not 2)

---

## ✅ Test 5: GA4 Tracking (Verify Still Working)

### Steps

1. Go to **Google Analytics** → **Realtime** → **Events**
2. Perform actions on website (view yacht, click BOOK NOW, submit quote)

### Expected Results

- [ ] Events appear in GA4 Realtime
- [ ] Event names match:
  - `view_item` (yacht view)
  - `add_to_cart` (BOOK NOW click)
  - `begin_checkout` (booking form submit)
  - `generate_lead` (quote request)

---

## 📊 Success Criteria

All tests pass if:

- ✅ All 3 events appear in Facebook Test Events
- ✅ All 3 events show "Deduplicated" badge
- ✅ All 3 events show 2 sources (Browser + Server)
- ✅ Event Match Quality is 7.0+
- ✅ No JavaScript errors in browser console
- ✅ GA4 events still working

---

## 🐛 Common Issues

### Issue: Events appear but NOT deduplicated

**Cause:** Event ID not matching between server and client

**Solution:**
1. Open browser console → Network tab
2. Find AJAX call to tracking endpoint
3. Check response for `event_id`
4. Find Facebook Pixel call in Network tab
5. Verify `event_id` parameter matches
6. If not matching, check code in `yacht-details-v3-scripts.php`

### Issue: Events appear in Test Events but not in Overview

**Cause:** Test Events are separate from production events

**Solution:**
- Test Events are for testing only
- Production events appear in Overview after 15-30 minutes
- Use Test Events during development
- Use Overview for production monitoring

### Issue: Server-side events not appearing

**Cause:** Facebook Conversions API not configured

**Solution:**
1. Go to WordPress Admin → YOLO Settings
2. Check "Facebook Conversions API Access Token" is set
3. Generate new token if needed: https://business.facebook.com/events_manager2/list/pixel/settings/conversions_api
4. Copy token and paste in WordPress settings

### Issue: Event Match Quality low (< 7.0)

**Cause:** Missing user data in server-side events

**Solution:**
- For Lead and Purchase events, user data (email, phone, name) should be sent
- Check `class-yolo-ys-analytics.php` → `send_facebook_capi_event()` method
- Verify user data is being hashed and sent to Facebook

---

## 📞 Need Help?

If tests fail or you encounter issues:

1. **Check browser console** for JavaScript errors
2. **Check PHP error logs** for server-side errors
3. **Check Facebook Test Events** for event details
4. **Contact support** with screenshots and error messages

---

**Happy Testing! 🎉**

---

**Version:** 60.6.4  
**Last Updated:** December 16, 2024
