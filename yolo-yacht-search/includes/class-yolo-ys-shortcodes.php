<?php
/**
 * Shortcodes for the plugin
 */
class YOLO_YS_Shortcodes {
    
    public function __construct() {
        add_shortcode('yolo_search_widget', array($this, 'search_widget_shortcode'));
        add_shortcode('yolo_search_results', array($this, 'search_results_shortcode'));
        add_shortcode('yolo_our_fleet', array($this, 'our_fleet_shortcode'));
        add_shortcode('yolo_yacht_details', array($this, 'yacht_details_shortcode'));
    }
    
    /**
     * Search widget shortcode
     */
    public function search_widget_shortcode($atts) {
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/templates/search-form.php';
        return ob_get_clean();
    }
    
    /**
     * Search results shortcode
     */
    public function search_results_shortcode($atts) {
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/templates/search-results.php';
        return ob_get_clean();
    }
    
    /**
     * Our Fleet shortcode - displays all yachts (YOLO first, then partners)
     */
    public function our_fleet_shortcode($atts) {
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/templates/our-fleet.php';
        return ob_get_clean();
    }
    
    /**
     * Yacht Details shortcode - displays single yacht with all information
     */
    public function yacht_details_shortcode($atts) {
        ob_start();
        include YOLO_YS_PLUGIN_DIR . 'public/templates/yacht-details-v3.php';
        return ob_get_clean();
    }
}

// Initialize shortcodes
new YOLO_YS_Shortcodes();
