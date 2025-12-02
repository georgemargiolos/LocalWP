<?php
/**
 * Yacht Details v3 Styles - COMPLETE RESPONSIVE UPDATE
 * Version: 2.6.0
 * 
 * FEATURES:
 * ✓ Sticky booking sidebar on desktop
 * ✓ All content flows on LEFT, booking on RIGHT
 * ✓ Fully responsive (mobile-first)
 * ✓ Touch-friendly (44px minimum tap targets)
 * ✓ Equipment icons properly styled
 * ✓ Technical characteristics with icons
 * ✓ CSS variables for easy color customization
 */

// Get custom colors from admin settings
$colors = array(
    'primary' => get_option('yolo_ys_color_primary', '#1e3a8a'),
    'primary_hover' => get_option('yolo_ys_color_primary_hover', '#1e40af'),
    'secondary' => get_option('yolo_ys_color_secondary', '#b91c1c'),
    'secondary_hover' => get_option('yolo_ys_color_secondary_hover', '#991b1b'),
    'success' => get_option('yolo_ys_color_success', '#059669'),
    'text_dark' => get_option('yolo_ys_color_text_dark', '#1f2937'),
    'text_light' => get_option('yolo_ys_color_text_light', '#6b7280'),
);
?>
<style>
/* ============================================
   CSS CUSTOM PROPERTIES - Easy Color Customization
   ============================================ */
:root {
    --yolo-primary: <?php echo esc_attr($colors['primary']); ?>;
    --yolo-primary-hover: <?php echo esc_attr($colors['primary_hover']); ?>;
    --yolo-primary-light: #dbeafe;
    --yolo-secondary: <?php echo esc_attr($colors['secondary']); ?>;
    --yolo-secondary-hover: <?php echo esc_attr($colors['secondary_hover']); ?>;
    --yolo-success: <?php echo esc_attr($colors['success']); ?>;
    --yolo-warning: #92400e;
    --yolo-warning-bg: #fef3c7;
    --yolo-danger: #dc2626;
    --yolo-danger-bg: #fef2f2;
    --yolo-text-dark: <?php echo esc_attr($colors['text_dark']); ?>;
    --yolo-text-medium: #4b5563;
    --yolo-text-light: <?php echo esc_attr($colors['text_light']); ?>;
    --yolo-border: #e5e7eb;
    --yolo-bg-light: #f9fafb;
    --yolo-bg-lighter: #f3f4f6;
    --yolo-white: #ffffff;
    --yolo-radius-sm: 6px;
    --yolo-radius-md: 8px;
    --yolo-radius-lg: 12px;
    --yolo-shadow-sm: 0 2px 4px rgba(0,0,0,0.05);
    --yolo-shadow-md: 0 4px 12px rgba(0,0,0,0.1);
    --yolo-shadow-lg: 0 8px 24px rgba(0,0,0,0.15);
    --yolo-transition: 0.3s ease;
    --yolo-container-padding: clamp(12px, 4vw, 40px);
    --yolo-sidebar-width: 380px;
}

/* ============================================
   BASE CONTAINER
   ============================================ */.yolo-yacht-details-v3 {
    width: 100%;
    max-width: 100vw; /* Constrain to viewport width - fixes WordPress theme overflow */
    margin: 0;
    padding: var(--yolo-container-padding);
    box-sizing: border-box;
    overflow: visible; /* Allow sticky positioning to work */
}

.yolo-yacht-details-v3 *,
.yolo-yacht-details-v3 *::before,
.yolo-yacht-details-v3 *::after {
    box-sizing: border-box;
}

/* ============================================
   YACHT HEADER
   ============================================ */
.yacht-header {
    text-align: center;
    margin-bottom: clamp(20px, 4vw, 30px);
    padding: 0 10px;
}

.yacht-name {
    font-size: clamp(24px, 6vw, 42px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin: 0 0 8px 0;
    letter-spacing: clamp(1px, 0.5vw, 2px);
    line-height: 1.2;
    word-wrap: break-word;
}

.yacht-model {
    font-size: clamp(16px, 4vw, 24px);
    font-weight: 600;
    color: var(--yolo-primary);
    margin: 0 0 8px 0;
}

.yacht-location {
    font-size: clamp(14px, 3vw, 16px);
    color: var(--yolo-text-light);
    margin: 0;
}

/* ============================================
   MAIN LAYOUT - Bootstrap Grid handles page layout
   ============================================ */
/* Page layout removed - Bootstrap Grid handles container/row/col */

/* Main Content Area - Just spacing */
.yacht-main-content {
    display: flex;
    flex-direction: column;
    gap: clamp(24px, 5vw, 40px);
}

/* ============================================
   STICKY SIDEBAR FIX - Research-backed solution
   ============================================
   PROBLEM: Bootstrap's .row uses display:flex with align-items:stretch,
            which makes columns equal height and breaks position:sticky
   SOLUTION: Use align-self:flex-start to prevent column stretching
   SOURCES: Bootstrap 5.3 docs + Stack Overflow validation
   ============================================ */

@media (min-width: 992px) {
    /* REMOVED: align-self on column was preventing sticky from working
       The column needs to stretch to full row height for sticky to work properly
       The sidebar itself will stick within the tall column */
    /* .col-lg-4:has(.yacht-booking-section) {
        align-self: flex-start !important;
    } */
}

/* Booking Sidebar - Sticky positioning */
.yacht-booking-section {
    position: sticky;
    top: 100px;                          /* Stick 100px from top of viewport */
    max-height: calc(100vh - 120px);     /* Constrain height to viewport */
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: var(--yolo-primary) var(--yolo-bg-light);
}

/* Disable sticky on mobile/tablet (not needed when columns stack) */
@media (max-width: 991px) {
    .yacht-booking-section {
        position: static !important;
        align-self: auto;
        max-height: none;
        overflow-y: visible;
    }
}

.yacht-booking-section::-webkit-scrollbar {
    width: 6px;
}

.yacht-booking-section::-webkit-scrollbar-track {
    background: var(--yolo-bg-light);
    border-radius: 3px;
}

.yacht-booking-section::-webkit-scrollbar-thumb {
    background: var(--yolo-primary);
    border-radius: 3px;
}

/* ============================================
   IMAGE CAROUSEL
   ============================================ */
.yacht-images-carousel {
    position: relative;
    background: var(--yolo-bg-lighter);
    border-radius: var(--yolo-radius-lg);
    overflow: hidden;
    width: 100%;
}

.carousel-container {
    position: relative;
    width: 100%;
    height: clamp(250px, 50vw, 550px);
}

@media (min-width: 768px) {
    .carousel-container {
        height: clamp(350px, 45vw, 550px);
    }
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
    touch-action: pan-y pinch-zoom;
}

.carousel-slide.active {
    opacity: 1;
}

.carousel-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Carousel Navigation - Touch Friendly (min 48px) */
.carousel-prev,
.carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    min-width: 48px;
    min-height: 48px;
    width: clamp(48px, 10vw, 64px);
    height: clamp(48px, 10vw, 64px);
    font-size: clamp(24px, 5vw, 36px);
    cursor: pointer;
    z-index: 10;
    transition: all var(--yolo-transition);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    -webkit-tap-highlight-color: transparent;
}

.carousel-prev:hover,
.carousel-next:hover {
    background: rgba(0, 0, 0, 0.85);
    transform: translateY(-50%) scale(1.05);
}

.carousel-prev:active,
.carousel-next:active {
    transform: translateY(-50%) scale(0.95);
}

.carousel-prev {
    left: clamp(10px, 3vw, 20px);
}

.carousel-next {
    right: clamp(10px, 3vw, 20px);
}

/* Carousel Dots */
.carousel-dots {
    position: absolute;
    bottom: clamp(12px, 3vw, 20px);
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    gap: clamp(8px, 2vw, 12px);
    z-index: 10;
    flex-wrap: wrap;
    justify-content: center;
    max-width: 90%;
    padding: 10px 16px;
    background: rgba(0, 0, 0, 0.4);
    border-radius: 25px;
}

.carousel-dots .dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    cursor: pointer;
    transition: all var(--yolo-transition);
    border: none;
    padding: 0;
}

.carousel-dots .dot.active,
.carousel-dots .dot:hover {
    background: white;
    transform: scale(1.3);
}

.no-images {
    display: flex;
    align-items: center;
    justify-content: center;
    height: clamp(200px, 40vw, 400px);
    font-size: clamp(18px, 4vw, 24px);
    color: #9ca3af;
}

/* ============================================
   BOOKING SECTION (Sticky Sidebar)
   ============================================ */
.yacht-booking-section {
    background: var(--yolo-white);
    border: 2px solid var(--yolo-border);
    border-radius: var(--yolo-radius-lg);
    padding: clamp(16px, 4vw, 24px);
    box-shadow: var(--yolo-shadow-md);
}

.yacht-booking-section h3 {
    font-size: clamp(18px, 4vw, 22px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin: 0 0 20px 0;
    text-align: center;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--yolo-border);
}

.yacht-booking-section h4 {
    font-size: clamp(14px, 3.5vw, 16px);
    font-weight: 600;
    color: var(--yolo-text-dark);
    margin: 20px 0 12px 0;
    text-align: center;
}

/* ============================================
   PRICE CAROUSEL
   ============================================ */
.yacht-price-carousel-section {
    padding: clamp(16px, 4vw, 24px);
    background: linear-gradient(135deg, var(--yolo-bg-light) 0%, #eef2ff 100%);
    border-radius: var(--yolo-radius-lg);
    border: 1px solid var(--yolo-border);
}

.yacht-price-carousel-section h3 {
    font-size: clamp(18px, 4vw, 24px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin: 0 0 20px 0;
    text-align: center;
}

.price-carousel-container {
    position: relative;
    padding: 0 clamp(40px, 10vw, 55px);
}

.price-carousel-slides {
    display: flex;
    gap: clamp(12px, 2vw, 16px);
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 10px 5px 15px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.price-carousel-slides::-webkit-scrollbar {
    display: none;
}

.price-slide {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: clamp(14px, 3vw, 20px);
    border: 2px solid var(--yolo-border);
    border-radius: var(--yolo-radius-md);
    background: var(--yolo-white);
    transition: all var(--yolo-transition);
    min-width: clamp(200px, 50vw, 260px);
    flex-shrink: 0;
    cursor: pointer;
    box-shadow: var(--yolo-shadow-sm);
}

@media (min-width: 768px) {
    .price-slide {
        min-width: clamp(220px, 25vw, 260px);
    }
}

.price-slide:hover {
    border-color: var(--yolo-primary);
    box-shadow: var(--yolo-shadow-md);
    transform: translateY(-3px);
}

.price-slide.active {
    border-color: var(--yolo-primary);
    background: linear-gradient(to bottom, #eff6ff 0%, var(--yolo-white) 100%);
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
}

.price-week {
    font-size: clamp(12px, 2.8vw, 14px);
    font-weight: 600;
    color: var(--yolo-text-dark);
    margin-bottom: 8px;
}

.price-product {
    font-size: clamp(10px, 2.2vw, 11px);
    color: var(--yolo-text-light);
    margin-bottom: 8px;
}

.price-original {
    margin-bottom: 5px;
}

.strikethrough {
    text-decoration: line-through;
    color: #9ca3af;
    font-size: clamp(13px, 2.8vw, 15px);
}

.price-discount-badge {
    background: var(--yolo-warning-bg);
    color: var(--yolo-warning);
    padding: 5px 10px;
    border-radius: 4px;
    font-size: clamp(10px, 2.2vw, 11px);
    font-weight: 600;
    margin-bottom: 10px;
    display: inline-block;
}

.price-final {
    font-size: clamp(18px, 4.5vw, 24px);
    font-weight: 700;
    color: var(--yolo-success);
    margin-bottom: 12px;
}

.price-select-btn {
    background: var(--yolo-primary);
    color: white;
    border: none;
    padding: clamp(12px, 3vw, 14px);
    border-radius: var(--yolo-radius-sm);
    font-size: clamp(12px, 2.8vw, 14px);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--yolo-transition);
    width: 100%;
    min-height: 48px;
}

.price-select-btn:hover {
    background: var(--yolo-primary-hover);
}

/* Price Carousel Arrows */
.price-carousel-prev,
.price-carousel-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: var(--yolo-primary);
    color: white;
    border: none;
    min-width: 44px;
    min-height: 44px;
    width: clamp(44px, 9vw, 52px);
    height: clamp(44px, 9vw, 52px);
    font-size: clamp(20px, 4vw, 26px);
    cursor: pointer;
    border-radius: 50%;
    transition: all var(--yolo-transition);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: var(--yolo-shadow-md);
}

.price-carousel-prev:hover,
.price-carousel-next:hover {
    background: var(--yolo-primary-hover);
    transform: translateY(-50%) scale(1.1);
}

.price-carousel-prev {
    left: 0;
}

.price-carousel-next {
    right: 0;
}

/* ============================================
   DATE PICKER
   ============================================ */
.date-picker-section {
    margin-bottom: 20px;
    position: relative;
    z-index: 100;
}

.litepicker {
    z-index: 9999 !important;
}

#dateRangePicker {
    width: 100%;
    padding: clamp(14px, 3.5vw, 16px);
    border: 2px solid #212529;
    border-radius: var(--yolo-radius-sm);
    font-size: clamp(14px, 3.5vw, 16px);
    font-weight: 500;
    text-align: center;
    cursor: pointer;
    background: var(--yolo-white);
    transition: all 0.2s;
    min-height: 52px;
}

#dateRangePicker:hover {
    transform: translateY(-2px);
    box-shadow: var(--yolo-shadow-sm);
}

#dateRangePicker:focus {
    outline: none;
    border-color: var(--yolo-primary);
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
}

/* ============================================
   SELECTED PRICE DISPLAY
   ============================================ */
.selected-price-display {
    background: linear-gradient(135deg, #f0f9ff 0%, #eff6ff 100%);
    border: 2px solid var(--yolo-primary-light);
    border-radius: var(--yolo-radius-md);
    padding: clamp(14px, 3.5vw, 20px);
    margin-bottom: 20px;
    text-align: center;
}

.selected-price-original {
    font-size: clamp(13px, 3vw, 14px);
    color: var(--yolo-text-light);
    margin-bottom: 6px;
}

.selected-price-discount {
    background: var(--yolo-warning-bg);
    color: var(--yolo-warning);
    font-size: clamp(11px, 2.5vw, 12px);
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 4px;
    display: inline-block;
    margin-bottom: 10px;
}

.selected-price-final {
    font-size: clamp(26px, 7vw, 32px);
    font-weight: 700;
    color: var(--yolo-primary);
}

/* ============================================
   BOOK NOW BUTTON
   ============================================ */
.btn-book-now {
    width: 100%;
    padding: clamp(16px, 4vw, 20px);
    background: linear-gradient(135deg, var(--yolo-secondary) 0%, #dc2626 100%);
    color: white;
    border: none;
    border-radius: var(--yolo-radius-md);
    font-size: clamp(16px, 4vw, 18px);
    font-weight: 700;
    cursor: pointer;
    transition: all var(--yolo-transition);
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    min-height: 70px;
    box-shadow: 0 4px 14px rgba(185, 28, 28, 0.25);
}

.btn-book-now .btn-main-text {
    font-size: clamp(16px, 4vw, 18px);
    font-weight: 700;
    letter-spacing: 1.5px;
}

.btn-book-now .btn-sub-text {
    font-size: clamp(12px, 2.8vw, 14px);
    font-weight: 500;
    opacity: 0.95;
}

.btn-book-now:hover {
    background: linear-gradient(135deg, #991b1b 0%, #b91c1c 100%);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(185, 28, 28, 0.35);
}

/* ============================================
   QUOTE SECTION & FORM
   ============================================ */
.quote-section {
    text-align: center;
    margin-bottom: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--yolo-border);
}

.quote-label {
    font-size: clamp(13px, 3vw, 14px);
    color: var(--yolo-text-light);
    margin: 0 0 12px 0;
}

.btn-request-quote {
    width: 100%;
    padding: clamp(14px, 3.5vw, 16px);
    background: var(--yolo-primary);
    color: white;
    border: none;
    border-radius: var(--yolo-radius-sm);
    font-size: clamp(14px, 3.5vw, 16px);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--yolo-transition);
    min-height: 52px;
}

.btn-request-quote:hover {
    background: var(--yolo-primary-hover);
    transform: translateY(-2px);
}

.quote-form {
    margin-top: 20px;
    padding: clamp(16px, 4vw, 20px);
    background: var(--yolo-bg-light);
    border-radius: var(--yolo-radius-md);
}

.quote-form h4 {
    font-size: clamp(16px, 4vw, 18px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin: 0 0 16px 0;
    text-align: center;
}

/* form-row now uses Bootstrap Grid (row g-2 with col-12 col-sm-6) */
.form-row {
    margin-bottom: 10px;
}

/* form-row responsive behavior handled by Bootstrap Grid */

.form-field {
    margin-bottom: 10px;
}

.form-field input,
.form-field textarea {
    width: 100%;
    padding: clamp(12px, 3vw, 14px);
    border: 1px solid #d1d5db;
    border-radius: var(--yolo-radius-sm);
    font-size: clamp(14px, 3.5vw, 16px);
    font-family: inherit;
    min-height: 48px;
    -webkit-appearance: none;
}

.form-field input:focus,
.form-field textarea:focus {
    outline: none;
    border-color: var(--yolo-primary);
    box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
}

.form-field textarea {
    min-height: 100px;
    resize: vertical;
}

.form-note {
    font-size: 12px;
    color: var(--yolo-text-light);
    margin: 10px 0 5px 0;
}

.form-tagline {
    font-size: 13px;
    color: var(--yolo-text-medium);
    margin: 0 0 15px 0;
    text-align: center;
}

.btn-submit-quote {
    width: 100%;
    padding: clamp(14px, 3.5vw, 16px);
    background: var(--yolo-primary);
    color: white;
    border: none;
    border-radius: var(--yolo-radius-sm);
    font-size: clamp(14px, 3.5vw, 16px);
    font-weight: 600;
    cursor: pointer;
    transition: all var(--yolo-transition);
    min-height: 50px;
}

.btn-submit-quote:hover {
    background: var(--yolo-primary-hover);
}

/* ============================================
   QUICK SPECS
   ============================================ */
/* yacht-quick-specs now uses Bootstrap Grid (row g-3 with col-6 col-sm-6 col-md-3) */
.yacht-quick-specs {
    /* No grid styles needed - Bootstrap handles layout */
}

.spec-item {
    text-align: center;
    padding: clamp(16px, 4vw, 24px);
    background: var(--yolo-white);
    border: 1px solid var(--yolo-border);
    border-radius: var(--yolo-radius-lg);
    transition: all var(--yolo-transition);
    box-shadow: var(--yolo-shadow-sm);
    min-height: 180px; /* Ensure all spec boxes have same height as Refit box */
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.spec-item:hover {
    transform: translateY(-3px);
    box-shadow: var(--yolo-shadow-md);
    border-color: var(--yolo-primary-light);
}

.spec-icon {
    margin-bottom: clamp(10px, 2.5vw, 14px);
}

.spec-icon i {
    font-size: clamp(28px, 6vw, 36px);
    color: var(--yolo-primary);
}

.spec-value {
    font-size: clamp(18px, 4.5vw, 24px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin-bottom: 5px;
}

.spec-value .refit {
    display: block;
    font-size: clamp(11px, 2.5vw, 12px);
    font-weight: 400;
    color: var(--yolo-text-light);
    margin-top: 4px;
}

.spec-label {
    font-size: clamp(12px, 3vw, 14px);
    color: var(--yolo-text-light);
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ============================================
   DESCRIPTION SECTION
   ============================================ */
.yacht-description-section {
    margin-bottom: 0;
}

.yacht-description-section h3 {
    font-size: clamp(20px, 5vw, 26px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 3px solid var(--yolo-primary);
}

.yacht-description-content {
    font-size: clamp(14px, 3.5vw, 16px);
    line-height: 1.8;
    color: var(--yolo-text-medium);
    background: var(--yolo-white);
    padding: clamp(16px, 4vw, 24px);
    border-radius: var(--yolo-radius-md);
    border-left: 4px solid var(--yolo-primary);
    box-shadow: var(--yolo-shadow-sm);
}

.description-preview,
.description-full {
    margin-bottom: 0;
}

.description-toggle {
    background: none;
    border: none;
    color: var(--yolo-primary);
    font-size: clamp(14px, 3.5vw, 16px);
    font-weight: 600;
    cursor: pointer;
    padding: 12px 0;
    margin-top: 10px;
    text-decoration: underline;
    transition: color 0.2s;
    min-height: 44px;
    display: inline-block;
}

.description-toggle:hover {
    color: var(--yolo-primary-hover);
}

/* ============================================
   EQUIPMENT SECTION
   ============================================ */
.yacht-equipment-section h3 {
    font-size: clamp(20px, 5vw, 26px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin: 0 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 3px solid var(--yolo-primary);
}

/* equipment-grid now uses Bootstrap Grid (row g-3 with col-12 col-sm-6) */
.equipment-grid {
    /* No grid styles needed - Bootstrap handles layout */
}

/* equipment-grid responsive behavior handled by Bootstrap Grid */

.equipment-item {
    display: flex;
    align-items: center;
    gap: clamp(12px, 3vw, 16px);
    padding: clamp(14px, 3.5vw, 18px);
    background: var(--yolo-white);
    border-radius: var(--yolo-radius-md);
    font-size: clamp(14px, 3.5vw, 15px);
    color: var(--yolo-text-dark);
    border: 1px solid var(--yolo-border);
    transition: all 0.2s ease;
    box-shadow: var(--yolo-shadow-sm);
}

.equipment-item:hover {
    background: var(--yolo-bg-light);
    border-color: var(--yolo-primary-light);
    transform: translateX(5px);
}

/* Equipment Icon Box */
.equipment-item i {
    font-size: clamp(20px, 4.5vw, 24px);
    min-width: clamp(44px, 10vw, 52px);
    height: clamp(44px, 10vw, 52px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--yolo-primary);
    background: var(--yolo-primary-light);
    border-radius: var(--yolo-radius-md);
    flex-shrink: 0;
}

.equipment-item span {
    font-weight: 500;
}

/* ============================================
   MAP SECTION
   ============================================ */
.yacht-map-section h3 {
    font-size: clamp(20px, 5vw, 26px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid var(--yolo-primary);
}

.map-container {
    position: relative;
    width: 100%;
    padding-bottom: 50%;
    height: 0;
    overflow: hidden;
    border-radius: var(--yolo-radius-lg);
    box-shadow: var(--yolo-shadow-md);
}

.map-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border: 0;
}

/* ============================================
   TECHNICAL CHARACTERISTICS
   ============================================ */
.yacht-technical h3 {
    font-size: clamp(20px, 5vw, 26px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid var(--yolo-primary);
}

/* tech-grid now uses Bootstrap Grid (row g-3 with col-12 col-sm-6) */
.tech-grid {
    /* No grid styles needed - Bootstrap handles layout */
}

/* tech-grid responsive behavior handled by Bootstrap Grid */

.tech-item {
    background: var(--yolo-white);
    padding: clamp(16px, 4vw, 20px);
    border-radius: var(--yolo-radius-md);
    display: flex;
    align-items: center;
    gap: clamp(14px, 3.5vw, 18px);
    border: 1px solid var(--yolo-border);
    box-shadow: var(--yolo-shadow-sm);
    transition: all var(--yolo-transition);
}

.tech-item:hover {
    border-color: var(--yolo-primary-light);
    transform: translateY(-2px);
    box-shadow: var(--yolo-shadow-md);
}

.tech-icon {
    width: clamp(50px, 12vw, 60px);
    height: clamp(50px, 12vw, 60px);
    background: linear-gradient(135deg, var(--yolo-primary) 0%, #2563eb 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(30, 58, 138, 0.3);
}

.tech-icon i {
    font-size: clamp(20px, 5vw, 26px);
    color: var(--yolo-white);
}

.tech-content {
    flex: 1;
    min-width: 0;
}

.tech-label {
    font-size: clamp(11px, 2.5vw, 12px);
    font-weight: 700;
    color: var(--yolo-text-light);
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 4px;
}

.tech-value {
    font-size: clamp(16px, 4vw, 20px);
    font-weight: 600;
    color: var(--yolo-primary);
}

/* ============================================
   EXTRAS SECTION
   ============================================ */
.yacht-extras-combined h3 {
    font-size: clamp(20px, 5vw, 26px);
    font-weight: 700;
    color: var(--yolo-text-dark);
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 3px solid var(--yolo-primary);
}

.yacht-extras-combined .extras-note {
    font-size: clamp(13px, 3vw, 14px);
    font-weight: 400;
    color: var(--yolo-text-light);
    font-style: italic;
}

/* extras-two-column now uses Bootstrap Grid (row g-4 with col-12 col-md-6) */
.extras-two-column {
    /* No grid styles needed - Bootstrap handles layout */
}

.extras-column h4 {
    font-size: clamp(16px, 4vw, 18px);
    font-weight: 600;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.extras-column:first-child h4 {
    color: var(--yolo-danger);
}

.extras-column:last-child h4 {
    color: var(--yolo-primary);
}

/* extras-grid is a simple vertical list - no grid needed */
.extras-grid {
    display: flex;
    flex-direction: column;
    gap: clamp(10px, 2.5vw, 12px);
}

.extra-item {
    padding: clamp(14px, 3.5vw, 18px);
    background: var(--yolo-white);
    border-radius: var(--yolo-radius-md);
    border: 1px solid var(--yolo-border);
    box-shadow: var(--yolo-shadow-sm);
}

.extra-item.obligatory {
    background: var(--yolo-danger-bg);
    border-color: #fecaca;
    border-left: 4px solid var(--yolo-danger);
}

.extra-item.optional {
    background: #f0f9ff;
    border-color: #bfdbfe;
    border-left: 4px solid var(--yolo-primary);
}

.extra-name {
    font-size: clamp(14px, 3.5vw, 16px);
    color: var(--yolo-text-dark);
    font-weight: 600;
}

.extra-price {
    font-size: clamp(15px, 3.5vw, 17px);
    font-weight: 700;
    color: var(--yolo-success);
    margin-top: 8px;
}

.price-unit {
    font-size: clamp(11px, 2.5vw, 12px);
    font-weight: 400;
    color: var(--yolo-text-light);
    margin-left: 4px;
}

.payment-location {
    font-size: clamp(11px, 2.5vw, 12px);
    color: var(--yolo-text-light);
    display: block;
    margin-top: 4px;
}

/* ============================================
   BACK BUTTON
   ============================================ */
.yacht-actions {
    margin-top: clamp(30px, 6vw, 50px);
    text-align: center;
}

.btn-back {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: clamp(14px, 3.5vw, 16px) clamp(28px, 6vw, 40px);
    background: #6b7280;
    color: white;
    text-decoration: none;
    border-radius: var(--yolo-radius-md);
    font-weight: 600;
    font-size: clamp(14px, 3.5vw, 16px);
    transition: all var(--yolo-transition);
    min-height: 52px;
}

.btn-back:hover {
    background: #4b5563;
    transform: translateY(-2px);
    color: white;
}

/* ============================================
   MOBILE SWIPE HINT
   ============================================ */
@media (max-width: 767px) {
    .price-carousel-container::after {
        content: '← Swipe to see more →';
        display: block;
        text-align: center;
        font-size: 12px;
        color: var(--yolo-text-light);
        margin-top: 12px;
        opacity: 0.8;
    }
}

/* ============================================
   INFO CARDS (Security Deposit, Cancellation, Check-in)
   ============================================ */
.info-card {
    background: var(--yolo-white);
    border: 1px solid var(--yolo-border);
    border-radius: var(--yolo-radius-lg);
    padding: clamp(20px, 4vw, 30px);
    box-shadow: var(--yolo-shadow-sm);
    transition: all var(--yolo-transition);
}

.info-card:hover {
    box-shadow: var(--yolo-shadow-md);
    transform: translateY(-2px);
}

/* Security Deposit Card */
.deposit-amount-large {
    font-size: clamp(32px, 5vw, 48px);
    font-weight: 700;
    color: var(--yolo-primary);
    margin-bottom: 20px;
    text-align: center;
}

.deposit-card p {
    margin-bottom: 15px;
    line-height: 1.6;
    color: var(--yolo-text);
}

.deposit-note {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--yolo-border);
    font-size: 0.95em;
}

/* Cancellation Policy Card */
.cancellation-timeline {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-bottom: 25px;
}

.timeline-item {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 15px;
    background: var(--yolo-light-bg);
    border-radius: var(--yolo-radius-md);
    transition: all var(--yolo-transition);
}

.timeline-item:hover {
    background: var(--yolo-primary-light);
    transform: translateX(5px);
}

.timeline-icon {
    font-size: 32px;
    color: var(--yolo-primary);
    flex-shrink: 0;
}

.timeline-content {
    flex: 1;
}

.timeline-label {
    font-weight: 600;
    color: var(--yolo-text);
    margin-bottom: 5px;
}

.timeline-value {
    font-size: 1.1em;
    font-weight: 700;
    color: var(--yolo-primary);
}

.cancellation-note {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid var(--yolo-border);
    font-size: 0.95em;
    color: var(--yolo-text);
}

/* Check-in/Check-out Cards */
.checkin-card,
.checkout-card,
.checkin-day-card {
    text-align: center;
    padding: clamp(20px, 4vw, 30px);
}

.card-icon {
    font-size: 48px;
    color: var(--yolo-primary);
    margin-bottom: 15px;
}

.card-label {
    font-size: 0.9em;
    color: var(--yolo-text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    font-weight: 600;
}

.card-value {
    font-size: clamp(20px, 3vw, 28px);
    font-weight: 700;
    color: var(--yolo-text);
}

.checkout-card .card-icon {
    color: var(--yolo-error);
}

.checkin-day-card .card-icon {
    color: var(--yolo-success);
}

/* Responsive adjustments */
@media (max-width: 767px) {
    .cancellation-timeline {
        gap: 15px;
    }
    
    .timeline-item {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .timeline-icon {
        font-size: 28px;
    }
}

/* ============================================
   PRINT STYLES
   ============================================ */
@media print {
    .yacht-booking-sidebar,
    .carousel-prev,
    .carousel-next,
    .carousel-dots,
    .price-carousel-prev,
    .price-carousel-next {
        display: none !important;
    }
    
    /* Bootstrap Grid handles layout */
}
</style>

