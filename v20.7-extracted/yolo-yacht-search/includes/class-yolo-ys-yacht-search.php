<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * The core plugin class
 */
class YOLO_YS_Yacht_Search {
    
    protected $loader;
    protected $plugin_name;
    protected $version;
    
    public function __construct() {
        $this->version = YOLO_YS_VERSION;
        $this->plugin_name = 'yolo-yacht-search';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }
    
    private function load_dependencies() {
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-loader.php';
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-booking-manager-api.php';
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-shortcodes.php';
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-quote-handler.php';
        require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin.php';
        // Guest Licenses removed - replaced by Admin Documents
        require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-documents.php';
        require_once YOLO_YS_PLUGIN_DIR . 'includes/class-yolo-ys-admin-documents-shortcode.php';
        require_once YOLO_YS_PLUGIN_DIR . 'public/class-yolo-ys-public.php';
        require_once YOLO_YS_PLUGIN_DIR . 'public/class-yolo-ys-public-search.php';
        
        $this->loader = new YOLO_YS_Loader();
    }
    
    private function define_admin_hooks() {
        $plugin_admin = new YOLO_YS_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
        
        // Initialize improved document management admin panel (replaces old Guest Licenses)
        new YOLO_YS_Admin_Documents();
        
        // Initialize guest users
        new YOLO_YS_Guest_Users();

        // Initialize admin documents shortcode
        new YOLO_YS_Admin_Documents_Shortcode();
        
        // Initialize base manager system
        new YOLO_YS_Base_Manager();
        
        // Initialize quote requests system
        new YOLO_YS_Quote_Requests();
        
        // Initialize contact messages system
        new YOLO_YS_Contact_Messages();
        
        // Initialize shortcodes
        new YOLO_YS_Shortcodes();
        
        // Initialize Stripe handlers (only if class exists - loaded conditionally based on stripe-php)
        if (class_exists('YOLO_YS_Stripe_Handlers')) {
            new YOLO_YS_Stripe_Handlers();
        }
    }
    
    private function define_public_hooks() {
        $plugin_public = new YOLO_YS_Public($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        // AJAX handlers are registered in class-yolo-ys-public-search.php
        
        // Initialize quote handler for quote request form
        new YOLO_YS_Quote_Handler();
    }
    
    public function run() {
        $this->loader->run();
    }
    
    public function get_plugin_name() {
        return $this->plugin_name;
    }
    
    public function get_version() {
        return $this->version;
    }
}
