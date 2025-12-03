<?php
/**
 * Base Manager - Check-Out Admin Page
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
                <h2 style="margin: 0;"><span class="dashicons dashicons-dismiss"></span> Check-Out Management</h2>
                <button class="button button-primary" id="new-checkout-btn">
                    <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span> New Check-Out
                </button>
            </div>
            <div class="card-body" style="padding: 20px;">
                
                <!-- Check-Out Form (Hidden by default) -->
                <div id="checkout-form-container" style="display: none; margin-bottom: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px;">
                    <h3>New Check-Out</h3>
                    <form id="checkout-form">
                        <table class="form-table">
                            <tr>
                                <th scope="row"><label for="checkout-booking-select">Select Booking *</label></th>
                                <td>
                                    <select id="checkout-booking-select" class="regular-text" required>
                                        <option value="">Choose booking...</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="checkout-yacht-select">Select Yacht *</label></th>
                                <td>
                                    <select id="checkout-yacht-select" class="regular-text" required>
                                        <option value="">Choose yacht...</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="checkout-damages">Damages/Issues</label></th>
                                <td>
                                    <textarea id="checkout-damages" class="large-text" rows="4" placeholder="Describe any damages or issues found..."></textarea>
                                </td>
                            </tr>
                        </table>
                        
                        <div id="checkout-checklist-container" style="margin: 20px 0;"></div>
                        
                        <h4>Base Manager Signature</h4>
                        <div class="signature-pad-container" style="border: 2px solid #ddd; border-radius: 4px; background: #fff; padding: 10px; margin-bottom: 20px;">
                            <canvas id="checkout-signature-pad" width="600" height="200" style="border: 1px solid #ccc; cursor: crosshair; display: block;"></canvas>
                            <button type="button" class="button" id="clear-checkout-signature" style="margin-top: 10px;">
                                <span class="dashicons dashicons-image-rotate" style="vertical-align: middle;"></span> Clear Signature
                            </button>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="button button-primary" id="complete-checkout-btn">
                                <span class="dashicons dashicons-yes" style="vertical-align: middle;"></span> Complete Check-Out
                            </button>
                            <button type="button" class="button button-primary" id="save-checkout-pdf-btn">
                                <span class="dashicons dashicons-pdf" style="vertical-align: middle;"></span> Save PDF
                            </button>
                            <button type="button" class="button" id="send-checkout-guest-btn">
                                <span class="dashicons dashicons-email" style="vertical-align: middle;"></span> Send to Guest
                            </button>
                            <button type="button" class="button" id="cancel-checkout-btn">Cancel</button>
                        </div>
                    </form>
                </div>
                
                <!-- Previous Check-Outs List -->
                <div id="checkout-list">
                    <h3>Previous Check-Outs</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Booking ID</th>
                                <th>Yacht</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="checkout-tbody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 40px; color: #666;">
                                    <span class="spinner is-active" style="float: none; margin: 0 auto;"></span>
                                    <p>Loading check-outs...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let checkoutSignaturePad;
    
    // Initialize
    loadYachts();
    loadBookings();
    loadCheckouts();
    
    // New Check-Out button
    $('#new-checkout-btn').on('click', function() {
        $('#checkout-form-container').slideDown();
        initializeSignaturePad();
    });
    
    // Cancel button
    $('#cancel-checkout-btn').on('click', function() {
        $('#checkout-form-container').slideUp();
        $('#checkout-form')[0].reset();
    });
    
    // Initialize signature pad
    function initializeSignaturePad() {
        if (typeof SignaturePad !== 'undefined') {
            const canvas = document.getElementById('checkout-signature-pad');
            if (canvas && !checkoutSignaturePad) {
                checkoutSignaturePad = new SignaturePad(canvas);
            }
        }
    }
    
    // Clear signature
    $('#clear-checkout-signature').on('click', function() {
        if (checkoutSignaturePad) {
            checkoutSignaturePad.clear();
        }
    });
    
    // Complete Check-Out
    $('#complete-checkout-btn').on('click', function() {
        if (!checkoutSignaturePad || checkoutSignaturePad.isEmpty()) {
            alert('Please provide a signature');
            return;
        }
        
        const formData = {
            action: 'yolo_bm_save_checkout',
            nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>',
            booking_id: $('#checkout-booking-select').val(),
            yacht_id: $('#checkout-yacht-select').val(),
            damages: $('#checkout-damages').val(),
            signature: checkoutSignaturePad.toDataURL()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('Check-out completed successfully!');
                    $('#checkout-form-container').slideUp();
                    $('#checkout-form')[0].reset();
                    checkoutSignaturePad.clear();
                    loadCheckouts();
                } else {
                    alert('Error: ' + (response.data || 'Failed to save check-out'));
                }
            },
            error: function() {
                alert('Failed to save check-out. Please try again.');
            }
        });
    });
    
    // Send to Guest
    $('#send-checkout-guest-btn').on('click', function() {
        alert('Send to Guest functionality - to be implemented');
    });
    
    // Load functions
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
                    let options = '<option value="">Choose yacht...</option>';
                    response.data.forEach(function(yacht) {
                        options += `<option value="${yacht.id}">${yacht.name}</option>`;
                    });
                    $('#checkout-yacht-select').html(options);
                }
            }
        });
    }
    
    function loadBookings() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_bookings_calendar',
                nonce: '<?php echo wp_create_nonce('yolo_base_manager_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    let options = '<option value="">Choose booking...</option>';
                    response.data.forEach(function(booking) {
                        options += `<option value="${booking.id}">BM-${booking.id} - ${booking.customer_name}</option>`;
                    });
                    $('#checkout-booking-select').html(options);
                }
            }
        });
    }
    
    function loadCheckouts() {
        // Placeholder - will load from database
        $('#checkout-tbody').html('<tr><td colspan="5" style="text-align: center; color: #666;">No check-outs yet</td></tr>');
    }
});
</script>
