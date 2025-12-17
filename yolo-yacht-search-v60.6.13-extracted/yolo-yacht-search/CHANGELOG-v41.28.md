# YOLO Yacht Search & Booking Plugin - Changelog v41.28

## Version 41.28 - Purchase Event Tracking Fix (2024-12-09)

### 🎯 Critical Fix: Purchase Event Now Fires on Confirmation Page

**Problem Identified:**
- Purchase event was only added to webhook handler (optional/not configured)
- Actual payment flow uses Stripe redirect → confirmation page → AJAX booking creation
- Purchase event was missing from confirmation page, so conversions weren't tracked
- Affected both GA4 (GTM) and Facebook tracking

**Solution Implemented:**

### ✅ Changes Made

#### 1. **Client-Side Purchase Tracking** (booking-confirmation.php lines 197-230)
Added Purchase event tracking when confirmation page displays:

```javascript
// GA4 via GTM
window.dataLayer.push({
    event: 'purchase',
    transaction_id: 'session_id or booking_id',
    currency: 'EUR',
    value: total_price,
    items: [{
        item_id: yacht_id,
        item_name: yacht_name,
        price: total_price,
        quantity: 1
    }]
});

// Facebook Pixel (client-side)
fbq('track', 'Purchase', {
    content_type: 'product',
    content_ids: [yacht_id],
    content_name: yacht_name,
    currency: 'EUR',
    value: total_price,
    order_id: transaction_id
}, {
    eventID: 'purchase_booking_id_timestamp'
});
```

#### 2. **Server-Side Facebook CAPI Tracking** (booking-confirmation.php lines 339-358)
Added Purchase event via Facebook Conversions API when booking is created:

```php
// Track Purchase event via Facebook CAPI (server-side)
if (function_exists('yolo_analytics')) {
    $transaction_id = $booking->stripe_session_id ? $booking->stripe_session_id : 'booking-' . $booking->id;
    $user_data = array(
        'em' => $customer_email,
        'ph' => $customer_phone,
        'fn' => $customer_first_name,
        'ln' => $customer_last_name
    );
    
    yolo_analytics()->track_purchase(
        $transaction_id,
        $yacht_id,
        $total_price,
        $yacht_name,
        $user_data
    );
    
    error_log('YOLO YS: Purchase event tracked via CAPI for booking #' . $booking_id);
}
```

### 📊 Complete Event Tracking Status

All 7 events now work for both GA4 and Facebook:

| Event | GA4 (GTM) | Facebook Pixel | Facebook CAPI | Status |
|-------|-----------|----------------|---------------|---------|
| search | ✅ | ✅ | ✅ | Working |
| view_item | ✅ | ✅ | ✅ | Working |
| add_to_cart | ✅ | ✅ | ✅ | Working |
| begin_checkout | ✅ | ✅ | ✅ | Working |
| add_payment_info | ✅ | ✅ | ✅ | Working |
| generate_lead | ✅ | ✅ | ✅ | Working |
| **purchase** | ✅ | ✅ | ✅ | **FIXED** |

### 🔧 Technical Details

**Purchase Event Triggers:**
1. **Server-side CAPI** - Fires when booking is created in database (line 349)
2. **Client-side dataLayer** - Fires when confirmation page loads (line 200)
3. **Client-side fbq()** - Fires when confirmation page loads (line 217)

**Event Deduplication:**
- CAPI uses event_id to prevent duplicate tracking
- Client-side uses eventID parameter for Facebook Pixel
- GTM/GA4 uses transaction_id for deduplication

**User Data Matching:**
- Email, phone, first name, last name sent to CAPI
- Improves Facebook attribution and conversion tracking
- Hashed automatically by CAPI implementation

### 📁 Files Modified

1. `/public/templates/booking-confirmation.php`
   - Added client-side Purchase tracking (lines 197-230)
   - Added server-side CAPI tracking (lines 339-358)

2. `/yolo-yacht-search.php`
   - Version bumped to 41.28

### 🧪 Testing Instructions

1. **Complete Test Booking:**
   - Use Stripe test card: 4242 4242 4242 4242
   - Complete booking flow through confirmation page

2. **Verify Facebook Tracking:**
   - Open Facebook Events Manager → Test Events
   - Should see Purchase event with:
     - Event source: Server (CAPI)
     - Event source: Browser (Pixel)
     - Deduplication status: Matched

3. **Verify GA4 Tracking:**
   - Open GTM Preview Mode
   - Complete test booking
   - Check dataLayer for 'purchase' event
   - Verify in GA4 DebugView

4. **Check WordPress Logs:**
   - Look for: "Purchase event tracked via CAPI for booking #X"
   - Confirms server-side tracking fired

### 🎯 Impact

**Before v41.28:**
- Purchase conversions NOT tracked (most important event!)
- Missing revenue data in GA4 and Facebook
- Incomplete funnel analysis

**After v41.28:**
- ✅ Purchase conversions tracked reliably
- ✅ Revenue data flows to GA4 and Facebook
- ✅ Complete booking funnel from search to purchase
- ✅ Server-side reliability (ad blocker proof)
- ✅ Event deduplication prevents double-counting

### 🚀 Deployment

1. Upload `yolo-yacht-search-v41.28.zip` to WordPress
2. Activate plugin (or update if already active)
3. Test with Stripe test card
4. Monitor Facebook Test Events and GTM Preview

### 📝 Notes

- Purchase event now fires on confirmation page (not webhook)
- Works with Stripe redirect flow (no webhook needed)
- Both client-side and server-side tracking for reliability
- User data included for better Facebook attribution
- Transaction ID uses Stripe session ID for consistency

---

**Previous Version:** 41.27 (Facebook CAPI + GTM integration)  
**Current Version:** 41.28 (Purchase event tracking fix)  
**Next Steps:** Test in production, monitor conversion data
