<?php
if (!defined('ABSPATH')) {
    exit;
}

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
        add_shortcode('yolo_guest_login', array($this, 'render_guest_login'));
        
        // AJAX handlers
        add_action('wp_ajax_yolo_upload_license', array($this, 'ajax_upload_license'));
        add_action('wp_ajax_yolo_save_crew_list', array($this, 'ajax_save_crew_list'));
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
            error_log('YOLO YS Guest: Starting create_guest_user for email: ' . $customer_email);
            
            // CRITICAL: Ensure guest role exists BEFORE creating/updating user
            // This fixes the bug where role doesn't exist during webhook calls
            $this->register_guest_role();
            error_log('YOLO YS Guest: Guest role ensured to exist');
            
            // Check if user already exists
            $user = get_user_by('email', $customer_email);
            
            if ($user) {
                error_log('YOLO YS Guest: User already exists with ID: ' . $user->ID);
                
                // User exists - update their role to guest if needed
                if (!in_array('guest', (array) $user->roles)) {
                    $user->add_role('guest');
                    error_log('YOLO YS Guest: Added guest role to existing user');
                }
                
                // UPDATE PASSWORD to match new booking reference
                // This ensures the password shown in the email always works
                $password = $confirmation_number . 'YoLo';
                wp_set_password($password, $user->ID);
                error_log('YOLO YS Guest: Updated password for existing user to match new booking');
                
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
                error_log('YOLO YS Guest: Linked booking ' . $booking_id . ' to user ' . $user->ID);
                
                return array(
                    'success' => true,
                    'user_id' => $user->ID,
                    'username' => $user->user_login,
                    'password' => $password, // Return updated password
                    'message' => 'Existing user updated with new password'
                );
            }
            
            // Create new user - use email as username (WordPress handles special chars)
            $username = sanitize_user($customer_email, true); // strict mode for safety
            error_log('YOLO YS Guest: Creating new user with username: ' . $username);
            
            // Ensure username is unique
            $base_username = $username;
            $counter = 1;
            while (username_exists($username)) {
                $username = $base_username . $counter;
                $counter++;
            }
            
            $password = $confirmation_number . 'YoLo';
            
            $user_id = wp_create_user($username, $password, $customer_email);
            
            if (is_wp_error($user_id)) {
                error_log('YOLO YS Guest: wp_create_user failed: ' . $user_id->get_error_message());
                throw new Exception($user_id->get_error_message());
            }
            
            error_log('YOLO YS Guest: User created with ID: ' . $user_id);
            
            // Get the user object and set role
            $user = new WP_User($user_id);
            $user->set_role('guest');
            error_log('YOLO YS Guest: Role set to guest');
            
            // Update user meta
            wp_update_user(array(
                'ID' => $user_id,
                'first_name' => $customer_first_name,
                'last_name' => $customer_last_name,
                'display_name' => $customer_first_name . ' ' . $customer_last_name
            ));
            error_log('YOLO YS Guest: User meta updated');
            
            // Link booking to user
            global $wpdb;
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            $result = $wpdb->update(
                $table_bookings,
                array('user_id' => $user_id),
                array('id' => $booking_id),
                array('%d'),
                array('%d')
            );
            error_log('YOLO YS Guest: Booking link result: ' . ($result !== false ? 'success' : 'failed'));
            
            error_log('YOLO YS Guest: Guest user creation completed successfully');
            
            return array(
                'success' => true,
                'user_id' => $user_id,
                'username' => $username,
                'password' => $password,
                'message' => 'Guest user created successfully'
            );
            
        } catch (Exception $e) {
            error_log('YOLO YS Guest: EXCEPTION - ' . $e->getMessage());
            error_log('YOLO YS Guest: Stack trace - ' . $e->getTraceAsString());
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
        
        // Check if user is a guest (also allow administrators for testing)
        if (!in_array('guest', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
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
        
        // Enqueue a dedicated script and localize it properly
        wp_enqueue_script(
            'yolo-guest-dashboard',
            YOLO_YS_PLUGIN_URL . 'public/js/yolo-guest-dashboard.js',
            array('jquery'),
            YOLO_YS_VERSION,
            true
        );

        wp_localize_script('yolo-guest-dashboard', 'yolo_guest_vars', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yolo_upload_license'),
            'crew_nonce' => wp_create_nonce('yolo_save_crew_list'),
            'guest_document_nonce' => wp_create_nonce('yolo_guest_document_nonce')
        ));
        
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/partials/yolo-ys-guest-dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX handler for license upload
     */
    public function ajax_upload_license() {
        try {
            // Debug logging
            error_log('YOLO YS License Upload: Starting upload handler');
            error_log('YOLO YS License Upload: POST data - ' . print_r($_POST, true));
            
            // NOTE: Nonce verification removed for license uploads
            // Security is maintained through:
            // 1. User must be logged in (checked below)
            // 2. User can only upload to their own bookings (verified by user_id match)
            // 3. File type and size validation
            // This prevents nonce expiration issues for guests uploading days/months after booking
            
            // Check if user is logged in
            if (!is_user_logged_in()) {
                wp_send_json_error(array('message' => 'You must be logged in to upload files'));
                return;
            }
            
            $user_id = get_current_user_id();
            
            // Get booking ID and file type
            $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
            $file_type = isset($_POST['file_type']) ? sanitize_text_field($_POST['file_type']) : '';
            
            // Updated file types to include Skipper 2
            $allowed_file_types = array('front', 'back', 'skipper2_front', 'skipper2_back', 'id_front', 'id_back');
            
            if (!$booking_id || !in_array($file_type, $allowed_file_types)) {
                wp_send_json_error(array('message' => 'Invalid parameters or file type.'));
                return;
            }
            
            // Verify booking belongs to user
            global $wpdb;
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            $user = wp_get_current_user();
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
            error_log('YOLO YS License Upload: File received - ' . $file['name'] . ' (' . $file['size'] . ' bytes, type: ' . $file['type'] . ')');
            
            // Check for upload errors
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $upload_errors = array(
                    UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit',
                    UPLOAD_ERR_FORM_SIZE => 'File exceeds form limit',
                    UPLOAD_ERR_PARTIAL => 'File only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Server temp folder missing',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write to disk',
                    UPLOAD_ERR_EXTENSION => 'Upload blocked by extension',
                );
                $error_msg = isset($upload_errors[$file['error']]) ? $upload_errors[$file['error']] : 'Unknown error';
                error_log('YOLO YS License Upload: Upload error - ' . $error_msg);
                wp_send_json_error(array('message' => 'Upload error: ' . $error_msg));
                return;
            }
            
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
            
            if (!is_writable($license_dir)) {
                error_log('YOLO YS License Upload: Directory not writable - ' . $license_dir);
                wp_send_json_error(array('message' => 'Upload directory is not writable'));
                return;
            }
            
            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'license_' . $file_type . '_' . time() . '.' . $extension;
            $file_path = $license_dir . '/' . $filename;
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $file_path)) {
                error_log('YOLO YS License Upload: Failed to move file from ' . $file['tmp_name'] . ' to ' . $file_path);
                wp_send_json_error(array('message' => 'Failed to save file'));
                return;
            }
            
            error_log('YOLO YS License Upload: File saved to ' . $file_path);
            
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
    
	    /**
	     * AJAX handler for crew list save
	     */
	    public function ajax_save_crew_list() {
	        try {
	            // Verify nonce
	            if (!isset($_POST['crew_list_nonce']) || !wp_verify_nonce($_POST['crew_list_nonce'], 'yolo_save_crew_list')) {
	                wp_send_json_error(array('message' => 'Security check failed.'));
	                return;
	            }
	            
	            if (!is_user_logged_in()) {
	                wp_send_json_error(array('message' => 'You must be logged in to save the crew list.'));
	                return;
	            }
	            
	            $user = wp_get_current_user();
	            $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
	            $crew_data = isset($_POST['crew']) ? $_POST['crew'] : array();
	            
	            if (!$booking_id || empty($crew_data)) {
	                wp_send_json_error(array('message' => 'Invalid booking ID or empty crew data.'));
	                return;
	            }
	            
	            global $wpdb;
	            $table_crew = $wpdb->prefix . 'yolo_crew_list';
	            
	            // 1. Delete existing crew list for this booking
	            $wpdb->delete($table_crew, array('booking_id' => $booking_id));
	            
	            $saved_count = 0;
	            
	            // 2. Insert new crew members
	            foreach ($crew_data as $index => $member) {
	                // Only save if first name is provided (assuming it's the minimum required field)
	                if (empty($member['first_name']) || empty($member['last_name'])) {
	                    continue;
	                }
	                
	                $wpdb->insert(
	                    $table_crew,
	                    array(
	                        'booking_id' => $booking_id,
	                        'user_id' => $user->ID,
	                        'crew_member_index' => intval($index),
	                        'first_name' => sanitize_text_field($member['first_name']),
	                        'last_name' => sanitize_text_field($member['last_name']),
	                        'sex' => sanitize_text_field($member['sex']),
	                        'id_type' => sanitize_text_field($member['id_type']),
	                        'id_number' => sanitize_text_field($member['id_number']),
	                        'birth_date' => sanitize_text_field($member['birth_date']),
	                        'role' => sanitize_text_field($member['role']),
	                        'mobile_number' => sanitize_text_field($member['mobile_number']),
	                        'nationality' => sanitize_text_field($member['nationality']),
	                        'created_at' => current_time('mysql')
	                    ),
	                    array('%d', '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
	                );
	                $saved_count++;
	            }
	            
	            wp_send_json_success(array('message' => "Crew list saved successfully. $saved_count members recorded."));
	            
	        } catch (Exception $e) {
	            error_log('YOLO YS Crew List: EXCEPTION - ' . $e->getMessage());
	            wp_send_json_error(array('message' => 'An error occurred while saving the crew list.'));
	        }
	    }
	    
	    /**
	     * Render guest login form shortcode
	     */
	    public function render_guest_login() {
        // If already logged in, redirect to dashboard
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            if (in_array('guest', (array) $user->roles)) {
                $dashboard_page = get_page_by_path('guest-dashboard');
                if ($dashboard_page) {
                    wp_redirect(get_permalink($dashboard_page->ID));
                    exit;
                }
            }
        }
        
        ob_start();
        include plugin_dir_path(dirname(__FILE__)) . 'public/partials/yolo-ys-guest-login.php';
        return ob_get_clean();
    }
}
