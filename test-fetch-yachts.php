<?php
/**
 * Test script to fetch yachts from Booking Manager API
 * Using GET /yachts endpoint with companyId parameter
 */

// Your API key
$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';

// Start with YOLO company only
$company_id = 7850;

// Base URL
$base_url = 'https://www.booking-manager.com/api/v2';

echo "=== YOLO Yacht Search - API Test ===\n\n";
echo "Fetching yachts for Company ID: $company_id (YOLO)\n";
echo str_repeat('=', 60) . "\n\n";

// Endpoint: Get yachts with companyId filter
$url = $base_url . '/yachts?companyId=' . $company_id;

echo "URL: $url\n\n";

// Make request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $api_key,
    'Accept: application/json'
]);

echo "Making API request...\n\n";

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code === 200) {
    $data = json_decode($response, true);
    
    if (is_array($data)) {
        echo "✅ SUCCESS! Found " . count($data) . " yachts for YOLO (Company 7850)\n";
        echo str_repeat('=', 60) . "\n\n";
        
        // Show summary of all yachts
        echo "YACHT SUMMARY:\n";
        echo str_repeat('-', 60) . "\n";
        foreach ($data as $index => $yacht) {
            $num = $index + 1;
            echo "$num. {$yacht['name']} (ID: {$yacht['id']})\n";
            echo "   Model: {$yacht['model']}\n";
            if (isset($yacht['products']) && is_array($yacht['products'])) {
                echo "   Products: " . count($yacht['products']) . "\n";
            }
            echo "\n";
        }
        
        // Show detailed info for first yacht
        if (count($data) > 0) {
            echo "\n" . str_repeat('=', 60) . "\n";
            echo "DETAILED INFO - FIRST YACHT:\n";
            echo str_repeat('=', 60) . "\n\n";
            print_r($data[0]);
        }
        
        // Save to file
        file_put_contents('/home/ubuntu/LocalWP/yachts-company-7850.json', json_encode($data, JSON_PRETTY_PRINT));
        echo "\n\n✅ Full data saved to: yachts-company-7850.json\n";
        
    } else {
        echo "⚠️ No data returned or invalid format\n\n";
    }
} else {
    echo "❌ Error: HTTP $http_code\n";
    echo "Response: " . substr($response, 0, 500) . "\n\n";
}

echo "\n" . str_repeat('=', 60) . "\n";
echo "Test Complete\n";
echo str_repeat('=', 60) . "\n";
