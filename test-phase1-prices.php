<?php
/**
 * Test Phase 1: Price Fetching and Display
 */

// Simulate WordPress environment
define('ABSPATH', '/tmp/');
define('WPINC', 'wp-includes');

// Mock WordPress functions
function get_option($key, $default = '') {
    $options = array(
        'yolo_ys_api_key' => '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe',
        'yolo_ys_my_company_id' => '7850',
        'yolo_ys_friend_companies' => '4366,3604,6711'
    );
    return isset($options[$key]) ? $options[$key] : $default;
}

function wp_remote_get($url, $args) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: ' . $args['headers']['Authorization'],
        'Accept: ' . $args['headers']['Accept']
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return array(
        'body' => $response,
        'response' => array('code' => $http_code)
    );
}

function is_wp_error($thing) {
    return false;
}

function wp_remote_retrieve_body($response) {
    return $response['body'];
}

function wp_remote_retrieve_response_code($response) {
    return $response['response']['code'];
}

// Load API class
require_once '/home/ubuntu/LocalWP/yolo-yacht-search/includes/class-yolo-ys-booking-manager-api.php';

echo "=== Phase 1: Price Fetching Test ===\n\n";

// Test 1: Fetch prices for YOLO (7850)
echo "Test 1: Fetching prices for YOLO (Company 7850)...\n";
$api = new YOLO_YS_Booking_Manager_API();

try {
    $date_from = date('Y-m-d') . 'T00:00:00';
    $date_to = date('Y-m-d', strtotime('+3 months')) . 'T23:59:59';
    
    echo "Date range: $date_from to $date_to\n";
    
    $prices = $api->get_prices(7850, $date_from, $date_to);
    
    if (!empty($prices)) {
        echo "✅ SUCCESS! Found " . count($prices) . " price entries\n\n";
        
        // Show first 3 prices
        echo "Sample prices:\n";
        for ($i = 0; $i < min(3, count($prices)); $i++) {
            $p = $prices[$i];
            echo sprintf(
                "  - Yacht ID: %s | %s to %s | %s %.2f %s\n",
                $p['yachtId'],
                substr($p['dateFrom'], 0, 10),
                substr($p['dateTo'], 0, 10),
                $p['product'],
                $p['price'],
                $p['currency']
            );
        }
        
        echo "\n✅ Phase 1 Test PASSED!\n";
        echo "\nNext steps:\n";
        echo "1. Upload plugin to WordPress\n";
        echo "2. Activate plugin\n";
        echo "3. Click 'Sync All Yachts Now' button\n";
        echo "4. Prices will be stored in database\n";
        echo "5. 'From €XXX per week' will appear on yacht cards\n";
        
    } else {
        echo "❌ No prices returned\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

