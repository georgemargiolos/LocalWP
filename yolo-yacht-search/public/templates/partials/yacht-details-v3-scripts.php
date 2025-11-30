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
        formData.append('nonce', yoloYSData.nonce);
        
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
        const match = text.match(/([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/); // FIXED (v2.3.8): Handle comma-separated thousands
        if (match) {
            const price = match[1].replace(/,/g, ''); // Remove commas before formatting
            priceOriginal.textContent = formatPrice(price) + ' ' + match[2];
        }
    }
    
    if (priceFinal) {
        const text = priceFinal.textContent.trim();
        const match = text.match(/([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/); // FIXED (v2.3.8): Handle comma-separated thousands
        if (match) {
            const price = match[1].replace(/,/g, ''); // Remove commas before formatting
            priceFinal.textContent = formatPrice(price) + ' ' + match[2];
        }
    }
    
    if (priceDiscount) {
        const text = priceDiscount.textContent.trim();
        const match = text.match(/Save\s+([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/); // FIXED (v2.3.8): Handle comma-separated thousands
        if (match) {
            const price = match[1].replace(/,/g, ''); // Remove commas before formatting
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
    
    // Scroll to top to show the updated price
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
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
    // CRITICAL FIX v2.5.3: yachtId MUST be STRING to preserve large integer precision
    // JavaScript Number type loses precision for integers > 2^53 (9007199254740992)
    // Large yacht IDs like 7136018700000107850 get corrupted to 7136018700000108000
    // This causes API lookups to fail with "yacht not found" errors
    const yachtId = "<?php echo esc_attr($yacht->id); ?>";
    const yachtName = <?php echo json_encode($yacht->name); ?>;
    
    // Show booking form modal
    showBookingFormModal(yachtId, yachtName, dateFrom, dateTo, totalPrice, currency);
}

// HTML escape function to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Show booking form modal
function showBookingFormModal(yachtId, yachtName, dateFrom, dateTo, totalPrice, currency) {
    // Escape yacht name to prevent XSS
    const yachtNameEscaped = escapeHtml(yachtName);
    
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
        ">
            <div style="
                background: white;
                padding: 40px;
                border-radius: 12px;
                max-width: 600px;
                width: 90%;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                margin: 20px;
            ">
                <h2 style="margin-top: 0; color: #1e40af; font-size: 28px; text-align: center; margin-bottom: 10px;">Complete Your Booking</h2>
                <p style="text-align: center; color: #6b7280; margin-bottom: 30px;">Please provide your details to proceed with payment</p>
                
                <!-- Booking Summary -->
                <div style="
                    background: #f3f4f6;
                    padding: 20px;
                    border-radius: 8px;
                    margin-bottom: 30px;
                ">
                    <h3 style="margin-top: 0; margin-bottom: 15px; color: #374151; font-size: 18px;">📋 Booking Summary</h3>
                    <div style="display: grid; gap: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280; font-weight: 500;">Yacht:</span>
                            <span style="color: #111827; font-weight: 600;">${yachtNameEscaped}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280; font-weight: 500;">Check-in:</span>
                            <span style="color: #111827; font-weight: 600;">${dateFromFormatted}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #6b7280; font-weight: 500;">Check-out:</span>
                            <span style="color: #111827; font-weight: 600;">${dateToFormatted}</span>
                        </div>
                        <hr style="border: none; border-top: 1px solid #d1d5db; margin: 10px 0;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="color: #111827; font-weight: 600; font-size: 18px;">Total Price:</span>
                            <span style="color: #1e40af; font-weight: 700; font-size: 20px;">${priceFormatted}</span>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Information Form -->
                <form id="bookingForm">
                    <h3 style="margin-top: 0; margin-bottom: 20px; color: #374151; font-size: 18px;">👤 Your Information</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div>
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
                        <div>
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
                    
                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="button" onclick="closeBookingFormModal()" style="
                            flex: 1;
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
// CRITICAL FIX (v2.5.0): Set to false after 1 second to allow automatic page load trigger
// but still check availability when user manually selects dates
let isInitialLoad = true;
setTimeout(() => { isInitialLoad = false; }, 1000);

// Flag to skip API call when date is set programmatically from carousel
let skipApiCallForCarouselSelection = false;

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
            <div style="font-size: 15px; color: #1f2937; font-weight: 500;">You pay <strong style="color: #b91c1c;">${depositPercentage}%</strong> (<strong style="color: #b91c1c;">${formatEuropeanPrice(depositAmount, currency)} ${currency}</strong>) to reserve this yacht</div>
        `;
        priceFinal.parentNode.appendChild(depositInfo);
    }
}

// CRITICAL: Initialize price display and set up date picker event handlers
// DO NOT MODIFY without understanding the price carousel bug fix!
document.addEventListener('DOMContentLoaded', function() {
    updatePriceDisplayWithDeposit();
    
    // Update when date picker changes
    if (window.yoloDatePicker) {
        window.yoloDatePicker.on('selected', function(date1, date2) {
            // CRITICAL FIX (v2.3.5): Skip API call on initial page load
            // Bug history: Litepicker triggered 'selected' event on page load,
            // causing automatic API call that overwrote correct database prices
            // with wrong/failed API responses (showing 925 EUR instead of 2,925 EUR)
            // 
            // Solution: Use isInitialLoad flag to prevent first automatic trigger
            // Only call live API when user manually changes dates
            if (isInitialLoad) {
                isInitialLoad = false;
                return; // Skip API call, use database prices from carousel
            }
            
            // CRITICAL FIX (v2.5.2): Skip API call when selecting from carousel
            // Bug: Clicking "SELECT THIS WEEK" on carousel triggers date picker,
            // which then calls API that may return empty (false "not available" error)
            // Solution: Skip API call when dates are set programmatically from carousel
            if (skipApiCallForCarouselSelection) {
                console.log('YOLO YS: Skipping API call - dates set from carousel');
                return; // Skip API call, use carousel prices
            }
            
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
                    nonce: yoloYSData.nonce,
                    // CRITICAL FIX v2.5.3: yacht_id as STRING to preserve precision
                    yacht_id: "<?php echo esc_attr($yacht_id); ?>",
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
                    const includedExtras = data.data.included_extras || 0;
                    const extrasAtBase = data.data.extras_at_base || 0;
                    const extrasDetails = data.data.extras_details || [];
                    
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
                    
                    // Add extras information if present
                    if (includedExtras > 0) {
                        const extrasNote = document.createElement('div');
                        extrasNote.id = 'extrasNote';
                        extrasNote.style.cssText = 'font-size: 13px; color: #059669; margin-top: 8px; font-weight: 500;';
                        extrasNote.textContent = `Obligatory extras included (${formatEuropeanPrice(includedExtras, currency)} ${currency})`;
                        
                        // Remove existing note if present
                        const existingNote = document.getElementById('extrasNote');
                        if (existingNote) existingNote.remove();
                        
                        priceFinal.parentNode.insertBefore(extrasNote, priceFinal.nextSibling);
                    }
                    
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
