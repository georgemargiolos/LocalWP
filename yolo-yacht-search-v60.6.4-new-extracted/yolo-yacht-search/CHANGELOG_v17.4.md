# YOLO Yacht Search Plugin - Changelog v17.4

**Version:** 17.4  
**Date:** December 3, 2025  
**Status:** ✅ Production Ready

---

## 🎯 Major Feature: In-House Quote Request System

Complete overhaul of the quote request system to eliminate email dependency and provide real-time notifications to base managers and administrators.

---

## ✨ New Features

### 1. Quote Requests Database System
- **New Database Table:** `wp_yolo_quote_requests`
  - Customer information (name, email, phone)
  - Yacht preference
  - Charter dates (check-in, check-out)
  - Number of guests
  - Special requests
  - Status tracking (new, reviewed, responded)
  - Internal notes
  - Viewed by tracking
  - Timestamps

### 2. Quote Requests Admin Interface
- **New Menu Item:** YOLO Yacht Search → Quote Requests
- **List View:**
  - Statistics dashboard (total, new, reviewed, responded)
  - Status filters
  - Sortable table with all quote details
  - Quick actions (view details, mark as reviewed/responded)
  - Visual status badges
- **Detail View:**
  - Complete customer information
  - Charter details with duration calculation
  - Special requests display
  - Internal notes editor
  - Quick contact actions (email, phone)
  - Status management

### 3. WordPress Admin Bar Notifications
- **Notification Badge:**
  - Displays unread quote count in admin bar
  - Real-time updates
  - Click to view quote requests
  - Visible to administrators and enabled base managers

### 4. Browser Push Notifications
- **In-Page Notifications:**
  - Pop-up notifications for new quotes
  - Customer name display
  - Quick actions (view quote, dismiss)
  - Auto-dismiss after 10 seconds
  - Staggered display for multiple notifications
  - Smooth animations (slide in/out)

### 5. Notification Settings Page
- **New Menu Item:** YOLO Yacht Search → Notification Settings (Admin Only)
- **Features:**
  - View all administrators (always notified)
  - Manage base manager notifications
  - Toggle admin bar badge per user
  - Toggle browser push notifications per user
  - Select all functionality
  - Visual toggle switches
  - Help section with notification explanations

### 6. Updated Quote Form Handler
- **No More Emails:** Quote requests saved directly to database
- **Automatic Notifications:** Triggers notifications for configured users
- **Enhanced Data Capture:** Supports number of guests field
- **Better User Feedback:** Success messages with quote ID

---

## 🔧 Technical Changes

### New Files Created
1. `includes/class-yolo-ys-quote-requests.php` - Quote requests management class
2. `admin/partials/quote-requests-list.php` - Quote requests list view
3. `admin/partials/quote-request-detail.php` - Quote request detail view
4. `admin/partials/quote-notification-settings.php` - Notification settings page
5. `admin/css/quote-notifications.css` - Notification styles
6. `admin/js/quote-notifications.js` - Notification JavaScript

### Modified Files
1. `yolo-yacht-search.php` - Added quote requests initialization
2. `admin/class-yolo-ys-admin.php` - Added quote requests and notification settings menus
3. `includes/class-yolo-ys-quote-handler.php` - Updated to use database instead of email

### Database Schema
```sql
CREATE TABLE wp_yolo_quote_requests (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    customer_name varchar(255) NOT NULL,
    customer_email varchar(255) NOT NULL,
    customer_phone varchar(50) DEFAULT NULL,
    yacht_preference varchar(255) DEFAULT NULL,
    checkin_date date DEFAULT NULL,
    checkout_date date DEFAULT NULL,
    num_guests int(11) DEFAULT NULL,
    special_requests text DEFAULT NULL,
    status varchar(50) DEFAULT 'new',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    viewed_by varchar(255) DEFAULT NULL,
    notes text DEFAULT NULL,
    PRIMARY KEY (id),
    KEY status (status),
    KEY created_at (created_at)
);
```

### AJAX Endpoints
1. `yolo_save_quote_request` - Save new quote request (public)
2. `yolo_update_quote_status` - Update quote status
3. `yolo_delete_quote_request` - Delete quote request (admin only)
4. `yolo_save_quote_notes` - Save internal notes
5. `yolo_clear_pending_notifications` - Clear user's pending notifications
6. `yolo_update_notification_preference` - Update user notification settings

### User Meta Keys
- `yolo_unread_quotes` - Count of unread quotes per user
- `yolo_pending_notifications` - Array of pending notifications
- `yolo_quote_notifications_enabled` - Enable/disable admin bar notifications
- `yolo_push_notifications_enabled` - Enable/disable browser push notifications

---

## 🎨 UI/UX Improvements

### Statistics Dashboard
- Visual stat boxes with color coding
- Real-time counts (total, new, reviewed, responded)
- Clean, modern design

### Status System
- **New:** Blue badge - Just submitted
- **Reviewed:** Yellow badge - Being processed
- **Responded:** Green badge - Completed

### Notification Design
- Professional pop-up notifications
- Icon-based visual hierarchy
- Smooth animations
- Mobile-responsive

### Settings Interface
- Toggle switches for intuitive control
- Clear help documentation
- Administrator vs. base manager distinction
- Select all functionality

---

## 🔒 Security

- Nonce verification on all AJAX requests
- Capability checks (edit_posts for base managers, manage_options for admins)
- Input sanitization (sanitize_text_field, sanitize_email, sanitize_textarea_field)
- SQL injection prevention (prepared statements)
- XSS prevention (esc_html, esc_attr)

---

## 📊 Access Control

### Administrators
- Full access to quote requests
- Full access to notification settings
- Always receive notifications
- Can delete quotes

### Base Managers
- View and manage quote requests
- Update status and notes
- Configurable notification preferences
- Cannot delete quotes
- Cannot access notification settings

---

## 🚀 Performance

- AJAX-powered interface (no page reloads)
- Indexed database queries
- Efficient notification system
- Minimal JavaScript footprint
- CSS animations (hardware accelerated)

---

## 📝 Workflow

### For Customers
1. Submit quote request via form
2. Receive confirmation message
3. Wait for contact from base manager/admin

### For Base Managers/Admins
1. Receive notification (admin bar badge + browser push)
2. Click notification to view quote requests
3. Review quote details
4. Mark as "Reviewed" when processing
5. Add internal notes
6. Contact customer (email/phone quick actions)
7. Mark as "Responded" when complete

### For Administrators (Settings)
1. Go to YOLO Yacht Search → Notification Settings
2. View which base managers exist
3. Enable/disable notifications per base manager
4. Choose admin bar badge and/or browser push
5. Save settings

---

## 🔄 Migration Notes

- **Existing Quotes:** Old quote requests table structure is replaced
- **Email System:** No longer sends emails (in-house system only)
- **Backward Compatibility:** Quote form still works with same AJAX endpoint
- **Database:** Table auto-creates on first quote submission

---

## 🐛 Bug Fixes

- Fixed duplicate notification issue
- Fixed unread count not decrementing
- Fixed status filter not working
- Fixed toggle switches not saving correctly

---

## 📦 Dependencies

- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+
- jQuery (included with WordPress)

---

## 🎯 Testing Checklist

- [x] Quote form submission
- [x] Database storage
- [x] Admin bar notification badge
- [x] Browser push notifications
- [x] Quote list view
- [x] Quote detail view
- [x] Status updates
- [x] Internal notes
- [x] Notification settings
- [x] Base manager access control
- [x] Admin access control
- [x] Mobile responsiveness
- [x] AJAX functionality
- [x] Security (nonces, capabilities)

---

## 📚 Documentation

- All code fully commented
- PHPDoc blocks for all methods
- Inline comments for complex logic
- Help section in notification settings

---

## 🔮 Future Enhancements

Potential improvements for future versions:
- Email notifications as optional backup
- SMS notifications integration
- Quote response templates
- Automated follow-up reminders
- Quote analytics dashboard
- Export quotes to CSV/PDF
- Quote assignment to specific base managers
- Quote priority levels
- Quote categories/tags

---

## 👥 Credits

**Developed by:** Manus AI  
**For:** YOLO Charters  
**Project:** LocalWP - YOLO Yacht Search Plugin

---

## 📞 Support

For issues or questions:
- GitHub: https://github.com/georgemargiolos/LocalWP
- Documentation: See HANDOFF_v17.4.md for technical details

---

**End of Changelog v17.4**
