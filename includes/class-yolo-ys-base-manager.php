<?php
/**
 * Base Manager System
 *
 * Handles base manager role, dashboard, yacht management, check-in/check-out processes,
 * and warehouse management.
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Base_Manager {

    /**
     * Initialize the base manager system
     */
    public function __construct() {
        // Register base manager role on plugin activation
        add_action('init', array($this, 'register_base_manager_role'));
        
        // Register shortcode
        add_shortcode('base_manager', array($this, 'render_base_manager_dashboard'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_yolo_bm_save_yacht', array($this, 'ajax_save_yacht'));
        add_action('wp_ajax_yolo_bm_get_yachts', array($this, 'ajax_get_yachts'));
        add_action('wp_ajax_yolo_bm_delete_yacht', array($this, 'ajax_delete_yacht'));
        add_action('wp_ajax_yolo_bm_save_equipment_category', array($this, 'ajax_save_equipment_category'));
        add_action('wp_ajax_yolo_bm_get_equipment_categories', array($this, 'ajax_get_equipment_categories'));
        add_action('wp_ajax_yolo_bm_save_checkin', array($this, 'ajax_save_checkin'));
        add_action('wp_ajax_yolo_bm_save_checkout', array($this, 'ajax_save_checkout'));
        add_action('wp_ajax_yolo_bm_generate_pdf', array($this, 'ajax_generate_pdf'));
        add_action('wp_ajax_yolo_bm_send_to_guest', array($this, 'ajax_send_to_guest'));
        add_action('wp_ajax_yolo_bm_save_warehouse_item', array($this, 'ajax_save_warehouse_item'));
        add_action('wp_ajax_yolo_bm_get_warehouse_items', array($this, 'ajax_get_warehouse_items'));
        add_action('wp_ajax_yolo_bm_get_bookings_calendar', array($this, 'ajax_get_bookings_calendar'));
        
        // Guest AJAX handlers
        add_action('wp_ajax_yolo_guest_sign_document', array($this, 'ajax_guest_sign_document'));
        add_action('wp_ajax_yolo_guest_get_documents', array($this, 'ajax_guest_get_documents'));
        
        // Redirect base managers from wp-admin
        add_action('admin_init', array($this, 'redirect_base_manager_from_admin'));
    }

    /**
     * Register base manager role
     */
    public function register_base_manager_role() {
        if (!get_role('base_manager')) {
            add_role(
                'base_manager',
                'Base Manager',
                array(
                    'read' => true,
                    'level_0' => true,
                    'manage_base_operations' => true,
                )
            );
            error_log('YOLO YS: Base Manager role created');
        }
    }

    /**
     * Redirect base managers away from wp-admin
     */
    public function redirect_base_manager_from_admin() {
        $user = wp_get_current_user();
        
        if (in_array('base_manager', (array) $user->roles) && !defined('DOING_AJAX')) {
            $base_manager_page = get_option('yolo_ys_base_manager_page_id');
            if ($base_manager_page) {
                wp_redirect(get_permalink($base_manager_page));
                exit;
            } else {
                wp_redirect(home_url());
                exit;
            }
        }
    }

    /**
     * Enqueue assets for base manager dashboard
     */
    public function enqueue_assets() {
        if (is_page() && has_shortcode(get_post()->post_content, 'base_manager')) {
            // Bootstrap 5 (if not already loaded)
            wp_enqueue_style(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
                array(),
                '5.3.2'
            );
            
            wp_enqueue_script(
                'bootstrap-5',
                'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
                array(),
                '5.3.2',
                true
            );
            
            // Font Awesome
            wp_enqueue_style(
                'font-awesome-6',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                array(),
                '6.4.0'
            );
            
            // Signature Pad library
            wp_enqueue_script(
                'signature-pad',
                'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js',
                array(),
                '4.1.7',
                true
            );
            
            // Custom CSS
            wp_enqueue_style(
                'yolo-base-manager-css',
                YOLO_YS_PLUGIN_URL . 'public/css/base-manager.css',
                array('bootstrap-5'),
                YOLO_YS_VERSION
            );
            
            // Custom JS
            wp_enqueue_script(
                'yolo-base-manager-js',
                YOLO_YS_PLUGIN_URL . 'public/js/base-manager.js',
                array('jquery', 'bootstrap-5', 'signature-pad'),
                YOLO_YS_VERSION,
                true
            );
            
            // Localize script
            wp_localize_script('yolo-base-manager-js', 'yoloBaseManager', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('yolo_base_manager_nonce'),
            ));
        }
    }

    /**
     * Render base manager dashboard
     */
    public function render_base_manager_dashboard($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="alert alert-warning">Please log in to access the Base Manager dashboard.</div>';
        }
        
        $user = wp_get_current_user();
        
        // Check if user has base_manager or administrator role
        if (!in_array('base_manager', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            return '<div class="alert alert-danger">Access denied. You do not have permission to view this page.</div>';
        }
        
        // Include the dashboard template
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/partials/base-manager-dashboard.php';
        return ob_get_clean();
    }

    /**
     * AJAX: Save yacht
     */
    public function ajax_save_yacht() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_yachts';
        
        $yacht_id = isset($_POST['yacht_id']) ? intval($_POST['yacht_id']) : 0;
        $yacht_name = sanitize_text_field($_POST['yacht_name']);
        $yacht_model = sanitize_text_field($_POST['yacht_model']);
        $owner_name = sanitize_text_field($_POST['owner_name']);
        $owner_surname = sanitize_text_field($_POST['owner_surname']);
        $owner_mobile = sanitize_text_field($_POST['owner_mobile']);
        $owner_email = sanitize_email($_POST['owner_email']);
        
        // Handle file uploads
        $company_logo = '';
        $boat_logo = '';
        
        if (!empty($_FILES['company_logo']['name'])) {
            $company_logo = $this->handle_file_upload($_FILES['company_logo'], 'company_logo');
        }
        
        if (!empty($_FILES['boat_logo']['name'])) {
            $boat_logo = $this->handle_file_upload($_FILES['boat_logo'], 'boat_logo');
        }
        
        $data = array(
            'yacht_name' => $yacht_name,
            'yacht_model' => $yacht_model,
            'owner_name' => $owner_name,
            'owner_surname' => $owner_surname,
            'owner_mobile' => $owner_mobile,
            'owner_email' => $owner_email,
            'updated_at' => current_time('mysql'),
        );
        
        if ($company_logo) {
            $data['company_logo'] = $company_logo;
        }
        
        if ($boat_logo) {
            $data['boat_logo'] = $boat_logo;
        }
        
        if ($yacht_id > 0) {
            // Update existing yacht
            $wpdb->update($table_name, $data, array('id' => $yacht_id));
            wp_send_json_success(array('message' => 'Yacht updated successfully', 'yacht_id' => $yacht_id));
        } else {
            // Insert new yacht
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
            $yacht_id = $wpdb->insert_id;
            wp_send_json_success(array('message' => 'Yacht created successfully', 'yacht_id' => $yacht_id));
        }
    }

    /**
     * Handle file upload
     */
    private function handle_file_upload($file, $prefix) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            return $movefile['url'];
        }
        
        return '';
    }

    /**
     * AJAX: Get yachts
     */
    public function ajax_get_yachts() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_yachts';
        
        $yachts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY yacht_name ASC");
        
        wp_send_json_success(array('yachts' => $yachts));
    }

    /**
     * AJAX: Delete yacht
     */
    public function ajax_delete_yacht() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $yacht_id = intval($_POST['yacht_id']);
        
        $wpdb->delete($wpdb->prefix . 'yolo_bm_yachts', array('id' => $yacht_id));
        
        wp_send_json_success(array('message' => 'Yacht deleted successfully'));
    }

    /**
     * AJAX: Save equipment category
     */
    public function ajax_save_equipment_category() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_equipment_categories';
        
        $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $yacht_id = intval($_POST['yacht_id']);
        $category_name = sanitize_text_field($_POST['category_name']);
        $items = json_decode(stripslashes($_POST['items']), true);
        
        $data = array(
            'yacht_id' => $yacht_id,
            'category_name' => $category_name,
            'items' => json_encode($items),
            'updated_at' => current_time('mysql'),
        );
        
        if ($category_id > 0) {
            $wpdb->update($table_name, $data, array('id' => $category_id));
            wp_send_json_success(array('message' => 'Category updated successfully', 'category_id' => $category_id));
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
            $category_id = $wpdb->insert_id;
            wp_send_json_success(array('message' => 'Category created successfully', 'category_id' => $category_id));
        }
    }

    /**
     * AJAX: Get equipment categories
     */
    public function ajax_get_equipment_categories() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $yacht_id = intval($_POST['yacht_id']);
        $table_name = $wpdb->prefix . 'yolo_bm_equipment_categories';
        
        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE yacht_id = %d ORDER BY category_name ASC",
            $yacht_id
        ));
        
        // Decode items JSON for each category
        foreach ($categories as $category) {
            $category->items = json_decode($category->items, true);
        }
        
        wp_send_json_success(array('categories' => $categories));
    }

    /**
     * AJAX: Save check-in
     */
    public function ajax_save_checkin() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_checkins';
        
        $booking_id = intval($_POST['booking_id']);
        $yacht_id = intval($_POST['yacht_id']);
        $checklist_data = stripslashes($_POST['checklist_data']);
        $signature = sanitize_text_field($_POST['signature']);
        $status = sanitize_text_field($_POST['status']);
        
        $data = array(
            'booking_id' => $booking_id,
            'yacht_id' => $yacht_id,
            'checklist_data' => $checklist_data,
            'signature' => $signature,
            'status' => $status,
            'completed_by' => get_current_user_id(),
            'updated_at' => current_time('mysql'),
        );
        
        // Check if check-in already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE booking_id = %d",
            $booking_id
        ));
        
        if ($existing) {
            $wpdb->update($table_name, $data, array('id' => $existing->id));
            $checkin_id = $existing->id;
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
            $checkin_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array('message' => 'Check-in saved successfully', 'checkin_id' => $checkin_id));
    }

    /**
     * AJAX: Save check-out
     */
    public function ajax_save_checkout() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_checkouts';
        
        $booking_id = intval($_POST['booking_id']);
        $yacht_id = intval($_POST['yacht_id']);
        $checklist_data = stripslashes($_POST['checklist_data']);
        $signature = sanitize_text_field($_POST['signature']);
        $status = sanitize_text_field($_POST['status']);
        
        $data = array(
            'booking_id' => $booking_id,
            'yacht_id' => $yacht_id,
            'checklist_data' => $checklist_data,
            'signature' => $signature,
            'status' => $status,
            'completed_by' => get_current_user_id(),
            'updated_at' => current_time('mysql'),
        );
        
        // Check if check-out already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE booking_id = %d",
            $booking_id
        ));
        
        if ($existing) {
            $wpdb->update($table_name, $data, array('id' => $existing->id));
            $checkout_id = $existing->id;
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
            $checkout_id = $wpdb->insert_id;
        }
        
        wp_send_json_success(array('message' => 'Check-out saved successfully', 'checkout_id' => $checkout_id));
    }

    /**
     * AJAX: Generate PDF
     */
    public function ajax_generate_pdf() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        $type = sanitize_text_field($_POST['type']); // 'checkin' or 'checkout'
        $record_id = intval($_POST['record_id']);
        
        // Generate PDF using FPDF or similar library
        $pdf_url = $this->generate_pdf_document($type, $record_id);
        
        if ($pdf_url) {
            wp_send_json_success(array('pdf_url' => $pdf_url));
        } else {
            wp_send_json_error(array('message' => 'Failed to generate PDF'));
        }
    }

    /**
     * Generate PDF document
     */
    private function generate_pdf_document($type, $record_id) {
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-pdf-generator.php';
        
        if ($type === 'checkin') {
            return YOLO_YS_PDF_Generator::generate_checkin_pdf($record_id);
        } else if ($type === 'checkout') {
            return YOLO_YS_PDF_Generator::generate_checkout_pdf($record_id);
        }
        
        return false;
    }

    /**
     * AJAX: Send to guest
     */
    public function ajax_send_to_guest() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        $type = sanitize_text_field($_POST['type']);
        $record_id = intval($_POST['record_id']);
        $booking_id = intval($_POST['booking_id']);
        
        // Get booking and guest info
        global $wpdb;
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            wp_send_json_error(array('message' => 'Booking not found'));
            return;
        }
        
        // Send email to guest
        $sent = $this->send_document_to_guest($booking, $type, $record_id);
        
        if ($sent) {
            wp_send_json_success(array('message' => 'Document sent to guest successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send document'));
        }
    }

    /**
     * Send document to guest via email
     */
    private function send_document_to_guest($booking, $type, $record_id) {
        global $wpdb;
        
        // Get document data
        $table_name = $type === 'checkin' ? $wpdb->prefix . 'yolo_bm_checkins' : $wpdb->prefix . 'yolo_bm_checkouts';
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $record_id
        ));
        
        if (!$document || !$document->pdf_url) {
            // Generate PDF first if not exists
            require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-pdf-generator.php';
            if ($type === 'checkin') {
                $pdf_url = YOLO_YS_PDF_Generator::generate_checkin_pdf($record_id);
            } else {
                $pdf_url = YOLO_YS_PDF_Generator::generate_checkout_pdf($record_id);
            }
        } else {
            $pdf_url = $document->pdf_url;
        }
        
        if (!$pdf_url) {
            return false;
        }
        
        // Get guest dashboard URL
        $guest_dashboard_url = home_url('/guest-dashboard/');
        
        // Prepare email
        $to = $booking->customer_email;
        $subject = 'YOLO Charters - ' . ucfirst($type) . ' Document for Booking #' . $booking->id;
        
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';
        $message .= '<h2 style="color: #1e3a8a; text-align: center;">YOLO Charters</h2>';
        $message .= '<h3 style="color: #495057;">Yacht ' . ucfirst($type) . ' Document</h3>';
        $message .= '<p>Dear ' . esc_html($booking->customer_name) . ',</p>';
        $message .= '<p>Your yacht ' . $type . ' document for booking #' . $booking->id . ' is ready for your review and signature.</p>';
        $message .= '<p><strong>Please review the document and sign it in your guest dashboard.</strong></p>';
        $message .= '<div style="text-align: center; margin: 30px 0;">';
        $message .= '<a href="' . esc_url($guest_dashboard_url) . '" style="background-color: #1e3a8a; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;">View in Dashboard</a>';
        $message .= '</div>';
        $message .= '<p>You can also download the document directly:</p>';
        $message .= '<p><a href="' . esc_url($pdf_url) . '" style="color: #1e3a8a;">Download PDF Document</a></p>';
        $message .= '<hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">';
        $message .= '<p style="font-size: 12px; color: #6c757d;">Best regards,<br>YOLO Charters Team</p>';
        $message .= '</div></body></html>';
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: YOLO Charters <noreply@yolocharters.com>'
        );
        
        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * AJAX: Save warehouse item
     */
    public function ajax_save_warehouse_item() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_warehouse';
        
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $yacht_id = intval($_POST['yacht_id']);
        $item_name = sanitize_text_field($_POST['item_name']);
        $quantity = intval($_POST['quantity']);
        $expiry_date = sanitize_text_field($_POST['expiry_date']);
        $location = sanitize_text_field($_POST['location']);
        
        $data = array(
            'yacht_id' => $yacht_id,
            'item_name' => $item_name,
            'quantity' => $quantity,
            'expiry_date' => $expiry_date,
            'location' => $location,
            'updated_at' => current_time('mysql'),
        );
        
        if ($item_id > 0) {
            $wpdb->update($table_name, $data, array('id' => $item_id));
            wp_send_json_success(array('message' => 'Item updated successfully', 'item_id' => $item_id));
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table_name, $data);
            $item_id = $wpdb->insert_id;
            wp_send_json_success(array('message' => 'Item created successfully', 'item_id' => $item_id));
        }
    }

    /**
     * AJAX: Get warehouse items
     */
    public function ajax_get_warehouse_items() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $yacht_id = intval($_POST['yacht_id']);
        $table_name = $wpdb->prefix . 'yolo_bm_warehouse';
        
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE yacht_id = %d ORDER BY item_name ASC",
            $yacht_id
        ));
        
        wp_send_json_success(array('items' => $items));
    }

    /**
     * AJAX: Get bookings calendar
     */
    public function ajax_get_bookings_calendar() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('manage_base_operations') && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bookings';
        
        $bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY check_in_date ASC");
        
        wp_send_json_success(array('bookings' => $bookings));
    }

    /**
     * AJAX: Guest sign document
     */
    public function ajax_guest_sign_document() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please log in'));
            return;
        }
        
        $type = sanitize_text_field($_POST['type']);
        $record_id = intval($_POST['record_id']);
        $signature = sanitize_text_field($_POST['signature']);
        
        global $wpdb;
        $table_name = $type === 'checkin' ? $wpdb->prefix . 'yolo_bm_checkins' : $wpdb->prefix . 'yolo_bm_checkouts';
        
        $wpdb->update(
            $table_name,
            array(
                'guest_signature' => $signature,
                'guest_signed_at' => current_time('mysql'),
            ),
            array('id' => $record_id)
        );
        
        wp_send_json_success(array('message' => 'Document signed successfully'));
    }

    /**
     * AJAX: Guest get documents
     */
    public function ajax_guest_get_documents() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please log in'));
            return;
        }
        
        $user_id = get_current_user_id();
        
        global $wpdb;
        
        // Get user's bookings
        $bookings = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}yolo_bookings WHERE user_id = %d",
            $user_id
        ));
        
        $booking_ids = array_map(function($b) { return $b->id; }, $bookings);
        
        if (empty($booking_ids)) {
            wp_send_json_success(array('checkins' => array(), 'checkouts' => array()));
            return;
        }
        
        $ids_placeholder = implode(',', array_fill(0, count($booking_ids), '%d'));
        
        // Get check-ins
        $checkins = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_checkins WHERE booking_id IN ($ids_placeholder)",
            ...$booking_ids
        ));
        
        // Get check-outs
        $checkouts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_checkouts WHERE booking_id IN ($ids_placeholder)",
            ...$booking_ids
        ));
        
        wp_send_json_success(array('checkins' => $checkins, 'checkouts' => $checkouts));
    }
}

// Initialize the base manager system
new YOLO_YS_Base_Manager();
