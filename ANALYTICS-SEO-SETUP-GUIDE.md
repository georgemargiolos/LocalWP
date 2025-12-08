# YOLO Yacht Search - Analytics & SEO Setup Guide
**Version:** 41.19  
**Date:** December 9, 2025

---

## 📋 WHAT'S INCLUDED

Your plugin now has a complete analytics and SEO system:

✅ **Google Analytics 4** - Track full booking funnel  
✅ **Facebook Pixel** - Track conversions and retarget  
✅ **Server-side tracking** - Bypass ad blockers for accurate purchase data  
✅ **Open Graph tags** - Beautiful social media sharing  
✅ **Twitter Cards** - Optimized Twitter sharing  
✅ **Schema.org** - Rich Google search results  

---

## ⚙️ SETUP INSTRUCTIONS

### Step 1: Configure Settings

Go to **WordPress Admin → YOLO Yacht Search → Settings**

Scroll to the new **"Analytics & SEO Settings"** section.

### Step 2: Google Analytics 4 (Optional but Recommended)

1. Go to [Google Analytics](https://analytics.google.com)
2. Create a GA4 property if you don't have one
3. Go to **Admin → Data Streams → Web**
4. Copy your **Measurement ID** (looks like `G-XXXXXXXXXX`)
5. Paste it in the **"GA4 Measurement ID"** field

**For server-side purchase tracking (bypasses ad blockers):**
1. In GA4, go to **Admin → Data Streams → Measurement Protocol API secrets**
2. Click **Create** and give it a name
3. Copy the **Secret Value**
4. Paste it in the **"GA4 API Secret"** field

### Step 3: Facebook Pixel (Optional but Recommended)

1. Go to [Facebook Events Manager](https://business.facebook.com/events_manager2)
2. Create a Pixel if you don't have one
3. Copy your **Pixel ID** (15-16 digits)
4. Paste it in the **"Facebook Pixel ID"** field

**For server-side purchase tracking (bypasses ad blockers):**
1. In Events Manager, go to **Settings → Conversions API**
2. Click **Generate Access Token**
3. Copy the token (starts with `EAA...`)
4. Paste it in the **"Facebook Access Token"** field

### Step 4: Social Media Sharing (Optional)

**Default Image:**
- Upload a default image (1200x630px recommended)
- Paste the URL in **"Default Open Graph Image URL"**
- This image shows when sharing pages without yacht images

**Twitter Handle:**
- Enter your Twitter/X handle (e.g., `@YOLOCharters`)
- Paste it in **"Twitter Handle"** field

### Step 5: Schema.org (Recommended - Already Enabled)

✅ **"Enable Schema.org Structured Data"** is checked by default.

This adds structured data to yacht pages for:
- Rich Google search results
- Better SEO
- Product information display

### Step 6: Debug Mode (For Testing Only)

Check **"Enable Analytics Debug Mode"** to:
- See analytics events in browser console
- See server-side logs in WordPress error log

**Important:** Uncheck this in production!

---

## 🧪 TESTING

### Test Client-Side Tracking

1. Install browser extensions:
   - [Facebook Pixel Helper](https://chrome.google.com/webstore/detail/facebook-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc)
   - [Google Tag Assistant](https://tagassistant.google.com/)

2. Enable **Debug Mode** in settings

3. Open browser console (F12)

4. Test each action:

| Action | Expected Console Log | Expected GA4 | Expected FB |
|--------|---------------------|--------------|-------------|
| Open yacht page | `[YOLO Analytics] GA4: view_item` | ✅ | ✅ |
| Click week in carousel | `[YOLO Analytics] GA4: add_to_cart` | ✅ | ✅ |
| Click "Book Now" | `[YOLO Analytics] GA4: begin_checkout` | ✅ | ✅ |
| Submit booking form | `[YOLO Analytics] GA4: add_payment_info` | ✅ | ✅ |
| Complete payment | (server-side, check logs) | ✅ | ✅ |

### Test Social Sharing

1. Go to a yacht details page
2. Copy the URL
3. Test with:
   - [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
   - [Twitter Card Validator](https://cards-dev.twitter.com/validator)

You should see:
- Yacht name and model as title
- Yacht description
- Yacht image (or default image)
- Price information

### Test Schema.org

1. Go to a yacht details page
2. Copy the URL
3. Test with [Google Rich Results Test](https://search.google.com/test/rich-results)

You should see:
- Product schema detected
- Yacht information
- Price and availability

---

## 📊 TRACKED EVENTS

### Funnel Tracking

The system tracks the complete booking funnel:

1. **Search** → User searches for yachts
2. **View Yacht** → User opens yacht details
3. **Add to Cart** → User selects dates/week
4. **Begin Checkout** → User clicks "Book Now"
5. **Add Payment Info** → User submits booking form
6. **Purchase** → User completes Stripe payment

### Additional Events

- **Quote Request** → User submits quote form (tracked as Lead)

---

## 💰 COSTS

**Everything is FREE:**
- Google Analytics 4: FREE
- Facebook Pixel: FREE
- Facebook Conversions API: FREE
- GA4 Measurement Protocol: FREE
- Open Graph tags: FREE
- Twitter Cards: FREE
- Schema.org: FREE

**Total monthly cost: $0**

---

## 🔒 PRIVACY & GDPR

**What's tracked:**
- Page views
- Button clicks
- Form submissions
- Purchase events

**Personal data handling:**
- Email and phone are hashed (SHA-256) before sending to Facebook
- No personal data stored in analytics
- IP addresses handled by GA4/Facebook privacy settings

**GDPR compliance:**
- Consider adding cookie consent banner
- Update privacy policy to mention analytics
- Provide opt-out mechanism if required

---

## 🐛 TROUBLESHOOTING

### Analytics not tracking

1. Check browser console for errors
2. Verify IDs are correct (no spaces, correct format)
3. Check if ad blocker is enabled (client-side will be blocked)
4. Enable Debug Mode and check console logs

### Social sharing not working

1. Clear Facebook/Twitter cache:
   - [Facebook Sharing Debugger](https://developers.facebook.com/tools/debug/)
   - Click "Scrape Again"
2. Check if yacht has image
3. Verify Default OG Image URL is valid

### Schema.org not detected

1. Check if Schema is enabled in settings
2. Test with [Google Rich Results Test](https://search.google.com/test/rich-results)
3. Verify yacht page has valid data

### Server-side tracking not working

1. Check WordPress error log
2. Verify API secrets/tokens are correct
3. Test Stripe webhook is working
4. Enable Debug Mode and check logs

---

## 📝 NEXT STEPS

1. **Configure settings** (at minimum: GA4 ID and FB Pixel ID)
2. **Test tracking** with browser extensions
3. **Disable Debug Mode** in production
4. **Monitor analytics** in GA4 and Facebook Events Manager
5. **Optimize campaigns** based on funnel data

---

## 🎯 BENEFITS

**Marketing:**
- Track ROI of ad campaigns
- Retarget users who viewed yachts
- Optimize conversion funnel
- Measure booking success rate

**SEO:**
- Better Google rankings with structured data
- Rich search results with yacht info
- Improved click-through rates

**Social:**
- Beautiful yacht sharing on Facebook/Twitter
- Automatic image and description
- Professional appearance

---

**Questions?** Check the implementation guide or WordPress error logs with Debug Mode enabled.

**Version:** 41.19  
**Implementation Date:** December 9, 2025
