<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Improved PDF Generator for Check-in/Check-out Documents
 * 
 * Features:
 * - Professional branded header with logo
 * - Configurable company info from WordPress options
 * - Styled tables with colors
 * - Page numbers and document ID in footer
 * - Both signatures displayed nicely
 * - Terms & conditions section
 * - Proper page breaks for long checklists
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 41.12
 */

// Include FPDF library
require_once YOLO_YS_PLUGIN_DIR . 'vendor/fpdf/fpdf.php';

class YOLO_YS_PDF_Generator extends FPDF {

    // Brand colors
    private $primary_color = array(30, 58, 138);    // Navy blue #1e3a8a
    private $secondary_color = array(16, 185, 129); // Green #10b981
    private $light_gray = array(243, 244, 246);     // #f3f4f6
    private $dark_gray = array(55, 65, 81);         // #374151
    
    // Company info - loaded from WordPress options or defaults
    private $company_name;
    private $company_address;
    private $company_phone;
    private $company_email;
    private $company_website;
    private $company_logo_path;
    
    // Document info for footer
    private $document_id;
    private $document_type;

    /**
     * Constructor - load company settings
     */
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4') {
        parent::__construct($orientation, $unit, $size);
        
        // Load company info from WordPress options (with defaults)
        $this->company_name = get_option('yolo_company_name', 'YOLO Charters');
        $this->company_address = get_option('yolo_company_address', 'Preveza Main Port, Greece');
        $this->company_phone = get_option('yolo_company_phone', '+30 26820 12345');
        $this->company_email = get_option('yolo_company_email', 'info@yolocharters.com');
        $this->company_website = get_option('yolo_company_website', 'www.yolocharters.com');
        
        // Logo path - check if custom logo exists
        $custom_logo = get_option('yolo_company_logo');
        if ($custom_logo && file_exists($custom_logo)) {
            $this->company_logo_path = $custom_logo;
        } else {
            // Default logo path
            $this->company_logo_path = YOLO_YS_PLUGIN_DIR . 'assets/images/yolo-logo.png';
        }
        
        // Set auto page break with margin for footer
        $this->SetAutoPageBreak(true, 25);
    }

    /**
     * Custom Header - Called automatically on each page
     */
    public function Header() {
        // Header background
        $this->SetFillColor($this->primary_color[0], $this->primary_color[1], $this->primary_color[2]);
        $this->Rect(0, 0, 210, 35, 'F');
        
        // Logo (if exists)
        if ($this->company_logo_path && file_exists($this->company_logo_path)) {
            $this->Image($this->company_logo_path, 10, 5, 25);
        }
        
        // Company name
        $this->SetFont('Arial', 'B', 20);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(40, 8);
        $this->Cell(0, 10, $this->company_name, 0, 1);
        
        // Company tagline/contact
        $this->SetFont('Arial', '', 9);
        $this->SetXY(40, 18);
        $this->Cell(0, 5, $this->company_address . ' | ' . $this->company_phone, 0, 1);
        $this->SetXY(40, 23);
        $this->Cell(0, 5, $this->company_email . ' | ' . $this->company_website, 0, 1);
        
        // Document type badge (right side)
        if ($this->document_type) {
            $this->SetFillColor($this->secondary_color[0], $this->secondary_color[1], $this->secondary_color[2]);
            $badge_text = strtoupper($this->document_type);
            $this->SetFont('Arial', 'B', 12);
            $this->SetXY(140, 10);
            $this->Cell(60, 15, $badge_text, 0, 0, 'C', true);
        }
        
        // Reset text color
        $this->SetTextColor(0, 0, 0);
        $this->Ln(25);
    }

    /**
     * Custom Footer - Called automatically on each page
     */
    public function Footer() {
        // Position at 15mm from bottom
        $this->SetY(-20);
        
        // Separator line
        $this->SetDrawColor($this->light_gray[0], $this->light_gray[1], $this->light_gray[2]);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        $this->Ln(3);
        
        // Footer text
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor($this->dark_gray[0], $this->dark_gray[1], $this->dark_gray[2]);
        
        // Left: Document ID
        $this->Cell(60, 5, 'Document ID: ' . $this->document_id, 0, 0, 'L');
        
        // Center: Generated date
        $this->Cell(70, 5, 'Generated: ' . date('d/m/Y H:i'), 0, 0, 'C');
        
        // Right: Page number
        $this->Cell(60, 5, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'R');
        
        // Reset
        $this->SetTextColor(0, 0, 0);
    }

    /**
     * Section Title with styling
     */
    private function SectionTitle($title, $icon = '') {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor($this->primary_color[0], $this->primary_color[1], $this->primary_color[2]);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 8, '  ' . $icon . ' ' . $title, 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }

    /**
     * Info Row (label: value)
     */
    private function InfoRow($label, $value) {
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor($this->dark_gray[0], $this->dark_gray[1], $this->dark_gray[2]);
        $this->Cell(50, 6, $label . ':', 0, 0);
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 6, $value, 0, 1);
    }

    /**
     * Equipment checklist table with styling
     */
    private function EquipmentTable($checklist_data, $is_checkout = false) {
        if (!$checklist_data || !is_array($checklist_data)) {
            $this->SetFont('Arial', 'I', 10);
            $this->Cell(0, 8, 'No equipment checklist data available.', 0, 1, 'C');
            return;
        }
        
        // Table header
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor($this->light_gray[0], $this->light_gray[1], $this->light_gray[2]);
        $this->SetDrawColor(200, 200, 200);
        
        $this->Cell(90, 8, 'Equipment Item', 1, 0, 'L', true);
        $this->Cell(30, 8, 'Qty', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Condition', 1, 0, 'C', true);
        $this->Cell(35, 8, 'Status', 1, 1, 'C', true);
        
        // Table rows
        $this->SetFont('Arial', '', 9);
        $fill = false;
        $current_category = '';
        
        foreach ($checklist_data as $item) {
            // Check if we need a page break
            if ($this->GetY() > 240) {
                $this->AddPage();
                // Re-draw header row
                $this->SetFont('Arial', 'B', 10);
                $this->SetFillColor($this->light_gray[0], $this->light_gray[1], $this->light_gray[2]);
                $this->Cell(90, 8, 'Equipment Item', 1, 0, 'L', true);
                $this->Cell(30, 8, 'Qty', 1, 0, 'C', true);
                $this->Cell(35, 8, 'Condition', 1, 0, 'C', true);
                $this->Cell(35, 8, 'Status', 1, 1, 'C', true);
                $this->SetFont('Arial', '', 9);
            }
            
            // Category row
            $category = isset($item['category']) ? $item['category'] : '';
            if ($category && $category !== $current_category) {
                $current_category = $category;
                $this->SetFont('Arial', 'B', 9);
                $this->SetFillColor(230, 230, 230);
                $this->Cell(190, 7, $category, 1, 1, 'L', true);
                $this->SetFont('Arial', '', 9);
            }
            
            // Item row
            $itemName = isset($item['name']) ? $item['name'] : (isset($item['item']) ? $item['item'] : 'Unknown');
            $itemQty = isset($item['quantity']) ? $item['quantity'] : '-';
            $isChecked = isset($item['checked']) ? $item['checked'] : false;
            
            // Status color
            if ($isChecked) {
                $status = 'OK';
                $this->SetTextColor(16, 185, 129); // Green
            } else {
                $status = $is_checkout ? 'Damaged/Missing' : 'Missing';
                $this->SetTextColor(220, 38, 38); // Red
            }
            
            $this->SetFillColor(255, 255, 255);
            if ($fill) {
                $this->SetFillColor(250, 250, 250);
            }
            
            $this->SetTextColor(0, 0, 0);
            $this->Cell(90, 7, '   ' . $itemName, 1, 0, 'L', $fill);
            $this->Cell(30, 7, $itemQty, 1, 0, 'C', $fill);
            $this->Cell(35, 7, $isChecked ? 'Good' : 'Check', 1, 0, 'C', $fill);
            
            // Status with color
            if ($isChecked) {
                $this->SetTextColor(16, 185, 129);
            } else {
                $this->SetTextColor(220, 38, 38);
            }
            $this->Cell(35, 7, $status, 1, 1, 'C', $fill);
            $this->SetTextColor(0, 0, 0);
            
            $fill = !$fill;
        }
    }

    /**
     * Signature Box with nice styling
     */
    private function SignatureBox($title, $signature_data, $date, $x_position) {
        $start_y = $this->GetY();
        
        // Box border
        $this->SetDrawColor(200, 200, 200);
        $this->Rect($x_position, $start_y, 85, 45, 'D');
        
        // Title
        $this->SetXY($x_position + 2, $start_y + 2);
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor($this->primary_color[0], $this->primary_color[1], $this->primary_color[2]);
        $this->Cell(81, 6, $title, 0, 1, 'C');
        
        // Signature area
        $this->SetTextColor(0, 0, 0);
        if ($signature_data) {
            try {
                $sig_data = str_replace('data:image/png;base64,', '', $signature_data);
                $sig_decoded = base64_decode($sig_data, true);
                
                if ($sig_decoded !== false) {
                    $sig_file = sys_get_temp_dir() . '/sig_' . uniqid() . '.png';
                    if (file_put_contents($sig_file, $sig_decoded)) {
                        $this->Image($sig_file, $x_position + 17, $start_y + 10, 50, 20);
                        @unlink($sig_file);
                    }
                }
            } catch (Exception $e) {
                $this->SetXY($x_position + 2, $start_y + 18);
                $this->SetFont('Arial', 'I', 9);
                $this->Cell(81, 6, '[Signature Error]', 0, 1, 'C');
            }
        } else {
            // Empty signature line
            $this->SetDrawColor(150, 150, 150);
            $this->Line($x_position + 15, $start_y + 28, $x_position + 70, $start_y + 28);
        }
        
        // Date
        $this->SetXY($x_position + 2, $start_y + 35);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor($this->dark_gray[0], $this->dark_gray[1], $this->dark_gray[2]);
        if ($date) {
            $this->Cell(81, 6, 'Date: ' . date('d/m/Y', strtotime($date)), 0, 1, 'C');
        } else {
            $this->Cell(81, 6, 'Date: ________________', 0, 1, 'C');
        }
        
        $this->SetTextColor(0, 0, 0);
    }

    /**
     * Terms and Conditions Section
     */
    private function TermsSection($is_checkout = false) {
        $this->AddPage();
        $this->SectionTitle('Terms & Conditions');
        
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor($this->dark_gray[0], $this->dark_gray[1], $this->dark_gray[2]);
        
        if ($is_checkout) {
            $terms = array(
                '1. The charterer confirms that all equipment listed above has been returned in the condition stated.',
                '2. Any damages or missing items noted will be assessed and charged according to the charter agreement.',
                '3. The security deposit will be refunded within 14 business days after check-out, minus any deductions for damages.',
                '4. The charterer acknowledges that the yacht has been returned with the agreed fuel level.',
                '5. Both parties agree that this document represents the final condition of the yacht at check-out.',
                '6. Any disputes shall be resolved according to Greek maritime law.',
            );
        } else {
            $terms = array(
                '1. The charterer acknowledges receipt of the yacht and all equipment listed above in good condition.',
                '2. The charterer agrees to return the yacht in the same condition, allowing for normal wear.',
                '3. The charterer is responsible for any damage caused during the charter period.',
                '4. The security deposit held will cover any damages or missing equipment.',
                '5. The charterer must report any incidents or damages immediately to the charter company.',
                '6. Navigation is restricted to the agreed cruising area as specified in the charter agreement.',
                '7. The charterer confirms possession of valid sailing licenses as required by Greek law.',
                '8. Emergency contacts: Coast Guard 108, Company: ' . $this->company_phone,
            );
        }
        
        foreach ($terms as $term) {
            $this->MultiCell(0, 5, $term, 0, 'L');
            $this->Ln(2);
        }
        
        $this->SetTextColor(0, 0, 0);
    }

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
            error_log('YOLO YS PDF: Check-in not found: ' . $checkin_id);
            return false;
        }
        
        // Get yacht data
        $yacht = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_yachts WHERE id = %d",
            $checkin->yacht_id
        ));
        
        if (!$yacht) {
            error_log('YOLO YS PDF: Yacht not found for check-in ID: ' . $checkin_id);
            return false;
        }
        
        // Get booking data
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d",
            $checkin->booking_id
        ));
        
        if (!$booking) {
            error_log('YOLO YS PDF: Booking not found for check-in ID: ' . $checkin_id);
            return false;
        }
        
        // Create PDF
        $pdf = new self();
        $pdf->document_id = 'CHK-IN-' . str_pad($checkin_id, 6, '0', STR_PAD_LEFT);
        $pdf->document_type = 'CHECK-IN';
        $pdf->AliasNbPages();
        $pdf->AddPage();
        
        // Yacht Information Section
        $pdf->SectionTitle('Yacht Information', '⛵');
        $pdf->InfoRow('Yacht Name', $yacht->yacht_name);
        $pdf->InfoRow('Model', $yacht->yacht_model ?? 'N/A');
        $pdf->InfoRow('Year', $yacht->year_built ?? 'N/A');
        $pdf->InfoRow('Length', ($yacht->length ?? 'N/A') . ' m');
        $pdf->Ln(5);
        
        // Booking Information Section
        $pdf->SectionTitle('Booking Information', '📋');
        $pdf->InfoRow('Booking Reference', 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT));
        $pdf->InfoRow('Charter Guest', $booking->customer_name ?? 'N/A');
        $pdf->InfoRow('Email', $booking->customer_email ?? 'N/A');
        $pdf->InfoRow('Phone', $booking->customer_phone ?? 'N/A');
        $pdf->InfoRow('Check-in Date', date('d F Y', strtotime($booking->date_from)));
        $pdf->InfoRow('Check-out Date', date('d F Y', strtotime($booking->date_to)));
        $pdf->InfoRow('Charter Duration', ceil((strtotime($booking->date_to) - strtotime($booking->date_from)) / 86400) . ' days');
        $pdf->Ln(5);
        
        // Equipment Checklist Section
        $pdf->SectionTitle('Equipment Checklist', '🔧');
        $checklist_data = json_decode($checkin->checklist_data, true);
        $pdf->EquipmentTable($checklist_data, false);
        $pdf->Ln(10);
        
        // Signatures Section
        // Check if we have enough space, otherwise add new page
        if ($pdf->GetY() > 200) {
            $pdf->AddPage();
        }
        
        $pdf->SectionTitle('Signatures & Confirmation', '✍️');
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, 'By signing below, both parties confirm that the yacht and equipment have been inspected and the condition is as stated above.', 0, 'L');
        $pdf->Ln(5);
        
        // Two signature boxes side by side
        $sig_y = $pdf->GetY();
        $pdf->SignatureBox('Base Manager Signature', $checkin->signature, $checkin->created_at, 10);
        $pdf->SetY($sig_y);
        $pdf->SignatureBox('Charter Guest Signature', $checkin->guest_signature, $checkin->guest_signed_at, 110);
        
        // Terms & Conditions on new page
        $pdf->TermsSection(false);
        
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
            error_log('YOLO YS PDF: Check-out not found: ' . $checkout_id);
            return false;
        }
        
        // Get yacht data
        $yacht = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bm_yachts WHERE id = %d",
            $checkout->yacht_id
        ));
        
        if (!$yacht) {
            error_log('YOLO YS PDF: Yacht not found for check-out ID: ' . $checkout_id);
            return false;
        }
        
        // Get booking data
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}yolo_bookings WHERE id = %d",
            $checkout->booking_id
        ));
        
        if (!$booking) {
            error_log('YOLO YS PDF: Booking not found for check-out ID: ' . $checkout_id);
            return false;
        }
        
        // Create PDF
        $pdf = new self();
        $pdf->document_id = 'CHK-OUT-' . str_pad($checkout_id, 6, '0', STR_PAD_LEFT);
        $pdf->document_type = 'CHECK-OUT';
        $pdf->AliasNbPages();
        $pdf->AddPage();
        
        // Yacht Information Section
        $pdf->SectionTitle('Yacht Information', '⛵');
        $pdf->InfoRow('Yacht Name', $yacht->yacht_name);
        $pdf->InfoRow('Model', $yacht->yacht_model ?? 'N/A');
        $pdf->InfoRow('Year', $yacht->year_built ?? 'N/A');
        $pdf->InfoRow('Length', ($yacht->length ?? 'N/A') . ' m');
        $pdf->Ln(5);
        
        // Booking Information Section
        $pdf->SectionTitle('Booking Information', '📋');
        $pdf->InfoRow('Booking Reference', 'BK-' . str_pad($booking->id, 6, '0', STR_PAD_LEFT));
        $pdf->InfoRow('Charter Guest', $booking->customer_name ?? 'N/A');
        $pdf->InfoRow('Email', $booking->customer_email ?? 'N/A');
        $pdf->InfoRow('Phone', $booking->customer_phone ?? 'N/A');
        $pdf->InfoRow('Check-in Date', date('d F Y', strtotime($booking->date_from)));
        $pdf->InfoRow('Check-out Date', date('d F Y', strtotime($booking->date_to)));
        $pdf->Ln(5);
        
        // Equipment Checklist Section
        $pdf->SectionTitle('Equipment Return Checklist', '🔧');
        $checklist_data = json_decode($checkout->checklist_data, true);
        $pdf->EquipmentTable($checklist_data, true);
        $pdf->Ln(10);
        
        // Fuel & Condition Notes
        $pdf->SectionTitle('Additional Notes', '📝');
        $pdf->SetFont('Arial', '', 10);
        $pdf->MultiCell(0, 6, 'Fuel Level at Return: ______________________', 0, 'L');
        $pdf->Ln(2);
        $pdf->MultiCell(0, 6, 'General Condition Notes:', 0, 'L');
        $pdf->Rect(10, $pdf->GetY(), 190, 20, 'D');
        $pdf->Ln(25);
        
        // Signatures Section
        if ($pdf->GetY() > 200) {
            $pdf->AddPage();
        }
        
        $pdf->SectionTitle('Signatures & Confirmation', '✍️');
        $pdf->Ln(5);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->MultiCell(0, 5, 'By signing below, both parties confirm that the yacht has been returned and inspected, and agree to the condition noted above.', 0, 'L');
        $pdf->Ln(5);
        
        // Two signature boxes side by side
        $sig_y = $pdf->GetY();
        $pdf->SignatureBox('Base Manager Signature', $checkout->signature, $checkout->created_at, 10);
        $pdf->SetY($sig_y);
        $pdf->SignatureBox('Charter Guest Signature', $checkout->guest_signature, $checkout->guest_signed_at, 110);
        
        // Terms & Conditions
        $pdf->TermsSection(true);
        
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

/**
 * Register WordPress settings for company info
 * Add this to your plugin's settings page
 */
function yolo_register_pdf_settings() {
    register_setting('yolo_pdf_settings', 'yolo_company_name');
    register_setting('yolo_pdf_settings', 'yolo_company_address');
    register_setting('yolo_pdf_settings', 'yolo_company_phone');
    register_setting('yolo_pdf_settings', 'yolo_company_email');
    register_setting('yolo_pdf_settings', 'yolo_company_website');
    register_setting('yolo_pdf_settings', 'yolo_company_logo');
}
add_action('admin_init', 'yolo_register_pdf_settings');

