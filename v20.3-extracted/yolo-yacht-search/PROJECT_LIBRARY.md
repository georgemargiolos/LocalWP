# YOLO Yacht Search - Project Library

**Quick Reference Guide for Development Sessions**

---

## 📁 PROJECT OVERVIEW

**Name:** YOLO Yacht Search WordPress Plugin  
**Repository:** https://github.com/georgemargiolos/LocalWP  
**Current Version:** 17.10  
**WordPress Version:** 6.4+  
**PHP Version:** 8.1+  
**Database:** MySQL 8.0+

---

## 🎯 CURRENT STATUS (December 3, 2025)

### ✅ What's Working
- Quote request form submission
- Main plugin functionality
- Yacht search and display
- Booking system
- Base Manager menu routing (v17.10)

### ⚠️ What Needs Work
- Base Manager CRUD operations (untested)
- Admin page designs (need improvement)
- Documents management (needs investigation)

### 🚨 Critical Issues
None currently - v17.10 fixed menu routing

---

## 📚 ESSENTIAL DOCUMENTATION

### Must-Read Files (In Order)
1. **HANDOFF_SESSION_DECEMBER_3_2025.md** - Complete session context
2. **CHANGELOG_v17.10.md** - Latest changes
3. **CHANGELOG.md** - Full version history
4. **PROJECT_LIBRARY.md** - This file

### Reference Documentation
- **TESTING_REPORT_v17.9.0.md** - Testing methodology
- **INSTALL_v17.9.0.md** - Installation guide
- **NEXT_SESSION_PROMPT.md** - Template for next session

---

## 🗂️ FILE STRUCTURE

### Core Files
```
yolo-yacht-search/
├── yolo-yacht-search.php                 # Main plugin file
├── includes/
│   ├── class-yolo-ys-yacht-search.php    # Main class
│   ├── class-yolo-ys-loader.php          # Hook loader
│   ├── class-yolo-ys-activator.php       # Activation (role creation)
│   ├── class-yolo-ys-base-manager.php    # ⭐ Base Manager system
│   └── class-yolo-ys-quote-handler.php   # Quote requests
├── admin/
│   ├── class-yolo-ys-admin.php           # Admin interface
│   └── partials/
│       ├── base-manager-admin-dashboard.php  # ⭐ Dashboard (good design)
│       ├── base-manager-yacht-management.php # ⭐ Needs design work
│       ├── base-manager-checkin.php          # ⭐ Needs design work
│       ├── base-manager-checkout.php         # ⭐ Needs design work
│       └── base-manager-warehouse.php        # ⭐ Needs design work
└── public/
    ├── templates/
    │   ├── yacht-details-v3.php          # Yacht details page
    │   └── partials/
    │       └── yacht-details-v3-scripts.php  # ⭐ Quote form JS
    └── js/
        └── base-manager.js               # Base Manager frontend JS
```

---

## 🔧 KEY TECHNICAL DETAILS

### Database Tables
- `wp_yolo_bm_yachts` - Yacht information
- `wp_yolo_bm_equipment_categories` - Equipment
- `wp_yolo_bm_checkins` - Check-in records
- `wp_yolo_bm_checkouts` - Check-out records
- `wp_yolo_bm_warehouse` - Inventory
- `wp_yolo_bookings` - Bookings
- `wp_yolo_quote_requests` - Quote requests

### User Roles
- **Administrator** - Full access (has `edit_posts` + all capabilities)
- **Base Manager** - Editor capabilities + custom Base Manager capabilities
  - `manage_base_operations`
  - `manage_yachts`
  - `manage_checkins`
  - `manage_checkouts`
  - `manage_warehouse`
  - Does NOT have `manage_options` (security)

### Menu Structure
```
YOLO Yacht Search (edit_posts, priority 10)
├── Settings
├── Bookings
├── Base Manager Dashboard (edit_posts, priority 25) ⭐
├── Yacht Management (edit_posts, priority 25) ⭐
├── Check-In (edit_posts, priority 25) ⭐
├── Check-Out (edit_posts, priority 25) ⭐
├── Warehouse (edit_posts, priority 25) ⭐
├── Texts
├── Quote Requests
├── Contact Messages
└── Notification Settings
```

### AJAX Endpoints
```php
// Base Manager
wp_ajax_yolo_bm_save_yacht
wp_ajax_yolo_bm_get_yachts
wp_ajax_yolo_bm_delete_yacht
wp_ajax_yolo_bm_save_equipment_category
wp_ajax_yolo_bm_get_equipment_categories
wp_ajax_yolo_bm_save_checkin
wp_ajax_yolo_bm_save_checkout
wp_ajax_yolo_bm_generate_pdf
wp_ajax_yolo_bm_send_to_guest
wp_ajax_yolo_bm_save_warehouse_item
wp_ajax_yolo_bm_get_warehouse_items
wp_ajax_yolo_bm_delete_warehouse_item

// Quote Requests
wp_ajax_nopriv_yolo_submit_quote_request
```

---

## 🐛 COMMON ISSUES & SOLUTIONS

### Issue: "Sorry, you are not allowed to access this page"
**Cause:** Capability mismatch between parent and submenu  
**Solution:** Ensure all menus use `edit_posts` capability

### Issue: Frontend loads instead of admin dashboard
**Cause:** Menu hook timing issue  
**Solution:** Use priority 25 for Base Manager menus

### Issue: AJAX returns -1
**Cause:** Nonce verification failed  
**Solution:** Check nonce name matches in template and handler

### Issue: Database field not found
**Cause:** Template uses different field name than database  
**Solution:** Check database schema and update template

---

## 🎨 DESIGN GUIDELINES

### Good Design Reference
**File:** `admin/partials/base-manager-admin-dashboard.php`

**Features:**
- Card-based layout
- Clean spacing
- Professional typography
- WordPress admin styling
- Responsive design

### Design Patterns to Use
- WordPress admin UI components
- Card containers for sections
- Consistent spacing (15-20px)
- Professional color scheme
- Clear visual hierarchy

---

## 🧪 TESTING CHECKLIST

### Before Each Release
- [ ] Test plugin activation/deactivation
- [ ] Test with Administrator role
- [ ] Test with Base Manager role
- [ ] Test all AJAX endpoints
- [ ] Test all CRUD operations
- [ ] Check browser console for errors
- [ ] Verify database queries work
- [ ] Test on different screen sizes

### Base Manager Testing
- [ ] Add Yacht
- [ ] Edit Yacht
- [ ] Delete Yacht
- [ ] Equipment categories
- [ ] Check-In with signature
- [ ] Check-Out with signature
- [ ] PDF generation
- [ ] Send to Guest
- [ ] Warehouse operations

---

## 📊 VERSION HISTORY QUICK REF

| Version | Status | Key Issue |
|---------|--------|-----------|
| 17.10 | ✅ Current | Menu routing fixed |
| 17.9.1 | ❌ Broken | Capability mismatch |
| 17.9.0 | ❌ Broken | Permission errors |
| 17.8.6 | ✅ Working | ZIP structure fixed |
| 17.8.5 | ❌ Broken | Fatal error |

---

## 🚀 NEXT STEPS

### Priority 1: Testing
Test all Base Manager CRUD operations and fix bugs

### Priority 2: Design
Improve admin page designs to match dashboard quality

### Priority 3: Features
Investigate and implement documents management

### Priority 4: Release
Create v17.11 with tested and working functionality

---

## 💡 DEVELOPMENT TIPS

### For AI Assistants
1. Always read HANDOFF document first
2. Test before releasing
3. Don't assume code works
4. Use proper version numbering (17.11, not 17.10.1)
5. User is admin - they should see everything

### For Developers
1. Follow WordPress coding standards
2. Use proper nonce verification
3. Check capabilities in templates
4. Test with different user roles
5. Document all changes

---

## 🔗 QUICK LINKS

- **GitHub:** https://github.com/georgemargiolos/LocalWP
- **Latest Release:** https://github.com/georgemargiolos/LocalWP/releases/tag/v17.10
- **Test Server:** mytestserver.gr
- **Local WordPress:** /home/ubuntu/wordpress/

---

## 📞 SUPPORT

**Project Owner:** George Margiolos  
**Development Environment:** Manus Sandbox  
**Session Handoff:** See HANDOFF_SESSION_DECEMBER_3_2025.md

---

*Last Updated: December 3, 2025*  
*Current Version: 17.10*  
*Next Version: 17.11 (planned)*
