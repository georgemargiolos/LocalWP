<?php
/**
 * Plugin Name: YOLO Yacht Search & Booking
 * Plugin URI: https://github.com/georgemargiolos/LocalWP
 * Description: Yacht search plugin with Booking Manager API integration for YOLO Charters. Features search widget and results blocks with company prioritization.
 * Version: 1.1.0
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
define('YOLO_YS_VERSION', '1.1.0');

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

/**
 * The code that runs during plugin activation.
 */
function activate_yolo_yacht_search() {
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-activator.php';
    YOLO_YS_Activator::activate();
    
    // Create database tables
    $db = new YOLO_YS_Database();
    $db->create_tables();
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
