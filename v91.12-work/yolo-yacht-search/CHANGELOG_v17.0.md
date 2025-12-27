# YOLO Yacht Search Plugin - Version 17.0 Changelog

**Release Date:** December 3, 2025  
**Generated:** 2025-12-03 02:00:00 GMT+2

---

## 🚀 Major Feature: Base Manager System

Version 17.0 introduces a complete **Base Manager System** for yacht charter operations management. This is the largest feature addition to the plugin, providing comprehensive tools for base operations, yacht management, and guest interaction.

---

## ✨ New Features

### 1. Base Manager User Role
- **New WordPress Role:** `base_manager`
- Custom capabilities for base operations management
- Automatic redirect from wp-admin to dedicated dashboard
- Role-based access control for all base manager features

### 2. Base Manager Dashboard (`[base_manager]` shortcode)
- **Bootstrap 5 Grid System** layout
- Fully responsive design
- Tab-based navigation:
  - Bookings Calendar
  - Yacht Management
  - Check-In
  - Check-Out
  - Warehouse Management

### 3. Yacht Management System
- **Create/Edit Yachts** with:
  - Yacht name and model
  - Company logo upload
  - Boat logo upload (optional)
  - Boat owner information (name, surname, mobile, email)
- **Equipment Categories Management:**
  - Create custom categories (Electronics, Kitchenware, Chart Table, etc.)
  - Add items to each category
  - Quantity tracking with +/- controls
  - Item completion status for check-in/check-out
- Full CRUD operations for yachts and equipment

### 4. Check-In Process
- **Item-by-Item Checklist:**
  - Equipment verification by category
  - Quantity confirmation
  - Completion marking
- **Digital Signature Capture:**
  - Base manager signature
  - Canvas-based signature pad
- **PDF Generation:**
  - Professional check-in documents
  - Company and boat logos
  - Equipment checklist
  - Signature placement: Base Manager (bottom-left), Guest (bottom-right)
- **Send to Guest:**
  - Email notification
  - Document available in guest dashboard
  - Guest can view, download, and sign

### 5. Check-Out Process
- Same comprehensive features as check-in
- Separate tracking and documentation
- Independent PDF generation
- Guest signature workflow

### 6. Guest Dashboard Integration
- **New Sections:**
  - Check-In Documents
  - Check-Out Documents
- **Guest Capabilities:**
  - View sent documents
  - Download PDFs
  - **Digital signature** with canvas pad
  - Send signed documents back to base manager
- **Document Status Indicators:**
  - Pending signature (orange)
  - Signed (green)
- **Signature Modal:**
  - Full-screen signature canvas
  - Clear signature option
  - Mobile-responsive design

### 7. Warehouse Management
- **Boat-Specific Inventory:**
  - Select yacht from dropdown
  - Add/edit inventory items
  - Item name and quantity
  - Expiry date tracking
  - Location field
  - +/- quantity controls
- **Real-time Updates:**
  - AJAX-based operations
  - Instant inventory refresh
  - No page reloads required

### 8. Bookings Calendar View
- **Read-Only Calendar** for base managers
- View all bookings with:
  - Customer information
  - Yacht details
  - Check-in/Check-out dates
  - Booking status
  - Total price
- Synchronized with admin bookings system
- Real-time updates via AJAX

---

## 🗄️ Database Changes

### New Tables

#### `wp_yolo_bm_yachts`
Stores yacht information for base manager system:
- `id` (Primary Key)
- `yacht_name`
- `yacht_model`
- `company_logo` (URL)
- `boat_logo` (URL)
- `owner_name`
- `owner_surname`
- `owner_mobile`
- `owner_email`
- `created_at`
- `updated_at`

#### `wp_yolo_bm_equipment_categories`
Stores equipment categories for yachts:
- `id` (Primary Key)
- `yacht_id` (Foreign Key)
- `category_name`
- `items` (JSON - array of equipment items)
- `created_at`
- `updated_at`

#### `wp_yolo_bm_checkins`
Stores check-in records:
- `id` (Primary Key)
- `booking_id` (Foreign Key)
- `yacht_id` (Foreign Key)
- `checklist_data` (JSON)
- `signature` (Base64 image - Base Manager)
- `guest_signature` (Base64 image - Guest)
- `guest_signed_at` (Timestamp)
- `pdf_url`
- `status` (pending/signed)
- `created_at`
- `updated_at`

#### `wp_yolo_bm_checkouts`
Stores check-out records (same structure as checkins):
- `id` (Primary Key)
- `booking_id` (Foreign Key)
- `yacht_id` (Foreign Key)
- `checklist_data` (JSON)
- `signature` (Base64 image - Base Manager)
- `guest_signature` (Base64 image - Guest)
- `guest_signed_at` (Timestamp)
- `pdf_url`
- `status` (pending/signed)
- `created_at`
- `updated_at`

#### `wp_yolo_bm_warehouse`
Stores warehouse inventory:
- `id` (Primary Key)
- `yacht_id` (Foreign Key)
- `item_name`
- `quantity`
- `expiry_date`
- `location`
- `created_at`
- `updated_at`

---

## 📁 New Files

### PHP Classes
- `includes/class-yolo-ys-base-manager.php` - Main base manager system class
- `includes/class-yolo-ys-base-manager-database.php` - Database table creation
- `includes/class-yolo-ys-pdf-generator.php` - PDF generation for check-in/check-out

### Templates
- `public/partials/base-manager-dashboard.php` - Base manager dashboard template

### Assets
- `public/css/base-manager.css` - Base manager styles
- `public/js/base-manager.js` - Base manager JavaScript functionality
- `vendor/fpdf/fpdf.php` - FPDF library for PDF generation

### Guest Dashboard Updates
- Updated `public/partials/yolo-ys-guest-dashboard.php` - Added check-in/check-out sections
- Updated `public/js/yolo-guest-dashboard.js` - Added signature functionality
- Updated `public/css/guest-dashboard.css` - Added signature modal styles

---

## 🔧 Technical Improvements

### AJAX Handlers
New AJAX endpoints for base manager operations:
- `yolo_bm_save_yacht` - Save/update yacht
- `yolo_bm_get_yachts` - Retrieve yachts
- `yolo_bm_delete_yacht` - Delete yacht
- `yolo_bm_save_equipment_category` - Save equipment category
- `yolo_bm_get_equipment_categories` - Retrieve categories
- `yolo_bm_save_checkin` - Save check-in record
- `yolo_bm_save_checkout` - Save check-out record
- `yolo_bm_generate_pdf` - Generate PDF document
- `yolo_bm_send_to_guest` - Email document to guest
- `yolo_bm_save_warehouse_item` - Save warehouse item
- `yolo_bm_get_warehouse_items` - Retrieve warehouse items
- `yolo_bm_get_bookings_calendar` - Get bookings data
- `yolo_guest_sign_document` - Guest document signing

### Security
- Nonce verification for all AJAX requests
- Capability checks for base manager operations
- Booking ownership verification for guest signatures
- Base64 signature validation
- SQL injection prevention with prepared statements

### PDF Generation
- FPDF library integration
- Professional document layout
- Company and boat logo support
- Equipment checklist formatting
- Signature placement at document bottom
- Automatic file cleanup after generation
- PDF storage in WordPress uploads directory

---

## 🎨 UI/UX Enhancements

### Base Manager Dashboard
- Modern Bootstrap 5 design
- Intuitive tab navigation
- Responsive tables and forms
- Modal dialogs for data entry
- Real-time validation
- Loading indicators
- Success/error notifications

### Guest Dashboard
- Signature modal with canvas
- Touch-friendly signature pad
- Document status badges
- Download and view buttons
- Mobile-optimized layout
- Clear visual feedback

---

## 🔄 Workflow

### Check-In Workflow
1. Base manager creates check-in for booking
2. Selects yacht and verifies equipment
3. Signs document digitally
4. Generates PDF with signature
5. Sends to guest via email
6. Guest receives notification
7. Guest views document in dashboard
8. Guest signs document
9. PDF regenerated with both signatures
10. Both parties have signed document

### Check-Out Workflow
Same as check-in, with separate documentation and tracking.

---

## 📊 Integration Points

### Existing Systems
- **Bookings System:** Check-in/check-out linked to bookings
- **Guest Users:** Documents appear in guest dashboard
- **Email Notifications:** Automatic emails when documents sent
- **WordPress Uploads:** PDFs stored in uploads directory
- **User Roles:** Integration with WordPress role system

---

## 🐛 Bug Fixes
- Fixed incomplete AJAX handler registration
- Corrected signature placement in PDFs
- Improved date format consistency
- Fixed signature image cleanup
- Resolved booking ownership verification

---

## 📝 Code Quality
- Comprehensive inline documentation
- PSR-2 coding standards
- Modular class structure
- Separation of concerns
- Error handling and logging
- Code regression testing completed

---

## 🚦 Testing Status

### ✅ Completed Tests
- Base manager role creation
- Dashboard rendering
- Yacht CRUD operations
- Equipment category management
- Check-in process
- Check-out process
- PDF generation
- Guest signature integration
- Warehouse management
- Bookings calendar view
- AJAX functionality
- Security checks
- Mobile responsiveness

---

## 📦 Dependencies

### New Dependencies
- **FPDF Library** (included in vendor/)
- **Bootstrap 5.3.2** (CDN)
- **Font Awesome 6.4.0** (CDN)
- **Signature Pad Library** (CDN)

### Existing Dependencies
- WordPress 5.0+
- PHP 7.4+
- MySQL 5.7+

---

## 🔮 Future Enhancements

### Potential Additions
- Email templates customization
- Multi-language support for PDFs
- Advanced reporting and analytics
- Equipment maintenance tracking
- Damage assessment with photos
- Integration with accounting systems
- Mobile app for base managers
- QR code scanning for equipment

---

## 📖 Documentation

### Updated Files
- `README.md` - Updated with v17.0 features
- `VERSION-HISTORY.md` - Added v17.0 entry
- `CHANGELOG_v17.0.md` - This file
- `HANDOFF_v17.0.md` - Technical handoff document

### New Documentation
- Base Manager User Guide (to be created)
- API Documentation for AJAX endpoints (to be created)

---

## 🎯 Migration Notes

### Upgrading from v16.7 to v17.0

1. **Database Migration:**
   - New tables will be created automatically on plugin activation
   - No data migration required (new feature)

2. **User Roles:**
   - Base manager role created automatically
   - Assign users to base manager role via WordPress admin

3. **Page Setup:**
   - Create a new page with `[base_manager]` shortcode
   - Set page as base manager dashboard in plugin settings

4. **File Permissions:**
   - Ensure WordPress uploads directory is writable
   - Check PDF generation directories are created

5. **Testing:**
   - Test base manager login and dashboard access
   - Verify PDF generation works
   - Test guest signature workflow
   - Confirm email notifications

---

## 👥 Credits

**Development:** Manus AI Agent  
**Project:** YOLO Yacht Search Plugin  
**Repository:** github.com/georgemargiolos/LocalWP  
**Version:** 17.0  
**Date:** December 3, 2025

---

## 📞 Support

For issues, questions, or feature requests:
- GitHub Issues: github.com/georgemargiolos/LocalWP/issues
- Documentation: See README.md and HANDOFF files

---

**End of Changelog v17.0**
