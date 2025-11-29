<?php
$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$base_url = 'https://www.booking-manager.com/api/v2';

echo "Testing Booking Manager REST API v2 for equipment endpoints\n";
echo "==============================================================\n\n";

$endpoints_to_test = [
    '/equipment',
    '/equipment-categories',
    '/equipmentCategories',
    '/generic-equipment',
    '/equipment-catalog'
];

foreach ($endpoints_to_test as $endpoint) {
    echo "Testing: $endpoint\n";
    
    $url = $base_url . $endpoint;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: ' . $api_key,
        'Accept: application/json'
    ));
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Status: $http_code\n";
    
    if ($http_code === 200) {
        echo "✓ SUCCESS! Endpoint exists!\n";
        echo "Response preview:\n";
        echo substr($response, 0, 500) . "\n";
        echo "\n";
    } else {
        echo "✗ Not found or error\n\n";
    }
}
?>
