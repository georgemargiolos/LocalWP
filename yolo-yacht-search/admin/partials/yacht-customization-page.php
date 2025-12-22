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

// Get all yachts for dropdown
$yachts = $wpdb->get_results("SELECT id, name, model, company_id FROM {$yachts_table} ORDER BY name ASC");

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

// Get YOLO company ID for badge display
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);
?>

<div class="wrap yolo-yacht-customization">
    <h1><span class="dashicons dashicons-images-alt2"></span> Yacht Customization</h1>
    <p class="description">Customize media (images & videos) and descriptions for individual yachts. Changes here override synced data from Booking Manager.</p>
    
    <!-- Yacht Selector -->
    <div class="yacht-selector-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin: 20px 0; border-radius: 4px;">
        <form method="get" action="">
            <input type="hidden" name="page" value="yolo-yacht-customization">
            <label for="yacht_id" style="font-weight: 600; margin-right: 10px;">Select Yacht:</label>
            <select name="yacht_id" id="yacht_id" style="min-width: 300px; padding: 8px;" onchange="this.form.submit()">
                <option value="">-- Choose a yacht --</option>
                <?php foreach ($yachts as $yacht): ?>
                    <?php 
                    $is_yolo = ($yacht->company_id == $yolo_company_id);
                    $badge = $is_yolo ? ' [YOLO]' : '';
                    ?>
                    <option value="<?php echo esc_attr($yacht->id); ?>" <?php selected($selected_yacht_id, $yacht->id); ?>>
                        <?php echo esc_html($yacht->name . ' - ' . $yacht->model . $badge); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    
    <?php if ($selected_yacht): ?>
        <!-- Yacht Info Header -->
        <div class="yacht-info-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <h2 style="margin: 0 0 5px 0; color: #fff;">
                <?php echo esc_html($selected_yacht->name); ?>
                <?php if ($selected_yacht->company_id == $yolo_company_id): ?>
                    <span style="background: #ffd700; color: #333; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 10px;">YOLO FLEET</span>
                <?php endif; ?>
            </h2>
            <p style="margin: 0; opacity: 0.9;"><?php echo esc_html($selected_yacht->model); ?> | ID: <?php echo esc_html($selected_yacht->id); ?></p>
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
        
        <!-- Description Section -->
        <div class="customization-section description-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; border-radius: 4px;">
            <h3><span class="dashicons dashicons-editor-paragraph"></span> Description</h3>
            
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
                        <p style="color: #666; font-style: italic;">No description synced from Booking Manager.</p>
                    <?php endif; ?>
                </div>
                
                <button type="button" id="copy-synced-description" class="button" style="margin-top: 10px;"
                        <?php echo empty($selected_yacht->description) ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-migrate" style="vertical-align: middle;"></span>
                    Copy to Custom Description Editor
                </button>
            </div>
            
            <!-- Custom Description Editor -->
            <div id="custom-description-section" style="<?php echo ($yacht_settings && $yacht_settings->use_custom_description) ? '' : 'opacity: 0.5; pointer-events: none;'; ?>">
                <h4>Custom Description (HTML Editor)</h4>
                <?php
                $custom_description = $yacht_settings ? $yacht_settings->custom_description : '';
                wp_editor($custom_description, 'custom_description_editor', array(
                    'textarea_name' => 'custom_description',
                    'textarea_rows' => 10,
                    'media_buttons' => true,
                    'teeny' => false,
                    'quicktags' => true,
                ));
                ?>
                
                <button type="button" id="save-description" class="button button-primary" style="margin-top: 15px;">
                    <span class="dashicons dashicons-saved" style="vertical-align: middle;"></span>
                    Save Description
                </button>
            </div>
        </div>
        
        <!-- Starting From Price Section (all boats) -->
        <div class="customization-section pricing-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; border-radius: 4px;">
            <h3><span class="dashicons dashicons-money-alt"></span> Starting From Price (for Facebook/Google Ads)</h3>
            <p class="description">This price is used for Facebook Pixel ViewContent events and Google Analytics view_item events. Set this to your lowest weekly charter rate.</p>
            
            <div style="margin-top: 15px;">
                <label for="starting_from_price" style="font-weight: 600; display: block; margin-bottom: 5px;">Starting From Price (EUR):</label>
                <input type="number" id="starting_from_price" name="starting_from_price" 
                       value="<?php 
                           // v75.11: Load starting_from_price from column (not key-value)
                           $starting_price = $yacht_settings && isset($yacht_settings->starting_from_price) ? $yacht_settings->starting_from_price : '';
                           // Only show if > 0
                           echo esc_attr($starting_price > 0 ? $starting_price : '');
                       ?>"
                       placeholder="e.g., 2500"
                       style="width: 150px; padding: 8px;" min="0" step="1">
                <span style="color: #666; margin-left: 10px;">EUR</span>
                
                <button type="button" id="save-starting-price" class="button button-primary" style="margin-left: 15px;">
                    <span class="dashicons dashicons-saved" style="vertical-align: middle;"></span>
                    Save Price
                </button>
                
                <p class="description" style="margin-top: 10px;">
                    <strong>Tip:</strong> This should match the price you set in your Facebook Product Catalog for this yacht.
                </p>
            </div>
        </div>
        
    <?php else: ?>
        <!-- No yacht selected message -->
        <div style="background: #fff; padding: 40px; text-align: center; border: 1px solid #ccd0d4; border-radius: 4px;">
            <span class="dashicons dashicons-yacht" style="font-size: 48px; color: #ccc;"></span>
            <h3 style="color: #666;">Select a yacht to customize</h3>
            <p style="color: #999;">Choose a yacht from the dropdown above to manage its media and description.</p>
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
    
    // Initialize sortable for drag-drop reordering (v65.15)
    if (typeof $.fn.sortable !== 'undefined') {
        $('#custom-media-grid').sortable({
            items: '.media-item',
            placeholder: 'ui-sortable-placeholder',
            cursor: 'move',
            tolerance: 'pointer',
            update: function(event, ui) {
                // Auto-save order after drag
                saveMediaOrder();
            }
        });
    }
    
    // Helper function to render media item HTML
    function renderMediaItem(media) {
        var isVideo = media.media_type === 'video';
        var thumbUrl = media.thumbnail_url || (isVideo ? 'https://img.youtube.com/vi/' + media.media_url + '/mqdefault.jpg' : media.media_url);
        
        var html = '<div class="media-item" data-id="' + media.id + '" data-type="' + media.media_type + '" ' +
                   'style="background: #fff; border: 2px solid #ddd; border-radius: 4px; overflow: hidden; cursor: move; position: relative;">';
        
        if (isVideo) {
            html += '<div style="position: relative;">' +
                    '<img src="' + thumbUrl + '" style="width: 100%; height: 100px; object-fit: cover;">' +
                    '<span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); color: #fff; padding: 5px 10px; border-radius: 4px;">' +
                    '<span class="dashicons dashicons-video-alt3"></span></span></div>';
        } else {
            html += '<img src="' + thumbUrl + '" style="width: 100%; height: 100px; object-fit: cover;">';
        }
        
        html += '<div style="padding: 5px; font-size: 11px; background: #f8f9fa;">' +
                '<span class="media-type-badge" style="background: ' + (isVideo ? '#e74c3c' : '#3498db') + '; color: #fff; padding: 1px 5px; border-radius: 2px; font-size: 10px;">' +
                media.media_type.toUpperCase() + '</span>' +
                '<button type="button" class="delete-media-btn" data-id="' + media.id + '" ' +
                'style="float: right; background: none; border: none; color: #dc3545; cursor: pointer; padding: 0;">' +
                '<span class="dashicons dashicons-trash"></span></button></div></div>';
        
        return html;
    }
    
    // Helper function to save media order
    function saveMediaOrder() {
        var order = [];
        $('#custom-media-grid .media-item').each(function() {
            order.push($(this).data('id'));
        });
        
        $.post(ajaxUrl, {
            action: 'yolo_save_media_order',
            yacht_id: yachtId,
            order: order,
            nonce: nonce
        });
    }
    
    // Copy synced images button (v65.15)
    $('#copy-synced-images').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Copying...');
        
        $.post(ajaxUrl, {
            action: 'yolo_copy_synced_images',
            yacht_id: yachtId,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                // Clear grid and add new items
                $('#custom-media-grid').empty();
                $.each(response.data.media, function(i, media) {
                    $('#custom-media-grid').append(renderMediaItem(media));
                });
                // Reinitialize sortable
                $('#custom-media-grid').sortable('refresh');
                alert(response.data.message);
            } else {
                alert('Error: ' + response.data);
            }
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-migrate" style="vertical-align: middle;"></span> Copy Synced Images to Custom Media');
        });
    });
    
    // Add image button - opens WordPress Media Library (v65.15)
    $('#add-image-btn').on('click', function() {
        // Check if wp.media is available
        if (typeof wp !== 'undefined' && wp.media) {
            var mediaUploader = wp.media({
                title: 'Select or Upload Image',
                button: { text: 'Add Image' },
                multiple: true,
                library: { type: 'image' }
            });
            
            mediaUploader.on('select', function() {
                var attachments = mediaUploader.state().get('selection').toJSON();
                $.each(attachments, function(i, attachment) {
                    addMedia('image', attachment.url, attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
                });
            });
            
            mediaUploader.open();
        } else {
            // Fallback to URL prompt
            var url = prompt('Enter image URL:');
            if (url) {
                addMedia('image', url, url);
            }
        }
    });
    
    // Add video button (v65.15)
    $('#add-video-btn').on('click', function() {
        var choice = confirm('Click OK to enter a YouTube URL, or Cancel to upload a video file.');
        
        if (choice) {
            // YouTube URL
            var url = prompt('Enter YouTube URL (e.g., https://www.youtube.com/watch?v=XXXXX):');
            if (url) {
                addMedia('video', url, '');
            }
        } else {
            // Upload video file
            if (typeof wp !== 'undefined' && wp.media) {
                var mediaUploader = wp.media({
                    title: 'Select or Upload Video',
                    button: { text: 'Add Video' },
                    multiple: false,
                    library: { type: 'video' }
                });
                
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    addMedia('video', attachment.url, attachment.thumb ? attachment.thumb.src : '');
                });
                
                mediaUploader.open();
            } else {
                alert('WordPress Media Library not available. Please enter a YouTube URL instead.');
            }
        }
    });
    
    // Helper function to add media via AJAX
    function addMedia(type, url, thumbnail) {
        $.post(ajaxUrl, {
            action: 'yolo_add_custom_media',
            yacht_id: yachtId,
            media_type: type,
            media_url: url,
            thumbnail_url: thumbnail,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                // Remove "no media" message if present
                $('#custom-media-grid .no-media-message').remove();
                // Add new media item
                $('#custom-media-grid').append(renderMediaItem(response.data));
                // Reinitialize sortable
                $('#custom-media-grid').sortable('refresh');
            } else {
                alert('Error: ' + response.data);
            }
        });
    }
    
    // Delete media button (v65.15)
    $(document).on('click', '.delete-media-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!confirm('Are you sure you want to delete this media?')) {
            return;
        }
        
        var $item = $(this).closest('.media-item');
        var mediaId = $(this).data('id');
        
        $.post(ajaxUrl, {
            action: 'yolo_delete_custom_media',
            media_id: mediaId,
            nonce: nonce
        }, function(response) {
            if (response.success) {
                $item.fadeOut(300, function() {
                    $(this).remove();
                    // Show "no media" message if grid is empty
                    if ($('#custom-media-grid .media-item').length === 0) {
                        $('#custom-media-grid').html('<p class="no-media-message" style="color: #666; grid-column: 1/-1;">No custom media yet. Use "Copy Synced Images" or add new media below.</p>');
                    }
                });
            } else {
                alert('Error: ' + response.data);
            }
        });
    });
    
    // Save order button (v65.15)
    $('#save-media-order').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true).text('Saving...');
        
        saveMediaOrder();
        
        setTimeout(function() {
            $btn.prop('disabled', false).html('<span class="dashicons dashicons-saved" style="vertical-align: middle;"></span> Save Order');
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
