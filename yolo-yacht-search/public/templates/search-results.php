<?php
/**
 * Search Results Template
 * Uses the same yacht card component as "Our Yachts" page
 */
?>

<div class="yolo-ys-search-results">
    
    <!-- Results will be loaded here via JavaScript -->
    <div id="yolo-ys-results-container">
        
        <!-- Initial state: No search performed -->
        <div class="yolo-ys-no-results" id="yolo-ys-initial-state">
            <h3><?php _e('Search for Yachts', 'yolo-yacht-search'); ?></h3>
            <p><?php _e('Use the search form to find available yachts for your charter.', 'yolo-yacht-search'); ?></p>
        </div>
        
    </div>
    
</div>

<style>
.yolo-ys-search-results {
    padding: 40px 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.yolo-ys-results-header {
    margin-bottom: 30px;
    text-align: center;
}

.yolo-ys-results-header h2 {
    font-size: 32px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 10px;
}

.yolo-ys-results-count {
    font-size: 18px;
    color: #6b7280;
}

.yolo-ys-section-header {
    margin: 40px 0 20px 0;
    padding-bottom: 12px;
    border-bottom: 3px solid #b91c1c;
}

.yolo-ys-section-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.yolo-ys-section-header.friends {
    border-bottom-color: #1e3a8a;
}

.yolo-ys-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.yolo-ys-no-results {
    text-align: center;
    padding: 60px 20px;
    background: #f9fafb;
    border-radius: 8px;
}

.yolo-ys-no-results h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 12px;
}

.yolo-ys-no-results p {
    font-size: 16px;
    color: #6b7280;
}

.yolo-ys-loading {
    text-align: center;
    padding: 60px 20px;
}

.yolo-ys-loading-spinner {
    width: 50px;
    height: 50px;
    border: 4px solid #e5e7eb;
    border-top-color: #b91c1c;
    border-radius: 50%;
    animation: yolo-spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes yolo-spin {
    to { transform: rotate(360deg); }
}

.yolo-ys-loading p {
    font-size: 16px;
    color: #6b7280;
}

@media (max-width: 768px) {
    .yolo-ys-results-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .yolo-ys-results-header h2 {
        font-size: 24px;
    }
}
</style>
