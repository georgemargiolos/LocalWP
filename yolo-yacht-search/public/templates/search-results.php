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
            <div class="yolo-ys-form-field">
                <label for="yolo-ys-results-boat-type"><?php _e('Boat Type', 'yolo-yacht-search'); ?></label>
                <select id="yolo-ys-results-boat-type" name="boat_type">
                    <option value=""><?php _e('All types', 'yolo-yacht-search'); ?></option>
                    <option value="Sailing yacht"><?php _e('Sailing yacht', 'yolo-yacht-search'); ?></option>
                    <option value="Catamaran"><?php _e('Catamaran', 'yolo-yacht-search'); ?></option>
                </select>
            </div>
            <div class="yolo-ys-form-field">
                <label for="yolo-ys-results-dates"><?php _e('Dates', 'yolo-yacht-search'); ?></label>
                <input type="text" id="yolo-ys-results-dates" name="dates" placeholder="<?php _e('Select dates...', 'yolo-yacht-search'); ?>" readonly />
            </div>
            <div class="yolo-ys-form-field">
                <label>&nbsp;</label>
                <button type="submit" class="yolo-ys-search-button">
                    <?php _e('SEARCH', 'yolo-yacht-search'); ?>
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

<style>
.yolo-ys-search-results {
    padding: 40px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.yolo-ys-results-header {
    margin-bottom: 30px;
    text-align: center;
}

.yolo-ys-results-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
}

.yolo-ys-results-count {
    font-size: 18px;
    color: #6b7280;
}

.yolo-ys-section-header {
    margin: 40px 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 3px solid #b91c1c;
}

.yolo-ys-section-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.yolo-ys-section-header.friends {
    border-bottom-color: #1e3a8a;
}

.yolo-ys-results-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
    margin-bottom: 40px;
}

.yolo-ys-no-results {
    text-align: center;
    padding: 60px 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.yolo-ys-no-results h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
}

.yolo-ys-no-results p {
    font-size: 16px;
    color: #6b7280;
}

.yolo-ys-loading {
    text-align: center;
    padding: 60px 20px;
}

.yolo-ys-loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #e5e7eb;
    border-top-color: #b91c1c;
    border-radius: 50%;
    animation: yolo-spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes yolo-spin {
    to { transform: rotate(360deg); }
}

.yolo-ys-loading p {
    font-size: 16px;
    color: #6b7280;
}

@media (max-width: 1024px) {
    .yolo-ys-results-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
}

.yolo-ys-results-search-form {
    background: #f9fafb;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 40px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.yolo-ys-results-search-form .yolo-ys-search-form {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr;
    gap: 20px;
    align-items: end;
}

@media (max-width: 1024px) {
    .yolo-ys-results-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .yolo-ys-results-search-form .yolo-ys-search-form {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .yolo-ys-results-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .yolo-ys-results-header h2 {
        font-size: 24px;
    }
    
    .yolo-ys-results-search-form {
        padding: 20px;
    }
}
</style>
