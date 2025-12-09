<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Booking Confirmation Template
 * Shortcode: [yolo_booking_confirmation]
 * 
 * v20.3 FIX: 
 * - Functions defined BEFORE they are called
 * - Removed PHP sleep() blocking - uses AJAX polling
 * - Guest user creation happens for ALL bookings (not just new ones)
 */

// ============================================
// FUNCTION DEFINITIONS (must come first!)
// ============================================

/**
 * Ensure guest user exists for a booking
 */
if (!function_exists('yolo_ensure_guest_user_exists')) {
function yolo_ensure_guest_user_exists($booking) {
    if (empty($booking->customer_email)) {
        error_log('YOLO YS: Cannot create guest user - no email for booking #' . $booking->id);
        return;
    }
    
    $existing_user = get_user_by('email', $booking->customer_email);
    if ($existing_user) {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        if (empty($booking->user_id)) {
            $wpdb->update(
                $table_bookings,
                array('user_id' => $existing_user->ID),
                array('id' => $booking->id),
                array('%d'),
                array('%d')
            );
        }
        return;
    }
    
    if (!class_exists('YOLO_YS_Guest_Users')) {
        return;
    }
    
    $guest_manager = new YOLO_YS_Guest_Users();
    
    $customer_first_name = '';
    $customer_last_name = '';
    if (!empty($booking->customer_name)) {
        $name_parts = explode(' ', trim($booking->customer_name), 2);
        $customer_first_name = isset($name_parts[0]) ? $name_parts[0] : $booking->customer_name;
        $customer_last_name = isset($name_parts[1]) ? $name_parts[1] : '';
    }
    
    $guest_result = $guest_manager->create_guest_user(
        $booking->id,
        $booking->customer_email,
        $customer_first_name,
        $customer_last_name,
        $booking->id
    );
    
    if ($guest_result['success'] && !empty($guest_result['password'])) {
        $login_url = wp_login_url(home_url('/guest-dashboard'));
        $credentials_subject = 'Your Guest Account - YOLO Charters';
        $credentials_message = sprintf(
            "Your guest account has been created!\n\nUsername: %s\nPassword: %s\n\nLogin here: %s",
            $guest_result['username'],
            $guest_result['password'],
            $login_url
        );
        wp_mail($booking->customer_email, $credentials_subject, $credentials_message);
    }
}
}

/**
 * Show the booking confirmation details
 */
if (!function_exists('yolo_show_booking_confirmation')) {
function yolo_show_booking_confirmation($booking) {
    $deposit_percentage = get_option('yolo_ys_deposit_percentage', 50);
    ?>
    <div class="container py-5">
        <div class="yolo-booking-confirmation">
        <div class="confirmation-header">
            <div class="success-icon">
                <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                    <circle cx="40" cy="40" r="38" stroke="#10b981" stroke-width="4"/>
                    <path d="M25 40L35 50L55 30" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1><?php yolo_ys_text_e('booking_confirmed', 'Booking Confirmed!'); ?></h1>
            <p class="confirmation-subtitle">Thank you for your booking. Your yacht charter is confirmed.</p>
        </div>
        
        <div class="confirmation-content">
            <div class="booking-details-card">
                <h2>Booking Details</h2>
                
                <div class="detail-row">
                    <span class="detail-label"><?php yolo_ys_text_e('booking_reference', 'Booking Reference'); ?>:</span>
                    <span class="detail-value"><strong><?php echo esc_html($booking->bm_reservation_id ? $booking->bm_reservation_id : '#' . $booking->id); ?></strong></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Yacht:</span>
                    <span class="detail-value"><?php echo esc_html($booking->yacht_name); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Charter Period:</span>
                    <span class="detail-value">
                        <?php echo date('F j, Y', strtotime($booking->date_from)); ?> - 
                        <?php echo date('F j, Y', strtotime($booking->date_to)); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value">
                        <?php 
                        $days = (strtotime($booking->date_to) - strtotime($booking->date_from)) / 86400;
                        echo intval($days) . ' days';
                        ?>
                    </span>
                </div>
            </div>
            
            <div class="payment-details-card">
                <h2>Payment Summary</h2>
                
                <div class="detail-row">
                    <span class="detail-label">Total Charter Price:</span>
                    <span class="detail-value">
                        <?php echo YOLO_YS_Price_Formatter::format_price($booking->total_price, $booking->currency); ?>
                    </span>
                </div>
                
                <div class="detail-row highlight">
                    <span class="detail-label">Deposit Paid (<?php echo $deposit_percentage; ?>%):</span>
                    <span class="detail-value success">
                        <?php echo YOLO_YS_Price_Formatter::format_price($booking->deposit_paid, $booking->currency); ?>
                    </span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Remaining Balance:</span>
                    <span class="detail-value">
                        <?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?>
                    </span>
                </div>
                
                <div class="payment-note">
                    <p><strong>Important:</strong> The remaining balance of <?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?> is due before your charter date. We will contact you with payment instructions.</p>
                </div>
            </div>
            
            <div class="customer-details-card">
                <h2>Contact Information</h2>
                
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo esc_html($booking->customer_name); ?></span>
                </div>
                
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo esc_html($booking->customer_email); ?></span>
                </div>
            </div>
            
            <div class="next-steps-card">
                <h2>What's Next?</h2>
                <ol class="next-steps-list">
                    <li><strong>Confirmation Email:</strong> Check your inbox at <?php echo esc_html($booking->customer_email); ?> for your booking confirmation.</li>
                    <li><strong>Guest Dashboard:</strong> Login to your <a href="<?php echo home_url('/guest-dashboard'); ?>">Guest Dashboard</a> to upload your sailing license.</li>
                    <li><strong>Remaining Payment:</strong> We will contact you 30 days before your charter with payment instructions.</li>
                    <li><strong>Questions?</strong> Contact us at <a href="mailto:info@yolo-charters.com">info@yolo-charters.com</a> or call +30 698 506 4875.</li>
                </ol>
            </div>
            
            <div class="action-buttons">
                <a href="<?php echo home_url(); ?>" class="btn btn-primary">Return to Home</a>
                <a href="<?php echo home_url('/guest-dashboard'); ?>" class="btn btn-secondary">Go to Guest Dashboard</a>
            </div>
        </div>
    </div>
    </div>
    
    <!-- Purchase Event Tracking (GA4 + Facebook) -->
    <script>
    // Track Purchase event for GA4 (via GTM)
    if (typeof window.dataLayer !== 'undefined') {
        window.dataLayer.push({
            event: 'purchase',
            transaction_id: '<?php echo esc_js($booking->stripe_session_id ? $booking->stripe_session_id : 'booking-' . $booking->id); ?>',
            currency: '<?php echo esc_js($booking->currency); ?>',
            value: <?php echo floatval($booking->total_price); ?>,
            items: [{
                item_id: '<?php echo esc_js($booking->yacht_id); ?>',
                item_name: '<?php echo esc_js($booking->yacht_name); ?>',
                price: <?php echo floatval($booking->total_price); ?>,
                quantity: 1
            }]
        });
        console.log('YOLO Analytics: Purchase event tracked (GA4)');
    }
    
    // Track Purchase event for Facebook Pixel (client-side)
    if (typeof fbq !== 'undefined') {
        fbq('track', 'Purchase', {
            content_type: 'product',
            content_ids: ['<?php echo esc_js($booking->yacht_id); ?>'],
            content_name: '<?php echo esc_js($booking->yacht_name); ?>',
            currency: '<?php echo esc_js($booking->currency); ?>',
            value: <?php echo floatval($booking->total_price); ?>,
            order_id: '<?php echo esc_js($booking->stripe_session_id ? $booking->stripe_session_id : 'booking-' . $booking->id); ?>'
        }, {
            eventID: 'purchase_<?php echo esc_js($booking->id); ?>_<?php echo time(); ?>'
        });
        console.log('YOLO Analytics: Purchase event tracked (Facebook Pixel)');
    }
    </script>
    <?php
}
}

/**
 * Create booking from Stripe session
 */
if (!function_exists('yolo_create_booking_from_stripe')) {
function yolo_create_booking_from_stripe($session_id, $wpdb, $table_bookings) {
    try {
        $secret_key = get_option('yolo_ys_stripe_secret_key', '');
        if (empty($secret_key)) {
            return null;
        }
        
        \Stripe\Stripe::setApiKey($secret_key);
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        
        if ($session->payment_status !== 'paid') {
            return null;
        }
        
        $yacht_id = $session->metadata->yacht_id;
        $yacht_name = $session->metadata->yacht_name;
        $date_from = $session->metadata->date_from;
        $date_to = $session->metadata->date_to;
        $total_price = $session->metadata->total_price;
        $deposit_amount = $session->metadata->deposit_amount;
        $remaining_balance = $session->metadata->remaining_balance;
        $currency = isset($session->metadata->currency) ? $session->metadata->currency : 'EUR';
        
        $customer_first_name = isset($session->metadata->customer_first_name) ? $session->metadata->customer_first_name : '';
        $customer_last_name = isset($session->metadata->customer_last_name) ? $session->metadata->customer_last_name : '';
        $customer_name = isset($session->metadata->customer_name) ? $session->metadata->customer_name : '';
        $customer_email = isset($session->metadata->customer_email) ? $session->metadata->customer_email : '';
        $customer_phone = isset($session->metadata->customer_phone) ? $session->metadata->customer_phone : '';
        
        if (empty($customer_email) && isset($session->customer_details->email)) {
            $customer_email = $session->customer_details->email;
        }
        if (empty($customer_name) && isset($session->customer_details->name)) {
            $customer_name = $session->customer_details->name;
        }
        
        $wpdb->insert($table_bookings, array(
            'yacht_id' => $yacht_id,
            'yacht_name' => $yacht_name,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'total_price' => $total_price,
            'deposit_paid' => $deposit_amount,
            'remaining_balance' => $remaining_balance,
            'currency' => $currency,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'stripe_session_id' => $session_id,
            'stripe_payment_intent' => isset($session->payment_intent) ? $session->payment_intent : '',
            'payment_status' => 'deposit_paid',
            'booking_status' => 'confirmed',
            'created_at' => current_time('mysql'),
        ));
        
        $booking_id = $wpdb->insert_id;
        if (!$booking_id) {
            return null;
        }
        
        // Create reservation in Booking Manager
        $api = new YOLO_YS_Booking_Manager_API();
        $reservation_data = array(
            'yachtId' => (int)$yacht_id,
            'dateFrom' => date('Y-m-d\TH:i:s', strtotime($date_from)),
            'dateTo' => date('Y-m-d\TH:i:s', strtotime($date_to)),
            'client' => array('name' => $customer_name, 'email' => $customer_email),
            'status' => 1,
            'sendNotification' => true,
            'note' => 'Online booking via YOLO Charters. Stripe: ' . ($session->payment_intent ?? ''),
        );
        
        $result = $api->create_reservation($reservation_data);
        
        if ($result['success']) {
            $bm_reservation_id = isset($result['data']['id']) ? $result['data']['id'] : null;
            $wpdb->update($table_bookings, array('bm_reservation_id' => $bm_reservation_id), array('id' => $booking_id));
            
            if ($bm_reservation_id) {
                $api->create_payment($bm_reservation_id, array(
                    'amount' => floatval($deposit_amount),
                    'currency' => $currency,
                    'paymentDate' => current_time('Y-m-d\TH:i:s'),
                    'paymentMethod' => 'Credit Card (Stripe)',
                    'note' => 'Deposit. Stripe: ' . ($session->payment_intent ?? ''),
                ));
            }
        }
        
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_bookings} WHERE id = %d", $booking_id));
        
        if ($booking) {
            yolo_ensure_guest_user_exists($booking);
            try {
                YOLO_YS_Email::send_booking_confirmation($booking);
                YOLO_YS_Email::send_admin_notification($booking);
            } catch (Exception $e) {
                error_log('YOLO YS: Email error - ' . $e->getMessage());
            }
            
            // Track Purchase event via Facebook CAPI (server-side)
            if (function_exists('yolo_analytics')) {
                $transaction_id = $booking->stripe_session_id ? $booking->stripe_session_id : 'booking-' . $booking->id;
                $user_data = array(
                    'em' => $customer_email,
                    'ph' => $customer_phone,
                    'fn' => $customer_first_name,
                    'ln' => $customer_last_name
                );
                
                yolo_analytics()->track_purchase(
                    $transaction_id,
                    $yacht_id,
                    $total_price,
                    $yacht_name,
                    $user_data
                );
                
                error_log('YOLO YS: Purchase event tracked via CAPI for booking #' . $booking_id);
            }
        }
        
        return $booking;
        
    } catch (Exception $e) {
        error_log('YOLO YS: Booking creation failed - ' . $e->getMessage());
        return null;
    }
}
}

/**
 * Show loading page with AJAX polling
 */
if (!function_exists('yolo_show_loading_page')) {
function yolo_show_loading_page($session_id) {
    ?>
    <div id="yolo-booking-loading" style="text-align: center; padding: 60px 20px;">
        <div style="border: 4px solid #f3f3f3; border-top: 4px solid #dc2626; border-radius: 50%; width: 60px; height: 60px; animation: yolo-spin 1s linear infinite; margin: 0 auto 20px;"></div>
        <h2 style="color: #374151; margin-bottom: 10px;">Processing Your Booking</h2>
        <p id="loading-status" style="color: #6b7280; font-size: 16px;">Confirming your payment with Stripe...</p>
        <p style="color: #9ca3af; font-size: 14px; margin-top: 20px;">This usually takes just a few seconds.</p>
    </div>
    
    <div id="yolo-booking-error" style="display: none; text-align: center; padding: 40px;">
        <h2 style="color: #dc2626;">Processing Taking Longer Than Expected</h2>
        <p style="color: #6b7280;">Your payment was successful! We're just finishing up the booking details.</p>
        <p style="color: #6b7280;">Please check your email for confirmation, or <a href="javascript:location.reload()">refresh this page</a>.</p>
        <p style="margin-top: 20px;"><a href="<?php echo esc_url(home_url()); ?>" style="display: inline-block; padding: 12px 24px; background: #dc2626; color: white; text-decoration: none; border-radius: 6px;">Return to Home</a></p>
    </div>
    
    <style>@keyframes yolo-spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>
    
    <script>
    (function() {
        var maxAttempts = 30, attempts = 0;
        var sessionId = '<?php echo esc_js($session_id); ?>';
        
        function checkBooking() {
            attempts++;
            if (attempts > maxAttempts) {
                document.getElementById('yolo-booking-loading').style.display = 'none';
                document.getElementById('yolo-booking-error').style.display = 'block';
                return;
            }
            
            var statusEl = document.getElementById('loading-status');
            if (statusEl) statusEl.textContent = 'Confirming your booking... (' + attempts + '/' + maxAttempts + ')';
            
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '<?php echo admin_url('admin-ajax.php'); ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success && response.data.found) {
                            window.location.reload();
                        } else {
                            setTimeout(checkBooking, 2000);
                        }
                    } catch (e) {
                        setTimeout(checkBooking, 2000);
                    }
                }
            };
            xhr.send('action=yolo_check_booking_status&session_id=' + encodeURIComponent(sessionId));
        }
        
        setTimeout(checkBooking, 2000);
    })();
    </script>
    <?php
}
}

// ============================================
// MAIN TEMPLATE LOGIC (uses functions above)
// ============================================

$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

if (empty($session_id)) {
    echo '<div class="yolo-booking-error"><h2>Invalid Booking Reference</h2><p>We could not find your booking. Please check your email for confirmation details.</p></div>';
    return;
}

global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';

$booking = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_bookings} WHERE stripe_session_id = %s",
    $session_id
));

if ($booking) {
    yolo_ensure_guest_user_exists($booking);
    yolo_show_booking_confirmation($booking);
    return;
}

$booking = yolo_create_booking_from_stripe($session_id, $wpdb, $table_bookings);

if ($booking) {
    yolo_show_booking_confirmation($booking);
    return;
}

yolo_show_loading_page($session_id);
