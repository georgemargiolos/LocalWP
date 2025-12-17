<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fired during plugin deactivation
 */
class YOLO_YS_Deactivator {
    
    /**
     * Deactivation tasks
     */
    public static function deactivate() {
        // Unschedule all auto-sync cron events (v30.0)
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-auto-sync.php';
        YOLO_YS_Auto_Sync::unschedule_all_events();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
