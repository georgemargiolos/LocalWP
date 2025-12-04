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
	        $table_crew = $wpdb->prefix . 'yolo_crew_list';
	        
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
	        
	        $crew_lists = $wpdb->get_results("
	            SELECT * FROM {$table_crew} ORDER BY booking_id, crew_member_index ASC
	        ");
	        
	        // Group crew lists by booking
	        $grouped_crew = array();
	        foreach ($crew_lists as $crew_member) {
	            $grouped_crew[$crew_member->booking_id][] = $crew_member;
	        }
	        
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
	                    'licenses' => array(),
	                    'crew_list' => isset($grouped_crew[$license->booking_id]) ? $grouped_crew[$license->booking_id] : array()
	                );
	            }
	            $grouped_licenses[$license->booking_id]['licenses'][] = $license;
	        }
	        
	        // Merge bookings that only have crew list data (no licenses yet)
	        foreach ($grouped_crew as $booking_id => $crew_list) {
	            if (!isset($grouped_licenses[$booking_id])) {
	                // Fetch booking info for crew-only bookings
	                $booking_info = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_bookings} WHERE id = %d", $booking_id));
	                
	                // CRITICAL FIX: Check if booking info exists before proceeding
	                if (!$booking_info) {
	                    // If the booking is deleted or not found, skip this crew list entry
	                    continue;
	                }
	                
	                $user_info = $wpdb->get_row($wpdb->prepare("SELECT display_name FROM {$wpdb->users} WHERE ID = %d", $crew_list[0]->user_id));
	                
	                $grouped_licenses[$booking_id] = array(
	                    'booking_info' => array(
	                        'id' => $booking_info->id,
	                        'yacht_name' => $booking_info->yacht_name,
	                        'customer_name' => $booking_info->customer_name,
	                        'customer_email' => $booking_info->customer_email,
	                        'user_display_name' => $user_info->display_name,
	                        'date_from' => $booking_info->date_from,
	                        'date_to' => $booking_info->date_to
	                    ),
	                    'licenses' => array(),
	                    'crew_list' => $crew_list
	                );
	            }
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
                        
	                        $has_s1_front = false;
	                        $has_s1_back = false;
	                        $s1_front_license = null;
	                        $s1_back_license = null;
	                        
	                        $has_s2_front = false;
	                        $has_s2_back = false;
	                        $s2_front_license = null;
	                        $s2_back_license = null;
	                        
	                        foreach ($booking_licenses as $license) {
	                            if ($license->file_type === 'front') {
	                                $has_s1_front = true;
	                                $s1_front_license = $license;
	                            }
	                            if ($license->file_type === 'back') {
	                                $has_s1_back = true;
	                                $s1_back_license = $license;
	                            }
	                            if ($license->file_type === 'skipper2_front') {
	                                $has_s2_front = true;
	                                $s2_front_license = $license;
	                            }
	                            if ($license->file_type === 'skipper2_back') {
	                                $has_s2_back = true;
	                                $s2_back_license = $license;
	                            }
	                        }
	                        
	                        $is_s1_complete = $has_s1_front && $has_s1_back;
	                        $is_s2_complete = $has_s2_front && $has_s2_back;
	                        $is_complete = $is_s1_complete && $is_s2_complete; // Assuming both are required for full completion check
	                        
	                        $crew_list = $data['crew_list'];
	                        $has_crew_list = !empty($crew_list);
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
	                                    <?php if ($is_s1_complete): ?>
	                                        <span class="yolo-status-badge complete">✓ Skipper 1 Complete</span>
	                                    <?php else: ?>
	                                        <span class="yolo-status-badge incomplete">⚠ Skipper 1 Incomplete</span>
	                                    <?php endif; ?>
	                                    <?php if ($has_crew_list): ?>
	                                        <span class="yolo-status-badge complete">✓ Crew List Saved</span>
	                                    <?php else: ?>
	                                        <span class="yolo-status-badge incomplete">⚠ Crew List Missing</span>
	                                    <?php endif; ?>
	                                </div>
	                            </div>
	                            
	                            <div class="yolo-license-card-body">
	                                <div class="yolo-customer-info">
	                                    <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($booking['customer_email']); ?>"><?php echo esc_html($booking['customer_email']); ?></a></p>
	                                    <p><strong>Booking ID:</strong> #<?php echo esc_html($booking['id']); ?></p>
	                                </div>
	                                
	                                <!-- Crew List Display -->
	                                <div class="yolo-crew-list-admin">
	                                    <h4>Crew List (<?php echo count($crew_list); ?> members)</h4>
	                                    <?php if ($has_crew_list): ?>
	                                        <table class="yolo-crew-table-admin">
	                                            <thead>
	                                                <tr>
	                                                    <th>#</th>
	                                                    <th>Name</th>
	                                                    <th>Sex</th>
	                                                    <th>ID Type</th>
	                                                    <th>ID No.</th>
	                                                    <th>Birth Date</th>
	                                                    <th>Role</th>
	                                                    <th>Mobile</th>
	                                                    <th>Nationality</th>
	                                                </tr>
	                                            </thead>
	                                            <tbody>
	                                                <?php $count = 1; foreach ($crew_list as $member): ?>
	                                                    <tr>
	                                                        <td><?php echo $count++; ?></td>
	                                                        <td><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></td>
	                                                        <td><?php echo esc_html(ucfirst($member->sex)); ?></td>
	                                                        <td><?php echo esc_html(ucwords(str_replace('_', ' ', $member->id_type))); ?></td>
	                                                        <td><?php echo esc_html($member->id_number); ?></td>
	                                                        <td><?php echo esc_html($member->birth_date); ?></td>
	                                                        <td><?php echo esc_html(ucfirst($member->role)); ?></td>
	                                                        <td><?php echo esc_html($member->mobile_number); ?></td>
	                                                        <td><?php echo esc_html($member->nationality); ?></td>
	                                                    </tr>
	                                                <?php endforeach; ?>
	                                            </tbody>
	                                        </table>
	                                    <?php else: ?>
	                                        <p class="yolo-not-uploaded">No crew list submitted yet.</p>
	                                    <?php endif; ?>
	                                </div>
	                                
	                                <div class="yolo-license-images">
	                                    <!-- Skipper 1 Licenses -->
	                                    <div class="yolo-license-group">
	                                        <h4>Skipper 1 License</h4>
	                                        <div class="yolo-license-image-item">
	                                            <h5>Front Side</h5>
	                                            <?php if ($has_s1_front): ?>
	                                                <div class="yolo-license-preview">
	                                                    <a href="<?php echo esc_url($s1_front_license->file_url); ?>" target="_blank">
	                                                        <img src="<?php echo esc_url($s1_front_license->file_url); ?>" alt="Skipper 1 License Front">
	                                                    </a>
	                                                </div>
	                                                <div class="yolo-license-actions">
	                                                    <a href="<?php echo esc_url($s1_front_license->file_url); ?>" download class="button button-primary">Download</a>
	                                                    <span class="yolo-upload-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s1_front_license->uploaded_at)); ?></span>
	                                                </div>
	                                            <?php else: ?>
	                                                <p class="yolo-not-uploaded">Not uploaded yet</p>
	                                            <?php endif; ?>
	                                        </div>
	                                        
	                                        <div class="yolo-license-image-item">
	                                            <h5>Back Side</h5>
	                                            <?php if ($has_s1_back): ?>
	                                                <div class="yolo-license-preview">
	                                                    <a href="<?php echo esc_url($s1_back_license->file_url); ?>" target="_blank">
	                                                        <img src="<?php echo esc_url($s1_back_license->file_url); ?>" alt="Skipper 1 License Back">
	                                                    </a>
	                                                </div>
	                                                <div class="yolo-license-actions">
	                                                    <a href="<?php echo esc_url($s1_back_license->file_url); ?>" download class="button button-primary">Download</a>
	                                                    <span class="yolo-upload-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s1_back_license->uploaded_at)); ?></span>
	                                                </div>
	                                            <?php else: ?>
	                                                <p class="yolo-not-uploaded">Not uploaded yet</p>
	                                            <?php endif; ?>
	                                        </div>
	                                    </div>
	                                    
	                                    <!-- Skipper 2 Licenses -->
	                                    <div class="yolo-license-group">
	                                        <h4>Skipper 2 License (Optional)</h4>
	                                        <div class="yolo-license-image-item">
	                                            <h5>Front Side</h5>
	                                            <?php if ($has_s2_front): ?>
	                                                <div class="yolo-license-preview">
	                                                    <a href="<?php echo esc_url($s2_front_license->file_url); ?>" target="_blank">
	                                                        <img src="<?php echo esc_url($s2_front_license->file_url); ?>" alt="Skipper 2 License Front">
	                                                    </a>
	                                                </div>
	                                                <div class="yolo-license-actions">
	                                                    <a href="<?php echo esc_url($s2_front_license->file_url); ?>" download class="button button-primary">Download</a>
	                                                    <span class="yolo-upload-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s2_front_license->uploaded_at)); ?></span>
	                                                </div>
	                                            <?php else: ?>
	                                                <p class="yolo-not-uploaded">Not uploaded yet</p>
	                                            <?php endif; ?>
	                                        </div>
	                                        
	                                        <div class="yolo-license-image-item">
	                                            <h5>Back Side</h5>
	                                            <?php if ($has_s2_back): ?>
	                                                <div class="yolo-license-preview">
	                                                    <a href="<?php echo esc_url($s2_back_license->file_url); ?>" target="_blank">
	                                                        <img src="<?php echo esc_url($s2_back_license->file_url); ?>" alt="Skipper 2 License Back">
	                                                    </a>
	                                                </div>
	                                                <div class="yolo-license-actions">
	                                                    <a href="<?php echo esc_url($s2_back_license->file_url); ?>" download class="button button-primary">Download</a>
	                                                    <span class="yolo-upload-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s2_back_license->uploaded_at)); ?></span>
	                                                </div>
	                                            <?php else: ?>
	                                                <p class="yolo-not-uploaded">Not uploaded yet</p>
	                                            <?php endif; ?>
	                                        </div>
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
