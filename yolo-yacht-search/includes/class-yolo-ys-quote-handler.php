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
     */
    public function handle_quote_request() {
        // Verify nonce (if you want to add security)
        // check_ajax_referer('yolo_quote_nonce', 'nonce');
        
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
        
        // Prepare email content
        $admin_email = get_option('admin_email');
        $subject = 'New Quote Request for ' . $yacht_name;
        
        $message = "New quote request received:\n\n";
        $message .= "Yacht: " . $yacht_name . " (ID: " . $yacht_id . ")\n";
        $message .= "Name: " . $first_name . " " . $last_name . "\n";
        $message .= "Email: " . $email . "\n";
        $message .= "Phone: " . $phone . "\n";
        
        if (!empty($date_from) && !empty($date_to)) {
            $message .= "Dates: " . $date_from . " to " . $date_to . "\n";
        }
        
        if (!empty($special_requests)) {
            $message .= "\nSpecial Requests:\n" . $special_requests . "\n";
        }
        
        $message .= "\n---\nSent from YOLO Yacht Search Plugin";
        
        // Send email
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $sent = wp_mail($admin_email, $subject, $message, $headers);
        
        if ($sent) {
            // Store in database (optional)
            $this->store_quote_request(array(
                'yacht_id' => $yacht_id,
                'yacht_name' => $yacht_name,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'email' => $email,
                'phone' => $phone,
                'special_requests' => $special_requests,
                'date_from' => $date_from,
                'date_to' => $date_to
            ));
            
            wp_send_json_success(array('message' => 'Quote request sent successfully!'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send quote request. Please try again.'));
        }
    }
    
    /**
     * Store quote request in database
     */
    private function store_quote_request($data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        // Create table if not exists
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id varchar(255) NOT NULL,
            yacht_name varchar(255) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(50) NOT NULL,
            special_requests text,
            date_from date DEFAULT NULL,
            date_to date DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id),
            KEY email (email)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Insert data
        $wpdb->insert(
            $table_name,
            array(
                'yacht_id' => $data['yacht_id'],
                'yacht_name' => $data['yacht_name'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'special_requests' => $data['special_requests'],
                'date_from' => !empty($data['date_from']) ? $data['date_from'] : null,
                'date_to' => !empty($data['date_to']) ? $data['date_to'] : null
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
}

// Initialize quote handler
new YOLO_YS_Quote_Handler();
