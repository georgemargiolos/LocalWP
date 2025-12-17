<?php
/**
 * Base Manager - Yacht Management Admin Page
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

<div class="wrap yolo-base-manager-page">
    <!-- Welcome Header -->
    <div class="yolo-bm-welcome-card">
        <div class="yolo-bm-welcome-content">
            <h1><i class="dashicons dashicons-admin-multisite"></i> Yacht Management</h1>
            <p>Manage your fleet, equipment categories, and inventory for each yacht</p>
        </div>
        <button class="button button-primary button-hero" id="add-yacht-btn">
            <span class="dashicons dashicons-plus-alt"></span> Add New Yacht
        </button>
    </div>
    
    <!-- Yachts Grid -->
    <div id="yachts-list" class="yolo-bm-grid">
        <div class="yolo-bm-loading">
            <span class="spinner is-active"></span>
            <p>Loading yachts...</p>
        </div>
    </div>
</div>

<!-- Add/Edit Yacht Modal -->
<div id="yacht-modal" class="yolo-bm-modal" style="display: none;">
    <div class="yolo-bm-modal-content">
        <span class="yolo-bm-modal-close">&times;</span>
        <h2 id="yacht-modal-title"><i class="dashicons dashicons-admin-multisite"></i> Add New Yacht</h2>
        <form id="yacht-form">
            <input type="hidden" id="yacht-id" name="yacht_id" value="">
            
            <div class="yolo-bm-form-section">
                <h3>Yacht Information</h3>
                <div class="yolo-bm-form-row">
                    <div class="yolo-bm-form-group">
                        <label for="yacht-name">Yacht Name *</label>
                        <input type="text" id="yacht-name" name="yacht_name" class="yolo-bm-input" required>
                    </div>
                    <div class="yolo-bm-form-group">
                        <label for="yacht-model">Model</label>
                        <input type="text" id="yacht-model" name="yacht_model" class="yolo-bm-input">
                    </div>
                </div>
            </div>
            
            <div class="yolo-bm-form-section">
                <h3>Owner Information</h3>
                <div class="yolo-bm-form-row">
                    <div class="yolo-bm-form-group">
                        <label for="owner-name">First Name *</label>
                        <input type="text" id="owner-name" name="owner_name" class="yolo-bm-input" required>
                    </div>
                    <div class="yolo-bm-form-group">
                        <label for="owner-surname">Last Name *</label>
                        <input type="text" id="owner-surname" name="owner_surname" class="yolo-bm-input" required>
                    </div>
                </div>
                <div class="yolo-bm-form-row">
                    <div class="yolo-bm-form-group">
                        <label for="owner-mobile">Mobile *</label>
                        <input type="tel" id="owner-mobile" name="owner_mobile" class="yolo-bm-input" required>
                    </div>
                    <div class="yolo-bm-form-group">
                        <label for="owner-email">Email *</label>
                        <input type="email" id="owner-email" name="owner_email" class="yolo-bm-input" required>
                    </div>
                </div>
            </div>
            
            <div class="yolo-bm-form-actions">
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-yes"></span> Save Yacht
                </button>
                <button type="button" class="button button-large" id="cancel-yacht-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Equipment Management Modal -->
<div id="equipment-modal" class="yolo-bm-modal" style="display: none;">
    <div class="yolo-bm-modal-content yolo-bm-modal-large">
        <span class="yolo-bm-modal-close" id="close-equipment-modal">&times;</span>
        <h2 id="equipment-modal-title"><i class="dashicons dashicons-admin-tools"></i> Equipment Management</h2>
        <p class="yolo-bm-modal-subtitle">Manage equipment categories and items for <strong id="equipment-yacht-name"></strong></p>
        
        <input type="hidden" id="equipment-yacht-id" value="">
        
        <!-- Add Category Section -->
        <div class="yolo-bm-add-category-section">
            <div class="yolo-bm-form-row">
                <div class="yolo-bm-form-group" style="flex: 1;">
                    <input type="text" id="new-category-name" class="yolo-bm-input" placeholder="Enter category name (e.g., Safety Equipment, Navigation)">
                </div>
                <button type="button" class="button button-primary" id="add-category-btn">
                    <span class="dashicons dashicons-plus-alt"></span> Add Category
                </button>
            </div>
        </div>
        
        <!-- Categories List -->
        <div id="equipment-categories-list" class="yolo-bm-categories-list">
            <div class="yolo-bm-loading">
                <span class="spinner is-active"></span>
                <p>Loading equipment...</p>
            </div>
        </div>
    </div>
</div>

<style>
/* Base Manager Global Styles */
.yolo-base-manager-page {
    background: #f5f7fa;
    margin: -20px -20px 0 -22px;
    padding: 30px;
    min-height: 100vh;
}

/* Welcome Card */
.yolo-bm-welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 16px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.yolo-bm-welcome-content h1 {
    margin: 0 0 10px 0;
    font-size: 32px;
    font-weight: 700;
    color: white;
    display: flex;
    align-items: center;
    gap: 15px;
}

.yolo-bm-welcome-content h1 .dashicons {
    font-size: 40px;
    width: 40px;
    height: 40px;
}

.yolo-bm-welcome-content p {
    margin: 0;
    font-size: 16px;
    opacity: 0.95;
}

.yolo-bm-welcome-card .button-hero {
    background: white;
    color: #667eea;
    border: none;
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.yolo-bm-welcome-card .button-hero:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

/* Yacht Cards Grid */
.yolo-bm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    margin-top: 30px;
}

.yolo-bm-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.yolo-bm-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.yolo-bm-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f3f4f6;
}

.yolo-bm-card-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
}

.yolo-bm-card-title {
    flex: 1;
}

.yolo-bm-card-title h3 {
    margin: 0 0 4px 0;
    font-size: 20px;
    font-weight: 600;
    color: #1f2937;
}

.yolo-bm-card-title .yacht-model {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.yolo-bm-card-body {
    margin-bottom: 20px;
}

.yolo-bm-info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 0;
    font-size: 14px;
    color: #4b5563;
}

.yolo-bm-info-row .dashicons {
    color: #667eea;
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.yolo-bm-info-row strong {
    color: #1f2937;
    min-width: 80px;
}

.yolo-bm-card-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.yolo-bm-card-actions .button {
    flex: 1;
    min-width: 100px;
    justify-content: center;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.yolo-bm-card-actions .button-primary {
    background: #667eea;
    border-color: #667eea;
}

.yolo-bm-card-actions .button-primary:hover {
    background: #5568d3;
}

.yolo-bm-card-actions .equipment-btn {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.yolo-bm-card-actions .equipment-btn:hover {
    background: #059669;
}

.yolo-bm-card-actions .button-link-delete {
    background: #ef4444;
    border-color: #ef4444;
    color: white;
}

.yolo-bm-card-actions .button-link-delete:hover {
    background: #dc2626;
}

/* Loading State */
.yolo-bm-loading {
    text-align: center;
    padding: 60px 20px;
    color: #6b7280;
}

.yolo-bm-loading .spinner {
    float: none;
    margin: 0 auto 16px;
}

/* Modal Styles */
.yolo-bm-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.yolo-bm-modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 40px;
    border-radius: 16px;
    width: 90%;
    max-width: 600px;
    max-height: 85vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.3s ease;
}

.yolo-bm-modal-large {
    max-width: 900px;
}

@keyframes slideDown {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.yolo-bm-modal-close {
    color: #9ca3af;
    float: right;
    font-size: 32px;
    font-weight: bold;
    line-height: 1;
    cursor: pointer;
    transition: color 0.2s ease;
}

.yolo-bm-modal-close:hover {
    color: #ef4444;
}

.yolo-bm-modal-content h2 {
    margin: 0 0 8px 0;
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 12px;
}

.yolo-bm-modal-subtitle {
    margin: 0 0 30px 0;
    color: #6b7280;
    font-size: 15px;
}

/* Form Styles */
.yolo-bm-form-section {
    margin-bottom: 30px;
}

.yolo-bm-form-section h3 {
    margin: 0 0 16px 0;
    font-size: 18px;
    font-weight: 600;
    color: #374151;
    padding-bottom: 8px;
    border-bottom: 2px solid #f3f4f6;
}

.yolo-bm-form-row {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
}

.yolo-bm-form-group {
    flex: 1;
}

.yolo-bm-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.yolo-bm-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 15px;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.yolo-bm-input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.yolo-bm-form-actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    padding-top: 24px;
    border-top: 2px solid #f3f4f6;
}

.yolo-bm-form-actions .button {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
}

/* Equipment Management Styles */
.yolo-bm-add-category-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    border: 2px dashed #d1d5db;
}

.yolo-bm-categories-list {
    max-height: 500px;
    overflow-y: auto;
}

.yolo-bm-category-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 16px;
    transition: all 0.2s ease;
}

.yolo-bm-category-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.yolo-bm-category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f3f4f6;
}

.yolo-bm-category-title {
    font-size: 18px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 8px;
}

.yolo-bm-category-title .dashicons {
    color: #667eea;
}

.yolo-bm-category-actions {
    display: flex;
    gap: 8px;
}

.yolo-bm-item-list {
    margin-bottom: 16px;
}

.yolo-bm-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #f9fafb;
    border-radius: 8px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
}

.yolo-bm-item:hover {
    background: #f3f4f6;
}

.yolo-bm-item-name {
    flex: 1;
    font-size: 14px;
    color: #374151;
    font-weight: 500;
}

.yolo-bm-item-quantity {
    font-size: 13px;
    color: #6b7280;
    font-weight: 600;
    margin-left: 8px;
}

.yolo-bm-item-edit {
    background: #3b82f6;
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    line-height: 1;
}

.yolo-bm-item-edit:hover {
    background: #2563eb;
    transform: scale(1.1);
}

.yolo-bm-item-remove {
    background: #ef4444;
    color: white;
    border: none;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 16px;
    line-height: 1;
}

.yolo-bm-item-remove:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.yolo-bm-add-item-row {
    display: flex;
    gap: 8px;
}

.yolo-bm-add-item-row input {
    flex: 1;
    padding: 10px 14px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
}

.yolo-bm-add-item-row input:focus {
    outline: none;
    border-color: #667eea;
}

.yolo-bm-add-item-btn {
    background: #10b981;
    color: white;
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 20px;
    font-weight: bold;
}

.yolo-bm-add-item-btn:hover {
    background: #059669;
    transform: scale(1.05);
}

.yolo-bm-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #9ca3af;
}

.yolo-bm-empty-state .dashicons {
    font-size: 48px;
    width: 48px;
    height: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

/* Responsive */
@media (max-width: 768px) {
    .yolo-bm-welcome-card {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .yolo-bm-grid {
        grid-template-columns: 1fr;
    }
    
    .yolo-bm-form-row {
        flex-direction: column;
    }
    
    .yolo-bm-modal-content {
        margin: 10% auto;
        width: 95%;
        padding: 24px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentYachtId = null;
    let currentYachtName = '';
    let equipmentCategories = [];
    
    // Load yachts on page load
    loadYachts();
    
    // Add yacht button
    $('#add-yacht-btn').on('click', function() {
        $('#yacht-modal-title').html('<i class="dashicons dashicons-admin-multisite"></i> Add New Yacht');
        $('#yacht-form')[0].reset();
        $('#yacht-id').val('');
        $('#yacht-modal').show();
    });
    
    // Close modals
    $('.yolo-bm-modal-close, #cancel-yacht-btn').on('click', function() {
        $('#yacht-modal').hide();
    });
    
    $('#close-equipment-modal').on('click', function() {
        $('#equipment-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'yacht-modal') {
            $('#yacht-modal').hide();
        }
        if (event.target.id === 'equipment-modal') {
            $('#equipment-modal').hide();
        }
    });
    
    // Save yacht
    $('#yacht-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'yolo_bm_save_yacht',
            nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
            yacht_id: $('#yacht-id').val(),
            yacht_name: $('#yacht-name').val(),
            yacht_model: $('#yacht-model').val(),
            owner_name: $('#owner-name').val(),
            owner_surname: $('#owner-surname').val(),
            owner_mobile: $('#owner-mobile').val(),
            owner_email: $('#owner-email').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Yacht saved successfully!');
                    $('#yacht-modal').hide();
                    loadYachts();
                } else {
                    alert('Error: ' + (response.data || 'Failed to save yacht'));
                }
            },
            error: function() {
                alert('Failed to save yacht. Please try again.');
            }
        });
    });
    
    // Load yachts function
    function loadYachts() {
        console.log('Loading yachts...');
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                console.log('Yachts response:', response);
                if (response.success && response.data) {
                    if (response.data.length > 0) {
                        displayYachts(response.data);
                    } else {
                        $('#yachts-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-admin-multisite"></span><p>No yachts found. Click "Add New Yacht" to create one.</p></div>');
                    }
                } else {
                    console.error('Yachts load failed:', response);
                    $('#yachts-list').html('<div class="yolo-bm-empty-state" style="color: #ef4444;"><span class="dashicons dashicons-warning"></span><p>Error loading yachts: ' + (response.data || 'Unknown error') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading yachts:', status, error, xhr);
                $('#yachts-list').html('<div class="yolo-bm-empty-state" style="color: #ef4444;"><span class="dashicons dashicons-warning"></span><p>Failed to load yachts. Please refresh the page.</p></div>');
            }
        });
    }
    
    // Display yachts function
    function displayYachts(yachts) {
        if (!yachts || yachts.length === 0) {
            $('#yachts-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-admin-multisite"></span><p>No yachts found. Click "Add New Yacht" to create one.</p></div>');
            return;
        }
        
        let html = '';
        yachts.forEach(function(yacht) {
            html += `
                <div class="yolo-bm-card">
                    <div class="yolo-bm-card-header">
                        <div class="yolo-bm-card-icon">
                            <span class="dashicons dashicons-admin-multisite"></span>
                        </div>
                        <div class="yolo-bm-card-title">
                            <h3>${yacht.yacht_name}</h3>
                            ${yacht.yacht_model ? `<p class="yacht-model">${yacht.yacht_model}</p>` : ''}
                        </div>
                    </div>
                    <div class="yolo-bm-card-body">
                        <div class="yolo-bm-info-row">
                            <span class="dashicons dashicons-admin-users"></span>
                            <strong>Owner:</strong> ${yacht.owner_name} ${yacht.owner_surname}
                        </div>
                        <div class="yolo-bm-info-row">
                            <span class="dashicons dashicons-email"></span>
                            <strong>Email:</strong> ${yacht.owner_email}
                        </div>
                        <div class="yolo-bm-info-row">
                            <span class="dashicons dashicons-phone"></span>
                            <strong>Mobile:</strong> ${yacht.owner_mobile}
                        </div>
                    </div>
                    <div class="yolo-bm-card-actions">
                        <button class="button button-primary edit-yacht-btn" data-yacht-id="${yacht.id}">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </button>
                        <button class="button equipment-btn" data-yacht-id="${yacht.id}" data-yacht-name="${yacht.yacht_name}">
                            <span class="dashicons dashicons-admin-tools"></span> Equipment
                        </button>
                        <button class="button button-link-delete delete-yacht-btn" data-yacht-id="${yacht.id}">
                            <span class="dashicons dashicons-trash"></span> Delete
                        </button>
                    </div>
                </div>
            `;
        });
        
        $('#yachts-list').html(html);
        
        // Edit yacht
        $('.edit-yacht-btn').on('click', function() {
            const yachtId = $(this).data('yacht-id');
            const yacht = yachts.find(y => y.id == yachtId);
            if (yacht) {
                $('#yacht-modal-title').html('<i class="dashicons dashicons-edit"></i> Edit Yacht');
                $('#yacht-id').val(yacht.id);
                $('#yacht-name').val(yacht.yacht_name);
                $('#yacht-model').val(yacht.yacht_model || '');
                $('#owner-name').val(yacht.owner_name || '');
                $('#owner-surname').val(yacht.owner_surname || '');
                $('#owner-mobile').val(yacht.owner_mobile || '');
                $('#owner-email').val(yacht.owner_email || '');
                $('#yacht-modal').show();
            }
        });
        
        // Equipment button
        $('.equipment-btn').on('click', function() {
            currentYachtId = $(this).data('yacht-id');
            currentYachtName = $(this).data('yacht-name');
            $('#equipment-yacht-id').val(currentYachtId);
            $('#equipment-yacht-name').text(currentYachtName);
            $('#equipment-modal').show();
            loadEquipmentCategories();
        });
        
        // Delete yacht
        $('.delete-yacht-btn').on('click', function() {
            if (!confirm('Are you sure you want to delete this yacht? This will also delete all associated equipment.')) {
                return;
            }
            
            const yachtId = $(this).data('yacht-id');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'yolo_bm_delete_yacht',
                    nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
                    yacht_id: yachtId
                },
                success: function(response) {
                    if (response.success) {
                        alert('Yacht deleted successfully!');
                        loadYachts();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to delete yacht'));
                    }
                },
                error: function() {
                    alert('Failed to delete yacht. Please try again.');
                }
            });
        });
    }
    
    // Add category
    $('#add-category-btn').on('click', function() {
        const categoryName = $('#new-category-name').val().trim();
        if (!categoryName) {
            alert('Please enter a category name');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_save_equipment_category',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
                yacht_id: currentYachtId,
                category_name: categoryName,
                items: JSON.stringify([])
            },
            success: function(response) {
                if (response.success) {
                    $('#new-category-name').val('');
                    loadEquipmentCategories();
                } else {
                    alert('Error: ' + (response.data || 'Failed to add category'));
                }
            },
            error: function() {
                alert('Failed to add category. Please try again.');
            }
        });
    });
    
    // Load equipment categories
    function loadEquipmentCategories() {
        $('#equipment-categories-list').html('<div class="yolo-bm-loading"><span class="spinner is-active"></span><p>Loading equipment...</p></div>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_equipment_categories',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
                yacht_id: currentYachtId
            },
            success: function(response) {
                if (response.success && response.data) {
                    equipmentCategories = response.data;
                    displayEquipmentCategories(response.data);
                } else {
                    $('#equipment-categories-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-admin-tools"></span><p>No equipment categories yet. Add one above to get started.</p></div>');
                }
            },
            error: function() {
                $('#equipment-categories-list').html('<div class="yolo-bm-empty-state" style="color: #ef4444;"><span class="dashicons dashicons-warning"></span><p>Failed to load equipment. Please try again.</p></div>');
            }
        });
    }
    
    // Display equipment categories
    function displayEquipmentCategories(categories) {
        if (!categories || categories.length === 0) {
            $('#equipment-categories-list').html('<div class="yolo-bm-empty-state"><span class="dashicons dashicons-admin-tools"></span><p>No equipment categories yet. Add one above to get started.</p></div>');
            return;
        }
        
        let html = '';
        categories.forEach(function(category) {
            const items = category.items ? JSON.parse(category.items) : [];
            
            html += `
                <div class="yolo-bm-category-card" data-category-id="${category.id}">
                    <div class="yolo-bm-category-header">
                        <div class="yolo-bm-category-title">
                            <span class="dashicons dashicons-category"></span>
                            ${category.category_name}
                        </div>
                        <div class="yolo-bm-category-actions">
                            <button class="button button-small button-link-delete delete-category-btn" data-category-id="${category.id}">
                                <span class="dashicons dashicons-trash"></span> Delete Category
                            </button>
                        </div>
                    </div>
                    <div class="yolo-bm-item-list">
            `;
            
            if (items.length > 0) {
                items.forEach(function(item, index) {
                    // Support both old format (string) and new format (object with name and quantity)
                    const itemName = typeof item === 'string' ? item : item.name;
                    const itemQuantity = typeof item === 'string' ? '' : (item.quantity || '');
                    
                    html += `
                        <div class="yolo-bm-item">
                            <span class="yolo-bm-item-name">${itemName}</span>
                            <span class="yolo-bm-item-quantity">${itemQuantity ? `(${itemQuantity})` : ''}</span>
                            <button class="yolo-bm-item-edit edit-item-btn" data-category-id="${category.id}" data-item-index="${index}" title="Edit quantity">✎</button>
                            <button class="yolo-bm-item-remove remove-item-btn" data-category-id="${category.id}" data-item-index="${index}">−</button>
                        </div>
                    `;
                });
            }
            
            html += `
                    </div>
                    <div class="yolo-bm-add-item-row">
                        <input type="text" class="new-item-input" placeholder="Enter item name" data-category-id="${category.id}">
                        <input type="text" class="new-item-quantity-input" placeholder="Qty" data-category-id="${category.id}" style="width: 80px; margin-left: 8px;">
                        <button class="yolo-bm-add-item-btn add-item-btn" data-category-id="${category.id}">+</button>
                    </div>
                </div>
            `;
        });
        
        $('#equipment-categories-list').html(html);
        
        // Add item
        $('.add-item-btn').on('click', function() {
            const categoryId = $(this).data('category-id');
            const nameInput = $(`.new-item-input[data-category-id="${categoryId}"]`);
            const quantityInput = $(`.new-item-quantity-input[data-category-id="${categoryId}"]`);
            const itemName = nameInput.val().trim();
            const itemQuantity = quantityInput.val().trim();
            
            if (!itemName) {
                alert('Please enter an item name');
                return;
            }
            
            const category = equipmentCategories.find(c => c.id == categoryId);
            if (category) {
                const items = category.items ? JSON.parse(category.items) : [];
                // Store as object with name and quantity
                items.push({
                    name: itemName,
                    quantity: itemQuantity
                });
                
                saveEquipmentCategory(categoryId, category.category_name, items, function() {
                    nameInput.val('');
                    quantityInput.val('');
                    loadEquipmentCategories();
                });
            }
        });
        
        // Edit item quantity
        $('.edit-item-btn').on('click', function() {
            const categoryId = $(this).data('category-id');
            const itemIndex = $(this).data('item-index');
            
            const category = equipmentCategories.find(c => c.id == categoryId);
            if (category) {
                const items = category.items ? JSON.parse(category.items) : [];
                const item = items[itemIndex];
                
                // Support both old format (string) and new format (object)
                const currentName = typeof item === 'string' ? item : item.name;
                const currentQuantity = typeof item === 'string' ? '' : (item.quantity || '');
                
                const newQuantity = prompt(`Enter quantity for "${currentName}":`, currentQuantity);
                
                if (newQuantity !== null) { // User didn't cancel
                    // Convert to new format
                    items[itemIndex] = {
                        name: currentName,
                        quantity: newQuantity.trim()
                    };
                    
                    saveEquipmentCategory(categoryId, category.category_name, items, function() {
                        loadEquipmentCategories();
                    });
                }
            }
        });
        
        // Remove item
        $('.remove-item-btn').on('click', function() {
            const categoryId = $(this).data('category-id');
            const itemIndex = $(this).data('item-index');
            
            const category = equipmentCategories.find(c => c.id == categoryId);
            if (category) {
                const items = category.items ? JSON.parse(category.items) : [];
                items.splice(itemIndex, 1);
                
                saveEquipmentCategory(categoryId, category.category_name, items, function() {
                    loadEquipmentCategories();
                });
            }
        });
        
        // Delete category
        $('.delete-category-btn').on('click', function() {
            if (!confirm('Are you sure you want to delete this category and all its items?')) {
                return;
            }
            
            const categoryId = $(this).data('category-id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'yolo_bm_delete_equipment_category',
                    nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
                    category_id: categoryId
                },
                success: function(response) {
                    if (response.success) {
                        loadEquipmentCategories();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to delete category'));
                    }
                },
                error: function() {
                    alert('Failed to delete category. Please try again.');
                }
            });
        });
        
        // Enter key to add item
        $('.new-item-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const categoryId = $(this).data('category-id');
                $(`.add-item-btn[data-category-id="${categoryId}"]`).click();
            }
        });
    }
    
    // Save equipment category
    function saveEquipmentCategory(categoryId, categoryName, items, callback) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_save_equipment_category',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
                category_id: categoryId,
                yacht_id: currentYachtId,
                category_name: categoryName,
                items: JSON.stringify(items)
            },
            success: function(response) {
                if (response.success) {
                    if (callback) callback();
                } else {
                    alert('Error: ' + (response.data || 'Failed to save category'));
                }
            },
            error: function() {
                alert('Failed to save category. Please try again.');
            }
        });
    }
    
    // Enter key to add category
    $('#new-category-name').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#add-category-btn').click();
        }
    });
});
</script>
