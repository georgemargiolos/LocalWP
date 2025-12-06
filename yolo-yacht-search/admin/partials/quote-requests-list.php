<?php
/**
 * Quote Requests List View
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/Partials
 * @since 17.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['quote_id'])) {
    $action = sanitize_text_field($_GET['action']);
    $quote_id = intval($_GET['quote_id']);
    
    switch ($action) {
        case 'view':
            include_once plugin_dir_path(__FILE__) . 'quote-request-detail.php';
            return;
            
        case 'mark_reviewed':
            global $wpdb;
            $table_name = $wpdb->prefix . 'yolo_quote_requests';
            $wpdb->update($table_name, array('status' => 'reviewed'), array('id' => $quote_id));
            echo '<div class="notice notice-success is-dismissible"><p>Quote marked as reviewed!</p></div>';
            break;
            
        case 'mark_responded':
            global $wpdb;
            $table_name = $wpdb->prefix . 'yolo_quote_requests';
            $wpdb->update($table_name, array('status' => 'responded'), array('id' => $quote_id));
            echo '<div class="notice notice-success is-dismissible"><p>Quote marked as responded!</p></div>';
            break;
    }
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Get quotes
$quotes = YOLO_YS_Quote_Requests::get_all_quotes($status_filter);

// Get statistics
global $wpdb;
$table_name = $wpdb->prefix . 'yolo_quote_requests';
$total_quotes = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
$new_quotes = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'new'");
$reviewed_quotes = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'reviewed'");
$responded_quotes = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status = 'responded'");
?>

<div class="wrap yolo-quote-requests-wrapper">
    <h1 class="wp-heading-inline">Quote Requests</h1>
    <hr class="wp-header-end">
    
    <!-- Statistics -->
    <div class="yolo-quote-stats">
        <div class="stat-box">
            <div class="stat-number"><?php echo $total_quotes; ?></div>
            <div class="stat-label">Total Requests</div>
        </div>
        <div class="stat-box new">
            <div class="stat-number"><?php echo $new_quotes; ?></div>
            <div class="stat-label">New</div>
        </div>
        <div class="stat-box reviewed">
            <div class="stat-number"><?php echo $reviewed_quotes; ?></div>
            <div class="stat-label">Reviewed</div>
        </div>
        <div class="stat-box responded">
            <div class="stat-number"><?php echo $responded_quotes; ?></div>
            <div class="stat-label">Responded</div>
        </div>
    </div>
    
    <!-- Status Filter -->
    <div class="yolo-status-filter">
        <a href="?page=yolo-quote-requests&status=all" class="filter-btn <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
            All (<?php echo $total_quotes; ?>)
        </a>
        <a href="?page=yolo-quote-requests&status=new" class="filter-btn <?php echo $status_filter === 'new' ? 'active' : ''; ?>">
            New (<?php echo $new_quotes; ?>)
        </a>
        <a href="?page=yolo-quote-requests&status=reviewed" class="filter-btn <?php echo $status_filter === 'reviewed' ? 'active' : ''; ?>">
            Reviewed (<?php echo $reviewed_quotes; ?>)
        </a>
        <a href="?page=yolo-quote-requests&status=responded" class="filter-btn <?php echo $status_filter === 'responded' ? 'active' : ''; ?>">
            Responded (<?php echo $responded_quotes; ?>)
        </a>
    </div>
    
    <!-- Quotes Table -->
    <?php if (empty($quotes)): ?>
        <!-- Empty state OUTSIDE table to avoid DataTables column count error (v30.0 fix) -->
        <div class="notice notice-info" style="text-align: center; padding: 40px; margin: 20px 0;">
            <p style="font-size: 16px; color: #666; margin: 0;">No quote requests found.</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped yolo-quotes-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Yacht Preference</th>
                    <th>Charter Dates</th>
                    <th>Guests</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quotes as $quote): ?>
                    <tr class="quote-row <?php echo $quote['status']; ?>">
                        <td><strong>#<?php echo $quote['id']; ?></strong></td>
                        <td>
                            <?php echo date('M d, Y', strtotime($quote['created_at'])); ?><br>
                            <small><?php echo date('g:i A', strtotime($quote['created_at'])); ?></small>
                        </td>
                        <td><strong><?php echo esc_html($quote['customer_name']); ?></strong></td>
                        <td>
                            <a href="mailto:<?php echo esc_attr($quote['customer_email']); ?>">
                                <?php echo esc_html($quote['customer_email']); ?>
                            </a><br>
                            <?php if ($quote['customer_phone']): ?>
                                <small><?php echo esc_html($quote['customer_phone']); ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $quote['yacht_preference'] ? esc_html($quote['yacht_preference']) : '—'; ?></td>
                        <td>
                            <?php if ($quote['checkin_date'] && $quote['checkout_date']): ?>
                                <?php echo date('M d', strtotime($quote['checkin_date'])); ?> - 
                                <?php echo date('M d, Y', strtotime($quote['checkout_date'])); ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo $quote['num_guests'] ? $quote['num_guests'] : '—'; ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $quote['status']; ?>">
                                <?php echo ucfirst($quote['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="?page=yolo-quote-requests&action=view&quote_id=<?php echo $quote['id']; ?>" class="button button-small">
                                View Details
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.yolo-quote-requests-wrapper {
    max-width: 100%;
}

.yolo-quote-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-box {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-box.new {
    border-left: 4px solid #3b82f6;
}

.stat-box.reviewed {
    border-left: 4px solid #f59e0b;
}

.stat-box.responded {
    border-left: 4px solid #10b981;
}

.stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #1e3a8a;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 14px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.yolo-status-filter {
    display: flex;
    gap: 10px;
    margin: 20px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #e5e7eb;
}

.filter-btn {
    padding: 10px 20px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s;
}

.filter-btn:hover {
    background: #f3f4f6;
    color: #1e3a8a;
}

.filter-btn.active {
    background: #1e3a8a;
    color: white;
}

.yolo-quotes-table {
    margin-top: 20px;
}

.yolo-quotes-table th {
    font-weight: 600;
    background: #f9fafb;
}

.quote-row.new {
    background: #eff6ff;
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
