<?php
/**
 * Admin Bookings List Template with Tabbed Views
 *
 * @package    YOLO_Yacht_Search
 * @subpackage YOLO_Yacht_Search/admin/partials
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get current view from URL parameter (default: table)
$current_view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'table';

// Handle actions for table view
if ($current_view === 'table' && isset($_GET['action']) && isset($_GET['booking_id'])) {
    $action = sanitize_text_field($_GET['action']);
    $booking_id = intval($_GET['booking_id']);
    
    switch ($action) {
        case 'view':
            include_once plugin_dir_path(__FILE__) . 'booking-detail.php';
            return;
            
        case 'send_reminder':
            $result = YOLO_YS_Admin_Bookings_Manager::send_payment_reminder($booking_id);
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>Payment reminder sent successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to send payment reminder.</p></div>';
            }
            break;
            
        case 'mark_paid':
            $result = YOLO_YS_Admin_Bookings_Manager::mark_as_paid($booking_id);
            if ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>Booking marked as fully paid!</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>Failed to update booking status.</p></div>';
            }
            break;
    }
}

// Handle export for table view
if ($current_view === 'table' && isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    YOLO_YS_Admin_Bookings_Manager::export_to_csv();
    exit;
}
?>

<div class="wrap yolo-bookings-wrapper">
    <h1 class="wp-heading-inline">Bookings</h1>
    <hr class="wp-header-end">
    
    <!-- View Tabs -->
    <div class="yolo-view-tabs">
        <a href="?page=yolo-ys-bookings&view=table" class="yolo-tab <?php echo $current_view === 'table' ? 'active' : ''; ?>">
            📊 Table View
        </a>
        <a href="?page=yolo-ys-bookings&view=calendar" class="yolo-tab <?php echo $current_view === 'calendar' ? 'active' : ''; ?>">
            📅 Calendar View
        </a>
    </div>
    
    <?php if ($current_view === 'table'): ?>
        <!-- TABLE VIEW -->
        <?php
        // Display booking statistics
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $total_bookings = $wpdb->get_var("SELECT COUNT(*) FROM {$table_bookings}");
        $deposit_paid = $wpdb->get_var("SELECT COUNT(*) FROM {$table_bookings} WHERE payment_status = 'deposit_paid'");
        $fully_paid = $wpdb->get_var("SELECT COUNT(*) FROM {$table_bookings} WHERE payment_status = 'fully_paid'");
        $total_revenue = $wpdb->get_var("SELECT SUM(deposit_paid) FROM {$table_bookings}");
        ?>
        
        <div class="yolo-booking-stats">
            <div class="stat-box">
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $deposit_paid; ?></div>
                <div class="stat-label">Pending Balance</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo $fully_paid; ?></div>
                <div class="stat-label">Fully Paid</div>
            </div>
            <div class="stat-box">
                <div class="stat-number"><?php echo YOLO_YS_Price_Formatter::format_price($total_revenue, 'EUR'); ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
        
        <form method="get">
            <input type="hidden" name="page" value="yolo-ys-bookings">
            <input type="hidden" name="view" value="table">
            <?php
            $bookings_table = new YOLO_YS_Admin_Bookings();
            $bookings_table->prepare_items();
            $bookings_table->search_box('Search Bookings', 'booking');
            $bookings_table->display();
            ?>
        </form>
        
    <?php else: ?>
        <!-- CALENDAR VIEW -->
        <?php include_once plugin_dir_path(__FILE__) . 'charter-calendar-view.php'; ?>
    <?php endif; ?>
</div>

<style>
.yolo-bookings-wrapper {
    max-width: 100%;
}

.yolo-view-tabs {
    display: flex;
    gap: 10px;
    margin: 20px 0;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 0;
}

.yolo-tab {
    padding: 12px 24px;
    text-decoration: none;
    color: #6b7280;
    font-weight: 500;
    font-size: 15px;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
    position: relative;
    top: 2px;
}

.yolo-tab:hover {
    color: #1e3a8a;
    background: #f3f4f6;
}

.yolo-tab.active {
    color: #1e3a8a;
    border-bottom-color: #1e3a8a;
    font-weight: 600;
}

/* Ensure calendar view fits properly */
.yolo-charter-calendar {
    margin-top: 0;
}

.yolo-charter-calendar h1 {
    display: none; /* Hide duplicate title */
}

.yolo-calendar-subtitle {
    margin-top: 0;
}
</style>
