# Yolo-Charters.com Search Interface Analysis

## Frontend Search Block Elements

Based on the homepage inspection, the search block contains:

1. **Boat Type Dropdown** (select element `filter_kind`)
   - Options: "Boat type", "Sailing yacht", "Catamaran"
   - Default: "Sailing yacht"

2. **Date Range Picker** (input element `all-days`)
   - Placeholder: "Dates"
   - Current value: "29.11.2025 - 06.12.2025"
   - Uses a custom date picker component (likely Litepicker based on the API manual)

3. **Search Button**
   - Triggers the search query

4. **Availability Status Filter** (checkbox)
   - Label: "Include yachts without availability confirmation"
   - Optional filter for search results

## Backend CSS Analysis

The provided CSS file reveals the following key components:

### Search Form Structure (DIV.wbm_ssf_box)
- Small Search Form (SSF) container
- Left/right data columns for form layout
- Submit button styling
- Advanced search toggle option
- Clear functionality

### Calendar Component (jCal)
- Custom calendar widget for date selection
- Month/year navigation
- Day selection with hover states
- Previous/subsequent month display
- Selected day highlighting
- Disabled/invalid day styling

### Form Styling
- Input fields: 258px width
- Form blocks with 20px margin
- Contact form column layout
- Error/info message styling (red/green borders)

### Search Result Display
- Table-based layout for yacht listings
- Price information display (green color: #00FF00)
- Discount styling
- Yacht details tables
- Image galleries (main: 446px x 336px, side: 216px x 336px)

### Key CSS Classes for WordPress Plugin
1. `.wbm_ssf_box` - Main search form container
2. `.formField` - Input field styling (width: 200px)
3. `.box_content` - Content container with borders
4. `.price` - Price display (green, 15px)
5. `.discount` - Discount display (green)
6. `.error` / `.info` - Message styling
7. `.btn.disabled` - Disabled button state

## Integration Points for WordPress Plugin

### Required Form Elements
1. Boat type selector (dropdown)
2. Date range picker (start/end dates)
3. Optional filters:
   - Base/marina location
   - Sailing area
   - Number of cabins
   - Length range
   - Price range
4. Availability status toggle

### API Integration Flow
1. User fills search form
2. JavaScript captures form data
3. AJAX request to WordPress backend (PHP)
4. PHP makes API call to Booking Manager `/offers` endpoint
5. Response cached in WordPress database
6. Results rendered and returned to frontend
7. Display results in table/grid format

### Security Considerations
- API key stored in WordPress options (encrypted)
- Server-side API calls only (never expose key to frontend)
- Nonce verification for AJAX requests
- Input sanitization and validation
