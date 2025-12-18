<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contact Messages Management Class
 *
 * Handles contact form submissions, database storage, and notifications
 *
 * @package YOLO_Yacht_Search
 * @subpackage Includes
 * @since 17.5
 */

class YOLO_YS_Contact_Messages {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Create database table
        add_action('init', array($this, 'maybe_create_table'));
        
        // Register shortcode
        add_shortcode('yolo_contact_form', array($this, 'render_contact_form'));
        
        // AJAX handlers
        add_action('wp_ajax_yolo_submit_contact_form', array($this, 'ajax_submit_contact_form'));
        add_action('wp_ajax_nopriv_yolo_submit_contact_form', array($this, 'ajax_submit_contact_form'));
        add_action('wp_ajax_yolo_update_message_status', array($this, 'ajax_update_message_status'));
        add_action('wp_ajax_yolo_delete_contact_message', array($this, 'ajax_delete_contact_message'));
        add_action('wp_ajax_yolo_save_message_notes', array($this, 'ajax_save_message_notes'));
        add_action('wp_ajax_yolo_clear_contact_notifications', array($this, 'ajax_clear_contact_notifications'));
        
        // Admin bar notification
        add_action('admin_bar_menu', array($this, 'add_admin_bar_notification'), 999);
        
        // Enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'enqueue_notification_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    }
    
    /**
     * Create database table if not exists
     */
    public function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                contact_name varchar(255) NOT NULL,
                contact_email varchar(255) NOT NULL,
                contact_phone varchar(50) DEFAULT NULL,
                contact_subject varchar(500) NOT NULL,
                contact_message text NOT NULL,
                status varchar(50) DEFAULT 'new',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                viewed_by varchar(255) DEFAULT NULL,
                notes text DEFAULT NULL,
                PRIMARY KEY  (id),
                KEY status (status),
                KEY created_at (created_at),
                KEY contact_email (contact_email)
            ) $charset_collate;";
            
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }
    
    /**
     * Render contact form shortcode
     */
    public function render_contact_form($atts) {
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/partials/contact-form.php';
        return ob_get_clean();
    }
    
    /**
     * Handle contact form submission via AJAX
     */
    public function ajax_submit_contact_form() {
        check_ajax_referer('yolo_contact_form', 'nonce');
        
        // Validate input
        $errors = array();
        
        $contact_name = sanitize_text_field($_POST['contact_name'] ?? '');
        if (empty($contact_name)) {
            $errors['contact_name'] = 'Name is required.';
        }
        
        $contact_email = sanitize_email($_POST['contact_email'] ?? '');
        if (empty($contact_email) || !is_email($contact_email)) {
            $errors['contact_email'] = 'Valid email is required.';
        }
        
        $contact_phone = sanitize_text_field($_POST['contact_phone'] ?? '');
        
        $contact_subject = sanitize_text_field($_POST['contact_subject'] ?? '');
        if (empty($contact_subject)) {
            $errors['contact_subject'] = 'Subject is required.';
        }
        
        $contact_message = sanitize_textarea_field($_POST['contact_message'] ?? '');
        if (empty($contact_message)) {
            $errors['contact_message'] = 'Message is required.';
        }
        
        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => 'Please fix the errors and try again.',
                'errors' => $errors
            ));
        }
        
        // Save to database
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'contact_name' => $contact_name,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone,
                'contact_subject' => $contact_subject,
                'contact_message' => $contact_message,
                'status' => 'new'
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            wp_send_json_error(array(
                'message' => 'Failed to save your message. Please try again.'
            ));
        }
        
        $message_id = $wpdb->insert_id;
        
        // Trigger CRM integration (v71.0)
        $crm_data = array(
            'email' => $contact_email,
            'first_name' => explode(' ', $contact_name)[0],
            'last_name' => implode(' ', array_slice(explode(' ', $contact_name), 1)),
            'phone' => $contact_phone,
            'subject' => $contact_subject,
            'message' => $contact_message
        );
        do_action('yolo_contact_message_submitted', $message_id, $crm_data);
        
        // Trigger notifications
        $this->trigger_notifications($message_id, $contact_name, $contact_subject);
        
        wp_send_json_success(array(
            'message' => 'Thank you for your message! We will get back to you soon.',
            'message_id' => $message_id
        ));
    }
    
    /**
     * Trigger notifications for new contact message
     */
    private function trigger_notifications($message_id, $contact_name, $subject) {
        $users = $this->get_notification_users();
        
        foreach ($users as $user_id) {
            // Increment unread count
            $unread = get_user_meta($user_id, 'yolo_unread_contacts', true);
            $unread = $unread ? intval($unread) + 1 : 1;
            update_user_meta($user_id, 'yolo_unread_contacts', $unread);
            
            // Add pending notification
            $notifications = get_user_meta($user_id, 'yolo_pending_contact_notifications', true);
            if (!is_array($notifications)) {
                $notifications = array();
            }
            
            $notifications[] = array(
                'message_id' => $message_id,
                'contact_name' => $contact_name,
                'subject' => $subject,
                'timestamp' => current_time('timestamp')
            );
            
            update_user_meta($user_id, 'yolo_pending_contact_notifications', $notifications);
        }
    }
    
    /**
     * Get list of users who should receive notifications
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
            $notify_enabled = get_user_meta($bm->ID, 'yolo_contact_notifications_enabled', true);
            if ($notify_enabled === '1' || $notify_enabled === true) {
                $users[] = $bm->ID;
            }
        }
        
        return array_unique($users);
    }
    
    /**
     * Update message status via AJAX
     */
    public function ajax_update_message_status() {
        check_ajax_referer('yolo_contact_notifications', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $message_id = intval($_POST['message_id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        
        if (!$message_id || !in_array($status, array('new', 'reviewed', 'responded'))) {
            wp_send_json_error('Invalid parameters');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $message_id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to update status');
        }
        
        wp_send_json_success();
    }
    
    /**
     * Delete contact message via AJAX (admin only)
     */
    public function ajax_delete_contact_message() {
        check_ajax_referer('yolo_contact_notifications', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $message_id = intval($_POST['message_id'] ?? 0);
        
        if (!$message_id) {
            wp_send_json_error('Invalid message ID');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $message_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to delete message');
        }
        
        wp_send_json_success();
    }
    
    /**
     * Save message notes via AJAX
     */
    public function ajax_save_message_notes() {
        check_ajax_referer('yolo_contact_notifications', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $message_id = intval($_POST['message_id'] ?? 0);
        $notes = sanitize_textarea_field($_POST['notes'] ?? '');
        
        if (!$message_id) {
            wp_send_json_error('Invalid message ID');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        $result = $wpdb->update(
            $table_name,
            array('notes' => $notes),
            array('id' => $message_id),
            array('%s'),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error('Failed to save notes');
        }
        
        wp_send_json_success();
    }
    
    /**
     * Clear pending contact notifications via AJAX
     */
    public function ajax_clear_contact_notifications() {
        check_ajax_referer('yolo_contact_notifications', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $user_id = get_current_user_id();
        delete_user_meta($user_id, 'yolo_pending_contact_notifications');
        
        wp_send_json_success();
    }
    
    /**
     * Add notification badge to admin bar
     */
    public function add_admin_bar_notification($wp_admin_bar) {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        $user_id = get_current_user_id();
        $unread_count = get_user_meta($user_id, 'yolo_unread_contacts', true);
        $unread_count = $unread_count ? intval($unread_count) : 0;
        
        if ($unread_count > 0) {
            $wp_admin_bar->add_node(array(
                'id' => 'yolo-contact-notifications',
                'title' => '<span class="ab-icon dashicons dashicons-email"></span><span class="yolo-notification-badge">' . $unread_count . '</span>',
                'href' => admin_url('admin.php?page=yolo-contact-messages'),
                'meta' => array(
                    'class' => 'yolo-admin-bar-notification',
                    'title' => sprintf(_n('%d new contact message', '%d new contact messages', $unread_count, 'yolo-yacht-search'), $unread_count)
                )
            ));
        }
    }
    
    /**
     * Enqueue notification scripts for admin
     */
    public function enqueue_notification_scripts($hook) {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        // Use same CSS and JS as quote notifications
        wp_enqueue_style('yolo-contact-notifications', YOLO_YS_PLUGIN_URL . 'admin/css/quote-notifications.css', array(), YOLO_YS_VERSION);
        wp_enqueue_script('yolo-contact-notifications', YOLO_YS_PLUGIN_URL . 'admin/js/contact-notifications.js', array('jquery'), YOLO_YS_VERSION, true);
        
        // Localize script
        $user_id = get_current_user_id();
        $pending_notifications = get_user_meta($user_id, 'yolo_pending_contact_notifications', true);
        
        wp_localize_script('yolo-contact-notifications', 'yoloContactNotifications', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yolo_contact_notifications'),
            'pendingNotifications' => is_array($pending_notifications) ? $pending_notifications : array(),
            'messagesUrl' => admin_url('admin.php?page=yolo-contact-messages')
        ));
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        // Contact Form 7 CSS is already loaded, no need to duplicate
    }
    
    /**
     * Get all contact messages
     */
    public static function get_all_messages($status = 'all', $limit = 100, $offset = 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        $where = '';
        if ($status !== 'all') {
            $where = $wpdb->prepare("WHERE status = %s", $status);
        }
        
        $sql = "SELECT * FROM $table_name $where ORDER BY created_at DESC LIMIT %d OFFSET %d";
        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset), ARRAY_A);
    }
    
    /**
     * Get single contact message by ID
     */
    public static function get_message_by_id($message_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $message_id
        ), ARRAY_A);
    }
    
    /**
     * Mark message as viewed
     */
    public static function mark_as_viewed($message_id, $user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_contact_messages';
        
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        
        // Update viewed_by
        $wpdb->update(
            $table_name,
            array('viewed_by' => $user->display_name),
            array('id' => $message_id),
            array('%s'),
            array('%d')
        );
        
        // Decrement unread count
        $unread = get_user_meta($user_id, 'yolo_unread_contacts', true);
        $unread = max(0, intval($unread) - 1);
        update_user_meta($user_id, 'yolo_unread_contacts', $unread);
        
        return true;
    }
}
