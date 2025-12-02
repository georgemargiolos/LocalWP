/**
 * YOLO Guest Dashboard JavaScript
 * Handles license uploads and crew list saving
 * v2.7.5
 */
jQuery(document).ready(function($) {
    
    // Check if yolo_guest_vars exists
    if (typeof yolo_guest_vars === 'undefined') {
        console.error('YOLO Guest Dashboard: yolo_guest_vars not defined!');
        return;
    }
    
    console.log('YOLO Guest Dashboard: Initialized');
    console.log('YOLO Guest Dashboard: AJAX URL =', yolo_guest_vars.ajax_url);
    
    // License Upload Handler
    $('.yolo-license-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var bookingId = form.data('booking-id');
        var fileType = form.data('file-type');
        var fileInput = form.find('input[type="file"]')[0];
        var messageDiv = form.find('.yolo-upload-message');
        var submitBtn = form.find('button[type="submit"]');
        
        // Clear previous messages
        messageDiv.html('');
        
        if (!fileInput.files || !fileInput.files[0]) {
            messageDiv.html('<span class="error">⚠️ Please select a file first</span>');
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'yolo_upload_license');
        formData.append('nonce', yolo_guest_vars.nonce);
        formData.append('_wpnonce', yolo_guest_vars.nonce);
        formData.append('booking_id', bookingId);
        formData.append('file_type', fileType);
        formData.append('license_file', fileInput.files[0]);
        
        // Store original button text
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<span class="spinner"></span> Uploading...');
        
        $.ajax({
            url: yolo_guest_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 60000,
            success: function(response) {
                console.log('Upload response:', response);
                if (response.success) {
                    messageDiv.html('<span class="success">✓ ' + response.data.message + '</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : 'Upload failed';
                    messageDiv.html('<span class="error">✗ ' + errorMsg + '</span>');
                    submitBtn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Upload error:', status, error);
                var errorMsg = 'Upload failed. ';
                if (status === 'timeout') {
                    errorMsg += 'Request timed out.';
                } else {
                    errorMsg += 'Please try again.';
                }
                messageDiv.html('<span class="error">✗ ' + errorMsg + '</span>');
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Crew List Save Handler
    $('.yolo-crew-list-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var messageDiv = form.find('.yolo-crew-message');
        var submitBtn = form.find('.yolo-save-crew-btn');
        
        var formData = form.serialize();
        var originalText = submitBtn.text();
        
        submitBtn.prop('disabled', true).html('<span class="spinner"></span> Saving...');
        messageDiv.html('');
        
        $.ajax({
            url: yolo_guest_vars.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 30000,
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<span class="success">✓ ' + (response.data.message || 'Crew list saved!') + '</span>');
                } else {
                    var errorMsg = response.data && response.data.message ? response.data.message : 'Save failed';
                    messageDiv.html('<span class="error">✗ ' + errorMsg + '</span>');
                }
                submitBtn.prop('disabled', false).text(originalText);
            },
            error: function(xhr, status, error) {
                messageDiv.html('<span class="error">✗ Save failed. Please try again.</span>');
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Accordion toggle for crew list
    $('.yolo-section-toggle').on('click', function() {
        var section = $(this).closest('.yolo-accordion-section');
        section.toggleClass('open');
        section.find('.yolo-section-content').slideToggle(300);
    });
});
