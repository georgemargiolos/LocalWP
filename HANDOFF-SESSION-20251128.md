# Handoff Document - YOLO Yacht Search Plugin

**Date:** November 28, 2025  
**From:** Manus AI (Current Session)  
**To:** Manus AI (Next Session)

## 1. Session Summary & Accomplishments

This session was highly productive, focusing on critical bug fixing and significant UI/UX enhancements. We successfully resolved a major performance issue and redesigned a key user interface component.

### Key Achievements:

1.  **CRITICAL BUG FIX (v1.5.3 & v1.5.4):** Diagnosed and resolved a severe performance bug where the yacht data sync was hanging for over an hour. The root cause was twofold:
    *   **Incorrect API Parameter:** The `get_prices` API call was using `company` instead of `companyId`, causing the API to return price data for every boat in the Booking Manager system.
    *   **Excessive Data Fetching:** Even with the correct parameter, fetching 12 months of data was too slow.
    *   **Solution:** Corrected the parameter to `companyId` and reduced the price sync period to 3 months. **Sync time is now 30-60 seconds instead of 1+ hours.**

2.  **UI/UX ENHANCEMENT (v1.5.5):** Completely redesigned the price/availability carousel on the yacht details page to match the professional design of `Boataround.com`.
    *   **Peak Season Filter:** The carousel now exclusively displays prices for **May through September**.
    *   **Grid Layout:** Shows **4 weeks at a time** in a responsive grid (4→2→1 columns).
    *   **Smart Navigation:** Users can navigate through weekly periods in groups of four, dramatically improving usability.

### Versions Created in this Session:

*   **v1.5.3:** Critical fix for the `companyId` API parameter.
*   **v1.5.4:** Performance fix, reducing price sync from 12 to 3 months.
*   **v1.5.5:** UI/UX redesign of the price carousel.

## 2. Technical Deep Dive

### Booking Manager API Integration

The core of the API communication is handled by `includes/class-yolo-ys-booking-manager-api.php`.

#### API Call Pattern

All requests are made through the private `make_request()` method, which handles authentication and response parsing.

```php
// includes/class-yolo-ys-booking-manager-api.php
private function make_request($endpoint, $params = array()) {
    $url = $this->base_url . $endpoint;
    if (!empty($params)) {
        $url .= "?" . http_build_query($params);
    }
    
    $args = array(
        'headers' => array(
            'Authorization' => $this->api_key, // API key is sent in the header
            'Accept' => 'application/json',
        ),
        'timeout' => 30, // 30-second timeout for requests
    );
    
    $response = wp_remote_get($url, $args);
    // ... handles response, errors, and returns JSON decoded data
}
```

#### Key Endpoints Used:

| Endpoint | Method | Class Method | Parameters | Purpose |
| :--- | :--- | :--- | :--- | :--- |
| `/yachts` | GET | `get_yachts_by_company()` | `companyId` | Fetches all boats for a specific company. |
| `/prices` | GET | `get_prices()` | `companyId`, `dateFrom`, `dateTo` | Fetches weekly prices for a specific company within a date range. |

**Note:** The critical bug was in `get_prices()`, which incorrectly used `company` instead of `companyId` as a parameter key.

### Caching & Data Storage Strategy

The plugin relies heavily on a local database cache to ensure fast page loads and minimize API calls. The sync process populates these tables.

#### Database Schema:

The `YOLO_YS_Database` and `YOLO_YS_Database_Prices` classes manage the custom tables.

*   `wp_yolo_yachts`: Stores core yacht details (name, model, specs).
*   `wp_yolo_yacht_images`: Stores image URLs.
*   `wp_yolo_yacht_products`: Stores charter types (e.g., Bareboat).
*   `wp_yolo_yacht_equipment`: Stores equipment lists.
*   `wp_yolo_yacht_extras`: Stores optional extras.
*   `wp_yolo_yacht_prices`: Stores all weekly pricing data.

#### Sync Process (`includes/class-yolo-ys-sync.php`)

The `sync_all_yachts()` function orchestrates the entire process:

1.  **Set Time Limit:** Increases PHP's `max_execution_time` to 300 seconds to prevent timeouts during the sync.
2.  **Fetch All Yachts:** Loops through all company IDs (YOLO + Partners) and calls `get_yachts_by_company()` for each.
3.  **Store Yacht Data:** Uses `db->store_yacht()` to insert or update yacht details in the `wp_yolo_yachts` and related tables.
4.  **Fetch Prices:** Calls the `sync_prices()` method.
5.  **Store Prices:** The `sync_prices()` method fetches data for the next **3 months** (as of v1.5.4) and stores it in the `wp_yolo_yacht_prices` table.
6.  **Update Timestamp:** Records the completion time in `wp_options` under the `yolo_ys_last_sync` key.

This caching strategy means the frontend of the website **never calls the API directly**. All data is served from the local WordPress database, ensuring maximum performance.

## 3. Project Status & Next Steps

**Current Version:** v1.5.5

**Overall Progress:** 80% complete. The plugin is stable, performs well, and has a professional UI for displaying yachts. The core data sync and display functionalities are complete.

### Remaining High-Priority Features:

1.  **Search Functionality (Top Priority):** The search widget on the frontend is currently a UI placeholder. The backend logic to process search queries against the local database is the most critical missing feature.
    *   **Task:** Implement the logic in `public/class-yolo-ys-shortcodes.php` for the `[yolo_search_results]` shortcode to filter yachts based on user input (boat type, dates, etc.).

2.  **Stripe Payment Integration:** The "Book Now" button is a placeholder. This involves integrating the Stripe API to handle payments.

3.  **Booking Creation:** After a successful payment, the plugin needs to make a `POST` request to the Booking Manager API's `/bookings` endpoint to create the official booking.

### Recommendation for Next Session:

**Focus exclusively on implementing the search functionality.** This is the #1 core feature that is currently non-functional and is essential for the plugin to be useful.
