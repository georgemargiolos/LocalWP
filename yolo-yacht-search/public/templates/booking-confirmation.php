<?php
/**
 * Booking Confirmation Template
 * Shortcode: [yolo_booking_confirmation]
 * 
 * This page is shown after successful Stripe payment.
 * It creates the booking in the database if it doesn't exist yet.
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

// Show loading indicator while checking for booking
echo '<div id="yolo-booking-loading" style="text-align: center; padding: 40px;">';
echo '<div class="yolo-spinner" style="border: 4px solid #f3f3f3; border-top: 4px solid #dc2626; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>';
echo '<p style="color: #6b7280; font-size: 16px;">Processing your booking confirmation...</p>';
echo '</div>';
echo '<style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style>';

// Flush output to show loading indicator immediately
if (ob_get_level() > 0) {
    ob_flush();
    flush();
}

// Check if booking already exists (with retry for webhook race condition)
global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';

// Try to find booking with retries (webhook might still be processing)
$max_retries = 10;
$retry_delay = 1; // seconds
$booking = null;

for ($i = 0; $i < $max_retries; $i++) {
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_bookings} WHERE stripe_session_id = %s",
        $session_id
    ));
    
    if ($booking) {
        break; // Booking found!
    }
    
    // Wait before retry (except on last attempt)
    if ($i < $max_retries - 1) {
        sleep($retry_delay);
    }
}

// If booking doesn't exist, create it from Stripe session
if (!$booking) {
    try {
        // Initialize Stripe
        $secret_key = get_option('yolo_ys_stripe_secret_key', '');
        if (!empty($secret_key)) {
            \Stripe\Stripe::setApiKey($secret_key);
            
            // Retrieve session from Stripe
            $session = \Stripe\Checkout\Session::retrieve($session_id);
            
            // Check if payment was successful
            if ($session->payment_status === 'paid') {
                // Extract booking details from metadata
                $yacht_id = $session->metadata->yacht_id;
                $yacht_name = $session->metadata->yacht_name;
                $date_from = $session->metadata->date_from;
                $date_to = $session->metadata->date_to;
                $total_price = $session->metadata->total_price;
                $deposit_amount = $session->metadata->deposit_amount;
                $remaining_balance = $session->metadata->remaining_balance;
                $currency = isset($session->metadata->currency) ? $session->metadata->currency : 'EUR';
                
                // Get customer details from metadata (preferred) or customer_details (fallback)
                $customer_first_name = isset($session->metadata->customer_first_name) ? $session->metadata->customer_first_name : '';
                $customer_last_name = isset($session->metadata->customer_last_name) ? $session->metadata->customer_last_name : '';
                $customer_name = isset($session->metadata->customer_name) ? $session->metadata->customer_name : '';
                $customer_email = isset($session->metadata->customer_email) ? $session->metadata->customer_email : '';
                $customer_phone = isset($session->metadata->customer_phone) ? $session->metadata->customer_phone : '';
                
                // Fallback to Stripe customer_details if metadata is empty
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
                
                // Create reservation in Booking Manager
                $api = new YOLO_YS_Booking_Manager_API();
                
                // Prepare reservation data
                $reservation_data = array(
                    'yachtId' => (int)$yacht_id,
                    'dateFrom' => date('Y-m-d\TH:i:s', strtotime($date_from)),
                    'dateTo' => date('Y-m-d\TH:i:s', strtotime($date_to)),
                    'client' => array(
                        'name' => $customer_name,
                        'email' => $customer_email,
                    ),
                    'status' => 1, // 1 = Confirmed
                    'sendNotification' => true,
                    'note' => 'Online booking via YOLO Charters. Stripe Payment: ' . (isset($session->payment_intent) ? $session->payment_intent : ''),
                );
                
                $result = $api->create_reservation($reservation_data);
                
                if ($result['success']) {
                    $bm_reservation_id = isset($result['data']['id']) ? $result['data']['id'] : null;
                    
                    // Update booking with Booking Manager reservation ID
                    $wpdb->update(
                        $table_bookings,
                        array('bm_reservation_id' => $bm_reservation_id),
                        array('id' => $booking_id)
                    );
                    
                    // Record payment in Booking Manager
                    if ($bm_reservation_id) {
                        $payment_data = array(
                            'amount' => floatval($deposit_amount),
                            'currency' => $currency,
                            'paymentDate' => current_time('Y-m-d\TH:i:s'),
                            'paymentMethod' => 'Credit Card (Stripe)',
                            'note' => 'Deposit payment. Stripe Payment Intent: ' . (isset($session->payment_intent) ? $session->payment_intent : ''),
                        );
                        
                        $payment_result = $api->create_payment($bm_reservation_id, $payment_data);
                        
                        if (!$payment_result['success']) {
                            error_log('YOLO YS: Failed to record payment in Booking Manager: ' . print_r($payment_result, true));
                        }
                    }
                    
                    error_log('YOLO YS: Booking Manager reservation created - ID: ' . $bm_reservation_id);
                } else {
                    error_log('YOLO YS: Failed to create Booking Manager reservation: ' . print_r($result, true));
                    
                    // Send alert email to admin
                    $admin_email = get_option('admin_email');
                    $admin_subject = 'ACTION REQUIRED: Booking #' . $booking_id . ' - Manual Confirmation Needed';
                    $admin_message = "A customer has paid but the automatic reservation in Booking Manager failed.\n\n";
                    $admin_message .= "Please manually create the reservation in Booking Manager.\n\n";
                    $admin_message .= "Booking ID: #" . $booking_id . "\n";
                    $admin_message .= "Yacht: " . $yacht_name . "\n";
                    $admin_message .= "Customer: " . $customer_name . "\n";
                    $admin_message .= "Email: " . $customer_email . "\n";
                    $admin_message .= "Dates: " . date('M d, Y', strtotime($date_from)) . " - " . date('M d, Y', strtotime($date_to)) . "\n";
                    $admin_message .= "Deposit: " . YOLO_YS_Price_Formatter::format_price($deposit_amount, $currency) . "\n\n";
                    $admin_message .= "Error: " . (isset($result['error']) ? $result['error'] : 'Unknown error') . "\n";
                    
                    wp_mail($admin_email, $admin_subject, $admin_message, array('Content-Type: text/plain; charset=UTF-8'));
                }
                
                // Send confirmation email using HTML template
                YOLO_YS_Email::send_booking_confirmation($booking);
                
                // Send admin notification
                YOLO_YS_Email::send_admin_notification($booking);
                
                // Retrieve the newly created booking
                $booking = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$table_bookings} WHERE id = %d",
                    $booking_id
                ));
                
                error_log('YOLO YS: Booking created on return from Stripe - ID: ' . $booking_id);
            }
        }
    } catch (Exception $e) {
        error_log('YOLO YS: Failed to create booking from Stripe session - ' . $e->getMessage());
    }
}

// Hide loading indicator
echo '<script>document.getElementById("yolo-booking-loading").style.display = "none";</script>';

// If still no booking, show error
if (!$booking) {
    echo '<div class="yolo-booking-error">';
    echo '<h2>Processing Payment</h2>';
    echo '<p>We are processing your payment. This may take a few moments. Please check your email for confirmation or contact us if you have any questions.</p>';
    echo '<p style="margin-top: 20px;"><a href="' . esc_url(home_url()) . '" class="yolo-button">Return to Home</a></p>';
    echo '</div>';
    return;
}

// Get deposit percentage
$deposit_percentage = get_option('yolo_ys_deposit_percentage', 50);
?>

<div class="yolo-booking-confirmation">
    <div class="confirmation-header">
        <div class="success-icon">
            <svg width="80" height="80" viewBox="0 0 80 80" fill="none">
                <circle cx="40" cy="40" r="38" stroke="#10b981" stroke-width="4"/>
                <path d="M25 40L35 50L55 30" stroke="#10b981" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <h1>Booking Confirmed!</h1>
        <p class="confirmation-subtitle">Thank you for your booking. Your yacht charter is confirmed.</p>
    </div>
    
    <div class="confirmation-content">
        <div class="booking-details-card">
            <h2>Booking Details</h2>
            
            <div class="detail-row">
                <span class="detail-label">Booking Reference:</span>
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
            <a href="<?php echo home_url('/our-yachts'); ?>" class="btn btn-secondary">Browse More Yachts</a>
        </div>
    </div>
</div>
