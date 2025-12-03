<?php
/**
 * Base Manager - Yacht Management Admin Page
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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <h2 style="margin: 0;"><i class="dashicons dashicons-admin-multisite"></i> Yacht Management</h2>
                <button class="button button-primary" id="add-yacht-btn">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> Add Yacht
                </button>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div id="yachts-list" class="yachts-grid">
                    <div style="text-align: center; padding: 40px;">
                        <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
                        <p>Loading yachts...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Yacht Modal -->
<div id="yacht-modal" class="yolo-modal" style="display: none;">
    <div class="yolo-modal-content">
        <span class="yolo-modal-close">&times;</span>
        <h2 id="yacht-modal-title">Add New Yacht</h2>
        <form id="yacht-form">
            <input type="hidden" id="yacht-id" name="yacht_id" value="">
            
            <div class="form-group">
                <label for="yacht-name">Yacht Name *</label>
                <input type="text" id="yacht-name" name="yacht_name" class="regular-text" required>
            </div>
            
            <div class="form-group">
                <label for="yacht-model">Model</label>
                <input type="text" id="yacht-model" name="yacht_model" class="regular-text">
            </div>
            
            <h3>Owner Information</h3>
            
            <div class="form-group">
                <label for="owner-name">Owner First Name *</label>
                <input type="text" id="owner-name" name="owner_name" class="regular-text" required>
            </div>
            
            <div class="form-group">
                <label for="owner-surname">Owner Last Name *</label>
                <input type="text" id="owner-surname" name="owner_surname" class="regular-text" required>
            </div>
            
            <div class="form-group">
                <label for="owner-mobile">Owner Mobile *</label>
                <input type="tel" id="owner-mobile" name="owner_mobile" class="regular-text" required>
            </div>
            
            <div class="form-group">
                <label for="owner-email">Owner Email *</label>
                <input type="email" id="owner-email" name="owner_email" class="regular-text" required>
            </div>
            
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="button button-primary">Save Yacht</button>
                <button type="button" class="button" id="cancel-yacht-btn">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.yolo-bm-content {
    margin-top: 20px;
}

.yachts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.yacht-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    background: #fff;
    transition: box-shadow 0.3s;
}

.yacht-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.yacht-card h3 {
    margin-top: 0;
    color: #2271b1;
}

.yacht-card .yacht-info {
    margin: 10px 0;
}

.yacht-card .yacht-info p {
    margin: 5px 0;
    color: #666;
}

.yacht-card .yacht-actions {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

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
    max-width: 600px;
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

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Load yachts on page load
    loadYachts();
    
    // Add Yacht button
    $('#add-yacht-btn').on('click', function() {
        $('#yacht-modal-title').text('Add New Yacht');
        $('#yacht-form')[0].reset();
        $('#yacht-id').val('');
        $('#yacht-modal').show();
    });
    
    // Close modal
    $('.yolo-modal-close, #cancel-yacht-btn').on('click', function() {
        $('#yacht-modal').hide();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(event) {
        if (event.target.id === 'yacht-modal') {
            $('#yacht-modal').hide();
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
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data && response.data.yachts) {
                    displayYachts(response.data.yachts);
                } else {
                    $('#yachts-list').html('<p style="text-align: center; padding: 40px; color: #666;">No yachts found. Click "Add Yacht" to create one.</p>');
                }
            },
            error: function() {
                $('#yachts-list').html('<p style="text-align: center; padding: 40px; color: #d63638;">Failed to load yachts. Please refresh the page.</p>');
            }
        });
    }
    
    // Display yachts function
    function displayYachts(yachts) {
        if (!yachts || yachts.length === 0) {
            $('#yachts-list').html('<p style="text-align: center; padding: 40px; color: #666;">No yachts found. Click "Add Yacht" to create one.</p>');
            return;
        }
        
        let html = '';
        yachts.forEach(function(yacht) {
            html += `
                <div class="yacht-card">
                    <h3>${yacht.yacht_name}</h3>
                    <div class="yacht-info">
                        ${yacht.yacht_model ? `<p><strong>Model:</strong> ${yacht.yacht_model}</p>` : ''}
                        <p><strong>Owner:</strong> ${yacht.owner_name} ${yacht.owner_surname}</p>
                        <p><strong>Contact:</strong> ${yacht.owner_email}</p>
                        <p><strong>Mobile:</strong> ${yacht.owner_mobile}</p>
                    </div>
                    <div class="yacht-actions">
                        <button class="button button-small edit-yacht-btn" data-yacht-id="${yacht.id}">Edit</button>
                        <button class="button button-small button-link-delete delete-yacht-btn" data-yacht-id="${yacht.id}">Delete</button>
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
                $('#yacht-modal-title').text('Edit Yacht');
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
        
        // Delete yacht
        $('.delete-yacht-btn').on('click', function() {
            if (!confirm('Are you sure you want to delete this yacht?')) {
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
});
</script>
