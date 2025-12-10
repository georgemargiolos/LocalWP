<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Database management for yacht storage
 */
class YOLO_YS_Database {
    
    private $table_yachts;
    private $table_products;
    private $table_images;
    private $table_extras;
    private $table_equipment;
    private $table_equipment_catalog;
    private $equipment_cache = null; // Cache for equipment catalog
    
    public function __construct() {
        global $wpdb;
        $this->table_yachts = $wpdb->prefix . 'yolo_yachts';
        $this->table_products = $wpdb->prefix . 'yolo_yacht_products';
        $this->table_images = $wpdb->prefix . 'yolo_yacht_images';
        $this->table_extras = $wpdb->prefix . 'yolo_yacht_extras';
        $this->table_equipment = $wpdb->prefix . 'yolo_yacht_equipment';
        $this->table_equipment_catalog = $wpdb->prefix . 'yolo_equipment_catalog';
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
        $table_equipment_catalog = $wpdb->prefix . 'yolo_equipment_catalog';
        
        // Yachts table
        $sql_yachts = "CREATE TABLE {$table_yachts} (
            id bigint(20) NOT NULL,
            company_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            model varchar(255) DEFAULT NULL,
            type varchar(100) DEFAULT NULL,
            shipyard_id bigint(20) DEFAULT NULL,
            year_of_build int(11) DEFAULT NULL,
            refit_year int(11) DEFAULT NULL,
            home_base varchar(255) DEFAULT NULL,
            length decimal(10,2) DEFAULT NULL,
            beam decimal(10,2) DEFAULT NULL,
            draft decimal(10,2) DEFAULT NULL,
            deposit decimal(10,2) DEFAULT NULL,
            checkin_time varchar(10) DEFAULT NULL,
            checkout_time varchar(10) DEFAULT NULL,
            checkin_day tinyint(1) DEFAULT NULL,
            cabins int(11) DEFAULT NULL,
            wc int(11) DEFAULT NULL,
            berths int(11) DEFAULT NULL,
            max_people_on_board int(11) DEFAULT NULL,
            engine_power int(11) DEFAULT NULL,
            fuel_capacity int(11) DEFAULT NULL,
            water_capacity int(11) DEFAULT NULL,
            description longtext DEFAULT NULL,
            cancellation_policy longtext DEFAULT NULL,
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
            payableInBase tinyint(1) DEFAULT 0,
            PRIMARY KEY  (id, yacht_id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        
        // Equipment table
        $sql_equipment = "CREATE TABLE {$table_equipment} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            equipment_id bigint(20) NOT NULL,
            equipment_name varchar(255) DEFAULT NULL,
            category varchar(100) DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id)
        ) $charset_collate;";
        
        // Equipment catalog table (master list of all equipment)
        $sql_equipment_catalog = "CREATE TABLE {$table_equipment_catalog} (
            id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            last_synced datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Bookings table
        $table_bookings = $wpdb->prefix . 'yolo_bookings';
        $sql_bookings = "CREATE TABLE {$table_bookings} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id bigint(20) NOT NULL,
            yacht_name varchar(255) NOT NULL,
            date_from date NOT NULL,
            date_to date NOT NULL,
            total_price decimal(10,2) NOT NULL,
            deposit_paid decimal(10,2) NOT NULL,
            remaining_balance decimal(10,2) NOT NULL,
            currency varchar(10) DEFAULT 'EUR',
            customer_email varchar(255) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_phone varchar(50) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            stripe_session_id varchar(255) DEFAULT NULL,
            stripe_payment_intent varchar(255) DEFAULT NULL,
            payment_status varchar(50) DEFAULT 'pending',
            booking_status varchar(50) DEFAULT 'pending',
            booking_manager_id varchar(255) DEFAULT NULL,
            bm_reservation_id varchar(255) DEFAULT NULL,
            bm_sync_error text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id),
            KEY customer_email (customer_email),
            KEY user_id (user_id),
            KEY stripe_session_id (stripe_session_id),
            KEY bm_reservation_id (bm_reservation_id)
        ) $charset_collate;";
        
        // License uploads table
        $table_license_uploads = $wpdb->prefix . 'yolo_license_uploads';
        $sql_license_uploads = "CREATE TABLE {$table_license_uploads} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            file_type varchar(20) NOT NULL,
            file_path text NOT NULL,
            file_url text NOT NULL,
            uploaded_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id),
            KEY user_id (user_id)
        ) $charset_collate;";
        
        dbDelta($sql_yachts);
        dbDelta($sql_products);
        dbDelta($sql_images);
        dbDelta($sql_extras);
        dbDelta($sql_equipment);
        dbDelta($sql_equipment_catalog);
        dbDelta($sql_bookings);
        dbDelta($sql_license_uploads);

        // Crew list table
        $table_crew_list = $wpdb->prefix . 'yolo_crew_list';
        $sql_crew_list = "CREATE TABLE {$table_crew_list} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            crew_member_index int(2) NOT NULL,
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            sex varchar(10) NOT NULL,
            id_type varchar(20) NOT NULL,
            id_number varchar(100) NOT NULL,
            birth_date date NOT NULL,
            role varchar(20) NOT NULL,
            mobile_number varchar(50) NOT NULL,
            nationality varchar(100) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY booking_member (booking_id, crew_member_index),
            KEY user_id (user_id)
        ) $charset_collate;";
        dbDelta($sql_crew_list);
        
        // Icons mapping table
        $table_icons = $wpdb->prefix . 'yolo_feature_icons';
        $sql_icons = "CREATE TABLE {$table_icons} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            feature_type varchar(50) NOT NULL COMMENT 'Type: extra, equipment, spec, section',
            feature_name varchar(255) NOT NULL COMMENT 'Name of the feature/extra/equipment',
            icon_class varchar(100) NOT NULL DEFAULT 'fa-solid fa-circle' COMMENT 'FontAwesome class',
            icon_style varchar(50) DEFAULT NULL COMMENT 'Additional CSS style',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY feature_unique (feature_type, feature_name),
            KEY feature_type (feature_type)
        ) $charset_collate;";
        dbDelta($sql_icons);
        
        // Admin to Guest Documents table
        $table_admin_documents = $wpdb->prefix . 'yolo_admin_documents';
        $sql_admin_documents = "CREATE TABLE {$table_admin_documents} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) NOT NULL,
            uploaded_by bigint(20) NOT NULL COMMENT 'Admin user ID',
            file_name varchar(255) NOT NULL,
            file_path text NOT NULL,
            file_url text NOT NULL,
            file_size bigint(20) DEFAULT NULL,
            file_type varchar(100) DEFAULT NULL,
            description text DEFAULT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id),
            KEY uploaded_by (uploaded_by)
        ) $charset_collate;";
        dbDelta($sql_admin_documents);
        
        update_option('yolo_ys_db_version', '1.8');
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
            'type' => isset($yacht_data['kind']) ? $yacht_data['kind'] : null,
            'shipyard_id' => isset($yacht_data['shipyardId']) ? $yacht_data['shipyardId'] : null,
            'year_of_build' => isset($yacht_data['year']) ? $yacht_data['year'] : null,
            'refit_year' => $this->parse_refit_year($yacht_data),
            'home_base' => isset($yacht_data['homeBase']) ? $yacht_data['homeBase'] : null,
            'length' => isset($yacht_data['length']) ? $yacht_data['length'] : null,
            'beam' => isset($yacht_data['beam']) ? $yacht_data['beam'] : null,
            'draft' => isset($yacht_data['draught']) ? $yacht_data['draught'] : null,
            'deposit' => isset($yacht_data['deposit']) ? $yacht_data['deposit'] : null,
            'checkin_time' => isset($yacht_data['defaultCheckInTime']) ? $yacht_data['defaultCheckInTime'] : null,
            'checkout_time' => isset($yacht_data['defaultCheckOutTime']) ? $yacht_data['defaultCheckOutTime'] : null,
            'checkin_day' => isset($yacht_data['defaultCheckInDay']) ? $yacht_data['defaultCheckInDay'] : null,
            'cabins' => isset($yacht_data['cabins']) ? $yacht_data['cabins'] : null,
            'wc' => isset($yacht_data['wc']) ? $yacht_data['wc'] : null,
            'berths' => isset($yacht_data['berths']) ? $yacht_data['berths'] : null,
            'max_people_on_board' => isset($yacht_data['maxPeopleOnBoard']) ? $yacht_data['maxPeopleOnBoard'] : null,
            'engine_power' => isset($yacht_data['enginePower']) ? $yacht_data['enginePower'] : null,
            'fuel_capacity' => isset($yacht_data['fuelCapacity']) ? $yacht_data['fuelCapacity'] : null,
            'water_capacity' => isset($yacht_data['waterCapacity']) ? $yacht_data['waterCapacity'] : null,
            'description' => isset($yacht_data['descriptions'][0]['text']) ? $yacht_data['descriptions'][0]['text'] : null,
            'cancellation_policy' => isset($yacht_data['cancellationPolicy']) ? $yacht_data['cancellationPolicy'] : null,
            'raw_data' => json_encode($yacht_data),
            'last_synced' => current_time('mysql')
        );
        
        // Insert or update yacht
        $wpdb->replace($this->table_yachts, $yacht_insert);
        
        $yacht_id = $yacht_data['id'];
        
        // Delete old related data
        $wpdb->delete($this->table_products, array('yacht_id' => $yacht_id));
        
        // Delete old local image files before deleting database records
        $old_images = $wpdb->get_results($wpdb->prepare(
            "SELECT image_url, thumbnail_url FROM {$this->table_images} WHERE yacht_id = %s",
            $yacht_id
        ));
        
        $upload_dir = wp_upload_dir();
        $yolo_images_dir = $upload_dir['basedir'] . '/yolo-yacht-images';
        
        foreach ($old_images as $old_image) {
            // Delete main image if it's a local file
            if (!empty($old_image->image_url) && strpos($old_image->image_url, $upload_dir['baseurl']) !== false) {
                $filename = basename($old_image->image_url);
                $file_path = $yolo_images_dir . '/' . $filename;
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            
            // Delete thumbnail if it's a local file
            if (!empty($old_image->thumbnail_url) && strpos($old_image->thumbnail_url, $upload_dir['baseurl']) !== false) {
                $thumb_filename = basename($old_image->thumbnail_url);
                $thumb_path = $yolo_images_dir . '/' . $thumb_filename;
                if (file_exists($thumb_path)) {
                    @unlink($thumb_path);
                }
            }
        }
        
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
        
        // Store images - Download and save locally
        if (isset($yacht_data['images']) && is_array($yacht_data['images'])) {
            // Create upload directory if it doesn't exist
            $upload_dir = wp_upload_dir();
            $yolo_images_dir = $upload_dir['basedir'] . '/yolo-yacht-images';
            $yolo_images_url = $upload_dir['baseurl'] . '/yolo-yacht-images';
            
            if (!file_exists($yolo_images_dir)) {
                wp_mkdir_p($yolo_images_dir);
            }
            
            foreach ($yacht_data['images'] as $index => $image) {
                if (empty($image['url'])) {
                    continue; // Skip if no URL
                }
                
                // Download image from Booking Manager CDN
                $remote_url = $image['url'];
                $filename = basename(parse_url($remote_url, PHP_URL_PATH));
                $local_path = $yolo_images_dir . '/' . $filename;
                $local_url = $yolo_images_url . '/' . $filename;
                
                // Download if not already exists
                if (!file_exists($local_path)) {
                    $image_data = @file_get_contents($remote_url);
                    if ($image_data !== false) {
                        file_put_contents($local_path, $image_data);
                    } else {
                        // If download fails, use CDN URL as fallback
                        $local_url = $remote_url;
                    }
                }
                
                // Download thumbnail if available
                $thumbnail_local_url = null;
                if (!empty($image['thumbnailUrl'])) {
                    $thumb_remote_url = $image['thumbnailUrl'];
                    $thumb_filename = basename(parse_url($thumb_remote_url, PHP_URL_PATH));
                    $thumb_local_path = $yolo_images_dir . '/' . $thumb_filename;
                    $thumbnail_local_url = $yolo_images_url . '/' . $thumb_filename;
                    
                    if (!file_exists($thumb_local_path)) {
                        $thumb_data = @file_get_contents($thumb_remote_url);
                        if ($thumb_data !== false) {
                            file_put_contents($thumb_local_path, $thumb_data);
                        } else {
                            $thumbnail_local_url = $thumb_remote_url;
                        }
                    }
                }
                
                // Store local URL in database
                $wpdb->insert($this->table_images, array(
                    'yacht_id' => $yacht_id,
                    'image_url' => $local_url,
                    'thumbnail_url' => $thumbnail_local_url,
                    'is_primary' => ($index === 0) ? 1 : 0,
                    'sort_order' => $index
                ));
            }
        }
        
        // Store extras - collect from ALL products, not just products[0]
        $extras_to_store = array();
        
        // Collect extras from every product
        if (!empty($yacht_data['products']) && is_array($yacht_data['products'])) {
            foreach ($yacht_data['products'] as $product) {
                if (!empty($product['extras']) && is_array($product['extras'])) {
                    $extras_to_store = array_merge($extras_to_store, $product['extras']);
                }
            }
        }
        
        // Collect top-level extras (if any)
        if (!empty($yacht_data['extras']) && is_array($yacht_data['extras'])) {
            $extras_to_store = array_merge($extras_to_store, $yacht_data['extras']);
        }
        
        // Store all collected extras
        foreach ($extras_to_store as $extra) {
            $wpdb->insert($this->table_extras, array(
                'id' => $extra['id'],
                'yacht_id' => $yacht_id,
                'name' => $extra['name'],
                'price' => isset($extra['price']) ? $extra['price'] : null,
                'currency' => isset($extra['currency']) ? $extra['currency'] : 'EUR',
                'obligatory' => !empty($extra['obligatory']) ? 1 : 0,
                'unit' => isset($extra['unit']) ? $extra['unit'] : null,
                'payableInBase' => !empty($extra['payableInBase']) ? 1 : 0
            ));
        }
        
        // Store equipment (just store IDs, names will be looked up from catalog when displaying)
        if (isset($yacht_data['equipment']) && is_array($yacht_data['equipment'])) {
            foreach ($yacht_data['equipment'] as $equip) {
                $wpdb->insert($this->table_equipment, array(
                    'yacht_id' => $yacht_id,
                    'equipment_id' => $equip['id'],
                    'equipment_name' => null, // Will be populated from catalog on display
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
                "SELECT * FROM {$this->table_images} WHERE yacht_id = %s ORDER BY sort_order ASC",
                $yacht->id
            ));
            
            $yacht->products = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$this->table_products} WHERE yacht_id = %s",
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
    
    /**
     * Store equipment catalog
     */
    public function store_equipment_catalog($equipment_items) {
        global $wpdb;
        
        foreach ($equipment_items as $item) {
            $wpdb->replace($this->table_equipment_catalog, array(
                'id' => $item['id'],
                'name' => $item['name'],
                'last_synced' => current_time('mysql')
            ));
        }
        
        return true;
    }
    
    /**
     * Get equipment name by ID
     */
    /**
     * Load equipment catalog into memory cache
     */
    private function load_equipment_cache() {
        if ($this->equipment_cache === null) {
            global $wpdb;
            $results = $wpdb->get_results(
                "SELECT id, name FROM {$this->table_equipment_catalog}",
                ARRAY_A
            );
            
            $this->equipment_cache = array();
            foreach ($results as $row) {
                $this->equipment_cache[$row['id']] = $row['name'];
            }
        }
    }
    
    /**
     * Get equipment name from cache (much faster than individual queries)
     */
    public function get_equipment_name($equipment_id) {
        $this->load_equipment_cache();
        return isset($this->equipment_cache[$equipment_id]) ? $this->equipment_cache[$equipment_id] : 'Unknown Equipment';
    }
}
