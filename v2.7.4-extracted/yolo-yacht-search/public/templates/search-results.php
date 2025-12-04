<?php
/**
 * Search Results Template
 * Uses the same yacht card component as "Our Yachts" page
 */
?>

<div class="yolo-ys-search-results">
    
    <!-- Search Form at Top of Results -->
    <div class="yolo-ys-results-search-form" id="yolo-ys-results-search-form" style="display: none;">
        <form id="yolo-ys-results-form" class="yolo-ys-search-form">
            
            <!-- Boat Type Field -->
            <div class="yolo-ys-form-field yolo-ys-field-select">
                <select id="yolo-ys-results-boat-type" name="boat_type" autocomplete="off">
                    <option value="" selected><?php _e('Boat type', 'yolo-yacht-search'); ?></option>
                    <option value="Sailing yacht"><?php _e('Sailing yacht', 'yolo-yacht-search'); ?></option>
                    <option value="Catamaran"><?php _e('Catamaran', 'yolo-yacht-search'); ?></option>
                </select>
            </div>
            
            <!-- Date Range Field -->
            <div class="yolo-ys-form-field yolo-ys-field-date">
                <input type="text" id="yolo-ys-results-dates" name="dates" placeholder="<?php _e('Dates', 'yolo-yacht-search'); ?>" readonly />
            </div>
            
            <!-- Search Button -->
            <div class="yolo-ys-form-field yolo-ys-field-button">
                <button type="submit" class="yolo-ys-search-button">
                    <span><?php _e('SEARCH', 'yolo-yacht-search'); ?></span>
                </button>
            </div>
            
        </form>
    </div>
    
    <!-- Results will be loaded here via JavaScript -->
    <div id="yolo-ys-results-container">
        
        <!-- Initial state: No search performed -->
        <div class="yolo-ys-no-results" id="yolo-ys-initial-state">
            <h3><?php _e('Search for Yachts', 'yolo-yacht-search'); ?></h3>
            <p><?php _e('Use the search form to find available yachts for your charter.', 'yolo-yacht-search'); ?></p>
        </div>
        
    </div>
    
</div>

<!-- Loading Template -->
<script type="text/html" id="yolo-ys-loading-template">
    <div class="yolo-ys-loading">
        <div class="yolo-ys-loading-spinner"></div>
        <p>Searching for available yachts...</p>
    </div>
</script>

<!-- Results Template -->
<script type="text/html" id="yolo-ys-results-template">
    <div class="yolo-ys-results-header">
        <h2>Search Results</h2>
        <p class="yolo-ys-results-count">Found {{total_count}} yacht(s) available</p>
    </div>
    
    {{#if yolo_boats.length}}
    <div class="yolo-ys-section-header">
        <h3>YOLO Charters Fleet</h3>
    </div>
    <div class="yolo-ys-results-grid" id="yolo-boats-grid">
        {{#each yolo_boats}}
        <!-- Boat cards will be rendered by JavaScript -->
        {{/each}}
    </div>
    {{/if}}
    
    {{#if friend_boats.length}}
    <div class="yolo-ys-section-header friends">
        <h3>Partner Fleet</h3>
    </div>
    <div class="yolo-ys-results-grid" id="friend-boats-grid">
        {{#each friend_boats}}
        <!-- Boat cards will be rendered by JavaScript -->
        {{/each}}
    </div>
    {{/if}}
    
    {{#if no_results}}
    <div class="yolo-ys-no-results">
        <h3>No Yachts Found</h3>
        <p>Try adjusting your search criteria or dates.</p>
    </div>
    {{/if}}
</script>
