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
            slug varchar(255) DEFAULT NULL,
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
            status varchar(20) DEFAULT 'active',
            deactivated_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            KEY status (status),
            KEY company_id (company_id),
            KEY name (name),
            UNIQUE KEY slug (slug)
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
        
        // Yacht Custom Media table (v65.14) - for custom images and videos
        $table_custom_media = $wpdb->prefix . 'yolo_yacht_custom_media';
        $sql_custom_media = "CREATE TABLE {$table_custom_media} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            yacht_id varchar(50) NOT NULL,
            media_type varchar(20) NOT NULL COMMENT 'image or video',
            media_url varchar(500) NOT NULL COMMENT 'URL or file path',
            thumbnail_url varchar(500) DEFAULT NULL COMMENT 'For videos: YouTube thumbnail or custom',
            title varchar(255) DEFAULT NULL,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY yacht_id (yacht_id),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        dbDelta($sql_custom_media);
        
        // Yacht Custom Settings table (v65.14, updated v75.11) - for custom description, flags, and pricing
        $table_custom_settings = $wpdb->prefix . 'yolo_yacht_custom_settings';
        $sql_custom_settings = "CREATE TABLE {$table_custom_settings} (
            yacht_id varchar(50) NOT NULL,
            use_custom_media tinyint(1) DEFAULT 0 COMMENT 'Use local images/videos instead of synced',
            use_custom_description tinyint(1) DEFAULT 0 COMMENT 'Use custom description instead of synced',
            custom_description longtext DEFAULT NULL,
            starting_from_price decimal(10,2) DEFAULT 0 COMMENT 'Starting from price for Facebook/Google Ads tracking (v75.11)',
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (yacht_id)
        ) $charset_collate;";
        dbDelta($sql_custom_settings);
        
        update_option('yolo_ys_db_version', '1.9');
    }
    
    /**
     * Store yacht data
     */
    public function store_yacht($yacht_data, $company_id) {
        global $wpdb;
        
        // Generate slug from yacht name and model
        $yacht_name = $yacht_data['name'];
        $yacht_model = isset($yacht_data['model']) ? $yacht_data['model'] : '';
        $base_slug = sanitize_title($yacht_name . '-' . $yacht_model);
        
        // Check if slug already exists for a different yacht
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_yachts} WHERE slug = %s AND id != %s",
            $base_slug,
            $yacht_data['id']
        ));
        
        // If slug exists for different yacht, append company ID to make unique
        $slug = $existing ? $base_slug . '-' . $company_id : $base_slug;
        
        // Prepare yacht data
        $yacht_insert = array(
            'id' => $yacht_data['id'],
            'company_id' => $company_id,
            'name' => $yacht_data['name'],
            'model' => isset($yacht_data['model']) ? $yacht_data['model'] : null,
            'slug' => $slug,
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
        // v81.1: Added try-catch and memory management to prevent crashes
        if (isset($yacht_data['images']) && is_array($yacht_data['images'])) {
            // Create upload directory if it doesn't exist
            $upload_dir = wp_upload_dir();
            $yolo_images_dir = $upload_dir['basedir'] . '/yolo-yacht-images';
            $yolo_images_url = $upload_dir['baseurl'] . '/yolo-yacht-images';
            
            if (!file_exists($yolo_images_dir)) {
                wp_mkdir_p($yolo_images_dir);
            }
            
            foreach ($yacht_data['images'] as $index => $image) {
                try {
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
                        // v81.1: Use wp_remote_get with timeout instead of file_get_contents
                        $response = wp_remote_get($remote_url, array(
                            'timeout' => 15,
                            'sslverify' => false
                        ));
                        
                        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                            $image_data = wp_remote_retrieve_body($response);
                            if (!empty($image_data)) {
                                file_put_contents($local_path, $image_data);
                                
                                // Optimize the downloaded image to reduce file size
                                $this->optimize_yacht_image($local_path);
                            }
                            // Free memory immediately
                            unset($image_data, $response);
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
                            $thumb_response = wp_remote_get($thumb_remote_url, array(
                                'timeout' => 10,
                                'sslverify' => false
                            ));
                            
                            if (!is_wp_error($thumb_response) && wp_remote_retrieve_response_code($thumb_response) === 200) {
                                $thumb_data = wp_remote_retrieve_body($thumb_response);
                                if (!empty($thumb_data)) {
                                    file_put_contents($thumb_local_path, $thumb_data);
                                }
                                unset($thumb_data, $thumb_response);
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
                    
                } catch (Exception $e) {
                    // v81.1: Log error but continue with other images
                    error_log('YOLO YS: Image download failed for yacht ' . $yacht_id . ': ' . $e->getMessage());
                    continue;
                }
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
    public function get_all_yachts($company_id = null, $include_inactive = false) {
        global $wpdb;
        
        $conditions = array();
        
        if ($company_id) {
            $conditions[] = $wpdb->prepare("company_id = %d", $company_id);
        }
        
        // v80.5: Filter by status - only show active yachts by default
        if (!$include_inactive) {
            $conditions[] = "(status = 'active' OR status IS NULL)";
        }
        
        $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
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
    
    /**
     * Optimize yacht image after download
     * Reduces file size while maintaining quality using WordPress image editor
     * 
     * Process:
     * 1. Load image using wp_get_image_editor()
     * 2. Resize if larger than 1920px (retina-ready for desktop)
     * 3. Set quality to 85% (sweet spot for size vs quality)
     * 4. Save optimized version over original
     * 
     * Expected results:
     * - Original: 2-5 MB → Optimized: 300-500 KB (85-90% reduction)
     * - Maintains visual quality for web display
     * - Faster page loads, reduced storage, lower bandwidth
     * 
     * @param string $image_path Full path to image file
     * @return bool Success status
     */
    private function optimize_yacht_image($image_path) {
        if (!file_exists($image_path)) {
            error_log('YOLO YS: Image optimization failed - file not found: ' . basename($image_path));
            return false;
        }
        
        // Use WordPress image editor (supports GD and ImageMagick)
        $editor = wp_get_image_editor($image_path);
        
        if (is_wp_error($editor)) {
            error_log('YOLO YS: Image editor failed for ' . basename($image_path) . ': ' . $editor->get_error_message());
            return false;
        }
        
        // Get current dimensions
        $size = $editor->get_size();
        $width = $size['width'];
        $height = $size['height'];
        
        // Only resize if larger than 1920px width (retina-ready for desktop)
        // This preserves quality while reducing file size significantly
        if ($width > 1920 || $height > 1080) {
            $editor->resize(1920, 1080, false); // false = maintain aspect ratio
            error_log(sprintf(
                'YOLO YS: Resized %s from %dx%d to max 1920x1080',
                basename($image_path),
                $width,
                $height
            ));
        }
        
        // Set quality to 85% (WordPress default is 82%, we use 85% for slightly better quality)
        // This is the sweet spot: excellent visual quality with 85-90% file size reduction
        $editor->set_quality(85);
        
        // Save optimized version (overwrites original)
        $result = $editor->save($image_path);
        
        if (is_wp_error($result)) {
            error_log('YOLO YS: Failed to save optimized image: ' . $result->get_error_message());
            return false;
        }
        
        // Log success with file size info
        $file_size = file_exists($image_path) ? filesize($image_path) : 0;
        $file_size_mb = round($file_size / 1024 / 1024, 2);
        error_log(sprintf(
            'YOLO YS: Optimized %s (final size: %s MB)',
            basename($image_path),
            $file_size_mb
        ));
        
        return true;
    }
    
    /**
     * Get yacht by slug
     * 
     * @param string $slug The yacht slug
     * @return object|null Yacht data or null if not found
     */
    public function get_yacht_by_slug($slug) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_yachts} WHERE slug = %s",
            $slug
        ));
    }
    
    /**
     * Get yacht URL by ID (returns pretty URL if slug exists)
     * 
     * @param string|int $yacht_id The yacht ID
     * @return string The yacht URL
     */
    public function get_yacht_url($yacht_id) {
        global $wpdb;
        
        $yacht = $wpdb->get_row($wpdb->prepare(
            "SELECT slug FROM {$this->table_yachts} WHERE id = %s",
            $yacht_id
        ));
        
        if ($yacht && !empty($yacht->slug)) {
            return home_url('/yacht/' . $yacht->slug . '/');
        }
        
        // Fallback to old URL format
        $details_page_id = get_option('yolo_ys_yacht_details_page', 0);
        if ($details_page_id) {
            return add_query_arg('yacht_id', $yacht_id, get_permalink($details_page_id));
        }
        
        return home_url('/yacht-details-page/?yacht_id=' . $yacht_id);
    }
    
    /**
     * Generate slugs for all existing yachts (one-time migration)
     * 
     * @return int Number of yachts updated
     */
    public static function generate_all_slugs() {
        global $wpdb;
        $table = $wpdb->prefix . 'yolo_yachts';
        
        // Get all yachts without slugs
        $yachts = $wpdb->get_results("SELECT id, name, model, company_id FROM {$table} WHERE slug IS NULL OR slug = ''");
        
        $updated = 0;
        foreach ($yachts as $yacht) {
            $base_slug = sanitize_title($yacht->name . '-' . $yacht->model);
            
            // Check for duplicates
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE slug = %s AND id != %s",
                $base_slug,
                $yacht->id
            ));
            
            $slug = $existing ? $base_slug . '-' . $yacht->company_id : $base_slug;
            
            $wpdb->update(
                $table,
                array('slug' => $slug),
                array('id' => $yacht->id)
            );
            $updated++;
        }
        
        return $updated;
    }
    
    /**
     * Activate yachts that are present in API response (v80.5)
     * Sets status to 'active' and clears deactivated_at timestamp
     * 
     * @param array $yacht_ids Array of yacht IDs from API
     * @return int Number of yachts activated
     */
    public function activate_yachts($yacht_ids) {
        if (empty($yacht_ids)) {
            return 0;
        }
        
        global $wpdb;
        
        $placeholders = implode(',', array_fill(0, count($yacht_ids), '%d'));
        $query = $wpdb->prepare(
            "UPDATE {$this->table_yachts} 
             SET status = 'active', deactivated_at = NULL 
             WHERE id IN ($placeholders) AND (status != 'active' OR status IS NULL)",
            $yacht_ids
        );
        
        return $wpdb->query($query);
    }
    
    /**
     * Deactivate yachts from a company that are NOT in API response (v80.5)
     * Sets status to 'inactive' and records deactivated_at timestamp
     * These yachts are no longer in the company's fleet but data is preserved
     * 
     * @param int $company_id Company ID
     * @param array $api_yacht_ids Yacht IDs that ARE in the API response
     * @return int Number of yachts deactivated
     */
    public function deactivate_missing_yachts($company_id, $api_yacht_ids) {
        global $wpdb;
        
        if (empty($api_yacht_ids)) {
            // If no yachts in API response, don't deactivate anything
            // This prevents mass deactivation on API failure
            return 0;
        }
        
        $placeholders = implode(',', array_fill(0, count($api_yacht_ids), '%d'));
        $params = array_merge($api_yacht_ids, array($company_id));
        
        $query = $wpdb->prepare(
            "UPDATE {$this->table_yachts} 
             SET status = 'inactive', deactivated_at = NOW() 
             WHERE id NOT IN ($placeholders) 
             AND company_id = %d 
             AND (status = 'active' OR status IS NULL)",
            $params
        );
        
        return $wpdb->query($query);
    }
    
    /**
     * Store yacht data WITHOUT images (Phase 1 of two-phase sync)
     * v81.1: Separate data sync from image sync to prevent timeouts
     * 
     * @param array $yacht_data Yacht data from API
     * @param int $company_id Company ID
     * @return bool Success
     */
    public function store_yacht_data_only($yacht_data, $company_id) {
        global $wpdb;
        
        // Generate slug from yacht name and model
        $yacht_name = $yacht_data['name'];
        $yacht_model = isset($yacht_data['model']) ? $yacht_data['model'] : '';
        $base_slug = sanitize_title($yacht_name . '-' . $yacht_model);
        
        // Check if slug already exists for a different yacht
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_yachts} WHERE slug = %s AND id != %s",
            $base_slug,
            $yacht_data['id']
        ));
        
        // If slug exists for different yacht, append company ID to make unique
        $slug = $existing ? $base_slug . '-' . $company_id : $base_slug;
        
        // Prepare yacht data
        $yacht_insert = array(
            'id' => $yacht_data['id'],
            'company_id' => $company_id,
            'name' => $yacht_data['name'],
            'model' => isset($yacht_data['model']) ? $yacht_data['model'] : null,
            'slug' => $slug,
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
        
        // Delete old products, extras, equipment (NOT images - those are handled in Phase 2)
        $wpdb->delete($this->table_products, array('yacht_id' => $yacht_id));
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
        
        // Store extras - collect from ALL products
        $extras_to_store = array();
        
        if (!empty($yacht_data['products']) && is_array($yacht_data['products'])) {
            foreach ($yacht_data['products'] as $product) {
                if (!empty($product['extras']) && is_array($product['extras'])) {
                    $extras_to_store = array_merge($extras_to_store, $product['extras']);
                }
            }
        }
        
        if (!empty($yacht_data['extras']) && is_array($yacht_data['extras'])) {
            $extras_to_store = array_merge($extras_to_store, $yacht_data['extras']);
        }
        
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
        
        // Store equipment
        if (isset($yacht_data['equipment']) && is_array($yacht_data['equipment'])) {
            foreach ($yacht_data['equipment'] as $equip) {
                $wpdb->insert($this->table_equipment, array(
                    'yacht_id' => $yacht_id,
                    'equipment_id' => $equip['id'],
                    'equipment_name' => null,
                    'category' => isset($equip['category']) ? $equip['category'] : null
                ));
            }
        }
        
        return true;
    }
    
    /**
     * Clear all images for a yacht (before re-syncing)
     * v81.1: Part of two-phase sync
     * 
     * @param string $yacht_id Yacht ID
     * @return bool Success
     */
    public function clear_yacht_images($yacht_id) {
        global $wpdb;
        
        // Get old images to delete files
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
        
        // Delete database records
        $wpdb->delete($this->table_images, array('yacht_id' => $yacht_id));
        
        return true;
    }
    
    /**
     * Download and store a single image (Phase 2 of two-phase sync)
     * v81.1: Downloads one image at a time to prevent memory/timeout issues
     * 
     * @param string $yacht_id Yacht ID
     * @param array $image Image data from API
     * @param int $index Sort order index
     * @return bool Success
     */
    public function download_and_store_single_image($yacht_id, $image, $index) {
        global $wpdb;
        
        if (empty($image['url'])) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $yolo_images_dir = $upload_dir['basedir'] . '/yolo-yacht-images';
        $yolo_images_url = $upload_dir['baseurl'] . '/yolo-yacht-images';
        
        // Create directory if needed
        if (!file_exists($yolo_images_dir)) {
            wp_mkdir_p($yolo_images_dir);
        }
        
        $remote_url = $image['url'];
        $filename = basename(parse_url($remote_url, PHP_URL_PATH));
        $local_path = $yolo_images_dir . '/' . $filename;
        $local_url = $yolo_images_url . '/' . $filename;
        
        // Download if not already exists
        if (!file_exists($local_path)) {
            $response = wp_remote_get($remote_url, array(
                'timeout' => 30,
                'sslverify' => false
            ));
            
            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $image_data = wp_remote_retrieve_body($response);
                if (!empty($image_data)) {
                    file_put_contents($local_path, $image_data);
                    
                    // Optimize the downloaded image
                    $this->optimize_yacht_image($local_path);
                }
                unset($image_data, $response);
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
                $thumb_response = wp_remote_get($thumb_remote_url, array(
                    'timeout' => 15,
                    'sslverify' => false
                ));
                
                if (!is_wp_error($thumb_response) && wp_remote_retrieve_response_code($thumb_response) === 200) {
                    $thumb_data = wp_remote_retrieve_body($thumb_response);
                    if (!empty($thumb_data)) {
                        file_put_contents($thumb_local_path, $thumb_data);
                    }
                    unset($thumb_data, $thumb_response);
                } else {
                    $thumbnail_local_url = $thumb_remote_url;
                }
            }
        }
        
        // Store in database
        $wpdb->insert($this->table_images, array(
            'yacht_id' => $yacht_id,
            'image_url' => $local_url,
            'thumbnail_url' => $thumbnail_local_url,
            'is_primary' => ($index === 0) ? 1 : 0,
            'sort_order' => $index
        ));
        
        return true;
    }
}
