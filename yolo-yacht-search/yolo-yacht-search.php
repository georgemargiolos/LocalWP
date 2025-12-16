<?php
/**
 * Plugin Name: YOLO Yacht Search & Booking
 * Plugin URI: https://github.com/georgemargiolos/LocalWP
 * Description: Yacht search plugin with Booking Manager API integration for YOLO Charters. Features search widget and results blocks with company prioritization.
 * Version: 60.6.10
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
define('YOLO_YS_VERSION', '60.6.10');

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

// Load shortcodes
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-shortcodes.php';

// Load quote handler
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-quote-handler.php';

// Load text helpers
require_once YOLO_YS_PLUGIN_DIR . 'includes/text-helpers.php';

// Load analytics and SEO (v41.19)
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-analytics.php';
require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-meta-tags.php';

// Load icons admin
if (is_admin()) {
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-icons-admin.php';
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-documents.php';
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-colors.php';
}

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
 * The core plugin class
 */
require YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-yacht-search.php';

/**
 * Begins execution of the plugin.
 */
function run_yolo_yacht_search() {
    $plugin = new YOLO_YS_Yacht_Search();
    $plugin->run();
}

run_yolo_yacht_search();

// Initialize analytics and SEO (v41.19) - Wrapped in plugins_loaded hook to prevent critical errors
add_action('plugins_loaded', function() {
    yolo_analytics();
    yolo_meta_tags();
}, 10);

// Initialize icons admin (admin-only) - Wrapped in admin_init hook
add_action('admin_init', function() {
    $icons_admin = new YOLO_YS_Icons_Admin();
    add_action('admin_menu', array($icons_admin, 'register_menu'));
    add_action('wp_ajax_yolo_save_icon', array($icons_admin, 'ajax_save_icon'));
    add_action('wp_ajax_yolo_delete_icon', array($icons_admin, 'ajax_delete_icon'));

    // Initialize admin colors
    new YOLO_YS_Admin_Colors();
}, 10);

// Initialize warehouse notifications system - Wrapped in plugins_loaded hook
add_action('plugins_loaded', function() {
    new YOLO_YS_Warehouse_Notifications();
}, 10);

// Initialize auto-sync system (v30.0) - Wrapped in plugins_loaded hook
add_action('plugins_loaded', function() {
    new YOLO_YS_Auto_Sync();
}, 10);

/**
 * Check database version and run migrations if needed
 */
function yolo_ys_check_db_version() {
    $current_db_version = get_option('yolo_ys_db_version', '1.0');
    $required_db_version = '1.8'; // Updated for Admin Documents feature

    if (version_compare($current_db_version, $required_db_version, '<')) {
        error_log('YOLO YS: Database version outdated. Running migrations...');
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-activator.php';

        // Re-run table creation (dbDelta will update existing tables)
        YOLO_YS_Database::create_tables();

        // Also create Base Manager tables (added in v17.13)
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-base-manager-database.php';
        YOLO_YS_Base_Manager_Database::create_tables();

        // Run migrations
        $reflection = new ReflectionClass('YOLO_YS_Activator');
        $method = $reflection->getMethod('run_migrations');
        $method->setAccessible(true);
        $method->invoke(null);

        error_log('YOLO YS: Database migrations completed');
    }
}
add_action('plugins_loaded', 'yolo_ys_check_db_version');
