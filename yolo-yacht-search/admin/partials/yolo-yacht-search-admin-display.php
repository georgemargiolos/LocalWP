<?php
/**
 * Admin Settings Page
 * v81.0 - Progressive sync with live dashboard
 */

// Get sync status
$sync = new YOLO_YS_Sync();
$sync_status = $sync->get_sync_status();

?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <!-- Equipment Catalog Sync Section -->
    <div class="yolo-ys-equipment-sync-section" style="background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #16a34a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #16a34a;">🔧 Equipment Catalog Sync</h2>
        
        <div style="background: #dcfce7; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <div style="font-size: 16px; font-weight: 600; color: #374151;">Equipment catalog contains names for all equipment IDs</div>
            <div style="color: #6b7280; font-size: 14px; margin-top: 5px;">⚠️ <strong>IMPORTANT:</strong> Sync this BEFORE syncing yachts!</div>
        </div>
        
        <button type="button" id="yolo-ys-sync-equipment-button" class="button button-primary button-hero" style="background: #16a34a; border-color: #16a34a; text-shadow: none; box-shadow: none;">
            <span class="dashicons dashicons-admin-tools" style="margin-top: 8px;"></span>
            Sync Equipment Catalog
        </button>
        
        <div id="yolo-ys-equipment-sync-message" style="margin-top: 15px;"></div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 13px;">
            <strong>What happens when you sync equipment catalog:</strong><br>
            • Fetches all 50 equipment items from Booking Manager API<br>
            • Stores equipment IDs and names in local database<br>
            • Enables yacht sync to display equipment names correctly<br>
            • <strong>Note:</strong> This is a one-time sync, only needs to be run once or when new equipment is added
        </p>
    </div>
    
    <!-- Yacht Sync Section - Progressive -->
    <div class="yolo-ys-sync-section" style="background: white; padding: 20px; margin: 20px 0; border-left: 4px solid #dc2626; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <h2 style="margin-top: 0; color: #dc2626;">🚤 Yacht Database Sync</h2>
        
        <!-- Stats Row -->
        <div class="yolo-ys-sync-stats" style="display: flex; flex-wrap: wrap; gap: 20px; margin: 20px 0;">
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
        
        <!-- Sync Button -->
        <button type="button" id="yolo-progressive-yacht-sync-button" class="button button-primary button-hero" style="background: #dc2626; border-color: #dc2626; text-shadow: none; box-shadow: none;">
            <span class="dashicons dashicons-update" style="margin-top: 8px;"></span>
            Sync Yachts Now
        </button>
        
        <!-- Progressive Sync Dashboard (hidden initially) -->
        <div id="yolo-yacht-sync-dashboard" style="display: none; margin-top: 20px; border: 2px solid #dc2626; border-radius: 12px; overflow: hidden;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); color: white; padding: 15px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 18px; font-weight: 600;">
                        <span id="yacht-sync-status-text">SYNC IN PROGRESS</span>
                    </div>
                    <div style="font-size: 24px; font-weight: 700; font-family: monospace;">
                        ⏱️ <span id="yacht-sync-timer">00:00</span>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div style="margin-top: 15px; background: rgba(255,255,255,0.2); border-radius: 10px; height: 24px; overflow: hidden;">
                    <div id="yacht-sync-progress-bar" style="width: 0%; height: 100%; background: white; border-radius: 10px; transition: width 0.3s ease;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 14px;">
                    <span id="yacht-sync-count">0/0</span>
                    <span id="yacht-sync-percent">0%</span>
                </div>
            </div>
            
            <!-- Body -->
            <div style="padding: 20px; background: #fef2f2;">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <!-- Statistics -->
                    <div style="flex: 1; min-width: 200px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin: 0 0 15px 0; color: #374151;">📊 Statistics</h4>
                        <div style="display: grid; gap: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Companies:</span>
                                <span id="yacht-sync-companies" style="font-weight: 600;">0/0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Yachts:</span>
                                <span id="yacht-sync-yachts" style="font-weight: 600;">0/0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Images:</span>
                                <span id="yacht-sync-images" style="font-weight: 600;">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Extras:</span>
                                <span id="yacht-sync-extras" style="font-weight: 600;">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Equipment:</span>
                                <span id="yacht-sync-equipment" style="font-weight: 600;">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 5px;">
                                <span style="color: #6b7280;">Speed:</span>
                                <span id="yacht-sync-speed" style="font-weight: 600;">--</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">ETA:</span>
                                <span id="yacht-sync-eta" style="font-weight: 600;">--</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Feed -->
                    <div style="flex: 1; min-width: 250px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin: 0 0 15px 0; color: #374151;">⚡ Live Feed</h4>
                        <div id="yacht-sync-live-feed" style="height: 180px; overflow-y: auto; font-size: 13px; font-family: monospace;">
                            <!-- Live updates appear here -->
                        </div>
                    </div>
                </div>
                
                <!-- Company Breakdown -->
                <div style="margin-top: 20px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="margin: 0 0 15px 0; color: #374151;">🏢 Company Breakdown</h4>
                    <div id="yacht-sync-company-breakdown">
                        <!-- Company progress bars appear here -->
                    </div>
                </div>
                
                <!-- Cancel Button -->
                <div style="margin-top: 20px; text-align: center;">
                    <button type="button" id="yacht-sync-cancel-button" class="button" style="background: #6b7280; color: white; border: none;">
                        ⏹️ Stop Sync
                    </button>
                </div>
            </div>
        </div>
        
        <div id="yolo-ys-sync-message" style="margin-top: 15px;"></div>
        
        <!-- Auto-Sync Yachts -->
        <div style="margin-top: 20px; padding: 15px; background: #fef2f2; border-radius: 8px; border: 1px solid #fecaca;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px;">
                <label for="yolo-ys-auto-sync-yachts" style="font-weight: 600; color: #dc2626;">🔄 Auto-Sync:</label>
                <select id="yolo-ys-auto-sync-yachts" style="padding: 8px 12px; font-size: 14px; border: 2px solid #dc2626; border-radius: 6px;">
                    <option value="disabled" <?php selected(get_option('yolo_ys_auto_sync_yachts_frequency', 'disabled'), 'disabled'); ?>>Disabled</option>
                    <option value="twicedaily" <?php selected(get_option('yolo_ys_auto_sync_yachts_frequency', 'disabled'), 'twicedaily'); ?>>Twice Daily</option>
                    <option value="daily" <?php selected(get_option('yolo_ys_auto_sync_yachts_frequency', 'disabled'), 'daily'); ?>>Daily</option>
                    <option value="weekly" <?php selected(get_option('yolo_ys_auto_sync_yachts_frequency', 'disabled'), 'weekly'); ?>>Weekly</option>
                </select>
                <label style="font-weight: 600;">at</label>
                <select id="yolo-ys-auto-sync-yachts-time" style="padding: 8px 12px; font-size: 14px; border: 2px solid #dc2626; border-radius: 6px;">
                    <?php
                    $selected_time = get_option('yolo_ys_auto_sync_yachts_time', '03:00');
                    for ($i = 0; $i < 24; $i++) {
                        $time = sprintf('%02d:00', $i);
                        $display = date('g:00 A', strtotime($time));
                        echo '<option value="' . esc_attr($time) . '" ' . selected($selected_time, $time, false) . '>' . esc_html($display) . '</option>';
                    }
                    ?>
                </select>
                <span id="yolo-ys-auto-sync-yachts-status" style="color: #6b7280; font-size: 13px;">
                    <?php
                    $next_yacht_sync = wp_next_scheduled('yolo_ys_auto_sync_yachts');
                    if ($next_yacht_sync) {
                        echo 'Next: ' . date('M d, g:i A', $next_yacht_sync);
                    }
                    ?>
                </span>
            </div>
        </div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 13px;">
            <strong>What happens when you sync yachts:</strong><br>
            • Fetches all yachts from YOLO (7850) and partner companies<br>
            • Syncs one yacht at a time (no timeouts!)<br>
            • Shows live progress with statistics<br>
            • <strong>Note:</strong> This does NOT sync prices. Use the separate "Sync Prices" button below.
        </p>
    </div>
    
    <!-- Offers Sync Section - Progressive -->
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
                $saved_year = (int) get_option('yolo_ys_offers_sync_year', $current_year + 1);
                for ($y = $current_year; $y <= $current_year + 3; $y++) {
                    $selected = ($y == $saved_year) ? 'selected' : '';
                    echo "<option value='$y' $selected>$y</option>";
                }
                ?>
            </select>
            <span style="color: #6b7280; font-size: 12px; margin-left: 10px;">Also used for auto-sync</span>
        </div>
        
        <button type="button" id="yolo-progressive-price-sync-button" class="button button-primary button-hero" style="background: #2563eb; border-color: #2563eb; text-shadow: none; box-shadow: none;">
            <span class="dashicons dashicons-tag" style="margin-top: 8px;"></span>
            Sync Weekly Offers
        </button>
        
        <!-- Progressive Price Sync Dashboard (hidden initially) -->
        <div id="yolo-price-sync-dashboard" style="display: none; margin-top: 20px; border: 2px solid #2563eb; border-radius: 12px; overflow: hidden;">
            <!-- Header -->
            <div style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; padding: 15px 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="font-size: 18px; font-weight: 600;">
                        <span id="price-sync-status-text">SYNC IN PROGRESS</span>
                        <span id="price-sync-year-display" style="opacity: 0.8; margin-left: 10px;"></span>
                    </div>
                    <div style="font-size: 24px; font-weight: 700; font-family: monospace;">
                        ⏱️ <span id="price-sync-timer">00:00</span>
                    </div>
                </div>
                <!-- Progress Bar -->
                <div style="margin-top: 15px; background: rgba(255,255,255,0.2); border-radius: 10px; height: 24px; overflow: hidden;">
                    <div id="price-sync-progress-bar" style="width: 0%; height: 100%; background: white; border-radius: 10px; transition: width 0.3s ease;"></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 8px; font-size: 14px;">
                    <span id="price-sync-count">0/0 yachts</span>
                    <span id="price-sync-percent">0%</span>
                </div>
            </div>
            
            <!-- Body -->
            <div style="padding: 20px; background: #eff6ff;">
                <div style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <!-- Statistics -->
                    <div style="flex: 1; min-width: 200px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin: 0 0 15px 0; color: #374151;">📊 Statistics</h4>
                        <div style="display: grid; gap: 10px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Yachts:</span>
                                <span id="price-sync-yachts" style="font-weight: 600;">0/0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">Weekly Offers:</span>
                                <span id="price-sync-offers" style="font-weight: 600;">0</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-top: 1px solid #e5e7eb; padding-top: 10px; margin-top: 5px;">
                                <span style="color: #6b7280;">Speed:</span>
                                <span id="price-sync-speed" style="font-weight: 600;">--</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #6b7280;">ETA:</span>
                                <span id="price-sync-eta" style="font-weight: 600;">--</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Live Feed -->
                    <div style="flex: 1; min-width: 250px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                        <h4 style="margin: 0 0 15px 0; color: #374151;">⚡ Live Feed</h4>
                        <div id="price-sync-live-feed" style="height: 180px; overflow-y: auto; font-size: 13px; font-family: monospace;">
                            <!-- Live updates appear here -->
                        </div>
                    </div>
                </div>
                
                <!-- Company Breakdown -->
                <div style="margin-top: 20px; background: white; padding: 15px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <h4 style="margin: 0 0 15px 0; color: #374151;">🏢 Company Breakdown</h4>
                    <div id="price-sync-company-breakdown">
                        <!-- Company progress bars appear here -->
                    </div>
                </div>
                
                <!-- Cancel Button -->
                <div style="margin-top: 20px; text-align: center;">
                    <button type="button" id="price-sync-cancel-button" class="button" style="background: #6b7280; color: white; border: none;">
                        ⏹️ Stop Sync
                    </button>
                </div>
            </div>
        </div>
        
        <div id="yolo-ys-price-sync-message" style="margin-top: 15px;"></div>
        
        <!-- Auto-Sync Offers -->
        <div style="margin-top: 20px; padding: 15px; background: #eff6ff; border-radius: 8px; border: 1px solid #bfdbfe;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px;">
                <label for="yolo-ys-auto-sync-offers" style="font-weight: 600; color: #2563eb;">🔄 Auto-Sync:</label>
                <select id="yolo-ys-auto-sync-offers" style="padding: 8px 12px; font-size: 14px; border: 2px solid #2563eb; border-radius: 6px;">
                    <option value="disabled" <?php selected(get_option('yolo_ys_auto_sync_offers_frequency', 'disabled'), 'disabled'); ?>>Disabled</option>
                    <option value="twicedaily" <?php selected(get_option('yolo_ys_auto_sync_offers_frequency', 'disabled'), 'twicedaily'); ?>>Twice Daily</option>
                    <option value="daily" <?php selected(get_option('yolo_ys_auto_sync_offers_frequency', 'disabled'), 'daily'); ?>>Daily</option>
                    <option value="weekly" <?php selected(get_option('yolo_ys_auto_sync_offers_frequency', 'disabled'), 'weekly'); ?>>Weekly</option>
                </select>
                <label style="font-weight: 600;">at</label>
                <select id="yolo-ys-auto-sync-offers-time" style="padding: 8px 12px; font-size: 14px; border: 2px solid #2563eb; border-radius: 6px;">
                    <?php
                    $selected_time = get_option('yolo_ys_auto_sync_offers_time', '03:00');
                    for ($i = 0; $i < 24; $i++) {
                        $time = sprintf('%02d:00', $i);
                        $display = date('g:00 A', strtotime($time));
                        echo '<option value="' . esc_attr($time) . '" ' . selected($selected_time, $time, false) . '>' . esc_html($display) . '</option>';
                    }
                    ?>
                </select>
                <span id="yolo-ys-auto-sync-offers-status" style="color: #6b7280; font-size: 13px;">
                    <?php
                    $next_offers_sync = wp_next_scheduled('yolo_ys_auto_sync_offers');
                    if ($next_offers_sync) {
                        echo 'Next: ' . date('M d, g:i A', $next_offers_sync);
                    }
                    ?>
                </span>
            </div>
        </div>
        
        <p style="margin-top: 15px; color: #6b7280; font-size: 13px;">
            <strong>What happens when you sync offers:</strong><br>
            • Fetches weekly charter offers for each yacht individually (no timeouts!)<br>
            • Shows live progress with statistics<br>
            • Stores weekly availability with prices, discounts, and start/end bases<br>
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
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Yacht details page with image carousel and complete info</td>
            </tr>
            <tr style="background: white;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_booking_confirmation]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Booking confirmation page (shown after successful deposit payment)</td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_balance_payment]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Balance payment page (for paying remaining 50% before charter)</td>
            </tr>
            <tr style="background: white;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_balance_confirmation]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Balance payment confirmation page (shown after successful balance payment)</td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_guest_dashboard]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Guest dashboard with bookings and license upload</td>
            </tr>
            <tr style="background: white;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_guest_login]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Custom guest login form - redirects to dashboard after login</td>
            </tr>
            <tr>
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[base_manager]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Base manager operations dashboard with yacht management, check-in/out, and warehouse</td>
            </tr>
            <tr style="background: white;">
                <td style="padding: 12px; border: 1px solid #e5e7eb; font-family: monospace; font-weight: 600;">[yolo_contact_form]</td>
                <td style="padding: 12px; border: 1px solid #e5e7eb;">Contact form with database storage and notifications</td>
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
    
    // ==========================================
    // EQUIPMENT SYNC (unchanged)
    // ==========================================
    $('#yolo-ys-sync-equipment-button').on('click', function() {
        var $button = $(this);
        var $message = $('#yolo-ys-equipment-sync-message');
        
        $button.prop('disabled', true);
        $button.find('.dashicons').addClass('dashicons-update-spin');
        $message.html('<div class="notice notice-info"><p>⏳ Syncing equipment catalog...</p></div>');
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_sync_equipment',
                nonce: yoloYsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $message.html('<div class="notice notice-success"><p><strong>✅ Success!</strong> ' + response.data.message + '</p></div>');
                } else {
                    $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> ' + response.data.message + '</p></div>');
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> Failed to sync. Please try again.</p></div>');
            },
            complete: function() {
                $button.prop('disabled', false);
                $button.find('.dashicons').removeClass('dashicons-update-spin');
            }
        });
    });
    
    // ==========================================
    // PROGRESSIVE YACHT SYNC
    // ==========================================
    var yachtSyncState = {
        running: false,
        cancelled: false,
        startTime: null,
        timerInterval: null,
        speedSamples: [],
        phase: 1  // 1 = data sync, 2 = image sync
    };
    
    $('#yolo-progressive-yacht-sync-button').on('click', function() {
        var $button = $(this);
        var $dashboard = $('#yolo-yacht-sync-dashboard');
        var $message = $('#yolo-ys-sync-message');
        
        $button.prop('disabled', true);
        $message.html('<div class="notice notice-info"><p>⏳ Initializing yacht sync...</p></div>');
        
        // Initialize sync
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_progressive_init_yacht_sync',
                nonce: yoloYsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $message.html('');
                    $dashboard.show();
        yachtSyncState.running = true;
        yachtSyncState.cancelled = false;
        yachtSyncState.startTime = Date.now();
        yachtSyncState.speedSamples = [];
        yachtSyncState.phase = 1;  // Start with Phase 1 (data sync)
                    
                    // Start timer
                    yachtSyncState.timerInterval = setInterval(updateYachtTimer, 1000);
                    
                    // Update initial state
                    updateYachtSyncUI(response.data.state);
                    
                    // Start syncing
                    syncNextYacht();
                } else {
                    $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> ' + response.data.message + '</p></div>');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> Failed to initialize sync.</p></div>');
                $button.prop('disabled', false);
            }
        });
    });
    
    function syncNextYacht() {
        if (yachtSyncState.cancelled) return;
        
        // Determine which action to call based on current phase
        var action = yachtSyncState.phase === 2 
            ? 'yolo_progressive_sync_next_image_batch' 
            : 'yolo_progressive_sync_next_yacht';
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: action,
                nonce: yoloYsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Check if phase changed
                    if (data.phase_changed && data.phase === 2) {
                        yachtSyncState.phase = 2;
                        addToYachtLiveFeed('📦 Phase 1 complete - Starting image download...', 0);
                    }
                    
                    // Add to live feed based on phase
                    if (data.phase === 1 && data.yacht_synced) {
                        addToYachtLiveFeed('✓ ' + data.yacht_synced + ' (data)', data.elapsed_ms);
                    } else if (data.phase === 2 && data.yacht_name) {
                        addToYachtLiveFeed('🖼️ ' + data.yacht_name + ' (+' + data.images_downloaded + ' images)', data.elapsed_ms);
                    }
                    
                    // Track speed
                    if (data.elapsed_ms > 0) {
                        yachtSyncState.speedSamples.push(data.elapsed_ms);
                        if (yachtSyncState.speedSamples.length > 10) {
                            yachtSyncState.speedSamples.shift();
                        }
                    }
                    
                    // Update UI
                    updateYachtProgress(data);
                    
                    // Update phase indicator
                    if (data.phase === 1) {
                        $('#yacht-sync-status-text').text('PHASE 1: Syncing yacht data');
                    } else if (data.phase === 2) {
                        $('#yacht-sync-status-text').text('PHASE 2: Downloading images');
                    }
                    
                    if (data.done) {
                        completeYachtSync(data);
                    } else {
                        // Continue (300ms delay for data, 500ms for images)
                        var delay = data.phase === 2 ? 500 : 300;
                        setTimeout(syncNextYacht, delay);
                    }
                } else {
                    addToYachtLiveFeed('❌ Error: ' + (response.data.message || 'Unknown error'), 0);
                    // Retry after error
                    setTimeout(syncNextYacht, 2000);
                }
            },
            error: function() {
                addToYachtLiveFeed('❌ Network error - retrying...', 0);
                setTimeout(syncNextYacht, 3000);
            }
        });
    }
    
    function updateYachtTimer() {
        var elapsed = Math.floor((Date.now() - yachtSyncState.startTime) / 1000);
        var minutes = Math.floor(elapsed / 60);
        var seconds = elapsed % 60;
        $('#yacht-sync-timer').text(
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0')
        );
    }
    
    function updateYachtSyncUI(state) {
        // Update company breakdown
        var companyHtml = '';
        for (var companyId in state.companies) {
            var company = state.companies[companyId];
            var percent = company.total > 0 ? Math.round((company.synced / company.total) * 100) : 0;
            var statusIcon = company.status === 'complete' ? '✅' : (company.status === 'syncing' ? '⟳' : '○');
            
            companyHtml += '<div style="margin-bottom: 10px;">';
            companyHtml += '<div style="display: flex; justify-content: space-between; margin-bottom: 4px;">';
            companyHtml += '<span>' + statusIcon + ' Company ' + companyId + '</span>';
            companyHtml += '<span>' + company.synced + '/' + company.total + '</span>';
            companyHtml += '</div>';
            companyHtml += '<div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">';
            companyHtml += '<div style="width: ' + percent + '%; height: 100%; background: ' + (company.status === 'complete' ? '#16a34a' : '#dc2626') + '; transition: width 0.3s;"></div>';
            companyHtml += '</div>';
            companyHtml += '</div>';
        }
        $('#yacht-sync-company-breakdown').html(companyHtml);
    }
    
    function updateYachtProgress(data) {
        // Progress bar
        $('#yacht-sync-progress-bar').css('width', data.progress + '%');
        $('#yacht-sync-count').text(data.synced + '/' + data.total);
        $('#yacht-sync-percent').text(data.progress + '%');
        
        // Stats
        $('#yacht-sync-yachts').text(data.synced + '/' + data.total);
        if (data.stats) {
            $('#yacht-sync-images').text(data.stats.images);
            $('#yacht-sync-extras').text(data.stats.extras);
            $('#yacht-sync-equipment').text(data.stats.equipment);
        }
        
        // Company breakdown
        if (data.companies) {
            updateYachtSyncUI({ companies: data.companies });
            
            // Count completed companies
            var completedCompanies = 0;
            var totalCompanies = 0;
            for (var cid in data.companies) {
                totalCompanies++;
                if (data.companies[cid].status === 'complete') completedCompanies++;
            }
            $('#yacht-sync-companies').text(completedCompanies + '/' + totalCompanies);
        }
        
        // Speed and ETA
        if (yachtSyncState.speedSamples.length > 0) {
            var avgSpeed = yachtSyncState.speedSamples.reduce(function(a, b) { return a + b; }, 0) / yachtSyncState.speedSamples.length;
            $('#yacht-sync-speed').text((avgSpeed / 1000).toFixed(1) + 's/yacht');
            
            var remaining = data.total - data.synced;
            var etaSeconds = Math.round((remaining * avgSpeed) / 1000);
            if (etaSeconds > 60) {
                $('#yacht-sync-eta').text(Math.floor(etaSeconds / 60) + 'm ' + (etaSeconds % 60) + 's');
            } else {
                $('#yacht-sync-eta').text(etaSeconds + 's');
            }
        }
    }
    
    function addToYachtLiveFeed(message, elapsed) {
        var time = new Date().toLocaleTimeString();
        var elapsedText = elapsed > 0 ? ' (' + elapsed + 'ms)' : '';
        var $feed = $('#yacht-sync-live-feed');
        $feed.prepend('<div style="padding: 4px 0; border-bottom: 1px solid #f3f4f6;">' + time + ' - ' + message + elapsedText + '</div>');
        
        // Keep only last 50 entries
        while ($feed.children().length > 50) {
            $feed.children().last().remove();
        }
    }
    
    function completeYachtSync(data) {
        yachtSyncState.running = false;
        clearInterval(yachtSyncState.timerInterval);
        
        $('#yacht-sync-status-text').text('✅ SYNC COMPLETE');
        $('#yacht-sync-cancel-button').hide();
        
        var $message = $('#yolo-ys-sync-message');
        $message.html(
            '<div class="notice notice-success"><p>' +
            '<strong>✅ Success!</strong> ' + data.message +
            '<br>Duration: ' + data.duration +
            '<br>Images: ' + data.stats.images + ' | Extras: ' + data.stats.extras + ' | Equipment: ' + data.stats.equipment +
            '</p></div>'
        );
        
        $('#yolo-progressive-yacht-sync-button').prop('disabled', false);
        
        // Reload page after 5 seconds
        setTimeout(function() {
            location.reload();
        }, 5000);
    }
    
    // Cancel yacht sync
    $('#yacht-sync-cancel-button').on('click', function() {
        yachtSyncState.cancelled = true;
        yachtSyncState.running = false;
        clearInterval(yachtSyncState.timerInterval);
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_progressive_cancel_sync',
                nonce: yoloYsAdmin.nonce
            }
        });
        
        $('#yacht-sync-status-text').text('⏹️ SYNC CANCELLED');
        $('#yacht-sync-cancel-button').hide();
        $('#yolo-progressive-yacht-sync-button').prop('disabled', false);
    });
    
    // ==========================================
    // PROGRESSIVE PRICE SYNC
    // ==========================================
    var priceSyncState = {
        running: false,
        cancelled: false,
        startTime: null,
        timerInterval: null,
        speedSamples: []
    };
    
    $('#yolo-progressive-price-sync-button').on('click', function() {
        var $button = $(this);
        var $dashboard = $('#yolo-price-sync-dashboard');
        var $message = $('#yolo-ys-price-sync-message');
        var year = $('#yolo-ys-sync-year').val();
        
        $button.prop('disabled', true);
        $message.html('<div class="notice notice-info"><p>⏳ Initializing price sync for ' + year + '...</p></div>');
        
        // Initialize sync
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_progressive_init_price_sync',
                nonce: yoloYsAdmin.nonce,
                year: year
            },
            success: function(response) {
                if (response.success) {
                    $message.html('');
                    $dashboard.show();
                    priceSyncState.running = true;
                    priceSyncState.cancelled = false;
                    priceSyncState.startTime = Date.now();
                    priceSyncState.speedSamples = [];
                    
                    // Show year
                    $('#price-sync-year-display').text('(' + year + ')');
                    
                    // Start timer
                    priceSyncState.timerInterval = setInterval(updatePriceTimer, 1000);
                    
                    // Set initial progress values
                    var initialState = response.data.state;
                    $('#price-sync-progress-bar').css('width', '0%');
                    $('#price-sync-count').text('0/' + initialState.total_yachts + ' yachts');
                    $('#price-sync-percent').text('0%');
                    $('#price-sync-yachts').text('0/' + initialState.total_yachts);
                    $('#price-sync-offers').text('0');
                    
                    // Update company breakdown
                    updatePriceSyncUI(initialState);
                    
                    // Start syncing
                    syncNextPrice();
                } else {
                    $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> ' + response.data.message + '</p></div>');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                $message.html('<div class="notice notice-error"><p><strong>❌ Error:</strong> Failed to initialize sync.</p></div>');
                $button.prop('disabled', false);
            }
        });
    });
    
    function syncNextPrice() {
        if (priceSyncState.cancelled) return;
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_progressive_sync_next_price',
                nonce: yoloYsAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    
                    // Add to live feed
                    if (data.yacht_synced) {
                        var offersText = data.offers_synced ? ' - ' + data.offers_synced + ' weeks' : '';
                        addToPriceLiveFeed('✓ ' + data.yacht_synced + offersText, data.elapsed_ms);
                        
                        // Track speed
                        if (data.elapsed_ms > 0) {
                            priceSyncState.speedSamples.push(data.elapsed_ms);
                            if (priceSyncState.speedSamples.length > 10) {
                                priceSyncState.speedSamples.shift();
                            }
                        }
                    }
                    
                    // Update UI
                    updatePriceProgress(data);
                    
                    if (data.done) {
                        completePriceSync(data);
                    } else {
                        // Continue to next yacht
                        setTimeout(syncNextPrice, 100);
                    }
                } else {
                    addToPriceLiveFeed('❌ Error: ' + (response.data.message || 'Unknown error'), 0);
                }
            },
            error: function() {
                addToPriceLiveFeed('❌ Network error - retrying...', 0);
                setTimeout(syncNextPrice, 2000);
            }
        });
    }
    
    function updatePriceTimer() {
        var elapsed = Math.floor((Date.now() - priceSyncState.startTime) / 1000);
        var minutes = Math.floor(elapsed / 60);
        var seconds = elapsed % 60;
        $('#price-sync-timer').text(
            String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0')
        );
    }
    
    function updatePriceSyncUI(state) {
        // Update company breakdown
        var companyHtml = '';
        for (var companyId in state.companies) {
            var company = state.companies[companyId];
            var percent = company.total > 0 ? Math.round((company.synced / company.total) * 100) : 0;
            var statusIcon = company.status === 'complete' ? '✅' : (company.status === 'syncing' ? '⟳' : '○');
            
            companyHtml += '<div style="margin-bottom: 10px;">';
            companyHtml += '<div style="display: flex; justify-content: space-between; margin-bottom: 4px;">';
            companyHtml += '<span>' + statusIcon + ' Company ' + companyId + '</span>';
            companyHtml += '<span>' + company.synced + '/' + company.total + '</span>';
            companyHtml += '</div>';
            companyHtml += '<div style="background: #e5e7eb; border-radius: 4px; height: 8px; overflow: hidden;">';
            companyHtml += '<div style="width: ' + percent + '%; height: 100%; background: ' + (company.status === 'complete' ? '#16a34a' : '#2563eb') + '; transition: width 0.3s;"></div>';
            companyHtml += '</div>';
            companyHtml += '</div>';
        }
        $('#price-sync-company-breakdown').html(companyHtml);
    }
    
    function updatePriceProgress(data) {
        // Progress bar
        $('#price-sync-progress-bar').css('width', data.progress + '%');
        $('#price-sync-count').text(data.synced + '/' + data.total + ' yachts');
        $('#price-sync-percent').text(data.progress + '%');
        
        // Stats
        $('#price-sync-yachts').text(data.synced + '/' + data.total);
        if (data.stats) {
            $('#price-sync-offers').text(data.stats.offers);
        }
        
        // Company breakdown
        if (data.companies) {
            updatePriceSyncUI({ companies: data.companies });
        }
        
        // Speed and ETA
        if (priceSyncState.speedSamples.length > 0) {
            var avgSpeed = priceSyncState.speedSamples.reduce(function(a, b) { return a + b; }, 0) / priceSyncState.speedSamples.length;
            $('#price-sync-speed').text((avgSpeed / 1000).toFixed(1) + 's/yacht');
            
            var remaining = data.total - data.synced;
            var etaSeconds = Math.round((remaining * avgSpeed) / 1000);
            if (etaSeconds > 60) {
                $('#price-sync-eta').text(Math.floor(etaSeconds / 60) + 'm ' + (etaSeconds % 60) + 's');
            } else {
                $('#price-sync-eta').text(etaSeconds + 's');
            }
        }
    }
    
    function addToPriceLiveFeed(message, elapsed) {
        var time = new Date().toLocaleTimeString();
        var elapsedText = elapsed > 0 ? ' (' + elapsed + 'ms)' : '';
        var $feed = $('#price-sync-live-feed');
        $feed.prepend('<div style="padding: 4px 0; border-bottom: 1px solid #f3f4f6;">' + time + ' - ' + message + elapsedText + '</div>');
        
        // Keep only last 50 entries
        while ($feed.children().length > 50) {
            $feed.children().last().remove();
        }
    }
    
    function completePriceSync(data) {
        priceSyncState.running = false;
        clearInterval(priceSyncState.timerInterval);
        
        $('#price-sync-status-text').text('✅ SYNC COMPLETE');
        $('#price-sync-cancel-button').hide();
        
        var $message = $('#yolo-ys-price-sync-message');
        $message.html(
            '<div class="notice notice-success"><p>' +
            '<strong>✅ Success!</strong> ' + data.message +
            '<br>Duration: ' + data.duration +
            '<br>Year: ' + data.year +
            '</p></div>'
        );
        
        $('#yolo-progressive-price-sync-button').prop('disabled', false);
        
        // Reload page after 5 seconds
        setTimeout(function() {
            location.reload();
        }, 5000);
    }
    
    // Cancel price sync
    $('#price-sync-cancel-button').on('click', function() {
        priceSyncState.cancelled = true;
        priceSyncState.running = false;
        clearInterval(priceSyncState.timerInterval);
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_progressive_cancel_sync',
                nonce: yoloYsAdmin.nonce
            }
        });
        
        $('#price-sync-status-text').text('⏹️ SYNC CANCELLED');
        $('#price-sync-cancel-button').hide();
        $('#yolo-progressive-price-sync-button').prop('disabled', false);
    });
    
    // ==========================================
    // FRIEND COMPANIES NAME LOOKUP (v81.4)
    // ==========================================
    
    // Look up company names on page load and when field changes
    function lookupCompanyNames() {
        var $field = $('#yolo_ys_friend_companies');
        var $container = $('#yolo-friend-companies-names');
        var companyIds = $field.val();
        
        if (!companyIds || companyIds.trim() === '') {
            $container.html('');
            return;
        }
        
        $container.html('<span style="color: #6b7280;">🔍 Looking up company names...</span>');
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_lookup_company_names',
                nonce: yoloYsAdmin.nonce,
                company_ids: companyIds
            },
            success: function(response) {
                if (response.success && response.data.companies) {
                    var html = '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
                    response.data.companies.forEach(function(company) {
                        var bgColor = company.status === 'found' ? '#dcfce7' : '#fee2e2';
                        var textColor = company.status === 'found' ? '#166534' : '#991b1b';
                        var icon = company.status === 'found' ? '✓' : '✗';
                        html += '<span style="background: ' + bgColor + '; color: ' + textColor + '; padding: 4px 10px; border-radius: 4px; font-size: 13px;">';
                        html += icon + ' <strong>' + company.id + '</strong>: ' + company.name;
                        html += '</span>';
                    });
                    html += '</div>';
                    $container.html(html);
                } else {
                    $container.html('<span style="color: #dc2626;">Failed to look up companies</span>');
                }
            },
            error: function() {
                $container.html('<span style="color: #dc2626;">Error looking up companies</span>');
            }
        });
    }
    
    // Run on page load
    lookupCompanyNames();
    
    // Run when field changes (with debounce)
    var companyLookupTimeout;
    $('#yolo_ys_friend_companies').on('input', function() {
        clearTimeout(companyLookupTimeout);
        companyLookupTimeout = setTimeout(lookupCompanyNames, 500);
    });
    
    // ==========================================
    // AUTO-SYNC SETTINGS
    // ==========================================
    
    // Save year selection for auto-sync
    $('#yolo-ys-sync-year').on('change', function() {
        var year = $(this).val();
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_save_offers_year',
                nonce: yoloYsAdmin.nonce,
                year: year
            }
        });
    });
    
    // Auto-Sync Settings Handlers
    $('#yolo-ys-auto-sync-yachts, #yolo-ys-auto-sync-yachts-time').on('change', function() {
        saveAutoSyncSetting('yolo-ys-auto-sync-yachts');
    });
    
    $('#yolo-ys-auto-sync-offers, #yolo-ys-auto-sync-offers-time').on('change', function() {
        saveAutoSyncSetting('yolo-ys-auto-sync-offers');
    });
    
    function saveAutoSyncSetting(settingName) {
        var frequency = $('#' + settingName).val();
        var time = $('#' + settingName + '-time').val();
        var $status = $('#' + settingName + '-status');
        
        $status.text('Saving...');
        
        $.ajax({
            url: yoloYsAdmin.ajax_url,
            type: 'POST',
            data: {
                action: 'yolo_ys_save_auto_sync_settings',
                nonce: yoloYsAdmin.nonce,
                setting: settingName,
                frequency: frequency,
                time: time
            },
            success: function(response) {
                if (response.success) {
                    if (frequency === 'disabled') {
                        $status.text('Disabled').css('color', '#6b7280');
                    } else {
                        $status.text('Next: ' + response.data.next_run).css('color', '#16a34a');
                    }
                } else {
                    $status.text('Error: ' + response.data).css('color', '#dc2626');
                }
            },
            error: function() {
                $status.text('Save failed').css('color', '#dc2626');
            }
        });
    }
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
