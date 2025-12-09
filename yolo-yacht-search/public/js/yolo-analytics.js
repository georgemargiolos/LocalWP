/**
 * YOLO Yacht Search - Client-Side Analytics
 * @since 41.19
 * @updated 41.25 - Removed GA4/FB Pixel initialization (handled by site-wide plugin)
 *                  Kept 7 custom yacht booking funnel events that integrate with existing tracking
 * 
 * IMPORTANT: This script assumes GA4 gtag() and Facebook Pixel fbq() are already loaded
 * by your site-wide analytics plugin (e.g., Site Kit, MonsterInsights, PixelYourSite).
 * 
 * Custom Events Tracked:
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
    
    function debug(...args) {
        if (config.debug_mode) console.log('[YOLO Analytics]', ...args);
    }
    
    // Check if external plugins have loaded GA4/FB Pixel
    function hasGA4() { 
        return typeof gtag === 'function'; 
    }
    
    function hasFB() { 
        return typeof fbq === 'function'; 
    }
    
    function trackGA4(event, params = {}) {
        if (!hasGA4()) {
            debug('GA4 not available - ensure gtag() is loaded by site-wide analytics plugin');
            return;
        }
        debug('GA4:', event, params);
        gtag('event', event, params);
    }
    
    function trackFB(event, params = {}) {
        if (!hasFB()) {
            debug('Facebook Pixel not available - ensure fbq() is loaded by site-wide analytics plugin');
            return;
        }
        debug('FB:', event, params);
        fbq('track', event, params);
    }
    
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
            debug('Initializing custom yacht booking events...');
            if (!hasGA4() && !hasFB()) {
                debug('WARNING: Neither GA4 nor Facebook Pixel detected. Install site-wide analytics plugin.');
            }
            this.autoTrack();
            this.bindEvents();
        },
        
        autoTrack: function() {
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
            
            // Week selection
            $(document).on('click', '.week-price-card, .price-carousel-item', function() {
                const yacht = getYachtData();
                yacht.price = parseFloat($(this).find('.price').text().replace(/[^\d.]/g, '')) || yacht.price;
                self.trackSelectWeek(yacht);
            });
            
            // Book Now
            $(document).on('click', '#bookNowBtn, .book-now-btn', function() {
                self.trackBeginCheckout(getYachtData());
            });
            
            // Booking form
            $(document).on('submit', '#booking-form, .yolo-booking-form', function() {
                self.trackAddPaymentInfo(getYachtData());
            });
            
            // Quote
            $(document).on('submit', '.quote-form, #quote-request-form', function() {
                self.trackQuoteRequest(getYachtData());
            });
        },
        
        trackSearch: function(p) {
            trackGA4('search', { search_term: p.search_term || '' });
            trackFB('Search', { search_string: p.search_term || '' });
        },
        
        trackViewYacht: function(p) {
            trackGA4('view_item', {
                currency: p.currency, value: p.price,
                items: [{ item_id: String(p.id), item_name: p.name, price: p.price }]
            });
            trackFB('ViewContent', {
                content_type: 'product', content_ids: [String(p.id)],
                content_name: p.name, currency: p.currency, value: p.price
            });
        },
        
        trackSelectWeek: function(p) {
            trackGA4('add_to_cart', {
                currency: p.currency, value: p.price,
                items: [{ item_id: String(p.id), item_name: p.name, price: p.price }]
            });
            trackFB('AddToCart', {
                content_type: 'product', content_ids: [String(p.id)],
                currency: p.currency, value: p.price
            });
        },
        
        trackBeginCheckout: function(p) {
            trackGA4('begin_checkout', {
                currency: p.currency, value: p.price,
                items: [{ item_id: String(p.id), item_name: p.name, price: p.price }]
            });
            trackFB('InitiateCheckout', {
                content_type: 'product', content_ids: [String(p.id)],
                currency: p.currency, value: p.price
            });
        },
        
        trackAddPaymentInfo: function(p) {
            trackGA4('add_payment_info', {
                currency: p.currency, value: p.price,
                items: [{ item_id: String(p.id), item_name: p.name, price: p.price }]
            });
            trackFB('AddPaymentInfo', {
                content_type: 'product', content_ids: [String(p.id)],
                currency: p.currency, value: p.price
            });
        },
        
        trackQuoteRequest: function(p) {
            trackGA4('generate_lead', { currency: p.currency, value: p.price });
            trackFB('Lead', { content_ids: [String(p.id)], currency: p.currency, value: p.price });
        },
        
        trackPurchase: function(p) {
            trackGA4('purchase', {
                transaction_id: p.transaction_id, currency: p.currency, value: p.value,
                items: [{ item_id: String(p.yacht_id), item_name: p.yacht_name, price: p.value }]
            });
            trackFB('Purchase', {
                content_type: 'product', content_ids: [String(p.yacht_id)],
                currency: p.currency, value: p.value
            });
        }
    };
    
    $(document).ready(function() {
        // Always initialize - custom events will use external GA4/FB Pixel
        YoloAnalytics.init();
    });
})(jQuery);
