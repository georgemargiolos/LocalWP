# Hardcoded Texts Audit - Frontend Templates

## Scan Date: December 12, 2025

This document lists ALL hardcoded text strings found in frontend templates that should be made customizable.

## Summary

- **Total files scanned:** 15+ template files
- **Hardcoded texts found:** 30+
- **Status:** In progress

## Hardcoded Texts by Category

### 1. Yacht Specifications Labels

**File:** `partials/yacht-card.php`
- Line 108: "Refit"

**File:** `yacht-details-v2.php`
- Line 153: "Length"
- Line 158: "Cabins"
- Line 168: "Year"
- Line 173: "Head"
- Line 183: "DRAUGHT"
- Line 190: "ENGINE"
- Line 197: "WATER"
- Line 204: "BEAM"
- Line 211: "FUEL"
- Line 218: "BERTHS"

**File:** `yacht-details-v3.php`
- Line 378: "BEAM"
- Line 390: "BERTHS"

**File:** `yacht-details.php`
- Line 113: "Length"
- Line 119: "Cabins"
- Line 125: "Year"
- Line 136: "Head"

### 2. Section Titles

**File:** `yacht-details-v2.php`
- Line 228: "Description"
- Line 236: "Equipment"

**File:** `yacht-details.php`
- Line 181: "Equipment" (h3 section title)
- Line 226: "Description" (h3 section title)

### 3. Button Labels

**File:** `partials/yacht-details-v3-scripts.php`
- Line 71: "CANCEL" (custom dates modal)
- Line 697: "CANCEL" (booking form modal)

### 4. Form Placeholders & Labels

**File:** `partials/yacht-details-v3-scripts.php`
- Line 64: "Message" (textarea placeholder with yacht name interpolation)

### 5. Toggle/UI Elements

**File:** `partials/yacht-details-v3-scripts.php`
- Line 13: "More..." / "Less" (description toggle - comment)

### 6. Dropdown Options

**File:** `search-form.php`
- Line 15: "Catamaran" (boat type option)

**File:** `search-results.php`
- Line 25: "Catamaran" (boat type option)

Note: "Sailing yacht" also appears but uses `_e()` translation function

### 7. Pricing Labels

**File:** `yacht-details.php`
- Line 211: "Free" (for free extras)

### 8. Already Fixed

✅ "Remaining:" - Fixed in v60.3 (yacht-details-v3-scripts.php line 886)

## Texts Already Customizable

The following texts are already using `yolo_get_text()` or `_e()`:
- Most button labels (Book Now, Request Quote, etc.)
- Most form labels
- Most section titles
- Boat type "Sailing yacht"

## Recommended Actions

1. **High Priority:**
   - Yacht spec labels (Length, Cabins, Year, etc.) - used on every yacht page
   - Section titles (Description, Equipment)
   - CANCEL button labels

2. **Medium Priority:**
   - Technical spec labels (DRAUGHT, ENGINE, WATER, BEAM, FUEL, BERTHS)
   - "Free" label for extras
   - "Refit" label

3. **Low Priority:**
   - Form placeholder text (can be customized but less critical)
   - Dropdown options (Catamaran - though Sailing yacht is already translatable)

## Implementation Plan

For each hardcoded text:
1. Add to text customization settings (admin/partials/texts-page.php)
2. Add to save handler array
3. Add to display array with default value
4. Replace hardcoded text with `yolo_get_text('key', 'Default')`
5. Test save/display functionality
