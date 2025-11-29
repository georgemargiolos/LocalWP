<?php
/**
 * Search Form Template (Yolo-Charters.com Style)
 */
?>

<div class="yolo-ys-search-widget">
    <form id="yolo-ys-search-form" class="yolo-ys-search-form">
        
        <!-- Boat Type Field -->
        <div class="yolo-ys-form-field">
            <label for="yolo-ys-boat-type"><?php _e('Boat Type', 'yolo-yacht-search'); ?></label>
            <select id="yolo-ys-boat-type" name="boat_type">
                <option value=""><?php _e('All types', 'yolo-yacht-search'); ?></option>
                <option value="Sailing yacht" selected><?php _e('Sailing yacht', 'yolo-yacht-search'); ?></option>
                <option value="Catamaran"><?php _e('Catamaran', 'yolo-yacht-search'); ?></option>
            </select>
        </div>
        
        <!-- Date Range Field -->
        <div class="yolo-ys-form-field">
            <label for="yolo-ys-dates"><?php _e('Dates', 'yolo-yacht-search'); ?></label>
            <input type="text" id="yolo-ys-dates" name="dates" placeholder="<?php _e('Select dates...', 'yolo-yacht-search'); ?>" readonly />
        </div>
        
        <!-- Search Button -->
        <div class="yolo-ys-form-field">
            <label>&nbsp;</label><!-- Empty label for alignment -->
            <button type="submit" class="yolo-ys-search-button">
                <?php _e('SEARCH', 'yolo-yacht-search'); ?>
            </button>
        </div>
        
    </form>
</div>
