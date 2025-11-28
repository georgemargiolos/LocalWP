# YOLO Yacht Search Plugin - Complete Setup Guide

## 📦 Installation

### Step 1: Upload Plugin
1. Download `yolo-yacht-search-v1.0.2.zip`
2. Go to WordPress Admin → **Plugins** → **Add New**
3. Click **Upload Plugin**
4. Choose the zip file
5. Click **Install Now**
6. Click **Activate Plugin**

---

## ⚙️ Initial Configuration

### Step 2: Sync Yacht Data (IMPORTANT!)
1. Go to **YOLO Yacht Search** in WordPress admin menu
2. You'll see the sync dashboard with statistics
3. Click the big red button: **"Sync All Yachts Now"**
4. Wait 1-2 minutes while it fetches data from:
   - YOLO (Company 7850)
   - Partner companies (4366, 3604, 6711)
5. Page will reload showing:
   - Total Yachts: X
   - YOLO Yachts: X
   - Partner Yachts: X
   - Last Sync: Just now

**What happens during sync:**
- Fetches all yachts from Booking Manager API
- Stores yacht data in WordPress database
- Downloads yacht images, specs, equipment, extras
- Makes the plugin ready to use!

---

## 📄 Create Required Pages

### Step 3: Create "Search Results" Page
1. Go to **Pages** → **Add New**
2. Title: `Search Results`
3. In the content editor, add:
   ```
   [yolo_search_results]
   ```
4. Click **Publish**

### Step 4: Create "Yacht Details" Page
1. Go to **Pages** → **Add New**
2. Title: `Yacht Details`
3. In the content editor, add:
   ```
   [yolo_yacht_details]
   ```
4. Click **Publish**

### Step 5: Create "Our Fleet" Page (Optional)
1. Go to **Pages** → **Add New**
2. Title: `Our Fleet` (or "Browse Yachts")
3. In the content editor, add:
   ```
   [yolo_our_fleet]
   ```
4. Click **Publish**

---

## 🔧 Configure Plugin Settings

### Step 6: Link Pages to Plugin
1. Go to **YOLO Yacht Search** in admin menu
2. Scroll to **Company Settings** section
3. Find **"Search Results Page"** dropdown
   - Select: `Search Results`
4. Find **"Yacht Details Page"** dropdown
   - Select: `Yacht Details`
5. Click **Save Settings** at the bottom

**All other settings are already prefilled:**
- ✅ API Key
- ✅ My Company ID: 7850
- ✅ Friend Companies: 4366, 3604, 6711
- ✅ Cache Duration: 24 hours
- ✅ Currency: EUR
- ✅ Colors: Blue & Red

---

## 🏠 Add Search Widget to Homepage

### Step 7: Add Search Form
1. Go to **Pages** → Edit your **Homepage**
2. In the content editor, add:
   ```
   [yolo_search_widget]
   ```
3. Click **Update**

**Optional:** You can also add this to any other page where you want the search form.

---

## ✅ Test the Plugin

### Step 8: Test User Flow
1. **Visit Homepage**
   - You should see the search widget with:
     - Boat type dropdown
     - Date range picker
     - Search button

2. **Search for Yachts**
   - Select a boat type (e.g., "Sailing yacht")
   - Pick dates (Saturday to Saturday)
   - Click **SEARCH**

3. **View Results**
   - Should redirect to "Search Results" page
   - See two sections:
     - **YOLO Charters Fleet** (with red badges)
     - **Partner Companies**

4. **View Yacht Details**
   - Click **"View Details"** on any yacht
   - Should see:
     - Image carousel (auto-advances every 5 seconds)
     - Complete specifications
     - Equipment list
     - Available extras
     - Back button

5. **Browse Fleet**
   - Visit "Our Fleet" page
   - See all yachts in grid layout
   - YOLO boats first (red badges)
   - Partner boats below

---

## 🎨 Customization (Optional)

### Change Colors
1. Go to **YOLO Yacht Search** → **Styling Settings**
2. Use color pickers to change:
   - Primary Color (default: blue #1e3a8a)
   - Button Background (default: red #dc2626)
   - Button Text (default: white #ffffff)
3. Click **Save Settings**

### Change Currency
1. Go to **YOLO Yacht Search** → **General Settings**
2. Select currency: EUR, USD, or GBP
3. Click **Save Settings**

---

## 🔄 Maintenance

### When to Re-Sync Yachts

Run sync when:
- You add new yachts to Booking Manager
- Yacht details change (specs, images, prices)
- Equipment or extras are updated
- **Recommended:** Once per week

**How to re-sync:**
1. Go to **YOLO Yacht Search** in admin
2. Click **"Sync All Yachts Now"**
3. Wait for completion

---

## 📋 Shortcode Reference

| Shortcode | Purpose | Where to Use |
|-----------|---------|--------------|
| `[yolo_search_widget]` | Search form | Homepage, search page |
| `[yolo_search_results]` | Search results | Dedicated results page |
| `[yolo_our_fleet]` | All yachts grid | Fleet browsing page |
| `[yolo_yacht_details]` | Individual yacht | Dedicated details page |

---

## 🚨 Troubleshooting

### No yachts showing?
✅ Click "Sync All Yachts Now" in admin  
✅ Check API key is correct  
✅ Wait for sync to complete

### Search not working?
✅ Make sure you selected "Search Results" page in settings  
✅ Verify that page has `[yolo_search_results]` shortcode

### View Details button goes to 404?
✅ Make sure you selected "Yacht Details" page in settings  
✅ Verify that page has `[yolo_yacht_details]` shortcode

### Images not loading?
✅ Run sync again  
✅ Check internet connection  
✅ Verify Booking Manager API is accessible

---

## 📞 Support

For issues or questions:
- Check this guide first
- Review README.md for technical details
- Contact: GitHub Issues

---

## ✨ You're Done!

Your yacht charter website is now ready with:
- ✅ Search functionality
- ✅ Fleet display
- ✅ Yacht details pages
- ✅ Database storage
- ✅ Beautiful design

**Next Steps:**
- Add search widget to more pages
- Customize colors to match your brand
- Set up weekly yacht sync
- Add booking/payment integration (future feature)

Enjoy! 🎉
