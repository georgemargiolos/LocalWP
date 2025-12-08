<?php
/**
 * Yacht Details V3 - Inline CSS Variables
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get individual color settings from admin
$color_primary = get_option('yolo_ys_color_primary', '#1e3a8a');
$color_primary_hover = get_option('yolo_ys_color_primary_hover', '#1e40af');
$color_secondary = get_option('yolo_ys_color_secondary', '#b91c1c');
$color_secondary_hover = get_option('yolo_ys_color_secondary_hover', '#991b1b');
$color_success = get_option('yolo_ys_color_success', '#059669');
$color_warning = get_option('yolo_ys_color_warning', '#92400e');
$color_danger = get_option('yolo_ys_color_danger', '#dc2626');
$color_text_dark = get_option('yolo_ys_color_text_dark', '#1f2937');
$color_text_medium = get_option('yolo_ys_color_text_medium', '#4b5563');
$color_text_light = get_option('yolo_ys_color_text_light', '#6b7280');
$color_border = get_option('yolo_ys_color_border', '#e5e7eb');
$color_bg_light = get_option('yolo_ys_color_bg_light', '#f9fafb');
?>
<style>
:root {
    --yolo-primary: <?php echo esc_attr($color_primary); ?>;
    --yolo-primary-hover: <?php echo esc_attr($color_primary_hover); ?>;
    --yolo-primary-light: #dbeafe;
    --yolo-secondary: <?php echo esc_attr($color_secondary); ?>;
    --yolo-secondary-hover: <?php echo esc_attr($color_secondary_hover); ?>;
    --yolo-success: <?php echo esc_attr($color_success); ?>;
    --yolo-warning: <?php echo esc_attr($color_warning); ?>;
    --yolo-warning-bg: #fef3c7;
    --yolo-danger: <?php echo esc_attr($color_danger); ?>;
    --yolo-danger-bg: #fef2f2;
    --yolo-text-dark: <?php echo esc_attr($color_text_dark); ?>;
    --yolo-text-medium: <?php echo esc_attr($color_text_medium); ?>;
    --yolo-text-light: <?php echo esc_attr($color_text_light); ?>;
    --yolo-border: <?php echo esc_attr($color_border); ?>;
    --yolo-bg-light: <?php echo esc_attr($color_bg_light); ?>;
    --yolo-bg-lighter: #f3f4f6;
    --yolo-white: #ffffff;
    --yolo-radius-sm: 6px;
    --yolo-radius-md: 8px;
    --yolo-radius-lg: 12px;
    --yolo-shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --yolo-shadow-md: 0 4px 12px rgba(0,0,0,0.1);
    --yolo-shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
    --yolo-transition: 0.3s ease;
    --yolo-container-padding: clamp(12px, 4vw, 40px);
    --yolo-sidebar-width: 380px;
}
</style>
