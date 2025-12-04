<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database class for yacht prices
 */
class YOLO_YS_Database_Prices {
    
    /**
     * Create prices table
     */
    public static function create_prices_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id varchar(255) NOT NULL,
            date_from datetime NOT NULL,
            date_to datetime NOT NULL,
            product varchar(100) NOT NULL,
            price decimal(10,2) NOT NULL,
            currency varchar(10) NOT NULL,
            start_price decimal(10,2) DEFAULT NULL,
            discount_percentage decimal(5,2) DEFAULT NULL,
            last_synced datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id),
            KEY date_from (date_from),
            KEY date_to (date_to)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Store price data
     */
    public static function store_price($price_data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        // Check if price already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE yacht_id = %s AND date_from = %s AND date_to = %s AND product = %s",
            $price_data['yachtId'],
            $price_data['dateFrom'],
            $price_data['dateTo'],
            $price_data['product']
        ));
        
        $data = array(
            'yacht_id' => $price_data['yachtId'],
            'date_from' => $price_data['dateFrom'],
            'date_to' => $price_data['dateTo'],
            'product' => $price_data['product'],
            'price' => $price_data['price'],
            'currency' => $price_data['currency'],
            'start_price' => isset($price_data['startPrice']) ? $price_data['startPrice'] : null,
            'discount_percentage' => isset($price_data['discountPercentage']) ? $price_data['discountPercentage'] : null,
            'last_synced' => current_time('mysql')
        );
        
        if ($existing) {
            // Update
            $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing->id)
            );
        } else {
            // Insert
            $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Get prices for a yacht
     */
    public static function get_yacht_prices($yacht_id, $limit = 52) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        error_log('YOLO YS: Getting prices for yacht_id: ' . $yacht_id);
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE yacht_id = %s 
            AND date_from >= NOW()
            ORDER BY date_from ASC
            LIMIT %d",
            $yacht_id,
            $limit
        ));
        
        error_log('YOLO YS: Found ' . count($results) . ' price records');
        
        if (empty($results)) {
            // Debug: Check if ANY prices exist for this yacht
            $all_prices = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE yacht_id = %s",
                $yacht_id
            ));
            error_log('YOLO YS: Total prices for yacht (including past): ' . $all_prices);
            
            // Debug: List all yacht IDs in prices table
            $sample_ids = $wpdb->get_col("SELECT DISTINCT yacht_id FROM $table_name LIMIT 5");
            error_log('YOLO YS: Sample yacht_ids in prices table: ' . implode(', ', $sample_ids));
        }
        
        return $results;
    }
    
    /**
     * Get minimum price for a yacht
     */
    public static function get_min_price($yacht_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT MIN(price) as min_price, currency 
            FROM $table_name 
            WHERE yacht_id = %s 
            AND date_from >= NOW()
            GROUP BY currency
            ORDER BY min_price ASC
            LIMIT 1",
            $yacht_id
        ));
    }
    
    /**
     * Store offer data (from /offers endpoint)
     * This is the preferred method for storing weekly charter availability and prices
     */
    public static function store_offer($offer_data, $company_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        // Check if offer already exists
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE yacht_id = %s AND date_from = %s AND date_to = %s AND product = %s",
            $offer_data['yachtId'],
            $offer_data['dateFrom'],
            $offer_data['dateTo'],
            isset($offer_data['product']) ? $offer_data['product'] : 'Bareboat'
        ));
        
        $data = array(
            'yacht_id' => $offer_data['yachtId'],
            'date_from' => $offer_data['dateFrom'],
            'date_to' => $offer_data['dateTo'],
            'product' => isset($offer_data['product']) ? $offer_data['product'] : 'Bareboat',
            'price' => $offer_data['price'],
            'currency' => isset($offer_data['currency']) ? $offer_data['currency'] : 'EUR',
            'start_price' => isset($offer_data['startPrice']) ? $offer_data['startPrice'] : null,
            'discount_percentage' => isset($offer_data['discountPercentage']) ? $offer_data['discountPercentage'] : null,
            'last_synced' => current_time('mysql')
        );
        
        if ($existing) {
            // Update
            $wpdb->update(
                $table_name,
                $data,
                array('id' => $existing->id)
            );
        } else {
            // Insert
            $wpdb->insert($table_name, $data);
        }
    }
    
    /**
     * Delete old prices
     */
    public static function delete_old_prices() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        $wpdb->query("DELETE FROM $table_name WHERE date_to < NOW()");
    }
    
    /**
     * Delete old offers (alias for delete_old_prices)
     */
    public static function delete_old_offers() {
        self::delete_old_prices();
    }
    
    /**
     * Ensure unique index exists for fast REPLACE operations
     */
    public static function ensure_unique_index() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        // Check if index exists
        $index_exists = $wpdb->get_var("SHOW INDEX FROM {$table_name} WHERE Key_name = 'yacht_date_product'");
        
        if (!$index_exists) {
            // Add unique index for faster REPLACE operations
            $wpdb->query("ALTER TABLE {$table_name} ADD UNIQUE INDEX yacht_date_product (yacht_id, date_from, date_to, product)");
            error_log('YOLO YS: Created unique index on yacht_prices table');
        }
    }
    
    /**
     * Store offers in batch (much faster than individual inserts)
     * Uses REPLACE INTO with prepared values
     */
    public static function store_offers_batch($offers, $company_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        if (empty($offers)) {
            return;
        }
        
        // Build batch insert query
        $values = array();
        $placeholders = array();
        
        foreach ($offers as $offer) {
            $yacht_id = isset($offer['yachtId']) ? $offer['yachtId'] : '';
            $date_from = isset($offer['dateFrom']) ? $offer['dateFrom'] : '';
            $date_to = isset($offer['dateTo']) ? $offer['dateTo'] : '';
            $product = isset($offer['product']) ? $offer['product'] : 'Bareboat';
            $price = isset($offer['price']) ? floatval($offer['price']) : 0;
            $currency = isset($offer['currency']) ? $offer['currency'] : 'EUR';
            $start_price = isset($offer['startPrice']) ? floatval($offer['startPrice']) : $price;
            $discount_percentage = isset($offer['discountPercentage']) ? floatval($offer['discountPercentage']) : 0;
            
            // Skip invalid offers
            if (empty($yacht_id) || empty($date_from) || empty($date_to)) {
                continue;
            }
            
            $placeholders[] = "(%s, %s, %s, %s, %f, %s, %f, %f, %s)";
            $values[] = $yacht_id;
            $values[] = $date_from;
            $values[] = $date_to;
            $values[] = $product;
            $values[] = $price;
            $values[] = $currency;
            $values[] = $start_price;
            $values[] = $discount_percentage;
            $values[] = current_time('mysql');
        }
        
        if (empty($placeholders)) {
            return;
        }
        
        // Use REPLACE INTO to handle duplicates
        $sql = "REPLACE INTO {$table_name} 
                (yacht_id, date_from, date_to, product, price, currency, start_price, discount_percentage, last_synced)
                VALUES " . implode(', ', $placeholders);
        
        $wpdb->query($wpdb->prepare($sql, $values));
        
        if ($wpdb->last_error) {
            error_log('YOLO YS: Batch insert error: ' . $wpdb->last_error);
        }
    }
}
