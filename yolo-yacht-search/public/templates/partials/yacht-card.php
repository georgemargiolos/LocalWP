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
    $refit_display = 'Refit: ' . $yacht->refit_year;
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
        
        <!-- Specs Grid (matches yolo-charters.com exactly) -->
        <div class="yolo-ys-yacht-specs-grid">
            <div class="yolo-ys-spec-item">
                <div class="yolo-ys-spec-value"><?php echo esc_html($yacht->cabins); ?></div>
                <div class="yolo-ys-spec-label">Cabins</div>
            </div>
            
            <div class="yolo-ys-spec-item">
                <div class="yolo-ys-spec-value">
                    <?php echo esc_html($yacht->year_of_build); ?>
                    <?php if ($refit_display): ?>
                        <span class="yolo-ys-refit-note"><?php echo esc_html($refit_display); ?></span>
                    <?php endif; ?>
                </div>
                <div class="yolo-ys-spec-label">Built year</div>
            </div>
            
            <div class="yolo-ys-spec-item">
                <div class="yolo-ys-spec-value"><?php echo esc_html($length_ft); ?> ft</div>
                <div class="yolo-ys-spec-label">Length</div>
            </div>
        </div>
        
        <?php
        // Get minimum price
        $min_price = YOLO_YS_Database_Prices::get_min_price($yacht->id);
        ?>
        
        <!-- Price -->
        <?php if ($min_price && isset($min_price->min_price)): ?>
        <div class="yolo-ys-yacht-price">
            From <strong><?php echo number_format($min_price->min_price, 0, ',', '.'); ?> <?php echo esc_html($min_price->currency); ?></strong> per week
        </div>
        <?php endif; ?>
        
        <!-- Details Button -->
        <a href="<?php echo esc_url($details_url); ?>" class="yolo-ys-details-btn">
            DETAILS
        </a>
    </div>
</div>

<style>
.yolo-ys-yacht-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
}

.yolo-ys-yacht-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.yolo-ys-yacht-image {
    width: 100%;
    height: 240px;
    overflow: hidden;
    background: #f3f4f6;
    position: relative;
}

.yolo-ys-yacht-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.yolo-ys-yacht-card:hover .yolo-ys-yacht-image img {
    transform: scale(1.05);
}

.yolo-ys-yacht-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    color: #9ca3af;
}

.yolo-ys-yacht-content {
    padding: 20px;
}

.yolo-ys-yacht-location {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 12px;
}

.yolo-ys-yacht-header {
    margin-bottom: 20px;
}

.yolo-ys-yacht-name {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 4px 0;
}

.yolo-ys-yacht-model {
    font-size: 16px;
    font-weight: 600;
    color: #1e3a8a;
    margin: 0;
}

.yolo-ys-yacht-specs-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.yolo-ys-spec-item {
    text-align: center;
}

.yolo-ys-spec-value {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 4px;
}

.yolo-ys-refit-note {
    display: block;
    font-size: 12px;
    font-weight: 400;
    color: #6b7280;
    margin-top: 2px;
}

.yolo-ys-spec-label {
    font-size: 13px;
    color: #6b7280;
}

.yolo-ys-details-btn {
    display: block;
    width: 100%;
    padding: 14px 20px;
    background: #b91c1c;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.yolo-ys-yacht-price {
    font-size: 16px;
    color: #059669;
    margin-bottom: 15px;
    padding: 12px;
    background: #f0fdf4;
    border-radius: 4px;
    text-align: center;
}

.yolo-ys-yacht-price strong {
    font-size: 20px;
    font-weight: 700;
}

.yolo-ys-details-btn {
    display: block;
    width: 100%;
    padding: 14px 20px;
    background: #b91c1c;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 4px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.5px;
    transition: all 0.3s ease;
}

.yolo-ys-details-btn:hover {
    background: #991b1b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(185, 28, 28, 0.3);
    color: white;
}

@media (max-width: 768px) {
    .yolo-ys-yacht-image {
        height: 200px;
    }
    
    .yolo-ys-yacht-specs-grid {
        gap: 10px;
    }
    
    .yolo-ys-spec-value {
        font-size: 16px;
    }
}
</style>
