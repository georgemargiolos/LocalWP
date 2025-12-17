<?php
if (!defined('ABSPATH')) {
    exit;
}

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
        add_action('wp_ajax_yolo_ys_sync_equipment', array($this, 'ajax_sync_equipment'));
        add_action('wp_ajax_yolo_ys_sync_yachts', array($this, 'ajax_sync_yachts'));
        add_action('wp_ajax_yolo_ys_sync_prices', array($this, 'ajax_sync_prices'));
        
        // Yacht Customization AJAX handlers (v65.14)
        add_action('wp_ajax_yolo_save_yacht_custom_setting', array($this, 'ajax_save_yacht_custom_setting'));
        add_action('wp_ajax_yolo_save_yacht_custom_description', array($this, 'ajax_save_yacht_custom_description'));
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
        
        // Enqueue bookings CSS on bookings page
        $screen = get_current_screen();
        if ($screen && $screen->id === 'yolo-yacht-search_page_yolo-ys-bookings') {
            wp_enqueue_style(
                'yolo-ys-admin-bookings',
                YOLO_YS_PLUGIN_URL . 'admin/css/admin-bookings.css',
                array(),
                $this->version
            );
        }
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
            'edit_posts', // Changed from 'manage_options' to allow base managers (who have editor capabilities)
            'yolo-yacht-search',
            array($this, 'display_plugin_admin_page'),
            'dashicons-palmtree',
            30
        );
        
        // Add Settings submenu (replaces the auto-generated duplicate)
        add_submenu_page(
            'yolo-yacht-search',
            __('Settings', 'yolo-yacht-search'),
            __('Settings', 'yolo-yacht-search'),
            'edit_posts', // Changed to allow base managers
            'yolo-yacht-search',
            array($this, 'display_plugin_admin_page')
        );
        
        // Add Bookings submenu
        add_submenu_page(
            'yolo-yacht-search',
            __('Bookings', 'yolo-yacht-search'),
            __('Bookings', 'yolo-yacht-search'),
            'edit_posts', // Changed to allow base managers
            'yolo-ys-bookings',
            array($this, 'display_bookings_page')
        );
        
        // Add Texts submenu
        add_submenu_page(
            'yolo-yacht-search',
            __('Texts', 'yolo-yacht-search'),
            __('Texts', 'yolo-yacht-search'),
            'edit_posts', // Changed to allow base managers
            'yolo-yacht-texts',
            array($this, 'display_texts_page')
        );
        
        // Add Quote Requests submenu
        add_submenu_page(
            'yolo-yacht-search',
            __('Quote Requests', 'yolo-yacht-search'),
            __('Quote Requests', 'yolo-yacht-search'),
            'edit_posts', // Allow base managers
            'yolo-quote-requests',
            array($this, 'display_quote_requests_page')
        );
        
        // Add Contact Messages submenu
        add_submenu_page(
            'yolo-yacht-search',
            __('Contact Messages', 'yolo-yacht-search'),
            __('Contact Messages', 'yolo-yacht-search'),
            'edit_posts', // Allow base managers
            'yolo-contact-messages',
            array($this, 'display_contact_messages_page')
        );
        
        // Add Notification Settings submenu (admin only)
        add_submenu_page(
            'yolo-yacht-search',
            __('Notification Settings', 'yolo-yacht-search'),
            __('Notification Settings', 'yolo-yacht-search'),
            'manage_options', // Admin only
            'yolo-notification-settings',
            array($this, 'display_notification_settings_page')
        );
        
        // Add Yacht Customization submenu (v65.14)
        add_submenu_page(
            'yolo-yacht-search',
            __('Yacht Customization', 'yolo-yacht-search'),
            __('Yacht Customization', 'yolo-yacht-search'),
            'manage_options', // Admin only
            'yolo-yacht-customization',
            array($this, 'display_yacht_customization_page')
        );
    }
    
    /**
     * Display admin page
     */
    public function display_plugin_admin_page() {
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/yolo-yacht-search-admin-display.php';
    }
    
    /**
     * Display bookings page
     */
    public function display_bookings_page() {
        // Load required classes
        require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-bookings.php';
        require_once YOLO_YS_PLUGIN_DIR . 'admin/class-yolo-ys-admin-bookings-manager.php';
        
        // Display bookings list with tabbed view (table + calendar)
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/bookings-list-v2.php';
    }
    
    /**
     * Display texts customization page
     */
    public function display_texts_page() {
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/texts-page.php';
    }
    
    /**
     * Display quote requests page
     */
    public function display_quote_requests_page() {
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/quote-requests-list.php';
    }
    
    /**
     * Display contact messages page
     */
    public function display_contact_messages_page() {
        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        
        if ($action === 'view' && isset($_GET['message_id'])) {
            include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/contact-message-detail.php';
        } else {
            include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/contact-messages-list.php';
        }
    }
    
    /**
     * Display notification settings page
     */
    public function display_notification_settings_page() {
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/quote-notification-settings.php';
    }
    
    /**
     * Display yacht customization page (v65.14)
     */
    public function display_yacht_customization_page() {
        include_once YOLO_YS_PLUGIN_DIR . 'admin/partials/yacht-customization-page.php';
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
        
        // REMOVED in v30.0: cache_duration setting was never used
        // Data freshness now depends on manual sync or auto-sync cron jobs
        
        register_setting('yolo-yacht-search', 'yolo_ys_currency');
        add_settings_field(
            'yolo_ys_currency',
            __('Currency', 'yolo-yacht-search'),
            array($this, 'currency_callback'),
            'yolo-yacht-search',
            'yolo_ys_general_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_google_maps_api_key');
        add_settings_field(
            'yolo_ys_google_maps_api_key',
            __('Google Maps API Key', 'yolo-yacht-search'),
            array($this, 'google_maps_api_key_callback'),
            'yolo-yacht-search',
            'yolo_ys_general_settings'
        );
        
        // Stripe Settings Section
        add_settings_section(
            'yolo_ys_stripe_settings',
            __('Stripe Payment Settings', 'yolo-yacht-search'),
            array($this, 'stripe_settings_callback'),
            'yolo-yacht-search'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_stripe_publishable_key');
        add_settings_field(
            'yolo_ys_stripe_publishable_key',
            __('Stripe Publishable Key', 'yolo-yacht-search'),
            array($this, 'stripe_publishable_key_callback'),
            'yolo-yacht-search',
            'yolo_ys_stripe_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_stripe_secret_key');
        add_settings_field(
            'yolo_ys_stripe_secret_key',
            __('Stripe Secret Key', 'yolo-yacht-search'),
            array($this, 'stripe_secret_key_callback'),
            'yolo-yacht-search',
            'yolo_ys_stripe_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_stripe_webhook_secret');
        add_settings_field(
            'yolo_ys_stripe_webhook_secret',
            __('Stripe Webhook Secret', 'yolo-yacht-search'),
            array($this, 'stripe_webhook_secret_callback'),
            'yolo-yacht-search',
            'yolo_ys_stripe_settings'
        );
        

        register_setting('yolo-yacht-search', 'yolo_ys_deposit_percentage');
        add_settings_field(
            'yolo_ys_deposit_percentage',
            __('Deposit Percentage', 'yolo-yacht-search'),
            array($this, 'deposit_percentage_callback'),
            'yolo-yacht-search',
            'yolo_ys_stripe_settings'
        );
        
        // Styling Settings Section
        add_settings_section(
            'yolo_ys_styling_settings',
            __('Styling Settings', 'yolo-yacht-search'),
            array($this, 'styling_settings_callback'),
            'yolo-yacht-search'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_load_fontawesome');
        add_settings_field(
            'yolo_ys_load_fontawesome',
            __('Load FontAwesome from CDN', 'yolo-yacht-search'),
            array($this, 'load_fontawesome_callback'),
            'yolo-yacht-search',
            'yolo_ys_styling_settings'
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
        
        // Analytics & SEO Settings Section
        add_settings_section(
            'yolo_ys_analytics_settings',
            __('Analytics & SEO Settings', 'yolo-yacht-search'),
            array($this, 'analytics_settings_callback'),
            'yolo-yacht-search'
        );
        
        
        register_setting('yolo-yacht-search', 'yolo_default_og_image');
        add_settings_field(
            'yolo_default_og_image',
            __('Default Open Graph Image URL', 'yolo-yacht-search'),
            array($this, 'default_og_image_callback'),
            'yolo-yacht-search',
            'yolo_ys_analytics_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_twitter_handle');
        add_settings_field(
            'yolo_twitter_handle',
            __('Twitter Handle', 'yolo-yacht-search'),
            array($this, 'twitter_handle_callback'),
            'yolo-yacht-search',
            'yolo_ys_analytics_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_enable_schema');
        add_settings_field(
            'yolo_enable_schema',
            __('Enable Schema.org Structured Data', 'yolo-yacht-search'),
            array($this, 'enable_schema_callback'),
            'yolo-yacht-search',
            'yolo_ys_analytics_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_enable_debug_mode');
        add_settings_field(
            'yolo_enable_debug_mode',
            __('Enable Analytics Debug Mode', 'yolo-yacht-search'),
            array($this, 'enable_debug_mode_callback'),
            'yolo-yacht-search',
            'yolo_ys_analytics_settings'
        );
        
        // Facebook Conversions API Settings
        register_setting('yolo-yacht-search', 'yolo_ys_fb_pixel_id');
        add_settings_field(
            'yolo_ys_fb_pixel_id',
            __('Facebook Pixel ID', 'yolo-yacht-search'),
            array($this, 'fb_pixel_id_callback'),
            'yolo-yacht-search',
            'yolo_ys_analytics_settings'
        );
        
        register_setting('yolo-yacht-search', 'yolo_ys_fb_access_token');
        add_settings_field(
            'yolo_ys_fb_access_token',
            __('Facebook Conversions API Access Token', 'yolo-yacht-search'),
            array($this, 'fb_access_token_callback'),
            'yolo-yacht-search',
            'yolo_ys_analytics_settings'
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
    
    // REMOVED in v30.0: cache_duration_callback() - setting was never used
    
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
    
    public function google_maps_api_key_callback() {
        $value = get_option('yolo_ys_google_maps_api_key', 'AIzaSyB4aSnafHcLVFdMSBnLf_0wRjYHhj7P4L4');
        echo '<input type="text" name="yolo_ys_google_maps_api_key" value="' . esc_attr($value) . '" class="large-text code" />';
        echo '<p class="description">' . __('Google Maps API key for displaying yacht locations on maps', 'yolo-yacht-search') . '</p>';
    }
    
    public function load_fontawesome_callback() {
        $value = get_option('yolo_ys_load_fontawesome', '0');
        echo '<label><input type="checkbox" name="yolo_ys_load_fontawesome" value="1" ' . checked($value, '1', false) . ' /> ' . __('Load FontAwesome 6 from CDN', 'yolo-yacht-search') . '</label>';
        echo '<p class="description">' . __('Uncheck this if your theme already loads FontAwesome (e.g., FontAwesome 7 Kit). Default: unchecked.', 'yolo-yacht-search') . '</p>';
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
    
    // Stripe Settings Callbacks
    public function stripe_settings_callback() {
        echo '<p>' . __('Configure Stripe payment gateway for accepting yacht bookings. Get your API keys from <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a>.', 'yolo-yacht-search') . '</p>';
    }
    
    public function stripe_publishable_key_callback() {
        $value = get_option('yolo_ys_stripe_publishable_key', 'pk_test_51ST5sKEqtLDG25BLYenhP94HzLvKGFhAjOFNTZVZpUZLUNJVUkXoGEYoypHzmqVltBELrX2QpsVhhqzcRgvPyedG00Wpt5SF3d');
        echo '<input type="text" name="yolo_ys_stripe_publishable_key" value="' . esc_attr($value) . '" class="large-text code" placeholder="pk_test_... or pk_live_..." />';
        echo '<p class="description">' . __('Your Stripe publishable key. Use pk_test_ for testing or pk_live_ for live payments.', 'yolo-yacht-search') . '</p>';
    }
    
    public function stripe_secret_key_callback() {
        $value = get_option('yolo_ys_stripe_secret_key', 'sk_test_51ST5sKEqtLDG25BLFqTjNKXepps0axIoIafVyOQ1eVn3lRXoTQ3z0oB4TlqLQ8mhM19F5QBrO5MxCMZ1NN7kmITT00IK1vaUhE');
        echo '<input type="password" name="yolo_ys_stripe_secret_key" value="' . esc_attr($value) . '" class="large-text code" placeholder="sk_test_... or sk_live_..." />';
        echo '<p class="description">' . __('Your Stripe secret key. Use sk_test_ for testing or sk_live_ for live payments. Keep this secure!', 'yolo-yacht-search') . '</p>';
    }
    
    public function stripe_webhook_secret_callback() {
        $value = get_option('yolo_ys_stripe_webhook_secret', '');
        $webhook_url = home_url('/wp-json/yolo-yacht-search/v1/stripe-webhook');
        echo '<input type="password" name="yolo_ys_stripe_webhook_secret" value="' . esc_attr($value) . '" class="large-text code" placeholder="whsec_... (optional)" />';
        echo '<p class="description"><strong style="color: #10b981;">✓ Webhooks are OPTIONAL!</strong> Bookings are automatically created when customers return from payment.</p>';
        echo '<p class="description">' . __('For production reliability, you can optionally setup webhook at: <code>' . esc_html($webhook_url) . '</code>', 'yolo-yacht-search') . '</p>';
        echo '<p class="description">' . __('Add this URL to your <a href="https://dashboard.stripe.com/webhooks" target="_blank">Stripe Webhooks</a> and listen for <code>checkout.session.completed</code> event.', 'yolo-yacht-search') . '</p>';
    }
    

    
    public function deposit_percentage_callback() {
        $value = get_option('yolo_ys_deposit_percentage', '50');
        echo '<input type="number" name="yolo_ys_deposit_percentage" value="' . esc_attr($value) . '" class="small-text" min="1" max="100" step="1" /> %';
        echo '<p class="description">' . __('Percentage of charter price to charge as deposit (1-100%). Customer pays remaining balance later. Example: 50% means customer pays half now, half later.', 'yolo-yacht-search') . '</p>';
    }
    
    // Analytics & SEO Callbacks
    public function analytics_settings_callback() {
        echo '<p>' . __('Configure SEO and tracking settings. This plugin sends custom yacht booking events to both Google Analytics 4 (via GTM) and Facebook Conversions API (server-side).', 'yolo-yacht-search') . '</p>';
        echo '<p><strong>' . __('Facebook Conversions API:', 'yolo-yacht-search') . '</strong> ' . __('Server-side tracking for better data quality and attribution. Events are sent directly from your WordPress server to Facebook, bypassing browser limitations.', 'yolo-yacht-search') . '</p>';
    }
    
    
    public function default_og_image_callback() {
        $value = get_option('yolo_default_og_image', '');
        echo '<input type="url" name="yolo_default_og_image" value="' . esc_attr($value) . '" class="regular-text" placeholder="https://example.com/image.jpg" />';
        echo '<p class="description">' . __('Default image URL for social media sharing (Open Graph). Recommended size: 1200x630px.', 'yolo-yacht-search') . '</p>';
    }
    
    public function twitter_handle_callback() {
        $value = get_option('yolo_twitter_handle', '');
        echo '<input type="text" name="yolo_twitter_handle" value="' . esc_attr($value) . '" class="regular-text" placeholder="@YOLOCharters" />';
        echo '<p class="description">' . __('Your Twitter/X handle (e.g., @YOLOCharters) for Twitter Card attribution.', 'yolo-yacht-search') . '</p>';
    }
    
    public function enable_schema_callback() {
        $value = get_option('yolo_enable_schema', '1');
        echo '<input type="checkbox" name="yolo_enable_schema" value="1" ' . checked('1', $value, false) . ' />';
        echo '<label>' . __('Enable Schema.org structured data for better SEO and rich Google results', 'yolo-yacht-search') . '</label>';
    }
    
    public function enable_debug_mode_callback() {
        $value = get_option('yolo_enable_debug_mode', '0');
        echo '<input type="checkbox" name="yolo_enable_debug_mode" value="1" ' . checked('1', $value, false) . ' />';
        echo '<label>' . __('Enable debug mode (shows analytics events in browser console and logs server-side events)', 'yolo-yacht-search') . '</label>';
    }
    
    public function fb_pixel_id_callback() {
        $value = get_option('yolo_ys_fb_pixel_id', '');
        echo '<input type="text" name="yolo_ys_fb_pixel_id" value="' . esc_attr($value) . '" class="regular-text" placeholder="1896226957957033" />';
        echo '<p class="description">' . __('Your Facebook Pixel ID (15-16 digits). Find it in Facebook Events Manager.', 'yolo-yacht-search') . '</p>';
    }
    
    public function fb_access_token_callback() {
        $value = get_option('yolo_ys_fb_access_token', '');
        echo '<textarea name="yolo_ys_fb_access_token" rows="3" class="large-text code" placeholder="EAAc8FRZAYvDs...">' . esc_textarea($value) . '</textarea>';
        echo '<p class="description">' . __('Your Facebook Conversions API Access Token. Generate it in Facebook Events Manager > Settings > Conversions API.', 'yolo-yacht-search') . '</p>';
        echo '<p class="description"><strong>' . __('Events tracked server-side:', 'yolo-yacht-search') . '</strong> ViewContent (yacht view), Lead (quote request), Purchase (booking completed)</p>';
        echo '<p class="description"><strong>' . __('Events tracked client-side:', 'yolo-yacht-search') . '</strong> Search, AddToCart (week selection), InitiateCheckout (book now), AddPaymentInfo (form submission)</p>';
    }
    
    /**
     * AJAX handler for equipment catalog sync
     */
    public function ajax_sync_equipment() {
        check_ajax_referer('yolo_ys_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        $sync = new YOLO_YS_Sync();
        $result = $sync->sync_equipment_catalog();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
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
    
    /**
     * AJAX handler for offers sync
     */
    public function ajax_sync_prices() {
        check_ajax_referer('yolo_ys_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
        }
        
        // Get year parameter (default to next year)
        $year = isset($_POST['year']) ? intval($_POST['year']) : (date('Y') + 1);
        
        $sync = new YOLO_YS_Sync();
        $result = $sync->sync_all_offers($year);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * AJAX: Save yacht custom setting (use_custom_media or use_custom_description)
     * @since 65.14
     */
    public function ajax_save_yacht_custom_setting() {
        check_ajax_referer('yolo_yacht_customization_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
        $setting = isset($_POST['setting']) ? sanitize_text_field($_POST['setting']) : '';
        $value = isset($_POST['value']) ? intval($_POST['value']) : 0;
        
        if (empty($yacht_id) || !in_array($setting, array('use_custom_media', 'use_custom_description'))) {
            wp_send_json_error('Invalid parameters');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        
        // Check if record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT yacht_id FROM {$table} WHERE yacht_id = %s",
            $yacht_id
        ));
        
        if ($exists) {
            $wpdb->update(
                $table,
                array($setting => $value),
                array('yacht_id' => $yacht_id),
                array('%d'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $table,
                array(
                    'yacht_id' => $yacht_id,
                    $setting => $value
                ),
                array('%s', '%d')
            );
        }
        
        wp_send_json_success('Setting saved');
    }
    
    /**
     * AJAX: Save yacht custom description
     * @since 65.14
     */
    public function ajax_save_yacht_custom_description() {
        check_ajax_referer('yolo_yacht_customization_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        $yacht_id = isset($_POST['yacht_id']) ? sanitize_text_field($_POST['yacht_id']) : '';
        $description = isset($_POST['description']) ? wp_kses_post(wp_unslash($_POST['description'])) : '';
        
        if (empty($yacht_id)) {
            wp_send_json_error('Invalid yacht ID');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        
        // Check if record exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT yacht_id FROM {$table} WHERE yacht_id = %s",
            $yacht_id
        ));
        
        if ($exists) {
            $wpdb->update(
                $table,
                array('custom_description' => $description),
                array('yacht_id' => $yacht_id),
                array('%s'),
                array('%s')
            );
        } else {
            $wpdb->insert(
                $table,
                array(
                    'yacht_id' => $yacht_id,
                    'custom_description' => $description
                ),
                array('%s', '%s')
            );
        }
        
        wp_send_json_success('Description saved');
    }
}
