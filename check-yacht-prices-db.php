<?php
/**
 * Check what prices exist in the database for yacht 6362109340000107850 (Lemon)
 */

// Load WordPress
require_once('/var/www/html/wp-load.php');

global $wpdb;
$yacht_id = '6362109340000107850'; // Lemon
$table_name = $wpdb->prefix . 'yolo_yacht_prices';

echo "=== Checking Prices in Database ===\n\n";
echo "Yacht ID: $yacht_id\n";
echo "Table: $table_name\n\n";

// Get all prices for this yacht
$prices = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE yacht_id = %s ORDER BY date_from ASC",
    $yacht_id
));

if (empty($prices)) {
    echo "❌ NO PRICES FOUND in database for this yacht!\n\n";
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'");
    if (!$table_exists) {
        echo "⚠️  Table does not exist!\n";
    } else {
        echo "✅ Table exists\n";
        
        // Check total records in table
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        echo "Total price records in table: $total\n\n";
        
        if ($total > 0) {
            // Show some sample records
            echo "Sample records:\n";
            $samples = $wpdb->get_results("SELECT yacht_id, date_from, date_to, price, currency FROM $table_name LIMIT 5");
            foreach ($samples as $sample) {
                echo "  - Yacht: {$sample->yacht_id}, {$sample->date_from} to {$sample->date_to}, {$sample->price} {$sample->currency}\n";
            }
        }
    }
} else {
    echo "✅ FOUND " . count($prices) . " price records:\n\n";
    
    foreach ($prices as $index => $price) {
        $from = date('M j, Y', strtotime($price->date_from));
        $to = date('M j, Y', strtotime($price->date_to));
        $days = (strtotime($price->date_to) - strtotime($price->date_from)) / (60 * 60 * 24);
        
        echo ($index + 1) . ". {$from} → {$to} ({$days} days)\n";
        echo "   Product: {$price->product}\n";
        echo "   Price: " . number_format($price->price, 2) . " {$price->currency}\n";
        
        if ($price->discount_percentage > 0) {
            echo "   Discount: {$price->discount_percentage}% (was " . number_format($price->start_price, 2) . ")\n";
        }
        
        echo "   Last synced: {$price->last_synced}\n\n";
    }
}

// Also check what the template would get
echo "\n=== What Template Gets (with peak season filter) ===\n\n";

$all_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
$filtered_prices = array();

if (!empty($all_prices)) {
    foreach ($all_prices as $price) {
        $month = (int)date('n', strtotime($price->date_from)); // 1-12
        // Only include May (5), June (6), July (7), August (8), September (9)
        if ($month >= 5 && $month <= 9) {
            $filtered_prices[] = $price;
        }
    }
}

echo "Total prices from get_yacht_prices(): " . count($all_prices) . "\n";
echo "After peak season filter (May-Sep): " . count($filtered_prices) . "\n\n";

if (!empty($filtered_prices)) {
    foreach ($filtered_prices as $index => $price) {
        $from = date('M j, Y', strtotime($price->date_from));
        $to = date('M j, Y', strtotime($price->date_to));
        echo ($index + 1) . ". {$from} → {$to}\n";
    }
}
