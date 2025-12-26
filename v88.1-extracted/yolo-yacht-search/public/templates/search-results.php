<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
/**
 * Search Results Template (v81.20)
 * Features:
 * - Server-side filtering with Apply Filters button
 * - Range selectors for Length, Year, Price
 * - Mobile-friendly collapsed filters
 * - Featured Yachts section (YOLO boats, no filters)
 * - Paginated results with Load More
 */
?>

<div class="yolo-ys-search-results">
    <!-- Page Title (v88.2) -->
    <h1 class="search-results-title" style="font-size: 1.75rem; font-weight: 700; color: rgb(37, 99, 235); text-align: center; margin: 0 0 0.75rem 0; padding: 0; letter-spacing: -0.5px; display: block;"><?php _e('Search Results', 'yolo-yacht-search'); ?></h1>
    
    <div class="container-fluid">
    
    <!-- Search Form at Top of Results -->
    <div class="yolo-ys-results-search-form" id="yolo-ys-results-search-form">
        <form id="yolo-ys-results-form" class="yolo-ys-search-form">
            
            <!-- Boat Type Field -->
            <div class="yolo-ys-form-field yolo-ys-field-select">
                <select id="yolo-ys-results-boat-type" name="boat_type" autocomplete="off">
                    <option value="" selected><?php yolo_ys_text_e('boat_type', 'All boat Types'); ?></option>
                    <option value="Sailing yacht"><?php _e('Sailing yacht', 'yolo-yacht-search'); ?></option>
                    <option value="Catamaran"><?php _e('Catamaran', 'yolo-yacht-search'); ?></option>
                </select>
                <i class="fa-solid fa-chevron-down yolo-ys-select-arrow"></i>
            </div>
            
            <!-- Date Range Field -->
            <div class="yolo-ys-form-field yolo-ys-field-date">
                <i class="fa-regular fa-calendar yolo-ys-date-icon"></i>
                <input type="text" id="yolo-ys-results-dates" name="dates" placeholder="<?php echo esc_attr(yolo_ys_text('select_dates', 'Select dates')); ?>" readonly />
            </div>
            
            <!-- Search Button -->
            <div class="yolo-ys-form-field yolo-ys-field-button">
                <button type="submit" class="yolo-ys-search-button">
                    <span><?php yolo_ys_text_e('search_button', 'SEARCH YACHTS'); ?></span>
                </button>
            </div>
            
        </form>
    </div>
    
    <!-- Advanced Filters Section (v81.20) -->
    <div class="yolo-ys-advanced-filters" id="yolo-ys-advanced-filters" style="display: none;">
        
        <!-- Mobile Toggle Button -->
        <button type="button" class="filters-mobile-toggle" id="filters-mobile-toggle">
            <i class="fa-solid fa-sliders"></i>
            <span><?php _e('Filters', 'yolo-yacht-search'); ?></span>
            <i class="fa-solid fa-chevron-down toggle-icon"></i>
        </button>
        
        <!-- Filters Content (collapsed on mobile) -->
        <div class="filters-content" id="filters-content">
            <div class="filters-row">
                <!-- Location Filter (First) -->
                <div class="filter-group">
                    <label for="filter-location"><i class="fas fa-map-marker-alt"></i> <?php _e('Yacht Location', 'yolo-yacht-search'); ?></label>
                    <select id="filter-location" class="yolo-filter">
                        <option value=""><?php _e('Any Location', 'yolo-yacht-search'); ?></option>
                        <option value="Lefkada">Lefkada</option>
                        <option value="Corfu">Corfu</option>
                        <option value="Kefalonia">Kefalonia</option>
                        <option value="Zakynthos">Zakynthos</option>
                        <option value="Ithaca">Ithaca</option>
                        <option value="Preveza">Preveza</option>
                        <option value="Syvota">Syvota</option>
                        <option value="Vonitsa">Vonitsa</option>
                        <option value="Palairos">Palairos</option>
                        <option value="Plataria">Plataria</option>
                        <option value="Astakos">Astakos</option>
                        <option value="Paxos">Paxos</option>
                    </select>
                </div>
                
                <!-- Cabins Filter -->
                <div class="filter-group">
                    <label for="filter-cabins"><i class="fas fa-bed"></i> <?php _e('Cabins', 'yolo-yacht-search'); ?></label>
                    <select id="filter-cabins" class="yolo-filter">
                        <option value=""><?php _e('Any', 'yolo-yacht-search'); ?></option>
                        <option value="2">2+</option>
                        <option value="3">3+</option>
                        <option value="4">4+</option>
                        <option value="5">5+</option>
                        <option value="6">6+</option>
                    </select>
                </div>
                
                <!-- Length Range Filter -->
                <div class="filter-group filter-range">
                    <label><i class="fas fa-ruler-horizontal"></i> <?php _e('Length (m)', 'yolo-yacht-search'); ?></label>
                    <div class="range-inputs">
                        <select id="filter-length-min" class="yolo-filter range-min">
                            <option value=""><?php _e('Min', 'yolo-yacht-search'); ?></option>
                            <option value="10">10m</option>
                            <option value="11">11m</option>
                            <option value="12">12m</option>
                            <option value="13">13m</option>
                            <option value="14">14m</option>
                            <option value="15">15m</option>
                            <option value="16">16m</option>
                            <option value="17">17m</option>
                            <option value="18">18m</option>
                        </select>
                        <span class="range-separator">-</span>
                        <select id="filter-length-max" class="yolo-filter range-max">
                            <option value=""><?php _e('Max', 'yolo-yacht-search'); ?></option>
                            <option value="12">12m</option>
                            <option value="13">13m</option>
                            <option value="14">14m</option>
                            <option value="15">15m</option>
                            <option value="16">16m</option>
                            <option value="17">17m</option>
                            <option value="18">18m</option>
                            <option value="20">20m</option>
                            <option value="25">25m</option>
                        </select>
                    </div>
                </div>
                
                <!-- Year Range Filter -->
                <div class="filter-group filter-range">
                    <label><i class="fas fa-calendar"></i> <?php _e('Year Built', 'yolo-yacht-search'); ?></label>
                    <div class="range-inputs">
                        <select id="filter-year-min" class="yolo-filter range-min">
                            <option value=""><?php _e('Min', 'yolo-yacht-search'); ?></option>
                            <option value="2010">2010</option>
                            <option value="2012">2012</option>
                            <option value="2014">2014</option>
                            <option value="2016">2016</option>
                            <option value="2018">2018</option>
                            <option value="2020">2020</option>
                            <option value="2022">2022</option>
                            <option value="2024">2024</option>
                        </select>
                        <span class="range-separator">-</span>
                        <select id="filter-year-max" class="yolo-filter range-max">
                            <option value=""><?php _e('Max', 'yolo-yacht-search'); ?></option>
                            <option value="2016">2016</option>
                            <option value="2018">2018</option>
                            <option value="2020">2020</option>
                            <option value="2022">2022</option>
                            <option value="2024">2024</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>
                </div>
                
                <!-- Price Range Filter -->
                <div class="filter-group filter-range">
                    <label><i class="fas fa-euro-sign"></i> <?php _e('Price (€/week)', 'yolo-yacht-search'); ?></label>
                    <div class="range-inputs">
                        <select id="filter-price-min" class="yolo-filter range-min">
                            <option value=""><?php _e('Min', 'yolo-yacht-search'); ?></option>
                            <option value="1000">1,000</option>
                            <option value="2000">2,000</option>
                            <option value="3000">3,000</option>
                            <option value="4000">4,000</option>
                            <option value="5000">5,000</option>
                            <option value="7500">7,500</option>
                            <option value="10000">10,000</option>
                        </select>
                        <span class="range-separator">-</span>
                        <select id="filter-price-max" class="yolo-filter range-max">
                            <option value=""><?php _e('Max', 'yolo-yacht-search'); ?></option>
                            <option value="2000">2,000</option>
                            <option value="3000">3,000</option>
                            <option value="4000">4,000</option>
                            <option value="5000">5,000</option>
                            <option value="7500">7,500</option>
                            <option value="10000">10,000</option>
                            <option value="15000">15,000</option>
                            <option value="20000">20,000</option>
                        </select>
                    </div>
                </div>
                
                <!-- Equipment Filter (Multi-select) -->
                <div class="filter-group filter-equipment">
                    <label for="filter-equipment"><i class="fas fa-cog"></i> <?php _e('Equipment', 'yolo-yacht-search'); ?></label>
                    <div class="equipment-dropdown">
                        <button type="button" class="equipment-dropdown-toggle" id="equipment-dropdown-toggle">
                            <span id="equipment-selected-text"><?php _e('Select Equipment', 'yolo-yacht-search'); ?></span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="equipment-dropdown-menu" id="equipment-dropdown-menu">
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="46"> <?php _e('Solar Panels', 'yolo-yacht-search'); ?>
                            </label>
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="7"> <?php _e('Bimini', 'yolo-yacht-search'); ?>
                            </label>
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="1"> <?php _e('Generator', 'yolo-yacht-search'); ?>
                            </label>
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="2"> <?php _e('Air Conditioning', 'yolo-yacht-search'); ?>
                            </label>
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="3"> <?php _e('Bow Thruster', 'yolo-yacht-search'); ?>
                            </label>
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="4"> <?php _e('Electric Winch', 'yolo-yacht-search'); ?>
                            </label>
                            <label class="equipment-checkbox">
                                <input type="checkbox" name="equipment[]" value="5"> <?php _e('Watermaker', 'yolo-yacht-search'); ?>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Sort By -->
                <div class="filter-group">
                    <label for="filter-sort"><i class="fas fa-sort"></i> <?php _e('Sort By', 'yolo-yacht-search'); ?></label>
                    <select id="filter-sort" class="yolo-filter">
                        <option value="price_asc"><?php _e('Price: Low to High', 'yolo-yacht-search'); ?></option>
                        <option value="price_desc"><?php _e('Price: High to Low', 'yolo-yacht-search'); ?></option>
                        <option value="year_desc"><?php _e('Year: Newest First', 'yolo-yacht-search'); ?></option>
                        <option value="length_desc"><?php _e('Length: Longest First', 'yolo-yacht-search'); ?></option>
                        <option value="cabins_desc"><?php _e('Cabins: Most First', 'yolo-yacht-search'); ?></option>
                    </select>
                </div>
            </div>
            
            <!-- Filter Actions -->
            <div class="filter-actions">
                <button type="button" id="apply-filters" class="btn-apply-filters">
                    <i class="fa-solid fa-search"></i> <?php _e('Apply Filters', 'yolo-yacht-search'); ?>
                </button>
                <button type="button" id="clear-filters" class="btn-clear-filters">
                    <i class="fa-solid fa-times"></i> <?php _e('Clear All', 'yolo-yacht-search'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Results will be loaded here via JavaScript -->
    <div id="yolo-ys-results-container">
        
        <!-- Initial state: No search performed -->
        <div class="yolo-ys-no-results" id="yolo-ys-initial-state">
            <h3><?php _e('Search for Yachts', 'yolo-yacht-search'); ?></h3>
            <p><?php _e('Use the search form to find available yachts for your charter.', 'yolo-yacht-search'); ?></p>
        </div>
        
        <!-- Featured Yachts Section (YOLO boats) -->
        <div class="yolo-ys-featured-section" id="yolo-ys-featured-section" style="display: none;">
            <h2 class="section-title"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i> <?php _e('Featured Yachts', 'yolo-yacht-search'); ?></h2>
            <div class="row g-4" id="yolo-ys-featured-boats">
                <!-- Featured boats loaded via JS -->
            </div>
        </div>
        
        <!-- Results Count -->
        <div class="yolo-ys-results-count" id="yolo-ys-results-count" style="display: none;">
            <span id="results-count-text"></span>
        </div>
        
        <!-- Partner Boats Grid -->
        <div class="row g-4" id="yolo-ys-partner-boats">
            <!-- Partner boats loaded via JS -->
        </div>
        
        <!-- Load More Button -->
        <div class="yolo-ys-load-more" id="yolo-ys-load-more" style="display: none;">
            <button type="button" id="load-more-btn" class="btn-load-more">
                <span id="load-more-text"><?php _e('Load more yachts', 'yolo-yacht-search'); ?></span>
                <span id="load-more-remaining"></span>
            </button>
        </div>
        
        <!-- Loading Spinner -->
        <div class="yolo-ys-loading" id="yolo-ys-loading" style="display: none;">
            <div class="spinner"></div>
            <p><?php _e('Loading results...', 'yolo-yacht-search'); ?></p>
        </div>
        
    </div>
    </div><!-- .container-fluid -->
</div>

<!-- v88.3 Filter Reorganization Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for filters to be visible
    const checkFilters = setInterval(function() {
        const filtersRow = document.querySelector('.filters-row');
        const locationGroup = document.getElementById('filter-location')?.closest('.filter-group');
        
        if (filtersRow && locationGroup) {
            clearInterval(checkFilters);
            reorganizeFilters();
        }
    }, 100);
    
    function reorganizeFilters() {
        const filtersRow = document.querySelector('.filters-row');
        if (!filtersRow) return;
        
        // Get all filter groups and add data-filter attributes for CSS targeting
        const locationGroup = document.getElementById('filter-location').closest('.filter-group');
        locationGroup.setAttribute('data-filter', 'location');
        
        const cabinsGroup = document.getElementById('filter-cabins').closest('.filter-group');
        cabinsGroup.setAttribute('data-filter', 'cabins');
        
        const lengthMinGroup = document.getElementById('filter-length-min').closest('.filter-group');
        lengthMinGroup.setAttribute('data-filter', 'length-min');
        
        const lengthMaxGroup = document.getElementById('filter-length-max').closest('.filter-group');
        lengthMaxGroup.setAttribute('data-filter', 'length-max');
        
        const yearMinGroup = document.getElementById('filter-year-min').closest('.filter-group');
        yearMinGroup.setAttribute('data-filter', 'year-min');
        
        const yearMaxGroup = document.getElementById('filter-year-max').closest('.filter-group');
        yearMaxGroup.setAttribute('data-filter', 'year-max');
        
        const priceMinGroup = document.getElementById('filter-price-min').closest('.filter-group');
        priceMinGroup.setAttribute('data-filter', 'price-min');
        
        const priceMaxGroup = document.getElementById('filter-price-max').closest('.filter-group');
        priceMaxGroup.setAttribute('data-filter', 'price-max');
        
        const equipmentGroup = document.getElementById('equipment-dropdown-toggle').closest('.filter-group');
        equipmentGroup.setAttribute('data-filter', 'equipment');
        
        const sortByGroup = document.getElementById('filter-sort').closest('.filter-group');
        sortByGroup.setAttribute('data-filter', 'sort');
        const applyBtn = document.getElementById('apply-filters');
        const clearBtn = document.getElementById('clear-filters');
        
        // Get dates and boat type from search form
        const datesInput = document.getElementById('yolo-ys-results-dates');
        const boatTypeSelect = document.getElementById('yolo-ys-results-boat-type');
        
        if (!locationGroup || !datesInput || !boatTypeSelect) return;
        
        // Clear existing content
        filtersRow.innerHTML = '';
        
        // Create Row 1 container
        const row1 = document.createElement('div');
        row1.id = 'filter-row-1';
        
        // Create Row 2 container
        const row2 = document.createElement('div');
        row2.id = 'filter-row-2';
        
        // Create dates group for row 1
        const datesGroup = document.createElement('div');
        datesGroup.className = 'filter-group';
        datesGroup.setAttribute('data-filter', 'dates');
        datesGroup.innerHTML = '<label><i class="fas fa-calendar-alt"></i> Choose Dates</label>';
        datesInput.className = 'yolo-filter';
        datesGroup.appendChild(datesInput);
        
        // Create boat type group for row 1
        const boatTypeGroup = document.createElement('div');
        boatTypeGroup.className = 'filter-group';
        boatTypeGroup.setAttribute('data-filter', 'boat-type');
        boatTypeGroup.innerHTML = '<label><i class="fas fa-ship"></i> Boat Type</label>';
        boatTypeSelect.className = 'yolo-filter';
        boatTypeGroup.appendChild(boatTypeSelect);
        
        // ROW 1: Location, Dates, Boat Type, Cabins, Year (Min/Max)
        row1.appendChild(locationGroup);
        row1.appendChild(datesGroup);
        row1.appendChild(boatTypeGroup);
        row1.appendChild(cabinsGroup);
        row1.appendChild(yearMinGroup);
        row1.appendChild(yearMaxGroup);
        
        // ROW 2: Sort By, Length (Min/Max), Equipment, Price (Min/Max), Buttons
        row2.appendChild(sortByGroup);
        row2.appendChild(lengthMinGroup);
        row2.appendChild(lengthMaxGroup);
        row2.appendChild(equipmentGroup);
        row2.appendChild(priceMinGroup);
        row2.appendChild(priceMaxGroup);
        
        // Buttons wrapper
        const btnWrapper = document.createElement('div');
        btnWrapper.className = 'filter-buttons-wrapper';
        btnWrapper.appendChild(applyBtn);
        btnWrapper.appendChild(clearBtn);
        row2.appendChild(btnWrapper);
        
        // Add rows to container
        filtersRow.appendChild(row1);
        filtersRow.appendChild(row2);
        
        // Re-initialize flatpickr on the moved dates input
        if (typeof flatpickr !== 'undefined' && datesInput) {
            flatpickr(datesInput, {
                mode: 'range',
                dateFormat: 'd.m.Y',
                minDate: 'today',
                locale: { firstDayOfWeek: 1 },
                onChange: function(selectedDates, dateStr, instance) {
                    // v88.3: Update currentSearchParams when dates change
                    if (selectedDates.length === 2 && typeof window.yoloUpdateSearchParams === 'function') {
                        window.yoloUpdateSearchParams(selectedDates[0], selectedDates[1]);
                    }
                }
            });
        }
        
        // v88.3: Hook boat type change to update currentSearchParams
        boatTypeSelect.addEventListener('change', function() {
            if (typeof window.yoloUpdateBoatType === 'function') {
                window.yoloUpdateBoatType(this.value);
            }
        });
        
        // v88.3: Override Apply Filters to include boat type and dates
        const origApplyClick = applyBtn.onclick;
        applyBtn.addEventListener('click', function(e) {
            // Update currentSearchParams with current boat type value
            if (typeof window.yoloUpdateBoatType === 'function') {
                window.yoloUpdateBoatType(boatTypeSelect.value);
            }
            
            // Update dates if flatpickr has selected dates
            const fp = datesInput._flatpickr;
            if (fp && fp.selectedDates.length === 2 && typeof window.yoloUpdateSearchParams === 'function') {
                window.yoloUpdateSearchParams(fp.selectedDates[0], fp.selectedDates[1]);
            }
        }, true); // Use capture to run before other handlers
    }
});
</script>

<!-- v88.3: Helper functions to update search params from reorganized filters -->
<script>
(function() {
    // Format date for API (YYYY-MM-DDTHH:mm:ss)
    function formatDateForAPI(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day + 'T00:00:00';
    }
    
    // Expose function to update dates in currentSearchParams
    window.yoloUpdateSearchParams = function(startDate, endDate) {
        if (typeof currentSearchParams !== 'undefined') {
            currentSearchParams.dateFrom = formatDateForAPI(startDate);
            currentSearchParams.dateTo = formatDateForAPI(endDate);
        }
    };
    
    // Expose function to update boat type in currentSearchParams
    window.yoloUpdateBoatType = function(boatType) {
        if (typeof currentSearchParams !== 'undefined') {
            currentSearchParams.kind = boatType || '';
        }
    };
})();
</script>

<!-- v88.7: Select2 initialization with FontAwesome icons -->
<script>
jQuery(document).ready(function($) {
    // Wait for filter reorganization to complete
    setTimeout(function() {
        
        // Icon mappings for different filter types
        var iconMaps = {
            'filter-location': {
                '': 'fa-map-marker-alt',
                'Lefkada': 'fa-umbrella-beach',
                'Corfu': 'fa-umbrella-beach',
                'Kefalonia': 'fa-umbrella-beach',
                'Zakynthos': 'fa-umbrella-beach',
                'Ithaca': 'fa-umbrella-beach',
                'Preveza': 'fa-anchor',
                'Syvota': 'fa-umbrella-beach',
                'Vonitsa': 'fa-anchor',
                'Palairos': 'fa-anchor',
                'Plataria': 'fa-anchor',
                'Astakos': 'fa-anchor',
                'Paxos': 'fa-umbrella-beach'
            },
            'yolo-ys-results-boat-type': {
                '': 'fa-ship',
                'Sailing yacht': 'fa-sailboat',
                'Catamaran': 'fa-dharmachakra'
            },
            'filter-cabins': {
                '': 'fa-bed',
                '2': 'fa-bed',
                '3': 'fa-bed',
                '4': 'fa-bed',
                '5': 'fa-bed',
                '6': 'fa-bed'
            },
            'filter-sort': {
                'price_asc': 'fa-arrow-up-1-9',
                'price_desc': 'fa-arrow-down-9-1',
                'year_desc': 'fa-calendar-check',
                'length_desc': 'fa-ruler',
                'cabins_desc': 'fa-bed'
            }
        };
        
        // Custom template function for Select2
        function formatOption(option, selectId) {
            if (!option.id) {
                return option.text;
            }
            var icons = iconMaps[selectId] || {};
            var iconClass = icons[option.id] || icons[''] || 'fa-circle';
            var $option = $(
                '<span><i class="fas ' + iconClass + '" style="margin-right: 8px; width: 16px; text-align: center;"></i>' + option.text + '</span>'
            );
            return $option;
        }
        
        // Initialize Select2 on filter dropdowns
        var selectIds = ['filter-location', 'yolo-ys-results-boat-type', 'filter-cabins', 'filter-sort'];
        
        selectIds.forEach(function(selectId) {
            var $select = $('#' + selectId);
            if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
                $select.select2({
                    minimumResultsForSearch: Infinity, // Disable search box
                    dropdownAutoWidth: false,
                    width: '100%',
                    templateResult: function(option) {
                        return formatOption(option, selectId);
                    },
                    templateSelection: function(option) {
                        return formatOption(option, selectId);
                    }
                });
            }
        });
        
        // Also initialize length and year range selects (simpler, no icons)
        var rangeSelects = ['filter-length-min', 'filter-length-max', 'filter-year-min', 'filter-year-max', 'filter-price-min', 'filter-price-max'];
        rangeSelects.forEach(function(selectId) {
            var $select = $('#' + selectId);
            if ($select.length && !$select.hasClass('select2-hidden-accessible')) {
                $select.select2({
                    minimumResultsForSearch: Infinity,
                    dropdownAutoWidth: false,
                    width: '100%'
                });
            }
        });
        
    }, 600); // Wait for filter reorganization
});
</script>
