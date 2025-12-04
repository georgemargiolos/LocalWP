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
        // Nonce removed - security handled by login check and booking ownership verification
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
    // Only toggle class - CSS handles visibility via .open class
    // Don't use slideToggle as it conflicts with CSS display rules
    $('.yolo-section-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var section = $(this).closest('.yolo-accordion-section');
        section.toggleClass('open');
    });
});

/**
 * Guest Signature Functionality for Check-In/Check-Out Documents
 * @since 17.0
 */

(function($) {
    'use strict';
    
    let guestSignaturePad;
    let currentDocumentId;
    let currentDocumentType;
    
    $(document).ready(function() {
        // Initialize signature modal
        initializeSignatureModal();
        
        // Sign document button click
        $(document).on('click', '.yolo-sign-doc-btn', function() {
            currentDocumentId = $(this).data('checkin-id') || $(this).data('checkout-id');
            currentDocumentType = $(this).data('type');
            showSignatureModal();
        });
        
        // Clear signature
        $('.yolo-clear-signature-btn').on('click', function() {
            if (guestSignaturePad) {
                guestSignaturePad.clear();
            }
        });
        
        // Cancel signature
        $('.yolo-cancel-signature-btn, .yolo-signature-modal-close').on('click', function() {
            hideSignatureModal();
        });
        
        // Submit signature
        $('.yolo-submit-signature-btn').on('click', function() {
            submitGuestSignature();
        });
        
        // Close modal on outside click
        $('#signatureModal').on('click', function(e) {
            if ($(e.target).is('#signatureModal')) {
                hideSignatureModal();
            }
        });
    });
    
    /**
     * Initialize signature modal and pad
     */
    function initializeSignatureModal() {
        const canvas = document.getElementById('guestSignaturePad');
        
        if (canvas && typeof SignaturePad !== 'undefined') {
            guestSignaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)',
                minWidth: 1,
                maxWidth: 3
            });
            
            // Resize canvas to fit container
            resizeCanvas(canvas);
            
            // Resize on window resize
            $(window).on('resize', function() {
                resizeCanvas(canvas);
            });
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
        
        if (guestSignaturePad) {
            guestSignaturePad.clear();
        }
    }
    
    /**
     * Show signature modal
     */
    function showSignatureModal() {
        $('#signatureModal').fadeIn(300);
        $('body').css('overflow', 'hidden');
        
        if (guestSignaturePad) {
            guestSignaturePad.clear();
        }
    }
    
    /**
     * Hide signature modal
     */
    function hideSignatureModal() {
        $('#signatureModal').fadeOut(300);
        $('body').css('overflow', '');
        
        if (guestSignaturePad) {
            guestSignaturePad.clear();
        }
        
        currentDocumentId = null;
        currentDocumentType = null;
    }
    
    /**
     * Submit guest signature
     */
    function submitGuestSignature() {
        if (!guestSignaturePad || guestSignaturePad.isEmpty()) {
            alert('Please provide your signature before submitting.');
            return;
        }
        
        if (!currentDocumentId || !currentDocumentType) {
            alert('Invalid document. Please try again.');
            return;
        }
        
        const signatureData = guestSignaturePad.toDataURL();
        
        // Show loading state
        $('.yolo-submit-signature-btn').prop('disabled', true).text('Submitting...');
        
        $.ajax({
            url: yolo_guest_vars.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_guest_sign_document',
                nonce: yolo_guest_vars.guest_document_nonce,
                document_id: currentDocumentId,
                document_type: currentDocumentType,
                signature: signatureData
            },
            success: function(response) {
                if (response.success) {
                    alert('Document signed successfully!');
                    hideSignatureModal();
                    location.reload(); // Reload to show updated status
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to sign document'));
                }
            },
            error: function() {
                alert('Network error. Please try again.');
            },
            complete: function() {
                $('.yolo-submit-signature-btn').prop('disabled', false).text('Submit Signature');
            }
        });
    }
    
})(jQuery);
