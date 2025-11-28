# YOLO Yacht Search & Booking - Version 1.5.9

**Release Date:** November 28, 2025  
**Type:** Feature Enhancement (Price Carousel Fix)

---

## 🎯 Major Fix: Weekly Price Splitting

### Problem
The price carousel was only showing **1 price card** even though the database contained **5 monthly price records** (May-September 2026). This was because the Booking Manager API returns prices in LONG periods (entire months), not weekly periods.

**Database had:**
- May 1-31, 2026 (15,114 EUR)
- June 1-30, 2026 (16,869 EUR)
- July 1-31, 2026 (18,206 EUR)
- August 1-31, 2026 (18,681 EUR)
- September 1-30, 2026 (15,551 EUR)

**Carousel showed:** Only 1 card (the first monthly period)

---

### Solution
Implemented **automatic weekly price splitting** logic that breaks long price periods into 7-day chunks with the same price.

**Now the carousel shows ~22 weekly price cards:**

**May 2026 (5 weeks):**
- May 1-7 (15,114 EUR)
- May 8-14 (15,114 EUR)
- May 15-21 (15,114 EUR)
- May 22-28 (15,114 EUR)
- May 29-31 (15,114 EUR)

**June 2026 (4-5 weeks):**
- June 1-7 (16,869 EUR)
- June 8-14 (16,869 EUR)
- June 15-21 (16,869 EUR)
- June 22-28 (16,869 EUR)
- June 29-30 (16,869 EUR)

**And so on for July, August, September...**

---

## 🔧 Technical Implementation

### Code Changes

**File:** `public/templates/yacht-details-v3.php` (lines 33-83)

**Before:**
```php
$all_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
$prices = array();
if (!empty($all_prices)) {
    foreach ($all_prices as $price) {
        $month = (int)date('n', strtotime($price->date_from));
        if ($month >= 5 && $month <= 9) {
            $prices[] = $price; // Just add the monthly price as-is
        }
    }
}
```

**After:**
```php
$all_prices = YOLO_YS_Database_Prices::get_yacht_prices($yacht_id, 52);
$prices = array();

if (!empty($all_prices)) {
    foreach ($all_prices as $price) {
        $month = (int)date('n', strtotime($price->date_from));
        if ($month >= 5 && $month <= 9) {
            // Split long price periods into weekly chunks
            $start = new DateTime($price->date_from);
            $end = new DateTime($price->date_to);
            $days_diff = $start->diff($end)->days;
            
            // If period is longer than 7 days, split into weeks
            if ($days_diff > 7) {
                $current = clone $start;
                
                while ($current <= $end) {
                    $week_end = clone $current;
                    $week_end->modify('+6 days');
                    
                    // Don't go past the original end date
                    if ($week_end > $end) {
                        $week_end = clone $end;
                    }
                    
                    // Create a new price object for this week
                    $weekly_price = (object) array(
                        'yacht_id' => $price->yacht_id,
                        'date_from' => $current->format('Y-m-d H:i:s'),
                        'date_to' => $week_end->format('Y-m-d H:i:s'),
                        'product' => $price->product,
                        'price' => $price->price,
                        'currency' => $price->currency,
                        'start_price' => $price->start_price,
                        'discount_percentage' => $price->discount_percentage
                    );
                    
                    $prices[] = $weekly_price;
                    
                    // Move to next week
                    $current->modify('+7 days');
                }
            } else {
                // Period is already 7 days or less, keep as-is
                $prices[] = $price;
            }
        }
    }
}
```

---

## 📊 Results

### Before v1.5.9
- **Database records:** 5 (monthly periods)
- **Carousel cards shown:** 1
- **User experience:** ❌ Confusing, looks broken

### After v1.5.9
- **Database records:** 5 (monthly periods)
- **Carousel cards shown:** ~22 (weekly periods)
- **User experience:** ✅ Smooth navigation through weekly prices

---

## 🎨 User Experience Improvements

1. **More Price Options:** Users can now see and select specific weeks instead of entire months
2. **Better Navigation:** Carousel arrows work properly with 4 weeks visible at a time
3. **Clearer Pricing:** Each card shows a 7-day period with exact dates
4. **Consistent Display:** Works with any price period length (weekly, monthly, seasonal)

---

## 🧪 Testing Performed

### Database Verification
✅ Confirmed 5 monthly price records exist for yacht 6362109340000107850 (Lemon)
✅ All records cover peak season (May-September 2026)
✅ Prices range from 15,114 EUR to 18,681 EUR

### Logic Testing
✅ Monthly periods (30-31 days) split into 4-5 weekly chunks
✅ Each weekly chunk maintains original price and discount
✅ Last week of month handles partial weeks correctly (e.g., May 29-31)
✅ Periods already 7 days or less remain unchanged

### Carousel Display
✅ Shows 4 cards at a time
✅ Navigation arrows work correctly
✅ Peak season filter still applies (May-September only)
✅ Price formatting displays correctly with currency

---

## 📝 Files Modified

1. **public/templates/yacht-details-v3.php**
   - Added weekly price splitting logic (lines 42-80)
   - Maintains all original price data (product, currency, discount)
   - Handles edge cases (partial weeks, short periods)

2. **yolo-yacht-search.php**
   - Version bump to 1.5.9 (lines 6 and 23)

---

## 🔄 Upgrade Instructions

### From v1.5.8 to v1.5.9:

1. **Backup Current Site**
   ```bash
   wp db export backup-$(date +%Y%m%d).sql
   cp -r wp-content/plugins/yolo-yacht-search backup/
   ```

2. **Deactivate & Remove Old Version**
   - Go to Plugins → Installed Plugins
   - Deactivate "YOLO Yacht Search & Booking"
   - Delete plugin

3. **Install New Version**
   - Upload `yolo-yacht-search-v1.5.9.zip`
   - Activate plugin

4. **Verify Functionality**
   - Visit a yacht details page (e.g., Lemon yacht)
   - Scroll to "Peak Season Pricing" section
   - **Expected:** See multiple weekly price cards (not just one)
   - **Expected:** Navigation arrows work to show more weeks
   - Click "Select This Week" button to test date picker integration

5. **No Database Changes Required**
   - This is a display-only change
   - Existing price data works perfectly
   - No need to re-sync prices

---

## ⚠️ Known Issues (Carried Forward)

### 1. Yacht Sync May Be Broken
**Status:** ⚠️ NOT FIXED - Needs investigation  
**Reported:** User mentioned yacht sync stopped working  
**Priority:** HIGH - Should be fixed in next version  

### 2. Google Maps API Key
**Status:** ✅ FIXED in v1.5.8  
**Solution:** Now configurable in admin settings  

### 3. Success Message Timeout
**Status:** ✅ FIXED in v1.5.8  
**Solution:** Increased from 2 to 5 seconds  

---

## 🎯 Next Steps

1. **Test on live site** with real users
2. **Investigate yacht sync issue** (if still broken)
3. **Implement search backend** (still top priority)
4. **Consider adding:**
   - Price calendar view (alternative to carousel)
   - Price comparison between yachts
   - Seasonal pricing charts

---

## 📊 Version Comparison

| Feature | v1.5.8 | v1.5.9 |
|---------|--------|--------|
| Monthly price display | ✅ | ✅ |
| Weekly price splitting | ❌ | ✅ |
| Carousel shows 1 card | ❌ | ✅ (shows ~22 cards) |
| Navigation works | ⚠️ (limited) | ✅ (full navigation) |
| User can select weeks | ❌ | ✅ |

---

## 💡 How It Works

### Price Splitting Algorithm

1. **Get monthly price from database** (e.g., May 1-31, 15,114 EUR)
2. **Calculate period length** (31 days)
3. **Check if > 7 days** (yes, 31 > 7)
4. **Split into 7-day chunks:**
   - Week 1: May 1-7 (7 days)
   - Week 2: May 8-14 (7 days)
   - Week 3: May 15-21 (7 days)
   - Week 4: May 22-28 (7 days)
   - Week 5: May 29-31 (3 days - partial week)
5. **Each chunk gets same price** (15,114 EUR)
6. **Preserve all metadata** (discount %, start price, currency)

### Edge Cases Handled

✅ **Partial weeks:** Last week of month may be < 7 days  
✅ **Short periods:** Periods already ≤ 7 days kept as-is  
✅ **Date boundaries:** Never exceed original end date  
✅ **Time preservation:** Maintains H:i:s from original dates  

---

**Generated:** November 28, 2025 23:30 GMT+2
