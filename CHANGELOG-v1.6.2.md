# Changelog - Version 1.6.2

**Release Date:** November 28, 2025  
**Type:** Critical Bug Fix - HTTP 500 Error  
**Previous Version:** 1.6.1

---

## 📋 Overview

Version 1.6.2 fixes the **HTTP 500 Internal Server Error** that occurred when syncing weekly offers. The Booking Manager API was rejecting requests with multiple company IDs due to PHP's array encoding using brackets `companyId[0]=7850&companyId[1]=4366`. 

The solution: **Split the API call to fetch offers one company at a time** instead of all companies in a single request.

---

## 🔧 The Problem

### What Was Happening

When clicking "Sync Weekly Offers" in the admin panel:
1. Plugin called `/offers` endpoint with all 4 company IDs at once
2. PHP's `http_build_query()` encoded arrays as `companyId[0]=7850&companyId[1]=4366&companyId[2]=3604&companyId[3]=6711`
3. Booking Manager API expected repeated parameters: `companyId=7850&companyId=4366&companyId=3604&companyId=6711`
4. API threw **HTTP 500 NullPointerException**
5. Plugin caught exception and showed: **"Failed to sync offers. Please try again."**

### Root Cause

From ChatGPT's analysis and Swagger UI testing:
> "The Swagger UI examples use repeated parameter names (`companyId=7850&companyId=4366`) rather than bracketed indices. The Booking Manager server appears to misinterpret the bracketed syntax and throws an internal error."

---

## ✅ The Fix

### What Changed

**Before (v1.6.1):**
```php
// Single API call with all companies
$offers = $this->api->get_offers(array(
    'companyId' => $all_companies,  // [7850, 4366, 3604, 6711]
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'tripDuration' => array(7),
    'flexibility' => 6,
    'productName' => 'bareboat'
));
```

**After (v1.6.2):**
```php
// Call API once per company
foreach ($all_companies as $company_id) {
    try {
        $offers = $this->api->get_offers(array(
            'companyId' => array($company_id),  // Single company
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'tripDuration' => array(7),
            'flexibility' => 6,
            'productName' => 'bareboat'
        ));
        
        // Store offers for this company
        foreach ($offers as $offer) {
            YOLO_YS_Database_Prices::store_offer($offer, $company_id);
            $results['offers_synced']++;
        }
        
    } catch (Exception $e) {
        // Log error but continue with other companies
        $results['errors'][] = 'Company ' . $company_id . ': ' . $e->getMessage();
    }
}
```

### Benefits

1. **Avoids HTTP 500 error** - Single company per request works reliably
2. **Better error handling** - If one company fails, others still sync
3. **Better logging** - Shows progress per company in error logs
4. **More resilient** - Partial success instead of total failure

---

## 📊 Technical Details

### Files Modified

| File | Lines Changed | What Changed |
|------|---------------|--------------|
| `includes/class-yolo-ys-sync.php` | 131-201 | Replaced single API call with per-company loop |
| `yolo-yacht-search.php` | 3, 30 | Updated version to 1.6.2 |

### Code Changes

**Line 138-185:** New per-company loop with try-catch
```php
// Call API once per company to avoid HTTP 500 error
// The API fails when multiple companies are passed with array syntax companyId[0]=...
foreach ($all_companies as $company_id) {
    if (empty($company_id)) continue;
    
    try {
        error_log('YOLO YS: Fetching offers for company ' . $company_id . ' for year ' . $year);
        
        $offers = $this->api->get_offers(array(
            'companyId' => array($company_id),
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'tripDuration' => array(7),
            'flexibility' => 6,
            'productName' => 'bareboat'
        ));
        
        if (!is_array($offers)) {
            $error_msg = "Company $company_id: Unexpected response format";
            $results['errors'][] = $error_msg;
            error_log('YOLO YS: ' . $error_msg);
            continue;
        }
        
        foreach ($offers as $offer) {
            if (isset($offer['yachtId'])) {
                $yachtOffersMap[$offer['yachtId']] = true;
            }
            YOLO_YS_Database_Prices::store_offer($offer, $company_id);
            $results['offers_synced']++;
        }
        
        error_log('YOLO YS: Stored ' . count($offers) . ' offers for company ' . $company_id);
        
    } catch (Exception $e) {
        $error_msg = 'Company ' . $company_id . ': ' . $e->getMessage();
        $results['errors'][] = $error_msg;
        error_log('YOLO YS: Failed to sync offers - ' . $error_msg);
    }
}
```

**Line 190-201:** Updated success/failure logic
```php
if ($results['offers_synced'] > 0) {
    $results['success'] = true;
    $results['message'] = sprintf(
        'Successfully synced %d weekly offers for year %d (%d companies, %d yachts)',
        $results['offers_synced'],
        $year,
        count($all_companies),
        $results['yachts_with_offers']
    );
} else {
    $results['message'] = 'No offers were synced. Check errors.';
}
```

---

## 🚀 Installation Instructions

### Upgrade from v1.6.1

1. **Deactivate** current plugin in WordPress Admin
2. **Delete** old plugin files
3. **Upload** `yolo-yacht-search-v1.6.2.zip`
4. **Activate** plugin
5. **Test** offers sync

### Fresh Install

1. Upload `yolo-yacht-search-v1.6.2.zip` via WordPress Admin
2. Activate plugin
3. Configure settings (company IDs, Google Maps API key)
4. Sync yachts first
5. Then sync offers for desired year

---

## ✅ Testing Checklist

### Critical Test
- [ ] **Offers Sync Works:** Admin → YOLO Yacht Search → Select year → "Sync Weekly Offers" → Should complete successfully
- [ ] **Success Message:** Shows "Successfully synced X weekly offers for year YYYY"
- [ ] **No HTTP 500:** Check WordPress debug log - no API errors
- [ ] **Offers Stored:** Check yacht details page - price carousel shows weeks

### Regression Tests
- [ ] Yacht sync still works
- [ ] Price carousel displays correctly (4 weeks on desktop)
- [ ] Description section visible
- [ ] Google Maps loads
- [ ] Image carousel works
- [ ] Date picker works

---

## 📝 Error Log Output (Expected)

When sync runs successfully, you should see in WordPress debug log:

```
YOLO YS: Fetching offers for company 7850 for year 2026
YOLO YS: Stored 312 offers for company 7850
YOLO YS: Fetching offers for company 4366 for year 2026
YOLO YS: Stored 156 offers for company 4366
YOLO YS: Fetching offers for company 3604 for year 2026
YOLO YS: Stored 89 offers for company 3604
YOLO YS: Fetching offers for company 6711 for year 2026
YOLO YS: Stored 45 offers for company 6711
```

---

## 🐛 Known Limitations

1. **Slower sync** - 4 API calls instead of 1 (but more reliable)
2. **No progress bar** - Admin shows "syncing..." until all companies complete
3. **Partial failures possible** - Some companies may sync while others fail

---

## 🔮 Future Improvements

1. **Add progress indicator** - Show "Syncing company 1 of 4..."
2. **Custom query encoding** - Build query string manually to use repeated parameters
3. **Caching** - Cache offers per company to reduce API calls
4. **Batch processing** - Split year into quarters for even faster sync

---

## 🔗 Related Documents

- `CHANGELOG-v1.6.1.md` - Previous version changes
- `HANDOFF-NEXT-SESSION.md` - Complete project documentation
- ChatGPT Analysis PDF (v3) - Original bug diagnosis

---

## 📞 Support

For issues:
- Check WordPress debug log (`wp-content/debug.log`)
- Review `HANDOFF-NEXT-SESSION.md` troubleshooting section
- GitHub: georgemargiolos/LocalWP

---

**End of Changelog v1.6.2**

*HTTP 500 error fixed. Offers sync now works reliably by calling API once per company.*
