# YOLO Yacht Search Plugin v17.8.5 - Installation Guide

## 🚀 Quick Install

### Method 1: Direct Upload (Recommended)
1. Download `yolo-yacht-search-v17.8.5.zip` from GitHub release
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Click "Activate Plugin"
5. **Refresh your browser** to see Base Manager menu

### Method 2: Manual FTP Upload
1. Extract `yolo-yacht-search-v17.8.5.zip`
2. Upload the `LocalWP` folder to `/wp-content/plugins/`
3. Rename `LocalWP` to `yolo-yacht-search` (if needed)
4. Go to WordPress Admin → Plugins
5. Activate "YOLO Yacht Search & Booking"
6. **Refresh your browser** to see Base Manager menu

---

## ✅ What's Fixed in v17.8.5

### 1. Base Manager Menu Now Visible to Admins
**Before:** Administrators couldn't see Base Manager menu in wp-admin  
**After:** Administrators can now see and access all Base Manager features

**To Verify:**
- Log in as administrator
- Look for "Base Manager" menu item in wp-admin sidebar (with yacht icon)
- Click to access Dashboard, Yacht Management, Check-In, Check-Out, Warehouse

### 2. Quote Request Form Now Works
**Before:** Quote form did nothing when submitted  
**After:** Quote form submits successfully with success message

**To Verify:**
1. Go to any yacht detail page on your site
2. Click "REQUEST A QUOTE" button
3. Fill in the form (name, email, phone, special requests)
4. Click "Request a quote"
5. You should see: "✓ Quote request submitted successfully! We will contact you soon."
6. Go to wp-admin → Quote Requests to see the new quote

---

## 🔧 Troubleshooting

### Base Manager Menu Still Not Visible?
1. **Clear browser cache** (Ctrl+F5 or Cmd+Shift+R)
2. **Log out and log back in** to WordPress
3. **Verify you're logged in as Administrator** (not Editor or other role)
4. Check that plugin version shows **17.8.5** in Plugins page

### Quote Form Still Not Working?
1. **Clear browser cache** completely
2. **Check browser console** for JavaScript errors (F12 → Console tab)
3. **Verify plugin is active** and version is 17.8.5
4. **Test in incognito/private window** to rule out caching issues

### Database Table Missing?
If you see errors about missing tables, run this SQL in phpMyAdmin:

```sql
-- Check if tables exist
SHOW TABLES LIKE 'wp_yolo_%';

-- If missing, deactivate and reactivate the plugin to recreate tables
```

Then:
1. Go to Plugins page
2. Deactivate "YOLO Yacht Search & Booking"
3. Activate it again
4. Tables will be recreated automatically

---

## 📋 Post-Installation Checklist

### For Administrators:
- [ ] Base Manager menu visible in wp-admin sidebar
- [ ] Can access Base Manager Dashboard
- [ ] Can access Yacht Management
- [ ] Can access Check-In page
- [ ] Can access Check-Out page
- [ ] Can access Warehouse Management
- [ ] Can view Quote Requests page

### For Quote Form:
- [ ] Quote form appears when clicking "REQUEST A QUOTE"
- [ ] Form accepts all required fields
- [ ] Success message displays after submission
- [ ] Quote appears in wp-admin → Quote Requests
- [ ] Admin receives notification (check bell icon in admin bar)

### For Base Manager Role Users:
- [ ] Base Manager users can still log in
- [ ] Base Manager users see their custom dashboard
- [ ] Base Manager users can access yacht/warehouse/checkin/checkout
- [ ] Base Manager users cannot access Plugins/Themes/Settings (restricted)

---

## 🎯 Key Features Working

### Base Manager System (v17.0-v17.8.5)
✅ Yacht Management with equipment categories  
✅ Check-In process with digital signatures  
✅ Check-Out process with damage reporting  
✅ Warehouse inventory management  
✅ PDF generation with dual signatures  
✅ Guest dashboard integration  
✅ Booking calendar view  
✅ Admin access to all features  

### Quote Request System
✅ Quote form on yacht detail pages  
✅ In-house quote storage (no email required)  
✅ Admin notification system  
✅ Quote status management (New → Reviewed → Responded)  
✅ Internal notes for each quote  
✅ Spam protection with honeypot  

---

## 📞 Support

### Issues or Questions?
1. Check `CHANGELOG_v17.8.5.md` for technical details
2. Review `COMMON-ERRORS.md` for known issues and solutions
3. Check GitHub Issues: https://github.com/georgemargiolos/LocalWP/issues

### Need Help?
Create an issue on GitHub with:
- WordPress version
- PHP version
- Plugin version (should be 17.8.5)
- Description of the problem
- Screenshots if applicable
- Browser console errors (if any)

---

## 🔄 Upgrading from Previous Versions

### From v17.8.4 → v17.8.5
**No special steps required.** Just replace files and refresh browser.

### From v17.0-v17.8.3 → v17.8.5
**No database changes.** Just replace files and refresh browser.

### From v16.x → v17.8.5
**Major upgrade with new features:**
1. Backup your database before upgrading
2. Install v17.8.5
3. Plugin will automatically create new tables on activation
4. All existing data (yachts, bookings, quotes) will be preserved
5. New Base Manager features will be available immediately

---

## 📦 What's Included

### Core Files
- `yolo-yacht-search.php` - Main plugin file (v17.8.5)
- `includes/` - All PHP classes and handlers
- `admin/` - Admin interface templates
- `public/` - Public-facing templates and assets
- `vendor/` - FPDF library for PDF generation

### Documentation
- `CHANGELOG_v17.8.5.md` - Detailed changelog for this release
- `CHANGELOG_v17.8.md` - Full v17.x series changelog
- `HANDOFF_v17.8.md` - Technical specifications
- `COMMON-ERRORS.md` - Troubleshooting guide
- `INSTALL_v17.8.5.md` - This file

### Database Tables Created
- `wp_yolo_yachts` - Yacht inventory
- `wp_yolo_equipment_categories` - Equipment categories
- `wp_yolo_equipment_items` - Equipment items per yacht
- `wp_yolo_checkins` - Check-in records
- `wp_yolo_checkouts` - Check-out records
- `wp_yolo_warehouse` - Warehouse inventory
- `wp_yolo_quote_requests` - Quote requests

---

## ✨ Success!

If you can see the Base Manager menu and submit quote requests successfully, you're all set! 

The plugin is now fully operational with all v17.x features working correctly.

---

**Version:** 17.8.5  
**Release Date:** December 3, 2025  
**GitHub:** https://github.com/georgemargiolos/LocalWP  
**License:** GPL v2 or later
