<?php
/**
 * Yacht Details Template v2
 * With Image Carousel and Weekly Price Carousel
 */

// Get yacht ID from URL
$yacht_id = isset($_GET['yacht_id']) ? sanitize_text_field($_GET['yacht_id']) : '';

if (empty($yacht_id)) {
    echo '<p>No yacht specified.</p>';
    return;
}

// Get yacht data
global $wpdb;
$yacht_table = $wpdb->prefix . 'yolo_yachts';
$yacht = $wpdb->get_row($wpdb->prepare("SELECT * FROM $yacht_table WHERE id = %s", $yacht_id));

if (!$yacht) {
    echo '<p>Yacht not found.</p>';
    return;
}

// Get images
$images_table = $wpdb->prefix . 'yolo_yacht_images';
$images = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $images_table WHERE yacht_id = %s ORDER BY sort_order ASC",
    $yacht_id
));

// Get prices
$prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);

// Get equipment
$equipment_table = $wpdb->prefix . 'yolo_yacht_equipment';
$equipment = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $equipment_table WHERE yacht_id = %s",
    $yacht_id
));

// Get extras
$extras_table = $wpdb->prefix . 'yolo_yacht_extras';
$extras = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $extras_table WHERE yacht_id = %s",
    $yacht_id
));

// Convert meters to feet
$length_ft = $yacht->length ? round($yacht->length * 3.28084) : 0;
$beam_ft = $yacht->beam ? round($yacht->beam * 3.28084, 1) : 0;
$draft_ft = $yacht->draft ? round($yacht->draft * 3.28084, 1) : 0;
?>

<div class="yolo-yacht-details-v2">
    
    <!-- Yacht Name Header -->
    <div class="yacht-header">
        <h1 class="yacht-name"><?php echo esc_html(strtoupper($yacht->name)); ?></h1>
        <h2 class="yacht-model"><?php echo esc_html($yacht->model); ?></h2>
        <?php if ($yacht->home_base): ?>
            <p class="yacht-location">📍 <?php echo esc_html($yacht->home_base); ?></p>
        <?php endif; ?>
    </div>
    
    <!-- Main Content Grid: Images + Price Carousel -->
    <div class="yacht-main-grid">
        
        <!-- Image Carousel -->
        <div class="yacht-images-carousel">
            <?php if (!empty($images)): ?>
                <div class="carousel-container">
                    <div class="carousel-slides">
                        <?php foreach ($images as $index => $image): ?>
                            <div class="carousel-slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Navigation Arrows -->
                    <?php if (count($images) > 1): ?>
                        <button class="carousel-prev" onclick="yachtCarousel.prev()">‹</button>
                        <button class="carousel-next" onclick="yachtCarousel.next()">›</button>
                        
                        <!-- Dots -->
                        <div class="carousel-dots">
                            <?php foreach ($images as $index => $image): ?>
                                <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="yachtCarousel.goTo(<?php echo $index; ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-images">⛵ No images available</div>
            <?php endif; ?>
        </div>
        
        <!-- Weekly Price Carousel -->
        <div class="yacht-price-carousel">
            <h3>Weekly Prices</h3>
            <?php if (!empty($prices)): ?>
                <div class="price-carousel-container">
                    <div class="price-carousel-slides">
                        <?php foreach ($prices as $index => $price): 
                            $discount_amount = $price->start_price - $price->price;
                            $week_start = date('M j', strtotime($price->date_from));
                            $week_end = date('M j, Y', strtotime($price->date_to));
                        ?>
                            <div class="price-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-date-from="<?php echo esc_attr($price->date_from); ?>"
                                 data-date-to="<?php echo esc_attr($price->date_to); ?>"
                                 data-price="<?php echo esc_attr($price->price); ?>">
                                
                                <div class="price-week"><?php echo $week_start; ?> - <?php echo $week_end; ?></div>
                                <div class="price-product"><?php echo esc_html($price->product); ?></div>
                                
                                <?php if ($price->discount_percentage > 0): ?>
                                    <div class="price-original">
                                        <span class="strikethrough"><?php echo number_format($price->start_price, 0, ',', '.'); ?> <?php echo esc_html($price->currency); ?></span>
                                    </div>
                                    <div class="price-discount-badge">
                                        <?php echo number_format($price->discount_percentage, 2); ?>% OFF - Save <?php echo number_format($discount_amount, 0, ',', '.'); ?> <?php echo esc_html($price->currency); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="price-final">
                                    <?php echo number_format($price->price, 0, ',', '.'); ?> <?php echo esc_html($price->currency); ?>
                                </div>
                                
                                <button class="price-select-btn" onclick="selectWeek(this)">Select This Week</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($prices) > 1): ?>
                        <button class="price-carousel-prev" onclick="priceCarousel.prev()">‹</button>
                        <button class="price-carousel-next" onclick="priceCarousel.next()">›</button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No pricing available. Please contact us for a quote.</p>
            <?php endif; ?>
        </div>
        
    </div>
    
    <!-- Quick Specs -->
    <div class="yacht-quick-specs">
        <div class="spec-item">
            <div class="spec-icon">📏</div>
            <div class="spec-value"><?php echo $length_ft; ?> ft</div>
            <div class="spec-label">Length</div>
        </div>
        <div class="spec-item">
            <div class="spec-icon">🛏️</div>
            <div class="spec-value"><?php echo esc_html($yacht->cabins); ?></div>
            <div class="spec-label">Cabins</div>
        </div>
        <div class="spec-item">
            <div class="spec-icon">⏳</div>
            <div class="spec-value">
                <?php echo esc_html($yacht->year_of_build); ?>
                <?php if ($yacht->refit_year): ?>
                    <span class="refit">(Refit <?php echo esc_html($yacht->refit_year); ?>)</span>
                <?php endif; ?>
            </div>
            <div class="spec-label">Year</div>
        </div>
        <div class="spec-item">
            <div class="spec-icon">🚽</div>
            <div class="spec-value"><?php echo esc_html($yacht->wc); ?></div>
            <div class="spec-label">Head</div>
        </div>
    </div>
    
    <!-- Technical Characteristics -->
    <div class="yacht-technical">
        <h3>Technical Characteristics</h3>
        <div class="tech-grid">
            <?php if ($yacht->draft): ?>
                <div class="tech-item">
                    <div class="tech-label">DRAUGHT</div>
                    <div class="tech-value"><?php echo $draft_ft; ?> ft</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->engine_power): ?>
                <div class="tech-item">
                    <div class="tech-label">ENGINE</div>
                    <div class="tech-value"><?php echo esc_html($yacht->engine_power); ?> HP</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->water_capacity): ?>
                <div class="tech-item">
                    <div class="tech-label">WATER</div>
                    <div class="tech-value"><?php echo esc_html($yacht->water_capacity); ?> L</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->beam): ?>
                <div class="tech-item">
                    <div class="tech-label">BEAM</div>
                    <div class="tech-value"><?php echo $beam_ft; ?> ft</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->fuel_capacity): ?>
                <div class="tech-item">
                    <div class="tech-label">FUEL</div>
                    <div class="tech-value"><?php echo esc_html($yacht->fuel_capacity); ?> L</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->berths): ?>
                <div class="tech-item">
                    <div class="tech-label">BERTHS</div>
                    <div class="tech-value"><?php echo esc_html($yacht->berths); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Description -->
    <?php if ($yacht->description): ?>
        <div class="yacht-description">
            <h3>Description</h3>
            <p><?php echo nl2br(esc_html($yacht->description)); ?></p>
        </div>
    <?php endif; ?>
    
    <!-- Equipment -->
    <?php if (!empty($equipment)): ?>
        <div class="yacht-equipment">
            <h3>Equipment</h3>
            <p class="equipment-list">
                <?php 
                $equipment_names = array_map(function($e) { return $e->equipment_name; }, $equipment);
                echo esc_html(implode(', ', $equipment_names));
                ?>
            </p>
        </div>
    <?php endif; ?>
    
    <!-- Optional Extras -->
    <?php if (!empty($extras)): ?>
        <div class="yacht-extras">
            <h3>Optional Extras</h3>
            <ul class="extras-list">
                <?php foreach ($extras as $extra): ?>
                    <li>
                        <span class="extra-name"><?php echo esc_html($extra->name); ?></span>
                        <span class="extra-price"><?php echo number_format($extra->price, 0, ',', '.'); ?> <?php echo esc_html($extra->currency); ?> / <?php echo esc_html(str_replace('_', ' ', $extra->unit)); ?></span>
                        <?php if ($extra->obligatory): ?>
                            <span class="extra-obligatory">*Obligatory</span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <!-- Back Button -->
    <div class="yacht-actions">
        <a href="javascript:history.back()" class="btn-back">← Back to Fleet</a>
    </div>
    
</div>

<style>
.yolo-yacht-details-v2 {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.yacht-header {
    text-align: center;
    margin-bottom: 40px;
}

.yacht-name {
    font-size: 42px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 10px 0;
    letter-spacing: 2px;
}

.yacht-model {
    font-size: 24px;
    font-weight: 600;
    color: #1e3a8a;
    margin: 0 0 10px 0;
}

.yacht-location {
    font-size: 16px;
    color: #6b7280;
    margin: 0;
}

/* Main Grid: Images + Price Carousel */
.yacht-main-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 40px;
}

.yacht-main-grid > *:first-child {
    flex: 2 1 60%;
    min-width: 300px;
}

.yacht-main-grid > *:last-child {
    flex: 1 1 30%;
    min-width: 280px;
}

@media (max-width: 968px) {
    .yacht-main-grid > * {
        flex: 1 1 100%;
    }
}

/* Image Carousel */
.yacht-images-carousel {
    position: relative;
    background: #f3f4f6;
    border-radius: 12px;
    overflow: hidden;
}

.carousel-container {
    position: relative;
    width: 100%;
    height: 500px;
}

.carousel-slides {
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.carousel-slide.active {
    opacity: 1;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-prev,
.carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    font-size: 40px;
    padding: 10px 20px;
    cursor: pointer;
    z-index: 10;
    transition: background 0.3s;
}

.carousel-prev:hover,
.carousel-next:hover {
    background: rgba(0,0,0,0.8);
}

.carousel-prev {
    left: 10px;
}

.carousel-next {
    right: 10px;
}

.carousel-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.carousel-dots .dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: background 0.3s;
}

.carousel-dots .dot.active,
.carousel-dots .dot:hover {
    background: white;
}

/* Price Carousel */
.yacht-price-carousel {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
}

.yacht-price-carousel h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
    text-align: center;
}

.price-carousel-container {
    position: relative;
}

.price-carousel-slides {
    position: relative;
    min-height: 300px;
}

.price-slide {
    display: none;
    text-align: center;
    padding: 20px;
}

.price-slide.active {
    display: block;
}

.price-week {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 8px;
}

.price-product {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 15px;
}

.price-original {
    margin-bottom: 10px;
}

.strikethrough {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: 18px;
}

.price-discount-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 15px;
    display: inline-block;
}

.price-final {
    font-size: 36px;
    font-weight: 700;
    color: #059669;
    margin-bottom: 20px;
}

.price-select-btn {
    background: #1e3a8a;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
}

.price-select-btn:hover {
    background: #1e40af;
    transform: translateY(-2px);
}

.price-carousel-prev,
.price-carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #1e3a8a;
    color: white;
    border: none;
    font-size: 24px;
    padding: 8px 16px;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.3s;
}

.price-carousel-prev:hover,
.price-carousel-next:hover {
    background: #1e40af;
}

.price-carousel-prev {
    left: -15px;
}

.price-carousel-next {
    right: -15px;
}

/* Quick Specs */
.yacht-quick-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 40px;
}

.yacht-quick-specs > * {
    flex: 1 1 calc(25% - 20px);
    min-width: 120px;
}

@media (max-width: 768px) {
    .yacht-quick-specs > * {
        flex: 1 1 calc(50% - 20px);
    }
}

.spec-item {
    text-align: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.spec-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.spec-value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 5px;
}

.spec-value .refit {
    display: block;
    font-size: 12px;
    font-weight: 400;
    color: #6b7280;
    margin-top: 4px;
}

.spec-label {
    font-size: 14px;
    color: #6b7280;
}

/* Technical Characteristics */
.yacht-technical {
    margin-bottom: 40px;
}

.yacht-technical h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 20px;
}

.tech-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.tech-grid > * {
    flex: 1 1 calc(50% - 20px);
    min-width: 250px;
}

@media (max-width: 768px) {
    .tech-grid > * {
        flex: 1 1 100%;
    }
}

.tech-item {
    background: #f9fafb;
    padding: 15px 20px;
    border-radius: 8px;
}

.tech-label {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.tech-value {
    font-size: 18px;
    font-weight: 600;
    color: #1e3a8a;
}

/* Description */
.yacht-description {
    margin-bottom: 40px;
}

.yacht-description h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
}

.yacht-description p {
    font-size: 16px;
    line-height: 1.6;
    color: #4b5563;
}

/* Equipment */
.yacht-equipment {
    margin-bottom: 40px;
}

.yacht-equipment h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
}

.equipment-list {
    font-size: 16px;
    line-height: 1.8;
    color: #4b5563;
}

/* Extras */
.yacht-extras {
    margin-bottom: 40px;
}

.yacht-extras h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
}

.extras-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.extras-list li {
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.extra-name {
    font-size: 16px;
    color: #1f2937;
}

.extra-price {
    font-size: 16px;
    font-weight: 600;
    color: #059669;
}

.extra-obligatory {
    font-size: 12px;
    color: #dc2626;
    font-weight: 600;
}

/* Actions */
.yacht-actions {
    margin-top: 40px;
    text-align: center;
}

.btn-back {
    display: inline-block;
    padding: 12px 30px;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

.no-images {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 500px;
    font-size: 24px;
    color: #9ca3af;
}
</style>

<script>
// Image Carousel
const yachtCarousel = {
    currentSlide: 0,
    slides: document.querySelectorAll('.carousel-slide'),
    dots: document.querySelectorAll('.carousel-dots .dot'),
    
    goTo: function(index) {
        this.slides[this.currentSlide].classList.remove('active');
        this.dots[this.currentSlide].classList.remove('active');
        
        this.currentSlide = index;
        
        this.slides[this.currentSlide].classList.add('active');
        this.dots[this.currentSlide].classList.add('active');
    },
    
    next: function() {
        const nextIndex = (this.currentSlide + 1) % this.slides.length;
        this.goTo(nextIndex);
    },
    
    prev: function() {
        const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.goTo(prevIndex);
    }
};

// Price Carousel
const priceCarousel = {
    currentSlide: 0,
    slides: document.querySelectorAll('.price-slide'),
    
    goTo: function(index) {
        this.slides[this.currentSlide].classList.remove('active');
        this.currentSlide = index;
        this.slides[this.currentSlide].classList.add('active');
    },
    
    next: function() {
        const nextIndex = (this.currentSlide + 1) % this.slides.length;
        this.goTo(nextIndex);
    },
    
    prev: function() {
        const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.goTo(prevIndex);
    }
};

// Select Week Function
function selectWeek(button) {
    const slide = button.closest('.price-slide');
    const dateFrom = slide.dataset.dateFrom;
    const dateTo = slide.dataset.dateTo;
    const price = slide.dataset.price;
    
    // TODO: Implement date picker population and booking flow
    Toastify({
        text: 'Selected week: ' + dateFrom + ' to ' + dateTo + ' - Price: €' + price,
        duration: 4000,
        gravity: 'top',
        position: 'right',
        backgroundColor: '#1e3a8a',
        stopOnFocus: true
    }).showToast();
}

// Auto-advance disabled - manual navigation only (v70.5)
</script>
