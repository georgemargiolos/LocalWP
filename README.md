# YOLO Yacht Search & Booking Plugin

**Version:** 50.0 📊  
**Last Updated:** December 9, 2025 GMT+2  
**WordPress Plugin for Yacht Charter Search and Booking**

---

## 🚀 What's New in v50.0 - Custom Gutenberg Blocks & Plugin Independence

This version introduces **custom Gutenberg blocks** for displaying yacht information and blog posts, plus removes Contact Form 7 dependency for better plugin independence.

### Major Features:

1. ✅ **Horizontal Yacht Cards Block** - Display YOLO yachts in beautiful horizontal cards
2. ✅ **Blog Posts Grid Block** - Display recent blog posts in responsive 3-column grid
3. ✅ **Contact Form 7 Independence** - Standalone CSS eliminates CF7 dependency
4. ✅ **Enhanced Yacht Details** - Map section anchoring for better navigation
5. ✅ **Theme Font Inheritance** - All blocks inherit WordPress theme fonts

### Horizontal Yacht Cards Block Features:

- **Image Carousel** - Swiper.js integration with logo overlay (max 100px)
- **Yacht Information** - Name, location, specs, description, pricing
- **Responsive Design** - Horizontal on desktop, stacks on mobile
- **Smart Linking** - Location links to yacht details map section (#yacht-map-section)
- **Description Preview** - 100-word excerpt with "Read more..." link
- **Front Page Support** - Works on homepage and all post types

### Blog Posts Grid Block Features:

- **Adjustable Count** - Display 1-12 posts via block settings
- **Featured Images** - Hover zoom effect for engagement
- **Category Badges** - Automatic display (hides "Uncategorized")
- **Responsive Grid** - 3→2→1 columns on different screen sizes
- **Custom Styling** - #1572F5 button color, 16px border-radius
- **Theme Integration** - Inherits all fonts from WordPress theme

### Key Changes:

1. ✅ **Standalone CSS** - Contact Form 7 styles now bundled in plugin
2. ✅ **Search Widget Update** - Background changed to #ffffff26
3. ✅ **Map Section ID** - Added yacht-map-section ID for anchor linking
4. ✅ **Block Registration** - Manual editor script enqueuing for reliability
5. ✅ **Front Page Filter** - Priority 999 for theme compatibility

### Setup Required:

1. Install and activate YOLO Yacht Search & Booking Plugin v50.0
2. Install standalone block plugins:
   - YOLO Horizontal Yacht Cards Block v50.0
   - YOLO Blog Posts Block v1.0.0
3. Add blocks via Gutenberg editor (search "YOLO")

---

## 🎨 v41.27 - Facebook Conversions API (Server-Side Tracking)

This version implements **true server-side Facebook Conversions API** tracking following Facebook's official best practices. Events are sent directly from your WordPress server to Facebook, providing superior data quality, better attribution, and cannot be blocked by browser ad blockers.

### Major Features:

1. ✅ **Server-Side Tracking** - 3 critical events sent from WordPress server
2. ✅ **Event Deduplication** - Unique event_id prevents double-counting
3. ✅ **User Data Hashing** - SHA-256 encryption for privacy compliance
4. ✅ **High Match Quality** - 8-10/10 score with comprehensive user data
5. ✅ **Non-Blocking** - Async requests won't slow down your site

### Key Changes:

1. ✅ **dataLayer Integration** - All events use dataLayer.push() instead of gtag()/fbq()
2. ✅ **GTM Compatibility** - Events visible in GTM Preview mode
3. ✅ **Flexible Routing** - Send to GA4, Facebook, or any platform via GTM
4. ✅ **Better Debugging** - Inspect dataLayer in browser console
5. ✅ **Future-Proof** - Easy to add new destinations without code changes

### Events Tracked:

- **search** - User searches for yachts
- **view_item** - User views yacht details
- **add_to_cart** - User selects a week/price
- **begin_checkout** - User clicks "Book Now"
- **add_payment_info** - User submits booking form
- **generate_lead** - User requests a quote
- **purchase** - Booking completed

### Setup Required:

See `/GTM_SETUP_GUIDE.md` for complete Google Tag Manager configuration instructions.

---

## 🎨 v41.21 - Text & Color Settings Audit Complete

This version implements the **comprehensive text and color settings audit**, fixing 2 critical bugs and adding **40+ new customization options**.

### Critical Bugs Fixed:

1. ✅ **Color Loading Bug** - Colors now load from individual options instead of non-existent array
2. ✅ **PHP in CSS File** - Removed PHP code from yacht-details-v3.css (was causing variables to fail)

### New Features:

- **5 New Color Settings** (16 total)
  - Lighter Background, Separator, Header BG, Disabled State, Loading Spinner
- **35 New Text Settings** (71 total)
  - Yacht Details (10), Extras (7), Technical Specs (3), Fleet (4), Quote Form (6), and more
- **All Templates Updated** - 18 hardcoded strings replaced with text helper functions

### Impact:

**Before:** 11 colors, 36 texts = 47 settings  
**After:** 16 colors, 71 texts = **87 settings** (+85% increase!)

All yacht details page text is now customizable via WordPress admin.

---

## 📋 Complete Session Summary (December 9, 2025)

**Total Duration:** 8+ hours  
**Versions Released:** v41.28 through v50.0  
**Major Features Added:** Custom Gutenberg Blocks, CF7 Independence  
**Standalone Plugins Created:** 2 (Horizontal Yacht Cards, Blog Posts Grid)  
**Files Modified:** 20+  
**Lines Changed:** 1,500+

---

## 🔥 Recent Versions Highlights

### v50.0 - Custom Gutenberg Blocks (CURRENT)
- Created YOLO Horizontal Yacht Cards Block v50.0
- Created YOLO Blog Posts Block v1.0.0
- Removed Contact Form 7 dependency
- Added standalone CSS for contact forms
- Updated search widget background to #ffffff26
- Added yacht-map-section ID for anchor linking
- All blocks inherit WordPress theme fonts
- Front page support for all blocks

### v41.27 - Facebook Conversions API
- Implemented true server-side Facebook Conversions API
- 3 events sent server-side (ViewContent, Lead, Purchase)
- 4 events sent client-side (Search, AddToCart, InitiateCheckout, AddPaymentInfo)
- Event deduplication with unique event_id
- User data hashing (email, phone, names)
- High event match quality (8-10/10)
- Added admin settings for Pixel ID and Access Token

### v41.26 - Google Tag Manager Integration
- Switched to dataLayer.push() for all events
- Removed direct gtag()/fbq() calls
- Added GTM compatibility
- Created comprehensive GTM setup guide
- 7 yacht booking events tracked

### v41.25 - Analytics Cleanup
- Removed GA4/FB Pixel base tracking from plugin
- Kept all 7 custom yacht booking events
- Integrated with external analytics plugins (PixelYourSite, Site Kit)
- Removed duplicate tracking
- Simplified analytics architecture

### v41.21 - Text & Color Settings Audit
- Fixed color loading bug in yacht-details-v3-styles.php
- Removed PHP code from yacht-details-v3.css
- Added 5 new color settings
- Added 35 new text settings
- Updated all templates with text helpers

### v41.20 - Critical Error Hotfix
- Fixed text-helpers.php filename error
- Fixed YOLO_YS_PLUGIN_URL undefined constant
- Removed duplicate require_once

### v41.19 - Analytics & SEO System
- Google Analytics 4 (GA4) integration
- Facebook Pixel tracking
- Open Graph tags for social sharing
- Twitter Cards
- Schema.org structured data
- Server-side conversion tracking

### v41.18 - Search Widget Responsive Fix
- Fixed date picker hidden on medium screens
- Added tablet breakpoint at 1024px
- Reduced fixed widths for better flexibility

### v41.17 - Toastify Migration Complete
- Replaced all 8 alert() calls with beautiful toasts
- Added payment explanation in booking modal
- Color-coded notifications (red=error, green=success, blue=info)

---

## 🎯 Current Plugin Capabilities

### Core Features:
- ✅ Yacht search widget with date picker
- ✅ Fleet display (YOLO + Partner boats)
- ✅ Yacht details page (v3 with pricing carousel)
- ✅ Stripe payment integration (50% deposit)
- ✅ Booking Manager API sync
- ✅ Guest user system with dashboard
- ✅ Base Manager (Check-in/Check-out with PDFs)
- ✅ Quote request system
- ✅ Contact messages system
- ✅ Auto-sync system (hourly)
- ✅ Warehouse notifications
- ✅ Admin documents system

### Custom Blocks (v50.0):
- ✅ Horizontal Yacht Cards Block
- ✅ Blog Posts Grid Block
- ✅ Front page support
- ✅ Theme font inheritance
- ✅ Responsive designs
- ✅ Swiper.js integration

### Analytics & SEO (v41.19):
- ✅ Google Analytics 4 (GA4)
- ✅ Facebook Pixel
- ✅ Server-side conversion tracking
- ✅ Open Graph tags
- ✅ Twitter Cards
- ✅ Schema.org structured data

### Customization (v41.21):
- ✅ 16 color settings (all UI elements)
- ✅ 71 text settings (all visible text)
- ✅ Icon management system
- ✅ Document templates

### Payment Features:
- ✅ 50% deposit at booking
- ✅ Balance payment page
- ✅ Stripe checkout
- ✅ Booking confirmation emails
- ✅ Payment tracking

### Base Manager Features:
- ✅ Check-in/Check-out forms
- ✅ Equipment checklists
- ✅ Damage reporting
- ✅ Signature capture
- ✅ PDF generation
- ✅ Email to guests
- ✅ Warehouse integration

### Guest Features:
- ✅ Guest dashboard
- ✅ Document upload (licenses, passports)
- ✅ Document signing
- ✅ Booking history
- ✅ Balance payment

---

## 📦 Installation

### Main Plugin:
1. Download `yolo-yacht-search-v50.0.zip`
2. Upload to WordPress via Plugins → Add New → Upload
3. Activate plugin
4. Configure settings:
   - Booking Manager API credentials
   - Stripe API keys
   - Google Maps API key
   - GA4 Measurement ID (optional)
   - Facebook Pixel ID (optional)
5. Customize colors and texts via admin

### Standalone Block Plugins:
1. Download `yolo-horizontal-yacht-cards-v50.0.zip`
2. Download `yolo-blog-posts-block-v1.0.0.zip`
3. Upload each to WordPress via Plugins → Add New → Upload
4. Activate both plugins
5. Add blocks via Gutenberg editor (search "YOLO")

---

## 🔧 Configuration

### Required Settings:
- **Booking Manager API:** Base URL, Username, Password
- **Stripe:** Publishable Key, Secret Key (test or live)
- **Google Maps:** API Key

### Optional Settings:
- **Analytics:** GA4 ID, Facebook Pixel ID
- **Colors:** 16 customizable colors
- **Texts:** 71 customizable text strings
- **Email:** SMTP settings for notifications

### Block Settings:
- **Horizontal Yacht Cards:** No settings (displays all YOLO yachts)
- **Blog Posts Grid:** Adjustable post count (1-12 posts)

---

## 📚 Documentation

- **CHANGELOG-v50.0.md** - Detailed changelog for v50.0
- **HANDOFF-v50.0.md** - Technical handoff document
- **BLOCKS-TRACKING.md** - Custom blocks documentation
- **GTM_SETUP_GUIDE.md** - Google Tag Manager setup
- **ANALYTICS-SEO-SETUP-GUIDE.md** - GA4 and Facebook Pixel setup
- **BookingManagerAPIManual.md** - API integration guide
- **FEATURE-STATUS.md** - Feature implementation status
- **KNOWN-ISSUES.md** - Known issues and workarounds
- **PROJECT_LIBRARY.md** - Code library and snippets

---

## 🐛 Known Issues

None in v50.0! All critical bugs fixed.

---

## 🚀 Roadmap

### Completed in v50.0:
- ✅ Custom Gutenberg blocks
- ✅ Contact Form 7 independence
- ✅ Horizontal yacht cards block
- ✅ Blog posts grid block
- ✅ Theme font inheritance
- ✅ Front page support
- ✅ Responsive designs

### Future Enhancements:
- [ ] Multi-currency support
- [ ] Multi-language support (WPML)
- [ ] Advanced search filters
- [ ] Yacht comparison feature
- [ ] Customer reviews system
- [ ] Loyalty program integration
- [ ] Additional custom blocks (testimonials, pricing tables, etc.)

---

## 📞 Support

For issues, feature requests, or questions:
- **Repository:** https://github.com/georgemargiolos/LocalWP
- **Issues:** https://github.com/georgemargiolos/LocalWP/issues

---

## 📝 License

GPL v2 or later

---

## 👨‍💻 Author

**George Margiolos**  
GitHub: [@georgemargiolos](https://github.com/georgemargiolos)

---

## 🙏 Credits

- **Stripe PHP Library** - Payment processing
- **Booking Manager API** - Yacht data sync
- **Toastify** - Beautiful notifications
- **Swiper** - Touch-enabled carousels
- **Flatpickr** - Date picker
- **FPDF** - PDF generation
- **FontAwesome** - Icons
- **Gutenberg** - WordPress block editor

---

**Last Session:** December 9, 2025  
**Next Session:** TBD  
**Status:** ✅ Production Ready
