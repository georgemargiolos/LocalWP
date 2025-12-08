<?php
/**
 * Yacht Details v3 - CSS Variables Only
 * Version: 40.7
 * Date: December 8, 2025
 * 
 * This file now only outputs CSS custom properties from admin settings.
 * All actual styles are in: public/css/yacht-details-v3.css
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get custom colors from admin settings
$colors = array(
    'primary' => get_option('yolo_ys_color_primary', '#1e3a8a'),
    'primary_hover' => get_option('yolo_ys_color_primary_hover', '#1e40af'),
    'secondary' => get_option('yolo_ys_color_secondary', '#b91c1c'),
    'secondary_hover' => get_option('yolo_ys_color_secondary_hover', '#991b1b'),
    'success' => get_option('yolo_ys_color_success', '#059669'),
    'danger' => get_option('yolo_ys_color_danger', '#dc2626'),
    'text_dark' => get_option('yolo_ys_color_text_dark', '#1f2937'),
    'text_light' => get_option('yolo_ys_color_text_light', '#6b7280'),
    'bg_light' => get_option('yolo_ys_color_bg_light', '#f9fafb'),
    'border' => get_option('yolo_ys_color_border', '#e5e7eb'),
    'white' => '#ffffff',
    'primary_light' => '#dbeafe'
);
?>
<style>
:root {
    /* Primary Colors */
    --yolo-primary: <?php echo esc_attr($colors['primary']); ?>;
    --yolo-primary-hover: <?php echo esc_attr($colors['primary_hover']); ?>;
    --yolo-primary-light: <?php echo esc_attr($colors['primary_light']); ?>;
    
    /* Secondary Colors */
    --yolo-secondary: <?php echo esc_attr($colors['secondary']); ?>;
    --yolo-secondary-hover: <?php echo esc_attr($colors['secondary_hover']); ?>;
    
    /* Status Colors */
    --yolo-success: <?php echo esc_attr($colors['success']); ?>;
    --yolo-danger: <?php echo esc_attr($colors['danger']); ?>;
    
    /* Text Colors */
    --yolo-text-dark: <?php echo esc_attr($colors['text_dark']); ?>;
    --yolo-text-light: <?php echo esc_attr($colors['text_light']); ?>;
    
    /* Background Colors */
    --yolo-bg-light: <?php echo esc_attr($colors['bg_light']); ?>;
    --yolo-white: <?php echo esc_attr($colors['white']); ?>;
    
    /* Border Colors */
    --yolo-border: <?php echo esc_attr($colors['border']); ?>;
    
    /* Spacing */
    --yolo-radius-sm: 4px;
    --yolo-radius-md: 6px;
    --yolo-radius-lg: 8px;
    
    /* Shadows */
    --yolo-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --yolo-shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
    
    /* Transitions */
    --yolo-transition: all 0.2s ease;
}
</style>
