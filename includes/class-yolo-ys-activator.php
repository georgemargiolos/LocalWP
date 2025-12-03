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
        
        // Create base manager tables
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-base-manager-database.php';
        YOLO_YS_Base_Manager_Database::create_tables();
        
        // Create base manager role
        self::create_base_manager_role();
        
        // Run migrations
        self::run_migrations();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create base manager role
     */
    private static function create_base_manager_role() {
        // Get editor role capabilities
        $editor = get_role('editor');
        
        if (!get_role('base_manager') && $editor) {
            $capabilities = $editor->capabilities;
            
            // Add custom base manager capabilities
            $capabilities['manage_base_operations'] = true;
            $capabilities['manage_yachts'] = true;
            $capabilities['manage_checkins'] = true;
            $capabilities['manage_checkouts'] = true;
            $capabilities['manage_warehouse'] = true;
            
            add_role('base_manager', 'Base Manager', $capabilities);
            error_log('YOLO YS: Base Manager role created with Editor capabilities');
        } else if (get_role('base_manager')) {
            // Update existing role - remove manage_options if it was added
            $base_manager = get_role('base_manager');
            $base_manager->remove_cap('manage_options');
            error_log('YOLO YS: Base Manager role updated - removed manage_options for security');
        }
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
        
        // Migration 3: Fix extras table primary key to allow duplicate extra IDs across yachts (v1.9.4)
        $extras_table = $wpdb->prefix . 'yolo_yacht_extras';
        
        // Check if table exists and has wrong primary key
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$extras_table}'");
        if ($table_exists) {
            // Check current primary key
            $keys = $wpdb->get_results("SHOW KEYS FROM {$extras_table} WHERE Key_name = 'PRIMARY'");
            
            // If primary key is only on 'id' column, we need to fix it
            if (count($keys) === 1 && $keys[0]->Column_name === 'id') {
                // Drop old primary key and create composite key
                $wpdb->query("ALTER TABLE {$extras_table} DROP PRIMARY KEY, ADD PRIMARY KEY (id, yacht_id)");
                error_log('YOLO YS: Fixed extras table primary key to composite (id, yacht_id)');
            }
        }
    }
}
