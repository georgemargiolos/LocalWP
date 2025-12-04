<?php
/**
 * YOLO Yacht Search - Admin Colors Settings
 * Version: 2.6.0
 * 
 * Add this file to: yolo-yacht-search/admin/class-yolo-ys-admin-colors.php
 * Then add the menu hook in yolo-yacht-search.php (see instructions)
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Admin_Colors {
    
    /**
     * Color settings configuration
     */
    private $color_settings = array();
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->init_color_settings();
        add_action('admin_menu', array($this, 'add_submenu_page'), 20); // Priority 20 to load after main menu
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_color_picker'));
    }
    
    /**
     * Initialize color settings with descriptions
     */
    private function init_color_settings() {
        $this->color_settings = array(
            // Primary Colors
            'yolo_ys_color_primary' => array(
                'label' => 'Primary Color',
                'description' => 'Main brand color used for: Request a Quote button, Select Week buttons, Date picker border on focus, Section heading borders, Equipment icons background',
                'default' => '#1e3a8a',
                'group' => 'Primary Colors'
            ),
            'yolo_ys_color_primary_hover' => array(
                'label' => 'Primary Hover Color',
                'description' => 'Hover state for primary buttons: Request a Quote hover, Select Week hover, Form submit buttons hover',
                'default' => '#1e40af',
                'group' => 'Primary Colors'
            ),
            
            // Secondary Colors (CTA)
            'yolo_ys_color_secondary' => array(
                'label' => 'Book Now Button Color',
                'description' => 'The main BOOK NOW call-to-action button background color',
                'default' => '#b91c1c',
                'group' => 'Call-to-Action'
            ),
            'yolo_ys_color_secondary_hover' => array(
                'label' => 'Book Now Button Hover',
                'description' => 'BOOK NOW button color when user hovers over it',
                'default' => '#991b1b',
                'group' => 'Call-to-Action'
            ),
            
            // Status Colors
            'yolo_ys_color_success' => array(
                'label' => 'Price Color (Green)',
                'description' => 'Final price display color, Extra prices, Success messages',
                'default' => '#059669',
                'group' => 'Status Colors'
            ),
            'yolo_ys_color_warning' => array(
                'label' => 'Discount Badge Color',
                'description' => 'Discount percentage badges (text color on yellow background)',
                'default' => '#92400e',
                'group' => 'Status Colors'
            ),
            'yolo_ys_color_danger' => array(
                'label' => 'Obligatory Extras Color',
                'description' => 'Obligatory extras section heading, Required indicator color',
                'default' => '#dc2626',
                'group' => 'Status Colors'
            ),
            
            // Text Colors
            'yolo_ys_color_text_dark' => array(
                'label' => 'Headings Text Color',
                'description' => 'Yacht name, Section headings, Price week labels, Form labels',
                'default' => '#1f2937',
                'group' => 'Text Colors'
            ),
            'yolo_ys_color_text_medium' => array(
                'label' => 'Body Text Color',
                'description' => 'Description text, Equipment names, Extra item names',
                'default' => '#4b5563',
                'group' => 'Text Colors'
            ),
            'yolo_ys_color_text_light' => array(
                'label' => 'Secondary Text Color',
                'description' => 'Location text, Spec labels, Form hints, Price units',
                'default' => '#6b7280',
                'group' => 'Text Colors'
            ),
            
            // UI Colors
            'yolo_ys_color_border' => array(
                'label' => 'Border Color',
                'description' => 'Card borders, Section dividers, Form field borders',
                'default' => '#e5e7eb',
                'group' => 'UI Colors'
            ),
            'yolo_ys_color_bg_light' => array(
                'label' => 'Light Background',
                'description' => 'Cards background, Quote form background, Spec items background',
                'default' => '#f9fafb',
                'group' => 'UI Colors'
            ),
        );
    }
    
    /**
     * Add submenu page under YOLO Yacht Search
     */
    public function add_submenu_page() {
        add_submenu_page(
            'yolo-yacht-search',          // Parent slug
            'Color Settings',              // Page title
            'Colors',                       // Menu title
            'manage_options',               // Capability
            'yolo-yacht-colors',            // Menu slug
            array($this, 'render_page')     // Callback
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        foreach ($this->color_settings as $option_name => $config) {
            register_setting('yolo_ys_colors', $option_name, array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_hex_color',
                'default' => $config['default']
            ));
        }
    }
    
    /**
     * Enqueue WordPress color picker
     */
    public function enqueue_color_picker($hook) {
        if ($hook !== 'yolo-yacht-search_page_yolo-yacht-colors') {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        
        // Initialize color pickers
        add_action('admin_footer', function() {
            ?>
            <script>
            jQuery(document).ready(function($) {
                $('.yolo-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        // Live preview could be added here
                    }
                });
            });
            </script>
            <?php
        });
    }
    
    /**
     * Render the settings page
     */
    public function render_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Save settings message
        if (isset($_GET['settings-updated'])) {
            add_settings_error('yolo_ys_colors', 'settings_updated', 'Color settings saved successfully!', 'updated');
        }
        
        // Group settings by category
        $grouped_settings = array();
        foreach ($this->color_settings as $option_name => $config) {
            $group = $config['group'];
            if (!isset($grouped_settings[$group])) {
                $grouped_settings[$group] = array();
            }
            $grouped_settings[$group][$option_name] = $config;
        }
        
        ?>
        <div class="wrap">
            <h1>
                <span class="dashicons dashicons-admin-appearance" style="font-size: 30px; margin-right: 10px;"></span>
                YOLO Yacht Search - Color Settings
            </h1>
            
            <p style="font-size: 14px; color: #666; max-width: 800px;">
                Customize the colors used throughout the yacht details page. Each color includes a description 
                of where it's used so you know exactly what you're changing.
            </p>
            
            <?php settings_errors('yolo_ys_colors'); ?>
            
            <form method="post" action="options.php">
                <?php settings_fields('yolo_ys_colors'); ?>
                
                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
                    <?php foreach ($grouped_settings as $group_name => $settings): ?>
                        <div style="background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                            <h2 style="margin-top: 0; padding-bottom: 10px; border-bottom: 2px solid #1e3a8a; color: #1e3a8a;">
                                <?php echo esc_html($group_name); ?>
                            </h2>
                            
                            <table class="form-table" style="margin: 0;">
                                <tbody>
                                    <?php foreach ($settings as $option_name => $config): ?>
                                        <tr>
                                            <td style="padding: 15px 0; border-bottom: 1px solid #eee;">
                                                <div style="display: flex; align-items: flex-start; gap: 15px;">
                                                    <div style="flex-shrink: 0;">
                                                        <input type="text" 
                                                               name="<?php echo esc_attr($option_name); ?>" 
                                                               value="<?php echo esc_attr(get_option($option_name, $config['default'])); ?>" 
                                                               class="yolo-color-picker"
                                                               data-default-color="<?php echo esc_attr($config['default']); ?>"
                                                        />
                                                    </div>
                                                    <div style="flex: 1;">
                                                        <strong style="font-size: 14px; color: #1e3a8a;">
                                                            <?php echo esc_html($config['label']); ?>
                                                        </strong>
                                                        <p style="margin: 5px 0 0; font-size: 12px; color: #666; line-height: 1.5;">
                                                            <?php echo esc_html($config['description']); ?>
                                                        </p>
                                                        <code style="font-size: 11px; background: #f0f0f1; padding: 2px 6px; border-radius: 3px; margin-top: 5px; display: inline-block;">
                                                            Default: <?php echo esc_html($config['default']); ?>
                                                        </code>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 30px; padding: 20px; background: #f0f0f1; border-radius: 4px;">
                    <div style="display: flex; gap: 15px; align-items: center;">
                        <?php submit_button('Save Colors', 'primary', 'submit', false); ?>
                        
                        <button type="button" class="button" onclick="resetToDefaults()">
                            Reset to Defaults
                        </button>
                        
                        <span style="color: #666; font-size: 13px;">
                            💡 Tip: Changes will apply to all yacht detail pages immediately after saving.
                        </span>
                    </div>
                </div>
            </form>
            
            <!-- Color Preview Section -->
            <div style="margin-top: 40px; background: white; border: 1px solid #ccd0d4; border-radius: 4px; padding: 20px;">
                <h2 style="margin-top: 0; color: #1e3a8a;">
                    <span class="dashicons dashicons-visibility"></span> 
                    Live Preview
                </h2>
                <p style="color: #666;">Here's how some of your color choices will look:</p>
                
                <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
                    <!-- Book Now Button Preview -->
                    <div style="text-align: center;">
                        <p style="margin: 0 0 10px; font-weight: bold;">Book Now Button</p>
                        <button style="
                            background: <?php echo esc_attr(get_option('yolo_ys_color_secondary', '#b91c1c')); ?>;
                            color: white;
                            padding: 15px 30px;
                            border: none;
                            border-radius: 6px;
                            font-weight: bold;
                            cursor: pointer;
                        ">BOOK NOW</button>
                    </div>
                    
                    <!-- Request Quote Button Preview -->
                    <div style="text-align: center;">
                        <p style="margin: 0 0 10px; font-weight: bold;">Request Quote Button</p>
                        <button style="
                            background: <?php echo esc_attr(get_option('yolo_ys_color_primary', '#1e3a8a')); ?>;
                            color: white;
                            padding: 12px 24px;
                            border: none;
                            border-radius: 6px;
                            font-weight: 600;
                            cursor: pointer;
                        ">REQUEST A QUOTE</button>
                    </div>
                    
                    <!-- Price Preview -->
                    <div style="text-align: center;">
                        <p style="margin: 0 0 10px; font-weight: bold;">Price Display</p>
                        <span style="
                            font-size: 24px;
                            font-weight: bold;
                            color: <?php echo esc_attr(get_option('yolo_ys_color_success', '#059669')); ?>;
                        ">€2,925.00</span>
                    </div>
                    
                    <!-- Heading Preview -->
                    <div style="text-align: center;">
                        <p style="margin: 0 0 10px; font-weight: bold;">Section Heading</p>
                        <h3 style="
                            margin: 0;
                            padding-bottom: 8px;
                            border-bottom: 3px solid <?php echo esc_attr(get_option('yolo_ys_color_primary', '#1e3a8a')); ?>;
                            color: <?php echo esc_attr(get_option('yolo_ys_color_text_dark', '#1f2937')); ?>;
                        ">Equipment</h3>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        function resetToDefaults() {
            if (confirm('Are you sure you want to reset all colors to their default values?')) {
                jQuery('.yolo-color-picker').each(function() {
                    var defaultColor = jQuery(this).data('default-color');
                    jQuery(this).val(defaultColor);
                    jQuery(this).wpColorPicker('color', defaultColor);
                });
            }
        }
        </script>
        <?php
    }
}

