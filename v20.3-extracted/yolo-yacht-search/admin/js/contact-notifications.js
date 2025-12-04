/**
 * Contact Message Notifications JavaScript
 *
 * Handles browser notifications for new contact messages
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/JS
 * @since 17.5
 */

(function($) {
    'use strict';
    
    // Check if we have pending notifications
    if (typeof yoloContactNotifications === 'undefined' || !yoloContactNotifications.pendingNotifications) {
        return;
    }
    
    var pendingNotifications = yoloContactNotifications.pendingNotifications;
    
    // Show notifications
    if (pendingNotifications.length > 0) {
        showContactNotifications(pendingNotifications);
        clearPendingNotifications();
    }
    
    /**
     * Show contact message notifications
     */
    function showContactNotifications(notifications) {
        notifications.forEach(function(notification, index) {
            setTimeout(function() {
                showSingleNotification(notification);
            }, index * 500); // Stagger notifications by 500ms
        });
    }
    
    /**
     * Show single notification
     */
    function showSingleNotification(notification) {
        // Create notification element
        var $notification = $('<div class="yolo-notification yolo-notification-contact">')
            .html(
                '<div class="yolo-notification-icon">' +
                    '<span class="dashicons dashicons-email"></span>' +
                '</div>' +
                '<div class="yolo-notification-content">' +
                    '<div class="yolo-notification-title">New Contact Message</div>' +
                    '<div class="yolo-notification-message">' +
                        '<strong>' + escapeHtml(notification.contact_name) + '</strong><br>' +
                        escapeHtml(notification.subject) +
                    '</div>' +
                '</div>' +
                '<button class="yolo-notification-close">&times;</button>'
            );
        
        // Add to page
        if ($('#yolo-notifications-container').length === 0) {
            $('body').append('<div id="yolo-notifications-container"></div>');
        }
        
        $('#yolo-notifications-container').append($notification);
        
        // Animate in
        setTimeout(function() {
            $notification.addClass('show');
        }, 10);
        
        // Click to view
        $notification.on('click', function(e) {
            if (!$(e.target).hasClass('yolo-notification-close')) {
                window.location.href = yoloContactNotifications.messagesUrl + '&action=view&message_id=' + notification.message_id;
            }
        });
        
        // Close button
        $notification.find('.yolo-notification-close').on('click', function(e) {
            e.stopPropagation();
            closeNotification($notification);
        });
        
        // Auto-close after 10 seconds
        setTimeout(function() {
            closeNotification($notification);
        }, 10000);
    }
    
    /**
     * Close notification
     */
    function closeNotification($notification) {
        $notification.removeClass('show');
        setTimeout(function() {
            $notification.remove();
        }, 300);
    }
    
    /**
     * Clear pending notifications
     */
    function clearPendingNotifications() {
        $.ajax({
            url: yoloContactNotifications.ajaxUrl,
            type: 'POST',
            data: {
                action: 'yolo_clear_contact_notifications',
                nonce: yoloContactNotifications.nonce
            }
        });
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
})(jQuery);
