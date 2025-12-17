<?php
/**
 * Quote Notification Settings Page
 *
 * Allows admins to configure which base managers receive quote notifications
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/Partials
 * @since 17.4
 */

if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['save_notification_settings']) && check_admin_referer('yolo_notification_settings')) {
    $base_managers = get_users(array('role' => 'base_manager'));
    
    foreach ($base_managers as $bm) {
        $enabled = isset($_POST['notify_user_' . $bm->ID]) ? '1' : '0';
        update_user_meta($bm->ID, 'yolo_quote_notifications_enabled', $enabled);
        
        $push_enabled = isset($_POST['push_user_' . $bm->ID]) ? '1' : '0';
        update_user_meta($bm->ID, 'yolo_push_notifications_enabled', $push_enabled);
    }
    
    echo '<div class="notice notice-success is-dismissible"><p>Notification settings saved!</p></div>';
}

// Get all base managers
$base_managers = get_users(array('role' => 'base_manager'));
$admins = get_users(array('role' => 'administrator'));
?>

<div class="wrap yolo-notification-settings-wrapper">
    <h1>Quote Notification Settings</h1>
    <p class="description">Configure who receives notifications when new quote requests are submitted.</p>
    
    <form method="post" action="">
        <?php wp_nonce_field('yolo_notification_settings'); ?>
        
        <!-- Administrators Section -->
        <div class="notification-section">
            <h2>Administrators</h2>
            <p class="description">All administrators automatically receive quote notifications.</p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin): ?>
                        <tr>
                            <td><strong><?php echo esc_html($admin->display_name); ?></strong></td>
                            <td><?php echo esc_html($admin->user_email); ?></td>
                            <td><span class="status-badge status-active">Always Enabled</span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Base Managers Section -->
        <div class="notification-section">
            <h2>Base Managers</h2>
            <p class="description">Select which base managers should receive quote notifications.</p>
            
            <?php if (empty($base_managers)): ?>
                <p style="padding: 20px; background: #f9fafb; border-radius: 6px; color: #6b7280;">
                    No base managers found. Create base manager users to enable selective notifications.
                </p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 50px;">
                                <input type="checkbox" id="select-all-notify" title="Select All">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th style="width: 150px;">Admin Bar Badge</th>
                            <th style="width: 150px;">Browser Push</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($base_managers as $bm): 
                            $notify_enabled = get_user_meta($bm->ID, 'yolo_quote_notifications_enabled', true);
                            $push_enabled = get_user_meta($bm->ID, 'yolo_push_notifications_enabled', true);
                        ?>
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           name="notify_user_<?php echo $bm->ID; ?>" 
                                           class="notify-checkbox"
                                           <?php checked($notify_enabled, '1'); ?>>
                                </td>
                                <td><strong><?php echo esc_html($bm->display_name); ?></strong></td>
                                <td><?php echo esc_html($bm->user_email); ?></td>
                                <td>
                                    <label class="toggle-label">
                                        <input type="checkbox" 
                                               name="notify_user_<?php echo $bm->ID; ?>" 
                                               class="toggle-checkbox"
                                               <?php checked($notify_enabled, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="toggle-label">
                                        <input type="checkbox" 
                                               name="push_user_<?php echo $bm->ID; ?>" 
                                               class="toggle-checkbox"
                                               <?php checked($push_enabled, '1'); ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($base_managers)): ?>
            <p class="submit">
                <button type="submit" name="save_notification_settings" class="button button-primary button-large">
                    Save Notification Settings
                </button>
            </p>
        <?php endif; ?>
    </form>
    
    <!-- Help Section -->
    <div class="notification-help">
        <h3>How Notifications Work</h3>
        <div class="help-grid">
            <div class="help-card">
                <span class="dashicons dashicons-admin-users"></span>
                <h4>Admin Bar Badge</h4>
                <p>Shows a notification badge in the WordPress admin bar when logged in. Badge displays the number of unread quote requests.</p>
            </div>
            <div class="help-card">
                <span class="dashicons dashicons-bell"></span>
                <h4>Browser Push</h4>
                <p>Displays pop-up notifications in the browser when new quotes arrive. Works even when the admin page is in the background.</p>
            </div>
            <div class="help-card">
                <span class="dashicons dashicons-email-alt"></span>
                <h4>In-House System</h4>
                <p>All quote requests are stored in the database. No emails are sent. View and manage quotes from the Quote Requests page.</p>
            </div>
        </div>
    </div>
</div>

<style>
.yolo-notification-settings-wrapper {
    max-width: 1200px;
}

.notification-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.notification-section h2 {
    margin-top: 0;
    color: #1e3a8a;
}

.notification-section .description {
    margin-bottom: 15px;
    color: #6b7280;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-badge.status-active {
    background: #d1fae5;
    color: #065f46;
}

/* Toggle Switch */
.toggle-label {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
}

.toggle-checkbox {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #cbd5e1;
    transition: 0.3s;
    border-radius: 24px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

.toggle-checkbox:checked + .toggle-slider {
    background-color: #3b82f6;
}

.toggle-checkbox:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-checkbox:focus + .toggle-slider {
    box-shadow: 0 0 1px #3b82f6;
}

/* Help Section */
.notification-help {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.notification-help h3 {
    margin-top: 0;
    color: #1e3a8a;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.help-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 15px;
    text-align: center;
}

.help-card .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
    color: #3b82f6;
    margin-bottom: 10px;
}

.help-card h4 {
    margin: 10px 0;
    color: #374151;
}

.help-card p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Select all checkbox
    $('#select-all-notify').on('change', function() {
        $('.notify-checkbox').prop('checked', $(this).prop('checked'));
    });
});
</script>
