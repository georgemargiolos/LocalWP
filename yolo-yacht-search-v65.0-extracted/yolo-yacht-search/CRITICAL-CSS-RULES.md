# CRITICAL CSS RULES - DO NOT BREAK

## 1. Yacht Details Overflow Rule

**File:** `public/css/bootstrap-mobile-fixes.css`

### THE RULE:
`.yolo-yacht-details-v3` must use `overflow-x: clip` (NOT `hidden` or `visible`)

### WHY:
- `overflow-x: hidden` breaks `position: sticky` on the booking sidebar
- `overflow-x: visible` causes horizontal scroll on mobile
- `overflow-x: clip` prevents horizontal scroll WITHOUT breaking sticky

### CORRECT PATTERN:

```css
/* At the TOP of the file - yacht-details is EXCLUDED from this list */
.yolo-ys-our-fleet,
.yolo-ys-search-results,
.yolo-booking-confirmation,
.yolo-balance-payment,
.yolo-guest-login-container,
.yolo-guest-dashboard {
    overflow-x: clip;  /* yacht-details NOT here */
    max-width: 100%;
}

/* At the END of the file - yacht-details has its own rule */
.yolo-yacht-details-v3 {
    overflow-x: clip !important;
    max-width: 100vw !important;
}
```

### WRONG PATTERN (DO NOT DO):

```css
/* WRONG - Adding yacht-details to early list with visible */
.yolo-ys-our-fleet,
.yolo-ys-search-results,
.yolo-yacht-details-v3,  /* ← WRONG - Don't add here */
.yolo-booking-confirmation {
    overflow-x: visible;  /* ← WRONG value */
}
```

---

## 2. Bootstrap Grid Integration

**DO NOT** add Bootstrap CSS/JS from scratch - it's already integrated.

**Files:**
- `vendor/bootstrap/bootstrap.min.css` (local copy)
- `vendor/bootstrap/bootstrap.bundle.min.js` (local copy)
- Loaded via `public/class-yolo-ys-public.php`

---

## 3. Font Handling

Plugin inherits fonts from WordPress theme. **DO NOT** use wildcard selectors.

### WRONG (broke the plugin in v22C):
```css
/* NEVER DO THIS - breaks layouts */
[class*="yolo-"] * {
    font-family: inherit;
}

.yolo-yacht-details-v3 * {
    font-family: inherit;  /* ← Wildcard breaks things */
}
```

### CORRECT:
```css
/* OK on containers - simple inheritance */
.yolo-balance-payment {
    font-family: inherit;
}

/* OK on form elements - browsers don't inherit by default */
input, textarea, select, button {
    font-family: inherit;
}
```

### Rule:
- ✅ `font-family: inherit` on **containers** (simple, no wildcard)
- ✅ `font-family: inherit` on **form elements** (input, textarea, button)
- ❌ **NEVER** use `*` wildcard with font-family

---

## Version History of This Document

- **v30.0** - Initial documentation created

