# YOLO Horizontal Blog Posts Block

**Version:** 1.0.0  
**Author:** George Margiolos  
**License:** GPL v2 or later

---

## Description

Display blog posts in a beautiful horizontal card layout with featured image on the left and content on the right. Perfect for blog pages and content showcases. Fully responsive with mobile-first design.

---

## Features

### Visual Features
- Horizontal layout on desktop (image left, content right)
- Stacked layout on mobile (image top, content bottom)
- Featured image with hover zoom effect
- Clean card design with subtle shadows
- Smooth hover transitions
- Professional spacing and padding
- #1572F5 button color (YOLO brand)

### Content Features
- Post title (linked to post)
- Post excerpt (200 characters max)
- "Read More" button
- Adjustable post count (1-20 posts)
- Automatic fallback for posts without featured images

### Technical Features
- Server-side rendering via render.php
- WP_Query for post retrieval
- RangeControl for post count adjustment
- Theme font inheritance (no font overrides)
- Fully responsive CSS
- Front page support (priority 999 filter)
- Browser-compatible JavaScript (no JSX)

---

## Installation

1. Upload `yolo-horizontal-blog-posts-v1.0.0.zip` to WordPress
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the zip file and click **Install Now**
4. Click **Activate Plugin**
5. No other dependencies required

---

## Usage

### Adding to Page/Post

1. Edit page/post in Gutenberg editor
2. Click "+" to add new block
3. Search for "YOLO Horizontal Blog Posts" or "horizontal"
4. Click block to insert
5. Adjust post count in block settings (right sidebar)
6. Preview and publish

### Block Settings

**Post Count:** 1-20 posts (default: 10)
- Adjust via RangeControl slider in block settings panel
- Controls how many blog posts are displayed

---

## Design Specifications

### Layout
- **Desktop (>768px):** Horizontal layout (300px image | flexible content)
- **Tablet (768px-992px):** Horizontal layout (250px image | flexible content)
- **Mobile (≤768px):** Stacked layout (full-width image on top, content below)

### Colors
- Button: #1572F5 (YOLO brand blue)
- Button Hover: #0d5fd4 (darker blue)
- Card Background: #ffffff (white)
- Text: Inherits from theme
- Shadow: rgba(0, 0, 0, 0.1)

### Typography
- **All fonts inherit from WordPress theme**
- No font-family declarations
- No font-size declarations
- Theme controls all typography

### Spacing
- Card margin bottom: 40px
- Gap between image and content: 30px
- Content padding: 30px (desktop), 20px (mobile)
- Button padding: 12px 30px

---

## Responsive Breakpoints

- **Desktop:** >992px - Full horizontal layout
- **Tablet:** 768px-992px - Compact horizontal layout
- **Mobile:** ≤768px - Stacked layout
- **Extra Small:** ≤480px - Optimized spacing

---

## File Structure

```
yolo-horizontal-blog-posts/
├── yolo-horizontal-blog-posts.php    # Main plugin file
├── block.json                        # Block metadata
├── render.php                        # Server-side rendering
├── index.js                          # Block registration (no JSX)
├── style.css                         # Frontend styles
├── editor.css                        # Editor styles
└── README.md                         # This file
```

---

## Technical Specifications

### Database Query

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

### Dependencies
- WordPress 5.8+
- Published blog posts (recommended with featured images)

### Browser Compatibility
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## Customization

### Available Settings
- **Post Count:** 1-20 posts via block settings

### Future Enhancement Ideas
- Category filter
- Tag filter
- Date range filter
- Sort order options (date, title, random)
- Excerpt length adjustment
- Show/hide featured image
- Custom button text
- Custom button color
- Pagination support

---

## Known Issues & Limitations

### Limitations
- Maximum 20 posts (can be increased if needed)
- No pagination (displays set number of posts)
- Excerpt limited to 200 characters
- Best appearance with featured images

### Known Issues
- None currently

---

## Version History

| Version | Date | Changes |
|---------|------|---------|
| 1.0.0 | Dec 10, 2025 | Initial release with horizontal layout |

---

## Support

For issues, feature requests, or questions:
- **Repository:** https://github.com/georgemargiolos/LocalWP
- **Issues:** https://github.com/georgemargiolos/LocalWP/issues

---

## License

GPL v2 or later

---

## Credits

- **Developed for:** YOLO Charters
- **Author:** George Margiolos
- **WordPress Block API:** WordPress Core Team

---

**Last Updated:** December 10, 2025  
**Status:** ✅ Production Ready
