(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initLitepicker();
        initSearchForm();
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
            searchYachts(dateFrom, dateTo, kind);
        }
    }
    
    /**
     * Search yachts via AJAX
     */
    function searchYachts(dateFrom, dateTo, kind) {
        const resultsContainer = $('#yolo-ys-results-container');
        if (!resultsContainer.length) return;
        
        // Show loading
        const loadingTemplate = $('#yolo-ys-loading-template').html();
        resultsContainer.html(loadingTemplate);
        
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
        const template = $('#yolo-ys-results-template').html();
        
        // Check if no results
        const noResults = data.total_count === 0;
        
        // Simple template rendering (replace with Handlebars if needed)
        let html = template;
        
        // Replace conditionals and data
        html = html.replace(/\{\{total_count\}\}/g, data.total_count);
        
        // Render YOLO boats
        if (data.yolo_boats && data.yolo_boats.length > 0) {
            html = html.replace(/\{\{#if yolo_boats\.length\}\}/g, '');
            html = html.replace(/\{\{\/if\}\}/g, '');
            
            let yoloBoatsHtml = '';
            data.yolo_boats.forEach(boat => {
                yoloBoatsHtml += renderBoatCard(boat, true);
            });
            html = html.replace(/\{\{#each yolo_boats\}\}.*?\{\{\/each\}\}/s, yoloBoatsHtml);
        } else {
            html = html.replace(/\{\{#if yolo_boats\.length\}\}.*?\{\{\/if\}\}/s, '');
        }
        
        // Render friend boats
        if (data.friend_boats && data.friend_boats.length > 0) {
            html = html.replace(/\{\{#if friend_boats\.length\}\}/g, '');
            
            let friendBoatsHtml = '';
            data.friend_boats.forEach(boat => {
                friendBoatsHtml += renderBoatCard(boat, false);
            });
            html = html.replace(/\{\{#each friend_boats\}\}.*?\{\{\/each\}\}/s, friendBoatsHtml);
        } else {
            html = html.replace(/\{\{#if friend_boats\.length\}\}.*?\{\{\/if\}\}/s, '');
        }
        
        // Handle no results
        if (noResults) {
            html = html.replace(/\{\{#if no_results\}\}/g, '');
            html = html.replace(/\{\{\/if\}\}/g, '');
        } else {
            html = html.replace(/\{\{#if no_results\}\}.*?\{\{\/if\}\}/s, '');
        }
        
        resultsContainer.html(html);
    }
    
    /**
     * Render boat card
     */
    function renderBoatCard(boat, isYolo) {
        const badgeHtml = isYolo ? '<div class="yolo-ys-yacht-badge">YOLO</div>' : '';
        const yoloClass = isYolo ? 'yolo-boat' : '';
        const companyInfo = !isYolo ? '<p class="yolo-ys-company-info">Partner Company</p>' : '';
        
        // Image or placeholder
        const imageHtml = boat.image_url 
            ? `<img src="${boat.image_url}" alt="${boat.yacht}" style="width:100%;height:200px;object-fit:cover;border-radius:8px 8px 0 0;">` 
            : '<span class="yolo-ys-yacht-image-placeholder">⛵</span>';
        
        // Details URL
        const detailsUrl = boat.details_url || '#';
        
        return `
            <div class="yolo-ys-yacht-card ${yoloClass}">
                ${badgeHtml}
                <div class="yolo-ys-yacht-image">
                    ${imageHtml}
                </div>
                <div class="yolo-ys-yacht-info">
                    <h3 class="yolo-ys-yacht-name">${boat.yacht || 'Unknown'}</h3>
                    <p class="yolo-ys-yacht-type">${boat.product || ''}</p>
                    <div class="yolo-ys-yacht-location">
                        📍 ${boat.startBase || 'Location not specified'}
                    </div>
                    <div class="yolo-ys-yacht-price">
                        <div>
                            <span class="yolo-ys-price-amount">${boat.price || '0'}</span>
                            <span class="yolo-ys-price-currency">${boat.currency || 'EUR'}</span>
                        </div>
                        <a href="${detailsUrl}" class="yolo-ys-view-button">View Details</a>
                    </div>
                    ${companyInfo}
                </div>
            </div>
        `;
    }
    
})(jQuery);
