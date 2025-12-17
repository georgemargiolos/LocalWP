# AI Session Handoff - v20.9 - December 4, 2025

## ⚠️ CRITICAL: READ THIS BEFORE MAKING ANY CHANGES

### MISTAKES I MADE THIS SESSION (DO NOT REPEAT)

1. **Tried to "add Bootstrap 5" when it was ALREADY THERE since v20.3**
   - The plugin already uses Bootstrap 5 Grid (`container-fluid`, `row`, `col-lg-*`)
   - I broke everything by trying to add Bootstrap classes on top of existing Bootstrap
   - ALWAYS check existing code first before "improving" anything

2. **Added `overflow-x: hidden` on `html, body` which BREAKS `position: sticky`**
   - This was in `bootstrap-mobile-fixes.css` 
   - Sticky sidebar stopped working
   - Fixed in v20.9 by removing it and using `overflow-x: clip` on specific containers instead

3. **Reverted to wrong git commit and mixed old CSS Grid code with new Bootstrap Grid code**
   - Created a mess of incompatible layout systems
   - Had to restore from v20.7 ZIP file provided by user

4. **Over-engineered simple fixes**
   - User asked to fix sticky sidebar
   - I rewrote entire templates instead of finding the ONE CSS issue

---

## PROJECT OVERVIEW

**Plugin:** YOLO Yacht Search & Booking  
**Current Version:** 20.9  
**Location:** `yolo-yacht-search/`  
**GitHub:** https://github.com/georgemargiolos/LocalWP

### What It Does
- Yacht charter search and booking for YOLO Charters (Greece)
- Integrates with Booking Manager API v2
- Stripe payments (50% deposit)
- Guest user system with license uploads
- Admin booking management

### Company IDs
- YOLO: 7850 (prioritized in search results)
- Partners: 4366, 3604, 6711

---

## TECHNICAL ARCHITECTURE

### Layout System (v20.3+)
- **Bootstrap 5.3** - Grid system via CDN
- **DO NOT** add more Bootstrap - it's already fully integrated
- Main layout: `container-fluid` → `row` → `col-lg-8` / `col-lg-4`

### Key CSS Files
| File | Purpose |
|------|---------|
| `public/css/bootstrap-mobile-fixes.css` | 960+ lines of mobile responsive CSS |
| `public/templates/partials/yacht-details-v3-styles.php` | Yacht details page inline styles |
| `public/css/yacht-card.css` | Yacht card component styles |

### Sticky Sidebar (CRITICAL)
The yacht details page has a sticky booking sidebar. For it to work:

1. **Parent row needs:** `align-items: start` (or use Bootstrap's `align-items-start`)
2. **Sidebar column needs:** Bootstrap class `sticky-lg-top` OR custom CSS:
   ```css
   position: sticky;
   top: 100px;
   ```
3. **NO `overflow: hidden` or `overflow-x: hidden` on ANY ancestor** (html, body, containers)
   - Use `overflow-x: clip` instead if you need to prevent horizontal scroll

### API Integration
- **Base URL:** https://www.booking-manager.com/api/v2
- **Date Format:** `yyyy-MM-ddTHH:mm:ss` (CRITICAL - wrong format = API errors)
- **Auth:** Raw API key in Authorization header (NOT Bearer token)
- **Response:** Data wrapped in `value` property - must extract it

---

## FILE STRUCTURE

```
yolo-yacht-search/
├── yolo-yacht-search.php          # Main plugin file
├── includes/
│   ├── class-yolo-ys-database.php
│   ├── class-yolo-ys-booking-manager-api.php
│   ├── class-yolo-ys-stripe.php
│   ├── class-yolo-ys-shortcodes.php
│   ├── class-yolo-ys-sync.php
│   ├── class-yolo-ys-email.php
│   └── class-yolo-ys-guest-users.php
├── admin/
│   ├── class-yolo-ys-admin.php
│   └── class-yolo-ys-admin-bookings-manager.php
├── public/
│   ├── class-yolo-ys-public.php   # Enqueues styles/scripts
│   ├── css/
│   │   ├── bootstrap-mobile-fixes.css  # Mobile responsive (DON'T ADD overflow-x:hidden to body!)
│   │   └── yacht-card.css
│   └── templates/
│       ├── yacht-details-v3.php   # Main yacht details template
│       ├── our-fleet.php
│       ├── search-results.php
│       └── partials/
│           ├── yacht-details-v3-styles.php
│           ├── yacht-details-v3-scripts.php
│           └── yacht-card.php
└── stripe-php/                    # Stripe PHP SDK 13.16.0
```

---

## SHORTCODES

| Shortcode | Purpose |
|-----------|---------|
| `[yolo_search_widget]` | Search form |
| `[yolo_search_results]` | Search results grid |
| `[yolo_our_fleet]` | All yachts display |
| `[yolo_yacht_details]` | Single yacht page |
| `[yolo_booking_confirmation]` | Post-booking confirmation |
| `[yolo_balance_payment]` | Remaining balance payment |
| `[yolo_balance_confirmation]` | Balance payment confirmation |
| `[yolo_guest_login]` | Guest login form |
| `[yolo_guest_dashboard]` | Guest dashboard |
| `[yolo_contact_form]` | Contact form |

---

## KNOWN WORKING VERSIONS

| Version | Status | Notes |
|---------|--------|-------|
| v20.3 | ✅ Working | Sticky sidebar works, Bootstrap 5 Grid |
| v20.7 | ⚠️ Broken sticky | Added `overflow-x: hidden` to body |
| v20.9 | ✅ Fixed | Removed overflow-x: hidden, sticky works |

---

## USER PREFERENCES

- **"Run everything mode"** - Don't ask for confirmation, just do it
- **Minimal changes** - Don't over-engineer, fix only what's asked
- **Test before committing** - User will test on live site

---

## COMMON MISTAKES TO AVOID

1. ❌ Don't add Bootstrap - it's already there
2. ❌ Don't use `overflow-x: hidden` on body/html
3. ❌ Don't change HTML structure without understanding existing CSS
4. ❌ Don't revert to old commits without checking what version user has
5. ❌ Don't "improve" code that wasn't asked to be improved

---

## NEXT SESSION CHECKLIST

Before making ANY changes:
1. [ ] Read this handoff file
2. [ ] Check current version number in `yolo-yacht-search.php`
3. [ ] Understand the existing code structure
4. [ ] Make MINIMAL changes to fix the specific issue
5. [ ] Test that sticky sidebar still works after changes

---

## VERSION HISTORY (Recent)

- **v20.9** (Dec 4, 2025) - Fixed sticky sidebar by removing `overflow-x: hidden` from body
- **v20.8** - Attempted fix (wrong approach)
- **v20.7** - Broke sticky sidebar with overflow-x: hidden
- **v20.3** - Last known good version with working sticky sidebar

---

## CONTACT

User: George Margiolos
GitHub: https://github.com/georgemargiolos

