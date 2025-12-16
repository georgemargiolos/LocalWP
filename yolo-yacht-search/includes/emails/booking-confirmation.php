<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
/**
 * Booking Confirmation Email Template
 * 
 * Variables available:
 * - $booking_reference
 * - $customer_name
 * - $customer_email
 * - $yacht_name
 * - $date_from
 * - $date_to
 * - $total_price
 * - $deposit_paid
 * - $remaining_balance
 * - $currency
 * - $balance_payment_url
 * - $guest_login_url (optional)
 * - $guest_password (optional)
 */
?>

<h2>Thank You for Your Booking!</h2>

<p>Dear <?php echo esc_html($customer_name); ?>,</p>

<p>We're excited to confirm your yacht charter booking. Your adventure on the beautiful Mediterranean awaits!</p>

<div class="booking-card">
    <h3>Booking Details</h3>
    
    <div class="detail-row">
        <span class="detail-label">Booking Reference:</span>
        <span class="detail-value"><?php echo esc_html($booking_reference); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Yacht:</span>
        <span class="detail-value"><?php echo esc_html($yacht_name); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Charter Period:</span>
        <span class="detail-value">
            <?php echo date('F j, Y', strtotime($date_from)); ?> - 
            <?php echo date('F j, Y', strtotime($date_to)); ?>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Total Price:</span>
        <span class="detail-value"><?php echo YOLO_YS_Price_Formatter::format_price($total_price, $currency); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Deposit Paid:</span>
        <span class="detail-value" style="color: #10b981;">
            ✓ <?php echo YOLO_YS_Price_Formatter::format_price($deposit_paid, $currency); ?>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Remaining Balance:</span>
        <span class="detail-value" style="color: #d97706;">
            <?php echo YOLO_YS_Price_Formatter::format_price($remaining_balance, $currency); ?>
        </span>
    </div>
</div>

<?php if ($remaining_balance > 0): ?>
<div class="highlight-box">
    <p><strong>Payment Reminder:</strong> The remaining balance of <?php echo YOLO_YS_Price_Formatter::format_price($remaining_balance, $currency); ?> is due before your charter. You can pay it anytime using the button below.</p>
</div>

<div style="text-align: center;">
    <a href="<?php echo esc_url($balance_payment_url); ?>" class="button">
        Pay Remaining Balance
    </a>
</div>
<?php endif; ?>

<!-- Guest Login Section -->
<?php 
$guest_login_url = home_url('/guest-login');
$guest_password = $booking_reference . 'YoLo';
?>
<div class="info-box">
    <p><strong>🔐 Your Guest Account</strong></p>
    <p>We've created a guest account for you to manage your booking, view documents, and track your charter details.</p>
    <p><strong>Login URL:</strong> <a href="<?php echo esc_url($guest_login_url); ?>" style="color: #1e40af;"><?php echo esc_html(str_replace(['https://', 'http://'], '', $guest_login_url)); ?></a></p>
    <p><strong>Email:</strong> <code><?php echo esc_html($customer_email); ?></code></p>
    <p><strong>Password:</strong> <code><?php echo esc_html($guest_password); ?></code></p>
    <p style="font-size: 13px; color: #3b82f6; margin-top: 8px;"><em>Your password is your Booking Reference + "YoLo"</em></p>
</div>

<div style="text-align: center;">
    <a href="<?php echo esc_url($guest_login_url); ?>" class="button-secondary">
        Access Guest Portal
    </a>
</div>

<h2>What's Next?</h2>

<p><strong>1. Review Your Booking:</strong> Keep this email for your records. Your booking reference is: <strong><?php echo esc_html($booking_reference); ?></strong></p>

<p><strong>2. Complete Payment:</strong> Please pay the remaining balance before your charter date.</p>

<p><strong>3. Prepare for Charter:</strong> We'll contact you 2 weeks before your charter with detailed information about check-in, yacht handover, and local recommendations.</p>

<p><strong>4. Questions?</strong> Our team is here to help! Reply to this email or call us at +30 698 506 4875.</p>

<div class="success-box">
    <p>🎉 <strong>Get Ready for an Unforgettable Experience!</strong> We can't wait to welcome you aboard and help you create amazing memories on the water.</p>
</div>

<p>Best regards,<br>
<strong>The YOLO Charters Team</strong></p>
