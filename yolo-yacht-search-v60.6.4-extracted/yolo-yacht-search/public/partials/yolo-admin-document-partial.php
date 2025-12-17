<?php
/**
 * Admin Document Partial Template
 * Used by the [yolo_admin_documents] shortcode to display a single booking's documents.
 * 
 * Variables available:
 * - $booking (object)
 * - $licenses (array of objects)
 * - $crew_list (array of objects)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// --- License Processing ---
$s1_front_license = null;
$s1_back_license = null;
$s2_front_license = null;
$s2_back_license = null;

foreach ($licenses as $license) {
    if ($license->file_type === 'front') {
        $s1_front_license = $license;
    }
    if ($license->file_type === 'back') {
        $s1_back_license = $license;
    }
    if ($license->file_type === 'skipper2_front') {
        $s2_front_license = $license;
    }
    if ($license->file_type === 'skipper2_back') {
        $s2_back_license = $license;
    }
}

$has_s1_front = !is_null($s1_front_license);
$has_s1_back = !is_null($s1_back_license);
$has_s2_front = !is_null($s2_front_license);
$has_s2_back = !is_null($s2_back_license);

$is_s1_complete = $has_s1_front && $has_s1_back;
$is_s2_complete = $has_s2_front && $has_s2_back;
$has_crew_list = !empty($crew_list);

?>

<div class="yolo-document-partial">
    <h2>Charter Documents for Booking #<?php echo esc_html($booking->id); ?></h2>

    <div class="yolo-document-info">
        <div>
            <p><strong>Yacht:</strong> <?php echo esc_html($booking->yacht_name); ?></p>
            <p><strong>Customer:</strong> <?php echo esc_html($booking->customer_name); ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>"><?php echo esc_html($booking->customer_email); ?></a></p>
        </div>
        <div>
            <p><strong>Check-in:</strong> <?php echo date('M j, Y', strtotime($booking->date_from)); ?></p>
            <p><strong>Check-out:</strong> <?php echo date('M j, Y', strtotime($booking->date_to)); ?></p>
            <p><strong>Status:</strong> <?php echo esc_html(ucfirst($booking->booking_status)); ?></p>
        </div>
    </div>

    <!-- Crew List Section -->
    <div class="yolo-crew-list-frontend">
        <h3>Crew List (<?php echo count($crew_list); ?> members)</h3>
        <?php if ($has_crew_list): ?>
            <table class="yolo-crew-table-frontend">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Sex</th>
                        <th>ID Type</th>
                        <th>ID No.</th>
                        <th>Birth Date</th>
                        <th>Role</th>
                        <th>Mobile</th>
                        <th>Nationality</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count = 1; foreach ($crew_list as $member): ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></td>
                            <td><?php echo esc_html(ucfirst($member->sex)); ?></td>
                            <td><?php echo esc_html(ucwords(str_replace('_', ' ', $member->id_type))); ?></td>
                            <td><?php echo esc_html($member->id_number); ?></td>
                            <td><?php echo esc_html($member->birth_date); ?></td>
                            <td><?php echo esc_html(ucfirst($member->role)); ?></td>
                            <td><?php echo esc_html($member->mobile_number); ?></td>
                            <td><?php echo esc_html($member->nationality); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="yolo-not-uploaded" style="padding: 15px;">No crew list submitted yet.</p>
        <?php endif; ?>
    </div>

    <!-- License Section -->
    <h3>Sailing Licenses</h3>
    <div class="yolo-license-grid-admin">
        
        <!-- Skipper 1 Licenses -->
        <div class="yolo-license-group-admin">
            <h4>Skipper 1 License (Status: <?php echo $is_s1_complete ? 'Complete' : 'Incomplete'; ?>)</h4>
            
            <div class="yolo-license-image-item-admin">
                <h5>Front Side</h5>
                <?php if ($has_s1_front): ?>
                    <div class="yolo-license-preview-admin">
                        <a href="<?php echo esc_url($s1_front_license->file_url); ?>" target="_blank">
                            <img src="<?php echo esc_url($s1_front_license->file_url); ?>" alt="Skipper 1 License Front">
                        </a>
                    </div>
                    <div class="yolo-license-actions-admin">
                        <a href="<?php echo esc_url($s1_front_license->file_url); ?>" download class="button button-primary">Download</a>
                        <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s1_front_license->uploaded_at)); ?></span>
                    </div>
                <?php else: ?>
                    <p class="yolo-not-uploaded">Not uploaded yet</p>
                <?php endif; ?>
            </div>
            
            <div class="yolo-license-image-item-admin">
                <h5>Back Side</h5>
                <?php if ($has_s1_back): ?>
                    <div class="yolo-license-preview-admin">
                        <a href="<?php echo esc_url($s1_back_license->file_url); ?>" target="_blank">
                            <img src="<?php echo esc_url($s1_back_license->file_url); ?>" alt="Skipper 1 License Back">
                        </a>
                    </div>
                    <div class="yolo-license-actions-admin">
                        <a href="<?php echo esc_url($s1_back_license->file_url); ?>" download class="button button-primary">Download</a>
                        <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s1_back_license->uploaded_at)); ?></span>
                    </div>
                <?php else: ?>
                    <p class="yolo-not-uploaded">Not uploaded yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Skipper 2 Licenses -->
        <div class="yolo-license-group-admin">
            <h4>Skipper 2 License (Status: <?php echo $is_s2_complete ? 'Complete' : 'Incomplete'; ?>)</h4>
            
            <div class="yolo-license-image-item-admin">
                <h5>Front Side</h5>
                <?php if ($has_s2_front): ?>
                    <div class="yolo-license-preview-admin">
                        <a href="<?php echo esc_url($s2_front_license->file_url); ?>" target="_blank">
                            <img src="<?php echo esc_url($s2_front_license->file_url); ?>" alt="Skipper 2 License Front">
                        </a>
                    </div>
                    <div class="yolo-license-actions-admin">
                        <a href="<?php echo esc_url($s2_front_license->file_url); ?>" download class="button button-primary">Download</a>
                        <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s2_front_license->uploaded_at)); ?></span>
                    </div>
                <?php else: ?>
                    <p class="yolo-not-uploaded">Not uploaded yet</p>
                <?php endif; ?>
            </div>
            
            <div class="yolo-license-image-item-admin">
                <h5>Back Side</h5>
                <?php if ($has_s2_back): ?>
                    <div class="yolo-license-preview-admin">
                        <a href="<?php echo esc_url($s2_back_license->file_url); ?>" target="_blank">
                            <img src="<?php echo esc_url($s2_back_license->file_url); ?>" alt="Skipper 2 License Back">
                        </a>
                    </div>
                    <div class="yolo-license-actions-admin">
                        <a href="<?php echo esc_url($s2_back_license->file_url); ?>" download class="button button-primary">Download</a>
                        <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s2_back_license->uploaded_at)); ?></span>
                    </div>
                <?php else: ?>
                    <p class="yolo-not-uploaded">Not uploaded yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="yolo-action-buttons">
        <a href="<?php echo admin_url('admin-ajax.php') . '?action=yolo_export_crew_list_csv&booking_id=' . $booking->id . '&nonce=' . wp_create_nonce('yolo_admin_documents_nonce'); ?>" class="button button-primary" download>Export Crew List (CSV)</a>
        <a href="<?php echo admin_url('admin-ajax.php') . '?action=yolo_download_all_licenses_zip&booking_id=' . $booking->id . '&nonce=' . wp_create_nonce('yolo_admin_documents_nonce'); ?>" class="button button-primary" download>Download All Licenses (ZIP)</a>
    </div>
</div>
