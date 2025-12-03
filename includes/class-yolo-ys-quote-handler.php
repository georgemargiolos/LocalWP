<?php
/**
 * Quote Request Handler
 */
class YOLO_YS_Quote_Handler {
    
    public function __construct() {
        add_action('wp_ajax_yolo_submit_quote_request', array($this, 'handle_quote_request'));
        add_action('wp_ajax_nopriv_yolo_submit_quote_request', array($this, 'handle_quote_request'));
    }
    
    /**
     * Handle quote request submission
     * Updated to use in-house quote requests system (no email)
     */
    public function handle_quote_request() {
        // Verify nonce for security
        check_ajax_referer('yolo_quote_nonce', 'nonce');
        
        // Get form data
        $yacht_id = sanitize_text_field($_POST['yacht_id']);
        $yacht_name = sanitize_text_field($_POST['yacht_name']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $special_requests = sanitize_textarea_field($_POST['special_requests']);
        $date_from = sanitize_text_field($_POST['date_from']);
        $date_to = sanitize_text_field($_POST['date_to']);
        $num_guests = isset($_POST['num_guests']) ? intval($_POST['num_guests']) : null;
        
        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields.'));
            return;
        }
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
            return;
        }
        
        // Store in database using new quote requests system
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        $data = array(
            'customer_name' => $first_name . ' ' . $last_name,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'yacht_preference' => $yacht_name,
            'checkin_date' => !empty($date_from) ? $date_from : null,
            'checkout_date' => !empty($date_to) ? $date_to : null,
            'num_guests' => $num_guests,
            'special_requests' => $special_requests,
            'status' => 'new'
        );
        
        $result = $wpdb->insert($table_name, $data);
        
        if ($result) {
            $quote_id = $wpdb->insert_id;
            
            // Trigger notifications
            $this->trigger_notifications($quote_id, $data);
            
            wp_send_json_success(array(
                'message' => 'Quote request submitted successfully! We will contact you soon.',
                'quote_id' => $quote_id
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to submit quote request. Please try again.'));
        }
    }
    
    /**
     * Trigger notifications for new quote request
     */
    private function trigger_notifications($quote_id, $data) {
        // Get users who should be notified
        $notify_users = $this->get_notification_users();
        
        foreach ($notify_users as $user_id) {
            // Increment unread count for user
            $current_count = get_user_meta($user_id, 'yolo_unread_quotes', true);
            $new_count = intval($current_count) + 1;
            update_user_meta($user_id, 'yolo_unread_quotes', $new_count);
            
            // Store notification data for browser push
            $notifications = get_user_meta($user_id, 'yolo_pending_notifications', true);
            if (!is_array($notifications)) {
                $notifications = array();
            }
            
            $notifications[] = array(
                'type' => 'quote_request',
                'quote_id' => $quote_id,
                'customer_name' => $data['customer_name'],
                'timestamp' => current_time('timestamp')
            );
            
            update_user_meta($user_id, 'yolo_pending_notifications', $notifications);
        }
    }
    
    /**
     * Get users who should receive notifications
     */
    private function get_notification_users() {
        $users = array();
        
        // Get all administrators
        $admins = get_users(array('role' => 'administrator'));
        foreach ($admins as $admin) {
            $users[] = $admin->ID;
        }
        
        // Get base managers who have notifications enabled
        $base_managers = get_users(array('role' => 'base_manager'));
        foreach ($base_managers as $bm) {
            $notify_enabled = get_user_meta($bm->ID, 'yolo_quote_notifications_enabled', true);
            if ($notify_enabled === '1' || $notify_enabled === true) {
                $users[] = $bm->ID;
            }
        }
        
        return array_unique($users);
    }
    

}

