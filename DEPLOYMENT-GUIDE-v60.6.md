# YOLO Yacht Search Plugin v60.6 - Deployment Guide

**Version**: 60.6  
**Release Date**: December 12, 2025  
**Status**: Ready for Production

---

## 📦 What's Included

This release includes:

1. **Critical Bug Fix (v60.5)**: Search results container width now displays consistently at full width regardless of boat count
2. **Complete Text Customization (v60.6)**: 100% of frontend texts are now customizable through admin interface

---

## 🚀 Deployment Steps

### Step 1: Backup Current Version

Before deploying, backup your current plugin:

```bash
# Via WordPress Admin
Plugins → Installed Plugins → YOLO Yacht Search & Booking → Deactivate
Download current version via FTP/cPanel

# Or via command line
cd /path/to/wordpress/wp-content/plugins/
zip -r yolo-yacht-search-backup-$(date +%Y%m%d).zip yolo-yacht-search/
```

### Step 2: Upload New Version

**Option A: Via WordPress Admin (Recommended)**
1. Go to **Plugins → Add New → Upload Plugin**
2. Choose `yolo-yacht-search-v60.6.zip`
3. Click **Install Now**
4. Click **Activate Plugin**

**Option B: Via FTP/cPanel**
1. Delete old `yolo-yacht-search` folder
2. Upload and extract `yolo-yacht-search-v60.6.zip`
3. Go to **Plugins** and activate

**Option C: Via Command Line**
```bash
cd /path/to/wordpress/wp-content/plugins/
rm -rf yolo-yacht-search/
unzip yolo-yacht-search-v60.6.zip
chown -R www-data:www-data yolo-yacht-search/
```

### Step 3: Clear Caches

**WordPress Cache:**
- If using caching plugin (WP Super Cache, W3 Total Cache, etc.), clear all caches
- Go to plugin settings → Clear/Purge cache

**Browser Cache:**
- Hard refresh: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
- Or clear browser cache completely

**CDN Cache (if applicable):**
- Cloudflare: Purge Everything
- Other CDN: Clear cache according to provider

### Step 4: Verify Installation

1. Go to **Plugins → Installed Plugins**
2. Find **YOLO Yacht Search & Booking**
3. Verify version shows **60.6**

---

## ✅ Post-Deployment Testing

### Critical Tests (Must Pass)

**Search Results Width Bug Fix:**
- [ ] Navigate to search results page
- [ ] Test with 1 YOLO boat → Should be **full width** (1264px)
- [ ] Test with 3 YOLO boats → Should be **full width** (1264px)
- [ ] Test with 0 YOLO boats + partner boats → Should be **full width** (1264px)
- [ ] Test on mobile device → Should be **edge-to-edge**
- [ ] Test on tablet → Should be **responsive**

**Text Customization:**
- [ ] Go to **YOLO Yacht Search → Texts**
- [ ] Find **"Remaining Label (in price box)"** field
- [ ] Change to custom text (e.g., "Balance:")
- [ ] Save settings
- [ ] Visit yacht details page
- [ ] Select dates to see price breakdown
- [ ] Verify custom text appears instead of "Remaining:"

**CANCEL Button Customization:**
- [ ] Go to **YOLO Yacht Search → Texts**
- [ ] Find **"Cancel Button"** field
- [ ] Change to custom text (e.g., "Close")
- [ ] Save settings
- [ ] Visit yacht details page
- [ ] Click "Request a Quote" or "Book Now"
- [ ] Verify custom text appears on modal cancel button

### Regression Tests (Ensure Nothing Broke)

**Core Functionality:**
- [ ] Search widget works correctly
- [ ] Search results display properly
- [ ] Yacht details page loads correctly
- [ ] Booking flow works end-to-end
- [ ] Payment processing works (test mode)
- [ ] Guest dashboard accessible
- [ ] Email notifications sent

**Responsive Design:**
- [ ] Test on mobile (< 576px)
- [ ] Test on tablet (576px - 991px)
- [ ] Test on desktop (≥ 992px)
- [ ] No horizontal scrolling
- [ ] All buttons clickable
- [ ] All forms usable

---

## 🐛 Troubleshooting

### Issue: Version Still Shows Old Number

**Solution:**
1. Deactivate plugin
2. Reactivate plugin
3. Clear WordPress object cache
4. Hard refresh browser

### Issue: Search Results Still Constrained

**Solution:**
1. Clear all caches (WordPress + browser + CDN)
2. Check browser console for CSS errors
3. Verify `bootstrap-mobile-fixes.css` loaded
4. Verify `search-results.css` loaded AFTER bootstrap-mobile-fixes.css

### Issue: Custom Texts Not Appearing

**Solution:**
1. Verify settings saved (check WordPress options table)
2. Clear all caches
3. Check browser console for JavaScript errors
4. Verify `yacht-details-v3-scripts.php` loaded correctly

### Issue: Plugin Won't Activate

**Solution:**
1. Check PHP version (requires 7.4+)
2. Check WordPress version (requires 5.8+)
3. Check server error logs
4. Verify file permissions (755 for folders, 644 for files)

---

## 📋 Rollback Procedure

If you encounter critical issues:

**Step 1: Deactivate v60.6**
```bash
cd /path/to/wordpress/wp-content/plugins/
mv yolo-yacht-search yolo-yacht-search-v60.6-broken
```

**Step 2: Restore Backup**
```bash
unzip yolo-yacht-search-backup-YYYYMMDD.zip
```

**Step 3: Reactivate**
- Go to **Plugins** and activate the restored version

**Step 4: Clear Caches**
- Clear all caches as described above

**Step 5: Report Issue**
- Document the error
- Check error logs
- Contact developer with details

---

## 🔧 Configuration

### New Settings Available

After deployment, you can customize these new texts:

**Admin → YOLO Yacht Search → Texts → Additional UI Elements:**

1. **Cancel Button** (default: "CANCEL")
   - Used in modal dialogs
   - Appears in custom dates modal and booking form modal

2. **Free Label** (default: "Free")
   - Used for free extras pricing
   - Appears in extras section

3. **Catamaran** (default: "Catamaran")
   - Used in boat type dropdown
   - Appears in search widget and search results

4. **Message Placeholder** (default: "Message")
   - Used in form placeholders
   - Appears in quote request forms

**Admin → YOLO Yacht Search → Texts → Booking Section:**

5. **Remaining Label** (default: "Remaining:")
   - Used in yacht details price box
   - Appears when showing deposit breakdown

---

## 📊 What Changed

### Files Modified

**Core Plugin:**
- `yolo-yacht-search.php` - Version bump to 60.6

**Admin:**
- `admin/partials/texts-page.php` - Added 5 new text settings

**Frontend:**
- `public/class-yolo-ys-public.php` - CSS dependency fix (v60.5)
- `public/css/bootstrap-mobile-fixes.css` - Removed conflicting max-width (v60.5)
- `public/templates/partials/yacht-details-v3-scripts.php` - Use customizable texts

**Documentation:**
- `CHANGELOG-v60.5.md` - CSS load order fix documentation
- `CHANGELOG-v60.6.md` - Text customization documentation

### Database Changes

**None** - This is a code-only update. No database migrations required.

### Settings Changes

**New Options Added:**
- `yolo_ys_text_remaining` (default: "Remaining:")
- `yolo_ys_text_cancel` (default: "CANCEL")
- `yolo_ys_text_free` (default: "Free")
- `yolo_ys_text_catamaran` (default: "Catamaran")
- `yolo_ys_text_message_placeholder` (default: "Message")

All have sensible defaults, so no action required unless you want to customize.

---

## 🎯 Next Steps After Deployment

1. **Test thoroughly** using the checklist above
2. **Customize texts** if needed for your market/language
3. **Monitor** for any issues in the first 24-48 hours
4. **Update documentation** if you've customized texts
5. **Train staff** on new text customization features

---

## 📞 Support

If you encounter any issues:

1. Check this deployment guide
2. Review CHANGELOG-v60.6.md for technical details
3. Check error logs (WordPress debug.log, server error logs)
4. Contact developer with:
   - Error message
   - Steps to reproduce
   - Browser/device information
   - Screenshots if applicable

---

**Deployment prepared by**: Manus AI Agent  
**Last updated**: December 12, 2025  
**Version**: 60.6
