<?php
/**
 * Server-side rendering for YOLO Horizontal Yacht Cards block
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get database instance
$db = new YOLO_YS_Database();

// Get YOLO company ID
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);

// Get all YOLO yachts
$yachts = $db->get_all_yachts($yolo_company_id);

if (empty($yachts)) {
    echo '<div class="yolo-ys-no-yachts"><p>No YOLO yachts available.</p></div>';
    return;
}

// Get details page URL
$details_page_id = get_option('yolo_ys_yacht_details_page', '');

?>

<div class="yolo-ys-horizontal-cards-container">
    <?php foreach ($yachts as $yacht): 
        // Get all images for this yacht
        $images = !empty($yacht->images) ? $yacht->images : array();
        
        // Get details URL
        $details_url = $details_page_id ? add_query_arg('yacht_id', $yacht->id, get_permalink($details_page_id)) : '#';
        
        // Convert meters to feet
        $length_ft = $yacht->length ? round($yacht->length * 3.28084) : 0;
        
        // Format refit display
        $refit_display = $yacht->refit_year ? '<strong>Refit: ' . $yacht->refit_year . '</strong>' : '—';
        
        // Get minimum price
        $min_price = YOLO_YS_Database_Prices::get_min_price($yacht->id);
    ?>
    
    <div class="yolo-ys-horizontal-card">
        <!-- Image Carousel (Left Side) -->
        <div class="yolo-ys-horizontal-card-images">
            <?php if (!empty($images)): ?>
                <div class="swiper yolo-yacht-swiper-<?php echo esc_attr($yacht->id); ?>">
                    <div class="swiper-wrapper">
                        <?php foreach ($images as $image): ?>
                            <div class="swiper-slide">
                                <img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Navigation buttons -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                    <!-- Pagination -->
                    <div class="swiper-pagination"></div>
                </div>
            <?php else: ?>
                <div class="yolo-ys-yacht-placeholder">⛵</div>
            <?php endif; ?>
            
            <!-- YOLO FLEET Badge -->
            <div class="yolo-ys-fleet-badge-overlay">YOLO FLEET</div>
        </div>
        
        <!-- Content (Right Side) -->
        <div class="yolo-ys-horizontal-card-content">
            <!-- Location -->
            <?php if ($yacht->home_base): ?>
                <div class="yolo-ys-yacht-location">
                    📍 <?php echo esc_html($yacht->home_base); ?>
                </div>
            <?php endif; ?>
            
            <!-- Name and Model -->
            <div class="yolo-ys-yacht-header">
                <h3 class="yolo-ys-yacht-name"><?php echo esc_html($yacht->name); ?></h3>
                <h4 class="yolo-ys-yacht-model"><?php echo esc_html($yacht->model); ?></h4>
            </div>
            
            <!-- Description -->
            <?php if (!empty($yacht->description)): ?>
                <div class="yolo-ys-yacht-description">
                    <?php echo wp_kses_post(wp_trim_words($yacht->description, 30, '...')); ?>
                </div>
            <?php endif; ?>
            
            <!-- Specs Grid -->
            <div class="yolo-ys-yacht-specs-horizontal">
                <div class="yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->cabins); ?></div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('cabins', 'CABINS'); ?></div>
                </div>
                
                <div class="yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->wc ?? 'N/A'); ?></div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('wc', 'WC'); ?></div>
                </div>
                
                <div class="yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->year_of_build); ?></div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('year_built', 'YEAR BUILT'); ?></div>
                </div>
                
                <div class="yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo $refit_display; ?></div>
                    <div class="yolo-ys-spec-label">REFIT</div>
                </div>
                
                <div class="yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($length_ft); ?> ft</div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('length', 'LENGTH'); ?></div>
                </div>
            </div>
            
            <!-- Price and Button Row -->
            <div class="yolo-ys-horizontal-card-footer">
                <!-- Price -->
                <?php if ($min_price && isset($min_price->min_price)): ?>
                <div class="yolo-ys-yacht-price">
                    <?php yolo_ys_text_e('from_price', 'From'); ?> <strong><?php echo number_format($min_price->min_price, 2, ',', '.'); ?> <?php echo esc_html($min_price->currency); ?></strong> <?php yolo_ys_text_e('per_week', 'per week'); ?>
                </div>
                <?php endif; ?>
                
                <!-- Details Button -->
                <a href="<?php echo esc_url($details_url); ?>" class="yolo-ys-details-btn">
                    <?php yolo_ys_text_e('details_button', 'DETAILS'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <?php endforeach; ?>
</div>

<script>
// Initialize Swiper for each yacht card
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($yachts as $yacht): ?>
    new Swiper('.yolo-yacht-swiper-<?php echo esc_js($yacht->id); ?>', {
        loop: true,
        // autoplay disabled - manual navigation only (v70.5)
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
    });
    <?php endforeach; ?>
});
</script>
