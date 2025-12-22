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

// Get yacht from URL - support both slug (pretty URL) and legacy yacht_id
$yacht_slug = get_query_var('yacht_slug', '');
$yacht_id = isset($_GET['yacht_id']) ? sanitize_text_field($_GET['yacht_id']) : '';

// If we have a slug, look up the yacht ID
if (!empty($yacht_slug) && empty($yacht_id)) {
    global $wpdb;
    $yacht_row = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}yolo_yachts WHERE slug = %s",
        $yacht_slug
    ));
    if ($yacht_row) {
        $yacht_id = $yacht_row->id;
    }
}

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
// FIX: Output buffering catches any stray PHP warnings that could corrupt JavaScript
$fb_event_id = '';
if (function_exists('yolo_analytics')) {
    ob_start();
    try {
        // v75.11: Get starting_from_price from yacht custom settings (all boats)
        $yacht_price = 0;
        global $wpdb;
        $custom_settings_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        $starting_price = $wpdb->get_var($wpdb->prepare(
            "SELECT starting_from_price FROM $custom_settings_table WHERE yacht_id = %s",
            $yacht_id
        ));
        $yacht_price = $starting_price ? floatval($starting_price) : 0;
        $yacht_name_for_tracking = !empty($yacht->name) ? $yacht->name : '';
        $fb_event_id = @yolo_analytics()->track_yacht_view($yacht_id, $yacht_price, $yacht_name_for_tracking);
    } catch (Exception $e) {
        $fb_event_id = '';
    }
    ob_end_clean();
}
if (!is_string($fb_event_id) || empty($fb_event_id)) {
    $fb_event_id = '';
}

// Get images - Check for custom media first (v65.16)
$custom_settings_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
$custom_media_table = $wpdb->prefix . 'yolo_yacht_custom_media';

$custom_settings = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $custom_settings_table WHERE yacht_id = %s",
    $yacht_id
));

$use_custom_media = $custom_settings && $custom_settings->use_custom_media;
$use_custom_description = $custom_settings && $custom_settings->use_custom_description;

if ($use_custom_media) {
    // Use custom media (images + videos)
    $media_items = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $custom_media_table WHERE yacht_id = %s ORDER BY sort_order ASC",
        $yacht_id
    ));
    $images = array();
    $videos = array();
    foreach ($media_items as $item) {
        if ($item->media_type === 'video') {
            $videos[] = $item;
        } else {
            // Convert to image format for compatibility
            $img = new stdClass();
            $img->image_url = $item->media_url;
            $img->thumbnail_url = $item->thumbnail_url;
            $images[] = $img;
        }
    }
} else {
    // Use synced images (default behavior)
    $images_table = $wpdb->prefix . 'yolo_yacht_images';
    $images = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $images_table WHERE yacht_id = %s ORDER BY sort_order ASC",
        $yacht_id
    ));
    $videos = array();
}

// Get custom description if enabled
$display_description = $yacht->description;
if ($use_custom_description && !empty($custom_settings->custom_description)) {
    $display_description = $custom_settings->custom_description;
}

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

// Default to first available future week if no dates provided (v72.7: changed from July-only)
if (empty($requested_date_from) || empty($requested_date_to)) {
    if (!empty($prices)) {
        // Simply use the first available price (already sorted by date)
        $requested_date_from = substr($prices[0]->date_from, 0, 10);
        $requested_date_to = substr($prices[0]->date_to, 0, 10);
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
            
            <!-- Image & Video Carousel - Swiper (v30.1, updated v65.16 for videos) -->
            <div class="yacht-images-carousel">
                <?php if (!empty($images) || !empty($videos)): ?>
                    <div class="swiper yacht-image-swiper">
                        <div class="swiper-wrapper">
                            <?php 
                            // If using custom media, interleave based on sort order
                            if ($use_custom_media && !empty($media_items)): 
                                foreach ($media_items as $index => $item): 
                                    if ($item->media_type === 'video'):
                                        // Check if it's a YouTube video (short ID) or full URL
                                        $video_id = $item->media_url;
                                        if (strlen($video_id) === 11 && !strpos($video_id, '.')) {
                                            // It's a YouTube video ID
                            ?>
                                <div class="swiper-slide swiper-slide-video" data-video-id="<?php echo esc_attr($video_id); ?>">
                                    <div class="video-thumbnail-wrapper" style="position: relative; cursor: pointer;">
                                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/maxresdefault.jpg" 
                                             alt="<?php echo esc_attr($yacht->name); ?> Video"
                                             onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr($video_id); ?>/hqdefault.jpg'">
                                        <div class="video-play-button" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                                             background: rgba(0,0,0,0.7); color: #fff; width: 80px; height: 80px; border-radius: 50%; 
                                             display: flex; align-items: center; justify-content: center; font-size: 30px;">
                                            <i class="fa-solid fa-play" style="margin-left: 5px;"></i>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                        } else {
                                            // It's a direct video URL (uploaded file)
                            ?>
                                <div class="swiper-slide swiper-slide-video-file">
                                    <video controls style="width: 100%; height: 100%; object-fit: cover;">
                                        <source src="<?php echo esc_url($item->media_url); ?>" type="video/mp4">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php 
                                        }
                                    else: 
                            ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo esc_url($item->media_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                                </div>
                            <?php 
                                    endif;
                                endforeach; 
                            else:
                                // Default behavior - just images
                                foreach ($images as $index => $image): 
                            ?>
                                <div class="swiper-slide">
                                    <img src="<?php echo esc_url($image->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                                </div>
                            <?php 
                                endforeach;
                            endif; 
                            ?>
                        </div>
                        
                        <?php 
                        $total_media = $use_custom_media ? count($media_items) : count($images);
                        if ($total_media > 1): 
                        ?>
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
            
            <!-- Description Section (v65.17 - supports more tag for custom descriptions) -->
            
            <?php if (!empty($display_description)): ?>
            <?php
                // Check for <!-- more --> tag in custom descriptions (v65.17)
                $has_more = false;
                $preview_content = '';
                $full_content = '';
                
                if ($use_custom_description && strpos($display_description, '<!-- more -->') !== false) {
                    // Split by <!-- more --> tag
                    $parts = explode('<!-- more -->', $display_description, 2);
                    $preview_content = trim($parts[0]);
                    $full_content = isset($parts[1]) ? trim($parts[1]) : '';
                    $has_more = !empty($full_content);
                } elseif ($use_custom_description) {
                    // Custom description without <!-- more --> tag - show all
                    $preview_content = $display_description;
                    $has_more = false;
                } else {
                    // Synced description - use paragraph-based split
                    $paragraphs = array_filter(explode("\n", $display_description));
                    $preview_paragraphs = array_slice($paragraphs, 0, 2);
                    $remaining_paragraphs = array_slice($paragraphs, 2);
                    $preview_content = implode("\n", $preview_paragraphs);
                    $full_content = implode("\n", $remaining_paragraphs);
                    $has_more = count($remaining_paragraphs) > 0;
                }
            ?>
            <div class="yacht-description-section">
                <h3><i class="fa-solid fa-info-circle"></i> Description</h3>
                <div class="yacht-description-content">
                    <div class="description-preview">
                        <?php 
                        if ($use_custom_description) {
                            echo wp_kses_post($preview_content);
                        } else {
                            echo nl2br(esc_html($preview_content));
                        }
                        ?>
                    </div>
                    <?php if ($has_more): ?>
                        <div class="description-full" style="display: none;">
                            <?php 
                            if ($use_custom_description) {
                                echo wp_kses_post($full_content);
                            } else {
                                echo nl2br(esc_html($full_content));
                            }
                            ?>
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
            <div class="yacht-equipment-section yolo-accordion-section">
                <h3 onclick="toggleAccordion(this)"><?php echo yolo_ys_get_icon('section', 'Equipment', 'fa-solid fa-ship'); ?> <?php yolo_ys_text_e('equipment', 'Equipment'); ?><span class="yolo-accordion-toggle"><i class="fa-solid fa-chevron-down"></i></span></h3>
                <div class="yolo-accordion-content">
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
                </div><!-- end accordion-content -->
            </div>
            <?php endif; ?>
            
            <!-- Location Map -->
            <?php if ($yacht->home_base): ?>
            <div id="yacht-map-section" class="yacht-map-section">
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
            <div class="yacht-technical yolo-accordion-section">
                <h3 onclick="toggleAccordion(this)"><?php echo yolo_ys_get_icon('section', 'Technical Characteristics', 'fa-solid fa-cogs'); ?> <?php yolo_ys_text_e('technical_specs', 'Technical Characteristics'); ?><span class="yolo-accordion-toggle"><i class="fa-solid fa-chevron-down"></i></span></h3>
                <div class="yolo-accordion-content">
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
                </div><!-- end accordion-content -->
            </div>
            
            <!-- Extras -->
            <?php if (!empty($extras)): ?>
                <?php 
                $obligatory_extras = array_filter($extras, function($e) { return $e->obligatory == 1; });
                $optional_extras = array_filter($extras, function($e) { return $e->obligatory == 0; });
                ?>
                
                <div class="yacht-extras-combined yolo-accordion-section">
                    <h3 onclick="toggleAccordion(this)"><i class="fa-solid fa-plus-circle"></i> <?php yolo_ys_text_e('extras', 'Extras'); ?> <span class="extras-note"><?php yolo_ys_text_e('payable_at_base', '(Payable at the base)'); ?></span><span class="yolo-accordion-toggle"><i class="fa-solid fa-chevron-down"></i></span></h3>
                    <div class="yolo-accordion-content">
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
                            
                            <?php 
                            // Show "What's Included" section for YOLO boats with obligatory extras
                            $my_company_id = get_option('yolo_ys_my_company_id', '7850');
                            if (isset($yacht->company_id) && $yacht->company_id == $my_company_id): 
                            ?>
                            <!-- What's Included in Your Deluxe Charter Pack -->
                            <div class="charter-pack-included mt-3">
                                <h5 class="charter-pack-title">
                                    <i class="fa-solid fa-gift"></i>
                                    <?php echo esc_html(get_option('yolo_ys_text_charter_pack_title', "What's Included in Your Deluxe Charter Pack")); ?>
                                </h5>
                                <div class="charter-pack-items">
                                    <!-- Transit Log -->
                                    <div class="charter-pack-item">
                                        <div class="pack-item-icon"><i class="fa-solid fa-file-lines"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Transit Log</h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_transit_log', 'Official cruising permit required for sailing in Greek waters. We handle all the paperwork so you can focus on your adventure.')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Check-in/Check-out -->
                                    <div class="charter-pack-item">
                                        <div class="pack-item-icon"><i class="fa-solid fa-clipboard-check"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Professional Check-in/Check-out</h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_checkin', 'Our experienced staff will guide you through the yacht, explain all systems, and ensure a smooth handover.')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Final Cleaning -->
                                    <div class="charter-pack-item">
                                        <div class="pack-item-icon"><i class="fa-solid fa-broom"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Final Cleaning</h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_cleaning', 'Return the yacht without worrying about cleaning. Our team will take care of everything after your charter.')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Bed Linen & Towels -->
                                    <div class="charter-pack-item">
                                        <div class="pack-item-icon"><i class="fa-solid fa-bed"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Bed Linen & Bath Towels</h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_linen', 'Fresh, high-quality linens and towels for all guests. Everything you need for a comfortable stay onboard.')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Gas Bottle -->
                                    <div class="charter-pack-item">
                                        <div class="pack-item-icon"><i class="fa-solid fa-fire-flame-simple"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Gas Bottle</h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_gas', 'Full gas bottle for cooking. Prepare delicious meals in the galley throughout your charter.')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Dinghy with Outboard -->
                                    <div class="charter-pack-item">
                                        <div class="pack-item-icon"><i class="fa-solid fa-anchor"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Dinghy with Outboard Engine</h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_dinghy', 'Explore secluded beaches and coves with the included tender. Perfect for shore excursions and provisioning runs.')); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Free SUP -->
                                    <div class="charter-pack-item charter-pack-item-free">
                                        <div class="pack-item-icon"><i class="fa-solid fa-person-swimming"></i></div>
                                        <div class="pack-item-content">
                                            <h6>Stand Up Paddle Board (SUP) <span class="free-badge">FREE</span></h6>
                                            <p><?php echo esc_html(get_option('yolo_ys_text_charter_pack_sup', 'Glide across crystal-clear Greek waters on our complimentary SUP board. Great for exercise and exploring!')); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
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
                    </div><!-- end accordion-content -->
                </div>
            <?php endif; ?>
            
            <!-- Security Deposit Section -->
            <?php if (!empty($yacht->deposit)): ?>
            <div class="yacht-section deposit-section yolo-accordion-section">
                <h3 onclick="toggleAccordion(this)"><?php echo yolo_ys_get_icon('section', 'Security Deposit', 'fa-solid fa-shield-halved'); ?> <?php yolo_ys_text_e('security_deposit', 'Security Deposit'); ?><span class="yolo-accordion-toggle"><i class="fa-solid fa-chevron-down"></i></span></h3>
                <div class="yolo-accordion-content">
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
                </div><!-- end accordion-content -->
            </div>
            <?php endif; ?>
            
            <!-- Cancellation Policy Section -->
            <div class="yacht-section cancellation-section yolo-accordion-section">
                <h3 onclick="toggleAccordion(this)"><?php echo yolo_ys_get_icon('section', 'Cancellation Policy', 'fa-solid fa-calendar-xmark'); ?> <?php yolo_ys_text_e('cancellation_policy', 'Cancellation Policy'); ?><span class="yolo-accordion-toggle"><i class="fa-solid fa-chevron-down"></i></span></h3>
                <div class="yolo-accordion-content">
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
                </div><!-- end accordion-content -->
            </div>
            
            <!-- Check-in / Check-out Section -->
            <div class="yacht-section checkin-section yolo-accordion-section">
                <h3 onclick="toggleAccordion(this)"><i class="fa-solid fa-clock"></i> Check-in & Check-out<span class="yolo-accordion-toggle"><i class="fa-solid fa-chevron-down"></i></span></h3>
                <div class="yolo-accordion-content">
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
                </div><!-- end accordion-content -->
            </div>
            
                </div>
            </div>
            <!-- END MAIN CONTENT -->
            
            <!-- ============================================ -->
            <!-- BOOKING SIDEBAR (RIGHT - STICKY - 4 cols desktop) -->
            <!-- ============================================ -->
            <div class="col-12 col-lg-4">
            <div class="yacht-booking-section" id="yacht-booking-section">
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
                
                <?php
                // Payment Icons Box (v75.19 - supports custom icons)
                $show_payment_icons = get_option('yolo_ys_payment_icons_show_box', '1');
                if ($show_payment_icons === '1') {
                    $payment_icons_title = get_option('yolo_ys_payment_icons_title', 'We accept');
                    $enabled_icons = get_option('yolo_ys_payment_icons_enabled', array('visa', 'mastercard', 'amex', 'paypal', 'apple-pay', 'google-pay', 'klarna', 'revolut'));
                    $icon_order = get_option('yolo_ys_payment_icons_order', '');
                    $visible_count = intval(get_option('yolo_ys_payment_icons_visible_count', 8));
                    $show_more_text = get_option('yolo_ys_payment_icons_show_more_text', '+ %d more payment methods');
                    $show_less_text = get_option('yolo_ys_payment_icons_show_less_text', 'Show less');
                    
                    // Define built-in icons (file path relative to plugin)
                    $builtin_icons = array(
                        'visa' => array('file' => 'visa.svg', 'is_custom' => false),
                        'mastercard' => array('file' => 'mastercard.svg', 'is_custom' => false),
                        'amex' => array('file' => 'amex.svg', 'is_custom' => false),
                        'discover' => array('file' => 'discover.svg', 'is_custom' => false),
                        'paypal' => array('file' => 'paypal.svg', 'is_custom' => false),
                        'apple-pay' => array('file' => 'apple-pay.svg', 'is_custom' => false),
                        'google-pay' => array('file' => 'google-pay.svg', 'is_custom' => false),
                        'klarna' => array('file' => 'klarna.svg', 'is_custom' => false),
                        'revolut' => array('file' => 'revolut.svg', 'is_custom' => false),
                        'samsung-pay' => array('file' => 'samsung-pay.svg', 'is_custom' => false),
                        'link' => array('file' => 'link.svg', 'is_custom' => false),
                        'bancontact' => array('file' => 'bancontact.svg', 'is_custom' => false),
                        'blik' => array('file' => 'blik.svg', 'is_custom' => false),
                        'eps' => array('file' => 'eps.svg', 'is_custom' => false),
                        'mbway' => array('file' => 'mbway.svg', 'is_custom' => false),
                        'mobilepay' => array('file' => 'mobilepay.svg', 'is_custom' => false),
                        'kakaopay' => array('file' => 'kakaopay.svg', 'is_custom' => false),
                        'naverpay' => array('file' => 'naverpay.svg', 'is_custom' => false),
                        'payco' => array('file' => 'payco.svg', 'is_custom' => false),
                        'satispay' => array('file' => 'satispay.svg', 'is_custom' => false),
                        'stripe' => array('file' => 'stripe.svg', 'is_custom' => false)
                    );
                    
                    // Get custom uploaded icons
                    $custom_icons = get_option('yolo_ys_payment_icons_custom', array());
                    
                    // Filter out hidden built-in icons
                    $hidden_icons = get_option('yolo_ys_payment_icons_hidden', array());
                    foreach ($hidden_icons as $hidden_id) {
                        unset($builtin_icons[$hidden_id]);
                    }
                    
                    // Merge all icons
                    $all_icons = array_merge($builtin_icons, $custom_icons);
                    
                    // Sort by order and filter enabled
                    $ordered_icons = array();
                    if (!empty($icon_order)) {
                        foreach (explode(',', $icon_order) as $icon_id) {
                            if (isset($all_icons[$icon_id]) && in_array($icon_id, $enabled_icons)) {
                                $ordered_icons[$icon_id] = $all_icons[$icon_id];
                            }
                        }
                    }
                    // Add any enabled icons not in order
                    foreach ($enabled_icons as $icon_id) {
                        if (isset($all_icons[$icon_id]) && !isset($ordered_icons[$icon_id])) {
                            $ordered_icons[$icon_id] = $all_icons[$icon_id];
                        }
                    }
                    
                    $total_icons = count($ordered_icons);
                    $remaining = $total_icons - $visible_count;
                    $icons_url = plugins_url('public/images/payment-icons/', dirname(dirname(__FILE__)));
                    
                    if ($total_icons > 0):
                ?>
                <!-- Payment Icons Box (v75.19) -->
                <div class="payment-icons-box">
                    <div class="payment-icons-title"><?php echo esc_html($payment_icons_title); ?></div>
                    <div class="payment-icons-grid" id="paymentIconsGrid">
                        <?php 
                        $count = 0;
                        foreach ($ordered_icons as $icon_id => $icon_data):
                            $hidden_class = ($count >= $visible_count) ? 'payment-icon-hidden' : '';
                            // Custom icons have full URL, built-in icons need base URL prepended
                            $is_custom = isset($icon_data['is_custom']) && $icon_data['is_custom'];
                            $icon_src = $is_custom ? $icon_data['file'] : $icons_url . $icon_data['file'];
                        ?>
                            <img src="<?php echo esc_url($icon_src); ?>" 
                                 alt="<?php echo esc_attr($icon_id); ?>" 
                                 class="payment-icon <?php echo $hidden_class; ?>"
                                 loading="lazy">
                        <?php 
                            $count++;
                        endforeach; 
                        ?>
                    </div>
                    <?php if ($remaining > 0): ?>
                    <div class="payment-icons-toggle">
                        <a href="#" id="paymentIconsToggle" 
                           data-show-text="<?php echo esc_attr(sprintf($show_more_text, $remaining)); ?>"
                           data-hide-text="<?php echo esc_attr($show_less_text); ?>">
                            <?php echo esc_html(sprintf($show_more_text, $remaining)); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <?php 
                    endif;
                }
                ?>
                
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

<!-- Mobile Sticky Bottom Bar (v75.13) -->
<div class="yolo-mobile-sticky-bar" id="mobileStickBar">
    <button type="button" class="yolo-sticky-cta-btn" onclick="scrollToBookingSection()">
        <i class="fa-solid fa-calendar-check"></i> CHECK AVAILABILITY
    </button>
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

