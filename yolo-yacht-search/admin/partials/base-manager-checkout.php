<?php
/**
 * Base Manager - Check-Out Admin Page (Mobile-First)
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.11.2
 */

if (!defined('ABSPATH')) {
    exit;
}

// Permission check
if (!current_user_can('edit_posts')) {
    wp_die(__('Sorry, you are not allowed to access this page.', 'yolo-yacht-search'));
}
?>

<div class="wrap yolo-base-manager-page yolo-bm-checkout-page">
    <!-- Welcome Header -->
    <div class="yolo-bm-welcome-card yolo-bm-checkout-header">
        <div class="yolo-bm-welcome-content">
            <h1><i class="dashicons dashicons-upload"></i> Check-Out Management</h1>
            <p>Create check-out documents with equipment verification and signatures</p>
        </div>
        <button class="button button-primary button-hero" id="new-checkout-btn">
            <span class="dashicons dashicons-plus-alt"></span> New Check-Out
        </button>
    </div>
    
    <!-- Check-Out Form (Hidden by default) -->
    <div id="checkout-form-container" class="yolo-bm-form-container" style="display: none;">
        <div class="yolo-bm-form-card">
            <div class="yolo-bm-form-header yolo-bm-checkout-form-header">
                <h2><i class="dashicons dashicons-clipboard"></i> New Check-Out Document</h2>
                <button type="button" class="yolo-bm-close-btn" id="cancel-checkout-btn">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <form id="checkout-form">
                <!-- Booking & Yacht Selection -->
                <div class="yolo-bm-form-section">
                    <h3>Booking Information</h3>
                    <div class="yolo-bm-form-row">
                        <div class="yolo-bm-form-group">
                            <label for="checkout-booking-select">Select Booking *</label>
                            <select id="checkout-booking-select" class="yolo-bm-select" required>
                                <option value="">Choose booking...</option>
                            </select>
                        </div>
                    </div>
                    <div class="yolo-bm-form-row">
                        <div class="yolo-bm-form-group">
                            <label for="checkout-yacht-select">Select Yacht *</label>
                            <select id="checkout-yacht-select" class="yolo-bm-select" required>
                                <option value="">Choose yacht...</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Equipment Checklist (Loaded dynamically) -->
                <div id="equipment-checklist-section" class="yolo-bm-form-section" style="display: none;">
                    <h3><i class="dashicons dashicons-admin-tools"></i> Equipment Verification</h3>
                    <p class="yolo-bm-section-description">Check all equipment items to verify they are returned and in good condition</p>
                    <div id="equipment-checklist-container" class="yolo-bm-equipment-checklist"></div>
                </div>
                
                <!-- Photo Documentation Section -->
                <div id="photo-documentation-section" class="yolo-bm-form-section">
                    <h3><i class="dashicons dashicons-camera"></i> Photo Documentation</h3>
                    <p class="yolo-bm-section-description">Take photos of the yacht condition at return. Tap a category to add photos.</p>
                    
                    <div class="yolo-bm-photo-categories">
                        <!-- Exterior -->
                        <div class="yolo-bm-photo-category" data-category="exterior">
                            <div class="yolo-bm-category-header-photo">
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                                <span>Exterior</span>
                                <span class="yolo-bm-photo-count" data-category="exterior">0</span>
                            </div>
                            <p class="yolo-bm-category-hint">Hull, deck, cockpit, rails</p>
                            <div class="yolo-bm-photo-grid" data-category="exterior"></div>
                            <div class="yolo-bm-photo-upload-zone" data-category="exterior">
                                <input type="file" class="yolo-bm-photo-input" data-category="exterior" accept="image/*" multiple capture="environment">
                                <div class="yolo-bm-upload-placeholder">
                                    <span class="dashicons dashicons-camera"></span>
                                    <span>Tap to add photos or drag & drop</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Interior -->
                        <div class="yolo-bm-photo-category" data-category="interior">
                            <div class="yolo-bm-category-header-photo">
                                <span class="dashicons dashicons-admin-home"></span>
                                <span>Interior</span>
                                <span class="yolo-bm-photo-count" data-category="interior">0</span>
                            </div>
                            <p class="yolo-bm-category-hint">Cabin, galley, heads, saloon</p>
                            <div class="yolo-bm-photo-grid" data-category="interior"></div>
                            <div class="yolo-bm-photo-upload-zone" data-category="interior">
                                <input type="file" class="yolo-bm-photo-input" data-category="interior" accept="image/*" multiple capture="environment">
                                <div class="yolo-bm-upload-placeholder">
                                    <span class="dashicons dashicons-camera"></span>
                                    <span>Tap to add photos or drag & drop</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Equipment -->
                        <div class="yolo-bm-photo-category" data-category="equipment">
                            <div class="yolo-bm-category-header-photo">
                                <span class="dashicons dashicons-admin-tools"></span>
                                <span>Equipment</span>
                                <span class="yolo-bm-photo-count" data-category="equipment">0</span>
                            </div>
                            <p class="yolo-bm-category-hint">Sails, engine, electronics, safety gear</p>
                            <div class="yolo-bm-photo-grid" data-category="equipment"></div>
                            <div class="yolo-bm-photo-upload-zone" data-category="equipment">
                                <input type="file" class="yolo-bm-photo-input" data-category="equipment" accept="image/*" multiple capture="environment">
                                <div class="yolo-bm-upload-placeholder">
                                    <span class="dashicons dashicons-camera"></span>
                                    <span>Tap to add photos or drag & drop</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- New Damage -->
                        <div class="yolo-bm-photo-category" data-category="damage">
                            <div class="yolo-bm-category-header-photo yolo-bm-damage-header">
                                <span class="dashicons dashicons-warning"></span>
                                <span>New Damage</span>
                                <span class="yolo-bm-photo-count" data-category="damage">0</span>
                            </div>
                            <p class="yolo-bm-category-hint">Document any new damage found</p>
                            <div class="yolo-bm-photo-grid" data-category="damage"></div>
                            <div class="yolo-bm-photo-upload-zone" data-category="damage">
                                <input type="file" class="yolo-bm-photo-input" data-category="damage" accept="image/*" multiple capture="environment">
                                <div class="yolo-bm-upload-placeholder">
                                    <span class="dashicons dashicons-camera"></span>
                                    <span>Tap to add photos or drag & drop</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Fuel/Engine Readings -->
                        <div class="yolo-bm-photo-category" data-category="readings">
                            <div class="yolo-bm-category-header-photo">
                                <span class="dashicons dashicons-dashboard"></span>
                                <span>Fuel & Engine</span>
                                <span class="yolo-bm-photo-count" data-category="readings">0</span>
                            </div>
                            <p class="yolo-bm-category-hint">Fuel gauge, engine hours, water tank</p>
                            <div class="yolo-bm-photo-grid" data-category="readings"></div>
                            <div class="yolo-bm-photo-upload-zone" data-category="readings">
                                <input type="file" class="yolo-bm-photo-input" data-category="readings" accept="image/*" multiple capture="environment">
                                <div class="yolo-bm-upload-placeholder">
                                    <span class="dashicons dashicons-camera"></span>
                                    <span>Tap to add photos or drag & drop</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="yolo-bm-photo-summary">
                        <span class="dashicons dashicons-images-alt2"></span>
                        <span>Total Photos: <strong id="total-photo-count">0</strong> / 20</span>
                    </div>
                </div>
                
                <!-- Signature Section -->
                <div class="yolo-bm-form-section">
                    <h3><i class="dashicons dashicons-edit"></i> Base Manager Signature</h3>
                    <p class="yolo-bm-section-description">Sign below to confirm check-out completion</p>
                    <div class="yolo-bm-signature-container">
                        <canvas id="checkout-signature-pad" class="yolo-bm-signature-canvas"></canvas>
                        <button type="button" class="button yolo-bm-clear-signature-btn" id="clear-checkout-signature">
                            <span class="dashicons dashicons-image-rotate"></span> Clear Signature
                        </button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="yolo-bm-form-actions yolo-bm-sticky-actions">
                    <button type="button" class="button button-primary button-large yolo-bm-btn-success" id="complete-checkout-btn">
                        <span class="dashicons dashicons-yes"></span> Complete Check-Out
                    </button>
                    <button type="button" class="button button-large yolo-bm-btn-secondary" id="save-checkout-pdf-btn">
                        <span class="dashicons dashicons-pdf"></span> Save PDF
                    </button>
                    <button type="button" class="button button-large yolo-bm-btn-secondary" id="send-checkout-guest-btn">
                        <span class="dashicons dashicons-email"></span> Send to Guest
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Previous Check-Outs List -->
    <div id="checkout-list-container" class="yolo-bm-list-container">
        <h2><i class="dashicons dashicons-list-view"></i> Previous Check-Outs</h2>
        <div id="checkout-list" class="yolo-bm-list">
            <div class="yolo-bm-loading">
                <span class="spinner is-active"></span>
                <p>Loading check-outs...</p>
            </div>
        </div>
    </div>
</div>

<!-- Photo Annotation Modal -->
<div id="annotation-modal" class="yolo-bm-modal" style="display: none;">
    <div class="yolo-bm-modal-content yolo-bm-annotation-modal">
        <button type="button" class="yolo-bm-modal-close">&times;</button>
        <h3><i class="dashicons dashicons-edit"></i> Photo Details</h3>
        <div class="yolo-bm-annotation-body">
            <div class="yolo-bm-annotation-preview">
                <img id="annotation-photo-img" src="" alt="Photo preview">
            </div>
            <div class="yolo-bm-annotation-form">
                <div class="yolo-bm-form-group">
                    <label for="annotation-caption">Caption</label>
                    <input type="text" id="annotation-caption" placeholder="Brief description (e.g., Starboard hull)" maxlength="100">
                </div>
                <div class="yolo-bm-form-group">
                    <label for="annotation-notes">Notes</label>
                    <textarea id="annotation-notes" rows="3" placeholder="Additional notes about this photo..."></textarea>
                </div>
            </div>
        </div>
        <div class="yolo-bm-modal-actions">
            <button type="button" class="button" id="cancel-annotation-btn">Cancel</button>
            <button type="button" class="button button-primary" id="save-annotation-btn">Save</button>
        </div>
    </div>
</div>

<style>
/* Mobile-First Check-Out Styles - COMPLETE CSS v17.12.1 */
.yolo-bm-checkout-page {
    background: #f5f7fa;
    margin: -20px -20px 0 -22px;
    padding: 20px;
    min-height: 100vh;
}

/* Form Container */
.yolo-bm-form-container {
    margin-bottom: 30px;
}

.yolo-bm-form-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.yolo-bm-form-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.yolo-bm-form-header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.yolo-bm-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.yolo-bm-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.yolo-bm-close-btn .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}

/* Form Sections */
.yolo-bm-form-section {
    padding: 24px;
    border-bottom: 2px solid #f3f4f6;
}

.yolo-bm-form-section:last-child {
    border-bottom: none;
}

.yolo-bm-form-section h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}

.yolo-bm-section-description {
    margin: 0 0 20px 0;
    color: #6b7280;
    font-size: 14px;
}

/* Select Inputs - Mobile Optimized */
.yolo-bm-select {
    width: 100%;
    padding: 16px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 16px;
    background: white;
    transition: all 0.2s ease;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"%3e%3cpolyline points="6 9 12 15 18 9"%3e%3c/polyline%3e%3c/svg%3e');
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 20px;
    padding-right: 44px;
}

.yolo-bm-select:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

/* Equipment Checklist */
.yolo-bm-equipment-checklist {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.yolo-bm-equipment-category {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.yolo-bm-category-header {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    padding: 16px 20px;
    font-weight: 600;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.yolo-bm-category-header .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
}

.yolo-bm-category-items {
    padding: 12px;
}

.yolo-bm-equipment-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    padding: 16px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: all 0.2s ease;
    min-height: 60px;
}

.yolo-bm-equipment-item:last-child {
    margin-bottom: 0;
}

.yolo-bm-equipment-item.checked {
    background: #fffbeb;
    border-color: #f59e0b;
}

.yolo-bm-equipment-checkbox {
    width: 32px;
    height: 32px;
    min-width: 32px;
    border: 3px solid #d1d5db;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    background: white;
}

.yolo-bm-equipment-checkbox.checked {
    background: #f59e0b;
    border-color: #f59e0b;
}

.yolo-bm-equipment-checkbox.checked::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-size: 20px;
    font-weight: bold;
}

.yolo-bm-equipment-label {
    flex: 1;
    font-size: 15px;
    color: #374151;
    font-weight: 500;
}

.yolo-bm-equipment-note-btn {
    background: #f3f4f6;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.yolo-bm-equipment-note-btn:hover,
.yolo-bm-equipment-note-btn.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.yolo-bm-equipment-note-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.yolo-bm-equipment-note-btn.has-note {
    background: #fef3c7;
    border-color: #f59e0b;
    color: #92400e;
}

.yolo-bm-equipment-note-field {
    padding: 0 16px 16px 64px;
}

.yolo-bm-equipment-note {
    width: 100%;
    min-height: 60px;
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    resize: vertical;
    font-family: inherit;
}

.yolo-bm-equipment-note:focus {
    outline: none;
    border-color: #f59e0b;
    box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
}

/* Signature Pad - Mobile Optimized - CRITICAL FIX! */
.yolo-bm-signature-container {
    background: #f9fafb;
    border: 3px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px;
}

.yolo-bm-signature-canvas {
    width: 100%;
    height: 250px;
    background: white;
    border: 3px solid #d1d5db;
    border-radius: 12px;
    cursor: crosshair;
    touch-action: none;
    display: block;
}

.yolo-bm-clear-signature-btn {
    margin-top: 16px;
    width: 100%;
    padding: 14px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* Action Buttons - Mobile Optimized */
.yolo-bm-form-actions {
    padding: 24px;
    background: #f9fafb;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.yolo-bm-sticky-actions {
    position: sticky;
    bottom: 0;
    z-index: 10;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.1);
}

.yolo-bm-form-actions .button {
    width: 100%;
    padding: 16px 24px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    min-height: 56px;
    transition: all 0.2s ease;
}

.yolo-bm-btn-success {
    background: #f59e0b;
    border-color: #f59e0b;
    color: white;
}

.yolo-bm-btn-success:hover {
    background: #d97706;
    border-color: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

.yolo-bm-btn-secondary {
    background: white;
    border: 2px solid #e5e7eb;
    color: #374151;
}

.yolo-bm-btn-secondary:hover {
    background: #f9fafb;
    border-color: #d1d5db;
}

/* Previous Check-Outs List */
.yolo-bm-list-container {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.yolo-bm-list-container h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 10px;
}

.yolo-bm-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.yolo-bm-list-item {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s ease;
}

.yolo-bm-list-item:hover {
    border-color: #f59e0b;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.1);
}

.yolo-bm-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

.yolo-bm-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    color: #d1d5db;
    margin-bottom: 16px;
}

/* Welcome Header */
.yolo-bm-welcome-card {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    border-radius: 16px;
    padding: 24px;
    color: white;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}

.yolo-bm-welcome-content h1 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.yolo-bm-welcome-content p {
    margin: 0;
    opacity: 0.9;
}

/* Responsive - Desktop */
@media (min-width: 768px) {
    .yolo-bm-checkout-page {
        padding: 30px;
    }
    
    .yolo-bm-form-actions {
        flex-direction: row;
    }
    
    .yolo-bm-form-actions .button {
        flex: 1;
    }
    
    .yolo-bm-equipment-checklist {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }
}

/* Photo Documentation Styles */
.yolo-bm-photo-categories {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.yolo-bm-photo-category {
    background: #f8f9fa;
    border: 1px solid #e1e5eb;
    border-radius: 8px;
    padding: 15px;
}

.yolo-bm-category-header-photo {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #1e3a5f;
    margin-bottom: 5px;
}

.yolo-bm-category-header-photo .dashicons {
    color: #f59e0b;
}

.yolo-bm-damage-header .dashicons {
    color: #ef4444 !important;
}

.yolo-bm-photo-count {
    background: #f59e0b;
    color: white;
    font-size: 11px;
    padding: 2px 8px;
    border-radius: 10px;
    margin-left: auto;
}

.yolo-bm-category-hint {
    font-size: 12px;
    color: #6b7280;
    margin: 0 0 10px 0;
}

.yolo-bm-photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 8px;
    margin-bottom: 10px;
}

.yolo-bm-photo-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 6px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.2s;
}

.yolo-bm-photo-item:hover {
    border-color: #f59e0b;
}

.yolo-bm-photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.yolo-bm-photo-item.uploading {
    opacity: 0.6;
}

.yolo-bm-photo-uploading {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

.yolo-bm-photo-delete {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: rgba(239, 68, 68, 0.9);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.2s;
}

.yolo-bm-photo-item:hover .yolo-bm-photo-delete {
    opacity: 1;
}

.yolo-bm-photo-delete .dashicons {
    font-size: 14px;
    width: 14px;
    height: 14px;
}

.yolo-bm-photo-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0,0,0,0.7);
    color: white;
    font-size: 10px;
    padding: 3px 5px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.yolo-bm-photo-upload-zone {
    position: relative;
    border: 2px dashed #d1d5db;
    border-radius: 6px;
    padding: 15px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s;
}

.yolo-bm-photo-upload-zone:hover,
.yolo-bm-photo-upload-zone.drag-over {
    border-color: #f59e0b;
    background: #fffbeb;
}

.yolo-bm-photo-upload-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.yolo-bm-upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    color: #6b7280;
    font-size: 13px;
}

.yolo-bm-upload-placeholder .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
    color: #9ca3af;
}

.yolo-bm-photo-summary {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 15px;
    padding: 10px 15px;
    background: #fef3c7;
    border-radius: 6px;
    color: #92400e;
    font-size: 14px;
}

/* Annotation Modal */
.yolo-bm-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100000;
    padding: 20px;
}

.yolo-bm-modal-content {
    background: white;
    border-radius: 12px;
    width: 100%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

.yolo-bm-modal-content h3 {
    margin: 0;
    padding: 15px 20px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 8px;
}

.yolo-bm-modal-close {
    position: absolute;
    top: 10px;
    right: 15px;
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #6b7280;
}

.yolo-bm-annotation-body {
    padding: 20px;
}

.yolo-bm-annotation-preview {
    margin-bottom: 15px;
    border-radius: 8px;
    overflow: hidden;
}

.yolo-bm-annotation-preview img {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    background: #f3f4f6;
}

.yolo-bm-annotation-form .yolo-bm-form-group {
    margin-bottom: 15px;
}

.yolo-bm-annotation-form label {
    display: block;
    font-weight: 500;
    margin-bottom: 5px;
    color: #374151;
}

.yolo-bm-annotation-form input,
.yolo-bm-annotation-form textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.yolo-bm-modal-actions {
    padding: 15px 20px;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* Mobile adjustments for photos */
@media (max-width: 480px) {
    .yolo-bm-photo-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .yolo-bm-photo-delete {
        opacity: 1;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
jQuery(document).ready(function($) {
    console.log('=== YOLO Base Manager Check-Out v41.4 Initializing ===');
    
    // Verify ajaxurl is defined
    if (typeof ajaxurl === 'undefined') {
        console.error('CRITICAL: ajaxurl is undefined! WordPress admin AJAX will not work.');
        alert('Configuration error: AJAX URL not found. Please refresh the page.');
        return;
    }
    console.log('Check-Out: ajaxurl verified:', ajaxurl);
    
    let checkoutSignaturePad = null;
    let selectedYachtId = null;
    let currentCheckoutId = null; // Store the check-out ID after completion
    
    // Load initial data
    console.log('Check-Out: Starting initial data load...');
    loadYachts();
    loadBookings();
    loadCheckouts();
    
    // New check-out button
    $('#new-checkout-btn').on('click', function() {
        $('#checkout-form-container').slideDown(function() {
            initializeSignaturePad();
        });
        $('#checkout-list-container').hide();
    });
    
    // Cancel check-out
    $('#cancel-checkout-btn').on('click', function() {
        $('#checkout-form-container').slideUp();
        $('#checkout-list-container').show();
        $('#checkout-form')[0].reset();
        $('#equipment-checklist-section').hide();
        currentCheckoutId = null; // Reset the check-out ID
        if (checkoutSignaturePad) {
            checkoutSignaturePad.clear();
        }
    });
    
    // Yacht selection - load equipment
    $('#checkout-yacht-select').on('change', function() {
        selectedYachtId = $(this).val();
        if (selectedYachtId) {
            loadEquipmentChecklist(selectedYachtId);
        } else {
            $('#equipment-checklist-section').hide();
        }
    });
    
    // Clear signature
    $('#clear-checkout-signature').on('click', function() {
        if (checkoutSignaturePad) {
            checkoutSignaturePad.clear();
        }
    });
    
    // Complete check-out
    $('#complete-checkout-btn').on('click', function() {
        const bookingId = $('#checkout-booking-select').val();
        const yachtId = $('#checkout-yacht-select').val();
        
        if (!bookingId || !yachtId) {
            alert('Please select both booking and yacht');
            return;
        }
        
        if (!checkoutSignaturePad || checkoutSignaturePad.isEmpty()) {
            alert('Please provide your signature');
            return;
        }
        
        // Collect equipment checklist data including notes
        const equipmentData = [];
        $('.yolo-bm-equipment-checkbox').each(function() {
            const categoryName = $(this).closest('.yolo-bm-equipment-category').find('.yolo-bm-category-header').text().trim();
            const itemName = $(this).siblings('.yolo-bm-equipment-label').text();
            const isChecked = $(this).hasClass('checked');
            const safeItemName = $(this).data('item').replace(/[^a-zA-Z0-9]/g, '_');
            const itemNote = $('.yolo-bm-equipment-note[data-item="' + safeItemName + '"]').val() || '';
            
            equipmentData.push({
                category: categoryName,
                item: itemName,
                checked: isChecked,
                note: itemNote
            });
        });
        
        const formData = {
            action: 'yolo_bm_save_checkout',
            nonce: yoloBaseManager.nonce,
            booking_id: bookingId,
            yacht_id: yachtId,
            checklist_data: JSON.stringify(equipmentData),
            signature: checkoutSignaturePad.toDataURL(),
            photo_ids: JSON.stringify(getPhotoIds()),
            status: 'completed'
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    currentCheckoutId = response.data.checkout_id;
                    alert('Check-out completed successfully! You can now Save PDF or Send to Guest.');
                    // Don't close form - user may want to save PDF or send to guest
                    $('#save-checkout-pdf-btn, #send-checkout-guest-btn').css('opacity', '1').prop('disabled', false);
                    loadCheckouts();
                } else {
                    alert('Error: ' + (response.data || 'Failed to complete check-out'));
                }
            },
            error: function() {
                alert('Failed to complete check-out. Please try again.');
            }
        });
    });
    
    // Save PDF button
    $('#save-checkout-pdf-btn').on('click', function() {
        if (!currentCheckoutId) {
            alert('Please complete the check-out first before saving PDF.');
            return;
        }
        
        $(this).prop('disabled', true).text('Generating PDF...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_generate_pdf',
                nonce: yoloBaseManager.nonce,
                type: 'checkout',
                record_id: currentCheckoutId
            },
            success: function(response) {
                $('#save-checkout-pdf-btn').prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> Save PDF');
                if (response.success && response.data.pdf_url) {
                    window.open(response.data.pdf_url, '_blank');
                } else {
                    alert('Error: ' + (response.data?.message || 'Failed to generate PDF'));
                }
            },
            error: function() {
                $('#save-checkout-pdf-btn').prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> Save PDF');
                alert('Failed to generate PDF. Please try again.');
            }
        });
    });
    
    // Send to Guest button
    $('#send-checkout-guest-btn').on('click', function() {
        if (!currentCheckoutId) {
            alert('Please complete the check-out first before sending to guest.');
            return;
        }
        
        const bookingId = $('#checkout-booking-select').val();
        if (!bookingId) {
            alert('No booking selected.');
            return;
        }
        
        $(this).prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_send_to_guest',
                nonce: yoloBaseManager.nonce,
                type: 'checkout',
                record_id: currentCheckoutId,
                booking_id: bookingId
            },
            success: function(response) {
                $('#send-checkout-guest-btn').prop('disabled', false).html('<span class="dashicons dashicons-email"></span> Send to Guest');
                if (response.success) {
                    alert('Document sent to guest successfully!');
                } else {
                    alert('Error: ' + (response.data?.message || 'Failed to send to guest'));
                }
            },
            error: function() {
                $('#send-checkout-guest-btn').prop('disabled', false).html('<span class="dashicons dashicons-email"></span> Send to Guest');
                alert('Failed to send to guest. Please try again.');
            }
        });
    });
    
    // Initialize signature pad
    function initializeSignaturePad() {
        const canvas = document.getElementById('checkout-signature-pad');
        if (canvas) {
            if (checkoutSignaturePad) {
                checkoutSignaturePad.clear();
            }
            checkoutSignaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 2,
                maxWidth: 4
            });
            resizeCanvas(canvas);
        }
    }
    
    function resizeCanvas(canvas) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        if (checkoutSignaturePad) {
            checkoutSignaturePad.clear();
        }
    }
    
    // Load yachts
    function loadYachts() {
        console.log('Check-Out: Loading yachts...');
        console.log('Check-Out: AJAX URL:', ajaxurl);
        console.log('Check-Out: Action:', 'yolo_bm_get_yachts');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                console.log('Check-Out: Yachts AJAX SUCCESS');
                console.log('Check-Out: Full response object:', response);
                console.log('Check-Out: response.success:', response.success);
                console.log('Check-Out: response.data:', response.data);
                
                if (response.success && response.data) {
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        let options = '<option value="">Choose yacht...</option>';
                        response.data.forEach(function(yacht) {
                            console.log('Check-Out: Processing yacht:', yacht);
                            options += `<option value="${yacht.id}">${yacht.yacht_name}${yacht.yacht_model ? ' - ' + yacht.yacht_model : ''}</option>`;
                        });
                        $('#checkout-yacht-select').html(options);
                        console.log('Check-Out: Successfully loaded ' + response.data.length + ' yachts into dropdown');
                    } else {
                        console.warn('Check-Out: No yachts found in response.data');
                        $('#checkout-yacht-select').html('<option value="">No yachts available</option>');
                    }
                } else {
                    console.error('Check-Out: Response indicates failure');
                    console.error('Check-Out: response.success:', response.success);
                    console.error('Check-Out: response.data:', response.data);
                    if (response.data && response.data.message) {
                        alert('Error loading yachts: ' + response.data.message);
                    }
                    $('#checkout-yacht-select').html('<option value="">Error loading yachts</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Check-Out: AJAX ERROR loading yachts');
                console.error('Check-Out: Status:', status);
                console.error('Check-Out: Error:', error);
                console.error('Check-Out: XHR object:', xhr);
                console.error('Check-Out: Response text:', xhr.responseText);
                alert('Network error loading yachts. Check console for details.');
                $('#checkout-yacht-select').html('<option value="">Network error</option>');
            }
        });
    }
    
    // Load bookings
    function loadBookings() {
        console.log('Check-Out: Loading bookings...');
        console.log('Check-Out: AJAX URL:', ajaxurl);
        console.log('Check-Out: Action:', 'yolo_bm_get_bookings_calendar');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_bookings_calendar',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                console.log('Check-Out: Bookings AJAX SUCCESS');
                console.log('Check-Out: Full response object:', response);
                console.log('Check-Out: response.success:', response.success);
                console.log('Check-Out: response.data:', response.data);
                
                if (response.success && response.data) {
                    if (Array.isArray(response.data) && response.data.length > 0) {
                        let options = '<option value="">Choose booking...</option>';
                        response.data.forEach(function(booking) {
                            console.log('Check-Out: Processing booking:', booking);
                            options += `<option value="${booking.id}">${booking.id} - ${booking.customer_name}${booking.yacht_name ? ' (' + booking.yacht_name + ')' : ''}</option>`;
                        });
                        $('#checkout-booking-select').html(options);
                        console.log('Check-Out: Successfully loaded ' + response.data.length + ' bookings into dropdown');
                    } else {
                        console.warn('Check-Out: No bookings found in response.data');
                        $('#checkout-booking-select').html('<option value="">No bookings available</option>');
                    }
                } else {
                    console.error('Check-Out: Response indicates failure');
                    console.error('Check-Out: response.success:', response.success);
                    console.error('Check-Out: response.data:', response.data);
                    if (response.data && response.data.message) {
                        alert('Error loading bookings: ' + response.data.message);
                    }
                    $('#checkout-booking-select').html('<option value="">Error loading bookings</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Check-Out: AJAX ERROR loading bookings');
                console.error('Check-Out: Status:', status);
                console.error('Check-Out: Error:', error);
                console.error('Check-Out: XHR object:', xhr);
                console.error('Check-Out: Response text:', xhr.responseText);
                alert('Network error loading bookings. Check console for details.');
                $('#checkout-booking-select').html('<option value="">Network error</option>');
            }
        });
    }
    
    // Load equipment checklist
    function loadEquipmentChecklist(yachtId) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_equipment_categories',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    displayEquipmentChecklist(response.data);
                    $('#equipment-checklist-section').slideDown();
                } else {
                    $('#equipment-checklist-section').hide();
                }
            }
        });
    }
    
    // Display equipment checklist
    function displayEquipmentChecklist(categories) {
        let html = '';
        
        categories.forEach(function(category) {
            const items = category.items ? JSON.parse(category.items) : [];
            
            if (items.length > 0) {
                html += `
                    <div class="yolo-bm-equipment-category">
                        <div class="yolo-bm-category-header">
                            <span class="dashicons dashicons-category"></span>
                            ${category.category_name}
                        </div>
                        <div class="yolo-bm-category-items">
                `;
                
                items.forEach(function(item) {
                    // Support both old format (string) and new format (object with name and quantity)
                    const itemName = typeof item === 'string' ? item : item.name;
                    const itemQuantity = typeof item === 'string' ? '' : (item.quantity || '');
                    const itemLabel = itemQuantity ? `${itemName} (${itemQuantity})` : itemName;
                    const safeItemName = itemName.replace(/[^a-zA-Z0-9]/g, '_');
                    
                    html += `
                        <div class="yolo-bm-equipment-item">
                            <div class="yolo-bm-equipment-checkbox" data-item="${itemName}"></div>
                            <div class="yolo-bm-equipment-label">${itemLabel}</div>
                            <button type="button" class="yolo-bm-equipment-note-btn" data-item="${safeItemName}" title="Add note">
                                <span class="dashicons dashicons-edit"></span>
                            </button>
                        </div>
                        <div class="yolo-bm-equipment-note-field" data-item="${safeItemName}" style="display: none;">
                            <textarea class="yolo-bm-equipment-note" data-item="${safeItemName}" placeholder="Add notes about this item (e.g., condition, missing, damaged)..."></textarea>
                        </div>
                    `;
                });
                
                html += `
                        </div>
                    </div>
                `;
            }
        });
        
        $('#equipment-checklist-container').html(html);
        
        // Equipment checkbox click
        $('.yolo-bm-equipment-checkbox').on('click', function() {
            $(this).toggleClass('checked');
            $(this).closest('.yolo-bm-equipment-item').toggleClass('checked');
        });
        
        // Equipment note button click
        $('.yolo-bm-equipment-note-btn').on('click', function() {
            var itemName = $(this).data('item');
            var $noteField = $('.yolo-bm-equipment-note-field[data-item="' + itemName + '"]');
            $noteField.slideToggle(200);
            $(this).toggleClass('active');
        });
    }
    
    // Load check-outs
    function loadCheckouts() {
        console.log('Check-Out: Loading check-outs list...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_checkouts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    console.log('Check-Out: Loaded ' + response.data.length + ' check-outs');
                    let html = '<div class="yolo-table-responsive-wrapper"><table class="yolo-bm-table"><thead><tr>';
                    html += '<th>ID</th><th>Booking</th><th>Yacht</th><th>Date</th><th>Status</th><th>Actions</th>';
                    html += '</tr></thead><tbody>';
                    
                    response.data.forEach(function(checkout) {
                        const yachtName = checkout.managed_yacht_name || checkout.booking_yacht_name || 'N/A';
                        const customerName = checkout.customer_name || 'N/A';
                        const status = checkout.guest_signature ? 'Signed' : 'Pending';
                        const statusClass = checkout.guest_signature ? 'status-signed' : 'status-pending';
                        const createdDate = new Date(checkout.created_at).toLocaleDateString();
                        
                        html += '<tr>';
                        html += '<td>#' + checkout.id + '</td>';
                        html += '<td>' + customerName + '</td>';
                        html += '<td>' + yachtName + '</td>';
                        html += '<td>' + createdDate + '</td>';
                        html += '<td><span class="yolo-bm-status ' + statusClass + '">' + status + '</span></td>';
                        html += '<td>';
                        if (checkout.pdf_url) {
                            html += '<a href="' + checkout.pdf_url + '" target="_blank" class="yolo-bm-btn-icon" title="View PDF"><span class="dashicons dashicons-pdf"></span></a> ';
                        }
                        html += '</td>';
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    $('#checkout-list').html(html);
                } else {
                    console.log('Check-Out: No check-outs found');
                    $('#checkout-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-clipboard"></span><p>No check-outs yet. Click "New Check-Out" to create one.</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Check-Out: Failed to load check-outs:', error);
                $('#checkout-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-warning"></span><p>Failed to load check-outs. Please refresh the page.</p></div>');
            }
        });
    }
    
    // Photo Documentation Variables
    var uploadedPhotos = {
        exterior: [],
        interior: [],
        equipment: [],
        damage: [],
        readings: []
    };
    var maxPhotos = 20;
    
    // Initialize photo upload handlers
    function initPhotoUpload() {
        // File input change handler
        $('.yolo-bm-photo-input').on('change', function(e) {
            var category = $(this).data('category');
            handlePhotoFiles(e.target.files, category);
            $(this).val(''); // Reset input
        });
        
        // Drag and drop handlers
        $('.yolo-bm-photo-upload-zone').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('drag-over');
        }).on('dragleave drop', function(e) {
            e.preventDefault();
            $(this).removeClass('drag-over');
        }).on('drop', function(e) {
            var category = $(this).data('category');
            var files = e.originalEvent.dataTransfer.files;
            handlePhotoFiles(files, category);
        });
        
        // Delete photo handler
        $(document).on('click', '.yolo-bm-photo-delete', function(e) {
            e.stopPropagation();
            var $item = $(this).closest('.yolo-bm-photo-item');
            var category = $item.data('category');
            var index = $item.data('index');
            deletePhoto(category, index);
        });
        
        // Photo click for annotation
        $(document).on('click', '.yolo-bm-photo-item', function(e) {
            if (!$(e.target).hasClass('yolo-bm-photo-delete') && !$(e.target).closest('.yolo-bm-photo-delete').length) {
                var category = $(this).data('category');
                var index = $(this).data('index');
                openPhotoAnnotation(category, index);
            }
        });
    }
    
    // Handle photo files
    function handlePhotoFiles(files, category) {
        var totalPhotos = getTotalPhotoCount();
        
        for (var i = 0; i < files.length; i++) {
            if (totalPhotos >= maxPhotos) {
                alert('Maximum ' + maxPhotos + ' photos allowed.');
                break;
            }
            
            var file = files[i];
            if (!file.type.startsWith('image/')) {
                continue;
            }
            
            // Compress and upload
            compressAndUploadPhoto(file, category);
            totalPhotos++;
        }
    }
    
    // Compress image before upload
    function compressAndUploadPhoto(file, category) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = new Image();
            img.onload = function() {
                var canvas = document.createElement('canvas');
                var maxWidth = 1920;
                var maxHeight = 1080;
                var width = img.width;
                var height = img.height;
                
                if (width > maxWidth || height > maxHeight) {
                    var ratio = Math.min(maxWidth / width, maxHeight / height);
                    width *= ratio;
                    height *= ratio;
                }
                
                canvas.width = width;
                canvas.height = height;
                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                canvas.toBlob(function(blob) {
                    uploadPhoto(blob, file.name, category);
                }, 'image/jpeg', 0.8);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    
    // Upload photo to server
    function uploadPhoto(blob, filename, category) {
        var formData = new FormData();
        formData.append('action', 'yolo_bm_upload_photo');
        formData.append('nonce', yoloBaseManager.nonce);
        formData.append('photo', blob, filename);
        formData.append('category', category);
        formData.append('document_type', 'checkout');
        formData.append('document_id', currentCheckoutId || 0);
        
        // Show uploading state
        var tempId = 'temp_' + Date.now();
        addPhotoToGrid(category, {
            id: tempId,
            thumbnail_url: URL.createObjectURL(blob),
            caption: '',
            uploading: true
        });
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Remove temp photo
                removePhotoFromGrid(category, tempId);
                
                if (response.success) {
                    uploadedPhotos[category].push(response.data);
                    addPhotoToGrid(category, response.data);
                    updatePhotoCounts();
                } else {
                    alert('Failed to upload photo: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                removePhotoFromGrid(category, tempId);
                alert('Failed to upload photo. Please try again.');
            }
        });
    }
    
    // Add photo to grid
    function addPhotoToGrid(category, photo) {
        var index = uploadedPhotos[category].length - 1;
        if (photo.uploading) {
            index = photo.id;
        }
        
        var html = '<div class="yolo-bm-photo-item' + (photo.uploading ? ' uploading' : '') + '" data-category="' + category + '" data-index="' + index + '" data-id="' + photo.id + '">';
        html += '<img src="' + photo.thumbnail_url + '" alt="">';
        if (photo.uploading) {
            html += '<div class="yolo-bm-photo-uploading"><span class="spinner is-active"></span></div>';
        } else {
            html += '<button type="button" class="yolo-bm-photo-delete" title="Delete"><span class="dashicons dashicons-no-alt"></span></button>';
            if (photo.caption) {
                html += '<div class="yolo-bm-photo-caption">' + photo.caption + '</div>';
            }
        }
        html += '</div>';
        
        $('.yolo-bm-photo-grid[data-category="' + category + '"]').append(html);
    }
    
    // Remove photo from grid
    function removePhotoFromGrid(category, id) {
        $('.yolo-bm-photo-item[data-category="' + category + '"][data-id="' + id + '"]').remove();
    }
    
    // Delete photo
    function deletePhoto(category, index) {
        if (!confirm('Delete this photo?')) return;
        
        var photo = uploadedPhotos[category][index];
        if (!photo) return;
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_delete_photo',
                nonce: yoloBaseManager.nonce,
                photo_id: photo.id
            },
            success: function(response) {
                if (response.success) {
                    uploadedPhotos[category].splice(index, 1);
                    rebuildPhotoGrid(category);
                    updatePhotoCounts();
                }
            }
        });
    }
    
    // Rebuild photo grid
    function rebuildPhotoGrid(category) {
        var $grid = $('.yolo-bm-photo-grid[data-category="' + category + '"]');
        $grid.empty();
        uploadedPhotos[category].forEach(function(photo, index) {
            photo.index = index;
            var html = '<div class="yolo-bm-photo-item" data-category="' + category + '" data-index="' + index + '" data-id="' + photo.id + '">';
            html += '<img src="' + photo.thumbnail_url + '" alt="">';
            html += '<button type="button" class="yolo-bm-photo-delete" title="Delete"><span class="dashicons dashicons-no-alt"></span></button>';
            if (photo.caption) {
                html += '<div class="yolo-bm-photo-caption">' + photo.caption + '</div>';
            }
            html += '</div>';
            $grid.append(html);
        });
    }
    
    // Open photo annotation modal
    function openPhotoAnnotation(category, index) {
        var photo = uploadedPhotos[category][index];
        if (!photo) return;
        
        $('#annotation-photo-img').attr('src', photo.file_url || photo.thumbnail_url);
        $('#annotation-caption').val(photo.caption || '');
        $('#annotation-notes').val(photo.notes || '');
        $('#annotation-modal').data('category', category).data('index', index).show();
    }
    
    // Save photo annotation
    $(document).on('click', '#save-annotation-btn', function() {
        var category = $('#annotation-modal').data('category');
        var index = $('#annotation-modal').data('index');
        var photo = uploadedPhotos[category][index];
        
        var caption = $('#annotation-caption').val();
        var notes = $('#annotation-notes').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_update_photo',
                nonce: yoloBaseManager.nonce,
                photo_id: photo.id,
                caption: caption,
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    uploadedPhotos[category][index].caption = caption;
                    uploadedPhotos[category][index].notes = notes;
                    rebuildPhotoGrid(category);
                    $('#annotation-modal').hide();
                }
            }
        });
    });
    
    // Close annotation modal
    $(document).on('click', '#annotation-modal .yolo-bm-modal-close, #cancel-annotation-btn', function() {
        $('#annotation-modal').hide();
    });
    
    // Get total photo count
    function getTotalPhotoCount() {
        var total = 0;
        for (var cat in uploadedPhotos) {
            total += uploadedPhotos[cat].length;
        }
        return total;
    }
    
    // Update photo counts
    function updatePhotoCounts() {
        for (var cat in uploadedPhotos) {
            $('.yolo-bm-photo-count[data-category="' + cat + '"]').text(uploadedPhotos[cat].length);
        }
        $('#total-photo-count').text(getTotalPhotoCount());
    }
    
    // Get photo IDs for form submission
    function getPhotoIds() {
        var ids = [];
        for (var cat in uploadedPhotos) {
            uploadedPhotos[cat].forEach(function(photo) {
                ids.push(photo.id);
            });
        }
        return ids;
    }
    
    // Initialize photo upload on page load
    initPhotoUpload();
});
</script>
