<?php
/**
 * The admin-specific functionality of the plugin
 */
class YOLO_YS_Admin {
    
    private $plugin_name;
    private $version;
    
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        
        // Add AJAX handlers
        add_action('wp_ajax_yolo_ys_sync_yachts', array($this, 'ajax_sync_yachts'));
    }
    
    /**
     * Enqueue admin styles
     */
    public function enqueue_styles() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style(
            $this->plugin_name,
            YOLO_YS_PLUGIN_URL . 'admin/css/yolo-yacht-search-admin.css',
            array(),
            $this->version
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script(
            $this->plugin_name,
            YOLO_YS_PLUGIN_URL . 'admin/js/yolo-yacht-search-admin.js',
            array('jquery', 'wp-color-picker'),
            $this->version,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script($this->plugin_name, 'yoloYsAdmin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yolo_ys_admin_nonce')
        ));
    }
    
    /**
     * Add plugin admin menu
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('YOLO Yacht Search', 'yolo-yacht-search'),
            __('YOLO Yacht Search', 'yolo-yacht-search'),
            'manage_options',
            'yolo-yacht-search',
            array($this, 'display_plugin_admin_page'),
            'dashicons-palmtree',
            30
        );
    }
    
    /**
     * Display admin page
     */
    public function display_plugin_admin_page() {
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/yolo-yacht-search-admin-display.php';
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        // API Settings Section
        add_settings_section(
            'yolo_ys_api_settings',
            __('Booking Manager API Settings', 'yolo-yacht-search'),
            array($this, 'api_settings_callback'),
            'yolo-yacht-search'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_api_key');
        add_settings_field(
            'yolo_ys_api_key',
            __('API Key', 'yolo-yacht-search'),
            array($this, 'api_key_callback'),
            'yolo-yacht-search',
            'yolo_ys_api_settings'
        );
        
        // Company Settings Section
        add_settings_section(
            'yolo_ys_company_settings',
            __('Company Settings', 'yolo-yacht-search'),
            array($this, 'company_settings_callback'),
            'yolo-yacht-search'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_my_company_id');
        add_settings_field(
            'yolo_ys_my_company_id',
            __('My Company ID (YOLO)', 'yolo-yacht-search'),
            array($this, 'my_company_id_callback'),
            'yolo-yacht-search',
            'yolo_ys_company_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_friend_companies');
        add_settings_field(
            'yolo_ys_friend_companies',
            __('Friend Companies IDs', 'yolo-yacht-search'),
            array($this, 'friend_companies_callback'),
            'yolo-yacht-search',
            'yolo_ys_company_settings'
        );
        
        // Results Page Setting
        register_setting('yolo-yacht-search', 'yolo_ys_results_page');
        add_settings_field(
            'yolo_ys_results_page',
            __('Search Results Page', 'yolo-yacht-search'),
            array($this, 'results_page_callback'),
            'yolo-yacht-search',
            'yolo_ys_company_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_yacht_details_page');
        add_settings_field(
            'yolo_ys_yacht_details_page',
            __('Yacht Details Page', 'yolo-yacht-search'),
            array($this, 'yacht_details_page_callback'),
            'yolo-yacht-search',
            'yolo_ys_company_settings'
        );
        
        // General Settings Section
        add_settings_section(
            'yolo_ys_general_settings',
            __('General Settings', 'yolo-yacht-search'),
            array($this, 'general_settings_callback'),
            'yolo-yacht-search'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_cache_duration');
        add_settings_field(
            'yolo_ys_cache_duration',
            __('Cache Duration (hours)', 'yolo-yacht-search'),
            array($this, 'cache_duration_callback'),
            'yolo-yacht-search',
            'yolo_ys_general_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_currency');
        add_settings_field(
            'yolo_ys_currency',
            __('Currency', 'yolo-yacht-search'),
            array($this, 'currency_callback'),
            'yolo-yacht-search',
            'yolo_ys_general_settings'
        );
        
        // Styling Settings Section
        add_settings_section(
            'yolo_ys_styling_settings',
            __('Styling Settings', 'yolo-yacht-search'),
            array($this, 'styling_settings_callback'),
            'yolo-yacht-search'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_primary_color', array('sanitize_callback' => 'sanitize_hex_color'));
        add_settings_field(
            'yolo_ys_primary_color',
            __('Primary Color', 'yolo-yacht-search'),
            array($this, 'primary_color_callback'),
            'yolo-yacht-search',
            'yolo_ys_styling_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_button_bg_color', array('sanitize_callback' => 'sanitize_hex_color'));
        add_settings_field(
            'yolo_ys_button_bg_color',
            __('Button Background Color', 'yolo-yacht-search'),
            array($this, 'button_bg_color_callback'),
            'yolo-yacht-search',
            'yolo_ys_styling_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_button_text_color', array('sanitize_callback' => 'sanitize_hex_color'));
        add_settings_field(
            'yolo_ys_button_text_color',
            __('Button Text Color', 'yolo-yacht-search'),
            array($this, 'button_text_color_callback'),
            'yolo-yacht-search',
            'yolo_ys_styling_settings'
        );
    }
    
    // Section Callbacks
    public function api_settings_callback() {
        echo '<p>' . __('Configure your Booking Manager API credentials.', 'yolo-yacht-search') . '</p>';
    }
    
    public function company_settings_callback() {
        echo '<p>' . __('Configure your company ID and friend companies. Your boats (YOLO - 7850) will appear first in search results.', 'yolo-yacht-search') . '</p>';
    }
    
    public function general_settings_callback() {
        echo '<p>' . __('General plugin settings.', 'yolo-yacht-search') . '</p>';
    }
    
    public function styling_settings_callback() {
        echo '<p>' . __('Customize the appearance of the search forms and results.', 'yolo-yacht-search') . '</p>';
    }
    
    // Field Callbacks
    public function api_key_callback() {
        $value = get_option('yolo_ys_api_key', '');
        echo '<textarea name="yolo_ys_api_key" rows="3" class="large-text code">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Your Booking Manager API key (prefilled)', 'yolo-yacht-search') . '</p>';
    }
    
    public function my_company_id_callback() {
        $value = get_option('yolo_ys_my_company_id', '7850');
        echo '<input type="text" name="yolo_ys_my_company_id" value="' . esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">' . __('Your YOLO company ID (boats will show first in results)', 'yolo-yacht-search') . '</p>';
    }
    
    public function friend_companies_callback() {
        $value = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        echo '<input type="text" name="yolo_ys_friend_companies" value="' . esc_attr($value) . '" class="large-text" />';
        echo '<p class="description">' . __('Comma-separated list of friend company IDs (prefilled: 4366, 3604, 6711)', 'yolo-yacht-search') . '</p>';
    }
    
    public function results_page_callback() {
        $value = get_option('yolo_ys_results_page', '');
        $pages = get_pages();
        
        echo '<select name="yolo_ys_results_page" class="regular-text">';
        echo '<option value="">' . __('Select a page...', 'yolo-yacht-search') . '</option>';
        foreach ($pages as $page) {
            $selected = ($value == $page->ID) ? 'selected' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Select the page where search results will be displayed (must contain [yolo_search_results] shortcode)', 'yolo-yacht-search') . '</p>';
    }
    
    public function yacht_details_page_callback() {
        $value = get_option('yolo_ys_yacht_details_page', '');
        $pages = get_pages();
        
        echo '<select name="yolo_ys_yacht_details_page" class="regular-text">';
        echo '<option value="">' . __('Select a page...', 'yolo-yacht-search') . '</option>';
        foreach ($pages as $page) {
            $selected = ($value == $page->ID) ? 'selected' : '';
            echo '<option value="' . esc_attr($page->ID) . '" ' . $selected . '>' . esc_html($page->post_title) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('Select the page where yacht details will be displayed (must contain [yolo_yacht_details] shortcode)', 'yolo-yacht-search') . '</p>';
    }
    
    public function cache_duration_callback() {
        $value = get_option('yolo_ys_cache_duration', '24');
        echo '<input type="number" name="yolo_ys_cache_duration" value="' . esc_attr($value) . '" class="small-text" min="1" max="168" />';
        echo '<p class="description">' . __('How long to cache API results (1-168 hours)', 'yolo-yacht-search') . '</p>';
    }
    
    public function currency_callback() {
        $value = get_option('yolo_ys_currency', 'EUR');
        $currencies = array('EUR' => 'Euro (€)', 'USD' => 'US Dollar ($)', 'GBP' => 'British Pound (£)');
        
        echo '<select name="yolo_ys_currency" class="regular-text">';
        foreach ($currencies as $code => $name) {
            $selected = ($value == $code) ? 'selected' : '';
            echo '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($name) . '</option>';
        }
        echo '</select>';
    }
    
    public function primary_color_callback() {
        $value = get_option('yolo_ys_primary_color', '#1e3a8a');
        echo '<input type="text" name="yolo_ys_primary_color" value="' . esc_attr($value) . '" class="color-picker" />';
    }
    
    public function button_bg_color_callback() {
        $value = get_option('yolo_ys_button_bg_color', '#dc2626');
        echo '<input type="text" name="yolo_ys_button_bg_color" value="' . esc_attr($value) . '" class="color-picker" />';
    }
    
    public function button_text_color_callback() {
        $value = get_option('yolo_ys_button_text_color', '#ffffff');
        echo '<input type="text" name="yolo_ys_button_text_color" value="' . esc_attr($value) . '" class="color-picker" />';
    }
    
    /**
     * AJAX handler for yacht sync
     */
    public function ajax_sync_yachts() {
        check_ajax_referer('yolo_ys_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $sync = new YOLO_YS_Sync();
        $result = $sync->sync_all_yachts();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}
