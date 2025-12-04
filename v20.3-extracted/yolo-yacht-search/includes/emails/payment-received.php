<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
/**
 * Payment Received Email Template
 * 
 * Variables available:
 * - $booking_reference
 * - $customer_name
 * - $yacht_name
 * - $date_from
 * - $date_to
 * - $total_price
 * - $currency
 */
?>

<h2>Payment Confirmed!</h2>

<p>Dear <?php echo esc_html($customer_name); ?>,</p>

<p>Great news! We have received your final payment. Your booking is now <strong>fully paid</strong> and confirmed.</p>

<div class="success-box">
    <p>✓ <strong>Payment Successful:</strong> Your booking is fully paid. You're all set for your charter!</p>
</div>

<div class="booking-card">
    <h3>Booking Confirmation</h3>
    
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
        <span class="detail-label">Total Paid:</span>
        <span class="detail-value" style="color: #10b981; font-size: 18px;">
            ✓ <?php echo YOLO_YS_Price_Formatter::format_price($total_price, $currency); ?>
        </span>
    </div>
    
    <div class="detail-row">
        <span class="detail-label">Payment Status:</span>
        <span class="detail-value" style="color: #10b981;">
            <strong>FULLY PAID</strong>
        </span>
    </div>
</div>

<h2>What Happens Next?</h2>

<p><strong>2 Weeks Before Charter:</strong> We'll send you detailed check-in instructions, including:</p>

<ul style="color: #4b5563; line-height: 1.8;">
    <li>Marina location and parking information</li>
    <li>Check-in time and contact person</li>
    <li>Yacht handover procedure</li>
    <li>Local recommendations and tips</li>
</ul>

<p><strong>Day of Charter:</strong> Our team will meet you at the marina for yacht handover and safety briefing.</p>

<p><strong>During Charter:</strong> We're available 24/7 for any questions or assistance you may need.</p>

<div class="highlight-box" style="background: #d1fae5; border-color: #10b981;">
    <p style="color: #065f46;">🌊 <strong>Start Planning Your Adventure!</strong> Research the best anchorages, restaurants, and hidden gems along your route. We're happy to provide recommendations!</p>
</div>

<p><strong>Questions or Special Requests?</strong> Feel free to reach out to us:</p>

<ul style="color: #4b5563; line-height: 1.8;">
    <li>Email: <a href="mailto:info@yolo-charters.com" style="color: #dc2626;">info@yolo-charters.com</a></li>
    <li>Phone: +30 698 506 4875</li>
</ul>

<p>We can't wait to welcome you aboard and help you create unforgettable memories!</p>

<p>Best regards,<br>
<strong>The YOLO Charters Team</strong></p>
