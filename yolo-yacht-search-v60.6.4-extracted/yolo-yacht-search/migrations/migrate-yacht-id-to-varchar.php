<?php
/**
 * Database Migration Script: Convert yacht_id from bigint(20) to varchar(50)
 * 
 * CRITICAL FIX: Yacht IDs from Booking Manager API can be 19 digits long
 * JavaScript Number type can only safely handle up to 15 digits (Number.MAX_SAFE_INTEGER)
 * This causes integer overflow and data corruption
 * 
 * Fixed in: v2.5.5
 * Bug history: v2.5.3 identified the issue but didn't migrate existing schema
 * 
 * USAGE:
 * 1. Backup your database first!
 * 2. Run this script once via WordPress admin or WP-CLI
 * 3. Verify all yacht IDs are preserved correctly
 * 
 * TABLES TO MIGRATE:
 * - wp_yolo_yachts (id column)
 * - wp_yolo_yacht_products (yacht_id column)
 * - wp_yolo_yacht_images (yacht_id column)
 * - wp_yolo_yacht_extras (yacht_id column)
 * - wp_yolo_yacht_equipment (yacht_id column)
 * - wp_yolo_yacht_prices (yacht_id column)
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

function yolo_ys_migrate_yacht_id_to_varchar() {
    global $wpdb;
    
    $results = array(
        'success' => false,
        'message' => '',
        'tables_migrated' => 0,
        'errors' => array()
    );
    
    // Increase time limit for migration
    set_time_limit(600); // 10 minutes
    
    $prefix = $wpdb->prefix;
    
    // Define tables and their yacht_id columns
    $tables_to_migrate = array(
        array(
            'table' => $prefix . 'yolo_yachts',
            'column' => 'id',
            'is_primary' => true
        ),
        array(
            'table' => $prefix . 'yolo_yacht_products',
            'column' => 'yacht_id',
            'is_primary' => false
        ),
        array(
            'table' => $prefix . 'yolo_yacht_images',
            'column' => 'yacht_id',
            'is_primary' => false
        ),
        array(
            'table' => $prefix . 'yolo_yacht_extras',
            'column' => 'yacht_id',
            'is_primary' => false
        ),
        array(
            'table' => $prefix . 'yolo_yacht_equipment',
            'column' => 'yacht_id',
            'is_primary' => false
        ),
        array(
            'table' => $prefix . 'yolo_yacht_prices',
            'column' => 'yacht_id',
            'is_primary' => false
        )
    );
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        foreach ($tables_to_migrate as $table_info) {
            $table = $table_info['table'];
            $column = $table_info['column'];
            $is_primary = $table_info['is_primary'];
            
            error_log("YOLO YS Migration: Processing table {$table}, column {$column}");
            
            // Check if table exists
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
            if (!$table_exists) {
                error_log("YOLO YS Migration: Table {$table} does not exist, skipping");
                continue;
            }
            
            // If this is a primary key, we need to drop it first
            if ($is_primary) {
                error_log("YOLO YS Migration: Dropping PRIMARY KEY on {$table}");
                $wpdb->query("ALTER TABLE {$table} DROP PRIMARY KEY");
            } else {
                // Drop foreign key constraints if they exist
                // (WordPress doesn't use foreign keys by default, but just in case)
                error_log("YOLO YS Migration: Checking for indexes on {$table}.{$column}");
            }
            
            // Modify column to varchar(50)
            error_log("YOLO YS Migration: Converting {$table}.{$column} to VARCHAR(50)");
            $sql = "ALTER TABLE {$table} MODIFY COLUMN {$column} VARCHAR(50) NOT NULL";
            $result = $wpdb->query($sql);
            
            if ($result === false) {
                throw new Exception("Failed to modify {$table}.{$column}: " . $wpdb->last_error);
            }
            
            // Re-add primary key if needed
            if ($is_primary) {
                error_log("YOLO YS Migration: Re-adding PRIMARY KEY on {$table}.{$column}");
                $wpdb->query("ALTER TABLE {$table} ADD PRIMARY KEY ({$column})");
            }
            
            $results['tables_migrated']++;
            error_log("YOLO YS Migration: Successfully migrated {$table}.{$column}");
        }
        
        // Also add deposit column if it doesn't exist
        error_log("YOLO YS Migration: Adding deposit column if missing");
        $deposit_exists = $wpdb->get_var("SHOW COLUMNS FROM {$prefix}yolo_yachts LIKE 'deposit'");
        if (!$deposit_exists) {
            $wpdb->query("ALTER TABLE {$prefix}yolo_yachts ADD COLUMN deposit DECIMAL(10,2) DEFAULT NULL AFTER draft");
            error_log("YOLO YS Migration: Added deposit column");
        }
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        $results['success'] = true;
        $results['message'] = sprintf(
            'Successfully migrated %d tables from bigint(20) to varchar(50)',
            $results['tables_migrated']
        );
        
        error_log("YOLO YS Migration: " . $results['message']);
        
    } catch (Exception $e) {
        // Rollback on error
        $wpdb->query('ROLLBACK');
        
        $results['success'] = false;
        $results['message'] = 'Migration failed: ' . $e->getMessage();
        $results['errors'][] = $e->getMessage();
        
        error_log("YOLO YS Migration ERROR: " . $e->getMessage());
    }
    
    return $results;
}

/**
 * Verify migration was successful
 */
function yolo_ys_verify_yacht_id_migration() {
    global $wpdb;
    $prefix = $wpdb->prefix;
    
    $tables = array(
        $prefix . 'yolo_yachts' => 'id',
        $prefix . 'yolo_yacht_products' => 'yacht_id',
        $prefix . 'yolo_yacht_images' => 'yacht_id',
        $prefix . 'yolo_yacht_extras' => 'yacht_id',
        $prefix . 'yolo_yacht_equipment' => 'yacht_id',
        $prefix . 'yolo_yacht_prices' => 'yacht_id'
    );
    
    $verification = array();
    
    foreach ($tables as $table => $column) {
        $column_info = $wpdb->get_row("SHOW COLUMNS FROM {$table} LIKE '{$column}'");
        
        if ($column_info) {
            $is_varchar = (strpos(strtolower($column_info->Type), 'varchar') !== false);
            $verification[$table . '.' . $column] = array(
                'type' => $column_info->Type,
                'is_varchar' => $is_varchar,
                'status' => $is_varchar ? '✅ MIGRATED' : '❌ STILL BIGINT'
            );
        } else {
            $verification[$table . '.' . $column] = array(
                'type' => 'NOT FOUND',
                'is_varchar' => false,
                'status' => '❌ COLUMN MISSING'
            );
        }
    }
    
    return $verification;
}

// If running directly via WP-CLI or admin action
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('yolo migrate-yacht-id', 'yolo_ys_migrate_yacht_id_to_varchar');
    WP_CLI::add_command('yolo verify-yacht-id', 'yolo_ys_verify_yacht_id_migration');
}
