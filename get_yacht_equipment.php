<?php
// Load WordPress environment
require_once("yolo-yacht-search/yolo-yacht-search.php");

// Yacht ID for 'Lemon'
$yacht_id = 6362109340000107850;

echo "Fetching equipment for Yacht ID: " . $yacht_id . " (Lemon)\n";
echo "==============================================================\n\n";

// Get equipment data
global $wpdb;
$equipment_table = $wpdb->prefix . "yolo_yacht_equipment";
$equipment = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $equipment_table WHERE yacht_id = %s",
    $yacht_id
));

if (empty($equipment)) {
    echo "No equipment found for this yacht.\n";
} else {
    echo "Equipment List:\n";
    foreach ($equipment as $item) {
        echo "- " . esc_html($item->equipment_name) . "\n";
    }
}
?>
