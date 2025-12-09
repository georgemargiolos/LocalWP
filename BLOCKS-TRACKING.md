# YOLO Custom Gutenberg Blocks - Tracking & Documentation

**Last Updated:** December 9, 2025 GMT+2  
**Total Blocks:** 2  
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

## 🎨 Design Guidelines

### Color Palette

**Blog Posts Block:**
- Button Color: #1572F5 (brand blue)
- Button Hover: Darker shade of #1572F5
- Card Background: White (#ffffff)
- Text: Inherits from theme

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

---

## 🔧 Development Guidelines

### Block Development Standards

**1. File Structure:**
```
block-name/
├── block-name.php       # Main plugin file
├── block.json           # Block metadata (required)
├── render.php           # Server-side rendering (required)
├── index.js             # Block registration (required)
├── style.css            # Frontend styles (required)
├── editor.css           # Editor styles (optional)
└── README.md            # Documentation (recommended)
```

**2. Block Registration:**
- Use block.json for metadata
- Register via register_block_type()
- Manual editor script enqueuing for reliability
- Add front page support with priority 999 filter

**3. JavaScript Guidelines:**
- No JSX syntax (use React.createElement)
- Browser-compatible code only
- Proper dependency management
- Version numbers based on file modification time

**4. CSS Guidelines:**
- No font-family declarations
- No font-size declarations
- Use relative units (em, rem) for spacing
- Mobile-first responsive design
- Avoid !important unless absolutely necessary

**5. PHP Guidelines:**
- Sanitize all user input
- Escape all output
- Use prepared statements for database queries
- Follow WordPress coding standards
- Add ABSPATH check to prevent direct access

### Testing Checklist

**Before Release:**
- ✅ Test on front page
- ✅ Test on posts and pages
- ✅ Test on desktop (>992px)
- ✅ Test on tablet (768px-992px)
- ✅ Test on mobile (<768px)
- ✅ Test in Chrome/Edge
- ✅ Test in Firefox
- ✅ Test in Safari
- ✅ Test with different themes
- ✅ Check console for errors
- ✅ Check PHP error log
- ✅ Verify font inheritance
- ✅ Verify responsive design
- ✅ Test all links
- ✅ Test all interactions

---

## 📊 Block Comparison

| Feature | Horizontal Yacht Cards | Blog Posts Grid |
|---------|------------------------|-----------------|
| **Version** | 50.0 | 1.0.0 |
| **Layout** | Horizontal cards | 3-column grid |
| **Data Source** | wp_yolo_yachts table | WordPress posts |
| **Settings** | None | Post count (1-12) |
| **Dependencies** | YOLO plugin v50.0+ | None |
| **Responsive** | Horizontal → Stacked | 3 → 2 → 1 columns |
| **Images** | Swiper carousel | Featured images |
| **Special Features** | Logo overlay, map linking | Hover zoom, category badges |
| **Font Inheritance** | ✅ Yes | ✅ Yes |
| **Front Page Support** | ✅ Yes | ✅ Yes |

---

## 🚀 Future Block Ideas

### Planned Blocks

**1. YOLO Testimonials Block**
- Display customer testimonials in carousel or grid
- Star ratings
- Customer photos
- Responsive design

**2. YOLO Pricing Tables Block**
- Compare yacht charter packages
- Highlight popular options
- Call-to-action buttons
- Responsive columns

**3. YOLO FAQ Accordion Block**
- Frequently asked questions
- Expandable/collapsible sections
- Search functionality
- Category filtering

**4. YOLO Image Gallery Block**
- Yacht photo galleries
- Lightbox functionality
- Grid or masonry layout
- Filtering by yacht

**5. YOLO Contact Form Block**
- Custom contact form
- Email integration
- Form validation
- Success messages

### Enhancement Ideas

**For Existing Blocks:**

**Horizontal Yacht Cards:**
- Add filter by yacht type
- Add sort options
- Add pagination
- Add "Compare Yachts" feature
- Add availability calendar preview

**Blog Posts Grid:**
- Add category filter
- Add tag filter
- Add search functionality
- Add load more button
- Add post date display
- Add author display

---

## 📚 Resources & References

### WordPress Documentation
- [Block Editor Handbook](https://developer.wordpress.org/block-editor/)
- [Block API Reference](https://developer.wordpress.org/block-editor/reference-guides/block-api/)
- [Server-Side Rendering](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/creating-dynamic-blocks/)

### Libraries Used
- [Swiper.js](https://swiperjs.com/) - Image carousel
- [WordPress React Components](https://developer.wordpress.org/block-editor/reference-guides/components/) - Block controls

### Code Standards
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)
- [WordPress JavaScript Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/javascript/)

---

## 🔐 Security Considerations

### Security Best Practices

**1. Input Sanitization:**
- Sanitize all user input with appropriate functions
- Use `sanitize_text_field()` for text
- Use `absint()` for integers
- Use `esc_url()` for URLs

**2. Output Escaping:**
- Escape all output with appropriate functions
- Use `esc_html()` for HTML content
- Use `esc_attr()` for attributes
- Use `esc_url()` for URLs

**3. Database Security:**
- Use `$wpdb->prepare()` for all queries
- Never use direct SQL queries
- Validate data types before queries

**4. File Security:**
- Add ABSPATH check to all PHP files
- Prevent direct file access
- Validate file uploads (if applicable)

**5. Nonce Verification:**
- Add nonces to all AJAX requests
- Verify nonces before processing
- Use unique nonce names

---

## 📞 Support & Maintenance

### Support Channels
- **GitHub Issues:** https://github.com/georgemargiolos/LocalWP/issues
- **Repository:** https://github.com/georgemargiolos/LocalWP
- **Documentation:** This file + individual block README files

### Maintenance Schedule
- **Weekly:** Review GitHub issues
- **Monthly:** Security updates
- **Quarterly:** Feature enhancements
- **Annually:** Major version updates

### Version Numbering
- **Major.Minor.Patch** (e.g., 1.0.0)
- **Major:** Breaking changes or major features
- **Minor:** New features, backward compatible
- **Patch:** Bug fixes, minor improvements

---

## 📝 Changelog

### December 9, 2025
- ✅ Created YOLO Horizontal Yacht Cards Block v50.0
- ✅ Created YOLO Blog Posts Block v1.0.0
- ✅ Created this tracking document
- ✅ Documented all blocks comprehensively

---

## 🎯 Quick Reference

### Block Namespaces
- **Horizontal Yacht Cards:** `yolo-yacht-search/yacht-horizontal-cards`
- **Blog Posts Grid:** `yolo-blog-posts/blog-posts-grid`

### Package Locations
- **Horizontal Yacht Cards:** `/home/ubuntu/yolo-horizontal-yacht-cards-v50.0.zip`
- **Blog Posts Grid:** `/home/ubuntu/yolo-blog-posts-block-v1.0.0.zip`

### Installation Commands (WP-CLI)
```bash
# Install Horizontal Yacht Cards
wp plugin install /path/to/yolo-horizontal-yacht-cards-v50.0.zip --activate

# Install Blog Posts Grid
wp plugin install /path/to/yolo-blog-posts-block-v1.0.0.zip --activate

# Check status
wp plugin list | grep yolo
```

### Uninstallation Commands (WP-CLI)
```bash
# Deactivate and delete Horizontal Yacht Cards
wp plugin deactivate yolo-horizontal-yacht-cards
wp plugin delete yolo-horizontal-yacht-cards

# Deactivate and delete Blog Posts Grid
wp plugin deactivate yolo-blog-posts-block
wp plugin delete yolo-blog-posts-block
```

---

**Document Maintained By:** George Margiolos / AI Assistant (Manus)  
**Last Updated:** December 9, 2025 GMT+2  
**Next Review:** January 9, 2026  
**Status:** ✅ Current & Complete
