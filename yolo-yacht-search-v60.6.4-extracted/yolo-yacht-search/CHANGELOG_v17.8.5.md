# YOLO Yacht Search Plugin - Version 17.8.5 Changelog

**Release Date:** December 3, 2025  
**Release Type:** Critical Bug Fix  
**Previous Version:** 17.8.4 FINAL

---

## 🐛 Critical Bug Fixes

### Issue #1: Base Manager Menu Not Visible to Administrators
**Problem:**  
Administrator users could not see the Base Manager menu in wp-admin. The menu required the custom capability `manage_base_operations`, which only Base Manager role users had. Administrators with `manage_options` capability were blocked from accessing Base Manager functionality.

**Root Cause:**  
The `add_admin_dashboard_page()` method in `class-yolo-ys-base-manager.php` used hardcoded capability checks (`manage_base_operations`) for all menu items, preventing administrators from seeing or accessing Base Manager features.

**Solution:**  
Modified capability logic to support both user types:
- **Administrators:** Use `manage_options` capability (standard WordPress admin capability)
- **Base Managers:** Use `manage_base_operations` capability (custom role capability)

**Files Changed:**
- `includes/class-yolo-ys-base-manager.php` - Lines 107-167

**Code Changes:**
```php
// OLD: Hardcoded capability
add_menu_page(
    'Base Manager Dashboard',
    'Base Manager',
    'manage_base_operations',  // ❌ Only base managers could see this
    ...
);

// NEW: Dynamic capability based on user role
$menu_capability = current_user_can('manage_options') ? 'manage_options' : 'manage_base_operations';
add_menu_page(
    'Base Manager Dashboard',
    'Base Manager',
    $menu_capability,  // ✅ Both admins and base managers can see this
    ...
);
```

**Result:**  
✅ Administrators can now see and access all Base Manager functionality  
✅ Base Managers continue to work as before with custom capabilities  
✅ No breaking changes to existing role structure

---

### Issue #2: Quote Request Form Not Working
**Problem:**  
The "Request a Quote" form on yacht detail pages did not work at all:
- No success message displayed after submission
- Quotes were not saved to database
- No entries appeared in admin Quote Requests page
- Form appeared to do nothing when submitted

**Root Cause:**  
The quote form HTML existed (`id="quoteRequestForm"`) but there was **no JavaScript event handler** to capture and process form submissions. The form submission code was completely missing from the scripts file.

**Solution:**  
Added complete quote form submission handler to `yacht-details-v3-scripts.php`:
- Form submission event listener
- AJAX request to `yolo_submit_quote_request` action
- Nonce verification for security
- Date range integration from Litepicker
- Guest count integration
- Loading state management
- Success/error message display
- Form reset and hide after successful submission

**Files Changed:**
- `public/templates/partials/yacht-details-v3-scripts.php` - Lines 1004-1067

**Code Added:**
```javascript
const quoteForm = document.getElementById('quoteRequestForm');
if (quoteForm) {
    quoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(this);
        formData.append('action', 'yolo_submit_quote_request');
        formData.append('nonce', '<?php echo wp_create_nonce('yolo_quote_nonce'); ?>');
        
        // Get date range from picker if available
        if (window.yoloDatePicker) {
            const startDate = window.yoloDatePicker.getStartDate();
            const endDate = window.yoloDatePicker.getEndDate();
            if (startDate) formData.append('date_from', startDate.format('YYYY-MM-DD'));
            if (endDate) formData.append('date_to', endDate.format('YYYY-MM-DD'));
        }
        
        // Submit via AJAX with loading state and error handling
        // ... (full implementation in file)
    });
}
```

**Result:**  
✅ Quote form now submits successfully  
✅ Success message displays to user  
✅ Quotes saved to database (`yolo_quote_requests` table)  
✅ Quotes appear in admin Quote Requests page  
✅ Notifications triggered for administrators and base managers  
✅ Form resets and hides after successful submission

---

## 📝 Technical Details

### Backend Handler (Already Existed - No Changes)
The backend quote handler in `includes/class-yolo-ys-quote-handler.php` was working correctly:
- AJAX action: `yolo_submit_quote_request`
- Nonce verification: `yolo_quote_nonce`
- Database insertion to `yolo_quote_requests` table
- Notification system for admins and base managers

The issue was purely on the frontend - the JavaScript to trigger this backend handler was missing.

### Testing Checklist
- [x] Admin users can see Base Manager menu in wp-admin
- [x] Admin users can access all Base Manager submenus (Dashboard, Yacht Management, Check-In, Check-Out, Warehouse)
- [x] Base Manager role users can still access their functionality
- [x] Quote form displays when "REQUEST A QUOTE" button is clicked
- [x] Quote form submits successfully with all fields
- [x] Success message displays after submission
- [x] Quote appears in admin Quote Requests page
- [x] Notifications triggered for administrators
- [x] Form resets and hides after successful submission

---

## 🔄 Upgrade Notes

### From v17.8.4 to v17.8.5
**No database changes required.**  
**No configuration changes required.**

Simply replace plugin files and refresh WordPress admin. Changes take effect immediately.

### Compatibility
- WordPress: 5.8+
- PHP: 7.4+
- MySQL: 8.0+
- All v17.x features remain intact
- No breaking changes

---

## 📦 Files Modified in This Release

1. **yolo-yacht-search.php**
   - Updated version from 17.8.4 to 17.8.5

2. **includes/class-yolo-ys-base-manager.php**
   - Modified `add_admin_dashboard_page()` method
   - Added dynamic capability logic for admin/base manager access

3. **public/templates/partials/yacht-details-v3-scripts.php**
   - Added complete quote form submission handler
   - Integrated with existing AJAX backend

---

## 🎯 Impact Summary

**User Impact:**
- Administrators can now fully manage Base Manager operations
- Customers can successfully submit quote requests
- Quote requests are properly tracked and managed

**Developer Impact:**
- No API changes
- No database schema changes
- No configuration changes required

**Business Impact:**
- Critical functionality restored
- Quote request pipeline operational
- Base Manager system fully accessible to admins

---

## 🔗 Related Documentation

- **Base Manager System:** See `HANDOFF_v17.0.md`
- **Full v17.x Changelog:** See `CHANGELOG_v17.8.md`
- **GitHub Repository:** https://github.com/georgemargiolos/LocalWP

---

## 👨‍💻 Development Notes

**Why These Bugs Existed:**
1. **Base Manager Menu:** Original design assumed only Base Manager role would need access. Admins were inadvertently locked out by overly restrictive capability checks.
2. **Quote Form:** Frontend JavaScript handler was never implemented, despite backend handler being complete. Form HTML existed but was non-functional.

**Why They're Fixed Now:**
1. **Base Manager Menu:** Added role-aware capability detection that works for both admins and base managers.
2. **Quote Form:** Implemented missing JavaScript submission handler with proper integration to existing backend.

Both fixes are minimal, surgical changes that restore intended functionality without affecting other features.

---

**End of Changelog**
