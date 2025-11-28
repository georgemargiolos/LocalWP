<?php
/**
 * Search Results Template
 */
?>

<div class="yolo-ys-search-results">
    
    <!-- Results will be loaded here via JavaScript -->
    <div id="yolo-ys-results-container">
        
        <!-- Initial state: No search performed -->
        <div class="yolo-ys-no-results" id="yolo-ys-initial-state">
            <h3><?php _e('Search for Yachts', 'yolo-yacht-search'); ?></h3>
            <p><?php _e('Use the search form to find available yachts for your charter.', 'yolo-yacht-search'); ?></p>
        </div>
        
    </div>
    
</div>

<script type="text/html" id="yolo-ys-results-template">
    
    <!-- Results Header -->
    <div class="yolo-ys-results-header">
        <h2><?php _e('Available Yachts', 'yolo-yacht-search'); ?></h2>
        <p class="yolo-ys-results-count">
            <?php _e('Found', 'yolo-yacht-search'); ?> <strong>{{total_count}}</strong> <?php _e('yachts', 'yolo-yacht-search'); ?>
        </p>
    </div>
    
    <!-- YOLO Boats Section -->
    {{#if yolo_boats.length}}
    <div class="yolo-ys-section-header">
        <h3><?php _e('YOLO Charters Fleet', 'yolo-yacht-search'); ?></h3>
    </div>
    
    <div class="yolo-ys-results-grid">
        {{#each yolo_boats}}
        <div class="yolo-ys-yacht-card yolo-boat">
            <div class="yolo-ys-yacht-badge"><?php _e('YOLO', 'yolo-yacht-search'); ?></div>
            
            <div class="yolo-ys-yacht-image">
                <span class="yolo-ys-yacht-image-placeholder">⛵</span>
            </div>
            
            <div class="yolo-ys-yacht-info">
                <h3 class="yolo-ys-yacht-name">{{yacht}}</h3>
                <p class="yolo-ys-yacht-type">{{product}}</p>
                
                <div class="yolo-ys-yacht-location">
                    📍 {{startBase}}
                </div>
                
                <div class="yolo-ys-yacht-price">
                    <div>
                        <span class="yolo-ys-price-amount">{{price}}</span>
                        <span class="yolo-ys-price-currency">{{currency}}</span>
                    </div>
                    <a href="#" class="yolo-ys-view-button"><?php _e('View Details', 'yolo-yacht-search'); ?></a>
                </div>
            </div>
        </div>
        {{/each}}
    </div>
    {{/if}}
    
    <!-- Friend Companies Boats Section -->
    {{#if friend_boats.length}}
    <div class="yolo-ys-section-header friends">
        <h3><?php _e('Partner Companies', 'yolo-yacht-search'); ?></h3>
    </div>
    
    <div class="yolo-ys-results-grid">
        {{#each friend_boats}}
        <div class="yolo-ys-yacht-card">
            
            <div class="yolo-ys-yacht-image">
                <span class="yolo-ys-yacht-image-placeholder">⛵</span>
            </div>
            
            <div class="yolo-ys-yacht-info">
                <h3 class="yolo-ys-yacht-name">{{yacht}}</h3>
                <p class="yolo-ys-yacht-type">{{product}}</p>
                
                <div class="yolo-ys-yacht-location">
                    📍 {{startBase}}
                </div>
                
                <div class="yolo-ys-yacht-price">
                    <div>
                        <span class="yolo-ys-price-amount">{{price}}</span>
                        <span class="yolo-ys-price-currency">{{currency}}</span>
                    </div>
                    <a href="#" class="yolo-ys-view-button"><?php _e('View Details', 'yolo-yacht-search'); ?></a>
                </div>
                
                <p class="yolo-ys-company-info"><?php _e('Partner Company', 'yolo-yacht-search'); ?></p>
            </div>
        </div>
        {{/each}}
    </div>
    {{/if}}
    
    <!-- No Results -->
    {{#if no_results}}
    <div class="yolo-ys-no-results">
        <h3><?php _e('No Yachts Found', 'yolo-yacht-search'); ?></h3>
        <p><?php _e('Try adjusting your search criteria or dates.', 'yolo-yacht-search'); ?></p>
    </div>
    {{/if}}
    
</script>

<script type="text/html" id="yolo-ys-loading-template">
    <div class="yolo-ys-loading">
        <div class="yolo-ys-loading-spinner"></div>
        <p><?php _e('Searching for available yachts...', 'yolo-yacht-search'); ?></p>
    </div>
</script>
