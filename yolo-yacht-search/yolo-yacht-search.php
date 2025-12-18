<?php
/**
 * Plugin Name: YOLO Yacht Search & Booking
 * Plugin URI: https://github.com/georgemargiolos/LocalWP
 * Description: Yacht search plugin with Booking Manager API integration for YOLO Charters. Features search widget and results blocks with company prioritization.
 * Version: 72.0
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
define('YOLO_YS_VERSION', '72.0');

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
