# Price Data Explanation

## What We Get from the API

The `/prices` endpoint returns **4 price fields** for each yacht:

### 1. **`price`** - Final Price (After Discount)
- This is the **actual price** customers pay
- Already includes any discounts applied
- **This is what we display** on yacht cards

### 2. **`startPrice`** - Original Price (Before Discount)
- The original/base price before any discounts
- Used to show "was €X, now €Y" comparisons

### 3. **`discountPercentage`** - Discount Applied
- Percentage discount (e.g., 3.77%, 5%)
- Can be 0 if no discount

### 4. **`currency`** - Currency
- Always "EUR" for your yachts

---

## Example Price Data

### Example 1: Crewed Yacht with 3.77% Discount
```json
{
  "yachtId": 2726600687501682,
  "dateFrom": "2026-05-01 00:00:00",
  "dateTo": "2026-12-31 23:59:59",
  "product": "Crewed",
  "price": 908352,           ← Final price (what customer pays)
  "currency": "EUR",
  "startPrice": 943943,      ← Original price
  "discountPercentage": 3.77 ← 3.77% discount
}
```

**Calculation:**
- Original: €943,943
- Discount: 3.77% = €35,591
- **Final: €908,352** ✅ (This is what we show)

---

### Example 2: Bareboat with No Discount
```json
{
  "yachtId": 6007661790000100000,
  "dateFrom": "2026-05-01 00:00:00",
  "dateTo": "2026-12-31 23:59:59",
  "product": "Bareboat",
  "price": 130641,           ← Final price
  "currency": "EUR",
  "startPrice": 130640.53,   ← Original price (basically same)
  "discountPercentage": 0    ← No discount
}
```

**Calculation:**
- Original: €130,641
- Discount: 0%
- **Final: €130,641** ✅

---

## What We Store in Database

We store **ALL 4 fields**:

```sql
CREATE TABLE wp_yolo_yacht_prices (
    price decimal(10,2) NOT NULL,              ← Final price
    start_price decimal(10,2) DEFAULT NULL,    ← Original price
    discount_percentage decimal(5,2) DEFAULT NULL, ← Discount %
    currency varchar(10) NOT NULL              ← Currency
);
```

---

## What We Display

### Current Display (Phase 1):
**"From €908,352 per week"**

- Shows the **`price`** field (final price after discount)
- Uses minimum price across all date ranges
- Format: `number_format($price, 0, ',', '.')`

### Future Display Options (Phase 2+):

**Option A: Show discount**
```
From €908,352 per week
Was €943,943 (Save 3.77%)
```

**Option B: Strikethrough**
```
From €943,943 €908,352 per week
     ^^^^^^^^ (crossed out)
```

**Option C: Badge**
```
From €908,352 per week  [3.77% OFF]
```

---

## Important Notes

### ✅ We DO Calculate:
- **Minimum price** - Find cheapest week for each yacht
- **Currency formatting** - Add thousand separators

### ❌ We DON'T Calculate:
- **Discount amount** - API already provides it
- **Final price** - API already provides it
- **Percentage** - API already provides it

**The API does all the math for us!** We just store and display it.

---

## Price Ranges

From the test data (85 entries), prices range from:

- **Minimum:** €11,293 (Bareboat)
- **Maximum:** €1,260,841 (Crewed luxury yacht)
- **Average:** ~€300,000-400,000

---

## What "From €XXX per week" Means

The price shown is:
- **Minimum weekly price** for that yacht
- Across **all available dates** in next 12 months
- For **any charter type** (Bareboat or Crewed)
- **After discounts** applied

Example:
```
Strawberry yacht has:
- May 2026: €2,800/week
- June 2026: €3,200/week
- July 2026: €4,500/week (high season)
- August 2026: €4,800/week

We show: "From €2,800 per week"
```

---

## Database Query for Minimum Price

```php
SELECT MIN(price) as min_price, currency 
FROM wp_yolo_yacht_prices 
WHERE yacht_id = '7175166040000000001' 
AND date_from >= NOW()
GROUP BY currency
ORDER BY min_price ASC
LIMIT 1
```

This gives us the **cheapest available week** for that yacht.

---

## Summary

| Field | What It Is | Do We Store? | Do We Display? |
|-------|-----------|--------------|----------------|
| `price` | Final price (after discount) | ✅ Yes | ✅ Yes (on cards) |
| `startPrice` | Original price (before discount) | ✅ Yes | ❌ Not yet |
| `discountPercentage` | Discount % | ✅ Yes | ❌ Not yet |
| `currency` | Currency code | ✅ Yes | ✅ Yes |

**Current display:** "From €908,352 per week"  
**Future enhancement:** Show original price + discount badge
