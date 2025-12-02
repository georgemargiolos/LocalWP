/**
 * Base Manager Dashboard JavaScript
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.0
 */

(function($) {
    'use strict';

    // Global variables
    let checkinSignaturePad, checkoutSignaturePad;
    let currentYachtId = null;
    let currentBookingId = null;

    $(document).ready(function() {
        // Initialize
        initializeSignaturePads();
        loadBookingsCalendar();
        loadYachts();
        
        // Event listeners
        $('#add-yacht-btn').on('click', showYachtModal);
        $('#save-yacht-btn').on('click', saveYacht);
        $('#add-equipment-item-btn').on('click', addEquipmentItemRow);
        $('#save-equipment-category-btn').on('click', saveEquipmentCategory);
        
        // Check-in events
        $('#new-checkin-btn').on('click', showCheckinForm);
        $('#cancel-checkin-btn').on('click', hideCheckinForm);
        $('#clear-checkin-signature').on('click', () => checkinSignaturePad.clear());
        $('#complete-checkin-btn').on('click', completeCheckin);
        $('#save-checkin-pdf-btn').on('click', () => generatePDF('checkin'));
        $('#send-checkin-guest-btn').on('click', () => sendToGuest('checkin'));
        $('#checkin-yacht-select').on('change', loadCheckinChecklist);
        
        // Check-out events
        $('#new-checkout-btn').on('click', showCheckoutForm);
        $('#cancel-checkout-btn').on('click', hideCheckoutForm);
        $('#clear-checkout-signature').on('click', () => checkoutSignaturePad.clear());
        $('#complete-checkout-btn').on('click', completeCheckout);
        $('#save-checkout-pdf-btn').on('click', () => generatePDF('checkout'));
        $('#send-checkout-guest-btn').on('click', () => sendToGuest('checkout'));
        $('#checkout-yacht-select').on('change', loadCheckoutChecklist);
        
        // Warehouse events
        $('#warehouse-yacht-select').on('change', loadWarehouseItems);
        $('#add-warehouse-item-btn').on('click', showWarehouseItemModal);
        $('#save-warehouse-item-btn').on('click', saveWarehouseItem);
        $('#increase-quantity').on('click', () => adjustQuantity(1));
        $('#decrease-quantity').on('click', () => adjustQuantity(-1));
        
        // Tab change events
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const target = $(e.target).data('bs-target');
            if (target === '#yachts') {
                loadYachts();
            } else if (target === '#warehouse') {
                loadYachtsForWarehouse();
            } else if (target === '#checkin') {
                loadBookingsForCheckin();
                loadYachtsForCheckin();
            } else if (target === '#checkout') {
                loadBookingsForCheckout();
                loadYachtsForCheckout();
            }
        });
    });

    /**
     * Initialize signature pads
     */
    function initializeSignaturePads() {
        const checkinCanvas = document.getElementById('checkin-signature-pad');
        const checkoutCanvas = document.getElementById('checkout-signature-pad');
        
        if (checkinCanvas && typeof SignaturePad !== 'undefined') {
            checkinSignaturePad = new SignaturePad(checkinCanvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            resizeCanvas(checkinCanvas);
        }
        
        if (checkoutCanvas && typeof SignaturePad !== 'undefined') {
            checkoutSignaturePad = new SignaturePad(checkoutCanvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            resizeCanvas(checkoutCanvas);
        }
    }

    /**
     * Resize canvas to fit container
     */
    function resizeCanvas(canvas) {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
    }

    /**
     * Load bookings calendar
     */
    function loadBookingsCalendar() {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_bookings_calendar',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderBookingsTable(response.data.bookings);
                } else {
                    showAlert('danger', 'Failed to load bookings');
                }
            },
            error: function() {
                showAlert('danger', 'Error loading bookings');
            }
        });
    }

    /**
     * Render bookings table
     */
    function renderBookingsTable(bookings) {
        const tbody = $('#bookings-tbody');
        tbody.empty();
        
        if (bookings.length === 0) {
            tbody.append('<tr><td colspan="7" class="text-center text-muted">No bookings found</td></tr>');
            return;
        }
        
        bookings.forEach(booking => {
            const row = `
                <tr>
                    <td>${booking.id}</td>
                    <td>${booking.customer_name || 'N/A'}</td>
                    <td>${booking.yacht_name || 'N/A'}</td>
                    <td>${formatDate(booking.check_in_date)}</td>
                    <td>${formatDate(booking.check_out_date)}</td>
                    <td><span class="badge badge-${getStatusClass(booking.status)}">${booking.status}</span></td>
                    <td>${formatCurrency(booking.total_amount)}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    /**
     * Load yachts
     */
    function loadYachts() {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderYachtsList(response.data.yachts);
                } else {
                    showAlert('danger', 'Failed to load yachts');
                }
            },
            error: function() {
                showAlert('danger', 'Error loading yachts');
            }
        });
    }

    /**
     * Render yachts list
     */
    function renderYachtsList(yachts) {
        const container = $('#yachts-list');
        container.empty();
        
        if (yachts.length === 0) {
            container.append('<div class="col-12 text-center text-muted">No yachts found. Click "Add Yacht" to create one.</div>');
            return;
        }
        
        yachts.forEach(yacht => {
            const card = `
                <div class="col-md-6 col-lg-4">
                    <div class="yacht-card">
                        <div class="yacht-card-header">
                            <div>
                                <h4 class="yacht-card-title">${yacht.yacht_name}</h4>
                                <p class="yacht-card-model">${yacht.yacht_model}</p>
                            </div>
                        </div>
                        <div class="yacht-card-body">
                            <div class="yacht-card-info">
                                <div class="yacht-card-info-item">
                                    <i class="fas fa-user"></i>
                                    ${yacht.owner_name} ${yacht.owner_surname}
                                </div>
                                <div class="yacht-card-info-item">
                                    <i class="fas fa-phone"></i>
                                    ${yacht.owner_mobile}
                                </div>
                                <div class="yacht-card-info-item">
                                    <i class="fas fa-envelope"></i>
                                    ${yacht.owner_email}
                                </div>
                            </div>
                            <div class="yacht-card-actions">
                                <button class="btn btn-sm btn-primary" onclick="editYacht(${yacht.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-info" onclick="manageEquipment(${yacht.id})">
                                    <i class="fas fa-tools"></i> Equipment
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deleteYacht(${yacht.id})">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.append(card);
        });
    }

    /**
     * Show yacht modal
     */
    function showYachtModal() {
        $('#yacht-form')[0].reset();
        $('#yacht-id').val('');
        $('#company-logo-preview').empty();
        $('#boat-logo-preview').empty();
        const modal = new bootstrap.Modal(document.getElementById('yachtModal'));
        modal.show();
    }

    /**
     * Save yacht
     */
    function saveYacht() {
        const form = $('#yacht-form')[0];
        const formData = new FormData(form);
        formData.append('action', 'yolo_bm_save_yacht');
        formData.append('nonce', yoloBaseManager.nonce);
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.data.message);
                    bootstrap.Modal.getInstance(document.getElementById('yachtModal')).hide();
                    loadYachts();
                } else {
                    showAlert('danger', response.data.message);
                }
            },
            error: function() {
                showAlert('danger', 'Error saving yacht');
            }
        });
    }

    /**
     * Edit yacht
     */
    window.editYacht = function(yachtId) {
        // Load yacht data and populate form
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    const yacht = response.data.yachts.find(y => y.id == yachtId);
                    if (yacht) {
                        $('#yacht-id').val(yacht.id);
                        $('#yacht-name').val(yacht.yacht_name);
                        $('#yacht-model').val(yacht.yacht_model);
                        $('#owner-name').val(yacht.owner_name);
                        $('#owner-surname').val(yacht.owner_surname);
                        $('#owner-mobile').val(yacht.owner_mobile);
                        $('#owner-email').val(yacht.owner_email);
                        
                        if (yacht.company_logo) {
                            $('#company-logo-preview').html(`<img src="${yacht.company_logo}" class="image-preview">`);
                        }
                        if (yacht.boat_logo) {
                            $('#boat-logo-preview').html(`<img src="${yacht.boat_logo}" class="image-preview">`);
                        }
                        
                        const modal = new bootstrap.Modal(document.getElementById('yachtModal'));
                        modal.show();
                    }
                }
            }
        });
    };

    /**
     * Delete yacht
     */
    window.deleteYacht = function(yachtId) {
        if (!confirm('Are you sure you want to delete this yacht?')) {
            return;
        }
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_delete_yacht',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.data.message);
                    loadYachts();
                } else {
                    showAlert('danger', response.data.message);
                }
            },
            error: function() {
                showAlert('danger', 'Error deleting yacht');
            }
        });
    };

    /**
     * Manage equipment
     */
    window.manageEquipment = function(yachtId) {
        currentYachtId = yachtId;
        loadEquipmentCategories(yachtId);
        const modal = new bootstrap.Modal(document.getElementById('equipmentModal'));
        modal.show();
    };

    /**
     * Load equipment categories
     */
    function loadEquipmentCategories(yachtId) {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_equipment_categories',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success) {
                    renderEquipmentCategories(response.data.categories);
                }
            }
        });
    }

    /**
     * Render equipment categories
     */
    function renderEquipmentCategories(categories) {
        // Implementation for displaying categories
        $('#equipment-items-container').empty();
        addEquipmentItemRow();
    }

    /**
     * Add equipment item row
     */
    function addEquipmentItemRow() {
        const row = `
            <div class="equipment-item">
                <input type="text" class="form-control" placeholder="Item name" name="item_name[]">
                <input type="number" class="form-control" placeholder="Qty" name="item_quantity[]" min="0" value="1">
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-danger remove-equipment-item">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        $('#equipment-items-container').append(row);
        
        $('.remove-equipment-item').off('click').on('click', function() {
            $(this).closest('.equipment-item').remove();
        });
    }

    /**
     * Save equipment category
     */
    function saveEquipmentCategory() {
        const categoryName = $('#category-name').val();
        const items = [];
        
        $('.equipment-item').each(function() {
            const name = $(this).find('input[name="item_name[]"]').val();
            const quantity = $(this).find('input[name="item_quantity[]"]').val();
            if (name) {
                items.push({ name: name, quantity: parseInt(quantity) || 0 });
            }
        });
        
        if (!categoryName || items.length === 0) {
            showAlert('warning', 'Please enter category name and at least one item');
            return;
        }
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_save_equipment_category',
                nonce: yoloBaseManager.nonce,
                yacht_id: currentYachtId,
                category_name: categoryName,
                items: JSON.stringify(items)
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.data.message);
                    $('#category-name').val('');
                    $('#equipment-items-container').empty();
                    addEquipmentItemRow();
                } else {
                    showAlert('danger', response.data.message);
                }
            }
        });
    }

    // Continue in next part...

})(jQuery);

    /**
     * Show check-in form
     */
    function showCheckinForm() {
        $('#checkin-form-container').slideDown();
        loadBookingsForCheckin();
        loadYachtsForCheckin();
    }

    /**
     * Hide check-in form
     */
    function hideCheckinForm() {
        $('#checkin-form-container').slideUp();
        $('#checkin-form')[0].reset();
        checkinSignaturePad.clear();
    }

    /**
     * Load bookings for check-in
     */
    function loadBookingsForCheckin() {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_bookings_calendar',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#checkin-booking-select');
                    select.empty().append('<option value="">Choose booking...</option>');
                    response.data.bookings.forEach(booking => {
                        select.append(`<option value="${booking.id}">${booking.id} - ${booking.customer_name} (${formatDate(booking.check_in_date)})</option>`);
                    });
                }
            }
        });
    }

    /**
     * Load yachts for check-in
     */
    function loadYachtsForCheckin() {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#checkin-yacht-select');
                    select.empty().append('<option value="">Choose yacht...</option>');
                    response.data.yachts.forEach(yacht => {
                        select.append(`<option value="${yacht.id}">${yacht.yacht_name} - ${yacht.yacht_model}</option>`);
                    });
                }
            }
        });
    }

    /**
     * Load check-in checklist
     */
    function loadCheckinChecklist() {
        const yachtId = $('#checkin-yacht-select').val();
        if (!yachtId) return;
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_equipment_categories',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success) {
                    renderChecklist(response.data.categories, 'checkin');
                }
            }
        });
    }

    /**
     * Render checklist
     */
    function renderChecklist(categories, type) {
        const container = $(`#${type}-checklist-container`);
        container.empty();
        
        if (categories.length === 0) {
            container.append('<div class="alert alert-info">No equipment categories found for this yacht. Please add equipment categories first.</div>');
            return;
        }
        
        categories.forEach(category => {
            let categoryHtml = `
                <div class="checklist-category">
                    <h5 class="checklist-category-title">${category.category_name}</h5>
            `;
            
            if (category.items && category.items.length > 0) {
                category.items.forEach((item, index) => {
                    categoryHtml += `
                        <div class="checklist-item" data-category="${category.id}" data-item="${index}">
                            <input type="checkbox" class="checklist-checkbox" id="${type}-item-${category.id}-${index}">
                            <label for="${type}-item-${category.id}-${index}" class="checklist-item-name">${item.name}</label>
                            <div class="checklist-item-quantity">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustChecklistQuantity('${type}', ${category.id}, ${index}, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control form-control-sm" value="${item.quantity}" min="0" id="${type}-qty-${category.id}-${index}">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="adjustChecklistQuantity('${type}', ${category.id}, ${index}, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    `;
                });
            }
            
            categoryHtml += '</div>';
            container.append(categoryHtml);
        });
        
        // Add checkbox change handler
        $('.checklist-checkbox').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('.checklist-item').addClass('completed');
            } else {
                $(this).closest('.checklist-item').removeClass('completed');
            }
        });
    }

    /**
     * Adjust checklist quantity
     */
    window.adjustChecklistQuantity = function(type, categoryId, itemIndex, delta) {
        const input = $(`#${type}-qty-${categoryId}-${itemIndex}`);
        const currentValue = parseInt(input.val()) || 0;
        const newValue = Math.max(0, currentValue + delta);
        input.val(newValue);
    };

    /**
     * Complete check-in
     */
    function completeCheckin() {
        const bookingId = $('#checkin-booking-select').val();
        const yachtId = $('#checkin-yacht-select').val();
        
        if (!bookingId || !yachtId) {
            showAlert('warning', 'Please select booking and yacht');
            return;
        }
        
        if (checkinSignaturePad.isEmpty()) {
            showAlert('warning', 'Please provide signature');
            return;
        }
        
        const checklistData = collectChecklistData('checkin');
        const signature = checkinSignaturePad.toDataURL();
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_save_checkin',
                nonce: yoloBaseManager.nonce,
                booking_id: bookingId,
                yacht_id: yachtId,
                checklist_data: JSON.stringify(checklistData),
                signature: signature,
                status: 'completed'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Check-in completed successfully');
                    hideCheckinForm();
                } else {
                    showAlert('danger', response.data.message);
                }
            },
            error: function() {
                showAlert('danger', 'Error completing check-in');
            }
        });
    }

    /**
     * Collect checklist data
     */
    function collectChecklistData(type) {
        const data = [];
        $(`.checklist-item`).each(function() {
            const categoryId = $(this).data('category');
            const itemIndex = $(this).data('item');
            const checked = $(this).find('.checklist-checkbox').is(':checked');
            const quantity = $(this).find('input[type="number"]').val();
            const name = $(this).find('.checklist-item-name').text();
            
            data.push({
                category: categoryId,
                item: itemIndex,
                name: name,
                checked: checked,
                quantity: parseInt(quantity) || 0
            });
        });
        return data;
    }

    /**
     * Generate PDF
     */
    function generatePDF(type) {
        const recordId = type === 'checkin' ? currentCheckinId : currentCheckoutId;
        
        if (!recordId) {
            showAlert('warning', 'Please complete the ' + type + ' first');
            return;
        }
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_generate_pdf',
                nonce: yoloBaseManager.nonce,
                type: type,
                record_id: recordId
            },
            success: function(response) {
                if (response.success) {
                    window.open(response.data.pdf_url, '_blank');
                } else {
                    showAlert('danger', 'Failed to generate PDF');
                }
            }
        });
    }

    /**
     * Send to guest
     */
    function sendToGuest(type) {
        const bookingId = type === 'checkin' ? $('#checkin-booking-select').val() : $('#checkout-booking-select').val();
        const recordId = type === 'checkin' ? currentCheckinId : currentCheckoutId;
        
        if (!recordId) {
            showAlert('warning', 'Please complete the ' + type + ' first');
            return;
        }
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_send_to_guest',
                nonce: yoloBaseManager.nonce,
                type: type,
                record_id: recordId,
                booking_id: bookingId
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Document sent to guest successfully');
                } else {
                    showAlert('danger', response.data.message);
                }
            }
        });
    }

    /**
     * Show check-out form
     */
    function showCheckoutForm() {
        $('#checkout-form-container').slideDown();
        loadBookingsForCheckout();
        loadYachtsForCheckout();
    }

    /**
     * Hide check-out form
     */
    function hideCheckoutForm() {
        $('#checkout-form-container').slideUp();
        $('#checkout-form')[0].reset();
        checkoutSignaturePad.clear();
    }

    /**
     * Load bookings for check-out
     */
    function loadBookingsForCheckout() {
        loadBookingsForCheckin(); // Same logic
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_bookings_calendar',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#checkout-booking-select');
                    select.empty().append('<option value="">Choose booking...</option>');
                    response.data.bookings.forEach(booking => {
                        select.append(`<option value="${booking.id}">${booking.id} - ${booking.customer_name} (${formatDate(booking.check_out_date)})</option>`);
                    });
                }
            }
        });
    }

    /**
     * Load yachts for check-out
     */
    function loadYachtsForCheckout() {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#checkout-yacht-select');
                    select.empty().append('<option value="">Choose yacht...</option>');
                    response.data.yachts.forEach(yacht => {
                        select.append(`<option value="${yacht.id}">${yacht.yacht_name} - ${yacht.yacht_model}</option>`);
                    });
                }
            }
        });
    }

    /**
     * Load check-out checklist
     */
    function loadCheckoutChecklist() {
        const yachtId = $('#checkout-yacht-select').val();
        if (!yachtId) return;
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_equipment_categories',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success) {
                    renderChecklist(response.data.categories, 'checkout');
                }
            }
        });
    }

    /**
     * Complete check-out
     */
    function completeCheckout() {
        const bookingId = $('#checkout-booking-select').val();
        const yachtId = $('#checkout-yacht-select').val();
        
        if (!bookingId || !yachtId) {
            showAlert('warning', 'Please select booking and yacht');
            return;
        }
        
        if (checkoutSignaturePad.isEmpty()) {
            showAlert('warning', 'Please provide signature');
            return;
        }
        
        const checklistData = collectChecklistData('checkout');
        const signature = checkoutSignaturePad.toDataURL();
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_save_checkout',
                nonce: yoloBaseManager.nonce,
                booking_id: bookingId,
                yacht_id: yachtId,
                checklist_data: JSON.stringify(checklistData),
                signature: signature,
                status: 'completed'
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', 'Check-out completed successfully');
                    hideCheckoutForm();
                } else {
                    showAlert('danger', response.data.message);
                }
            },
            error: function() {
                showAlert('danger', 'Error completing check-out');
            }
        });
    }

    /**
     * Load yachts for warehouse
     */
    function loadYachtsForWarehouse() {
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_yachts',
                nonce: yoloBaseManager.nonce
            },
            success: function(response) {
                if (response.success) {
                    const select = $('#warehouse-yacht-select');
                    select.empty().append('<option value="">Choose yacht...</option>');
                    response.data.yachts.forEach(yacht => {
                        select.append(`<option value="${yacht.id}">${yacht.yacht_name} - ${yacht.yacht_model}</option>`);
                    });
                }
            }
        });
    }

    /**
     * Load warehouse items
     */
    function loadWarehouseItems() {
        const yachtId = $('#warehouse-yacht-select').val();
        if (!yachtId) {
            $('#warehouse-tbody').html('<tr><td colspan="5" class="text-center text-muted">Select a yacht to view inventory</td></tr>');
            return;
        }
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_warehouse_items',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success) {
                    renderWarehouseItems(response.data.items);
                }
            }
        });
    }

    /**
     * Render warehouse items
     */
    function renderWarehouseItems(items) {
        const tbody = $('#warehouse-tbody');
        tbody.empty();
        
        if (items.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center text-muted">No items found</td></tr>');
            return;
        }
        
        items.forEach(item => {
            const expiryClass = isExpiringSoon(item.expiry_date) ? 'text-danger' : '';
            const row = `
                <tr>
                    <td>${item.item_name}</td>
                    <td>${item.quantity}</td>
                    <td class="${expiryClass}">${item.expiry_date ? formatDate(item.expiry_date) : 'N/A'}</td>
                    <td>${item.location || 'N/A'}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="editWarehouseItem(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteWarehouseItem(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    /**
     * Show warehouse item modal
     */
    function showWarehouseItemModal() {
        const yachtId = $('#warehouse-yacht-select').val();
        if (!yachtId) {
            showAlert('warning', 'Please select a yacht first');
            return;
        }
        
        $('#warehouse-item-form')[0].reset();
        $('#warehouse-item-id').val('');
        $('#warehouse-item-yacht-id').val(yachtId);
        $('#warehouse-item-quantity').val(0);
        
        const modal = new bootstrap.Modal(document.getElementById('warehouseItemModal'));
        modal.show();
    }

    /**
     * Save warehouse item
     */
    function saveWarehouseItem() {
        const itemId = $('#warehouse-item-id').val();
        const yachtId = $('#warehouse-item-yacht-id').val();
        const itemName = $('#warehouse-item-name').val();
        const quantity = $('#warehouse-item-quantity').val();
        const expiryDate = $('#warehouse-item-expiry').val();
        const location = $('#warehouse-item-location').val();
        
        if (!itemName) {
            showAlert('warning', 'Please enter item name');
            return;
        }
        
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_save_warehouse_item',
                nonce: yoloBaseManager.nonce,
                item_id: itemId,
                yacht_id: yachtId,
                item_name: itemName,
                quantity: quantity,
                expiry_date: expiryDate,
                location: location
            },
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.data.message);
                    bootstrap.Modal.getInstance(document.getElementById('warehouseItemModal')).hide();
                    loadWarehouseItems();
                } else {
                    showAlert('danger', response.data.message);
                }
            }
        });
    }

    /**
     * Adjust quantity
     */
    function adjustQuantity(delta) {
        const input = $('#warehouse-item-quantity');
        const currentValue = parseInt(input.val()) || 0;
        const newValue = Math.max(0, currentValue + delta);
        input.val(newValue);
    }

    /**
     * Edit warehouse item
     */
    window.editWarehouseItem = function(itemId) {
        // Load item data and show modal
        const yachtId = $('#warehouse-yacht-select').val();
        $.ajax({
            url: yoloBaseManager.ajaxurl,
            type: 'POST',
            data: {
                action: 'yolo_bm_get_warehouse_items',
                nonce: yoloBaseManager.nonce,
                yacht_id: yachtId
            },
            success: function(response) {
                if (response.success) {
                    const item = response.data.items.find(i => i.id == itemId);
                    if (item) {
                        $('#warehouse-item-id').val(item.id);
                        $('#warehouse-item-yacht-id').val(item.yacht_id);
                        $('#warehouse-item-name').val(item.item_name);
                        $('#warehouse-item-quantity').val(item.quantity);
                        $('#warehouse-item-expiry').val(item.expiry_date);
                        $('#warehouse-item-location').val(item.location);
                        
                        const modal = new bootstrap.Modal(document.getElementById('warehouseItemModal'));
                        modal.show();
                    }
                }
            }
        });
    };

    /**
     * Delete warehouse item
     */
    window.deleteWarehouseItem = function(itemId) {
        if (!confirm('Are you sure you want to delete this item?')) {
            return;
        }
        
        // Implementation for delete
        showAlert('info', 'Delete functionality to be implemented');
    };

    /**
     * Utility functions
     */
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }

    function formatCurrency(amount) {
        if (!amount) return '€0.00';
        return '€' + parseFloat(amount).toFixed(2);
    }

    function getStatusClass(status) {
        const statusMap = {
            'completed': 'completed',
            'pending': 'pending',
            'draft': 'draft',
            'signed': 'signed'
        };
        return statusMap[status] || 'draft';
    }

    function isExpiringSoon(dateString) {
        if (!dateString) return false;
        const expiryDate = new Date(dateString);
        const today = new Date();
        const daysUntilExpiry = Math.ceil((expiryDate - today) / (1000 * 60 * 60 * 24));
        return daysUntilExpiry <= 30 && daysUntilExpiry >= 0;
    }

    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Insert at top of active tab
        $('.tab-pane.active .card-body').prepend(alertHtml);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

})(jQuery);
