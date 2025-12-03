<?php
/**
 * Guest Dashboard Template - Redesigned v2.7.5
 * Clean, modern, fully responsive
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();

// Ensure variables are defined (defensive programming)
$bookings = isset($bookings) ? $bookings : array();
$licenses = isset($licenses) ? $licenses : array();

// Get existing crew data
global $wpdb;
$table_crew = $wpdb->prefix . 'yolo_crew_list';
?>

<div class="yolo-guest-dashboard alignfull">
    <div class="yolo-guest-header">
        <h1>Welcome, <?php echo esc_html($user->first_name ?: $user->display_name); ?>!</h1>
        <p class="yolo-guest-subtitle">Manage your bookings and upload documents</p>
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="yolo-logout-btn">Logout</a>
    </div>
    
    <?php if (empty($bookings)): ?>
        <div class="yolo-no-bookings">
            <h3>No Bookings Found</h3>
            <p>You don't have any bookings yet. <a href="<?php echo home_url('/our-yachts'); ?>">Browse our yachts</a> to make your first booking!</p>
        </div>
    <?php else: ?>
        <div class="yolo-bookings-list">
            <?php foreach ($bookings as $booking): 
                $booking_licenses = isset($licenses[$booking->id]) ? $licenses[$booking->id] : array();
                
                // Check uploaded licenses
                $has_front = $has_back = $has_s2_front = $has_s2_back = $has_id_front = $has_id_back = false;
                $front_url = $back_url = $s2_front_url = $s2_back_url = $id_front_url = $id_back_url = '';
                
                foreach ($booking_licenses as $license) {
                    switch ($license->file_type) {
                        case 'front': $has_front = true; $front_url = $license->file_url; break;
                        case 'back': $has_back = true; $back_url = $license->file_url; break;
                        case 'skipper2_front': $has_s2_front = true; $s2_front_url = $license->file_url; break;
                        case 'skipper2_back': $has_s2_back = true; $s2_back_url = $license->file_url; break;
                        case 'id_front': $has_id_front = true; $id_front_url = $license->file_url; break;
                        case 'id_back': $has_id_back = true; $id_back_url = $license->file_url; break;
                    }
                }
                
                // Get existing crew data for this booking
                $crew_data = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$table_crew} WHERE booking_id = %d ORDER BY crew_member_index ASC",
                    $booking->id
                ), ARRAY_A);
                $crew_by_index = array();
                foreach ($crew_data as $crew) {
                    $crew_by_index[$crew['crew_member_index']] = $crew;
                }
            ?>
                <div class="yolo-booking-card">
                    <div class="yolo-booking-header">
                        <h2><?php echo esc_html($booking->yacht_name); ?></h2>
                        <span class="yolo-booking-status yolo-status-<?php echo esc_attr($booking->booking_status); ?>">
                            <?php echo esc_html(ucfirst($booking->booking_status)); ?>
                        </span>
                    </div>
                    
                    <div class="yolo-booking-details">
                        <!-- Booking Info Grid -->
                        <div class="yolo-booking-info">
                            <div class="yolo-info-item">
                                <span class="yolo-info-label">Check-in</span>
                                <span class="yolo-info-value"><?php echo date('M j, Y', strtotime($booking->date_from)); ?></span>
                            </div>
                            <div class="yolo-info-item">
                                <span class="yolo-info-label">Check-out</span>
                                <span class="yolo-info-value"><?php echo date('M j, Y', strtotime($booking->date_to)); ?></span>
                            </div>
                            <div class="yolo-info-item">
                                <span class="yolo-info-label">Total Price</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->currency); ?> <?php echo number_format($booking->total_price, 2); ?></span>
                            </div>
                            <div class="yolo-info-item">
                                <span class="yolo-info-label">Deposit Paid</span>
                                <span class="yolo-info-value highlight"><?php echo esc_html($booking->currency); ?> <?php echo number_format($booking->deposit_paid, 2); ?></span>
                            </div>
                            <div class="yolo-info-item">
                                <span class="yolo-info-label">Balance Due</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->currency); ?> <?php echo number_format($booking->remaining_balance, 2); ?></span>
                            </div>
                            <div class="yolo-info-item">
                                <span class="yolo-info-label">Booking #</span>
                                <span class="yolo-info-value"><?php echo esc_html($booking->id); ?></span>
                            </div>
                        </div>
                        
                        <!-- Skipper 1 License Section -->
                        <div class="yolo-accordion-section yolo-license-section <?php echo ($has_front || $has_back) ? 'open' : ''; ?>">
                            <button type="button" class="yolo-section-toggle">
                                <span>📜 Skipper 1 - Sailing License <?php echo ($has_front && $has_back) ? '✓' : ''; ?></span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content" <?php echo ($has_front || $has_back) ? 'style="display:block;"' : ''; ?>>
                                <div class="yolo-license-grid">
                                    <div class="yolo-license-item <?php echo $has_front ? 'uploaded' : ''; ?>">
                                        <label class="yolo-license-label">Front Side</label>
                                        <?php if ($has_front): ?>
                                            <div class="yolo-license-preview">
                                                <img src="<?php echo esc_url($front_url); ?>" alt="License Front">
                                            </div>
                                            <span class="yolo-license-uploaded-badge">✓ Uploaded</span>
                                        <?php endif; ?>
                                        <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="front">
                                            <div class="yolo-file-input-wrapper">
                                                <input type="file" name="license_file" accept="image/*">
                                            </div>
                                            <button type="submit" class="yolo-upload-btn">
                                                <?php echo $has_front ? 'Replace' : 'Upload'; ?> Front
                                            </button>
                                            <div class="yolo-upload-message"></div>
                                        </form>
                                    </div>
                                    
                                    <div class="yolo-license-item <?php echo $has_back ? 'uploaded' : ''; ?>">
                                        <label class="yolo-license-label">Back Side</label>
                                        <?php if ($has_back): ?>
                                            <div class="yolo-license-preview">
                                                <img src="<?php echo esc_url($back_url); ?>" alt="License Back">
                                            </div>
                                            <span class="yolo-license-uploaded-badge">✓ Uploaded</span>
                                        <?php endif; ?>
                                        <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="back">
                                            <div class="yolo-file-input-wrapper">
                                                <input type="file" name="license_file" accept="image/*">
                                            </div>
                                            <button type="submit" class="yolo-upload-btn">
                                                <?php echo $has_back ? 'Replace' : 'Upload'; ?> Back
                                            </button>
                                            <div class="yolo-upload-message"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Skipper 2 License Section -->
                        <div class="yolo-accordion-section yolo-skipper2-section">
                            <button type="button" class="yolo-section-toggle">
                                <span>📜 Skipper 2 - License (Optional)</span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content">
                                <div class="yolo-license-grid">
                                    <div class="yolo-license-item <?php echo $has_s2_front ? 'uploaded' : ''; ?>">
                                        <label class="yolo-license-label">Front Side</label>
                                        <?php if ($has_s2_front): ?>
                                            <div class="yolo-license-preview">
                                                <img src="<?php echo esc_url($s2_front_url); ?>" alt="Skipper 2 Front">
                                            </div>
                                            <span class="yolo-license-uploaded-badge">✓ Uploaded</span>
                                        <?php endif; ?>
                                        <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="skipper2_front">
                                            <div class="yolo-file-input-wrapper">
                                                <input type="file" name="license_file" accept="image/*">
                                            </div>
                                            <button type="submit" class="yolo-upload-btn">
                                                <?php echo $has_s2_front ? 'Replace' : 'Upload'; ?> Front
                                            </button>
                                            <div class="yolo-upload-message"></div>
                                        </form>
                                    </div>
                                    
                                    <div class="yolo-license-item <?php echo $has_s2_back ? 'uploaded' : ''; ?>">
                                        <label class="yolo-license-label">Back Side</label>
                                        <?php if ($has_s2_back): ?>
                                            <div class="yolo-license-preview">
                                                <img src="<?php echo esc_url($s2_back_url); ?>" alt="Skipper 2 Back">
                                            </div>
                                            <span class="yolo-license-uploaded-badge">✓ Uploaded</span>
                                        <?php endif; ?>
                                        <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="skipper2_back">
                                            <div class="yolo-file-input-wrapper">
                                                <input type="file" name="license_file" accept="image/*">
                                            </div>
                                            <button type="submit" class="yolo-upload-btn">
                                                <?php echo $has_s2_back ? 'Replace' : 'Upload'; ?> Back
                                            </button>
                                            <div class="yolo-upload-message"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- National ID/Passport Section -->
                        <div class="yolo-accordion-section yolo-id-section">
                            <button type="button" class="yolo-section-toggle">
                                <span>🪪 National ID / Passport <?php echo ($has_id_front && $has_id_back) ? '✓' : ''; ?></span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content">
                                <p class="yolo-id-note">Upload both sides of your National ID or Passport for verification.</p>
                                <div class="yolo-license-grid">
                                    <div class="yolo-license-item <?php echo $has_id_front ? 'uploaded' : ''; ?>">
                                        <label class="yolo-license-label">Front Side</label>
                                        <?php if ($has_id_front): ?>
                                            <div class="yolo-license-preview">
                                                <img src="<?php echo esc_url($id_front_url); ?>" alt="ID Front">
                                            </div>
                                            <span class="yolo-license-uploaded-badge">✓ Uploaded</span>
                                        <?php endif; ?>
                                        <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="id_front">
                                            <div class="yolo-file-input-wrapper">
                                                <input type="file" name="license_file" accept="image/*">
                                            </div>
                                            <button type="submit" class="yolo-upload-btn">
                                                <?php echo $has_id_front ? 'Replace' : 'Upload'; ?> Front
                                            </button>
                                            <div class="yolo-upload-message"></div>
                                        </form>
                                    </div>
                                    
                                    <div class="yolo-license-item <?php echo $has_id_back ? 'uploaded' : ''; ?>">
                                        <label class="yolo-license-label">Back Side</label>
                                        <?php if ($has_id_back): ?>
                                            <div class="yolo-license-preview">
                                                <img src="<?php echo esc_url($id_back_url); ?>" alt="ID Back">
                                            </div>
                                            <span class="yolo-license-uploaded-badge">✓ Uploaded</span>
                                        <?php endif; ?>
                                        <form class="yolo-license-form" data-booking-id="<?php echo esc_attr($booking->id); ?>" data-file-type="id_back">
                                            <div class="yolo-file-input-wrapper">
                                                <input type="file" name="license_file" accept="image/*">
                                            </div>
                                            <button type="submit" class="yolo-upload-btn">
                                                <?php echo $has_id_back ? 'Replace' : 'Upload'; ?> Back
                                            </button>
                                            <div class="yolo-upload-message"></div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Crew List Section -->
                        <div class="yolo-accordion-section yolo-crew-section">
                            <button type="button" class="yolo-section-toggle">
                                <span>👥 Crew List (Max 12 People)</span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content">
                                <p class="yolo-crew-note">Enter details for all crew members including yourself.</p>
                                
                                <form class="yolo-crew-list-form" data-booking-id="<?php echo esc_attr($booking->id); ?>">
                                    <input type="hidden" name="action" value="yolo_save_crew_list">
                                    <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
                                    <?php wp_nonce_field('yolo_save_crew_list', 'crew_list_nonce'); ?>
                                    
                                    <div class="yolo-crew-cards">
                                        <?php for ($i = 1; $i <= 12; $i++): 
                                            $member = isset($crew_by_index[$i]) ? $crew_by_index[$i] : array();
                                        ?>
                                            <div class="yolo-crew-card">
                                                <div class="yolo-crew-card-header">
                                                    <span class="yolo-crew-number"><?php echo $i; ?></span>
                                                    <span style="font-size: 12px; color: #6b7280;"><?php echo $i === 1 ? 'Primary Skipper' : ($i === 2 ? 'Secondary Skipper / Crew' : 'Crew Member'); ?></span>
                                                </div>
                                                <div class="yolo-crew-fields">
                                                    <div class="yolo-crew-field">
                                                        <label>First Name</label>
                                                        <input type="text" name="crew[<?php echo $i; ?>][first_name]" value="<?php echo esc_attr($member['first_name'] ?? ''); ?>" placeholder="First name">
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>Last Name</label>
                                                        <input type="text" name="crew[<?php echo $i; ?>][last_name]" value="<?php echo esc_attr($member['last_name'] ?? ''); ?>" placeholder="Last name">
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>Sex</label>
                                                        <select name="crew[<?php echo $i; ?>][sex]">
                                                            <option value="">Select</option>
                                                            <option value="male" <?php selected($member['sex'] ?? '', 'male'); ?>>Male</option>
                                                            <option value="female" <?php selected($member['sex'] ?? '', 'female'); ?>>Female</option>
                                                        </select>
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>ID Type</label>
                                                        <select name="crew[<?php echo $i; ?>][id_type]">
                                                            <option value="">Select</option>
                                                            <option value="national_id" <?php selected($member['id_type'] ?? '', 'national_id'); ?>>National ID</option>
                                                            <option value="passport" <?php selected($member['id_type'] ?? '', 'passport'); ?>>Passport</option>
                                                        </select>
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>ID/Passport Number</label>
                                                        <input type="text" name="crew[<?php echo $i; ?>][id_number]" value="<?php echo esc_attr($member['id_number'] ?? ''); ?>" placeholder="ID number">
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>Birth Date</label>
                                                        <input type="date" name="crew[<?php echo $i; ?>][birth_date]" value="<?php echo esc_attr($member['birth_date'] ?? ''); ?>">
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>Role</label>
                                                        <select name="crew[<?php echo $i; ?>][role]">
                                                            <option value="">Select</option>
                                                            <option value="skipper1" <?php selected($member['role'] ?? '', 'skipper1'); ?>>Skipper 1</option>
                                                            <option value="skipper2" <?php selected($member['role'] ?? '', 'skipper2'); ?>>Skipper 2</option>
                                                            <option value="crew" <?php selected($member['role'] ?? '', 'crew'); ?>>Crew</option>
                                                        </select>
                                                    </div>
                                                    <div class="yolo-crew-field">
                                                        <label>Mobile</label>
                                                        <input type="tel" name="crew[<?php echo $i; ?>][mobile_number]" value="<?php echo esc_attr($member['mobile_number'] ?? ''); ?>" placeholder="+30...">
                                                    </div>
                                                    <div class="yolo-crew-field full-width">
                                                        <label>Nationality</label>
                                                        <input type="text" name="crew[<?php echo $i; ?>][nationality]" value="<?php echo esc_attr($member['nationality'] ?? ''); ?>" placeholder="e.g., Greek">
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    
                                    <button type="submit" class="yolo-save-crew-btn">Save Crew List</button>
                                    <div class="yolo-crew-message"></div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Check-In Documents Section -->
                        <?php
                        // Fetch check-in documents for this booking
                        $table_checkins = $wpdb->prefix . 'yolo_bm_checkins';
                        $checkins = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$table_checkins} WHERE booking_id = %d ORDER BY created_at DESC",
                            $booking->id
                        ));
                        ?>
                        <div class="yolo-accordion-section yolo-checkin-section">
                            <button type="button" class="yolo-section-toggle">
                                <span>✅ Check-In Documents <?php echo !empty($checkins) ? '(' . count($checkins) . ')' : ''; ?></span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content">
                                <?php if (!empty($checkins)): ?>
                                    <p class="yolo-docs-note">Review and sign your yacht check-in documents.</p>
                                    <div class="yolo-checkin-docs-list">
                                        <?php foreach ($checkins as $checkin): ?>
                                            <div class="yolo-checkin-doc-item <?php echo $checkin->guest_signature ? 'signed' : 'pending'; ?>">
                                                <div class="yolo-doc-header">
                                                    <h4>Check-In Document #<?php echo $checkin->id; ?></h4>
                                                    <span class="yolo-doc-status <?php echo $checkin->guest_signature ? 'status-signed' : 'status-pending'; ?>">
                                                        <?php echo $checkin->guest_signature ? '✓ Signed' : '⏳ Pending Signature'; ?>
                                                    </span>
                                                </div>
                                                <p class="yolo-doc-date">Created: <?php echo date('M j, Y g:i A', strtotime($checkin->created_at)); ?></p>
                                                
                                                <div class="yolo-doc-actions">
                                                    <?php if ($checkin->pdf_url): ?>
                                                        <a href="<?php echo esc_url($checkin->pdf_url); ?>" target="_blank" class="yolo-view-doc-btn">View PDF</a>
                                                        <a href="<?php echo esc_url($checkin->pdf_url); ?>" download class="yolo-download-doc-btn">Download</a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!$checkin->guest_signature): ?>
                                                        <button type="button" class="yolo-sign-doc-btn" data-checkin-id="<?php echo $checkin->id; ?>" data-type="checkin">
                                                            ✍️ Sign Document
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="yolo-no-docs-message">No check-in documents available yet. Your base manager will send check-in documents when ready.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Check-Out Documents Section -->
                        <?php
                        // Fetch check-out documents for this booking
                        $table_checkouts = $wpdb->prefix . 'yolo_bm_checkouts';
                        $checkouts = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$table_checkouts} WHERE booking_id = %d ORDER BY created_at DESC",
                            $booking->id
                        ));
                        ?>
                        <div class="yolo-accordion-section yolo-checkout-section">
                            <button type="button" class="yolo-section-toggle">
                                <span>📋 Check-Out Documents <?php echo !empty($checkouts) ? '(' . count($checkouts) . ')' : ''; ?></span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content">
                                <?php if (!empty($checkouts)): ?>
                                    <p class="yolo-docs-note">Review and sign your yacht check-out documents.</p>
                                    <div class="yolo-checkout-docs-list">
                                        <?php foreach ($checkouts as $checkout): ?>
                                            <div class="yolo-checkout-doc-item <?php echo $checkout->guest_signature ? 'signed' : 'pending'; ?>">
                                                <div class="yolo-doc-header">
                                                    <h4>Check-Out Document #<?php echo $checkout->id; ?></h4>
                                                    <span class="yolo-doc-status <?php echo $checkout->guest_signature ? 'status-signed' : 'status-pending'; ?>">
                                                        <?php echo $checkout->guest_signature ? '✓ Signed' : '⏳ Pending Signature'; ?>
                                                    </span>
                                                </div>
                                                <p class="yolo-doc-date">Created: <?php echo date('M j, Y g:i A', strtotime($checkout->created_at)); ?></p>
                                                
                                                <div class="yolo-doc-actions">
                                                    <?php if ($checkout->pdf_url): ?>
                                                        <a href="<?php echo esc_url($checkout->pdf_url); ?>" target="_blank" class="yolo-view-doc-btn">View PDF</a>
                                                        <a href="<?php echo esc_url($checkout->pdf_url); ?>" download class="yolo-download-doc-btn">Download</a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!$checkout->guest_signature): ?>
                                                        <button type="button" class="yolo-sign-doc-btn" data-checkout-id="<?php echo $checkout->id; ?>" data-type="checkout">
                                                            ✍️ Sign Document
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="yolo-no-docs-message">No check-out documents available yet. Your base manager will send check-out documents when ready.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Incoming Documents from YOLO Charters -->
                        <?php
                        // Fetch admin-uploaded documents for this booking
                        $table_admin_docs = $wpdb->prefix . 'yolo_admin_documents';
                        $admin_documents = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$table_admin_docs} WHERE booking_id = %d ORDER BY uploaded_at DESC",
                            $booking->id
                        ));
                        ?>
                        <div class="yolo-accordion-section yolo-incoming-docs-section">
                            <button type="button" class="yolo-section-toggle">
                                <span>📥 Incoming Documents from YOLO Charters <?php echo !empty($admin_documents) ? '(' . count($admin_documents) . ')' : ''; ?></span>
                                <span class="icon">▼</span>
                            </button>
                            <div class="yolo-section-content">
                                <?php if (!empty($admin_documents)): ?>
                                    <p class="yolo-docs-note">Documents sent to you by YOLO Charters for your charter.</p>
                                    <div class="yolo-incoming-docs-list">
                                        <?php foreach ($admin_documents as $doc): ?>
                                            <div class="yolo-incoming-doc-item">
                                                <div class="yolo-doc-icon">
                                                    <?php
                                                    $extension = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
                                                    $icon = '📄';
                                                    if (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                                                        $icon = '🖼️';
                                                    } elseif ($extension === 'pdf') {
                                                        $icon = '📑';
                                                    } elseif (in_array($extension, ['doc', 'docx'])) {
                                                        $icon = '📝';
                                                    }
                                                    echo $icon;
                                                    ?>
                                                </div>
                                                <div class="yolo-doc-info">
                                                    <h4><?php echo esc_html($doc->file_name); ?></h4>
                                                    <?php if ($doc->description): ?>
                                                        <p class="yolo-doc-description"><?php echo esc_html($doc->description); ?></p>
                                                    <?php endif; ?>
                                                    <p class="yolo-doc-meta">
                                                        Sent: <?php echo date('M j, Y g:i A', strtotime($doc->uploaded_at)); ?>
                                                        <?php if ($doc->file_size): ?>
                                                            | Size: <?php echo size_format($doc->file_size); ?>
                                                        <?php endif; ?>
                                                    </p>
                                                </div>
                                                <div class="yolo-doc-actions">
                                                    <a href="<?php echo esc_url($doc->file_url); ?>" target="_blank" class="yolo-view-doc-btn">View</a>
                                                    <a href="<?php echo esc_url($doc->file_url); ?>" download class="yolo-download-doc-btn">Download</a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="yolo-no-docs-message">No documents have been sent to you yet. Documents sent by YOLO Charters will appear here.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>


<!-- Signature Modal -->
<div class="yolo-signature-modal" id="signatureModal" style="display: none;">
    <div class="yolo-signature-modal-content">
        <div class="yolo-signature-modal-header">
            <h3>Sign Document</h3>
            <button type="button" class="yolo-signature-modal-close">&times;</button>
        </div>
        <div class="yolo-signature-modal-body">
            <p>Please sign below to confirm you have reviewed and agree to the document.</p>
            <div class="yolo-signature-pad-wrapper">
                <canvas id="guestSignaturePad" class="yolo-signature-canvas"></canvas>
            </div>
            <button type="button" class="yolo-clear-signature-btn">Clear Signature</button>
        </div>
        <div class="yolo-signature-modal-footer">
            <button type="button" class="yolo-cancel-signature-btn">Cancel</button>
            <button type="button" class="yolo-submit-signature-btn">Submit Signature</button>
        </div>
    </div>
</div>
