<?php
// Simulate WordPress environment
define("ABSPATH", dirname(__FILE__) . "/");
define("WPINC", "wp-includes");

// Mock the WordPress database class and other functions if they don\\\\'t exist
if (!class_exists("wpdb")) {
    class wpdb {
        public $prefix = "wp_yolo_";
        public function prepare($query, $args) {
            // A very simple prepare mock
            $query = str_replace("%s", "\\\"" . $args . "\\\"", $query);
            return $query;
        }
        public function get_results($query) {
            // This is the tricky part - we need a real DB connection.
            // Let\\\\'s try to connect to the LocalWP database directly.
            $mysqli = new mysqli("localhost", "root", "root", "local");
            if ($mysqli->connect_errno) {
                echo "Failed to connect to MySQL: " . $mysqli->connect_error;
                return [];
            }

            $result = $mysqli->query($query);
            $data = [];
            if ($result) {
                while ($row = $result->fetch_object()) {
                    $data[] = $row;
                }
                $result->free();
            }
            $mysqli->close();
            return $data;
        }
    }
    $wpdb = new wpdb();
}

function esc_html($text) {
    return htmlspecialchars($text);
}

// Yacht ID for \'Lemon\'
$yacht_id = 6362109340000107850;

echo "Fetching equipment for Yacht ID: " . $yacht_id . " (Lemon)\n";
echo "==============================================================\n\n";

// Get equipment data
global $wpdb;
$equipment_table = $wpdb->prefix . "yacht_equipment";
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
