<?php
/**
 * Direct test of Booking Manager API /prices endpoint
 */

$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$base_url = 'https://www.booking-manager.com/api/v2';

// Test with 3 months
$company_id = 7850; // YOLO company
$date_from = date('Y-m-d') . 'T00:00:00';
$date_to = date('Y-m-d', strtotime('+3 months')) . 'T23:59:59';

echo "Testing Booking Manager API /prices endpoint\n";
echo "============================================\n\n";
echo "Company ID: $company_id\n";
echo "Date From: $date_from\n";
echo "Date To: $date_to\n\n";

$params = array(
    'companyId' => $company_id,
    'dateFrom' => $date_from,
    'dateTo' => $date_to
);

$url = $base_url . '/prices?' . http_build_query($params);

echo "URL: $url\n\n";

$start_time = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: ' . $api_key,
    'Accept: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // 60 second timeout

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

$end_time = microtime(true);
$duration = round($end_time - $start_time, 2);

echo "HTTP Status Code: $http_code\n";
echo "Request Duration: {$duration} seconds\n\n";

if ($curl_error) {
    echo "CURL Error: $curl_error\n";
    exit(1);
}

if ($http_code !== 200) {
    echo "API Error Response:\n";
    echo $response . "\n";
    exit(1);
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Decode Error: " . json_last_error_msg() . "\n";
    echo "Raw Response:\n";
    echo substr($response, 0, 1000) . "\n";
    exit(1);
}

echo "SUCCESS!\n";
echo "Number of price records returned: " . count($data) . "\n\n";

if (count($data) > 0) {
    echo "First price record:\n";
    print_r($data[0]);
    echo "\n";
    
    // Save to file for inspection
    file_put_contents('/home/ubuntu/LocalWP/test-prices-response.json', json_encode($data, JSON_PRETTY_PRINT));
    echo "Full response saved to: test-prices-response.json\n";
}
