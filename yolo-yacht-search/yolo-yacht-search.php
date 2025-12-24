<?php
/**
 * Plugin Name: YOLO Yacht Search & Booking
 * Plugin URI: https://github.com/georgemargiolos/LocalWP
 * Description: Yacht search plugin with Booking Manager API integration for YOLO Charters. Features search widget and results blocks with company prioritization.
 * Version: 81.6
 * Author: George Margiolos
 * Author URI: https://github.com/georgemargiolos
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yolo-yacht-search
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Plugin version
define('YOLO_YS_VERSION', '81.6');

// Plugin directory path
define('YOLO_YS_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Plugin directory URL
define('YOLO_YS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Plugin basename
define('YOLO_YS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Load dependencies
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-booking-manager-api.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database-prices.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-sync.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-price-formatter.php';

// Load email system (CRITICAL FIX v17.12.1 - was missing, caused Send Reminder to fail!)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-email.php';

// Load guest user management
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-guest-users.php';

// Load base manager system
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-pdf-generator.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-base-manager.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-warehouse-notifications.php';

// Load quote requests system
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-quote-requests.php';

// Load contact messages system
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-contact-messages.php';

// Load auto-sync system (v30.0)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-auto-sync.php';

// Load progressive sync system (v81.0)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-progressive-sync.php';

// Load CRM system (v71.0)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-crm.php';

// Load shortcodes
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-shortcodes.php';

// Load quote handler
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-quote-handler.php';

// Load text helpers
require_once YOLO_YS_PLUGIN_DIR . 'includes/text-helpers.php';

// Load analytics and SEO (v41.19)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-analytics.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-meta-tags.php';
yolo_meta_tags(); // Initialize meta tags singleton (v75.7 fix)

// Load sitemap integration (v75.0 - Pretty URLs)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-sitemap.php';

// Load icons admin
if (is_admin()) {
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-icons-admin.php';
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-documents.php';
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-colors.php';
}

// v75.21: Allow SVG uploads for payment icons
add_filter('upload_mimes', function($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});

// v75.21: Fix SVG file type detection
add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext === 'svg') {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}, 10, 4);

// Load Stripe PHP library
if (file_exists(YOLO_YS_PLUGIN_DIR . 'stripe-php/init.php')) {
    require_once YOLO_YS_PLUGIN_DIR . 'stripe-php/init.php';
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-stripe.php';
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-stripe-handlers.php';
}

/**
 * The code that runs during plugin activation.
 */
function activate_yolo_yacht_search() {
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-activator.php';
    YOLO_YS_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_yolo_yacht_search() {
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-deactivator.php';
    YOLO_YS_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_yolo_yacht_search');
register_deactivation_hook(__FILE__, 'deactivate_yolo_yacht_search');

/**
 * Check for plugin updates and run migrations if needed (v80.7)
 */
function yolo_ys_check_version() {
    $installed_version = get_option('yolo_ys_version', '0');
    if (version_compare($installed_version, YOLO_YS_VERSION, '<')) {
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-activator.php';
        YOLO_YS_Activator::activate();
        update_option('yolo_ys_version', YOLO_YS_VERSION);
    }
}
add_action('plugins_loaded', 'yolo_ys_check_version');

/**
 * The core plugin class
 */
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-yacht-search.php';

/**
 * Begins execution of the plugin.
 */
function run_yolo_yacht_search() {
    $plugin = new YOLO_YS_Yacht_Search();
    $plugin->run();
}

run_yolo_yacht_search();

// Initialize CRM singleton to register hooks (v71.2 fix)
add_action('init', function() {
    if (function_exists('yolo_crm')) {
        yolo_crm();
    }
}, 20);

// v72.11: Initialize Auto-Sync to register AJAX handlers and cron hooks
add_action('init', function() {
    if (class_exists('YOLO_YS_Auto_Sync')) {
        new YOLO_YS_Auto_Sync();
    }
}, 20);

// v75.0: Run migrations on plugin update (activation hook doesn't run on updates)
add_action('plugins_loaded', function() {
    $installed_version = get_option('yolo_ys_db_version', '0');
    
    // If version is less than 75.4, run migrations
    if (version_compare($installed_version, '75.4', '<')) {
        // Always flush rewrite rules when updating to 75.4+
        update_option('yolo_ys_flush_rewrite_rules', true);
    }
    
    // If version is less than 75.0, run the slug migration
    if (version_compare($installed_version, '75.0', '<')) {
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-activator.php';
        
        // Run migrations (this will add slug column if missing)
        global $wpdb;
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        
        // Check if slug column exists
        $slug_column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$yachts_table} LIKE 'slug'"
        );
        
        if (empty($slug_column_exists)) {
            // Add slug column
            $wpdb->query(
                "ALTER TABLE {$yachts_table} 
                 ADD COLUMN slug varchar(255) DEFAULT NULL 
                 AFTER model"
            );
            
            // Add unique index on slug (ignore if already exists)
            $wpdb->query(
                "ALTER TABLE {$yachts_table} 
                 ADD UNIQUE KEY slug (slug)"
            );
            
            error_log('YOLO YS v75.0: Added slug column to yachts table');
        }
        
        // Generate slugs for existing yachts without slugs
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database.php';
        $updated = YOLO_YS_Database::generate_all_slugs();
        if ($updated > 0) {
            error_log('YOLO YS v75.0: Generated slugs for ' . $updated . ' yachts');
        }
        
        // Mark that we need to flush rewrite rules (will be done in init hook)
        update_option('yolo_ys_flush_rewrite_rules', true);
    }
    
    // v75.11: Add starting_from_price column to custom settings table
    if (version_compare($installed_version, '75.11', '<')) {
        global $wpdb;
        $custom_settings_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        
        // Check if table exists first
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$custom_settings_table}'");
        
        if ($table_exists) {
            // Check if starting_from_price column exists
            $column_exists = $wpdb->get_results(
                "SHOW COLUMNS FROM {$custom_settings_table} LIKE 'starting_from_price'"
            );
            
            if (empty($column_exists)) {
                // Add starting_from_price column
                $wpdb->query(
                    "ALTER TABLE {$custom_settings_table} 
                     ADD COLUMN starting_from_price decimal(10,2) DEFAULT 0 
                     COMMENT 'Starting from price for Facebook/Google Ads tracking (v75.11)'
                     AFTER custom_description"
                );
                error_log('YOLO YS v75.11: Added starting_from_price column to custom settings table');
            }
        }
    }
    
    // Update version to current
    if (version_compare($installed_version, '75.11', '<')) {
        update_option('yolo_ys_db_version', '75.11');
        error_log('YOLO YS: Database migrated to v75.11');
    }
}, 5);

// v75.4: Flush rewrite rules AFTER they are registered (in init hook)
add_action('init', function() {
    if (get_option('yolo_ys_flush_rewrite_rules', false)) {
        flush_rewrite_rules();
        delete_option('yolo_ys_flush_rewrite_rules');
        error_log('YOLO YS: Flushed rewrite rules for pretty URLs');
    }
}, 999); // High priority to run after rewrite rules are registered
