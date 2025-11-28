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

// Price Carousel - Show 4 weeks at a time
const priceCarousel = {
    currentIndex: 0,
    visibleSlides: 4,
    slides: document.querySelectorAll('.price-slide'),
    container: document.querySelector('.price-carousel-slides'),
    
    init: function() {
        this.updateView();
    },
    
    updateView: function() {
        if (this.slides.length === 0) return;
        
        // Hide all slides
        this.slides.forEach(slide => {
            slide.style.display = 'none';
            slide.classList.remove('active');
        });
        
        // Show 4 slides starting from currentIndex
        for (let i = 0; i < this.visibleSlides && (this.currentIndex + i) < this.slides.length; i++) {
            this.slides[this.currentIndex + i].style.display = 'block';
            if (i === 0) {
                this.slides[this.currentIndex + i].classList.add('active');
            }
        }
        
        // Update arrow visibility
        this.updateArrows();
    },
    
    updateArrows: function() {
        const prevBtn = document.querySelector('.price-carousel-prev');
        const nextBtn = document.querySelector('.price-carousel-next');
        
        if (prevBtn) {
            prevBtn.style.opacity = this.currentIndex > 0 ? '1' : '0.3';
            prevBtn.style.cursor = this.currentIndex > 0 ? 'pointer' : 'not-allowed';
        }
        
        if (nextBtn) {
            const hasMore = (this.currentIndex + this.visibleSlides) < this.slides.length;
            nextBtn.style.opacity = hasMore ? '1' : '0.3';
            nextBtn.style.cursor = hasMore ? 'pointer' : 'not-allowed';
        }
    },
    
    next: function() {
        if (this.slides.length === 0) return;
        if ((this.currentIndex + this.visibleSlides) < this.slides.length) {
            this.currentIndex += this.visibleSlides;
            this.updateView();
        }
    },
    
    prev: function() {
        if (this.slides.length === 0) return;
        if (this.currentIndex > 0) {
            this.currentIndex = Math.max(0, this.currentIndex - this.visibleSlides);
            this.updateView();
        }
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
