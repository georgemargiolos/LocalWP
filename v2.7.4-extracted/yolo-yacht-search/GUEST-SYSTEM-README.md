# Guest User System - Setup & Documentation

## Overview
The guest user system automatically creates WordPress user accounts for customers after successful yacht bookings. Guests can log in to view their bookings and upload their sailing licenses.

---

## Features

### 1. **Automatic Guest User Creation**
- After successful Stripe payment, a WordPress user is created automatically
- **User Role:** `guest` (custom role with minimal permissions)
- **Username:** Customer's email address
- **Password:** `[booking_id]YoLo` (e.g., if booking ID is 123, password is `123YoLo`)
- Credentials are sent via email with login link

### 2. **Guest Dashboard**
- Displays all bookings for the logged-in guest
- Shows booking details (yacht, dates, prices, status)
- License upload functionality (front + back images)
- Mobile-responsive design

### 3. **License Upload**
- Guests can upload front and back images of their sailing license
- Supported formats: JPG, PNG, GIF
- Maximum file size: 5MB per image
- Files stored in: `/wp-content/uploads/yolo-licenses/[booking_id]/`
- AJAX-powered upload with progress feedback

### 4. **Admin Panel**
- View all guest license uploads
- Download license images
- See upload status (complete/incomplete)
- Filter by booking

### 5. **Security Features**
- Guests cannot access wp-admin (auto-redirected to homepage)
- Guests can only view their own bookings
- NONCE verification on all AJAX uploads
- File type and size validation

---

## Setup Instructions

### Step 1: Create Guest Dashboard Page

1. **Go to:** WordPress Admin → Pages → Add New
2. **Page Title:** Guest Dashboard
3. **Page Content:** Add the shortcode:
   ```
   [yolo_guest_dashboard]
   ```
4. **Permalink:** Set to `guest-dashboard` (important!)
5. **Publish** the page

### Step 2: Test Guest User Flow

1. **Make a test booking** through the yacht booking system
2. **Check email** for guest credentials (username + password)
3. **Log in** at `/wp-login.php` using the credentials
4. **Verify redirect** to guest dashboard page
5. **Upload license** images (front + back)
6. **Check admin panel** at YOLO Yacht Search → Guest Licenses

### Step 3: Configure Email Settings (Optional)

The system sends two emails after booking:
1. **Booking Confirmation** - Receipt with booking details
2. **Guest Credentials** - Login username and password

Emails are sent using WordPress `wp_mail()`. For better deliverability, consider:
- Installing an SMTP plugin (e.g., WP Mail SMTP)
- Configuring your server's mail settings

---

## Shortcode Reference

### `[yolo_guest_dashboard]`

**Description:** Displays the guest dashboard with bookings and license upload

**Usage:**
```
[yolo_guest_dashboard]
```

**Behavior:**
- **Not logged in:** Shows login prompt with link to WordPress login
- **Logged in (guest role):** Shows bookings and license upload forms
- **Logged in (other roles):** Shows access denied message

**No parameters required**

---

## Database Tables

### `wp_yolo_bookings`
Stores all yacht bookings with customer information

**New Field Added:**
- `user_id` (bigint) - Links booking to WordPress user account

### `wp_yolo_license_uploads`
Stores uploaded license images

**Fields:**
- `id` - Auto-increment primary key
- `booking_id` - Foreign key to bookings table
- `user_id` - Foreign key to WordPress users table
- `file_type` - 'front' or 'back'
- `file_path` - Absolute server path to file
- `file_url` - Public URL to file
- `uploaded_at` - Timestamp of upload

---

## User Roles

### Guest Role
**Capabilities:**
- `read` - Can view content
- `level_0` - Lowest permission level

**Restrictions:**
- Cannot access wp-admin
- Cannot edit posts/pages
- Cannot manage WordPress settings
- Can only view their own bookings

---

## File Structure

```
yolo-yacht-search/
├── includes/
│   └── class-yolo-ys-guest-users.php          # Guest user management
├── admin/
│   ├── class-yolo-ys-admin-guest-licenses.php # Admin panel for licenses
│   └── css/
│       └── yolo-ys-admin-guest-licenses.css   # Admin panel styles
├── public/
│   ├── partials/
│   │   └── yolo-ys-guest-dashboard.php        # Dashboard template
│   └── css/
│       └── guest-dashboard.css                # Dashboard styles
└── GUEST-SYSTEM-README.md                     # This file
```

---

## AJAX Endpoints

### `yolo_upload_license`
**Action:** Upload sailing license image

**Parameters:**
- `nonce` - Security nonce (required)
- `booking_id` - Booking ID (required)
- `file_type` - 'front' or 'back' (required)
- `license_file` - File upload (required)

**Response:**
```json
{
  "success": true,
  "data": {
    "message": "License uploaded successfully",
    "file_url": "https://example.com/wp-content/uploads/yolo-licenses/123/license_front_1234567890.jpg",
    "file_type": "front"
  }
}
```

---

## Email Templates

### Guest Credentials Email

**Subject:** Your Guest Account - YOLO Charters

**Content:**
```
Dear [Customer Name],

Your guest account has been created!

Login Details:
Username: [customer@email.com]
Password: [123YoLo]

Login here: [login_url]

Once logged in, you can:
- View your booking details
- Upload your sailing license (front and back)
- Track your charter information

Please upload your sailing license before your charter date.

Best regards,
YOLO Charters Team
```

---

## Troubleshooting

### Guest cannot log in
- **Check:** Email was sent with correct credentials
- **Verify:** Guest dashboard page exists with slug `guest-dashboard`
- **Test:** Try logging in with username (email) and password format `[booking_id]YoLo`

### Guest redirected to wp-admin instead of dashboard
- **Check:** Page slug is exactly `guest-dashboard`
- **Verify:** Page is published (not draft)
- **Clear:** Browser cache and try again

### License upload fails
- **Check:** File size is under 5MB
- **Verify:** File type is JPG, PNG, or GIF
- **Ensure:** Upload directory is writable: `/wp-content/uploads/yolo-licenses/`

### Admin panel shows no licenses
- **Verify:** Guest has uploaded files
- **Check:** Database table `wp_yolo_license_uploads` exists
- **Refresh:** Admin panel page

---

## Customization

### Change Password Format

Edit `includes/class-yolo-ys-guest-users.php`, line ~113:

```php
// Current format: [booking_id]YoLo
$password = $confirmation_number . 'YoLo';

// Custom format examples:
$password = 'YOLO' . $confirmation_number;           // YOLO123
$password = $confirmation_number . '@Yacht2024';     // 123@Yacht2024
$password = wp_generate_password(12, true, true);    // Random strong password
```

### Customize Dashboard Design

Edit `public/css/guest-dashboard.css` to change colors, layout, or styling.

**Key CSS classes:**
- `.yolo-guest-dashboard` - Main container
- `.yolo-booking-card` - Individual booking card
- `.yolo-license-upload` - License upload section
- `.yolo-upload-btn` - Upload button

### Add Custom Fields to Dashboard

Edit `public/partials/yolo-ys-guest-dashboard.php` to add more booking information or custom sections.

---

## Version History

### v2.5.6 (Current)
- ✅ Guest user auto-creation after booking
- ✅ Guest dashboard shortcode
- ✅ License upload (front + back)
- ✅ Admin panel for viewing uploads
- ✅ Email notifications with credentials
- ✅ Login redirect to dashboard
- ✅ wp-admin access prevention

---

## Support

For issues or questions:
1. Check this documentation
2. Review error logs in WordPress admin
3. Contact plugin developer

---

**Last Updated:** November 30, 2025  
**Plugin Version:** 2.5.6  
**Database Version:** 1.5
