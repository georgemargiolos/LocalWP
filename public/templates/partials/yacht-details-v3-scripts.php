<?php
/**
 * Yacht Details v3 Scripts - FIXED VERSION
 * Version: 2.6.0
 * 
 * FIXES:
 * ✓ Added missing toggleDescription() function
 * ✓ Added toggleQuoteForm() function
 */
?>
<script>
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

// Show custom dates modal for non-Saturday bookings
function showCustomDatesModal(dateFrom, dateTo) {
    // Create modal HTML
    const modalHTML = `
        <div id="customDatesModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        ">
            <div style="
                background: white;
                padding: 30px;
                border-radius: 8px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            ">
                <h3 style="margin-top: 0; color: #1e40af; font-size: 20px;">⚠️ Custom Dates Required</h3>
                <p style="color: #4b5563; line-height: 1.6;">
                    We charter our yachts from <strong>Saturday to Saturday</strong>. 
                    If you need something special or custom dates, please fill this form and we'll get back to you.
                </p>
                <form id="customDatesForm" style="margin-top: 20px;">
                    <input type="text" name="name" placeholder="Your Name *" required style="
                        width: 100%;
                        padding: 10px;
                        margin-bottom: 10px;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                        box-sizing: border-box;
                    ">
                    <input type="email" name="email" placeholder="Your Email *" required style="
                        width: 100%;
                        padding: 10px;
                        margin-bottom: 10px;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                        box-sizing: border-box;
                    ">
                    <input type="tel" name="phone" placeholder="Your Phone" style="
                        width: 100%;
                        padding: 10px;
                        margin-bottom: 10px;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                        box-sizing: border-box;
                    ">
                    <textarea name="message" rows="4" placeholder="Message" style="
                        width: 100%;
                        padding: 10px;
                        margin-bottom: 10px;
                        border: 1px solid #d1d5db;
                        border-radius: 4px;
                        box-sizing: border-box;
                        resize: vertical;
                    ">I would like to charter <?php echo esc_js($yacht->name ?? ''); ?> from ${dateFrom} to ${dateTo}. Please contact me with availability and pricing.</textarea>
                    <input type="hidden" name="yacht_id" value="<?php echo esc_attr($yacht_id ?? ''); ?>">
                    <input type="hidden" name="yacht_name" value="<?php echo esc_attr($yacht->name ?? ''); ?>">
                    <input type="hidden" name="date_from" value="${dateFrom}">
                    <input type="hidden" name="date_to" value="${dateTo}">
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" style="
                            flex: 1;
                            background: #1e40af;
                            color: white;
                            padding: 12px;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                            font-weight: bold;
                        ">SEND REQUEST</button>
                        <button type="button" onclick="closeCustomDatesModal()" style="
                            flex: 1;
                            background: #6b7280;
                            color: white;
                            padding: 12px;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                        ">CANCEL</button>
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
            
            // Show loading state
            const priceFinal = document.getElementById('selectedPriceFinal');
            const originalText = priceFinal.textContent;
            priceFinal.textContent = 'Checking availability...';
            priceFinal.style.opacity = '0.6';
            
            // Fetch live price from Booking Manager API
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'yolo_get_live_price',
                    yacht_id: "<?php echo esc_attr($yacht_id ?? ''); ?>",
                    date_from: dateFrom,
                    date_to: dateTo,
                })
            })
            .then(response => response.json())
            .then(data => {
                priceFinal.style.opacity = '1';
                
                if (data.success && data.data.available) {
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
                    
                    // Store live price for booking
                    window.yoloLivePrice = {
                        price: price,
                        startPrice: startPrice,
                        discount: discount,
                        currency: currency,
                        dateFrom: dateFrom,
                        dateTo: dateTo,
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
                    priceFinal.textContent = 'Not Available';
                    priceFinal.style.color = '#dc2626';
                    
                    alert(data.data ? data.data.message : 'This yacht is not available for the selected dates. Please choose different dates.');
                    
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
                alert('Failed to check availability. Please try again.');
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

// Image Carousel
const yachtCarousel = {
    currentSlide: 0,
    slides: document.querySelectorAll('.carousel-slide'),
    dots: document.querySelectorAll('.carousel-dots .dot'),
    
    goTo: function(index) {
        if (this.slides.length === 0) return;
        
        this.slides[this.currentSlide].classList.remove('active');
        if (this.dots.length > 0) {
            this.dots[this.currentSlide].classList.remove('active');
        }
        
        this.currentSlide = index;
        
        this.slides[this.currentSlide].classList.add('active');
        if (this.dots.length > 0) {
            this.dots[this.currentSlide].classList.add('active');
        }
    },
    
    next: function() {
        if (this.slides.length === 0) return;
        const nextIndex = (this.currentSlide + 1) % this.slides.length;
        this.goTo(nextIndex);
    },
    
    prev: function() {
        if (this.slides.length === 0) return;
        const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.goTo(prevIndex);
    }
};

// Price Carousel - Horizontal scroll showing 4 weeks at a time
const priceCarousel = {
    container: document.querySelector('.price-carousel-slides'),
    slideWidth: 265, // 250px width + 15px gap
    visibleSlides: 4,
    
    init: function() {
        if (!this.container) return;
        // All slides are visible, just scrolled
        this.updateArrows();
    },
    
    updateArrows: function() {
        if (!this.container) return;
        const prevBtn = document.querySelector('.price-carousel-prev');
        const nextBtn = document.querySelector('.price-carousel-next');
        
        if (!prevBtn || !nextBtn) return;
        
        const scrollLeft = this.container.scrollLeft;
        const maxScroll = this.container.scrollWidth - this.container.clientWidth;
        
        prevBtn.style.display = scrollLeft > 0 ? 'flex' : 'none';
        nextBtn.style.display = scrollLeft < maxScroll - 1 ? 'flex' : 'none';
    },
    
    scrollNext: function() {
        if (!this.container) return;
        this.container.scrollBy({
            left: this.slideWidth,
            behavior: 'smooth'
        });
        setTimeout(() => this.updateArrows(), 300);
    },
    
    scrollPrev: function() {
        if (!this.container) return;
        this.container.scrollBy({
            left: -this.slideWidth,
            behavior: 'smooth'
        });
        setTimeout(() => this.updateArrows(), 300);
    }
};

// Initialize carousels
document.addEventListener('DOMContentLoaded', function() {
    if (yachtCarousel.slides.length > 0) {
        yachtCarousel.goTo(0);
        
        // Auto-advance every 5 seconds
        setInterval(function() {
            yachtCarousel.next();
        }, 5000);
    }
    
    if (priceCarousel.container) {
        priceCarousel.init();
    }
    
    // Auto-select week based on init dates or default to July
    autoSelectWeek();
});

// Auto-select week on page load
function autoSelectWeek() {
    const priceSlides = document.querySelectorAll('.price-slide');
    if (priceSlides.length === 0) return;
    
    const carouselContainer = document.querySelector('.price-carousel-container');
    const initDateFrom = carouselContainer ? carouselContainer.dataset.initDateFrom : '';
    const initDateTo = carouselContainer ? carouselContainer.dataset.initDateTo : '';
    
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
    
    // Get price from active price slide
    const activeSlide = document.querySelector('.price-slide.active');
    if (!activeSlide) {
        alert('Please select a week first');
        return;
    }
    
    const totalPrice = parseFloat(activeSlide.dataset.price);
    const currency = activeSlide.dataset.currency || 'EUR';
    
    if (!totalPrice || totalPrice <= 0) {
        alert('Invalid price');
        return;
    }
    
    // Get yacht details
    // CRITICAL: yachtId MUST be string - large IDs lose precision with intval/Number
    const yachtId = "<?php echo esc_attr($yacht->id ?? ''); ?>";
    const yachtName = <?php echo json_encode($yacht->name ?? ''); ?>;
    
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
    
    // Create modal HTML
    const modalHTML = `
        <div id="bookingFormModal" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            overflow-y: auto;
            padding: 20px;
        ">
            <div style="
                background: white;
                padding: clamp(20px, 5vw, 40px);
                border-radius: 12px;
                max-width: 600px;
                width: 100%;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            ">
                <h2 style="margin-top: 0; color: #1e40af; font-size: clamp(22px, 5vw, 28px); text-align: center; margin-bottom: 10px;">Complete Your Booking</h2>
                <p style="text-align: center; color: #6b7280; margin-bottom: 30px; font-size: clamp(14px, 3vw, 16px);">Please provide your details to proceed with payment</p>
                
                <!-- Booking Summary -->
                <div style="
                    background: #f3f4f6;
                    padding: clamp(15px, 4vw, 20px);
                    border-radius: 8px;
                    margin-bottom: 30px;
                ">
                    <h3 style="margin-top: 0; margin-bottom: 15px; color: #374151; font-size: 18px;">📋 Booking Summary</h3>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 5px;">
                            <span style="color: #6b7280; font-weight: 500;">Yacht:</span>
                            <span style="color: #111827; font-weight: 600;">${yachtName}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 5px;">
                            <span style="color: #6b7280; font-weight: 500;">Check-in:</span>
                            <span style="color: #111827; font-weight: 600;">${dateFromFormatted}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 5px;">
                            <span style="color: #6b7280; font-weight: 500;">Check-out:</span>
                            <span style="color: #111827; font-weight: 600;">${dateToFormatted}</span>
                        </div>
                        <hr style="border: none; border-top: 1px solid #d1d5db; margin: 10px 0;">
                        <div style="display: flex; justify-content: space-between; flex-wrap: wrap; gap: 5px;">
                            <span style="color: #111827; font-weight: 600; font-size: 18px;">Total Price:</span>
                            <span style="color: #1e40af; font-weight: 700; font-size: 20px;">${priceFormatted}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Information Form -->
                <form id="bookingForm">
                    <h3 style="margin-top: 0; margin-bottom: 20px; color: #374151; font-size: 18px;">👤 Your Information</h3>
                    
                    <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 15px;">
                        <div style="flex: 1 1 calc(50% - 15px); min-width: 200px;">
                            <label style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500; font-size: 14px;">First Name *</label>
                            <input type="text" name="first_name" required style="
                                width: 100%;
                                padding: 12px;
                                border: 2px solid #d1d5db;
                                border-radius: 6px;
                                box-sizing: border-box;
                                font-size: 15px;
                                transition: border-color 0.3s;
                            ">
                        </div>
                        <div style="flex: 1 1 calc(50% - 15px); min-width: 200px;">
                            <label style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500; font-size: 14px;">Last Name *</label>
                            <input type="text" name="last_name" required style="
                                width: 100%;
                                padding: 12px;
                                border: 2px solid #d1d5db;
                                border-radius: 6px;
                                box-sizing: border-box;
                                font-size: 15px;
                                transition: border-color 0.3s;
                            ">
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500; font-size: 14px;">Email Address *</label>
                        <input type="email" name="email" required style="
                            width: 100%;
                            padding: 12px;
                            border: 2px solid #d1d5db;
                            border-radius: 6px;
                            box-sizing: border-box;
                            font-size: 15px;
                            transition: border-color 0.3s;
                        ">
                    </div>
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; color: #374151; font-weight: 500; font-size: 14px;">Mobile Number *</label>
                        <input type="tel" name="phone" required placeholder="+30 123 456 7890" style="
                            width: 100%;
                            padding: 12px;
                            border: 2px solid #d1d5db;
                            border-radius: 6px;
                            box-sizing: border-box;
                            font-size: 15px;
                            transition: border-color 0.3s;
                        ">
                    </div>
                    
                    <input type="hidden" name="yacht_id" value="${yachtId}">
                    <input type="hidden" name="yacht_name" value="${yachtName}">
                    <input type="hidden" name="date_from" value="${dateFrom}">
                    <input type="hidden" name="date_to" value="${dateTo}">
                    <input type="hidden" name="total_price" value="${totalPrice}">
                    <input type="hidden" name="currency" value="${currency}">
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
                        <button type="button" onclick="closeBookingFormModal()" style="
                            flex: 1;
                            min-width: 120px;
                            background: #e5e7eb;
                            color: #374151;
                            padding: 15px;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-weight: 600;
                            font-size: 16px;
                            transition: background 0.3s;
                        ">CANCEL</button>
                        <button type="submit" style="
                            flex: 2;
                            min-width: 200px;
                            background: #1e40af;
                            color: white;
                            padding: 15px;
                            border: none;
                            border-radius: 8px;
                            cursor: pointer;
                            font-weight: 700;
                            font-size: 16px;
                            transition: background 0.3s;
                        ">PROCEED TO PAYMENT →</button>
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
                customer_phone: phone
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.session_id) {
                // Redirect to Stripe Checkout
                const stripe = Stripe('<?php echo get_option('yolo_ys_stripe_publishable_key', ''); ?>');
                stripe.redirectToCheckout({
                    sessionId: data.data.session_id
                }).then(function(result) {
                    if (result.error) {
                        alert(result.error.message);
                        submitBtn.textContent = originalText;
                        submitBtn.disabled = false;
                    }
                });
            } else {
                alert('Error creating checkout session: ' + (data.data.message || 'Unknown error'));
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to initiate checkout. Please try again.');
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
            <div>Remaining: <strong>${formatEuropeanPrice(remainingBalance)} ${currency}</strong></div>
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
                    // Show success message
                    alert('✓ ' + data.data.message);
                    // Reset form
                    quoteForm.reset();
                    // Hide form
                    toggleQuoteForm();
                } else {
                    // Show error message
                    alert('✗ ' + (data.data.message || 'Failed to submit quote request. Please try again.'));
                }
                // Restore button
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            })
            .catch(error => {
                console.error('Quote submission error:', error);
                alert('✗ Failed to submit quote request. Please try again or contact us directly.');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            });
        });
    }
});

// Date picker event handler is now attached immediately after Litepicker creation (see line 237+)
// This ensures the handler is always attached when the picker exists, avoiding race conditions
</script>

<!-- Load Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>

