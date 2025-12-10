# YOLO Custom Gutenberg Blocks - Tracking & Documentation

**Last Updated:** December 10, 2025 GMT+2  
**Total Blocks:** 3  
**Status:** ✅ Production Ready

---

## 📋 Overview

This document tracks all custom Gutenberg blocks developed for the YOLO Yacht Charters website. Each block is designed to integrate seamlessly with the YOLO Yacht Search & Booking Plugin and WordPress theme, providing specialized functionality for displaying yacht information and blog content.

**Key Principles:**
- All blocks are standalone plugins (not bundled in main plugin)
- Server-side rendering for better SEO and performance
- Theme font inheritance (no font overrides)
- Fully responsive designs
- Front page support enabled
- No JSX syntax (browser-compatible JavaScript)

---

## 🚢 Block 1: YOLO Horizontal Yacht Cards

### Basic Information

| Property | Value |
|----------|-------|
| **Block Name** | YOLO Horizontal Yacht Cards |
| **Namespace** | yolo-yacht-search/yacht-horizontal-cards |
| **Version** | 50.0 |
| **Plugin Name** | YOLO Horizontal Yacht Cards Block |
| **Status** | ✅ Active & Production Ready |
| **Created** | December 9, 2025 |
| **Last Updated** | December 9, 2025 |

### Description

Displays YOLO company yachts in beautiful horizontal card layouts with image carousels and comprehensive yacht information. Each card features a Swiper.js-powered image carousel on the left and detailed yacht specifications on the right, with full responsiveness for mobile devices.

### Features

**Visual Features:**
- Image carousel with Swiper.js integration
- Logo overlay on images (max 100px width on desktop, 60px on mobile)
- Horizontal layout on desktop (50% carousel | 50% info)
- Stacked layout on mobile (carousel on top, info below)
- Smooth carousel transitions with navigation arrows
- Professional card design with proper spacing

**Content Features:**
- Yacht name with location (separated by |)
- Yacht specifications (length, cabins, guests, crew, year)
- 100-word description preview with "Read more..." link
- Pricing information (weekly rate)
- Location link to yacht details map section (#yacht-map-section)
- "View Details" button linking to full yacht page

**Technical Features:**
- Server-side rendering via render.php
- Database query for YOLO company yachts only
- Theme font inheritance (no font overrides)
- Fully responsive CSS
- Front page support (priority 999 filter)
- Manual editor script enqueuing for reliability

### Usage

**Installation:**
1. Upload `yolo-horizontal-yacht-cards-v50.0.zip` to WordPress
2. Activate plugin via Plugins → Installed Plugins
3. Ensure YOLO Yacht Search & Booking Plugin v50.0+ is active

**Adding to Page/Post:**
1. Edit page/post in Gutenberg editor
2. Click "+" to add new block
3. Search for "YOLO Horizontal Yacht Cards" or "yacht"
4. Click block to insert
5. Block automatically displays all YOLO yachts
6. No settings required (displays all YOLO yachts by default)

**Block Settings:**
- None (block displays all YOLO company yachts automatically)

### Technical Specifications

**Database Query:**
```php
global $wpdb;
$table_name = $wpdb->prefix . 'yolo_yachts';
$yachts = $wpdb->get_results(
    "SELECT * FROM $table_name WHERE company = 'YOLO' ORDER BY name ASC"
);
```

**File Structure:**
```
yolo-horizontal-yacht-cards/
├── yolo-horizontal-yacht-cards.php    # Main plugin file
├── block.json                         # Block metadata
├── render.php                         # Server-side rendering
├── index.js                           # Block registration (no JSX)
├── style.css                          # Frontend styles
├── editor.css                         # Editor styles
└── README.md                          # Block documentation
```

**Dependencies:**
- WordPress 5.8+
- YOLO Yacht Search & Booking Plugin v50.0+
- Swiper.js (loaded from CDN)
- wp_yolo_yachts database table

**Responsive Breakpoints:**
- Desktop (>768px): Horizontal layout
- Mobile (≤768px): Stacked layout
- Logo: 100px (desktop) → 60px (mobile)

### Customization Options

**Available Customizations:**
- None currently (displays all YOLO yachts)

**Future Customization Ideas:**
- Filter by yacht type (catamaran, monohull)
- Filter by number of cabins
- Sort order (name, price, year)
- Number of yachts to display
- Show/hide specific fields

### Known Issues & Limitations

**Limitations:**
- Only displays YOLO company yachts (partner yachts excluded by design)
- No pagination (displays all yachts)
- Requires YOLO Yacht Search & Booking Plugin
- Requires wp_yolo_yachts database table

**Known Issues:**
- None currently

### Version History

| Version | Date | Changes |
|---------|------|---------|
| 50.0 | Dec 9, 2025 | Initial release with full functionality |

### Package Location

**File:** `/home/ubuntu/yolo-horizontal-yacht-cards-v50.0.zip`  
**Size:** ~50KB  
**Install:** WordPress → Plugins → Add New → Upload

---

## 📝 Block 2: YOLO Blog Posts Grid

### Basic Information

| Property | Value |
|----------|-------|
| **Block Name** | YOLO Blog Posts Grid |
| **Namespace** | yolo-blog-posts/blog-posts-grid |
| **Version** | 1.0.0 |
| **Plugin Name** | YOLO Blog Posts Block |
| **Status** | ✅ Active & Production Ready |
| **Created** | December 9, 2025 |
| **Last Updated** | December 9, 2025 |

### Description

Displays recent blog posts in a responsive 3-column grid with featured images, category badges, and customizable post count. Perfect for showcasing blog content on the homepage or any page/post.

### Features

**Visual Features:**
- 3-column grid layout (responsive: 3→2→1 columns)
- Featured images with hover zoom effect (scale 1.05)
- Category badges with automatic color coding
- Rounded cards with 16px border-radius
- Box-shadow on hover for depth
- Professional spacing and padding
- #1572F5 button color (brand color)

**Content Features:**
- Post featured image
- Post title (linked to post)
- Category badges (hides "Uncategorized")
- Post excerpt (55 words)
- "Read More" button
- Adjustable post count (1-12 posts)

**Technical Features:**
- Server-side rendering via render.php
- WP_Query for post retrieval
- RangeControl for post count adjustment
- Theme font inheritance (no font overrides)
- Fully responsive CSS Grid
- Front page support (priority 999 filter)
- Links open in same page (no target="_blank")

### Usage

**Installation:**
1. Upload `yolo-blog-posts-block-v1.0.0.zip` to WordPress
2. Activate plugin via Plugins → Installed Plugins
3. No other dependencies required

**Adding to Page/Post:**
1. Edit page/post in Gutenberg editor
2. Click "+" to add new block
3. Search for "YOLO Blog Posts" or "blog"
4. Click block to insert
5. Adjust post count in block settings (right sidebar)
6. Preview and publish

**Block Settings:**
- **Post Count:** 1-12 posts (default: 6)
  - Adjust via RangeControl slider in block settings panel

### Technical Specifications

**WP_Query:**
```php
$args = array(
    'post_type' => 'post',
    'posts_per_page' => $attributes['postCount'],
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
);
$query = new WP_Query($args);
```

**File Structure:**
```
yolo-blog-posts-block/
├── yolo-blog-posts-block.php          # Main plugin file
├── block.json                         # Block metadata
├── render.php                         # Server-side rendering
├── index.js                           # Block registration with RangeControl
├── style.css                          # Frontend styles
├── editor.css                         # Editor styles
└── README.md                          # Block documentation
```

**Dependencies:**
- WordPress 5.8+
- Published blog posts with featured images (recommended)

**Responsive Breakpoints:**
- Desktop (>992px): 3 columns
- Tablet (768px-992px): 2 columns
- Mobile (≤768px): 1 column

### Customization Options

**Available Customizations:**
- **Post Count:** 1-12 posts (via RangeControl)

**Future Customization Ideas:**
- Category filter (show posts from specific category)
- Tag filter
- Sort order (date, title, random)
- Excerpt length adjustment
- Show/hide featured image
- Show/hide category badges
- Show/hide excerpt
- Custom button text
- Custom button color

### Known Issues & Limitations

**Limitations:**
- Maximum 12 posts (can be increased if needed)
- No pagination (displays set number of posts)
- Requires featured images for best appearance
- Automatically hides "Uncategorized" category

**Known Issues:**
- None currently

### Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Dec 9, 2025 | Initial release with adjustable post count |

### Package Location

**File:** `/home/ubuntu/yolo-blog-posts-block-v1.0.0.zip`  
**Size:** ~30KB  
**Install:** WordPress → Plugins → Add New → Upload

---

## 📰 Block 3: YOLO Horizontal Blog Posts

### Basic Information

| Property | Value |
|----------|-------|
| **Block Name** | YOLO Horizontal Blog Posts |
| **Namespace** | yolo-blog-posts/horizontal-blog-posts |
| **Version** | 1.0.0 |
| **Plugin Name** | YOLO Horizontal Blog Posts Block |
| **Status** | ✅ Active & Production Ready |
| **Created** | December 10, 2025 |
| **Last Updated** | December 10, 2025 |

### Description

Displays blog posts in a horizontal card layout with featured image on the left and content on the right. Perfect for blog pages where you want a more detailed, article-style presentation of posts.

### Features

**Visual Features:**
- Horizontal card layout (image left, content right)
- Featured image (300px width on desktop)
- Stacked layout on mobile (image top, content below)
- White cards with subtle shadow and hover effects
- Image zoom on hover (scale 1.05)
- Min-height: 250px for consistent card heights
- #1572F5 button color (brand color)

**Content Features:**
- Post featured image with fallback
- Post title (linked to post)
- Full post content excerpt (up to 500 words)
- Clickable "Read more..." link in excerpt
- "Read More" button at bottom
- Adjustable post count (1-20 posts)

**Technical Features:**
- Server-side rendering via render.php
- Uses get_the_content() for full content (not WordPress excerpt)
- 500-word maximum to fill space properly
- Theme font inheritance (no font overrides)
- Fully responsive CSS with flexbox
- No front page filter (works on all pages by default)
- Manual editor script enqueuing for reliability

### Usage

**Installation:**
1. Upload `yolo-horizontal-blog-posts-v1.0.0.zip` to WordPress
2. Activate plugin via Plugins → Installed Plugins
3. No other dependencies required

**Adding to Page/Post:**
1. Edit page/post in Gutenberg editor
2. Click "+" to add new block
3. Search for "YOLO Horizontal Blog Posts" or "blog"
4. Click block to insert
5. Adjust post count in block settings (right sidebar)
6. Preview and publish

**Block Settings:**
- **Post Count:** 1-20 posts (default: 10)
  - Adjust via RangeControl slider in block settings panel

### Technical Specifications

**WP_Query:**
```php
$args = array(
    'post_type' => 'post',
    'posts_per_page' => $post_count,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'DESC'
);
$query = new WP_Query($args);
```

**Content Extraction:**
```php
// Get the full content (not excerpt which is limited by WordPress)
$content = get_the_content();
$content = strip_tags($content);
$content = strip_shortcodes($content);
$content = wp_strip_all_tags($content);

// Limit to maximum 500 words to fill the space
$words = explode(' ', $content);
if (count($words) > 500) {
    $words = array_slice($words, 0, 500);
    $content = implode(' ', $words);
}
```

**File Structure:**
```
yolo-horizontal-blog-posts/
├── yolo-horizontal-blog-posts.php     # Main plugin file
├── block.json                         # Block metadata
├── render.php                         # Server-side rendering
├── index.js                           # Block registration (no JSX)
├── style.css                          # Frontend styles
├── editor.css                         # Editor styles
└── README.md                          # Block documentation
```

**Dependencies:**
- WordPress 5.8+
- Published blog posts with featured images (recommended)

**Responsive Breakpoints:**
- Desktop (>992px): Full horizontal layout (300px image + flex content)
- Tablet (768px-992px): Compact horizontal (200px image + flex content)
- Mobile (≤768px): Stacked vertical layout
- Extra Small (≤480px): Optimized spacing

### Customization Options

**Available Customizations:**
- **Post Count:** 1-20 posts (via RangeControl)

**Future Customization Ideas:**
- Category filter
- Tag filter
- Sort order (date, title, random)
- Show/hide featured image
- Show/hide "Read More" button
- Custom button text
- Custom button color
- Adjustable image width

### Known Issues & Limitations

**Limitations:**
- Maximum 20 posts (can be increased if needed)
- No pagination (displays set number of posts)
- Requires featured images for best appearance
- 500-word excerpt maximum (prevents overly long cards)

**Known Issues:**
- None currently

**Critical Fixes Applied:**
- v1.0.4: Removed problematic allowed_block_types_all filter that blocked all other blocks
- v1.0.9: Fixed excerpt to use get_the_content() instead of get_the_excerpt() to properly fill white space

### Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Dec 10, 2025 | Stable release with full content extraction and proper space filling |

### Package Location

**File:** `/home/ubuntu/LocalWP/yolo-horizontal-blog-posts/`  
**Zip:** `yolo-horizontal-blog-posts-v1.0.0.zip`  
**Size:** ~8KB  
**Install:** WordPress → Plugins → Add New → Upload

---

## 🎨 Design Guidelines

### Color Palette

**All Blocks:**
- Button Color: #1572F5 (brand blue)
- Button Hover: #0d5fd4 (darker blue)
- Card Background: White (#ffffff)
- Text: Inherits from theme
- Links: #1572F5

**Horizontal Yacht Cards:**
- All colors inherit from WordPress theme
- Logo overlay: White background with subtle shadow

### Typography

**Critical Rule:** All fonts and font sizes MUST inherit from WordPress theme

**Implementation:**
- No font-family declarations in block CSS
- No font-size declarations in block CSS
- WordPress theme controls all typography
- Blocks adapt to any theme automatically

### Spacing & Layout

**Horizontal Yacht Cards:**
- Card padding: 20px
- Gap between carousel and info: 30px
- Margin between cards: 30px

**Blog Posts Grid:**
- Grid gap: 30px
- Card padding: 20px
- Margin bottom: 30px

**Horizontal Blog Posts:**
- Card padding: 0 (image edge-to-edge)
- Content padding: 30px
- Gap between image and content: 0
- Margin between cards: 30px
- Min-height: 250px

### Responsive Design

**Mobile-First Approach:**
- Start with mobile layout
- Add desktop enhancements via media queries
- Test on all device sizes
- Ensure touch interactions work

**Breakpoints:**
- Mobile: 0-767px
- Tablet: 768px-991px
- Desktop: 992px+
- Extra Small: 0-480px (for horizontal blog posts)

---

## 🚀 Deployment Guide

### Installation Steps

**For All Blocks:**
1. Download the block ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin"
4. Choose the ZIP file
5. Click "Install Now"
6. Click "Activate"
7. Block is ready to use in Gutenberg editor

### Testing Checklist

**Before Deployment:**
- [ ] Test on desktop (Chrome, Firefox, Safari)
- [ ] Test on tablet (iPad, Android tablet)
- [ ] Test on mobile (iPhone, Android phone)
- [ ] Verify all links work correctly
- [ ] Check hover effects
- [ ] Verify theme font inheritance
- [ ] Test with different post counts
- [ ] Check with and without featured images
- [ ] Verify responsive breakpoints
- [ ] Test in Gutenberg editor
- [ ] Test on front page (if applicable)
- [ ] Check for console errors
- [ ] Verify database queries work

### Troubleshooting

**Block Not Appearing:**
- Ensure plugin is activated
- Clear browser cache
- Refresh Gutenberg editor
- Check for JavaScript errors in console

**Styling Issues:**
- Clear WordPress cache
- Clear browser cache
- Check for CSS conflicts with theme
- Verify no font overrides in block CSS

**Content Not Displaying:**
- Check database connection
- Verify required tables exist
- Check post status (must be "publish")
- Verify featured images are set

**All Blocks Disappearing (Horizontal Blog Posts specific):**
- This was caused by the allowed_block_types_all filter
- Fixed in v1.0.4 by removing the filter
- Block now uses standard WordPress registration

---

## 📚 Development Notes

### Code Standards

**PHP:**
- WordPress Coding Standards
- Escape all output (esc_html, esc_url, esc_attr)
- Sanitize all input
- Use prepared statements for database queries
- Check for ABSPATH to prevent direct access

**JavaScript:**
- No JSX syntax (browser-compatible ES5/ES6)
- Use wp.element.createElement() instead of JSX
- Register blocks with wp.blocks.registerBlockType()
- Use WordPress components (RangeControl, InspectorControls)

**CSS:**
- Mobile-first approach
- No font-family or font-size declarations
- Use flexbox and CSS Grid for layouts
- Smooth transitions for hover effects
- Consistent spacing and padding

### Best Practices

**Server-Side Rendering:**
- Always use render.php for dynamic content
- Improves SEO and performance
- Enables caching
- Better for database queries

**Theme Independence:**
- Never override theme fonts
- Use theme colors where possible
- Adapt to any WordPress theme
- Test with multiple themes

**Performance:**
- Minimize database queries
- Use WordPress caching
- Optimize images
- Lazy load when possible

### Common Pitfalls

**Avoid:**
- ❌ Using get_the_excerpt() (limited to 55 words by WordPress)
- ❌ Adding allowed_block_types_all filters (blocks all other blocks)
- ❌ Hardcoding font families or sizes
- ❌ Using target="_blank" without user consent
- ❌ Forgetting to escape output
- ❌ Not checking for empty data
- ❌ Ignoring mobile responsiveness

**Do:**
- ✅ Use get_the_content() for full content
- ✅ Use standard WordPress block registration
- ✅ Inherit fonts from theme
- ✅ Open links in same page by default
- ✅ Always escape and sanitize
- ✅ Provide fallbacks for missing data
- ✅ Test on all devices

---

## 📞 Support & Maintenance

### Maintenance Schedule

**Regular Tasks:**
- Test blocks with WordPress updates
- Test blocks with theme updates
- Monitor for JavaScript errors
- Check database performance
- Review user feedback
- Update documentation

**Version Updates:**
- Increment version numbers in all files
- Update BLOCKS-TRACKING.md
- Create changelog entries
- Test thoroughly before release
- Create new ZIP packages

### Contact Information

**Developer:** George Margiolos  
**GitHub:** https://github.com/georgemargiolos/LocalWP  
**Last Updated:** December 10, 2025 GMT+2

---

## 📋 Quick Reference

### Block Comparison

| Feature | Yacht Cards | Blog Grid | Blog Horizontal |
|---------|-------------|-----------|-----------------|
| Layout | Horizontal | 3-Column Grid | Horizontal |
| Data Source | Database | WP Posts | WP Posts |
| Customization | None | Post Count | Post Count |
| Image Position | Left | Top | Left |
| Responsive | Yes | Yes | Yes |
| Font Inheritance | Yes | Yes | Yes |
| Front Page Support | Yes | Yes | No (works everywhere) |

### Quick Install Commands

```bash
# Download blocks
cd /home/ubuntu/LocalWP/

# Yacht Cards
zip -r yolo-horizontal-yacht-cards-v50.0.zip yolo-yacht-search/public/blocks/yacht-horizontal-cards/

# Blog Grid
zip -r yolo-blog-posts-block-v1.0.0.zip yolo-blog-posts-block/

# Blog Horizontal
zip -r yolo-horizontal-blog-posts-v1.0.0.zip yolo-horizontal-blog-posts/
```

---

**End of Documentation**
