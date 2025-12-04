(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initLitepicker();
        initSearchForm();
        initResultsSearchForm();
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
            startDate: firstSaturday,
            endDate: nextSaturday,
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
                
                // Only allow Saturdays (can be made configurable)
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
        
        // Store picker instance
        window.yoloResultsDatePicker = picker;
        
        // Handle form submission
        $('#yolo-ys-results-form').on('submit', function(e) {
            e.preventDefault();
            performResultsSearch();
        });
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
            searchYachts(dateFrom, dateTo, kind);
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
            // Pre-fill results search form
            prefillResultsSearchForm(dateFrom, dateTo, kind);
            
            // Perform search
            searchYachts(dateFrom, dateTo, kind);
        }
    }
    
    /**
     * Pre-fill results search form with current search params
     */
    function prefillResultsSearchForm(dateFrom, dateTo, kind) {
        // Only proceed if the form exists
        const formElement = $('#yolo-ys-results-search-form');
        if (!formElement.length) return;
        
        // Show the search form
        formElement.show();
        
        // Set boat type
        $('#yolo-ys-results-boat-type').val(kind);
        
        // Set dates in picker (may not be initialized yet, that's OK)
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
        
        // Update URL and search
        const url = new URL(window.location.href);
        url.searchParams.set('dateFrom', dateFrom);
        url.searchParams.set('dateTo', dateTo);
        url.searchParams.set('kind', kind);
        window.history.pushState({}, '', url);
        
        // Perform search
        searchYachts(dateFrom, dateTo, kind);
    }
    
    /**
     * Search yachts via AJAX
     */
    function searchYachts(dateFrom, dateTo, kind) {
        const resultsContainer = $('#yolo-ys-results-container');
        if (!resultsContainer.length) return;
        
        // Show loading
        resultsContainer.html(`
            <div class="yolo-ys-loading">
                <div class="yolo-ys-loading-spinner"></div>
                <p>Searching for available yachts...</p>
            </div>
        `);
        
        // AJAX request
        $.ajax({
            url: yoloYSData.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_search_yachts',
                nonce: yoloYSData.nonce,
                dateFrom: dateFrom,
                dateTo: dateTo,
                kind: kind
            },
            success: function(response) {
                if (response.success) {
                    displayResults(response);
                } else {
                    resultsContainer.html('<div class="yolo-ys-no-results"><h3>Error</h3><p>Failed to load results.</p></div>');
                }
            },
            error: function() {
                resultsContainer.html('<div class="yolo-ys-no-results"><h3>Error</h3><p>Failed to connect to server.</p></div>');
            }
        });
    }
    
    /**
     * Display search results
     */
    function displayResults(data) {
        const resultsContainer = $('#yolo-ys-results-container');
        
        // Check if no results
        if (data.total_count === 0) {
            resultsContainer.html(`
                <div class="yolo-ys-no-results">
                    <h3>No Yachts Found</h3>
                    <p>Try adjusting your search criteria or dates.</p>
                </div>
            `);
            return;
        }
        
        // Build HTML directly
        let html = `
            <div class="yolo-ys-results-header">
                <h2>Search Results</h2>
                <p class="yolo-ys-results-count">Found ${data.total_count} yacht(s) available</p>
            </div>
        `;
        
        // Render YOLO boats
        if (data.yolo_boats && data.yolo_boats.length > 0) {
            html += `
                <div class="yolo-ys-section-header">
                    <h3>YOLO Charters Fleet</h3>
                </div>
                <div class="yolo-ys-results-grid">
            `;
            data.yolo_boats.forEach(boat => {
                html += renderBoatCard(boat, true);
            });
            html += '</div>';
        }
        
        // Render friend boats
        if (data.friend_boats && data.friend_boats.length > 0) {
            html += `
                <div class="yolo-ys-section-header friends">
                    <h3>Partner Fleet</h3>
                </div>
                <div class="yolo-ys-results-grid">
            `;
            data.friend_boats.forEach(boat => {
                html += renderBoatCard(boat, false);
            });
            html += '</div>';
        }
        
        resultsContainer.html(html);
    }
    
    /**
     * Render boat card
     * 
     * UPDATED (v2.5.4): Complete redesign to match "Our Yachts" section
     * - Strikethrough original price when discounted
     * - Discount percentage badge (red)
     * - Final discounted price (prominent, green)
     * - 3-column grid layout
     * - Modern card design with hover effects
     * - Proper image handling with aspect ratio
     */
    function renderBoatCard(boat, isYolo) {
        const yoloClass = isYolo ? 'yolo-yacht' : '';
        
        // Image or placeholder
        const imageHtml = boat.image_url 
            ? `<img src="${boat.image_url}" alt="${boat.yacht}" loading="lazy">` 
            : '<div class="yolo-ys-yacht-placeholder">⛵</div>';
        
        // Details URL
        const detailsUrl = boat.details_url || '#';
        
        // Format specs
        const lengthFt = boat.length ? Math.round(boat.length * 3.28084) : 0;
        
        // Split yacht name into name and model
        // Example: "Lemon Sun Odyssey 469" -> "Lemon" + "Sun Odyssey 469"
        let yachtName = boat.yacht || 'Unknown';
        let yachtModel = '';
        const nameParts = yachtName.split(' ');
        if (nameParts.length > 1) {
            yachtName = nameParts[0]; // First word is the name
            yachtModel = nameParts.slice(1).join(' '); // Rest is the model
        }
        
        // Helper function to format price with comma for thousands, dot for decimals
        const formatPrice = (price) => {
            if (!price || isNaN(price)) return '0.00';
            return Number(price).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        };
        
        // Refit display (if available)
        let refitDisplay = '';
        if (boat.refit_year) {
            refitDisplay = `<span class="yolo-ys-refit-note">Refit: ${boat.refit_year}</span>`;
        }
        
        // Price display with discount logic
        let priceHtml = '';
        
        // Check if we have discount information
        const hasDiscount = boat.original_price && boat.discount_percentage && 
                           parseFloat(boat.original_price) > parseFloat(boat.price);
        
        if (hasDiscount) {
            // Show original price (strikethrough), discount badge, and final price
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
            // No discount - show regular price
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
            <div class="yolo-ys-yacht-card ${yoloClass}">
                <div class="yolo-ys-yacht-image">
                    <a href="${detailsUrl}">
                        ${imageHtml}
                    </a>
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
                        <div class="yolo-ys-spec-item">
                            <div class="yolo-ys-spec-value">${boat.cabins || 0}</div>
                            <div class="yolo-ys-spec-label">Cabins</div>
                        </div>
                        <div class="yolo-ys-spec-item">
                            <div class="yolo-ys-spec-value">
                                ${boat.year_of_build || 'N/A'}
                                ${refitDisplay}
                            </div>
                            <div class="yolo-ys-spec-label">Built year</div>
                        </div>
                        <div class="yolo-ys-spec-item">
                            <div class="yolo-ys-spec-value">${lengthFt} ft</div>
                            <div class="yolo-ys-spec-label">Length</div>
                        </div>
                    </div>
                    ${priceHtml}
                    <a href="${detailsUrl}" class="yolo-ys-details-btn">DETAILS</a>
                </div>
            </div>
        `;
    }
    
})(jQuery);
