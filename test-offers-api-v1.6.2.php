<?php
/**
 * Test Booking Manager /offers API endpoint
 * Tests both single company and multiple companies to verify HTTP 500 issue
 */

// API Configuration
$base_url = 'https://api.booking-manager.com/v2';
$api_key = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJjb21wYW55SWQiOjc4NTAsInVzZXJJZCI6MTU5NjEsImV4cCI6MTc2NDU1NjgwMH0.xhIiTx5oON8cWFbXMjMvBBGAZ8UUPLBFYTVJZaHWGQs';

$companies = [7850, 4366, 3604, 6711];
$year = 2026;
$dateFrom = "{$year}-01-01T00:00:00";
$dateTo = "{$year}-12-31T23:59:59";

echo "=== Booking Manager API Test ===\n\n";

// Test 1: Single company (should work)
echo "TEST 1: Single company (7850)\n";
echo str_repeat("-", 50) . "\n";

$params = [
    'companyId' => [7850],
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'tripDuration' => [7],
    'flexibility' => 6,
    'productName' => 'bareboat'
];

$url = $base_url . '/offers?' . http_build_query($params);
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";
if ($error) {
    echo "cURL Error: $error\n";
}

if ($http_code == 200) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        echo "✅ SUCCESS: Received " . count($data) . " offers\n";
        if (count($data) > 0) {
            echo "First offer sample:\n";
            print_r(array_slice($data, 0, 1));
        }
    } else {
        echo "❌ ERROR: Response is not an array\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
} else {
    echo "❌ ERROR: HTTP $http_code\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

echo "\n\n";

// Test 2: Multiple companies (might fail with HTTP 500)
echo "TEST 2: Multiple companies (all 4)\n";
echo str_repeat("-", 50) . "\n";

$params = [
    'companyId' => [7850, 4366, 3604, 6711],
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'tripDuration' => [7],
    'flexibility' => 6,
    'productName' => 'bareboat'
];

$url = $base_url . '/offers?' . http_build_query($params);
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";
if ($error) {
    echo "cURL Error: $error\n";
}

if ($http_code == 200) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        echo "✅ SUCCESS: Received " . count($data) . " offers\n";
        if (count($data) > 0) {
            echo "First offer sample:\n";
            print_r(array_slice($data, 0, 1));
        }
    } else {
        echo "❌ ERROR: Response is not an array\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
} else {
    echo "❌ ERROR: HTTP $http_code (Expected - this is the bug!)\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

echo "\n\n";

// Test 3: Custom query string (manual encoding without brackets)
echo "TEST 3: Custom query string (repeated parameters)\n";
echo str_repeat("-", 50) . "\n";

$query_parts = [];
foreach ([7850, 4366, 3604, 6711] as $companyId) {
    $query_parts[] = 'companyId=' . urlencode($companyId);
}
$query_parts[] = 'dateFrom=' . urlencode($dateFrom);
$query_parts[] = 'dateTo=' . urlencode($dateTo);
$query_parts[] = 'tripDuration=7';
$query_parts[] = 'flexibility=6';
$query_parts[] = 'productName=bareboat';

$url = $base_url . '/offers?' . implode('&', $query_parts);
echo "URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status: $http_code\n";
if ($error) {
    echo "cURL Error: $error\n";
}

if ($http_code == 200) {
    $data = json_decode($response, true);
    if (is_array($data)) {
        echo "✅ SUCCESS: Received " . count($data) . " offers\n";
        echo "This proves custom encoding works!\n";
        if (count($data) > 0) {
            echo "First offer sample:\n";
            print_r(array_slice($data, 0, 1));
        }
    } else {
        echo "❌ ERROR: Response is not an array\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
} else {
    echo "❌ ERROR: HTTP $http_code\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

echo "\n\n=== Test Complete ===\n";
