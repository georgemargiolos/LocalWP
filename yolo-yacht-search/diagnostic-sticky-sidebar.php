<?php
/**
 * DIAGNOSTIC TOOL: Sticky Sidebar CSS Checker
 * 
 * Upload this file to your WordPress root directory and access it via:
 * https://your-site.com/diagnostic-sticky-sidebar.php
 * 
 * This will show you:
 * 1. If the sticky CSS is being loaded
 * 2. What CSS properties are applied to the sidebar
 * 3. Any potential conflicts
 */

// Load WordPress
require_once('wp-load.php');

// Get plugin version
$plugin_version = defined('YOLO_YS_VERSION') ? YOLO_YS_VERSION : 'Unknown';

// Check if styles file exists
$styles_file = WP_PLUGIN_DIR . '/yolo-yacht-search/public/templates/partials/yacht-details-v3-styles.php';
$styles_exists = file_exists($styles_file);

// Get file modification time
$styles_modified = $styles_exists ? date('Y-m-d H:i:s', filemtime($styles_file)) : 'N/A';

// Read the styles file and check for sticky CSS
$has_sticky_css = false;
$sticky_css_line = 'Not found';
if ($styles_exists) {
    $styles_content = file_get_contents($styles_file);
    if (strpos($styles_content, 'position: sticky') !== false) {
        $has_sticky_css = true;
        // Find the line number
        $lines = explode("\n", $styles_content);
        foreach ($lines as $num => $line) {
            if (strpos($line, 'position: sticky') !== false) {
                $sticky_css_line = 'Line ' . ($num + 1);
                break;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sticky Sidebar Diagnostic</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .diagnostic-box {
            background: white;
            border-radius: 8px;
            padding: 24px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #1e3a8a;
            margin-top: 0;
        }
        h2 {
            color: #333;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 8px;
        }
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 14px;
        }
        .status.ok {
            background: #d1fae5;
            color: #065f46;
        }
        .status.error {
            background: #fee2e2;
            color: #991b1b;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-label {
            font-weight: 600;
            color: #4b5563;
        }
        .info-value {
            color: #1f2937;
        }
        code {
            background: #f3f4f6;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .test-section {
            background: #f9fafb;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-top: 20px;
        }
        .sticky-demo {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }
        .demo-content {
            flex: 1;
            background: #dbeafe;
            padding: 20px;
            border-radius: 6px;
            height: 800px;
        }
        .demo-sidebar {
            width: 300px;
            background: #fef3c7;
            padding: 20px;
            border-radius: 6px;
            position: sticky;
            top: 20px;
            height: fit-content;
        }
    </style>
</head>
<body>
    <div class="diagnostic-box">
        <h1>🔍 Sticky Sidebar Diagnostic Tool</h1>
        <p>This tool checks if the sticky sidebar CSS is properly loaded in your YOLO Yacht Search plugin.</p>
    </div>

    <div class="diagnostic-box">
        <h2>Plugin Information</h2>
        <div class="info-row">
            <span class="info-label">Plugin Version:</span>
            <span class="info-value"><code><?php echo esc_html($plugin_version); ?></code></span>
        </div>
        <div class="info-row">
            <span class="info-label">Expected Version:</span>
            <span class="info-value"><code>2.7.13</code> or higher</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="status <?php echo version_compare($plugin_version, '2.7.13', '>=') ? 'ok' : 'error'; ?>">
                <?php echo version_compare($plugin_version, '2.7.13', '>=') ? '✓ OK' : '✗ OUTDATED'; ?>
            </span>
        </div>
    </div>

    <div class="diagnostic-box">
        <h2>Styles File Check</h2>
        <div class="info-row">
            <span class="info-label">File Path:</span>
            <span class="info-value"><code>yacht-details-v3-styles.php</code></span>
        </div>
        <div class="info-row">
            <span class="info-label">File Exists:</span>
            <span class="status <?php echo $styles_exists ? 'ok' : 'error'; ?>">
                <?php echo $styles_exists ? '✓ YES' : '✗ NO'; ?>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Last Modified:</span>
            <span class="info-value"><code><?php echo esc_html($styles_modified); ?></code></span>
        </div>
        <div class="info-row">
            <span class="info-label">Contains Sticky CSS:</span>
            <span class="status <?php echo $has_sticky_css ? 'ok' : 'error'; ?>">
                <?php echo $has_sticky_css ? '✓ YES' : '✗ NO'; ?>
            </span>
        </div>
        <?php if ($has_sticky_css): ?>
        <div class="info-row">
            <span class="info-label">Sticky CSS Location:</span>
            <span class="info-value"><code><?php echo esc_html($sticky_css_line); ?></code></span>
        </div>
        <?php endif; ?>
    </div>

    <div class="diagnostic-box">
        <h2>Browser Requirements</h2>
        <div class="info-row">
            <span class="info-label">Current Screen Width:</span>
            <span class="info-value"><code id="screen-width">Checking...</code></span>
        </div>
        <div class="info-row">
            <span class="info-label">Minimum Required:</span>
            <span class="info-value"><code>1024px</code></span>
        </div>
        <div class="info-row">
            <span class="info-label">Sticky Supported:</span>
            <span class="info-value"><code id="sticky-support">Checking...</code></span>
        </div>
    </div>

    <div class="diagnostic-box">
        <h2>Live Sticky Test</h2>
        <p>Scroll down to see if the yellow sidebar "sticks" to the top while the blue content scrolls.</p>
        
        <div class="sticky-demo">
            <div class="demo-content">
                <h3>Main Content (Scrollable)</h3>
                <p>This is the main content area. Keep scrolling down...</p>
                <?php for ($i = 1; $i <= 20; $i++): ?>
                    <p>Content paragraph <?php echo $i; ?>. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                <?php endfor; ?>
            </div>
            <div class="demo-sidebar">
                <h3>Sticky Sidebar</h3>
                <p><strong>This should stick!</strong></p>
                <p>If this yellow box follows you while scrolling, then <code>position: sticky</code> works in your browser.</p>
                <p>If it scrolls away, there's a browser compatibility issue.</p>
            </div>
        </div>
    </div>

    <div class="diagnostic-box">
        <h2>Recommendations</h2>
        
        <?php if (!$styles_exists): ?>
        <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 16px;">
            <strong>❌ Styles file not found!</strong>
            <p>The yacht-details-v3-styles.php file is missing. Please re-upload the plugin.</p>
        </div>
        <?php endif; ?>

        <?php if (!$has_sticky_css): ?>
        <div style="background: #fee2e2; border-left: 4px solid #dc2626; padding: 16px; margin-bottom: 16px;">
            <strong>❌ Sticky CSS not found!</strong>
            <p>The styles file exists but doesn't contain <code>position: sticky</code>. You may have an old version.</p>
        </div>
        <?php endif; ?>

        <?php if ($styles_exists && $has_sticky_css): ?>
        <div style="background: #d1fae5; border-left: 4px solid #059669; padding: 16px; margin-bottom: 16px;">
            <strong>✓ Sticky CSS is present!</strong>
            <p>The CSS code is in the file. If it's still not working on your yacht details page:</p>
            <ol>
                <li>Clear your browser cache (Ctrl+Shift+R or Cmd+Shift+R)</li>
                <li>Make sure your screen is at least 1024px wide</li>
                <li>Check browser DevTools (F12) for CSS conflicts</li>
                <li>Look for parent elements with <code>overflow: hidden</code></li>
            </ol>
        </div>
        <?php endif; ?>

        <div style="background: #dbeafe; border-left: 4px solid #1e3a8a; padding: 16px;">
            <strong>💡 Next Steps:</strong>
            <ol>
                <li>Check the "Live Sticky Test" above - does the yellow sidebar stick?</li>
                <li>If YES: The issue is specific to the yacht details page</li>
                <li>If NO: Your browser doesn't support <code>position: sticky</code></li>
            </ol>
        </div>
    </div>

    <script>
        // Check screen width
        function updateScreenWidth() {
            const width = window.innerWidth;
            document.getElementById('screen-width').textContent = width + 'px';
            document.getElementById('screen-width').style.color = width >= 1024 ? '#059669' : '#dc2626';
            document.getElementById('screen-width').style.fontWeight = 'bold';
        }
        
        // Check sticky support
        function checkStickySupport() {
            const supported = CSS.supports('position', 'sticky') || CSS.supports('position', '-webkit-sticky');
            const el = document.getElementById('sticky-support');
            el.textContent = supported ? '✓ YES' : '✗ NO';
            el.style.color = supported ? '#059669' : '#dc2626';
            el.style.fontWeight = 'bold';
        }
        
        updateScreenWidth();
        checkStickySupport();
        window.addEventListener('resize', updateScreenWidth);
    </script>
</body>
</html>
