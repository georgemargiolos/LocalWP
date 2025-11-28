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
                    Our Fleet
                </h2>
                
                <div class="yolo-ys-yacht-grid">
                    <?php foreach ($yolo_yachts as $yacht): ?>
                        <?php include YOLO_YS_PLUGIN_DIR . 'public/templates/partials/yacht-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Partner Fleet Section -->
        <?php if (!empty($partner_yachts)): ?>
            <div class="yolo-ys-fleet-section yolo-ys-partner-fleet">
                <h2 class="yolo-ys-fleet-title">Partner Companies</h2>
                
                <div class="yolo-ys-yacht-grid">
                    <?php foreach ($partner_yachts as $yacht): ?>
                        <?php include YOLO_YS_PLUGIN_DIR . 'public/templates/partials/yacht-card.php'; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<style>
.yolo-ys-our-fleet {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
}

.yolo-ys-fleet-section {
    margin-bottom: 60px;
}

.yolo-ys-fleet-title {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 30px;
    color: #1e3a8a;
    text-align: center;
}

.yolo-ys-fleet-badge {
    display: inline-block;
    background: #dc2626;
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 600;
    margin-right: 10px;
    vertical-align: middle;
}

.yolo-ys-yacht-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    margin-top: 30px;
}

.yolo-ys-no-yachts {
    text-align: center;
    padding: 60px 20px;
    background: #f3f4f6;
    border-radius: 8px;
}

.yolo-ys-no-yachts p {
    font-size: 18px;
    color: #6b7280;
    margin: 0;
}

@media (max-width: 768px) {
    .yolo-ys-yacht-grid {
        grid-template-columns: 1fr;
    }
    
    .yolo-ys-fleet-title {
        font-size: 24px;
    }
}
</style>
