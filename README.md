# YOLO Yacht Search & Booking Plugin

**Version:** 41.21 🎨  
**Last Updated:** December 8, 2025 GMT+2  
**WordPress Plugin for Yacht Charter Search and Booking**

---

## 🎨 What's New in v41.21 - Text & Color Settings Audit Complete

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

## 📋 Complete Session Summary (December 8, 2025)

**Total Duration:** 6+ hours  
**Versions Released:** v41.9 through v41.21 (13 versions!)  
**Major Features Added:** Analytics & SEO, PDF Generator, Toastify, Settings Audit  
**Critical Bugs Fixed:** 15+  
**Files Modified:** 50+  
**Lines Changed:** 2,000+

---

## 🔥 Recent Versions Highlights

### v41.21 - Text & Color Settings Audit (CURRENT)
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

### v41.16 - Book Now Button Fix
- Fixed button staying greyed out after carousel selection
- Fixed duplicate const declaration bug

### v41.15 - Book Now Button Re-enable
- Button now re-enables when selecting available week from carousel

### v41.14 - Beautiful Notifications
- Replaced ugly browser alerts with Toastify toasts
- "Another customer just booked..." now shows as styled notification

### v41.13 - Professional PDF Generator
- Branded PDFs with company logo
- Navy blue & green color scheme
- Styled equipment tables
- Side-by-side signature boxes
- Terms & conditions page

### v41.12 - Check-in/Checkout Lists
- Added ajax_get_checkins handler
- Added ajax_get_checkouts handler
- Added ajax_upload_document handler

### v41.11 - Base Manager Critical Fixes
- Fixed check-in/checkout dropdowns not loading
- Fixed Save PDF button
- Fixed Send to Guest button
- Fixed Guest Dashboard permissions

### v41.10 - Nonce & JavaScript Fixes
- Fixed hardcoded nonces in check-in/checkout templates
- Removed conflicting base-manager.js from admin pages

### v41.9 - Settings Fixes
- Fixed FontAwesome setting not working
- Removed Stripe test mode checkbox (auto-detect now)

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

1. Download `yolo-yacht-search-v41.21.zip`
2. Upload to WordPress via Plugins → Add New → Upload
3. Activate plugin
4. Configure settings:
   - Booking Manager API credentials
   - Stripe API keys
   - Google Maps API key
   - GA4 Measurement ID (optional)
   - Facebook Pixel ID (optional)
5. Customize colors and texts via admin

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

---

## 📚 Documentation

- **CHANGELOG-v41.21.md** - Detailed changelog for v41.21
- **HANDOFF-v41.11-December8-2025.md** - Technical handoff document
- **ANALYTICS-SEO-SETUP-GUIDE.md** - GA4 and Facebook Pixel setup
- **BookingManagerAPIManual.md** - API integration guide
- **FEATURE-STATUS.md** - Feature implementation status
- **KNOWN-ISSUES.md** - Known issues and workarounds
- **PROJECT_LIBRARY.md** - Code library and snippets

---

## 🐛 Known Issues

None in v41.21! All critical bugs fixed.

---

## 🚀 Roadmap

### Completed in This Session:
- ✅ FontAwesome setting fix
- ✅ Stripe test mode removal
- ✅ Check-in/checkout dropdowns fix
- ✅ Base Manager buttons fix
- ✅ Guest permissions fix
- ✅ PDF generator upgrade
- ✅ Toastify migration
- ✅ Book Now button fix
- ✅ Search widget responsive fix
- ✅ Analytics & SEO system
- ✅ Text & color settings audit

### Future Enhancements:
- [ ] Multi-currency support
- [ ] Multi-language support (WPML)
- [ ] Advanced search filters
- [ ] Yacht comparison feature
- [ ] Customer reviews system
- [ ] Loyalty program integration

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

---

**Last Session:** December 8, 2025  
**Next Session:** TBD  
**Status:** ✅ Production Ready
