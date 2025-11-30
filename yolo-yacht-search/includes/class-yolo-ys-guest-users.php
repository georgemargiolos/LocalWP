<?php
/**
 * Guest User Management
 * Handles guest user creation, login redirects, and dashboard functionality
 */
class YOLO_YS_Guest_Users {
    
    public function __construct() {
        // Register custom user role
        add_action('init', array($this, 'register_guest_role'));
        
        // Login redirect for guest users
        add_filter('login_redirect', array($this, 'guest_login_redirect'), 10, 3);
        
        // Prevent guests from accessing wp-admin
        add_action('admin_init', array($this, 'prevent_guest_admin_access'));
        
        // Register shortcodes
        add_shortcode('yolo_guest_dashboard', array($this, 'render_guest_dashboard'));
        
        // AJAX handlers
        add_action('wp_ajax_yolo_upload_license', array($this, 'ajax_upload_license'));
    }
    
    /**
     * Register custom 'guest' user role
     */
    public function register_guest_role() {
        if (!get_role('guest')) {
            add_role('guest', 'Guest', array(
                'read' => true, // Can view content
                'level_0' => true
            ));
        }
    }
    
    /**
     * Redirect guest users to dashboard after login
     */
    public function guest_login_redirect($redirect_to, $request, $user) {
        if (isset($user->roles) && is_array($user->roles)) {
            if (in_array('guest', $user->roles)) {
                // Get the guest dashboard page URL
                $dashboard_page = get_page_by_path('guest-dashboard');
                if ($dashboard_page) {
                    return get_permalink($dashboard_page->ID);
                }
                // Fallback to home page if dashboard page doesn't exist
                return home_url();
            }
        }
        return $redirect_to;
    }
    
    /**
     * Prevent guest users from accessing wp-admin
     */
    public function prevent_guest_admin_access() {
        $user = wp_get_current_user();
        if (in_array('guest', (array) $user->roles)) {
            wp_redirect(home_url());
            exit;
        }
    }
    
    /**
     * Create guest user after successful booking
     * 
     * @param int $booking_id Booking ID
     * @param string $customer_email Customer email
     * @param string $customer_first_name Customer first name
     * @param string $customer_last_name Customer last name
     * @param string $confirmation_number Booking confirmation number
     * @return array Result with success status and user data
     */
    public function create_guest_user($booking_id, $customer_email, $customer_first_name, $customer_last_name, $confirmation_number) {
        try {
            // Check if user already exists
            $user = get_user_by('email', $customer_email);
            
            if ($user) {
                // User exists - update their role to guest if needed
                if (!in_array('guest', (array) $user->roles)) {
                    $user->add_role('guest');
                }
                
                // Link booking to existing user
                global $wpdb;
                $table_bookings = $wpdb->prefix . 'yolo_bookings';
                $wpdb->update(
                    $table_bookings,
                    array('user_id' => $user->ID),
                    array('id' => $booking_id),
                    array('%d'),
                    array('%d')
                );
                
                return array(
                    'success' => true,
                    'user_id' => $user->ID,
                    'username' => $user->user_login,
                    'password' => null, // Don't change existing password
                    'message' => 'Existing user updated'
                );
            }
            
            // Create new user
            $username = sanitize_user($customer_email);
            $password = $confirmation_number . 'YoLo';
            
            $user_id = wp_create_user($username, $password, $customer_email);
            
            if (is_wp_error($user_id)) {
                throw new Exception($user_id->get_error_message());
            }
            
            // Update user meta
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $customer_first_name,
                'last_name' => $customer_last_name,
                'display_name' => $customer_first_name . ' ' . $customer_last_name,
                'role' => 'guest'
            ));
            
            // Link booking to user
            global $wpdb;
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            $wpdb->update(
                $table_bookings,
                array('user_id' => $user_id),
                array('id' => $booking_id),
                array('%d'),
                array('%d')
            );
            
            return array(
                'success' => true,
                'user_id' => $user_id,
                'username' => $username,
                'password' => $password,
                'message' => 'Guest user created successfully'
            );
            
        } catch (Exception $e) {
            error_log('YOLO YS: Failed to create guest user: ' . $e->getMessage());
            return array(
                'success' => false,
                'message' => $e->getMessage()
            );
        }
    }
    
    /**
     * Render guest dashboard shortcode
     */
    public function render_guest_dashboard() {
        if (!is_user_logged_in()) {
            return '<div class="yolo-guest-notice">
                <p>Please <a href="' . wp_login_url(get_permalink()) . '">log in</a> to view your dashboard.</p>
            </div>';
        }
        
        $user = wp_get_current_user();
        
        // Check if user is a guest
        if (!in_array('guest', (array) $user->roles)) {
            return '<div class="yolo-guest-notice">
                <p>Access denied. This page is only for guests.</p>
            </div>';
        }
        
        // Get user's bookings
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_bookings} WHERE user_id = %d OR customer_email = %s ORDER BY created_at DESC",
            $user->ID,
            $user->user_email
        ));
        
        // Get license uploads
        $table_licenses = $wpdb->prefix . 'yolo_license_uploads';
        $licenses = array();
        foreach ($bookings as $booking) {
            $booking_licenses = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_licenses} WHERE booking_id = %d ORDER BY uploaded_at DESC",
                $booking->id
            ));
            $licenses[$booking->id] = $booking_licenses;
        }
        
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/partials/yolo-ys-guest-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for license upload
     */
    public function ajax_upload_license() {
        try {
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_upload_license')) {
                wp_send_json_error(array('message' => 'Security check failed'));
                return;
            }
            
            // Check if user is logged in
            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => 'You must be logged in to upload files'));
                return;
            }
            
            $user = wp_get_current_user();
            
            // Check if user is a guest
            if (!in_array('guest', (array) $user->roles)) {
                wp_send_json_error(array('message' => 'Access denied'));
                return;
            }
            
            // Get booking ID and file type
            $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
            $file_type = isset($_POST['file_type']) ? sanitize_text_field($_POST['file_type']) : '';
            
            if (!$booking_id || !in_array($file_type, array('front', 'back'))) {
                wp_send_json_error(array('message' => 'Invalid parameters'));
                return;
            }
            
            // Verify booking belongs to user
            global $wpdb;
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            $booking = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_bookings} WHERE id = %d AND (user_id = %d OR customer_email = %s)",
                $booking_id,
                $user->ID,
                $user->user_email
            ));
            
            if (!$booking) {
                wp_send_json_error(array('message' => 'Booking not found or access denied'));
                return;
            }
            
            // Handle file upload
            if (!isset($_FILES['license_file'])) {
                wp_send_json_error(array('message' => 'No file uploaded'));
                return;
            }
            
            $file = $_FILES['license_file'];
            
            // Validate file type
            $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            if (!in_array($file['type'], $allowed_types)) {
                wp_send_json_error(array('message' => 'Invalid file type. Only JPG, PNG, and GIF are allowed.'));
                return;
            }
            
            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                wp_send_json_error(array('message' => 'File too large. Maximum size is 5MB.'));
                return;
            }
            
            // Create upload directory
            $upload_dir = wp_upload_dir();
            $license_dir = $upload_dir['basedir'] . '/yolo-licenses/' . $booking_id;
            
            if (!file_exists($license_dir)) {
                wp_mkdir_p($license_dir);
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'license_' . $file_type . '_' . time() . '.' . $extension;
            $file_path = $license_dir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                wp_send_json_error(array('message' => 'Failed to save file'));
                return;
            }
            
            // Save to database
            $table_licenses = $wpdb->prefix . 'yolo_license_uploads';
            $file_url = $upload_dir['baseurl'] . '/yolo-licenses/' . $booking_id . '/' . $filename;
            
            $wpdb->insert(
                $table_licenses,
                array(
                    'booking_id' => $booking_id,
                    'user_id' => $user->ID,
                    'file_type' => $file_type,
                    'file_path' => $file_path,
                    'file_url' => $file_url,
                    'uploaded_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s')
            );
            
            wp_send_json_success(array(
                'message' => 'License uploaded successfully',
                'file_url' => $file_url,
                'file_type' => $file_type
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
}
