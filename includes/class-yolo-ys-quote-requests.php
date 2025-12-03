<?php
/**
 * Quote Requests Management
 *
 * Handles quote request storage, notifications, and management
 *
 * @package YOLO_Yacht_Search
 * @subpackage Quote_Requests
 * @since 17.4
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Quote_Requests {

    /**
     * Initialize the quote requests system
     */
    public function __construct() {
        // Create database table on activation
        add_action('init', array($this, 'maybe_create_table'));
        
        // AJAX handlers
        add_action('wp_ajax_yolo_save_quote_request', array($this, 'ajax_save_quote_request'));
        add_action('wp_ajax_nopriv_yolo_save_quote_request', array($this, 'ajax_save_quote_request'));
        add_action('wp_ajax_yolo_update_quote_status', array($this, 'ajax_update_quote_status'));
        add_action('wp_ajax_yolo_delete_quote_request', array($this, 'ajax_delete_quote_request'));
        add_action('wp_ajax_yolo_save_quote_notes', array($this, 'ajax_save_quote_notes'));
        add_action('wp_ajax_yolo_clear_pending_notifications', array($this, 'ajax_clear_pending_notifications'));
        add_action('wp_ajax_yolo_update_notification_preference', array($this, 'ajax_update_notification_preference'));
        
        // Admin bar notification
        add_action('admin_bar_menu', array($this, 'add_admin_bar_notification'), 999);
        
        // Enqueue notification scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_notification_scripts'));
    }

    /**
     * Create quote requests table
     */
    public function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        // Check if table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                customer_name varchar(255) NOT NULL,
                customer_email varchar(255) NOT NULL,
                customer_phone varchar(50) DEFAULT NULL,
                yacht_preference varchar(255) DEFAULT NULL,
                checkin_date date DEFAULT NULL,
                checkout_date date DEFAULT NULL,
                num_guests int(11) DEFAULT NULL,
                special_requests text DEFAULT NULL,
                status varchar(50) DEFAULT 'new',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                viewed_by varchar(255) DEFAULT NULL,
                notes text DEFAULT NULL,
                PRIMARY KEY (id),
                KEY status (status),
                KEY created_at (created_at)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
            
            error_log('YOLO YS: Quote requests table created');
        }
    }

    /**
     * Save quote request (AJAX handler)
     */
    public function ajax_save_quote_request() {
        // Verify nonce for logged-in users, allow public submissions
        if (is_user_logged_in()) {
            check_ajax_referer('yolo_quote_request_nonce', 'nonce');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        // Sanitize input
        $data = array(
            'customer_name' => sanitize_text_field($_POST['customer_name']),
            'customer_email' => sanitize_email($_POST['customer_email']),
            'customer_phone' => sanitize_text_field($_POST['customer_phone']),
            'yacht_preference' => sanitize_text_field($_POST['yacht_preference']),
            'checkin_date' => sanitize_text_field($_POST['checkin_date']),
            'checkout_date' => sanitize_text_field($_POST['checkout_date']),
            'num_guests' => intval($_POST['num_guests']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests']),
            'status' => 'new'
        );
        
        // Insert into database
        $result = $wpdb->insert($table_name, $data);
        
        if ($result) {
            $quote_id = $wpdb->insert_id;
            
            // Trigger notifications
            $this->trigger_notifications($quote_id, $data);
            
            wp_send_json_success(array(
                'message' => 'Quote request submitted successfully!',
                'quote_id' => $quote_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Failed to save quote request. Please try again.'
            ));
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

    /**
     * Add notification badge to admin bar
     */
    public function add_admin_bar_notification($wp_admin_bar) {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        $user_id = get_current_user_id();
        $unread_count = get_user_meta($user_id, 'yolo_unread_quotes', true);
        $unread_count = intval($unread_count);
        
        if ($unread_count > 0) {
            $wp_admin_bar->add_node(array(
                'id' => 'yolo-quote-notifications',
                'title' => '<span class="ab-icon dashicons dashicons-email-alt"></span> <span class="yolo-quote-badge">' . $unread_count . '</span> New Quote' . ($unread_count > 1 ? 's' : ''),
                'href' => admin_url('admin.php?page=yolo-quote-requests'),
                'meta' => array(
                    'class' => 'yolo-quote-notification-item'
                )
            ));
        }
    }

    /**
     * Enqueue notification scripts
     */
    public function enqueue_notification_scripts($hook) {
        wp_enqueue_style(
            'yolo-quote-notifications',
            YOLO_YS_PLUGIN_URL . 'admin/css/quote-notifications.css',
            array(),
            YOLO_YS_VERSION
        );
        
        wp_enqueue_script(
            'yolo-quote-notifications',
            YOLO_YS_PLUGIN_URL . 'admin/js/quote-notifications.js',
            array('jquery'),
            YOLO_YS_VERSION,
            true
        );
        
        // Localize script with notification data
        $user_id = get_current_user_id();
        $pending_notifications = get_user_meta($user_id, 'yolo_pending_notifications', true);
        $push_enabled = get_user_meta($user_id, 'yolo_push_notifications_enabled', true);
        
        wp_localize_script('yolo-quote-notifications', 'yoloQuoteNotifications', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yolo_quote_notifications'),
            'pending_notifications' => is_array($pending_notifications) ? $pending_notifications : array(),
            'push_enabled' => $push_enabled === '1' || $push_enabled === true,
            'quotes_url' => admin_url('admin.php?page=yolo-quote-requests')
        ));
    }

    /**
     * Update quote status (AJAX handler)
     */
    public function ajax_update_quote_status() {
        check_ajax_referer('yolo_quote_notifications', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        $quote_id = intval($_POST['quote_id']);
        $status = sanitize_text_field($_POST['status']);
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $quote_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Status updated'));
        } else {
            wp_send_json_error(array('message' => 'Failed to update status'));
        }
    }

    /**
     * Delete quote request (AJAX handler)
     */
    public function ajax_delete_quote_request() {
        check_ajax_referer('yolo_quote_notifications', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        $quote_id = intval($_POST['quote_id']);
        
        $result = $wpdb->delete($table_name, array('id' => $quote_id), array('%d'));
        
        if ($result) {
            wp_send_json_success(array('message' => 'Quote request deleted'));
        } else {
            wp_send_json_error(array('message' => 'Failed to delete quote request'));
        }
    }
    
    /**
     * Save quote notes (AJAX handler)
     */
    public function ajax_save_quote_notes() {
        check_ajax_referer('yolo_quote_notifications', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        $quote_id = intval($_POST['quote_id']);
        $notes = sanitize_textarea_field($_POST['notes']);
        
        $result = $wpdb->update(
            $table_name,
            array('notes' => $notes),
            array('id' => $quote_id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Notes saved'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save notes'));
        }
    }

    /**
     * Get all quote requests
     */
    public static function get_all_quotes($status = 'all', $limit = 100, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        if ($status === 'all') {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $limit,
                $offset
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE status = %s ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $status,
                $limit,
                $offset
            );
        }
        
        return $wpdb->get_results($sql, ARRAY_A);
    }

    /**
     * Get quote request by ID
     */
    public static function get_quote_by_id($quote_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $quote_id),
            ARRAY_A
        );
    }

    /**
     * Mark quote as viewed
     */
    public static function mark_as_viewed($quote_id, $user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_quote_requests';
        
        $current_user = get_userdata($user_id);
        $viewed_by = $current_user->display_name;
        
        $wpdb->update(
            $table_name,
            array('viewed_by' => $viewed_by),
            array('id' => $quote_id),
            array('%s'),
            array('%d')
        );
        
        // Decrement unread count for this user
        $unread_count = get_user_meta($user_id, 'yolo_unread_quotes', true);
        $new_count = max(0, intval($unread_count) - 1);
        update_user_meta($user_id, 'yolo_unread_quotes', $new_count);
    }
    
    /**
     * Clear pending notifications (AJAX handler)
     */
    public function ajax_clear_pending_notifications() {
        check_ajax_referer('yolo_quote_notifications', 'nonce');
        
        $user_id = get_current_user_id();
        delete_user_meta($user_id, 'yolo_pending_notifications');
        
        wp_send_json_success();
    }
    
    /**
     * Update notification preference (AJAX handler)
     */
    public function ajax_update_notification_preference() {
        check_ajax_referer('yolo_quote_notifications', 'nonce');
        
        $user_id = get_current_user_id();
        $enabled = intval($_POST['enabled']);
        
        update_user_meta($user_id, 'yolo_push_notifications_enabled', $enabled);
        
        wp_send_json_success();
    }
}
