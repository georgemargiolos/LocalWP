<?php
/**
 * Base Manager - Check-In Admin Page (Mobile-First)
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

<div class="wrap yolo-base-manager-page yolo-bm-checkin-page">
    <!-- Welcome Header -->
    <div class="yolo-bm-welcome-card">
        <div class="yolo-bm-welcome-content">
            <h1><i class="dashicons dashicons-yes-alt"></i> Check-In Management</h1>
            <p>Create check-in documents with equipment verification and signatures</p>
        </div>
        <button class="button button-primary button-hero" id="new-checkin-btn">
            <span class="dashicons dashicons-plus-alt"></span> New Check-In
        </button>
    </div>
    
    <!-- Check-In Form (Hidden by default) -->
    <div id="checkin-form-container" class="yolo-bm-form-container" style="display: none;">
        <div class="yolo-bm-form-card">
            <div class="yolo-bm-form-header">
                <h2><i class="dashicons dashicons-clipboard"></i> New Check-In Document</h2>
                <button type="button" class="yolo-bm-close-btn" id="cancel-checkin-btn">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <form id="checkin-form">
                <!-- Booking & Yacht Selection -->
                <div class="yolo-bm-form-section">
                    <h3>Booking Information</h3>
                    <div class="yolo-bm-form-row">
                        <div class="yolo-bm-form-group">
                            <label for="checkin-booking-select">Select Booking *</label>
                            <select id="checkin-booking-select" class="yolo-bm-select" required>
                                <option value="">Choose booking...</option>
                            </select>
                        </div>
                    </div>
                    <div class="yolo-bm-form-row">
                        <div class="yolo-bm-form-group">
                            <label for="checkin-yacht-select">Select Yacht *</label>
                            <select id="checkin-yacht-select" class="yolo-bm-select" required>
                                <option value="">Choose yacht...</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Equipment Checklist (Loaded dynamically) -->
                <div id="equipment-checklist-section" class="yolo-bm-form-section" style="display: none;">
                    <h3><i class="dashicons dashicons-admin-tools"></i> Equipment Verification</h3>
                    <p class="yolo-bm-section-description">Check all equipment items to verify they are present and in good condition</p>
                    <div id="equipment-checklist-container" class="yolo-bm-equipment-checklist"></div>
                </div>
                
                <!-- Signature Section -->
                <div class="yolo-bm-form-section">
                    <h3><i class="dashicons dashicons-edit"></i> Base Manager Signature</h3>
                    <p class="yolo-bm-section-description">Sign below to confirm check-in completion</p>
                    <div class="yolo-bm-signature-container">
                        <canvas id="checkin-signature-pad" class="yolo-bm-signature-canvas"></canvas>
                        <button type="button" class="button yolo-bm-clear-signature-btn" id="clear-checkin-signature">
                            <span class="dashicons dashicons-image-rotate"></span> Clear Signature
                        </button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="yolo-bm-form-actions yolo-bm-sticky-actions">
                    <button type="button" class="button button-primary button-large yolo-bm-btn-success" id="complete-checkin-btn">
                        <span class="dashicons dashicons-yes"></span> Complete Check-In
                    </button>
                    <button type="button" class="button button-large yolo-bm-btn-secondary" id="save-checkin-pdf-btn">
                        <span class="dashicons dashicons-pdf"></span> Save PDF
                    </button>
                    <button type="button" class="button button-large yolo-bm-btn-secondary" id="send-checkin-guest-btn">
                        <span class="dashicons dashicons-email"></span> Send to Guest
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Previous Check-Ins List -->
    <div id="checkin-list-container" class="yolo-bm-list-container">
        <h2><i class="dashicons dashicons-list-view"></i> Previous Check-Ins</h2>
        <div id="checkin-list" class="yolo-bm-list">
            <div class="yolo-bm-loading">
                <span class="spinner is-active"></span>
                <p>Loading check-ins...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Mobile-First Check-In Styles */
.yolo-bm-checkin-page {
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
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    min-height: 60px; /* Touch-friendly */
}

.yolo-bm-equipment-item:last-child {
    margin-bottom: 0;
}

.yolo-bm-equipment-item.checked {
    background: #ecfdf5;
    border-color: #10b981;
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
    background: #10b981;
    border-color: #10b981;
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

/* Signature Pad - Mobile Optimized */
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
    min-height: 56px; /* Touch-friendly */
    transition: all 0.2s ease;
}

.yolo-bm-btn-success {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.yolo-bm-btn-success:hover {
    background: #059669;
    border-color: #059669;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
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

/* Previous Check-Ins List */
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
    border-color: #10b981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.1);
}

/* Responsive - Desktop */
@media (min-width: 768px) {
    .yolo-bm-checkin-page {
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
</style>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
jQuery(document).ready(function($) {
    let checkinSignaturePad = null;
    let selectedYachtId = null;
    
    // Load initial data
    loadYachts();
    loadBookings();
    loadCheckins();
    
    // New check-in button
    $('#new-checkin-btn').on('click', function() {
        $('#checkin-form-container').slideDown(function() {
            initializeSignaturePad();
        });
        $('#checkin-list-container').hide();
    });
    
    // Cancel check-in
    $('#cancel-checkin-btn').on('click', function() {
        $('#checkin-form-container').slideUp();
        $('#checkin-list-container').show();
        $('#checkin-form')[0].reset();
        $('#equipment-checklist-section').hide();
        if (checkinSignaturePad) {
            checkinSignaturePad.clear();
        }
    });
    
    // Yacht selection - load equipment
    $('#checkin-yacht-select').on('change', function() {
        selectedYachtId = $(this).val();
        if (selectedYachtId) {
            loadEquipmentChecklist(selectedYachtId);
        } else {
            $('#equipment-checklist-section').hide();
        }
    });
    
    // Clear signature
    $('#clear-checkin-signature').on('click', function() {
        if (checkinSignaturePad) {
            checkinSignaturePad.clear();
        }
    });
    
    // Complete check-in
    $('#complete-checkin-btn').on('click', function() {
        const bookingId = $('#checkin-booking-select').val();
        const yachtId = $('#checkin-yacht-select').val();
        
        if (!bookingId || !yachtId) {
            alert('Please select both booking and yacht');
            return;
        }
        
        if (!checkinSignaturePad || checkinSignaturePad.isEmpty()) {
            alert('Please provide your signature');
            return;
        }
        
        // Collect equipment checklist data
        const equipmentData = [];
        $('.yolo-bm-equipment-checkbox').each(function() {
            const categoryName = $(this).closest('.yolo-bm-equipment-category').find('.yolo-bm-category-header').text().trim();
            const itemName = $(this).siblings('.yolo-bm-equipment-label').text();
            const isChecked = $(this).hasClass('checked');
            
            equipmentData.push({
                category: categoryName,
                item: itemName,
                checked: isChecked
            });
        });
        
        const formData = {
            action: 'yolo_bm_save_checkin',
            nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
            booking_id: bookingId,
            yacht_id: yachtId,
            checklist_data: JSON.stringify(equipmentData),
            signature: checkinSignaturePad.toDataURL(),
            status: 'completed'
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Check-in completed successfully!');
                    $('#cancel-checkin-btn').click();
                    loadCheckins();
                } else {
                    alert('Error: ' + (response.data || 'Failed to complete check-in'));
                }
            },
            error: function() {
                alert('Failed to complete check-in. Please try again.');
            }
        });
    });
    
    // Initialize signature pad
    function initializeSignaturePad() {
        const canvas = document.getElementById('checkin-signature-pad');
        if (canvas) {
            if (checkinSignaturePad) {
                checkinSignaturePad.clear();
            }
            checkinSignaturePad = new SignaturePad(canvas, {
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
        if (checkinSignaturePad) {
            checkinSignaturePad.clear();
        }
    }
    
    // Load yachts
    function loadYachts() {
        console.log('Check-In: Loading yachts...');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                console.log('Check-In: Yachts response:', response);
                if (response.success && response.data) {
                    let options = '<option value="">Choose yacht...</option>';
                    response.data.forEach(function(yacht) {
                        options += `<option value="${yacht.id}">${yacht.yacht_name}${yacht.yacht_model ? ' - ' + yacht.yacht_model : ''}</option>`;
                    });
                    $('#checkin-yacht-select').html(options);
                    console.log('Check-In: Loaded ' + response.data.length + ' yachts');
                } else {
                    console.error('Check-In: Failed to load yachts:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Check-In: AJAX error loading yachts:', status, error, xhr);
            }
        });
    }
    
    // Load bookings
    function loadBookings() {
        console.log('Check-In: Loading bookings...');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_bookings_calendar',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                console.log('Check-In: Bookings response:', response);
                if (response.success && response.data) {
                    let options = '<option value="">Choose booking...</option>';
                    response.data.forEach(function(booking) {
                        options += `<option value="${booking.id}">BM-${booking.id} - ${booking.customer_name}${booking.yacht_name ? ' (' + booking.yacht_name + ')' : ''}</option>`;
                    });
                    $('#checkin-booking-select').html(options);
                    console.log('Check-In: Loaded ' + response.data.length + ' bookings');
                } else {
                    console.error('Check-In: Failed to load bookings:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Check-In: AJAX error loading bookings:', status, error, xhr);
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
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
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
                    
                    html += `
                        <div class="yolo-bm-equipment-item">
                            <div class="yolo-bm-equipment-checkbox" data-item="${itemName}"></div>
                            <div class="yolo-bm-equipment-label">${itemLabel}</div>
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
    }
    
    // Load check-ins
    function loadCheckins() {
        $('#checkin-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-clipboard"></span><p>No check-ins yet. Click "New Check-In" to create one.</p></div>');
    }
});
</script>
