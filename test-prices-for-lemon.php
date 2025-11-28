<?php
/**
 * Test get_prices API for specific yacht (Lemon)
 */

$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$base_url = 'https://www.booking-manager.com/api/v2';

$yacht_id = 6362109340000107850; // Lemon
$company_id = 7850; // YOLO company

echo "Testing Booking Manager API /prices endpoint for Yacht: Lemon\n";
echo "==============================================================\n\n";
echo "Yacht ID: $yacht_id\n";
echo "Company ID: $company_id\n\n";

// Test 1: Get prices for company (what the current code does)
echo "TEST 1: Get prices by companyId (current approach)\n";
echo "---------------------------------------------------\n";

$date_from = '2026-05-01T00:00:00';
$date_to = '2026-09-30T23:59:59';

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
    echo "Total price records: " . count($data) . "\n";
    
    // Filter for Lemon
    $lemon_prices = array_filter($data, function($price) use ($yacht_id) {
        return isset($price['yachtId']) && $price['yachtId'] == $yacht_id;
    });
    
    echo "Price records for Lemon: " . count($lemon_prices) . "\n\n";
    
    if (count($lemon_prices) > 0) {
        echo "Sample price records for Lemon:\n";
        $count = 0;
        foreach ($lemon_prices as $price) {
            if ($count >= 3) break;
            echo "\n  Record " . ($count + 1) . ":\n";
            echo "    Date From: " . ($price['dateFrom'] ?? 'N/A') . "\n";
            echo "    Date To: " . ($price['dateTo'] ?? 'N/A') . "\n";
            echo "    Price: " . ($price['price'] ?? 'N/A') . " " . ($price['currency'] ?? '') . "\n";
            echo "    Product: " . ($price['product'] ?? 'N/A') . "\n";
            $count++;
        }
    }
} else {
    echo "Error: HTTP $http_code\n";
    echo substr($response, 0, 500) . "\n";
}

echo "\n\n";

// Test 2: Try with yachtId parameter
echo "TEST 2: Get prices by yachtId (alternative approach)\n";
echo "-----------------------------------------------------\n";

$params2 = array(
    'yachtId' => $yacht_id,
    'dateFrom' => $date_from,
    'dateTo' => $date_to
);

$url2 = $base_url . '/prices?' . http_build_query($params2);
echo "URL: $url2\n\n";

$start_time = microtime(true);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url2);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: ' . $api_key,
    'Accept: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response2 = curl_exec($ch);
$http_code2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$end_time = microtime(true);
$duration2 = round($end_time - $start_time, 2);

echo "HTTP Status: $http_code2\n";
echo "Duration: {$duration2}s\n";

if ($http_code2 === 200) {
    $data2 = json_decode($response2, true);
    echo "Total price records: " . count($data2) . "\n\n";
    
    if (count($data2) > 0) {
        echo "Sample price records:\n";
        for ($i = 0; $i < min(3, count($data2)); $i++) {
            echo "\n  Record " . ($i + 1) . ":\n";
            echo "    Yacht ID: " . ($data2[$i]['yachtId'] ?? 'N/A') . "\n";
            echo "    Date From: " . ($data2[$i]['dateFrom'] ?? 'N/A') . "\n";
            echo "    Date To: " . ($data2[$i]['dateTo'] ?? 'N/A') . "\n";
            echo "    Price: " . ($data2[$i]['price'] ?? 'N/A') . " " . ($data2[$i]['currency'] ?? '') . "\n";
            echo "    Product: " . ($data2[$i]['product'] ?? 'N/A') . "\n";
        }
        
        // Save full response
        file_put_contents('/home/ubuntu/LocalWP/lemon-prices-response.json', json_encode($data2, JSON_PRETTY_PRINT));
        echo "\n\nFull response saved to: lemon-prices-response.json\n";
    }
} else {
    echo "Error: HTTP $http_code2\n";
    echo substr($response2, 0, 500) . "\n";
}
