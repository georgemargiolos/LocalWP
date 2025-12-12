# Catamaran Layout Investigation

## Date: December 12, 2025

## Issue Report
User reports that catamaran search results show narrow yacht cards (not full width), while sailing yacht results display correctly.

## Investigation So Far

### What We Know
1. **JavaScript Rendering:** Lines 325 & 340 in `yolo-yacht-search-public.js` show yacht cards are rendered with `col-12 col-sm-6 col-lg-4` classes - SAME for all boat types
2. **Backend Filtering:** Lines 72-82 in `class-yolo-ys-public-search.php` show boat type filtering only affects SQL WHERE clause - no special rendering logic
3. **HTML Structure:** Browser inspection confirms no catamaran-specific classes or attributes
4. **Bootstrap Grid:** `col-lg-4` = 33.33% width on desktop (≥992px)

### What We DON'T Know Yet
- **Is this boat-count-specific or catamaran-specific?**
  - Current catamaran results: 1 YOLO boat (Strawberry) → narrow
  - Current sailing yacht results: 2 YOLO boats (Lemon + Aquilo) → correct (side-by-side)
  - **PENDING TEST:** Single sailing yacht results → narrow or full width?

### User Test In Progress
User is searching for a date range where only Lemon (sailing yacht) is available to determine if:
- **Hypothesis A:** ANY single yacht displays narrow (boat-count issue)
- **Hypothesis B:** Only catamarans display narrow (catamaran-specific bug)

## Attempted Fixes (Reverted)
1. **v60.1-60.2:** CSS selector to make single cards full width
   - Selector: `.yolo-ys-section-header + .container-fluid .row.g-4 .col-lg-4:first-child:last-child`
   - Properties: `max-width: 100%`, `flex: 0 0 100%`, `width: 100%` (all with `!important`)
   - **Result:** Did not work (CSS not applied or overridden)
   - **Reverted:** Per user request to investigate root cause first

2. **Bootstrap Dependency:** Added `array('bootstrap')` to CSS enqueue
   - **Purpose:** Load search-results.css AFTER Bootstrap
   - **Reverted:** Per user request

## Current Status
- All catamaran-related CSS changes reverted
- Waiting for user test results
- Will implement proper fix once root cause is confirmed

## Next Steps
1. Wait for user test: single sailing yacht layout
2. Analyze results to confirm hypothesis A or B
3. Implement targeted fix based on findings
4. Test both scenarios (single catamaran + single sailing yacht)
5. Verify multi-yacht scenarios still work correctly
