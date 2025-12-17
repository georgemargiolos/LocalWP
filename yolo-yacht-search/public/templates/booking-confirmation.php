<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Booking Confirmation Template
 * Shortcode: [yolo_booking_confirmation]
 * 
 * v65.23 FIX: 
 * - AJAX-based booking creation - spinner shows IMMEDIATELY
 * - Progressive text updates: 0-10s, 10-35s, 35-45s, 45-60s
 * - Customizable texts from settings page
 * - Responsive spinner design for mobile
 */

$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

if (empty($session_id)) {
    echo '<div class="yolo-booking-error" style="text-align:center;padding:40px;"><h2>Invalid Booking Reference</h2><p>We could not find your booking. Please check your email for confirmation details.</p></div>';
    return;
}

global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';

// Check if booking already exists (e.g., page refresh)
$booking = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_bookings} WHERE stripe_session_id = %s",
    $session_id
));

if ($booking) {
    // Booking exists - show confirmation immediately
    yolo_show_booking_confirmation_v2($booking);
    return;
}

// Booking doesn't exist - show spinner and process via AJAX
yolo_show_ajax_processing_page($session_id);
return;

// ============================================
// FUNCTION DEFINITIONS
// ============================================

/**
 * Show the AJAX-based processing page with spinner
 * v65.23: Shows spinner IMMEDIATELY, then uses AJAX to create booking
 */
function yolo_show_ajax_processing_page($session_id) {
    // Get customizable texts from settings with defaults
    $heading_1 = get_option('yolo_ys_text_spinner_heading_1', 'Confirming Your Payment');
    $subtext_1 = get_option('yolo_ys_text_spinner_subtext_1', 'Please wait while we verify your payment...');
    $heading_2 = get_option('yolo_ys_text_spinner_heading_2', 'Processing Your Booking');
    $subtext_2 = get_option('yolo_ys_text_spinner_subtext_2', "Almost there! Please don't close this window.");
    $heading_3 = get_option('yolo_ys_text_spinner_heading_3', 'Finalizing Details');
    $subtext_3 = get_option('yolo_ys_text_spinner_subtext_3', "This is taking a bit longer than usual. Please don't close this window.");
    $heading_4 = get_option('yolo_ys_text_spinner_heading_4', 'Still Working...');
    $subtext_4 = get_option('yolo_ys_text_spinner_subtext_4', "Your payment was successful. We're just finishing up the details.");
    ?>
    <style>
    .yolo-processing-container {
        text-align: center;
        padding: 60px 20px;
        max-width: 500px;
        margin: 0 auto;
    }
    .yolo-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #dc2626;
        border-radius: 50%;
        width: 60px;
        height: 60px;
        animation: yolo-spin 1s linear infinite;
        margin: 0 auto 24px;
    }
    .yolo-processing-heading {
        color: #374151;
        font-size: 24px;
        font-weight: 600;
        margin: 0 0 12px 0;
        line-height: 1.3;
    }
    .yolo-processing-subtext {
        color: #6b7280;
        font-size: 16px;
        margin: 0;
        line-height: 1.5;
    }
    @keyframes yolo-spin { 
        0% { transform: rotate(0deg); } 
        100% { transform: rotate(360deg); } 
    }
    
    /* Mobile responsive styles */
    @media (max-width: 480px) {
        .yolo-processing-container {
            padding: 40px 16px;
        }
        .yolo-spinner {
            width: 50px;
            height: 50px;
            margin-bottom: 20px;
        }
        .yolo-processing-heading {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .yolo-processing-subtext {
            font-size: 14px;
        }
    }
    
    .yolo-error-container {
        text-align: center;
        padding: 40px 20px;
        display: none;
    }
    .yolo-error-container h2 {
        color: #dc2626;
        margin-bottom: 15px;
    }
    .yolo-error-container p {
        color: #6b7280;
        margin-bottom: 10px;
    }
    .yolo-error-container .btn {
        display: inline-block;
        padding: 12px 24px;
        background: #dc2626;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        margin-top: 15px;
    }
    </style>
    
    <div id="yolo-booking-processing" class="yolo-processing-container">
        <div class="yolo-spinner"></div>
        <h2 id="processing-heading" class="yolo-processing-heading"><?php echo esc_html($heading_1); ?></h2>
        <p id="processing-status" class="yolo-processing-subtext"><?php echo esc_html($subtext_1); ?></p>
    </div>
    
    <div id="yolo-booking-error" class="yolo-error-container">
        <h2>Processing Taking Longer Than Expected</h2>
        <p>Your payment was successful! We're just finishing up the booking details.</p>
        <p>Please check your email for confirmation, or <a href="javascript:location.reload()">refresh this page</a>.</p>
        <a href="<?php echo esc_url(home_url()); ?>" class="btn">Return to Home</a>
    </div>
    
    <script>
    (function() {
        var startTime = Date.now();
        var headingEl = document.getElementById('processing-heading');
        var statusEl = document.getElementById('processing-status');
        var sessionId = <?php echo json_encode($session_id); ?>;
        var ajaxUrl = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;
        
        // Texts from settings
        var texts = {
            heading1: <?php echo json_encode($heading_1); ?>,
            subtext1: <?php echo json_encode($subtext_1); ?>,
            heading2: <?php echo json_encode($heading_2); ?>,
            subtext2: <?php echo json_encode($subtext_2); ?>,
            heading3: <?php echo json_encode($heading_3); ?>,
            subtext3: <?php echo json_encode($subtext_3); ?>,
            heading4: <?php echo json_encode($heading_4); ?>,
            subtext4: <?php echo json_encode($subtext_4); ?>
        };
        
        var currentStage = 1;
        var textInterval;
        
        function updateText() {
            var elapsed = (Date.now() - startTime) / 1000;
            var newStage = 1;
            
            if (elapsed >= 45) {
                newStage = 4;
            } else if (elapsed >= 35) {
                newStage = 3;
            } else if (elapsed >= 10) {
                newStage = 2;
            }
            
            if (newStage !== currentStage) {
                currentStage = newStage;
                switch(newStage) {
                    case 2:
                        headingEl.textContent = texts.heading2;
                        statusEl.textContent = texts.subtext2;
                        break;
                    case 3:
                        headingEl.textContent = texts.heading3;
                        statusEl.textContent = texts.subtext3;
                        break;
                    case 4:
                        headingEl.textContent = texts.heading4;
                        statusEl.textContent = texts.subtext4;
                        break;
                }
            }
        }
        
        // Start text updates
        textInterval = setInterval(updateText, 1000);
        
        // Process booking via AJAX
        function processBooking() {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxUrl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Booking created or exists - reload to show confirmation
                                clearInterval(textInterval);
                                window.location.reload();
                            } else {
                                // Check if payment pending
                                if (response.data && response.data.status === 'pending') {
                                    // Payment not yet confirmed, retry in 2 seconds
                                    setTimeout(processBooking, 2000);
                                } else {
                                    showError();
                                }
                            }
                        } catch (e) {
                            showError();
                        }
                    } else {
                        showError();
                    }
                }
            };
            xhr.send('action=yolo_process_stripe_booking&session_id=' + encodeURIComponent(sessionId));
        }
        
        function showError() {
            clearInterval(textInterval);
            document.getElementById('yolo-booking-processing').style.display = 'none';
            document.getElementById('yolo-booking-error').style.display = 'block';
        }
        
        // Start processing immediately
        processBooking();
        
        // Timeout after 60 seconds
        setTimeout(function() {
            if (document.getElementById('yolo-booking-processing').style.display !== 'none') {
                showError();
            }
        }, 60000);
    })();
    </script>
    <?php
}

/**
 * Show the booking confirmation details
 */
function yolo_show_booking_confirmation_v2($booking) {
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
                    <span class="detail-value"><strong><?php 
                        $booking_reference = $booking->bm_reservation_id 
                            ? 'BM-' . $booking->bm_reservation_id 
                            : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
                        echo esc_html($booking_reference); 
                    ?></strong></span>
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
    <?php
    $booking_id_safe = ($booking && isset($booking->id)) ? $booking->id : '';
    $booking_yacht_id_safe = ($booking && isset($booking->yacht_id)) ? $booking->yacht_id : '';
    $booking_yacht_name_safe = ($booking && isset($booking->yacht_name)) ? $booking->yacht_name : '';
    $booking_currency_safe = ($booking && isset($booking->currency)) ? $booking->currency : 'EUR';
    $booking_total_price_safe = ($booking && isset($booking->total_price)) ? floatval($booking->total_price) : 0;
    $booking_stripe_session_safe = ($booking && isset($booking->stripe_session_id)) ? $booking->stripe_session_id : '';
    ?>
    <script>
    // Track Purchase event for GA4 (via GTM)
    if (typeof window.dataLayer !== 'undefined') {
        window.dataLayer.push({
            event: 'purchase',
            transaction_id: '<?php echo esc_js($booking_stripe_session_safe ? $booking_stripe_session_safe : 'booking-' . $booking_id_safe); ?>',
            currency: '<?php echo esc_js($booking_currency_safe); ?>',
            value: <?php echo $booking_total_price_safe; ?>,
            items: [{
                item_id: '<?php echo esc_js($booking_yacht_id_safe); ?>',
                item_name: '<?php echo esc_js($booking_yacht_name_safe); ?>',
                price: <?php echo $booking_total_price_safe; ?>,
                quantity: 1
            }]
        });
    }
    
    // Track Purchase event for Facebook Pixel with retry logic
    (function sendPurchaseToFacebook(retryCount) {
        retryCount = retryCount || 0;
        
        if (typeof fbq !== 'function') {
            if (retryCount < 10) {
                setTimeout(function() { sendPurchaseToFacebook(retryCount + 1); }, 500);
                return;
            }
            return;
        }
        
        var eventId = window.fbPurchaseEventId || 'purchase_' + <?php echo json_encode($booking_id_safe); ?> + '_<?php echo time(); ?>';
        
        fbq('track', 'Purchase', {
            content_type: 'product',
            content_ids: [<?php echo json_encode($booking_yacht_id_safe); ?>],
            content_name: <?php echo json_encode($booking_yacht_name_safe); ?>,
            currency: <?php echo json_encode($booking_currency_safe); ?>,
            value: <?php echo $booking_total_price_safe; ?>,
            order_id: <?php echo json_encode($booking_stripe_session_safe ? $booking_stripe_session_safe : 'booking-' . $booking_id_safe); ?>
        }, {
            eventID: eventId
        });
    })();
    </script>
    <?php
}
