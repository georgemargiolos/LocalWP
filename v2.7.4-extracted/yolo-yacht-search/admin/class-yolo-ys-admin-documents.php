<?php
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
    }
    
    /**
     * Add admin menu item
     */
    public function add_admin_menu() {
        add_submenu_page(
            'yolo-yacht-search',
            'Document Management',
            'Document Management',
            'manage_options',
            'yolo-document-management',
            array($this, 'render_admin_page')
        );
    }
    
    /**
     * Enqueue admin styles and scripts
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'yolo-yacht-search_page_yolo-document-management') {
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
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        // Fetch all bookings for the dropdown
        $bookings = $wpdb->get_results("
            SELECT id, yacht_name, date_from, date_to, customer_name
            FROM {$table_bookings}
            ORDER BY date_from DESC
        ");
        
        ?>
        <div class="wrap yolo-admin-documents-page">
            <h1>Document Management</h1>
            <p class="yolo-admin-subtitle">Select a charter to view all uploaded licenses and crew lists.</p>
            
            <div class="yolo-booking-selector-admin">
                <label for="yolo-booking-select-admin"><strong>Select Charter:</strong></label>
                <select id="yolo-booking-select-admin" class="yolo-select-large">
                    <option value="">-- Select a Booking --</option>
                    <?php foreach ($bookings as $booking): ?>
                        <option value="<?php echo esc_attr($booking->id); ?>">
                            #<?php echo esc_html($booking->id); ?> - <?php echo esc_html($booking->yacht_name); ?> 
                            (<?php echo date('M j, Y', strtotime($booking->date_from)); ?> - <?php echo date('M j, Y', strtotime($booking->date_to)); ?>) 
                            - <?php echo esc_html($booking->customer_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div id="yolo-document-viewer-admin">
                <div class="yolo-initial-message">
                    <p>Please select a charter from the dropdown above to view documents.</p>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#yolo-booking-select-admin').on('change', function() {
                var bookingId = $(this).val();
                var viewer = $('#yolo-document-viewer-admin');
                
                if (bookingId) {
                    viewer.html('<div class="yolo-loading-message"><p>Loading documents...</p></div>');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'yolo_admin_fetch_booking_documents',
                            booking_id: bookingId,
                            nonce: '<?php echo wp_create_nonce('yolo_admin_documents_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                viewer.html(response.data.html);
                            } else {
                                viewer.html('<div class="yolo-error-message"><p>Error: ' + response.data.message + '</p></div>');
                            }
                        },
                        error: function() {
                            viewer.html('<div class="yolo-error-message"><p>An unknown error occurred while fetching documents.</p></div>');
                        }
                    });
                } else {
                    viewer.html('<div class="yolo-initial-message"><p>Please select a charter from the dropdown above to view documents.</p></div>');
                }
            });
        });
        </script>
        <?php
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
}
