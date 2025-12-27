<?php
/**
 * Backoffice Document Viewer Partial
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

// Initialize variables if not set (prevents undefined variable warnings)
if (!isset($licenses)) {
    $licenses = array();
}
if (!isset($booking)) {
    $booking = (object) array(
        'bm_reservation_id' => '',
        'yacht_name' => '',
        'customer_name' => '',
        'customer_email' => '',
        'date_from' => '',
        'date_to' => '',
        'booking_status' => ''
    );
}
if (!isset($crew_list)) {
    $crew_list = array();
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

<div class="yolo-document-viewer-content">
    <h2>Charter Documents for Booking <?php echo !empty($booking->bm_reservation_id) ? '#' . esc_html($booking->bm_reservation_id) : ''; ?></h2>

    <div class="yolo-document-info-admin">
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
    <div class="yolo-crew-list-admin-section">
        <h3>Crew List (<?php echo count($crew_list); ?> members)</h3>
        <?php if ($has_crew_list): ?>
            <div class="yolo-crew-table-admin-wrapper">
                <table class="yolo-crew-table-admin">
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
            </div>
        <?php else: ?>
            <p class="yolo-no-crew-message">No crew list submitted yet.</p>
        <?php endif; ?>
    </div>

    <!-- License Section -->
    <div class="yolo-license-section-admin">
        <h3>Sailing Licenses</h3>
        <div class="yolo-license-grid-admin">
            
            <!-- Skipper 1 Licenses -->
            <div class="yolo-license-group-admin">
                <h4>Skipper 1 License <?php echo $is_s1_complete ? '✓ Complete' : '⚠ Incomplete'; ?></h4>
                
                <div class="yolo-license-item-admin">
                    <h5>Front Side</h5>
                    <?php if ($has_s1_front): ?>
                        <div class="yolo-license-preview-admin">
                            <a href="<?php echo esc_url($s1_front_license->file_url); ?>" target="_blank">
                                <img src="<?php echo esc_url($s1_front_license->file_url); ?>" alt="Skipper 1 License Front">
                            </a>
                        </div>
                        <div class="yolo-license-actions-admin">
                            <a href="<?php echo esc_url($s1_front_license->file_url); ?>" download class="button button-primary button-small">Download</a>
                            <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s1_front_license->uploaded_at)); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="yolo-not-uploaded-admin">Not uploaded yet</p>
                    <?php endif; ?>
                </div>
                
                <div class="yolo-license-item-admin">
                    <h5>Back Side</h5>
                    <?php if ($has_s1_back): ?>
                        <div class="yolo-license-preview-admin">
                            <a href="<?php echo esc_url($s1_back_license->file_url); ?>" target="_blank">
                                <img src="<?php echo esc_url($s1_back_license->file_url); ?>" alt="Skipper 1 License Back">
                            </a>
                        </div>
                        <div class="yolo-license-actions-admin">
                            <a href="<?php echo esc_url($s1_back_license->file_url); ?>" download class="button button-primary button-small">Download</a>
                            <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s1_back_license->uploaded_at)); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="yolo-not-uploaded-admin">Not uploaded yet</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Skipper 2 Licenses -->
            <div class="yolo-license-group-admin">
                <h4>Skipper 2 License <?php echo $is_s2_complete ? '✓ Complete' : '⚠ Incomplete'; ?></h4>
                
                <div class="yolo-license-item-admin">
                    <h5>Front Side</h5>
                    <?php if ($has_s2_front): ?>
                        <div class="yolo-license-preview-admin">
                            <a href="<?php echo esc_url($s2_front_license->file_url); ?>" target="_blank">
                                <img src="<?php echo esc_url($s2_front_license->file_url); ?>" alt="Skipper 2 License Front">
                            </a>
                        </div>
                        <div class="yolo-license-actions-admin">
                            <a href="<?php echo esc_url($s2_front_license->file_url); ?>" download class="button button-primary button-small">Download</a>
                            <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s2_front_license->uploaded_at)); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="yolo-not-uploaded-admin">Not uploaded yet</p>
                    <?php endif; ?>
                </div>
                
                <div class="yolo-license-item-admin">
                    <h5>Back Side</h5>
                    <?php if ($has_s2_back): ?>
                        <div class="yolo-license-preview-admin">
                            <a href="<?php echo esc_url($s2_back_license->file_url); ?>" target="_blank">
                                <img src="<?php echo esc_url($s2_back_license->file_url); ?>" alt="Skipper 2 License Back">
                            </a>
                        </div>
                        <div class="yolo-license-actions-admin">
                            <a href="<?php echo esc_url($s2_back_license->file_url); ?>" download class="button button-primary button-small">Download</a>
                            <span class="yolo-upload-date-admin">Uploaded: <?php echo date('M j, Y g:i A', strtotime($s2_back_license->uploaded_at)); ?></span>
                        </div>
                    <?php else: ?>
                        <p class="yolo-not-uploaded-admin">Not uploaded yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Admin Documents Section -->
    <?php
    // Fetch admin-uploaded documents for this booking
    global $wpdb;
    $table_admin_docs = $wpdb->prefix . 'yolo_admin_documents';
    $admin_documents = $wpdb->get_results($wpdb->prepare(
        "SELECT ad.*, u.display_name as uploader_name 
        FROM {$table_admin_docs} ad 
        LEFT JOIN {$wpdb->users} u ON ad.uploaded_by = u.ID 
        WHERE ad.booking_id = %d 
        ORDER BY ad.uploaded_at DESC",
        $booking->id
    ));
    ?>
    <div class="yolo-admin-documents-section">
        <h3>Send Documents to Guest</h3>
        <p class="yolo-section-description">Upload documents that will be visible to the guest in their dashboard.</p>
        
        <form id="yolo-admin-upload-form" class="yolo-admin-upload-form" enctype="multipart/form-data">
            <input type="hidden" name="booking_id" value="<?php echo esc_attr($booking->id); ?>">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('yolo_admin_upload_document_nonce'); ?>">
            
            <div class="yolo-upload-field">
                <label for="yolo-admin-doc-file"><strong>Select File:</strong></label>
                <input type="file" id="yolo-admin-doc-file" name="document_file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
            </div>
            
            <div class="yolo-upload-field">
                <label for="yolo-admin-doc-description"><strong>Description (Optional):</strong></label>
                <input type="text" id="yolo-admin-doc-description" name="description" placeholder="e.g., Charter Agreement, Safety Instructions" maxlength="255">
            </div>
            
            <button type="submit" class="button button-primary">Upload Document</button>
            <span class="yolo-upload-status"></span>
        </form>
        
        <div class="yolo-admin-documents-list">
            <h4>Sent Documents (<?php echo count($admin_documents); ?>)</h4>
            <?php if (!empty($admin_documents)): ?>
                <table class="yolo-documents-table-admin">
                    <thead>
                        <tr>
                            <th>File Name</th>
                            <th>Description</th>
                            <th>Uploaded By</th>
                            <th>Upload Date</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admin_documents as $doc): ?>
                            <tr>
                                <td><?php echo esc_html($doc->file_name); ?></td>
                                <td><?php echo esc_html($doc->description ?: '-'); ?></td>
                                <td><?php echo esc_html($doc->uploader_name ?: 'Unknown'); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($doc->uploaded_at)); ?></td>
                                <td><?php echo $doc->file_size ? size_format($doc->file_size) : '-'; ?></td>
                                <td>
                                    <a href="<?php echo esc_url($doc->file_url); ?>" target="_blank" class="button button-small">View</a>
                                    <a href="<?php echo esc_url($doc->file_url); ?>" download class="button button-small">Download</a>
                                    <button class="button button-small yolo-delete-admin-doc" data-doc-id="<?php echo esc_attr($doc->id); ?>" data-booking-id="<?php echo esc_attr($booking->id); ?>">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="yolo-no-documents-message">No documents sent to guest yet.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="yolo-action-buttons-admin">
        <a href="<?php echo admin_url('admin-ajax.php') . '?action=yolo_admin_export_crew_list_csv&booking_id=' . $booking->id . '&nonce=' . wp_create_nonce('yolo_admin_documents_nonce'); ?>" class="button button-primary" download>Export Crew List (CSV)</a>
        <a href="<?php echo admin_url('admin-ajax.php') . '?action=yolo_admin_download_all_licenses_zip&booking_id=' . $booking->id . '&nonce=' . wp_create_nonce('yolo_admin_documents_nonce'); ?>" class="button button-primary" download>Download All Licenses (ZIP)</a>
    </div>
</div>
