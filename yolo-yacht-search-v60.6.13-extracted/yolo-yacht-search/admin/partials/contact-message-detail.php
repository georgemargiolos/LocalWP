<?php
/**
 * Contact Message Detail View
 *
 * Displays single contact message with full details
 *
 * @package YOLO_Yacht_Search
 * @subpackage Admin/Partials
 * @since 17.5
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get message ID
$message_id = isset($_GET['message_id']) ? intval($_GET['message_id']) : 0;

if (!$message_id) {
    wp_die('Invalid message ID');
}

// Get message
$message = YOLO_YS_Contact_Messages::get_message_by_id($message_id);

if (!$message) {
    wp_die('Message not found');
}

// Mark as viewed
YOLO_YS_Contact_Messages::mark_as_viewed($message_id, get_current_user_id());
?>

<div class="wrap yolo-message-detail-wrapper">
    <h1 class="wp-heading-inline">Contact Message #<?php echo esc_html($message_id); ?></h1>
    <a href="?page=yolo-contact-messages" class="page-title-action">← Back to Messages</a>
    <hr class="wp-header-end">
    
    <div class="yolo-message-detail-grid">
        <!-- Left Column -->
        <div class="yolo-detail-main">
            <!-- Status Card -->
            <div class="detail-card">
                <h3>Status</h3>
                <div class="status-controls">
                    <?php
                    $statuses = array('new', 'reviewed', 'responded');
                    foreach ($statuses as $status):
                        $is_active = $message['status'] === $status;
                        $status_label = ucfirst($status);
                    ?>
                        <button class="status-btn <?php echo $is_active ? 'active' : ''; ?>" 
                                data-status="<?php echo esc_attr($status); ?>"
                                data-message-id="<?php echo esc_attr($message_id); ?>">
                            <?php echo esc_html($status_label); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="status-info">
                    <p><strong>Viewed by:</strong> <?php echo esc_html($message['viewed_by'] ?: 'Not viewed'); ?></p>
                    <p><strong>Last updated:</strong> <?php echo esc_html(date('M j, Y g:i A', strtotime($message['updated_at']))); ?></p>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="detail-card">
                <h3>Contact Information</h3>
                <div class="contact-info-grid">
                    <div class="info-item">
                        <label>Name:</label>
                        <div class="info-value"><?php echo esc_html($message['contact_name']); ?></div>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <div class="info-value">
                            <a href="mailto:<?php echo esc_attr($message['contact_email']); ?>">
                                <?php echo esc_html($message['contact_email']); ?>
                            </a>
                            <a href="mailto:<?php echo esc_attr($message['contact_email']); ?>" 
                               class="button button-small" style="margin-left: 10px;">
                                <span class="dashicons dashicons-email-alt"></span> Send Email
                            </a>
                        </div>
                    </div>
                    <?php if ($message['contact_phone']): ?>
                        <div class="info-item">
                            <label>Phone:</label>
                            <div class="info-value">
                                <a href="tel:<?php echo esc_attr($message['contact_phone']); ?>">
                                    <?php echo esc_html($message['contact_phone']); ?>
                                </a>
                                <a href="tel:<?php echo esc_attr($message['contact_phone']); ?>" 
                                   class="button button-small" style="margin-left: 10px;">
                                    <span class="dashicons dashicons-phone"></span> Call
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Message Content -->
            <div class="detail-card">
                <h3>Subject</h3>
                <p class="message-subject"><?php echo esc_html($message['contact_subject']); ?></p>
                
                <h3>Message</h3>
                <div class="message-content">
                    <?php echo nl2br(esc_html($message['contact_message'])); ?>
                </div>
                
                <div class="message-meta">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    Received: <?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($message['created_at']))); ?>
                </div>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="yolo-detail-sidebar">
            <!-- Internal Notes -->
            <div class="detail-card">
                <h3>Internal Notes</h3>
                <p class="description">Private notes for your team (not visible to customer)</p>
                <textarea id="message-notes" 
                          class="widefat" 
                          rows="10" 
                          placeholder="Add internal notes here..."><?php echo esc_textarea($message['notes']); ?></textarea>
                <button id="save-notes-btn" 
                        class="button button-primary" 
                        data-message-id="<?php echo esc_attr($message_id); ?>"
                        style="margin-top: 10px; width: 100%;">
                    Save Notes
                </button>
                <div id="notes-save-status" style="margin-top: 10px;"></div>
            </div>
            
            <!-- Actions -->
            <?php if (current_user_can('manage_options')): ?>
                <div class="detail-card">
                    <h3>Actions</h3>
                    <button id="delete-message-btn" 
                            class="button button-link-delete" 
                            data-message-id="<?php echo esc_attr($message_id); ?>"
                            style="width: 100%;">
                        <span class="dashicons dashicons-trash"></span> Delete Message
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.yolo-message-detail-grid {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 20px;
    margin-top: 20px;
}

.detail-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.detail-card h3 {
    margin-top: 0;
    color: #1e3a8a;
    font-size: 16px;
    border-bottom: 2px solid #e5e7eb;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

/* Status Controls */
.status-controls {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.status-btn {
    flex: 1;
    padding: 10px;
    border: 2px solid #e5e7eb;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.2s;
}

.status-btn:hover {
    border-color: #3b82f6;
}

.status-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.status-info p {
    margin: 5px 0;
    font-size: 14px;
    color: #64748b;
}

/* Contact Info */
.contact-info-grid {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 5px;
}

.info-value {
    display: flex;
    align-items: center;
}

.info-value a {
    text-decoration: none;
}

/* Message Content */
.message-subject {
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 20px 0;
}

.message-content {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 15px;
    line-height: 1.6;
    color: #1e293b;
    margin-bottom: 15px;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #64748b;
    font-size: 14px;
}

.message-meta .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

/* Responsive */
@media (max-width: 1024px) {
    .yolo-message-detail-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Update status
    $('.status-btn').on('click', function() {
        var $btn = $(this);
        var messageId = $btn.data('message-id');
        var newStatus = $btn.data('status');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_update_message_status',
                nonce: '<?php echo wp_create_nonce('yolo_contact_notifications'); ?>',
                message_id: messageId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    $('.status-btn').removeClass('active');
                    $btn.addClass('active');
                }
            }
        });
    });
    
    // Save notes
    $('#save-notes-btn').on('click', function() {
        var $btn = $(this);
        var messageId = $btn.data('message-id');
        var notes = $('#message-notes').val();
        var $status = $('#notes-save-status');
        
        $btn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_save_message_notes',
                nonce: '<?php echo wp_create_nonce('yolo_contact_notifications'); ?>',
                message_id: messageId,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<span style="color: #059669;">✓ Notes saved successfully</span>');
                } else {
                    $status.html('<span style="color: #dc2626;">✗ Failed to save notes</span>');
                }
                setTimeout(function() {
                    $status.html('');
                }, 3000);
            },
            complete: function() {
                $btn.prop('disabled', false).text('Save Notes');
            }
        });
    });
    
    // Delete message
    $('#delete-message-btn').on('click', function() {
        if (!confirm('Are you sure you want to delete this message? This action cannot be undone.')) {
            return;
        }
        
        var messageId = $(this).data('message-id');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_delete_contact_message',
                nonce: '<?php echo wp_create_nonce('yolo_contact_notifications'); ?>',
                message_id: messageId
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = '?page=yolo-contact-messages';
                } else {
                    alert('Failed to delete message');
                }
            }
        });
    });
});
</script>
