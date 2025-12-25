<?php
/**
 * Facebook Product Catalog Feed Generator
 * 
 * @package YOLO_Yacht_Search
 * @since 86.1
 */

if (!defined('ABSPATH')) {
    exit;
}

class YOLO_YS_Facebook_Catalog {
    
    /**
     * All company IDs for catalog (YOLO + Partners)
     * v86.4: Now includes YOLO company alongside partners
     */
    private $catalog_company_ids;
    
    /**
     * Constructor - load YOLO + partner companies from settings
     * v86.4: Combined YOLO and partners for unified catalog
     */
    public function __construct() {
        // Get YOLO company ID
        $yolo_company = intval(get_option('yolo_ys_my_company_id', 7850));
        
        // Get partner company IDs
        $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
        $partners = array_filter(array_map('intval', array_map('trim', explode(',', $friend_companies))));
        
        // Fallback for partners if empty
        if (empty($partners)) {
            $partners = array(4366, 3604, 6711);
        }
        
        // Combine: YOLO + Partners for catalog
        // v86.5 FIX: Keep as strings to match database column type
        $this->catalog_company_ids = array_unique(array_merge(array(strval($yolo_company)), array_map('strval', $partners)));
    }
    
    /**
     * Brand mapping for models that don't include brand prefix
     */
    private $brand_mapping = array(
        // Beneteau family
        'Oceanis' => 'Beneteau',
        'First' => 'Beneteau',
        
        // Jeanneau family
        'Sun Odyssey' => 'Jeanneau',
        'Sun Fast' => 'Jeanneau',
        'Merry Fisher' => 'Jeanneau',
        
        // Fountaine Pajot family
        'Salina' => 'Fountaine Pajot',
        'Saba' => 'Fountaine Pajot',
        'Isla' => 'Fountaine Pajot',
        'Lucia' => 'Fountaine Pajot',
        'Elba' => 'Fountaine Pajot',
        'Astrea' => 'Fountaine Pajot',
        'Alegria' => 'Fountaine Pajot',
        'Saona' => 'Fountaine Pajot',
        'Tanna' => 'Fountaine Pajot',
        'Aura' => 'Fountaine Pajot',
        
        // Lagoon (Beneteau Group)
        'Lagoon' => 'Lagoon',
        
        // Other common models
        'Dufour' => 'Dufour',
        'Bavaria' => 'Bavaria',
        'Hanse' => 'Hanse',
        'Elan' => 'Elan',
        'Bali' => 'Bali',
        'Catana' => 'Catana',
        'Nautitech' => 'Nautitech',
        'Leopard' => 'Leopard',
        'Azimut' => 'Azimut',
        'Sunseeker' => 'Sunseeker',
        'Princess' => 'Princess',
        'Excess' => 'Excess',
        'Moody' => 'Moody',
        'Dehler' => 'Dehler',
        'Hallberg-Rassy' => 'Hallberg-Rassy',
        'X-Yachts' => 'X-Yachts',
        'Grand Soleil' => 'Grand Soleil',
        'More' => 'More',
    );
    
    /**
     * Extract brand from model name
     * 
     * @param string $model The yacht model name
     * @return string The brand name
     */
    public function extract_brand_from_model($model) {
        if (empty($model)) {
            return 'Other';
        }
        
        $model = trim($model);
        
        // Check brand mapping (covers most cases)
        foreach ($this->brand_mapping as $prefix => $brand) {
            if (stripos($model, $prefix) === 0) {
                return $brand;
            }
        }
        
        // If model contains a number, the word before it might be the brand
        // e.g., "Bavaria C42" -> "Bavaria"
        if (preg_match('/^([A-Za-z\-]+)\s/', $model, $matches)) {
            $potential_brand = trim($matches[1]);
            // v86.2 FIX: Check if it's a known brand (either as key or value)
            if (array_key_exists($potential_brand, $this->brand_mapping)) {
                return $this->brand_mapping[$potential_brand];
            }
            if (in_array($potential_brand, $this->brand_mapping)) {
                return $potential_brand;
            }
        }
        
        return 'Other';
    }
    
    /**
     * Determine boat type from model name
     * 
     * @param string $model The yacht model name
     * @return string Sailboat or Catamaran
     */
    public function get_boat_type($model) {
        $catamaran_brands = array(
            'Lagoon', 'Fountaine Pajot', 'Bali', 'Catana', 'Nautitech', 
            'Leopard', 'Excess', 'Saona', 'Saba', 'Lucia', 'Isla', 
            'Elba', 'Astrea', 'Alegria', 'Tanna', 'Aura', 'Salina'
        );
        
        foreach ($catamaran_brands as $cat_brand) {
            if (stripos($model, $cat_brand) !== false) {
                return 'Catamaran';
            }
        }
        
        return 'Sailboat';
    }
    
    /**
     * Update starting_from_price for all partner boats based on minimum offer price
     * Called after offers sync
     */
    public function update_partner_starting_prices() {
        global $wpdb;
        
        // v86.4: Early return if no companies configured
        if (empty($this->catalog_company_ids)) {
            error_log('YOLO Facebook Catalog: No companies configured for price update');
            return 0;
        }
        
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
        $custom_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        
        // Get all catalog yachts (YOLO + Partners) with their minimum prices from offers
        // v86.5 FIX: Use %s for string company_ids
        $partner_placeholders = implode(',', array_fill(0, count($this->catalog_company_ids), '%s'));
        
        $sql = $wpdb->prepare(
            "SELECT 
                y.id as yacht_id,
                y.model,
                MIN(p.price) as min_price
            FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE y.company_id IN ({$partner_placeholders})
            AND (y.status = 'active' OR y.status IS NULL)
            AND p.date_from >= %s
            GROUP BY y.id, y.model",
            ...array_merge(
                $this->catalog_company_ids,
                array(date('Y-m-d'))
            )
        );
        
        $results = $wpdb->get_results($sql);
        
        if (empty($results)) {
            error_log('YOLO Facebook Catalog: No partner boats with prices found');
            return 0;
        }
        
        $updated_count = 0;
        
        foreach ($results as $row) {
            if (empty($row->min_price) || $row->min_price <= 0) {
                continue;
            }
            
            // Check if custom settings row exists
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$custom_table} WHERE yacht_id = %s",
                $row->yacht_id
            ));
            
            if ($existing) {
                // Update existing row
                $wpdb->update(
                    $custom_table,
                    array('starting_from_price' => floatval($row->min_price)),
                    array('yacht_id' => $row->yacht_id),
                    array('%f'),
                    array('%s')
                );
            } else {
                // Insert new row
                $wpdb->insert(
                    $custom_table,
                    array(
                        'yacht_id' => $row->yacht_id,
                        'starting_from_price' => floatval($row->min_price)
                    ),
                    array('%s', '%f')
                );
            }
            
            $updated_count++;
        }
        
        error_log("YOLO Facebook Catalog: Updated starting prices for {$updated_count} partner boats");
        
        // Update last catalog sync time
        update_option('yolo_ys_last_fb_catalog_update', current_time('mysql'));
        
        return $updated_count;
    }
    
    /**
     * Get partner boats for catalog
     * 
     * @return array Array of yacht objects
     */
    public function get_partner_boats() {
        global $wpdb;
        
        // v86.4: Early return if no companies configured
        if (empty($this->catalog_company_ids)) {
            return array();
        }
        
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        $custom_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        $images_table = $wpdb->prefix . 'yolo_yacht_images';
        
        // v86.5 FIX: Use %s for string company_ids
        $partner_placeholders = implode(',', array_fill(0, count($this->catalog_company_ids), '%s'));
        
        $sql = $wpdb->prepare(
            "SELECT 
                y.id as yacht_id,
                y.model,
                y.slug,
                y.description,
                y.cabins,
                y.berths,
                y.home_base,
                y.build_year,
                y.company_id,
                COALESCE(c.starting_from_price, 0) as starting_from_price,
                COALESCE(c.custom_description, '') as custom_description
            FROM {$yachts_table} y
            LEFT JOIN {$custom_table} c ON y.id = c.yacht_id
            WHERE y.company_id IN ({$partner_placeholders})
            AND (y.status = 'active' OR y.status IS NULL)
            AND COALESCE(c.starting_from_price, 0) > 0
            ORDER BY y.model ASC",
            ...$this->catalog_company_ids
        );
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Get images for a yacht
     * 
     * @param string $yacht_id The yacht ID
     * @return array Array of image URLs
     */
    public function get_yacht_images($yacht_id) {
        global $wpdb;
        
        $images_table = $wpdb->prefix . 'yolo_yacht_images';
        $custom_media_table = $wpdb->prefix . 'yolo_yacht_custom_media';
        $custom_settings_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        
        // First check if yacht uses custom media
        $use_custom = $wpdb->get_var($wpdb->prepare(
            "SELECT use_custom_media FROM {$custom_settings_table} WHERE yacht_id = %s",
            $yacht_id
        ));
        
        if ($use_custom) {
            // Get custom media images
            $custom_images = $wpdb->get_col($wpdb->prepare(
                "SELECT media_url FROM {$custom_media_table} WHERE yacht_id = %s AND media_type = 'image' ORDER BY sort_order ASC LIMIT 11",
                $yacht_id
            ));
            
            if (!empty($custom_images)) {
                return $custom_images;
            }
        }
        
        // Fall back to API images
        $api_images = $wpdb->get_col($wpdb->prepare(
            "SELECT image_url FROM {$images_table} WHERE yacht_id = %s ORDER BY sort_order ASC LIMIT 11",
            $yacht_id
        ));
        
        return $api_images ?: array();
    }
    
    /**
     * Generate the CSV feed content
     * 
     * @return string CSV content
     */
    public function generate_feed() {
        $boats = $this->get_partner_boats();
        
        // CSV Header
        $csv_lines = array();
        $csv_lines[] = 'id,title,description,price,link,image_link,additional_image_link,brand,availability,condition,custom_label_0,custom_label_1,custom_label_2,custom_label_3,custom_label_4';
        
        if (empty($boats)) {
            return implode("\n", $csv_lines);
        }
        
        $site_url = home_url();
        $yacht_base_url = $site_url . '/yacht/';
        
        foreach ($boats as $boat) {
            // Get images
            $images = $this->get_yacht_images($boat->yacht_id);
            
            // Skip if no images
            if (empty($images)) {
                continue;
            }
            
            // Primary image
            $primary_image = $images[0];
            
            // Additional images (up to 10)
            $additional_images = array_slice($images, 1, 10);
            $additional_images_str = implode(',', $additional_images);
            
            // Description - use custom if available, otherwise API description
            $description = !empty($boat->custom_description) ? $boat->custom_description : $boat->description;
            $description = wp_strip_all_tags($description);
            $description = preg_replace('/\s+/', ' ', $description); // Normalize whitespace
            $description = substr($description, 0, 5000); // Facebook limit
            
            // Price
            $price = number_format($boat->starting_from_price, 2, '.', '') . ' EUR';
            
            // Link - v86.2 FIX: Add fallback for empty slugs
            if (!empty($boat->slug)) {
                $link = $yacht_base_url . $boat->slug . '/';
            } else {
                $link = home_url('/yacht-details/?yacht_id=' . $boat->yacht_id);
            }
            
            // Brand
            $brand = $this->extract_brand_from_model($boat->model);
            
            // Boat type
            $boat_type = $this->get_boat_type($boat->model);
            
            // Custom labels
            $custom_label_0 = $boat_type; // Sailboat/Catamaran
            $custom_label_1 = !empty($boat->cabins) ? $boat->cabins : '';
            $custom_label_2 = !empty($boat->berths) ? $boat->berths : '';
            $custom_label_3 = !empty($boat->home_base) ? $boat->home_base : '';
            $custom_label_4 = !empty($boat->build_year) ? $boat->build_year : '';
            
            // Build CSV row
            $row = array(
                $this->csv_escape($boat->yacht_id, true),  // v86.2: Force quote large numbers
                $this->csv_escape($boat->model),
                $this->csv_escape($description),
                $this->csv_escape($price),
                $this->csv_escape($link),
                $this->csv_escape($primary_image),
                $this->csv_escape($additional_images_str),
                $this->csv_escape($brand),
                'in stock',
                'used',
                $this->csv_escape($custom_label_0),
                $this->csv_escape($custom_label_1),
                $this->csv_escape($custom_label_2),
                $this->csv_escape($custom_label_3),
                $this->csv_escape($custom_label_4),
            );
            
            $csv_lines[] = implode(',', $row);
        }
        
        return implode("\n", $csv_lines);
    }
    
    /**
     * Escape value for CSV
     * v86.2 FIX: Added force_quote parameter for large numbers
     * 
     * @param string $value The value to escape
     * @param bool $force_quote Force quoting (for large numbers that Excel might misinterpret)
     * @return string Escaped value
     */
    private function csv_escape($value, $force_quote = false) {
        if ($value === null || $value === '') {
            return '""';
        }
        
        $value = (string) $value;
        $value = str_replace('"', '""', $value);
        
        // v86.2 FIX: Always quote if forced (for long numbers like yacht IDs)
        if ($force_quote || strpos($value, ',') !== false || strpos($value, '"') !== false || strpos($value, "\n") !== false) {
            return '"' . $value . '"';
        }
        
        return $value;
    }
    
    /**
     * Output the feed with proper headers
     */
    public function output_feed() {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: inline; filename="facebook-yacht-catalog.csv"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        
        echo $this->generate_feed();
        exit;
    }
    
    /**
     * Get the feed URL
     * 
     * @return string The feed URL
     */
    public function get_feed_url() {
        return home_url('/facebook-catalog-feed/');
    }
    
    /**
     * Get catalog stats for admin display
     * 
     * @return array Stats array
     */
    public function get_stats() {
        global $wpdb;
        
        $yachts_table = $wpdb->prefix . 'yolo_yachts';
        $custom_table = $wpdb->prefix . 'yolo_yacht_custom_settings';
        
        // v86.5 FIX: Use %s for string company_ids
        $partner_placeholders = implode(',', array_fill(0, count($this->catalog_company_ids), '%s'));
        
        // Total catalog boats (YOLO + Partners)
        $total = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$yachts_table} 
            WHERE company_id IN ({$partner_placeholders})
            AND (status = 'active' OR status IS NULL)",
            ...$this->catalog_company_ids
        ));
        
        // Catalog boats with prices
        $with_prices = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$yachts_table} y
            LEFT JOIN {$custom_table} c ON y.id = c.yacht_id
            WHERE y.company_id IN ({$partner_placeholders})
            AND (y.status = 'active' OR y.status IS NULL)
            AND COALESCE(c.starting_from_price, 0) > 0",
            ...$this->catalog_company_ids
        ));
        
        $last_update = get_option('yolo_ys_last_fb_catalog_update', 'Never');
        
        return array(
            'total_partner_boats' => intval($total),
            'boats_with_prices' => intval($with_prices),
            'last_update' => $last_update,
        );
    }
}
