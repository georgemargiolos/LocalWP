<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Improved Backoffice Document Management
 * Replaces the original Guest Licenses page with a better interface
 */
class YOLO_YS_Admin_Documents {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_ajax_yolo_admin_fetch_booking_documents', array($this, 'ajax_fetch_booking_documents'));
        add_action('wp_ajax_yolo_admin_export_crew_list_csv', array($this, 'ajax_export_crew_list_csv'));
        add_action('wp_ajax_yolo_admin_download_all_licenses_zip', array($this, 'ajax_download_all_licenses_zip'));
        add_action('wp_ajax_yolo_admin_upload_document', array($this, 'ajax_upload_document'));
        add_action('wp_ajax_yolo_admin_delete_document', array($this, 'ajax_delete_document'));
    }
    
    /**
     * Add admin menu item
     * DISABLED: Charter Calendar is now merged into Bookings page as a tab view
     */
    public function add_admin_menu() {
        // Charter Calendar menu item removed - now accessible via Bookings > Calendar View tab
        /*
        add_submenu_page(
            'yolo-yacht-search',
            'Charter Calendar',
            'Charter Calendar',
            'manage_options',
            'yolo-charter-calendar',
            array($this, 'render_admin_page')
        );
        */
    }
    
    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_assets($hook) {
        // Load on both old Charter Calendar page (if accessed directly) and new Bookings page
        if ($hook !== 'yolo-yacht-search_page_yolo-charter-calendar' && $hook !== 'yolo-yacht-search_page_yolo-ys-bookings') {
            return;
        }
        
        wp_enqueue_style(
            'yolo-admin-documents',
            plugin_dir_url(dirname(__FILE__)) . 'admin/css/yolo-ys-admin-documents.css',
            array(),
            YOLO_YS_VERSION
        );
        
        wp_enqueue_script('jquery');
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        // Use new calendar view instead of dropdown
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/charter-calendar-view.php';
    }
    
    /**
     * AJAX handler to fetch documents for a specific booking
     */
    public function ajax_fetch_booking_documents() {
        // Security Check
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Access Denied.'));
        }
        
        // Nonce Check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_admin_documents_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        
        if (!$booking_id) {
            wp_send_json_error(array('message' => 'Invalid booking ID.'));
        }
        
        global $wpdb;
        $table_licenses = $wpdb->prefix . 'yolo_license_uploads';
        $table_crew = $wpdb->prefix . 'yolo_crew_list';
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        // Fetch Booking Info
        $booking = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_bookings} WHERE id = %d", $booking_id));
        if (!$booking) {
            wp_send_json_error(array('message' => 'Booking not found.'));
        }
        
        // Fetch Licenses
        $licenses = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_licenses} WHERE booking_id = %d", $booking_id));
        
        // Fetch Crew List
        $crew_list = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_crew} WHERE booking_id = %d ORDER BY crew_member_index ASC", $booking_id));
        
        // Render HTML
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'admin/partials/yolo-admin-document-viewer.php';
        $html = ob_get_clean();
        
        wp_send_json_success(array('html' => $html));
    }
    
    /**
     * AJAX handler to export the crew list as a CSV file
     */
    public function ajax_export_crew_list_csv() {
        // Security Check
        if (!current_user_can('manage_options')) {
            wp_die('Access Denied.');
        }
        
        // Nonce Check
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'yolo_admin_documents_nonce')) {
            wp_die('Security check failed.');
        }
        
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        
        if (!$booking_id) {
            wp_die('Invalid booking ID.');
        }
        
        global $wpdb;
        $table_crew = $wpdb->prefix . 'yolo_crew_list';
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        // Fetch Crew List
        $crew_list = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$table_crew} WHERE booking_id = %d ORDER BY crew_member_index ASC", $booking_id), ARRAY_A);
        
        // Fetch Booking Info for filename
        $booking = $wpdb->get_row($wpdb->prepare("SELECT yacht_name, date_from FROM {$table_bookings} WHERE id = %d", $booking_id));
        $filename = 'crew_list_' . $booking_id . '_' . sanitize_title($booking->yacht_name) . '_' . date('Ymd', strtotime($booking->date_from)) . '.csv';
        
        if (empty($crew_list)) {
            wp_die('No crew list found for this booking.');
        }
        
        // Output headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Get column headers
        $header = array_keys($crew_list[0]);
        $exclude_keys = array('id', 'booking_id', 'user_id', 'created_at');
        $header = array_diff($header, $exclude_keys);
        
        // Custom header mapping
        $header_map = array(
            'crew_member_index' => '#',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'sex' => 'Sex',
            'id_type' => 'ID Type',
            'id_number' => 'ID Number',
            'birth_date' => 'Birth Date',
            'role' => 'Role',
            'mobile_number' => 'Mobile Number',
            'nationality' => 'Nationality'
        );
        
        $final_header = array_map(function($key) use ($header_map) {
            return isset($header_map[$key]) ? $header_map[$key] : ucwords(str_replace('_', ' ', $key));
        }, $header);
        
        // Write the header row
        fputcsv($output, $final_header);
        
        // Write the data rows
        foreach ($crew_list as $row) {
            $data_row = array();
            foreach ($header as $key) {
                $data_row[] = $row[$key];
            }
            fputcsv($output, $data_row);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * AJAX handler to download all licenses as a ZIP file
     */
    public function ajax_download_all_licenses_zip() {
        // Security Check
        if (!current_user_can('manage_options')) {
            wp_die('Access Denied.');
        }
        
        // Nonce Check
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'yolo_admin_documents_nonce')) {
            wp_die('Security check failed.');
        }
        
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        
        if (!$booking_id) {
            wp_die('Invalid booking ID.');
        }
        
        global $wpdb;
        $table_licenses = $wpdb->prefix . 'yolo_license_uploads';
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        // Fetch Licenses
        $licenses = $wpdb->get_results($wpdb->prepare("SELECT file_path, file_type FROM {$table_licenses} WHERE booking_id = %d", $booking_id));
        
        // Fetch Booking Info for filename
        $booking = $wpdb->get_row($wpdb->prepare("SELECT yacht_name, date_from FROM {$table_bookings} WHERE id = %d", $booking_id));
        $zip_filename = 'licenses_' . $booking_id . '_' . sanitize_title($booking->yacht_name) . '_' . date('Ymd', strtotime($booking->date_from)) . '.zip';
        
        if (empty($licenses)) {
            wp_die('No licenses found for this booking.');
        }
        
        // Create a temporary ZIP file
        $temp_file = tempnam(sys_get_temp_dir(), 'yolo_zip');
        $zip = new ZipArchive();
        
        if ($zip->open($temp_file, ZipArchive::CREATE) !== TRUE) {
            wp_die('Could not create zip file.');
        }
        
        foreach ($licenses as $license) {
            $file_path = $license->file_path;
            $file_type = $license->file_type;
            
            // Get the original file extension
            $path_parts = pathinfo($file_path);
            $extension = isset($path_parts['extension']) ? '.' . $path_parts['extension'] : '';
            
            // Create a friendly name for the file inside the ZIP
            $internal_filename = $file_type . $extension;
            
            // Add the file to the zip
            if (file_exists($file_path)) {
                $zip->addFile($file_path, $internal_filename);
            }
        }
        
        $zip->close();
        
        // Output headers for ZIP download
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
        header('Content-Length: ' . filesize($temp_file));
        
        readfile($temp_file);
        
        // Clean up the temporary file
        unlink($temp_file);
        exit;
    }
    
    /**
     * AJAX handler to upload a document from admin to guest
     */
    public function ajax_upload_document() {
        // Security Check
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Access Denied.'));
        }
        
        // Nonce Check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_admin_upload_document_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $description = isset($_POST['description']) ? sanitize_text_field($_POST['description']) : '';
        
        if (!$booking_id) {
            wp_send_json_error(array('message' => 'Invalid booking ID.'));
        }
        
        // Check if file was uploaded
        if (empty($_FILES['document_file'])) {
            wp_send_json_error(array('message' => 'No file uploaded.'));
        }
        
        $file = $_FILES['document_file'];
        
        // Validate file
        $allowed_types = array('application/pdf', 'image/jpeg', 'image/jpg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        if (!in_array($file['type'], $allowed_types)) {
            wp_send_json_error(array('message' => 'Invalid file type. Only PDF, JPG, PNG, DOC, and DOCX files are allowed.'));
        }
        
        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            wp_send_json_error(array('message' => 'File size exceeds 10MB limit.'));
        }
        
        // Set up upload directory
        $upload_dir = wp_upload_dir();
        $yolo_upload_dir = $upload_dir['basedir'] . '/yolo-admin-documents';
        
        if (!file_exists($yolo_upload_dir)) {
            wp_mkdir_p($yolo_upload_dir);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_basename = sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME));
        $unique_filename = $file_basename . '_' . time() . '.' . $file_extension;
        $file_path = $yolo_upload_dir . '/' . $unique_filename;
        $file_url = $upload_dir['baseurl'] . '/yolo-admin-documents/' . $unique_filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error(array('message' => 'Failed to save file.'));
        }
        
        // Save to database
        global $wpdb;
        $table_admin_documents = $wpdb->prefix . 'yolo_admin_documents';
        
        $result = $wpdb->insert(
            $table_admin_documents,
            array(
                'booking_id' => $booking_id,
                'uploaded_by' => get_current_user_id(),
                'file_name' => $file['name'],
                'file_path' => $file_path,
                'file_url' => $file_url,
                'file_size' => $file['size'],
                'file_type' => $file['type'],
                'description' => $description,
                'uploaded_at' => current_time('mysql')
            ),
            array('%d', '%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            // Delete the uploaded file if database insert failed
            unlink($file_path);
            wp_send_json_error(array('message' => 'Failed to save document information.'));
        }
        
        wp_send_json_success(array(
            'message' => 'Document uploaded successfully!',
            'document_id' => $wpdb->insert_id
        ));
    }
    
    /**
     * AJAX handler to delete an admin-uploaded document
     */
    public function ajax_delete_document() {
        // Security Check
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Access Denied.'));
        }
        
        // Nonce Check
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_admin_documents_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed.'));
        }
        
        $doc_id = isset($_POST['doc_id']) ? intval($_POST['doc_id']) : 0;
        
        if (!$doc_id) {
            wp_send_json_error(array('message' => 'Invalid document ID.'));
        }
        
        global $wpdb;
        $table_admin_documents = $wpdb->prefix . 'yolo_admin_documents';
        
        // Get document info
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_admin_documents} WHERE id = %d",
            $doc_id
        ));
        
        if (!$document) {
            wp_send_json_error(array('message' => 'Document not found.'));
        }
        
        // Delete file from filesystem
        if (file_exists($document->file_path)) {
            unlink($document->file_path);
        }
        
        // Delete from database
        $result = $wpdb->delete(
            $table_admin_documents,
            array('id' => $doc_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to delete document from database.'));
        }
        
        wp_send_json_success(array('message' => 'Document deleted successfully!'));
    }
}
