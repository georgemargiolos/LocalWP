<?php
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
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name 
            WHERE yacht_id = %s 
            AND date_from >= NOW()
            ORDER BY date_from ASC
            LIMIT %d",
            $yacht_id,
            $limit
        ));
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
}
