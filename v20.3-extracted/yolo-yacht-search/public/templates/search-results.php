<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
/**
 * Search Results Template
 * Uses the same yacht card component as "Our Yachts" page
 */
?>

<div class="yolo-ys-search-results">
    <!-- Page Title -->
    <h1 class="search-results-title" style="font-size: 1.75rem; font-weight: 700; color: rgb(37, 99, 235); text-align: center; margin: 0 0 0.75rem 0; padding: 0; letter-spacing: -0.5px; display: block;"><?php _e('Search Results', 'yolo-yacht-search'); ?></h1>
    
    <div class="container-fluid">
    
    <!-- Search Form at Top of Results -->
    <div class="yolo-ys-results-search-form" id="yolo-ys-results-search-form">
        <form id="yolo-ys-results-form" class="yolo-ys-search-form">
            
            <!-- Boat Type Field -->
            <div class="yolo-ys-form-field yolo-ys-field-select">
                <select id="yolo-ys-results-boat-type" name="boat_type" autocomplete="off">
                    <option value="" selected><?php yolo_ys_text_e('boat_type', 'Boat type'); ?></option>
                    <option value="Sailing yacht"><?php _e('Sailing yacht', 'yolo-yacht-search'); ?></option>
                    <option value="Catamaran"><?php _e('Catamaran', 'yolo-yacht-search'); ?></option>
                </select>
            </div>
            
            <!-- Date Range Field -->
            <div class="yolo-ys-form-field yolo-ys-field-date">
                <input type="text" id="yolo-ys-results-dates" name="dates" placeholder="<?php echo esc_attr(yolo_ys_text('date_from', 'Dates')); ?>" readonly />
            </div>
            
            <!-- Search Button -->
            <div class="yolo-ys-form-field yolo-ys-field-button">
                <button type="submit" class="yolo-ys-search-button">
                    <span><?php yolo_ys_text_e('search_button', 'SEARCH'); ?></span>
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
    </div><!-- .container-fluid -->
</div>

<!-- Templates removed - JavaScript builds HTML directly without Handlebars -->
