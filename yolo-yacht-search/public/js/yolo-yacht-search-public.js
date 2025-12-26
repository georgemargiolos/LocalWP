(function($) {
    'use strict';
    
    // v81.17: Server-side filtering state (v88.5: Exposed globally for filter reorganization)
    window.currentSearchParams = {
        dateFrom: '',
        dateTo: '',
        kind: ''
    };
    var currentSearchParams = window.currentSearchParams;
    let currentPage = 1;
    let isLoading = false;
    
    // Initialize when document is ready
    $(document).ready(function() {
        initLitepicker();
        initSearchForm();
        initResultsSearchForm();
        initFilters();
        checkForSearchParams();
    });
    
    /**
     * Initialize Litepicker (Yolo-Charters.com style)
     */
    function initLitepicker() {
        const dateInput = document.getElementById('yolo-ys-dates');
        if (!dateInput) return;
        
        // Calculate next Saturday
        let firstSaturday = new Date();
        while (firstSaturday.getDay() !== 6) {
            firstSaturday.setDate(firstSaturday.getDate() + 1);
        }
        
        let nextSaturday = new Date(firstSaturday.getTime());
        nextSaturday.setDate(nextSaturday.getDate() + 7);
        
        // Initialize Litepicker
        const picker = new Litepicker({
            element: dateInput,
            plugins: ['mobilefriendly'],
            mobilefriendly: {
                breakpoint: 480,
            },
            format: 'DD.MM.YYYY',
            firstDay: 0,
            singleMode: false,
            minDate: new Date(),
            numberOfColumns: 2,
            numberOfMonths: 2,
            disallowLockDaysInRange: true,
            position: 'top',
            tooltipNumber: (totalDays) => {
                return totalDays - 1;
            },
            tooltipText: { 
                "one": "night", 
                "other": "nights" 
            },
            lockDaysFilter: (date) => {
                let today = new Date();
                let saturday = date.getTime() > today.getTime() && date.getDay() === 6;
                return !saturday;
            }
        });
        
        // Store picker instance globally
        window.yoloDatePicker = picker;
    }
    
    /**
     * Initialize search form
     */
    function initSearchForm() {
        const form = $('#yolo-ys-search-form');
        if (!form.length) return;
        
        form.on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
    }
    
    /**
     * Initialize results page search form
     */
    function initResultsSearchForm() {
        const dateInput = document.getElementById('yolo-ys-results-dates');
        if (!dateInput) return;
        
        // Initialize Litepicker for results form
        const picker = new Litepicker({
            element: dateInput,
            plugins: ['mobilefriendly'],
            mobilefriendly: {
                breakpoint: 480,
            },
            format: 'DD.MM.YYYY',
            firstDay: 0,
            singleMode: false,
            minDate: new Date(),
            numberOfColumns: 2,
            numberOfMonths: 2,
            disallowLockDaysInRange: true,
            position: 'auto',
            tooltipNumber: (totalDays) => {
                return totalDays - 1;
            },
            tooltipText: { 
                "one": "night", 
                "other": "nights" 
            },
            lockDaysFilter: (date) => {
                let today = new Date();
                let saturday = date.getTime() > today.getTime() && date.getDay() === 6;
                return !saturday;
            }
        });
        
        // Store picker instance
        window.yoloResultsDatePicker = picker;
        
        // Handle form submission
        $('#yolo-ys-results-form').on('submit', function(e) {
            e.preventDefault();
            performResultsSearch();
        });
    }
    
    /**
     * Initialize filters (v81.17)
     */
    function initFilters() {
        // Equipment dropdown toggle
        $('#equipment-dropdown-toggle').on('click', function(e) {
            e.stopPropagation();
            $('#equipment-dropdown-menu').toggleClass('show');
        });
        
        // Close equipment dropdown when clicking outside (exclude Select2 elements)
        $(document).on('click', function(e) {
            // Don't close if clicking on Select2 elements
            if ($(e.target).closest('.select2-container, .select2-dropdown, .select2-results, .select2-selection').length) {
                return;
            }
            if (!$(e.target).closest('.filter-equipment').length) {
                $('#equipment-dropdown-menu').removeClass('show');
            }
        });
        
        // Update equipment selected text
        $('input[name="equipment[]"]').on('change', function() {
            updateEquipmentText();
        });
        
        // Mobile filters toggle (v90.0: Updated for top-positioned filters)
        $('#filters-mobile-toggle').on('click', function() {
            const content = $('#filters-content');
            const toggle = $(this);
            const filtersContainer = $('#yolo-ys-advanced-filters');
            
            content.toggleClass('expanded');
            toggle.toggleClass('active');
            
            // v90.0: Add class to parent container for proper max-height handling
            filtersContainer.toggleClass('filters-expanded');
        });
        
        // v90.2: Sticky filters on scroll (mobile only)
        initStickyFilters();
        
        // Apply Filters button
        $('#apply-filters').on('click', function() {
            currentPage = 1;
            searchYachtsFiltered();
        });
        
        // Clear Filters button
        $('#clear-filters').on('click', function() {
            clearFilters();
        });
        
        // Load More button
        $(document).on('click', '#load-more-btn', function() {
            loadMoreResults();
        });
    }
    
    /**
     * Update equipment dropdown text
     */
    function updateEquipmentText() {
        const checked = $('input[name="equipment[]"]:checked');
        if (checked.length === 0) {
            $('#equipment-selected-text').text('Select Equipment');
        } else if (checked.length === 1) {
            $('#equipment-selected-text').text(checked.parent().text().trim());
        } else {
            $('#equipment-selected-text').text(checked.length + ' selected');
        }
    }
    
    /**
     * Clear all filters (v81.20: Range filters)
     */
    function clearFilters() {
        $('#filter-cabins').val('');
        $('#filter-length-min').val('');
        $('#filter-length-max').val('');
        $('#filter-year-min').val('');
        $('#filter-year-max').val('');
        $('#filter-price-min').val('');
        $('#filter-price-max').val('');
        $('#filter-location').val('');
        $('#filter-sort').val('price_asc');
        $('input[name="equipment[]"]').prop('checked', false);
        updateEquipmentText();
        
        // Re-search with cleared filters
        currentPage = 1;
        searchYachtsFiltered();
    }
    
    /**
     * Get current filter values (v81.20: Range filters)
     */
    function getFilterValues() {
        const equipment = [];
        $('input[name="equipment[]"]:checked').each(function() {
            equipment.push($(this).val());
        });
        
        return {
            cabins: $('#filter-cabins').val() || 0,
            lengthMin: $('#filter-length-min').val() || 0,
            lengthMax: $('#filter-length-max').val() || 0,
            yearMin: $('#filter-year-min').val() || 0,
            yearMax: $('#filter-year-max').val() || 0,
            priceMin: $('#filter-price-min').val() || 0,
            priceMax: $('#filter-price-max').val() || 0,
            location: $('#filter-location').val() || '',
            equipment: equipment,
            sort: $('#filter-sort').val() || 'price_asc'
        };
    }
    
    /**
     * Perform search
     */
    function performSearch() {
        const picker = window.yoloDatePicker;
        if (!picker) {
            alert('Date picker not initialized');
            return;
        }
        
        const startDate = picker.getStartDate();
        const endDate = picker.getEndDate();
        
        if (!startDate || !endDate) {
            alert('Please select dates');
            return;
        }
        
        // Format dates for API (yyyy-MM-dd'T'HH:mm:ss)
        const dateFrom = formatDateForAPI(startDate);
        const dateTo = formatDateForAPI(endDate);
        const kind = $('#yolo-ys-boat-type').val();
        
        // If results page is set, redirect with params
        if (yoloYSData.results_page_url) {
            const url = new URL(yoloYSData.results_page_url);
            url.searchParams.set('dateFrom', dateFrom);
            url.searchParams.set('dateTo', dateTo);
            url.searchParams.set('kind', kind);
            window.location.href = url.toString();
        } else {
            // Search on same page
            currentSearchParams = { dateFrom, dateTo, kind };
            currentPage = 1;
            searchYachtsFiltered();
        }
    }
    
    /**
     * Format date for API
     */
    function formatDateForAPI(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}T00:00:00`;
    }
    
    /**
     * Check for search params in URL
     */
    function checkForSearchParams() {
        const resultsContainer = $('#yolo-ys-results-container');
        if (!resultsContainer.length) return;
        
        const urlParams = new URLSearchParams(window.location.search);
        const dateFrom = urlParams.get('dateFrom');
        const dateTo = urlParams.get('dateTo');
        const kind = urlParams.get('kind') || '';
        
        if (dateFrom && dateTo) {
            // Store search params
            currentSearchParams = { dateFrom, dateTo, kind };
            
            // Pre-fill results search form
            prefillResultsSearchForm(dateFrom, dateTo, kind);
            
            // Perform filtered search
            currentPage = 1;
            searchYachtsFiltered();
        }
    }
    
    /**
     * Pre-fill results search form with current search params
     */
    function prefillResultsSearchForm(dateFrom, dateTo, kind) {
        const formElement = $('#yolo-ys-results-search-form');
        if (!formElement.length) return;
        
        formElement.show();
        $('#yolo-ys-results-boat-type').val(kind);
        
        const picker = window.yoloResultsDatePicker;
        if (picker && dateFrom && dateTo) {
            try {
                const startDate = new Date(dateFrom);
                const endDate = new Date(dateTo);
                picker.setDateRange(startDate, endDate);
            } catch (e) {
                console.log('Could not set date range:', e);
            }
        }
    }
    
    /**
     * Perform search from results page form
     */
    function performResultsSearch() {
        const picker = window.yoloResultsDatePicker;
        if (!picker) {
            alert('Date picker not initialized');
            return;
        }
        
        const startDate = picker.getStartDate();
        const endDate = picker.getEndDate();
        
        if (!startDate || !endDate) {
            alert('Please select dates');
            return;
        }
        
        const dateFrom = formatDateForAPI(startDate);
        const dateTo = formatDateForAPI(endDate);
        const kind = $('#yolo-ys-results-boat-type').val();
        
        // Update URL
        const url = new URL(window.location.href);
        url.searchParams.set('dateFrom', dateFrom);
        url.searchParams.set('dateTo', dateTo);
        url.searchParams.set('kind', kind);
        window.history.pushState({}, '', url);
        
        // Store and search
        currentSearchParams = { dateFrom, dateTo, kind };
        currentPage = 1;
        searchYachtsFiltered();
    }
    
    /**
     * Search yachts with filters via AJAX (v81.20: Range filters)
     */
    function searchYachtsFiltered() {
        if (isLoading) return;
        isLoading = true;
        
        const filters = getFilterValues();
        
        // v88.4: Read boat type and dates from moved filter elements
        var boatTypeSelect = document.getElementById('yolo-ys-results-boat-type');
        var datesInput = document.getElementById('yolo-ys-results-dates');
        
        // Update currentSearchParams from filter elements if they exist
        if (boatTypeSelect) {
            currentSearchParams.kind = boatTypeSelect.value || '';
        }
        
        // Parse dates from flatpickr input (format: DD.MM.YYYY - DD.MM.YYYY)
        if (datesInput && datesInput.value) {
            var dateRange = datesInput.value.split(' - ');
            if (dateRange.length === 2) {
                var parseDate = function(dateStr) {
                    var parts = dateStr.trim().split('.');
                    if (parts.length === 3) {
                        return parts[2] + '-' + parts[1] + '-' + parts[0] + 'T00:00:00';
                    }
                    return '';
                };
                currentSearchParams.dateFrom = parseDate(dateRange[0]);
                currentSearchParams.dateTo = parseDate(dateRange[1]);
            }
        }
        
        // Show loading
        showLoading();
        
        // AJAX request
        $.ajax({
            url: yoloYSData.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_search_yachts_filtered',
                nonce: yoloYSData.nonce,
                dateFrom: currentSearchParams.dateFrom,
                dateTo: currentSearchParams.dateTo,
                kind: currentSearchParams.kind,
                cabins: filters.cabins,
                lengthMin: filters.lengthMin,
                lengthMax: filters.lengthMax,
                yearMin: filters.yearMin,
                yearMax: filters.yearMax,
                priceMin: filters.priceMin,
                priceMax: filters.priceMax,
                location: filters.location,
                equipment: JSON.stringify(filters.equipment),
                sort: filters.sort,
                page: currentPage
            },
            success: function(response) {
                isLoading = false;
                hideLoading();
                
                if (response.success) {
                    displayFilteredResults(response, currentPage === 1);
                } else {
                    showError('Failed to load results.');
                }
            },
            error: function() {
                isLoading = false;
                hideLoading();
                showError('Failed to connect to server.');
            }
        });
    }
    
    /**
     * Display filtered results (v81.17)
     */
    function displayFilteredResults(data, isNewSearch) {
        // Show filters section
        $('#yolo-ys-advanced-filters').show();
        
        // Hide initial state
        $('#yolo-ys-initial-state').hide();
        
        // Handle Featured Yachts (YOLO boats)
        if (isNewSearch) {
            const featuredSection = $('#yolo-ys-featured-section');
            const featuredContainer = $('#yolo-ys-featured-boats');
            
            if (data.featured_boats && data.featured_boats.length > 0) {
                featuredContainer.empty();
                data.featured_boats.forEach(boat => {
                    featuredContainer.append(`<div class="col-12 col-sm-6 col-lg-4">${renderBoatCard(boat, true)}</div>`);
                });
                featuredSection.show();
            } else {
                featuredSection.hide();
            }
        }
        
        // Handle Partner boats
        const partnerContainer = $('#yolo-ys-partner-boats');
        
        if (isNewSearch) {
            partnerContainer.empty();
        }
        
        if (data.partner_boats && data.partner_boats.length > 0) {
            data.partner_boats.forEach(boat => {
                partnerContainer.append(`<div class="col-12 col-sm-6 col-lg-4">${renderBoatCard(boat, false)}</div>`);
            });
        }
        
        // Update results count (v81.23: Include featured boats in count)
        const resultsCount = $('#yolo-ys-results-count');
        const countText = $('#results-count-text');
        const featuredCount = (data.featured_boats && data.featured_boats.length) ? data.featured_boats.length : 0;
        const totalYachts = featuredCount + (data.total_count || 0);
        
        if (totalYachts > 0) {
            countText.text(`Found ${totalYachts} yacht(s)`);
            resultsCount.show();
        } else if (isNewSearch) {
            // No results at all
            partnerContainer.html(`
                <div class="col-12">
                    <div class="yolo-ys-no-results">
                        <h3>No Yachts Found</h3>
                        <p>Try adjusting your filters or search criteria.</p>
                    </div>
                </div>
            `);
            resultsCount.hide();
        }
        
        // Handle Load More button
        const loadMoreSection = $('#yolo-ys-load-more');
        const loadMoreBtn = $('#load-more-btn');
        const loadMoreRemaining = $('#load-more-remaining');
        
        if (data.has_more) {
            const remaining = data.total_count - (data.page * data.per_page);
            loadMoreRemaining.text(`(${remaining} remaining)`);
            loadMoreSection.show();
        } else {
            loadMoreSection.hide();
        }
    }
    
    /**
     * Load more results
     */
    function loadMoreResults() {
        currentPage++;
        searchYachtsFiltered();
    }
    
    /**
     * Show loading spinner
     */
    function showLoading() {
        $('#yolo-ys-loading').show();
        $('#load-more-btn').prop('disabled', true);
        $('#load-more-text').text('Loading...');
    }
    
    /**
     * Hide loading spinner
     */
    function hideLoading() {
        $('#yolo-ys-loading').hide();
        $('#load-more-btn').prop('disabled', false);
        $('#load-more-text').text('Load more yachts');
    }
    
    /**
     * Get airport distance HTML (v81.18)
     */
    function getAirportDistanceHtml(homeBase) {
        if (!homeBase) return '';
        
        const baseLower = homeBase.toLowerCase().trim();
        
        // Airport mapping: 'search_term' => ['airport_name', 'code', 'distance_km']
        const airportMap = {
            // PREVEZA / LEFKADA AREA (PVK - Aktion Airport)
            'preveza marina': ['Aktion Airport', 'PVK', 5],
            'preveza main port': ['Aktion Airport', 'PVK', 7],
            'cleopatra marina': ['Aktion Airport', 'PVK', 6],
            'd-marin marina lefkas': ['Aktion Airport', 'PVK', 25],
            'd-marin lefkas': ['Aktion Airport', 'PVK', 25],
            'port of lefkas': ['Aktion Airport', 'PVK', 25],
            'lefkada': ['Aktion Airport', 'PVK', 25],
            'nydri marina': ['Aktion Airport', 'PVK', 40],
            'nydri port': ['Aktion Airport', 'PVK', 40],
            'nydri': ['Aktion Airport', 'PVK', 40],
            'vliho': ['Aktion Airport', 'PVK', 38],
            'vasiliki': ['Aktion Airport', 'PVK', 55],
            'sivota': ['Aktion Airport', 'PVK', 35],
            'nikiana': ['Aktion Airport', 'PVK', 32],
            'lygia': ['Aktion Airport', 'PVK', 28],
            'ligia': ['Aktion Airport', 'PVK', 28],
            'marina paleros': ['Aktion Airport', 'PVK', 45],
            'palairos': ['Aktion Airport', 'PVK', 45],
            'vounaki': ['Aktion Airport', 'PVK', 45],
            'vonitsa': ['Aktion Airport', 'PVK', 15],
            'astakos': ['Aktion Airport', 'PVK', 60],
            'plataria': ['Aktion Airport', 'PVK', 50],
            'mitikas': ['Aktion Airport', 'PVK', 55],
            'mytikas': ['Aktion Airport', 'PVK', 55],
            'perigiali': ['Aktion Airport', 'PVK', 25],
            // CORFU AREA (CFU - Corfu Airport)
            'd-marin marina gouvia': ['Corfu Airport', 'CFU', 8],
            'd-marin gouvia': ['Corfu Airport', 'CFU', 8],
            'gouvia': ['Corfu Airport', 'CFU', 8],
            'corfu harbor': ['Corfu Airport', 'CFU', 3],
            'corfu': ['Corfu Airport', 'CFU', 3],
            'mandraki': ['Corfu Airport', 'CFU', 4],
            'benitses': ['Corfu Airport', 'CFU', 12],
            'palaiokastritsas': ['Corfu Airport', 'CFU', 25],
            'alipa': ['Corfu Airport', 'CFU', 25],
            // PAXOS
            'paxos': ['Corfu Airport', 'CFU', 50],
            'gaios': ['Corfu Airport', 'CFU', 50],
            // KEFALONIA AREA (EFL - Kefalonia Airport)
            'argostoli': ['Kefalonia Airport', 'EFL', 10],
            'fiskardo': ['Kefalonia Airport', 'EFL', 50],
            'sami': ['Kefalonia Airport', 'EFL', 25],
            'agia effimia': ['Kefalonia Airport', 'EFL', 35],
            'agia pelagia': ['Kefalonia Airport', 'EFL', 20],
            'lixouri': ['Kefalonia Airport', 'EFL', 15],
            'poros': ['Kefalonia Airport', 'EFL', 30],
            // ITHACA
            'ithaca': ['Kefalonia Airport', 'EFL', 40],
            'vathy': ['Kefalonia Airport', 'EFL', 40],
            // ZAKYNTHOS AREA (ZTH - Zakynthos Airport)
            'zakynthos': ['Zakynthos Airport', 'ZTH', 5],
            'zante': ['Zakynthos Airport', 'ZTH', 5],
            'agios sostis': ['Zakynthos Airport', 'ZTH', 8]
        };
        
        // Try exact match first
        if (airportMap[baseLower]) {
            const [name, code, km] = airportMap[baseLower];
            return `<span class="yolo-ys-location-separator"> | </span><span class="yolo-ys-airport-distance"><i class="fa-duotone fa-plane" style="color: #3b82f6;"></i> ${km}km from ${code} - ${name}</span>`;
        }
        
        // Try partial match
        for (const [searchTerm, airportInfo] of Object.entries(airportMap)) {
            if (baseLower.includes(searchTerm)) {
                const [name, code, km] = airportInfo;
                return `<span class="yolo-ys-location-separator"> | </span><span class="yolo-ys-airport-distance"><i class="fa-duotone fa-plane" style="color: #3b82f6;"></i> ${km}km from ${code} - ${name}</span>`;
            }
        }
        
        return '';
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        $('#yolo-ys-partner-boats').html(`
            <div class="col-12">
                <div class="yolo-ys-no-results">
                    <h3>Error</h3>
                    <p>${message}</p>
                </div>
            </div>
        `);
    }
    
    /**
     * Render boat card (v81.18: Added airport distance)
     */
    function renderBoatCard(boat, isYolo) {
        const yoloClass = isYolo ? 'yolo-yacht' : '';
        
        // Image or placeholder
        const imageHtml = boat.image_url 
            ? `<img src="${boat.image_url}" alt="${boat.yacht}" loading="lazy">` 
            : '<div class="yolo-ys-yacht-placeholder">⛵</div>';
        
        // YOLO logo for YOLO yachts
        const featuredBadgeHtml = isYolo 
            ? '<div class="yolo-ys-featured-badge"><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i> Featured Yacht</div>' 
            : '';
        
        const yoloLogoHtml = isYolo 
            ? '<img src="https://yolo-charters.com/wp-content/uploads/2025/11/logo-for-YOLO-charters.png" alt="YOLO Charters" class="yolo-ys-yacht-logo">' 
            : '';
        
        // Details URL
        const detailsUrl = boat.details_url || '#';
        
        // Format specs
        const lengthFt = boat.length ? Math.round(boat.length * 3.28084) : 0;
        
        // Use separate name and model fields from API (v87.3 fix)
        let yachtName = boat.yacht || 'Unknown';
        let yachtModel = boat.model || '';
        
        // Get airport info (v81.18)
        const airportHtml = getAirportDistanceHtml(boat.startBase);
        
        // Helper function to format price with European locale
        const formatPrice = (price) => {
            if (!price || isNaN(price)) return '0,00';
            return Number(price).toLocaleString('de-DE', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };
        
        // Refit display
        let refitDisplay = '';
        if (boat.refit_year) {
            refitDisplay = `<span class="yolo-ys-refit-note">Refit: ${boat.refit_year}</span>`;
        }
        
        // Price display with discount logic
        let priceHtml = '';
        const hasDiscount = boat.original_price && boat.discount_percentage && 
                           parseFloat(boat.original_price) > parseFloat(boat.price);
        
        if (hasDiscount) {
            const originalPrice = parseFloat(boat.original_price);
            const finalPrice = parseFloat(boat.price);
            const discountPercent = Math.round(parseFloat(boat.discount_percentage));
            
            priceHtml = `
                <div class="yolo-ys-yacht-price-container">
                    <div class="yolo-ys-price-original">
                        <span class="yolo-ys-price-strikethrough">${formatPrice(originalPrice)} ${boat.currency}</span>
                    </div>
                    <div class="yolo-ys-price-discount-row">
                        <span class="yolo-ys-discount-badge">-${discountPercent}%</span>
                        <div class="yolo-ys-price-final">
                            <strong>${formatPrice(finalPrice)} ${boat.currency}</strong>
                            <span class="yolo-ys-price-period">per week</span>
                        </div>
                    </div>
                </div>
            `;
        } else {
            priceHtml = `
                <div class="yolo-ys-yacht-price-container">
                    <div class="yolo-ys-price-final">
                        From <strong>${formatPrice(boat.price)} ${boat.currency}</strong>
                        <span class="yolo-ys-price-period">per week</span>
                    </div>
                </div>
            `;
        }
        
        return `
            <div class="yolo-ys-yacht-card yolo-ys-clickable-card ${yoloClass}">
                <a href="${detailsUrl}" class="yolo-ys-card-link" aria-label="${yachtName} - ${yachtModel}"></a>
                <div class="yolo-ys-yacht-image">
                    ${imageHtml}
                    ${yoloLogoHtml}
                    ${featuredBadgeHtml}
                </div>
                <div class="yolo-ys-yacht-content">
                    <div class="yolo-ys-yacht-location">
                        <span class="yolo-ys-location-icon"><i class="fa-duotone fa-location-dot" style="color: #dc2626;"></i></span>
                        <span class="yolo-ys-location-text">${boat.startBase || 'Location not specified'}</span>
                        ${airportHtml}
                    </div>
                    <div class="yolo-ys-yacht-header">
                        <h3 class="yolo-ys-yacht-name">${yachtName}</h3>
                        ${yachtModel ? `<h4 class="yolo-ys-yacht-model">${yachtModel}</h4>` : ''}
                    </div>
                    <div class="yolo-ys-yacht-specs-grid">
                        <div class="yolo-ys-specs-row">
                            <div class="yolo-ys-spec-item">
                                <div class="yolo-ys-spec-value">${boat.cabins || 0}</div>
                                <div class="yolo-ys-spec-label">CABINS</div>
                            </div>
                            <div class="yolo-ys-spec-item">
                                <div class="yolo-ys-spec-value">${boat.wc || 0}</div>
                                <div class="yolo-ys-spec-label">HEADS</div>
                            </div>
                        </div>
                        <div class="yolo-ys-specs-row">
                            <div class="yolo-ys-spec-item">
                                <div class="yolo-ys-spec-value">${boat.year_of_build || 'N/A'}</div>
                                <div class="yolo-ys-spec-label">BUILT YEAR</div>
                            </div>
                            ${boat.refit_year ? `
                            <div class="yolo-ys-spec-item">
                                <div class="yolo-ys-spec-value yolo-ys-refit-bold">${refitDisplay}</div>
                                <div class="yolo-ys-spec-label">REFIT</div>
                            </div>` : ''}
                            <div class="yolo-ys-spec-item">
                                <div class="yolo-ys-spec-value">${lengthFt} ft</div>
                                <div class="yolo-ys-spec-label">LENGTH</div>
                            </div>
                        </div>
                    </div>
                    ${priceHtml}
                    <span class="yolo-ys-details-btn">DETAILS</span>
                </div>
            </div>
        `;
    }
    
    /**
     * v90.2: Initialize sticky filters on scroll (mobile only)
     * Makes the filters bar stick to the top when scrolling past its original position
     */
    function initStickyFilters() {
        // Only run on mobile
        if (window.innerWidth > 768) return;
        
        const filtersContainer = document.getElementById('yolo-ys-advanced-filters');
        if (!filtersContainer) return;
        
        // Create placeholder element to prevent content jump
        let placeholder = document.querySelector('.yolo-ys-filters-placeholder');
        if (!placeholder) {
            placeholder = document.createElement('div');
            placeholder.className = 'yolo-ys-filters-placeholder';
            filtersContainer.parentNode.insertBefore(placeholder, filtersContainer.nextSibling);
        }
        
        let filtersTop = null;
        let filtersHeight = null;
        let isSticky = false;
        
        // Calculate initial position after filters are visible
        function updateFilterPosition() {
            if (!filtersContainer.classList.contains('is-sticky')) {
                filtersTop = filtersContainer.getBoundingClientRect().top + window.pageYOffset;
                filtersHeight = filtersContainer.offsetHeight;
            }
        }
        
        // Initial calculation with delay to ensure filters are visible
        setTimeout(updateFilterPosition, 500);
        
        // Recalculate on resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                // Remove sticky on desktop
                filtersContainer.classList.remove('is-sticky');
                placeholder.classList.remove('active');
                isSticky = false;
            } else {
                updateFilterPosition();
            }
        });
        
        // Scroll handler
        window.addEventListener('scroll', function() {
            if (window.innerWidth > 768) return;
            if (filtersTop === null) {
                updateFilterPosition();
                return;
            }
            
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            
            if (scrollTop >= filtersTop && !isSticky) {
                // Make sticky
                filtersContainer.classList.add('is-sticky');
                placeholder.style.height = filtersHeight + 'px';
                placeholder.classList.add('active');
                isSticky = true;
            } else if (scrollTop < filtersTop && isSticky) {
                // Remove sticky
                filtersContainer.classList.remove('is-sticky');
                placeholder.classList.remove('active');
                isSticky = false;
            }
        }, { passive: true });
        
        console.log('YOLO: Sticky filters initialized for mobile');
    }
    
})(jQuery);
