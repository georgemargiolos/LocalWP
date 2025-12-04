<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Bookings Manager - Actions and Utilities
 *
 * @package    YOLO_Yacht_Search
 * @subpackage YOLO_Yacht_Search/admin
 */

class YOLO_YS_Admin_Bookings_Manager {
    
    /**
     * Send payment reminder email
     */
    public static function send_payment_reminder($booking_id) {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_bookings} WHERE id = %d",
            $booking_id
        ));
        
        if (!$booking || $booking->payment_status !== 'deposit_paid') {
            return false;
        }
        
        // Send HTML email using template
        return YOLO_YS_Email::send_payment_reminder($booking);
    }
    
    /**
     * Mark booking as fully paid
     */
    public static function mark_as_paid($booking_id) {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        $result = $wpdb->update(
            $table_bookings,
            array(
                'payment_status' => 'fully_paid',
                'deposit_paid' => $wpdb->get_var($wpdb->prepare(
                    "SELECT total_price FROM {$table_bookings} WHERE id = %d",
                    $booking_id
                )),
                'remaining_balance' => 0
            ),
            array('id' => $booking_id),
            array('%s', '%f', '%f'),
            array('%d')
        );
        
        if ($result) {
            // Send confirmation email
            $booking = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_bookings} WHERE id = %d",
                $booking_id
            ));
            
            if ($booking) {
                // Send HTML email using template
                YOLO_YS_Email::send_payment_received($booking);
            }
        }
        
        return $result !== false;
    }
    
    /**
     * Export bookings to CSV
     */
    public static function export_to_csv() {
        global $wpdb;
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        
        // Get filters from URL
        $where = array('1=1');
        $where_values = array();
        
        if (!empty($_GET['payment_status'])) {
            $where[] = 'payment_status = %s';
            $where_values[] = sanitize_text_field($_GET['payment_status']);
        }
        
        if (!empty($_GET['yacht_id'])) {
            $where[] = 'yacht_id = %d';
            $where_values[] = intval($_GET['yacht_id']);
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get bookings
        if (!empty($where_values)) {
            $bookings = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$table_bookings} WHERE {$where_clause} ORDER BY created_at DESC",
                $where_values
            ), ARRAY_A);
        } else {
            $bookings = $wpdb->get_results(
                "SELECT * FROM {$table_bookings} WHERE {$where_clause} ORDER BY created_at DESC",
                ARRAY_A
            );
        }
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=yolo-bookings-' . date('Y-m-d') . '.csv');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Add headers
        fputcsv($output, array(
            'ID',
            'Booking Reference',
            'Created Date',
            'Yacht ID',
            'Yacht Name',
            'Customer Name',
            'Customer Email',
            'Customer Phone',
            'Charter From',
            'Charter To',
            'Total Price',
            'Deposit Paid',
            'Remaining Balance',
            'Currency',
            'Payment Status',
            'Booking Status',
            'Stripe Session ID',
            'BM Reservation ID'
        ));
        
        // Add data
        foreach ($bookings as $booking) {
            $booking_reference = !empty($booking['bm_reservation_id']) 
                ? 'BM-' . $booking['bm_reservation_id'] 
                : 'YOLO-' . date('Y') . '-' . str_pad($booking['id'], 4, '0', STR_PAD_LEFT);
            
            fputcsv($output, array(
                $booking['id'],
                $booking_reference,
                $booking['created_at'],
                $booking['yacht_id'],
                $booking['yacht_name'],
                $booking['customer_name'],
                $booking['customer_email'],
                $booking['customer_phone'],
                $booking['date_from'],
                $booking['date_to'],
                $booking['total_price'],
                $booking['deposit_paid'],
                $booking['remaining_balance'],
                $booking['currency'],
                $booking['payment_status'],
                $booking['booking_status'],
                $booking['stripe_session_id'],
                $booking['bm_reservation_id']
            ));
        }
        
        fclose($output);
    }
}
