<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stripe AJAX and REST API Handlers
 */
class YOLO_YS_Stripe_Handlers {
    
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_yolo_create_checkout_session', array($this, 'ajax_create_checkout_session'));
        add_action('wp_ajax_nopriv_yolo_create_checkout_session', array($this, 'ajax_create_checkout_session'));
        
        add_action('wp_ajax_yolo_create_balance_checkout', array($this, 'ajax_create_balance_checkout'));
        add_action('wp_ajax_nopriv_yolo_create_balance_checkout', array($this, 'ajax_create_balance_checkout'));
        
        add_action('wp_ajax_yolo_get_live_price', array($this, 'ajax_get_live_price'));
        add_action('wp_ajax_nopriv_yolo_get_live_price', array($this, 'ajax_get_live_price'));
        
        add_action('wp_ajax_yolo_submit_custom_quote', array($this, 'ajax_submit_custom_quote'));
        add_action('wp_ajax_nopriv_yolo_submit_custom_quote', array($this, 'ajax_submit_custom_quote'));
        
        // AJAX handler for checking booking status (used by loading page)
        add_action('wp_ajax_yolo_check_booking_status', array($this, 'ajax_check_booking_status'));
        add_action('wp_ajax_nopriv_yolo_check_booking_status', array($this, 'ajax_check_booking_status'));
        
        // Register REST API endpoint for webhook
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
    }
    
    /**
     * AJAX handler to check if booking exists for a Stripe session
     */
    public function ajax_check_booking_status() {
        $session_id = isset($_POST['session_id']) ? sanitize_text_field($_POST['session_id']) : '';
        
        if (empty($session_id)) {
            wp_send_json_error(array('message' => 'No session ID provided'));
            return;
        }
        
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_bookings} WHERE stripe_session_id = %s",
            $session_id
        ));
        
        if ($booking) {
            wp_send_json_success(array('found' => true, 'booking_id' => $booking->id));
        } else {
            wp_send_json_success(array('found' => false));
        }
    }
    
    /**
     * AJAX handler to create Stripe Checkout Session
     */
    public function ajax_create_checkout_session() {
        try {
            // Verify nonce for security
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_ys_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
                return;
            }
            
            // Get POST data
            // CRITICAL FIX v2.5.3: yacht_id MUST be STRING, not integer
            // Large yacht IDs (e.g., 7136018700000107850) exceed PHP_INT_MAX on 32-bit systems
            // and lose precision in JavaScript Number type (max safe integer is 2^53)
            // Using intval() corrupts the ID, causing API lookups to fail
            $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
            $yacht_name = isset($_POST['yacht_name']) ? sanitize_text_field($_POST['yacht_name']) : '';
            $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
            $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0;
            $currency = isset($_POST['currency']) ? sanitize_text_field($_POST['currency']) : 'EUR';
            
            // Get customer information
            $customer_first_name = isset($_POST['customer_first_name']) ? sanitize_text_field($_POST['customer_first_name']) : '';
            $customer_last_name = isset($_POST['customer_last_name']) ? sanitize_text_field($_POST['customer_last_name']) : '';
            $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
            $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
            
            // Get extras information
            $included_extras = isset($_POST['included_extras']) ? floatval($_POST['included_extras']) : 0;
            $extras_at_base = isset($_POST['extras_at_base']) ? floatval($_POST['extras_at_base']) : 0;
            $extras_details = isset($_POST['extras_details']) ? sanitize_text_field($_POST['extras_details']) : '[]';
            
            // Validate inputs
            if (empty($yacht_id) || empty($date_from) || empty($date_to) || $total_price <= 0) {
                wp_send_json_error(array(
                    'message' => 'Missing required booking information'
                ));
                return;
            }
            
            // Validate customer information
            if (empty($customer_first_name) || empty($customer_last_name) || empty($customer_email) || empty($customer_phone)) {
                wp_send_json_error(array(
                    'message' => 'Missing required customer information'
                ));
                return;
            }
            
            // Create Stripe Checkout Session
            $stripe = new YOLO_YS_Stripe();
            $result = $stripe->create_checkout_session(
                $yacht_id,
                $yacht_name,
                $date_from,
                $date_to,
                $total_price,
                $currency,
                $customer_first_name,
                $customer_last_name,
                $customer_email,
                $customer_phone,
                $included_extras,
                $extras_at_base,
                $extras_details
            );
            
            if ($result['success']) {
                wp_send_json_success($result);
            } else {
                wp_send_json_error($result);
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX handler to get live price from Booking Manager
     */
    public function ajax_get_live_price() {
        try {
            // Note: No nonce check - this is public price checking functionality
            // Get yacht ID and dates        // CRITICAL FIX v2.5.3: yacht_id MUST be STRING, not integer
            // Large yacht IDs (e.g., 7136018700000107850) exceed PHP_INT_MAX on 32-bit systems
            // and lose precision in JavaScript Number type (max safe integer is 2^53)
            // Using intval() corrupts the ID, causing API lookups to fail
            $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
            $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
            
            
            if (empty($yacht_id) || empty($date_from) || empty($date_to)) {
                wp_send_json_error(array(
                    'message' => 'Missing required parameters'
                ));
                return;
            }
            
            // Get live price from Booking Manager API
            $api = new YOLO_YS_Booking_Manager_API();
            $result = $api->get_live_price($yacht_id, $date_from, $date_to);
            
            if ($result['success'] && $result['available']) {
                wp_send_json_success(array(
                    'available' => true,
                    'price' => $result['price'],
                    'discount' => $result['discount'],
                    'final_price' => $result['final_price'],
                    'base_price' => isset($result['base_price']) ? $result['base_price'] : $result['final_price'],
                    'included_extras' => isset($result['included_extras']) ? $result['included_extras'] : 0,
                    'extras_at_base' => isset($result['extras_at_base']) ? $result['extras_at_base'] : 0,
                    'extras_details' => isset($result['extras_details']) ? $result['extras_details'] : array(),
                    'currency' => $result['currency'],
                ));
            } else if ($result['success'] && !$result['available']) {
                wp_send_json_error(array(
                    'available' => false,
                    'message' => 'Another customer just booked this yacht for these dates. Please select another yacht or check out other available dates.'
                ));
            } else {
                wp_send_json_error(array(
                    'message' => isset($result['error']) ? $result['error'] : 'Failed to check availability'
                ));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX handler to submit custom quote request
     */
    public function ajax_submit_custom_quote() {
        try {
            // Note: No nonce check - this is a public contact form
            // Get form data
            $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
            $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
            // CRITICAL FIX v2.5.3: yacht_id MUST be STRING, not integer
            // Large yacht IDs (e.g., 7136018700000107850) exceed PHP_INT_MAX on 32-bit systems
            // and lose precision in JavaScript Number type (max safe integer is 2^53)
            // Using intval() corrupts the ID, causing API lookups to fail
            $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
            $yacht_name = isset($_POST['yacht_name']) ? sanitize_text_field($_POST['yacht_name']) : '';
            $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
            
            // Validate required fields
            if (empty($name) || empty($email)) {
                wp_send_json_error(array(
                    'message' => 'Name and email are required'
                ));
                return;
            }
            
            // Send email to admin
            $admin_email = get_option('admin_email');
            $subject = 'Custom Charter Quote Request - ' . $yacht_name;
            $body = "New custom charter quote request:\n\n";
            $body .= "Yacht: " . $yacht_name . "\n";
            $body .= "Dates: " . $date_from . " to " . $date_to . "\n\n";
            $body .= "Customer Details:\n";
            $body .= "Name: " . $name . "\n";
            $body .= "Email: " . $email . "\n";
            $body .= "Phone: " . $phone . "\n\n";
            $body .= "Message:\n" . $message . "\n";
            
            $headers = array('Content-Type: text/plain; charset=UTF-8');
            
            $sent = wp_mail($admin_email, $subject, $body, $headers);
            
            if ($sent) {
                wp_send_json_success(array(
                    'message' => 'Quote request sent successfully'
                ));
            } else {
                wp_send_json_error(array(
                    'message' => 'Failed to send email'
                ));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Register REST API endpoint for Stripe webhook
     */
    public function register_webhook_endpoint() {
        register_rest_route('yolo-yacht-search/v1', '/stripe-webhook', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_stripe_webhook'),
            'permission_callback' => '__return_true', // Stripe will verify via signature
        ));
    }
    
    /**
     * AJAX handler to create Stripe Checkout Session for balance payment
     */
    public function ajax_create_balance_checkout() {
        try {
            // Verify nonce for security
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_ys_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
                return;
            }
            
            // Get booking ID
            $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
            
            if (!$booking_id) {
                wp_send_json_error(array('message' => 'Invalid booking ID'));
                return;
            }
            
            // Get booking from database
            global $wpdb;
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            $booking = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_bookings} WHERE id = %d",
                $booking_id
            ));
            
            if (!$booking) {
                wp_send_json_error(array('message' => 'Booking not found'));
                return;
            }
            
            // Check if already paid
            if ($booking->payment_status === 'fully_paid') {
                wp_send_json_error(array('message' => 'This booking is already fully paid'));
                return;
            }
            
            // Check if there's a balance to pay
            if ($booking->remaining_balance <= 0) {
                wp_send_json_error(array('message' => 'No balance due for this booking'));
                return;
            }
            
            // Create Stripe session for balance payment
            $stripe = new YOLO_YS_Stripe();
            $session_url = $stripe->create_balance_checkout_session(
                $booking->id,
                $booking->yacht_id,
                $booking->yacht_name,
                $booking->date_from,
                $booking->date_to,
                $booking->remaining_balance,
                $booking->currency,
                $booking->customer_name,
                $booking->customer_email,
                $booking->customer_phone
            );
            
            if ($session_url) {
                wp_send_json_success(array('url' => $session_url));
            } else {
                wp_send_json_error(array('message' => 'Failed to create payment session'));
            }
            
        } catch (Exception $e) {
            error_log('YOLO YS: Balance checkout error - ' . $e->getMessage());
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    /**
     * Handle Stripe webhook callback
     */
    public function handle_stripe_webhook($request) {
        $stripe = new YOLO_YS_Stripe();
        $stripe->handle_webhook();
        
        return new WP_REST_Response(array('status' => 'success'), 200);
    }
}
