<?php
/**
 * Yacht Details Template
 * Displays complete yacht information matching yolo-charters.com design
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
// v91.20: Order by is_primary DESC first to ensure primary image is first
$table_images = $wpdb->prefix . 'yolo_yacht_images';
$images = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_images WHERE yacht_id = %s ORDER BY is_primary DESC, sort_order ASC",
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
    "SELECT equipment_name FROM $table_equipment WHERE yacht_id = %s",
    $yacht_id
));

// Convert meters to feet
$length_ft = $yacht->length ? round($yacht->length * 3.28084) : 0;
$beam_ft = $yacht->beam ? round($yacht->beam * 3.28084) : 0;
$draft_ft = $yacht->draft ? round($yacht->draft * 3.28084) : 0;

// Get fleet page URL for back button
$fleet_page_id = get_option('yolo_ys_results_page', '');
$back_url = $fleet_page_id ? get_permalink($fleet_page_id) : home_url('/');
?>

<div class="yolo-ys-yacht-details">

    <!-- Hero Section -->
    <div class="yolo-ys-hero">
        <h1 class="yolo-ys-yacht-title"><?php echo esc_html(strtoupper($yacht->name)); ?></h1>
        <h2 class="yolo-ys-yacht-subtitle"><?php echo esc_html(strtoupper($yacht->model)); ?></h2>
    </div>

    <!-- Back Button -->
    <div class="yolo-ys-back-btn-container">
        <a href="<?php echo esc_url($back_url); ?>" class="yolo-ys-back-btn">
            ← Back
        </a>
    </div>

    <!-- Image Gallery -->
    <div class="yolo-ys-gallery-container">
        <?php if (!empty($images)): ?>
            <div class="yolo-ys-main-image">
                <img src="<?php echo esc_url($images[0]->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
            </div>

            <?php if (count($images) > 1): ?>
                <div class="yolo-ys-thumbnails">
                    <?php for ($i = 1; $i < min(3, count($images)); $i++): ?>
                        <div class="yolo-ys-thumbnail">
                            <img src="<?php echo esc_url($images[$i]->image_url); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
                        </div>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Location -->
    <?php if ($yacht->home_base): ?>
        <div class="yolo-ys-location">
            📍 <?php echo esc_html($yacht->home_base); ?>
        </div>
    <?php endif; ?>

    <!-- Quick Specs Grid -->
    <div class="yolo-ys-quick-specs">
        <div class="yolo-ys-spec-box">
            <div class="yolo-ys-spec-icon">📏</div>
            <div class="yolo-ys-spec-label">Length</div>
            <div class="yolo-ys-spec-value"><?php echo esc_html($length_ft); ?> ft</div>
        </div>

        <div class="yolo-ys-spec-box">
            <div class="yolo-ys-spec-icon">🛏️</div>
            <div class="yolo-ys-spec-label">Cabins</div>
            <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->cabins); ?></div>
        </div>

        <div class="yolo-ys-spec-box">
            <div class="yolo-ys-spec-icon">⏳</div>
            <div class="yolo-ys-spec-label">Year</div>
            <div class="yolo-ys-spec-value">
                <?php echo esc_html($yacht->year_of_build); ?>
                <?php if ($yacht->refit_year): ?>
                    <span class="yolo-ys-refit-small">Refit: <?php echo esc_html($yacht->refit_year); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <div class="yolo-ys-spec-box">
            <div class="yolo-ys-spec-icon">🚽</div>
            <div class="yolo-ys-spec-label">Head</div>
            <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->wc); ?></div>
        </div>
    </div>

    <!-- Technical Characteristics -->
    <div class="yolo-ys-section">
        <h3 class="yolo-ys-section-title">Technical characteristics</h3>

        <div class="yolo-ys-tech-grid">
            <div class="yolo-ys-tech-item">
                <span class="yolo-ys-tech-label">DRAUGHT:</span>
                <span class="yolo-ys-tech-value"><?php echo esc_html($draft_ft); ?> ft</span>
            </div>

            <div class="yolo-ys-tech-item">
                <span class="yolo-ys-tech-label">ENGINE:</span>
                <span class="yolo-ys-tech-value"><?php echo esc_html($yacht->engine_power); ?> hp</span>
            </div>

            <div class="yolo-ys-tech-item">
                <span class="yolo-ys-tech-label">WATER CAPACITY:</span>
                <span class="yolo-ys-tech-value"><?php echo esc_html($yacht->water_capacity); ?> l</span>
            </div>

            <div class="yolo-ys-tech-item">
                <span class="yolo-ys-tech-label">BEAM:</span>
                <span class="yolo-ys-tech-value"><?php echo esc_html($beam_ft); ?> ft</span>
            </div>

            <div class="yolo-ys-tech-item">
                <span class="yolo-ys-tech-label">FUEL CAPACITY:</span>
                <span class="yolo-ys-tech-value"><?php echo esc_html($yacht->fuel_capacity); ?> l</span>
            </div>

            <div class="yolo-ys-tech-item">
                <span class="yolo-ys-tech-label">BERTHS:</span>
                <span class="yolo-ys-tech-value"><?php echo esc_html($yacht->berths); ?></span>
            </div>
        </div>
    </div>

    <!-- Equipment -->
    <?php if (!empty($equipment)): ?>
        <div class="yolo-ys-section">
            <h3 class="yolo-ys-section-title">Equipment</h3>
            <div class="yolo-ys-equipment-text">
                <strong>Equipment:</strong>
                <?php
                $equipment_names = array_map(function($item) {
                    return $item->equipment_name;
                }, $equipment);
                echo esc_html(implode(', ', $equipment_names));
                ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Optional Extras -->
    <?php if (!empty($extras)): ?>
        <div class="yolo-ys-section">
            <h3 class="yolo-ys-section-title">Optional extras</h3>
            <div class="yolo-ys-extras-list">
                <?php foreach ($extras as $extra): ?>
                    <div class="yolo-ys-extra-item">
                        <div class="yolo-ys-extra-name">
                            <?php if ($extra->obligatory): ?>
                                <span class="yolo-ys-obligatory">*</span>
                            <?php endif; ?>
                            <?php echo esc_html($extra->name); ?>
                        </div>
                        <div class="yolo-ys-extra-price">
                            <?php if ($extra->price > 0): ?>
                                <span class="yolo-ys-price-amount"><?php echo esc_html(number_format($extra->price, 2)); ?> <?php echo esc_html($extra->currency); ?></span>
                            <?php else: ?>
                                <span class="yolo-ys-price-free">Free</span>
                            <?php endif; ?>
                            <?php if ($extra->unit): ?>
                                <span class="yolo-ys-price-unit">per <?php echo esc_html($extra->unit); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Description -->
    <?php if ($yacht->description): ?>
        <div class="yolo-ys-section">
            <h3 class="yolo-ys-section-title">Description</h3>
            <div class="yolo-ys-description">
                <?php echo wp_kses_post(nl2br($yacht->description)); ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<style>
.yolo-ys-yacht-details {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: inherit;
}

/* Hero Section */
.yolo-ys-hero {
    text-align: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, #f3e8c8 0%, #e8d5a0 100%);
    border-radius: 0 0 50% 50% / 0 0 20% 20%;
    margin-bottom: 30px;
}

.yolo-ys-yacht-title {
    font-size: 48px;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0 0 10px 0;
    letter-spacing: 2px;
}

.yolo-ys-yacht-subtitle {
    font-size: 36px;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0;
    letter-spacing: 1px;
}

/* Back Button */
.yolo-ys-back-btn-container {
    margin-bottom: 20px;
}

.yolo-ys-back-btn {
    display: inline-block;
    padding: 10px 20px;
    background: #f3f4f6;
    color: #1e3a8a;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.yolo-ys-back-btn:hover {
    background: #e5e7eb;
    color: #1e3a8a;
}

/* Gallery */
.yolo-ys-gallery-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 30px;
}

.yolo-ys-gallery-container > .yolo-ys-main-image {
    flex: 2 1 60%;
    min-width: 300px;
}

.yolo-ys-gallery-container > .yolo-ys-thumbnails {
    flex: 1 1 30%;
    min-width: 150px;
}

.yolo-ys-main-image {
    border-radius: 8px;
    overflow: hidden;
    height: 400px;
}

.yolo-ys-main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.yolo-ys-thumbnails {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.yolo-ys-thumbnail {
    border-radius: 8px;
    overflow: hidden;
    height: calc((400px - 15px) / 2);
}

.yolo-ys-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Location */
.yolo-ys-location {
    font-size: 16px;
    color: #1e3a8a;
    margin-bottom: 30px;
    font-weight: 600;
}

/* Quick Specs */
.yolo-ys-quick-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 40px;
}

.yolo-ys-quick-specs > .yolo-ys-spec-box {
    flex: 1 1 calc(25% - 20px);
    min-width: 120px;
}

.yolo-ys-spec-box {
    background: #f9fafb;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
}

.yolo-ys-spec-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.yolo-ys-spec-label {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 8px;
    text-transform: capitalize;
}

.yolo-ys-spec-value {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
}

.yolo-ys-refit-small {
    display: block;
    font-size: 12px;
    font-weight: 400;
    color: #6b7280;
    margin-top: 4px;
}

/* Sections */
.yolo-ys-section {
    margin-bottom: 40px;
}

.yolo-ys-section-title {
    font-size: 24px;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0 0 20px 0;
}

/* Technical Grid */
.yolo-ys-tech-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

.yolo-ys-tech-grid > .yolo-ys-tech-item {
    flex: 1 1 calc(50% - 20px);
    min-width: 250px;
}

.yolo-ys-tech-item {
    display: flex;
    justify-content: space-between;
    padding: 15px 0;
    border-bottom: 1px solid #e5e7eb;
}

.yolo-ys-tech-label {
    font-size: 14px;
    font-weight: 600;
    color: #1e3a8a;
    text-transform: uppercase;
}

.yolo-ys-tech-value {
    font-size: 14px;
    color: #1f2937;
}

/* Equipment */
.yolo-ys-equipment-text {
    font-size: 15px;
    line-height: 1.8;
    color: #374151;
}

.yolo-ys-equipment-text strong {
    color: #1e3a8a;
}

/* Extras */
.yolo-ys-extras-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.yolo-ys-extra-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 6px;
}

.yolo-ys-extra-name {
    font-size: 15px;
    font-weight: 600;
    color: #1f2937;
}

.yolo-ys-obligatory {
    color: #dc2626;
    margin-right: 5px;
}

.yolo-ys-extra-price {
    text-align: right;
}

.yolo-ys-price-amount {
    font-size: 16px;
    font-weight: 700;
    color: #059669;
}

.yolo-ys-price-free {
    font-size: 14px;
    font-weight: 600;
    color: #059669;
}

.yolo-ys-price-unit {
    display: block;
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

/* Description */
.yolo-ys-description {
    font-size: 15px;
    line-height: 1.8;
    color: #374151;
}

/* Error */
.yolo-ys-error {
    padding: 20px;
    background: #fee2e2;
    border: 1px solid #fecaca;
    border-radius: 6px;
    color: #991b1b;
}

/* Responsive */
@media (max-width: 768px) {
    .yolo-ys-yacht-title {
        font-size: 32px;
    }

    .yolo-ys-yacht-subtitle {
        font-size: 24px;
    }

    .yolo-ys-gallery-container {
        flex-direction: column;
    }

    .yolo-ys-gallery-container > .yolo-ys-main-image,
    .yolo-ys-gallery-container > .yolo-ys-thumbnails {
        flex: 1 1 100%;
    }

    .yolo-ys-thumbnails {
        flex-direction: row;
    }

    .yolo-ys-thumbnail {
        height: 120px;
    }

    .yolo-ys-quick-specs > .yolo-ys-spec-box {
        flex: 1 1 calc(50% - 20px);
    }

    .yolo-ys-tech-grid > .yolo-ys-tech-item {
        flex: 1 1 100%;
    }
}
</style>
