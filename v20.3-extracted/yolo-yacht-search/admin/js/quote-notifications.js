/**
 * Quote Notifications JavaScript
 *
 * Handles browser push notifications and admin bar updates
 *
 * @package YOLO_Yacht_Search
 * @since 17.4
 */

(function($) {
    'use strict';
    
    // Check if we have pending notifications
    if (typeof yoloQuoteNotifications !== 'undefined' && yoloQuoteNotifications.pending_notifications.length > 0) {
        // Show browser notifications if enabled
        if (yoloQuoteNotifications.push_enabled) {
            showBrowserNotifications();
        }
        
        // Clear pending notifications
        clearPendingNotifications();
    }
    
    /**
     * Show browser push notifications
     */
    function showBrowserNotifications() {
        const notifications = yoloQuoteNotifications.pending_notifications;
        
        notifications.forEach((notification, index) => {
            setTimeout(() => {
                showPushNotification(notification);
            }, index * 500); // Stagger notifications
        });
    }
    
    /**
     * Show individual push notification
     */
    function showPushNotification(notification) {
        const $notification = $(`
            <div class="yolo-push-notification">
                <div class="yolo-push-notification-header">
                    <div class="yolo-push-notification-icon">
                        <span class="dashicons dashicons-email-alt"></span>
                    </div>
                    <div class="yolo-push-notification-title">New Quote Request</div>
                    <button class="yolo-push-notification-close" aria-label="Close">×</button>
                </div>
                <div class="yolo-push-notification-body">
                    <strong>${notification.customer_name}</strong> has submitted a quote request.
                </div>
                <div class="yolo-push-notification-actions">
                    <a href="${yoloQuoteNotifications.quotes_url}&action=view&quote_id=${notification.quote_id}" 
                       class="yolo-push-notification-btn yolo-push-notification-btn-primary">
                        View Quote
                    </a>
                    <button class="yolo-push-notification-btn yolo-push-notification-btn-secondary dismiss-notification">
                        Dismiss
                    </button>
                </div>
            </div>
        `);
        
        $('body').append($notification);
        
        // Auto-dismiss after 10 seconds
        setTimeout(() => {
            dismissNotification($notification);
        }, 10000);
        
        // Close button
        $notification.find('.yolo-push-notification-close, .dismiss-notification').on('click', function(e) {
            e.preventDefault();
            dismissNotification($notification);
        });
    }
    
    /**
     * Dismiss notification with animation
     */
    function dismissNotification($notification) {
        $notification.addClass('hiding');
        setTimeout(() => {
            $notification.remove();
        }, 300);
    }
    
    /**
     * Clear pending notifications from user meta
     */
    function clearPendingNotifications() {
        $.ajax({
            url: yoloQuoteNotifications.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_clear_pending_notifications',
                nonce: yoloQuoteNotifications.nonce
            }
        });
    }
    
    /**
     * Request browser notification permission
     */
    window.yoloRequestNotificationPermission = function() {
        if (!('Notification' in window)) {
            alert('This browser does not support desktop notifications.');
            return;
        }
        
        if (Notification.permission === 'granted') {
            alert('Notifications are already enabled!');
            return;
        }
        
        Notification.requestPermission().then(function(permission) {
            if (permission === 'granted') {
                alert('Notifications enabled! You will now receive alerts for new quote requests.');
                // Update user preference
                updateNotificationPreference(true);
            } else {
                alert('Notification permission denied.');
            }
        });
    };
    
    /**
     * Update notification preference
     */
    function updateNotificationPreference(enabled) {
        $.ajax({
            url: yoloQuoteNotifications.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_update_notification_preference',
                nonce: yoloQuoteNotifications.nonce,
                enabled: enabled ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    console.log('Notification preference updated');
                }
            }
        });
    }
    
})(jQuery);
