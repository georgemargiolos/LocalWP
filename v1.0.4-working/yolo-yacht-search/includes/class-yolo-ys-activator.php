<?php
/**
 * Fired during plugin activation
 */
class YOLO_YS_Activator {
    
    /**
     * Activation tasks
     */
    public static function activate() {
        // Set default options with prefilled values
        $defaults = array(
            'yolo_ys_api_key' => '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe',
            'yolo_ys_my_company_id' => '7850',
            'yolo_ys_friend_companies' => '4366,3604,6711',
            'yolo_ys_cache_duration' => '24',
            'yolo_ys_currency' => 'EUR',
            'yolo_ys_results_page' => '',
            'yolo_ys_primary_color' => '#1e3a8a',
            'yolo_ys_accent_color' => '#dc2626',
            'yolo_ys_button_bg_color' => '#dc2626',
            'yolo_ys_button_text_color' => '#ffffff',
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
