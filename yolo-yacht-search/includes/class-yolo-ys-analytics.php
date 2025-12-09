<?php
/**
 * YOLO Yacht Search - Analytics & Tracking
 * @since 41.19
 * @updated 41.25 - Removed GA4/FB Pixel base tracking (handled by site-wide plugin)
 *                  Kept custom yacht booking events that integrate with existing tracking
 */

if (!defined('ABSPATH')) exit;

class YOLO_YS_Analytics {
    
    private static $instance = null;
    private $debug_mode;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->debug_mode = get_option('yolo_enable_debug_mode', '0') === '1';
        
        // Only enqueue custom events script
        add_action('wp_enqueue_scripts', array($this, 'enqueue_analytics_script'));
    }
    
    /**
     * Enqueue client-side analytics JS with custom yacht booking events
     * Note: Assumes GA4 gtag() and Facebook Pixel fbq() are loaded by site-wide analytics plugin
     */
    public function enqueue_analytics_script() {
        if (is_admin()) return;
        
        wp_enqueue_script('yolo-analytics', 
            plugin_dir_url(dirname(__FILE__)) . 'public/js/yolo-analytics.js',
            array('jquery'), YOLO_YS_VERSION, true);
        
        wp_localize_script('yolo-analytics', 'yoloAnalyticsConfig', array(
            'debug_mode' => $this->debug_mode,
            'currency' => 'EUR',
        ));
    }
}

function yolo_analytics() {
    return YOLO_YS_Analytics::get_instance();
}
