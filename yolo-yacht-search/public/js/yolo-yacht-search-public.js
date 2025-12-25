(function($) {
    'use strict';
    
    // v81.17: Server-side filtering state
    let currentSearchParams = {
        dateFrom: '',
        dateTo: '',
        kind: ''
    };
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
        
        // Close equipment dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.filter-equipment').length) {
                $('#equipment-dropdown-menu').removeClass('show');
            }
        });
        
        // Update equipment selected text
        $('input[name="equipment[]"]').on('change', function() {
            updateEquipmentText();
        });
        
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
     * Clear all filters
     */
    function clearFilters() {
        $('#filter-cabins').val('');
        $('#filter-length').val('');
        $('#filter-year').val('');
        $('#filter-location').val('');
        $('#filter-sort').val('price_asc');
        $('input[name="equipment[]"]').prop('checked', false);
        updateEquipmentText();
        
        // Re-search with cleared filters
        currentPage = 1;
        searchYachtsFiltered();
    }
    
    /**
     * Get current filter values
     */
    function getFilterValues() {
        const equipment = [];
        $('input[name="equipment[]"]:checked').each(function() {
            equipment.push($(this).val());
        });
        
        return {
            cabins: $('#filter-cabins').val() || 0,
            length: $('#filter-length').val() || 0,
            year: $('#filter-year').val() || 0,
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
     * Search yachts with filters via AJAX (v81.17)
     */
    function searchYachtsFiltered() {
        if (isLoading) return;
        isLoading = true;
        
        const filters = getFilterValues();
        
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
                length: filters.length,
                year: filters.year,
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
        
        // Update results count
        const resultsCount = $('#yolo-ys-results-count');
        const countText = $('#results-count-text');
        
        if (data.total_count > 0) {
            countText.text(`Found ${data.total_count} yacht(s)`);
            resultsCount.show();
        } else if (isNewSearch && (!data.featured_boats || data.featured_boats.length === 0)) {
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
        $('#load-more-btn').prop('disabled', true).text('Loading...');
    }
    
    /**
     * Hide loading spinner
     */
    function hideLoading() {
        $('#yolo-ys-loading').hide();
        $('#load-more-btn').prop('disabled', false);
        $('#load-more-text').text('Load More');
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
     * Render boat card
     */
    function renderBoatCard(boat, isYolo) {
        const yoloClass = isYolo ? 'yolo-yacht' : '';
        
        // Image or placeholder
        const imageHtml = boat.image_url 
            ? `<img src="${boat.image_url}" alt="${boat.yacht}" loading="lazy">` 
            : '<div class="yolo-ys-yacht-placeholder">⛵</div>';
        
        // YOLO logo for YOLO yachts
        const yoloLogoHtml = isYolo 
            ? '<img src="https://yolo-charters.com/wp-content/uploads/2025/11/logo-for-YOLO-charters.png" alt="YOLO Charters" class="yolo-ys-yacht-logo">' 
            : '';
        
        // Details URL
        const detailsUrl = boat.details_url || '#';
        
        // Format specs
        const lengthFt = boat.length ? Math.round(boat.length * 3.28084) : 0;
        
        // Split yacht name into name and model
        let yachtName = boat.yacht || 'Unknown';
        let yachtModel = '';
        const nameParts = yachtName.split(' ');
        if (nameParts.length > 1) {
            yachtName = nameParts[0];
            yachtModel = nameParts.slice(1).join(' ');
        }
        
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
                </div>
                <div class="yolo-ys-yacht-content">
                    <div class="yolo-ys-yacht-location">
                        <span class="yolo-ys-location-icon">📍</span>
                        <span class="yolo-ys-location-text">${boat.startBase || 'Location not specified'}</span>
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
    
})(jQuery);
