<?php
/**
 * Database management for yacht storage
 */
class YOLO_YS_Database {
    
    private $table_yachts;
    private $table_products;
    private $table_images;
    private $table_extras;
    private $table_equipment;
    
    public function __construct() {
        global $wpdb;
        $this->table_yachts = $wpdb->prefix . 'yolo_yachts';
        $this->table_products = $wpdb->prefix . 'yolo_yacht_products';
        $this->table_images = $wpdb->prefix . 'yolo_yacht_images';
        $this->table_extras = $wpdb->prefix . 'yolo_yacht_extras';
        $this->table_equipment = $wpdb->prefix . 'yolo_yacht_equipment';
    }
    
    /**
     * Create database tables
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Define table names
        $table_yachts = $wpdb->prefix . 'yolo_yachts';
        $table_products = $wpdb->prefix . 'yolo_yacht_products';
        $table_images = $wpdb->prefix . 'yolo_yacht_images';
        $table_extras = $wpdb->prefix . 'yolo_yacht_extras';
        $table_equipment = $wpdb->prefix . 'yolo_yacht_equipment';
        
        // Yachts table
        $sql_yachts = "CREATE TABLE {$table_yachts} (
            id bigint(20) NOT NULL,
            company_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            model varchar(255) DEFAULT NULL,
            shipyard_id bigint(20) DEFAULT NULL,
            year_of_build int(11) DEFAULT NULL,
            refit_year int(11) DEFAULT NULL,
            home_base varchar(255) DEFAULT NULL,
            length decimal(10,2) DEFAULT NULL,
            beam decimal(10,2) DEFAULT NULL,
            draft decimal(10,2) DEFAULT NULL,
            cabins int(11) DEFAULT NULL,
            wc int(11) DEFAULT NULL,
            berths int(11) DEFAULT NULL,
            max_people_on_board int(11) DEFAULT NULL,
            engine_power int(11) DEFAULT NULL,
            fuel_capacity int(11) DEFAULT NULL,
            water_capacity int(11) DEFAULT NULL,
            description longtext DEFAULT NULL,
            raw_data longtext DEFAULT NULL,
            last_synced datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY company_id (company_id),
            KEY name (name)
        ) $charset_collate;";
        
        // Products table (charter types)
        $sql_products = "CREATE TABLE {$table_products} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            product_type varchar(100) NOT NULL,
            base_price decimal(10,2) DEFAULT NULL,
            currency varchar(10) DEFAULT NULL,
            is_default tinyint(1) DEFAULT 0,
            raw_data longtext DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        
        // Images table
        $sql_images = "CREATE TABLE {$table_images} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            image_url varchar(500) NOT NULL,
            thumbnail_url varchar(500) DEFAULT NULL,
            is_primary tinyint(1) DEFAULT 0,
            sort_order int(11) DEFAULT 0,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        
        // Extras table
        $sql_extras = "CREATE TABLE {$table_extras} (
            id bigint(20) NOT NULL,
            yacht_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            price decimal(10,2) DEFAULT NULL,
            currency varchar(10) DEFAULT NULL,
            obligatory tinyint(1) DEFAULT 0,
            unit varchar(50) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        
        // Equipment table
        $sql_equipment = "CREATE TABLE {$table_equipment} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            equipment_id bigint(20) NOT NULL,
            equipment_name varchar(255) NOT NULL,
            category varchar(100) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        
        dbDelta($sql_yachts);
        dbDelta($sql_products);
        dbDelta($sql_images);
        dbDelta($sql_extras);
        dbDelta($sql_equipment);
        
        update_option('yolo_ys_db_version', '1.0');
    }
    
    /**
     * Store yacht data
     */
    public function store_yacht($yacht_data, $company_id) {
        global $wpdb;
        
        // Prepare yacht data
        $yacht_insert = array(
            'id' => $yacht_data['id'],
            'company_id' => $company_id,
            'name' => $yacht_data['name'],
            'model' => isset($yacht_data['model']) ? $yacht_data['model'] : null,
            'shipyard_id' => isset($yacht_data['shipyardId']) ? $yacht_data['shipyardId'] : null,
            'year_of_build' => isset($yacht_data['year']) ? $yacht_data['year'] : null,
            'refit_year' => $this->parse_refit_year($yacht_data),
            'home_base' => isset($yacht_data['homeBase']) ? $yacht_data['homeBase'] : null,
            'length' => isset($yacht_data['length']) ? $yacht_data['length'] : null,
            'beam' => isset($yacht_data['beam']) ? $yacht_data['beam'] : null,
            'draft' => isset($yacht_data['draught']) ? $yacht_data['draught'] : null,
            'cabins' => isset($yacht_data['cabins']) ? $yacht_data['cabins'] : null,
            'wc' => isset($yacht_data['wc']) ? $yacht_data['wc'] : null,
            'berths' => isset($yacht_data['berths']) ? $yacht_data['berths'] : null,
            'max_people_on_board' => isset($yacht_data['maxPeopleOnBoard']) ? $yacht_data['maxPeopleOnBoard'] : null,
            'engine_power' => isset($yacht_data['enginePower']) ? $yacht_data['enginePower'] : null,
            'fuel_capacity' => isset($yacht_data['fuelCapacity']) ? $yacht_data['fuelCapacity'] : null,
            'water_capacity' => isset($yacht_data['waterCapacity']) ? $yacht_data['waterCapacity'] : null,
            'description' => isset($yacht_data['descriptions'][0]['text']) ? $yacht_data['descriptions'][0]['text'] : null,
            'raw_data' => json_encode($yacht_data),
            'last_synced' => current_time('mysql')
        );
        
        // Insert or update yacht
        $wpdb->replace($this->table_yachts, $yacht_insert);
        
        $yacht_id = $yacht_data['id'];
        
        // Delete old related data
        $wpdb->delete($this->table_products, array('yacht_id' => $yacht_id));
        $wpdb->delete($this->table_images, array('yacht_id' => $yacht_id));
        $wpdb->delete($this->table_extras, array('yacht_id' => $yacht_id));
        $wpdb->delete($this->table_equipment, array('yacht_id' => $yacht_id));
        
        // Store products
        if (isset($yacht_data['products']) && is_array($yacht_data['products'])) {
            foreach ($yacht_data['products'] as $product) {
                $wpdb->insert($this->table_products, array(
                    'yacht_id' => $yacht_id,
                    'product_type' => isset($product['product']) ? $product['product'] : 'Unknown',
                    'base_price' => isset($product['basePrice']) ? $product['basePrice'] : null,
                    'currency' => isset($product['currency']) ? $product['currency'] : 'EUR',
                    'is_default' => isset($product['isDefaultProduct']) ? $product['isDefaultProduct'] : 0,
                    'raw_data' => json_encode($product)
                ));
            }
        }
        
        // Store images
        if (isset($yacht_data['images']) && is_array($yacht_data['images'])) {
            foreach ($yacht_data['images'] as $index => $image) {
                $wpdb->insert($this->table_images, array(
                    'yacht_id' => $yacht_id,
                    'image_url' => $image['url'],
                    'thumbnail_url' => isset($image['thumbnailUrl']) ? $image['thumbnailUrl'] : null,
                    'is_primary' => ($index === 0) ? 1 : 0,
                    'sort_order' => $index
                ));
            }
        }
        
        // Store extras
        if (isset($yacht_data['products'][0]['extras']) && is_array($yacht_data['products'][0]['extras'])) {
            foreach ($yacht_data['products'][0]['extras'] as $extra) {
                $wpdb->insert($this->table_extras, array(
                    'id' => $extra['id'],
                    'yacht_id' => $yacht_id,
                    'name' => $extra['name'],
                    'price' => isset($extra['price']) ? $extra['price'] : null,
                    'currency' => isset($extra['currency']) ? $extra['currency'] : 'EUR',
                    'obligatory' => isset($extra['obligatory']) ? $extra['obligatory'] : 0,
                    'unit' => isset($extra['unit']) ? $extra['unit'] : null
                ));
            }
        }
        
        // Store equipment
        if (isset($yacht_data['equipment']) && is_array($yacht_data['equipment'])) {
            foreach ($yacht_data['equipment'] as $equip) {
                $wpdb->insert($this->table_equipment, array(
                    'yacht_id' => $yacht_id,
                    'equipment_id' => $equip['id'],
                    'equipment_name' => $equip['name'],
                    'category' => isset($equip['category']) ? $equip['category'] : null
                ));
            }
        }
        
        return true;
    }
    
    /**
     * Get all yachts from database
     */
    public function get_all_yachts($company_id = null) {
        global $wpdb;
        
        $where = '';
        if ($company_id) {
            $where = $wpdb->prepare("WHERE company_id = %d", $company_id);
        }
        
        $yachts = $wpdb->get_results("SELECT * FROM {$this->table_yachts} {$where} ORDER BY name ASC");
        
        // Enrich with images
        foreach ($yachts as &$yacht) {
            $yacht->images = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$this->table_images} WHERE yacht_id = %d ORDER BY sort_order ASC",
                $yacht->id
            ));
            
            $yacht->products = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$this->table_products} WHERE yacht_id = %d",
                $yacht->id
            ));
        }
        
        return $yachts;
    }
    
    /**
     * Parse refit year from yearNote field
     */
    private function parse_refit_year($yacht_data) {
        if (isset($yacht_data['yearNote']) && !empty($yacht_data['yearNote'])) {
            // Extract year from "Refit 2026" or "Refit: 2025" format
            if (preg_match('/(\d{4})/', $yacht_data['yearNote'], $matches)) {
                return intval($matches[1]);
            }
        }
        return null;
    }
    
    /**
     * Get sync stats
     */
    public function get_sync_stats() {
        global $wpdb;
        
        $stats = array();
        
        // Total yachts
        $stats['total_yachts'] = $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_yachts}");
        
        // Per company
        $stats['yolo_yachts'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_yachts} WHERE company_id = %d",
            get_option('yolo_ys_my_company_id', 7850)
        ));
        
        // Last sync
        $stats['last_sync'] = $wpdb->get_var("SELECT MAX(last_synced) FROM {$this->table_yachts}");
        
        return $stats;
    }
}
