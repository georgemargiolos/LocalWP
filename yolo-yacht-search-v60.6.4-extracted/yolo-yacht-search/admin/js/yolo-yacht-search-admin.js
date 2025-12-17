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
    });
    
})(jQuery);
