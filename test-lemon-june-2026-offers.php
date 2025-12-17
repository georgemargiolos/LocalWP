<?php
/**
 * Test Booking Manager /offers API for Lemon yacht
 * First week of June 2026: June 1-7, 2026
 * (Charter weeks are typically Saturday to Saturday, so June 1-8, 2026)
 * 
 * INSTRUCTIONS:
 * 1. Copy this file to your WordPress root directory
 * 2. Visit: http://your-site.local/test-lemon-june-2026-offers.php in browser
 * OR
 * 1. Open Local WP app → Open Site Shell
 * 2. Run: php test-lemon-june-2026-offers.php
 */

// Try to load WordPress
$wp_config_paths = [
    __DIR__ . '/wp-config.php',
    __DIR__ . '/../wp-config.php',
    __DIR__ . '/../../wp-config.php',
];

$wp_loaded = false;
foreach ($wp_config_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        if (function_exists('get_option')) {
            $wp_loaded = true;
            break;
        }
    }
}

// API Configuration
$base_url = 'https://www.booking-manager.com/api/v2';

// Try to get API key from WordPress options, otherwise use hardcoded
if ($wp_loaded) {
    $api_key = get_option('yolo_ys_api_key', '');
} else {
    // Fallback API key (replace if needed)
    $api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55SWQiOjc4NTAsInVzZXJJZCI6MTU5NjEsImV4cCI6MTc2NDU1NjgwMH0.xhIiTx5oON8cWFbXMjMvBBGAZ8UUPLBFYTVJZaHWGQs';
}

if (empty($api_key)) {
    die("❌ ERROR: API key not found. Please set 'yolo_ys_api_key' in WordPress options or update this script.\n");
}

// Lemon yacht ID
$yacht_id = '6362109340000107850';

// First week of June 2026
// Charter weeks typically start Saturday, so June 1, 2026 is a Monday
// For a full week charter, we'll check June 1-8, 2026 (Saturday to Saturday)
$dateFrom = '2026-06-01T17:00:00';  // June 1, 2026 at 5 PM (typical check-in time)
$dateTo = '2026-06-08T17:00:00';    // June 8, 2026 at 5 PM (typical check-out time)

echo "=== Lemon Yacht Availability Check ===\n\n";
echo "Yacht: Lemon (ID: $yacht_id)\n";
echo "Date Range: June 1-8, 2026 (First week of June)\n";
echo str_repeat("=", 60) . "\n\n";

// Build query parameters
$query_params = array(
    'yachtId' => $yacht_id,
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
);

// Build URL with query string
$query_string = http_build_query($query_params);
$url = $base_url . '/offers?' . $query_string;

echo "API Request:\n";
echo "URL: $url\n";
echo "Method: GET\n";
echo "Headers: Authorization: [API_KEY]\n\n";

// Make API request using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: ' . $api_key,  // Booking Manager uses raw API key, not Bearer token
    'Accept: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "Response:\n";
echo "HTTP Status Code: $http_code\n";

if ($curl_error) {
    echo "cURL Error: $curl_error\n";
    exit(1);
}

if ($http_code == 200) {
    $data = json_decode($response, true);
    
    if ($data === null) {
        echo "❌ ERROR: Failed to parse JSON response\n";
        echo "Raw response: " . substr($response, 0, 500) . "\n";
        exit(1);
    }
    
    echo "✅ SUCCESS: API call successful\n\n";
    
    // Handle different response formats
    $offers = array();
    if (isset($data['offers']) && is_array($data['offers'])) {
        $offers = $data['offers'];
    } elseif (is_array($data) && isset($data[0])) {
        $offers = $data;
    } elseif (is_array($data) && isset($data['yachtId'])) {
        $offers = array($data);
    } else {
        $offers = $data;
    }
    
    echo "Number of offers found: " . count($offers) . "\n\n";
    
    if (count($offers) > 0) {
        echo "✅ AVAILABILITY: Lemon appears to be AVAILABLE for June 1-8, 2026\n\n";
        
        // Find offers that match our date range
        $matching_offers = array();
        foreach ($offers as $offer) {
            if (isset($offer['yachtId']) && $offer['yachtId'] == $yacht_id) {
                $matching_offers[] = $offer;
            }
        }
        
        if (count($matching_offers) > 0) {
            echo "Matching offers for Lemon:\n";
            echo str_repeat("-", 60) . "\n";
            
            foreach ($matching_offers as $index => $offer) {
                echo "\nOffer #" . ($index + 1) . ":\n";
                echo "  Yacht ID: " . (isset($offer['yachtId']) ? $offer['yachtId'] : 'N/A') . "\n";
                echo "  Date From: " . (isset($offer['dateFrom']) ? $offer['dateFrom'] : 'N/A') . "\n";
                echo "  Date To: " . (isset($offer['dateTo']) ? $offer['dateTo'] : 'N/A') . "\n";
                echo "  Price: " . (isset($offer['price']) ? number_format($offer['price'], 2) : 'N/A') . " " . (isset($offer['currency']) ? $offer['currency'] : 'EUR') . "\n";
                if (isset($offer['startPrice'])) {
                    echo "  Start Price: " . number_format($offer['startPrice'], 2) . " " . (isset($offer['currency']) ? $offer['currency'] : 'EUR') . "\n";
                }
                if (isset($offer['discountPercentage'])) {
                    echo "  Discount: " . $offer['discountPercentage'] . "%\n";
                }
                if (isset($offer['available']) && is_bool($offer['available'])) {
                    echo "  Available: " . ($offer['available'] ? 'Yes' : 'No') . "\n";
                }
            }
        } else {
            echo "Note: Found offers but none specifically match Lemon yacht ID\n";
            echo "First offer sample:\n";
            print_r($offers[0]);
        }
    } else {
        echo "❌ AVAILABILITY: Lemon does NOT appear to be available for June 1-8, 2026\n";
        echo "(No offers returned for this date range)\n";
    }
    
    // Save full response to file for inspection
    $output_file = 'lemon-june-2026-offers-response.json';
    file_put_contents($output_file, json_encode($data, JSON_PRETTY_PRINT));
    echo "\n\nFull API response saved to: $output_file\n";
    
} else {
    echo "❌ ERROR: API request failed\n";
    echo "HTTP Status: $http_code\n";
    echo "Response: " . substr($response, 0, 1000) . "\n";
    
    $error_data = json_decode($response, true);
    if ($error_data && isset($error_data['message'])) {
        echo "\nError Message: " . $error_data['message'] . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Test Complete\n";

