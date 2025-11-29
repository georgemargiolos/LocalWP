<?php
/**
 * Stripe AJAX and REST API Handlers
 */
class YOLO_YS_Stripe_Handlers {
    
    public function __construct() {
        // Register AJAX handlers
        add_action('wp_ajax_yolo_create_checkout_session', array($this, 'ajax_create_checkout_session'));
        add_action('wp_ajax_nopriv_yolo_create_checkout_session', array($this, 'ajax_create_checkout_session'));
        
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
            
            // Validate inputs
            if (empty($yacht_id) || empty($date_from) || empty($date_to) || $total_price <= 0) {
                wp_send_json_error(array(
                    'message' => 'Missing required booking information'
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
                $total_price
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
