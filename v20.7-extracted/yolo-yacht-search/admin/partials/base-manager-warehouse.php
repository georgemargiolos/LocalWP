<?php
/**
 * Base Manager - Warehouse Management Admin Page
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.11
 */

if (!defined('ABSPATH')) {
    exit;
}

// Permission check
if (!current_user_can('edit_posts')) {
    wp_die(__('Sorry, you are not allowed to access this page.', 'yolo-yacht-search'));
}
?>

<div class="wrap yolo-base-manager-warehouse">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Welcome Section -->
    <div class="yolo-bm-welcome-section">
        <div class="yolo-bm-welcome-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2>Warehouse Inventory Management</h2>
                    <p>Track inventory, manage stock levels, monitor expiry dates, and set up automatic notifications.</p>
                </div>
                <button class="button button-primary button-hero" id="add-item-btn" style="background: white; color: #667eea; border: 2px solid white;">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> Add New Item
                </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="yolo-bm-filter-section">
        <div class="yolo-bm-filter-card">
            <div class="filter-group">
                <label for="filter-yacht">Filter by Yacht:</label>
                <select id="filter-yacht" class="filter-select">
                    <option value="">All Yachts</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-category">Filter by Category:</label>
                <select id="filter-category" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="safety">Safety Equipment</option>
                    <option value="cleaning">Cleaning Supplies</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="food">Food & Beverages</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="filter-status">Filter by Status:</label>
                <select id="filter-status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="expiring_soon">Expiring Soon</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Warehouse Items Grid -->
    <div id="warehouse-items-grid" class="yolo-bm-warehouse-grid">
        <div style="text-align: center; padding: 60px 20px;">
            <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
            <p style="margin-top: 15px; color: #666;">Loading warehouse items...</p>
        </div>
    </div>

</div>

<!-- Add/Edit Item Modal -->
<div id="warehouse-modal" class="yolo-bm-modal" style="display: none;">
    <div class="yolo-bm-modal-content">
        <span class="yolo-bm-modal-close">&times;</span>
        <h2 id="warehouse-modal-title">Add Warehouse Item</h2>
        <form id="warehouse-form">
            <input type="hidden" id="item-id" name="item_id" value="">
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label for="item-yacht">Yacht *</label>
                    <select id="item-yacht" name="yacht_id" class="form-control" required>
                        <option value="">Select Yacht...</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="item-name">Item Name *</label>
                    <input type="text" id="item-name" name="item_name" class="form-control" placeholder="e.g., Life Jacket, Cleaning Solution" required>
                </div>
                <div class="form-group">
                    <label for="item-category">Category *</label>
                    <select id="item-category" name="item_category" class="form-control" required>
                        <option value="safety">Safety Equipment</option>
                        <option value="cleaning">Cleaning Supplies</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="food">Food & Beverages</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="item-quantity">Quantity *</label>
                    <input type="number" id="item-quantity" name="item_quantity" class="form-control" min="0" placeholder="0" required>
                </div>
                <div class="form-group">
                    <label for="item-unit">Unit</label>
                    <select id="item-unit" name="item_unit" class="form-control">
                        <option value="pcs">Pieces</option>
                        <option value="kg">Kilograms</option>
                        <option value="liters">Liters</option>
                        <option value="boxes">Boxes</option>
                        <option value="bottles">Bottles</option>
                        <option value="cans">Cans</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="item-location">Storage Location *</label>
                    <input type="text" id="item-location" name="location" class="form-control" placeholder="e.g., Shelf A3, Cabinet 2, Storage Room" required>
                    <small class="form-hint">Specify where this item is stored</small>
                </div>
                <div class="form-group">
                    <label for="item-expiry">Expiry Date</label>
                    <input type="date" id="item-expiry" name="expiry_date" class="form-control">
                    <small class="form-hint">Leave empty if item doesn't expire</small>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="form-section" id="notification-section" style="display: none;">
                <h3 style="margin: 25px 0 15px 0; color: #667eea; font-size: 16px;">
                    <span class="dashicons dashicons-bell" style="vertical-align: middle;"></span> 
                    Expiry Notification Settings
                </h3>
                
                <div class="notification-card">
                    <div class="form-group checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="enable-notifications" name="enable_notifications">
                            <span>Enable expiry notifications for this item</span>
                        </label>
                    </div>

                    <div id="notification-options" style="display: none; margin-top: 15px; padding: 15px; background: #f9fafb; border-radius: 6px;">
                        <div class="form-group">
                            <label for="notify-days-before">Notify how many days before expiry? *</label>
                            <select id="notify-days-before" name="notify_days_before" class="form-control">
                                <option value="1">1 day before</option>
                                <option value="3">3 days before</option>
                                <option value="7" selected>7 days before (1 week)</option>
                                <option value="14">14 days before (2 weeks)</option>
                                <option value="30">30 days before (1 month)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Notification Methods:</label>
                            <div class="checkbox-group-vertical">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="notify_email" checked>
                                    <span><span class="dashicons dashicons-email" style="color: #667eea;"></span> Email Notification</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="notify_dashboard">
                                    <span><span class="dashicons dashicons-dashboard" style="color: #667eea;"></span> Dashboard Alert</span>
                                </label>
                                <label class="checkbox-label">
                                    <input type="checkbox" name="notify_viber">
                                    <span><span class="dashicons dashicons-smartphone" style="color: #8b5cf6;"></span> Viber Message (Coming Soon)</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notify-recipients">Notify Base Managers:</label>
                            <select id="notify-recipients" name="notify_recipients[]" class="form-control" multiple size="4">
                                <?php
                                // Get all base managers
                                $base_managers = get_users(array('role' => 'base_manager'));
                                $admins = get_users(array('role' => 'administrator'));
                                $all_managers = array_merge($base_managers, $admins);
                                
                                foreach ($all_managers as $manager) {
                                    echo '<option value="' . esc_attr($manager->ID) . '" selected>' . 
                                         esc_html($manager->display_name) . ' (' . esc_html($manager->user_email) . ')</option>';
                                }
                                ?>
                            </select>
                            <small class="form-hint">Hold Ctrl (Cmd on Mac) to select multiple</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-yes" style="vertical-align: middle;"></span> Save Item
                </button>
                <button type="button" class="button button-large" id="cancel-item-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
/* Warehouse Page Styles - Matching Dashboard Design */
.yolo-base-manager-warehouse {
    max-width: 1400px;
}

/* Welcome Section */
.yolo-bm-welcome-section {
    margin: 20px 0 30px 0;
}

.yolo-bm-welcome-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.yolo-bm-welcome-card h2 {
    color: white;
    margin: 0 0 10px 0;
    font-size: 28px;
}

.yolo-bm-welcome-card p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
    margin: 0;
}

/* Filter Section */
.yolo-bm-filter-section {
    margin-bottom: 30px;
}

.yolo-bm-filter-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
    font-size: 14px;
}

.filter-select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.filter-select:focus {
    border-color: #667eea;
    outline: none;
}

/* Warehouse Items Grid */
.yolo-bm-warehouse-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.warehouse-item-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.warehouse-item-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    border-color: #667eea;
}

.warehouse-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.warehouse-item-title {
    font-size: 18px;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 5px 0;
}

.warehouse-item-yacht {
    font-size: 13px;
    color: #667eea;
    font-weight: 600;
}

.warehouse-item-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-in-stock {
    background: #d1fae5;
    color: #065f46;
}

.badge-low-stock {
    background: #fed7aa;
    color: #92400e;
}

.badge-expiring {
    background: #fecaca;
    color: #991b1b;
}

.warehouse-item-details {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 15px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #6b7280;
}

.detail-item .dashicons {
    color: #667eea;
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.detail-item strong {
    color: #1f2937;
}

.warehouse-item-location {
    background: #f3f4f6;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
    font-size: 13px;
    color: #4b5563;
}

.warehouse-item-location .dashicons {
    color: #667eea;
    vertical-align: middle;
}

.warehouse-item-expiry {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    font-size: 13px;
}

.warehouse-item-expiry.expiring-soon {
    background: #fee2e2;
    border-left-color: #dc2626;
}

.warehouse-item-actions {
    display: flex;
    gap: 8px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.warehouse-item-actions .button {
    flex: 1;
    text-align: center;
    justify-content: center;
}

/* Modal Styles */
.yolo-bm-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.6);
    backdrop-filter: blur(4px);
}

.yolo-bm-modal-content {
    background-color: #fefefe;
    margin: 3% auto;
    padding: 0;
    border: none;
    border-radius: 12px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.yolo-bm-modal-content h2 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin: 0;
    padding: 25px 30px;
    border-radius: 12px 12px 0 0;
    font-size: 24px;
}

.yolo-bm-modal-close {
    color: white;
    float: right;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    line-height: 24px;
    transition: transform 0.2s;
}

.yolo-bm-modal-close:hover {
    transform: scale(1.1);
}

#warehouse-form {
    padding: 30px;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #374151;
    font-size: 14px;
}

.form-control {
    padding: 12px;
    border: 2px solid #e5e7eb;
    border-radius: 6px;
    font-size: 14px;
    transition: all 0.3s;
    width: 100%;
    box-sizing: border-box;
}

.form-control:focus {
    border-color: #667eea;
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-hint {
    font-size: 12px;
    color: #6b7280;
    margin-top: 4px;
    font-style: italic;
}

.form-section {
    margin-top: 25px;
    padding-top: 25px;
    border-top: 2px solid #e5e7eb;
}

.notification-card {
    background: #f9fafb;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
}

.checkbox-group {
    margin-bottom: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-size: 14px;
    color: #374151;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-group-vertical {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #e5e7eb;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.button-large {
    padding: 12px 30px;
    font-size: 15px;
    height: auto;
}

.button-primary.button-large {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    text-shadow: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.button-primary.button-large:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-1px);
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
}

/* Responsive */
@media (max-width: 768px) {
    .yolo-bm-warehouse-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .yolo-bm-filter-card {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let allItems = [];
    let allYachts = [];
    
    // Load yachts and warehouse items
    loadYachts();
    loadWarehouseItems();
    
    // Load yachts for dropdowns
    function loadYachts() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    allYachts = response.data;
                    
                    // Populate filter dropdown
                    let filterOptions = '<option value="">All Yachts</option>';
                    allYachts.forEach(function(yacht) {
                        filterOptions += `<option value="${yacht.id}">${yacht.yacht_name}</option>`;
                    });
                    $('#filter-yacht').html(filterOptions);
                    
                    // Populate form dropdown
                    let formOptions = '<option value="">Select Yacht...</option>';
                    allYachts.forEach(function(yacht) {
                        formOptions += `<option value="${yacht.id}">${yacht.yacht_name}</option>`;
                    });
                    $('#item-yacht').html(formOptions);
                }
            }
        });
    }
    
    // Add Item button
    $('#add-item-btn').on('click', function() {
        $('#warehouse-modal-title').text('Add Warehouse Item');
        $('#warehouse-form')[0].reset();
        $('#item-id').val('');
        $('#notification-section').hide();
        $('#notification-options').hide();
        $('#enable-notifications').prop('checked', false);
        $('#warehouse-modal').show();
    });
    
    // Close modal
    $('.yolo-bm-modal-close, #cancel-item-btn').on('click', function() {
        $('#warehouse-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'warehouse-modal') {
            $('#warehouse-modal').hide();
        }
    });
    
    // Show notification section when expiry date is set
    $('#item-expiry').on('change', function() {
        if ($(this).val()) {
            $('#notification-section').slideDown();
        } else {
            $('#notification-section').slideUp();
            $('#enable-notifications').prop('checked', false);
            $('#notification-options').hide();
        }
    });
    
    // Toggle notification options
    $('#enable-notifications').on('change', function() {
        if ($(this).is(':checked')) {
            $('#notification-options').slideDown();
        } else {
            $('#notification-options').slideUp();
        }
    });
    
    // Save warehouse item
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'yolo_bm_save_warehouse_item',
            nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
            item_id: $('#item-id').val(),
            yacht_id: $('#item-yacht').val(),
            item_name: $('#item-name').val(),
            quantity: $('#item-quantity').val(),
            expiry_date: $('#item-expiry').val(),
            location: $('#item-location').val(),
            category: $('#item-category').val(),
            unit: $('#item-unit').val(),
            enable_notifications: $('#enable-notifications').is(':checked') ? 1 : 0,
            notify_days_before: $('#notify-days-before').val(),
            notify_email: $('input[name="notify_email"]').is(':checked') ? 1 : 0,
            notify_dashboard: $('input[name="notify_dashboard"]').is(':checked') ? 1 : 0,
            notify_viber: $('input[name="notify_viber"]').is(':checked') ? 1 : 0,
            notify_recipients: $('#notify-recipients').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('✅ Item saved successfully!');
                    $('#warehouse-modal').hide();
                    loadWarehouseItems();
                } else {
                    alert('❌ Error: ' + (response.data.message || 'Failed to save item'));
                }
            },
            error: function() {
                alert('❌ Failed to save item. Please try again.');
            }
        });
    });
    
    // Load warehouse items
    function loadWarehouseItems() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_warehouse_items',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data && response.data.items) {
                    allItems = response.data.items;
                    displayWarehouseItems(allItems);
                } else {
                    $('#warehouse-items-grid').html('<div style="text-align: center; padding: 60px 20px; color: #666;"><p style="font-size: 18px;">📦 No items in warehouse yet.</p><p>Click "Add New Item" to get started!</p></div>');
                }
            },
            error: function() {
                $('#warehouse-items-grid').html('<div style="text-align: center; padding: 60px 20px; color: #d63638;"><p>❌ Failed to load warehouse items.</p><p>Please refresh the page.</p></div>');
            }
        });
    }
    
    // Display warehouse items
    function displayWarehouseItems(items) {
        if (!items || items.length === 0) {
            $('#warehouse-items-grid').html('<div style="text-align: center; padding: 60px 20px; color: #666;"><p style="font-size: 18px;">📦 No items found.</p></div>');
            return;
        }
        
        let html = '';
        items.forEach(function(item) {
            const yacht = allYachts.find(y => y.id == item.yacht_id);
            const yachtName = yacht ? yacht.yacht_name : 'Unknown Yacht';
            
            // Determine status
            let status = 'in-stock';
            let statusBadge = '<span class="warehouse-item-badge badge-in-stock">In Stock</span>';
            
            if (item.quantity < 10) {
                status = 'low-stock';
                statusBadge = '<span class="warehouse-item-badge badge-low-stock">⚠️ Low Stock</span>';
            }
            
            // Check expiry
            let expiryHtml = '';
            if (item.expiry_date) {
                const expiryDate = new Date(item.expiry_date);
                const today = new Date();
                const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
                
                if (daysUntilExpiry <= 7 && daysUntilExpiry >= 0) {
                    statusBadge = '<span class="warehouse-item-badge badge-expiring">⏰ Expiring Soon</span>';
                    expiryHtml = `<div class="warehouse-item-expiry expiring-soon">
                        <span class="dashicons dashicons-warning"></span> 
                        <strong>Expires in ${daysUntilExpiry} days</strong> (${item.expiry_date})
                    </div>`;
                } else if (daysUntilExpiry < 0) {
                    statusBadge = '<span class="warehouse-item-badge badge-expiring">❌ Expired</span>';
                    expiryHtml = `<div class="warehouse-item-expiry expiring-soon">
                        <span class="dashicons dashicons-dismiss"></span> 
                        <strong>Expired</strong> (${item.expiry_date})
                    </div>`;
                } else {
                    expiryHtml = `<div class="warehouse-item-expiry">
                        <span class="dashicons dashicons-calendar-alt"></span> 
                        Expires: ${item.expiry_date}
                    </div>`;
                }
            }
            
            html += `
                <div class="warehouse-item-card" data-yacht-id="${item.yacht_id}" data-category="${item.category || ''}" data-status="${status}">
                    <div class="warehouse-item-header">
                        <div>
                            <h3 class="warehouse-item-title">${item.item_name}</h3>
                            <div class="warehouse-item-yacht">🚤 ${yachtName}</div>
                        </div>
                        ${statusBadge}
                    </div>
                    
                    <div class="warehouse-item-details">
                        <div class="detail-item">
                            <span class="dashicons dashicons-tag"></span>
                            <span>${item.category || 'Other'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <strong>${item.quantity}</strong> ${item.unit || 'pcs'}
                        </div>
                    </div>
                    
                    ${item.location ? `
                    <div class="warehouse-item-location">
                        <span class="dashicons dashicons-location"></span>
                        <strong>Location:</strong> ${item.location}
                    </div>
                    ` : ''}
                    
                    ${expiryHtml}
                    
                    <div class="warehouse-item-actions">
                        <button class="button edit-item-btn" data-item-id="${item.id}">
                            <span class="dashicons dashicons-edit"></span> Edit
                        </button>
                        <button class="button button-link-delete delete-item-btn" data-item-id="${item.id}">
                            <span class="dashicons dashicons-trash"></span> Delete
                        </button>
                    </div>
                </div>
            `;
        });
        
        $('#warehouse-items-grid').html(html);
        
        // Attach event handlers
        attachItemEventHandlers(items);
    }
    
    // Attach event handlers to item cards
    function attachItemEventHandlers(items) {
        // Edit item
        $('.edit-item-btn').on('click', function() {
            const itemId = $(this).data('item-id');
            const item = items.find(i => i.id == itemId);
            if (item) {
                $('#warehouse-modal-title').text('Edit Warehouse Item');
                $('#item-id').val(item.id);
                $('#item-yacht').val(item.yacht_id);
                $('#item-name').val(item.item_name);
                $('#item-category').val(item.category || 'other');
                $('#item-quantity').val(item.quantity);
                $('#item-unit').val(item.unit || 'pcs');
                $('#item-location').val(item.location || '');
                $('#item-expiry').val(item.expiry_date || '');
                
                if (item.expiry_date) {
                    $('#notification-section').show();
                }
                
                $('#warehouse-modal').show();
            }
        });
        
        // Delete item
        $('.delete-item-btn').on('click', function() {
            if (!confirm('⚠️ Are you sure you want to delete this item from the warehouse?')) {
                return;
            }
            
            const itemId = $(this).data('item-id');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'yolo_bm_delete_warehouse_item',
                    nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
                    item_id: itemId
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ Item deleted successfully!');
                        loadWarehouseItems();
                    } else {
                        alert('❌ Error: ' + (response.data.message || 'Failed to delete item'));
                    }
                },
                error: function() {
                    alert('❌ Failed to delete item. Please try again.');
                }
            });
        });
    }
    
    // Filter functionality
    $('#filter-yacht, #filter-category, #filter-status').on('change', function() {
        filterItems();
    });
    
    function filterItems() {
        const yachtFilter = $('#filter-yacht').val();
        const categoryFilter = $('#filter-category').val();
        const statusFilter = $('#filter-status').val();
        
        let filteredItems = allItems;
        
        if (yachtFilter) {
            filteredItems = filteredItems.filter(item => item.yacht_id == yachtFilter);
        }
        
        if (categoryFilter) {
            filteredItems = filteredItems.filter(item => item.category === categoryFilter);
        }
        
        if (statusFilter) {
            filteredItems = filteredItems.filter(item => {
                if (statusFilter === 'low_stock') return item.quantity < 10;
                if (statusFilter === 'expiring_soon') {
                    if (!item.expiry_date) return false;
                    const expiryDate = new Date(item.expiry_date);
                    const today = new Date();
                    const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
                    return daysUntilExpiry <= 7 && daysUntilExpiry >= 0;
                }
                if (statusFilter === 'in_stock') return item.quantity >= 10;
                return true;
            });
        }
        
        displayWarehouseItems(filteredItems);
    }
});
</script>
