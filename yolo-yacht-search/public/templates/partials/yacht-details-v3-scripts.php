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

// Price Carousel
const priceCarousel = {
    currentSlide: 0,
    slides: document.querySelectorAll('.price-slide'),
    
    goTo: function(index) {
        if (this.slides.length === 0) return;
        this.slides[this.currentSlide].classList.remove('active');
        this.currentSlide = index;
        this.slides[this.currentSlide].classList.add('active');
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
            fetch(yolo_ajax.ajax_url, {
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
    if (typeof google === 'undefined' || !yachtLocation) return;
    
    // Geocode the location
    const geocoder = new google.maps.Geocoder();
    geocoder.geocode({ address: yachtLocation }, function(results, status) {
        if (status === 'OK') {
            const map = new google.maps.Map(document.getElementById('yachtMap'), {
                zoom: 14,
                center: results[0].geometry.location,
                mapTypeId: 'satellite'
            });
            
            new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                title: yachtLocation
            });
        } else {
            console.error('Geocode was not successful for the following reason: ' + status);
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
