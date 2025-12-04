<?php
// This is the new get_live_price method from Cursor AI
// Will be inserted into the main file

    /**
     * Get live price and availability for specific yacht and dates
     * Used for real-time price checking before booking
     * 
     * FIXED (v2.3.10): Handle multiple API response formats:
     * - { "offers": [...] } - wrapped format
     * - [...] - direct array format
     * - { "yachtId": ..., "price": ... } - single offer format
     */
    public function get_live_price($yacht_id, $date_from, $date_to) {
        // CRITICAL: Convert dates to required format: yyyy-MM-ddTHH:mm:ss
        $date_from_formatted = date('Y-m-d', strtotime($date_from)) . 'T17:00:00';
        $date_to_formatted = date('Y-m-d', strtotime($date_to)) . 'T17:00:00';
        
        $params = array(
            'yachtId' => $yacht_id,
            'dateFrom' => $date_from_formatted,
            'dateTo' => $date_to_formatted,
            // REMOVED: tripDuration parameter causes HTTP 404 from API
        );
        
        $endpoint = '/offers';
        $result = $this->make_request($endpoint, $params);
        
        if (!$result['success']) {
            return array(
                'success' => false,
                'available' => false,
                'error' => isset($result['error']) ? $result['error'] : 'Failed to fetch live price',
            );
        }
        
        $offers = array();
        
        // Handle different response formats
        if (isset($result['data']['offers']) && is_array($result['data']['offers'])) {
            // Format: { "offers": [...] }
            $offers = $result['data']['offers'];
        } elseif (is_array($result['data']) && isset($result['data'][0])) {
            // Format: [...] - direct array of offers
            $offers = $result['data'];
        } elseif (is_array($result['data']) && isset($result['data']['yachtId'])) {
            // Format: single offer object { "yachtId": ..., "price": ... }
            $offers = array($result['data']);
        } elseif (is_array($result['data']) && isset($result['data']['price'])) {
            // Format: single offer with price
            $offers = array($result['data']);
        }
        
        if (count($offers) > 0) {
            $offer = $offers[0];
            
            $base_price = isset($offer['price']) ? $offer['price'] : 0;
            $start_price = isset($offer['startPrice']) ? $offer['startPrice'] : $base_price;
            $discount = isset($offer['discountPercentage']) ? $offer['discountPercentage'] : 0;
            
            // Calculate obligatory extras
            $included_extras = 0;
            $extras_at_base = 0;
            $extras_details = array();
            
            if (isset($offer['obligatoryExtras']) && is_array($offer['obligatoryExtras'])) {
                foreach ($offer['obligatoryExtras'] as $extra) {
                    $extra_price = isset($extra['price']) ? floatval($extra['price']) : 0;
                    $payable_in_base = isset($extra['payableInBase']) ? $extra['payableInBase'] : true;
                    
                    $extras_details[] = array(
                        'name' => isset($extra['name']) ? $extra['name'] : '',
                        'price' => $extra_price,
                        'currency' => isset($extra['currency']) ? $extra['currency'] : 'EUR',
                        'payableInBase' => $payable_in_base,
                    );
                    
                    if (!$payable_in_base) {
                        $included_extras += $extra_price;
                    } else {
                        $extras_at_base += $extra_price;
                    }
                }
            }
            
            $total_price = $base_price + $included_extras;
            
            return array(
                'success' => true,
                'available' => true,
                'price' => $start_price,
                'discount' => $discount,
                'final_price' => $total_price,
                'base_price' => $base_price,
                'included_extras' => $included_extras,
                'extras_at_base' => $extras_at_base,
                'extras_details' => $extras_details,
                'currency' => isset($offer['currency']) ? $offer['currency'] : 'EUR',
            );
        }
        
        // No offers found from API - check local database as fallback
        global $wpdb;
        $table_name = $wpdb->prefix . 'yolo_yacht_prices';
        
        // Check if table exists
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
        
        if ($table_exists) {
            // Look for price in local database
            $local_price = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} 
                 WHERE yacht_id = %s 
                 AND date_from = %s 
                 AND date_to = %s
                 LIMIT 1",
                $yacht_id,
                $date_from,
                $date_to
            ));
            
            if ($local_price) {
                $price = floatval($local_price->start_price ?? $local_price->price);
                $final_price = floatval($local_price->price);
                $discount = floatval($local_price->discount_percentage ?? 0);
                $currency = $local_price->currency ?? 'EUR';
                
                return array(
                    'success' => true,
                    'available' => true,
                    'price' => $price,
                    'discount' => $discount,
                    'final_price' => $final_price,
                    'currency' => $currency,
                    'source' => 'database', // Indicate this came from cache
                );
            }
        }
        
        return array(
            'success' => true,
            'available' => false,
            'error' => 'Yacht not available for selected dates',
        );
    }
