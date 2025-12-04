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
                        
                        <!-- Skipper 2 License Upload Section -->
                        <div class="yolo-license-upload yolo-skipper2-upload">
                            <h3>📜 Skipper 2 License (Optional)</h3>
                            <p class="yolo-license-note">Please upload both sides of the second skipper's license, if applicable.</p>
                            
                            <?php
                            $has_s2_front = false;
                            $has_s2_back = false;
                            $s2_front_url = '';
                            $s2_back_url = '';
                            
                            foreach ($booking_licenses as $license) {
                                if ($license->file_type === 'skipper2_front') {
                                    $has_s2_front = true;
                                    $s2_front_url = $license->file_url;
                                }
                                if ($license->file_type === 'skipper2_back') {
                                    $has_s2_back = true;
                                    $s2_back_url = $license->file_url;
                                }
                            }
                            ?>
                            
                            <div class="yolo-license-grid">
                                <!-- Skipper 2 Front License -->
                                <div class="yolo-license-item">
                                    <label class="yolo-license-label">Skipper 2 Front Side</label>
                                    <?php if ($has_s2_front): ?>
                                        <div class="yolo-license-preview">
                                            <img src="<?php echo esc_url($s2_front_url); ?>" alt="Skipper 2 License Front">
                                            <span class="yolo-license-uploaded">✓ Uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                    <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="skipper2_front">
                                        <input type="file" name="license_file" accept="image/*" required>
                                        <button type="submit" class="yolo-upload-btn">
                                            <?php echo $has_s2_front ? 'Replace Front' : 'Upload Front'; ?>
                                        </button>
                                        <div class="yolo-upload-message"></div>
                                    </form>
                                </div>
                                
                                <!-- Skipper 2 Back License -->
                                <div class="yolo-license-item">
                                    <label class="yolo-license-label">Skipper 2 Back Side</label>
                                    <?php if ($has_s2_back): ?>
                                        <div class="yolo-license-preview">
                                            <img src="<?php echo esc_url($s2_back_url); ?>" alt="Skipper 2 License Back">
                                            <span class="yolo-license-uploaded">✓ Uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                    <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="skipper2_back">
                                        <input type="file" name="license_file" accept="image/*" required>
                                        <button type="submit" class="yolo-upload-btn">
                                            <?php echo $has_s2_back ? 'Replace Back' : 'Upload Back'; ?>
                                        </button>
                                        <div class="yolo-upload-message"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Crew List Section -->
                        <div class="yolo-crew-list-section">
                            <h3>👥 Crew List (Max 12 People)</h3>
                            <p class="yolo-crew-note">Please provide the details for all crew members, including yourself.</p>
                            
                            <form class="yolo-crew-list-form" data-booking-id="<?php echo esc_attr($booking->id); ?>">
                                <input type="hidden" name="action" value="yolo_save_crew_list">
                                <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                                <?php wp_nonce_field('yolo_save_crew_list', 'crew_list_nonce'); ?>
                                
                                <div class="yolo-crew-table-wrapper">
                                <table class="yolo-crew-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Surname</th>
                                            <th>Sex</th>
                                            <th>ID/Passport</th>
                                            <th>ID/Passport No.</th>
                                            <th>Birth Date</th>
                                            <th>Role</th>
                                            <th>Mobile</th>
                                            <th>Nationality</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <tr class="yolo-crew-row" data-index="<?php echo $i; ?>">
                                                <td><?php echo $i; ?></td>
                                                <td><input type="text" name="crew[<?php echo $i; ?>][first_name]" placeholder="Name" required></td>
                                                <td><input type="text" name="crew[<?php echo $i; ?>][last_name]" placeholder="Surname" required></td>
                                                <td>
                                                    <select name="crew[<?php echo $i; ?>][sex]" required>
                                                        <option value="">Select</option>
                                                        <option value="male">Male</option>
                                                        <option value="female">Female</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <select name="crew[<?php echo $i; ?>][id_type]" required>
                                                        <option value="">Select</option>
                                                        <option value="national_id">National ID</option>
                                                        <option value="passport">Passport</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" name="crew[<?php echo $i; ?>][id_number]" placeholder="ID/Passport No." required></td>
                                                <td><input type="date" name="crew[<?php echo $i; ?>][birth_date]" required></td>
                                                <td>
                                                    <select name="crew[<?php echo $i; ?>][role]" required>
                                                        <option value="">Select</option>
                                                        <option value="skipper1">Skipper 1</option>
                                                        <option value="skipper2">Skipper 2</option>
                                                        <option value="crew">Crew</option>
                                                    </select>
                                                </td>
                                                <td><input type="tel" name="crew[<?php echo $i; ?>][mobile_number]" placeholder="Mobile" required></td>
                                                <td><input type="text" name="crew[<?php echo $i; ?>][nationality]" placeholder="Nationality" required></td>
                                            </tr>
                                        <?php endfor; ?>
                                    </tbody>
                                </table>
                                </div>
                                
                                <button type="submit" class="yolo-save-crew-btn">Save Crew List</button>
                                <div class="yolo-crew-message"></div>
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
    // License Upload Handler
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
        formData.append('nonce', yolo_guest_vars.nonce);
        formData.append('_wpnonce', yolo_guest_vars.nonce);
        formData.append('booking_id', bookingId);
        formData.append('file_type', fileType);
        formData.append('license_file', fileInput.files[0]);
        
        // Store original button text
        if (!submitBtn.data('original-text')) {
            submitBtn.data('original-text', submitBtn.text());
        }
        
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
                    submitBtn.prop('disabled', false).text(submitBtn.data('original-text'));
                }
            },
            error: function() {
                messageDiv.html('<span class="error">✗ Upload failed. Please try again.</span>');
                submitBtn.prop('disabled', false).text(submitBtn.data('original-text'));
            }
        });
    });

    // Crew List Save Handler
    $('.yolo-crew-list-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var messageDiv = form.find('.yolo-crew-message');
        var submitBtn = form.find('.yolo-save-crew-btn');
        
        var formData = form.serialize();
        
        submitBtn.prop('disabled', true).text('Saving...');
        messageDiv.html('');
        
        $.ajax({
            url: yolo_guest_vars.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<span class="success">✓ Crew list saved successfully!</span>');
                } else {
                    messageDiv.html('<span class="error">✗ ' + response.data.message + '</span>');
                }
                submitBtn.prop('disabled', false).text('Save Crew List');
            },
            error: function() {
                messageDiv.html('<span class="error">✗ Save failed. Please try again.</span>');
                submitBtn.prop('disabled', false).text('Save Crew List');
            }
        });
    });
});
</script>
