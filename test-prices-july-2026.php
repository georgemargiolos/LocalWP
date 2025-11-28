<?php
/**
 * Test prices for July 2026 and various date ranges
 */

$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$base_url = 'https://www.booking-manager.com/api/v2';
$company_id = 7850;

function test_prices($label, $dateFrom, $dateTo, $company_id, $api_key, $base_url, $extra_params = array()) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "TEST: $label\n";
    echo str_repeat("=", 80) . "\n";
    
    $params = array_merge(array(
        'companyId' => $company_id,
        'dateFrom' => $dateFrom,
        'dateTo' => $dateTo
    ), $extra_params);
    
    echo "Parameters: " . json_encode($params, JSON_PRETTY_PRINT) . "\n\n";
    
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
            echo "✅ Records returned: " . count($data) . "\n";
            
            if (count($data) > 0) {
                echo "\n📊 First 3 price records:\n";
                foreach (array_slice($data, 0, 3) as $i => $price) {
                    echo "\n" . ($i + 1) . ". Yacht ID: " . ($price['yachtId'] ?? 'N/A') . "\n";
                    echo "   Date: " . ($price['dateFrom'] ?? 'N/A') . " to " . ($price['dateTo'] ?? 'N/A') . "\n";
                    echo "   Product: " . ($price['product'] ?? 'N/A') . "\n";
                    echo "   Price: " . ($price['price'] ?? 'N/A') . " " . ($price['currency'] ?? 'N/A') . "\n";
                    if (isset($price['startPrice']) && $price['startPrice'] != $price['price']) {
                        echo "   Start Price: " . $price['startPrice'] . " (Discount: " . ($price['discountPercentage'] ?? 0) . "%)\n";
                    }
                }
                
                // Save full response to file
                $filename = 'prices-' . str_replace(array(':', ' '), array('-', '_'), $label) . '.json';
                file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT));
                echo "\n💾 Full response saved to: $filename\n";
            }
        } else {
            echo "❌ Response is not an array\n";
            echo "Response: " . substr($response, 0, 500) . "\n";
        }
    } else {
        echo "❌ Error: HTTP $http_code\n";
        echo "Response: " . substr($response, 0, 500) . "\n";
    }
    
    return ($http_code === 200 && is_array($data = json_decode($response, true))) ? count($data) : 0;
}

echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║  BOOKING MANAGER API - PRICE DATA INVESTIGATION FOR COMPANY 7850 (YOLO)     ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";

// Test 1: July 2026 specifically
$count1 = test_prices(
    "July 2026 (Full Month)",
    '2026-07-01T00:00:00',
    '2026-07-31T23:59:59',
    $company_id,
    $api_key,
    $base_url
);

// Test 2: July 2026 (just first week)
$count2 = test_prices(
    "July 2026 (First Week)",
    '2026-07-01T00:00:00',
    '2026-07-07T23:59:59',
    $company_id,
    $api_key,
    $base_url
);

// Test 3: June-August 2026 (peak season)
$count3 = test_prices(
    "June-August 2026 (Peak Season)",
    '2026-06-01T00:00:00',
    '2026-08-31T23:59:59',
    $company_id,
    $api_key,
    $base_url
);

// Test 4: Entire 2026
$count4 = test_prices(
    "Entire Year 2026",
    '2026-01-01T00:00:00',
    '2026-12-31T23:59:59',
    $company_id,
    $api_key,
    $base_url
);

// Test 5: Current date to 1 year ahead
$count5 = test_prices(
    "Current Date to 1 Year Ahead",
    date('Y-m-d') . 'T00:00:00',
    date('Y-m-d', strtotime('+1 year')) . 'T23:59:59',
    $company_id,
    $api_key,
    $base_url
);

// Test 6: Try without companyId (maybe it's wrong parameter?)
echo "\n" . str_repeat("=", 80) . "\n";
echo "TEST: Without companyId parameter\n";
echo str_repeat("=", 80) . "\n";
$url = $base_url . '/prices?dateFrom=2026-07-01T00:00:00&dateTo=2026-07-31T23:59:59';
echo "URL: $url\n\n";

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

echo "HTTP Status: $http_code\n";
if ($http_code === 200) {
    $data = json_decode($response, true);
    $count6 = is_array($data) ? count($data) : 0;
    echo "Records returned: $count6\n";
    if ($count6 > 0) {
        echo "✅ SUCCESS! Prices found WITHOUT companyId parameter!\n";
        echo "First record: " . json_encode($data[0], JSON_PRETTY_PRINT) . "\n";
    }
} else {
    $count6 = 0;
    echo "Error: $http_code\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
}

// Test 7: Try with company parameter instead of companyId
$count7 = test_prices(
    "July 2026 with 'company' parameter",
    '2026-07-01T00:00:00',
    '2026-07-31T23:59:59',
    $company_id,
    $api_key,
    $base_url,
    array('company' => $company_id)
);

echo "\n\n";
echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
echo "║                                   SUMMARY                                    ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

$tests = array(
    "July 2026 (Full Month)" => $count1,
    "July 2026 (First Week)" => $count2,
    "June-August 2026 (Peak)" => $count3,
    "Entire Year 2026" => $count4,
    "Current Date to 1 Year" => $count5,
    "Without companyId" => $count6,
    "With 'company' param" => $count7
);

foreach ($tests as $test => $count) {
    $status = $count > 0 ? "✅ $count records" : "❌ 0 records";
    printf("%-30s : %s\n", $test, $status);
}

if (array_sum($tests) === 0) {
    echo "\n⚠️  WARNING: NO PRICE DATA FOUND IN ANY TEST!\n";
    echo "This suggests either:\n";
    echo "1. Company 7850 has no price data in the API\n";
    echo "2. The API key doesn't have access to this company's prices\n";
    echo "3. We're using the wrong parameter name or format\n";
} else {
    echo "\n✅ SUCCESS! Price data was found in at least one test.\n";
}
