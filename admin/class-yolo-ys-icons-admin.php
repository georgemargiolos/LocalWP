<?php
/**
 * Icons Management Admin Page
 *
 * Provides a comprehensive admin interface for managing FontAwesome icons
 * throughout the yacht details page. Allows customization of icons for:
 * - Obligatory Extras (default: red exclamation)
 * - Optional Extras (default: blue plus)
 * - Equipment (default: green check)
 * - Section Headers (Cancellation Policy, Security Deposit, etc.)
 *
 * Features:
 * - Live preview of icon changes
 * - Custom CSS styling per icon
 * - Database-backed persistence
 * - AJAX save/delete operations
 * - Fallback to sensible defaults
 *
 * @package    YOLO_Yacht_Search
 * @subpackage Admin
 * @since      2.7.8
 * @author     George Margiolos
 */

class YOLO_YS_Icons_Admin {
    
    /**
     * Register admin menu
     *
     * Adds "Icons" submenu under YOLO Yacht Search settings.
     * Requires 'manage_options' capability (admin only).
     *
     * @since 2.7.8
     * @return void
     */
    public function register_menu() {
        add_submenu_page(
            'yolo-yacht-search',  // FIXED: Changed from 'yolo-ys-settings' to match actual parent menu
            'Feature Icons',
            'Icons',
            'manage_options',
            'yolo-ys-icons',
            array($this, 'render_page')
        );
    }
    
    /**
     * Handle AJAX save icon request
     *
     * Saves or updates an icon mapping in the database.
     * Validates nonce and user permissions before processing.
     *
     * @since 2.7.8
     * @return void Sends JSON response
     */
    public function ajax_save_icon() {
        check_ajax_referer('yolo_icons_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'yolo_feature_icons';
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $feature_type = sanitize_text_field($_POST['feature_type']);
        $feature_name = sanitize_text_field($_POST['feature_name']);
        $icon_class = sanitize_text_field($_POST['icon_class']);
        $icon_style = sanitize_text_field($_POST['icon_style']);
        
        if ($id > 0) {
            // Update existing
            $result = $wpdb->update(
                $table,
                array(
                    'icon_class' => $icon_class,
                    'icon_style' => $icon_style
                ),
                array('id' => $id)
            );
        } else {
            // Insert new
            $result = $wpdb->insert(
                $table,
                array(
                    'feature_type' => $feature_type,
                    'feature_name' => $feature_name,
                    'icon_class' => $icon_class,
                    'icon_style' => $icon_style
                )
            );
        }
        
        if ($result !== false) {
            wp_send_json_success('Icon saved successfully');
        } else {
            wp_send_json_error('Failed to save icon');
        }
    }
    
    /**
     * Handle AJAX delete icon request
     *
     * Removes an icon mapping from the database (resets to default).
     * Validates nonce and user permissions before processing.
     *
     * @since 2.7.8
     * @return void Sends JSON response
     */
    public function ajax_delete_icon() {
        check_ajax_referer('yolo_icons_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'yolo_feature_icons';
        
        $id = intval($_POST['id']);
        
        $result = $wpdb->delete($table, array('id' => $id));
        
        if ($result !== false) {
            wp_send_json_success('Icon deleted successfully');
        } else {
            wp_send_json_error('Failed to delete icon');
        }
    }
    
    /**
     * Render admin page
     *
     * Displays the icons management interface with:
     * - All unique extras from database
     * - All unique equipment from database
     * - Predefined sections (Cancellation Policy, etc.)
     * - Live preview functionality
     * - Save/Reset buttons
     *
     * @since 2.7.8
     * @return void Outputs HTML
     */
    public function render_page() {
        global $wpdb;
        
        // Get all unique features from database
        $extras_table = $wpdb->prefix . 'yolo_yacht_extras';
        $equipment_table = $wpdb->prefix . 'yolo_yacht_equipment';
        $icons_table = $wpdb->prefix . 'yolo_feature_icons';
        
        // Get all extras
        $extras = $wpdb->get_results("
            SELECT DISTINCT name, obligatory 
            FROM {$extras_table} 
            ORDER BY name
        ");
        
        // Get all equipment from equipment-icons.php mapping
        require_once YOLO_YS_PLUGIN_DIR . 'includes/equipment-icons.php';
        
        // Extract equipment names from the icon mapping function
        $equipment_names = array(
            'Autopilot', 'Cockpit speakers', 'DVD player', 'Dinghy', 'Chart plotter',
            'Electric winches', 'Bimini', 'Flybridge', 'Bow thruster', 'Radar',
            'Heating', 'Generator', 'Lazy jack', 'Radio-CD player', 'Sprayhood',
            'Chart plotter in cockpit', 'Outboard engine', 'Teak deck', 'Wi-Fi & Internet',
            'Game console', 'Air conditioning', 'Dishwasher', 'Washing machine', 'Microwave',
            'Oven', 'Freezer', 'Ice maker', 'Coffee machine', 'Watermaker', 'Solar panels'
        );
        
        $equipment = array();
        foreach ($equipment_names as $name) {
            $equipment[] = (object) array('name' => $name);
        }
        
        // Get existing icon mappings
        $icon_mappings = $wpdb->get_results("
            SELECT * FROM {$icons_table} 
            ORDER BY feature_type, feature_name
        ", OBJECT_K);
        
        // Convert to associative array for easy lookup
        $icons_map = array();
        foreach ($icon_mappings as $mapping) {
            $key = $mapping->feature_type . '|' . $mapping->feature_name;
            $icons_map[$key] = $mapping;
        }
        
        // Define sections
        $sections = array(
            array('type' => 'section', 'name' => 'Cancellation Policy', 'label' => 'Cancellation Policy Section'),
            array('type' => 'section', 'name' => 'Security Deposit', 'label' => 'Security Deposit Section'),
            array('type' => 'section', 'name' => 'Check-in/Check-out', 'label' => 'Check-in/Check-out Section'),
            array('type' => 'section', 'name' => 'Technical Characteristics', 'label' => 'Technical Characteristics Section'),
        );
        
        ?>
        <div class="wrap">
            <h1>Feature Icons Management</h1>
            <p>Customize FontAwesome icons for yacht features, extras, equipment, and sections.</p>
            
            <div class="yolo-icons-container">
                <style>
                    .yolo-icons-container {
                        background: white;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
                    }
                    .icons-section {
                        margin-bottom: 40px;
                    }
                    .icons-section h2 {
                        font-size: 20px;
                        margin-bottom: 15px;
                        padding-bottom: 10px;
                        border-bottom: 2px solid #1e3a8a;
                    }
                    .icons-table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .icons-table th {
                        background: #f3f4f6;
                        padding: 12px;
                        text-align: left;
                        font-weight: 600;
                        border-bottom: 2px solid #e5e7eb;
                    }
                    .icons-table td {
                        padding: 10px 12px;
                        border-bottom: 1px solid #e5e7eb;
                    }
                    .icons-table tr:hover {
                        background: #f9fafb;
                    }
                    .icon-preview {
                        font-size: 24px;
                        width: 40px;
                        text-align: center;
                    }
                    .icon-input {
                        width: 100%;
                        padding: 6px 10px;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                    }
                    .btn-save-icon {
                        background: #1e3a8a;
                        color: white;
                        padding: 6px 16px;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 13px;
                    }
                    .btn-save-icon:hover {
                        background: #1e40af;
                    }
                    .btn-delete-icon {
                        background: #dc2626;
                        color: white;
                        padding: 6px 12px;
                        border: none;
                        border-radius: 4px;
                        cursor: pointer;
                        font-size: 13px;
                        margin-left: 5px;
                    }
                    .btn-delete-icon:hover {
                        background: #b91c1c;
                    }
                    .icon-hint {
                        font-size: 12px;
                        color: #6b7280;
                        margin-top: 4px;
                    }
                </style>
                
                <!-- Sections -->
                <div class="icons-section">
                    <h2>📋 Sections</h2>
                    <table class="icons-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Icon</th>
                                <th style="width: 200px;">Section</th>
                                <th>Icon Class</th>
                                <th style="width: 150px;">Style</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sections as $section): 
                                $key = $section['type'] . '|' . $section['name'];
                                $mapping = isset($icons_map[$key]) ? $icons_map[$key] : null;
                                $icon_class = $mapping ? $mapping->icon_class : 'fa-solid fa-circle';
                                $icon_style = $mapping ? $mapping->icon_style : '';
                                $id = $mapping ? $mapping->id : 0;
                            ?>
                            <tr data-id="<?php echo $id; ?>" data-type="<?php echo esc_attr($section['type']); ?>" data-name="<?php echo esc_attr($section['name']); ?>">
                                <td class="icon-preview">
                                    <i class="<?php echo esc_attr($icon_class); ?>" style="<?php echo esc_attr($icon_style); ?>"></i>
                                </td>
                                <td><strong><?php echo esc_html($section['label']); ?></strong></td>
                                <td>
                                    <input type="text" class="icon-input icon-class-input" value="<?php echo esc_attr($icon_class); ?>" placeholder="fa-solid fa-circle">
                                    <div class="icon-hint">Example: fa-solid fa-ban, fa-regular fa-calendar</div>
                                </td>
                                <td>
                                    <input type="text" class="icon-input icon-style-input" value="<?php echo esc_attr($icon_style); ?>" placeholder="color: #dc2626;">
                                </td>
                                <td>
                                    <button class="btn-save-icon">Save</button>
                                    <?php if ($id > 0): ?>
                                    <button class="btn-delete-icon">Reset</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Obligatory Extras -->
                <div class="icons-section">
                    <h2>⚠️ Obligatory Extras</h2>
                    <table class="icons-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Icon</th>
                                <th style="width: 200px;">Extra Name</th>
                                <th>Icon Class</th>
                                <th style="width: 150px;">Style</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($extras as $extra): 
                                if ($extra->obligatory != 1) continue;
                                $key = 'extra_obligatory|' . $extra->name;
                                $mapping = isset($icons_map[$key]) ? $icons_map[$key] : null;
                                $icon_class = $mapping ? $mapping->icon_class : 'fa-solid fa-circle-exclamation';
                                $icon_style = $mapping ? $mapping->icon_style : 'color: #dc2626;';
                                $id = $mapping ? $mapping->id : 0;
                            ?>
                            <tr data-id="<?php echo $id; ?>" data-type="extra_obligatory" data-name="<?php echo esc_attr($extra->name); ?>">
                                <td class="icon-preview">
                                    <i class="<?php echo esc_attr($icon_class); ?>" style="<?php echo esc_attr($icon_style); ?>"></i>
                                </td>
                                <td><strong><?php echo esc_html($extra->name); ?></strong></td>
                                <td>
                                    <input type="text" class="icon-input icon-class-input" value="<?php echo esc_attr($icon_class); ?>" placeholder="fa-solid fa-circle-exclamation">
                                </td>
                                <td>
                                    <input type="text" class="icon-input icon-style-input" value="<?php echo esc_attr($icon_style); ?>" placeholder="color: #dc2626;">
                                </td>
                                <td>
                                    <button class="btn-save-icon">Save</button>
                                    <?php if ($id > 0): ?>
                                    <button class="btn-delete-icon">Reset</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Optional Extras -->
                <div class="icons-section">
                    <h2>✨ Optional Extras</h2>
                    <table class="icons-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Icon</th>
                                <th style="width: 200px;">Extra Name</th>
                                <th>Icon Class</th>
                                <th style="width: 150px;">Style</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($extras as $extra): 
                                if ($extra->obligatory != 0) continue;
                                $key = 'extra_optional|' . $extra->name;
                                $mapping = isset($icons_map[$key]) ? $icons_map[$key] : null;
                                $icon_class = $mapping ? $mapping->icon_class : 'fa-solid fa-plus-circle';
                                $icon_style = $mapping ? $mapping->icon_style : 'color: #1e3a8a;';
                                $id = $mapping ? $mapping->id : 0;
                            ?>
                            <tr data-id="<?php echo $id; ?>" data-type="extra_optional" data-name="<?php echo esc_attr($extra->name); ?>">
                                <td class="icon-preview">
                                    <i class="<?php echo esc_attr($icon_class); ?>" style="<?php echo esc_attr($icon_style); ?>"></i>
                                </td>
                                <td><strong><?php echo esc_html($extra->name); ?></strong></td>
                                <td>
                                    <input type="text" class="icon-input icon-class-input" value="<?php echo esc_attr($icon_class); ?>" placeholder="fa-solid fa-plus-circle">
                                </td>
                                <td>
                                    <input type="text" class="icon-input icon-style-input" value="<?php echo esc_attr($icon_style); ?>" placeholder="color: #1e3a8a;">
                                </td>
                                <td>
                                    <button class="btn-save-icon">Save</button>
                                    <?php if ($id > 0): ?>
                                    <button class="btn-delete-icon">Reset</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Equipment -->
                <div class="icons-section">
                    <h2>⚙️ Equipment</h2>
                    <table class="icons-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Icon</th>
                                <th style="width: 200px;">Equipment Name</th>
                                <th>Icon Class</th>
                                <th style="width: 150px;">Style</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipment as $equip): 
                                $key = 'equipment|' . $equip->name;
                                $mapping = isset($icons_map[$key]) ? $icons_map[$key] : null;
                                $icon_class = $mapping ? $mapping->icon_class : 'fa-solid fa-check';
                                $icon_style = $mapping ? $mapping->icon_style : 'color: #059669;';
                                $id = $mapping ? $mapping->id : 0;
                            ?>
                            <tr data-id="<?php echo $id; ?>" data-type="equipment" data-name="<?php echo esc_attr($equip->name); ?>">
                                <td class="icon-preview">
                                    <i class="<?php echo esc_attr($icon_class); ?>" style="<?php echo esc_attr($icon_style); ?>"></i>
                                </td>
                                <td><strong><?php echo esc_html($equip->name); ?></strong></td>
                                <td>
                                    <input type="text" class="icon-input icon-class-input" value="<?php echo esc_attr($icon_class); ?>" placeholder="fa-solid fa-check">
                                </td>
                                <td>
                                    <input type="text" class="icon-input icon-style-input" value="<?php echo esc_attr($icon_style); ?>" placeholder="color: #059669;">
                                </td>
                                <td>
                                    <button class="btn-save-icon">Save</button>
                                    <?php if ($id > 0): ?>
                                    <button class="btn-delete-icon">Reset</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Save icon
                $('.btn-save-icon').on('click', function() {
                    var $row = $(this).closest('tr');
                    var id = $row.data('id');
                    var type = $row.data('type');
                    var name = $row.data('name');
                    var iconClass = $row.find('.icon-class-input').val();
                    var iconStyle = $row.find('.icon-style-input').val();
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'yolo_save_icon',
                            nonce: '<?php echo wp_create_nonce('yolo_icons_nonce'); ?>',
                            id: id,
                            feature_type: type,
                            feature_name: name,
                            icon_class: iconClass,
                            icon_style: iconStyle
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Icon saved successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        }
                    });
                });
                
                // Delete icon
                $('.btn-delete-icon').on('click', function() {
                    if (!confirm('Reset this icon to default?')) return;
                    
                    var $row = $(this).closest('tr');
                    var id = $row.data('id');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'yolo_delete_icon',
                            nonce: '<?php echo wp_create_nonce('yolo_icons_nonce'); ?>',
                            id: id
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('Icon reset successfully!');
                                location.reload();
                            } else {
                                alert('Error: ' + response.data);
                            }
                        }
                    });
                });
                
                // Live preview
                $('.icon-class-input, .icon-style-input').on('input', function() {
                    var $row = $(this).closest('tr');
                    var iconClass = $row.find('.icon-class-input').val();
                    var iconStyle = $row.find('.icon-style-input').val();
                    var $icon = $row.find('.icon-preview i');
                    
                    $icon.attr('class', iconClass);
                    $icon.attr('style', iconStyle);
                });
            });
            </script>
        </div>
        <?php
    }
}
