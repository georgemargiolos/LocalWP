# Yacht Details Styling Architecture

## IMPORTANT: Which File Contains the Actual Styles?

### ✅ ACTIVE FILE (Currently Used):
**`public/templates/partials/yacht-details-v3-styles.php`**
- 1164 lines of CSS
- Loaded via PHP include in `yacht-details-v3.php` (line 636)
- Supports dynamic color customization from WordPress admin settings
- Uses CSS custom properties (CSS variables)
- Gets colors from `get_option()` WordPress functions
- Outputs `<style>` block directly in HTML

### ❌ ORPHAN FILE (NOT Used):
**`public/css/yacht-details-v3.css.ORPHAN-NOT-USED`**
- 948 lines of CSS
- NEVER loaded or enqueued
- Static CSS with hardcoded colors
- Outdated version
- Renamed to `.ORPHAN-NOT-USED` to prevent confusion

---

## How Yacht Details Styles Are Loaded

### Current Architecture:
```
yacht-details-v3.php (template)
    ↓
    includes yacht-details-v3-styles.php (line 636)
        ↓
        Gets colors from WordPress options
        ↓
        Outputs <style> block with CSS variables
```

### CSS Files Loaded via wp_enqueue_style:
1. `bootstrap-grid` (CDN)
2. `yolo-yacht-search-public.css`
3. `yacht-card.css`
4. `emergency-override.css`
5. `yacht-details-responsive-fixes.css` ← Only yacht details CSS enqueued

### CSS Loaded via PHP Include:
- `yacht-details-v3-styles.php` ← Main yacht details styles (dynamic colors)

---

## Why PHP Include Instead of Enqueue?

The PHP include approach is used because:

1. **Dynamic Color Customization:**
   - Colors are pulled from WordPress admin settings
   - Each site can have different brand colors
   - CSS variables are generated dynamically

2. **Example:**
   ```php
   $colors = array(
       'primary' => get_option('yolo_ys_color_primary', '#1e3a8a'),
       'primary_hover' => get_option('yolo_ys_color_primary_hover', '#1e40af'),
       // ...
   );
   ```

3. **CSS Output:**
   ```css
   :root {
       --yolo-primary: #1e3a8a; /* From admin settings */
       --yolo-primary-hover: #1e40af; /* From admin settings */
       /* ... */
   }
   ```

---

## DO NOT:

❌ Enqueue `yacht-details-v3.css` or `yacht-details-v3.css.ORPHAN-NOT-USED`  
❌ Remove the PHP include from `yacht-details-v3.php`  
❌ Restore the orphan file  

## DO:

✅ Keep the PHP include approach for yacht details styles  
✅ Edit `yacht-details-v3-styles.php` for style changes  
✅ Use `yacht-details-responsive-fixes.css` for additional responsive fixes  
✅ Use `emergency-override.css` for critical overrides  

---

## File Locations

| File | Path | Purpose |
|------|------|---------|
| Main styles (ACTIVE) | `public/templates/partials/yacht-details-v3-styles.php` | Dynamic CSS with admin color settings |
| Responsive fixes | `public/css/yacht-details-responsive-fixes.css` | Additional responsive CSS |
| Emergency overrides | `public/css/emergency-override.css` | Critical Bootstrap Grid overrides |
| Orphan file (UNUSED) | `public/css/yacht-details-v3.css.ORPHAN-NOT-USED` | Old static CSS - NOT USED |

---

## Version History

- **v3.7.15:** Incorrectly tried to enqueue yacht-details-v3.css (MISTAKE)
- **v3.7.16:** Reverted enqueue, renamed orphan file, documented architecture

---

**Last Updated:** December 2, 2024  
**Status:** ✅ Corrected and documented
