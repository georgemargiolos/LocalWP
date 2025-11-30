<?php
/**
 * Stripe AJAX and REST API Handlers
 */
class YOLO_YS_Stripe_Handlers {
    
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_yolo_create_checkout_session', array($this, 'ajax_create_checkout_session'));
        add_action('wp_ajax_nopriv_yolo_create_checkout_session', array($this, 'ajax_create_checkout_session'));
        
        add_action('wp_ajax_yolo_get_live_price', array($this, 'ajax_get_live_price'));
        add_action('wp_ajax_nopriv_yolo_get_live_price', array($this, 'ajax_get_live_price'));
        
        add_action('wp_ajax_yolo_submit_custom_quote', array($this, 'ajax_submit_custom_quote'));
        add_action('wp_ajax_nopriv_yolo_submit_custom_quote', array($this, 'ajax_submit_custom_quote'));
        
        // Register REST API endpoint for webhook
        add_action('rest_api_init', array($this, 'register_webhook_endpoint'));
    }
    
    /**
     * AJAX handler to create Stripe Checkout Session
     */
    public function ajax_create_checkout_session() {
        try {
            // Get POST data
            $yacht_id = isset($_POST['yacht_id']) ? intval($_POST['yacht_id']) : 0;
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
                $customer_phone
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
            // Get POST data
            $yacht_id = isset($_POST['yacht_id']) ? intval($_POST['yacht_id']) : 0;
            $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : '';
            $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : '';
            
            // Validate inputs
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
            // Get form data
            $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
            $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
            $yacht_id = isset($_POST['yacht_id']) ? intval($_POST['yacht_id']) : 0;
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
     * Handle Stripe webhook callback
     */
    public function handle_stripe_webhook($request) {
        $stripe = new YOLO_YS_Stripe();
        $stripe->handle_webhook();
        
        return new WP_REST_Response(array('status' => 'success'), 200);
    }
}

// Initialize handlers
new YOLO_YS_Stripe_Handlers();
