<?php
/**
 * Yacht Details Template v3 - RESTRUCTURED FOR STICKY SIDEBAR
 * Version: 2.6.0
 * 
 * CHANGES:
 * ✓ Sticky booking sidebar on desktop (follows user while scrolling)
 * ✓ All content flows on LEFT side
 * ✓ Booking section on RIGHT side (sticky)
 * ✓ Mobile: booking section at top, then content below
 * ✓ Technical characteristics with icons
 * ✓ Equipment with proper icons
 * 
 * Replace: yolo-yacht-search/public/templates/yacht-details-v3.php
 */

// Include equipment icons mapping
if (defined('YOLO_YS_PLUGIN_DIR')) {
    require_once YOLO_YS_PLUGIN_DIR . 'includes/equipment-icons.php';
} else {
    require_once dirname(__DIR__, 2) . '/includes/equipment-icons.php';
}

// Get yacht ID from URL
$yacht_id = isset($_GET['yacht_id']) ? sanitize_text_field($_GET['yacht_id']) : '';

// Get search dates from URL (passed from search results)
$requested_date_from = isset($_GET['dateFrom']) ? sanitize_text_field($_GET['dateFrom']) : '';
$requested_date_to   = isset($_GET['dateTo'])   ? sanitize_text_field($_GET['dateTo']) : '';

if (!empty($requested_date_from))
    $requested_date_from = substr($requested_date_from, 0, 10);

if (!empty($requested_date_to))
    $requested_date_to = substr($requested_date_to, 0, 10);

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

// Track yacht view event (server-side Facebook Conversions API)
if (function_exists('yolo_analytics')) {
    $yacht_price = !empty($yacht->price) ? floatval($yacht->price) : 0;
    $yacht_name = !empty($yacht->name) ? $yacht->name : '';
    yolo_analytics()->track_yacht_view($yacht_id, $yacht_price, $yacht_name);
}

// Get images
$images_table = $wpdb->prefix . 'yolo_yacht_images';
$images = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $images_table WHERE yacht_id = %s ORDER BY sort_order ASC",
    $yacht_id
));

// Get weekly offers from database
$all_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
$prices = array();

if (!empty($all_prices)) {
    $today = date('Y-m-d');
    foreach ($all_prices as $price) {
        if ($price->date_from >= $today) {
            $prices[] = $price;
        }
    }
    
    usort($prices, function($a, $b) {
        return strtotime($a->date_from) - strtotime($b->date_from);
    });
    
    $prices = array_slice($prices, 0, 20);
}

// Default to first available July week if no dates provided
if (empty($requested_date_from) || empty($requested_date_to)) {
    foreach ($prices as $price) {
        if (date('m', strtotime($price->date_from)) == '07') {
            $requested_date_from = substr($price->date_from, 0, 10);
            $requested_date_to = substr($price->date_to, 0, 10);
            break;
        }
    }
}

// Get equipment with names from catalog
$equipment_table = $wpdb->prefix . 'yolo_yacht_equipment';
$catalog_table = $wpdb->prefix . 'yolo_equipment_catalog';
$equipment = $wpdb->get_results($wpdb->prepare(
    "SELECT e.*, c.name as equipment_name 
     FROM $equipment_table e 
     LEFT JOIN $catalog_table c ON e.equipment_id = c.id 
     WHERE e.yacht_id = %s",
    $yacht_id
));

// Get extras
$extras_table = $wpdb->prefix . 'yolo_yacht_extras';
$extras = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $extras_table WHERE yacht_id = %s",
    $yacht_id
));

// Calculate obligatory extras totals for Stripe checkout
$cached_included_extras = 0;  // Extras included in price (payableInBase = 0)
$cached_extras_at_base = 0;   // Extras payable at base (payableInBase = 1)
$cached_extras_details = array();
foreach ($extras as $extra) {
    if ($extra->obligatory) {
        $cached_extras_details[] = array(
            'name' => $extra->name,
            'price' => floatval($extra->price),
            'currency' => $extra->currency,
            'payableInBase' => (bool)$extra->payableInBase
        );
        if ($extra->payableInBase) {
            $cached_extras_at_base += floatval($extra->price);
        } else {
            $cached_included_extras += floatval($extra->price);
        }
    }
}

// Convert meters to feet
$length_ft = $yacht->length ? round($yacht->length * 3.28084) : 0;
$beam_ft = $yacht->beam ? round($yacht->beam * 3.28084, 1) : 0;
$draft_ft = $yacht->draft ? round($yacht->draft * 3.28084, 1) : 0;

// Get Litepicker JS
$litepicker_url = YOLO_YS_PLUGIN_URL . 'assets/js/litepicker.js';
?>

<!-- FontAwesome loaded conditionally via plugin settings (public/class-yolo-ys-public.php) -->

<!-- Load Litepicker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/litepicker/dist/css/litepicker.css"/>

<div class="yolo-yacht-details-v3">
    
    <!-- Yacht Name Header (Full Width) -->
    <div class="yacht-header">
        <h1 class="yacht-name">
            <span class="yacht-title"><?php echo esc_html(strtoupper($yacht->name)); ?></span>
            <span class="separator">|</span>
            <span class="model"><?php echo esc_html($yacht->model); ?></span>
            <?php if ($yacht->home_base): ?>
                <span class="separator">|</span>
                <span class="location" onclick="document.querySelector('.yacht-map-section h3')?.scrollIntoView({behavior: 'smooth'});">
                    <i class="fa-solid fa-location-dot"></i> <?php echo esc_html($yacht->home_base); ?>, Greece
                </span>
            <?php endif; ?>
        </h1>
    </div>
    
    <!-- MAIN CONTENT WRAPPER: Bootstrap Grid Layout -->
    <div class="container-fluid px-0">
        <div class="row g-4">
            
            <!-- ============================================ -->
            <!-- MAIN CONTENT (LEFT COLUMN - 8 cols desktop) -->
            <!-- ============================================ -->
            <div class="col-12 col-lg-8">
                <div class="yacht-main-content">
            
            <!-- Image Carousel - Swiper (v30.1) -->
            <div class="yacht-images-carousel">
                <?php if (!empty($images)): ?>
                    <div class="swiper yacht-image-swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($images as $index => $image): ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <!-- Navigation arrows -->
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                            
                            <!-- Pagination dots -->
                            <div class="swiper-pagination"></div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-images"><i class="fa-solid fa-sailboat"></i> No images available</div>
                <?php endif; ?>
            </div>
            
            <!-- Weekly Price Carousel - Swiper -->
            <?php if (!empty($prices)): ?>
            <div class="yacht-price-carousel-section">
                <h3><i class="fa-solid fa-calendar-week"></i> <?php yolo_ys_text_e('weekly_prices_title', 'Peak Season Pricing (May - September)'); ?></h3>
                <div class="swiper price-swiper" 
                     data-init-date-from="<?php echo esc_attr($requested_date_from); ?>"
                     data-init-date-to="<?php echo esc_attr($requested_date_to); ?>">
                    <div class="swiper-wrapper">
                        <?php foreach ($prices as $index => $price): 
                            $discount_amount = $price->start_price - $price->price;
                            $week_start = date('M j', strtotime($price->date_from));
                            $week_end = date('M j, Y', strtotime($price->date_to));
                        ?>
                            <div class="swiper-slide price-slide" 
                                 data-date-from="<?php echo esc_attr(date('Y-m-d', strtotime($price->date_from))); ?>"
                                 data-date-to="<?php echo esc_attr(date('Y-m-d', strtotime($price->date_to))); ?>"
                                 data-price="<?php echo esc_attr($price->price); ?>"
                                 data-start-price="<?php echo esc_attr($price->start_price); ?>"
                                 data-discount="<?php echo esc_attr($price->discount_percentage); ?>"
                                 data-currency="<?php echo esc_attr($price->currency); ?>">
                                
                                <div class="price-week"><?php echo $week_start; ?> - <?php echo $week_end; ?></div>
                                
                                <?php if ($price->discount_percentage > 0): ?>
                                    <div class="price-original">
                                        <span class="strikethrough"><?php echo number_format($price->start_price, 2, '.', ','); ?> <?php echo esc_html($price->currency); ?></span>
                                    </div>
                                    <div class="price-discount-badge">
                                        <?php echo number_format($price->discount_percentage, 2, '.', ','); ?>% <?php yolo_ys_text_e('discount_off', 'OFF'); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="price-final">
                                    <?php echo number_format($price->price, 2, '.', ','); ?> <?php echo esc_html($price->currency); ?>
                                </div>
                                
                                <button class="price-select-btn" onclick="selectWeek(this)"><?php yolo_ys_text_e('select_week', 'Select This Week'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (count($prices) > 1): ?>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quick Specs -->
            <div class="row g-3 yacht-quick-specs">
                <div class="col-6 col-sm-6 col-md-3">
                <div class="spec-item">
                    <div class="spec-icon"><i class="fa-solid fa-ruler-horizontal"></i></div>
                    <div class="spec-value"><?php echo $length_ft; ?> ft</div>
                    <div class="spec-label"><?php yolo_ys_text_e('length', 'Length'); ?></div>
                </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3">
                <div class="spec-item">
                    <div class="spec-icon"><i class="fa-solid fa-bed"></i></div>
                    <div class="spec-value"><?php echo esc_html($yacht->cabins); ?></div>
                    <div class="spec-label"><?php yolo_ys_text_e('cabins', 'Cabins'); ?></div>
                </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3">
                <div class="spec-item">
                    <div class="spec-icon"><i class="fa-solid fa-calendar-days"></i></div>
                    <div class="spec-value">
                        <?php echo esc_html($yacht->year_of_build); ?>
                        <?php if ($yacht->refit_year): ?>
                            <span class="refit">(Refit <?php echo esc_html($yacht->refit_year); ?>)</span>
                        <?php endif; ?>
                    </div>
                    <div class="spec-label"><?php yolo_ys_text_e('year_built', 'Year'); ?></div>
                </div>
                </div>
                <div class="col-6 col-sm-6 col-md-3">
                <div class="spec-item">
                    <div class="spec-icon"><i class="fa-solid fa-toilet"></i></div>
                    <div class="spec-value"><?php echo esc_html($yacht->wc); ?></div>
                    <div class="spec-label"><?php yolo_ys_text_e('wc', 'Heads'); ?></div>
                </div>
                </div>
            </div>
            
            <!-- Description Section -->
            <?php if (!empty($yacht->description)): ?>
            <?php
                $paragraphs = array_filter(explode("\n", $yacht->description));
                $preview_paragraphs = array_slice($paragraphs, 0, 2);
                $remaining_paragraphs = array_slice($paragraphs, 2);
                $has_more = count($remaining_paragraphs) > 0;
            ?>
            <div class="yacht-description-section">
                <h3><i class="fa-solid fa-info-circle"></i> Description</h3>
                <div class="yacht-description-content">
                    <div class="description-preview">
                        <?php echo nl2br(esc_html(implode("\n", $preview_paragraphs))); ?>
                    </div>
                    <?php if ($has_more): ?>
                        <div class="description-full" style="display: none;">
                            <?php echo nl2br(esc_html(implode("\n", $remaining_paragraphs))); ?>
                        </div>
                        <button class="description-toggle" onclick="toggleDescription(this)">
                            <span class="toggle-more"><?php yolo_ys_text_e('read_more', 'More...'); ?></span>
                            <span class="toggle-less" style="display: none;"><?php yolo_ys_text_e('read_less', 'Less'); ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Equipment Section -->
            <?php if (!empty($equipment)): ?>
            <div class="yacht-equipment-section">
                <h3><?php echo yolo_ys_get_icon('section', 'Equipment', 'fa-solid fa-ship'); ?> <?php yolo_ys_text_e('equipment', 'Equipment'); ?></h3>
                <div class="row g-3 equipment-grid">
                    <?php foreach ($equipment as $item): ?>
                        <?php $icon_class = yolo_get_equipment_icon($item->equipment_id, $item->equipment_name); ?>
                        <div class="col-12 col-sm-6">
                        <div class="equipment-item">
                            <i class="<?php echo esc_attr($icon_class); ?>"></i>
                            <span><?php echo esc_html($item->equipment_name); ?></span>
                        </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Location Map -->
            <?php if ($yacht->home_base): ?>
            <div class="yacht-map-section">
                <h3><i class="fa-solid fa-map-location-dot"></i> <?php yolo_ys_text_e('location', 'Location'); ?></h3>
                <div class="map-container">
                    <?php 
                    $maps_key = get_option('yolo_ys_google_maps_api_key', '');
                    if (!empty($maps_key)): 
                    ?>
                    <iframe 
                        src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr($maps_key); ?>&q=<?php echo urlencode($yacht->home_base . ', Greece'); ?>&zoom=12"
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                    <?php else: ?>
                    <div class="map-unavailable" style="padding: 40px; text-align: center; background: #f3f4f6; border-radius: 8px; color: #6b7280;">
                        <i class="fa-solid fa-map-location-dot" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5;"></i>
                        <p style="margin: 0; font-size: 16px;"><?php yolo_ys_text_e('map_unavailable', 'Map unavailable. Please configure Google Maps API key in plugin settings.'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Technical Characteristics -->
            <div class="yacht-technical">
                <h3><?php echo yolo_ys_get_icon('section', 'Technical Characteristics', 'fa-solid fa-cogs'); ?> <?php yolo_ys_text_e('technical_specs', 'Technical Characteristics'); ?></h3>
                <div class="row g-3 tech-grid">
                    <?php if ($yacht->draft): ?>
                        <div class="col-12 col-sm-6">
                        <div class="tech-item">
                            <div class="tech-icon"><i class="fa-solid fa-water"></i></div>
                            <div class="tech-content">
                                <div class="spec-label"><?php yolo_ys_text_e('draught', 'DRAUGHT'); ?></div>
                                <div class="tech-value"><?php echo $draft_ft; ?> ft (<?php echo number_format($yacht->draft, 2); ?> m)</div>
                            </div>
                        </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($yacht->beam): ?>
                        <div class="col-12 col-sm-6">
                        <div class="tech-item">
                            <div class="tech-icon"><i class="fa-solid fa-arrows-left-right"></i></div>
                            <div class="tech-content">
                                <div class="tech-label">BEAM</div>
                                <div class="tech-value"><?php echo $beam_ft; ?> ft (<?php echo number_format($yacht->beam, 2); ?> m)</div>
                            </div>
                        </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($yacht->berths): ?>
                        <div class="col-12 col-sm-6">
                        <div class="tech-item">
                            <div class="tech-icon"><i class="fa-solid fa-users"></i></div>
                            <div class="tech-content">
                                <div class="tech-label">BERTHS</div>
                                <div class="tech-value"><?php echo esc_html($yacht->berths); ?></div>
                            </div>
                        </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($yacht->engine_power): ?>
                        <div class="col-12 col-sm-6">
                        <div class="tech-item">
                            <div class="tech-icon"><i class="fa-solid fa-gauge-high"></i></div>
                            <div class="tech-content">
                                <div class="spec-label"><?php yolo_ys_text_e('engine', 'ENGINE'); ?></div>
                                <div class="tech-value"><?php echo esc_html($yacht->engine_power); ?> HP</div>
                            </div>
                        </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($yacht->fuel_capacity): ?>
                        <div class="col-12 col-sm-6">
                        <div class="tech-item">
                            <div class="tech-icon"><i class="fa-solid fa-gas-pump"></i></div>
                            <div class="tech-content">
                                <div class="tech-label">FUEL CAPACITY</div>
                                <div class="tech-value"><?php echo esc_html($yacht->fuel_capacity); ?> L</div>
                            </div>
                        </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($yacht->water_capacity): ?>
                        <div class="col-12 col-sm-6">
                        <div class="tech-item">
                            <div class="tech-icon"><i class="fa-solid fa-faucet-drip"></i></div>
                            <div class="tech-content">
                                <div class="tech-label">WATER CAPACITY</div>
                                <div class="tech-value"><?php echo esc_html($yacht->water_capacity); ?> L</div>
                            </div>
                        </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Extras -->
            <?php if (!empty($extras)): ?>
                <?php 
                $obligatory_extras = array_filter($extras, function($e) { return $e->obligatory == 1; });
                $optional_extras = array_filter($extras, function($e) { return $e->obligatory == 0; });
                ?>
                
                <div class="yacht-extras-combined">
                    <h3><i class="fa-solid fa-plus-circle"></i> <?php yolo_ys_text_e('extras', 'Extras'); ?> <span class="extras-note"><?php yolo_ys_text_e('payable_at_base', '(Payable at the base)'); ?></span></h3>
                    <div class="row g-4 extras-two-column">
                        <?php if (!empty($obligatory_extras)): ?>
                        <div class="col-12 col-md-6 extras-column">
                            <h4><?php yolo_ys_text_e('obligatory_extras', 'Obligatory Extras'); ?></h4>
                            <div class="extras-grid">
                                <?php foreach ($obligatory_extras as $extra): ?>
                                    <div class="extra-item obligatory">
                                        <div class="extra-name"><?php echo esc_html($extra->name); ?></div>
                                        <?php if ($extra->price > 0): ?>
                                            <div class="extra-price">
                                                <?php echo number_format($extra->price, 2); ?> <?php echo esc_html($extra->currency); ?>
                                <?php if (!empty($extra->unit)): ?>
                                    <?php
                                    $unit_display = $extra->unit;
                                    if ($unit_display === 'per_booking') $unit_display = 'Per Booking';
                                    elseif ($unit_display === 'per_night') $unit_display = 'Per Night';
                                    elseif ($unit_display === 'per_week') $unit_display = 'Per Week';
                                    elseif ($unit_display === 'per_day') $unit_display = 'Per Day';
                                    elseif ($unit_display === 'per_hour') $unit_display = 'Per Hour';
                                    elseif ($unit_display === 'per_booking_person') $unit_display = 'Per Person';
                                    ?>
                                    <span class="price-unit">(<?php echo esc_html($unit_display); ?>)</span>
                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($optional_extras)): ?>
                        <div class="col-12 col-md-6 extras-column">
                            <h4><?php yolo_ys_text_e('optional_extras', 'Optional Extras'); ?></h4>
                            <div class="extras-grid">
                                <?php foreach ($optional_extras as $extra): ?>
                                    <div class="extra-item optional">
                                        <div class="extra-name"><?php echo esc_html($extra->name); ?></div>
                                        <?php if ($extra->price > 0): ?>
                                            <div class="extra-price">
                                                <?php echo number_format($extra->price, 2); ?> <?php echo esc_html($extra->currency); ?>
                                <?php if (!empty($extra->unit)): ?>
                                    <?php
                                    $unit_display = $extra->unit;
                                    if ($unit_display === 'per_booking') $unit_display = 'Per Booking';
                                    elseif ($unit_display === 'per_night') $unit_display = 'Per Night';
                                    elseif ($unit_display === 'per_week') $unit_display = 'Per Week';
                                    elseif ($unit_display === 'per_day') $unit_display = 'Per Day';
                                    elseif ($unit_display === 'per_hour') $unit_display = 'Per Hour';
                                    elseif ($unit_display === 'per_booking_person') $unit_display = 'Per Person';
                                    ?>
                                    <span class="price-unit">(<?php echo esc_html($unit_display); ?>)</span>
                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Security Deposit Section -->
            <?php if (!empty($yacht->deposit)): ?>
            <div class="yacht-section deposit-section">
                <h3><?php echo yolo_ys_get_icon('section', 'Security Deposit', 'fa-solid fa-shield-halved'); ?> <?php yolo_ys_text_e('security_deposit', 'Security Deposit'); ?></h3>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="info-card deposit-card">
                            <div class="deposit-amount-large"><?php echo number_format($yacht->deposit, 0); ?> €</div>
                            <p>A security deposit of <strong><?php echo number_format($yacht->deposit, 0); ?> €</strong> is required at check-in. This deposit is held as a guarantee against any damage to the yacht or its equipment during your charter period.</p>
                            <p>The deposit will be fully refunded after check-out, provided the yacht is returned in the same condition as received, with no damages or missing equipment.</p>
                            <p class="deposit-note"><strong>Payment method:</strong> The deposit is typically held via credit card authorization or bank transfer. Please confirm the exact payment method with the charter company before your departure.</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Cancellation Policy Section -->
            <div class="yacht-section cancellation-section">
                <h3><?php echo yolo_ys_get_icon('section', 'Cancellation Policy', 'fa-solid fa-calendar-xmark'); ?> <?php yolo_ys_text_e('cancellation_policy', 'Cancellation Policy'); ?></h3>
                <div class="row g-3">
                    <div class="col-12">
                        <div class="info-card cancellation-card">
                            <div class="cancellation-timeline">
                                <div class="timeline-item">
                                    <div class="timeline-icon"><i class="fa-solid fa-circle-check"></i></div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">More than 60 days before charter</div>
                                        <div class="timeline-value">100% refund</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-icon"><i class="fa-solid fa-circle-half-stroke"></i></div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">30-60 days before charter</div>
                                        <div class="timeline-value">50% refund</div>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                                    <div class="timeline-content">
                                        <div class="timeline-label">Less than 30 days before charter</div>
                                        <div class="timeline-value">No refund</div>
                                    </div>
                                </div>
                            </div>
                            <p class="cancellation-note"><strong>Note:</strong> Cancellation must be made in writing via email. Refunds will be processed within 14 business days.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Check-in / Check-out Section -->
            <div class="yacht-section checkin-section">
                <h3><i class="fa-solid fa-clock"></i> Check-in & Check-out</h3>
                <div class="row g-3 checkin-grid-redesign">
                    <?php if (!empty($yacht->checkin_time)): ?>
                    <div class="col-12 col-md-4">
                        <div class="info-card checkin-card">
                            <div class="card-icon"><i class="fa-solid fa-circle-check"></i></div>
                            <div class="card-label">Check-in Time</div>
                            <div class="card-value"><?php echo esc_html($yacht->checkin_time); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($yacht->checkout_time)): ?>
                    <div class="col-12 col-md-4">
                        <div class="info-card checkout-card">
                            <div class="card-icon"><i class="fa-solid fa-circle-xmark"></i></div>
                            <div class="card-label">Check-out Time</div>
                            <div class="card-value"><?php echo esc_html($yacht->checkout_time); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($yacht->checkin_day)): ?>
                    <?php 
                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    $day_name = isset($days[$yacht->checkin_day - 1]) ? $days[$yacht->checkin_day - 1] : '';
                    ?>
                    <div class="col-12 col-md-4">
                        <div class="info-card checkin-day-card">
                            <div class="card-icon"><i class="fa-solid fa-calendar-day"></i></div>
                            <div class="card-label">Check-in Day</div>
                            <div class="card-value"><?php echo esc_html($day_name); ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
                </div>
            </div>
            <!-- END MAIN CONTENT -->
            
            <!-- ============================================ -->
            <!-- BOOKING SIDEBAR (RIGHT - STICKY - 4 cols desktop) -->
            <!-- ============================================ -->
            <div class="col-12 col-lg-4">
            <div class="yacht-booking-section">
                <h3><i class="fa-solid fa-calendar-check"></i> <?php yolo_ys_text_e('availability_pricing', 'Availability & Pricing'); ?></h3>
                
                <?php if (empty($prices)): ?>
                    <p style="text-align: center; color: #6b7280;"><?php yolo_ys_text_e('no_pricing', 'No pricing available. Please contact us for a quote.'); ?></p>
                <?php endif; ?>
                
                <!-- Selected Week Price Display -->
                <div id="selectedPriceDisplay" class="selected-price-display" style="display: none;">
                    <div id="selectedPriceOriginal" class="selected-price-original"></div>
                    <div id="selectedPriceDiscount" class="selected-price-discount"></div>
                    <div id="selectedPriceFinal" class="selected-price-final"></div>
                </div>
                
                <!-- Date Picker -->
                <div class="date-picker-section">
                    <h4><?php yolo_ys_text_e('choose_custom_dates', 'Or Choose Custom Dates'); ?></h4>
                    <input type="text" id="dateRangePicker" placeholder="Select dates" readonly 
                        data-init-date-from="<?php echo esc_attr($requested_date_from); ?>" 
                        data-init-date-to="<?php echo esc_attr($requested_date_to); ?>" />
                </div>
                
                <!-- Book Now Button -->
                <button class="btn-book-now" onclick="bookNow()" id="bookNowBtn">
                    <span class="btn-main-text"><?php yolo_ys_text_e('book_now', 'BOOK NOW'); ?></span>
                    <span class="btn-sub-text" id="depositText">Loading...</span>
                </button>
                
                <!-- Request Quote Section -->
                <div class="quote-section">
                    <p class="quote-label"><?php yolo_ys_text_e('quote_tagline', 'Need something special?'); ?></p>
                    <button class="btn-request-quote" onclick="toggleQuoteForm()">REQUEST A QUOTE</button>
                </div>
                
                <!-- Quote Form (Hidden by default) -->
                <div id="quoteForm" class="quote-form" style="display: none;">
                    <h4>Request a Quote</h4>
                    <form id="quoteRequestForm">
                        <input type="hidden" name="yacht_id" value="<?php echo esc_attr($yacht_id); ?>" />
                        <input type="hidden" name="yacht_name" value="<?php echo esc_attr($yacht->name); ?>" />
                        
                        <div class="row g-2 form-row">
                            <div class="col-12 col-sm-6 form-field">
                                <input type="text" name="first_name" placeholder="First name *" required />
                            </div>
                            <div class="col-12 col-sm-6 form-field">
                                <input type="text" name="last_name" placeholder="Last name *" required />
                            </div>
                        </div>
                        
                        <div class="row g-2 form-row">
                            <div class="col-12 col-sm-6 form-field">
                                <input type="email" name="email" placeholder="E-mail *" required />
                            </div>
                            <div class="col-12 col-sm-6 form-field">
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
            <!-- END BOOKING SIDEBAR -->
            
        </div>
        <!-- END ROW -->
    </div>
    <!-- END CONTAINER -->
    
</div>

<?php include dirname(__FILE__) . '/partials/yacht-details-v3-styles.php'; ?>

<!-- Pass cached extras to JavaScript for Stripe checkout -->
<script>
window.yoloCachedExtras = {
    includedExtras: <?php echo json_encode($cached_included_extras); ?>,
    extrasAtBase: <?php echo json_encode($cached_extras_at_base); ?>,
    extrasDetails: <?php echo json_encode($cached_extras_details); ?>
};
</script>

<?php include dirname(__FILE__) . '/partials/yacht-details-v3-scripts.php'; ?>

