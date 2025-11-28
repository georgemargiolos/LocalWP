<?php
/**
 * Test: Get prices for Strawberry boat (Lagoon 440)
 * Yacht ID: 7136018700000107850
 */

$apiKey = 'c1f1408e9e3a4bd5b0f8e6c7d2a3b4c5';
$companyId = 7850;
$yachtId = '7136018700000107850';

// Peak season 2026
$dateFrom = '2026-05-01T00:00:00';
$dateTo = '2026-09-30T23:59:59';

$url = 'https://api.booking-manager.com/2.2/prices';
$url .= '?dateFrom=' . urlencode($dateFrom);
$url .= '&dateTo=' . urlencode($dateTo);
$url .= '&companyId=' . $companyId;
$url .= '&yachtId=' . $yachtId;

echo "Testing Prices API for Strawberry (Lagoon 440)\n";
echo str_repeat("=", 70) . "\n\n";
echo "API URL: $url\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Key: ' . $apiKey,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$startTime = microtime(true);
$response = curl_exec($ch);
$endTime = microtime(true);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$duration = round(($endTime - $startTime) * 1000);

echo "HTTP Code: $httpCode\n";
echo "Duration: {$duration}ms\n\n";

if ($error) {
    echo "CURL Error: $error\n";
    exit(1);
}

if ($httpCode !== 200) {
    echo "Error Response:\n";
    echo $response . "\n";
    exit(1);
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON Parse Error: " . json_last_error_msg() . "\n";
    echo "Raw Response:\n$response\n";
    exit(1);
}

echo "Results:\n";
echo str_repeat("=", 70) . "\n";

if (empty($data)) {
    echo "No price records found!\n";
    exit(0);
}

echo "Total Records: " . count($data) . "\n\n";

foreach ($data as $index => $price) {
    $num = $index + 1;
    echo "Record #{$num}:\n";
    echo "  Yacht ID: " . ($price['yachtId'] ?? 'N/A') . "\n";
    echo "  Date From: " . ($price['dateFrom'] ?? 'N/A') . "\n";
    echo "  Date To: " . ($price['dateTo'] ?? 'N/A') . "\n";
    echo "  Product: " . ($price['product'] ?? 'N/A') . "\n";
    echo "  Price: " . ($price['price'] ?? 'N/A') . " " . ($price['currency'] ?? 'N/A') . "\n";
    echo "  Start Price: " . ($price['startPrice'] ?? 'N/A') . " " . ($price['currency'] ?? 'N/A') . "\n";
    echo "  Discount: " . ($price['discountPercentage'] ?? 0) . "%\n";
    
    // Calculate period length
    if (isset($price['dateFrom']) && isset($price['dateTo'])) {
        $from = new DateTime(substr($price['dateFrom'], 0, 10));
        $to = new DateTime(substr($price['dateTo'], 0, 10));
        $days = $from->diff($to)->days;
        $weeks = round($days / 7, 1);
        echo "  Period: {$days} days (~{$weeks} weeks)\n";
        
        if ($weeks > 1) {
            $pricePerWeek = round($price['price'] / $weeks, 2);
            echo "  Price per week: {$pricePerWeek} " . ($price['currency'] ?? 'EUR') . "\n";
        }
    }
    
    echo "\n";
}

echo str_repeat("=", 70) . "\n";
echo "Test completed!\n";
