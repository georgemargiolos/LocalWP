<?php
/**
 * Balance Payment Confirmation Template
 * Shortcode: [yolo_balance_confirmation]
 * 
 * This page is shown after successful balance payment.
 */

// Get session ID from URL
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

if (empty($session_id)) {
    echo '<div class="yolo-payment-error">';
    echo '<h2>Invalid Payment Reference</h2>';
    echo '<p>We could not find your payment. Please check your email for confirmation details.</p>';
    echo '</div>';
    return;
}

// Retrieve session from Stripe
try {
    $secret_key = get_option('yolo_ys_stripe_secret_key', '');
    if (!empty($secret_key)) {
        \Stripe\Stripe::setApiKey($secret_key);
        
        // Retrieve session from Stripe
        $session = \Stripe\Checkout\Session::retrieve($session_id);
        
        // Check if payment was successful
        if ($session->payment_status === 'paid' && isset($session->metadata->payment_type) && $session->metadata->payment_type === 'balance') {
            // Get booking ID from metadata
            $booking_id = $session->metadata->booking_id;
            
            // Update booking in database
            global $wpdb;
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            
            $wpdb->update(
                $table_bookings,
                array(
                    'payment_status' => 'fully_paid',
                    'deposit_paid' => $wpdb->get_var($wpdb->prepare(
                        "SELECT total_price FROM {$table_bookings} WHERE id = %d",
                        $booking_id
                    )),
                    'remaining_balance' => 0,
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $booking_id),
                array('%s', '%f', '%f', '%s'),
                array('%d')
            );
            
            // Get updated booking
            $booking = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_bookings} WHERE id = %d",
                $booking_id
            ));
            
            // Send confirmation email using HTML template
            if ($booking) {
                YOLO_YS_Email::send_payment_received($booking);
            }
        }
    }
} catch (Exception $e) {
    error_log('YOLO YS: Failed to process balance payment - ' . $e->getMessage());
}

// Get booking for display
if (isset($booking) && $booking) {
    $booking_reference = !empty($booking->bm_reservation_id) 
        ? $booking->bm_reservation_id 
        : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
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
        <h1>Payment Confirmed!</h1>
        <p class="confirmation-subtitle">Your booking is now fully paid. Thank you!</p>
    </div>
    
    <div class="confirmation-content">
        <div class="booking-details-card">
            <h2>Booking Details</h2>
            
            <div class="detail-row">
                <span class="detail-label">Booking Reference:</span>
                <span class="detail-value"><strong><?php echo esc_html($booking_reference); ?></strong></span>
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
        </div>
        
        <div class="payment-summary-card">
            <h2>Payment Summary</h2>
            
            <div class="detail-row">
                <span class="detail-label">Total Price:</span>
                <span class="detail-value"><?php echo YOLO_YS_Price_Formatter::format_price($booking->total_price, $booking->currency); ?></span>
            </div>
            
            <div class="detail-row highlight">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value success">✓ Fully Paid</span>
            </div>
        </div>
        
        <div class="next-steps-card">
            <h2>What's Next?</h2>
            <ul class="next-steps-list">
                <li><strong>Confirmation Email:</strong> Check your inbox for a detailed confirmation email.</li>
                <li><strong>Prepare for Charter:</strong> We'll contact you closer to your charter date with final details.</li>
                <li><strong>Questions?</strong> Contact us anytime at <a href="mailto:info@yolo-charters.com">info@yolo-charters.com</a></li>
            </ul>
        </div>
        
        <div class="action-buttons">
            <a href="<?php echo home_url(); ?>" class="btn btn-primary">Return to Home</a>
        </div>
    </div>
</div>
</div><!-- /container -->

<?php
} else {
    echo '<div class="yolo-payment-error">';
    echo '<h2>Payment Processing</h2>';
    echo '<p>We are processing your payment. Please check your email for confirmation.</p>';
    echo '</div>';
}
?>
