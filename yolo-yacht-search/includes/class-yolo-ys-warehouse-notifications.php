<?php
/**
 * Warehouse Expiry Notifications Handler
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.11
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Warehouse_Notifications {
    
    /**
     * Initialize the class
     */
    public function __construct() {
        // Schedule daily cron job for checking expiry notifications
        add_action('init', array($this, 'schedule_expiry_check'));
        add_action('yolo_warehouse_expiry_check', array($this, 'check_and_send_notifications'));
        
        // Add dashboard widget for expiring items
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
    }
    
    /**
     * Schedule the daily expiry check cron job
     */
    public function schedule_expiry_check() {
        if (!wp_next_scheduled('yolo_warehouse_expiry_check')) {
            wp_schedule_event(time(), 'daily', 'yolo_warehouse_expiry_check');
        }
    }
    
    /**
     * Check warehouse items and send notifications for expiring items
     */
    public function check_and_send_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_warehouse';
        
        // Get all items with expiry dates and notification settings
        $items = $wpdb->get_results(
            "SELECT * FROM $table_name 
            WHERE expiry_date IS NOT NULL 
            AND notification_settings IS NOT NULL 
            ORDER BY expiry_date ASC"
        );
        
        if (empty($items)) {
            return;
        }
        
        $today = new DateTime();
        $notifications_sent = 0;
        
        foreach ($items as $item) {
            // Parse notification settings
            $settings = json_decode($item->notification_settings, true);
            
            // Skip if notifications are not enabled
            if (empty($settings['enabled'])) {
                continue;
            }
            
            // Calculate days until expiry
            $expiry_date = new DateTime($item->expiry_date);
            $interval = $today->diff($expiry_date);
            $days_until_expiry = $interval->invert ? -$interval->days : $interval->days;
            
            // Check if we should send notification
            $notify_days_before = isset($settings['days_before']) ? intval($settings['days_before']) : 7;
            
            // Send notification if within the notification window
            if ($days_until_expiry <= $notify_days_before && $days_until_expiry >= 0) {
                // Check if we already sent notification today
                $last_notification = get_transient('yolo_warehouse_notification_' . $item->id);
                
                if (!$last_notification) {
                    $this->send_notification($item, $days_until_expiry, $settings);
                    
                    // Set transient to prevent duplicate notifications today
                    set_transient('yolo_warehouse_notification_' . $item->id, time(), DAY_IN_SECONDS);
                    
                    $notifications_sent++;
                }
            }
        }
        
        error_log("YOLO YS: Warehouse expiry check completed. Sent $notifications_sent notifications.");
    }
    
    /**
     * Send notification for expiring item
     */
    private function send_notification($item, $days_until_expiry, $settings) {
        // Get yacht name
        global $wpdb;
        $yacht = $wpdb->get_row($wpdb->prepare(
            "SELECT yacht_name FROM {$wpdb->prefix}yolo_bm_yachts WHERE id = %d",
            $item->yacht_id
        ));
        $yacht_name = $yacht ? $yacht->yacht_name : 'Unknown Yacht';
        
        // Prepare notification message
        $urgency = $days_until_expiry <= 3 ? '🚨 URGENT' : '⚠️ REMINDER';
        
        if ($days_until_expiry == 0) {
            $time_message = 'expires TODAY';
        } elseif ($days_until_expiry == 1) {
            $time_message = 'expires TOMORROW';
        } else {
            $time_message = "expires in $days_until_expiry days";
        }
        
        $subject = "$urgency: Warehouse Item Expiring - {$item->item_name}";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 8px 8px 0 0; }
                .header h1 { margin: 0; font-size: 24px; }
                .content { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
                .alert-box { background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; border-radius: 4px; }
                .alert-box.warning { background: #fef3c7; border-left-color: #f59e0b; }
                .item-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
                .detail-label { font-weight: 600; width: 150px; color: #667eea; }
                .detail-value { flex: 1; }
                .footer { background: #374151; color: white; padding: 20px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>$urgency: Warehouse Item Expiring</h1>
                </div>
                <div class='content'>
                    <div class='alert-box" . ($days_until_expiry <= 3 ? "" : " warning") . "'>
                        <strong>$time_message</strong> on {$item->expiry_date}
                    </div>
                    
                    <div class='item-details'>
                        <div class='detail-row'>
                            <div class='detail-label'>Item Name:</div>
                            <div class='detail-value'><strong>{$item->item_name}</strong></div>
                        </div>
                        <div class='detail-row'>
                            <div class='detail-label'>Yacht:</div>
                            <div class='detail-value'>$yacht_name</div>
                        </div>
                        <div class='detail-row'>
                            <div class='detail-label'>Category:</div>
                            <div class='detail-value'>{$item->category}</div>
                        </div>
                        <div class='detail-row'>
                            <div class='detail-label'>Quantity:</div>
                            <div class='detail-value'>{$item->quantity} {$item->unit}</div>
                        </div>
                        <div class='detail-row'>
                            <div class='detail-label'>Location:</div>
                            <div class='detail-value'>{$item->location}</div>
                        </div>
                        <div class='detail-row'>
                            <div class='detail-label'>Expiry Date:</div>
                            <div class='detail-value'><strong style='color: #dc2626;'>{$item->expiry_date}</strong></div>
                        </div>
                    </div>
                    
                    <p><strong>Action Required:</strong></p>
                    <ul>
                        <li>Check the item's condition</li>
                        <li>Replace or restock if necessary</li>
                        <li>Update the warehouse inventory</li>
                        <li>Dispose of expired items properly</li>
                    </ul>
                    
                    <a href='" . admin_url('admin.php?page=yolo-warehouse') . "' class='button'>View Warehouse Management</a>
                </div>
                <div class='footer'>
                    <p>This is an automated notification from YOLO Yacht Search - Warehouse Management System</p>
                    <p>You are receiving this because you are listed as a recipient for warehouse expiry notifications.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Send email notifications
        if (!empty($settings['methods']['email'])) {
            $recipients = isset($settings['recipients']) ? $settings['recipients'] : array();
            
            if (!empty($recipients)) {
                foreach ($recipients as $user_id) {
                    $user = get_userdata($user_id);
                    if ($user) {
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        wp_mail($user->user_email, $subject, $message, $headers);
                        error_log("YOLO YS: Expiry notification sent to {$user->user_email} for item: {$item->item_name}");
                    }
                }
            }
        }
        
        // Store dashboard notification
        if (!empty($settings['methods']['dashboard'])) {
            $this->store_dashboard_notification($item, $days_until_expiry, $yacht_name, $settings);
        }
        
        // TODO: Implement Viber notifications when API is available
        if (!empty($settings['methods']['viber'])) {
            error_log("YOLO YS: Viber notification requested for item: {$item->item_name} (not yet implemented)");
        }
    }
    
    /**
     * Store dashboard notification
     */
    private function store_dashboard_notification($item, $days_until_expiry, $yacht_name, $settings) {
        $notifications = get_option('yolo_warehouse_dashboard_notifications', array());
        
        $notification = array(
            'item_id' => $item->id,
            'item_name' => $item->item_name,
            'yacht_name' => $yacht_name,
            'expiry_date' => $item->expiry_date,
            'days_until_expiry' => $days_until_expiry,
            'location' => $item->location,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'timestamp' => current_time('mysql'),
            'read' => false
        );
        
        // Add to beginning of array
        array_unshift($notifications, $notification);
        
        // Keep only last 50 notifications
        $notifications = array_slice($notifications, 0, 50);
        
        update_option('yolo_warehouse_dashboard_notifications', $notifications);
        
        // Notify recipients
        $recipients = isset($settings['recipients']) ? $settings['recipients'] : array();
        foreach ($recipients as $user_id) {
            $user_notifications = get_user_meta($user_id, 'yolo_warehouse_notifications', true);
            if (!is_array($user_notifications)) {
                $user_notifications = array();
            }
            array_unshift($user_notifications, $notification);
            $user_notifications = array_slice($user_notifications, 0, 20);
            update_user_meta($user_id, 'yolo_warehouse_notifications', $user_notifications);
        }
    }
    
    /**
     * Add dashboard widget for expiring items
     */
    public function add_dashboard_widget() {
        // Only show to users who can manage base operations or are admins
        if (current_user_can('manage_base_operations') || current_user_can('manage_options')) {
            wp_add_dashboard_widget(
                'yolo_warehouse_expiring_items',
                '⚠️ Warehouse Items Expiring Soon',
                array($this, 'render_dashboard_widget')
            );
        }
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_bm_warehouse';
        
        // Get items expiring in the next 30 days
        $items = $wpdb->get_results(
            "SELECT w.*, y.yacht_name 
            FROM $table_name w
            LEFT JOIN {$wpdb->prefix}yolo_bm_yachts y ON w.yacht_id = y.id
            WHERE w.expiry_date IS NOT NULL 
            AND w.expiry_date >= CURDATE()
            AND w.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
            ORDER BY w.expiry_date ASC
            LIMIT 10"
        );
        
        if (empty($items)) {
            echo '<p style="color: #00a32a;">✅ No items expiring in the next 30 days.</p>';
            return;
        }
        
        echo '<style>
            .yolo-expiry-widget { font-size: 13px; }
            .yolo-expiry-item { padding: 12px; margin-bottom: 10px; border-left: 4px solid #f59e0b; background: #fef3c7; border-radius: 4px; }
            .yolo-expiry-item.urgent { border-left-color: #dc2626; background: #fee2e2; }
            .yolo-expiry-item-title { font-weight: 600; color: #1f2937; margin-bottom: 4px; }
            .yolo-expiry-item-details { color: #6b7280; font-size: 12px; }
            .yolo-expiry-days { font-weight: 600; color: #dc2626; }
        </style>';
        
        echo '<div class="yolo-expiry-widget">';
        
        foreach ($items as $item) {
            $expiry_date = new DateTime($item->expiry_date);
            $today = new DateTime();
            $interval = $today->diff($expiry_date);
            $days_until_expiry = $interval->days;
            
            $urgent_class = $days_until_expiry <= 7 ? ' urgent' : '';
            
            if ($days_until_expiry == 0) {
                $time_text = '🚨 Expires TODAY';
            } elseif ($days_until_expiry == 1) {
                $time_text = '⚠️ Expires TOMORROW';
            } else {
                $time_text = "⚠️ Expires in <span class='yolo-expiry-days'>$days_until_expiry days</span>";
            }
            
            echo '<div class="yolo-expiry-item' . $urgent_class . '">';
            echo '<div class="yolo-expiry-item-title">' . esc_html($item->item_name) . '</div>';
            echo '<div class="yolo-expiry-item-details">';
            echo '🚤 ' . esc_html($item->yacht_name) . ' | ';
            echo '📍 ' . esc_html($item->location) . '<br>';
            echo $time_text . ' (' . esc_html($item->expiry_date) . ')';
            echo '</div>';
            echo '</div>';
        }
        
        echo '<p style="text-align: center; margin-top: 15px;">';
        echo '<a href="' . admin_url('admin.php?page=yolo-warehouse') . '" class="button button-primary">View All Warehouse Items</a>';
        echo '</p>';
        
        echo '</div>';
    }
    
    /**
     * Manual trigger for testing (can be called via AJAX)
     */
    public function manual_trigger_check() {
        if (!current_user_can('manage_options')) {
            wp_die('Permission denied');
        }
        
        $this->check_and_send_notifications();
        
        wp_redirect(admin_url('admin.php?page=yolo-warehouse&notification_check=success'));
        exit;
    }
}
