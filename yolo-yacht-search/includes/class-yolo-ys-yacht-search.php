<?php
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
    }
    
    private function define_public_hooks() {
        $plugin_public = new YOLO_YS_Public($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        // AJAX handlers are registered in class-yolo-ys-public-search.php
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
