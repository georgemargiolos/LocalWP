<?php
/**
 * Yacht Card Partial
 * Displays a single yacht card
 * 
 * Variables available:
 * $yacht - yacht object from database
 */

// Get primary image
$primary_image = null;
if (!empty($yacht->images)) {
    foreach ($yacht->images as $img) {
        if ($img->is_primary) {
            $primary_image = $img->image_url;
            break;
        }
    }
    if (!$primary_image && !empty($yacht->images[0])) {
        $primary_image = $yacht->images[0]->image_url;
    }
}

// Check if this is a YOLO yacht
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);
$is_yolo = ($yacht->company_id == $yolo_company_id);

// Get product info
$product_type = 'Charter';
if (!empty($yacht->products)) {
    $product_type = $yacht->products[0]->product_type;
}

?>

<div class="yolo-ys-yacht-card <?php echo $is_yolo ? 'yolo-ys-yolo-yacht' : ''; ?>">
    
    <?php if ($is_yolo): ?>
        <div class="yolo-ys-yacht-badge">YOLO</div>
    <?php endif; ?>
    
    <div class="yolo-ys-yacht-image">
        <?php if ($primary_image): ?>
            <img src="<?php echo esc_url($primary_image); ?>" alt="<?php echo esc_attr($yacht->name); ?>" loading="lazy">
        <?php else: ?>
            <div class="yolo-ys-yacht-placeholder">⛵</div>
        <?php endif; ?>
    </div>
    
    <div class="yolo-ys-yacht-content">
        <h3 class="yolo-ys-yacht-name"><?php echo esc_html($yacht->name); ?></h3>
        
        <p class="yolo-ys-yacht-model"><?php echo esc_html($yacht->model); ?></p>
        
        <div class="yolo-ys-yacht-specs">
            <?php if ($yacht->year_of_build): ?>
                <span class="yolo-ys-spec">
                    <strong>Year:</strong> <?php echo esc_html($yacht->year_of_build); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($yacht->cabins): ?>
                <span class="yolo-ys-spec">
                    <strong>Cabins:</strong> <?php echo esc_html($yacht->cabins); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($yacht->berths): ?>
                <span class="yolo-ys-spec">
                    <strong>Berths:</strong> <?php echo esc_html($yacht->berths); ?>
                </span>
            <?php endif; ?>
            
            <?php if ($yacht->length): ?>
                <span class="yolo-ys-spec">
                    <strong>Length:</strong> <?php echo esc_html(number_format($yacht->length, 1)); ?>m
                </span>
            <?php endif; ?>
        </div>
        
        <?php if ($yacht->description): ?>
            <div class="yolo-ys-yacht-description">
                <?php 
                $short_desc = wp_trim_words($yacht->description, 20, '...');
                echo wp_kses_post($short_desc);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="yolo-ys-yacht-footer">
            <span class="yolo-ys-yacht-type"><?php echo esc_html($product_type); ?></span>
            <?php if (!$is_yolo): ?>
                <span class="yolo-ys-partner-label">Partner Company</span>
            <?php endif; ?>
        </div>
        
        <?php
        // Get yacht details page URL
        $details_page_id = get_option('yolo_ys_yacht_details_page', '');
        if ($details_page_id) {
            $details_url = add_query_arg('yacht_id', $yacht->id, get_permalink($details_page_id));
        } else {
            $details_url = add_query_arg('yacht_id', $yacht->id, home_url('/yacht-details/'));
        }
        ?>
        
        <a href="<?php echo esc_url($details_url); ?>" class="yolo-ys-view-details-btn">
            View Details →
        </a>
    </div>
    
</div>

<style>
.yolo-ys-yacht-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    position: relative;
    border: 2px solid #e5e7eb;
}

.yolo-ys-yacht-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.yolo-ys-yolo-yacht {
    border-color: #dc2626;
}

.yolo-ys-yacht-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    background: #dc2626;
    color: white;
    padding: 6px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    z-index: 10;
    box-shadow: 0 2px 6px rgba(220, 38, 38, 0.4);
}

.yolo-ys-yacht-image {
    width: 100%;
    height: 220px;
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

.yolo-ys-yacht-name {
    font-size: 22px;
    font-weight: 700;
    color: #1e3a8a;
    margin: 0 0 8px 0;
}

.yolo-ys-yacht-model {
    font-size: 16px;
    color: #6b7280;
    margin: 0 0 15px 0;
}

.yolo-ys-yacht-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e5e7eb;
}

.yolo-ys-spec {
    font-size: 14px;
    color: #374151;
}

.yolo-ys-spec strong {
    color: #1f2937;
}

.yolo-ys-yacht-description {
    font-size: 14px;
    line-height: 1.6;
    color: #4b5563;
    margin-bottom: 15px;
}

.yolo-ys-yacht-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.yolo-ys-yacht-type {
    font-size: 13px;
    color: #1e3a8a;
    font-weight: 600;
    background: #dbeafe;
    padding: 4px 12px;
    border-radius: 12px;
}

.yolo-ys-partner-label {
    font-size: 12px;
    color: #6b7280;
    font-style: italic;
}

.yolo-ys-view-details-btn {
    display: block;
    width: 100%;
    padding: 12px 20px;
    margin-top: 15px;
    background: #1e3a8a;
    color: white;
    text-align: center;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
}

.yolo-ys-view-details-btn:hover {
    background: #1e40af;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 58, 138, 0.3);
    color: white;
}

.yolo-ys-yolo-yacht .yolo-ys-view-details-btn {
    background: #dc2626;
}

.yolo-ys-yolo-yacht .yolo-ys-view-details-btn:hover {
    background: #b91c1c;
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

@media (max-width: 768px) {
    .yolo-ys-yacht-image {
        height: 200px;
    }
    
    .yolo-ys-yacht-name {
        font-size: 20px;
    }
}
</style>
