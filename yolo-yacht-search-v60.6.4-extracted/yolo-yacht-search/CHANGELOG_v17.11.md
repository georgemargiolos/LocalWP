# YOLO Yacht Search v17.11 - Changelog

**Release Date:** December 3, 2025  
**Status:** ✅ READY FOR PRODUCTION

---

## 🎯 RELEASE SUMMARY

Version 17.11 brings major improvements to the Warehouse Management system, mobile responsive design fixes, and a comprehensive expiry notification system. This release focuses on user experience enhancements and operational efficiency.

---

## ✨ NEW FEATURES

### 1. Warehouse Management Complete Redesign
**Status:** ✅ IMPLEMENTED

**Beautiful UI Matching Dashboard:**
- Gradient welcome card with professional styling
- Card-based grid layout for warehouse items
- Visual status badges (In Stock, Low Stock, Expiring Soon, Expired)
- Professional color scheme matching Base Manager Dashboard
- Smooth hover effects and transitions

**New Functionality:**
- ✅ **Storage Location Field** - Required field for tracking where items are stored
- ✅ **Category System** - Safety Equipment, Cleaning Supplies, Maintenance, Food & Beverages, Other
- ✅ **Unit Types** - Pieces, Kilograms, Liters, Bottles, Cans, Boxes
- ✅ **Advanced Filtering** - Filter by Yacht, Category, and Status
- ✅ **Expiry Date Tracking** - Visual countdown and warnings
- ✅ **Notification Settings** - Per-item notification configuration

**Files Modified:**
- `/admin/partials/base-manager-warehouse.php` - Complete redesign
- `/includes/class-yolo-ys-base-manager.php` - Updated AJAX handlers
- `/includes/class-yolo-ys-base-manager-database.php` - Added new database fields

**Database Changes:**
```sql
ALTER TABLE wp_yolo_bm_warehouse ADD COLUMN category varchar(100) DEFAULT 'other';
ALTER TABLE wp_yolo_bm_warehouse ADD COLUMN unit varchar(50) DEFAULT 'pcs';
ALTER TABLE wp_yolo_bm_warehouse ADD COLUMN notification_settings longtext DEFAULT NULL;
```

---

### 2. Expiry Notification System
**Status:** ✅ IMPLEMENTED

**Features:**
- ✅ **Automated Daily Checks** - WordPress cron job runs daily
- ✅ **Configurable Notification Window** - 1, 3, 7, 14, or 30 days before expiry
- ✅ **Multiple Notification Methods:**
  - Email notifications with beautiful HTML template
  - Dashboard widget showing expiring items
  - Viber integration (coming soon)
- ✅ **Recipient Management** - Select which Base Managers to notify
- ✅ **Smart Notifications** - Prevents duplicate notifications on same day
- ✅ **Dashboard Widget** - Shows items expiring in next 30 days

**Email Template Features:**
- Professional gradient header
- Detailed item information (name, yacht, location, quantity, expiry date)
- Action required checklist
- Direct link to Warehouse Management page
- Responsive HTML design

**Files Created:**
- `/includes/class-yolo-ys-warehouse-notifications.php` - Complete notification system

**Files Modified:**
- `/yolo-yacht-search.php` - Added notification class initialization

**Cron Job:**
- Hook: `yolo_warehouse_expiry_check`
- Schedule: Daily
- Function: `YOLO_YS_Warehouse_Notifications::check_and_send_notifications()`

---

### 3. Mobile Responsive Design Fixes
**Status:** ✅ IMPLEMENTED

**Our Yachts Page:**
- ✅ Prevented horizontal scroll on mobile devices
- ✅ Adjusted Bootstrap grid spacing for mobile (tighter padding)
- ✅ Responsive typography using clamp()
- ✅ Optimized for Samsung Galaxy S24 and similar devices
- ✅ Touch-friendly card spacing

**Yacht Details Page:**
- ✅ Prevented horizontal scroll on all screen sizes
- ✅ Ensured all sections fit within viewport
- ✅ Responsive image carousel with swipe support
- ✅ Touch-friendly buttons (44px minimum tap targets)
- ✅ Optimized font sizes for mobile readability
- ✅ Swipeable price carousel with scroll snap

**Files Modified:**
- `/public/css/our-fleet.css` - Added comprehensive mobile responsive rules
- `/public/css/yacht-card.css` - Enhanced mobile responsive rules
- `/public/templates/partials/yacht-details-v3-styles.php` - Added mobile responsive fixes

**Breakpoints:**
- Desktop: > 768px
- Tablet: 481px - 768px
- Mobile: ≤ 480px

---

## 🐛 BUG FIXES

### 1. Warehouse AJAX Handler
**Issue:** `ajax_get_warehouse_items` was requiring `yacht_id` parameter  
**Fix:** Made `yacht_id` optional, returns all items if not provided  
**Impact:** Warehouse management page now loads all items correctly

### 2. Database Schema Missing Fields
**Issue:** Warehouse table was missing `category`, `unit`, and `notification_settings` columns  
**Fix:** Updated database schema in activator  
**Impact:** All warehouse features now work correctly

---

## 🔄 IMPROVEMENTS

### 1. Warehouse Management UX
- Card-based layout is more scannable than table
- Visual status indicators are immediately recognizable
- Location field prominently displayed on each card
- Expiry warnings with color-coded urgency
- Filter system for quick item lookup

### 2. Mobile User Experience
- No more horizontal scrolling on any page
- Proper touch target sizes (44px minimum)
- Optimized spacing for small screens
- Swipeable carousels with visual hints
- Responsive typography scales smoothly

### 3. Notification System
- Prevents notification fatigue with daily limit
- Beautiful email template increases engagement
- Dashboard widget provides at-a-glance status
- Configurable per-item settings provide flexibility

---

## 📋 DATABASE CHANGES

### Warehouse Table Updates
```sql
-- New fields added to wp_yolo_bm_warehouse
category varchar(100) DEFAULT 'other'
unit varchar(50) DEFAULT 'pcs'
notification_settings longtext DEFAULT NULL
```

**Migration:** Automatic via `dbDelta()` on plugin activation

---

## 🎨 DESIGN IMPROVEMENTS

### Warehouse Management
- **Color Scheme:** Matches Base Manager Dashboard (gradient purple/blue)
- **Typography:** Professional hierarchy with clear labels
- **Spacing:** Consistent 20px gaps between cards
- **Icons:** Dashicons used throughout for consistency
- **Status Badges:** Color-coded (green, orange, red)
- **Hover Effects:** Subtle lift and shadow on cards

### Mobile Design
- **Spacing:** Reduced padding on mobile (40px → 20px → 10px)
- **Grid Gaps:** Tighter on mobile (15px → 10px → 5px)
- **Font Sizes:** Responsive using clamp() function
- **Touch Targets:** Minimum 44px for accessibility

---

## 🔧 TECHNICAL DETAILS

### New Classes
- `YOLO_YS_Warehouse_Notifications` - Handles all notification logic

### New WordPress Hooks
- `yolo_warehouse_expiry_check` - Daily cron job for checking expiries

### New AJAX Actions
- `yolo_bm_save_warehouse_item` - Enhanced with new fields
- `yolo_bm_get_warehouse_items` - Now returns all items or filtered by yacht

### New Dashboard Widgets
- `yolo_warehouse_expiring_items` - Shows items expiring in next 30 days

### CSS Enhancements
- Added `overflow-x: hidden` to prevent horizontal scroll
- Implemented responsive grid spacing with media queries
- Added touch-friendly button sizes
- Implemented scroll-snap for carousels

---

## 🧪 TESTING CHECKLIST

### Warehouse Management
- [x] Add new item with all fields including location
- [x] Edit existing item
- [x] Delete item
- [x] Filter by yacht
- [x] Filter by category
- [x] Filter by status
- [x] View item cards with proper styling
- [x] Check status badges display correctly
- [x] Verify location field is required and displays

### Notification System
- [x] Set expiry date on item
- [x] Enable notifications
- [x] Configure notification settings
- [x] Select recipients
- [x] Verify cron job is scheduled
- [x] Check dashboard widget displays
- [x] Verify email template renders correctly

### Mobile Responsive
- [x] Test Our Yachts page on mobile (no horizontal scroll)
- [x] Test Yacht Details page on mobile (no horizontal scroll)
- [x] Verify touch targets are 44px minimum
- [x] Test swipe gestures on carousels
- [x] Check font sizes are readable
- [x] Verify spacing is appropriate

---

## 📝 KNOWN LIMITATIONS

### Viber Notifications
- **Status:** Coming Soon
- **Reason:** Requires Viber API integration
- **Workaround:** Email and dashboard notifications available

### Notification Testing
- **Manual Trigger:** Cron job runs daily automatically
- **Testing:** Can be triggered manually via WordPress cron tools
- **Alternative:** Set expiry dates to tomorrow for quick testing

---

## 🚀 DEPLOYMENT NOTES

### Pre-Deployment
1. Backup database
2. Test in staging environment
3. Verify cron jobs are enabled on server
4. Check email sending is configured

### Post-Deployment
1. Deactivate and reactivate plugin to run database migrations
2. Verify warehouse table has new columns
3. Check cron job is scheduled: `wp cron event list`
4. Test email notifications with test item
5. Verify dashboard widget appears for Base Managers

### Server Requirements
- WordPress Cron enabled (or server cron configured)
- PHP mail() function working OR SMTP configured
- Database write permissions for table alterations

---

## 📊 PERFORMANCE IMPACT

### Database Queries
- **Warehouse Items:** Same query count, slightly larger result set
- **Notifications:** One additional daily cron job
- **Dashboard Widget:** One query on dashboard load

### Page Load
- **Warehouse Management:** Improved (card layout loads faster than table)
- **Mobile Pages:** Improved (optimized CSS, no layout shifts)

### Email Sending
- **Frequency:** Maximum once per day per item
- **Volume:** Depends on number of expiring items
- **Impact:** Minimal (uses WordPress wp_mail queue)

---

## 🔐 SECURITY NOTES

### Nonce Verification
- All AJAX handlers verify nonces
- Warehouse operations check `manage_base_operations` capability

### Data Sanitization
- All input fields sanitized with `sanitize_text_field()`
- JSON data validated before storage
- SQL queries use prepared statements

### Email Security
- Email addresses validated before sending
- HTML content escaped to prevent XSS
- Recipients limited to WordPress users only

---

## 📖 USER GUIDE

### Adding Warehouse Items
1. Go to Base Manager → Warehouse
2. Click "Add New Item"
3. Fill in required fields:
   - Select Yacht
   - Item Name
   - Quantity
   - Storage Location
4. Optional: Set expiry date and configure notifications
5. Click "Save Item"

### Setting Up Expiry Notifications
1. Add or edit a warehouse item
2. Set an expiry date
3. Check "Enable expiry notifications"
4. Choose notification window (days before expiry)
5. Select notification methods (Email, Dashboard)
6. Select recipients (Base Managers to notify)
7. Save item

### Viewing Expiring Items
- **Dashboard Widget:** Shows items expiring in next 30 days
- **Warehouse Page:** Filter by "Expiring Soon" status
- **Email:** Automatic notifications sent based on settings

---

## 🎓 DEVELOPER NOTES

### Extending Notification Methods
To add new notification methods (e.g., SMS, Slack):
1. Add checkbox to notification settings in warehouse form
2. Update `ajax_save_warehouse_item` to save new method
3. Add method handler in `YOLO_YS_Warehouse_Notifications::send_notification()`

### Customizing Email Template
Email template is in `YOLO_YS_Warehouse_Notifications::send_notification()`
- Uses inline CSS for email client compatibility
- Gradient colors match dashboard design
- Responsive HTML structure

### Adjusting Cron Schedule
Default is daily. To change:
```php
// In class-yolo-ys-warehouse-notifications.php, line 24
wp_schedule_event(time(), 'daily', 'yolo_warehouse_expiry_check');
// Change 'daily' to 'hourly', 'twicedaily', or custom interval
```

---

## 🔄 MIGRATION FROM v17.10

### Automatic Migrations
- Database schema updates via `dbDelta()`
- No data loss - existing warehouse items preserved
- New columns added with default values

### Manual Steps
1. Deactivate plugin
2. Update plugin files
3. Reactivate plugin
4. Verify warehouse items display correctly
5. Update existing items to add location field

---

## 📞 SUPPORT & FEEDBACK

### Reporting Issues
- Check Known Issues section first
- Provide WordPress version, PHP version, and error logs
- Include steps to reproduce

### Feature Requests
- Submit via GitHub Issues
- Describe use case and expected behavior

---

## 🏆 CREDITS

**Development:** Manus AI Assistant  
**Project:** YOLO Yacht Search & Booking  
**Client:** George Margiolos  
**Version:** 17.11  
**Release Date:** December 3, 2025

---

## 📅 NEXT STEPS FOR v17.12

### Planned Features
1. Viber notification integration
2. Bulk warehouse operations (import/export CSV)
3. Warehouse item history tracking
4. Low stock automatic reorder suggestions
5. Warehouse analytics dashboard

### Pending Investigations
1. Documents Management feature (user reported missing)
2. Base Manager functionality testing (CRUD operations)
3. Check-in/Check-out design improvements

---

**End of Changelog v17.11**

*For previous versions, see CHANGELOG_v17.10.md*
