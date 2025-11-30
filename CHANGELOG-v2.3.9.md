# YOLO Yacht Search Plugin - Changelog v2.3.9

**Date:** November 30, 2025  
**Version:** 2.3.9  
**Status:** 🐛 Critical Bug Fix - NaN Prices

---

## What's Fixed

### 🔴 CRITICAL: JavaScript Converting Correct Prices to NaN

**Problem:**
After deploying v2.3.8, prices would flash correctly then change to `NaN EUR` in the carousel.

**Root Cause:**
JavaScript was trying to "reformat" prices that were already correctly formatted by PHP from the database. The carousel prices come from the database (not live API), so they don't need JavaScript formatting.

**What Was Happening:**
1. PHP outputs correct price from database: `2,925.00 EUR` ✅
2. Page loads and shows correct price briefly ✅
3. JavaScript `DOMContentLoaded` event fires
4. JavaScript tries to reformat the already-correct price
5. Something goes wrong → `NaN EUR` ❌

**The Fix:**
Removed JavaScript formatting for carousel prices. They're already correctly formatted by PHP!

**What JavaScript SHOULD Format:**
- ✅ Price display box (next to Book Now button) - gets updated by live API
- ❌ Carousel prices - already correct from database

---

## Technical Details

### Before Fix (v2.3.8)

```javascript
// ❌ WRONG: Reformatting already-correct prices
document.querySelectorAll('.price-original span, .price-final, .price-discount-badge').forEach(function(el) {
    const text = el.textContent.trim(); // "2,925.00 EUR"
    const match = text.match(/([\d,]+(?:\.\d+)?)\s*([A-Z]{3})/);
    if (match) {
        const price = match[1].replace(/,/g, ''); // "2925.00"
        el.textContent = formatPrice(price) + ' ' + currency; // Something goes wrong → NaN
    }
});
```

### After Fix (v2.3.9)

```javascript
// ✅ CORRECT: Leave carousel prices alone, they're already correct!
// NOTE: Carousel prices are already correctly formatted by PHP from database
// DO NOT reformat them with JavaScript - it causes NaN errors!
// Only format the price display box which gets updated by live API calls
```

---

## Files Modified

| File | Change | Description |
|------|--------|-------------|
| `public/templates/partials/yacht-details-v3-scripts.php` | Lines 193-211 removed | Removed carousel price formatting |
| `yolo-yacht-search.php` | Version bump | Updated to 2.3.9 |

---

## Why This Happened

### The Confusion

**v2.3.7-2.3.8:** We thought the carousel was showing wrong prices because of a regex bug.

**Reality:** The carousel was showing correct prices from PHP, but JavaScript was breaking them!

### The Data Flow

**Carousel:**
```
Database → PHP → HTML → Display ✅
(No JavaScript needed!)
```

**Price Box:**
```
Database → PHP → HTML → Display (initial)
         ↓
Live API → JavaScript → Update Display ✅
(JavaScript needed for live updates!)
```

---

## Testing

### How to Test

1. Visit yacht details page:
   ```
   http://yolo-local.local/yacht-details-page/?yacht_id=6362109340000107850
   ```

2. Check the "Peak Season Pricing" carousel

3. Verify prices show correctly and DON'T change to NaN:
   - April 18-25: Should show `2,925.00 EUR` (not `NaN EUR`)
   - April 25-May 2: Should show `2,925.00 EUR` (not `NaN EUR`)
   - May 16-23: Should show `3,870.00 EUR` (not `NaN EUR`)

4. Prices should NOT flash or change after page load

### Expected Results

✅ Carousel prices display correctly  
✅ Prices do NOT change after page load  
✅ No `NaN EUR` anywhere  
✅ Price box (next to Book Now) still works with live API

---

## Deployment Notes

### No Database Changes Required

No database changes needed.

### No Sync Required

You do NOT need to re-run "Sync Prices".

### Clear Browser Cache

Users may need to clear their browser cache to see the fix.

---

## Version History

### v2.3.9 (Nov 30, 2025) - THIS VERSION
- 🐛 **FIXED:** JavaScript converting correct carousel prices to NaN
- 🧹 **REMOVED:** Unnecessary JavaScript price formatting for carousel
- 📝 **CLARIFIED:** Carousel uses database prices, price box uses live API

### v2.3.8 (Nov 30, 2025)
- 🐛 Attempted to fix price carousel regex (but introduced NaN bug)
- ✅ Fixed regex to handle comma-separated thousands
- ✅ Added comma-stripping before Number() conversion

### v2.3.7 (Nov 30, 2025)
- 🐛 Fixed API response parsing for `get_offers()` and `get_live_price()`
- 🐛 Fixed search box defaulting to "Sailing yacht"
- 🎨 Made search results yacht cards match "Our Yachts" design
- 📝 Added 3 missing shortcodes to admin documentation

---

## Lessons Learned

1. **Don't fix what isn't broken** - The carousel prices were already correct from PHP
2. **Understand the data flow** - Carousel uses database, price box uses live API
3. **Test before deploying** - Should have tested in sandbox first
4. **Listen to the user** - "It flashes correct then changes" was the key clue!

---

## Credits

**Bug Reported:** User (9 times! 😅)  
**Root Cause Analysis:** Manus AI (with user's help!)  
**Key Insight:** User ("Why don't you test here first?")  
**Fixed By:** Manus AI  
**Date:** November 30, 2025

---

**Status:** Ready for deployment ✅
