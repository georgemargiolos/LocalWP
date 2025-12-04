<?php
/**
 * Admin Booking Detail Template
 *
 * @package    YOLO_Yacht_Search
 * @subpackage YOLO_Yacht_Search/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;

if (!$booking_id) {
    echo '<div class="wrap"><h1>Invalid Booking</h1><p>Booking not found.</p></div>';
    return;
}

global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';
$booking = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_bookings} WHERE id = %d",
    $booking_id
));

if (!$booking) {
    echo '<div class="wrap"><h1>Booking Not Found</h1><p>The requested booking does not exist.</p></div>';
    return;
}

// Get booking reference
$booking_reference = !empty($booking->bm_reservation_id) 
    ? 'BM-' . $booking->bm_reservation_id 
    : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
?>

<div class="wrap">
    <h1>Booking Details #<?php echo $booking->id; ?></h1>
    <a href="?page=yolo-ys-bookings" class="page-title-action">← Back to Bookings</a>
    
    <div class="yolo-booking-detail">
        <div class="booking-detail-grid">
            <!-- Booking Information -->
            <div class="detail-card">
                <h2>Booking Information</h2>
                <table class="form-table">
                    <tr>
                        <th>Booking Reference:</th>
                        <td><strong><?php echo esc_html($booking_reference); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Booking Date:</th>
                        <td><?php echo date('F j, Y g:i A', strtotime($booking->created_at)); ?></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <?php
                            $status_class = '';
                            $status_label = '';
                            switch ($booking->payment_status) {
                                case 'deposit_paid':
                                    $status_class = 'status-warning';
                                    $status_label = 'Deposit Paid - Balance Due';
                                    break;
                                case 'fully_paid':
                                    $status_class = 'status-success';
                                    $status_label = 'Fully Paid';
                                    break;
                                case 'cancelled':
                                    $status_class = 'status-error';
                                    $status_label = 'Cancelled';
                                    break;
                                default:
                                    $status_class = 'status-pending';
                                    $status_label = 'Pending';
                            }
                            ?>
                            <span class="booking-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th>Stripe Session ID:</th>
                        <td><code><?php echo esc_html($booking->stripe_session_id); ?></code></td>
                    </tr>
                    <?php if (!empty($booking->stripe_payment_intent)): ?>
                    <tr>
                        <th>Stripe Payment Intent:</th>
                        <td><code><?php echo esc_html($booking->stripe_payment_intent); ?></code></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($booking->bm_reservation_id)): ?>
                    <tr>
                        <th>Booking Manager ID:</th>
                        <td><strong>BM-<?php echo esc_html($booking->bm_reservation_id); ?></strong></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Customer Information -->
            <div class="detail-card">
                <h2>Customer Information</h2>
                <table class="form-table">
                    <tr>
                        <th>Name:</th>
                        <td><?php echo esc_html($booking->customer_name); ?></td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td><a href="mailto:<?php echo esc_attr($booking->customer_email); ?>"><?php echo esc_html($booking->customer_email); ?></a></td>
                    </tr>
                    <?php if (!empty($booking->customer_phone)): ?>
                    <tr>
                        <th>Phone:</th>
                        <td><a href="tel:<?php echo esc_attr($booking->customer_phone); ?>"><?php echo esc_html($booking->customer_phone); ?></a></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
            
            <!-- Yacht Information -->
            <div class="detail-card">
                <h2>Yacht & Charter Details</h2>
                <table class="form-table">
                    <tr>
                        <th>Yacht:</th>
                        <td><strong><?php echo esc_html($booking->yacht_name); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Yacht ID:</th>
                        <td><?php echo esc_html($booking->yacht_id); ?></td>
                    </tr>
                    <tr>
                        <th>Charter Period:</th>
                        <td>
                            <?php echo date('F j, Y', strtotime($booking->date_from)); ?> - 
                            <?php echo date('F j, Y', strtotime($booking->date_to)); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>
                            <?php 
                            $days = (strtotime($booking->date_to) - strtotime($booking->date_from)) / 86400;
                            echo $days . ' days';
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Payment Information -->
            <div class="detail-card">
                <h2>Payment Information</h2>
                <table class="form-table">
                    <tr>
                        <th>Total Price:</th>
                        <td><strong><?php echo YOLO_YS_Price_Formatter::format_price($booking->total_price, $booking->currency); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Deposit Paid:</th>
                        <td class="text-success"><?php echo YOLO_YS_Price_Formatter::format_price($booking->deposit_paid, $booking->currency); ?></td>
                    </tr>
                    <tr>
                        <th>Remaining Balance:</th>
                        <td class="text-warning"><strong><?php echo YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency); ?></strong></td>
                    </tr>
                    <tr>
                        <th>Currency:</th>
                        <td><?php echo esc_html($booking->currency); ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td><span class="booking-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="booking-actions">
            <h2>Actions</h2>
            <div class="action-buttons">
                <?php if ($booking->payment_status === 'deposit_paid'): ?>
                <a href="?page=yolo-ys-bookings&action=send_reminder&booking_id=<?php echo $booking->id; ?>" 
                   class="button button-primary"
                   onclick="return confirm('Send payment reminder email to <?php echo esc_js($booking->customer_email); ?>?');">
                    📧 Send Payment Reminder
                </a>
                <a href="?page=yolo-ys-bookings&action=mark_paid&booking_id=<?php echo $booking->id; ?>" 
                   class="button button-secondary"
                   onclick="return confirm('Mark this booking as fully paid?');">
                    ✅ Mark as Fully Paid
                </a>
                <?php endif; ?>
                
                <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>?subject=Your Booking <?php echo esc_attr($booking_reference); ?>" 
                   class="button">
                    ✉️ Email Customer
                </a>
                
                <?php if (!empty($booking->customer_phone)): ?>
                <a href="tel:<?php echo esc_attr($booking->customer_phone); ?>" class="button">
                    📞 Call Customer
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
