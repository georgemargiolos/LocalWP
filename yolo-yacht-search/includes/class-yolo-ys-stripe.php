<?php
/**
 * Stripe Checkout Integration
 */
class YOLO_YS_Stripe {
    
    /**
     * Initialize Stripe with API key
     */
    private function init_stripe() {
        $secret_key = get_option('yolo_ys_stripe_secret_key', '');
        
        if (empty($secret_key)) {
            throw new Exception('Stripe secret key not configured');
        }
        
        \Stripe\Stripe::setApiKey($secret_key);
    }
    
    /**
     * Create Stripe Checkout Session for yacht booking
     * 
     * @param int $yacht_id Yacht ID
     * @param string $yacht_name Yacht name
     * @param string $date_from Start date (Y-m-d)
     * @param string $date_to End date (Y-m-d)
     * @param float $total_price Total charter price
     * @param string $currency Currency code (default EUR)
     * @param string $customer_first_name Customer first name
     * @param string $customer_last_name Customer last name
     * @param string $customer_email Customer email
     * @param string $customer_phone Customer phone
     * @return array Session data with session_id
     */
    public function create_checkout_session($yacht_id, $yacht_name, $date_from, $date_to, $total_price, $currency = 'EUR', $customer_first_name = '', $customer_last_name = '', $customer_email = '', $customer_phone = '') {
        try {
            $this->init_stripe();
            
            // Get deposit percentage from settings
            $deposit_percentage = get_option('yolo_ys_deposit_percentage', 50);
            
            // Calculate deposit amount
            $deposit_amount = YOLO_YS_Price_Formatter::calculate_deposit($total_price, $deposit_percentage);
            $remaining_balance = YOLO_YS_Price_Formatter::calculate_remaining_balance($total_price, $deposit_amount);
            
            // Convert to Stripe format (cents)
            $stripe_amount = YOLO_YS_Price_Formatter::format_for_stripe($deposit_amount, $currency);
            
            // Get success and cancel URLs
            $success_url = home_url('/booking-confirmation?session_id={CHECKOUT_SESSION_ID}');
            $cancel_url = home_url('/yacht-details-page/?yacht_id=' . $yacht_id . '&dateFrom=' . $date_from . '&dateTo=' . $date_to);
            
            // Prepare customer name
            $customer_name = trim($customer_first_name . ' ' . $customer_last_name);
            
            // Create Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => 'Yacht Charter: ' . $yacht_name,
                            'description' => sprintf(
                                'Deposit (%d%%) for charter from %s to %s. Remaining balance: %s',
                                $deposit_percentage,
                                date('M d, Y', strtotime($date_from)),
                                date('M d, Y', strtotime($date_to)),
                                YOLO_YS_Price_Formatter::format_price($remaining_balance, $currency)
                            ),
                        ],
                        'unit_amount' => $stripe_amount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'customer_email' => $customer_email,
                'metadata' => [
                    'yacht_id' => $yacht_id,
                    'yacht_name' => $yacht_name,
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                    'total_price' => $total_price,
                    'deposit_amount' => $deposit_amount,
                    'deposit_percentage' => $deposit_percentage,
                    'remaining_balance' => $remaining_balance,
                    'currency' => $currency,
                    'customer_first_name' => $customer_first_name,
                    'customer_last_name' => $customer_last_name,
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'customer_phone' => $customer_phone,
                ],
            ]);
            
            return array(
                'success' => true,
                'session_id' => $session->id,
                'deposit_amount' => $deposit_amount,
                'remaining_balance' => $remaining_balance,
            );
            
        } catch (Exception $e) {
            error_log('YOLO YS Stripe: Failed to create checkout session - ' . $e->getMessage());
            return array(
                'success' => false,
                'error' => $e->getMessage(),
            );
        }
    }
    
    /**
     * Create Stripe Checkout Session for balance payment
     */
    public function create_balance_checkout_session($booking_id, $yacht_id, $yacht_name, $date_from, $date_to, $balance_amount, $currency, $customer_name, $customer_email, $customer_phone) {
        try {
            $this->init_stripe();
            
            // Create Checkout Session
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($currency),
                        'product_data' => [
                            'name' => $yacht_name . ' - Balance Payment',
                            'description' => 'Charter from ' . date('M j, Y', strtotime($date_from)) . ' to ' . date('M j, Y', strtotime($date_to)),
                        ],
                        'unit_amount' => intval($balance_amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->get_balance_success_url() . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->get_balance_cancel_url(),
                'customer_email' => $customer_email,
                'metadata' => [
                    'booking_id' => $booking_id,
                    'yacht_id' => $yacht_id,
                    'yacht_name' => $yacht_name,
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                    'balance_amount' => $balance_amount,
                    'currency' => $currency,
                    'customer_name' => $customer_name,
                    'customer_email' => $customer_email,
                    'customer_phone' => $customer_phone,
                    'payment_type' => 'balance',
                ],
            ]);
            
            return $session->url;
            
        } catch (Exception $e) {
            error_log('YOLO YS Stripe: Failed to create balance checkout session - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get balance payment success URL
     */
    private function get_balance_success_url() {
        $page_id = get_option('yolo_ys_balance_confirmation_page_id');
        if ($page_id) {
            return get_permalink($page_id);
        }
        return home_url('/balance-confirmation');
    }
    
    /**
     * Get balance payment cancel URL
     */
    private function get_balance_cancel_url() {
        return home_url('/balance-payment');
    }
    
    /**
     * Handle Stripe webhook events
     */
    public function handle_webhook() {
        $payload = @file_get_contents('php://input');
        $sig_header = isset($_SERVER['HTTP_STRIPE_SIGNATURE']) ? $_SERVER['HTTP_STRIPE_SIGNATURE'] : '';
        $endpoint_secret = get_option('yolo_ys_stripe_webhook_secret', '');
        
        try {
            if (!empty($endpoint_secret)) {
                // Verify webhook signature
                $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
            } else {
                // For testing without webhook secret
                $event = json_decode($payload, true);
            }
            
            // Handle the event
            if ($event['type'] == 'checkout.session.completed') {
                $session = $event['data']['object'];
                $this->handle_successful_payment($session);
            }
            
            http_response_code(200);
            echo json_encode(['status' => 'success']);
            
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            error_log('YOLO YS Stripe Webhook: Invalid payload - ' . $e->getMessage());
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            error_log('YOLO YS Stripe Webhook: Invalid signature - ' . $e->getMessage());
            http_response_code(400);
            exit();
        } catch(Exception $e) {
            error_log('YOLO YS Stripe Webhook: Error - ' . $e->getMessage());
            http_response_code(500);
            exit();
        }
    }
    
    /**
     * Handle successful payment
     * 
     * @param array $session Stripe session data
     */
    private function handle_successful_payment($session) {
        global $wpdb;
        
        // Extract booking details from metadata
        $yacht_id = $session['metadata']['yacht_id'];
        $yacht_name = $session['metadata']['yacht_name'];
        $date_from = $session['metadata']['date_from'];
        $date_to = $session['metadata']['date_to'];
        $total_price = $session['metadata']['total_price'];
        $deposit_amount = $session['metadata']['deposit_amount'];
        $remaining_balance = $session['metadata']['remaining_balance'];
        
        // Get customer details
        $customer_email = isset($session['customer_details']['email']) ? $session['customer_details']['email'] : '';
        $customer_name = isset($session['customer_details']['name']) ? $session['customer_details']['name'] : '';
        
        // Store booking in database
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $wpdb->insert($table_bookings, array(
            'yacht_id' => $yacht_id,
            'yacht_name' => $yacht_name,
            'date_from' => $date_from,
            'date_to' => $date_to,
            'total_price' => $total_price,
            'deposit_paid' => $deposit_amount,
            'remaining_balance' => $remaining_balance,
            'currency' => isset($session['metadata']['currency']) ? $session['metadata']['currency'] : 'EUR',
            'customer_email' => $customer_email,
            'customer_name' => $customer_name,
            'stripe_session_id' => $session['id'],
            'stripe_payment_intent' => isset($session['payment_intent']) ? $session['payment_intent'] : '',
            'payment_status' => 'deposit_paid',
            'booking_status' => 'confirmed',
            'created_at' => current_time('mysql'),
        ));
        
        $booking_id = $wpdb->insert_id;
        
        // Create guest user account
        $this->create_guest_user($booking_id, $customer_email, $customer_name, $session['id']);
        
        // Create reservation in Booking Manager API
        $this->create_booking_manager_reservation($booking_id, $yacht_id, $date_from, $date_to, $customer_email, $customer_name);
        
        // Send confirmation email
        $this->send_confirmation_email($booking_id);
        
        error_log('YOLO YS: Booking created successfully - ID: ' . $booking_id);
    }
    
    /**
     * Create reservation in Booking Manager API
     * 
     * @param int $booking_id Local booking ID
     * @param int $yacht_id Yacht ID
     * @param string $date_from Start date
     * @param string $date_to End date
     * @param string $customer_email Customer email
     * @param string $customer_name Customer name
     */
    private function create_booking_manager_reservation($booking_id, $yacht_id, $date_from, $date_to, $customer_email, $customer_name) {
        global $wpdb;
        
        try {
            $api = new YOLO_YS_Booking_Manager_API();
            
            // Prepare reservation data for Booking Manager API
            $reservation_data = array(
                'yachtId' => $yacht_id,
                'dateFrom' => $date_from . 'T12:00:00',
                'dateTo' => $date_to . 'T11:59:00',
                'customer' => array(
                    'email' => $customer_email,
                    'name' => $customer_name,
                ),
                'status' => 'confirmed',
            );
            
            // Create reservation in Booking Manager
            $result = $api->create_reservation($reservation_data);
            
            if ($result['success'] && isset($result['reservation_id'])) {
                // Store Booking Manager reservation ID in database
                $table_bookings = $wpdb->prefix . 'yolo_bookings';
                $wpdb->update(
                    $table_bookings,
                    array('bm_reservation_id' => $result['reservation_id']),
                    array('id' => $booking_id),
                    array('%s'),
                    array('%d')
                );
                
                error_log('YOLO YS: Created Booking Manager reservation ID: ' . $result['reservation_id'] . ' for booking ID: ' . $booking_id);
            } else {
                $error_msg = isset($result['error']) ? $result['error'] : 'Unknown error';
                error_log('YOLO YS: Failed to create Booking Manager reservation for booking ID ' . $booking_id . ': ' . $error_msg);
                
                // Store error in database for debugging
                $wpdb->update(
                    $table_bookings,
                    array('bm_sync_error' => $error_msg),
                    array('id' => $booking_id),
                    array('%s'),
                    array('%d')
                );
            }
            
        } catch (Exception $e) {
            error_log('YOLO YS: Exception creating Booking Manager reservation - ' . $e->getMessage());
            
            // Store exception in database
            $table_bookings = $wpdb->prefix . 'yolo_bookings';
            $wpdb->update(
                $table_bookings,
                array('bm_sync_error' => $e->getMessage()),
                array('id' => $booking_id),
                array('%s'),
                array('%d')
            );
        }
    }
    
    /**
     * Send booking confirmation email
     * 
     * @param int $booking_id Booking ID
     */
    private function send_confirmation_email($booking_id) {
        global $wpdb;
        
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_bookings} WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            return;
        }
        
        $to = $booking->customer_email;
        $subject = 'Booking Confirmation - ' . $booking->yacht_name;
        
        $message = sprintf(
            "Dear %s,\n\n" .
            "Thank you for your booking!\n\n" .
            "Booking Details:\n" .
            "Yacht: %s\n" .
            "Dates: %s to %s\n" .
            "Total Price: %s\n" .
            "Deposit Paid: %s\n" .
            "Remaining Balance: %s\n\n" .
            "Your booking reference: #%d\n\n" .
            "We look forward to welcoming you aboard!\n\n" .
            "Best regards,\n" .
            "YOLO Charters Team",
            $booking->customer_name,
            $booking->yacht_name,
            date('F j, Y', strtotime($booking->date_from)),
            date('F j, Y', strtotime($booking->date_to)),
            YOLO_YS_Price_Formatter::format_price($booking->total_price, $booking->currency),
            YOLO_YS_Price_Formatter::format_price($booking->deposit_paid, $booking->currency),
            YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency),
            $booking_id
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
        
        error_log('YOLO YS: Confirmation email sent to ' . $to);
    }
    
    /**
     * Create guest user account after successful booking
     * 
     * @param int $booking_id Booking ID
     * @param string $customer_email Customer email
     * @param string $customer_name Customer full name
     * @param string $confirmation_number Stripe session ID used as confirmation
     */
    private function create_guest_user($booking_id, $customer_email, $customer_name, $confirmation_number) {
        error_log('YOLO YS: Starting guest user creation for booking ID: ' . $booking_id . ', email: ' . $customer_email);
        
        // Split customer name into first and last name
        $name_parts = explode(' ', trim($customer_name), 2);
        $customer_first_name = isset($name_parts[0]) ? $name_parts[0] : $customer_name;
        $customer_last_name = isset($name_parts[1]) ? $name_parts[1] : '';
        
        error_log('YOLO YS: Name split - First: ' . $customer_first_name . ', Last: ' . $customer_last_name);
        
        // Check if class exists
        if (!class_exists('YOLO_YS_Guest_Users')) {
            error_log('YOLO YS ERROR: YOLO_YS_Guest_Users class not found!');
            return;
        }
        
        // Create guest user
        $guest_manager = new YOLO_YS_Guest_Users();
        error_log('YOLO YS: Guest manager instantiated, calling create_guest_user()');
        
        $result = $guest_manager->create_guest_user(
            $booking_id,
            $customer_email,
            $customer_first_name,
            $customer_last_name,
            $booking_id // Use booking ID as confirmation number
        );
        
        error_log('YOLO YS: Guest user creation result: ' . print_r($result, true));
        
        if ($result['success']) {
            error_log('YOLO YS: Guest user created successfully - User ID: ' . $result['user_id'] . ' for booking ID: ' . $booking_id);
            
            // Send guest login credentials email
            $this->send_guest_credentials_email($booking_id, $customer_email, $customer_name, $result['username'], $result['password']);
        } else {
            error_log('YOLO YS ERROR: Failed to create guest user for booking ID: ' . $booking_id . ' - ' . $result['message']);
        }
    }
    
    /**
     * Send guest login credentials email
     * 
     * @param int $booking_id Booking ID
     * @param string $customer_email Customer email
     * @param string $customer_name Customer name
     * @param string $username Login username
     * @param string $password Login password
     */
    private function send_guest_credentials_email($booking_id, $customer_email, $customer_name, $username, $password) {
        // Only send if password was generated (new user)
        if (!$password) {
            return;
        }
        
        $dashboard_page = get_page_by_path('guest-dashboard');
        $dashboard_url = $dashboard_page ? get_permalink($dashboard_page->ID) : home_url();
        $login_url = wp_login_url($dashboard_url);
        
        $to = $customer_email;
        $subject = 'Your Guest Account - YOLO Charters';
        
        $message = sprintf(
            "Dear %s,\n\n" .
            "Your guest account has been created!\n\n" .
            "Login Details:\n" .
            "Username: %s\n" .
            "Password: %s\n\n" .
            "Login here: %s\n\n" .
            "Once logged in, you can:\n" .
            "- View your booking details\n" .
            "- Upload your sailing license (front and back)\n" .
            "- Track your charter information\n\n" .
            "Please upload your sailing license before your charter date.\n\n" .
            "Best regards,\n" .
            "YOLO Charters Team",
            $customer_name,
            $username,
            $password,
            $login_url
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        wp_mail($to, $subject, $message, $headers);
        
        error_log('YOLO YS: Guest credentials email sent to ' . $to);
    }
}
