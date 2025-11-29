<?php
// Populate yacht equipment from the JSON response using the equipment catalog

echo "Populating yacht equipment from JSON response...\n";
echo "==============================================================\n\n";

// Read the JSON file
$json_file = '/home/ubuntu/upload/response_1764428631662.json';
$json_data = file_get_contents($json_file);
$yachts = json_decode($json_data, true);

echo "Found " . count($yachts) . " yachts in JSON\n\n";

// Generate SQL for yacht equipment
$sql_file = "/home/ubuntu/LocalWP/yacht_equipment_inserts.sql";
$fp = fopen($sql_file, 'w');

$total_equipment = 0;

foreach ($yachts as $yacht) {
    $yacht_id = $yacht['id'];
    $yacht_name = $yacht['name'];
    
    if (isset($yacht['equipment']) && is_array($yacht['equipment'])) {
        echo "Processing equipment for: $yacht_name (ID: $yacht_id)\n";
        echo "  Equipment count: " . count($yacht['equipment']) . "\n";
        
        foreach ($yacht['equipment'] as $equip) {
            $equipment_id = $equip['id'];
            
            // SQL to insert equipment with name lookup from catalog
            $sql = "INSERT INTO wp_yolo_yacht_equipment (yacht_id, equipment_id, equipment_name, category) 
                    SELECT $yacht_id, $equipment_id, name, NULL 
                    FROM wp_yolo_equipment_catalog 
                    WHERE id = $equipment_id
                    ON DUPLICATE KEY UPDATE equipment_name=VALUES(equipment_name);\n";
            fwrite($fp, $sql);
            $total_equipment++;
        }
    }
}

fclose($fp);

echo "\nTotal equipment entries to insert: $total_equipment\n";
echo "SQL file generated: $sql_file\n";
echo "Run: sudo mysql user_db < $sql_file\n";
?>
