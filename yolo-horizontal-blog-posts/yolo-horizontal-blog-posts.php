<?php
/**
 * Plugin Name: YOLO Horizontal Blog Posts Block
 * Plugin URI: https://github.com/georgemargiolos/LocalWP
 * Description: Display blog posts in horizontal card layout with featured image on left and content on right
 * Version: 1.0.0
 * Author: George Margiolos
 * Author URI: https://github.com/georgemargiolos
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yolo-horizontal-blog-posts
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the block
 */
function yolo_horizontal_blog_posts_register_block() {
    // Register block type
    register_block_type(__DIR__, array(
        'render_callback' => 'yolo_horizontal_blog_posts_render'
    ));
}
add_action('init', 'yolo_horizontal_blog_posts_register_block');

/**
 * Render callback for the block
 */
function yolo_horizontal_blog_posts_render($attributes) {
    ob_start();
    include __DIR__ . '/render.php';
    return ob_get_clean();
}

/**
 * Enqueue editor script manually for reliability
 */
function yolo_horizontal_blog_posts_enqueue_editor_script() {
    wp_enqueue_script(
        'yolo-horizontal-blog-posts-editor',
        plugins_url('index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components'),
        filemtime(__DIR__ . '/index.js')
    );
}
add_action('enqueue_block_editor_assets', 'yolo_horizontal_blog_posts_enqueue_editor_script');

// Front page support removed - was blocking all other blocks
// The block will work on all pages by default through standard registration
