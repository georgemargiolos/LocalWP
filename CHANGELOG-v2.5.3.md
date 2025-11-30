# YOLO Yacht Search v2.5.3 - Complete Changelog

## Date: November 30, 2025

## Critical Bug Fixes

### 1. INTEGER OVERFLOW BUG - Yacht IDs Corrupted (CRITICAL)

**Problem:**
- Large yacht IDs (e.g., `7136018700000107850`) were being corrupted during processing
- PHP `intval()` on 32-bit systems returns `PHP_INT_MAX` for numbers exceeding 2^31
- JavaScript `Number` type loses precision for integers > 2^53 (max safe integer)
- Example: `7136018700000107850` becomes `7136018700000108000` in JavaScript
- Corrupted IDs don't match any yacht in the API → "Not Available" errors

**Root Cause:**
- PHP code used `intval($_POST['yacht_id'])` to validate yacht IDs
- JavaScript used unquoted numbers: `yacht_id: <?php echo $yacht_id; ?>`
- Both caused precision loss for large integers

**Fix:**
- **PHP:** Changed all `intval($yacht_id)` to `sanitize_text_field($yacht_id)`
- **JavaScript:** Changed all yacht_id to quoted strings: `"<?php echo esc_attr($yacht_id); ?>"`
- Yacht IDs now preserved as strings throughout the entire flow

**Files Changed:**
- `includes/class-yolo-ys-stripe-handlers.php` (3 locations)
- `public/templates/partials/yacht-details-v3-scripts.php` (2 locations)

---

### 2. CAROUSEL SKIP LOGIC - Prevent Unnecessary API Calls

**Problem:**
- Clicking "SELECT THIS WEEK" on price carousel triggered live API call
- Sometimes API returned empty (rate limits, temporary issues)
- Showed "Not Available" error even though carousel had valid cached price

**Fix:**
- Added `skipApiCallForCarouselSelection` flag
- Set to `true` when carousel week is clicked
- Date picker checks flag and skips API call if set
- Uses carousel's cached price directly instead

**Files Changed:**
- `public/templates/partials/yacht-details-v3-scripts.php`

---

### 3. DATABASE FALLBACK - Handle API Failures Gracefully

**Problem:**
- When Booking Manager API failed (HTTP 500, timeouts, etc.), plugin showed "Not Available"
- No fallback to cached database prices
- Users couldn't book even when prices existed in database

**Fix:**
- Added database fallback in `get_live_price()` method
- When API fails OR returns empty, checks local WordPress database
- Returns cached price if found
- Only shows "Not Available" if both API and database have no data

**Files Changed:**
- `includes/class-yolo-ys-booking-manager-api.php`

---

### 4. MULTI-FORMAT API RESPONSE HANDLING

**Problem:**
- Booking Manager API returns offers in different formats:
  - Wrapped: `{ "offers": [...] }`
  - Direct array: `[...]`
  - Single object: `{ "yachtId": ..., "price": ... }`
  - Price-only: `{ "price": ... }`
- Plugin only handled one format

**Fix:**
- Enhanced `get_live_price()` to detect and handle all formats
- Checks for `offers` key first
- Falls back to direct array
- Handles single object responses

**Files Changed:**
- `includes/class-yolo-ys-booking-manager-api.php`

---

### 5. INITIAL LOAD SKIP - Fix Page Load API Trigger

**Problem:**
- Date picker triggered on page load when dates pre-filled from URL
- Caused unnecessary API call
- Sometimes overwrote carousel prices with "Not Available"

**Fix:**
- Changed `isInitialLoad` from event-based to time-based
- Automatically becomes `false` after 1 second
- Page load events (happen immediately) → Skipped
- User date selections (happen after 1 second) → Work correctly

**Files Changed:**
- `public/templates/partials/yacht-details-v3-scripts.php`

---

### 6. REMOVED INVALID API PARAMETER

**Problem:**
- Plugin was sending `tripDuration=7` parameter to `/offers` endpoint
- This parameter is NOT documented in Booking Manager API v2.2.0
- Caused HTTP 404 errors for some requests

**Fix:**
- Removed `tripDuration` parameter from API calls
- API calculates duration automatically from date range

**Files Changed:**
- `includes/class-yolo-ys-booking-manager-api.php`

---

## Testing Performed

1. ✅ Tested with large yacht IDs (> 2^53) - precision preserved
2. ✅ Tested carousel "SELECT THIS WEEK" - no API call, instant response
3. ✅ Tested manual date selection - API call works correctly
4. ✅ Tested API failure scenarios - database fallback works
5. ✅ Tested with real WordPress installation
6. ✅ Verified with Booking Manager API directly

---

## Upgrade Instructions

1. Deactivate and delete old plugin version
2. Upload `yolo-yacht-search-v2.5.3.zip`
3. Activate plugin
4. Test availability check on any yacht page

---

## Technical Details

### Integer Precision Limits

| Type | Max Safe Integer | Example Issue |
|------|------------------|---------------|
| JavaScript Number | 2^53 = 9,007,199,254,740,992 | `7136018700000107850` → `7136018700000108000` |
| PHP 32-bit int | 2^31 = 2,147,483,647 | Returns `PHP_INT_MAX` |
| PHP 64-bit int | 2^63 = 9,223,372,036,854,775,807 | Works but JS still corrupts |

**Solution:** Keep yacht IDs as strings throughout entire application stack.

---

## Credits

- Bug discovery and analysis: Cursor AI
- Implementation and testing: Manus AI Agent
- Date: November 30, 2025
