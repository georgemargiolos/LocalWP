# YOLO Yacht Search Plugin - Installation Guide

## Quick Start

### Step 1: Install the Plugin

1. Download `yolo-yacht-search-v1.0.0.zip`
2. Go to WordPress Admin → **Plugins** → **Add New**
3. Click **Upload Plugin**
4. Choose the zip file and click **Install Now**
5. Click **Activate Plugin**

### Step 2: Configure Settings

1. Go to **YOLO Yacht Search** in the WordPress admin menu
2. Verify the prefilled settings:
   - ✅ **API Key**: Already filled with your Booking Manager API key
   - ✅ **My Company ID**: 7850 (YOLO)
   - ✅ **Friend Companies IDs**: 4366, 3604, 6711
3. Leave other settings as default or customize as needed
4. Click **Save Settings**

### Step 3: Create Search Results Page

1. Go to **Pages** → **Add New**
2. Title: "Search Results" (or any name you prefer)
3. In the block editor, click **+** to add a block
4. Search for "YOLO Search Results"
5. Add the **YOLO Search Results** block
6. Publish the page

### Step 4: Set Results Page in Settings

1. Go back to **YOLO Yacht Search** settings
2. In the **Search Results Page** dropdown, select the page you just created
3. Click **Save Settings**

### Step 5: Add Search Widget to Homepage

1. Go to **Pages** → Edit your homepage (or any page)
2. Click **+** to add a block
3. Search for "YOLO Search Widget"
4. Add the **YOLO Search Widget** block
5. Update/Publish the page

## You're Done! 🎉

Now when users:
1. Visit your homepage and use the search form
2. Select boat type and dates
3. Click "SEARCH"
4. They'll be redirected to the results page
5. **YOLO boats** (Company 7850) will appear first
6. **Partner boats** (Companies 4366, 3604, 6711) will appear below

## Customization

### Change Colors

Go to **YOLO Yacht Search** → **Styling Settings**:
- Primary Color (default: #1e3a8a - blue)
- Button Background (default: #dc2626 - red)
- Button Text (default: #ffffff - white)

### Change Cache Duration

Go to **YOLO Yacht Search** → **General Settings**:
- Cache Duration: 1-168 hours (default: 24 hours)
- Lower = More real-time, but slower
- Higher = Faster, but less real-time

### Disable Saturday-Only Booking

Edit `/public/js/yolo-yacht-search-public.js`:
- Find the `lockDaysFilter` function
- Change the logic to allow all days

## Troubleshooting

### Search Results Not Showing

1. Check that you've selected a Results Page in settings
2. Make sure the Results Page has the "YOLO Search Results" block
3. Check browser console for JavaScript errors

### No Boats Found

1. Verify API key is correct in settings
2. Check that company IDs are correct (7850, 4366, 3604, 6711)
3. Try different date ranges
4. Check Booking Manager API status

### Date Picker Not Working

1. Check browser console for JavaScript errors
2. Make sure Litepicker files are loaded
3. Try disabling other plugins that might conflict

## Advanced

### Add Custom CSS

Add to your theme's `style.css` or use a custom CSS plugin:

```css
/* Example: Change YOLO badge color */
.yolo-ys-yacht-badge {
    background: #your-color !important;
}

/* Example: Change search button style */
.yolo-ys-search-button {
    background: #your-color !important;
    border-radius: 20px !important;
}
```

### Hook into Search Results

Add to your theme's `functions.php`:

```php
// Modify search results before display
add_filter('yolo_ys_search_results', function($results) {
    // Your custom logic here
    return $results;
});
```

## Support

For issues or questions:
- Email: george@yolocharters.com
- GitHub: https://github.com/georgemargiolos/LocalWP

## What's Prefilled

The plugin comes with these values already configured:

✅ **Booking Manager API Key**: Your full API key  
✅ **YOLO Company ID**: 7850  
✅ **Partner Companies**: 4366, 3604, 6711  
✅ **Cache Duration**: 24 hours  
✅ **Currency**: EUR  
✅ **Primary Color**: #1e3a8a (blue)  
✅ **Button Color**: #dc2626 (red)  

You can change any of these in the settings if needed!
