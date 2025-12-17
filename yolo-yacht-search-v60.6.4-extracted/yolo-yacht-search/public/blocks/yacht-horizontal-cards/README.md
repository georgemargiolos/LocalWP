# YOLO Horizontal Yacht Cards Block

## Overview

A WordPress Gutenberg block that displays YOLO company yachts in a horizontal card layout with image carousel, complete yacht information, and responsive design.

## Features

✅ **Horizontal Layout** - Image carousel on left, yacht info on right  
✅ **Swiper Carousel** - Multiple images per yacht with navigation and pagination  
✅ **Complete Yacht Info** - All data from "Our Yachts" section  
✅ **YOLO Fleet Badge** - Automatic badge overlay on carousel  
✅ **Responsive Design** - Stacks vertically on mobile devices  
✅ **Bootstrap Compatible** - Uses existing Bootstrap grid system  
✅ **WordPress Fonts** - Inherits theme typography  

## Block Information

- **Block Name:** `yolo-ys/horizontal-yacht-cards`
- **Title:** YOLO Horizontal Yacht Cards
- **Category:** Widgets
- **Icon:** Slides

## Files Created

```
public/blocks/yacht-horizontal-cards/
├── block.json           # Block metadata and configuration
├── index.js            # Block editor registration
├── editor.css          # Editor-only styles
├── style.css           # Frontend styles (responsive)
├── render.php          # Server-side rendering template
└── README.md           # This file
```

## Yacht Information Displayed

Each horizontal card includes:

1. **Image Carousel** (left side)
   - All yacht images with Swiper navigation
   - Auto-play with 5-second delay
   - Pagination dots
   - Previous/Next buttons
   - YOLO FLEET badge overlay

2. **Yacht Details** (right side)
   - Location (with pin emoji)
   - Yacht name (large, bold)
   - Model name (blue)
   - Description (truncated to 30 words)
   - Specifications:
     - Cabins count
     - WC/Heads count
     - Year built
     - Refit year (if available)
     - Length (in feet)
   - Price (from X EUR per week)
   - DETAILS button (links to yacht details page)

## Responsive Behavior

### Desktop (> 992px)
- Horizontal layout: 45% image, 55% content
- Image height: 400px
- Specs displayed in horizontal row

### Tablet (768px - 992px)
- Horizontal layout: 40% image, 60% content
- Image height: 350px
- Slightly smaller fonts

### Mobile (< 768px)
- **Stacked layout** (vertical)
- Image on top (280px height)
- Content below
- Full-width details button
- Price and button stack vertically

### Small Mobile (< 480px)
- Image height: 240px
- Tighter spacing
- Smaller fonts
- Optimized for small screens

## How to Use

### 1. Add Block in WordPress Editor

1. Edit any page or post
2. Click the "+" button to add a block
3. Search for "YOLO Horizontal Yacht Cards"
4. Click to insert the block
5. Save/Publish the page

### 2. Block Displays Automatically

The block automatically:
- Fetches all YOLO company yachts
- Displays them in horizontal cards
- Initializes Swiper carousels
- Applies responsive styling

### 3. No Configuration Needed

The block works out of the box with:
- YOLO company ID from plugin settings
- Yacht details page from plugin settings
- All yacht data from database
- Existing Swiper and Bootstrap libraries

## Dependencies

### Already Loaded by Plugin

✅ **Bootstrap 5** - Grid system and utilities  
✅ **Swiper 11.0.0** - Carousel functionality  
✅ **WordPress Theme Fonts** - Typography inheritance

### No Additional Libraries Needed

The block uses existing plugin dependencies, so no additional libraries need to be loaded.

## Technical Details

### Swiper Configuration

Each yacht carousel is initialized with:
- **Loop:** true (infinite loop)
- **Autoplay:** 5 seconds delay
- **Pagination:** Clickable dots
- **Navigation:** Previous/Next buttons
- **Responsive:** Touch-friendly on mobile

### CSS Architecture

- **Mobile-first design** - Base styles for mobile, enhanced for desktop
- **Bootstrap compatible** - Uses Bootstrap grid classes
- **Theme inheritance** - Fonts inherit from WordPress theme
- **No font-family declarations** - All typography from theme

### Server-Side Rendering

The block uses server-side rendering (PHP) to:
- Query yacht data from database
- Generate HTML with yacht information
- Initialize Swiper for each yacht
- Ensure SEO-friendly content

## Browser Support

- ✅ Chrome/Edge (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Lazy loading** - Images load as needed
- **Local assets** - Swiper loaded from plugin (no CDN)
- **Minimal JavaScript** - Only Swiper initialization
- **Optimized CSS** - Single stylesheet, mobile-first

## Customization

### Modify Carousel Settings

Edit `render.php` lines 136-147 to change:
- Autoplay delay
- Loop behavior
- Navigation visibility
- Pagination style

### Modify Styling

Edit `style.css` to customize:
- Card dimensions
- Colors and shadows
- Spacing and padding
- Responsive breakpoints

### Modify Content

Edit `render.php` to change:
- Displayed yacht information
- Description word limit (line 97)
- Specs order and layout
- Button text and styling

## Troubleshooting

### Block Not Appearing in Editor

1. Clear WordPress cache
2. Regenerate block registration: `wp-cli block list`
3. Check browser console for JavaScript errors

### Carousel Not Working

1. Verify Swiper JS is loaded: Check browser console
2. Check for JavaScript conflicts with other plugins
3. Ensure yacht has multiple images in database

### Styling Issues

1. Clear browser cache
2. Check for theme CSS conflicts
3. Verify Bootstrap is loaded
4. Inspect element to see applied styles

### No Yachts Displayed

1. Verify YOLO company ID in plugin settings
2. Check yacht data in database
3. Ensure yachts belong to YOLO company
4. Check PHP error logs

## Version History

- **v1.0** (2024-12-09) - Initial release
  - Horizontal card layout
  - Swiper carousel integration
  - Full responsive design
  - Bootstrap compatible

## Support

For issues or questions:
1. Check WordPress debug.log for PHP errors
2. Check browser console for JavaScript errors
3. Verify plugin settings are correct
4. Test with default WordPress theme
