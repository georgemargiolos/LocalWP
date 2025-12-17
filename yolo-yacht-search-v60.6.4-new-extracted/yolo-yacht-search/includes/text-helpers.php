<?php
/**
 * Text Helper Functions
 * Provides easy access to customizable text strings
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get customizable text with fallback to default
 */
function yolo_ys_text($key, $default = '') {
    return get_option('yolo_ys_text_' . $key, $default);
}

/**
 * Echo customizable text
 */
function yolo_ys_text_e($key, $default = '') {
    echo esc_html(yolo_ys_text($key, $default));
}

/**
 * Get icon class for a feature
 * 
 * @param string $feature_type Type of feature (equipment, extra, section)
 * @param string $feature_name Name of the feature
 * @param string $default_icon Default icon class if not found
 * @return string Icon class
 */
function yolo_ys_get_icon($feature_type, $feature_name, $default_icon = 'fa-solid fa-check') {
    global $wpdb;
    $table = $wpdb->prefix . 'yolo_feature_icons';
    
    $icon = $wpdb->get_row($wpdb->prepare(
        "SELECT icon_class, icon_style FROM {$table} WHERE feature_type = %s AND feature_name = %s",
        $feature_type,
        $feature_name
    ));
    
    if ($icon && !empty($icon->icon_class)) {
        $style_attr = !empty($icon->icon_style) ? ' style="' . esc_attr($icon->icon_style) . '"' : '';
        return '<i class="' . esc_attr($icon->icon_class) . '"' . $style_attr . '></i>';
    }
    
    return '<i class="' . esc_attr($default_icon) . '"></i>';
}

/**
 * Echo icon for a feature
 */
function yolo_ys_icon($feature_type, $feature_name, $default_icon = 'fa-solid fa-check') {
    echo yolo_ys_get_icon($feature_type, $feature_name, $default_icon);
}
