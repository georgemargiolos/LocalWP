<?php
/**
 * Standalone script to check yacht prices in Local WP database
 * 
 * INSTRUCTIONS FOR USER:
 * 1. Open Local WP app
 * 2. Right-click on your site → Open Site Shell
 * 3. Navigate to your plugin directory: cd wp-content/plugins/yolo-yacht-search
 * 4. Run: php /path/to/this/script.php
 * 
 * OR
 * 
 * 1. Copy this script to your Local WP site root
 * 2. Visit: http://yolo-local.local/check-prices-standalone.php in browser
 */

// Try to find wp-config.php
$wp_config_paths = [
    __DIR__ . '/wp-config.php',
    __DIR__ . '/../wp-config.php',
    __DIR__ . '/../../wp-config.php',
    dirname(__FILE__) . '/wp-config.php',
];

$wp_config_found = false;
foreach ($wp_config_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_config_found = true;
        break;
    }
}

if (!$wp_config_found) {
    die("❌ Could not find wp-config.php\n\nPlease run this script from your WordPress root directory.\n");
}

// Connect to database
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_error) {
    die("❌ Database connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to database: " . DB_NAME . "\n\n";

// Get table prefix from wp-config
global $table_prefix;
if (!isset($table_prefix)) {
    $table_prefix = 'wp_';
}

$table_name = $table_prefix . 'yolo_yacht_prices';
$yacht_id = '6362109340000107850'; // Lemon

echo "=== Checking Prices in Database ===\n\n";
echo "Table: $table_name\n";
echo "Yacht ID: $yacht_id\n\n";

// Check if table exists
$result = $mysqli->query("SHOW TABLES LIKE '$table_name'");
if ($result->num_rows == 0) {
    die("❌ Table '$table_name' does not exist!\n");
}

echo "✅ Table exists\n\n";

// Get total records
$result = $mysqli->query("SELECT COUNT(*) as total FROM $table_name");
$row = $result->fetch_assoc();
echo "Total price records in table: " . $row['total'] . "\n\n";

// Get prices for this yacht
$stmt = $mysqli->prepare("SELECT * FROM $table_name WHERE yacht_id = ? ORDER BY date_from ASC");
$stmt->bind_param("s", $yacht_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "❌ NO PRICES FOUND for yacht $yacht_id\n\n";
    
    // Show sample records from other yachts
    echo "Sample records from other yachts:\n";
    $sample_result = $mysqli->query("SELECT yacht_id, date_from, date_to, price, currency FROM $table_name LIMIT 5");
    while ($row = $sample_result->fetch_assoc()) {
        echo "  - Yacht: {$row['yacht_id']}, {$row['date_from']} to {$row['date_to']}, {$row['price']} {$row['currency']}\n";
    }
} else {
    echo "✅ FOUND " . $result->num_rows . " price records for this yacht:\n\n";
    
    $index = 1;
    while ($price = $result->fetch_assoc()) {
        $from = date('M j, Y', strtotime($price['date_from']));
        $to = date('M j, Y', strtotime($price['date_to']));
        $days = (strtotime($price['date_to']) - strtotime($price['date_from'])) / (60 * 60 * 24);
        
        echo "$index. $from → $to (" . round($days) . " days)\n";
        echo "   Product: {$price['product']}\n";
        echo "   Price: " . number_format($price['price'], 2) . " {$price['currency']}\n";
        
        if ($price['discount_percentage'] > 0) {
            echo "   Discount: {$price['discount_percentage']}% (was " . number_format($price['start_price'], 2) . ")\n";
        }
        
        echo "   Last synced: {$price['last_synced']}\n\n";
        $index++;
    }
    
    // Check peak season filter
    echo "\n=== Peak Season Filter (May-September) ===\n\n";
    $stmt2 = $mysqli->prepare("SELECT * FROM $table_name WHERE yacht_id = ? AND MONTH(date_from) BETWEEN 5 AND 9 ORDER BY date_from ASC");
    $stmt2->bind_param("s", $yacht_id);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    echo "Prices in peak season (May-Sep): " . $result2->num_rows . "\n\n";
    
    if ($result2->num_rows > 0) {
        $index = 1;
        while ($price = $result2->fetch_assoc()) {
            $from = date('M j, Y', strtotime($price['date_from']));
            $to = date('M j, Y', strtotime($price['date_to']));
            echo "$index. $from → $to\n";
            $index++;
        }
    }
}

$mysqli->close();

echo "\n=== DONE ===\n";
