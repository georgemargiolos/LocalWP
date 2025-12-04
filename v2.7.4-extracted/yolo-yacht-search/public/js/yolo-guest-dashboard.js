jQuery(document).ready(function($) {
    // License Upload Handler
    $('.yolo-license-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var bookingId = form.data('booking-id');
        var fileType = form.data('file-type');
        var fileInput = form.find('input[type="file"]')[0];
        var messageDiv = form.find('.yolo-upload-message');
        var submitBtn = form.find('button[type="submit"]');
        
        if (!fileInput.files || !fileInput.files[0]) {
            messageDiv.html('<span class="error">Please select a file</span>');
            return;
        }
        
        // Check if yolo_guest_vars is defined
        if (typeof yolo_guest_vars === 'undefined') {
            messageDiv.html('<span class="error">Configuration error. Please refresh the page and try again.</span>');
            console.error('yolo_guest_vars is not defined');
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
        if (!submitBtn.data('original-text')) {
            submitBtn.data('original-text', submitBtn.text());
        }
        
        submitBtn.prop('disabled', true).text('Uploading...');
        messageDiv.html('');
        
        $.ajax({
            url: yolo_guest_vars.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 30000, // 30 second timeout
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<span class="success">✓ ' + response.data.message + '</span>');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    messageDiv.html('<span class="error">✗ ' + response.data.message + '</span>');
                    submitBtn.prop('disabled', false).text(submitBtn.data('original-text'));
                }
            },
            error: function(xhr, status, error) {
                var errorMsg = 'Upload failed. ';
                if (status === 'timeout') {
                    errorMsg += 'Request timed out. Please try again.';
                } else {
                    errorMsg += 'Please try again.';
                }
                messageDiv.html('<span class="error">✗ ' + errorMsg + '</span>');
                submitBtn.prop('disabled', false).text(submitBtn.data('original-text'));
                console.error('Upload error:', status, error);
            }
        });
    });

    // Crew List Save Handler
    $('.yolo-crew-list-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var messageDiv = form.find('.yolo-crew-message');
        var submitBtn = form.find('.yolo-save-crew-btn');
        
        if (typeof yolo_guest_vars === 'undefined') {
            messageDiv.html('<span class="error">Configuration error. Please refresh the page.</span>');
            return;
        }
        
        var formData = form.serialize();
        
        submitBtn.prop('disabled', true).text('Saving...');
        messageDiv.html('');
        
        $.ajax({
            url: yolo_guest_vars.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 30000,
            success: function(response) {
                if (response.success) {
                    messageDiv.html('<span class="success">✓ Crew list saved successfully!</span>');
                } else {
                    messageDiv.html('<span class="error">✗ ' + response.data.message + '</span>');
                }
                submitBtn.prop('disabled', false).text('Save Crew List');
            },
            error: function(xhr, status, error) {
                messageDiv.html('<span class="error">✗ Save failed. Please try again.</span>');
                submitBtn.prop('disabled', false).text('Save Crew List');
            }
        });
    });
});
