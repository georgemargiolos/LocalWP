<?php
/**
 * Charter Calendar View - Monthly Calendar with Bookings
 * Replaces dropdown selector with visual calendar
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_bookings = $wpdb->prefix . 'yolo_bookings';

// Get current month/year from query params or default to current
$current_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$current_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Fetch all bookings for display
$all_bookings = $wpdb->get_results("
    SELECT id, yacht_name, date_from, date_to, customer_name, payment_status, booking_status
    FROM {$table_bookings}
    ORDER BY date_from ASC
");

// Group bookings by month/year
$bookings_by_month = array();
foreach ($all_bookings as $booking) {
    $month_key = date('Y-m', strtotime($booking->date_from));
    if (!isset($bookings_by_month[$month_key])) {
        $bookings_by_month[$month_key] = array();
    }
    $bookings_by_month[$month_key][] = $booking;
}

// Get unique months that have bookings
$available_months = array_keys($bookings_by_month);
sort($available_months);

// If no month selected and there are bookings, default to first month with bookings
if (empty($_GET['month']) && !empty($available_months)) {
    $first_month = $available_months[0];
    $current_year = intval(substr($first_month, 0, 4));
    $current_month = intval(substr($first_month, 5, 2));
}

$current_month_key = sprintf('%04d-%02d', $current_year, $current_month);
$bookings_this_month = isset($bookings_by_month[$current_month_key]) ? $bookings_by_month[$current_month_key] : array();

// Calculate previous and next month
$prev_month = $current_month - 1;
$prev_year = $current_year;
if ($prev_month < 1) {
    $prev_month = 12;
    $prev_year--;
}

$next_month = $current_month + 1;
$next_year = $current_year;
if ($next_month > 12) {
    $next_month = 1;
    $next_year++;
}

$month_name = date('F Y', mktime(0, 0, 0, $current_month, 1, $current_year));
?>

<div class="wrap yolo-charter-calendar">
    <h1>Charter Calendar</h1>
    <p class="yolo-calendar-subtitle">View and manage all charter bookings by month</p>
    
    <!-- Month Navigation -->
    <div class="yolo-month-navigation">
        <a href="?page=yolo-ys-bookings&view=calendar&month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="yolo-nav-btn">
            ← <?php echo date('F Y', mktime(0, 0, 0, $prev_month, 1, $prev_year)); ?>
        </a>
        
        <h2 class="yolo-current-month"><?php echo $month_name; ?></h2>
        
        <a href="?page=yolo-ys-bookings&view=calendar&month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="yolo-nav-btn">
            <?php echo date('F Y', mktime(0, 0, 0, $next_month, 1, $next_year)); ?> →
        </a>
    </div>
    
    <!-- Month Tabs (Quick Jump) -->
    <?php if (!empty($available_months)): ?>
    <div class="yolo-month-tabs">
        <?php foreach ($available_months as $month_key): 
            $tab_year = intval(substr($month_key, 0, 4));
            $tab_month = intval(substr($month_key, 5, 2));
            $tab_label = date('M Y', mktime(0, 0, 0, $tab_month, 1, $tab_year));
            $is_active = ($month_key === $current_month_key) ? 'active' : '';
            $booking_count = count($bookings_by_month[$month_key]);
        ?>
            <a href="?page=yolo-ys-bookings&view=calendar&month=<?php echo $tab_month; ?>&year=<?php echo $tab_year; ?>" 
               class="yolo-month-tab <?php echo $is_active; ?>">
                <?php echo $tab_label; ?>
                <span class="yolo-booking-count">(<?php echo $booking_count; ?>)</span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Bookings List for Current Month -->
    <div class="yolo-bookings-container">
        <?php if (empty($bookings_this_month)): ?>
            <div class="yolo-no-bookings">
                <p>No charters scheduled for <?php echo $month_name; ?></p>
            </div>
        <?php else: ?>
            <div class="yolo-bookings-grid">
                <?php foreach ($bookings_this_month as $booking): 
                    $date_from = date('M j, Y', strtotime($booking->date_from));
                    $date_to = date('M j, Y', strtotime($booking->date_to));
                    $duration = (strtotime($booking->date_to) - strtotime($booking->date_from)) / 86400;
                    
                    $status_class = '';
                    $status_label = '';
                    if ($booking->payment_status === 'fully_paid') {
                        $status_class = 'status-paid';
                        $status_label = 'Fully Paid';
                    } elseif ($booking->payment_status === 'deposit_paid') {
                        $status_class = 'status-deposit';
                        $status_label = 'Deposit Paid';
                    } else {
                        $status_class = 'status-pending';
                        $status_label = 'Pending';
                    }
                ?>
                    <div class="yolo-booking-card <?php echo $status_class; ?>" data-booking-id="<?php echo $booking->id; ?>">
                        <div class="yolo-booking-header">
                            <h3><?php echo esc_html($booking->yacht_name); ?></h3>
                            <span class="yolo-booking-status <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                        </div>
                        
                        <div class="yolo-booking-details">
                            <div class="yolo-detail-row">
                                <span class="yolo-icon">📅</span>
                                <span><?php echo $date_from; ?> → <?php echo $date_to; ?></span>
                            </div>
                            
                            <div class="yolo-detail-row">
                                <span class="yolo-icon">⏱️</span>
                                <span><?php echo intval($duration); ?> days</span>
                            </div>
                            
                            <div class="yolo-detail-row">
                                <span class="yolo-icon">👤</span>
                                <span><?php echo esc_html($booking->customer_name); ?></span>
                            </div>
                        </div>
                        
                        <div class="yolo-booking-actions">
                            <button class="yolo-view-documents-btn" data-booking-id="<?php echo $booking->id; ?>">
                                📄 View Documents
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Document Viewer Modal -->
    <div id="yolo-document-modal" class="yolo-modal" style="display: none;">
        <div class="yolo-modal-content">
            <span class="yolo-modal-close">&times;</span>
            <div id="yolo-document-viewer-content">
                <p>Loading documents...</p>
            </div>
        </div>
    </div>
</div>

<style>
.yolo-charter-calendar {
    max-width: 1400px;
}

.yolo-calendar-subtitle {
    color: #666;
    font-size: 14px;
    margin-top: -10px;
    margin-bottom: 30px;
}

.yolo-month-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.yolo-current-month {
    margin: 0;
    font-size: 24px;
    color: #1e3a8a;
}

.yolo-nav-btn {
    padding: 10px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: all 0.3s;
}

.yolo-nav-btn:hover {
    background: #1e3a8a;
    color: #fff;
    border-color: #1e3a8a;
}

.yolo-month-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 30px;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.yolo-month-tab {
    padding: 8px 16px;
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    transition: all 0.3s;
}

.yolo-month-tab:hover {
    background: #e9ecef;
}

.yolo-month-tab.active {
    background: #1e3a8a;
    color: #fff;
    border-color: #1e3a8a;
}

.yolo-booking-count {
    font-size: 12px;
    opacity: 0.8;
}

.yolo-no-bookings {
    text-align: center;
    padding: 60px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #666;
}

.yolo-bookings-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.yolo-booking-card {
    flex: 1 1 calc(33.333% - 20px);
    min-width: 350px;
    max-width: 100%;
}

.yolo-booking-card {
    background: #fff;
    border: 2px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s;
    cursor: pointer;
}

.yolo-booking-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.yolo-booking-card.status-paid {
    border-left: 4px solid #10b981;
}

.yolo-booking-card.status-deposit {
    border-left: 4px solid #f59e0b;
}

.yolo-booking-card.status-pending {
    border-left: 4px solid #ef4444;
}

.yolo-booking-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.yolo-booking-header h3 {
    margin: 0;
    font-size: 18px;
    color: #1e3a8a;
}

.yolo-booking-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.yolo-booking-status.status-paid {
    background: #d1fae5;
    color: #065f46;
}

.yolo-booking-status.status-deposit {
    background: #fef3c7;
    color: #92400e;
}

.yolo-booking-status.status-pending {
    background: #fee2e2;
    color: #991b1b;
}

.yolo-booking-details {
    margin-bottom: 15px;
}

.yolo-detail-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    font-size: 14px;
    color: #374151;
}

.yolo-icon {
    font-size: 16px;
}

.yolo-booking-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.yolo-view-documents-btn {
    width: 100%;
    padding: 10px;
    background: #1e3a8a;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
}

.yolo-view-documents-btn:hover {
    background: #1e40af;
}

.yolo-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
}

.yolo-modal-content {
    position: relative;
    background-color: #fff;
    margin: 5% auto;
    padding: 30px;
    border-radius: 8px;
    width: 90%;
    max-width: 1000px;
    max-height: 80vh;
    overflow-y: auto;
}

.yolo-modal-close {
    position: absolute;
    right: 20px;
    top: 15px;
    font-size: 32px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.yolo-modal-close:hover {
    color: #000;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle view documents button
    $('.yolo-view-documents-btn').on('click', function(e) {
        e.stopPropagation();
        var bookingId = $(this).data('booking-id');
        var modal = $('#yolo-document-modal');
        var content = $('#yolo-document-viewer-content');
        
        modal.show();
        content.html('<p>Loading documents...</p>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_admin_fetch_booking_documents',
                booking_id: bookingId,
                nonce: '<?php echo wp_create_nonce('yolo_admin_documents_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    content.html(response.data.html);
                } else {
                    content.html('<div class="error"><p>Error: ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                content.html('<div class="error"><p>Failed to load documents.</p></div>');
            }
        });
    });
    
    // Close modal
    $('.yolo-modal-close').on('click', function() {
        $('#yolo-document-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).is('#yolo-document-modal')) {
            $('#yolo-document-modal').hide();
        }
    });
    
    // Handle document upload form (delegated event for dynamically loaded content)
    $(document).on('submit', '#yolo-admin-upload-form', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'yolo_admin_upload_document');
        
        var statusSpan = $(this).find('.yolo-upload-status');
        var submitBtn = $(this).find('button[type="submit"]');
        
        statusSpan.removeClass('success error').text('Uploading...');
        submitBtn.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    statusSpan.addClass('success').text(response.data.message);
                    // Reload the document viewer to show the new document
                    var bookingId = $('#yolo-admin-upload-form input[name="booking_id"]').val();
                    var content = $('#yolo-document-viewer-content');
                    
                    content.html('<p>Reloading documents...</p>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'yolo_admin_fetch_booking_documents',
                            booking_id: bookingId,
                            nonce: '<?php echo wp_create_nonce('yolo_admin_documents_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                content.html(response.data.html);
                            }
                        }
                    });
                } else {
                    statusSpan.addClass('error').text(response.data.message);
                }
                submitBtn.prop('disabled', false);
            },
            error: function() {
                statusSpan.addClass('error').text('Upload failed. Please try again.');
                submitBtn.prop('disabled', false);
            }
        });
    });
    
    // Handle document deletion (delegated event for dynamically loaded content)
    $(document).on('click', '.yolo-delete-admin-doc', function() {
        if (!confirm('Are you sure you want to delete this document?')) {
            return;
        }
        
        var docId = $(this).data('doc-id');
        var bookingId = $(this).data('booking-id');
        var btn = $(this);
        
        btn.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_admin_delete_document',
                document_id: docId,
                booking_id: bookingId,
                nonce: '<?php echo wp_create_nonce('yolo_admin_documents_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Reload the document viewer
                    var content = $('#yolo-document-viewer-content');
                    content.html('<p>Reloading documents...</p>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'yolo_admin_fetch_booking_documents',
                            booking_id: bookingId,
                            nonce: '<?php echo wp_create_nonce('yolo_admin_documents_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                content.html(response.data.html);
                            }
                        }
                    });
                } else {
                    alert('Error: ' + response.data.message);
                    btn.prop('disabled', false).text('Delete');
                }
            },
            error: function() {
                alert('Failed to delete document. Please try again.');
                btn.prop('disabled', false).text('Delete');
            }
        });
    });
});
</script>
