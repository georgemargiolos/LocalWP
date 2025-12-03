<?php
/**
 * Base Manager - Warehouse Management Admin Page
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.9
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap yolo-base-manager-page">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="yolo-bm-content">
        <div class="card" style="background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04); margin-top: 20px;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h2 style="margin: 0;"><span class="dashicons dashicons-store"></span> Warehouse Management</h2>
                <button class="button button-primary" id="add-item-btn">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> Add Item
                </button>
            </div>
            <div class="card-body" style="padding: 20px;">
                <p>Track inventory, manage stock levels, and monitor expiry dates.</p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Expiry Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="warehouse-tbody">
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                                <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
                                <p>Loading warehouse items...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Item Modal -->
<div id="warehouse-modal" class="yolo-modal" style="display: none;">
    <div class="yolo-modal-content">
        <span class="yolo-modal-close">&times;</span>
        <h2 id="warehouse-modal-title">Add Warehouse Item</h2>
        <form id="warehouse-form">
            <input type="hidden" id="item-id" name="item_id" value="">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="item-name">Item Name *</label></th>
                    <td><input type="text" id="item-name" name="item_name" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="item-category">Category</label></th>
                    <td>
                        <select id="item-category" name="item_category" class="regular-text">
                            <option value="safety">Safety Equipment</option>
                            <option value="cleaning">Cleaning Supplies</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="food">Food & Beverages</option>
                            <option value="other">Other</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="item-quantity">Quantity *</label></th>
                    <td><input type="number" id="item-quantity" name="item_quantity" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="item-unit">Unit</label></th>
                    <td>
                        <select id="item-unit" name="item_unit" class="regular-text">
                            <option value="pcs">Pieces</option>
                            <option value="kg">Kilograms</option>
                            <option value="liters">Liters</option>
                            <option value="boxes">Boxes</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="item-expiry">Expiry Date</label></th>
                    <td><input type="date" id="item-expiry" name="item_expiry" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="item-notes">Notes</label></th>
                    <td><textarea id="item-notes" name="item_notes" class="large-text" rows="3"></textarea></td>
                </tr>
            </table>
            
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="button button-primary">Save Item</button>
                <button type="button" class="button" id="cancel-item-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.yolo-modal {
    position: fixed;
    z-index: 100000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0,0,0,0.5);
}

.yolo-modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 30px;
    border: 1px solid #888;
    border-radius: 4px;
    width: 90%;
    max-width: 700px;
    position: relative;
}

.yolo-modal-close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    line-height: 20px;
}

.yolo-modal-close:hover,
.yolo-modal-close:focus {
    color: #000;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load warehouse items
    loadWarehouseItems();
    
    // Add Item button
    $('#add-item-btn').on('click', function() {
        $('#warehouse-modal-title').text('Add Warehouse Item');
        $('#warehouse-form')[0].reset();
        $('#item-id').val('');
        $('#warehouse-modal').show();
    });
    
    // Close modal
    $('.yolo-modal-close, #cancel-item-btn').on('click', function() {
        $('#warehouse-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'warehouse-modal') {
            $('#warehouse-modal').hide();
        }
    });
    
    // Save warehouse item
    $('#warehouse-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            action: 'yolo_bm_save_warehouse_item',
            nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
            item_id: $('#item-id').val(),
            item_name: $('#item-name').val(),
            item_category: $('#item-category').val(),
            item_quantity: $('#item-quantity').val(),
            item_unit: $('#item-unit').val(),
            item_expiry: $('#item-expiry').val(),
            item_notes: $('#item-notes').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Item saved successfully!');
                    $('#warehouse-modal').hide();
                    loadWarehouseItems();
                } else {
                    alert('Error: ' + (response.data || 'Failed to save item'));
                }
            },
            error: function() {
                alert('Failed to save item. Please try again.');
            }
        });
    });
    
    // Load warehouse items function
    function loadWarehouseItems() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_warehouse_items',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    displayWarehouseItems(response.data);
                } else {
                    $('#warehouse-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 40px; color: #666;">No items in warehouse. Click "Add Item" to create one.</td></tr>');
                }
            },
            error: function() {
                $('#warehouse-tbody').html('<tr><td colspan="7" style="text-align: center; padding: 40px; color: #d63638;">Failed to load warehouse items. Please refresh the page.</td></tr>');
            }
        });
    }
    
    // Display warehouse items
    function displayWarehouseItems(items) {
        let html = '';
        items.forEach(function(item) {
            const status = item.quantity < 10 ? '<span style="color: #d63638;">Low Stock</span>' : '<span style="color: #00a32a;">In Stock</span>';
            html += `
                <tr>
                    <td><strong>${item.name}</strong></td>
                    <td>${item.category}</td>
                    <td>${item.quantity}</td>
                    <td>${item.unit}</td>
                    <td>${item.expiry_date || '-'}</td>
                    <td>${status}</td>
                    <td>
                        <button class="button button-small edit-item-btn" data-item-id="${item.id}">Edit</button>
                        <button class="button button-small button-link-delete delete-item-btn" data-item-id="${item.id}">Delete</button>
                    </td>
                </tr>
            `;
        });
        
        $('#warehouse-tbody').html(html);
        
        // Edit item
        $('.edit-item-btn').on('click', function() {
            const itemId = $(this).data('item-id');
            const item = items.find(i => i.id == itemId);
            if (item) {
                $('#warehouse-modal-title').text('Edit Warehouse Item');
                $('#item-id').val(item.id);
                $('#item-name').val(item.name);
                $('#item-category').val(item.category);
                $('#item-quantity').val(item.quantity);
                $('#item-unit').val(item.unit);
                $('#item-expiry').val(item.expiry_date || '');
                $('#item-notes').val(item.notes || '');
                $('#warehouse-modal').show();
            }
        });
        
        // Delete item
        $('.delete-item-btn').on('click', function() {
            if (!confirm('Are you sure you want to delete this item?')) {
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
                        alert('Item deleted successfully!');
                        loadWarehouseItems();
                    } else {
                        alert('Error: ' + (response.data || 'Failed to delete item'));
                    }
                },
                error: function() {
                    alert('Failed to delete item. Please try again.');
                }
            });
        });
    }
});
</script>
