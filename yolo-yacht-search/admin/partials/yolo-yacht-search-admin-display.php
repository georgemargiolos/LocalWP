<?php
/**
 * Admin Settings Page
 */

// Get sync status
$sync = new YOLO_YS_Sync();
$sync_status = $sync->get_sync_status();

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Yacht Sync Section -->
    <div class="yolo-ys-sync-section" style="background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #dc2626; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #dc2626;">⚓ Yacht Database Sync</h2>
        
        <div class="yolo-ys-sync-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
                <div style="font-size: 32px; font-weight: 700; color: #1e3a8a;"><?php echo $sync_status['total_yachts']; ?></div>
                <div style="color: #6b7280; font-size: 14px;">Total Yachts</div>
            </div>
            <div style="background: #fee2e2; padding: 15px; border-radius: 8px;">
                <div style="font-size: 32px; font-weight: 700; color: #dc2626;"><?php echo $sync_status['yolo_yachts']; ?></div>
                <div style="color: #6b7280; font-size: 14px;">YOLO Yachts</div>
            </div>
            <div style="background: #dbeafe; padding: 15px; border-radius: 8px;">
                <div style="font-size: 32px; font-weight: 700; color: #1e3a8a;"><?php echo $sync_status['partner_yachts']; ?></div>
                <div style="color: #6b7280; font-size: 14px;">Partner Yachts</div>
            </div>
            <div style="background: #f3f4f6; padding: 15px; border-radius: 8px;">
                <div style="font-size: 16px; font-weight: 600; color: #374151;"><?php echo $sync_status['last_sync_human']; ?></div>
                <div style="color: #6b7280; font-size: 14px;">Last Yacht Sync</div>
            </div>
        </div>
        
        <button type="button" id="yolo-ys-sync-button" class="button button-primary button-hero" style="background: #dc2626; border-color: #dc2626; text-shadow: none; box-shadow: none;">
            <span class="dashicons dashicons-update" style="margin-top: 8px;"></span>
            Sync Yachts Now
        </button>
        
        <div id="yolo-ys-sync-message" style="margin-top: 15px;"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 13px;">
            <strong>What happens when you sync yachts:</strong><br>
            • Fetches all yachts from YOLO (7850) and partner companies (4366, 3604, 6711)<br>
            • Stores complete yacht data in WordPress database<br>
            • Updates images, specifications, equipment, and extras<br>
            • Makes search faster by querying local database instead of API<br>
            • <strong>Note:</strong> This does NOT sync prices. Use the separate "Sync Prices" button below.
        </p>
    </div>
    
    <!-- Offers Sync Section -->
    <div class="yolo-ys-price-sync-section" style="background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #2563eb;">💰 Weekly Offers Sync</h2>
        
        <div style="background: #dbeafe; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div style="font-size: 16px; font-weight: 600; color: #374151;"><?php echo $sync_status['last_price_sync_human']; ?></div>
            <div style="color: #6b7280; font-size: 14px;">Last Offers Sync</div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label for="yolo-ys-sync-year" style="font-weight: 600; margin-right: 10px;">Select Year:</label>
            <select id="yolo-ys-sync-year" style="padding: 8px 12px; font-size: 16px; border: 2px solid #2563eb; border-radius: 6px;">
                <?php 
                $current_year = date('Y');
                for ($y = $current_year; $y <= $current_year + 3; $y++) {
                    $selected = ($y == $current_year + 1) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
        </div>
        
        <button type="button" id="yolo-ys-sync-prices-button" class="button button-primary button-hero" style="background: #2563eb; border-color: #2563eb; text-shadow: none; box-shadow: none;">
            <span class="dashicons dashicons-tag" style="margin-top: 8px;"></span>
            Sync Weekly Offers
        </button>
        
        <div id="yolo-ys-price-sync-message" style="margin-top: 15px;"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 13px;">
            <strong>What happens when you sync offers:</strong><br>
            • Fetches ALL weekly charter offers (Saturday-to-Saturday) for the entire selected year<br>
            • Single API call retrieves offers from all companies (YOLO + partners)<br>
            • Stores weekly availability with prices, discounts, and start/end bases<br>
            • Updates the price carousel on yacht details pages<br>
            • <strong>Recommended:</strong> Sync for next year (<?php echo date('Y') + 1; ?>) before booking season starts
        </p>
    </div>
    
    <!-- Shortcodes Info -->
    <div class="yolo-ys-shortcodes-info" style="background: #dbeafe; padding: 20px; margin: 20px 0; border-left: 4px solid #1e3a8a; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #1e3a8a;">📋 Available Shortcodes</h3>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: white;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_search_widget]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Search form with boat type and date picker</td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_search_results]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Search results display (YOLO boats first)</td>
            </tr>
            <tr style="background: white;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_our_fleet]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Display all yachts in beautiful cards (YOLO first, then partners)</td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_yacht_details]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;"><strong>NEW!</strong> Yacht details page with image carousel and complete info</td>
            </tr>
        </table>
    </div>
    
    <!-- Settings Form -->
    <form method="post" action="options.php">
        <?php
        settings_fields('yolo-yacht-search');
        do_settings_sections('yolo-yacht-search');
        submit_button();
        ?>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Yacht Sync Handler
    $('#yolo-ys-sync-button').on('click', function() {
        var $button = $(this);
        var $message = $('#yolo-ys-sync-message');
        
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('dashicons-update-spin');
        $message.html('<div class="notice notice-info"><p>⏳ Syncing yachts... This may take a minute.</p></div>');
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_sync_yachts',
                nonce: yoloYsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $message.html(
                        '<div class="notice notice-success"><p>' +
                        '<strong>✅ Success!</strong> ' + response.data.message +
                        '<br>Companies synced: ' + response.data.companies_synced +
                        '<br>Yachts synced: ' + response.data.yachts_synced +
                        '</p></div>'
                    );
                    
                    // Reload page after 5 seconds to show updated stats
                    setTimeout(function() {
                        location.reload();
                    }, 5000);
                } else {
                    $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> Failed to sync yachts. Please try again.</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('dashicons-update-spin');
            }
        });
    });
    
    // Offers Sync Handler
    $('#yolo-ys-sync-prices-button').on('click', function() {
        var $button = $(this);
        var $message = $('#yolo-ys-price-sync-message');
        var year = $('#yolo-ys-sync-year').val();
        
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('dashicons-update-spin');
        $message.html('<div class="notice notice-info"><p>⏳ Syncing weekly offers for ' + year + '... This may take 1-2 minutes.</p></div>');
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_sync_prices',
                nonce: yoloYsAdmin.nonce,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    var message = '<div class="notice notice-success"><p>' +
                        '<strong>✅ Success!</strong> ' + response.data.message;
                    
                    if (response.data.offers_synced > 0) {
                        message += '<br>Weekly offers synced: ' + response.data.offers_synced;
                        message += '<br>Yachts with offers: ' + response.data.yachts_with_offers;
                        message += '<br>Year: ' + response.data.year;
                    }
                    
                    message += '</p></div>';
                    $message.html(message);
                    
                    // Reload page after 5 seconds to show updated stats
                    setTimeout(function() {
                        location.reload();
                    }, 5000);
                } else {
                    $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> Failed to sync offers. Please try again.</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('dashicons-update-spin');
            }
        });
    });
});
</script>

<style>
.dashicons-update-spin {
    animation: rotation 1s infinite linear;
}

@keyframes rotation {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(359deg);
    }
}
</style>
