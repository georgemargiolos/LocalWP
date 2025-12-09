/**
 * YOLO Yacht Search - Client-Side Analytics
 * @since 41.19
 * @updated 41.25 - Removed GA4/FB Pixel initialization (handled by site-wide plugin)
 * @updated 41.26 - Switched to dataLayer.push() for proper Google Tag Manager integration
 * 
 * IMPORTANT: This script pushes events to the dataLayer for Google Tag Manager.
 * You need to configure GTM to capture these events and send them to GA4/Facebook Pixel.
 * 
 * Custom Events Tracked (7 total):
 * 1. search - User searches for yachts
 * 2. view_item - User views yacht details page
 * 3. add_to_cart - User selects a week/price
 * 4. begin_checkout - User clicks "Book Now"
 * 5. add_payment_info - User submits booking form
 * 6. generate_lead - User requests a quote
 * 7. purchase - Booking completed (triggered from Stripe webhook)
 */
(function($) {
    'use strict';
    
    const config = window.yoloAnalyticsConfig || {};
    
    // Initialize dataLayer if it doesn't exist
    window.dataLayer = window.dataLayer || [];
    
    function debug(...args) {
        if (config.debug_mode) console.log('[YOLO Analytics]', ...args);
    }
    
    /**
     * Push event to dataLayer for GTM
     * @param {string} event - Event name (e.g., 'view_item', 'purchase')
     * @param {object} params - Event parameters
     */
    function pushToDataLayer(event, params = {}) {
        const eventData = {
            event: event,
            ...params
        };
        
        debug('dataLayer.push:', eventData);
        window.dataLayer.push(eventData);
    }
    
    /**
     * Get yacht data from current page
     */
    function getYachtData() {
        const urlParams = new URLSearchParams(window.location.search);
        return {
            id: urlParams.get('yacht_id') || urlParams.get('yacht'),
            name: $('.yacht-title, .yacht-name, h1').first().text().trim() || 'Yacht',
            price: parseFloat($('.yacht-price, #selectedPriceFinal').text().replace(/[^\d.]/g, '')) || 0,
            currency: config.currency || 'EUR'
        };
    }
    
    window.YoloAnalytics = {
        init: function() {
            debug('Initializing custom yacht booking events for GTM...');
            this.autoTrack();
            this.bindEvents();
        },
        
        autoTrack: function() {
            // Auto-track view_item on yacht details pages
            if ($('.yolo-yacht-details, .yolo-yacht-details-v3').length) {
                const yacht = getYachtData();
                if (yacht.id) this.trackViewYacht(yacht);
            }
        },
        
        bindEvents: function() {
            const self = this;
            
            // Search
            $(document).on('submit', '.yolo-search-form, #yacht-search-form, #yolo-ys-search-form', function() {
                self.trackSearch({ search_term: $(this).find('input[name="search"]').val() });
            });
            
            // Week selection (add to cart)
            $(document).on('click', '.week-price-card, .price-carousel-item', function() {
                const yacht = getYachtData();
                yacht.price = parseFloat($(this).find('.price').text().replace(/[^\d.]/g, '')) || yacht.price;
                self.trackSelectWeek(yacht);
            });
            
            // Book Now (begin checkout)
            $(document).on('click', '#bookNowBtn, .book-now-btn', function() {
                self.trackBeginCheckout(getYachtData());
            });
            
            // Booking form submission (add payment info)
            $(document).on('submit', '#booking-form, .yolo-booking-form', function() {
                self.trackAddPaymentInfo(getYachtData());
            });
            
            // Quote request (generate lead)
            $(document).on('submit', '.quote-form, #quote-request-form', function() {
                self.trackQuoteRequest(getYachtData());
            });
        },
        
        /**
         * Track search event
         */
        trackSearch: function(p) {
            pushToDataLayer('search', {
                search_term: p.search_term || ''
            });
        },
        
        /**
         * Track yacht view (view_item)
         */
        trackViewYacht: function(p) {
            pushToDataLayer('view_item', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
        },
        
        /**
         * Track week selection (add_to_cart)
         */
        trackSelectWeek: function(p) {
            pushToDataLayer('add_to_cart', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
        },
        
        /**
         * Track begin checkout
         */
        trackBeginCheckout: function(p) {
            pushToDataLayer('begin_checkout', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
        },
        
        /**
         * Track payment info added
         */
        trackAddPaymentInfo: function(p) {
            pushToDataLayer('add_payment_info', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
        },
        
        /**
         * Track quote request (generate_lead)
         */
        trackQuoteRequest: function(p) {
            pushToDataLayer('generate_lead', {
                currency: p.currency,
                value: p.price
            });
        },
        
        /**
         * Track purchase (called from server-side after Stripe payment)
         */
        trackPurchase: function(p) {
            pushToDataLayer('purchase', {
                transaction_id: p.transaction_id,
                currency: p.currency,
                value: p.value,
                items: [{
                    item_id: String(p.yacht_id),
                    item_name: p.yacht_name,
                    price: p.value
                }]
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        YoloAnalytics.init();
    });
})(jQuery);
