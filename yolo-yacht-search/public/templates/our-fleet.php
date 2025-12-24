<?php
/**
 * Our Fleet Template
 * Displays all yachts - YOLO boats first, then partner boats
 * v81.2: Added Load More pagination (15 boats initially)
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
$partner_yachts = array_values($partner_yachts); // Re-index array

// Pagination settings
$boats_per_page = 15;
$initial_yolo_count = min(count($yolo_yachts), $boats_per_page);
$remaining_slots = $boats_per_page - $initial_yolo_count;
$initial_partner_count = min(count($partner_yachts), $remaining_slots);

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
                <div class="container-fluid">
                    <div class="row g-4 yolo-ys-yacht-grid" id="yolo-fleet-container">
                        <?php 
                        $yolo_displayed = 0;
                        foreach ($yolo_yachts as $yacht): 
                            $hidden_class = ($yolo_displayed >= $initial_yolo_count) ? 'yolo-hidden-yacht' : '';
                        ?>
                            <div class="col-12 col-sm-6 col-lg-4 yolo-yacht-item <?php echo $hidden_class; ?>" data-yacht-type="yolo">
                            <?php include YOLO_YS_PLUGIN_DIR . 'public/templates/partials/yacht-card.php'; ?>
                            </div>
                        <?php 
                            $yolo_displayed++;
                        endforeach; 
                        ?>
                    </div>
                    
                    <?php if (count($yolo_yachts) > $initial_yolo_count): ?>
                        <div class="yolo-ys-load-more-container" id="yolo-fleet-load-more">
                            <button type="button" class="yolo-ys-load-more-btn" data-target="yolo" data-shown="<?php echo $initial_yolo_count; ?>" data-total="<?php echo count($yolo_yachts); ?>">
                                Load More YOLO Yachts (<?php echo count($yolo_yachts) - $initial_yolo_count; ?> remaining)
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Partner Fleet Section -->
        <?php if (!empty($partner_yachts)): ?>
            <div class="yolo-ys-fleet-section yolo-ys-partner-fleet">
                <h2 class="yolo-ys-fleet-title"><?php yolo_ys_text_e('partner_title', 'Partner Companies'); ?></h2>
                
                <div class="container-fluid">
                    <div class="row g-4 yolo-ys-yacht-grid" id="partner-fleet-container">
                        <?php 
                        $partner_displayed = 0;
                        foreach ($partner_yachts as $yacht): 
                            $hidden_class = ($partner_displayed >= $initial_partner_count) ? 'yolo-hidden-yacht' : '';
                        ?>
                            <div class="col-12 col-sm-6 col-lg-4 yolo-yacht-item <?php echo $hidden_class; ?>" data-yacht-type="partner">
                            <?php include YOLO_YS_PLUGIN_DIR . 'public/templates/partials/yacht-card.php'; ?>
                            </div>
                        <?php 
                            $partner_displayed++;
                        endforeach; 
                        ?>
                    </div>
                    
                    <?php if (count($partner_yachts) > $initial_partner_count): ?>
                        <div class="yolo-ys-load-more-container" id="partner-fleet-load-more">
                            <button type="button" class="yolo-ys-load-more-btn partner-btn" data-target="partner" data-shown="<?php echo $initial_partner_count; ?>" data-total="<?php echo count($partner_yachts); ?>">
                                Load More Partner Yachts (<?php echo count($partner_yachts) - $initial_partner_count; ?> remaining)
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
    
</div>

<style>
/* v81.2: Hide yachts beyond initial load */
.yolo-hidden-yacht {
    display: none !important;
}
</style>

<script>
(function($) {
    'use strict';
    
    $(document).ready(function() {
        const BOATS_PER_PAGE = 15;
        
        // Handle Load More button clicks
        $('.yolo-ys-load-more-btn').on('click', function() {
            const $btn = $(this);
            const target = $btn.data('target');
            const shown = parseInt($btn.data('shown'));
            const total = parseInt($btn.data('total'));
            
            // Find hidden yachts of this type and show next batch
            const $hiddenYachts = $(`.yolo-yacht-item.yolo-hidden-yacht[data-yacht-type="${target}"]`);
            const toShow = Math.min(BOATS_PER_PAGE, $hiddenYachts.length);
            
            // Show next batch with animation
            $hiddenYachts.slice(0, toShow).each(function(index) {
                const $yacht = $(this);
                setTimeout(function() {
                    $yacht.removeClass('yolo-hidden-yacht').hide().fadeIn(300);
                }, index * 50);
            });
            
            // Update button
            const newShown = shown + toShow;
            const remaining = total - newShown;
            
            $btn.data('shown', newShown);
            
            if (remaining > 0) {
                const typeLabel = target === 'yolo' ? 'YOLO' : 'Partner';
                $btn.text(`Load More ${typeLabel} Yachts (${remaining} remaining)`);
            } else {
                // Hide the button container
                $btn.closest('.yolo-ys-load-more-container').fadeOut(300);
            }
        });
    });
})(jQuery);
</script>
