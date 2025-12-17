# Changelog v60.6.2 - Dynamic Page Titles for SEO

**Date:** December 14, 2025  
**Type:** SEO Enhancement

---

## Issue Fixed

All yacht details pages had the same generic title "Yacht Details Page – Yolo Local", which is bad for:
- **SEO**: Google sees duplicate titles
- **Social Sharing**: Facebook/Twitter show generic title
- **User Experience**: Browser tabs all look the same

### Before v60.6.2

**Page Title**: `Yacht Details Page – Yolo Local` (same for all boats)

**Open Graph/Twitter**: Already had unique titles ✓

### After v60.6.2

**Page Title**: `LEMON | Sun Odyssey 469 – Yolo Charters` (unique per boat)

**Format**: `[Boat Name] | [Model] – Yolo Charters`

---

## Changes Made

### 1. Added WordPress Title Filter

Added `filter_yacht_page_title()` method to dynamically set page title based on yacht data:

```php
public function filter_yacht_page_title($title, $sep = null) {
    if (!$this->is_yacht_page()) {
        return $title;
    }
    
    $yacht = $this->get_current_yacht();
    if (empty($yacht)) {
        return $title;
    }
    
    $name = $yacht['name'] ?? 'Yacht';
    $model = $yacht['model'] ?? '';
    
    $new_title = $name;
    if ($model) {
        $new_title .= ' | ' . $model;
    }
    $new_title .= ' – Yolo Charters';
    
    return $new_title;
}
```

### 2. Updated Meta Tags Format

Changed meta tags title format from:
- **Before**: `[Name] - [Model] | [Site Name]`
- **After**: `[Name] | [Model] – Yolo Charters`

Now uses consistent branding "Yolo Charters" instead of WordPress site name.

---

## SEO Benefits

### Google Search
- ✅ Unique titles for each boat page
- ✅ Better click-through rates (descriptive titles)
- ✅ No duplicate content warnings

### Social Sharing
- ✅ Facebook shows boat name + model
- ✅ Twitter cards display correctly
- ✅ Professional branding ("Yolo Charters")

### User Experience
- ✅ Browser tabs show boat names
- ✅ Bookmarks are identifiable
- ✅ History is searchable

---

## Examples

**LEMON (Sun Odyssey 469)**:
```
Page Title: LEMON | Sun Odyssey 469 – Yolo Charters
```

**AQUILO (Bavaria Cruiser 46)**:
```
Page Title: AQUILO | Bavaria Cruiser 46 – Yolo Charters
```

**Boat without model**:
```
Page Title: Strawberry – Yolo Charters
```

---

## Files Modified

1. `includes/class-yolo-ys-meta-tags.php` - Added title filter + updated format
2. `yolo-yacht-search.php` - Version bump to 60.6.2

---

## Note About "Yolo Local"

If you still see "Yolo Local" in other parts of your site, it's from your WordPress site settings, not the plugin.

**To change**: Go to **WordPress Admin → Settings → General → Site Title** and change from "Yolo Local" to "Yolo Charters".

---

## Testing

After installing this update:

1. Visit any yacht details page
2. Check browser tab title - should show boat name
3. View page source - `<title>` tag should be unique
4. Share on Facebook/Twitter - should show boat name

---

**Status:** Ready for deployment - SEO optimized
