(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize color pickers
        $('.color-picker').wpColorPicker();
        
        // Tab navigation
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
        });
        
        // Delete booking handler
        $(document).on('click', '.yolo-delete-booking', function(e) {
            e.preventDefault();
            
            var $link = $(this);
            var bookingId = $link.data('booking-id');
            var customerName = $link.data('customer');
            
            if (confirm('Are you sure you want to delete booking #' + bookingId + ' for ' + customerName + '?\n\nThis action cannot be undone.')) {
                $link.text('Deleting...');
                
                $.ajax({
                    url: yoloYsAdmin.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'yolo_delete_booking',
                        booking_id: bookingId,
                        nonce: yoloYsAdmin.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row from the table
                            $link.closest('tr').fadeOut(300, function() {
                                $(this).remove();
                            });
                        } else {
                            alert('Error: ' + (response.data || 'Failed to delete booking'));
                            $link.text('Delete');
                        }
                    },
                    error: function() {
                        alert('Error: Could not connect to server');
                        $link.text('Delete');
                    }
                });
            }
        });
    });
    
})(jQuery);
