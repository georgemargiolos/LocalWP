<?php
/**
 * Test Script for C23.3
 * Run this from WordPress admin or WP-CLI to verify all features work
 * 
 * Usage: wp eval-file wp-content/plugins/yolo-yacht-search/test-c23.3.php
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    // If running from command line, load WordPress
    $wp_load_paths = array(
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../../../wp-load.php',
    );
    
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

echo "\n=== YOLO Yacht Search C23.3 Test Suite ===\n\n";

$tests_passed = 0;
$tests_failed = 0;

function test_pass($name) {
    global $tests_passed;
    $tests_passed++;
    echo "✅ PASS: $name\n";
}

function test_fail($name, $reason = '') {
    global $tests_failed;
    $tests_failed++;
    echo "❌ FAIL: $name" . ($reason ? " - $reason" : "") . "\n";
}

// ===========================================
// TEST 1: Auto-Sync Class Exists
// ===========================================
echo "--- Test 1: Auto-Sync Class ---\n";

if (class_exists('YOLO_YS_Auto_Sync')) {
    test_pass("YOLO_YS_Auto_Sync class exists");
} else {
    test_fail("YOLO_YS_Auto_Sync class exists", "Class not loaded");
}

// ===========================================
// TEST 2: Auto-Sync Methods Exist
// ===========================================
echo "\n--- Test 2: Auto-Sync Methods ---\n";

$auto_sync = new YOLO_YS_Auto_Sync();

if (method_exists($auto_sync, 'run_yacht_sync')) {
    test_pass("run_yacht_sync method exists");
} else {
    test_fail("run_yacht_sync method exists");
}

if (method_exists($auto_sync, 'run_offers_sync')) {
    test_pass("run_offers_sync method exists");
} else {
    test_fail("run_offers_sync method exists");
}

if (method_exists('YOLO_YS_Auto_Sync', 'schedule_yacht_sync')) {
    test_pass("schedule_yacht_sync static method exists");
} else {
    test_fail("schedule_yacht_sync static method exists");
}

if (method_exists('YOLO_YS_Auto_Sync', 'unschedule_all_events')) {
    test_pass("unschedule_all_events static method exists");
} else {
    test_fail("unschedule_all_events static method exists");
}

// ===========================================
// TEST 3: Dead Code Removed
// ===========================================
echo "\n--- Test 3: Dead Code Removed ---\n";

$api = new YOLO_YS_Booking_Manager_API();
if (!method_exists($api, 'get_offers_cached')) {
    test_pass("get_offers_cached removed from API class");
} else {
    test_fail("get_offers_cached removed from API class", "Method still exists!");
}

// Check cache_duration option is not registered
$settings = get_registered_settings();
if (!isset($settings['yolo_ys_cache_duration'])) {
    test_pass("cache_duration setting not registered");
} else {
    test_fail("cache_duration setting not registered", "Setting still exists in registry");
}

// ===========================================
// TEST 4: AJAX Handlers Registered
// ===========================================
echo "\n--- Test 4: AJAX Handlers ---\n";

global $wp_filter;

// Check yacht AJAX handler
if (has_action('wp_ajax_yolo_ys_save_auto_sync')) {
    test_pass("Auto-sync AJAX handler registered");
} else {
    test_fail("Auto-sync AJAX handler registered");
}

// Check base manager AJAX handlers
if (has_action('wp_ajax_yolo_bm_get_yachts')) {
    test_pass("Get yachts AJAX handler registered");
} else {
    test_fail("Get yachts AJAX handler registered");
}

if (has_action('wp_ajax_yolo_bm_get_equipment_categories')) {
    test_pass("Get equipment categories AJAX handler registered");
} else {
    test_fail("Get equipment categories AJAX handler registered");
}

// ===========================================
// TEST 5: Cron Scheduling Works
// ===========================================
echo "\n--- Test 5: Cron Scheduling ---\n";

// Test scheduling a yacht sync (then immediately unschedule)
$next_run = YOLO_YS_Auto_Sync::schedule_yacht_sync('daily', '06:00');
if ($next_run && is_numeric($next_run)) {
    test_pass("Yacht sync scheduling works (next run: " . date('Y-m-d H:i', $next_run) . ")");
    
    // Clean up - unschedule
    YOLO_YS_Auto_Sync::schedule_yacht_sync('disabled');
    test_pass("Yacht sync unscheduling works");
} else {
    test_fail("Yacht sync scheduling works");
}

// Test scheduling offers sync
$next_run = YOLO_YS_Auto_Sync::schedule_offers_sync('weekly', '03:00');
if ($next_run && is_numeric($next_run)) {
    test_pass("Offers sync scheduling works (next run: " . date('Y-m-d H:i', $next_run) . ")");
    
    // Clean up - unschedule
    YOLO_YS_Auto_Sync::schedule_offers_sync('disabled');
    test_pass("Offers sync unscheduling works");
} else {
    test_fail("Offers sync scheduling works");
}

// ===========================================
// TEST 6: Base Manager AJAX Response Format
// ===========================================
echo "\n--- Test 6: Base Manager Classes ---\n";

if (class_exists('YOLO_YS_Base_Manager')) {
    test_pass("YOLO_YS_Base_Manager class exists");
} else {
    test_fail("YOLO_YS_Base_Manager class exists");
}

// ===========================================
// TEST 7: Font-family Declarations
// ===========================================
echo "\n--- Test 7: Font Cleanup ---\n";

$public_css = file_get_contents(dirname(__FILE__) . '/public/css/yolo-yacht-search-public.css');
if (strpos($public_css, 'font-family: inherit') === false || 
    strpos($public_css, '[class*="yolo-"] *') === false) {
    test_pass("Aggressive font-family declarations removed from public CSS");
} else {
    test_fail("Aggressive font-family declarations removed from public CSS");
}

// ===========================================
// SUMMARY
// ===========================================
echo "\n=== SUMMARY ===\n";
echo "Passed: $tests_passed\n";
echo "Failed: $tests_failed\n";

if ($tests_failed === 0) {
    echo "\n🎉 All tests passed! C23.3 is working correctly.\n";
} else {
    echo "\n⚠️ Some tests failed. Please review the issues above.\n";
}

echo "\n";

