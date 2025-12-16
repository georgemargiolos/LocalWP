# YOLO Yacht Search - Manus Code Changes for Cursor Debug

**Date:** December 16, 2025  
**Issue:** JavaScript SyntaxError "Invalid or unexpected token" causing blank yacht details page  
**Error Location:** Line 2042:35 in rendered page output

---

## SUMMARY OF CHANGES MADE BY MANUS

I added Facebook event tracking with deduplication for AddToCart, InitiateCheckout, and Lead events. The changes span 6 files.

---

## CURRENT ERROR

```
Uncaught SyntaxError: Invalid or unexpected token (at yacht-details-page/?...:2042:35)
```

The yacht details page is blank. The error appears to be in the JavaScript output.

---

## FILE 1: yacht-details-v3-scripts.php

**Location:** `public/templates/partials/yacht-details-v3-scripts.php`

### Change 1: Added AJAX call in bookNow() function (around line 616)

```php
// Track AddToCart event (server-side CAPI + client-side Pixel with deduplication)
jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    type: 'POST',
    data: {
        action: 'yolo_track_add_to_cart',
        yacht_id: yachtId,
        yacht_name: yachtName,
        price: totalPrice
    },
    success: function(response) {
        if (response.success && response.data.event_id) {
            // Track on client-side with same event_id for deduplication
            if (typeof YoloAnalytics !== 'undefined') {
                YoloAnalytics.trackSelectWeek({
                    yacht_id: yachtId,
                    yacht_name: yachtName,
                    price: totalPrice,
                    currency: currency
                }, response.data.event_id);
            }
        }
    }
});
```

### Change 2: Added AJAX call in booking form submission (around line 770)

```php
// Track InitiateCheckout event (server-side CAPI + client-side Pixel with deduplication)
jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    type: 'POST',
    data: {
        action: 'yolo_track_initiate_checkout',
        yacht_id: yachtId,
        yacht_name: yachtName,
        price: totalPrice,
        date_from: dateFrom,
        date_to: dateTo
    },
    success: function(response) {
        if (response.success && response.data.event_id) {
            // Track on client-side with same event_id for deduplication
            if (typeof YoloAnalytics !== 'undefined') {
                YoloAnalytics.trackBeginCheckout({
                    yacht_id: yachtId,
                    yacht_name: yachtName,
                    price: totalPrice,
                    currency: currency,
                    date_from: dateFrom,
                    date_to: dateTo
                }, response.data.event_id);
            }
        }
    }
});
```

### Change 3: Added Lead tracking in quote form success handler (around line 1000)

```php
// Track Lead event on client-side with event_id from server-side for deduplication
if (data.data.event_id && typeof YoloAnalytics !== 'undefined') {
    YoloAnalytics.trackLead({
        yacht_id: "<?php echo esc_attr($yacht->id ?? ''); ?>",
        yacht_name: "<?php echo esc_js($yacht->name ?? ''); ?>",
        value: 0
    }, data.data.event_id);
}
```

---

## FILE 2: class-yolo-ys-public.php

**Location:** `public/class-yolo-ys-public.php`

### Added two new AJAX handler methods at end of class (before closing brace):

```php
/**
 * AJAX handler for tracking AddToCart event (BOOK NOW button click)
 */
public function ajax_track_add_to_cart() {
    // Get yacht data from request
    $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
    $yacht_name = isset($_POST['yacht_name']) ? sanitize_text_field($_POST['yacht_name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    
    // Track AddToCart event via Facebook CAPI
    // Note: track_add_to_cart($yacht_id, $price, $yacht_name) - price is 2nd param
    $event_id = false;
    if (function_exists('yolo_analytics')) {
        $event_id = yolo_analytics()->track_add_to_cart($yacht_id, $price, $yacht_name);
    }
    
    wp_send_json_success(array(
        'event_id' => $event_id
    ));
}

/**
 * AJAX handler for tracking InitiateCheckout event (booking form submission)
 */
public function ajax_track_initiate_checkout() {
    // Get booking data from request
    $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
    $yacht_name = isset($_POST['yacht_name']) ? sanitize_text_field($_POST['yacht_name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
    
    // Track InitiateCheckout event via Facebook CAPI
    // Note: track_begin_checkout($yacht_id, $price, $yacht_name) - correct method name
    $event_id = false;
    if (function_exists('yolo_analytics')) {
        $event_id = yolo_analytics()->track_begin_checkout($yacht_id, $price, $yacht_name);
    }
    
    wp_send_json_success(array(
        'event_id' => $event_id
    ));
}
```

---

## FILE 3: class-yolo-ys-yacht-search.php

**Location:** `includes/class-yolo-ys-yacht-search.php`

### Added AJAX action registrations in define_public_hooks() method:

```php
// AJAX handlers for Facebook event tracking
$this->loader->add_action('wp_ajax_yolo_track_add_to_cart', $plugin_public, 'ajax_track_add_to_cart');
$this->loader->add_action('wp_ajax_nopriv_yolo_track_add_to_cart', $plugin_public, 'ajax_track_add_to_cart');
$this->loader->add_action('wp_ajax_yolo_track_initiate_checkout', $plugin_public, 'ajax_track_initiate_checkout');
$this->loader->add_action('wp_ajax_nopriv_yolo_track_initiate_checkout', $plugin_public, 'ajax_track_initiate_checkout');
```

---

## FILE 4: class-yolo-ys-analytics.php

**Location:** `includes/class-yolo-ys-analytics.php`

### Fixed session_start() issue (around line 99):

**Before (broken):**
```php
// Store event_id in session for client-side deduplication
if (!session_id()) {
    session_start();
}
$_SESSION['yolo_last_fb_event_id'] = $event_id;
```

**After (fixed):**
```php
// Store event_id in session for client-side deduplication
// FIXED: Check headers_sent() to prevent PHP warnings that break JavaScript output
if (!session_id() && !headers_sent()) {
    @session_start();
}
if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION['yolo_last_fb_event_id'] = $event_id;
}
```

---

## FILE 5: yolo-analytics.js

**Location:** `public/js/yolo-analytics.js`

### Modified trackSelectWeek() to accept eventId parameter:

```javascript
trackSelectWeek: function(p, eventId) {
    // ... existing code ...
    
    // Send to Facebook Pixel with event_id from server-side for deduplication
    sendToFacebookPixel('AddToCart', {
        content_type: 'product',
        content_ids: [String(p.yacht_id || p.id)],
        content_name: p.yacht_name || p.name,
        currency: p.currency,
        value: p.price
    }, eventId || null);
}
```

### Modified trackBeginCheckout() to accept eventId parameter:

```javascript
trackBeginCheckout: function(p, eventId) {
    // ... existing code ...
    
    // Send to Facebook Pixel with event_id from server-side for deduplication
    sendToFacebookPixel('InitiateCheckout', {
        content_type: 'product',
        content_ids: [String(p.yacht_id || p.id)],
        content_name: p.yacht_name || p.name,
        currency: p.currency,
        value: p.price
    }, eventId || null);
}
```

### Added new trackLead() method:

```javascript
trackLead: function(p, eventId) {
    // Send to dataLayer for GA4
    pushToDataLayer('generate_lead', {
        currency: 'EUR',
        value: p.value || 0
    });
    
    // Send to Facebook Pixel with event_id from server-side for deduplication
    sendToFacebookPixel('Lead', {
        content_name: p.yacht_name || '',
        currency: 'EUR',
        value: p.value || 0
    }, eventId || null);
}
```

---

## FILE 6: yolo-yacht-search.php (Main Plugin File)

**Location:** `yolo-yacht-search.php`

### Wrapped initialization in WordPress hooks:

**Before (broken):**
```php
run_yolo_yacht_search();

// Initialize analytics and SEO (v41.19)
yolo_analytics();
yolo_meta_tags();

// Initialize icons admin
if (is_admin()) {
    $icons_admin = new YOLO_YS_Icons_Admin();
    // ...
}

// Initialize warehouse notifications system
new YOLO_YS_Warehouse_Notifications();

// Initialize auto-sync system (v30.0)
new YOLO_YS_Auto_Sync();
```

**After (fixed):**
```php
run_yolo_yacht_search();

// Initialize analytics and SEO (v41.19) - Wrapped in plugins_loaded hook
add_action('plugins_loaded', function() {
    yolo_analytics();
    yolo_meta_tags();
}, 10);

// Initialize icons admin (admin-only) - Wrapped in admin_init hook
add_action('admin_init', function() {
    $icons_admin = new YOLO_YS_Icons_Admin();
    // ...
}, 10);

// Initialize warehouse notifications system - Wrapped in plugins_loaded hook
add_action('plugins_loaded', function() {
    new YOLO_YS_Warehouse_Notifications();
}, 10);

// Initialize auto-sync system (v30.0) - Wrapped in plugins_loaded hook
add_action('plugins_loaded', function() {
    new YOLO_YS_Auto_Sync();
}, 10);
```

---

## FILE 7: class-yolo-ys-quote-handler.php

**Location:** `includes/class-yolo-ys-quote-handler.php`

### Modified to return event_id for client-side deduplication:

```php
// Track lead generation event (server-side Facebook Conversions API)
$event_id = false;
if (function_exists('yolo_analytics')) {
    $user_data = array(
        'email' => $email,
        'phone' => $phone,
        'first_name' => $first_name,
        'last_name' => $last_name
    );
    $event_id = yolo_analytics()->track_generate_lead(0, $user_data);
}

// ... later in response ...
wp_send_json_success(array(
    'message' => 'Quote request submitted successfully! We will contact you soon.',
    'quote_id' => $quote_id,
    'event_id' => $event_id  // NEW: Added for client-side deduplication
));
```

---

## POSSIBLE CAUSES OF THE ERROR

1. **PHP output before JavaScript** - A PHP warning or notice might be outputting before the `<script>` tag
2. **Special characters in yacht data** - The yacht name or other data might contain characters that break JavaScript strings
3. **Template literal issue** - The booking form modal uses template literals with `${}` which might conflict with PHP
4. **Missing variable** - A variable used in my code might not be defined in certain contexts

---

## DEBUGGING SUGGESTIONS

1. Check if there's any PHP output before the `<script>` tag at line 1344
2. Check if `$yacht->id` or `$yacht->name` contain special characters
3. Check if the `esc_js()` function is properly escaping the yacht name
4. Look for any PHP errors in the error log
5. Try removing my changes one by one to isolate the issue

---

## HOW TO REVERT

To completely revert my changes:

```bash
cd /path/to/plugin
git checkout 64f7d2d -- yolo-yacht-search/
```

This will restore all files to the state before my changes (v60.6.3).

---

## CONTACT

These changes were made by Manus AI on December 16, 2025.
