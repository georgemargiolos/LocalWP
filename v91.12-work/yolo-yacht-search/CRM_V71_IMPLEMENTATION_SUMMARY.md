# CRM Module Implementation Summary (v71.0)

This document provides a technical overview of the Customer Relationship Management (CRM) module introduced in version 71.0 of the YOLO Yacht Search plugin. It is intended for developers and quality assurance personnel for debugging, review, and future maintenance.

## 1. Project Goal

The primary goal was to build a comprehensive, in-house CRM system to manage leads and customers, track all interactions, and provide tools for sales and booking management directly within the WordPress admin interface.

## 2. Key Features Implemented

The CRM module is accessible via a new "CRM" submenu under the main "YOLO Yacht Search" menu.

| Feature | Description | Implementation Detail |
| :--- | :--- | :--- |
| **Customer Management** | Centralized database for leads and customers. | New database table `yolo_crm_customers`. |
| **Pipeline Stages** | Tracks customer progress through defined stages. | Statuses: `new`, `contacted`, `qualified`, `proposal_sent`, `negotiating`, `booked`, `lost`. |
| **Activity Timeline** | Logs all customer interactions. | New database table `yolo_crm_activities`. Triggered by hooks (`yolo_quote_request_submitted`, `yolo_contact_message_submitted`, `yolo_booking_created`) and manual AJAX calls. |
| **Reminders** | Allows staff to set follow-up tasks. | New database table `yolo_crm_reminders`. Hourly cron job (`yolo_crm_check_reminders`) sends email notifications to the assigned user. |
| **Manual Booking** | Creates a new booking record and a guest user account. | AJAX handler `ajax_create_manual_booking`. Guest password is set to `{BM_Reservation_ID}YoLo`. |
| **Send Offer** | Sends a custom offer email to the customer. | AJAX handler `ajax_send_offer`. Automatically changes customer status to `proposal_sent` if in an early stage. |
| **Data Migration** | Imports existing data from legacy tables. | Static method `migrate_existing_data()` called on activation. Imports from `yolo_quote_requests`, `yolo_contact_messages`, and `yolo_bookings`. |
| **Customer Tagging** | Allows categorization of customers. | New tables `yolo_crm_tags` and `yolo_crm_customer_tags`. |

## 3. Architectural Changes and Integration

The CRM is implemented primarily within the `YOLO_YS_CRM` class (`includes/class-yolo-ys-crm.php`).

### 3.1. Database Structure

Five new tables were introduced:
1.  `yolo_crm_customers`: Stores customer profiles, status, source, assigned user, and stats (bookings count, revenue).
2.  `yolo_crm_activities`: Stores a chronological log of all customer interactions.
3.  `yolo_crm_reminders`: Stores follow-up tasks, due dates, and assignment.
4.  `yolo_crm_tags`: Stores a list of available tags (e.g., 'VIP', 'Repeat Customer').
5.  `yolo_crm_customer_tags`: Links customers to tags (many-to-many relationship).

### 3.2. Core Logic

-   **Customer Upsert (`create_or_update_customer`)**: This method is the single point of entry for creating new customers or updating existing ones based on email address. It is called by all hooks (quote request, contact message, booking).
-   **Activity Logging (`log_activity`)**: This method records an event in the `yolo_crm_activities` table and updates the customer's `last_activity_at` timestamp.
-   **Hooks**: The CRM is integrated into the existing lead generation process via the following actions:
    -   `add_action('yolo_quote_request_submitted', ...)`
    -   `add_action('yolo_contact_message_submitted', ...)`
    -   `add_action('yolo_booking_created', ...)`

### 3.3. Admin Interface

-   A new menu item is added in `admin/class-yolo-ys-admin.php` via `add_submenu_page`.
-   The UI is rendered by `admin/partials/crm-page.php`.
-   All dynamic content (customer list, filtering, status updates, activity logging) is handled via dedicated AJAX endpoints defined in `YOLO_YS_CRM::__construct()` and implemented in `admin/js/crm.js`.

## 4. File Changes Summary

| File | Change Type | Description |
| :--- | :--- | :--- |
| `includes/class-yolo-ys-crm.php` | **NEW** | Main CRM class with all core logic, database methods, AJAX handlers, and cron job. |
| `admin/partials/crm-page.php` | **NEW** | HTML/PHP template for the CRM list and detail views, including all modal forms. |
| `admin/css/crm.css` | **NEW** | Dedicated CSS for the CRM interface (list, detail, timeline, modals). |
| `admin/js/crm.js` | **NEW** | Frontend JavaScript for all AJAX interactions, pagination, and modal handling. |
| `yolo-yacht-search.php` | MODIFIED | Updated plugin version to `71.0` and included `class-yolo-ys-crm.php`. |
| `includes/class-yolo-ys-activator.php` | MODIFIED | Added calls to `YOLO_YS_CRM::create_tables()` and `YOLO_YS_CRM::migrate_existing_data()` on activation. |
| `admin/class-yolo-ys-admin.php` | MODIFIED | Added the "CRM" submenu and the `display_crm_page` method. |
| `includes/class-yolo-ys-quote-requests.php` | MODIFIED | Added `do_action('yolo_quote_request_submitted', ...)` hook after saving a quote request. |
| `includes/class-yolo-ys-contact-messages.php` | MODIFIED | Added `do_action('yolo_contact_message_submitted', ...)` hook after saving a contact message. |

## 5. Potential Debugging Areas

| Area | Description | Check/Action |
| :--- | :--- | :--- |
| **Migration** | If existing data is missing in the CRM. | Check `yolo_crm_migration_completed` option. Run manual migration via the button on the CRM list page. Verify field mapping in `migrate_existing_data()`. |
| **AJAX Failures** | If customer list or actions fail. | Check browser console for errors. Verify `yolo_crm_nonce` in `admin/js/crm.js` and `YOLO_YS_CRM::ajax_*` methods. |
| **Reminders** | If email notifications are not sent. | Verify WordPress cron is running. Check `YOLO_YS_CRM::check_due_reminders()` logic and ensure `wp_schedule_event` is not blocked. |
| **Manual Booking** | If guest user is not created or password is wrong. | Check `YOLO_YS_CRM::ajax_create_manual_booking()` for `wp_create_user` errors. Verify password format: `{BM_Reservation_ID}YoLo`. |
| **Timeline Gaps** | If an interaction is not logged. | Ensure the relevant hook (`yolo_quote_request_submitted`, etc.) is correctly triggering `YOLO_YS_CRM::handle_*` methods. |

---
*Document Author: Manus AI*
*Date: December 18, 2025*
