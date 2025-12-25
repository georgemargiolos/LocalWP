<?php
/**
 * Yacht Customization Admin Page
 * 
 * Allows admins to:
 * - Customize media (images/videos) order per yacht
 * - Add custom videos (YouTube or uploaded)
 * - Override synced description with custom HTML
 * 
 * @since 65.14
 * @updated 81.10 - Added company filter dropdown
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get database instance
global $wpdb;
$yachts_table = $wpdb->prefix . 'yolo_yachts';
$images_table = $wpdb->prefix . 'yolo_yacht_images';
$custom_media_table = $wpdb->prefix . 'yolo_yacht_custom_media';
$custom_settings_table = $wpdb->prefix . 'yolo_yacht_custom_settings';

// Get YOLO company ID
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);

// Get friend company IDs
$friend_ids_raw = get_option('yolo_ys_friend_companies', '4366,3604,6711');
$friend_company_ids = array_filter(array_map('trim', explode(',', $friend_ids_raw)));

// Build company list with names
$companies = array();

// Add YOLO first
$companies[$yolo_company_id] = array(
    'id' => $yolo_company_id,
    'name' => 'YOLO Charters',
    'is_yolo' => true
);

// Add friend companies with names from API cache or database
$company_names = get_option('yolo_ys_company_names_cache', array());
foreach ($friend_company_ids as $company_id) {
    $company_name = isset($company_names[$company_id]) ? $company_names[$company_id] : "Company $company_id";
    $companies[$company_id] = array(
        'id' => $company_id,
        'name' => $company_name,
        'is_yolo' => false
    );
}

// Get selected company (default to YOLO)
$selected_company_id = isset($_GET['company_id']) ? sanitize_text_field($_GET['company_id']) : '';

// Get yachts based on company filter
if ($selected_company_id) {
    $yachts = $wpdb->get_results($wpdb->prepare(
        "SELECT id, name, model, company_id FROM {$yachts_table} WHERE company_id = %s ORDER BY name ASC",
        $selected_company_id
    ));
} else {
    // If no company selected, show empty yacht list
    $yachts = array();
}

// Get selected yacht
$selected_yacht_id = isset($_GET['yacht_id']) ? sanitize_text_field($_GET['yacht_id']) : '';
$selected_yacht = null;
$yacht_images = array();
$yacht_custom_media = array();
$yacht_settings = null;

if ($selected_yacht_id) {
    // Get yacht details
    $selected_yacht = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$yachts_table} WHERE id = %s",
        $selected_yacht_id
    ));
    
    // Get synced images
    $yacht_images = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$images_table} WHERE yacht_id = %s ORDER BY sort_order ASC",
        $selected_yacht_id
    ));
    
    // Get custom media
    $yacht_custom_media = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$custom_media_table} WHERE yacht_id = %s ORDER BY sort_order ASC",
        $selected_yacht_id
    ));
    
    // Get custom settings
    $yacht_settings = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$custom_settings_table} WHERE yacht_id = %s",
        $selected_yacht_id
    ));
}

// Count yachts per company for display
$company_yacht_counts = array();
foreach ($companies as $company_id => $company) {
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$yachts_table} WHERE company_id = %s",
        $company_id
    ));
    $company_yacht_counts[$company_id] = $count;
}
?>

<div class="wrap yolo-yacht-customization">
    <h1><span class="dashicons dashicons-images-alt2"></span> Yacht Customization</h1>
    <p class="description">Customize media (images & videos) and descriptions for individual yachts. Changes here override synced data from Booking Manager.</p>
    
    <!-- Company & Yacht Selector -->
    <div class="yacht-selector-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin: 20px 0; border-radius: 4px;">
        <form method="get" action="" id="yacht-selector-form">
            <input type="hidden" name="page" value="yolo-yacht-customization">
            
            <div style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                <!-- Company Dropdown -->
                <div>
                    <label for="company_id" style="display: block; font-weight: 600; margin-bottom: 5px;">
                        <span class="dashicons dashicons-building" style="vertical-align: middle;"></span> Select Company:
                    </label>
                    <select name="company_id" id="company_id" style="min-width: 250px; padding: 8px;">
                        <option value="">-- Choose a company --</option>
                        <?php foreach ($companies as $company_id => $company): ?>
                            <?php 
                            $count = isset($company_yacht_counts[$company_id]) ? $company_yacht_counts[$company_id] : 0;
                            $badge = $company['is_yolo'] ? ' ⭐' : '';
                            ?>
                            <option value="<?php echo esc_attr($company_id); ?>" <?php selected($selected_company_id, $company_id); ?>>
                                <?php echo esc_html($company['name'] . $badge . ' (' . $count . ' boats)'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Yacht Dropdown -->
                <div>
                    <label for="yacht_id" style="display: block; font-weight: 600; margin-bottom: 5px;">
                        <span class="dashicons dashicons-sos" style="vertical-align: middle;"></span> Select Yacht:
                    </label>
                    <select name="yacht_id" id="yacht_id" style="min-width: 350px; padding: 8px;" <?php echo empty($yachts) ? 'disabled' : ''; ?>>
                        <option value="">-- <?php echo empty($selected_company_id) ? 'Select a company first' : 'Choose a yacht'; ?> --</option>
                        <?php foreach ($yachts as $yacht): ?>
                            <option value="<?php echo esc_attr($yacht->id); ?>" <?php selected($selected_yacht_id, $yacht->id); ?>>
                                <?php echo esc_html($yacht->name . ' - ' . $yacht->model); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="button button-primary" style="height: 36px;">
                    <span class="dashicons dashicons-search" style="vertical-align: middle;"></span> Load Yacht
                </button>
            </div>
            
            <?php if ($selected_company_id && count($yachts) > 0): ?>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <span class="dashicons dashicons-info" style="vertical-align: middle;"></span>
                    Found <strong><?php echo count($yachts); ?></strong> yachts for <?php echo esc_html($companies[$selected_company_id]['name']); ?>
                </p>
            <?php elseif ($selected_company_id && count($yachts) === 0): ?>
                <p style="margin: 10px 0 0 0; color: #d63638;">
                    <span class="dashicons dashicons-warning" style="vertical-align: middle;"></span>
                    No yachts found for this company. Please run a sync first.
                </p>
            <?php endif; ?>
        </form>
    </div>
    
    <script>
    // Auto-submit when company changes to load yachts
    document.getElementById('company_id').addEventListener('change', function() {
        // Clear yacht selection when company changes
        document.getElementById('yacht_id').value = '';
        // Submit form to reload with new company
        document.getElementById('yacht-selector-form').submit();
    });
    </script>
    
    <?php if ($selected_yacht): ?>
        <!-- Yacht Info Header -->
        <div class="yacht-info-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="margin: 0 0 5px 0; color: #fff;">
                <?php echo esc_html($selected_yacht->name); ?>
                <?php if ($selected_yacht->company_id == $yolo_company_id): ?>
                    <span style="background: #ffd700; color: #333; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">YOLO FLEET</span>
                <?php else: ?>
                    <span style="background: #17a2b8; color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">PARTNER</span>
                <?php endif; ?>
            </h2>
            <p style="margin: 0; opacity: 0.9;"><?php echo esc_html($selected_yacht->model); ?> | ID: <?php echo esc_html($selected_yacht->id); ?> | Company: <?php echo esc_html($companies[$selected_yacht->company_id]['name'] ?? 'Unknown'); ?></p>
        </div>
        
        <!-- Media Section -->
        <div class="customization-section media-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; border-radius: 4px;">
            <h3><span class="dashicons dashicons-format-gallery"></span> Media (Images & Videos)</h3>
            
            <div class="toggle-section" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="use_custom_media" name="use_custom_media" 
                           <?php checked($yacht_settings && $yacht_settings->use_custom_media, 1); ?>
                           style="margin-right: 10px; width: 18px; height: 18px;">
                    <strong>Use local images & videos</strong>
                </label>
                <p class="description" style="margin: 5px 0 0 28px;">
                    When enabled, this yacht will display custom media you manage here instead of synced images from Booking Manager.
                </p>
            </div>
            
            <!-- Synced Images Preview (always visible) -->
            <div class="synced-images-section" style="margin-bottom: 20px;">
                <h4>Synced Images from Booking Manager (<?php echo count($yacht_images); ?> images)</h4>
                <div class="image-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px;">
                    <?php if (empty($yacht_images)): ?>
                        <p style="color: #666;">No synced images available.</p>
                    <?php else: ?>
                        <?php foreach ($yacht_images as $image): ?>
                            <div class="image-thumb" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                                <img src="<?php echo esc_url($image->image_url); ?>" 
                                     alt="<?php echo esc_attr($selected_yacht->name); ?>"
                                     style="width: 100%; height: 80px; object-fit: cover;">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <button type="button" id="copy-synced-images" class="button" style="margin-top: 10px;" 
                        <?php echo empty($yacht_images) ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-migrate" style="vertical-align: middle;"></span>
                    Copy Synced Images to Custom Media
                </button>
            </div>
            
            <!-- Custom Media Section (enabled when toggle is on) -->
            <div id="custom-media-section" style="<?php echo ($yacht_settings && $yacht_settings->use_custom_media) ? '' : 'opacity: 0.5; pointer-events: none;'; ?>">
                <h4>Custom Media (Drag to reorder)</h4>
                
                <div id="custom-media-grid" class="sortable-media-grid" 
                     style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; min-height: 100px; padding: 10px; background: #f0f0f1; border-radius: 4px;">
                    <?php if (empty($yacht_custom_media)): ?>
                        <p class="no-media-message" style="color: #666; grid-column: 1/-1;">No custom media yet. Use "Copy Synced Images" or add new media below.</p>
                    <?php else: ?>
                        <?php foreach ($yacht_custom_media as $media): ?>
                            <div class="media-item" data-id="<?php echo esc_attr($media->id); ?>" data-type="<?php echo esc_attr($media->media_type); ?>"
                                 style="background: #fff; border: 2px solid #ddd; border-radius: 4px; overflow: hidden; cursor: move; position: relative;">
                                <?php if ($media->media_type === 'video'): ?>
                                    <div style="position: relative;">
                                        <img src="<?php echo esc_url($media->thumbnail_url ?: 'https://img.youtube.com/vi/' . $media->media_url . '/mqdefault.jpg'); ?>" 
                                             style="width: 100%; height: 100px; object-fit: cover;">
                                        <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); color: #fff; padding: 5px 10px; border-radius: 4px;">
                                            <span class="dashicons dashicons-video-alt3"></span>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo esc_url($media->media_url); ?>" style="width: 100%; height: 100px; object-fit: cover;">
                                <?php endif; ?>
                                <div style="padding: 5px; font-size: 11px; background: #f8f9fa;">
                                    <span class="media-type-badge" style="background: <?php echo $media->media_type === 'video' ? '#e74c3c' : '#3498db'; ?>; color: #fff; padding: 1px 5px; border-radius: 2px; font-size: 10px;">
                                        <?php echo strtoupper($media->media_type); ?>
                                    </span>
                                    <button type="button" class="delete-media-btn" data-id="<?php echo esc_attr($media->id); ?>" 
                                            style="float: right; background: none; border: none; color: #dc3545; cursor: pointer; padding: 0;">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Add Media Buttons -->
                <div class="add-media-buttons" style="margin-top: 15px; display: flex; gap: 10px;">
                    <button type="button" id="add-image-btn" class="button">
                        <span class="dashicons dashicons-format-image" style="vertical-align: middle;"></span>
                        Add Image
                    </button>
                    <button type="button" id="add-video-btn" class="button">
                        <span class="dashicons dashicons-video-alt3" style="vertical-align: middle;"></span>
                        Add Video
                    </button>
                    <button type="button" id="save-media-order" class="button button-primary" style="margin-left: auto;">
                        <span class="dashicons dashicons-saved" style="vertical-align: middle;"></span>
                        Save Order
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Starting From Price Section (v75.11) -->
        <div class="customization-section price-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; border-radius: 4px;">
            <h3><span class="dashicons dashicons-money-alt"></span> Starting From Price</h3>
            <p class="description">Set a custom "Starting from" price for this yacht. This overrides the calculated weekly price.</p>
            
            <div style="display: flex; gap: 10px; align-items: center; margin-top: 15px;">
                <span style="font-size: 18px; font-weight: bold;">€</span>
                <input type="number" id="starting_from_price" name="starting_from_price" 
                       value="<?php echo esc_attr($yacht_settings ? $yacht_settings->starting_from_price : ''); ?>"
                       placeholder="e.g., 2500"
                       style="width: 150px; padding: 8px; font-size: 16px;">
                <span style="color: #666;">/week</span>
                <button type="button" id="save-starting-price" class="button button-primary">
                    <span class="dashicons dashicons-saved" style="vertical-align: middle;"></span>
                    Save Price
                </button>
            </div>
            
            <?php if ($yacht_settings && $yacht_settings->starting_from_price): ?>
                <p style="margin-top: 10px; color: #00a32a;">
                    <span class="dashicons dashicons-yes-alt" style="vertical-align: middle;"></span>
                    Custom price set: <strong>€<?php echo number_format($yacht_settings->starting_from_price, 0); ?>/week</strong>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Description Section -->
        <div class="customization-section description-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; border-radius: 4px;">
            <h3><span class="dashicons dashicons-edit"></span> Custom Description</h3>
            
            <div class="toggle-section" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="use_custom_description" name="use_custom_description" 
                           <?php checked($yacht_settings && $yacht_settings->use_custom_description, 1); ?>
                           style="margin-right: 10px; width: 18px; height: 18px;">
                    <strong>Use custom description</strong>
                </label>
                <p class="description" style="margin: 5px 0 0 28px;">
                    When enabled, this yacht will display your custom description instead of the synced one from Booking Manager.
                </p>
            </div>
            
            <!-- Synced Description Preview -->
            <div class="synced-description-section" style="margin-bottom: 20px;">
                <h4>Synced Description from Booking Manager</h4>
                <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; max-height: 200px; overflow-y: auto; border: 1px solid #ddd;">
                    <?php if (!empty($selected_yacht->description)): ?>
                        <?php echo wp_kses_post($selected_yacht->description); ?>
                    <?php else: ?>
                        <p style="color: #666; font-style: italic;">No synced description available.</p>
                    <?php endif; ?>
                </div>
                <button type="button" id="copy-synced-description" class="button" style="margin-top: 10px;"
                        <?php echo empty($selected_yacht->description) ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-migrate" style="vertical-align: middle;"></span>
                    Copy to Custom Description
                </button>
            </div>
            
            <!-- Custom Description Editor -->
            <div id="custom-description-section" style="<?php echo ($yacht_settings && $yacht_settings->use_custom_description) ? '' : 'opacity: 0.5; pointer-events: none;'; ?>">
                <h4>Custom Description (HTML supported)</h4>
                <?php 
                $custom_desc = $yacht_settings ? $yacht_settings->custom_description : '';
                wp_editor($custom_desc, 'custom_description_editor', array(
                    'textarea_rows' => 10,
                    'media_buttons' => false,
                    'teeny' => false,
                    'quicktags' => true,
                ));
                ?>
                <button type="button" id="save-description" class="button button-primary" style="margin-top: 10px;">
                    <span class="dashicons dashicons-saved" style="vertical-align: middle;"></span>
                    Save Description
                </button>
            </div>
        </div>
        
    <?php else: ?>
        <!-- No yacht selected message -->
        <div class="no-yacht-selected" style="background: #fff; padding: 40px; text-align: center; border: 1px solid #ccd0d4; border-radius: 4px;">
            <span class="dashicons dashicons-sos" style="font-size: 48px; color: #ccc;"></span>
            <h2 style="color: #666;">Select a yacht to customize</h2>
            <p style="color: #999;">Choose a company from the dropdown above, then select a yacht to manage its media and description.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.yolo-yacht-customization .sortable-media-grid .media-item:hover {
    border-color: #007cba;
}
.yolo-yacht-customization .sortable-media-grid .ui-sortable-placeholder {
    background: #e0e0e0;
    border: 2px dashed #999;
    visibility: visible !important;
    height: 130px;
}
.yolo-yacht-customization .dashicons {
    vertical-align: middle;
}
</style>

<?php if ($selected_yacht): ?>
<script>
jQuery(document).ready(function($) {
    var yachtId = '<?php echo esc_js($selected_yacht_id); ?>';
    var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
    var nonce = '<?php echo wp_create_nonce('yolo_yacht_customization_nonce'); ?>';
    
    // Toggle custom media section
    $('#use_custom_media').on('change', function() {
        var enabled = $(this).is(':checked');
        $('#custom-media-section').css({
            'opacity': enabled ? 1 : 0.5,
            'pointer-events': enabled ? 'auto' : 'none'
        });
        
        // Save setting via AJAX
        $.post(ajaxUrl, {
            action: 'yolo_save_yacht_custom_setting',
            yacht_id: yachtId,
            setting: 'use_custom_media',
            value: enabled ? 1 : 0,
            nonce: nonce
        });
    });
    
    // Toggle custom description section
    $('#use_custom_description').on('change', function() {
        var enabled = $(this).is(':checked');
        $('#custom-description-section').css({
            'opacity': enabled ? 1 : 0.5,
            'pointer-events': enabled ? 'auto' : 'none'
        });
        
        // Save setting via AJAX
        $.post(ajaxUrl, {
            action: 'yolo_save_yacht_custom_setting',
            yacht_id: yachtId,
            setting: 'use_custom_description',
            value: enabled ? 1 : 0,
            nonce: nonce
        });
    });
    
    // Initialize sortable for custom media
    $('#custom-media-grid').sortable({
        items: '.media-item',
        placeholder: 'ui-sortable-placeholder',
        tolerance: 'pointer',
        update: function(event, ui) {
            // Order will be saved when clicking "Save Order"
        }
    });
    
    // Copy synced images to custom media
    $('#copy-synced-images').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Copying...');
        
        $.post(ajaxUrl, {
            action: 'yolo_copy_synced_images',
            yacht_id: yachtId,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data);
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-migrate" style="vertical-align: middle;"></span> Copy Synced Images to Custom Media');
            }
        });
    });
    
    // Add image via media library
    $('#add-image-btn').on('click', function() {
        var frame = wp.media({
            title: 'Select Image for Yacht',
            button: { text: 'Add to Yacht' },
            multiple: true
        });
        
        frame.on('select', function() {
            var attachments = frame.state().get('selection').toJSON();
            var promises = [];
            
            attachments.forEach(function(attachment) {
                promises.push($.post(ajaxUrl, {
                    action: 'yolo_add_custom_media',
                    yacht_id: yachtId,
                    media_type: 'image',
                    media_url: attachment.url,
                    thumbnail_url: attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url,
                    nonce: nonce
                }));
            });
            
            $.when.apply($, promises).done(function() {
                location.reload();
            });
        });
        
        frame.open();
    });
    
    // Add video modal
    $('#add-video-btn').on('click', function() {
        var videoUrl = prompt('Enter YouTube video URL or video ID:');
        if (videoUrl) {
            // Extract video ID from URL if needed
            var videoId = videoUrl;
            var match = videoUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\s]+)/);
            if (match) {
                videoId = match[1];
            }
            
            $.post(ajaxUrl, {
                action: 'yolo_add_custom_media',
                yacht_id: yachtId,
                media_type: 'video',
                media_url: videoId,
                thumbnail_url: 'https://img.youtube.com/vi/' + videoId + '/mqdefault.jpg',
                nonce: nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data);
                }
            });
        }
    });
    
    // Delete media item
    $(document).on('click', '.delete-media-btn', function() {
        if (!confirm('Delete this media item?')) return;
        
        var mediaId = $(this).data('id');
        var $item = $(this).closest('.media-item');
        
        $.post(ajaxUrl, {
            action: 'yolo_delete_custom_media',
            media_id: mediaId,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                $item.fadeOut(300, function() { $(this).remove(); });
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
    
    // Save media order
    $('#save-media-order').on('click', function() {
        var order = [];
        $('#custom-media-grid .media-item').each(function(index) {
            order.push({
                id: $(this).data('id'),
                sort_order: index
            });
        });
        
        $.post(ajaxUrl, {
            action: 'yolo_save_media_order',
            yacht_id: yachtId,
            order: JSON.stringify(order),
            nonce: nonce
        }, function(response) {
            alert('Order saved!');
        }, 500);
    });
    
    // Copy synced description (will be implemented in Phase 3)
    $('#copy-synced-description').on('click', function() {
        var syncedDesc = <?php echo json_encode($selected_yacht ? $selected_yacht->description : ''); ?>;
        if (typeof tinymce !== 'undefined' && tinymce.get('custom_description_editor')) {
            tinymce.get('custom_description_editor').setContent(syncedDesc);
        } else {
            $('#custom_description_editor').val(syncedDesc);
        }
    });
    
    // Save starting from price (v75.11)
    $('#save-starting-price').on('click', function() {
        var $btn = $(this);
        var price = $('#starting_from_price').val();
        
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update" style="vertical-align: middle;"></span> Saving...');
        
        $.post(ajaxUrl, {
            action: 'yolo_save_yacht_custom_setting',
            yacht_id: yachtId,
            setting: 'starting_from_price',
            value: price,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                $btn.html('<span class="dashicons dashicons-yes" style="vertical-align: middle;"></span> Saved!');
                setTimeout(function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="vertical-align: middle;"></span> Save Price');
                }, 1500);
            } else {
                alert('Error saving price: ' + response.data);
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="vertical-align: middle;"></span> Save Price');
            }
        });
    });
    
    // Save description (will be fully implemented in Phase 3)
    $('#save-description').on('click', function() {
        var content = '';
        if (typeof tinymce !== 'undefined' && tinymce.get('custom_description_editor')) {
            content = tinymce.get('custom_description_editor').getContent();
        } else {
            content = $('#custom_description_editor').val();
        }
        
        $.post(ajaxUrl, {
            action: 'yolo_save_yacht_custom_description',
            yacht_id: yachtId,
            description: content,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                alert('Description saved successfully!');
            } else {
                alert('Error saving description: ' + response.data);
            }
        });
    });
});
</script>
<?php endif; ?>
