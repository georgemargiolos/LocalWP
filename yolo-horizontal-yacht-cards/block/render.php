<?php
if (!defined('ABSPATH')) exit;

// Check if YOLO plugin is active
if (!class_exists('YOLO_YS_Database')) {
    echo '<div style="padding:20px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;margin:20px 0;"><strong>⚠️ YOLO Horizontal Yacht Cards:</strong> The YOLO Yacht Search & Booking plugin must be installed and activated.</div>';
    return;
}

$db = new YOLO_YS_Database();
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);
$yachts = $db->get_all_yachts($yolo_company_id);

if (empty($yachts)) {
    echo '<div style="padding:40px;text-align:center;background:#f5f5f5;border-radius:8px;"><p>No YOLO yachts available.</p></div>';
    return;
}

$details_page_id = get_option('yolo_ys_yacht_details_page', '');
?>
<div class="yolo-hyc-container">
<?php foreach ($yachts as $yacht): 
    $images = !empty($yacht->images) ? $yacht->images : array();
    $details_url = $details_page_id ? add_query_arg('yacht_id', $yacht->id, get_permalink($details_page_id)) : '#';
    $length_ft = $yacht->length ? round($yacht->length * 3.28084) : 0;
    $min_price = class_exists('YOLO_YS_Database_Prices') ? YOLO_YS_Database_Prices::get_min_price($yacht->id) : null;
    
    // Prepare description with read more
    $full_description = wp_strip_all_tags($yacht->description);
    $description_words = str_word_count($full_description, 1);
    $word_count = count($description_words);
    $trimmed_description = wp_trim_words($full_description, 100, '');
    $needs_read_more = $word_count > 100;
?>
<div class="yolo-hyc-card">
    <div class="yolo-hyc-images">
        <?php if (!empty($images)): ?>
        <div class="swiper yolo-hyc-swiper-<?php echo esc_attr($yacht->id); ?>">
            <div class="swiper-wrapper">
                <?php foreach ($images as $image): ?>
                <div class="swiper-slide"><img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>"></div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
            <div class="swiper-pagination"></div>
        </div>
        <?php else: ?>
        <div class="yolo-hyc-placeholder">⛵</div>
        <?php endif; ?>
        <img src="https://yolo-charters.com/wp-content/uploads/2025/11/logo-for-YOLO-charters.png" alt="YOLO Charters" class="yolo-hyc-logo">
    </div>
    <div class="yolo-hyc-content">
        <h2 class="yolo-hyc-name">
            <?php echo esc_html($yacht->name); ?>
            <?php if (!empty($yacht->home_base)): ?>
                <span class="yolo-hyc-separator">|</span>
                <a href="<?php echo esc_url($details_url . '#yacht-map-section'); ?>" class="yolo-hyc-location">📍 <?php echo esc_html($yacht->home_base); ?></a>
            <?php endif; ?>
        </h2>
        <h4 class="yolo-hyc-model"><?php echo esc_html($yacht->model); ?></h4>
        <?php if (!empty($yacht->description)): ?>
        <p class="yolo-hyc-desc">
            <?php echo esc_html($trimmed_description); ?>
            <?php if ($needs_read_more): ?>
                <a href="<?php echo esc_url($details_url); ?>" class="yolo-hyc-read-more">Read more...</a>
            <?php endif; ?>
        </p>
        <?php endif; ?>
        <div class="yolo-hyc-specs">
            <div class="yolo-hyc-spec"><strong><?php echo esc_html($yacht->cabins ?: '-'); ?></strong><span>Cabins</span></div>
            <div class="yolo-hyc-spec"><strong><?php echo esc_html($yacht->wc ?? '-'); ?></strong><span>WC</span></div>
            <div class="yolo-hyc-spec">
                <strong><?php echo esc_html($yacht->year_of_build ?: '-'); ?></strong>
                <span>Year<?php if (!empty($yacht->year_of_refit)): ?> / Refit <?php echo esc_html($yacht->year_of_refit); ?><?php endif; ?></span>
            </div>
            <div class="yolo-hyc-spec"><strong><?php echo esc_html($length_ft); ?>ft</strong><span>Length</span></div>
        </div>
        <div class="yolo-hyc-footer">
            <?php if ($min_price && isset($min_price->min_price)): ?>
            <div class="yolo-hyc-price">From <strong><?php echo number_format($min_price->min_price, 0); ?> <?php echo esc_html($min_price->currency); ?></strong>/week</div>
            <?php endif; ?>
            <a href="<?php echo esc_url($details_url); ?>" class="yolo-hyc-btn">DETAILS</a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach ($yachts as $yacht): ?>
    if (typeof Swiper !== 'undefined') {
        new Swiper('.yolo-hyc-swiper-<?php echo esc_js($yacht->id); ?>', {
            loop: true,
            slidesPerView: 1,
            spaceBetween: 0,
            autoplay: { 
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            pagination: { 
                el: '.yolo-hyc-swiper-<?php echo esc_js($yacht->id); ?> .swiper-pagination', 
                clickable: true,
                dynamicBullets: true
            },
            navigation: { 
                nextEl: '.yolo-hyc-swiper-<?php echo esc_js($yacht->id); ?> .swiper-button-next', 
                prevEl: '.yolo-hyc-swiper-<?php echo esc_js($yacht->id); ?> .swiper-button-prev'
            },
            touchEventsTarget: 'container',
            touchRatio: 1,
            touchAngle: 45,
            grabCursor: true,
            autoHeight: false,
            observer: true,
            observeParents: true
        });
    }
    <?php endforeach; ?>
});
</script>
