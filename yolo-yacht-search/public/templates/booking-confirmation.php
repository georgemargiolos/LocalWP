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

// Check if booking already exists
global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';
$booking = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_bookings} WHERE stripe_session_id = %s",
    $session_id
));

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
                
                // Get customer details
                $customer_email = isset($session->customer_details->email) ? $session->customer_details->email : '';
                $customer_name = isset($session->customer_details->name) ? $session->customer_details->name : '';
                
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
                    'customer_email' => $customer_email,
                    'customer_name' => $customer_name,
                    'stripe_session_id' => $session_id,
                    'stripe_payment_intent' => isset($session->payment_intent) ? $session->payment_intent : '',
                    'payment_status' => 'deposit_paid',
                    'booking_status' => 'confirmed',
                    'created_at' => current_time('mysql'),
                ));
                
                $booking_id = $wpdb->insert_id;
                
                // Send confirmation email
                $to = $customer_email;
                $subject = 'Booking Confirmation - ' . $yacht_name;
                
                $message = sprintf(
                    "Dear %s,\n\n" .
                    "Thank you for your booking!\n\n" .
                    "Booking Details:\n" .
                    "Yacht: %s\n" .
                    "Dates: %s to %s\n" .
                    "Total Price: %s\n" .
                    "Deposit Paid: %s\n" .
                    "Remaining Balance: %s\n\n" .
                    "Your booking reference: #%d\n\n" .
                    "We look forward to welcoming you aboard!\n\n" .
                    "Best regards,\n" .
                    "YOLO Charters Team",
                    $customer_name,
                    $yacht_name,
                    date('F j, Y', strtotime($date_from)),
                    date('F j, Y', strtotime($date_to)),
                    YOLO_YS_Price_Formatter::format_price($total_price, $currency),
                    YOLO_YS_Price_Formatter::format_price($deposit_amount, $currency),
                    YOLO_YS_Price_Formatter::format_price($remaining_balance, $currency),
                    $booking_id
                );
                
                $headers = array('Content-Type: text/plain; charset=UTF-8');
                wp_mail($to, $subject, $message, $headers);
                
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

// If still no booking, show error
if (!$booking) {
    echo '<div class="yolo-booking-error">';
    echo '<h2>Processing Payment</h2>';
    echo '<p>We are processing your payment. Please check your email for confirmation or contact us if you have any questions.</p>';
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
                <span class="detail-value"><strong>#<?php echo esc_html($booking->id); ?></strong></span>
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
                    Contact us anytime at <a href="mailto:info@yolocharters.com">info@yolocharters.com</a> or call us at +30 123 456 7890.
                </li>
            </ol>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo home_url(); ?>" class="btn btn-primary">Return to Home</a>
            <a href="<?php echo home_url('/our-yachts'); ?>" class="btn btn-secondary">Browse More Yachts</a>
        </div>
    </div>
</div>

<style>
.yolo-booking-confirmation {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.confirmation-header {
    text-align: center;
    margin-bottom: 40px;
}

.success-icon {
    margin: 0 auto 20px;
    width: 80px;
    height: 80px;
}

.confirmation-header h1 {
    font-size: 32px;
    color: #10b981;
    margin: 0 0 10px 0;
}

.confirmation-subtitle {
    font-size: 18px;
    color: #6b7280;
}

.confirmation-content > div {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 20px;
}

.confirmation-content h2 {
    font-size: 20px;
    color: #1f2937;
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 2px solid #e5e7eb;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f3f4f6;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: #6b7280;
    font-weight: 500;
}

.detail-value {
    color: #1f2937;
    font-weight: 600;
    text-align: right;
}

.detail-row.highlight {
    background: #f0fdf4;
    margin: 0 -12px;
    padding: 12px;
    border-radius: 6px;
}

.detail-value.success {
    color: #10b981;
    font-size: 18px;
}

.payment-note {
    margin-top: 20px;
    padding: 16px;
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 4px;
}

.payment-note p {
    margin: 0;
    color: #92400e;
    font-size: 14px;
}

.next-steps-list {
    margin: 0;
    padding-left: 20px;
}

.next-steps-list li {
    margin-bottom: 16px;
    line-height: 1.6;
    color: #4b5563;
}

.next-steps-list strong {
    color: #1f2937;
}

.action-buttons {
    display: flex;
    gap: 16px;
    justify-content: center;
    margin-top: 32px;
}

.btn {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
}

.btn-primary {
    background: #dc2626;
    color: #ffffff;
}

.btn-primary:hover {
    background: #b91c1c;
}

.btn-secondary {
    background: #ffffff;
    color: #dc2626;
    border: 2px solid #dc2626;
}

.btn-secondary:hover {
    background: #fef2f2;
}

.yolo-booking-error {
    max-width: 600px;
    margin: 60px auto;
    padding: 40px;
    text-align: center;
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 8px;
}

.yolo-booking-error h2 {
    color: #dc2626;
    margin-bottom: 16px;
}

.yolo-booking-error p {
    color: #991b1b;
}

@media (max-width: 768px) {
    .yolo-booking-confirmation {
        padding: 10px;
    }
    
    .confirmation-header h1 {
        font-size: 24px;
    }
    
    .detail-row {
        flex-direction: column;
        gap: 4px;
    }
    
    .detail-value {
        text-align: left;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>
