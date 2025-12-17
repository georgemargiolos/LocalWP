# Changelog v60.6.1 - Clean Version (Text Customization + Container Fix)

**Date:** December 14, 2025  
**Type:** Bug Fix + Feature Enhancement

---

## What's Included

This is a **clean version** that combines:
- ✅ **v60.6**: Complete text customization system
- ✅ **v61.0**: Nested container fix
- ❌ **NO width-forcing CSS** (v60.7-v61.3 attempts removed)

---

## Features from v60.6

### Text Customization System

Made the following texts customizable through **WordPress Admin → YOLO Yacht Search → Texts**:

1. **"Remaining:"** - Deposit breakdown label
2. **"CANCEL"** - Modal dialog button
3. **"Free"** - Extras pricing label
4. **"Catamaran"** - Boat type dropdown option
5. **"Message"** - Form placeholder text

All existing 40+ customizable texts from previous versions are preserved.

---

## Fix from v61.0

### Nested Container Issue

**Problem:** The JavaScript was creating nested `<div class="container-fluid">` elements:

```html
<div class="container-fluid">  <!-- From template -->
    <div class="container-fluid">  <!-- From JavaScript - NESTED! -->
        <div class="row g-4">
            <!-- Yacht cards -->
        </div>
    </div>
</div>
```

This nesting caused Bootstrap grid to behave incorrectly.

**Fix:** Removed the inner `container-fluid` creation from JavaScript. Now it only creates the `row`:

```javascript
// BEFORE
html += `<div class="container-fluid"><div class="row g-4">`;

// AFTER
html += `<div class="row g-4">`;
```

**Result:** Clean HTML structure with no nesting:

```html
<div class="container-fluid">  <!-- From template only -->
    <div class="row g-4">
        <!-- Yacht cards -->
    </div>
</div>
```

---

## What's NOT Included

This version **does NOT include** any of the width-forcing CSS attempts from v60.7-v61.3:
- No `.col-lg-4` width overrides
- No `.entry-content` max-width removal
- No `.wp-block-group` targeting
- No aggressive `!important` rules

**Why?** User fixed the grid width issue through WordPress theme settings, so these CSS hacks are not needed.

---

## Files Modified

1. `public/js/yolo-yacht-search-public.js` (lines 321, 327, 336, 342) - Removed nested container-fluid
2. `yolo-yacht-search.php` - Version bump to 60.6.1

---

## Files Unchanged (from v60.6)

1. `admin/partials/texts-page.php` - Text customization settings
2. `public/templates/partials/yacht-details-v3-scripts.php` - Customizable text usage
3. All CSS files - No width-forcing rules added

---

## Upgrade Path

- **From v60.3 or earlier**: Direct upgrade, all features preserved
- **From v60.6**: Only adds nested container fix
- **From v60.7-v61.3**: Removes width-forcing CSS (not needed)

---

## Testing

After installing this update:

1. **Clear all caches** (WordPress + browser + CDN)
2. Test search results with different boat counts
3. Verify no nested containers in browser DevTools
4. Test text customization in admin panel

---

**Status:** Ready for deployment - Clean, stable version
