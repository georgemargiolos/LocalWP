<?php
/**
 * Yacht Details V3 - Inline CSS Variables
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get individual color settings from admin
$color_primary = get_option('yolo_ys_color_primary', '#1e3a8a');
$color_primary_hover = get_option('yolo_ys_color_primary_hover', '#1e40af');
$color_secondary = get_option('yolo_ys_color_secondary', '#b91c1c');
$color_secondary_hover = get_option('yolo_ys_color_secondary_hover', '#991b1b');
$color_success = get_option('yolo_ys_color_success', '#059669');
$color_warning = get_option('yolo_ys_color_warning', '#92400e');
$color_danger = get_option('yolo_ys_color_danger', '#dc2626');
$color_text_dark = get_option('yolo_ys_color_text_dark', '#1f2937');
$color_text_medium = get_option('yolo_ys_color_text_medium', '#4b5563');
$color_text_light = get_option('yolo_ys_color_text_light', '#6b7280');
$color_border = get_option('yolo_ys_color_border', '#e5e7eb');
$color_bg_light = get_option('yolo_ys_color_bg_light', '#f9fafb');
?>
<style>
:root {
    --yolo-primary: <?php echo esc_attr($color_primary); ?>;
    --yolo-primary-hover: <?php echo esc_attr($color_primary_hover); ?>;
    --yolo-primary-light: #dbeafe;
    --yolo-secondary: <?php echo esc_attr($color_secondary); ?>;
    --yolo-secondary-hover: <?php echo esc_attr($color_secondary_hover); ?>;
    --yolo-success: <?php echo esc_attr($color_success); ?>;
    --yolo-warning: <?php echo esc_attr($color_warning); ?>;
    --yolo-warning-bg: #fef3c7;
    --yolo-danger: <?php echo esc_attr($color_danger); ?>;
    --yolo-danger-bg: #fef2f2;
    --yolo-text-dark: <?php echo esc_attr($color_text_dark); ?>;
    --yolo-text-medium: <?php echo esc_attr($color_text_medium); ?>;
    --yolo-text-light: <?php echo esc_attr($color_text_light); ?>;
    --yolo-border: <?php echo esc_attr($color_border); ?>;
    --yolo-bg-light: <?php echo esc_attr($color_bg_light); ?>;
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
   MOBILE STICKY BOTTOM BAR (v75.13)
   Shows "CHECK AVAILABILITY" button on mobile
   ============================================ */

/* Hide by default on desktop */
.yolo-mobile-sticky-bar {
    display: none;
}

/* Show only on mobile/tablet */
@media (max-width: 991px) {
    .yolo-mobile-sticky-bar {
        display: block;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9998;
        background: #fff;
        padding: 12px 16px;
        box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.15);
        transform: translateY(0);
        transition: transform 0.3s ease, opacity 0.3s ease;
        /* Safe area for iPhone notch */
        padding-bottom: calc(12px + env(safe-area-inset-bottom, 0px));
    }
    
    .yolo-mobile-sticky-bar.hidden {
        transform: translateY(100%);
        opacity: 0;
        pointer-events: none;
    }
    
    .yolo-sticky-cta-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 16px 24px;
        background: var(--yolo-secondary, #b91c1c);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: background 0.2s ease, transform 0.1s ease;
        /* Minimum touch target */
        min-height: 52px;
    }
    
    .yolo-sticky-cta-btn:hover {
        background: var(--yolo-secondary-hover, #991b1b);
    }
    
    .yolo-sticky-cta-btn:active {
        transform: scale(0.98);
    }
    
    .yolo-sticky-cta-btn i {
        font-size: 18px;
    }
    
    /* Add padding to bottom of page so content isn't hidden behind sticky bar */
    .yolo-yacht-details-v3 {
        padding-bottom: 80px;
    }
}

/* Hide sticky bar when printing */
@media print {
    .yolo-mobile-sticky-bar {
        display: none !important;
    }
}

/* ============================================
   MOBILE ACCORDIONS (v75.13)
   Collapsible sections on mobile for better UX
   ============================================ */

/* Desktop: No accordion behavior */
@media (min-width: 992px) {
    .yolo-accordion-toggle {
        display: none !important;
    }
    
    .yolo-accordion-content {
        display: block !important;
        max-height: none !important;
        overflow: visible !important;
    }
}

/* Mobile: Accordion behavior */
@media (max-width: 991px) {
    /* Accordion header (the h3 becomes clickable) */
    .yolo-accordion-section h3 {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 0;
        margin: 0;
        border-bottom: 1px solid #e5e7eb;
        user-select: none;
        -webkit-tap-highlight-color: transparent;
    }
    
    .yolo-accordion-section h3:active {
        background: #f9fafb;
    }
    
    /* Accordion toggle icon */
    .yolo-accordion-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        flex-shrink: 0;
        margin-left: 12px;
        color: #6b7280;
        transition: transform 0.3s ease;
    }
    
    .yolo-accordion-section.expanded .yolo-accordion-toggle {
        transform: rotate(180deg);
    }
    
    /* Accordion content */
    .yolo-accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.35s ease-out, padding 0.35s ease-out;
        padding: 0;
    }
    
    .yolo-accordion-section.expanded .yolo-accordion-content {
        max-height: 2000px; /* Large enough for any content */
        padding: 16px 0;
        transition: max-height 0.5s ease-in, padding 0.35s ease-in;
    }
    
    /* Remove default section margins on mobile when using accordions */
    .yolo-accordion-section {
        margin-bottom: 0 !important;
        padding: 0 !important;
        background: #fff;
    }
    
    /* Add subtle separator between accordion sections */
    .yolo-accordion-section + .yolo-accordion-section {
        border-top: none;
    }
    
    /* Style adjustments for content inside accordions */
    .yolo-accordion-content .equipment-grid,
    .yolo-accordion-content .tech-grid,
    .yolo-accordion-content .extras-two-column,
    .yolo-accordion-content .checkin-grid-redesign {
        padding-top: 8px;
    }
    
    /* Info cards inside accordions */
    .yolo-accordion-content .info-card {
        margin-top: 8px;
    }
}

/* ==========================================================================
   PAYMENT ICONS BOX (v75.18)
   ========================================================================== */
.payment-icons-box {
    margin-top: 16px;
    padding: 16px;
    background: var(--yolo-bg-light);
    border-radius: var(--yolo-radius-md);
    text-align: center;
}

.payment-icons-title {
    font-size: 12px;
    color: var(--yolo-text-light);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payment-icons-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
    justify-items: center;
    align-items: center;
}

.payment-icon {
    width: 50px;
    height: 32px;
    object-fit: contain;
    transition: transform 0.2s ease, opacity 0.3s ease;
}

.payment-icon:hover {
    transform: scale(1.1);
}

.payment-icon-hidden {
    display: none;
}

.payment-icons-grid.expanded .payment-icon-hidden {
    display: block;
}

.payment-icons-toggle {
    margin-top: 12px;
}

.payment-icons-toggle a {
    font-size: 12px;
    color: var(--yolo-primary);
    text-decoration: none;
    cursor: pointer;
}

.payment-icons-toggle a:hover {
    text-decoration: underline;
}

/* Responsive adjustments */
@media (max-width: 480px) {
    .payment-icons-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 6px;
    }
    
    .payment-icon {
        width: 42px;
        height: 28px;
    }
    
    .payment-icons-box {
        padding: 12px;
    }
}
</style>
