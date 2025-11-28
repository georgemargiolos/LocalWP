<style>
.yolo-yacht-details-v3 {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.yacht-header {
    text-align: center;
    margin-bottom: 40px;
}

.yacht-name {
    font-size: 42px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 10px 0;
    letter-spacing: 2px;
}

.yacht-model {
    font-size: 24px;
    font-weight: 600;
    color: #1e3a8a;
    margin: 0 0 10px 0;
}

.yacht-location {
    font-size: 16px;
    color: #6b7280;
    margin: 0;
}

/* Main Grid: Images + Booking Section */
.yacht-main-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-bottom: 40px;
}

@media (max-width: 968px) {
    .yacht-main-grid {
        grid-template-columns: 1fr;
    }
}

/* Image Carousel */
.yacht-images-carousel {
    position: relative;
    background: #f3f4f6;
    border-radius: 12px;
    overflow: hidden;
}

.carousel-container {
    position: relative;
    width: 100%;
    height: 500px;
}

.carousel-slides {
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.carousel-slide.active {
    opacity: 1;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.carousel-prev,
.carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0,0,0,0.5);
    color: white;
    border: none;
    font-size: 40px;
    padding: 10px 20px;
    cursor: pointer;
    z-index: 10;
    transition: background 0.3s;
}

.carousel-prev:hover,
.carousel-next:hover {
    background: rgba(0,0,0,0.8);
}

.carousel-prev {
    left: 10px;
}

.carousel-next {
    right: 10px;
}

.carousel-dots {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: 10px;
    z-index: 10;
}

.carousel-dots .dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255,255,255,0.5);
    cursor: pointer;
    transition: background 0.3s;
}

.carousel-dots .dot.active,
.carousel-dots .dot:hover {
    background: white;
}

/* Booking Section */
.yacht-booking-section {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
}

.yacht-booking-section h3 {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
    text-align: center;
}

.yacht-booking-section h4 {
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    margin: 20px 0 10px 0;
    text-align: center;
}

/* Price Carousel */
.price-carousel-container {
    position: relative;
    margin-bottom: 20px;
}

.price-carousel-slides {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    min-height: 200px;
    padding: 0 40px; /* Space for arrows */
}

@media (max-width: 968px) {
    .price-carousel-slides {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 640px) {
    .price-carousel-slides {
        grid-template-columns: 1fr;
    }
}

.price-slide {
    display: none;
    text-align: center;
    padding: 15px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    transition: all 0.3s;
}

.price-slide:hover {
    border-color: #1e3a8a;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.price-slide.active {
    display: block;
    border-color: #1e3a8a;
}

.price-week {
    font-size: 12px;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 5px;
}

.price-product {
    font-size: 11px;
    color: #6b7280;
    margin-bottom: 8px;
}

.price-original {
    margin-bottom: 5px;
}

.strikethrough {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: 14px;
}

.price-discount-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 10px;
    font-weight: 600;
    margin-bottom: 8px;
    display: inline-block;
}

.price-final {
    font-size: 20px;
    font-weight: 700;
    color: #059669;
    margin-bottom: 10px;
}

.price-select-btn {
    background: #1e3a8a;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    width: 100%;
}

.price-select-btn:hover {
    background: #1e40af;
}

.price-carousel-prev,
.price-carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: #1e3a8a;
    color: white;
    border: none;
    font-size: 24px;
    padding: 8px 16px;
    cursor: pointer;
    border-radius: 4px;
    transition: background 0.3s;
}

.price-carousel-prev:hover,
.price-carousel-next:hover {
    background: #1e40af;
}

.price-carousel-prev {
    left: -15px;
}

.price-carousel-next {
    right: -15px;
}

/* Date Picker */
.date-picker-section {
    margin-bottom: 20px;
}

#dateRangePicker {
    width: 100%;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    text-align: center;
    cursor: pointer;
}

#dateRangePicker:focus {
    outline: none;
    border-color: #1e3a8a;
}

/* Book Now Button */
.btn-book-now {
    width: 100%;
    padding: 16px;
    background: #b91c1c;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
    margin-bottom: 20px;
}

.btn-book-now:hover {
    background: #991b1b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(185, 28, 28, 0.3);
}

/* Quote Section */
.quote-section {
    text-align: center;
    margin-bottom: 20px;
}

.quote-label {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 10px 0;
}

.btn-request-quote {
    width: 100%;
    padding: 14px;
    background: #1e3a8a;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-request-quote:hover {
    background: #1e40af;
    transform: translateY(-2px);
}

/* Quote Form */
.quote-form {
    margin-top: 20px;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.quote-form h4 {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 20px 0;
    text-align: center;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 10px;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-field {
    margin-bottom: 10px;
}

.form-field input,
.form-field textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 14px;
    font-family: inherit;
}

.form-field input:focus,
.form-field textarea:focus {
    outline: none;
    border-color: #1e3a8a;
}

.form-note {
    font-size: 12px;
    color: #6b7280;
    margin: 10px 0 5px 0;
}

.form-tagline {
    font-size: 13px;
    color: #4b5563;
    margin: 0 0 15px 0;
    text-align: center;
}

.btn-submit-quote {
    width: 100%;
    padding: 14px;
    background: #1e3a8a;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-submit-quote:hover {
    background: #1e40af;
}

/* Quick Specs */
.yacht-quick-specs {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    margin-bottom: 40px;
}

@media (max-width: 768px) {
    .yacht-quick-specs {
        grid-template-columns: repeat(2, 1fr);
    }
}

.spec-item {
    text-align: center;
    padding: 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.spec-icon {
    font-size: 32px;
    margin-bottom: 10px;
}

.spec-value {
    font-size: 20px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 5px;
}

.spec-value .refit {
    display: block;
    font-size: 12px;
    font-weight: 400;
    color: #6b7280;
    margin-top: 4px;
}

.spec-label {
    font-size: 14px;
    color: #6b7280;
}

/* Map Section */
.yacht-map-section {
    margin-bottom: 40px;
}

.yacht-map-section h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 20px;
}

/* Technical Characteristics */
.yacht-technical {
    margin-bottom: 40px;
}

.yacht-technical h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 20px;
}

.tech-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

@media (max-width: 768px) {
    .tech-grid {
        grid-template-columns: 1fr;
    }
}

.tech-item {
    background: #f9fafb;
    padding: 15px 20px;
    border-radius: 8px;
}

.tech-label {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.tech-value {
    font-size: 18px;
    font-weight: 600;
    color: #1e3a8a;
}

/* Description */
.yacht-description {
    margin-bottom: 40px;
}

.yacht-description h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
}

.yacht-description p {
    font-size: 16px;
    line-height: 1.6;
    color: #4b5563;
}

/* Equipment */
.yacht-equipment {
    margin-bottom: 40px;
}

.yacht-equipment h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
}

.equipment-list {
    font-size: 16px;
    line-height: 1.8;
    color: #4b5563;
}

/* Extras */
.yacht-extras {
    margin-bottom: 40px;
}

.yacht-extras h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 15px;
}

.yacht-extras.obligatory-extras h3 {
    color: #dc2626;
}

.yacht-extras .extras-note {
    font-size: 14px;
    font-weight: 400;
    color: #6b7280;
    font-style: italic;
}

.extras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
}

.extra-item {
    padding: 15px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.extra-item.obligatory {
    background: #fef2f2;
    border-color: #fecaca;
}

.extra-item.optional {
    background: #f0f9ff;
    border-color: #bfdbfe;
}

.extras-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.extras-list li {
    padding: 12px 0;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.extra-name {
    font-size: 16px;
    color: #1f2937;
}

.extra-price {
    font-size: 16px;
    font-weight: 600;
    color: #059669;
    margin-top: 8px;
}

.price-unit {
    font-size: 12px;
    font-weight: 400;
    color: #6b7280;
    margin-left: 4px;
}

.extra-obligatory {
    font-size: 12px;
    color: #dc2626;
    font-weight: 600;
}

/* Actions */
.yacht-actions {
    margin-top: 40px;
    text-align: center;
}

.btn-back {
    display: inline-block;
    padding: 12px 30px;
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-2px);
}

.no-images {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 500px;
    font-size: 24px;
    color: #9ca3af;
}
</style>
