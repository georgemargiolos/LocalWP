<script>
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
        
        if (prevBtn) {
            const canScrollLeft = this.container.scrollLeft > 0;
            prevBtn.style.opacity = canScrollLeft ? '1' : '0.3';
            prevBtn.style.cursor = canScrollLeft ? 'pointer' : 'not-allowed';
        }
        
        if (nextBtn) {
            const maxScroll = this.container.scrollWidth - this.container.clientWidth;
            const canScrollRight = this.container.scrollLeft < maxScroll - 10;
            nextBtn.style.opacity = canScrollRight ? '1' : '0.3';
            nextBtn.style.cursor = canScrollRight ? 'pointer' : 'not-allowed';
        }
    },
    
    next: function() {
        if (!this.container) return;
        const scrollAmount = this.slideWidth * this.visibleSlides;
        this.container.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        setTimeout(() => this.updateArrows(), 300);
    },
    
    prev: function() {
        if (!this.container) return;
        const scrollAmount = this.slideWidth * this.visibleSlides;
        this.container.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        setTimeout(() => this.updateArrows(), 300);
    }
};

// Initialize price carousel on load
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.price-carousel-slides')) {
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
        // Show price display
        priceDisplay.style.display = 'block';
        
        // Format numbers with thousand separators
        const formatPrice = (num) => Math.floor(num).toLocaleString('en-US').replace(/,/g, '.');
        
        // Show original price if there's a discount
        if (discount > 0) {
            const discountAmount = startPrice - price;
            priceOriginal.innerHTML = `<span class="strikethrough">${formatPrice(startPrice)} ${currency}</span>`;
            priceDiscount.innerHTML = `${parseFloat(discount).toFixed(2)}% OFF - Save ${formatPrice(discountAmount)} ${currency}`;
            priceOriginal.style.display = 'block';
            priceDiscount.style.display = 'block';
        } else {
            priceOriginal.style.display = 'none';
            priceDiscount.style.display = 'none';
        }
        
        // Show final price
        priceFinal.innerHTML = `${formatPrice(price)} ${currency}`;
    }
    
    // Populate date picker
    if (window.datePicker) {
        window.datePicker.setDateRange(dateFrom, dateTo);
    }
    
    // Scroll to booking section
    document.querySelector('.btn-book-now').scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Initialize Litepicker
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.getElementById('dateRangePicker');
    if (dateInput && typeof Litepicker !== 'undefined') {
        window.datePicker = new Litepicker({
            element: dateInput,
            singleMode: false,
            numberOfMonths: 2,
            numberOfColumns: 2,
            minDate: new Date(),
            format: 'YYYY-MM-DD',
            delimiter: ' - ',
            tooltipText: {
                one: 'night',
                other: 'nights'
            },
            tooltipNumber: (totalDays) => {
                return totalDays - 1;
            },
            setup: (picker) => {
                picker.on('selected', (date1, date2) => {
                    console.log('Selected dates:', date1.format('YYYY-MM-DD'), 'to', date2.format('YYYY-MM-DD'));
                });
            }
        });
        
        // Auto-populate with first week's dates and price
        setTimeout(() => {
            const firstSlide = document.querySelector('.price-slide');
            if (firstSlide) {
                const dateFrom = firstSlide.dataset.dateFrom;
                const dateTo = firstSlide.dataset.dateTo;
                const price = firstSlide.dataset.price;
                const startPrice = firstSlide.dataset.startPrice;
                const discount = firstSlide.dataset.discount;
                const currency = firstSlide.dataset.currency;
                
                // Set date picker
                if (dateFrom && dateTo && window.datePicker) {
                    window.datePicker.setDateRange(dateFrom, dateTo);
                }
                
                // Set price display
                const priceDisplay = document.getElementById('selectedPriceDisplay');
                const priceOriginal = document.getElementById('selectedPriceOriginal');
                const priceDiscount = document.getElementById('selectedPriceDiscount');
                const priceFinal = document.getElementById('selectedPriceFinal');
                
                if (priceDisplay && priceFinal) {
                    priceDisplay.style.display = 'block';
                    
                    const formatPrice = (num) => Math.floor(num).toLocaleString('en-US').replace(/,/g, '.');
                    
                    if (discount > 0) {
                        const discountAmount = startPrice - price;
                        priceOriginal.innerHTML = `<span class="strikethrough">${formatPrice(startPrice)} ${currency}</span>`;
                        priceDiscount.innerHTML = `${parseFloat(discount).toFixed(2)}% OFF - Save ${formatPrice(discountAmount)} ${currency}`;
                        priceOriginal.style.display = 'block';
                        priceDiscount.style.display = 'block';
                    } else {
                        priceOriginal.style.display = 'none';
                        priceDiscount.style.display = 'none';
                    }
                    
                    priceFinal.innerHTML = `${formatPrice(price)} ${currency}`;
                }
            }
        }, 500);
    }
});

// Toggle Quote Form
function toggleQuoteForm() {
    const form = document.getElementById('quoteForm');
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        form.style.display = 'none';
    }
}

// Handle Quote Form Submission
document.addEventListener('DOMContentLoaded', function() {
    const quoteForm = document.getElementById('quoteRequestForm');
    if (quoteForm) {
        quoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(quoteForm);
            const data = {
                action: 'yolo_submit_quote_request',
                nonce: yoloYSData.quote_nonce,
                yacht_id: formData.get('yacht_id'),
                yacht_name: formData.get('yacht_name'),
                first_name: formData.get('first_name'),
                last_name: formData.get('last_name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                special_requests: formData.get('special_requests'),
                date_from: window.datePicker ? window.datePicker.getStartDate()?.format('YYYY-MM-DD') : '',
                date_to: window.datePicker ? window.datePicker.getEndDate()?.format('YYYY-MM-DD') : ''
            };
            
            // Submit via AJAX
            fetch(yoloYSData.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('Quote request sent successfully! We will contact you soon.');
                    quoteForm.reset();
                    document.getElementById('quoteForm').style.display = 'none';
                } else {
                    alert('Error sending quote request. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending quote request. Please try again.');
            });
        });
    }
});

// Book Now Function
function bookNow() {
    if (!window.datePicker || !window.datePicker.getStartDate()) {
        alert('Please select dates first (either from weekly prices or custom date picker).');
        return;
    }
    
    const dateFrom = window.datePicker.getStartDate().format('YYYY-MM-DD');
    const dateTo = window.datePicker.getEndDate().format('YYYY-MM-DD');
    
    // TODO: Implement Stripe checkout or booking flow
    alert('Booking functionality coming soon!\n\nSelected dates:\n' + dateFrom + ' to ' + dateTo);
}

// Initialize Google Maps
function initMap() {
    console.log('initMap called');
    console.log('yachtLocation:', yachtLocation);
    console.log('google defined:', typeof google !== 'undefined');
    
    if (typeof google === 'undefined') {
        console.error('Google Maps API not loaded');
        return;
    }
    
    if (!yachtLocation) {
        console.error('No yacht location provided');
        return;
    }
    
    const mapElement = document.getElementById('yachtMap');
    if (!mapElement) {
        console.error('Map element not found');
        return;
    }
    
    console.log('Geocoding location:', yachtLocation);
    
    // Geocode the location
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: yachtLocation }, function(results, status) {
        console.log('Geocode status:', status);
        console.log('Geocode results:', results);
        
        if (status === 'OK') {
            const map = new google.maps.Map(mapElement, {
                zoom: 14,
                center: results[0].geometry.location,
                mapTypeId: 'satellite'
            });
            
            new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                title: yachtLocation
            });
            
            console.log('Map initialized successfully');
        } else {
            console.error('Geocode was not successful for the following reason: ' + status);
            // Fallback: show text location if geocoding fails
            mapElement.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 18px; color: #6b7280;">Base Location: ' + yachtLocation + '</div>';
        }
    });
}

// Auto-advance image carousel every 5 seconds
setInterval(function() {
    if (yachtCarousel.slides.length > 1) {
        yachtCarousel.next();
    }
}, 5000);
</script>
