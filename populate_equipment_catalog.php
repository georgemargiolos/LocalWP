<?php
// Populate equipment catalog from API
$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$url = 'https://www.booking-manager.com/api/v2/equipment';

echo "Fetching equipment catalog from API...\n";

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

if ($http_code === 200) {
    $equipment = json_decode($response, true);
    echo "Fetched " . count($equipment) . " equipment items\n\n";
    
    // Generate SQL INSERT statements
    $sql_file = "/home/ubuntu/LocalWP/equipment_catalog_inserts.sql";
    $fp = fopen($sql_file, 'w');
    
    foreach ($equipment as $item) {
        $id = $item['id'];
        $name = addslashes($item['name']);
        $sql = "INSERT INTO wp_yolo_equipment_catalog (id, name, last_synced) VALUES ($id, '$name', NOW()) ON DUPLICATE KEY UPDATE name='$name', last_synced=NOW();\n";
        fwrite($fp, $sql);
    }
    
    fclose($fp);
    echo "SQL file generated: $sql_file\n";
    echo "Run: sudo mysql user_db < $sql_file\n";
    
} else {
    echo "Failed to fetch equipment catalog (HTTP $http_code)\n";
}
?>
