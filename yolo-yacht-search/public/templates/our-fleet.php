<?php
/**
 * Our Fleet Template
 * Displays all yachts - YOLO boats first, then partner boats
 */

// Get database instance
$db = new YOLO_YS_Database();

// Get YOLO company ID
$yolo_company_id = get_option('yolo_ys_my_company_id', 7850);

// Get all yachts
$yolo_yachts = $db->get_all_yachts($yolo_company_id);
$partner_yachts = $db->get_all_yachts(); // Get all
$partner_yachts = array_filter($partner_yachts, function($yacht) use ($yolo_company_id) {
    return $yacht->company_id != $yolo_company_id;
});

?>

<div class="yolo-ys-our-fleet">
    
    <?php if (empty($yolo_yachts) && empty($partner_yachts)): ?>
        <div class="yolo-ys-no-yachts">
            <p>No yachts available. Please sync yacht data from the admin panel.</p>
        </div>
    <?php else: ?>
        
        <!-- YOLO Fleet Section -->
        <?php if (!empty($yolo_yachts)): ?>
            <div class="yolo-ys-fleet-section yolo-ys-yolo-fleet">
                <h2 class="yolo-ys-fleet-title">
                    <span class="yolo-ys-fleet-badge">YOLO Charters</span>
                    <?php yolo_ys_text_e('our_fleet_title', 'Our Fleet'); ?>
                </h2>
                
                <div class="container-fluid">
                    <div class="row g-4 yolo-ys-yacht-grid">
                        <?php foreach ($yolo_yachts as $yacht): ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <?php include YOLO_YS_PLUGIN_DIR . 'public/templates/partials/yacht-card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Partner Fleet Section -->
        <?php if (!empty($partner_yachts)): ?>
            <div class="yolo-ys-fleet-section yolo-ys-partner-fleet">
                <h2 class="yolo-ys-fleet-title"><?php yolo_ys_text_e('partner_title', 'Partner Companies'); ?></h2>
                
                <div class="container-fluid">
                    <div class="row g-4 yolo-ys-yacht-grid">
                        <?php foreach ($partner_yachts as $yacht): ?>
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
                            <?php include YOLO_YS_PLUGIN_DIR . 'public/templates/partials/yacht-card.php'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

