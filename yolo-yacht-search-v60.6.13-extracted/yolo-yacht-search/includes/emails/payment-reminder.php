<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
/**
 * Payment Reminder Email Template
 * 
 * Variables available:
 * - $booking_reference
 * - $customer_name
 * - $yacht_name
 * - $date_from
 * - $date_to
 * - $total_price
 * - $deposit_paid
 * - $remaining_balance
 * - $currency
 * - $balance_payment_url
 * - $days_until_charter
 */
?>

<h2>Payment Reminder</h2>

<p>Dear <?php echo esc_html($customer_name); ?>,</p>

<p>This is a friendly reminder about the remaining balance for your upcoming yacht charter.</p>

<div class="booking-card">
    <h3>Booking Summary</h3>
    
    <div class="detail-row">
        <span class="detail-label">Booking Reference:</span>
        <span class="detail-value"><?php echo esc_html($booking_reference); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Yacht:</span>
        <span class="detail-value"><?php echo esc_html($yacht_name); ?></span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Charter Dates:</span>
        <span class="detail-value">
            <?php echo date('F j, Y', strtotime($date_from)); ?> - 
            <?php echo date('F j, Y', strtotime($date_to)); ?>
        </span>
    </div>
</div>

<div class="booking-card" style="background: #fef3c7; border-color: #f59e0b;">
    <h3 style="border-color: #f59e0b;">Payment Information</h3>
    
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
        <span class="detail-label" style="font-size: 18px; color: #92400e;">Remaining Balance:</span>
        <span class="detail-value" style="font-size: 20px; color: #92400e;">
            <?php echo YOLO_YS_Price_Formatter::format_price($remaining_balance, $currency); ?>
        </span>
    </div>
</div>

<div class="highlight-box">
    <p><strong>Action Required:</strong> Please complete your payment before your charter date to ensure a smooth check-in process.</p>
</div>

<div style="text-align: center;">
    <a href="<?php echo esc_url($balance_payment_url); ?>" class="button">
        Pay Now - <?php echo YOLO_YS_Price_Formatter::format_price($remaining_balance, $currency); ?>
    </a>
</div>

<p><strong>Already Paid?</strong> If you've already completed your payment, please disregard this message. It may take a few hours for payments to be processed.</p>

<p><strong>Need Help?</strong> If you have any questions or need assistance, please don't hesitate to contact us:</p>

<ul style="color: #4b5563; line-height: 1.8;">
    <li>Email: <a href="mailto:info@yolo-charters.com" style="color: #dc2626;">info@yolo-charters.com</a></li>
    <li>Phone: +30 698 506 4875</li>
</ul>

<p>We look forward to welcoming you aboard!</p>

<p>Best regards,<br>
<strong>The YOLO Charters Team</strong></p>
