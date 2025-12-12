# Known Bugs - Fixed

This document tracks critical bugs that were discovered and fixed to prevent reintroduction.

---

## Bug #1: Text Customization Settings Not Saving (v60.3)

**Discovered:** December 12, 2025  
**Fixed in:** v60.3  
**Severity:** HIGH  
**Impact:** 40% of text customization functionality was broken

### Description

29 out of 75 text customization fields were not being saved when clicking "Save Text Settings" in the WordPress admin panel.

### Root Cause

The manual save handler in `admin/partials/texts-page.php` only included 46 fields in the `$text_options` array, but the form had 75 input fields. Fields were added to the form over time but never added to the save handler.

### Affected Fields

- `yolo_ys_text_availability_pricing` (user-reported)
- `yolo_ys_text_weekly_prices_title`
- `yolo_ys_text_discount_off`
- `yolo_ys_text_select_week`
- `yolo_ys_text_read_more` / `yolo_ys_text_read_less`
- `yolo_ys_text_location`
- `yolo_ys_text_no_pricing`
- `yolo_ys_text_choose_custom_dates`
- `yolo_ys_text_loading`
- `yolo_ys_text_extras`
- `yolo_ys_text_payable_at_base`
- `yolo_ys_text_per_booking` / `per_night` / `per_day` / `per_hour` / `per_person`
- `yolo_ys_text_draught` / `engine` / `refit`
- `yolo_ys_text_our_fleet_title` / `partner_title` / `no_yachts` / `details_button`
- `yolo_ys_text_quote_tagline` / `first_name` / `last_name` / `special_requests` / `required_field` / `quote_description`

### How to Prevent

**When adding new text customization fields:**

1. Add field to the form HTML (lines 200+)
2. **MUST** add field to `$text_options` array in save handler (lines 15-114)
3. **MUST** add field to `$texts` display array (lines 124-223)
4. **Verify** by running:
   ```bash
   cd /path/to/admin/partials
   grep -o 'name="yolo_ys_text_[^"]*"' texts-page.php | sed 's/name="//;s/"$//' | sort -u > /tmp/form_fields.txt
   grep -o "'yolo_ys_text_[^']*'" texts-page.php | sed "s/'//g" | sort -u > /tmp/save_fields.txt
   comm -23 /tmp/form_fields.txt /tmp/save_fields.txt
   # Should return empty (no missing fields)
   ```

### Files Modified

- `admin/partials/texts-page.php` (save handler + display array)
- `yolo-yacht-search.php` (version bump to 60.3)

### Prevention Checklist

- [ ] New field added to form HTML
- [ ] New field added to `$text_options` save array
- [ ] New field added to `$texts` display array
- [ ] Verification command run (returns empty)
- [ ] Manual test: edit field, save, refresh, verify persistence

---

## Bug #2: Catamaran Search Layout (v60.1 - v60.2)

**Discovered:** December 12, 2025  
**Fixed in:** v60.2  
**Severity:** MEDIUM  
**Impact:** Single yacht displays in narrow 33.33% column

### Description

When searching for catamarans (or any search returning exactly 1 yacht), the yacht card displayed in a narrow column (33.33% width) with large empty space on the right.

### Root Cause

Bootstrap grid uses `col-lg-4` for 3-column layout. With only 1 result, the card takes 1/3 of the row.

### Fix Attempts

**v60.1 (FAILED):**
```css
.col-lg-4:first-child:last-child {
    max-width: 100% !important;
    flex: 0 0 100% !important;
    /* MISSING: width: 100% !important; */
}
```

**v60.2 (SUCCESS):**
```css
.col-lg-4:first-child:last-child {
    max-width: 100% !important;
    flex: 0 0 100% !important;
    width: 100% !important;  /* ← CRITICAL */
}
```

### Lesson Learned

Bootstrap's flex system requires **all three properties** to override column width:
1. `max-width: 100%`
2. `flex: 0 0 100%`
3. `width: 100%` ← **Cannot be omitted**

### Files Modified

- `public/css/search-results.css` (lines 479-490)

---

## Prevention Guidelines

### For Text Customization

1. **Always use verification script** after adding new fields
2. **Test save functionality** manually before deployment
3. **Consider migrating to Settings API** to prevent manual array drift

### For CSS Fixes

1. **Test on actual server** - don't rely only on browser console
2. **Check computed styles** - not just applied styles
3. **Take screenshots** for visual confirmation
4. **Document all required properties** for complex overrides

### For All Bugs

1. **Document in this file** immediately after fixing
2. **Add to README** session summary
3. **Create detailed changelog** with root cause analysis
4. **Add prevention checklist** for future developers

---

**Last Updated:** December 12, 2025  
**Plugin Version:** 60.3
