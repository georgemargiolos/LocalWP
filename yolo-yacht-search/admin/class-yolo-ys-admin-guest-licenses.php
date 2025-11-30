<?php
/**
 * Admin Panel for Guest License Uploads
 * Displays all uploaded licenses with download functionality
 */
class YOLO_YS_Admin_Guest_Licenses {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_styles'));
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_submenu_page(
            'yolo-yacht-search',
            'Guest Licenses',
            'Guest Licenses',
            'manage_options',
            'yolo-guest-licenses',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_styles($hook) {
        if ($hook !== 'yolo-yacht-search_page_yolo-guest-licenses') {
            return;
        }
        
        wp_enqueue_style(
            'yolo-admin-guest-licenses',
            plugin_dir_url(dirname(__FILE__)) . 'admin/css/yolo-ys-admin-guest-licenses.css',
            array(),
            '1.0.0'
        );
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        global $wpdb;
        
        // Get all license uploads with booking and user information
        $table_licenses = $wpdb->prefix . 'yolo_license_uploads';
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $licenses = $wpdb->get_results("
            SELECT 
                l.*,
                b.yacht_name,
                b.customer_name,
                b.customer_email,
                b.date_from,
                b.date_to,
                u.display_name as user_display_name
            FROM {$table_licenses} l
            LEFT JOIN {$table_bookings} b ON l.booking_id = b.id
            LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID
            ORDER BY l.uploaded_at DESC
        ");
        
        // Group licenses by booking
        $grouped_licenses = array();
        foreach ($licenses as $license) {
            if (!isset($grouped_licenses[$license->booking_id])) {
                $grouped_licenses[$license->booking_id] = array(
                    'booking_info' => array(
                        'id' => $license->booking_id,
                        'yacht_name' => $license->yacht_name,
                        'customer_name' => $license->customer_name,
                        'customer_email' => $license->customer_email,
                        'user_display_name' => $license->user_display_name,
                        'date_from' => $license->date_from,
                        'date_to' => $license->date_to
                    ),
                    'licenses' => array()
                );
            }
            $grouped_licenses[$license->booking_id]['licenses'][] = $license;
        }
        
        ?>
        <div class="wrap yolo-admin-licenses">
            <h1>Guest License Uploads</h1>
            <p class="yolo-admin-description">View and download sailing licenses uploaded by guests</p>
            
            <?php if (empty($grouped_licenses)): ?>
                <div class="yolo-no-licenses">
                    <p>No license uploads yet.</p>
                </div>
            <?php else: ?>
                <div class="yolo-licenses-grid">
                    <?php foreach ($grouped_licenses as $booking_id => $data): ?>
                        <?php 
                        $booking = $data['booking_info'];
                        $booking_licenses = $data['licenses'];
                        
                        $has_front = false;
                        $has_back = false;
                        $front_license = null;
                        $back_license = null;
                        
                        foreach ($booking_licenses as $license) {
                            if ($license->file_type === 'front') {
                                $has_front = true;
                                $front_license = $license;
                            }
                            if ($license->file_type === 'back') {
                                $has_back = true;
                                $back_license = $license;
                            }
                        }
                        
                        $is_complete = $has_front && $has_back;
                        ?>
                        
                        <div class="yolo-license-card <?php echo $is_complete ? 'complete' : 'incomplete'; ?>">
                            <div class="yolo-license-card-header">
                                <div>
                                    <h3><?php echo esc_html($booking['customer_name']); ?></h3>
                                    <p class="yolo-booking-yacht"><?php echo esc_html($booking['yacht_name']); ?></p>
                                    <p class="yolo-booking-dates">
                                        <?php echo date('M j, Y', strtotime($booking['date_from'])); ?> - 
                                        <?php echo date('M j, Y', strtotime($booking['date_to'])); ?>
                                    </p>
                                </div>
                                <div class="yolo-license-status">
                                    <?php if ($is_complete): ?>
                                        <span class="yolo-status-badge complete">✓ Complete</span>
                                    <?php else: ?>
                                        <span class="yolo-status-badge incomplete">⚠ Incomplete</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="yolo-license-card-body">
                                <div class="yolo-customer-info">
                                    <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($booking['customer_email']); ?>"><?php echo esc_html($booking['customer_email']); ?></a></p>
                                    <p><strong>Booking ID:</strong> #<?php echo esc_html($booking['id']); ?></p>
                                </div>
                                
                                <div class="yolo-license-images">
                                    <!-- Front License -->
                                    <div class="yolo-license-image-item">
                                        <h4>Front Side</h4>
                                        <?php if ($has_front): ?>
                                            <div class="yolo-license-preview">
                                                <a href="<?php echo esc_url($front_license->file_url); ?>" target="_blank">
                                                    <img src="<?php echo esc_url($front_license->file_url); ?>" alt="License Front">
                                                </a>
                                            </div>
                                            <div class="yolo-license-actions">
                                                <a href="<?php echo esc_url($front_license->file_url); ?>" download class="button button-primary">Download</a>
                                                <span class="yolo-upload-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($front_license->uploaded_at)); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <p class="yolo-not-uploaded">Not uploaded yet</p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Back License -->
                                    <div class="yolo-license-image-item">
                                        <h4>Back Side</h4>
                                        <?php if ($has_back): ?>
                                            <div class="yolo-license-preview">
                                                <a href="<?php echo esc_url($back_license->file_url); ?>" target="_blank">
                                                    <img src="<?php echo esc_url($back_license->file_url); ?>" alt="License Back">
                                                </a>
                                            </div>
                                            <div class="yolo-license-actions">
                                                <a href="<?php echo esc_url($back_license->file_url); ?>" download class="button button-primary">Download</a>
                                                <span class="yolo-upload-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($back_license->uploaded_at)); ?></span>
                                            </div>
                                        <?php else: ?>
                                            <p class="yolo-not-uploaded">Not uploaded yet</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
