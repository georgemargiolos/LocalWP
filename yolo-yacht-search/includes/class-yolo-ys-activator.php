<?php
/**
 * Fired during plugin activation
 */
class YOLO_YS_Activator {
    
    /**
     * Activation tasks
     */
    public static function activate() {
        // Set default options with prefilled values
        $defaults = array(
            'yolo_ys_api_key' => '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe',
            'yolo_ys_my_company_id' => '7850',
            'yolo_ys_friend_companies' => '4366,3604,6711',
            'yolo_ys_cache_duration' => '24',
            'yolo_ys_currency' => 'EUR',
            'yolo_ys_results_page' => '',
            'yolo_ys_primary_color' => '#1e3a8a',
            'yolo_ys_accent_color' => '#dc2626',
            'yolo_ys_button_bg_color' => '#dc2626',
            'yolo_ys_button_text_color' => '#ffffff',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
        
        // Create database tables (classes already loaded in main plugin file)
        YOLO_YS_Database::create_tables();
        
        // Create prices table
        YOLO_YS_Database_Prices::create_prices_table();
        
        // Run migrations
        self::run_migrations();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Run database migrations
     */
    private static function run_migrations() {
        global $wpdb;
        
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        $equipment_table = $wpdb->prefix . 'yolo_yacht_equipment';
        
        // Migration 1: Add type column if it doesn't exist (v1.7.5)
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$yachts_table} LIKE 'type'"
        );
        
        if (empty($column_exists)) {
            $wpdb->query(
                "ALTER TABLE {$yachts_table} 
                 ADD COLUMN type varchar(100) DEFAULT NULL 
                 AFTER model"
            );
            
            error_log('YOLO YS: Added type column to yachts table');
        }
        
        // Migration 2: Fix equipment_name to allow NULL (v1.9.3)
        $equipment_column = $wpdb->get_results(
            "SHOW COLUMNS FROM {$equipment_table} LIKE 'equipment_name'"
        );
        
        if (!empty($equipment_column)) {
            // Check if column is NOT NULL
            $column_info = $equipment_column[0];
            if (isset($column_info->Null) && $column_info->Null === 'NO') {
                $wpdb->query(
                    "ALTER TABLE {$equipment_table} 
                     MODIFY COLUMN equipment_name varchar(255) DEFAULT NULL"
                );
                
                error_log('YOLO YS: Modified equipment_name column to allow NULL');
            }
        }
    }
}
