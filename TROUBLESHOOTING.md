# YOLO Yacht Search Plugin - Troubleshooting Guide

## Issue: Yacht Shows as Unavailable in Search (Despite Being Available in Booking Manager)

### Symptom
You've freed dates in Booking Manager, but when searching on the website, the yacht doesn't appear in results or shows as unavailable.

### Root Cause
The search function queries the **local WordPress database** (`yolo_yacht_prices` table), not the Booking Manager API in real-time. When you free dates in Booking Manager, the plugin's local database still contains old cached price data.

### Solution: Sync Weekly Offers

**Step 1:** Go to WordPress Admin → YOLO Yacht Search

**Step 2:** Click the **"Sync Weekly Offers"** button (blue button)

**Step 3:** Wait for the sync to complete (may take 2-3 minutes)

**Step 4:** Try searching again

### What Happens During Sync

```
1. DELETE all old prices for the year
   → DELETE FROM yolo_yacht_prices WHERE YEAR(date_from) = 2026

2. Fetch fresh offers from Booking Manager API
   → GET /offers?companyId=7850&dateFrom=2026-01-01&dateTo=2026-12-31

3. Store new prices in local database
   → INSERT INTO yolo_yacht_prices (yacht_id, date_from, date_to, price, currency...)
```

### How Search Works

```sql
-- Search queries the LOCAL database, not the API
SELECT y.id, y.name, p.price, p.date_from, p.date_to
FROM yolo_yachts y
INNER JOIN yolo_yacht_prices p ON y.id = p.yacht_id
WHERE p.date_from >= '2026-05-01' 
  AND p.date_from <= '2026-05-08'
ORDER BY y.company_id = 7850 DESC, p.price ASC
```

**Key Point:** If there's no matching price record in `yolo_yacht_prices`, the yacht won't appear in search results.

### When to Sync Weekly Offers

You should sync weekly offers whenever:
- ✅ You free up dates in Booking Manager
- ✅ You change prices in Booking Manager
- ✅ You add new availability windows
- ✅ You notice search results are outdated
- ✅ At least once per week (recommended)

### Automatic Sync (Future Enhancement)

Currently, syncing is **manual**. Future versions could implement:
- Scheduled daily/weekly auto-sync via WordPress cron
- Webhook from Booking Manager to trigger sync on changes
- Real-time API search (slower but always fresh)

---

## Issue: Sticky Booking Sidebar Not Working on Yacht Details Page

### Symptom
The booking sidebar on the yacht details page doesn't "stick" when scrolling down the page.

### Root Cause Analysis

The sticky sidebar CSS **IS properly implemented** in v2.7.13:

```css
@media (min-width: 1024px) {
    .yacht-booking-sidebar {
        position: sticky;
        top: 20px;
        max-height: calc(100vh - 40px);
    }
}
```

### Possible Causes

#### 1. Plugin Not Deployed to Live Site ⚠️

**Check:** Is the updated plugin file uploaded to your WordPress site?

**Solution:**
1. Download the latest plugin: `yolo-yacht-search-v2.7.13-FINAL.zip`
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload and activate
4. Clear browser cache (Ctrl+Shift+R)

#### 2. Browser Cache 🔄

**Check:** Hard refresh the page (Ctrl+Shift+R or Cmd+Shift+R on Mac)

**Solution:**
- Chrome: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- Firefox: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
- Safari: Cmd+Option+R

#### 3. Screen Width Too Narrow 📱

**Check:** Is your screen width at least 1024px?

The sticky sidebar **only works on desktop** (screens ≥1024px wide). On mobile and tablets, the booking section appears at the top of the page.

**Test:**
1. Open browser DevTools (F12)
2. Check screen width in bottom-right corner
3. If < 1024px, the sidebar won't be sticky (this is intentional)

#### 4. WordPress Theme CSS Conflict 🎨

**Check:** Is your theme overriding the `position: sticky` property?

**Diagnostic Test:**
1. Open browser DevTools (F12)
2. Right-click the booking sidebar → Inspect
3. Look for `.yacht-booking-sidebar` in the Styles panel
4. Check if `position: sticky` is crossed out (overridden)

**Solution if overridden:**

Add this to your theme's `style.css` or use a custom CSS plugin:

```css
/* Force sticky sidebar on yacht details */
@media (min-width: 1024px) {
    .yolo-yacht-details-v3 .yacht-booking-sidebar {
        position: sticky !important;
        top: 20px !important;
        max-height: calc(100vh - 40px) !important;
        overflow-y: auto !important;
    }
}
```

#### 5. Parent Container Overflow Issue 📦

**Check:** Does a parent container have `overflow: hidden` or `overflow: auto`?

`position: sticky` doesn't work if any parent element has `overflow` set to anything other than `visible`.

**Diagnostic Test:**
1. Open DevTools (F12)
2. Inspect the yacht details page
3. Check all parent containers of `.yacht-booking-sidebar`
4. Look for `overflow: hidden`, `overflow: auto`, or `overflow: scroll`

**Solution:**

If you find a parent with `overflow` set, you can try:

```css
/* Remove overflow from parent containers */
.yolo-yacht-details-v3,
.yacht-content-wrapper {
    overflow: visible !important;
}
```

### Verification Checklist

Use this checklist to verify the sticky sidebar is working:

- [ ] Plugin version is v2.7.13 or later
- [ ] Browser cache cleared (hard refresh)
- [ ] Screen width is ≥1024px
- [ ] DevTools shows `position: sticky` is active (not crossed out)
- [ ] No parent containers have `overflow: hidden/auto/scroll`
- [ ] Sidebar follows scroll when scrolling down the page

### Quick Test

**Expected Behavior:**
1. Open a yacht details page on desktop (≥1024px screen)
2. Scroll down the page
3. The booking sidebar (right side) should "stick" to the top and follow your scroll
4. The main content (left side) should scroll normally

**On Mobile/Tablet (<1024px):**
1. Booking section appears at the TOP of the page
2. Main content appears BELOW the booking section
3. No sticky behavior (intentional design)

---

## Issue: Search Results Not Loading

### Symptom
Search form submits but results don't appear, or you see "Loading..." forever.

### Diagnostic Steps

**1. Check Browser Console for Errors**
- Open DevTools (F12) → Console tab
- Submit search
- Look for red error messages

**2. Check AJAX Endpoint**
- The search uses AJAX: `wp-admin/admin-ajax.php?action=yolo_ys_search_yachts`
- Check Network tab in DevTools
- Look for failed requests (red)

**3. Check Database**
- Verify `yolo_yacht_prices` table has data
- Run in phpMyAdmin or Adminer:

```sql
SELECT COUNT(*) FROM wp_yolo_yacht_prices;
-- Should return > 0

SELECT * FROM wp_yolo_yacht_prices LIMIT 10;
-- Should show price records
```

### Common Fixes

**No price data:**
→ Sync Weekly Offers (blue button in admin)

**AJAX 500 error:**
→ Check WordPress error logs
→ Increase PHP memory limit in wp-config.php:
```php
define('WP_MEMORY_LIMIT', '256M');
```

**AJAX 404 error:**
→ Permalinks might be broken
→ Go to Settings → Permalinks → Save Changes

---

## Issue: Booking Confirmation Email Not Sent

### Symptom
Customer completes payment but doesn't receive booking confirmation email.

### Diagnostic Steps

**1. Check WordPress Mail Settings**
- Install "WP Mail SMTP" plugin
- Configure SMTP settings (Gmail, SendGrid, etc.)
- Send test email

**2. Check Spam Folder**
- WordPress default `wp_mail()` often goes to spam
- Use SMTP plugin for better deliverability

**3. Check Email Logs**
- Install "WP Mail Logging" plugin
- View all sent emails
- Check if email was sent but not delivered

### Solution

**Best Practice:** Use an SMTP plugin
1. Install "WP Mail SMTP" or "Easy WP SMTP"
2. Configure with your email provider
3. Test email delivery
4. Re-test booking flow

---

## Issue: Stripe Payment Fails

### Symptom
Customer clicks "Book Now" but payment page shows error.

### Diagnostic Steps

**1. Check Stripe Keys**
- Go to YOLO Yacht Search → Settings
- Verify Publishable Key and Secret Key are correct
- Make sure you're using the right mode (test vs. live)

**2. Check Stripe Webhook**
- Not required for basic functionality
- But recommended for production

**3. Check Browser Console**
- Open DevTools (F12) → Console
- Look for Stripe.js errors

### Common Fixes

**Invalid API Key:**
→ Double-check Stripe keys in settings
→ Make sure no extra spaces

**Stripe.js not loading:**
→ Check browser console for blocked scripts
→ Disable ad blockers

**Amount too low:**
→ Stripe requires minimum €0.50
→ Check yacht price is above minimum

---

## Issue: Guest User Cannot Login

### Symptom
Guest receives email with credentials but cannot log in.

### Diagnostic Steps

**1. Check Password Format**
- Default password: `[booking_id]YoLo`
- Example: If booking ID is 123, password is `123YoLo`
- Case-sensitive!

**2. Check Guest Dashboard Page**
- Must exist with slug: `guest-dashboard`
- Must contain shortcode: `[yolo_guest_dashboard]`

**3. Check User Role**
- User must have role: `guest`
- Check in WordPress Admin → Users

### Common Fixes

**Wrong password:**
→ Reset password in WordPress Admin → Users

**Page not found:**
→ Create page with slug `guest-dashboard`
→ Add shortcode `[yolo_guest_dashboard]`

**Redirected to wp-admin:**
→ Check guest dashboard page exists
→ Clear browser cache

---

## Getting Help

If you're still experiencing issues after trying these solutions:

1. **Check the CHANGELOG.md** for recent fixes
2. **Review the README.md** for setup instructions
3. **Check WordPress error logs** in wp-content/debug.log
4. **Enable WordPress debugging** in wp-config.php:
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
5. **Contact plugin developer** with:
   - Plugin version
   - WordPress version
   - PHP version
   - Error messages from logs
   - Steps to reproduce the issue

---

**Last Updated:** December 1, 2025  
**Plugin Version:** 2.7.13  
**Database Version:** 1.8
