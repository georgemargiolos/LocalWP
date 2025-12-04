<?php
/**
 * Texts Customization Admin Page
 * Allows customization of all text labels, buttons, and messages throughout the plugin
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['yolo_ys_save_texts']) && check_admin_referer('yolo_ys_texts_nonce')) {
    // Save all text options
    $text_options = array(
        // Booking Section
        'yolo_ys_text_book_now' => sanitize_text_field($_POST['yolo_ys_text_book_now']),
        'yolo_ys_text_select_dates' => sanitize_text_field($_POST['yolo_ys_text_select_dates']),
        'yolo_ys_text_check_availability' => sanitize_text_field($_POST['yolo_ys_text_check_availability']),
        'yolo_ys_text_request_quote' => sanitize_text_field($_POST['yolo_ys_text_request_quote']),
        'yolo_ys_text_total_price' => sanitize_text_field($_POST['yolo_ys_text_total_price']),
        'yolo_ys_text_deposit_required' => sanitize_text_field($_POST['yolo_ys_text_deposit_required']),
        'yolo_ys_text_remaining_balance' => sanitize_text_field($_POST['yolo_ys_text_remaining_balance']),
        
        // Quote Form
        'yolo_ys_text_quote_form_title' => sanitize_text_field($_POST['yolo_ys_text_quote_form_title']),
        'yolo_ys_text_your_name' => sanitize_text_field($_POST['yolo_ys_text_your_name']),
        'yolo_ys_text_your_email' => sanitize_text_field($_POST['yolo_ys_text_your_email']),
        'yolo_ys_text_your_phone' => sanitize_text_field($_POST['yolo_ys_text_your_phone']),
        'yolo_ys_text_your_message' => sanitize_text_field($_POST['yolo_ys_text_your_message']),
        'yolo_ys_text_send_request' => sanitize_text_field($_POST['yolo_ys_text_send_request']),
        
        // Section Headings
        'yolo_ys_text_description' => sanitize_text_field($_POST['yolo_ys_text_description']),
        'yolo_ys_text_equipment' => sanitize_text_field($_POST['yolo_ys_text_equipment']),
        'yolo_ys_text_technical_specs' => sanitize_text_field($_POST['yolo_ys_text_technical_specs']),
        'yolo_ys_text_obligatory_extras' => sanitize_text_field($_POST['yolo_ys_text_obligatory_extras']),
        'yolo_ys_text_optional_extras' => sanitize_text_field($_POST['yolo_ys_text_optional_extras']),
        'yolo_ys_text_cancellation_policy' => sanitize_text_field($_POST['yolo_ys_text_cancellation_policy']),
        'yolo_ys_text_security_deposit' => sanitize_text_field($_POST['yolo_ys_text_security_deposit']),
        'yolo_ys_text_checkin_checkout' => sanitize_text_field($_POST['yolo_ys_text_checkin_checkout']),
        
        // Technical Specs Labels
        'yolo_ys_text_length' => sanitize_text_field($_POST['yolo_ys_text_length']),
        'yolo_ys_text_beam' => sanitize_text_field($_POST['yolo_ys_text_beam']),
        'yolo_ys_text_draft' => sanitize_text_field($_POST['yolo_ys_text_draft']),
        'yolo_ys_text_cabins' => sanitize_text_field($_POST['yolo_ys_text_cabins']),
        'yolo_ys_text_wc' => sanitize_text_field($_POST['yolo_ys_text_wc']),
        'yolo_ys_text_berths' => sanitize_text_field($_POST['yolo_ys_text_berths']),
        'yolo_ys_text_year_built' => sanitize_text_field($_POST['yolo_ys_text_year_built']),
        'yolo_ys_text_engine_power' => sanitize_text_field($_POST['yolo_ys_text_engine_power']),
        'yolo_ys_text_fuel_capacity' => sanitize_text_field($_POST['yolo_ys_text_fuel_capacity']),
        'yolo_ys_text_water_capacity' => sanitize_text_field($_POST['yolo_ys_text_water_capacity']),
        
        // Search Widget
        'yolo_ys_text_search_title' => sanitize_text_field($_POST['yolo_ys_text_search_title']),
        'yolo_ys_text_date_from' => sanitize_text_field($_POST['yolo_ys_text_date_from']),
        'yolo_ys_text_date_to' => sanitize_text_field($_POST['yolo_ys_text_date_to']),
        'yolo_ys_text_boat_type' => sanitize_text_field($_POST['yolo_ys_text_boat_type']),
        'yolo_ys_text_search_button' => sanitize_text_field($_POST['yolo_ys_text_search_button']),
        
        // Search Results
        'yolo_ys_text_results_title' => sanitize_text_field($_POST['yolo_ys_text_results_title']),
        'yolo_ys_text_no_results' => sanitize_text_field($_POST['yolo_ys_text_no_results']),
        'yolo_ys_text_view_details' => sanitize_text_field($_POST['yolo_ys_text_view_details']),
        'yolo_ys_text_from_price' => sanitize_text_field($_POST['yolo_ys_text_from_price']),
        'yolo_ys_text_per_week' => sanitize_text_field($_POST['yolo_ys_text_per_week']),
        
        // Booking Confirmation
        'yolo_ys_text_booking_confirmed' => sanitize_text_field($_POST['yolo_ys_text_booking_confirmed']),
        'yolo_ys_text_booking_reference' => sanitize_text_field($_POST['yolo_ys_text_booking_reference']),
        'yolo_ys_text_payment_received' => sanitize_text_field($_POST['yolo_ys_text_payment_received']),
        'yolo_ys_text_confirmation_email' => sanitize_text_field($_POST['yolo_ys_text_confirmation_email']),
    );
    
    foreach ($text_options as $key => $value) {
        update_option($key, $value);
    }
    
    echo '<div class="notice notice-success"><p>Text settings saved successfully!</p></div>';
}

// Get current values or defaults
$texts = array(
    // Booking Section
    'book_now' => get_option('yolo_ys_text_book_now', 'BOOK NOW'),
    'select_dates' => get_option('yolo_ys_text_select_dates', 'Select Your Dates'),
    'check_availability' => get_option('yolo_ys_text_check_availability', 'Check Availability'),
    'request_quote' => get_option('yolo_ys_text_request_quote', 'Request a Quote'),
    'total_price' => get_option('yolo_ys_text_total_price', 'Total Price'),
    'deposit_required' => get_option('yolo_ys_text_deposit_required', 'Deposit Required (50%)'),
    'remaining_balance' => get_option('yolo_ys_text_remaining_balance', 'Remaining Balance'),
    
    // Quote Form
    'quote_form_title' => get_option('yolo_ys_text_quote_form_title', 'Request a Quote'),
    'your_name' => get_option('yolo_ys_text_your_name', 'Your Name'),
    'your_email' => get_option('yolo_ys_text_your_email', 'Your Email'),
    'your_phone' => get_option('yolo_ys_text_your_phone', 'Your Phone'),
    'your_message' => get_option('yolo_ys_text_your_message', 'Your Message'),
    'send_request' => get_option('yolo_ys_text_send_request', 'Send Request'),
    
    // Section Headings
    'description' => get_option('yolo_ys_text_description', 'Description'),
    'equipment' => get_option('yolo_ys_text_equipment', 'Equipment'),
    'technical_specs' => get_option('yolo_ys_text_technical_specs', 'Technical Characteristics'),
    'obligatory_extras' => get_option('yolo_ys_text_obligatory_extras', 'Obligatory Extras'),
    'optional_extras' => get_option('yolo_ys_text_optional_extras', 'Optional Extras'),
    'cancellation_policy' => get_option('yolo_ys_text_cancellation_policy', 'Cancellation Policy'),
    'security_deposit' => get_option('yolo_ys_text_security_deposit', 'Security Deposit'),
    'checkin_checkout' => get_option('yolo_ys_text_checkin_checkout', 'Check-in & Check-out'),
    
    // Technical Specs Labels
    'length' => get_option('yolo_ys_text_length', 'Length'),
    'beam' => get_option('yolo_ys_text_beam', 'Beam'),
    'draft' => get_option('yolo_ys_text_draft', 'Draft'),
    'cabins' => get_option('yolo_ys_text_cabins', 'Cabins'),
    'wc' => get_option('yolo_ys_text_wc', 'Heads'),
    'berths' => get_option('yolo_ys_text_berths', 'Berths'),
    'year_built' => get_option('yolo_ys_text_year_built', 'Year Built'),
    'engine_power' => get_option('yolo_ys_text_engine_power', 'Engine Power'),
    'fuel_capacity' => get_option('yolo_ys_text_fuel_capacity', 'Fuel Capacity'),
    'water_capacity' => get_option('yolo_ys_text_water_capacity', 'Water Capacity'),
    
    // Search Widget
    'search_title' => get_option('yolo_ys_text_search_title', 'Find Your Perfect Yacht'),
    'date_from' => get_option('yolo_ys_text_date_from', 'Date From'),
    'date_to' => get_option('yolo_ys_text_date_to', 'Date To'),
    'boat_type' => get_option('yolo_ys_text_boat_type', 'Boat Type'),
    'search_button' => get_option('yolo_ys_text_search_button', 'Search'),
    
    // Search Results
    'results_title' => get_option('yolo_ys_text_results_title', 'Available Yachts'),
    'no_results' => get_option('yolo_ys_text_no_results', 'No yachts found matching your criteria.'),
    'view_details' => get_option('yolo_ys_text_view_details', 'View Details'),
    'from_price' => get_option('yolo_ys_text_from_price', 'From'),
    'per_week' => get_option('yolo_ys_text_per_week', 'per week'),
    
    // Booking Confirmation
    'booking_confirmed' => get_option('yolo_ys_text_booking_confirmed', 'Booking Confirmed!'),
    'booking_reference' => get_option('yolo_ys_text_booking_reference', 'Booking Reference'),
    'payment_received' => get_option('yolo_ys_text_payment_received', 'Payment Received'),
    'confirmation_email' => get_option('yolo_ys_text_confirmation_email', 'A confirmation email has been sent to'),
);
?>

<div class="wrap">
    <h1><?php _e('Text Customization', 'yolo-yacht-search'); ?></h1>
    <p><?php _e('Customize all text labels, buttons, and messages throughout the plugin. Perfect for translation or rebranding.', 'yolo-yacht-search'); ?></p>
    
    <form method="post" action="">
        <?php wp_nonce_field('yolo_ys_texts_nonce'); ?>
        
        <table class="form-table">
            <!-- Booking Section -->
            <tr>
                <th colspan="2"><h2><?php _e('Booking Section', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_book_now"><?php _e('Book Now Button', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_book_now" name="yolo_ys_text_book_now" value="<?php echo esc_attr($texts['book_now']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_select_dates"><?php _e('Select Dates', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_select_dates" name="yolo_ys_text_select_dates" value="<?php echo esc_attr($texts['select_dates']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_check_availability"><?php _e('Check Availability Button', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_check_availability" name="yolo_ys_text_check_availability" value="<?php echo esc_attr($texts['check_availability']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_request_quote"><?php _e('Request Quote Button', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_request_quote" name="yolo_ys_text_request_quote" value="<?php echo esc_attr($texts['request_quote']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_total_price"><?php _e('Total Price Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_total_price" name="yolo_ys_text_total_price" value="<?php echo esc_attr($texts['total_price']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_deposit_required"><?php _e('Deposit Required Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_deposit_required" name="yolo_ys_text_deposit_required" value="<?php echo esc_attr($texts['deposit_required']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_remaining_balance"><?php _e('Remaining Balance Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_remaining_balance" name="yolo_ys_text_remaining_balance" value="<?php echo esc_attr($texts['remaining_balance']); ?>" class="regular-text"></td>
            </tr>
            
            <!-- Quote Form -->
            <tr>
                <th colspan="2"><h2><?php _e('Quote Request Form', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_quote_form_title"><?php _e('Form Title', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_quote_form_title" name="yolo_ys_text_quote_form_title" value="<?php echo esc_attr($texts['quote_form_title']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_your_name"><?php _e('Name Field Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_your_name" name="yolo_ys_text_your_name" value="<?php echo esc_attr($texts['your_name']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_your_email"><?php _e('Email Field Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_your_email" name="yolo_ys_text_your_email" value="<?php echo esc_attr($texts['your_email']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_your_phone"><?php _e('Phone Field Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_your_phone" name="yolo_ys_text_your_phone" value="<?php echo esc_attr($texts['your_phone']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_your_message"><?php _e('Message Field Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_your_message" name="yolo_ys_text_your_message" value="<?php echo esc_attr($texts['your_message']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_send_request"><?php _e('Send Button', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_send_request" name="yolo_ys_text_send_request" value="<?php echo esc_attr($texts['send_request']); ?>" class="regular-text"></td>
            </tr>
            
            <!-- Section Headings -->
            <tr>
                <th colspan="2"><h2><?php _e('Section Headings', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_description"><?php _e('Description Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_description" name="yolo_ys_text_description" value="<?php echo esc_attr($texts['description']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_equipment"><?php _e('Equipment Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_equipment" name="yolo_ys_text_equipment" value="<?php echo esc_attr($texts['equipment']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_technical_specs"><?php _e('Technical Specs Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_technical_specs" name="yolo_ys_text_technical_specs" value="<?php echo esc_attr($texts['technical_specs']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_obligatory_extras"><?php _e('Obligatory Extras Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_obligatory_extras" name="yolo_ys_text_obligatory_extras" value="<?php echo esc_attr($texts['obligatory_extras']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_optional_extras"><?php _e('Optional Extras Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_optional_extras" name="yolo_ys_text_optional_extras" value="<?php echo esc_attr($texts['optional_extras']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_cancellation_policy"><?php _e('Cancellation Policy Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_cancellation_policy" name="yolo_ys_text_cancellation_policy" value="<?php echo esc_attr($texts['cancellation_policy']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_security_deposit"><?php _e('Security Deposit Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_security_deposit" name="yolo_ys_text_security_deposit" value="<?php echo esc_attr($texts['security_deposit']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_checkin_checkout"><?php _e('Check-in/Check-out Section', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_checkin_checkout" name="yolo_ys_text_checkin_checkout" value="<?php echo esc_attr($texts['checkin_checkout']); ?>" class="regular-text"></td>
            </tr>
            
            <!-- Technical Specs Labels -->
            <tr>
                <th colspan="2"><h2><?php _e('Technical Specifications Labels', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_length"><?php _e('Length', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_length" name="yolo_ys_text_length" value="<?php echo esc_attr($texts['length']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_beam"><?php _e('Beam', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_beam" name="yolo_ys_text_beam" value="<?php echo esc_attr($texts['beam']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_draft"><?php _e('Draft', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_draft" name="yolo_ys_text_draft" value="<?php echo esc_attr($texts['draft']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_cabins"><?php _e('Cabins', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_cabins" name="yolo_ys_text_cabins" value="<?php echo esc_attr($texts['cabins']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_wc"><?php _e('WC', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_wc" name="yolo_ys_text_wc" value="<?php echo esc_attr($texts['wc']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_berths"><?php _e('Berths', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_berths" name="yolo_ys_text_berths" value="<?php echo esc_attr($texts['berths']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_year_built"><?php _e('Year Built', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_year_built" name="yolo_ys_text_year_built" value="<?php echo esc_attr($texts['year_built']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_engine_power"><?php _e('Engine Power', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_engine_power" name="yolo_ys_text_engine_power" value="<?php echo esc_attr($texts['engine_power']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_fuel_capacity"><?php _e('Fuel Capacity', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_fuel_capacity" name="yolo_ys_text_fuel_capacity" value="<?php echo esc_attr($texts['fuel_capacity']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_water_capacity"><?php _e('Water Capacity', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_water_capacity" name="yolo_ys_text_water_capacity" value="<?php echo esc_attr($texts['water_capacity']); ?>" class="regular-text"></td>
            </tr>
            
            <!-- Search Widget -->
            <tr>
                <th colspan="2"><h2><?php _e('Search Widget', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_search_title"><?php _e('Search Title', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_search_title" name="yolo_ys_text_search_title" value="<?php echo esc_attr($texts['search_title']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_date_from"><?php _e('Date From Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_date_from" name="yolo_ys_text_date_from" value="<?php echo esc_attr($texts['date_from']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_date_to"><?php _e('Date To Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_date_to" name="yolo_ys_text_date_to" value="<?php echo esc_attr($texts['date_to']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_boat_type"><?php _e('Boat Type Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_boat_type" name="yolo_ys_text_boat_type" value="<?php echo esc_attr($texts['boat_type']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_search_button"><?php _e('Search Button', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_search_button" name="yolo_ys_text_search_button" value="<?php echo esc_attr($texts['search_button']); ?>" class="regular-text"></td>
            </tr>
            
            <!-- Search Results -->
            <tr>
                <th colspan="2"><h2><?php _e('Search Results', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_results_title"><?php _e('Results Title', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_results_title" name="yolo_ys_text_results_title" value="<?php echo esc_attr($texts['results_title']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_no_results"><?php _e('No Results Message', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_no_results" name="yolo_ys_text_no_results" value="<?php echo esc_attr($texts['no_results']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_view_details"><?php _e('View Details Button', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_view_details" name="yolo_ys_text_view_details" value="<?php echo esc_attr($texts['view_details']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_from_price"><?php _e('From Price Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_from_price" name="yolo_ys_text_from_price" value="<?php echo esc_attr($texts['from_price']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_per_week"><?php _e('Per Week Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_per_week" name="yolo_ys_text_per_week" value="<?php echo esc_attr($texts['per_week']); ?>" class="regular-text"></td>
            </tr>
            
            <!-- Booking Confirmation -->
            <tr>
                <th colspan="2"><h2><?php _e('Booking Confirmation', 'yolo-yacht-search'); ?></h2></th>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_booking_confirmed"><?php _e('Booking Confirmed Title', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_booking_confirmed" name="yolo_ys_text_booking_confirmed" value="<?php echo esc_attr($texts['booking_confirmed']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_booking_reference"><?php _e('Booking Reference Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_booking_reference" name="yolo_ys_text_booking_reference" value="<?php echo esc_attr($texts['booking_reference']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_payment_received"><?php _e('Payment Received Label', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_payment_received" name="yolo_ys_text_payment_received" value="<?php echo esc_attr($texts['payment_received']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="yolo_ys_text_confirmation_email"><?php _e('Confirmation Email Message', 'yolo-yacht-search'); ?></label></th>
                <td><input type="text" id="yolo_ys_text_confirmation_email" name="yolo_ys_text_confirmation_email" value="<?php echo esc_attr($texts['confirmation_email']); ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <?php submit_button(__('Save Text Settings', 'yolo-yacht-search'), 'primary', 'yolo_ys_save_texts'); ?>
    </form>
    
    <hr>
    
    <h2><?php _e('Reset to Defaults', 'yolo-yacht-search'); ?></h2>
    <p><?php _e('Click the button below to reset all text settings to their default values.', 'yolo-yacht-search'); ?></p>
    <button type="button" class="button" onclick="if(confirm('Are you sure you want to reset all text settings to defaults?')) { window.location.href='<?php echo admin_url('admin.php?page=yolo-yacht-texts&reset=1'); ?>'; }"><?php _e('Reset All Texts to Defaults', 'yolo-yacht-search'); ?></button>
</div>
