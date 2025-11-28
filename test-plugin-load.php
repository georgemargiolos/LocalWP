<?php
// Simulate WordPress constants
define('WPINC', 'wp-includes');
define('ABSPATH', '/tmp/');

// Define plugin constants
define('YOLO_YS_VERSION', '1.3.1');
define('YOLO_YS_PLUGIN_DIR', '/home/ubuntu/LocalWP/yolo-yacht-search/');
define('YOLO_YS_PLUGIN_URL', 'http://localhost/');
define('YOLO_YS_PLUGIN_BASENAME', 'yolo-yacht-search/yolo-yacht-search.php');

// Try to load the plugin
try {
    echo "Loading dependencies...\n";
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-booking-manager-api.php';
    echo "✓ Booking Manager API loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database.php';
    echo "✓ Database class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-database-prices.php';
    echo "✓ Database Prices class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-sync.php';
    echo "✓ Sync class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-loader.php';
    echo "✓ Loader class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-shortcodes.php';
    echo "✓ Shortcodes class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-quote-handler.php';
    echo "✓ Quote Handler class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin.php';
    echo "✓ Admin class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'public/class-yolo-ys-public.php';
    echo "✓ Public class loaded\n";
    
    require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-yacht-search.php';
    echo "✓ Main Yacht Search class loaded\n";
    
    echo "\n✅ All classes loaded successfully!\n";
    
} catch (Error $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
