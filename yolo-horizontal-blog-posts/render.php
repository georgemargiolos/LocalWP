<?php
/**
 * Server-side rendering for YOLO Horizontal Blog Posts Block
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Get post count from block attributes
$post_count = isset($attributes['postCount']) ? absint($attributes['postCount']) : 10;

// Query blog posts
$args = array(
    'post_type' => 'post',
    'posts_per_page' => $post_count,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
);

$query = new WP_Query($args);

if (!$query->have_posts()) {
    return '<p style="text-align: center; padding: 20px;">No blog posts found.</p>';
}

// Start output
?>
<div class="yolo-horizontal-blog-posts-container">
    <?php while ($query->have_posts()) : $query->the_post(); ?>
        <div class="yolo-horizontal-blog-post-card">
            <div class="yolo-horizontal-blog-post-image">
                <?php if (has_post_thumbnail()) : ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php the_post_thumbnail('medium_large', array('class' => 'yolo-blog-post-img')); ?>
                    </a>
                <?php else : ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <div class="yolo-blog-post-no-image">
                            <span>No Image</span>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="yolo-horizontal-blog-post-content">
                <h2 class="yolo-horizontal-blog-post-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php echo esc_html(get_the_title()); ?>
                    </a>
                </h2>
                
                <div class="yolo-horizontal-blog-post-excerpt">
                    <?php 
                    // Get the full content (not excerpt which is limited by WordPress)
                    $content = get_the_content();
                    $content = strip_tags($content);
                    $content = strip_shortcodes($content);
                    $content = wp_strip_all_tags($content);
                    
                    // Limit to maximum 500 words to fill the space
                    $words = explode(' ', $content);
                    if (count($words) > 500) {
                        $words = array_slice($words, 0, 500);
                        $content = implode(' ', $words);
                    }
                    
                    // Output the content
                    echo esc_html($content);
                    
                    // Always add Read more... link
                    echo ' <a href="' . esc_url(get_permalink()) . '" class="yolo-horizontal-blog-read-more-link">Read more...</a>';
                    ?>
                </div>
                
                <div class="yolo-horizontal-blog-post-button-wrapper">
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="yolo-horizontal-blog-post-button">
                        Read More
                    </a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>
<?php

// Reset post data
wp_reset_postdata();
