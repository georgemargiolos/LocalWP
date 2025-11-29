<?php
$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$base_url = 'https://www.booking-manager.com/api/v2';

echo "Fetching yacht list from Booking Manager API\n";
echo "==============================================================\n\n";

$url = $base_url . '/yachts';

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
    $data = json_decode($response, true);
    
    $yachts_with_equipment = 0;
    foreach ($data as $yacht) {
        if (!empty($yacht['equipment'])) {
            $yachts_with_equipment++;
            echo "\n--- Yacht with Equipment ---\n";
            echo "Yacht ID: " . $yacht['id'] . "\n";
            echo "Yacht Name: " . $yacht['name'] . "\n";
            echo "Equipment:\n";
            print_r($yacht['equipment']);
        }
    }
    
    if ($yachts_with_equipment === 0) {
        echo "\nNo yachts found with equipment information in the API response.\n";
    }
    
} else {
    echo "Error: HTTP $http_code\n";
    echo substr($response, 0, 1000) . "\n";
}
?>
