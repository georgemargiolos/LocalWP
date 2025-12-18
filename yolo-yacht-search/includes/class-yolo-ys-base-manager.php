<?php
if (!defined('ABSPATH')) {
    exit;
}

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

class YOLO_YS_Base_Manager {

    /**
     * Initialize the base manager system
     */
    public function __construct() {
        // AUTO-FIX v17.13: Ensure tables exist
        add_action('admin_init', array($this, 'ensure_tables_exist'));
        
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
        add_action('wp_ajax_yolo_bm_delete_equipment_category', array($this, 'ajax_delete_equipment_category'));
        add_action('wp_ajax_yolo_bm_get_checkins', array($this, 'ajax_get_checkins'));
        add_action('wp_ajax_yolo_bm_save_checkin', array($this, 'ajax_save_checkin'));
        add_action('wp_ajax_yolo_bm_get_checkouts', array($this, 'ajax_get_checkouts'));
        add_action('wp_ajax_yolo_bm_save_checkout', array($this, 'ajax_save_checkout'));
        add_action('wp_ajax_yolo_bm_generate_pdf', array($this, 'ajax_generate_pdf'));
        add_action('wp_ajax_yolo_bm_send_to_guest', array($this, 'ajax_send_to_guest'));
        add_action('wp_ajax_yolo_bm_upload_document', array($this, 'ajax_upload_document'));
        add_action('wp_ajax_yolo_bm_save_warehouse_item', array($this, 'ajax_save_warehouse_item'));
        add_action('wp_ajax_yolo_bm_get_warehouse_items', array($this, 'ajax_get_warehouse_items'));
        add_action('wp_ajax_yolo_bm_delete_warehouse_item', array($this, 'ajax_delete_warehouse_item'));
        add_action('wp_ajax_yolo_bm_get_bookings_calendar', array($this, 'ajax_get_bookings_calendar'));
        
        // Guest AJAX handlers
        add_action('wp_ajax_yolo_guest_sign_document', array($this, 'ajax_guest_sign_document'));
        add_action('wp_ajax_yolo_guest_get_documents', array($this, 'ajax_guest_get_documents'));
        
        // Add custom admin dashboard page
        add_action('admin_menu', array($this, 'add_admin_dashboard_page'), 25); // Priority 25 to run AFTER main menu
        
        // Remove admin menu items for base managers
        add_action('admin_menu', array($this, 'remove_admin_menu_items'), 999);
        
        // Enqueue admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Register base manager role
     * Base Manager = Editor role + Base Manager capabilities
     */
    public function register_base_manager_role() {
        // Get editor role capabilities
        $editor = get_role('editor');
        
        if (!get_role('base_manager')) {
            // Create base manager with all editor capabilities
            $capabilities = $editor ? $editor->capabilities : array();
            
            // Add custom base manager capabilities
            $capabilities['manage_base_operations'] = true;
            $capabilities['manage_yachts'] = true;
            $capabilities['manage_checkins'] = true;
            $capabilities['manage_checkouts'] = true;
            $capabilities['manage_warehouse'] = true;
            
            add_role(
                'base_manager',
                'Base Manager',
                $capabilities
            );
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO YS: Base Manager role created with Editor capabilities');
            }
        } else {
            // Update existing base manager role with editor capabilities
            $base_manager = get_role('base_manager');
            if ($base_manager && $editor) {
                foreach ($editor->capabilities as $cap => $granted) {
                    $base_manager->add_cap($cap);
                }
                // Add custom capabilities
                $base_manager->add_cap('manage_base_operations');
                $base_manager->add_cap('manage_yachts');
                $base_manager->add_cap('manage_checkins');
                $base_manager->add_cap('manage_checkouts');
                $base_manager->add_cap('manage_warehouse');
            }
        }
    }

    /**
     * Add Base Manager submenu items under YOLO Yacht Search menu
     * Uses 'edit_posts' capability to match parent menu
     * Both Admins and Base Manager role users have this capability
     */
    public function add_admin_dashboard_page() {
        // Add Base Manager Dashboard submenu under YOLO Yacht Search
        add_submenu_page(
            'yolo-yacht-search',           // Parent slug (main YOLO menu)
            'Base Manager Dashboard',       // Page title
            'Base Manager Dashboard',       // Menu title
            'edit_posts',                  // Capability (matches parent menu)
            'yolo-base-manager',           // Menu slug
            array($this, 'render_admin_dashboard')  // Callback
        );
        
        // Add Yacht Management submenu
        add_submenu_page(
            'yolo-yacht-search',
            'Yacht Management',
            'Yacht Management',
            'edit_posts',
            'yolo-yacht-management',
            array($this, 'render_yacht_management_page')
        );
        
        // Add Check-In submenu
        add_submenu_page(
            'yolo-yacht-search',
            'Check-In',
            'Check-In',
            'edit_posts',
            'yolo-checkin',
            array($this, 'render_checkin_page')
        );
        
        // Add Check-Out submenu
        add_submenu_page(
            'yolo-yacht-search',
            'Check-Out',
            'Check-Out',
            'edit_posts',
            'yolo-checkout',
            array($this, 'render_checkout_page')
        );
        
        // Add Warehouse Management submenu
        add_submenu_page(
            'yolo-yacht-search',
            'Warehouse',
            'Warehouse',
            'edit_posts',
            'yolo-warehouse',
            array($this, 'render_warehouse_page')
        );
    }
    
    /**
     * Remove admin menu items for base managers
     */
    public function remove_admin_menu_items() {
        $user = wp_get_current_user();
        
        // Only restrict base managers, not admins
        if (in_array('base_manager', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
            // Remove WordPress core admin pages
            remove_menu_page('plugins.php');              // Plugins
            remove_menu_page('themes.php');               // Themes
            remove_menu_page('tools.php');                // Tools
            remove_menu_page('options-general.php');      // Settings
            remove_submenu_page('index.php', 'update-core.php'); // Updates
        }
    }
    
    /**
     * Enqueue admin assets for base manager pages
     */
    public function enqueue_admin_assets($hook) {
        // Only load on base manager pages
        if (strpos($hook, 'yolo-') === false && strpos($hook, 'yolo_') === false) {
            return;
        }
        
        // Base manager admin CSS
        wp_enqueue_style(
            'yolo-base-manager-admin',
            YOLO_YS_PLUGIN_URL . 'admin/css/base-manager-admin.css',
            array(),
            YOLO_YS_VERSION
        );
        
        // NOTE: Do NOT load base-manager.js on admin pages!
        // It's meant for the frontend shortcode and has a different data format expectation.
        // The admin Check-In/Check-Out pages have their own inline JavaScript.
        
        // Signature Pad library (still needed for admin pages)
        wp_enqueue_script(
            'signature-pad',
            'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js',
            array(),
            '4.1.7',
            true
        );
        
        // Add inline script to provide nonce for admin pages
        wp_add_inline_script(
            'signature-pad',
            'var yoloBaseManager = {' .
            '    ajaxurl: "' . admin_url('admin-ajax.php') . '",' .
            '    nonce: "' . wp_create_nonce('yolo_base_manager_nonce') . '"' .
            '};',
            'before'
        );
    }
    
    /**
     * Render admin dashboard page
     */
    public function render_admin_dashboard() {
        include YOLO_YS_PLUGIN_DIR . 'admin/partials/base-manager-admin-dashboard.php';
    }
    
    /**
     * Render yacht management page
     */
    public function render_yacht_management_page() {
        include YOLO_YS_PLUGIN_DIR . 'admin/partials/base-manager-yacht-management.php';
    }
    
    /**
     * Render check-in page
     */
    public function render_checkin_page() {
        include YOLO_YS_PLUGIN_DIR . 'admin/partials/base-manager-checkin.php';
    }
    
    /**
     * Render check-out page
     */
    public function render_checkout_page() {
        include YOLO_YS_PLUGIN_DIR . 'admin/partials/base-manager-checkout.php';
    }
    
    /**
     * Render warehouse page
     */
    public function render_warehouse_page() {
        include YOLO_YS_PLUGIN_DIR . 'admin/partials/base-manager-warehouse.php';
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
            
            // Font Awesome (conditional based on setting)
            if (get_option('yolo_ys_load_fontawesome', '0') === '1') {
                wp_enqueue_style(
                    'font-awesome-6',
                    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                    array(),
                    '6.4.0'
                );
            }
            
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
        
        if (!current_user_can('edit_posts')) {
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
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM v41.4: ajax_get_yachts called');
        }
        
        // Verify nonce
        if (!check_ajax_referer('yolo_base_manager_nonce', 'nonce', false)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Nonce verification FAILED');
            }
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.'));
            return;
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Nonce verified OK');
        }
        
        if (!current_user_can('edit_posts')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Permission denied - user cannot edit_posts');
            }
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Permission check passed');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_yachts';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Querying table: ' . $table_name);
        }
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists != $table_name) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Table ' . $table_name . ' does NOT exist! Creating tables...');
            }
            require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-base-manager-database.php';
            YOLO_YS_Base_Manager_Database::create_tables();
        }
        
        $yachts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY yacht_name ASC");
        
        if ($wpdb->last_error) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Database error: ' . $wpdb->last_error);
            }
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        $count = is_array($yachts) ? count($yachts) : 0;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Found ' . $count . ' yachts');
            error_log('YOLO BM: Yachts data: ' . print_r($yachts, true));
        }
        
        wp_send_json_success($yachts);
    }

    /**
     * AJAX: Delete yacht
     */
    public function ajax_delete_yacht() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
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
        
        if (!current_user_can('edit_posts')) {
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
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $yacht_id = intval($_POST['yacht_id']);
        $table_name = $wpdb->prefix . 'yolo_bm_equipment_categories';
        
        $categories = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE yacht_id = %s ORDER BY category_name ASC",
            $yacht_id
        ));
        
        wp_send_json_success($categories);
    }

    /**
     * AJAX: Delete equipment category
     */
    public function ajax_delete_equipment_category() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_equipment_categories';
        $category_id = intval($_POST['category_id']);
        
        $result = $wpdb->delete($table_name, array('id' => $category_id));
        
        if ($result) {
            wp_send_json_success(array('message' => 'Category deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete category'));
        }
    }

    /**
     * AJAX: Get check-ins list
     */
    public function ajax_get_checkins() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_checkins = $wpdb->prefix . 'yolo_bm_checkins';
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        $table_yachts = $wpdb->prefix . 'yolo_bm_yachts';
        
        $checkins = $wpdb->get_results("
            SELECT 
                c.*,
                b.customer_name,
                b.customer_email,
                b.yacht_name as booking_yacht_name,
                b.date_from,
                b.date_to,
                y.yacht_name as managed_yacht_name
            FROM {$table_checkins} c
            LEFT JOIN {$table_bookings} b ON c.booking_id = b.id
            LEFT JOIN {$table_yachts} y ON c.yacht_id = y.id
            ORDER BY c.created_at DESC
            LIMIT 50
        ");
        
        wp_send_json_success($checkins);
    }

    /**
     * AJAX: Save check-in
     */
    public function ajax_save_checkin() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
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
     * AJAX: Get check-outs list
     */
    public function ajax_get_checkouts() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_checkouts = $wpdb->prefix . 'yolo_bm_checkouts';
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        $table_yachts = $wpdb->prefix . 'yolo_bm_yachts';
        
        $checkouts = $wpdb->get_results("
            SELECT 
                c.*,
                b.customer_name,
                b.customer_email,
                b.yacht_name as booking_yacht_name,
                b.date_from,
                b.date_to,
                y.yacht_name as managed_yacht_name
            FROM {$table_checkouts} c
            LEFT JOIN {$table_bookings} b ON c.booking_id = b.id
            LEFT JOIN {$table_yachts} y ON c.yacht_id = y.id
            ORDER BY c.created_at DESC
            LIMIT 50
        ");
        
        wp_send_json_success($checkouts);
    }

    /**
     * AJAX: Save check-out
     */
    public function ajax_save_checkout() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
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
        
        if (!current_user_can('edit_posts')) {
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
        
        if (!current_user_can('edit_posts')) {
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
        $booking_ref = !empty($booking->bm_reservation_id) ? $booking->bm_reservation_id : $booking->id;
        $subject = 'YOLO Charters - ' . ucfirst($type) . ' Document for Booking #' . $booking_ref;
        
        $message = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $message .= '<div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">';
        $message .= '<h2 style="color: #1e3a8a; text-align: center;">YOLO Charters</h2>';
        $message .= '<h3 style="color: #495057;">Yacht ' . ucfirst($type) . ' Document</h3>';
        $message .= '<p>Dear ' . esc_html($booking->customer_name) . ',</p>';
        $message .= '<p>Your yacht ' . $type . ' document for booking #' . $booking_ref . ' is ready for your review and signature.</p>';
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
     * AJAX: Upload document (for Base Manager to send arbitrary documents to guests)
     */
    public function ajax_upload_document() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => 'No file uploaded or upload error'));
            return;
        }
        
        $booking_id = intval($_POST['booking_id']);
        $document_type = sanitize_text_field($_POST['document_type']); // 'checkin', 'checkout', or 'other'
        $document_title = sanitize_text_field($_POST['document_title']);
        
        // Validate booking exists
        global $wpdb;
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking) {
            wp_send_json_error(array('message' => 'Booking not found'));
            return;
        }
        
        // Handle file upload
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $file = $_FILES['document'];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $file_url = $movefile['url'];
            
            // Store document reference (could be in a custom table or as post meta)
            // For now, just return the URL and let the frontend handle it
            
            wp_send_json_success(array(
                'message' => 'Document uploaded successfully',
                'file_url' => $file_url,
                'file_name' => basename($movefile['file'])
            ));
        } else {
            wp_send_json_error(array('message' => 'Upload failed: ' . $movefile['error']));
        }
    }

    /**
     * AJAX: Save warehouse item
     */
    public function ajax_save_warehouse_item() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
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
        $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'other';
        $unit = isset($_POST['unit']) ? sanitize_text_field($_POST['unit']) : 'pcs';
        
        // Notification settings
        $enable_notifications = isset($_POST['enable_notifications']) ? intval($_POST['enable_notifications']) : 0;
        $notify_days_before = isset($_POST['notify_days_before']) ? intval($_POST['notify_days_before']) : 7;
        $notify_email = isset($_POST['notify_email']) ? intval($_POST['notify_email']) : 0;
        $notify_dashboard = isset($_POST['notify_dashboard']) ? intval($_POST['notify_dashboard']) : 0;
        $notify_viber = isset($_POST['notify_viber']) ? intval($_POST['notify_viber']) : 0;
        $notify_recipients = isset($_POST['notify_recipients']) ? $_POST['notify_recipients'] : array();
        
        $data = array(
            'yacht_id' => $yacht_id,
            'item_name' => $item_name,
            'quantity' => $quantity,
            'expiry_date' => $expiry_date ? $expiry_date : null,
            'location' => $location,
            'category' => $category,
            'unit' => $unit,
            'updated_at' => current_time('mysql'),
        );
        
        // Store notification settings as JSON in a meta field
        $notification_settings = array(
            'enabled' => $enable_notifications,
            'days_before' => $notify_days_before,
            'methods' => array(
                'email' => $notify_email,
                'dashboard' => $notify_dashboard,
                'viber' => $notify_viber
            ),
            'recipients' => is_array($notify_recipients) ? $notify_recipients : array()
        );
        $data['notification_settings'] = json_encode($notification_settings);
        
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
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_warehouse';
        
        // Get all warehouse items (filter by yacht_id if provided)
        $yacht_id = isset($_POST['yacht_id']) ? intval($_POST['yacht_id']) : 0;
        
        if ($yacht_id > 0) {
            $items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table_name WHERE yacht_id = %s ORDER BY item_name ASC",
                $yacht_id
            ));
        } else {
            $items = $wpdb->get_results("SELECT * FROM $table_name ORDER BY yacht_id ASC, item_name ASC");
        }
        
        wp_send_json_success(array('items' => $items));
    }

    /**
     * AJAX: Delete warehouse item
     */
    public function ajax_delete_warehouse_item() {
        check_ajax_referer('yolo_base_manager_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $item_id = intval($_POST['item_id']);
        
        $result = $wpdb->delete(
            $wpdb->prefix . 'yolo_bm_warehouse',
            array('id' => $item_id),
            array('%d')
        );
        
        if ($result) {
            wp_send_json_success(array('message' => 'Item deleted successfully'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete item'));
        }
    }

    /**
     * AJAX: Get bookings calendar
     */
    public function ajax_get_bookings_calendar() {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM v41.4: ajax_get_bookings_calendar called');
        }
        
        // Verify nonce
        if (!check_ajax_referer('yolo_base_manager_nonce', 'nonce', false)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Nonce verification FAILED for bookings');
            }
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page.'));
            return;
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Nonce verified OK for bookings');
        }
        
        if (!current_user_can('edit_posts')) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Permission denied for ajax_get_bookings_calendar');
            }
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Permission check passed for bookings');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bookings';
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Querying bookings table: ' . $table_name);
        }
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        if ($table_exists != $table_name) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Table ' . $table_name . ' does NOT exist!');
            }
            wp_send_json_error(array('message' => 'Bookings table not found. Please sync yachts first.'));
            return;
        }
        
        $bookings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_from ASC");
        
        if ($wpdb->last_error) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO BM: Database error: ' . $wpdb->last_error);
            }
            wp_send_json_error(array('message' => 'Database error: ' . $wpdb->last_error));
            return;
        }
        
        $count = is_array($bookings) ? count($bookings) : 0;
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('YOLO BM: Found ' . $count . ' bookings');
            if ($count > 0) {
                error_log('YOLO BM: First booking: ' . print_r($bookings[0], true));
            }
        }
        
        wp_send_json_success($bookings);
    }

    /**
     * AJAX: Guest sign document
     */
    public function ajax_guest_sign_document() {
        // Verify guest-specific nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'yolo_guest_document_nonce')) {
            wp_send_json_error(array('message' => 'Security verification failed'));
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Please log in'));
            return;
        }
        
        $type = sanitize_text_field($_POST['document_type']);
        $document_id = intval($_POST['document_id']);
        $signature = $_POST['signature']; // Base64 encoded image, sanitized below
        
        // Verify signature is valid base64 image
        if (!preg_match('/^data:image\/png;base64,/', $signature)) {
            wp_send_json_error(array('message' => 'Invalid signature format'));
            return;
        }
        
        global $wpdb;
        $table_name = $type === 'checkin' ? $wpdb->prefix . 'yolo_bm_checkins' : $wpdb->prefix . 'yolo_bm_checkouts';
        
        // Verify document exists and belongs to user's booking
        $document = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $document_id
        ));
        
        if (!$document) {
            wp_send_json_error(array('message' => 'Document not found'));
            return;
        }
        
        // Verify user owns the booking (check both user_id AND email for consistency)
        $user = wp_get_current_user();
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d AND (user_id = %d OR customer_email = %s)",
            $document->booking_id,
            $user->ID,
            $user->user_email
        ));
        
        if (!$booking) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO YS Sign Doc: Permission denied for user ' . $user->ID . ' (email: ' . $user->user_email . ') on booking ' . $document->booking_id);
            }
            wp_send_json_error(array('message' => 'Permission denied - booking not found for your account'));
            return;
        }
        
        // Update document with guest signature
        $result = $wpdb->update(
            $table_name,
            array(
                'guest_signature' => $signature,
                'guest_signed_at' => current_time('mysql'),
                'status' => 'signed',
            ),
            array('id' => $document_id)
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Failed to save signature'));
            return;
        }
        
        // Regenerate PDF with guest signature
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-pdf-generator.php';
        if ($type === 'checkin') {
            YOLO_YS_PDF_Generator::generate_checkin_pdf($document_id);
        } else {
            YOLO_YS_PDF_Generator::generate_checkout_pdf($document_id);
        }
        
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
    
    /**
     * Ensure Base Manager tables exist
     * ADDED in v17.13 fix - prevents empty dropdown issue
     */
    public function ensure_tables_exist() {
        global $wpdb;
        $table_yachts = $wpdb->prefix . 'yolo_bm_yachts';
        
        // Only run if tables don't exist
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_yachts'") != $table_yachts) {
            require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-base-manager-database.php';
            YOLO_YS_Base_Manager_Database::create_tables();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('YOLO YS v17.13: Base Manager tables auto-created');
            }
        }
    }
}

