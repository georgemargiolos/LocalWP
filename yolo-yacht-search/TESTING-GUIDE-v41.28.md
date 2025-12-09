# Testing Guide - YOLO Yacht Search v41.28
## Purchase Event Tracking Verification

---

## 🎯 What's New in v41.28

**Critical Fix:** Purchase event now fires on booking confirmation page for both GA4 and Facebook tracking.

**Previous Issue:** Purchase event was only in webhook handler (optional/not configured), so conversions weren't tracked.

**Solution:** Added Purchase tracking to confirmation page where booking is actually created.

---

## 🧪 Testing Checklist

### Pre-Test Setup

1. **Ensure GTM is in Preview Mode:**
   - Go to Google Tag Manager
   - Click "Preview" button
   - Enter your site URL
   - GTM debugger should appear at bottom of page

2. **Open Facebook Test Events:**
   - Go to Facebook Events Manager
   - Click "Test Events" tab
   - Should show "Waiting for activity..."

3. **Open Browser Console:**
   - Press F12 or right-click → Inspect
   - Go to "Console" tab
   - Clear console for clean test

4. **Have WordPress Debug Log Ready:**
   - SSH into server or use file manager
   - Open `/wp-content/debug.log`
   - Or enable debug logging in wp-config.php if not enabled

---

## 📋 Complete Booking Flow Test

### Step 1: Search Event
**Action:** Submit search form on homepage

**Expected Results:**
- ✅ GTM Preview shows "search" event in dataLayer
- ✅ Facebook Test Events shows "Search" event
- ✅ Console log: "YOLO Analytics: Search event tracked"

**Data to Verify:**
```javascript
{
  event: 'search',
  search_term: 'your search query'
}
```

---

### Step 2: View Item Event
**Action:** Click on a yacht to view details page

**Expected Results:**
- ✅ GTM Preview shows "view_item" event
- ✅ Facebook Test Events shows "ViewContent" event
- ✅ Console log: "YOLO Analytics: View Item event tracked"

**Data to Verify:**
```javascript
{
  event: 'view_item',
  currency: 'EUR',
  value: 1234.56,
  items: [{
    item_id: '123',
    item_name: 'Yacht Name',
    price: 1234.56
  }]
}
```

---

### Step 3: Add to Cart Event
**Action:** Select a week/price on yacht details page

**Expected Results:**
- ✅ GTM Preview shows "add_to_cart" event
- ✅ Facebook Test Events shows "AddToCart" event
- ✅ Console log: "YOLO Analytics: Add to Cart event tracked"

**Data to Verify:**
```javascript
{
  event: 'add_to_cart',
  currency: 'EUR',
  value: 1234.56,
  items: [{
    item_id: '123',
    item_name: 'Yacht Name',
    price: 1234.56
  }]
}
```

---

### Step 4: Begin Checkout Event
**Action:** Click "Book Now" button

**Expected Results:**
- ✅ GTM Preview shows "begin_checkout" event
- ✅ Facebook Test Events shows "InitiateCheckout" event
- ✅ Console log: "YOLO Analytics: Begin Checkout event tracked"

**Data to Verify:**
```javascript
{
  event: 'begin_checkout',
  currency: 'EUR',
  value: 1234.56,
  items: [{
    item_id: '123',
    item_name: 'Yacht Name',
    price: 1234.56
  }]
}
```

---

### Step 5: Add Payment Info Event
**Action:** Fill out booking form and click "Continue to Payment"

**Expected Results:**
- ✅ GTM Preview shows "add_payment_info" event
- ✅ Facebook Test Events shows "AddPaymentInfo" event
- ✅ Console log: "YOLO Analytics: Add Payment Info event tracked"

**Data to Verify:**
```javascript
{
  event: 'add_payment_info',
  currency: 'EUR',
  value: 1234.56,
  items: [{
    item_id: '123',
    item_name: 'Yacht Name',
    price: 1234.56
  }]
}
```

---

### Step 6: Complete Stripe Payment
**Action:** Enter Stripe test card and complete payment

**Test Card Details:**
- Card Number: `4242 4242 4242 4242`
- Expiry: Any future date (e.g., 12/25)
- CVC: Any 3 digits (e.g., 123)
- ZIP: Any 5 digits (e.g., 12345)

**Expected Results:**
- ✅ Stripe payment succeeds
- ✅ Redirected to booking confirmation page
- ✅ Loading spinner appears briefly

---

### Step 7: 🎯 Purchase Event (CRITICAL TEST)
**Action:** Wait for confirmation page to load completely

**Expected Results:**

#### A. Client-Side GA4 (GTM)
- ✅ GTM Preview shows "purchase" event in dataLayer
- ✅ Event data includes:
  ```javascript
  {
    event: 'purchase',
    transaction_id: 'cs_test_...' or 'booking-123',
    currency: 'EUR',
    value: 1234.56,
    items: [{
      item_id: '123',
      item_name: 'Yacht Name',
      price: 1234.56,
      quantity: 1
    }]
  }
  ```

#### B. Client-Side Facebook Pixel
- ✅ Console log: "YOLO Analytics: Purchase event tracked (Facebook Pixel)"
- ✅ Facebook Test Events shows "Purchase" event with:
  - Event Source: **Browser**
  - content_type: 'product'
  - content_ids: ['123']
  - content_name: 'Yacht Name'
  - currency: 'EUR'
  - value: 1234.56
  - order_id: 'cs_test_...' or 'booking-123'
  - eventID: 'purchase_123_timestamp'

#### C. Server-Side Facebook CAPI
- ✅ Facebook Test Events shows "Purchase" event with:
  - Event Source: **Server**
  - Same data as client-side
  - Deduplication Status: **Matched** (if both fired)
- ✅ WordPress debug.log shows:
  ```
  YOLO YS: Purchase event tracked via CAPI for booking #123
  ```

#### D. Confirmation Page Display
- ✅ Booking details shown correctly
- ✅ Yacht name, dates, prices displayed
- ✅ Booking reference number shown
- ✅ "Booking Confirmed!" message visible

---

## 🔍 Troubleshooting

### Purchase Event Not Firing

**Check 1: Booking Created Successfully?**
- Look for booking in WordPress admin → YOLO Bookings
- If no booking, check Stripe session ID in URL
- Check WordPress debug.log for errors

**Check 2: GTM Container Loaded?**
```javascript
// In browser console:
console.log(window.dataLayer);
// Should show array of events
```

**Check 3: Facebook Pixel Loaded?**
```javascript
// In browser console:
console.log(typeof fbq);
// Should show "function"
```

**Check 4: Analytics Class Available?**
```php
// Check WordPress debug.log for:
"YOLO YS: Purchase event tracked via CAPI for booking #123"
// If missing, analytics class may not be loaded
```

---

### Facebook Events Not Matching

**Issue:** Two separate Purchase events instead of one matched event

**Cause:** Event deduplication not working

**Solution:**
- Check that eventID is included in client-side call
- Check that event_id is generated in CAPI call
- Verify both use same format (should match automatically)

**Expected in Facebook Test Events:**
```
Purchase (Matched)
├── Browser: 1 event
└── Server: 1 event
```

**Not Expected:**
```
Purchase (Browser): 1 event
Purchase (Server): 1 event  ← Separate events = problem
```

---

### GTM Purchase Tag Not Firing

**Check 1: Trigger Configured?**
- GTM → Triggers → "Custom Event - purchase"
- Event name should be: `purchase`

**Check 2: Tag Configured?**
- GTM → Tags → "GA4 Event - Purchase"
- Trigger should be: "Custom Event - purchase"
- Configuration Type: Google Analytics: GA4 Event
- Event Name: `purchase`

**Check 3: Variables Mapped?**
- currency → {{DLV - currency}}
- value → {{DLV - value}}
- items → {{DLV - items}}
- transaction_id → {{DLV - transaction_id}}

---

## 📊 Success Criteria

### ✅ All 7 Events Tracked

| Event | GA4 | Facebook Pixel | Facebook CAPI |
|-------|-----|----------------|---------------|
| search | ✅ | ✅ | ✅ |
| view_item | ✅ | ✅ | ✅ |
| add_to_cart | ✅ | ✅ | ✅ |
| begin_checkout | ✅ | ✅ | ✅ |
| add_payment_info | ✅ | ✅ | ✅ |
| generate_lead | N/A | ✅ | ✅ |
| **purchase** | ✅ | ✅ | ✅ |

### ✅ Purchase Event Specifically

1. **GA4 (via GTM):**
   - Event appears in GTM Preview dataLayer
   - Purchase tag fires successfully
   - transaction_id, currency, value, items all populated

2. **Facebook Pixel (client-side):**
   - Purchase event in Test Events with "Browser" source
   - All parameters (content_ids, value, currency, order_id) present
   - eventID included for deduplication

3. **Facebook CAPI (server-side):**
   - Purchase event in Test Events with "Server" source
   - User data (email, phone, name) included
   - Deduplication shows "Matched" with Pixel event

4. **WordPress Logs:**
   - "Purchase event tracked via CAPI for booking #X" logged
   - No PHP errors or warnings

---

## 🎯 Final Verification

### In GA4 (after 24-48 hours)
1. Go to GA4 → Reports → Monetization → Ecommerce purchases
2. Should see test purchase with:
   - Transaction ID
   - Revenue amount
   - Item name (yacht)

### In Facebook Ads Manager (after 24-48 hours)
1. Go to Ads Manager → Events Manager
2. Click on your Pixel
3. Go to "Overview" tab
4. Should see Purchase events in chart
5. Check "Event Source Groups" shows both Browser and Server

---

## 📝 Test Results Template

```
YOLO Yacht Search v41.28 - Purchase Event Test Results
Date: ___________
Tester: ___________

✅ = Pass | ❌ = Fail | ⚠️ = Partial

[ ] Search event tracked (GA4 + Facebook)
[ ] View Item event tracked (GA4 + Facebook)
[ ] Add to Cart event tracked (GA4 + Facebook)
[ ] Begin Checkout event tracked (GA4 + Facebook)
[ ] Add Payment Info event tracked (GA4 + Facebook)
[ ] Generate Lead event tracked (Facebook only)
[ ] PURCHASE event tracked (GA4) - CLIENT SIDE
[ ] PURCHASE event tracked (Facebook Pixel) - CLIENT SIDE
[ ] PURCHASE event tracked (Facebook CAPI) - SERVER SIDE
[ ] Purchase events deduplicated in Facebook (Matched status)
[ ] WordPress debug.log shows CAPI Purchase tracking
[ ] Booking created successfully in database
[ ] Confirmation page displays correctly

Notes:
_________________________________
_________________________________
_________________________________

Overall Result: [ ] PASS [ ] FAIL
```

---

## 🚀 Production Deployment

Once testing is complete and all events fire correctly:

1. **Backup Current Plugin:**
   - Download current version from production
   - Keep as rollback option

2. **Upload v41.28:**
   - WordPress admin → Plugins → Add New → Upload
   - Select `yolo-yacht-search-v41.28.zip`
   - Click "Install Now"

3. **Activate/Update:**
   - Click "Activate Plugin" (or "Replace current with uploaded")

4. **Verify in Production:**
   - Complete one test booking with Stripe test mode
   - Check Facebook Test Events
   - Check GTM Preview Mode
   - Verify Purchase event fires

5. **Monitor for 48 Hours:**
   - Check GA4 for Purchase events
   - Check Facebook Events Manager
   - Check WordPress error logs
   - Monitor real bookings

---

**Version:** 41.28  
**Critical Fix:** Purchase event tracking on confirmation page  
**Test Focus:** Complete booking flow with Purchase event verification
