<?php
/**
 * Guest Dashboard Template
 * Displays booking information and license upload form
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
?>

<div class="yolo-guest-dashboard">
    <div class="yolo-guest-header">
        <h1>Welcome, <?php echo esc_html($user->first_name); ?>!</h1>
        <p class="yolo-guest-subtitle">Manage your yacht bookings and upload your sailing license</p>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="yolo-logout-btn">Logout</a>
    </div>
    
    <?php if (empty($bookings)): ?>
        <div class="yolo-no-bookings">
            <h3>No Bookings Found</h3>
            <p>You don't have any bookings yet. <a href="<?php echo home_url('/search-yachts'); ?>">Browse our yachts</a> to make your first booking!</p>
        </div>
    <?php else: ?>
        <div class="yolo-bookings-list">
            <?php foreach ($bookings as $booking): ?>
                <div class="yolo-booking-card">
                    <div class="yolo-booking-header">
                        <h2><?php echo esc_html($booking->yacht_name); ?></h2>
                        <span class="yolo-booking-status yolo-status-<?php echo esc_attr($booking->booking_status); ?>">
                            <?php echo esc_html(ucfirst($booking->booking_status)); ?>
                        </span>
                    </div>
                    
                    <div class="yolo-booking-details">
                        <div class="yolo-booking-info">
                            <div class="yolo-info-row">
                                <span class="yolo-info-label">📅 Check-in:</span>
                                <span class="yolo-info-value"><?php echo date('F j, Y', strtotime($booking->date_from)); ?></span>
                            </div>
                            <div class="yolo-info-row">
                                <span class="yolo-info-label">📅 Check-out:</span>
                                <span class="yolo-info-value"><?php echo date('F j, Y', strtotime($booking->date_to)); ?></span>
                            </div>
                            <div class="yolo-info-row">
                                <span class="yolo-info-label">💰 Total Price:</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->currency); ?> <?php echo number_format($booking->total_price, 2); ?></span>
                            </div>
                            <div class="yolo-info-row">
                                <span class="yolo-info-label">✅ Deposit Paid:</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->currency); ?> <?php echo number_format($booking->deposit_paid, 2); ?></span>
                            </div>
                            <div class="yolo-info-row">
                                <span class="yolo-info-label">⏳ Remaining Balance:</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->currency); ?> <?php echo number_format($booking->remaining_balance, 2); ?></span>
                            </div>
                            <div class="yolo-info-row">
                                <span class="yolo-info-label">🔖 Confirmation #:</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->id); ?></span>
                            </div>
                        </div>
                        
                        <!-- License Upload Section -->
                        <div class="yolo-license-upload">
                            <h3>📜 Sailing License</h3>
                            <p class="yolo-license-note">Please upload both sides of your sailing license for verification.</p>
                            
                            <?php
                            $booking_licenses = isset($licenses[$booking->id]) ? $licenses[$booking->id] : array();
                            $has_front = false;
                            $has_back = false;
                            $front_url = '';
                            $back_url = '';
                            
                            foreach ($booking_licenses as $license) {
                                if ($license->file_type === 'front') {
                                    $has_front = true;
                                    $front_url = $license->file_url;
                                }
                                if ($license->file_type === 'back') {
                                    $has_back = true;
                                    $back_url = $license->file_url;
                                }
                            }
                            ?>
                            
                            <div class="yolo-license-grid">
                                <!-- Front License -->
                                <div class="yolo-license-item">
                                    <label class="yolo-license-label">Front Side</label>
                                    <?php if ($has_front): ?>
                                        <div class="yolo-license-preview">
                                            <img src="<?php echo esc_url($front_url); ?>" alt="License Front">
                                            <span class="yolo-license-uploaded">✓ Uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                    <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="front">
                                        <input type="file" name="license_file" accept="image/*" required>
                                        <button type="submit" class="yolo-upload-btn">
                                            <?php echo $has_front ? 'Replace Front' : 'Upload Front'; ?>
                                        </button>
                                        <div class="yolo-upload-message"></div>
                                    </form>
                                </div>
                                
                                <!-- Back License -->
                                <div class="yolo-license-item">
                                    <label class="yolo-license-label">Back Side</label>
                                    <?php if ($has_back): ?>
                                        <div class="yolo-license-preview">
                                            <img src="<?php echo esc_url($back_url); ?>" alt="License Back">
                                            <span class="yolo-license-uploaded">✓ Uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                    <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="back">
                                        <input type="file" name="license_file" accept="image/*" required>
                                        <button type="submit" class="yolo-upload-btn">
                                            <?php echo $has_back ? 'Replace Back' : 'Upload Back'; ?>
                                        </button>
                                        <div class="yolo-upload-message"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    $('.yolo-license-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var bookingId = form.data('booking-id');
        var fileType = form.data('file-type');
        var fileInput = form.find('input[type="file"]')[0];
        var messageDiv = form.find('.yolo-upload-message');
        var submitBtn = form.find('button[type="submit"]');
        
        if (!fileInput.files || !fileInput.files[0]) {
            messageDiv.html('<span class="error">Please select a file</span>');
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'yolo_upload_license');
        formData.append('nonce', '<?php echo wp_create_nonce('yolo_upload_license'); ?>');
        formData.append('booking_id', bookingId);
        formData.append('file_type', fileType);
        formData.append('license_file', fileInput.files[0]);
        
        submitBtn.prop('disabled', true).text('Uploading...');
        messageDiv.html('');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<span class="success">✓ ' + response.data.message + '</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    messageDiv.html('<span class="error">✗ ' + response.data.message + '</span>');
                    submitBtn.prop('disabled', false).text(fileType === 'front' ? 'Upload Front' : 'Upload Back');
                }
            },
            error: function() {
                messageDiv.html('<span class="error">✗ Upload failed. Please try again.</span>');
                submitBtn.prop('disabled', false).text(fileType === 'front' ? 'Upload Front' : 'Upload Back');
            }
        });
    });
});
</script>
