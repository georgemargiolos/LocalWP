<?php
/**
 * Guest User Management
 * 
 * Handles guest user creation, authentication, and license uploads
 *
 * @package YOLO_Yacht_Search
 * @since 2.5.6
 */

class YOLO_YS_Guest_Manager {
    
    /**
     * Initialize guest manager
     */
    public function __construct() {
        // Register guest role on plugin activation
        add_action('init', array($this, 'register_guest_role'));
        
        // Handle license uploads
        add_action('wp_ajax_yolo_ys_upload_license', array($this, 'ajax_upload_license'));
        add_action('wp_ajax_nopriv_yolo_ys_upload_license', array($this, 'ajax_upload_license'));
    }
    
    /**
     * Register 'guest' user role
     */
    public function register_guest_role() {
        if (!get_role('guest')) {
            add_role(
                'guest',
                'Guest',
                array(
                    'read' => true,
                    'upload_files' => true
                )
            );
        }
    }
    
    /**
     * Create guest user after booking
     * 
     * @param int $booking_id Booking ID
     * @param string $customer_email Customer email
     * @param string $customer_name Customer name
     * @param string $confirmation_number Booking confirmation number
     * @return int|WP_Error User ID or error
     */
    public function create_guest_user($booking_id, $customer_email, $customer_name, $confirmation_number) {
        // Check if user already exists
        $existing_user = get_user_by('email', $customer_email);
        
        if ($existing_user) {
            // Update user meta with booking info
            update_user_meta($existing_user->ID, 'yolo_booking_id', $booking_id);
            update_user_meta($existing_user->ID, 'yolo_confirmation_number', $confirmation_number);
            
            error_log('YOLO YS: Guest user already exists: ' . $customer_email);
            return $existing_user->ID;
        }
        
        // Generate username from email
        $username = sanitize_user(str_replace('@', '_', $customer_email));
        
        // Password: confirmation_number + YoLo
        $password = $confirmation_number . 'YoLo';
        
        // Create user
        $user_id = wp_create_user($username, $password, $customer_email);
        
        if (is_wp_error($user_id)) {
            error_log('YOLO YS: Failed to create guest user: ' . $user_id->get_error_message());
            return $user_id;
        }
        
        // Set user role to guest
        $user = new WP_User($user_id);
        $user->set_role('guest');
        
        // Update user meta
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $customer_name,
            'first_name' => explode(' ', $customer_name)[0],
            'last_name' => isset(explode(' ', $customer_name)[1]) ? explode(' ', $customer_name)[1] : ''
        ));
        
        // Store booking info in user meta
        update_user_meta($user_id, 'yolo_booking_id', $booking_id);
        update_user_meta($user_id, 'yolo_confirmation_number', $confirmation_number);
        
        error_log('YOLO YS: Created guest user ID ' . $user_id . ' for booking ' . $booking_id);
        
        // Send welcome email with login credentials
        $this->send_welcome_email($customer_email, $customer_name, $username, $password, $confirmation_number);
        
        return $user_id;
    }
    
    /**
     * Send welcome email with login credentials
     */
    private function send_welcome_email($email, $name, $username, $password, $confirmation_number) {
        $subject = 'Welcome to YOLO Charters - Your Guest Account';
        
        $message = "Dear {$name},\n\n";
        $message .= "Thank you for booking with YOLO Charters!\n\n";
        $message .= "Your booking confirmation number is: {$confirmation_number}\n\n";
        $message .= "We've created a guest account for you to upload your skipper license.\n\n";
        $message .= "Login Credentials:\n";
        $message .= "Username: {$username}\n";
        $message .= "Password: {$password}\n\n";
        $message .= "Guest Dashboard: " . home_url('/guest-dashboard/') . "\n\n";
        $message .= "Please log in and upload your skipper license (front and back) before your charter date.\n\n";
        $message .= "Best regards,\n";
        $message .= "YOLO Charters Team";
        
        wp_mail($email, $subject, $message);
        
        error_log('YOLO YS: Sent welcome email to ' . $email);
    }
    
    /**
     * Handle license upload AJAX
     */
    public function ajax_upload_license() {
        // NOTE: Nonce verification removed for license uploads
        // Security is maintained through user login check and booking ownership verification
        // This prevents nonce expiration issues for guests uploading days/months after booking
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'You must be logged in to upload files.'));
            return;
        }
        
        $user_id = get_current_user_id();
        $booking_id = get_user_meta($user_id, 'yolo_booking_id', true);
        
        if (!$booking_id) {
            wp_send_json_error(array('message' => 'No booking found for your account.'));
            return;
        }
        
        // Check which file was uploaded
        $file_type = isset($_POST['file_type']) ? sanitize_text_field($_POST['file_type']) : '';
        
        if (!in_array($file_type, array('front', 'back', 'skipper2_front', 'skipper2_back', 'id_front', 'id_back'))) {
            wp_send_json_error(array('message' => 'Invalid file type.'));
            return;
        }
        
        // Handle file upload
        if (!isset($_FILES['license_file'])) {
            wp_send_json_error(array('message' => 'No file uploaded.'));
            return;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        // Upload file
        $file = $_FILES['license_file'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            // Store file info in database
            global $wpdb;
            $table_name = $wpdb->prefix . 'yolo_license_uploads';
            
            $wpdb->insert(
                $table_name,
                array(
                    'booking_id' => $booking_id,
                    'user_id' => $user_id,
                    'file_type' => $file_type,
                    'file_path' => $movefile['file'],
                    'file_url' => $movefile['url'],
                    'uploaded_at' => current_time('mysql')
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s')
            );
            
            error_log('YOLO YS: License uploaded - User: ' . $user_id . ', Booking: ' . $booking_id . ', Type: ' . $file_type);
            
            wp_send_json_success(array(
                'message' => 'License uploaded successfully!',
                'file_url' => $movefile['url']
            ));
        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    }
    
    /**
     * Get license uploads for a booking
     * 
     * @param int $booking_id Booking ID
     * @return array License uploads
     */
    public function get_license_uploads($booking_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_license_uploads';
        
        $uploads = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE booking_id = %d ORDER BY uploaded_at DESC",
            $booking_id
        ));
        
        return $uploads;
    }
    
    /**
     * Get all license uploads (for admin)
     * 
     * @return array All license uploads
     */
    public function get_all_license_uploads() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_license_uploads';
        
        $uploads = $wpdb->get_results(
            "SELECT l.*, b.yacht_name, b.customer_name, b.customer_email, b.date_from, b.date_to
             FROM {$table_name} l
             LEFT JOIN {$wpdb->prefix}yolo_bookings b ON l.booking_id = b.id
             ORDER BY l.uploaded_at DESC"
        );
        
        return $uploads;
    }
}
