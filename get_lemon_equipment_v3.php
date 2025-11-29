<?php
// Database credentials for LocalWP
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "local";

// Yacht ID for 'Lemon'
$yacht_id = 6362109340000107850;

echo "Fetching equipment for Yacht ID: " . $yacht_id . " (Lemon)\n";
echo "==============================================================\n\n";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$equipment_table = "wp_yolo_yacht_equipment";
$sql = "SELECT * FROM " . $equipment_table . " WHERE yacht_id = " . $yacht_id;

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Equipment List:\n";
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "- " . $row["equipment_name"]. "\n";
    }
} else {
    echo "No equipment found for this yacht.\n";
}
$conn->close();
?>
