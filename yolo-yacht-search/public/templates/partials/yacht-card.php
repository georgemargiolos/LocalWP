<?php
/**
 * Yacht Card Partial Template
 * Displays a single yacht card (matches yolo-charters.com design)
 *
 * @var object $yacht Yacht object from database
 * @var bool $is_yolo Whether this is a YOLO yacht
 */

$is_yolo = ($yacht->company_id == get_option('yolo_ys_my_company_id', 7850));
$details_page_id = get_option('yolo_ys_yacht_details_page', '');
$details_url = $details_page_id ? add_query_arg('yacht_id', $yacht->id, get_permalink($details_page_id)) : '#';

// Get primary image
$primary_image = '';
if (!empty($yacht->images)) {
    foreach ($yacht->images as $img) {
        if ($img->is_primary) {
            $primary_image = $img->image_url;
            break;
        }
    }
    if (empty($primary_image)) {
        $primary_image = $yacht->images[0]->image_url;
    }
}

// Format refit display
$refit_display = '';
if ($yacht->refit_year) {
    $refit_display = '<strong>Refit: ' . $yacht->refit_year . '</strong>';
}

// Convert meters to feet
$length_ft = $yacht->length ? round($yacht->length * 3.28084) : 0;
?>

<div class="yolo-ys-yacht-card <?php echo $is_yolo ? 'yolo-yacht' : ''; ?>">
    <div class="yolo-ys-yacht-image">
        <?php if ($primary_image): ?>
            <img src="<?php echo esc_url($primary_image); ?>" alt="<?php echo esc_attr($yacht->name); ?>">
        <?php else: ?>
            <div class="yolo-ys-yacht-placeholder">⛵</div>
        <?php endif; ?>
    </div>
    
    <div class="yolo-ys-yacht-content">
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
        
        <!-- Specs Grid - 2 rows: Row 1 (Cabins + Heads), Row 2 (Built Year + Refit + Length) -->
        <div class="yolo-ys-yacht-specs-grid">
            <!-- Row 1: Cabins + Heads -->
            <div class="row g-2 mb-2">
                <div class="col-6 yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->cabins); ?></div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('cabins', 'Cabins'); ?></div>
                </div>
                
                <div class="col-6 yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->wc ?? 'N/A'); ?></div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('wc', 'Heads'); ?></div>
                </div>
            </div>
            
            <!-- Row 2: Built Year + Refit + Length -->
            <div class="row g-2">
                <div class="col-4 yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value">
                        <?php echo esc_html($yacht->year_of_build); ?>
                    </div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('year_built', 'Built year'); ?></div>
                </div>
                
                <div class="col-4 yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value">
                        <?php if ($refit_display): ?>
                            <?php echo $refit_display; ?>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </div>
                    <div class="yolo-ys-spec-label">Refit</div>
                </div>
                
                <div class="col-4 yolo-ys-spec-item">
                    <div class="yolo-ys-spec-value"><?php echo esc_html($length_ft); ?> ft</div>
                    <div class="yolo-ys-spec-label"><?php yolo_ys_text_e('length', 'Length'); ?></div>
                </div>
            </div>
        </div>
        
        <?php
        // Get minimum price
        $min_price = YOLO_YS_Database_Prices::get_min_price($yacht->id);
        ?>
        
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
