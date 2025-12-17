/**
 * YOLO Yacht Search - Client-Side Analytics
 * @since 41.19
 * @updated 41.25 - Removed GA4/FB Pixel initialization (handled by site-wide plugin)
 * @updated 41.26 - Switched to dataLayer.push() for proper Google Tag Manager integration
 * @updated 41.27 - Added Facebook Pixel event deduplication with server-side CAPI
 * 
 * IMPORTANT: This script works in conjunction with server-side Facebook Conversions API.
 * - Server-side events (ViewContent, Lead, Purchase) are sent from PHP
 * - Client-side events (AddToCart, InitiateCheckout, AddPaymentInfo, Search) are sent here
 * - All events use event_id for deduplication to prevent double-counting
 * 
 * Custom Events Tracked (7 total):
 * 1. search - User searches for yachts (client-side)
 * 2. view_item - User views yacht details page (server-side ViewContent)
 * 3. add_to_cart - User selects a week/price (client-side AddToCart)
 * 4. begin_checkout - User clicks "Book Now" (client-side InitiateCheckout)
 * 5. add_payment_info - User submits booking form (client-side AddPaymentInfo)
 * 6. generate_lead - User requests a quote (server-side Lead)
 * 7. purchase - Booking completed (server-side Purchase from Stripe webhook)
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
     * Generate unique event ID for Facebook deduplication
     * @returns {string} Unique event ID
     */
    function generateEventId() {
        return 'evt_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
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
     * Send event to Facebook Pixel with deduplication
     * Includes retry logic to wait for fbq to be available (PixelYourSite loads async)
     * @param {string} eventName - Facebook event name (e.g., 'AddToCart', 'InitiateCheckout')
     * @param {object} params - Event parameters
     * @param {string} eventId - Event ID for deduplication (optional, will be generated if not provided)
     * @param {number} retryCount - Internal retry counter
     */
    function sendToFacebookPixel(eventName, params = {}, eventId = null, retryCount = 0) {
        // Generate event ID if not provided (do this first so it's consistent across retries)
        if (!eventId) {
            eventId = generateEventId();
        }
        
        // Check if Facebook Pixel is loaded
        if (typeof fbq !== 'function') {
            // Retry up to 10 times (5 seconds total) waiting for PixelYourSite to load
            if (retryCount < 10) {
                debug('Facebook Pixel not loaded yet, retrying in 500ms... (attempt ' + (retryCount + 1) + '/10)');
                setTimeout(function() {
                    sendToFacebookPixel(eventName, params, eventId, retryCount + 1);
                }, 500);
                return;
            }
            debug('Facebook Pixel not loaded after 10 retries, skipping event:', eventName);
            return;
        }
        
        // Send to Facebook Pixel with event ID for deduplication
        debug('fbq track:', eventName, params, 'eventID:', eventId);
        fbq('track', eventName, params, {eventID: eventId});
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
            debug('Initializing custom yacht booking events...');
            this.autoTrack();
            this.bindEvents();
        },
        
        autoTrack: function() {
            // Auto-track view_item on yacht details pages
            // Note: ViewContent is sent server-side, we only send to dataLayer for GA4
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
            
            // Book Now (add to cart)
            $(document).on('click', '#bookNowBtn, .book-now-btn', function() {
                self.trackSelectWeek(getYachtData());
            });
            
            // Booking form submission (initiate checkout / proceed to checkout)
            $(document).on('submit', '#booking-form, .yolo-booking-form', function() {
                self.trackBeginCheckout(getYachtData());
            });
            
            // Quote request (generate lead)
            // Note: Lead is sent server-side, we only send to dataLayer for GA4
            $(document).on('submit', '.quote-form, #quote-request-form', function() {
                self.trackQuoteRequest(getYachtData());
            });
        },
        
        /**
         * Track search event
         * Sent client-side to Facebook Pixel
         */
        trackSearch: function(p) {
            // Send to dataLayer for GA4
            pushToDataLayer('search', {
                search_term: p.search_term || ''
            });
            
            // Send to Facebook Pixel
            sendToFacebookPixel('Search', {
                search_string: p.search_term || ''
            });
        },
        
        /**
         * Track yacht view (view_item)
         * Sent to BOTH server-side (CAPI) and client-side (Pixel) for proper deduplication
         */
        trackViewYacht: function(p) {
            // Send to dataLayer for GA4
            pushToDataLayer('view_item', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
            
            // Send to Facebook Pixel with event_id from server-side for deduplication
            const eventId = window.fbViewContentEventId || null;
            sendToFacebookPixel('ViewContent', {
                content_type: 'product',
                content_ids: [String(p.id)],
                content_name: p.name,
                currency: p.currency,
                value: p.price
            }, eventId);
        },
        
        /**
         * Track week selection (add_to_cart)
         * Sent to BOTH server-side (CAPI) and client-side (Pixel) for proper deduplication
         * @param {object} p - Yacht data
         * @param {string} eventId - Optional event ID from server-side for deduplication
         */
        trackSelectWeek: function(p, eventId) {
            // Send to dataLayer for GA4
            pushToDataLayer('add_to_cart', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
            
            // Send to Facebook Pixel with event_id from server-side for deduplication
            sendToFacebookPixel('AddToCart', {
                content_type: 'product',
                content_ids: [String(p.yacht_id || p.id)],
                content_name: p.yacht_name || p.name,
                currency: p.currency,
                value: p.price
            }, eventId || null);
        },
        
        /**
         * Track begin checkout
         * Sent to BOTH server-side (CAPI) and client-side (Pixel) for proper deduplication
         * @param {object} p - Yacht data
         * @param {string} eventId - Optional event ID from server-side for deduplication
         */
        trackBeginCheckout: function(p, eventId) {
            // Send to dataLayer for GA4
            pushToDataLayer('begin_checkout', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
            
            // Send to Facebook Pixel with event_id from server-side for deduplication
            sendToFacebookPixel('InitiateCheckout', {
                content_type: 'product',
                content_ids: [String(p.yacht_id || p.id)],
                content_name: p.yacht_name || p.name,
                currency: p.currency,
                value: p.price
            }, eventId || null);
        },
        
        /**
         * Track payment info added
         * Sent client-side to Facebook Pixel
         */
        trackAddPaymentInfo: function(p) {
            // Send to dataLayer for GA4
            pushToDataLayer('add_payment_info', {
                currency: p.currency,
                value: p.price,
                items: [{
                    item_id: String(p.id),
                    item_name: p.name,
                    price: p.price
                }]
            });
            
            // Send to Facebook Pixel
            sendToFacebookPixel('AddPaymentInfo', {
                content_type: 'product',
                content_ids: [String(p.id)],
                content_name: p.name,
                currency: p.currency,
                value: p.price
            });
        },
        
        /**
         * Track quote request (generate_lead)
         * Server-side Lead is sent from PHP, this only sends to dataLayer for GA4
         */
        trackQuoteRequest: function(p) {
            // Send to dataLayer for GA4
            pushToDataLayer('generate_lead', {
                currency: p.currency,
                value: p.price
            });
            
            // Note: Lead is sent server-side with user data (email, phone, name)
            // No need to send client-side to avoid duplication
        },
        
        /**
         * Track lead generation (quote request submission)
         * Sent to BOTH server-side (CAPI) and client-side (Pixel) for proper deduplication
         * @param {object} p - Lead data
         * @param {string} eventId - Event ID from server-side for deduplication
         */
        trackLead: function(p, eventId) {
            // Send to dataLayer for GA4
            pushToDataLayer('generate_lead', {
                currency: 'EUR',
                value: p.value || 0
            });
            
            // Send to Facebook Pixel with event_id from server-side for deduplication
            sendToFacebookPixel('Lead', {
                content_name: p.yacht_name || '',
                currency: 'EUR',
                value: p.value || 0
            }, eventId || null);
        },
        
        /**
         * Track purchase (called from server-side after Stripe payment)
         * Server-side Purchase is sent from PHP, this only sends to dataLayer for GA4
         */
        trackPurchase: function(p) {
            // Send to dataLayer for GA4
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
            
            // Note: Purchase is sent server-side with user data and better attribution
            // No need to send client-side to avoid duplication
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        YoloAnalytics.init();
    });
})(jQuery);
