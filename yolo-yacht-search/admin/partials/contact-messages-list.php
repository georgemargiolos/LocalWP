<?php
/**
 * Contact Messages List Page
 *
 * Displays all contact form submissions with filtering
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/Partials
 * @since 17.5
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get filter
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';

// Get messages
$messages = YOLO_YS_Contact_Messages::get_all_messages($status_filter);

// Calculate statistics
global $wpdb;
$table_name = $wpdb->prefix . 'yolo_contact_messages';
$total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$new_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'new'");
$reviewed_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'reviewed'");
$responded_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'responded'");
?>

<div class="wrap yolo-contact-messages-wrapper">
    <h1 class="wp-heading-inline">Contact Messages</h1>
    <hr class="wp-header-end">
    
    <!-- Statistics Dashboard -->
    <div class="yolo-stats-dashboard">
        <div class="stat-box stat-total">
            <div class="stat-icon"><span class="dashicons dashicons-email"></span></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo esc_html($total_count); ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
        </div>
        
        <div class="stat-box stat-new">
            <div class="stat-icon"><span class="dashicons dashicons-marker"></span></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo esc_html($new_count); ?></div>
                <div class="stat-label">New</div>
            </div>
        </div>
        
        <div class="stat-box stat-reviewed">
            <div class="stat-icon"><span class="dashicons dashicons-visibility"></span></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo esc_html($reviewed_count); ?></div>
                <div class="stat-label">Reviewed</div>
            </div>
        </div>
        
        <div class="stat-box stat-responded">
            <div class="stat-icon"><span class="dashicons dashicons-yes-alt"></span></div>
            <div class="stat-content">
                <div class="stat-number"><?php echo esc_html($responded_count); ?></div>
                <div class="stat-label">Responded</div>
            </div>
        </div>
    </div>
    
    <!-- Filter Tabs -->
    <div class="yolo-filter-tabs">
        <a href="?page=yolo-contact-messages&status=all" 
           class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
            All (<?php echo esc_html($total_count); ?>)
        </a>
        <a href="?page=yolo-contact-messages&status=new" 
           class="filter-tab <?php echo $status_filter === 'new' ? 'active' : ''; ?>">
            New (<?php echo esc_html($new_count); ?>)
        </a>
        <a href="?page=yolo-contact-messages&status=reviewed" 
           class="filter-tab <?php echo $status_filter === 'reviewed' ? 'active' : ''; ?>">
            Reviewed (<?php echo esc_html($reviewed_count); ?>)
        </a>
        <a href="?page=yolo-contact-messages&status=responded" 
           class="filter-tab <?php echo $status_filter === 'responded' ? 'active' : ''; ?>">
            Responded (<?php echo esc_html($responded_count); ?>)
        </a>
    </div>
    
    <!-- Messages Table -->
    <?php if (empty($messages)): ?>
        <div class="yolo-empty-state">
            <span class="dashicons dashicons-email-alt"></span>
            <h3>No contact messages found</h3>
            <p>Contact messages will appear here when submitted via the contact form.</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped yolo-messages-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Subject</th>
                    <th style="width: 120px;">Status</th>
                    <th style="width: 150px;">Date</th>
                    <th style="width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $message): ?>
                    <tr data-message-id="<?php echo esc_attr($message['id']); ?>">
                        <td><?php echo esc_html($message['id']); ?></td>
                        <td><strong><?php echo esc_html($message['contact_name']); ?></strong></td>
                        <td><?php echo esc_html($message['contact_email']); ?></td>
                        <td><?php echo esc_html($message['contact_phone'] ?: '—'); ?></td>
                        <td><?php echo esc_html(wp_trim_words($message['contact_subject'], 10)); ?></td>
                        <td>
                            <?php
                            $status_class = 'status-' . $message['status'];
                            $status_label = ucfirst($message['status']);
                            ?>
                            <span class="status-badge <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html(date('M j, Y g:i A', strtotime($message['created_at']))); ?></td>
                        <td>
                            <a href="?page=yolo-contact-messages&action=view&message_id=<?php echo esc_attr($message['id']); ?>" 
                               class="button button-small">
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
/* Statistics Dashboard */
.yolo-stats-dashboard {
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
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-icon {
    font-size: 40px;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
}

.stat-total .stat-icon { background: #EEF2FF; color: #4F46E5; }
.stat-new .stat-icon { background: #DBEAFE; color: #2563EB; }
.stat-reviewed .stat-icon { background: #FEF3C7; color: #D97706; }
.stat-responded .stat-icon { background: #D1FAE5; color: #059669; }

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 13px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}

/* Filter Tabs */
.yolo-filter-tabs {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px;
    margin: 20px 0;
    display: flex;
    gap: 10px;
}

.filter-tab {
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    color: #64748b;
    font-weight: 500;
    transition: all 0.2s;
}

.filter-tab:hover {
    background: #f8fafc;
    color: #1e293b;
}

.filter-tab.active {
    background: #3b82f6;
    color: white;
}

/* Messages Table */
.yolo-messages-table {
    margin-top: 20px;
    background: white;
}

.yolo-messages-table th {
    font-weight: 600;
    color: #374151;
}

.yolo-messages-table td {
    vertical-align: middle;
}

/* Status Badges */
.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-new {
    background: #DBEAFE;
    color: #1E40AF;
}

.status-badge.status-reviewed {
    background: #FEF3C7;
    color: #92400E;
}

.status-badge.status-responded {
    background: #D1FAE5;
    color: #065F46;
}

/* Empty State */
.yolo-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    margin: 20px 0;
}

.yolo-empty-state .dashicons {
    font-size: 80px;
    width: 80px;
    height: 80px;
    color: #cbd5e1;
    margin-bottom: 20px;
}

.yolo-empty-state h3 {
    color: #1e293b;
    margin: 0 0 10px 0;
}

.yolo-empty-state p {
    color: #64748b;
    margin: 0;
}
</style>
