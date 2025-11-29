<script>
// Format price with comma thousands separator
function formatPrice(price) {
    if (!price) return '0';
    return Number(price).toLocaleString('en-US');
}

// Initialize Litepicker
const dateInput = document.getElementById('yolo-ys-yacht-dates');
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
});

// Select Week Function
function selectWeek(button) {
    const slide = button.closest('.price-slide');
    const dateFrom = slide.dataset.dateFrom;
    const dateTo = slide.dataset.dateTo;
    const price = slide.dataset.price;
    const startPrice = slide.dataset.startPrice;
    const discount = slide.dataset.discount;
    const currency = slide.dataset.currency;
    
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
</script>
