<?php
/**
 * Quote Request Detail View
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/Partials
 * @since 17.4
 */

if (!defined('ABSPATH')) {
    exit;
}

$quote_id = intval($_GET['quote_id']);
$quote = YOLO_YS_Quote_Requests::get_quote_by_id($quote_id);

if (!$quote) {
    echo '<div class="wrap"><h1>Quote Request Not Found</h1><p><a href="?page=yolo-quote-requests">Back to Quote Requests</a></p></div>';
    return;
}

// Mark as viewed
YOLO_YS_Quote_Requests::mark_as_viewed($quote_id, get_current_user_id());
?>

<div class="wrap yolo-quote-detail-wrapper">
    <h1>Quote Request #<?php echo $quote['id']; ?></h1>
    <a href="?page=yolo-quote-requests" class="button">&larr; Back to All Quotes</a>
    
    <div class="yolo-quote-detail-container">
        <!-- Status Card -->
        <div class="detail-card status-card">
            <h2>Status</h2>
            <div class="current-status">
                <span class="status-badge status-<?php echo $quote['status']; ?>">
                    <?php echo ucfirst($quote['status']); ?>
                </span>
            </div>
            <div class="status-actions">
                <?php if ($quote['status'] === 'new'): ?>
                    <a href="?page=yolo-quote-requests&action=mark_reviewed&quote_id=<?php echo $quote['id']; ?>" 
                       class="button button-primary">Mark as Reviewed</a>
                <?php endif; ?>
                <?php if ($quote['status'] === 'reviewed'): ?>
                    <a href="?page=yolo-quote-requests&action=mark_responded&quote_id=<?php echo $quote['id']; ?>" 
                       class="button button-primary">Mark as Responded</a>
                <?php endif; ?>
            </div>
            <div class="meta-info">
                <p><strong>Received:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($quote['created_at'])); ?></p>
                <?php if ($quote['viewed_by']): ?>
                    <p><strong>Viewed by:</strong> <?php echo esc_html($quote['viewed_by']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Customer Information -->
        <div class="detail-card">
            <h2>Customer Information</h2>
            <table class="detail-table">
                <tr>
                    <th>Name:</th>
                    <td><?php echo esc_html($quote['customer_name']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>
                        <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>">
                            <?php echo esc_html($quote['customer_email']); ?>
                        </a>
                    </td>
                </tr>
                <?php if ($quote['customer_phone']): ?>
                <tr>
                    <th>Phone:</th>
                    <td>
                        <a href="tel:<?php echo esc_attr($quote['customer_phone']); ?>">
                            <?php echo esc_html($quote['customer_phone']); ?>
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
            <div class="quick-actions">
                <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>" class="button">
                    <span class="dashicons dashicons-email"></span> Send Email
                </a>
                <?php if ($quote['customer_phone']): ?>
                <a href="tel:<?php echo esc_attr($quote['customer_phone']); ?>" class="button">
                    <span class="dashicons dashicons-phone"></span> Call
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Charter Details -->
        <div class="detail-card">
            <h2>Charter Details</h2>
            <table class="detail-table">
                <?php if ($quote['yacht_preference']): ?>
                <tr>
                    <th>Yacht Preference:</th>
                    <td><?php echo esc_html($quote['yacht_preference']); ?></td>
                </tr>
                <?php endif; ?>
                <?php if ($quote['checkin_date'] && $quote['checkout_date']): ?>
                <tr>
                    <th>Check-In Date:</th>
                    <td><?php echo date('F j, Y', strtotime($quote['checkin_date'])); ?></td>
                </tr>
                <tr>
                    <th>Check-Out Date:</th>
                    <td><?php echo date('F j, Y', strtotime($quote['checkout_date'])); ?></td>
                </tr>
                <tr>
                    <th>Duration:</th>
                    <td>
                        <?php 
                        $days = (strtotime($quote['checkout_date']) - strtotime($quote['checkin_date'])) / (60 * 60 * 24);
                        echo $days . ' day' . ($days != 1 ? 's' : '');
                        ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if ($quote['num_guests']): ?>
                <tr>
                    <th>Number of Guests:</th>
                    <td><?php echo $quote['num_guests']; ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Special Requests -->
        <?php if ($quote['special_requests']): ?>
        <div class="detail-card">
            <h2>Special Requests</h2>
            <div class="special-requests-content">
                <?php echo nl2br(esc_html($quote['special_requests'])); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Internal Notes -->
        <div class="detail-card">
            <h2>Internal Notes</h2>
            <textarea id="quote-notes" class="widefat" rows="5" placeholder="Add internal notes about this quote request..."><?php echo esc_textarea($quote['notes']); ?></textarea>
            <button type="button" class="button button-primary" onclick="saveQuoteNotes(<?php echo $quote['id']; ?>)">
                Save Notes
            </button>
        </div>
    </div>
</div>

<style>
.yolo-quote-detail-wrapper {
    max-width: 1200px;
}

.yolo-quote-detail-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.detail-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.detail-card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
    color: #1e3a8a;
}

.status-card {
    grid-column: 1 / -1;
}

.current-status {
    margin: 20px 0;
    text-align: center;
}

.current-status .status-badge {
    font-size: 18px;
    padding: 10px 24px;
}

.status-actions {
    text-align: center;
    margin: 20px 0;
}

.meta-info {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e5e7eb;
    color: #6b7280;
    font-size: 14px;
}

.detail-table {
    width: 100%;
    margin: 15px 0;
}

.detail-table th {
    text-align: left;
    padding: 10px;
    font-weight: 600;
    color: #374151;
    width: 40%;
    vertical-align: top;
}

.detail-table td {
    padding: 10px;
    color: #6b7280;
}

.detail-table tr {
    border-bottom: 1px solid #f3f4f6;
}

.quick-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.quick-actions .button {
    display: flex;
    align-items: center;
    gap: 5px;
}

.special-requests-content {
    background: #f9fafb;
    padding: 15px;
    border-radius: 6px;
    margin-top: 15px;
    line-height: 1.6;
    color: #374151;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-new {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.status-reviewed {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.status-responded {
    background: #d1fae5;
    color: #065f46;
}
</style>

<script>
function saveQuoteNotes(quoteId) {
    const notes = document.getElementById('quote-notes').value;
    
    jQuery.ajax({
        url: ajaxurl,
        type: 'POST',
        data: {
            action: 'yolo_save_quote_notes',
            nonce: '<?php echo wp_create_nonce('yolo_quote_notifications'); ?>',
            quote_id: quoteId,
            notes: notes
        },
        success: function(response) {
            if (response.success) {
                alert('Notes saved successfully!');
            } else {
                alert('Failed to save notes.');
            }
        }
    });
}
</script>
