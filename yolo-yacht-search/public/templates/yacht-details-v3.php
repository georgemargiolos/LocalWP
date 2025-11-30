<?php
/**
 * Yacht Details Template v3
 * With Date Picker, Quote Form, and Book Now Button
 * Price Carousel moved below images at full width
 */

// Include equipment icons mapping
// Use the plugin base directory constant to ensure the correct file path.
// YOLO_YS_PLUGIN_DIR is defined in the main plugin file and points to the root of this plugin.
if (defined('YOLO_YS_PLUGIN_DIR')) {
    require_once YOLO_YS_PLUGIN_DIR . 'includes/equipment-icons.php';
} else {
    // Fallback for direct access (should not happen in WordPress context)
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

// Get images
$images_table = $wpdb->prefix . 'yolo_yacht_images';
$images = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $images_table WHERE yacht_id = %s ORDER BY sort_order ASC",
    $yacht_id
));

// CRITICAL: Price carousel loads from DATABASE, NOT live API
// Get weekly offers (already Saturday-to-Saturday from /offers endpoint)
// These prices are synced via "Sync Weekly Offers" in admin
// DO NOT call live API here - it's slow and can fail
$all_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
$prices = array();

if (!empty($all_prices)) {
    // Filter to show only future dates and sort by date
    $today = date('Y-m-d');
    foreach ($all_prices as $price) {
        // Only include offers that haven't started yet or are current
        if ($price->date_from >= $today) {
            $prices[] = $price;
        }
    }
    
    // Sort by date (earliest first)
    usort($prices, function($a, $b) {
        return strtotime($a->date_from) - strtotime($b->date_from);
    });
    
    // Limit to next 20 weeks to keep carousel manageable
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
    
    <!-- Main Content Grid: Images + Booking Section -->
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
        
        <!-- Booking Section (Right Sidebar) -->
        <div class="yacht-booking-section">
            <h3>Availability & Pricing</h3>
            
            <?php if (empty($prices)): ?>
                <p>No pricing available. Please contact us for a quote.</p>
            <?php endif; ?>
            
            <!-- Selected Week Price Display -->
            <div id="selectedPriceDisplay" class="selected-price-display" style="display: none;">
                <div id="selectedPriceOriginal" class="selected-price-original"></div>
                <div id="selectedPriceDiscount" class="selected-price-discount"></div>
                <div id="selectedPriceFinal" class="selected-price-final"></div>
            </div>
            
            <!-- Date Picker -->
            <div class="date-picker-section">
                <h4>Or Choose Custom Dates</h4>
                <input type="text" id="dateRangePicker" placeholder="Select dates" readonly 
                    data-init-date-from="<?php echo esc_attr($requested_date_from); ?>" 
                    data-init-date-to="<?php echo esc_attr($requested_date_to); ?>" />
            </div>
            
            <!-- Book Now Button -->
            <button class="btn-book-now" onclick="bookNow()" id="bookNowBtn">
                <span class="btn-main-text">BOOK NOW</span>
                <span class="btn-sub-text" id="depositText">Loading...</span>
            </button>
            
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
    
    <!-- Weekly Price Carousel (FULL WIDTH BELOW IMAGES) -->
    <?php if (!empty($prices)): ?>
    <div class="yacht-price-carousel-section">
        <h3>Peak Season Pricing (May - September)</h3>
        <div class="price-carousel-container" 
             data-init-date-from="<?php echo esc_attr($requested_date_from); ?>"
             data-init-date-to="<?php echo esc_attr($requested_date_to); ?>"
             data-visible-slides="4">
            <div class="price-carousel-slides">
                <?php foreach ($prices as $index => $price): 
                    $discount_amount = $price->start_price - $price->price;
                    $week_start = date('M j', strtotime($price->date_from));
                    $week_end = date('M j, Y', strtotime($price->date_to));
                ?>
                    <div class="price-slide <?php echo $index === 0 ? 'active' : ''; ?>" 
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
                                <?php echo number_format($price->discount_percentage, 2, '.', ','); ?>% OFF - Save <?php echo number_format($discount_amount, 2, '.', ','); ?> <?php echo esc_html($price->currency); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="price-final">
                            <?php echo number_format($price->price, 2, '.', ','); ?> <?php echo esc_html($price->currency); ?>
                        </div>
                        
                        <button class="price-select-btn" onclick="selectWeek(this)">Select This Week</button>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($prices) > 1): ?>
                <button class="price-carousel-prev" onclick="priceCarousel.scrollPrev()">‹</button>
                <button class="price-carousel-next" onclick="priceCarousel.scrollNext()">›</button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
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
    
    <!-- Description Section -->
    <?php if (!empty($yacht->description)): ?>
    <?php
        // Split description into paragraphs
        $paragraphs = array_filter(explode("\n", $yacht->description));
        $preview_paragraphs = array_slice($paragraphs, 0, 2); // First 2 paragraphs
        $remaining_paragraphs = array_slice($paragraphs, 2); // Rest
        $has_more = count($remaining_paragraphs) > 0;
    ?>
    <div class="yacht-description-section">
        <h3>Description</h3>
        <div class="yacht-description-content">
            <div class="description-preview">
                <?php echo nl2br(esc_html(implode("\n", $preview_paragraphs))); ?>
            </div>
            <?php if ($has_more): ?>
                <div class="description-full" style="display: none;">
                    <?php echo nl2br(esc_html(implode("\n", $remaining_paragraphs))); ?>
                </div>
                <button class="description-toggle" onclick="toggleDescription(this)">
                    <span class="toggle-more">More...</span>
                    <span class="toggle-less" style="display: none;">Less</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Equipment Section -->
    <?php if (!empty($equipment)): ?>
    <?php
    // Load equipment icon mapping
    // Use the plugin directory constant to avoid incorrect paths when computing directory names.
    if (defined('YOLO_YS_PLUGIN_DIR')) {
        require_once YOLO_YS_PLUGIN_DIR . 'includes/equipment-icons.php';
    } else {
        // Fallback: traverse up two levels from this template and include the icons file
        require_once dirname(__DIR__, 2) . '/includes/equipment-icons.php';
    }
    ?>
    <!-- Load FontAwesome 7 Kit (always loaded for equipment icons) -->
    <script src="https://kit.fontawesome.com/5514c118d3.js" crossorigin="anonymous"></script>
    
    <div class="yacht-equipment-section">
        <h3>Equipment</h3>
        <div class="equipment-grid">
            <?php foreach ($equipment as $item): ?>
                <?php $icon_class = yolo_get_equipment_icon($item->equipment_id, $item->equipment_name); ?>
                <div class="equipment-item">
                    <i class="<?php echo esc_attr($icon_class); ?>" style="margin-right: 8px;"></i>
                    <span><?php echo esc_html($item->equipment_name); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Location Section -->
    <?php if ($yacht->home_base): ?>
    <div class="yacht-map-section">
        <h3>Location</h3>
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed/v1/place?key=<?php echo esc_attr(get_option('yolo_ys_google_maps_api_key', 'AIzaSyBFw0Qbyq9zTFTd-tUY6dZWTgaQzuU17R8')); ?>&q=<?php echo urlencode($yacht->home_base . ', Greece'); ?>&zoom=12"
                style="width: 100%; height: 400px; border: 0; border-radius: 8px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Technical Characteristics -->
    <div class="yacht-technical">
        <h3>Technical Characteristics</h3>
        <div class="tech-grid">
            <?php if ($yacht->draft): ?>
                <div class="tech-item">
                    <div class="tech-label">DRAUGHT</div>
                    <div class="tech-value"><?php echo $draft_ft; ?> ft (<?php echo number_format($yacht->draft, 2); ?> m)</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->beam): ?>
                <div class="tech-item">
                    <div class="tech-label">BEAM</div>
                    <div class="tech-value"><?php echo $beam_ft; ?> ft (<?php echo number_format($yacht->beam, 2); ?> m)</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->berths): ?>
                <div class="tech-item">
                    <div class="tech-label">BERTHS</div>
                    <div class="tech-value"><?php echo esc_html($yacht->berths); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->engine_power): ?>
                <div class="tech-item">
                    <div class="tech-label">ENGINE</div>
                    <div class="tech-value"><?php echo esc_html($yacht->engine_power); ?> HP</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->fuel_capacity): ?>
                <div class="tech-item">
                    <div class="tech-label">FUEL CAPACITY</div>
                    <div class="tech-value"><?php echo esc_html($yacht->fuel_capacity); ?> L</div>
                </div>
            <?php endif; ?>
            
            <?php if ($yacht->water_capacity): ?>
                <div class="tech-item">
                    <div class="tech-label">WATER CAPACITY</div>
                    <div class="tech-value"><?php echo esc_html($yacht->water_capacity); ?> L</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Cancellation Policy -->
    <div class="yacht-cancellation-policy">
        <h3>Cancellation Policy</h3>
        <div class="policy-content">
            <div class="policy-item">
                <div class="policy-icon">⚠️</div>
                <div class="policy-text">
                    <strong>More than 60 days before charter:</strong> Full refund minus 20% administrative fee
                </div>
            </div>
            <div class="policy-item">
                <div class="policy-icon">⚠️</div>
                <div class="policy-text">
                    <strong>30-60 days before charter:</strong> 50% of total charter fee refunded
                </div>
            </div>
            <div class="policy-item">
                <div class="policy-icon">❌</div>
                <div class="policy-text">
                    <strong>Less than 30 days before charter:</strong> No refund
                </div>
            </div>
            <div class="policy-note">
                <p><strong>Note:</strong> Cancellation must be made in writing. We strongly recommend purchasing travel insurance to cover unexpected cancellations.</p>
            </div>
        </div>
    </div>
    
    <!-- Security Deposit Section -->
    <?php if (!empty($yacht->deposit)): ?>
    <div class="yacht-section deposit-section">
        <h3>🔒 Security Deposit</h3>
        <div class="deposit-content">
            <div class="deposit-amount">
                <span class="amount-value"><?php echo number_format($yacht->deposit, 0); ?> €</span>
            </div>
            <div class="deposit-info">
                <p>A security deposit of <strong><?php echo number_format($yacht->deposit, 0); ?> €</strong> is required at check-in. This deposit is held as a guarantee against any damage to the yacht or its equipment during your charter period.</p>
                <p>The deposit will be fully refunded after check-out, provided the yacht is returned in the same condition as received, with no damages or missing equipment.</p>
                <p class="deposit-note"><strong>Payment method:</strong> The deposit is typically held via credit card authorization or bank transfer. Please confirm the exact payment method with the charter company before your departure.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Check-in / Check-out Section -->
    <div class="yacht-section checkin-section">
        <h3>⏰ Check-in & Check-out</h3>
        <div class="checkin-content">
            <div class="checkin-grid">
                <?php if (!empty($yacht->checkin_time)): ?>
                <div class="checkin-item">
                    <div class="checkin-icon">✅</div>
                    <div class="checkin-details">
                        <div class="checkin-label">Check-in Time</div>
                        <div class="checkin-value"><?php echo esc_html($yacht->checkin_time); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($yacht->checkout_time)): ?>
                <div class="checkin-item">
                    <div class="checkin-icon">❌</div>
                    <div class="checkin-details">
                        <div class="checkin-label">Check-out Time</div>
                        <div class="checkin-value"><?php echo esc_html($yacht->checkout_time); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if (!empty($yacht->checkin_day)): ?>
                <?php 
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $day_name = isset($days[$yacht->checkin_day - 1]) ? $days[$yacht->checkin_day - 1] : '';
                ?>
                <div class="checkin-item">
                    <div class="checkin-icon">📅</div>
                    <div class="checkin-details">
                        <div class="checkin-label">Check-in Day</div>
                        <div class="checkin-value"><?php echo esc_html($day_name); ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <div class="checkin-info">
                <p>Please arrive at the marina at the specified check-in time. Late arrivals should be communicated to the charter company in advance. Early check-in may be available upon request and subject to availability.</p>
            </div>
        </div>
    </div>
    
    <!-- Charter Company Section -->
    <div class="yacht-section company-section">
        <h3>⚓ Charter Company</h3>
        <div class="company-content">
            <div class="company-info">
                <?php if ($yacht->company_id == 7850): ?>
                    <div class="company-name">YOLO Charters</div>
                    <div class="company-badge yolo-badge">⭐ Our Fleet</div>
                    <p>This yacht is part of the YOLO Charters premium fleet. You'll receive direct service from our experienced team, ensuring the highest standards of quality and customer care.</p>
                <?php else: ?>
                    <div class="company-name">Partner Charter Company</div>
                    <div class="company-badge partner-badge">🤝 Trusted Partner</div>
                    <p>This yacht is operated by one of our carefully selected partner companies. All our partners meet strict quality standards and are fully licensed and insured.</p>
                <?php endif; ?>
                <div class="company-details">
                    <p><strong>Base Location:</strong> <?php echo !empty($yacht->home_base) ? esc_html($yacht->home_base) : 'Contact for details'; ?></p>
                    <p><strong>Company ID:</strong> #<?php echo esc_html($yacht->company_id); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Extras -->
    <?php if (!empty($extras)): ?>
        <?php 
        // Separate obligatory and optional extras
        $obligatory_extras = array_filter($extras, function($e) { return $e->obligatory == 1; });
        $optional_extras = array_filter($extras, function($e) { return $e->obligatory == 0; });
        ?>
        
        <div class="yacht-extras-combined">
            <h3>Extras <span class="extras-note">(Payable at the base)</span></h3>
            <div class="extras-two-column">
                <?php if (!empty($obligatory_extras)): ?>
                <div class="extras-column">
                    <h4>Obligatory Extras</h4>
                    <div class="extras-grid">
                        <?php foreach ($obligatory_extras as $extra): ?>
                            <div class="extra-item obligatory">
                                <div class="extra-name"><?php echo esc_html($extra->name); ?></div>
                                <?php if ($extra->price > 0): ?>
                                    <div class="extra-price">
                                        <?php echo number_format($extra->price, 2); ?> <?php echo esc_html($extra->currency); ?>
                                        <?php if (!empty($extra->unit)): ?>
                                            <span class="price-unit">(<?php echo esc_html($extra->unit); ?>)</span>
                                        <?php endif; ?>
                                        <?php if (!empty($extra->payableInBase)): ?>
                                            <span class="payment-location">(Payable at the base)</span>
                                        <?php else: ?>
                                            <span class="payment-location">(Included in price)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($optional_extras)): ?>
                <div class="extras-column">
                    <h4>Optional Extras</h4>
                    <div class="extras-grid">
                        <?php foreach ($optional_extras as $extra): ?>
                            <div class="extra-item optional">
                                <div class="extra-name"><?php echo esc_html($extra->name); ?></div>
                                <?php if ($extra->price > 0): ?>
                                    <div class="extra-price">
                                        <?php echo number_format($extra->price, 2); ?> <?php echo esc_html($extra->currency); ?>
                                        <?php if (!empty($extra->unit)): ?>
                                            <span class="price-unit">(<?php echo esc_html($extra->unit); ?>)</span>
                                        <?php endif; ?>
                                        <?php if (!empty($extra->payableInBase)): ?>
                                            <span class="payment-location">(Payable at the base)</span>
                                        <?php else: ?>
                                            <span class="payment-location">(Included in price)</span>
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
    
</div>

<!-- Google Maps now using iframe embed -->

<?php include dirname(__FILE__) . '/partials/yacht-details-v3-styles.php'; ?>
<?php include dirname(__FILE__) . '/partials/yacht-details-v3-scripts.php'; ?>
