<?php
/**
 * Admin settings page template
 */

// Check user capabilities
if (!current_user_can('manage_options')) {
    return;
}

// Save settings message
if (isset($_GET['settings-updated'])) {
    add_settings_error('yolo_ys_messages', 'yolo_ys_message', __('Settings Saved', 'yolo-yacht-search'), 'updated');
}

settings_errors('yolo_ys_messages');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <h2 class="nav-tab-wrapper">
        <a href="#api-settings" class="nav-tab nav-tab-active"><?php _e('API & Company', 'yolo-yacht-search'); ?></a>
        <a href="#general-settings" class="nav-tab"><?php _e('General', 'yolo-yacht-search'); ?></a>
        <a href="#styling-settings" class="nav-tab"><?php _e('Styling', 'yolo-yacht-search'); ?></a>
    </h2>
    
    <form action="options.php" method="post">
        <?php
        settings_fields('yolo-yacht-search');
        do_settings_sections('yolo-yacht-search');
        submit_button(__('Save Settings', 'yolo-yacht-search'));
        ?>
    </form>
    
    <div class="yolo-ys-info-box">
        <h3><?php _e('How to Use', 'yolo-yacht-search'); ?></h3>
        <ol>
            <li><?php _e('Create a page for search results and add the "YOLO Search Results" block to it', 'yolo-yacht-search'); ?></li>
            <li><?php _e('Select that page in the "Search Results Page" dropdown above', 'yolo-yacht-search'); ?></li>
            <li><?php _e('Add the "YOLO Search Widget" block to any page where you want the search form', 'yolo-yacht-search'); ?></li>
            <li><?php _e('Your YOLO boats (Company ID: 7850) will appear first, followed by friend companies', 'yolo-yacht-search'); ?></li>
        </ol>
        
        <h3><?php _e('Blocks Available', 'yolo-yacht-search'); ?></h3>
        <ul>
            <li><strong><?php _e('YOLO Search Widget', 'yolo-yacht-search'); ?></strong> - <?php _e('The search form (styled like yolo-charters.com)', 'yolo-yacht-search'); ?></li>
            <li><strong><?php _e('YOLO Search Results', 'yolo-yacht-search'); ?></strong> - <?php _e('Displays search results with YOLO boats prioritized', 'yolo-yacht-search'); ?></li>
        </ul>
    </div>
</div>

<style>
.yolo-ys-info-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-left: 4px solid #dc2626;
    padding: 20px;
    margin-top: 30px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
.yolo-ys-info-box h3 {
    margin-top: 0;
    color: #1e3a8a;
}
.yolo-ys-info-box ol, .yolo-ys-info-box ul {
    margin-left: 20px;
}
.yolo-ys-info-box li {
    margin-bottom: 8px;
}
</style>
