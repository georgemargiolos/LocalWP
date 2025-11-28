<?php
/**
 * Yacht Details Template
 * Displays complete yacht information with image carousel
 */

// Get yacht ID from URL
$yacht_id = isset($_GET['yacht_id']) ? sanitize_text_field($_GET['yacht_id']) : '';

if (empty($yacht_id)) {
    echo '<div class="yolo-ys-error"><p>No yacht selected. Please select a yacht from the fleet.</p></div>';
    return;
}

// Get yacht from database
global $wpdb;
$db = new YOLO_YS_Database();
$table_yachts = $wpdb->prefix . 'yolo_yachts';

$yacht = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table_yachts WHERE id = %s",
    $yacht_id
));

if (!$yacht) {
    echo '<div class="yolo-ys-error"><p>Yacht not found.</p></div>';
    return;
}

// Get images
$table_images = $wpdb->prefix . 'yolo_yacht_images';
$images = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_images WHERE yacht_id = %s ORDER BY sort_order ASC",
    $yacht_id
));

// Get products
$table_products = $wpdb->prefix . 'yolo_yacht_products';
$products = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_products WHERE yacht_id = %s",
    $yacht_id
));

// Get extras
$table_extras = $wpdb->prefix . 'yolo_yacht_extras';
$extras = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_extras WHERE yacht_id = %s",
    $yacht_id
));

// Get equipment
$table_equipment = $wpdb->prefix . 'yolo_yacht_equipment';
$equipment = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_equipment WHERE yacht_id = %s",
    $yacht_id
));

// Check if YOLO yacht
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);
$is_yolo = ($yacht->company_id == $yolo_company_id);

?>

<div class="yolo-ys-yacht-details">
    
    <!-- Back Button -->
    <div class="yolo-ys-back-button">
        <a href="javascript:history.back()" class="yolo-ys-btn-back">← Back to Fleet</a>
    </div>
    
    <!-- Header -->
    <div class="yolo-ys-details-header">
        <h1 class="yolo-ys-details-title">
            <?php echo esc_html($yacht->name); ?>
            <?php if ($is_yolo): ?>
                <span class="yolo-ys-yacht-badge-large">YOLO</span>
            <?php endif; ?>
        </h1>
        <p class="yolo-ys-details-subtitle"><?php echo esc_html($yacht->model); ?></p>
    </div>
    
    <!-- Image Carousel -->
    <?php if (!empty($images)): ?>
        <div class="yolo-ys-carousel-container">
            <div class="yolo-ys-carousel">
                <?php foreach ($images as $index => $image): ?>
                    <div class="yolo-ys-carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                        <img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($images) > 1): ?>
                <button class="yolo-ys-carousel-prev" onclick="yoloYsCarousel.prev()">‹</button>
                <button class="yolo-ys-carousel-next" onclick="yoloYsCarousel.next()">›</button>
                
                <div class="yolo-ys-carousel-dots">
                    <?php foreach ($images as $index => $image): ?>
                        <span class="yolo-ys-dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="yoloYsCarousel.goTo(<?php echo $index; ?>)"></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="yolo-ys-no-image">
            <div class="yolo-ys-placeholder-large">⛵</div>
        </div>
    <?php endif; ?>
    
    <!-- Main Content -->
    <div class="yolo-ys-details-content">
        
        <!-- Specifications -->
        <div class="yolo-ys-details-section">
            <h2>Specifications</h2>
            <div class="yolo-ys-specs-grid">
                <?php if ($yacht->year_of_build): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Year Built:</strong>
                        <span><?php echo esc_html($yacht->year_of_build); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->refit_year): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Refit Year:</strong>
                        <span><?php echo esc_html($yacht->refit_year); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->length): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Length:</strong>
                        <span><?php echo esc_html(number_format($yacht->length, 2)); ?> m</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->beam): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Beam:</strong>
                        <span><?php echo esc_html(number_format($yacht->beam, 2)); ?> m</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->draft): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Draft:</strong>
                        <span><?php echo esc_html(number_format($yacht->draft, 2)); ?> m</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->cabins): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Cabins:</strong>
                        <span><?php echo esc_html($yacht->cabins); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->wc): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>WC:</strong>
                        <span><?php echo esc_html($yacht->wc); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->berths): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Berths:</strong>
                        <span><?php echo esc_html($yacht->berths); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->max_people_on_board): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Max People:</strong>
                        <span><?php echo esc_html($yacht->max_people_on_board); ?></span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->engine_power): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Engine Power:</strong>
                        <span><?php echo esc_html($yacht->engine_power); ?> HP</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->fuel_capacity): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Fuel Capacity:</strong>
                        <span><?php echo esc_html($yacht->fuel_capacity); ?> L</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($yacht->water_capacity): ?>
                    <div class="yolo-ys-spec-item">
                        <strong>Water Capacity:</strong>
                        <span><?php echo esc_html($yacht->water_capacity); ?> L</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Description -->
        <?php if ($yacht->description): ?>
            <div class="yolo-ys-details-section">
                <h2>Description</h2>
                <div class="yolo-ys-description">
                    <?php echo nl2br(wp_kses_post($yacht->description)); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Charter Types -->
        <?php if (!empty($products)): ?>
            <div class="yolo-ys-details-section">
                <h2>Charter Types</h2>
                <div class="yolo-ys-products-list">
                    <?php foreach ($products as $product): ?>
                        <div class="yolo-ys-product-item">
                            <span class="yolo-ys-product-type"><?php echo esc_html($product->product_type); ?></span>
                            <?php if ($product->is_default): ?>
                                <span class="yolo-ys-default-badge">Default</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Equipment -->
        <?php if (!empty($equipment)): ?>
            <div class="yolo-ys-details-section">
                <h2>Equipment</h2>
                <div class="yolo-ys-equipment-grid">
                    <?php foreach ($equipment as $equip): ?>
                        <div class="yolo-ys-equipment-item">
                            ✓ <?php echo esc_html($equip->equipment_name); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Extras -->
        <?php if (!empty($extras)): ?>
            <div class="yolo-ys-details-section">
                <h2>Available Extras</h2>
                <div class="yolo-ys-extras-list">
                    <?php foreach ($extras as $extra): ?>
                        <div class="yolo-ys-extra-item">
                            <span class="yolo-ys-extra-name">
                                <?php echo esc_html($extra->name); ?>
                                <?php if ($extra->obligatory): ?>
                                    <span class="yolo-ys-obligatory">*Obligatory</span>
                                <?php endif; ?>
                            </span>
                            <?php if ($extra->price): ?>
                                <span class="yolo-ys-extra-price">
                                    <?php echo esc_html(number_format($extra->price, 0)); ?> <?php echo esc_html($extra->currency); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
.yolo-ys-yacht-details {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.yolo-ys-back-button {
    margin-bottom: 20px;
}

.yolo-ys-btn-back {
    display: inline-block;
    padding: 10px 20px;
    background: #f3f4f6;
    color: #374151;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.yolo-ys-btn-back:hover {
    background: #e5e7eb;
    color: #1f2937;
}

.yolo-ys-details-header {
    text-align: center;
    margin-bottom: 40px;
}

.yolo-ys-details-title {
    font-size: 42px;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0 0 10px 0;
}

.yolo-ys-yacht-badge-large {
    display: inline-block;
    background: #dc2626;
    color: white;
    padding: 8px 24px;
    border-radius: 25px;
    font-size: 18px;
    font-weight: 700;
    margin-left: 15px;
    vertical-align: middle;
}

.yolo-ys-details-subtitle {
    font-size: 24px;
    color: #6b7280;
    margin: 0;
}

/* Carousel */
.yolo-ys-carousel-container {
    position: relative;
    max-width: 100%;
    margin: 0 auto 40px;
    background: #000;
    border-radius: 12px;
    overflow: hidden;
}

.yolo-ys-carousel {
    position: relative;
    width: 100%;
    height: 600px;
}

.yolo-ys-carousel-slide {
    display: none;
    width: 100%;
    height: 100%;
}

.yolo-ys-carousel-slide.active {
    display: block;
}

.yolo-ys-carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.yolo-ys-carousel-prev,
.yolo-ys-carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    font-size: 48px;
    padding: 20px;
    cursor: pointer;
    transition: background 0.3s ease;
    z-index: 10;
}

.yolo-ys-carousel-prev:hover,
.yolo-ys-carousel-next:hover {
    background: rgba(0,0,0,0.8);
}

.yolo-ys-carousel-prev {
    left: 0;
}

.yolo-ys-carousel-next {
    right: 0;
}

.yolo-ys-carousel-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.yolo-ys-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: all 0.3s ease;
}

.yolo-ys-dot.active,
.yolo-ys-dot:hover {
    background: white;
    transform: scale(1.2);
}

.yolo-ys-no-image {
    background: #f3f4f6;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    margin-bottom: 40px;
}

.yolo-ys-placeholder-large {
    font-size: 120px;
    color: #9ca3af;
}

/* Content Sections */
.yolo-ys-details-content {
    background: white;
}

.yolo-ys-details-section {
    margin-bottom: 40px;
    padding: 30px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.yolo-ys-details-section h2 {
    font-size: 28px;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0 0 20px 0;
    padding-bottom: 15px;
    border-bottom: 2px solid #e5e7eb;
}

.yolo-ys-specs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.yolo-ys-spec-item {
    display: flex;
    justify-content: space-between;
    padding: 12px;
    background: #f9fafb;
    border-radius: 6px;
}

.yolo-ys-spec-item strong {
    color: #374151;
}

.yolo-ys-spec-item span {
    color: #1e3a8a;
    font-weight: 600;
}

.yolo-ys-description {
    font-size: 16px;
    line-height: 1.8;
    color: #374151;
}

.yolo-ys-products-list {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.yolo-ys-product-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 20px;
    background: #dbeafe;
    border-radius: 8px;
}

.yolo-ys-product-type {
    font-weight: 600;
    color: #1e3a8a;
}

.yolo-ys-default-badge {
    background: #10b981;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.yolo-ys-equipment-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 10px;
}

.yolo-ys-equipment-item {
    padding: 8px 12px;
    background: #f9fafb;
    border-radius: 6px;
    color: #374151;
    font-size: 14px;
}

.yolo-ys-extras-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.yolo-ys-extra-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    border-left: 4px solid #1e3a8a;
}

.yolo-ys-extra-name {
    font-weight: 600;
    color: #374151;
}

.yolo-ys-obligatory {
    color: #dc2626;
    font-size: 12px;
    margin-left: 8px;
}

.yolo-ys-extra-price {
    font-size: 18px;
    font-weight: 700;
    color: #10b981;
}

.yolo-ys-error {
    text-align: center;
    padding: 60px 20px;
    background: #fee2e2;
    border-radius: 8px;
    color: #dc2626;
}

@media (max-width: 768px) {
    .yolo-ys-details-title {
        font-size: 28px;
    }
    
    .yolo-ys-carousel {
        height: 300px;
    }
    
    .yolo-ys-specs-grid {
        grid-template-columns: 1fr;
    }
    
    .yolo-ys-equipment-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
var yoloYsCarousel = {
    currentSlide: 0,
    totalSlides: <?php echo count($images); ?>,
    
    showSlide: function(n) {
        var slides = document.querySelectorAll('.yolo-ys-carousel-slide');
        var dots = document.querySelectorAll('.yolo-ys-dot');
        
        if (n >= this.totalSlides) { this.currentSlide = 0; }
        if (n < 0) { this.currentSlide = this.totalSlides - 1; }
        
        slides.forEach(function(slide) {
            slide.classList.remove('active');
        });
        
        dots.forEach(function(dot) {
            dot.classList.remove('active');
        });
        
        if (slides[this.currentSlide]) {
            slides[this.currentSlide].classList.add('active');
        }
        
        if (dots[this.currentSlide]) {
            dots[this.currentSlide].classList.add('active');
        }
    },
    
    next: function() {
        this.currentSlide++;
        this.showSlide(this.currentSlide);
    },
    
    prev: function() {
        this.currentSlide--;
        this.showSlide(this.currentSlide);
    },
    
    goTo: function(n) {
        this.currentSlide = n;
        this.showSlide(this.currentSlide);
    }
};

// Auto-advance carousel every 5 seconds
setInterval(function() {
    if (yoloYsCarousel.totalSlides > 1) {
        yoloYsCarousel.next();
    }
}, 5000);
</script>
