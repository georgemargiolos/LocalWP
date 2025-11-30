# Changelog - YOLO Yacht Search & Booking Plugin

All notable changes to this project will be documented in this file.

## [2.5.4] - 2025-11-30

### Fixed - Search Results Card Styling
- **Beautiful 3-column grid layout** matching "Our Yachts" section
- **Strikethrough original prices** when yacht is discounted
- **Red discount badges** showing percentage off (-X%)
- **Prominent green final prices** in modern card design
- **Modern hover effects** with card lift and shadow enhancement
- **Responsive design**: 3 columns desktop, 2 columns tablet, 1 column mobile

### Added
- `original_price` field to search results (for strikethrough display)
- `discount_percentage` field to search results (for discount badge)
- `year_of_build` and `refit_year` fields from database to search results
- Gradient backgrounds for price containers and section headers
- Image lazy loading for better performance

### Changed
- Updated `renderBoatCard()` JavaScript function with discount logic
- Updated yacht-results CSS with beautiful modern styling
- Updated PHP search handler to include year_of_build and refit_year
- Renamed backend fields for consistency (start_price → original_price, discount → discount_percentage)

---

## [2.5.3] - 2025-11-30

### Fixed - CRITICAL: Integer Overflow Bug
- **Large yacht IDs** (19 digits like 7136018700000107850) were being corrupted
- JavaScript Number type cannot handle integers > 2^53 without precision loss
- Corrupted IDs caused API HTTP 500 errors and incorrect yacht matching

### Changed
- Changed yacht_id handling from **integers to strings** throughout codebase
- Updated `class-yolo-ys-booking-manager-api.php` to preserve yacht_id as string
- Updated AJAX handler to avoid intval() calls on yacht_id
- Updated yacht details template JavaScript to handle yacht_id as string

---

## [2.5.2] - 2025-11-30

### Added
- **Database fallback** for prices when API fails or returns empty results
- Multi-format API response handling (array vs object)
- Better error handling for API failures

### Fixed
- Carousel now uses cached database prices instead of triggering API calls
- Improved reliability when API is unavailable

---

## [2.5.1] - 2025-11-30

### Fixed
- **Removed tripDuration parameter** from API calls (was causing HTTP 500 errors)
- API calculates trip duration automatically from date range

---

## [2.5.0] - 2025-11-30

### Fixed
- **isInitialLoad flag bug** in date picker
- Changed from event-based to time-based (1 second timeout)
- Date picker now triggers availability checks on first selection

---

## [2.3.9] - 2025-11-29

### Added
- Enhanced debug logging for API calls
- Detailed error messages for troubleshooting

---

## [2.3.8] - 2025-11-29

### Fixed
- Carousel price loading to use cached prices
- Removed unnecessary API calls on carousel initialization

---

## [2.3.7] - 2025-11-29

### Changed
- Updated search results card design to match "Our Yachts" section
- Split yacht name and model into two lines
- Changed specs to show: Cabins, Built year, Length
- Red DETAILS button instead of blue
- Cleaner price display with green box

---

## Earlier Versions

See Git history for changes prior to v2.3.7.
