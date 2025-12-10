<?php
/**
 * Plugin Name: YOLO Horizontal Yacht Cards
 * Plugin URI: https://yolo-charters.com
 * Description: Display YOLO company yachts in horizontal cards with image carousel. Requires YOLO Yacht Search & Booking plugin.
 * Version: 55.1
 * Author: George Margiolos
 * Author URI: https://yolo-charters.com
 * License: GPL v2 or later
 * Text Domain: yolo-horizontal-yacht-cards
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('YOLO_HYC_VERSION', '55.1');
define('YOLO_HYC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YOLO_HYC_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Check if YOLO Yacht Search plugin is active
 */
function yolo_hyc_check_dependencies() {
    return class_exists('YOLO_YS_Database');
}

/**
 * Admin notice for missing dependencies
 */
function yolo_hyc_dependency_notice() {
    if (!yolo_hyc_check_dependencies()) {
        echo '<div class="notice notice-error"><p><strong>YOLO Horizontal Yacht Cards:</strong> This plugin requires the <strong>YOLO Yacht Search & Booking</strong> plugin to be installed and activated.</p></div>';
    }
}
add_action('admin_notices', 'yolo_hyc_dependency_notice');

/**
 * Register the block
 */
function yolo_hyc_register_block() {
    register_block_type(YOLO_HYC_PLUGIN_DIR . 'block');
}
add_action('init', 'yolo_hyc_register_block');

/**
 * Enqueue Swiper - Use local version from YOLO Yacht Search plugin
 */
function yolo_hyc_enqueue_swiper() {
    if (has_block('yolo-hyc/horizontal-yacht-cards')) {
        // Check if YOLO Yacht Search plugin is active and has Swiper
        if (defined('YOLO_YS_PLUGIN_URL')) {
            // Use local Swiper from YOLO Yacht Search plugin
            wp_enqueue_style(
                'swiper',
                YOLO_YS_PLUGIN_URL . 'vendor/swiper/swiper-bundle.min.css',
                array(),
                '11.0.0'
            );
            
            wp_enqueue_script(
                'swiper',
                YOLO_YS_PLUGIN_URL . 'vendor/swiper/swiper-bundle.min.js',
                array(),
                '11.0.0',
                true
            );
        } else {
            // Fallback: Use CDN if main plugin not available
            wp_enqueue_style(
                'swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
                array(),
                '11.0.0'
            );
            
            wp_enqueue_script(
                'swiper',
                'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
                array(),
                '11.0.0',
                true
            );
        }
    }
}
add_action('wp_enqueue_scripts', 'yolo_hyc_enqueue_swiper');

/**
 * Enqueue scroll handler on yacht details pages
 */
function yolo_hyc_enqueue_scroll_handler() {
    // Only load on pages that might be yacht details
    if (is_singular() || is_page()) {
        wp_enqueue_script(
            'yolo-hyc-scroll-handler',
            YOLO_HYC_PLUGIN_URL . 'block/scroll-handler.js',
            array(),
            YOLO_HYC_VERSION,
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'yolo_hyc_enqueue_scroll_handler');
