<?php
/**
 * Payment Icons Admin Page
 * Allows customization of payment method icons displayed under the Book Now button
 * v75.19 - Added custom icon upload
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define available built-in payment icons
$builtin_icons = array(
    'visa' => array('name' => 'Visa', 'file' => 'visa.svg'),
    'mastercard' => array('name' => 'Mastercard', 'file' => 'mastercard.svg'),
    'amex' => array('name' => 'American Express', 'file' => 'amex.svg'),
    'discover' => array('name' => 'Discover', 'file' => 'discover.svg'),
    'paypal' => array('name' => 'PayPal', 'file' => 'paypal.svg'),
    'apple-pay' => array('name' => 'Apple Pay', 'file' => 'apple-pay.svg'),
    'google-pay' => array('name' => 'Google Pay', 'file' => 'google-pay.svg'),
    'klarna' => array('name' => 'Klarna', 'file' => 'klarna.svg'),
    'revolut' => array('name' => 'Revolut Pay', 'file' => 'revolut.svg'),
    'samsung-pay' => array('name' => 'Samsung Pay', 'file' => 'samsung-pay.svg'),
    'link' => array('name' => 'Link (Stripe)', 'file' => 'link.svg'),
    'bancontact' => array('name' => 'Bancontact', 'file' => 'bancontact.svg'),
    'blik' => array('name' => 'BLIK', 'file' => 'blik.svg'),
    'eps' => array('name' => 'EPS', 'file' => 'eps.svg'),
    'mbway' => array('name' => 'MB Way', 'file' => 'mbway.svg'),
    'mobilepay' => array('name' => 'MobilePay', 'file' => 'mobilepay.svg'),
    'kakaopay' => array('name' => 'Kakao Pay', 'file' => 'kakaopay.svg'),
    'naverpay' => array('name' => 'Naver Pay', 'file' => 'naverpay.svg'),
    'payco' => array('name' => 'PAYCO', 'file' => 'payco.svg'),
    'satispay' => array('name' => 'Satispay', 'file' => 'satispay.svg'),
    'stripe' => array('name' => 'Stripe', 'file' => 'stripe.svg'),
);

// Get custom uploaded icons
$custom_icons = get_option('yolo_ys_payment_icons_custom', array());

// Get hidden built-in icons
$hidden_builtin_icons = get_option('yolo_ys_payment_icons_hidden', array());

// Handle built-in icon hide/delete
if (isset($_GET['hide_builtin_icon']) && check_admin_referer('hide_builtin_icon_' . $_GET['hide_builtin_icon'])) {
    $icon_to_hide = sanitize_text_field($_GET['hide_builtin_icon']);
    if (isset($builtin_icons[$icon_to_hide]) && !in_array($icon_to_hide, $hidden_builtin_icons)) {
        $hidden_builtin_icons[] = $icon_to_hide;
        update_option('yolo_ys_payment_icons_hidden', $hidden_builtin_icons);
        
        // Also remove from enabled icons
        $enabled_icons = get_option('yolo_ys_payment_icons_enabled', array());
        $enabled_icons = array_diff($enabled_icons, array($icon_to_hide));
        update_option('yolo_ys_payment_icons_enabled', array_values($enabled_icons));
        
        echo '<div class="notice notice-success"><p>Icon "' . esc_html($builtin_icons[$icon_to_hide]['name']) . '" has been hidden. You can restore it below.</p></div>';
    }
}

// Handle built-in icon restore
if (isset($_GET['restore_builtin_icon']) && check_admin_referer('restore_builtin_icon_' . $_GET['restore_builtin_icon'])) {
    $icon_to_restore = sanitize_text_field($_GET['restore_builtin_icon']);
    $hidden_builtin_icons = array_diff($hidden_builtin_icons, array($icon_to_restore));
    update_option('yolo_ys_payment_icons_hidden', array_values($hidden_builtin_icons));
    echo '<div class="notice notice-success"><p>Icon "' . esc_html($builtin_icons[$icon_to_restore]['name']) . '" has been restored.</p></div>';
    
    // Refresh the hidden list
    $hidden_builtin_icons = get_option('yolo_ys_payment_icons_hidden', array());
}

// Handle custom icon deletion
if (isset($_GET['delete_custom_icon']) && check_admin_referer('delete_custom_icon_' . $_GET['delete_custom_icon'])) {
    $icon_to_delete = sanitize_text_field($_GET['delete_custom_icon']);
    if (isset($custom_icons[$icon_to_delete])) {
        // Delete the file from media library
        if (isset($custom_icons[$icon_to_delete]['attachment_id'])) {
            wp_delete_attachment($custom_icons[$icon_to_delete]['attachment_id'], true);
        }
        unset($custom_icons[$icon_to_delete]);
        update_option('yolo_ys_payment_icons_custom', $custom_icons);
        
        // Also remove from enabled icons
        $enabled_icons = get_option('yolo_ys_payment_icons_enabled', array());
        $enabled_icons = array_diff($enabled_icons, array($icon_to_delete));
        update_option('yolo_ys_payment_icons_enabled', array_values($enabled_icons));
        
        echo '<div class="notice notice-success"><p>Custom icon deleted successfully!</p></div>';
    }
}

// Filter out hidden built-in icons
foreach ($hidden_builtin_icons as $hidden_id) {
    unset($builtin_icons[$hidden_id]);
}

// Handle custom icon upload
if (isset($_POST['yolo_ys_upload_custom_icon']) && check_admin_referer('yolo_ys_payment_icons_nonce')) {
    $icon_name = sanitize_text_field($_POST['custom_icon_name']);
    $icon_id = sanitize_title($icon_name);
    
    if (!empty($icon_name) && !empty($_FILES['custom_icon_file']['name'])) {
        // Check if it's an SVG
        $file_type = wp_check_filetype($_FILES['custom_icon_file']['name']);
        
        if ($file_type['ext'] === 'svg') {
            // Upload the file
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            $attachment_id = media_handle_upload('custom_icon_file', 0);
            
            if (!is_wp_error($attachment_id)) {
                $file_url = wp_get_attachment_url($attachment_id);
                
                // Make sure icon_id is unique
                $original_id = $icon_id;
                $counter = 1;
                while (isset($builtin_icons[$icon_id]) || isset($custom_icons[$icon_id])) {
                    $icon_id = $original_id . '-' . $counter;
                    $counter++;
                }
                
                $custom_icons[$icon_id] = array(
                    'name' => $icon_name,
                    'file' => $file_url,
                    'attachment_id' => $attachment_id,
                    'is_custom' => true,
                );
                update_option('yolo_ys_payment_icons_custom', $custom_icons);
                
                echo '<div class="notice notice-success"><p>Custom icon "' . esc_html($icon_name) . '" uploaded successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Error uploading file: ' . esc_html($attachment_id->get_error_message()) . '</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>Only SVG files are allowed. Please upload an SVG file.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error"><p>Please provide both icon name and file.</p></div>';
    }
}

// Merge built-in and custom icons
$available_icons = array_merge($builtin_icons, $custom_icons);

// Handle form submission
if (isset($_POST['yolo_ys_save_payment_icons']) && check_admin_referer('yolo_ys_payment_icons_nonce')) {
    // Save enabled icons
    $enabled_icons = isset($_POST['payment_icons_enabled']) ? array_map('sanitize_text_field', $_POST['payment_icons_enabled']) : array();
    update_option('yolo_ys_payment_icons_enabled', $enabled_icons);
    
    // Save icon order
    $icon_order = isset($_POST['payment_icons_order']) ? sanitize_text_field($_POST['payment_icons_order']) : '';
    update_option('yolo_ys_payment_icons_order', $icon_order);
    
    // Save texts
    update_option('yolo_ys_payment_icons_title', sanitize_text_field($_POST['payment_icons_title']));
    update_option('yolo_ys_payment_icons_show_more_text', sanitize_text_field($_POST['payment_icons_show_more_text']));
    update_option('yolo_ys_payment_icons_show_less_text', sanitize_text_field($_POST['payment_icons_show_less_text']));
    
    // Save display settings
    update_option('yolo_ys_payment_icons_visible_count', intval($_POST['payment_icons_visible_count']));
    update_option('yolo_ys_payment_icons_show_box', isset($_POST['payment_icons_show_box']) ? '1' : '0');
    
    echo '<div class="notice notice-success"><p>Payment icons settings saved successfully!</p></div>';
}

// Get current settings
$enabled_icons = get_option('yolo_ys_payment_icons_enabled', array('visa', 'mastercard', 'amex', 'paypal', 'apple-pay', 'google-pay', 'klarna', 'revolut'));
$icon_order = get_option('yolo_ys_payment_icons_order', implode(',', array_keys($available_icons)));
$title = get_option('yolo_ys_payment_icons_title', 'We accept');
$show_more_text = get_option('yolo_ys_payment_icons_show_more_text', '+ %d more payment methods');
$show_less_text = get_option('yolo_ys_payment_icons_show_less_text', 'Show less');
$visible_count = get_option('yolo_ys_payment_icons_visible_count', 8);
$show_box = get_option('yolo_ys_payment_icons_show_box', '1');

// Sort icons by saved order
$ordered_icons = array();
if (!empty($icon_order)) {
    $order_array = explode(',', $icon_order);
    foreach ($order_array as $icon_id) {
        if (isset($available_icons[$icon_id])) {
            $ordered_icons[$icon_id] = $available_icons[$icon_id];
        }
    }
}
// Add any new icons not in the saved order
foreach ($available_icons as $icon_id => $icon_data) {
    if (!isset($ordered_icons[$icon_id])) {
        $ordered_icons[$icon_id] = $icon_data;
    }
}

$icons_url = plugins_url('public/images/payment-icons/', dirname(dirname(__FILE__)));
?>

<div class="wrap">
    <h1><?php _e('Payment Icons Settings', 'yolo-yacht-search'); ?></h1>
    <p><?php _e('Configure the payment method icons displayed under the Book Now button on yacht detail pages.', 'yolo-yacht-search'); ?></p>
    
    <form method="post" action="">
        <?php wp_nonce_field('yolo_ys_payment_icons_nonce'); ?>
        
        <!-- Display Settings -->
        <h2><?php _e('Display Settings', 'yolo-yacht-search'); ?></h2>
        <table class="form-table">
            <tr>
                <th scope="row"><?php _e('Show Payment Icons Box', 'yolo-yacht-search'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="payment_icons_show_box" value="1" <?php checked($show_box, '1'); ?>>
                        <?php _e('Display payment icons under the Book Now button', 'yolo-yacht-search'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="payment_icons_title"><?php _e('Title Text', 'yolo-yacht-search'); ?></label></th>
                <td>
                    <input type="text" id="payment_icons_title" name="payment_icons_title" value="<?php echo esc_attr($title); ?>" class="regular-text">
                    <p class="description"><?php _e('Text shown above the payment icons (e.g., "We accept")', 'yolo-yacht-search'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="payment_icons_visible_count"><?php _e('Initially Visible Icons', 'yolo-yacht-search'); ?></label></th>
                <td>
                    <input type="number" id="payment_icons_visible_count" name="payment_icons_visible_count" value="<?php echo esc_attr($visible_count); ?>" min="4" max="20" style="width: 80px;">
                    <p class="description"><?php _e('Number of icons to show before "Show more" (recommended: 8 for 2 rows of 4)', 'yolo-yacht-search'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="payment_icons_show_more_text"><?php _e('"Show More" Text', 'yolo-yacht-search'); ?></label></th>
                <td>
                    <input type="text" id="payment_icons_show_more_text" name="payment_icons_show_more_text" value="<?php echo esc_attr($show_more_text); ?>" class="regular-text">
                    <p class="description"><?php _e('Use %d as placeholder for the count (e.g., "+ %d more payment methods")', 'yolo-yacht-search'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="payment_icons_show_less_text"><?php _e('"Show Less" Text', 'yolo-yacht-search'); ?></label></th>
                <td>
                    <input type="text" id="payment_icons_show_less_text" name="payment_icons_show_less_text" value="<?php echo esc_attr($show_less_text); ?>" class="regular-text">
                </td>
            </tr>
        </table>
        
        <!-- Payment Icons Selection -->
        <h2><?php _e('Payment Icons', 'yolo-yacht-search'); ?></h2>
        <p><?php _e('Check the icons you want to display and drag to reorder them. The first icons in the list will be shown initially.', 'yolo-yacht-search'); ?></p>
        
        <input type="hidden" name="payment_icons_order" id="payment_icons_order" value="<?php echo esc_attr($icon_order); ?>">
        
        <div id="payment-icons-sortable" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin: 20px 0;">
            <?php foreach ($ordered_icons as $icon_id => $icon_data): 
                $is_custom = isset($icon_data['is_custom']) && $icon_data['is_custom'];
                $icon_src = $is_custom ? $icon_data['file'] : $icons_url . $icon_data['file'];
            ?>
                <div class="payment-icon-item" data-icon-id="<?php echo esc_attr($icon_id); ?>" style="display: flex; align-items: center; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 6px; cursor: move;">
                    <input type="checkbox" name="payment_icons_enabled[]" value="<?php echo esc_attr($icon_id); ?>" <?php checked(in_array($icon_id, $enabled_icons)); ?> style="margin-right: 10px;">
                    <img src="<?php echo esc_url($icon_src); ?>" alt="<?php echo esc_attr($icon_data['name']); ?>" style="width: 50px; height: 32px; margin-right: 10px; object-fit: contain;">
                    <span style="flex: 1;"><?php echo esc_html($icon_data['name']); ?></span>
                    <?php if ($is_custom): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yolo-payment-icons&delete_custom_icon=' . $icon_id), 'delete_custom_icon_' . $icon_id); ?>" 
                           class="delete-custom-icon" 
                           onclick="return confirm('Are you sure you want to delete this custom icon?');"
                           title="Delete custom icon"
                           style="color: #dc2626; margin-right: 8px;">
                            <span class="dashicons dashicons-trash"></span>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yolo-payment-icons&hide_builtin_icon=' . $icon_id), 'hide_builtin_icon_' . $icon_id); ?>" 
                           class="hide-builtin-icon" 
                           onclick="return confirm('Hide this icon? You can restore it later.');"
                           title="Hide this icon"
                           style="color: #dc2626; margin-right: 8px;">
                            <span class="dashicons dashicons-hidden"></span>
                        </a>
                    <?php endif; ?>
                    <span class="dashicons dashicons-menu" style="color: #999;"></span>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Preview -->
        <h2><?php _e('Preview', 'yolo-yacht-search'); ?></h2>
        <div id="payment-icons-preview" style="background: #f9f9f9; padding: 20px; border-radius: 8px; max-width: 400px;">
            <div style="font-size: 12px; color: #666; margin-bottom: 8px;"><?php echo esc_html($title); ?></div>
            <div id="preview-icons" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px;">
                <?php 
                $count = 0;
                foreach ($ordered_icons as $icon_id => $icon_data): 
                    if (in_array($icon_id, $enabled_icons) && $count < $visible_count):
                        $is_custom = isset($icon_data['is_custom']) && $icon_data['is_custom'];
                        $icon_src = $is_custom ? $icon_data['file'] : $icons_url . $icon_data['file'];
                ?>
                    <img src="<?php echo esc_url($icon_src); ?>" alt="<?php echo esc_attr($icon_data['name']); ?>" style="width: 50px; height: 32px; object-fit: contain;">
                <?php 
                        $count++;
                    endif;
                endforeach; 
                ?>
            </div>
            <?php 
            $total_enabled = count($enabled_icons);
            $remaining = $total_enabled - $visible_count;
            if ($remaining > 0):
            ?>
                <div style="text-align: center; margin-top: 10px;">
                    <a href="#" style="font-size: 12px; color: var(--yolo-primary, #1e3a8a);">
                        <?php echo esc_html(sprintf($show_more_text, $remaining)); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php submit_button(__('Save Payment Icons Settings', 'yolo-yacht-search'), 'primary', 'yolo_ys_save_payment_icons'); ?>
    </form>
    
    <hr style="margin: 40px 0;">
    
    <!-- Custom Icon Upload Section -->
    <h2><?php _e('Upload Custom Payment Icon', 'yolo-yacht-search'); ?></h2>
    <div style="background: #f0f6fc; border: 1px solid #c3c4c7; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
        <h3 style="margin-top: 0; color: #1e3a8a;">
            <span class="dashicons dashicons-info" style="margin-right: 8px;"></span>
            <?php _e('SVG Icon Requirements', 'yolo-yacht-search'); ?>
        </h3>
        <table style="border-collapse: collapse; width: 100%; max-width: 500px;">
            <tr>
                <td style="padding: 8px 16px 8px 0; font-weight: 600;"><?php _e('Format:', 'yolo-yacht-search'); ?></td>
                <td style="padding: 8px 0;"><code>SVG</code> (Scalable Vector Graphics)</td>
            </tr>
            <tr>
                <td style="padding: 8px 16px 8px 0; font-weight: 600;"><?php _e('Recommended Size:', 'yolo-yacht-search'); ?></td>
                <td style="padding: 8px 0;"><code>50px × 32px</code> (width × height)</td>
            </tr>
            <tr>
                <td style="padding: 8px 16px 8px 0; font-weight: 600;"><?php _e('Aspect Ratio:', 'yolo-yacht-search'); ?></td>
                <td style="padding: 8px 0;"><code>1.5625:1</code> (approximately 3:2)</td>
            </tr>
            <tr>
                <td style="padding: 8px 16px 8px 0; font-weight: 600;"><?php _e('ViewBox:', 'yolo-yacht-search'); ?></td>
                <td style="padding: 8px 0;"><code>viewBox="0 0 50 32"</code> (recommended)</td>
            </tr>
        </table>
        <p style="margin-bottom: 0; color: #666;">
            <strong><?php _e('Tip:', 'yolo-yacht-search'); ?></strong> 
            <?php _e('SVG icons scale perfectly to any size. The dimensions above are display sizes - your SVG can have any internal dimensions as long as the aspect ratio is similar.', 'yolo-yacht-search'); ?>
        </p>
    </div>
    
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('yolo_ys_payment_icons_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="custom_icon_name"><?php _e('Icon Name', 'yolo-yacht-search'); ?></label></th>
                <td>
                    <input type="text" id="custom_icon_name" name="custom_icon_name" class="regular-text" placeholder="e.g., My Payment Method" required>
                    <p class="description"><?php _e('Display name for the payment method', 'yolo-yacht-search'); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="custom_icon_file"><?php _e('SVG File', 'yolo-yacht-search'); ?></label></th>
                <td>
                    <input type="file" id="custom_icon_file" name="custom_icon_file" accept=".svg" required>
                    <p class="description"><?php _e('Upload an SVG file (50×32px recommended)', 'yolo-yacht-search'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php submit_button(__('Upload Custom Icon', 'yolo-yacht-search'), 'secondary', 'yolo_ys_upload_custom_icon'); ?>
    </form>
    
    <?php 
    // Get the full list of built-in icons for restore section
    $all_builtin_icons = array(
        'visa' => array('name' => 'Visa', 'file' => 'visa.svg'),
        'mastercard' => array('name' => 'Mastercard', 'file' => 'mastercard.svg'),
        'amex' => array('name' => 'American Express', 'file' => 'amex.svg'),
        'discover' => array('name' => 'Discover', 'file' => 'discover.svg'),
        'paypal' => array('name' => 'PayPal', 'file' => 'paypal.svg'),
        'apple-pay' => array('name' => 'Apple Pay', 'file' => 'apple-pay.svg'),
        'google-pay' => array('name' => 'Google Pay', 'file' => 'google-pay.svg'),
        'klarna' => array('name' => 'Klarna', 'file' => 'klarna.svg'),
        'revolut' => array('name' => 'Revolut Pay', 'file' => 'revolut.svg'),
        'samsung-pay' => array('name' => 'Samsung Pay', 'file' => 'samsung-pay.svg'),
        'link' => array('name' => 'Link (Stripe)', 'file' => 'link.svg'),
        'bancontact' => array('name' => 'Bancontact', 'file' => 'bancontact.svg'),
        'blik' => array('name' => 'BLIK', 'file' => 'blik.svg'),
        'eps' => array('name' => 'EPS', 'file' => 'eps.svg'),
        'mbway' => array('name' => 'MB Way', 'file' => 'mbway.svg'),
        'mobilepay' => array('name' => 'MobilePay', 'file' => 'mobilepay.svg'),
        'kakaopay' => array('name' => 'Kakao Pay', 'file' => 'kakaopay.svg'),
        'naverpay' => array('name' => 'Naver Pay', 'file' => 'naverpay.svg'),
        'payco' => array('name' => 'PAYCO', 'file' => 'payco.svg'),
        'satispay' => array('name' => 'Satispay', 'file' => 'satispay.svg'),
        'stripe' => array('name' => 'Stripe', 'file' => 'stripe.svg'),
    );
    $hidden_builtin_icons = get_option('yolo_ys_payment_icons_hidden', array());
    ?>
    
    <?php if (!empty($hidden_builtin_icons)): ?>
    <h3><?php _e('Hidden Built-in Icons', 'yolo-yacht-search'); ?></h3>
    <p style="color: #666;"><?php _e('These icons have been hidden. Click "Restore" to add them back to the list.', 'yolo-yacht-search'); ?></p>
    <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-bottom: 30px;">
        <?php foreach ($hidden_builtin_icons as $icon_id): 
            if (isset($all_builtin_icons[$icon_id])):
                $icon_data = $all_builtin_icons[$icon_id];
        ?>
            <div style="background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 16px; text-align: center; width: 120px; opacity: 0.7;">
                <img src="<?php echo esc_url($icons_url . $icon_data['file']); ?>" alt="<?php echo esc_attr($icon_data['name']); ?>" style="width: 50px; height: 32px; object-fit: contain; margin-bottom: 8px; filter: grayscale(50%);">
                <div style="font-size: 12px; font-weight: 600; color: #666;"><?php echo esc_html($icon_data['name']); ?></div>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yolo-payment-icons&restore_builtin_icon=' . $icon_id), 'restore_builtin_icon_' . $icon_id); ?>" 
                   style="font-size: 11px; color: #2271b1;">
                    <?php _e('Restore', 'yolo-yacht-search'); ?>
                </a>
            </div>
        <?php endif; endforeach; ?>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($custom_icons)): ?>
    <h3><?php _e('Your Custom Icons', 'yolo-yacht-search'); ?></h3>
    <div style="display: flex; flex-wrap: wrap; gap: 16px;">
        <?php foreach ($custom_icons as $icon_id => $icon_data): ?>
            <div style="background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 16px; text-align: center; width: 120px;">
                <img src="<?php echo esc_url($icon_data['file']); ?>" alt="<?php echo esc_attr($icon_data['name']); ?>" style="width: 50px; height: 32px; object-fit: contain; margin-bottom: 8px;">
                <div style="font-size: 12px; font-weight: 600;"><?php echo esc_html($icon_data['name']); ?></div>
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=yolo-payment-icons&delete_custom_icon=' . $icon_id), 'delete_custom_icon_' . $icon_id); ?>" 
                   onclick="return confirm('Are you sure you want to delete this custom icon?');"
                   style="font-size: 11px; color: #dc2626;">
                    <?php _e('Delete', 'yolo-yacht-search'); ?>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize sortable
    $('#payment-icons-sortable').sortable({
        update: function(event, ui) {
            updateIconOrder();
        }
    });
    
    function updateIconOrder() {
        var order = [];
        $('#payment-icons-sortable .payment-icon-item').each(function() {
            order.push($(this).data('icon-id'));
        });
        $('#payment_icons_order').val(order.join(','));
    }
    
    // Update order on initial load
    updateIconOrder();
});
</script>

<style>
#payment-icons-sortable .payment-icon-item:hover {
    border-color: #2271b1;
    background: #f0f6fc;
}
#payment-icons-sortable .ui-sortable-helper {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
#payment-icons-sortable .ui-sortable-placeholder {
    border: 2px dashed #2271b1;
    background: #f0f6fc;
    visibility: visible !important;
}
.delete-custom-icon:hover {
    color: #991b1b !important;
}
</style>
