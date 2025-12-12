# Search Results Layout Issue - Catamaran Filter

## Problem Observed

When filtering search results for "Catamaran" only, the yacht cards are NOT full width.

**URL:** https://yolo-charters.com/search-results/?dateFrom=2026-07-04T00%3A00%3A00&dateTo=2026-07-11T00%3A00%3A00&kind=Catamaran

## Visual Analysis from Screenshots

### YOLO Charters Fleet Section
- Strawberry card appears to be in a narrow column (not full width)
- Card looks like it's constrained to maybe 400px width
- There's a lot of empty white space on the right side

### Partner Fleet Section
- Two partner boats (The, Lady) are displayed side-by-side
- Each card is about 50% width
- This looks correct for a 2-column layout

## Suspected Issue

The YOLO boat (Strawberry) is being displayed in a single-column layout but the card width is constrained, not expanding to full width.

Possible causes:
1. CSS grid/flexbox issue where single item doesn't expand
2. Different CSS class for YOLO boats vs partner boats
3. Missing CSS rule for single-item scenarios
4. Container width constraint

## Next Steps

1. Check the search results template file
2. Inspect CSS for yacht cards
3. Look for different styling between YOLO and partner boats
4. Check if there's a grid/flex layout that needs adjustment
