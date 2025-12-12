# CHANGELOG v60.6

**Release Date**: December 12, 2025  
**Status**: Ready for Production

## ✨ NEW FEATURE: Customizable "Remaining:" Text

### Problem
The "Remaining:" label in the yacht details price box (showing remaining balance after deposit) was hardcoded in the JavaScript, making it impossible to customize or translate.

### Solution
Added new customizable text setting for the "Remaining:" label:

**Files Changed:**

1. **admin/partials/texts-page.php**:
   - Added `yolo_ys_text_remaining` field to save handler (line 24)
   - Added default value `'Remaining:'` to settings array (line 134)
   - Added form field in Booking Section of admin page (lines 268-271)

2. **public/templates/partials/yacht-details-v3-scripts.php**:
   - Added PHP variable to pass customizable text to JavaScript (line 846)
   - Updated deposit info display to use customizable text instead of hardcoded "Remaining:" (line 887)

### How to Use

1. Go to **WordPress Admin → YOLO Yacht Search → Texts**
2. Find the **Booking Section**
3. Edit the **"Remaining Label (in price box)"** field
4. Default value: `Remaining:`
5. Save changes

The text will now appear in the yacht details price box when showing deposit breakdown.

### Example Use Cases
- Change to `"Balance:"` for different terminology
- Translate to other languages: `"Restante:"` (Spanish), `"Solde:"` (French), etc.
- Add custom formatting: `"Remaining Balance:"`, `"Still to Pay:"`, etc.

---

## 📋 Includes All v60.5 Fixes

This version includes the critical CSS load order fix from v60.5:
- Fixed search results container width bug (1264px vs 943px issue)
- Proper CSS dependency chain in `wp_enqueue_style()`
- Removed conflicting `max-width: 100%` rule

---

## 📝 Files Changed

### Modified Files
1. `yolo-yacht-search.php` - Version bump to 60.6
2. `admin/partials/texts-page.php` - Added "Remaining:" text customization
3. `public/templates/partials/yacht-details-v3-scripts.php` - Use customizable text in JavaScript

### Previous v60.5 Files
4. `public/class-yolo-ys-public.php` - CSS dependency fix (from v60.5)
5. `public/css/bootstrap-mobile-fixes.css` - Removed conflicting rule (from v60.5)

---

## 🧪 Testing Required

### Text Customization Testing
- [ ] Go to Texts settings page
- [ ] Change "Remaining Label (in price box)" to custom text
- [ ] Save settings
- [ ] Visit yacht details page
- [ ] Select dates to see price breakdown
- [ ] Verify custom text appears in deposit info box

### Regression Testing (v60.5 fixes)
- [ ] Search results page displays full width with 1 boat
- [ ] Search results page displays full width with 3 boats
- [ ] No layout breaks on mobile/tablet/desktop

---

## 📋 Deployment Notes

**Upgrade Path**: Direct upgrade from v60.0-60.5
- No database changes
- New settings field added (with default value)
- No template changes required
- CSS cache clear recommended

**Rollback**: Can safely rollback to v60.5 if issues occur

---

## 🔄 Version History

- **v60.0**: Image optimization during yacht sync
- **v60.5**: CSS load order fix for search results container width bug
- **v60.6**: Customizable "Remaining:" text (THIS VERSION)

---

## 🎯 Next Steps

After deployment and testing of v60.6:
1. Comprehensive audit of remaining hardcoded texts (29+ more identified)
2. Create settings fields for all customizable texts
3. Version 61.0 with full text customization system

---

**Prepared by**: Manus AI Agent  
**Reviewed by**: Pending  
**Approved by**: Pending
