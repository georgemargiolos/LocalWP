<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public-facing search functionality
 */

/**
 * AJAX handler for yacht search - Query from DATABASE first
 */
function yolo_ys_ajax_search_yachts() {
    // Note: Search widget doesn't require nonce (public search functionality)
    global $wpdb;
    
    // Validate required POST parameters
    $date_from = isset($_POST['dateFrom']) ? sanitize_text_field($_POST['dateFrom']) : '';
    $date_to = isset($_POST['dateTo']) ? sanitize_text_field($_POST['dateTo']) : '';
    $kind = isset($_POST['kind']) ? sanitize_text_field($_POST['kind']) : '';
    
    // Check required fields
    if (empty($date_from) || empty($date_to)) {
        wp_send_json_error(array('message' => 'Missing required date parameters'));
        return;
    }
    
    // Get company IDs - v80.4: Cast to integers for consistent type matching with database
    $my_company_id = (int) get_option('yolo_ys_my_company_id', '7850');
    $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
    $friend_ids = array_map('intval', array_map('trim', explode(',', $friend_companies)));
    
    // Extract dates (format: 2026-05-01T00:00:00)
    $search_date_from = substr($date_from, 0, 10); // Get YYYY-MM-DD
    $search_date_to = substr($date_to, 0, 10);
    
    // Query database for available yachts
    $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    
    // Build SQL query with image subquery to avoid N+1 problem
    $images_table = $wpdb->prefix . 'yolo_yacht_images';
    $sql = "SELECT DISTINCT 
                y.id as yacht_id,
                y.name as yacht,
                y.model,
                y.slug,
                y.company_id,
                y.home_base as startBase,
                y.length,
                y.cabins,
                y.wc,
                y.berths,
                y.year_of_build,
                y.refit_year,
                y.raw_data,
                p.date_from,
                p.date_to,
                p.price,
                p.start_price,
                p.currency,
                p.discount_percentage as discount,
                'Bareboat' as product,
                (SELECT image_url FROM {$images_table} img WHERE img.yacht_id = y.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as image_url
            FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE p.date_from >= %s 
            AND p.date_from <= %s
            AND (y.status = 'active' OR y.status IS NULL)";  // v80.5: Only show active yachts
    
    $params = array($search_date_from, $search_date_to);
    
    // Filter by boat type if specified
    if (!empty($kind)) {
        // Map search values to database values (API uses 'Sail boat' not 'Sailboat')
        $type_map = array(
            'Sailing yacht' => 'Sail boat',
            'Catamaran' => 'Catamaran'
        );
        
        $db_type = isset($type_map[$kind]) ? $type_map[$kind] : $kind;
        $sql .= " AND y.type = %s";
        $params[] = $db_type;
    }
    
    $sql .= " ORDER BY CASE WHEN y.company_id = %d THEN 0 ELSE 1 END, p.price ASC";
    $params[] = $my_company_id;
    
    $results = $wpdb->get_results($wpdb->prepare($sql, $params));
    
    // Separate YOLO boats from partner boats
    $yolo_boats = array();
    $friend_boats = array();
    
    // Get yacht details page URL
    $details_page_id = get_option('yolo_ys_yacht_details_page', 0);
    $details_page_url = $details_page_id ? get_permalink($details_page_id) : home_url('/yacht-details/');
    
    foreach ($results as $row) {
        // Primary image already fetched in main query to avoid N+1 problem
        $primary_image = $row->image_url;
        
        // Fallback: Extract from raw_data if no image in database
        if (empty($primary_image) && !empty($row->raw_data)) {
            $raw_data = json_decode($row->raw_data, true);
            if (!empty($raw_data['images'])) {
                // Find primary image or use first image
                foreach ($raw_data['images'] as $img) {
                    if (!empty($img['primary']) && $img['primary']) {
                        $primary_image = $img['url'];
                        break;
                    }
                }
                if (empty($primary_image) && !empty($raw_data['images'][0]['url'])) {
                    $primary_image = $raw_data['images'][0]['url'];
                }
            }
        }
        
        // Build details URL with search dates
        $search_week_from = date('Y-m-d', strtotime($row->date_from));
        $search_week_to   = date('Y-m-d', strtotime($row->date_to));
        
        // Build URL - use pretty URL if slug exists, otherwise fallback
        if (!empty($row->slug)) {
            $yacht_url = home_url('/yacht/' . $row->slug . '/');
            $yacht_url = add_query_arg(array(
                'dateFrom' => $search_week_from,
                'dateTo'   => $search_week_to,
            ), $yacht_url);
        } else {
            $yacht_url = add_query_arg(array(
                'yacht_id' => $row->yacht_id,
                'dateFrom' => $search_week_from,
                'dateTo'   => $search_week_to,
            ), $details_page_url);
        }
        
        $boat = array(
            'yacht_id' => $row->yacht_id,
            'yacht' => $row->yacht,
            'model' => $row->model,
            'product' => $row->product,
            'startBase' => $row->startBase,
            'price' => (float)$row->price,
            'original_price' => $row->start_price,  // For strikethrough display
            'discount_percentage' => $row->discount, // For discount badge
            'currency' => $row->currency,
            'length' => $row->length,
            'cabins' => $row->cabins,
            'wc' => $row->wc,
            'berths' => $row->berths,
            'year_of_build' => $row->year_of_build,
            'refit_year' => $row->refit_year,
            'date_from' => $row->date_from,
            'date_to' => $row->date_to,
            'image_url' => $primary_image,
            'details_url' => $yacht_url
        );
        
        if ($row->company_id == $my_company_id) {
            $yolo_boats[] = $boat;
        } else {
            // v85.3: Only include boats from friend companies, not all non-YOLO boats
            if (in_array((int)$row->company_id, $friend_ids)) {
                $friend_boats[] = $boat;
            }
        }
    }
    
    // Prepare response
    $response = array(
        'success' => true,
        'yolo_boats' => $yolo_boats,
        'friend_boats' => $friend_boats,
        'total_count' => count($yolo_boats) + count($friend_boats),
    );
    
    wp_send_json($response);
}

// Register AJAX handlers
add_action('wp_ajax_yolo_ys_search_yachts', 'yolo_ys_ajax_search_yachts');
add_action('wp_ajax_nopriv_yolo_ys_search_yachts', 'yolo_ys_ajax_search_yachts');


/**
 * AJAX handler for filtered yacht search with pagination (v81.17)
 * 
 * Features:
 * - Server-side filtering (cabins, length, year, location, equipment)
 * - Server-side sorting (price, year, length)
 * - Pagination with Load More
 * - Featured Yachts (YOLO) shown separately without filters
 */
function yolo_ys_ajax_search_yachts_filtered() {
    global $wpdb;
    
    // Get search parameters
    $date_from = isset($_POST['dateFrom']) ? sanitize_text_field($_POST['dateFrom']) : '';
    $date_to = isset($_POST['dateTo']) ? sanitize_text_field($_POST['dateTo']) : '';
    $kind = isset($_POST['kind']) ? sanitize_text_field($_POST['kind']) : '';
    
    // Get filter parameters
    $cabins = isset($_POST['cabins']) ? intval($_POST['cabins']) : 0;
    
    // v81.20: Range filters
    $length_min = isset($_POST['lengthMin']) ? floatval($_POST['lengthMin']) : 0;
    $length_max = isset($_POST['lengthMax']) ? floatval($_POST['lengthMax']) : 0;
    $year_min = isset($_POST['yearMin']) ? intval($_POST['yearMin']) : 0;
    $year_max = isset($_POST['yearMax']) ? intval($_POST['yearMax']) : 0;
    $price_min = isset($_POST['priceMin']) ? floatval($_POST['priceMin']) : 0;
    $price_max = isset($_POST['priceMax']) ? floatval($_POST['priceMax']) : 0;
    
    $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
    $equipment = isset($_POST['equipment']) ? json_decode(stripslashes($_POST['equipment']), true) : array();
    
    // Get sort parameter
    $sort = isset($_POST['sort']) ? sanitize_text_field($_POST['sort']) : 'price_asc';
    
    // Get pagination parameters
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = 15;
    $offset = ($page - 1) * $per_page;
    
    // Check required fields
    if (empty($date_from) || empty($date_to)) {
        wp_send_json_error(array('message' => 'Missing required date parameters'));
        return;
    }
    
    // Get company IDs - v85.3: Get friend companies for proper filtering
    $my_company_id = (int) get_option('yolo_ys_my_company_id', '7850');
    $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
    $friend_ids = array_filter(array_map('intval', array_map('trim', explode(',', $friend_companies))));
    
    // Extract dates
    $search_date_from = substr($date_from, 0, 10);
    $search_date_to = substr($date_to, 0, 10);
    
    // Table names
    $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    $images_table = $wpdb->prefix . 'yolo_yacht_images';
    $equipment_table = $wpdb->prefix . 'yolo_yacht_equipment';
    
    // Get yacht details page URL
    $details_page_id = get_option('yolo_ys_yacht_details_page', 0);
    $details_page_url = $details_page_id ? get_permalink($details_page_id) : home_url('/yacht-details/');
    
    // ========================================
    // QUERY 1: Featured Yachts (YOLO) - No filters
    // ========================================
    $featured_sql = "SELECT DISTINCT 
                y.id as yacht_id,
                y.name as yacht,
                y.model,
                y.slug,
                y.company_id,
                y.home_base as startBase,
                y.length,
                y.cabins,
                y.wc,
                y.berths,
                y.year_of_build,
                y.refit_year,
                p.date_from,
                p.date_to,
                p.price,
                p.start_price,
                p.currency,
                p.discount_percentage as discount,
                'Bareboat' as product,
                (SELECT image_url FROM {$images_table} img WHERE img.yacht_id = y.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as image_url
            FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE p.date_from >= %s 
            AND p.date_from <= %s
            AND y.company_id = %d
            AND (y.status = 'active' OR y.status IS NULL)";
    
    $featured_params = array($search_date_from, $search_date_to, $my_company_id);
    
    // Filter by boat type if specified
    if (!empty($kind)) {
        $type_map = array(
            'Sailing yacht' => 'Sail boat',
            'Catamaran' => 'Catamaran'
        );
        $db_type = isset($type_map[$kind]) ? $type_map[$kind] : $kind;
        $featured_sql .= " AND y.type = %s";
        $featured_params[] = $db_type;
    }
    
    $featured_sql .= " ORDER BY p.price ASC";
    
    $featured_results = $wpdb->get_results($wpdb->prepare($featured_sql, $featured_params));
    
    // ========================================
    // QUERY 2: Partner Yachts - With filters
    // v85.3: Filter by friend_companies list, not just "not my company"
    // ========================================
    
    // Build friend company placeholders
    $friend_placeholders = !empty($friend_ids) ? implode(',', array_fill(0, count($friend_ids), '%d')) : '0';
    
    $partner_sql = "SELECT DISTINCT 
                y.id as yacht_id,
                y.name as yacht,
                y.model,
                y.slug,
                y.company_id,
                y.home_base as startBase,
                y.length,
                y.cabins,
                y.wc,
                y.berths,
                y.year_of_build,
                y.refit_year,
                p.date_from,
                p.date_to,
                p.price,
                p.start_price,
                p.currency,
                p.discount_percentage as discount,
                'Bareboat' as product,
                (SELECT image_url FROM {$images_table} img WHERE img.yacht_id = y.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as image_url
            FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE p.date_from >= %s 
            AND p.date_from <= %s
            AND y.company_id IN ($friend_placeholders)
            AND (y.status = 'active' OR y.status IS NULL)";
    
    $partner_params = array($search_date_from, $search_date_to);
    foreach ($friend_ids as $fid) {
        $partner_params[] = $fid;
    }
    
    // Filter by boat type if specified
    if (!empty($kind)) {
        $partner_sql .= " AND y.type = %s";
        $partner_params[] = $db_type;
    }
    
    // Filter by cabins
    if ($cabins > 0) {
        $partner_sql .= " AND y.cabins >= %d";
        $partner_params[] = $cabins;
    }
    
    // Filter by length range (v81.20)
    if ($length_min > 0) {
        $partner_sql .= " AND y.length >= %f";
        $partner_params[] = $length_min;
    }
    if ($length_max > 0) {
        $partner_sql .= " AND y.length <= %f";
        $partner_params[] = $length_max;
    }
    
    // Filter by year range (v81.20)
    if ($year_min > 0) {
        $partner_sql .= " AND y.year_of_build >= %d";
        $partner_params[] = $year_min;
    }
    if ($year_max > 0) {
        $partner_sql .= " AND y.year_of_build <= %d";
        $partner_params[] = $year_max;
    }
    
    // Filter by price range (v81.20)
    if ($price_min > 0) {
        $partner_sql .= " AND p.price >= %f";
        $partner_params[] = $price_min;
    }
    if ($price_max > 0) {
        $partner_sql .= " AND p.price <= %f";
        $partner_params[] = $price_max;
    }
    
    // Filter by location (v81.25: Map location names to marina search patterns)
    if (!empty($location)) {
        // Map location filter values to marina name patterns
        $location_patterns = array(
            'Lefkada' => array('Lefkas', 'Lefkada', 'Nydri', 'Vliho', 'Vasiliki', 'Nikiana', 'Lygia', 'Ligia'),
            'Corfu' => array('Corfu', 'Gouvia', 'Kerkyra'),
            'Kefalonia' => array('Kefalonia', 'Argostoli', 'Fiskardo', 'Sami', 'Agia Effimia'),
            'Zakynthos' => array('Zakynthos', 'Zante'),
            'Ithaca' => array('Ithaca', 'Ithaki', 'Vathy'),
            'Preveza' => array('Preveza', 'Cleopatra'),
            'Syvota' => array('Syvota', 'Sivota'),
            'Vonitsa' => array('Vonitsa'),
            'Palairos' => array('Palairos', 'Paleros', 'Vounaki'),
            'Plataria' => array('Plataria'),
            'Astakos' => array('Astakos'),
            'Paxos' => array('Paxos', 'Gaios'),
        );
        
        if (isset($location_patterns[$location])) {
            $patterns = $location_patterns[$location];
            $like_conditions = array();
            foreach ($patterns as $pattern) {
                $like_conditions[] = "y.home_base LIKE %s";
                $partner_params[] = '%' . $wpdb->esc_like($pattern) . '%';
            }
            $partner_sql .= " AND (" . implode(' OR ', $like_conditions) . ")";
        } else {
            // Fallback: direct match
            $partner_sql .= " AND y.home_base LIKE %s";
            $partner_params[] = '%' . $wpdb->esc_like($location) . '%';
        }
    }
    
    // Filter by equipment (yacht must have ALL selected equipment)
    if (!empty($equipment) && is_array($equipment)) {
        foreach ($equipment as $equip_id) {
            $equip_id = intval($equip_id);
            if ($equip_id > 0) {
                $partner_sql .= " AND EXISTS (SELECT 1 FROM {$equipment_table} e WHERE e.yacht_id = y.id AND e.equipment_id = %d)";
                $partner_params[] = $equip_id;
            }
        }
    }
    
    // v85.3: Filter partner boats to Greek Ionian bases ONLY
    // REMOVED NULL fallback - was allowing Nikiti boats through!
    if (defined('YOLO_YS_GREEK_IONIAN_BASE_IDS') && !empty(YOLO_YS_GREEK_IONIAN_BASE_IDS)) {
        $ionian_base_ids = YOLO_YS_GREEK_IONIAN_BASE_IDS;
        $ionian_placeholders_base = implode(',', array_fill(0, count($ionian_base_ids), '%d'));
        $partner_sql .= " AND y.home_base_id IN ($ionian_placeholders_base)";
        foreach ($ionian_base_ids as $base_id) {
            $partner_params[] = (int)$base_id;
        }
    }
    
    // Get total count before pagination
    $count_sql = str_replace("SELECT DISTINCT", "SELECT COUNT(DISTINCT y.id) as total FROM (SELECT DISTINCT", $partner_sql);
    $count_sql = preg_replace('/SELECT DISTINCT.*?FROM/s', 'SELECT COUNT(DISTINCT y.id) as total FROM', $partner_sql);
    
    // Simpler approach: wrap the query
    // v85.3: Use friend_companies list for count query too
    $count_sql = "SELECT COUNT(*) FROM (SELECT DISTINCT y.id FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE p.date_from >= %s 
            AND p.date_from <= %s
            AND y.company_id IN ($friend_placeholders)
            AND (y.status = 'active' OR y.status IS NULL)";
    
    $count_params = array($search_date_from, $search_date_to);
    foreach ($friend_ids as $fid) {
        $count_params[] = $fid;
    }
    
    if (!empty($kind)) {
        $count_sql .= " AND y.type = %s";
        $count_params[] = $db_type;
    }
    if ($cabins > 0) {
        $count_sql .= " AND y.cabins >= %d";
        $count_params[] = $cabins;
    }
    // v81.20: Range filters for count query
    if ($length_min > 0) {
        $count_sql .= " AND y.length >= %f";
        $count_params[] = $length_min;
    }
    if ($length_max > 0) {
        $count_sql .= " AND y.length <= %f";
        $count_params[] = $length_max;
    }
    if ($year_min > 0) {
        $count_sql .= " AND y.year_of_build >= %d";
        $count_params[] = $year_min;
    }
    if ($year_max > 0) {
        $count_sql .= " AND y.year_of_build <= %d";
        $count_params[] = $year_max;
    }
    if ($price_min > 0) {
        $count_sql .= " AND p.price >= %f";
        $count_params[] = $price_min;
    }
    if ($price_max > 0) {
        $count_sql .= " AND p.price <= %f";
        $count_params[] = $price_max;
    }
    // Location filter for count (v81.25: Same mapping as main query)
    if (!empty($location)) {
        $location_patterns = array(
            'Lefkada' => array('Lefkas', 'Lefkada', 'Nydri', 'Vliho', 'Vasiliki', 'Nikiana', 'Lygia', 'Ligia'),
            'Corfu' => array('Corfu', 'Gouvia', 'Kerkyra'),
            'Kefalonia' => array('Kefalonia', 'Argostoli', 'Fiskardo', 'Sami', 'Agia Effimia'),
            'Zakynthos' => array('Zakynthos', 'Zante'),
            'Ithaca' => array('Ithaca', 'Ithaki', 'Vathy'),
            'Preveza' => array('Preveza', 'Cleopatra'),
            'Syvota' => array('Syvota', 'Sivota'),
            'Vonitsa' => array('Vonitsa'),
            'Palairos' => array('Palairos', 'Paleros', 'Vounaki'),
            'Plataria' => array('Plataria'),
            'Astakos' => array('Astakos'),
            'Paxos' => array('Paxos', 'Gaios'),
        );
        
        if (isset($location_patterns[$location])) {
            $patterns = $location_patterns[$location];
            $like_conditions = array();
            foreach ($patterns as $pattern) {
                $like_conditions[] = "y.home_base LIKE %s";
                $count_params[] = '%' . $wpdb->esc_like($pattern) . '%';
            }
            $count_sql .= " AND (" . implode(' OR ', $like_conditions) . ")";
        } else {
            $count_sql .= " AND y.home_base LIKE %s";
            $count_params[] = '%' . $wpdb->esc_like($location) . '%';
        }
    }
    if (!empty($equipment) && is_array($equipment)) {
        foreach ($equipment as $equip_id) {
            $equip_id = intval($equip_id);
            if ($equip_id > 0) {
                $count_sql .= " AND EXISTS (SELECT 1 FROM {$equipment_table} e WHERE e.yacht_id = y.id AND e.equipment_id = %d)";
                $count_params[] = $equip_id;
            }
        }
    }
    // v85.3: Greek Ionian filter for count query - REMOVED NULL fallback
    if (defined('YOLO_YS_GREEK_IONIAN_BASE_IDS') && !empty(YOLO_YS_GREEK_IONIAN_BASE_IDS)) {
        $ionian_base_ids = YOLO_YS_GREEK_IONIAN_BASE_IDS;
        $ionian_placeholders_count = implode(',', array_fill(0, count($ionian_base_ids), '%d'));
        $count_sql .= " AND y.home_base_id IN ($ionian_placeholders_count)";
        foreach ($ionian_base_ids as $base_id) {
            $count_params[] = (int)$base_id;
        }
    }
    $count_sql .= ") as counted";
    
    $total_count = $wpdb->get_var($wpdb->prepare($count_sql, $count_params));
    
    // Add sorting
    switch ($sort) {
        case 'price_desc':
            $partner_sql .= " ORDER BY p.price DESC";
            break;
        case 'year_desc':
            $partner_sql .= " ORDER BY y.year_of_build DESC, p.price ASC";
            break;
        case 'length_desc':
            $partner_sql .= " ORDER BY y.length DESC, p.price ASC";
            break;
        case 'cabins_desc':
            $partner_sql .= " ORDER BY y.cabins DESC, p.price ASC";
            break;
        case 'price_asc':
        default:
            $partner_sql .= " ORDER BY p.price ASC";
            break;
    }
    
    // Add pagination
    $partner_sql .= " LIMIT %d OFFSET %d";
    $partner_params[] = $per_page;
    $partner_params[] = $offset;
    
    $partner_results = $wpdb->get_results($wpdb->prepare($partner_sql, $partner_params));
    
    // ========================================
    // Format results
    // ========================================
    $featured_boats = array();
    $partner_boats = array();
    
    // Format featured boats
    foreach ($featured_results as $row) {
        $featured_boats[] = yolo_ys_format_boat_result($row, $details_page_url);
    }
    
    // Format partner boats
    foreach ($partner_results as $row) {
        $partner_boats[] = yolo_ys_format_boat_result($row, $details_page_url);
    }
    
    // Prepare response
    $response = array(
        'success' => true,
        'featured_boats' => $featured_boats,
        'partner_boats' => $partner_boats,
        'total_count' => intval($total_count),
        'page' => $page,
        'per_page' => $per_page,
        'total_pages' => ceil($total_count / $per_page),
        'has_more' => ($page * $per_page) < $total_count
    );
    
    wp_send_json($response);
}

/**
 * Helper function to format boat result
 */
function yolo_ys_format_boat_result($row, $details_page_url) {
    $primary_image = $row->image_url;
    
    // Fallback: Extract from raw_data if no image in database
    if (empty($primary_image) && !empty($row->raw_data)) {
        $raw_data = json_decode($row->raw_data, true);
        if (!empty($raw_data['images'])) {
            foreach ($raw_data['images'] as $img) {
                if (!empty($img['primary']) && $img['primary']) {
                    $primary_image = $img['url'];
                    break;
                }
            }
            if (empty($primary_image) && !empty($raw_data['images'][0]['url'])) {
                $primary_image = $raw_data['images'][0]['url'];
            }
        }
    }
    
    // Build details URL with search dates
    $search_week_from = date('Y-m-d', strtotime($row->date_from));
    $search_week_to   = date('Y-m-d', strtotime($row->date_to));
    
    if (!empty($row->slug)) {
        $yacht_url = home_url('/yacht/' . $row->slug . '/');
        $yacht_url = add_query_arg(array(
            'dateFrom' => $search_week_from,
            'dateTo'   => $search_week_to,
        ), $yacht_url);
    } else {
        $yacht_url = add_query_arg(array(
            'yacht_id' => $row->yacht_id,
            'dateFrom' => $search_week_from,
            'dateTo'   => $search_week_to,
        ), $details_page_url);
    }
    
    return array(
        'yacht_id' => $row->yacht_id,
        'yacht' => $row->yacht,
        'model' => $row->model,
        'product' => $row->product,
        'startBase' => $row->startBase,
        'price' => (float)$row->price,
        'original_price' => $row->start_price,
        'discount_percentage' => $row->discount,
        'currency' => $row->currency,
        'length' => $row->length,
        'cabins' => $row->cabins,
        'wc' => $row->wc,
        'berths' => $row->berths,
        'year_of_build' => $row->year_of_build,
        'refit_year' => $row->refit_year,
        'date_from' => $row->date_from,
        'date_to' => $row->date_to,
        'image_url' => $primary_image,
        'details_url' => $yacht_url
    );
}

// Register new AJAX handlers for filtered search
add_action('wp_ajax_yolo_ys_search_yachts_filtered', 'yolo_ys_ajax_search_yachts_filtered');
add_action('wp_ajax_nopriv_yolo_ys_search_yachts_filtered', 'yolo_ys_ajax_search_yachts_filtered');
