# Image Optimization Strategy for Yacht Sync
**Date:** December 12, 2025  
**Project:** YOLO Yacht Search & Booking Plugin

## Current Implementation Analysis

### How Images Are Currently Stored

Based on the code review of `class-yolo-ys-database.php` (lines 350-410), the current process is:

1. **Download from CDN:** Images are downloaded from Booking Manager's CDN (`booking-manager.com/cbm/documents/`)
2. **Store Locally:** Full-size images are saved to `/wp-content/uploads/yolo-yacht-images/`
3. **Database Storage:** Only URLs are stored in the database (not the image data itself)
4. **No Optimization:** Images are stored exactly as downloaded, with no resizing or compression

```php
// Current code (lines 374-380)
if (!file_exists($local_path)) {
    $image_data = @file_get_contents($remote_url);
    if ($image_data !== false) {
        file_put_contents($local_path, $image_data);  // ← No optimization!
    }
}
```

### Current Issues

**Storage Impact:**
- Each yacht has 10-20 images
- Average image size from Booking Manager: **2-5 MB** (high-resolution photos)
- 3 yachts × 15 images × 3 MB = **~135 MB** just for your fleet
- Partner yachts add significantly more storage

**Performance Impact:**
- Large images slow down page load times
- Mobile users suffer most (slow connections + large downloads)
- Server bandwidth costs increase
- No responsive image serving (same large image for mobile and desktop)

## Proposed Solutions

### ✅ **Solution 1: WordPress Image Resizing (RECOMMENDED)**

**Approach:** Use WordPress's built-in `wp_get_image_editor()` to create optimized versions during sync.

**Advantages:**
- ✓ Native WordPress integration (no external dependencies)
- ✓ Automatic format optimization (JPEG quality, WebP support if available)
- ✓ Creates multiple sizes (thumbnail, medium, large) for responsive serving
- ✓ Maintains aspect ratios automatically
- ✓ Works with existing WordPress media library features

**Implementation:**
```php
// After downloading image
$editor = wp_get_image_editor($local_path);
if (!is_wp_error($editor)) {
    // Resize to max 1920px width (retina-ready for desktop)
    $editor->resize(1920, 1080, false);
    $editor->set_quality(85); // Good balance of quality/size
    $editor->save($local_path);
    
    // Create thumbnail (for yacht cards)
    $thumb_path = str_replace('.jpg', '-thumb.jpg', $local_path);
    $editor->resize(600, 400, true); // Crop to exact size
    $editor->save($thumb_path);
}
```

**Expected Results:**
- Original: 3 MB → Optimized: **300-500 KB** (85-90% reduction)
- Thumbnails: **50-80 KB** for yacht cards
- Total storage for 3 yachts: **~20 MB** instead of 135 MB

---

### Solution 2: PHP GD Library Resizing

**Approach:** Use PHP's GD library for manual image manipulation.

**Advantages:**
- ✓ Available on most WordPress hosting
- ✓ Fine-grained control over compression
- ✓ Can convert formats (PNG → JPEG for smaller sizes)

**Disadvantages:**
- ✗ More code to maintain
- ✗ Need to handle different image formats separately
- ✗ Less integrated with WordPress ecosystem

---

### Solution 3: ImageMagick (If Available)

**Approach:** Use ImageMagick for advanced optimization.

**Advantages:**
- ✓ Best compression algorithms
- ✓ Can create WebP versions (modern format, 30% smaller than JPEG)
- ✓ Batch processing capabilities

**Disadvantages:**
- ✗ Not always available on shared hosting
- ✗ Requires checking for availability before use
- ✗ Overkill for this use case

---

### Solution 4: External Service (TinyPNG, Cloudinary)

**Approach:** Send images to external API for optimization.

**Advantages:**
- ✓ Best compression quality
- ✓ Automatic format selection (WebP, AVIF)
- ✓ CDN delivery included (Cloudinary)

**Disadvantages:**
- ✗ Costs money (API usage fees)
- ✗ Slower sync process (network latency)
- ✗ Dependency on external service
- ✗ Privacy concerns (sending yacht images to third party)

---

## Recommended Implementation Plan

### Phase 1: Basic Optimization (Quick Win)

**Add to `class-yolo-ys-database.php` in the `store_yacht()` method:**

```php
// After line 376 (after file_put_contents)
if ($image_data !== false) {
    file_put_contents($local_path, $image_data);
    
    // NEW: Optimize the downloaded image
    $this->optimize_yacht_image($local_path);
}
```

**New method to add:**

```php
/**
 * Optimize yacht image after download
 * Reduces file size while maintaining quality
 * 
 * @param string $image_path Full path to image file
 * @return bool Success status
 */
private function optimize_yacht_image($image_path) {
    if (!file_exists($image_path)) {
        return false;
    }
    
    // Use WordPress image editor
    $editor = wp_get_image_editor($image_path);
    
    if (is_wp_error($editor)) {
        error_log('YOLO YS: Image optimization failed for ' . basename($image_path));
        return false;
    }
    
    // Get current dimensions
    $size = $editor->get_size();
    $width = $size['width'];
    $height = $size['height'];
    
    // Resize if larger than 1920px (retina-ready for desktop)
    if ($width > 1920 || $height > 1080) {
        $editor->resize(1920, 1080, false); // Maintain aspect ratio
    }
    
    // Set quality to 85% (sweet spot for size vs quality)
    $editor->set_quality(85);
    
    // Save optimized version
    $result = $editor->save($image_path);
    
    if (is_wp_error($result)) {
        error_log('YOLO YS: Failed to save optimized image: ' . $result->get_error_message());
        return false;
    }
    
    error_log('YOLO YS: Optimized image: ' . basename($image_path));
    return true;
}
```

### Phase 2: Responsive Thumbnails (Better Performance)

**Create thumbnails for yacht cards:**

```php
/**
 * Create optimized thumbnail for yacht cards
 * 
 * @param string $original_path Full path to original image
 * @return string|null URL to thumbnail or null on failure
 */
private function create_yacht_thumbnail($original_path, $yacht_id, $index) {
    $editor = wp_get_image_editor($original_path);
    
    if (is_wp_error($editor)) {
        return null;
    }
    
    // Create 600x400 thumbnail (perfect for yacht cards)
    $editor->resize(600, 400, true); // Hard crop
    $editor->set_quality(80);
    
    // Save with unique name
    $upload_dir = wp_upload_dir();
    $yolo_images_dir = $upload_dir['basedir'] . '/yolo-yacht-images';
    $thumb_filename = "yacht-{$yacht_id}-thumb-{$index}.jpg";
    $thumb_path = $yolo_images_dir . '/' . $thumb_filename;
    
    $result = $editor->save($thumb_path);
    
    if (is_wp_error($result)) {
        return null;
    }
    
    return $upload_dir['baseurl'] . '/yolo-yacht-images/' . $thumb_filename;
}
```

### Phase 3: WebP Support (Modern Browsers)

**For even better compression:**

```php
// Check if server supports WebP
if (function_exists('imagewebp')) {
    $webp_path = str_replace('.jpg', '.webp', $local_path);
    $editor->save($webp_path, 'image/webp');
    // Store both JPEG and WebP URLs, serve WebP to modern browsers
}
```

## Expected Performance Improvements

### Storage Savings

| Scenario | Before | After | Savings |
|----------|--------|-------|---------|
| Single yacht image | 3 MB | 400 KB | 87% |
| Single yacht (15 images) | 45 MB | 6 MB | 87% |
| Your 3 yachts | 135 MB | 18 MB | 87% |
| With thumbnails | 135 MB | 20 MB | 85% |

### Page Load Improvements

| Page Type | Before | After | Improvement |
|-----------|--------|-------|-------------|
| Yacht card (1 image) | 3 MB | 80 KB (thumb) | **97% faster** |
| Yacht details (5 images) | 15 MB | 2 MB | **87% faster** |
| Fleet page (9 yachts) | 27 MB | 720 KB | **97% faster** |

### Mobile Performance

- **Before:** 27 MB fleet page = 54 seconds on 4G (slow)
- **After:** 720 KB fleet page = **1.4 seconds on 4G** ✓

## Implementation Checklist

- [ ] Add `optimize_yacht_image()` method to database class
- [ ] Add `create_yacht_thumbnail()` method to database class
- [ ] Modify `store_yacht()` to call optimization after download
- [ ] Update database schema to store thumbnail URLs separately
- [ ] Test with one yacht first
- [ ] Run full sync and verify image quality
- [ ] Update yacht card templates to use thumbnails
- [ ] Add WebP support (optional, Phase 3)
- [ ] Document changes in README

## Backward Compatibility

**Existing Images:** The optimization only applies to newly synced images. To optimize existing images:

```php
// Run once via WP-CLI or admin button
public function reoptimize_existing_images() {
    global $wpdb;
    $images = $wpdb->get_results("SELECT * FROM {$this->table_images}");
    
    foreach ($images as $image) {
        $local_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image->image_url);
        if (file_exists($local_path)) {
            $this->optimize_yacht_image($local_path);
        }
    }
}
```

## Conclusion

**Recommendation:** Implement Solution 1 (WordPress Image Resizing) in phases.

**Why:**
- ✓ Native WordPress integration (no dependencies)
- ✓ Proven, reliable, well-tested
- ✓ 85-90% storage reduction
- ✓ Massive page load improvements
- ✓ Easy to implement (50 lines of code)
- ✓ No ongoing costs
- ✓ Works on all WordPress hosting

**Next Steps:**
1. Implement Phase 1 (basic optimization)
2. Test with manual yacht sync
3. Verify image quality is acceptable
4. Deploy to production
5. Run full sync to optimize all images

**Estimated Time:** 30-45 minutes to implement and test.
