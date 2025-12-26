# Free Amenities Feature - Implementation Guide

## Overview

Display FREE amenities (SUP, snorkeling equipment, outboard engine, etc.) on yacht details pages for YOLO boats only, with customizable HTML descriptions per amenity type.

## New Admin Page

**Location:** WordPress Admin → YOLO Yacht Search → **Free Amenities**

### Settings Available

1. **Section Title** - Customizable heading (e.g., "🎁 FREE AMENITIES – Because Every Summer is YOLO")

2. **Section Introduction** - HTML intro text below the title

3. **Amenity HTML Descriptions** - Custom HTML for each type:
   - Free SUP (Stand Up Paddle Board)
   - Free Snorkeling Equipment
   - Free Outboard Engine
   - Free Floating Stern Lines
   - Free Kayak

4. **Per-Yacht Assignment** - Checkbox grid to assign amenities to each YOLO yacht

### Default Amenities Content

```html
<!-- Free SUP -->
<div class="amenity-item">
    <i class="fa-solid fa-person-swimming"></i>
    <strong>Free SUP (Stand Up Paddle Board)</strong>
    <p>Enjoy exploring the crystal-clear Greek waters with our complimentary 
    SUP board included with your charter.</p>
</div>

<!-- Free Snorkeling -->
<div class="amenity-item">
    <i class="fa-solid fa-mask-snorkel"></i>
    <strong>Free Snorkeling Equipment</strong>
    <p>Discover underwater wonders with our complimentary masks, snorkels, 
    and fins provided for all guests.</p>
</div>

<!-- Free Outboard Engine -->
<div class="amenity-item">
    <i class="fa-solid fa-anchor"></i>
    <strong>Free Outboard Engine</strong>
    <p>Your dinghy comes with a free outboard engine for easy shore 
    excursions and exploring secluded coves.</p>
</div>

<!-- Free Stern Lines -->
<div class="amenity-item">
    <i class="fa-solid fa-rope"></i>
    <strong>Free Floating Stern Lines</strong>
    <p>Professional floating stern lines included for safe and easy 
    mooring in Greek harbors and bays.</p>
</div>

<!-- Free Kayak -->
<div class="amenity-item">
    <i class="fa-solid fa-ship"></i>
    <strong>Free Kayak</strong>
    <p>Explore coastlines and hidden beaches with our complimentary 
    kayak included with your charter.</p>
</div>
```

## Frontend Display

### Location
- Shows on yacht details page (`yacht-details-v3.php`)
- Appears AFTER the "Extras" section
- BEFORE the "Security Deposit" section
- Only visible for YOLO boats (company ID 7850)

### CSS Classes

```css
.free-amenities-section          /* Main container */
.free-amenities-section h3       /* Section title */
.free-amenities-intro            /* Intro text */
.free-amenities-grid             /* Grid of amenities */
.free-amenity-item               /* Individual amenity card */
.amenity-item                    /* Inner content wrapper */
```

### Styling (Green theme)

```css
.free-amenities-section {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 2px solid #10b981;
    border-radius: var(--yolo-radius-lg);
    padding: clamp(24px, 5vw, 32px);
}

.free-amenities-section h3 {
    color: #047857;
}

.free-amenity-item {
    background: white;
    border: 1px solid #a7f3d0;
    border-radius: var(--yolo-radius-md);
    padding: 16px;
    transition: all 0.2s ease;
}

.free-amenity-item:hover {
    border-color: #10b981;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}
```

## WordPress Options Stored

| Option Name | Description |
|-------------|-------------|
| `yolo_ys_free_amenities_title` | Section title |
| `yolo_ys_free_amenities_intro` | Section intro HTML |
| `yolo_ys_amenity_html_free_sup` | SUP description HTML |
| `yolo_ys_amenity_html_free_snorkeling` | Snorkeling description HTML |
| `yolo_ys_amenity_html_free_outboard` | Outboard engine description HTML |
| `yolo_ys_amenity_html_free_stern_lines` | Stern lines description HTML |
| `yolo_ys_amenity_html_free_kayak` | Kayak description HTML |
| `yolo_ys_yacht_amenities` | Array of yacht ID => amenities enabled |

## Files Created/Modified

### New Files
- `admin/partials/free-amenities-page.php` - Admin settings page

### Modified Files
- `admin/class-yolo-ys-admin.php` - Added menu item and display function
- `public/templates/yacht-details-v3.php` - Added frontend display section
- `public/css/yacht-details-v3.css` - Added styling

## API Data Source

Free amenities can be auto-detected from the Booking Manager API:

1. **`comment` field** - Comma-separated text like "Free SUP, Free Snorkeling Equipment"
2. **`products[].extras[]`** - Obligatory extras with "free" in name/description
3. **`description` field** - May contain free amenity mentions

### Detection Keywords

| Amenity | Keywords |
|---------|----------|
| Free SUP | `free sup`, `free stand up paddle` |
| Free Snorkeling | `free snorkeling`, `snorkeling equipment` |
| Free Outboard | `free outboard`, `outboard engine` |
| Free Stern Lines | `free floating`, `stern lines`, `floating lines` |
| Free Kayak | `free kayak` |

## Usage

### For Strawberry, Lemon, Aquilo

1. Go to **YOLO YS → Free Amenities**
2. Write custom HTML for each amenity type
3. Check which amenities each yacht has in the grid
4. Save changes
5. View yacht details page to see the section

### Example Assignment

| Yacht | SUP | Snorkeling | Outboard | Stern Lines | Kayak |
|-------|-----|------------|----------|-------------|-------|
| Strawberry | ✅ | ✅ | ✅ | ✅ | ❌ |
| Lemon | ✅ | ✅ | ✅ | ✅ | ✅ |
| Aquilo | ✅ | ✅ | ✅ | ✅ | ❌ |

## Preview

The admin page includes a **live preview** showing how the section will appear on the frontend with current settings.
