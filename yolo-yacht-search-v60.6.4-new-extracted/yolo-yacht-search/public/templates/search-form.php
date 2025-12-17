<?php
/**
 * Search Form Template (Yolo-Charters.com Style)
 */
?>

<div class="yolo-ys-search-widget">
    <form id="yolo-ys-search-form" class="yolo-ys-search-form">
        
        <!-- Boat Type Field -->
        <div class="yolo-ys-form-field yolo-ys-field-select">
            <select id="yolo-ys-boat-type" name="boat_type" autocomplete="off">
                <option value="" selected><?php yolo_ys_text_e('boat_type', 'Boat type'); ?></option>
                <option value="Sailing yacht"><?php _e('Sailing yacht', 'yolo-yacht-search'); ?></option>
                <option value="Catamaran"><?php _e('Catamaran', 'yolo-yacht-search'); ?></option>
            </select>
        </div>
        
        <!-- Date Range Field -->
        <div class="yolo-ys-form-field yolo-ys-field-date">
            <input type="text" id="yolo-ys-dates" name="dates" placeholder="<?php echo esc_attr(yolo_ys_text('date_from', 'Dates')); ?>" readonly />
        </div>
        
        <!-- Search Button -->
        <div class="yolo-ys-form-field yolo-ys-field-button">
            <button type="submit" class="yolo-ys-search-button">
                <span><?php yolo_ys_text_e('search_button', 'SEARCH'); ?></span>
            </button>
        </div>
        
    </form>
</div>
