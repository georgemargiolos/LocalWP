<?php
/**
 * Diagnostic Script: Check Yacht Prices in Database
 * 
 * This script checks what prices are stored in the database for a specific yacht
 * to help debug the price carousel issue.
 * 
 * Usage: Place this file in WordPress root and access via browser:
 * http://yolo-local.local/check-prices-debug.php
 */

// Load WordPress
require_once('wp-load.php');

// Yacht ID from the screenshot
$yacht_id = '6362109340000107850';

global $wpdb;
$prices_table = $wpdb->prefix . 'yolo_yacht_prices';

echo "<h1>Price Carousel Debug - Yacht ID: {$yacht_id}</h1>";

// Get all prices for this yacht
$prices = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$prices_table} WHERE yacht_id = %s ORDER BY date_from ASC",
    $yacht_id
));

echo "<h2>Total Prices Found: " . count($prices) . "</h2>";

if (empty($prices)) {
    echo "<p style='color: red; font-weight: bold;'>❌ NO PRICES FOUND IN DATABASE!</p>";
    echo "<p>This means the weekly offers sync hasn't run yet, or this yacht has no offers.</p>";
    
    // Check if ANY prices exist in the table
    $total_prices = $wpdb->get_var("SELECT COUNT(*) FROM {$prices_table}");
    echo "<p>Total prices in database (all yachts): {$total_prices}</p>";
    
    // Check if this yacht exists in yachts table
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    $yacht = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$yachts_table} WHERE id = %s",
        $yacht_id
    ));
    
    if ($yacht) {
        echo "<p style='color: green;'>✅ Yacht exists in database: {$yacht->name}</p>";
    } else {
        echo "<p style='color: red;'>❌ Yacht NOT found in yachts table!</p>";
    }
    
    exit;
}

// Display prices in a table
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #333; color: white;'>";
echo "<th>Date From</th>";
echo "<th>Date To</th>";
echo "<th>Price</th>";
echo "<th>Start Price</th>";
echo "<th>Discount %</th>";
echo "<th>Currency</th>";
echo "<th>Product</th>";
echo "<th>Last Synced</th>";
echo "</tr>";

foreach ($prices as $price) {
    $is_future = strtotime($price->date_from) >= time();
    $row_color = $is_future ? '#e8f5e9' : '#ffebee';
    
    echo "<tr style='background: {$row_color};'>";
    echo "<td>" . date('M j, Y', strtotime($price->date_from)) . "</td>";
    echo "<td>" . date('M j, Y', strtotime($price->date_to)) . "</td>";
    echo "<td style='font-weight: bold; color: #1976d2;'>" . number_format($price->price, 2) . "</td>";
    echo "<td>" . number_format($price->start_price, 2) . "</td>";
    echo "<td>" . number_format($price->discount_percentage, 2) . "%</td>";
    echo "<td>{$price->currency}</td>";
    echo "<td>{$price->product}</td>";
    echo "<td>" . date('Y-m-d H:i:s', strtotime($price->last_synced)) . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h2>Expected Prices (from screenshot)</h2>";
echo "<p>According to your screenshot, the carousel should show:</p>";
echo "<ul>";
echo "<li>Apr 18 - Apr 25, 2026: €925.00</li>";
echo "<li>Apr 25 - May 2, 2026: €925.00</li>";
echo "<li>May 2 - May 9, 2026: €925.00</li>";
echo "<li>May 9 - May 16, 2026: €925.00</li>";
echo "<li>May 16 - May 23, 2026: €870.00</li>";
echo "</ul>";

echo "<hr>";
echo "<h2>What to check:</h2>";
echo "<ol>";
echo "<li>Do the prices in the table above match the expected prices?</li>";
echo "<li>If NO: The sync is pulling wrong data from the API</li>";
echo "<li>If YES: The template is displaying wrong data (check yacht-details-v3.php)</li>";
echo "<li>Check 'Last Synced' dates - are they recent?</li>";
echo "</ol>";

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<p><strong>If prices are WRONG in database:</strong></p>";
echo "<ol>";
echo "<li>Go to WordPress Admin → YOLO Yacht Search</li>";
echo "<li>Click 'Sync Prices' button</li>";
echo "<li>Wait for sync to complete</li>";
echo "<li>Refresh this page to see updated prices</li>";
echo "</ol>";

echo "<p><strong>If prices are CORRECT in database but wrong on page:</strong></p>";
echo "<ol>";
echo "<li>Clear WordPress cache (if using caching plugin)</li>";
echo "<li>Clear browser cache (Ctrl+Shift+Delete)</li>";
echo "<li>Check yacht-details-v3.php template for display bugs</li>";
echo "</ol>";
?>
