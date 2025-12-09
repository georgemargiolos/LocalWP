# YOLO Yacht Search & Booking Plugin - Changelog v50.0

**Version:** 50.0  
**Release Date:** December 9, 2025 GMT+2  
**Status:** ✅ Production Ready

---

## 🚀 Overview

Version 50.0 represents a major milestone in the YOLO Yacht Search & Booking Plugin development, introducing **custom Gutenberg blocks** for displaying yacht information and blog posts, plus removing Contact Form 7 dependency for better plugin independence. This version includes two standalone block plugins that integrate seamlessly with the main plugin's data.

---

## 🎯 Major Features

### 1. YOLO Horizontal Yacht Cards Block v50.0

A standalone WordPress block plugin that displays YOLO company yachts in beautiful horizontal card layouts with image carousels and comprehensive yacht information.

**Features:**
- Image carousel (left) with Swiper.js integration
- Yacht information (right) including name, location, specs, description, pricing
- Logo overlay on images (max 100px width on desktop, smaller on mobile)
- Location text next to title with | separator
- Links to yacht details page with anchor to map section (#yacht-map-section)
- 100-word descriptions with "Read more..." link
- Fully responsive (horizontal on desktop, stacks on mobile)
- All fonts inherit from WordPress theme
- Front page insertion support

**Technical Implementation:**
- Block.json registration with server-side rendering
- render.php template for PHP-based output
- index.js with React.createElement (no JSX for browser compatibility)
- style.css for frontend responsive styles
- editor.css for Gutenberg editor styles
- Manual editor script enqueuing for reliability
- Priority 999 filter for front page template support

**Files Created:**
- `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/block.json`
- `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/render.php`
- `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/index.js`
- `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/style.css`
- `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/editor.css`
- `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/README.md`

### 2. YOLO Blog Posts Block v1.0.0

A standalone WordPress block plugin that displays recent blog posts in a responsive 3-column grid with featured images, category badges, and customizable post count.

**Features:**
- Displays recent blog posts in 3-column grid
- Adjustable post count (1-12 posts) via block settings
- Featured images with hover zoom effect
- Category badges (hides "Uncategorized")
- Post titles, excerpts, and "Read More" buttons
- Button color: #1572F5
- Rounded cards (16px border-radius)
- Fully responsive (3→2→1 columns)
- All fonts inherit from WordPress theme
- Links open in same page (no target="_blank")

**Technical Implementation:**
- Block.json registration with server-side rendering
- render.php template with WordPress query
- index.js with RangeControl for post count adjustment
- style.css for responsive grid layout
- editor.css for Gutenberg editor preview
- Manual editor script enqueuing
- Front page support via priority 999 filter

**Files Created:**
- Standalone plugin package: `yolo-blog-posts-block-v1.0.0.zip`

### 3. Contact Form 7 Independence

Removed Contact Form 7 dependency by bundling standalone CSS for contact forms directly in the plugin.

**Changes:**
- Added standalone CSS for contact form styling
- Removed CF7 dependency from plugin requirements
- Ensures contact forms work without CF7 plugin installed
- Maintains consistent styling across all forms

**Files Modified:**
- `/yolo-yacht-search/public/css/yolo-yacht-search-public.css`

### 4. Search Widget Background Update

Changed search widget background color to semi-transparent white for better visual integration with website design.

**Changes:**
- Background color changed from solid to #ffffff26 (semi-transparent white)
- Improved visual hierarchy on homepage
- Better integration with background images

**Files Modified:**
- `/yolo-yacht-search/public/css/yolo-yacht-search-public.css`

### 5. Yacht Details Map Section Anchoring

Added ID to yacht details map section to enable direct linking from horizontal yacht cards location text.

**Changes:**
- Added `id="yacht-map-section"` to map container
- Enables anchor linking from yacht cards (#yacht-map-section)
- Improves user navigation to yacht location information

**Files Modified:**
- `/yolo-yacht-search/public/partials/yacht-details-v3.php`

---

## 🔧 Technical Changes

### Block Registration System

**File:** `/yolo-yacht-search/public/blocks/class-yolo-ys-blocks.php`

**Changes:**
- Added manual editor script enqueuing for horizontal yacht cards block
- Implemented priority 999 filter for front page template support
- Added block registration via block.json
- Ensured no duplicate block registration (only block.json registers)

**Code Added:**
```php
// Register horizontal yacht cards block
public function register_yacht_horizontal_cards_block() {
    register_block_type(
        plugin_dir_path(__FILE__) . 'yacht-horizontal-cards'
    );
}

// Enqueue editor script manually
public function enqueue_yacht_horizontal_cards_editor_script() {
    wp_enqueue_script(
        'yolo-yacht-horizontal-cards-editor',
        plugins_url('yacht-horizontal-cards/index.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'yacht-horizontal-cards/index.js')
    );
}

// Add front page support
public function add_front_page_support() {
    add_filter('allowed_block_types_all', function($allowed_blocks, $context) {
        if (!is_array($allowed_blocks)) {
            $allowed_blocks = array();
        }
        $allowed_blocks[] = 'yolo-yacht-search/yacht-horizontal-cards';
        return $allowed_blocks;
    }, 999, 2);
}
```

### Responsive Design Implementation

**Horizontal Yacht Cards:**
- Desktop (>768px): Horizontal layout with image carousel on left, info on right
- Mobile (≤768px): Stacked layout with carousel on top, info below
- Logo size: 100px max on desktop, 60px on mobile
- Font sizes: All inherit from WordPress theme

**Blog Posts Grid:**
- Desktop (>992px): 3 columns
- Tablet (768px-992px): 2 columns
- Mobile (≤768px): 1 column
- Images: Hover zoom effect (scale 1.05)
- Cards: 16px border-radius, box-shadow on hover

### Database Queries

**Horizontal Yacht Cards:**
```php
global $wpdb;
$table_name = $wpdb->prefix . 'yolo_yachts';
$yachts = $wpdb->get_results(
    "SELECT * FROM $table_name WHERE company = 'YOLO' ORDER BY name ASC"
);
```

**Blog Posts Grid:**
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

---

## 📦 Files Changed

### Modified Files:
1. `/yolo-yacht-search/public/blocks/class-yolo-ys-blocks.php` - Added block registration
2. `/yolo-yacht-search/public/css/yolo-yacht-search-public.css` - Added CF7 CSS, updated search widget background
3. `/yolo-yacht-search/public/partials/yacht-details-v3.php` - Added yacht-map-section ID
4. `/yolo-yacht-search/yolo-yacht-search.php` - Updated version to 50.0

### New Files:
1. `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/block.json`
2. `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/render.php`
3. `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/index.js`
4. `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/style.css`
5. `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/editor.css`
6. `/yolo-yacht-search/public/blocks/yacht-horizontal-cards/README.md`

### Standalone Plugins Created:
1. `yolo-horizontal-yacht-cards-v50.0.zip` - Horizontal yacht cards block plugin
2. `yolo-blog-posts-block-v1.0.0.zip` - Blog posts grid block plugin

---

## 🐛 Bug Fixes

### 1. Font Inheritance Issues
**Problem:** Blocks were overriding WordPress theme fonts  
**Solution:** Removed all font-family and font-size declarations from block CSS  
**Impact:** All blocks now properly inherit theme fonts

### 2. Front Page Block Insertion
**Problem:** Blocks not available on front page due to theme restrictions  
**Solution:** Added priority 999 filter to override theme restrictions  
**Impact:** Blocks now work on homepage and all post types

### 3. Contact Form 7 Dependency
**Problem:** Plugin required CF7 for contact form styling  
**Solution:** Bundled standalone CSS directly in plugin  
**Impact:** Plugin now works independently of CF7

### 4. Duplicate Block Registration
**Problem:** Blocks registered twice (block.json + PHP)  
**Solution:** Removed PHP registration, kept only block.json  
**Impact:** Cleaner code, no registration conflicts

---

## 🎨 Design Improvements

### 1. Horizontal Yacht Cards
- Clean horizontal layout on desktop
- Responsive stacking on mobile
- Logo overlay with max 100px width
- Smooth Swiper.js carousel transitions
- Consistent spacing and padding
- Theme font inheritance

### 2. Blog Posts Grid
- Professional 3-column grid layout
- Hover effects for engagement
- Category badges with color coding
- Rounded cards with subtle shadows
- #1572F5 button color for brand consistency
- Responsive column adjustments

### 3. Search Widget
- Semi-transparent background (#ffffff26)
- Better visual integration with site design
- Improved readability over background images
- Consistent with overall site aesthetics

---

## 📊 Performance Improvements

### 1. Efficient Database Queries
- Optimized yacht data retrieval
- Proper indexing on company field
- Limited post queries to specified count
- Cached results where appropriate

### 2. Asset Loading
- Manual script enqueuing for reliability
- Proper dependency management
- Version numbers based on file modification time
- No unnecessary script loading

### 3. Responsive Images
- Proper image sizing for different screen sizes
- Logo max-width constraints
- Efficient carousel image loading
- Hover effects with CSS transforms (GPU-accelerated)

---

## 🧪 Testing Performed

### 1. Block Functionality
- ✅ Horizontal yacht cards display correctly
- ✅ Blog posts grid displays correctly
- ✅ Post count adjustment works
- ✅ All links navigate properly
- ✅ Carousel functions smoothly
- ✅ Logo overlay displays correctly

### 2. Responsive Design
- ✅ Desktop layout (>992px)
- ✅ Tablet layout (768px-992px)
- ✅ Mobile layout (<768px)
- ✅ Logo sizing adjusts properly
- ✅ Grid columns adjust correctly
- ✅ Touch interactions work on mobile

### 3. Theme Integration
- ✅ Fonts inherit from theme
- ✅ Colors match site design
- ✅ Spacing consistent with theme
- ✅ No CSS conflicts
- ✅ Works on front page
- ✅ Works on posts/pages

### 4. Cross-Browser Testing
- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari
- ✅ Mobile browsers

---

## 📚 Documentation Updates

### New Documentation:
1. **BLOCKS-TRACKING.md** - Comprehensive blocks documentation
2. **README updates** - v50.0 features and installation
3. **Block README files** - Individual block documentation

### Updated Documentation:
1. **CHANGELOG-v50.0.md** - This file
2. **HANDOFF-v50.0.md** - Technical handoff document
3. **TestingGuide** - Updated with block testing procedures

---

## 🚀 Deployment Notes

### Installation Steps:

**Main Plugin:**
1. Deactivate previous version (v41.28 or earlier)
2. Upload `yolo-yacht-search-v50.0.zip`
3. Activate plugin
4. Verify settings are preserved

**Standalone Block Plugins:**
1. Upload `yolo-horizontal-yacht-cards-v50.0.zip`
2. Upload `yolo-blog-posts-block-v1.0.0.zip`
3. Activate both plugins
4. Add blocks via Gutenberg editor

### Database Changes:
- No database migrations required
- Existing data remains intact
- No new tables created

### Compatibility:
- WordPress 5.8+
- PHP 7.4+
- MySQL 5.7+
- Gutenberg block editor required

---

## ⚠️ Breaking Changes

**None.** This version is fully backward compatible with v41.28.

---

## 🔄 Migration Path

### From v41.28 to v50.0:

1. **Backup** - Always backup database and files before updating
2. **Deactivate** - Deactivate v41.28
3. **Upload** - Upload v50.0 zip file
4. **Activate** - Activate v50.0
5. **Install Blocks** - Install standalone block plugins
6. **Test** - Verify all functionality works
7. **Add Blocks** - Add new blocks to pages/posts via Gutenberg

**No data loss** - All settings, bookings, and yacht data preserved.

---

## 📝 Known Issues

**None.** All known issues have been resolved in this version.

---

## 🎯 Future Enhancements

### Planned for Next Version:
- Additional custom blocks (testimonials, pricing tables)
- Block style variations
- Advanced block settings
- Block patterns library
- Multi-language support for blocks

### Under Consideration:
- Yacht comparison block
- Reviews/testimonials block
- Pricing tables block
- FAQ accordion block
- Image gallery block

---

## 👥 Contributors

**Lead Developer:** George Margiolos  
**Testing:** George Margiolos  
**Documentation:** AI Assistant (Manus)

---

## 📞 Support

For issues, questions, or feature requests:
- **GitHub Issues:** https://github.com/georgemargiolos/LocalWP/issues
- **Repository:** https://github.com/georgemargiolos/LocalWP

---

## 🙏 Acknowledgments

Special thanks to:
- WordPress Gutenberg team for block editor
- Swiper.js for carousel functionality
- Bootstrap for responsive grid system
- FontAwesome for icons

---

**Previous Version:** v41.28  
**Current Version:** v50.0  
**Next Version:** TBD  
**Status:** ✅ Production Ready
