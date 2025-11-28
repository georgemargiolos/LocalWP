<?php
/**
 * Test different parameter combinations for /prices endpoint
 */

$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$base_url = 'https://www.booking-manager.com/api/v2';

function test_prices_endpoint($label, $params, $api_key, $base_url) {
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "TEST: $label\n";
    echo str_repeat("=", 70) . "\n";
    echo "Parameters: " . json_encode($params) . "\n\n";
    
    $url = $base_url . '/prices?' . http_build_query($params);
    
    $start_time = microtime(true);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: ' . $api_key,
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $end_time = microtime(true);
    $duration = round($end_time - $start_time, 2);
    
    echo "HTTP Status: $http_code\n";
    echo "Duration: {$duration}s\n";
    
    if ($http_code === 200) {
        $data = json_decode($response, true);
        if (is_array($data)) {
            echo "Records returned: " . count($data) . "\n";
            
            if (count($data) > 0) {
                echo "\nFirst record structure:\n";
                $first = $data[0];
                foreach ($first as $key => $value) {
                    if (is_array($value)) {
                        echo "  $key: [array with " . count($value) . " items]\n";
                    } else {
                        echo "  $key: $value\n";
                    }
                }
            }
        } else {
            echo "Response is not an array\n";
            echo "Response: " . substr($response, 0, 500) . "\n";
        }
    } else {
        echo "Error: HTTP $http_code\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
    
    return array('status' => $http_code, 'count' => ($http_code === 200 && is_array($data = json_decode($response, true))) ? count($data) : 0);
}

echo "BOOKING MANAGER API /prices ENDPOINT PARAMETER TESTING\n";
echo "=======================================================\n";

$company_id = 7850;
$yacht_id = 6362109340000107850; // Lemon

// Test 1: No parameters
$result1 = test_prices_endpoint(
    "No parameters",
    array(),
    $api_key,
    $base_url
);

// Test 2: Only companyId
$result2 = test_prices_endpoint(
    "Only companyId",
    array('companyId' => $company_id),
    $api_key,
    $base_url
);

// Test 3: companyId + dates (current to 3 months)
$result3 = test_prices_endpoint(
    "companyId + dates (3 months)",
    array(
        'companyId' => $company_id,
        'dateFrom' => date('Y-m-d') . 'T00:00:00',
        'dateTo' => date('Y-m-d', strtotime('+3 months')) . 'T23:59:59'
    ),
    $api_key,
    $base_url
);

// Test 4: companyId + dates (peak season 2026)
$result4 = test_prices_endpoint(
    "companyId + dates (peak season 2026)",
    array(
        'companyId' => $company_id,
        'dateFrom' => '2026-05-01T00:00:00',
        'dateTo' => '2026-09-30T23:59:59'
    ),
    $api_key,
    $base_url
);

// Test 5: Only yachtId
$result5 = test_prices_endpoint(
    "Only yachtId",
    array('yachtId' => $yacht_id),
    $api_key,
    $base_url
);

// Test 6: yachtId + dates
$result6 = test_prices_endpoint(
    "yachtId + dates (peak season 2026)",
    array(
        'yachtId' => $yacht_id,
        'dateFrom' => '2026-05-01T00:00:00',
        'dateTo' => '2026-09-30T23:59:59'
    ),
    $api_key,
    $base_url
);

// Test 7: Try with product parameter
$result7 = test_prices_endpoint(
    "companyId + dates + product=Bareboat",
    array(
        'companyId' => $company_id,
        'dateFrom' => '2026-05-01T00:00:00',
        'dateTo' => '2026-09-30T23:59:59',
        'product' => 'Bareboat'
    ),
    $api_key,
    $base_url
);

// Test 8: Try with baseId parameter
$result8 = test_prices_endpoint(
    "companyId + dates + baseId",
    array(
        'companyId' => $company_id,
        'dateFrom' => '2026-05-01T00:00:00',
        'dateTo' => '2026-09-30T23:59:59',
        'baseId' => 1
    ),
    $api_key,
    $base_url
);

echo "\n\n" . str_repeat("=", 70) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 70) . "\n";
echo "Test 1 (No params): {$result1['status']} - {$result1['count']} records\n";
echo "Test 2 (companyId only): {$result2['status']} - {$result2['count']} records\n";
echo "Test 3 (companyId + 3mo): {$result3['status']} - {$result3['count']} records\n";
echo "Test 4 (companyId + peak): {$result4['status']} - {$result4['count']} records\n";
echo "Test 5 (yachtId only): {$result5['status']} - {$result5['count']} records\n";
echo "Test 6 (yachtId + dates): {$result6['status']} - {$result6['count']} records\n";
echo "Test 7 (+ product): {$result7['status']} - {$result7['count']} records\n";
echo "Test 8 (+ baseId): {$result8['status']} - {$result8['count']} records\n";
