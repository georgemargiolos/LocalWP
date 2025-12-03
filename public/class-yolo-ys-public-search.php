<?php
/**
 * Public-facing search functionality
 */

/**
 * AJAX handler for yacht search - Query from DATABASE first
 */
function yolo_ys_ajax_search_yachts() {
    // Note: Search widget doesn't require nonce (public search functionality)
    global $wpdb;
    
    $date_from = sanitize_text_field($_POST['dateFrom']);
    $date_to = sanitize_text_field($_POST['dateTo']);
    $kind = sanitize_text_field($_POST['kind']);
    
    // Get company IDs
    $my_company_id = get_option('yolo_ys_my_company_id', '7850');
    $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
    $friend_ids = array_map('trim', explode(',', $friend_companies));
    
    // Extract dates (format: 2026-05-01T00:00:00)
    $search_date_from = substr($date_from, 0, 10); // Get YYYY-MM-DD
    $search_date_to = substr($date_to, 0, 10);
    
    // Query database for available yachts
    $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    
    // Build SQL query
    $sql = "SELECT DISTINCT 
                y.id as yacht_id,
                y.name as yacht,
                y.model,
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
                'Bareboat' as product
            FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE p.date_from >= %s 
            AND p.date_from <= %s";
    
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
        // Get primary image
        $images_table = $wpdb->prefix . 'yolo_yacht_images';
        $primary_image = $wpdb->get_var($wpdb->prepare(
            "SELECT image_url FROM {$images_table} WHERE yacht_id = %s AND is_primary = 1 LIMIT 1",
            $row->yacht_id
        ));
        
        // Build details URL with search dates
        $search_week_from = date('Y-m-d', strtotime($row->date_from));
        $search_week_to   = date('Y-m-d', strtotime($row->date_to));
        
        $yacht_url = add_query_arg(array(
            'yacht_id' => $row->yacht_id,
            'dateFrom' => $search_week_from,
            'dateTo'   => $search_week_to,
        ), $details_page_url);
        
        $boat = array(
            'yacht_id' => $row->yacht_id,
            'yacht' => $row->yacht . ' ' . $row->model,
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
            $friend_boats[] = $boat;
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
