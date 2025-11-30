<?php
/**
 * Email Sender Class
 * 
 * Handles sending HTML emails using templates
 */
class YOLO_YS_Email {
    
    /**
     * Send booking confirmation email
     */
    public static function send_booking_confirmation($booking) {
        // Prepare variables
        $booking_reference = !empty($booking->bm_reservation_id) 
            ? 'BM-' . $booking->bm_reservation_id 
            : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        
        $balance_payment_url = home_url('/balance-payment?ref=' . urlencode($booking_reference));
        
        $variables = array(
            'booking_reference' => $booking_reference,
            'customer_name' => $booking->customer_name,
            'yacht_name' => $booking->yacht_name,
            'date_from' => $booking->date_from,
            'date_to' => $booking->date_to,
            'total_price' => $booking->total_price,
            'deposit_paid' => $booking->deposit_paid,
            'remaining_balance' => $booking->remaining_balance,
            'currency' => $booking->currency,
            'balance_payment_url' => $balance_payment_url,
        );
        
        // Get email content
        $email_content = self::get_template_content('booking-confirmation', $variables);
        
        // Send email
        $to = $booking->customer_email;
        $subject = 'Booking Confirmation - ' . $booking->yacht_name;
        
        return self::send_html_email($to, $subject, $email_content, 'Booking Confirmed!');
    }
    
    /**
     * Send payment reminder email
     */
    public static function send_payment_reminder($booking) {
        // Prepare variables
        $booking_reference = !empty($booking->bm_reservation_id) 
            ? 'BM-' . $booking->bm_reservation_id 
            : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        
        $balance_payment_url = home_url('/balance-payment?ref=' . urlencode($booking_reference));
        
        $days_until_charter = ceil((strtotime($booking->date_from) - time()) / 86400);
        
        $variables = array(
            'booking_reference' => $booking_reference,
            'customer_name' => $booking->customer_name,
            'yacht_name' => $booking->yacht_name,
            'date_from' => $booking->date_from,
            'date_to' => $booking->date_to,
            'total_price' => $booking->total_price,
            'deposit_paid' => $booking->deposit_paid,
            'remaining_balance' => $booking->remaining_balance,
            'currency' => $booking->currency,
            'balance_payment_url' => $balance_payment_url,
            'days_until_charter' => $days_until_charter,
        );
        
        // Get email content
        $email_content = self::get_template_content('payment-reminder', $variables);
        
        // Send email
        $to = $booking->customer_email;
        $subject = 'Payment Reminder - ' . $booking->yacht_name;
        
        return self::send_html_email($to, $subject, $email_content, 'Payment Reminder');
    }
    
    /**
     * Send payment received email
     */
    public static function send_payment_received($booking) {
        // Prepare variables
        $booking_reference = !empty($booking->bm_reservation_id) 
            ? 'BM-' . $booking->bm_reservation_id 
            : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        
        $variables = array(
            'booking_reference' => $booking_reference,
            'customer_name' => $booking->customer_name,
            'yacht_name' => $booking->yacht_name,
            'date_from' => $booking->date_from,
            'date_to' => $booking->date_to,
            'total_price' => $booking->total_price,
            'currency' => $booking->currency,
        );
        
        // Get email content
        $email_content = self::get_template_content('payment-received', $variables);
        
        // Send email
        $to = $booking->customer_email;
        $subject = 'Payment Confirmed - ' . $booking->yacht_name;
        
        return self::send_html_email($to, $subject, $email_content, 'Payment Confirmed!');
    }
    
    /**
     * Get template content
     */
    private static function get_template_content($template_name, $variables) {
        // Extract variables
        extract($variables);
        
        // Start output buffering
        ob_start();
        
        // Include template
        include YOLO_YS_PLUGIN_DIR . 'includes/emails/' . $template_name . '.php';
        
        // Get content
        $content = ob_get_clean();
        
        return $content;
    }
    
    /**
     * Send HTML email
     */
    private static function send_html_email($to, $subject, $email_content, $email_heading) {
        // Prepare variables for main template
        $email_title = $subject;
        $logo_url = get_option('yolo_ys_email_logo_url', '');
        
        // Start output buffering
        ob_start();
        
        // Include main template
        include YOLO_YS_PLUGIN_DIR . 'includes/emails/email-template.php';
        
        // Get full HTML
        $html = ob_get_clean();
        
        // Set headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: YOLO Charters <info@yolocharters.com>',
        );
        
        // Send email
        $result = wp_mail($to, $subject, $html, $headers);
        
        if ($result) {
            error_log('YOLO YS: HTML email sent to ' . $to . ' - ' . $subject);
        } else {
            error_log('YOLO YS: Failed to send HTML email to ' . $to . ' - ' . $subject);
        }
        
        return $result;
    }
    
    /**
     * Send admin notification
     */
    public static function send_admin_notification($booking) {
        $admin_email = get_option('admin_email');
        
        $booking_reference = !empty($booking->bm_reservation_id) 
            ? 'BM-' . $booking->bm_reservation_id 
            : 'YOLO-' . date('Y') . '-' . str_pad($booking->id, 4, '0', STR_PAD_LEFT);
        
        $subject = 'New Booking - ' . $booking->yacht_name;
        
        $message = sprintf(
            "New booking received!\n\n" .
            "Booking Reference: %s\n" .
            "Customer: %s\n" .
            "Email: %s\n" .
            "Phone: %s\n" .
            "Yacht: %s\n" .
            "Dates: %s to %s\n" .
            "Total Price: %s\n" .
            "Deposit Paid: %s\n" .
            "Remaining Balance: %s\n\n" .
            "View in admin: %s",
            $booking_reference,
            $booking->customer_name,
            $booking->customer_email,
            $booking->customer_phone,
            $booking->yacht_name,
            date('F j, Y', strtotime($booking->date_from)),
            date('F j, Y', strtotime($booking->date_to)),
            YOLO_YS_Price_Formatter::format_price($booking->total_price, $booking->currency),
            YOLO_YS_Price_Formatter::format_price($booking->deposit_paid, $booking->currency),
            YOLO_YS_Price_Formatter::format_price($booking->remaining_balance, $booking->currency),
            admin_url('admin.php?page=yolo-ys-bookings&action=view&booking_id=' . $booking->id)
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        
        return wp_mail($admin_email, $subject, $message, $headers);
    }
}
