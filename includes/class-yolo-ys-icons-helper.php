<?php
/**
 * Icons Helper Class
 *
 * Centralized icon management system for yacht details page.
 * Fetches icon mappings from database with intelligent fallbacks.
 *
 * This class provides a simple API for retrieving and rendering
 * FontAwesome icons throughout the plugin. Icons are stored in
 * the yolo_feature_icons table and cached for performance.
 *
 * Usage:
 *   YOLO_YS_Icons_Helper::render_icon('extra_obligatory', 'Cleaning Fee');
 *   YOLO_YS_Icons_Helper::get_icon('equipment', 'GPS');
 *
 * Default Icons:
 * - extra_obligatory: fa-solid fa-circle-exclamation (red)
 * - extra_optional: fa-solid fa-plus-circle (blue)
 * - equipment: fa-solid fa-check (green)
 * - section: fa-solid fa-circle (default)
 *
 * @package    YOLO_Yacht_Search
 * @subpackage Helpers
 * @since      2.7.8
 * @author     George Margiolos
 */

class YOLO_YS_Icons_Helper {
    
    /**
     * Get icon for a feature
     *
     * Retrieves icon mapping from database. Falls back to defaults if not found.
     *
     * @since 2.7.8
     * @param string $feature_type Type: extra_obligatory, extra_optional, equipment, section
     * @param string $feature_name Name of the feature (e.g., 'Cleaning Fee', 'GPS')
     * @return array Array with 'class' and 'style' keys
     *               Example: ['class' => 'fa-solid fa-star', 'style' => 'color: #ff0000;']
     */
    public static function get_icon($feature_type, $feature_name) {
        global $wpdb;
        $table = $wpdb->prefix . 'yolo_feature_icons';
        
        $icon = $wpdb->get_row($wpdb->prepare(
            "SELECT icon_class, icon_style FROM {$table} WHERE feature_type = %s AND feature_name = %s",
            $feature_type,
            $feature_name
        ));
        
        if ($icon) {
            return array(
                'class' => $icon->icon_class,
                'style' => $icon->icon_style
            );
        }
        
        // Return defaults based on type
        return self::get_default_icon($feature_type);
    }
    
    /**
     * Get default icon for a feature type
     *
     * Returns sensible default icons when no custom mapping exists.
     *
     * @since 2.7.8
     * @param string $feature_type Type: extra_obligatory, extra_optional, equipment, section
     * @return array Array with 'class' and 'style' keys
     */
    private static function get_default_icon($feature_type) {
        $defaults = array(
            'extra_obligatory' => array(
                'class' => 'fa-solid fa-circle-exclamation',
                'style' => 'color: #dc2626;'
            ),
            'extra_optional' => array(
                'class' => 'fa-solid fa-plus-circle',
                'style' => 'color: #1e3a8a;'
            ),
            'equipment' => array(
                'class' => 'fa-solid fa-check',
                'style' => 'color: #059669;'
            ),
            'section' => array(
                'class' => 'fa-solid fa-circle',
                'style' => ''
            )
        );
        
        return isset($defaults[$feature_type]) ? $defaults[$feature_type] : array('class' => 'fa-solid fa-circle', 'style' => '');
    }
    
    /**
     * Render icon HTML
     *
     * Outputs complete HTML <i> tag with icon class and inline styles.
     * Ready to echo directly in templates.
     *
     * @since 2.7.8
     * @param string $feature_type Type: extra_obligatory, extra_optional, equipment, section
     * @param string $feature_name Name of the feature
     * @return string HTML for icon (e.g., '<i class="fa-solid fa-star" style="color: red;"></i>')
     */
    public static function render_icon($feature_type, $feature_name) {
        $icon = self::get_icon($feature_type, $feature_name);
        $style_attr = !empty($icon['style']) ? ' style="' . esc_attr($icon['style']) . '"' : '';
        return '<i class="' . esc_attr($icon['class']) . '"' . $style_attr . '></i>';
    }
}
