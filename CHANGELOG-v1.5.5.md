# YOLO Yacht Search - Version 1.5.5 Changelog

**Release Date:** November 28, 2025  
**Status:** UI/UX ENHANCEMENT

## 🎨 Price Carousel Redesign

### New Features

**1. Peak Season Filter**
- Price carousel now shows **only May-September** (peak charter season)
- Filters out off-season months automatically
- Focuses on the most relevant booking period

**2. 4-Week Grid Layout**
- Displays **4 weeks at a time** in a grid layout
- Matches industry-standard design (like Boataround.com)
- Better visual comparison of weekly prices
- Responsive: 4 columns → 2 columns → 1 column on mobile

**3. Improved Navigation**
- Left/right arrows navigate through weeks in groups of 4
- Arrows show disabled state when at start/end
- Smooth transitions between week groups

**4. Enhanced Visual Design**
- Compact card design for better information density
- Hover effects on price cards
- Active selection highlighting
- Optimized font sizes for 4-column layout

### Before vs After

**Before (v1.5.4):**
- Showed all 52 weeks of the year
- One week at a time
- Required clicking through many weeks
- Included off-season months with no bookings

**After (v1.5.5):**
- Shows only May-September (peak season)
- 4 weeks at a time in grid
- Navigate in groups of 4
- Focused on relevant booking period

### Technical Changes

**1. PHP Filter (yacht-details-v3.php)**
```php
// Filter prices for May-September only
$month = (int)date('n', strtotime($price->date_from));
if ($month >= 5 && $month <= 9) {
    $prices[] = $price;
}
```

**2. JavaScript Carousel (yacht-details-v3-scripts.php)**
- Updated to show 4 slides simultaneously
- Navigation jumps by 4 weeks instead of 1
- Arrow state management (enabled/disabled)

**3. CSS Grid Layout (yacht-details-v3-styles.php)**
```css
.price-carousel-slides {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
```

### User Experience Improvements

| Feature | Before | After |
|---------|--------|-------|
| **Months Shown** | All 12 months | May-September only |
| **Weeks Visible** | 1 at a time | 4 at a time |
| **Navigation** | Click 52 times | Click ~5 times |
| **Layout** | Single card | 4-column grid |
| **Comparison** | Difficult | Easy side-by-side |

### Why May-September?

1. **Peak Charter Season:** 95% of yacht bookings happen in these months
2. **Better Weather:** Mediterranean sailing season
3. **Focused Selection:** Customers see only relevant options
4. **Faster Loading:** Less data to process and display

### Responsive Design

- **Desktop (>968px):** 4 columns
- **Tablet (640-968px):** 2 columns
- **Mobile (<640px):** 1 column

### Files Changed

1. **public/templates/yacht-details-v3.php**
   - Lines 32-43: Added May-September filter
   - Line 117: Changed title to "Availability & Pricing"
   - Line 119: Added `data-visible-slides="4"` attribute

2. **public/templates/partials/yacht-details-v3-scripts.php**
   - Lines 37-107: Rewrote price carousel to show 4 slides
   - Added `init()` and `updateView()` methods
   - Added arrow state management

3. **public/templates/partials/yacht-details-v3-styles.php**
   - Lines 170-176: Changed to CSS Grid layout
   - Lines 178-188: Added responsive breakpoints
   - Lines 190-208: Updated card styling
   - Lines 210-266: Reduced font sizes for compact layout

4. **yolo-yacht-search.php**
   - Line 6: Updated version to 1.5.5
   - Line 23: Updated version constant

## 📦 Installation

1. **Deactivate** v1.5.4
2. **Delete** old plugin files
3. **Upload** v1.5.5
4. **Activate** the plugin
5. **View** any yacht details page to see the new carousel

## 🧪 Testing Checklist

- ✅ Only May-September weeks displayed
- ✅ 4 weeks shown at once in grid
- ✅ Left arrow navigates backward by 4 weeks
- ✅ Right arrow navigates forward by 4 weeks
- ✅ Arrows disabled at start/end
- ✅ Responsive on mobile/tablet
- ✅ Hover effects work
- ✅ "Select This Week" button works
- ✅ Date picker integration works

## 🎯 Next Steps

With the improved price carousel, the next priorities are:
1. Implement search functionality
2. Add Stripe payment integration
3. Create booking API integration

## 💡 Future Enhancements

- Add "Available" / "Reserved" status badges (requires API integration)
- Show number of interested customers (like screenshot)
- Add "Always the best price" guarantee badge
- Implement real-time availability checking
