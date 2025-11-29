<?php
/**
 * Test script to simulate yacht sync and identify hanging issues
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting yacht sync test...\n\n";

// Simulate API call
$api_key = '02e4d3a3-1f9a-4f7e-b0c6-8d5e9f2a7b3c';
$company_id = 7850;

echo "Testing API call for company $company_id...\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.bmanager.net/v2/yachts?companyId=$company_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json'
));
curl_setopt($ch, CURLOPT_TIMEOUT, 180);

$start_time = microtime(true);
$response = curl_exec($ch);
$end_time = microtime(true);

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

echo "API call completed in " . round($end_time - $start_time, 2) . " seconds\n";
echo "HTTP Code: $http_code\n";

if ($curl_error) {
    echo "CURL Error: $curl_error\n";
    exit(1);
}

if ($http_code !== 200) {
    echo "API Error: HTTP $http_code\n";
    echo "Response: " . substr($response, 0, 500) . "\n";
    exit(1);
}

$yachts = json_decode($response, true);

if (!is_array($yachts)) {
    echo "ERROR: Response is not an array\n";
    exit(1);
}

echo "\nYachts returned: " . count($yachts) . "\n\n";

// Simulate processing each yacht
foreach ($yachts as $index => $yacht) {
    echo "Processing yacht " . ($index + 1) . ": " . $yacht['name'] . "\n";
    
    // Check equipment
    if (isset($yacht['equipment']) && is_array($yacht['equipment'])) {
        echo "  - Equipment items: " . count($yacht['equipment']) . "\n";
    }
    
    // Check extras
    $extras_count = 0;
    if (isset($yacht['products']) && is_array($yacht['products'])) {
        foreach ($yacht['products'] as $product) {
            if (isset($product['extras']) && is_array($product['extras'])) {
                $extras_count += count($product['extras']);
            }
        }
    }
    echo "  - Extras: $extras_count\n";
}

echo "\n✅ Test completed successfully!\n";
