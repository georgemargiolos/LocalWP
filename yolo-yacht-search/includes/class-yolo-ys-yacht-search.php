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
        $this->define_rewrite_rules();
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
        
        // AJAX handlers for Facebook event tracking
        $this->loader->add_action('wp_ajax_yolo_track_add_to_cart', $plugin_public, 'ajax_track_add_to_cart');
        $this->loader->add_action('wp_ajax_nopriv_yolo_track_add_to_cart', $plugin_public, 'ajax_track_add_to_cart');
        $this->loader->add_action('wp_ajax_yolo_track_initiate_checkout', $plugin_public, 'ajax_track_initiate_checkout');
        $this->loader->add_action('wp_ajax_nopriv_yolo_track_initiate_checkout', $plugin_public, 'ajax_track_initiate_checkout');
        
        // Other AJAX handlers are registered in class-yolo-ys-public-search.php
        
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
    
    /**
     * Define rewrite rules for pretty yacht URLs
     */
    private function define_rewrite_rules() {
        add_action('init', array($this, 'add_yacht_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_yacht_query_vars'));
        add_action('template_redirect', array($this, 'handle_yacht_redirect'));
    }
    
    /**
     * Add rewrite rules for pretty yacht URLs
     */
    public function add_yacht_rewrite_rules() {
        // Match /yacht/yacht-slug/
        add_rewrite_rule(
            '^yacht/([^/]+)/?$',
            'index.php?pagename=yacht-details-page&yacht_slug=$matches[1]',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_yacht_query_vars($vars) {
        $vars[] = 'yacht_slug';
        return $vars;
    }
    
    /**
     * Handle redirect from old URLs to new URLs (301 redirect for SEO)
     */
    public function handle_yacht_redirect() {
        // Redirect old ?yacht_id= URLs to new /yacht/slug/ URLs
        if (isset($_GET['yacht_id']) && !get_query_var('yacht_slug', '')) {
            global $wpdb;
            $yacht_id = sanitize_text_field($_GET['yacht_id']);
            
            $yacht = $wpdb->get_row($wpdb->prepare(
                "SELECT slug FROM {$wpdb->prefix}yolo_yachts WHERE id = %s",
                $yacht_id
            ));
            
            if ($yacht && !empty($yacht->slug)) {
                $new_url = home_url('/yacht/' . $yacht->slug . '/');
                
                // Preserve date parameters
                if (isset($_GET['dateFrom'])) {
                    $new_url = add_query_arg('dateFrom', sanitize_text_field($_GET['dateFrom']), $new_url);
                }
                if (isset($_GET['dateTo'])) {
                    $new_url = add_query_arg('dateTo', sanitize_text_field($_GET['dateTo']), $new_url);
                }
                
                wp_redirect($new_url, 301);
                exit;
            }
        }
    }
}
