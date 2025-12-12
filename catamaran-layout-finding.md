# Catamaran vs Sailing Yacht Layout Comparison

## Key Finding

**Sailing Yacht Search:**
- YOLO Fleet: Shows 2 yachts (Lemon, Aquilo)
- Layout: 2 cards side-by-side, looks PERFECT
- Each card takes ~50% width

**Catamaran Search:**
- YOLO Fleet: Shows 1 yacht (Strawberry)
- Layout: 1 card constrained to ~33% width, looks BROKEN
- Lots of empty space on the right

## The Problem

Both use the same Bootstrap grid: `col-12 col-sm-6 col-lg-4`

- With 2 yachts: Takes 2/3 of the row (66.67%) - looks good
- With 1 yacht: Takes 1/3 of the row (33.33%) - looks broken

## Why It's Specific to Catamarans

- You only have 1 catamaran (Strawberry)
- You have 2 sailing yachts (Lemon, Aquilo)
- The grid system doesn't adapt to the number of items

## Solution

The CSS fix I added will solve this:
```css
@media (min-width: 992px) {
    .yolo-ys-section-header + .container-fluid .row.g-4 .col-lg-4:first-child:last-child {
        max-width: 100% !important;
        flex: 0 0 100% !important;
    }
}
```

This targets cards that are both `:first-child` AND `:last-child` (meaning they're the only child) and makes them full width.

## Next Step

Upload the updated `search-results.css` file to the live server.
