<?php
/**
 * PDF Generator for Check-in/Check-out Documents
 *
 * Uses FPDF library to generate PDF documents
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include FPDF library
require_once YOLO_YS_PLUGIN_DIR . 'vendor/fpdf/fpdf.php';

class YOLO_YS_PDF_Generator extends FPDF {

    private $company_logo;
    private $boat_logo;
    private $company_name = 'YOLO Charters';
    private $company_address = 'Preveza Main Port, Greece';
    private $company_phone = '+30 123 456 7890';
    private $company_email = 'info@yolocharters.com';

    /**
     * Generate check-in PDF
     */
    public static function generate_checkin_pdf($checkin_id) {
        global $wpdb;
        
        // Get check-in data
        $checkin = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_checkins WHERE id = %d",
            $checkin_id
        ));
        
        if (!$checkin) {
            return false;
        }
        
        // Get yacht data
        $yacht = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_yachts WHERE id = %d",
            $checkin->yacht_id
        ));
        
        // Get booking data
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d",
            $checkin->booking_id
        ));
        
        // Create PDF
        $pdf = new self();
        $pdf->company_logo = $yacht->company_logo ?? '';
        $pdf->boat_logo = $yacht->boat_logo ?? '';
        
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        // Title
        $pdf->Cell(0, 10, 'YACHT CHECK-IN DOCUMENT', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Add logos if available
        if ($pdf->company_logo) {
            $pdf->Image($pdf->company_logo, 10, 30, 40);
        }
        
        if ($pdf->boat_logo) {
            $pdf->Image($pdf->boat_logo, 160, 30, 40);
        }
        
        $pdf->Ln(30);
        
        // Company info
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, $pdf->company_name, 0, 1);
        $pdf->Cell(0, 5, $pdf->company_address, 0, 1);
        $pdf->Cell(0, 5, 'Tel: ' . $pdf->company_phone, 0, 1);
        $pdf->Cell(0, 5, 'Email: ' . $pdf->company_email, 0, 1);
        $pdf->Ln(10);
        
        // Yacht and booking info
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Yacht Information', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 6, 'Yacht Name:', 0, 0);
        $pdf->Cell(0, 6, $yacht->yacht_name, 0, 1);
        $pdf->Cell(50, 6, 'Model:', 0, 0);
        $pdf->Cell(0, 6, $yacht->yacht_model, 0, 1);
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Booking Information', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 6, 'Booking ID:', 0, 0);
        $pdf->Cell(0, 6, $booking->id, 0, 1);
        $pdf->Cell(50, 6, 'Customer:', 0, 0);
        $pdf->Cell(0, 6, $booking->customer_name ?? 'N/A', 0, 1);
        $pdf->Cell(50, 6, 'Check-in Date:', 0, 0);
        $pdf->Cell(0, 6, date('d/m/Y', strtotime($booking->check_in_date)), 0, 1);
        $pdf->Ln(10);
        
        // Checklist
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Equipment Checklist', 0, 1);
        $pdf->SetFont('Arial', '', 9);
        
        $checklist_data = json_decode($checkin->checklist_data, true);
        
        if ($checklist_data && is_array($checklist_data)) {
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(100, 6, 'Item', 1, 0, 'L', true);
            $pdf->Cell(30, 6, 'Quantity', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Status', 1, 1, 'C', true);
            
            foreach ($checklist_data as $item) {
                $pdf->Cell(100, 6, $item['name'], 1, 0);
                $pdf->Cell(30, 6, $item['quantity'], 1, 0, 'C');
                $pdf->Cell(30, 6, $item['checked'] ? 'OK' : 'Missing', 1, 1, 'C');
            }
        }
        
        $pdf->Ln(10);
        
        // Signatures
        // Signatures at bottom of page
        // Move to bottom of page (leaving 40mm from bottom)
        $pdf->SetY(-40);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Signatures', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        
        // Base Manager Signature (Bottom-Left)
        $bm_x = 10;
        $bm_y = $pdf->GetY();
        
        $pdf->SetXY($bm_x, $bm_y);
        $pdf->Cell(90, 6, 'Base Manager:', 0, 1);
        
        if ($checkin->signature) {
            try {
                $signature_data = str_replace('data:image/png;base64,', '', $checkin->signature);
                $signature_decoded = base64_decode($signature_data, true);
                
                if ($signature_decoded === false) {
                    throw new Exception('Invalid base64 signature data');
                }
                
                $signature_file = sys_get_temp_dir() . '/signature_' . $checkin_id . '_' . uniqid() . '.png';
                
                if (file_put_contents($signature_file, $signature_decoded) === false) {
                    throw new Exception('Failed to write signature file');
                }
                
                $pdf->Image($signature_file, $bm_x, $pdf->GetY(), 50);
                @unlink($signature_file);
            } catch (Exception $e) {
                error_log('YOLO YS PDF: Signature error - ' . $e->getMessage());
                $pdf->SetX($bm_x);
                $pdf->Cell(90, 6, '[Signature Error]', 0, 1);
            }
        } else {
            $pdf->SetX($bm_x);
            $pdf->Cell(90, 6, '___________________', 0, 1);
        }
        
        $pdf->SetX($bm_x);
        $pdf->Cell(90, 6, 'Date: ' . date('d/m/Y', strtotime($checkin->created_at)), 0, 0);
        
        // Guest Signature (Bottom-Right)
        $guest_x = 110;
        
        $pdf->SetXY($guest_x, $bm_y);
        $pdf->Cell(90, 6, 'Guest:', 0, 1);
        
        if ($checkin->guest_signature) {
            try {
                $guest_signature_data = str_replace('data:image/png;base64,', '', $checkin->guest_signature);
                $guest_signature_decoded = base64_decode($guest_signature_data, true);
                
                if ($guest_signature_decoded === false) {
                    throw new Exception('Invalid base64 guest signature data');
                }
                
                $guest_signature_file = sys_get_temp_dir() . '/guest_signature_' . $checkin_id . '_' . uniqid() . '.png';
                
                if (file_put_contents($guest_signature_file, $guest_signature_decoded) === false) {
                    throw new Exception('Failed to write guest signature file');
                }
                
                $pdf->Image($guest_signature_file, $guest_x, $pdf->GetY(), 50);
                @unlink($guest_signature_file);
            } catch (Exception $e) {
                error_log('YOLO YS PDF: Guest signature error (check-in) - ' . $e->getMessage());
                $pdf->SetX($guest_x);
                $pdf->Cell(90, 6, '[Guest Signature Error]', 0, 1);
            }
        } else {
            $pdf->SetX($guest_x);
            $pdf->Cell(90, 6, '___________________', 0, 1);
        }
        
        $pdf->SetX($guest_x);
        if ($checkin->guest_signed_at) {
            $pdf->Cell(90, 6, 'Date: ' . date('d/m/Y', strtotime($checkin->guest_signed_at)), 0, 0);
        } else {
            $pdf->Cell(90, 6, 'Date: ___________', 0, 0);
        }
        
        // Save PDF
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/yolo-checkin-pdfs/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $pdf_filename = 'checkin_' . $checkin_id . '_' . time() . '.pdf';
        $pdf_path = $pdf_dir . $pdf_filename;
        $pdf_url = $upload_dir['baseurl'] . '/yolo-checkin-pdfs/' . $pdf_filename;
        
        $pdf->Output('F', $pdf_path);
        
        // Update check-in record with PDF URL
        $wpdb->update(
            $wpdb->prefix . 'yolo_bm_checkins',
            array('pdf_url' => $pdf_url),
            array('id' => $checkin_id)
        );
        
        return $pdf_url;
    }

    /**
     * Generate check-out PDF
     */
    public static function generate_checkout_pdf($checkout_id) {
        global $wpdb;
        
        // Get check-out data
        $checkout = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_checkouts WHERE id = %d",
            $checkout_id
        ));
        
        if (!$checkout) {
            return false;
        }
        
        // Get yacht data
        $yacht = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_yachts WHERE id = %d",
            $checkout->yacht_id
        ));
        
        // Get booking data
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d",
            $checkout->booking_id
        ));
        
        // Create PDF (similar to check-in but with "CHECK-OUT" title)
        $pdf = new self();
        $pdf->company_logo = $yacht->company_logo ?? '';
        $pdf->boat_logo = $yacht->boat_logo ?? '';
        
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 16);
        
        // Title
        $pdf->Cell(0, 10, 'YACHT CHECK-OUT DOCUMENT', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Add logos if available
        if ($pdf->company_logo) {
            $pdf->Image($pdf->company_logo, 10, 30, 40);
        }
        
        if ($pdf->boat_logo) {
            $pdf->Image($pdf->boat_logo, 160, 30, 40);
        }
        
        $pdf->Ln(30);
        
        // Company info
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, $pdf->company_name, 0, 1);
        $pdf->Cell(0, 5, $pdf->company_address, 0, 1);
        $pdf->Cell(0, 5, 'Tel: ' . $pdf->company_phone, 0, 1);
        $pdf->Cell(0, 5, 'Email: ' . $pdf->company_email, 0, 1);
        $pdf->Ln(10);
        
        // Yacht and booking info
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Yacht Information', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 6, 'Yacht Name:', 0, 0);
        $pdf->Cell(0, 6, $yacht->yacht_name, 0, 1);
        $pdf->Cell(50, 6, 'Model:', 0, 0);
        $pdf->Cell(0, 6, $yacht->yacht_model, 0, 1);
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Booking Information', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 6, 'Booking ID:', 0, 0);
        $pdf->Cell(0, 6, $booking->id, 0, 1);
        $pdf->Cell(50, 6, 'Customer:', 0, 0);
        $pdf->Cell(0, 6, $booking->customer_name ?? 'N/A', 0, 1);
        $pdf->Cell(50, 6, 'Check-out Date:', 0, 0);
        $pdf->Cell(0, 6, date('d/m/Y', strtotime($booking->check_out_date)), 0, 1);
        $pdf->Ln(10);
        
        // Checklist
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Equipment Checklist', 0, 1);
        $pdf->SetFont('Arial', '', 9);
        
        $checklist_data = json_decode($checkout->checklist_data, true);
        
        if ($checklist_data && is_array($checklist_data)) {
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(100, 6, 'Item', 1, 0, 'L', true);
            $pdf->Cell(30, 6, 'Quantity', 1, 0, 'C', true);
            $pdf->Cell(30, 6, 'Status', 1, 1, 'C', true);
            
            foreach ($checklist_data as $item) {
                $pdf->Cell(100, 6, $item['name'], 1, 0);
                $pdf->Cell(30, 6, $item['quantity'], 1, 0, 'C');
                $pdf->Cell(30, 6, $item['checked'] ? 'OK' : 'Damaged/Missing', 1, 1, 'C');
            }
        }
        
        $pdf->Ln(10);
        
        // Signatures at bottom of page
        // Move to bottom of page (leaving 40mm from bottom)
        $pdf->SetY(-40);
        
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, 'Signatures', 0, 1);
        $pdf->SetFont('Arial', '', 10);
        
        // Base Manager Signature (Bottom-Left)
        $bm_x = 10;
        $bm_y = $pdf->GetY();
        
        $pdf->SetXY($bm_x, $bm_y);
        $pdf->Cell(90, 6, 'Base Manager:', 0, 1);
        
        if ($checkout->signature) {
            try {
                $signature_data = str_replace('data:image/png;base64,', '', $checkout->signature);
                $signature_decoded = base64_decode($signature_data, true);
                
                if ($signature_decoded === false) {
                    throw new Exception('Invalid base64 signature data');
                }
                
                $signature_file = sys_get_temp_dir() . '/signature_' . $checkout_id . '_' . uniqid() . '.png';
                
                if (file_put_contents($signature_file, $signature_decoded) === false) {
                    throw new Exception('Failed to write signature file');
                }
                
                $pdf->Image($signature_file, $bm_x, $pdf->GetY(), 50);
                @unlink($signature_file);
            } catch (Exception $e) {
                error_log('YOLO YS PDF: Signature error - ' . $e->getMessage());
                $pdf->SetX($bm_x);
                $pdf->Cell(90, 6, '[Signature Error]', 0, 1);
            }
        } else {
            $pdf->SetX($bm_x);
            $pdf->Cell(90, 6, '___________________', 0, 1);
        }
        
        $pdf->SetX($bm_x);
        $pdf->Cell(90, 6, 'Date: ' . date('d/m/Y', strtotime($checkout->created_at)), 0, 0);
        
        // Guest Signature (Bottom-Right)
        $guest_x = 110;
        
        $pdf->SetXY($guest_x, $bm_y);
        $pdf->Cell(90, 6, 'Guest:', 0, 1);
        
        if ($checkout->guest_signature) {
            try {
                $guest_signature_data = str_replace('data:image/png;base64,', '', $checkout->guest_signature);
                $guest_signature_decoded = base64_decode($guest_signature_data, true);
                
                if ($guest_signature_decoded === false) {
                    throw new Exception('Invalid base64 guest signature data');
                }
                
                $guest_signature_file = sys_get_temp_dir() . '/guest_signature_' . $checkout_id . '_' . uniqid() . '.png';
                
                if (file_put_contents($guest_signature_file, $guest_signature_decoded) === false) {
                    throw new Exception('Failed to write guest signature file');
                }
                
                $pdf->Image($guest_signature_file, $guest_x, $pdf->GetY(), 50);
                @unlink($guest_signature_file);
            } catch (Exception $e) {
                error_log('YOLO YS PDF: Guest signature error (checkout) - ' . $e->getMessage());
                $pdf->SetX($guest_x);
                $pdf->Cell(90, 6, '[Guest Signature Error]', 0, 1);
            }
        } else {
            $pdf->SetX($guest_x);
            $pdf->Cell(90, 6, '___________________', 0, 1);
        }
        
        $pdf->SetX($guest_x);
        if ($checkout->guest_signed_at) {
            $pdf->Cell(90, 6, 'Date: ' . date('d/m/Y', strtotime($checkout->guest_signed_at)), 0, 0);
        } else {
            $pdf->Cell(90, 6, 'Date: ___________', 0, 0);
        }
        
        // Save PDF
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/yolo-checkout-pdfs/';
        
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }
        
        $pdf_filename = 'checkout_' . $checkout_id . '_' . time() . '.pdf';
        $pdf_path = $pdf_dir . $pdf_filename;
        $pdf_url = $upload_dir['baseurl'] . '/yolo-checkout-pdfs/' . $pdf_filename;
        
        $pdf->Output('F', $pdf_path);
        
        // Update check-out record with PDF URL
        $wpdb->update(
            $wpdb->prefix . 'yolo_bm_checkouts',
            array('pdf_url' => $pdf_url),
            array('id' => $checkout_id)
        );
        
        return $pdf_url;
    }
}
