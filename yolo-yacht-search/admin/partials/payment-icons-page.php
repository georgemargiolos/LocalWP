<?php
/**
 * Payment Icons Admin Page
 * Allows customization of payment method icons displayed under the Book Now button
 * v75.18
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define available payment icons
$available_icons = array(
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
            <?php foreach ($ordered_icons as $icon_id => $icon_data): ?>
                <div class="payment-icon-item" data-icon-id="<?php echo esc_attr($icon_id); ?>" style="display: flex; align-items: center; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 6px; cursor: move;">
                    <input type="checkbox" name="payment_icons_enabled[]" value="<?php echo esc_attr($icon_id); ?>" <?php checked(in_array($icon_id, $enabled_icons)); ?> style="margin-right: 10px;">
                    <img src="<?php echo esc_url($icons_url . $icon_data['file']); ?>" alt="<?php echo esc_attr($icon_data['name']); ?>" style="width: 50px; height: 32px; margin-right: 10px; object-fit: contain;">
                    <span style="flex: 1;"><?php echo esc_html($icon_data['name']); ?></span>
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
                ?>
                    <img src="<?php echo esc_url($icons_url . $icon_data['file']); ?>" alt="<?php echo esc_attr($icon_data['name']); ?>" style="width: 50px; height: 32px; object-fit: contain;">
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
</style>
