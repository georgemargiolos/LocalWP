<?php
/**
 * Booking Confirmation Template
 * Shortcode: [yolo_booking_confirmation]
 * 
 * v2.7.5 FIX: 
 * - Removed PHP sleep() blocking - uses AJAX polling
 * - Guest user creation happens for ALL bookings (not just new ones)
 * - Shows BOTH booking references for clarity
 */

// Get session ID from URL
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

if (empty($session_id)) {
    echo '<div class="yolo-booking-error">';
    echo '<h2>Invalid Booking Reference</h2>';
    echo '<p>We could not find your booking. Please check your email for confirmation details.</p>';
    echo '</div>';
    return;
}

// Check if booking already exists
global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';

$booking = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_bookings} WHERE stripe_session_id = %s",
    $session_id
));

// If booking exists, ensure guest user exists then show confirmation
if ($booking) {
    // CRITICAL FIX: Always ensure guest user exists (even for existing bookings)
    ensure_guest_user_exists($booking);
    show_booking_confirmation($booking);
    return;
}

// If booking doesn't exist yet, try to create it from Stripe session
$booking = create_booking_from_stripe($session_id, $wpdb, $table_bookings);

if ($booking) {
    show_booking_confirmation($booking);
    return;
}

// If still no booking, show AJAX-based loading page
show_loading_page($session_id);

/**
 * CRITICAL FIX: Ensure guest user exists for a booking
 * This runs EVERY time the confirmation page is shown, not just on booking creation
 */
function ensure_guest_user_exists($booking) {
    if (empty($booking->customer_email)) {
        error_log('YOLO YS: Cannot create guest user - no email for booking #' . $booking->id);
        return;
    }
    
    // Check if user already exists for this email
    $existing_user = get_user_by('email', $booking->customer_email);
    if ($existing_user) {
        // User exists - make sure booking is linked to user
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
            error_log('YOLO YS: Linked existing user ' . $existing_user->ID . ' to booking #' . $booking->id);
        }
        return;
    }
    
    // No user exists - create one
    if (!class_exists('YOLO_YS_Guest_Users')) {
        error_log('YOLO YS ERROR: YOLO_YS_Guest_Users class not found!');
        return;
    }
    
    error_log('YOLO YS: Creating guest user for booking #' . $booking->id);
    
    $guest_manager = new YOLO_YS_Guest_Users();
    
    // Split customer name into first/last
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
        $booking->id // confirmation number = booking ID
    );
    
    error_log('YOLO YS: Guest user creation result: ' . print_r($guest_result, true));
    
    // Send credentials email if user was newly created
    if ($guest_result['success'] && !empty($guest_result['password'])) {
        $login_url = wp_login_url(home_url('/guest-dashboard'));
        $credentials_subject = 'Your Guest Account - YOLO Charters';
        $credentials_message = sprintf(
            "Your guest account has been created!\n\n" .
            "Username: %s\n" .
            "Password: %s\n\n" .
            "Login here: %s\n\n" .
            "You can view your bookings and upload your sailing license after logging in.",
            $guest_result['username'],
            $guest_result['password'],
            $login_url
        );
        wp_mail($booking->customer_email, $credentials_subject, $credentials_message);
        error_log('YOLO YS: Guest credentials email sent to ' . $booking->customer_email);
    }
}

/**
 * Show the booking confirmation details
 */
function show_booking_confirmation($booking) {
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
                    <li>
                        <strong>Confirmation Email:</strong> 
                        Check your inbox at <?php echo esc_html($booking->customer_email); ?> for your booking confirmation and receipt.
                    </li>
                    <li>
                        <strong>Guest Dashboard:</strong>
                        Login to your <a href="<?php echo home_url('/guest-dashboard'); ?>">Guest Dashboard</a> to upload your sailing license and view booking details.
                    </li>
                    <li>
                        <strong>Remaining Payment:</strong> 
                        We will contact you 30 days before your charter with instructions for the remaining balance payment.
                    </li>
                    <li>
                        <strong>Charter Details:</strong> 
                        You will receive detailed information about your yacht, check-in procedures, and what to bring closer to your charter date.
                    </li>
                    <li>
                        <strong>Questions?</strong> 
                        Contact us anytime at <a href="mailto:info@yolo-charters.com">info@yolo-charters.com</a> or call us at +30 698 506 4875.
                    </li>
                </ol>
            </div>
            
            <div class="action-buttons">
                <a href="<?php echo home_url(); ?>" class="btn btn-primary">Return to Home</a>
                <a href="<?php echo home_url('/guest-dashboard'); ?>" class="btn btn-secondary">Go to Guest Dashboard</a>
            </div>
        </div>
    </div>
    </div><!-- /container -->
    <?php
}

/**
 * Create booking from Stripe session (synchronous, no sleep)
 */
function create_booking_from_stripe($session_id, $wpdb, $table_bookings) {
    try {
        $secret_key = get_option('yolo_ys_stripe_secret_key', '');
        if (empty($secret_key)) {
            return null;
        }
        
        \Stripe\Stripe::setApiKey($secret_key);
        
        // Retrieve session from Stripe
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        
        // Check if payment was successful
        if ($session->payment_status !== 'paid') {
            return null;
        }
        
        // Extract booking details from metadata
        $yacht_id = $session->metadata->yacht_id;
        $yacht_name = $session->metadata->yacht_name;
        $date_from = $session->metadata->date_from;
        $date_to = $session->metadata->date_to;
        $total_price = $session->metadata->total_price;
        $deposit_amount = $session->metadata->deposit_amount;
        $remaining_balance = $session->metadata->remaining_balance;
        $currency = isset($session->metadata->currency) ? $session->metadata->currency : 'EUR';
        
        // Get customer details
        $customer_first_name = isset($session->metadata->customer_first_name) ? $session->metadata->customer_first_name : '';
        $customer_last_name = isset($session->metadata->customer_last_name) ? $session->metadata->customer_last_name : '';
        $customer_name = isset($session->metadata->customer_name) ? $session->metadata->customer_name : '';
        $customer_email = isset($session->metadata->customer_email) ? $session->metadata->customer_email : '';
        $customer_phone = isset($session->metadata->customer_phone) ? $session->metadata->customer_phone : '';
        
        // Fallback to Stripe customer_details
        if (empty($customer_email) && isset($session->customer_details->email)) {
            $customer_email = $session->customer_details->email;
        }
        if (empty($customer_name) && isset($session->customer_details->name)) {
            $customer_name = $session->customer_details->name;
        }
        
        // Create booking in database
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
            'client' => array(
                'name' => $customer_name,
                'email' => $customer_email,
            ),
            'status' => 1,
            'sendNotification' => true,
            'note' => 'Online booking via YOLO Charters. Stripe Payment: ' . (isset($session->payment_intent) ? $session->payment_intent : ''),
        );
        
        $result = $api->create_reservation($reservation_data);
        
        if ($result['success']) {
            $bm_reservation_id = isset($result['data']['id']) ? $result['data']['id'] : null;
            $wpdb->update(
                $table_bookings,
                array('bm_reservation_id' => $bm_reservation_id),
                array('id' => $booking_id)
            );
            
            // Record payment
            if ($bm_reservation_id) {
                $payment_data = array(
                    'amount' => floatval($deposit_amount),
                    'currency' => $currency,
                    'paymentDate' => current_time('Y-m-d\TH:i:s'),
                    'paymentMethod' => 'Credit Card (Stripe)',
                    'note' => 'Deposit payment. Stripe Payment Intent: ' . (isset($session->payment_intent) ? $session->payment_intent : ''),
                );
                $api->create_payment($bm_reservation_id, $payment_data);
            }
        }
        
        // Retrieve the booking
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_bookings} WHERE id = %d",
            $booking_id
        ));
        
        // Create guest user (this is for NEW bookings)
        if ($booking) {
            ensure_guest_user_exists($booking);
        }
        
        // Send confirmation emails
        if ($booking) {
            try {
                YOLO_YS_Email::send_booking_confirmation($booking);
                YOLO_YS_Email::send_admin_notification($booking);
            } catch (Exception $e) {
                error_log('YOLO YS: Email error - ' . $e->getMessage());
            }
        }
        
        return $booking;
        
    } catch (Exception $e) {
        error_log('YOLO YS: Booking creation failed - ' . $e->getMessage());
        return null;
    }
}

/**
 * Show loading page with AJAX polling (no PHP blocking!)
 */
function show_loading_page($session_id) {
    ?>
    <div id="yolo-booking-loading" style="text-align: center; padding: 60px 20px;">
        <div class="yolo-spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #dc2626; border-radius: 50%; width: 60px; height: 60px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
        <h2 style="color: #374151; margin-bottom: 10px;">Processing Your Booking</h2>
        <p id="loading-status" style="color: #6b7280; font-size: 16px;">Confirming your payment with Stripe...</p>
        <p style="color: #9ca3af; font-size: 14px; margin-top: 20px;">This usually takes just a few seconds.</p>
    </div>
    
    <div id="yolo-booking-error" style="display: none; text-align: center; padding: 40px;">
        <h2 style="color: #dc2626;">Processing Taking Longer Than Expected</h2>
        <p style="color: #6b7280;">Your payment was successful! We're just finishing up the booking details.</p>
        <p style="color: #6b7280;">Please check your email for confirmation, or <a href="javascript:location.reload()">refresh this page</a>.</p>
        <p style="margin-top: 20px;"><a href="<?php echo esc_url(home_url()); ?>" class="btn btn-primary" style="display: inline-block; padding: 12px 24px; background: #dc2626; color: white; text-decoration: none; border-radius: 6px;">Return to Home</a></p>
    </div>
    
