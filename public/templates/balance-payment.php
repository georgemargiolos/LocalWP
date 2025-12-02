<?php
/**
 * Balance Payment Template
 * Shortcode: [yolo_balance_payment]
 * 
 * This page allows customers to pay the remaining balance of their booking.
 */

// Get booking reference from URL
$booking_ref = isset($_GET['ref']) ? sanitize_text_field($_GET['ref']) : '';

if (empty($booking_ref)) {
    echo '<div class="yolo-payment-error">';
    echo '<h2>Invalid Payment Link</h2>';
    echo '<p>Please check your email for the correct payment link.</p>';
    echo '</div>';
    return;
}

// Find booking by reference
global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';

// Try to find by BM reservation ID or booking ID
$booking = null;
if (strpos($booking_ref, 'BM-') === 0) {
    $bm_id = str_replace('BM-', '', $booking_ref);
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_bookings} WHERE bm_reservation_id = %s",
        $bm_id
    ));
} else if (strpos($booking_ref, 'YOLO-') === 0) {
    $parts = explode('-', $booking_ref);
    $booking_id = isset($parts[2]) ? intval($parts[2]) : 0;
    $booking = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table_bookings} WHERE id = %d",
        $booking_id
    ));
}

if (!$booking) {
    echo '<div class="yolo-payment-error">';
    echo '<h2>Booking Not Found</h2>';
    echo '<p>We could not find your booking. Please contact us for assistance.</p>';
    echo '</div>';
    return;
}

// Check if already fully paid
if ($booking->payment_status === 'fully_paid') {
    echo '<div class="yolo-payment-success">';
    echo '<h2>Already Paid</h2>';
    echo '<p>This booking has already been fully paid. Thank you!</p>';
    echo '</div>';
    return;
}

// Check if remaining balance is 0
if ($booking->remaining_balance <= 0) {
    echo '<div class="yolo-payment-success">';
    echo '<h2>No Balance Due</h2>';
    echo '<p>There is no remaining balance for this booking.</p>';
    echo '</div>';
    return;
}
?>

<div class="container py-5">
<div class="yolo-balance-payment">
    <div class="payment-header">
        <h1>Complete Your Payment</h1>
        <p class="payment-subtitle">Pay the remaining balance for your yacht charter</p>
    </div>
    
    <div class="payment-content">
        <div class="booking-summary-card">
            <h2>Booking Summary</h2>
            
            <div class="summary-row">
                <span class="summary-label">Booking Reference:</span>
                <span class="summary-value"><strong><?php echo esc_html($booking_ref); ?></strong></span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Yacht:</span>
                <span class="summary-value"><?php echo esc_html($booking->yacht_name); ?></span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Charter Period:</span>
                <span class="summary-value">
                    <?php echo date('F j, Y', strtotime($booking->date_from)); ?> - 
                    <?php echo date('F j, Y', strtotime($booking->date_to)); ?>
                </span>
            </div>
            
            <div class="summary-row">
                <span class="summary-label">Customer:</span>
                <span class="summary-value"><?php echo esc_html($booking->customer_name); ?></span>
            </div>
        </div>
        
        <div class="payment-details-card">
            <h2>Payment Details</h2>
            
            <div class="payment-row">
                <span class="payment-label">Total Price:</span>
                <span class="payment-value"><?php echo YOLO_YS_Price_Formatter::format_price($booking->total_price, $booking->currency); ?></span>
            </div>
            
            <div class="payment-row paid">
                <span class="payment-label">Deposit Paid:</span>
                <span class="payment-value">-<?php echo YOLO_YS_Price_Formatter::format_price($booking->deposit_paid, $booking->currency); ?></span>
            </div>
            
            <div class="payment-row total">
                <span class="payment-label">Remaining Balance:</span>
                <span class="payment-value"><?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?></span>
            </div>
            
            <div class="payment-actions">
                <button id="yolo-pay-balance-btn" class="btn-pay-now" data-booking-id="<?php echo esc_attr($booking->id); ?>">
                    Pay Now - <?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?>
                </button>
            </div>
        </div>
        
        <div class="payment-info">
            <p><strong>Secure Payment:</strong> Your payment is processed securely through Stripe.</p>
            <p><strong>Questions?</strong> Contact us at <a href="mailto:info@yolo-charters.com">info@yolo-charters.com</a></p>
        </div>
    </div>
</div>
</div><!-- /container -->

<script>
jQuery(document).ready(function($) {
    $('#yolo-pay-balance-btn').on('click', function() {
        const button = $(this);
        const bookingId = button.data('booking-id');
        
        // Disable button and show loading
        button.prop('disabled', true);
        button.html('Processing...');
        
        // Create Stripe Checkout Session for balance payment
        $.ajax({
            url: yoloYSData.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_create_balance_checkout',
                booking_id: bookingId,
                nonce: yoloYSData.nonce
            },
            success: function(response) {
                if (response.success && response.data.url) {
                    // Redirect to Stripe Checkout
                    window.location.href = response.data.url;
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to create payment session'));
                    button.prop('disabled', false);
                    button.html('Pay Now - <?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?>');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                button.prop('disabled', false);
                button.html('Pay Now - <?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?>');
            }
        });
    });
});
</script>
