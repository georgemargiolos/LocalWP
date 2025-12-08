<?php
/**
 * Yacht Details V3 - Inline CSS Variables
 * 
 * This file outputs only CSS custom properties (variables) that can be
 * customized via admin settings. All other CSS is in the external file:
 * /public/css/yacht-details-v3.css
 * 
 * @package YOLO_Yacht_Search
 * @since 21.9
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get admin color settings
$colors = get_option('yolo_ys_colors', array(
    'primary' => '#1e3a8a',
    'primary_hover' => '#1e40af',
    'secondary' => '#b91c1c',
    'secondary_hover' => '#dc2626',
    'success' => '#059669',
    'text_dark' => '#1f2937',
    'text_light' => '#6b7280'
));
?>
<style>
/* ============================================
   CSS CUSTOM PROPERTIES - Easy Color Customization
   ============================================ */
:root {
    --yolo-primary: <?php echo esc_attr($colors['primary']); ?>;
    --yolo-primary-hover: <?php echo esc_attr($colors['primary_hover']); ?>;
    --yolo-primary-light: #dbeafe;
    --yolo-secondary: <?php echo esc_attr($colors['secondary']); ?>;
    --yolo-secondary-hover: <?php echo esc_attr($colors['secondary_hover']); ?>;
    --yolo-success: <?php echo esc_attr($colors['success']); ?>;
    --yolo-warning: #92400e;
    --yolo-warning-bg: #fef3c7;
    --yolo-danger: #dc2626;
    --yolo-danger-bg: #fef2f2;
    --yolo-text-dark: <?php echo esc_attr($colors['text_dark']); ?>;
    --yolo-text-medium: #4b5563;
    --yolo-text-light: <?php echo esc_attr($colors['text_light']); ?>;
    --yolo-border: #e5e7eb;
    --yolo-bg-light: #f9fafb;
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
