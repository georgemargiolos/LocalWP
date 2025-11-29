<?php
// Test equipment catalog sync and yacht equipment storage

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Equipment Catalog Sync Test\n";
echo "==============================================================\n\n";

// Step 1: Create equipment catalog table
echo "Step 1: Creating equipment catalog table...\n";
$sql = "CREATE TABLE IF NOT EXISTS wp_yolo_equipment_catalog (
    id bigint(20) NOT NULL,
    name varchar(255) NOT NULL,
    last_synced datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)";

if ($conn->query($sql) === TRUE) {
    echo "✓ Equipment catalog table created successfully\n\n";
} else {
    echo "✗ Error creating table: " . $conn->error . "\n\n";
}

// Step 2: Fetch equipment catalog from API
echo "Step 2: Fetching equipment catalog from API...\n";
$api_key = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe';
$url = 'https://www.booking-manager.com/api/v2/equipment';

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
    echo "✓ Fetched " . count($equipment) . " equipment items\n\n";
    
    // Step 3: Store equipment in database
    echo "Step 3: Storing equipment catalog in database...\n";
    $stored_count = 0;
    foreach ($equipment as $item) {
        $stmt = $conn->prepare("REPLACE INTO wp_yolo_equipment_catalog (id, name, last_synced) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $item['id'], $item['name']);
        if ($stmt->execute()) {
            $stored_count++;
        }
        $stmt->close();
    }
    echo "✓ Stored $stored_count equipment items\n\n";
    
    // Step 4: Show sample equipment
    echo "Step 4: Sample equipment from catalog:\n";
    $result = $conn->query("SELECT * FROM wp_yolo_equipment_catalog LIMIT 10");
    while ($row = $result->fetch_assoc()) {
        echo "  ID " . $row['id'] . ": " . $row['name'] . "\n";
    }
    echo "\n";
    
    // Step 5: Test equipment mapping for Lemon yacht
    echo "Step 5: Testing equipment mapping for Lemon yacht (ID: 6362109340000107850)...\n";
    $lemon_equipment_ids = [27, 46, 11, 21, 18, 37, 33, 50, 25, 45, 14, 42];
    
    echo "Equipment for Lemon:\n";
    foreach ($lemon_equipment_ids as $eq_id) {
        $stmt = $conn->prepare("SELECT name FROM wp_yolo_equipment_catalog WHERE id = ?");
        $stmt->bind_param("i", $eq_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        if ($row) {
            echo "  - " . $row['name'] . "\n";
        } else {
            echo "  - [ID $eq_id: Not found in catalog]\n";
        }
        $stmt->close();
    }
    
} else {
    echo "✗ Failed to fetch equipment catalog (HTTP $http_code)\n";
}

$conn->close();
?>
