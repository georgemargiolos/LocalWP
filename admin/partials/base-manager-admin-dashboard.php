<?php
/**
 * Base Manager Admin Dashboard
 *
 * Welcome page for base managers in wp-admin
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.1
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap yolo-base-manager-admin-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="yolo-bm-welcome-section">
        <div class="yolo-bm-welcome-card">
            <h2>Welcome to Base Manager Dashboard</h2>
            <p>Manage your yacht charter operations efficiently with our comprehensive base management tools.</p>
        </div>
    </div>

    <div class="yolo-bm-operations-grid">
        
        <!-- Yacht Management -->
        <div class="yolo-bm-operation-card">
            <div class="yolo-bm-operation-icon">
                <span class="dashicons dashicons-admin-multisite"></span>
            </div>
            <h3>Yacht Management</h3>
            <p>Create and manage yachts, equipment categories, and boat information.</p>
            <a href="<?php echo admin_url('admin.php?page=yolo-yacht-management'); ?>" class="button button-primary">
                Manage Yachts
            </a>
        </div>

        <!-- Check-In -->
        <div class="yolo-bm-operation-card">
            <div class="yolo-bm-operation-icon">
                <span class="dashicons dashicons-yes-alt"></span>
            </div>
            <h3>Check-In</h3>
            <p>Perform yacht check-ins with equipment verification and digital signatures.</p>
            <a href="<?php echo admin_url('admin.php?page=yolo-checkin'); ?>" class="button button-primary">
                Start Check-In
            </a>
        </div>

        <!-- Check-Out -->
        <div class="yolo-bm-operation-card">
            <div class="yolo-bm-operation-icon">
                <span class="dashicons dashicons-dismiss"></span>
            </div>
            <h3>Check-Out</h3>
            <p>Perform yacht check-outs with equipment verification and digital signatures.</p>
            <a href="<?php echo admin_url('admin.php?page=yolo-checkout'); ?>" class="button button-primary">
                Start Check-Out
            </a>
        </div>

        <!-- Warehouse Management -->
        <div class="yolo-bm-operation-card">
            <div class="yolo-bm-operation-icon">
                <span class="dashicons dashicons-store"></span>
            </div>
            <h3>Warehouse Management</h3>
            <p>Track inventory, manage stock levels, and monitor expiry dates.</p>
            <a href="<?php echo admin_url('admin.php?page=yolo-warehouse'); ?>" class="button button-primary">
                Manage Warehouse
            </a>
        </div>

        <!-- Bookings Management -->
        <div class="yolo-bm-operation-card">
            <div class="yolo-bm-operation-icon">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <h3>Bookings Management</h3>
            <p>View and manage all bookings, calendar view, and customer details.</p>
            <a href="<?php echo admin_url('admin.php?page=yolo-ys-bookings'); ?>" class="button button-primary">
                Manage Bookings
            </a>
        </div>

        <!-- Documents & PDFs -->
        <div class="yolo-bm-operation-card">
            <div class="yolo-bm-operation-icon">
                <span class="dashicons dashicons-media-document"></span>
            </div>
            <h3>Documents & PDFs</h3>
            <p>Access generated check-in/check-out PDFs and signed documents.</p>
            <a href="<?php echo admin_url('upload.php'); ?>" class="button button-primary">
                View Documents
            </a>
        </div>

    </div>

    <!-- Quick Stats -->
    <div class="yolo-bm-quick-stats">
        <h2>Quick Stats</h2>
        <div class="yolo-bm-stats-grid">
            <?php
            global $wpdb;
            
            // Count yachts
            $yacht_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}yolo_bm_yachts");
            
            // Count pending check-ins
            $pending_checkins = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}yolo_bm_checkins WHERE status = 'pending'");
            
            // Count pending check-outs
            $pending_checkouts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}yolo_bm_checkouts WHERE status = 'pending'");
            
            // Count warehouse items
            $warehouse_items = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}yolo_bm_warehouse");
            ?>
            
            <div class="yolo-bm-stat-card">
                <div class="yolo-bm-stat-number"><?php echo esc_html($yacht_count); ?></div>
                <div class="yolo-bm-stat-label">Total Yachts</div>
            </div>
            
            <div class="yolo-bm-stat-card">
                <div class="yolo-bm-stat-number"><?php echo esc_html($pending_checkins); ?></div>
                <div class="yolo-bm-stat-label">Pending Check-Ins</div>
            </div>
            
            <div class="yolo-bm-stat-card">
                <div class="yolo-bm-stat-number"><?php echo esc_html($pending_checkouts); ?></div>
                <div class="yolo-bm-stat-label">Pending Check-Outs</div>
            </div>
            
            <div class="yolo-bm-stat-card">
                <div class="yolo-bm-stat-number"><?php echo esc_html($warehouse_items); ?></div>
                <div class="yolo-bm-stat-label">Warehouse Items</div>
            </div>
        </div>
    </div>

</div>
