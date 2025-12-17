<?php
/**
 * Yacht Details v3 Scripts - FIXED VERSION
 * Version: 2.7.0
 * 
 * FIXES:
 * ✓ Added missing toggleDescription() function
 * ✓ Added toggleQuoteForm() function
 * ✓ v60.6.8: Defensive variable initialization to prevent PHP warnings
 */

// ============================================
// DEFENSIVE VARIABLE INITIALIZATION
// Prevents "property of non-object" PHP warnings that corrupt JavaScript
// ============================================
$fb_event_id = isset($fb_event_id) && is_string($fb_event_id) ? $fb_event_id : '';
$yacht_id = isset($yacht_id) ? sanitize_text_field($yacht_id) : '';
$yacht = isset($yacht) ? $yacht : null;
// Clean yacht name - remove newlines and normalize whitespace for safe JavaScript output
$yacht_name_safe = '';
if ($yacht && isset($yacht->name)) {
    $yacht_name_safe = $yacht->name;
    $yacht_name_safe = preg_replace('/[\r\n]+/', ' ', $yacht_name_safe);
    $yacht_name_safe = preg_replace('/\s+/', ' ', $yacht_name_safe);
    $yacht_name_safe = trim($yacht_name_safe);
}
$yacht_id_safe = ($yacht && isset($yacht->id)) ? $yacht->id : $yacht_id;
?>
<script>
// Facebook event_id for deduplication (from server-side CAPI)
// MUST use window. so yolo-analytics.js can access it
window.fbViewContentEventId = <?php echo json_encode($fb_event_id); ?>;

// ============================================
// FIXED: Toggle Description "More..." / "Less"
// This function was MISSING - causing the button to not work!
// ============================================
function toggleDescription(button) {
    const section = button.closest('.yacht-description-content');
    const fullText = section.querySelector('.description-full');
    const moreSpan = button.querySelector('.toggle-more');
    const lessSpan = button.querySelector('.toggle-less');
    
    if (!fullText) return;
    
    if (fullText.style.display === 'none' || fullText.style.display === '') {
        fullText.style.display = 'block';
        if (moreSpan) moreSpan.style.display = 'none';
        if (lessSpan) lessSpan.style.display = 'inline';
    } else {
        fullText.style.display = 'none';
        if (moreSpan) moreSpan.style.display = 'inline';
        if (lessSpan) lessSpan.style.display = 'none';
    }
}

// Toggle Quote Form visibility
function toggleQuoteForm() {
    const form = document.getElementById('quoteForm');
    if (form) {
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            // Scroll to form
            form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            form.style.display = 'none';
        }
    }
}

// ============================================
// YouTube Video Click Handler (v65.16)
// Opens YouTube video in lightbox when clicking play button in Swiper
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Handle YouTube video thumbnail clicks
    document.querySelectorAll('.swiper-slide-video .video-thumbnail-wrapper').forEach(function(wrapper) {
        wrapper.addEventListener('click', function(e) {
            e.preventDefault();
            const slide = wrapper.closest('.swiper-slide-video');
            const videoId = slide.dataset.videoId;
            if (videoId) {
                openYouTubeLightbox(videoId);
            }
        });
    });
});

// Open YouTube video in lightbox
function openYouTubeLightbox(videoId) {
    const lightboxHTML = `
        <div id="youtube-lightbox" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
             background: rgba(0,0,0,0.9); z-index: 99999; display: flex; align-items: center; justify-content: center;">
            <button onclick="closeYouTubeLightbox()" style="position: absolute; top: 20px; right: 30px; 
                    background: none; border: none; color: #fff; font-size: 40px; cursor: pointer; z-index: 100000;">&times;</button>
            <div style="width: 90%; max-width: 1000px; aspect-ratio: 16/9;">
                <iframe src="https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0" 
                        style="width: 100%; height: 100%; border: none;" 
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                        allowfullscreen></iframe>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', lightboxHTML);
    document.body.style.overflow = 'hidden';
    
    // Close on escape key
    document.addEventListener('keydown', function escHandler(e) {
        if (e.key === 'Escape') {
            closeYouTubeLightbox();
            document.removeEventListener('keydown', escHandler);
        }
    });
    
    // Close on background click
    document.getElementById('youtube-lightbox').addEventListener('click', function(e) {
        if (e.target.id === 'youtube-lightbox') {
            closeYouTubeLightbox();
        }
    });
}

// Close YouTube lightbox
function closeYouTubeLightbox() {
    const lightbox = document.getElementById('youtube-lightbox');
    if (lightbox) {
        lightbox.remove();
        document.body.style.overflow = '';
    }
}

// Show custom dates modal for non-Saturday bookings
function showCustomDatesModal(dateFrom, dateTo) {
    // Safe variables for use in template literal (using json_encode for proper escaping)
    const yachtNameForModal = <?php echo json_encode($yacht_name_safe); ?>;
    const yachtIdForModal = <?php echo json_encode($yacht_id_safe); ?>;
    
    // Create modal HTML - Using CSS classes from booking-modal.css
    const modalHTML = `
        <div id="customDatesModal" class="yolo-quote-modal">
            <div class="yolo-quote-modal-content">
                <h3 class="text-primary mb-3">⚠️ Custom Dates Required</h3>
                <p class="text-secondary lh-lg">
                    We charter our yachts from <strong>Saturday to Saturday</strong>. 
                    If you need something special or custom dates, please fill this form and we'll get back to you.
                </p>
                <form id="customDatesForm" class="mt-3">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Your Name *" required>
                    <input type="email" name="email" class="form-control mb-2" placeholder="Your Email *" required>
                    <input type="tel" name="phone" class="form-control mb-2" placeholder="Your Phone">
                    <textarea name="message" rows="4" class="form-control mb-2" placeholder="Message">I would like to charter ${yachtNameForModal} from ${dateFrom} to ${dateTo}. Please contact me with availability and pricing.</textarea>
                    <input type="hidden" name="yacht_id" value="${yachtIdForModal}">
                    <input type="hidden" name="yacht_name" value="${yachtNameForModal}">
                    <input type="hidden" name="date_from" value="${dateFrom}">
                    <input type="hidden" name="date_to" value="${dateTo}">
                    <div class="yolo-quote-modal-buttons">
                        <button type="submit" class="yolo-quote-btn-submit">SEND REQUEST</button>
                        <button type="button" onclick="closeCustomDatesModal()" class="yolo-quote-btn-cancel">${cancelText}</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Handle form submission
    document.getElementById('customDatesForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'yolo_submit_custom_quote');
        
        // Show loading
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'SENDING...';
        submitBtn.disabled = true;
        
        // Submit via AJAX
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Thank you! We will contact you shortly about your custom charter request.');
                closeCustomDatesModal();
            } else {
                alert('Failed to send request. Please try again or contact us directly.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send request. Please try again.');
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

function closeCustomDatesModal() {
    const modal = document.getElementById('customDatesModal');
    if (modal) {
        modal.remove();
    }
}

// Format price with standard formatting (comma for thousands, dot for decimals)
function formatPrice(price) {
    if (!price) return '0.00';
    // Convert to number and format with 2 decimals
    const num = Number(price);
    // Format with standard style: 18,681.00
    return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Initialize Litepicker after DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('dateRangePicker');
    if (dateInput && typeof Litepicker !== 'undefined') {
        // Get initial dates from data attributes (passed from search)
        const initDateFrom = dateInput.dataset.initDateFrom;
        const initDateTo = dateInput.dataset.initDateTo;
        
        const pickerConfig = {
            element: dateInput,
            singleMode: false,
            numberOfMonths: 2,
            numberOfColumns: 2,
            format: 'DD.MM.YYYY',
            minDate: new Date(),
            autoApply: true,
            tooltipText: {
                one: 'night',
                other: 'nights'
            },
            tooltipNumber: (totalDays) => {
                return totalDays - 1;
            }
        };
        
        // Set initial date range if provided
        if (initDateFrom && initDateTo) {
            pickerConfig.startDate = new Date(initDateFrom);
            pickerConfig.endDate = new Date(initDateTo);
        }
        
        // Create and store Litepicker instance globally
        window.yoloDatePicker = new Litepicker(pickerConfig);
        
        // Attach event handler immediately after creation to avoid race conditions
        window.yoloDatePicker.on('selected', function(date1, date2) {
            if (isInitialLoad) {
                isInitialLoad = false;
                return;
            }
            
            if (skipApiCallForCarouselSelection) {
                console.log('YOLO YS: Skipping API call - dates set from carousel');
                return;
            }
            
            const dateFrom = date1.format('YYYY-MM-DD');
            const dateTo = date2.format('YYYY-MM-DD');
            
            // Check if both dates are Saturdays
            const fromDay = date1.getDay();
            const toDay = date2.getDay();
            
            if (fromDay !== 6 || toDay !== 6) {
                showCustomDatesModal(dateFrom, dateTo);
                return;
            }
            
            // Show loading state with spinner
            const priceFinal = document.getElementById('selectedPriceFinal');
            const originalText = priceFinal.textContent;
            const checkingText = <?php echo json_encode(get_option('yolo_ys_text_checking_availability', 'Checking real-time availability, please wait...')); ?>;
            
            // Create spinner element
            priceFinal.innerHTML = '<span class="yolo-spinner"></span> ' + checkingText;
            priceFinal.style.opacity = '0.8';
            priceFinal.style.fontSize = '14px';
            priceFinal.style.color = '#6b7280';
            
            // Fetch live price from Booking Manager API
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'yolo_get_live_price',
                    yacht_id: <?php echo json_encode($yacht_id_safe); ?>,
                    date_from: dateFrom,
                    date_to: dateTo,
                })
            })
            .then(response => response.json())
            .then(data => {
                // Reset styles after loading
                priceFinal.style.opacity = '1';
                priceFinal.style.fontSize = '';
                priceFinal.style.color = '';
                
                // Check response success and availability
                // WordPress wp_send_json_success returns {success: true, data: {...}}
                // WordPress wp_send_json_error returns {success: false, data: {...}}
                if (data.success && data.data && data.data.available) {
                    // AVAILABLE - show price and enable booking
                    const price = data.data.final_price;
                    const startPrice = data.data.price;
                    const discount = data.data.discount;
                    const currency = data.data.currency;
                    
                    const priceOriginal = document.getElementById('selectedPriceOriginal');
                    const priceDiscount = document.getElementById('selectedPriceDiscount');
                    
                    if (parseFloat(discount) > 0) {
                        priceOriginal.textContent = formatEuropeanPrice(startPrice, currency) + ' ' + currency;
                        priceOriginal.style.display = 'block';
                        priceDiscount.textContent = parseFloat(discount).toFixed(2) + '% OFF - Save ' + formatEuropeanPrice(startPrice - price, currency) + ' ' + currency;
                        priceDiscount.style.display = 'block';
                    } else {
                        priceOriginal.style.display = 'none';
                        priceDiscount.style.display = 'none';
                    }
                    
                    priceFinal.textContent = formatEuropeanPrice(price, currency) + ' ' + currency;
                    
                    // Store live price for booking (including extras info)
                    window.yoloLivePrice = {
                        price: price,
                        startPrice: startPrice,
                        discount: discount,
                        currency: currency,
                        dateFrom: dateFrom,
                        dateTo: dateTo,
                        includedExtras: data.data.included_extras || 0,
                        extrasAtBase: data.data.extras_at_base || 0,
                        extrasDetails: data.data.extras_details || []
                    };
                    
                    // Remove active class from all carousel slides so updatePriceDisplayWithDeposit uses yoloLivePrice
                    document.querySelectorAll('.price-slide').forEach(slide => slide.classList.remove('active'));
                    
                    // Update deposit info (function will create or update the element)
                    updatePriceDisplayWithDeposit();
                    
                    // Enable Book Now button
                    const bookNowBtn = document.getElementById('bookNowBtn');
                    if (bookNowBtn) {
                        bookNowBtn.disabled = false;
                        bookNowBtn.style.opacity = '1';
                        bookNowBtn.style.cursor = 'pointer';
                    }
                } else {
                    // NOT AVAILABLE or ERROR - show unavailable message
                    priceFinal.textContent = 'Not Available';
                    priceFinal.style.color = '#dc2626';
                    
                    // Get message from response (works for both success:false and success:true with available:false)
                    const errorMessage = (data.data && data.data.message) 
                        ? data.data.message 
                        : 'This yacht is not available for the selected dates. Please choose different dates.';
                    
                    Toastify({
                        text: errorMessage,
                        duration: 6000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#dc2626',
                        stopOnFocus: true
                    }).showToast();
                    
                    const bookNowBtn = document.getElementById('bookNowBtn');
                    if (bookNowBtn) {
                        bookNowBtn.disabled = true;
                        bookNowBtn.style.opacity = '0.5';
                        bookNowBtn.style.cursor = 'not-allowed';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching live price:', error);
                priceFinal.textContent = originalText;
                priceFinal.style.opacity = '1';
                priceFinal.style.fontSize = '';
                priceFinal.style.color = '';
                Toastify({
                    text: 'Failed to check availability. Please try again.',
                    duration: 5000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#dc2626',
                    stopOnFocus: true
                }).showToast();
            });
        });
    }
});

// Format all prices on page load
document.addEventListener('DOMContentLoaded', function() {
    // NOTE: Carousel prices are already correctly formatted by PHP from database
    // DO NOT reformat them with JavaScript - it causes NaN errors!
    // Only format the price display box which gets updated by live API calls
    
    // Format prices in price display box (for live API updates)
    const priceOriginal = document.getElementById('selectedPriceOriginal');
    const priceFinal = document.getElementById('selectedPriceFinal');
    const priceDiscount = document.getElementById('selectedPriceDiscount');
    
    if (priceOriginal) {
        const text = priceOriginal.textContent.trim();
        const match = text.match(/([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            const price = match[1].replace(/,/g, '');
            priceOriginal.textContent = formatPrice(price) + ' ' + match[2];
        }
    }
    
    if (priceFinal) {
        const text = priceFinal.textContent.trim();
        const match = text.match(/([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            const price = match[1].replace(/,/g, '');
            priceFinal.textContent = formatPrice(price) + ' ' + match[2];
        }
    }
    
    if (priceDiscount) {
        const text = priceDiscount.textContent.trim();
        const match = text.match(/Save\s+([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            const price = match[1].replace(/,/g, '');
            priceDiscount.textContent = priceDiscount.textContent.replace(match[1], formatPrice(price));
        }
    }
});

// Swiper instances
let yachtImageSwiper = null;
let priceSwiper = null;

// Initialize Swipers
document.addEventListener('DOMContentLoaded', function() {
    // Image Carousel - Swiper
    const imageContainer = document.querySelector('.yacht-image-swiper');
    if (imageContainer) {
        yachtImageSwiper = new Swiper('.yacht-image-swiper', {
            loop: true,
            effect: 'fade',
            fadeEffect: {
                crossFade: true
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: true
            },
            lazy: {
                loadPrevNext: true,
                loadPrevNextAmount: 2
            },
            navigation: {
                nextEl: '.yacht-image-swiper .swiper-button-next',
                prevEl: '.yacht-image-swiper .swiper-button-prev',
            },
            pagination: {
                el: '.yacht-image-swiper .swiper-pagination',
                clickable: true,
            },
            keyboard: {
                enabled: true,
            },
            grabCursor: true,
        });
    }
    
    // Price Carousel - Swiper
    const priceContainer = document.querySelector('.price-swiper');
    if (priceContainer) {
        priceSwiper = new Swiper('.price-swiper', {
            slidesPerView: 'auto',
            spaceBetween: 16,
            freeMode: true,
            navigation: {
                nextEl: '.price-swiper .swiper-button-next',
                prevEl: '.price-swiper .swiper-button-prev',
            },
            breakpoints: {
                320: {
                    spaceBetween: 12,
                },
                768: {
                    spaceBetween: 16,
                },
            },
            grabCursor: true,
        });
    }
    
    // Auto-select week based on init dates or default to July
    autoSelectWeek();
});

// Auto-select week on page load
function autoSelectWeek() {
    const priceSlides = document.querySelectorAll('.price-slide');
    if (priceSlides.length === 0) return;
    
    const swiperContainer = document.querySelector('.price-swiper');
    const initDateFrom = swiperContainer ? swiperContainer.dataset.initDateFrom : '';
    const initDateTo = swiperContainer ? swiperContainer.dataset.initDateTo : '';
    
    let selectedSlide = null;
    
    // Try to match init dates from URL/server
    if (initDateFrom && initDateTo) {
        for (let slide of priceSlides) {
            if (slide.dataset.dateFrom === initDateFrom && slide.dataset.dateTo === initDateTo) {
                selectedSlide = slide;
                break;
            }
        }
    }
    
    // If no match, default to first July week
    if (!selectedSlide) {
        for (let slide of priceSlides) {
            const dateFrom = slide.dataset.dateFrom;
            if (dateFrom && dateFrom.substring(5, 7) === '07') {
                selectedSlide = slide;
                break;
            }
        }
    }
    
    // If still no match, use first slide
    if (!selectedSlide) {
        selectedSlide = priceSlides[0];
    }
    
    // Activate the selected slide and update price display
    if (selectedSlide) {
        // Remove active class from all slides
        priceSlides.forEach(s => s.classList.remove('active'));
        selectedSlide.classList.add('active');
        
        // Update price display
        const price = selectedSlide.dataset.price;
        const startPrice = selectedSlide.dataset.startPrice;
        const discount = selectedSlide.dataset.discount;
        const currency = selectedSlide.dataset.currency;
        
        const priceDisplay = document.getElementById('selectedPriceDisplay');
        const priceOriginal = document.getElementById('selectedPriceOriginal');
        const priceDiscount = document.getElementById('selectedPriceDiscount');
        const priceFinal = document.getElementById('selectedPriceFinal');
        
        if (priceDisplay && priceFinal) {
            priceDisplay.style.display = 'block';
            
            if (parseFloat(discount) > 0) {
                priceOriginal.textContent = formatPrice(startPrice) + ' ' + currency;
                priceOriginal.style.display = 'block';
                priceDiscount.textContent = parseFloat(discount).toFixed(2) + '% OFF - Save ' + formatPrice(Math.round(startPrice - price)) + ' ' + currency;
                priceDiscount.style.display = 'block';
            } else {
                priceOriginal.style.display = 'none';
                priceDiscount.style.display = 'none';
            }
            
            priceFinal.textContent = formatPrice(price) + ' ' + currency;
        }
    }
}

// Select Week Function
function selectWeek(button) {
    const slide = button.closest('.price-slide');
    const dateFrom = slide.dataset.dateFrom;
    const dateTo = slide.dataset.dateTo;
    const price = slide.dataset.price;
    const startPrice = slide.dataset.startPrice;
    const discount = slide.dataset.discount;
    const currency = slide.dataset.currency;
    
    // Update active class
    document.querySelectorAll('.price-slide').forEach(s => s.classList.remove('active'));
    slide.classList.add('active');
    
    // Update price display above Book Now
    const priceDisplay = document.getElementById('selectedPriceDisplay');
    const priceOriginal = document.getElementById('selectedPriceOriginal');
    const priceDiscount = document.getElementById('selectedPriceDiscount');
    const priceFinal = document.getElementById('selectedPriceFinal');
    
    if (priceDisplay && priceFinal) {
        // Show/hide discount elements
        if (parseFloat(discount) > 0) {
            priceOriginal.textContent = formatPrice(startPrice) + ' ' + currency;
            priceOriginal.style.display = 'block';
            priceDiscount.textContent = parseFloat(discount).toFixed(2) + '% OFF - Save ' + formatPrice(Math.round(startPrice - price)) + ' ' + currency;
            priceDiscount.style.display = 'block';
        } else {
            priceOriginal.style.display = 'none';
            priceDiscount.style.display = 'none';
        }
        
        priceFinal.textContent = formatPrice(price) + ' ' + currency;
    }
    
    // Update date picker - set flag to skip API call since we already have cached price
    if (window.yoloDatePicker && dateFrom && dateTo) {
        const from = new Date(dateFrom);
        const to = new Date(dateTo);
        
        // CRITICAL: Set flag to prevent API call - we're using cached carousel prices
        skipApiCallForCarouselSelection = true;
        window.yoloDatePicker.setDateRange(from, to);
        
        // Reset flag after a short delay (in case event fires asynchronously)
        setTimeout(() => {
            skipApiCallForCarouselSelection = false;
        }, 100);
    }
    
    // Update deposit info
    updatePriceDisplayWithDeposit();
    
    // FIX: Re-enable Book Now button when selecting from carousel (was disabled if unavailable dates were selected before)
    const bookNowBtn = document.getElementById('bookNowBtn');
    if (bookNowBtn) {
        bookNowBtn.disabled = false;
        bookNowBtn.style.opacity = '1';
        bookNowBtn.style.cursor = 'pointer';
    }
    
    // Also reset the price color in case it was red from "Not Available"
    // Note: priceFinal already declared above on line 485
    if (priceFinal) {
        priceFinal.style.color = ''; // Reset to default
    }
    
    // Scroll booking section into view on mobile
    if (window.innerWidth < 1024) {
        const bookingSection = document.querySelector('.yacht-booking-section');
        if (bookingSection) {
            bookingSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
}

// Book Now function - Shows booking form modal
function bookNow() {
    // Get selected dates and price
    const dateInput = document.getElementById('dateRangePicker');
    const priceFinal = document.getElementById('selectedPriceFinal');
    
    if (!dateInput || !priceFinal) {
        alert('Please select dates first');
        return;
    }
    
    // Get dates from Litepicker
    let dateFrom, dateTo;
    if (window.yoloDatePicker) {
        const startDate = window.yoloDatePicker.getStartDate();
        const endDate = window.yoloDatePicker.getEndDate();
        
        if (!startDate || !endDate) {
            alert('Please select your charter dates');
            return;
        }
        
        dateFrom = startDate.format('YYYY-MM-DD');
        dateTo = endDate.format('YYYY-MM-DD');
    } else {
        alert('Date picker not initialized');
        return;
    }
    
    // Get price from active price slide OR live price from date picker
    let totalPrice, currency;
    
    const activeSlide = document.querySelector('.price-slide.active');
    if (activeSlide) {
        // Price from carousel selection
        totalPrice = parseFloat(activeSlide.dataset.price);
        currency = activeSlide.dataset.currency || 'EUR';
    } else if (window.yoloLivePrice) {
        // Price from date picker selection (live API call)
        totalPrice = parseFloat(window.yoloLivePrice.price);
        currency = window.yoloLivePrice.currency || 'EUR';
    } else {
        alert('Please select your charter dates first');
        return;
    }
    
    if (!totalPrice || totalPrice <= 0) {
        alert('Invalid price. Please select a week from the calendar.');
        return;
    }
    
    // Get yacht details
    // CRITICAL: yachtId MUST be string - large IDs lose precision with intval/Number
    const yachtId = <?php echo json_encode($yacht_id_safe); ?>;
    const yachtName = <?php echo json_encode($yacht_name_safe); ?>;
    
    // Track AddToCart event (server-side CAPI + client-side Pixel with deduplication)
    jQuery.ajax({
        url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
        type: 'POST',
        data: {
            action: 'yolo_track_add_to_cart',
            yacht_id: yachtId,
            yacht_name: yachtName,
            price: totalPrice
        },
        success: function(response) {
            // Track on client-side with same event_id for deduplication
            if (response.success) {
                if (response.data) {
                    if (response.data.event_id) {
                        if (typeof YoloAnalytics !== 'undefined') {
                            YoloAnalytics.trackSelectWeek({
                                yacht_id: yachtId,
                                yacht_name: yachtName,
                                price: totalPrice,
                                currency: currency
                            }, response.data.event_id);
                        }
                    }
                }
            }
        }
    });
    
    // Show booking form modal
    showBookingFormModal(yachtId, yachtName, dateFrom, dateTo, totalPrice, currency);
}

// Show booking form modal
function showBookingFormModal(yachtId, yachtName, dateFrom, dateTo, totalPrice, currency) {
    // Format dates for display
    const dateFromFormatted = new Date(dateFrom).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    const dateToFormatted = new Date(dateTo).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    
    // Format price
    const priceFormatted = formatPrice(totalPrice) + ' ' + currency;
    
    // Create modal HTML - Using CSS classes from booking-modal.css
    const modalHTML = `
        <div id="bookingFormModal" class="yolo-booking-modal">
            <div class="yolo-booking-modal-content">
                <h2>Complete Your Booking</h2>
                <p class="yolo-booking-modal-subtitle">Please provide your details to proceed with payment</p>
                
                <!-- Booking Summary -->
                <div class="yolo-booking-summary">
                    <h3>📋 Booking Summary</h3>
                    <div class="d-flex flex-column gap-2">
                        <div class="yolo-booking-summary-row">
                            <span class="yolo-booking-summary-label">Yacht:</span>
                            <span class="yolo-booking-summary-value">${yachtName}</span>
                        </div>
                        <div class="yolo-booking-summary-row">
                            <span class="yolo-booking-summary-label">Check-in:</span>
                            <span class="yolo-booking-summary-value">${dateFromFormatted}</span>
                        </div>
                        <div class="yolo-booking-summary-row">
                            <span class="yolo-booking-summary-label">Check-out:</span>
                            <span class="yolo-booking-summary-value">${dateToFormatted}</span>
                        </div>
                        <hr class="my-2">
                        <div class="yolo-booking-summary-row yolo-booking-summary-total">
                            <span class="yolo-booking-summary-label fw-bold fs-5">Total Price:</span>
                            <span class="yolo-booking-summary-value text-primary fw-bold fs-4">${priceFormatted}</span>
                        </div>
                    </div>
                    
                    <!-- Payment Information -->
                    <div class="alert alert-info mt-3 mb-0" style="background-color: #e0f2fe; border-color: #0ea5e9; color: #0c4a6e;">
                        <strong>💳 Payment Details:</strong><br>
                        You will pay <strong>50% (${formatPrice(totalPrice * 0.5)} ${currency})</strong> now to secure your booking.<br>
                        The remaining <strong>50% (${formatPrice(totalPrice * 0.5)} ${currency})</strong> will be paid later. We will contact you with payment instructions.
                    </div>
                </div>
                
                <!-- Customer Information Form -->
                <form id="bookingForm" class="yolo-booking-form">
                    <h3>👤 Your Information</h3>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email Address *</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Mobile Number *</label>
                        <input type="tel" name="phone" class="form-control" required placeholder="+30 123 456 7890">
                    </div>
                    
                    <input type="hidden" name="yacht_id" value="${yachtId}">
                    <input type="hidden" name="yacht_name" value="${yachtName}">
                    <input type="hidden" name="date_from" value="${dateFrom}">
                    <input type="hidden" name="date_to" value="${dateTo}">
                    <input type="hidden" name="total_price" value="${totalPrice}">
                    <input type="hidden" name="currency" value="${currency}">
                    
                    <div class="yolo-booking-form-buttons">
                        <button type="button" onclick="closeBookingFormModal()" class="yolo-booking-btn-cancel">${cancelText}</button>
                        <button type="submit" class="yolo-booking-btn-submit">PROCEED TO PAYMENT →</button>
                    </div>
                </form>
            </div>
        </div>
    `;
    
    // Add modal to page
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Add focus styles
    const inputs = document.querySelectorAll('#bookingForm input[type="text"], #bookingForm input[type="email"], #bookingForm input[type="tel"]');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.borderColor = '#1e40af';
        });
        input.addEventListener('blur', function() {
            this.style.borderColor = '#d1d5db';
        });
    });
    
    // Handle form submission
    document.getElementById('bookingForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const firstName = formData.get('first_name');
        const lastName = formData.get('last_name');
        const email = formData.get('email');
        const phone = formData.get('phone');
        
        // Validate form
        if (!firstName || !lastName || !email || !phone) {
            alert('Please fill in all required fields');
            return;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
        submitBtn.disabled = true;
        
        // Track InitiateCheckout event (server-side CAPI + client-side Pixel with deduplication)
        jQuery.ajax({
            url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
            type: 'POST',
            data: {
                action: 'yolo_track_initiate_checkout',
                yacht_id: yachtId,
                yacht_name: yachtName,
                price: totalPrice,
                date_from: dateFrom,
                date_to: dateTo
            },
            success: function(response) {
                // Track on client-side with same event_id for deduplication
                if (response.success) {
                    if (response.data) {
                        if (response.data.event_id) {
                            if (typeof YoloAnalytics !== 'undefined') {
                                YoloAnalytics.trackBeginCheckout({
                                    yacht_id: yachtId,
                                    yacht_name: yachtName,
                                    price: totalPrice,
                                    currency: currency,
                                    date_from: dateFrom,
                                    date_to: dateTo
                                }, response.data.event_id);
                            }
                        }
                    }
                }
            }
        });
        
        // Create Stripe Checkout Session with customer data
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'yolo_create_checkout_session',
                nonce: yoloYSData.nonce,
                yacht_id: yachtId,
                yacht_name: yachtName,
                date_from: dateFrom,
                date_to: dateTo,
                total_price: totalPrice,
                currency: currency,
                customer_first_name: firstName,
                customer_last_name: lastName,
                customer_email: email,
                customer_phone: phone,
                // Use live price extras if available, otherwise fall back to cached database extras
                included_extras: window.yoloLivePrice && window.yoloLivePrice.includedExtras ? window.yoloLivePrice.includedExtras : (window.yoloCachedExtras ? window.yoloCachedExtras.includedExtras : 0),
                extras_at_base: window.yoloLivePrice && window.yoloLivePrice.extrasAtBase ? window.yoloLivePrice.extrasAtBase : (window.yoloCachedExtras ? window.yoloCachedExtras.extrasAtBase : 0),
                extras_details: window.yoloLivePrice && window.yoloLivePrice.extrasDetails ? JSON.stringify(window.yoloLivePrice.extrasDetails) : (window.yoloCachedExtras ? JSON.stringify(window.yoloCachedExtras.extrasDetails) : '[]')
            })
        })
        .then(response => response.json())
        .then(data => {
            // Check response success and session_id
            if (data.success) {
            if (data.data) {
            if (data.data.session_id) {
                // Redirect to Stripe Checkout
                const stripe = Stripe('<?php echo get_option('yolo_ys_stripe_publishable_key', ''); ?>');
                stripe.redirectToCheckout({
                    sessionId: data.data.session_id
                }).then(function(result) {
                    if (result.error) {
                        Toastify({
                            text: result.error.message,
                            duration: 6000,
                            gravity: 'top',
                            position: 'right',
                            backgroundColor: '#dc2626',
                            stopOnFocus: true
                        }).showToast();
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }
                });
            } else {
                Toastify({
                    text: 'Error creating checkout session: ' + (data.data.message || 'Unknown error'),
                    duration: 6000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#dc2626',
                    stopOnFocus: true
                }).showToast();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
            } // Close data.data check
            } // Close data.success check
        })
        .catch(error => {
            console.error('Error:', error);
            Toastify({
                text: 'Failed to initiate checkout. Please try again.',
                duration: 5000,
                gravity: 'top',
                position: 'right',
                backgroundColor: '#dc2626',
                stopOnFocus: true
            }).showToast();
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    });
}

// Close booking form modal
function closeBookingFormModal() {
    const modal = document.getElementById('bookingFormModal');
    if (modal) {
        modal.remove();
    }
}

// Format price with standard formatting (18,681.00 for all currencies)
function formatEuropeanPrice(price, currency) {
    if (!price) return '0.00';
    
    const num = parseFloat(price);
    // Standard format for all currencies: 18,681.00
    return num.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Flag to track if this is the initial page load
let isInitialLoad = true;
setTimeout(() => { isInitialLoad = false; }, 1000);

// Flag to skip API call when date is set programmatically from carousel
let skipApiCallForCarouselSelection = false;

// Customizable texts
const cancelText = <?php echo json_encode(get_option('yolo_ys_text_cancel', 'CANCEL')); ?>;
const remainingText = <?php echo json_encode(get_option('yolo_ys_text_remaining', 'Remaining:')); ?>;

// Update price display with deposit information
function updatePriceDisplayWithDeposit() {
    const depositPercentage = <?php echo intval(get_option('yolo_ys_deposit_percentage', 50)); ?>;
    const activeSlide = document.querySelector('.price-slide.active');
    
    let totalPrice, currency;
    
    // Try to get price from active carousel slide first
    if (activeSlide) {
        totalPrice = parseFloat(activeSlide.dataset.price);
        currency = activeSlide.dataset.currency || 'EUR';
    } 
    // Fallback to live price from date picker selection
    else if (window.yoloLivePrice) {
        totalPrice = parseFloat(window.yoloLivePrice.price);
        currency = window.yoloLivePrice.currency || 'EUR';
    } 
    // No price data available
    else {
        return;
    }
    const depositAmount = (totalPrice * depositPercentage) / 100;
    const remainingBalance = totalPrice - depositAmount;
    
    // Update button text to show deposit amount
    const depositText = document.getElementById('depositText');
    if (depositText) {
        depositText.textContent = `Pay ${formatEuropeanPrice(depositAmount)} ${currency} (${depositPercentage}%) Deposit`;
    }
    
    // Add or update deposit info below price
    const priceFinal = document.getElementById('selectedPriceFinal');
    if (priceFinal) {
        let depositInfo = document.getElementById('depositInfo');
        if (!depositInfo) {
            depositInfo = document.createElement('div');
            depositInfo.id = 'depositInfo';
            depositInfo.style.cssText = 'font-size: 14px; color: #6b7280; margin-top: 8px;';
            priceFinal.parentNode.appendChild(depositInfo);
        }
        // Always update the innerHTML with current prices
        depositInfo.innerHTML = `
            <div>Deposit (${depositPercentage}%): <strong>${formatEuropeanPrice(depositAmount)} ${currency}</strong></div>
            <div>${remainingText} <strong>${formatEuropeanPrice(remainingBalance)} ${currency}</strong></div>
        `;
    }
}

// Initialize price display on page load
// Note: Date picker event handler is attached immediately after Litepicker creation (see Litepicker init section)
document.addEventListener('DOMContentLoaded', function() {
    updatePriceDisplayWithDeposit();
    
    // ============================================
    // QUOTE REQUEST FORM HANDLER
    // ============================================
    const quoteForm = document.getElementById('quoteRequestForm');
    if (quoteForm) {
        quoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(this);
            formData.append('action', 'yolo_submit_quote_request');
            formData.append('nonce', '<?php echo wp_create_nonce('yolo_quote_nonce'); ?>');
            
            // Get date range from picker if available
            if (window.yoloDatePicker) {
                const startDate = window.yoloDatePicker.getStartDate();
                const endDate = window.yoloDatePicker.getEndDate();
                if (startDate) formData.append('date_from', startDate.format('YYYY-MM-DD'));
                if (endDate) formData.append('date_to', endDate.format('YYYY-MM-DD'));
            }
            
            // Get number of guests if available
            const guestsSelect = document.querySelector('select[name="num_guests"]');
            if (guestsSelect) {
                formData.append('num_guests', guestsSelect.value);
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'SENDING...';
            submitBtn.disabled = true;
            
            // Submit via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Track Lead event on client-side with event_id from server-side for deduplication
                    if (data.data) {
                        if (data.data.event_id) {
                            if (typeof YoloAnalytics !== 'undefined') {
                                YoloAnalytics.trackLead({
                                    yacht_id: <?php echo json_encode($yacht_id_safe); ?>,
                                    yacht_name: <?php echo json_encode($yacht_name_safe); ?>,
                                    value: 0
                                }, data.data.event_id);
                            }
                        }
                    }
                    
                    // Show success message
                    Toastify({
                        text: '✓ ' + data.data.message,
                        duration: 4000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#10b981',
                        stopOnFocus: true
                    }).showToast();
                    // Reset form
                    quoteForm.reset();
                    // Hide form
                    toggleQuoteForm();
                } else {
                    // Show error message
                    Toastify({
                        text: '✗ ' + (data.data.message || 'Failed to submit quote request. Please try again.'),
                        duration: 5000,
                        gravity: 'top',
                        position: 'right',
                        backgroundColor: '#dc2626',
                        stopOnFocus: true
                    }).showToast();
                }
                // Restore button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Quote submission error:', error);
                Toastify({
                    text: '✗ Failed to submit quote request. Please try again or contact us directly.',
                    duration: 5000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#dc2626',
                    stopOnFocus: true
                }).showToast();
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// Date picker event handler is now attached immediately after Litepicker creation (see line 237+)
// This ensures the handler is always attached when the picker exists, avoiding race conditions

// ============================================
// v30.6 FIX: Add .loaded class to prevent FOUC
// The CSS sets opacity: 0 by default, this makes it visible
// ============================================
window.addEventListener('load', function() {
    const yachtDetails = document.querySelector('.yolo-yacht-details-v3');
    if (yachtDetails) {
        yachtDetails.classList.add('loaded');
    }
});

// Fallback: If load event doesn't fire, show content after 500ms
setTimeout(function() {
    const yachtDetails = document.querySelector('.yolo-yacht-details-v3');
    if (yachtDetails && !yachtDetails.classList.contains('loaded')) {
        yachtDetails.classList.add('loaded');
    }
}, 500);
</script>

<!-- Load Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

