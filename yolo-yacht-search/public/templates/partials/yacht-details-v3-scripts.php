<script>
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
                    ">I would like to charter <?php echo esc_html($yacht_name); ?> from ${dateFrom} to ${dateTo}. Please contact me with availability and pricing.</textarea>
                    <input type="hidden" name="yacht_id" value="<?php echo $yacht_id; ?>">
                    <input type="hidden" name="yacht_name" value="<?php echo esc_attr($yacht_name); ?>">
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

// Format price with European formatting (dot for thousands, comma for decimals)
function formatPrice(price) {
    if (!price) return '0,00';
    // Convert to number and format with 2 decimals
    const num = Number(price);
    // Format with European style: 18.681,00
    return num.toLocaleString('de-DE', {
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
    }
});

// Format all prices on page load
document.addEventListener('DOMContentLoaded', function() {
    // Format prices in carousel
    document.querySelectorAll('.price-original span, .price-final, .price-discount-badge').forEach(function(el) {
        const text = el.textContent.trim();
        const match = text.match(/(\d+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            const price = match[1];
            const currency = match[2];
            el.textContent = formatPrice(price) + ' ' + currency;
        }
    });
    
    // Format prices in price display box
    const priceOriginal = document.getElementById('selectedPriceOriginal');
    const priceFinal = document.getElementById('selectedPriceFinal');
    const priceDiscount = document.getElementById('selectedPriceDiscount');
    
    if (priceOriginal) {
        const text = priceOriginal.textContent.trim();
        const match = text.match(/(\d+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            priceOriginal.textContent = formatPrice(match[1]) + ' ' + match[2];
        }
    }
    
    if (priceFinal) {
        const text = priceFinal.textContent.trim();
        const match = text.match(/(\d+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            priceFinal.textContent = formatPrice(match[1]) + ' ' + match[2];
        }
    }
    
    if (priceDiscount) {
        const text = priceDiscount.textContent.trim();
        const match = text.match(/Save\s+(\d+(?:\.\d+)?)\s*([A-Z]{3})/);
        if (match) {
            priceDiscount.textContent = priceDiscount.textContent.replace(match[1], formatPrice(match[1]));
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
    
    // Update date picker
    if (window.yoloDatePicker && dateFrom && dateTo) {
        const from = new Date(dateFrom);
        const to = new Date(dateTo);
        window.yoloDatePicker.setDateRange(from, to);
    }
    
    // Scroll to top to show the updated price
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Book Now function - Initiates Stripe Checkout
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
    const yachtId = <?php echo intval($yacht->id); ?>;
    const yachtName = <?php echo json_encode($yacht->name); ?>;
    
    // Show loading state
    const bookBtn = document.querySelector('.btn-book-now');
    const originalText = bookBtn.textContent;
    bookBtn.textContent = 'Processing...';
    bookBtn.disabled = true;
    
    // Create Stripe Checkout Session via AJAX
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'yolo_create_checkout_session',
            yacht_id: yachtId,
            yacht_name: yachtName,
            date_from: dateFrom,
            date_to: dateTo,
            total_price: totalPrice
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
                    bookBtn.textContent = originalText;
                    bookBtn.disabled = false;
                }
            });
        } else {
            alert('Error creating checkout session: ' + (data.data.message || 'Unknown error'));
            bookBtn.textContent = originalText;
            bookBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to initiate checkout. Please try again.');
        bookBtn.textContent = originalText;
        bookBtn.disabled = false;
    });
}

// Format price with proper European formatting (18.681,00 EUR)
function formatEuropeanPrice(price, currency) {
    if (!price) return '0,00';
    
    const num = parseFloat(price);
    if (currency === 'EUR') {
        // European format: 18.681,00
        return num.toLocaleString('de-DE', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    } else {
        // US format: 18,681.00
        return num.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
}

// Update price display with deposit information
function updatePriceDisplayWithDeposit() {
    const depositPercentage = <?php echo intval(get_option('yolo_ys_deposit_percentage', 50)); ?>;
    const activeSlide = document.querySelector('.price-slide.active');
    
    if (!activeSlide) return;
    
    const totalPrice = parseFloat(activeSlide.dataset.price);
    const currency = activeSlide.dataset.currency || 'EUR';
    const depositAmount = (totalPrice * depositPercentage) / 100;
    const remainingBalance = totalPrice - depositAmount;
    
    // Update button text to show deposit amount
    const depositText = document.getElementById('depositText');
    if (depositText) {
        depositText.textContent = `Pay ${formatEuropeanPrice(depositAmount)} ${currency} (${depositPercentage}%) Deposit`;
    }
    
    // Add deposit info below price
    const priceFinal = document.getElementById('selectedPriceFinal');
    if (priceFinal && !document.getElementById('depositInfo')) {
        const depositInfo = document.createElement('div');
        depositInfo.id = 'depositInfo';
        depositInfo.style.cssText = 'font-size: 14px; color: #6b7280; margin-top: 8px;';
        depositInfo.innerHTML = `
            <div>Deposit (${depositPercentage}%): <strong>${formatEuropeanPrice(depositAmount, currency)} ${currency}</strong></div>
            <div>Remaining: ${formatEuropeanPrice(remainingBalance, currency)} ${currency}</div>
        `;
        priceFinal.parentNode.appendChild(depositInfo);
    }
}

// Call on page load and when price changes
document.addEventListener('DOMContentLoaded', function() {
    updatePriceDisplayWithDeposit();
    
    // Update when date picker changes
    if (window.yoloDatePicker) {
        window.yoloDatePicker.on('selected', function(date1, date2) {
            const dateFrom = date1.format('YYYY-MM-DD');
            const dateTo = date2.format('YYYY-MM-DD');
            
            // Check if both dates are Saturdays
            const fromDay = date1.getDay();
            const toDay = date2.getDay();
            
            if (fromDay !== 6 || toDay !== 6) {
                // Not Saturday-to-Saturday
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
                    yacht_id: <?php echo $yacht_id; ?>,
                    date_from: dateFrom,
                    date_to: dateTo,
                })
            })
            .then(response => response.json())
            .then(data => {
                priceFinal.style.opacity = '1';
                
                if (data.success && data.data.available) {
                    // Update price display with live data
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
                    
                    // Update deposit info
                    const depositInfo = document.getElementById('depositInfo');
                    if (depositInfo) {
                        depositInfo.remove();
                    }
                    updatePriceDisplayWithDeposit();
                    
                    // Enable Book Now button
                    const bookNowBtn = document.getElementById('bookNowBtn');
                    if (bookNowBtn) {
                        bookNowBtn.disabled = false;
                        bookNowBtn.style.opacity = '1';
                        bookNowBtn.style.cursor = 'pointer';
                    }
                } else {
                    // Yacht not available
                    priceFinal.textContent = 'Not Available';
                    priceFinal.style.color = '#dc2626';
                    
                    // Show error message
                    alert(data.data ? data.data.message : 'This yacht is not available for the selected dates. Please choose different dates.');
                    
                    // Disable Book Now button
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
</script>

<!-- Load Stripe.js -->
<script src="https://js.stripe.com/v3/"></script>
