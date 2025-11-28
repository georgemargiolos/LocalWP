<?php
/**
 * Yacht Details Template v3
 * With Date Picker, Quote Form, and Book Now Button
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

// Get prices - filter for peak season (May-September) only
$all_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
$prices = array();
if (!empty($all_prices)) {
    foreach ($all_prices as $price) {
        $month = (int)date('n', strtotime($price->date_from)); // 1-12
        // Only include May (5), June (6), July (7), August (8), September (9)
        if ($month >= 5 && $month <= 9) {
            $prices[] = $price;
        }
    }
}

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

// Get Litepicker JS
$litepicker_url = YOLO_YS_PLUGIN_URL . 'assets/js/litepicker.js';
?>

<!-- Load Litepicker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>

<div class="yolo-yacht-details-v3">
    
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
        
        <!-- Weekly Price Carousel + Booking Section -->
        <div class="yacht-booking-section">
            <h3>Availability & Pricing</h3>
            <?php if (!empty($prices)): ?>
                <div class="price-carousel-container" data-visible-slides="4">
                    <div class="price-carousel-slides">
                        <?php foreach ($prices as $index => $price): 
                            $discount_amount = $price->start_price - $price->price;
                            $week_start = date('M j', strtotime($price->date_from));
                            $week_end = date('M j, Y', strtotime($price->date_to));
                        ?>
                            <div class="price-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-date-from="<?php echo esc_attr(date('Y-m-d', strtotime($price->date_from))); ?>"
                                 data-date-to="<?php echo esc_attr(date('Y-m-d', strtotime($price->date_to))); ?>"
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
            
            <!-- Date Picker -->
            <div class="date-picker-section">
                <h4>Or Choose Custom Dates</h4>
                <input type="text" id="dateRangePicker" placeholder="Select dates" readonly />
            </div>
            
            <!-- Book Now Button -->
            <button class="btn-book-now" onclick="bookNow()">BOOK NOW</button>
            
            <!-- Request Quote Section -->
            <div class="quote-section">
                <p class="quote-label">Need something special?</p>
                <button class="btn-request-quote" onclick="toggleQuoteForm()">REQUEST A QUOTE</button>
            </div>
            
            <!-- Quote Form (Hidden by default) -->
            <div id="quoteForm" class="quote-form" style="display: none;">
                <h4>Request a Quote</h4>
                <form id="quoteRequestForm">
                    <input type="hidden" name="yacht_id" value="<?php echo esc_attr($yacht_id); ?>" />
                    <input type="hidden" name="yacht_name" value="<?php echo esc_attr($yacht->name); ?>" />
                    
                    <div class="form-row">
                        <div class="form-field">
                            <input type="text" name="first_name" placeholder="First name *" required />
                        </div>
                        <div class="form-field">
                            <input type="text" name="last_name" placeholder="Last name *" required />
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-field">
                            <input type="email" name="email" placeholder="E-mail *" required />
                        </div>
                        <div class="form-field">
                            <input type="tel" name="phone" placeholder="Phone number *" required />
                        </div>
                    </div>
                    
                    <div class="form-field">
                        <textarea name="special_requests" placeholder="Special requests" rows="4"></textarea>
                    </div>
                    
                    <p class="form-note">* Required field</p>
                    <p class="form-tagline">Quick and free quotation for your sailing holiday.</p>
                    
                    <button type="submit" class="btn-submit-quote">Request a quote</button>
                </form>
            </div>
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
    
    <!-- Google Maps -->
    <?php if ($yacht->home_base): ?>
    <div class="yacht-map-section">
        <h3>Location</h3>
        <div id="yachtMap" style="width: 100%; height: 400px; background: #f3f4f6; border-radius: 8px;"></div>
    </div>
    <?php endif; ?>
    
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

<!-- Load Litepicker JS -->
<script src="<?php echo esc_url($litepicker_url); ?>"></script>

<!-- Load Google Maps -->
<script>
var yachtLocation = <?php echo json_encode($yacht->home_base); ?>;
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=YOUR_GOOGLE_MAPS_API_KEY&callback=initMap" async defer></script>

<?php include dirname(__FILE__) . '/partials/yacht-details-v3-styles.php'; ?>
<?php include dirname(__FILE__) . '/partials/yacht-details-v3-scripts.php'; ?>
