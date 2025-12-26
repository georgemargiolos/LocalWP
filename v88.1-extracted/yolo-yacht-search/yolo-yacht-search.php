<?php
/**
 * Plugin Name: YOLO Yacht Search & Booking
 * Plugin URI: https://github.com/georgemargiolos/LocalWP
 * Description: Yacht search plugin with Booking Manager API integration for YOLO Charters. Features search widget and results blocks with company prioritization.
 * Version: 88.8
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
define('YOLO_YS_VERSION', '88.8');

// Plugin directory path
define('YOLO_YS_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Plugin directory URL
define('YOLO_YS_PLUGIN_URL', plugin_dir_url(__FILE__));

// Plugin basename
define('YOLO_YS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Greek Ionian Base IDs - hardcoded for reliable filtering (v81.13)
// These are verified Greek Ionian bases: Lefkada, Corfu, Kefalonia, Zakynthos, Ithaca, Preveza, Paxos
define('YOLO_YS_GREEK_IONIAN_BASE_IDS', array(
    // Lefkada
    194,                    // D-Marin Marina Lefkas
    1639588120000100000,    // Port of Lefkas
    757699090000100000,     // Nikiana Marina
    1392047590000100000,    // Marina Perigiali
    1470547850000100000,    // Nydri Port
    5720014270000100000,    // Nydri Marina
    7263354840000100000,    // Hotel Armonia - Nydri
    6100618350000100000,    // Port of Vasiliki
    4038352310000100000,    // Vliho Yacht Club
    5018374970000100000,    // Vliho Bay
    2863072060000100000,    // Sivota Marina Lefkada
    6647930080000100000,    // Lygia Marina
    // Corfu
    14,                     // Corfu harbor
    492201260000100000,     // D-Marin Marina Gouvia
    3129817260000100000,    // Marina Benitses
    3885298680000100000,    // Old Port Corfu
    3885342670000100000,    // Corfu Sailing Club - Mandraki
    3143071690000100000,    // NAOK Sailing Club
    4483668290000100000,    // Alipa port - Palaiokastritsas
    // Kefalonia
    699817710000100000,     // Argostoli Yacht Marina
    6368129940000100000,    // Fiskardo Marina
    133,                    // Sami Port
    1472810110000100000,    // Agia Effimia
    3969163100000100000,    // Agia Pelagia Marina
    // Zakynthos
    153,                    // Zakynthos Marina
    23,                     // Zante
    5714550710000100000,    // Agios Sostis Harbor
    // Ithaca
    155,                    // Marina Ithakis - Vathy
    4837974350000100000,    // Port of Ithaca
    // Preveza area
    89,                     // Preveza Marina
    1935994390000100000,    // Preveza Main Port
    2491645230000100000,    // Cleopatra Marina
    6192088780000100000,    // Port of Mitikas
    6827132820000100000,    // Mytikas Port
    3838448700000100000,    // Port of Plataria
    1976257640000100000,    // Marina Sivota
    973630110000100000,     // Marina of Vonitsa
    395874570000100000,     // Marina Paleros
    96447290000100000,      // Vounaki - Palairos
    3868266710000100000,    // Marina Astakos
    // Paxos
    18                      // Paxos - Gaios
));

// Location to Base IDs mapping for search filters (v81.17)
define('YOLO_YS_LOCATION_BASE_IDS', array(
    'Lefkada' => array(
        194, 1639588120000100000, 757699090000100000, 1392047590000100000,
        1470547850000100000, 5720014270000100000, 7263354840000100000,
        6100618350000100000, 4038352310000100000, 5018374970000100000,
        2863072060000100000, 6647930080000100000
    ),
    'Corfu' => array(
        14, 492201260000100000, 3129817260000100000, 3885298680000100000,
        3885342670000100000, 3143071690000100000, 4483668290000100000
    ),
    'Kefalonia' => array(
        699817710000100000, 6368129940000100000, 133, 1472810110000100000,
        3969163100000100000
    ),
    'Zakynthos' => array(153, 23, 5714550710000100000),
    'Ithaca' => array(155, 4837974350000100000),
    'Preveza' => array(
        89, 1935994390000100000, 2491645230000100000,
        6192088780000100000, 6827132820000100000
    ),
    'Syvota' => array(1976257640000100000),
    'Vonitsa' => array(973630110000100000),
    'Palairos' => array(395874570000100000, 96447290000100000),
    'Plataria' => array(3838448700000100000),
    'Astakos' => array(3868266710000100000),
    'Paxos' => array(18)
));

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

// Load Facebook catalog feed (v86.1)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-facebook-catalog.php';

// Load CRM system (v71.0)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-crm.php';

// Load shortcodes
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-shortcodes.php';

// Load quote handler
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-quote-handler.php';

// Load text helpers
require_once YOLO_YS_PLUGIN_DIR . 'includes/text-helpers.php';

// Load airport helper (v81.18)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-airport-helper.php';

// Load marina coordinates helper (v86.4)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-marina-coordinates.php';

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

/**
 * Facebook Catalog Feed Endpoint
 * @since 86.1
 */
add_action('init', 'yolo_ys_register_fb_catalog_endpoint');
function yolo_ys_register_fb_catalog_endpoint() {
    add_rewrite_rule('^facebook-catalog-feed/?$', 'index.php?yolo_fb_catalog=1', 'top');
}

add_filter('query_vars', 'yolo_ys_fb_catalog_query_vars');
function yolo_ys_fb_catalog_query_vars($vars) {
    $vars[] = 'yolo_fb_catalog';
    return $vars;
}

add_action('template_redirect', 'yolo_ys_fb_catalog_template_redirect');
function yolo_ys_fb_catalog_template_redirect() {
    if (get_query_var('yolo_fb_catalog')) {
        // v86.6: Basic rate limiting - max 10 requests per minute per IP
        $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
        $transient_key = 'yolo_fb_rate_' . md5($ip);
        $request_count = (int) get_transient($transient_key);
        
        if ($request_count >= 10) {
            status_header(429);
            echo 'Rate limit exceeded. Please try again later.';
            exit;
        }
        
        set_transient($transient_key, $request_count + 1, 60); // 60 seconds
        
        if (class_exists('YOLO_YS_Facebook_Catalog')) {
            $catalog = new YOLO_YS_Facebook_Catalog();
            $catalog->output_feed();
        }
        exit;
    }
}

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
    
    // v81.19: Add home_base_id column to yachts table
    if (version_compare($installed_version, '81.19', '<')) {
        global $wpdb;
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        
        // Check if home_base_id column exists
        $column_exists = $wpdb->get_results(
            "SHOW COLUMNS FROM {$yachts_table} LIKE 'home_base_id'"
        );
        
        if (empty($column_exists)) {
            // Add home_base_id column
            $wpdb->query(
                "ALTER TABLE {$yachts_table} 
                 ADD COLUMN home_base_id bigint(20) DEFAULT NULL 
                 AFTER home_base"
            );
            error_log('YOLO YS v81.19: Added home_base_id column to yachts table');
        }
    }
    
    // Update version to current
    if (version_compare($installed_version, '81.19', '<')) {
        update_option('yolo_ys_db_version', '81.19');
        error_log('YOLO YS: Database migrated to v81.19');
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
