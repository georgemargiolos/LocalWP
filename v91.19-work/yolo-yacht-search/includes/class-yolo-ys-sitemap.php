<?php
/**
 * YOLO Yacht Search - Sitemap Integration
 * 
 * Adds yacht URLs to Google XML Sitemap Generator
 * 
 * @package YOLO_Yacht_Search
 * @since 75.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Sitemap {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor - register hooks
     */
    private function __construct() {
        // Hook into Google XML Sitemap Generator
        add_action('sm_buildmap', array($this, 'add_yachts_to_sitemap'));
    }
    
    /**
     * Add yacht URLs to sitemap
     * 
     * Called by Google XML Sitemap Generator during sitemap build
     */
    public function add_yachts_to_sitemap() {
        // Get generator instance
        if (!class_exists('GoogleSitemapGenerator')) {
            return;
        }
        
        $generator = GoogleSitemapGenerator::get_instance();
        if (!$generator) {
            return;
        }
        
        global $wpdb;
        
        // Get all yachts with slugs
        $yachts = $wpdb->get_results("
            SELECT id, slug, company_id, last_synced 
            FROM {$wpdb->prefix}yolo_yachts 
            WHERE slug IS NOT NULL AND slug != ''
            ORDER BY company_id ASC, name ASC
        ");
        
        if (empty($yachts)) {
            return;
        }
        
        $my_company_id = get_option('yolo_ys_my_company_id', 7850);
        
        foreach ($yachts as $yacht) {
            $url = home_url('/yacht/' . $yacht->slug . '/');
            $last_mod = !empty($yacht->last_synced) ? strtotime($yacht->last_synced) : time();
            $priority = ($yacht->company_id == $my_company_id) ? 0.8 : 0.6;
            
            $generator->add_url(
                $url,           // Location
                $last_mod,      // Last modified (UNIX timestamp)
                'weekly',       // Change frequency
                $priority       // Priority (0.0 - 1.0)
            );
        }
    }
}

// Initialize on plugins_loaded
add_action('plugins_loaded', function() {
    YOLO_YS_Sitemap::get_instance();
});
