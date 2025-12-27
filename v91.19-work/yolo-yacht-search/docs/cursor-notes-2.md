# YOLO Yacht Search v81.12 - Deep Debug Report

## Version Confirmed
✅ **v81.12** - "Fail-safe Greek Ionian filtering"

---

## 1. Auto-Sync vs Manual Sync - Are They The Same?

### ✅ YES - They Now Use The Same Functionality (v81.0+)

| Sync Type | Class Used | Method |
|-----------|------------|--------|
| **Manual Yacht Sync** (Admin button) | `YOLO_YS_Progressive_Sync` | `init_yacht_sync()` → `sync_next_yacht()` |
| **Auto Yacht Sync** (WordPress cron) | `YOLO_YS_Progressive_Sync` | `init_yacht_sync()` → `sync_next_yacht()` |
| **Manual Price Sync** (Admin button) | `YOLO_YS_Progressive_Sync` | `init_price_sync()` → `sync_next_price()` |
| **Auto Price Sync** (WordPress cron) | `YOLO_YS_Progressive_Sync` | `init_price_sync()` → `sync_next_price()` |

### How Auto-Sync Calls Progressive Sync:

```php
// class-yolo-ys-auto-sync.php
public function start_progressive_yacht_sync() {
    $sync = new YOLO_YS_Progressive_Sync();
    $result = $sync->init_yacht_sync();  // Same as manual!
    
    // Then schedules step-by-step via cron
    wp_schedule_single_event(time() + 1, self::PROGRESSIVE_YACHT_HOOK);
}

public function progressive_yacht_step() {
    $sync = new YOLO_YS_Progressive_Sync();
    $result = $sync->sync_next_yacht();  // Same as manual!
}
```

### Old Sync Methods (Legacy - Not Used)

The old `YOLO_YS_Sync` class methods (`sync_all_yachts()`, `sync_all_offers()`) are still present but:
- ⚠️ NOT called by admin buttons (UI uses progressive sync)
- ⚠️ NOT called by auto-sync (uses progressive sync since v81.0)
- They could be removed or kept for backward compatibility

---

## 2. Greek Ionian Filtering

### ✅ Correctly Implemented in v81.12

**Location:** `class-yolo-ys-progressive-sync.php` → `init_yacht_sync()`

```php
// v81.12: Get Greek Ionian base IDs for client-side filtering
// FAIL-SAFE: If we can't get base IDs, we skip friend companies entirely
$greek_ionian_base_ids = $this->get_greek_ionian_base_ids();
$can_filter_friends = !empty($greek_ionian_base_ids);

if (!$can_filter_friends) {
    error_log("WARNING: Could not get Greek Ionian base IDs - friend companies will be SKIPPED");
}

// v81.12: For friend companies, filter to Greek Ionian bases only
if ($is_friend_company) {
    $yachts = array_filter($yachts, function($yacht) use ($greek_ionian_base_ids) {
        return isset($yacht['homeBaseId']) && in_array($yacht['homeBaseId'], $greek_ionian_base_ids);
    });
}
```

### Filtering Logic:
- **YOLO boats (company 7850):** No filter - sync all boats
- **Friend companies:** Filter by `homeBaseId` - only sync if base is in Greek Ionian
- **Fail-safe:** If can't get base IDs, skip friend companies entirely (don't sync unfiltered)

---

## 3. Price/Offers Sync Analysis

### ✅ Correctly Implemented in v81.12

**Location:** `class-yolo-ys-progressive-sync.php` → `sync_next_price()`

```php
// Fetch offers for this yacht using correct array parameter format
$offers = $this->api->get_offers(array(
    'yachtId' => $yacht_id,
    'dateFrom' => "{$year}-01-01T00:00:00",
    'dateTo' => "{$year}-12-31T23:59:59",
    'flexibility' => 6,
    'productName' => 'bareboat'
));

// Store new offers using batch insert (correct method)
YOLO_YS_Database_Prices::store_offers_batch($offers, $company_id);
```

### Key Points:
- ✅ Uses array parameter (not individual args)
- ✅ Uses `YOLO_YS_Database_Prices::store_offers_batch()` (not `$this->db->store_offers()`)
- ✅ Deletes old prices for yacht/year before inserting new ones

---

## 4. Two-Phase Yacht Sync

### ✅ Correctly Implemented

**Phase 1:** Sync yacht data only (fast, ~500ms per yacht)
**Phase 2:** Download images in batches of 3 per request

This prevents timeouts and memory issues.

```php
public function sync_next_yacht() {
    // Phase 1: Fetch fresh yacht data from API
    $yacht_result = $this->api->get_yacht($yacht_id);
    
    // Store yacht data WITHOUT images
    $this->db->store_yacht_data_only($yacht_data, $company_id);
    
    // When Phase 1 complete, start Phase 2
    if ($current_index >= count($queue)) {
        return $this->start_image_sync_phase($state, $queue);
    }
}
```

---

## 5. Potential Issues Found

### ⚠️ Issue 1: Image Queue Uses `yacht_data` Which May Not Exist

**Location:** `start_image_sync_phase()` line 344

```php
foreach ($yacht_queue as $yacht_item) {
    $yacht_data = $yacht_item['yacht_data'];  // ⚠️ This key doesn't exist!
```

**Problem:** The yacht queue stores only `yacht_id`, `yacht_name`, `company_id`, `image_count` (line 172-178).
It does NOT store `yacht_data` anymore (removed in v81.6 to prevent MySQL size limits).

**Impact:** Phase 2 (image sync) will fail with undefined index error.

**Fix Required:** Fetch yacht data fresh from API in `start_image_sync_phase()`:

```php
foreach ($yacht_queue as $yacht_item) {
    $yacht_id = $yacht_item['yacht_id'];
    $yacht_name = $yacht_item['yacht_name'];
    
    // Fetch fresh yacht data to get images
    $yacht_result = $this->api->get_yacht($yacht_id);
    if (!$yacht_result['success']) continue;
    $yacht_data = $yacht_result['data'];
    
    // Now process images...
}
```

### ⚠️ Issue 2: Old Sync Methods Still Have Different Logic

The old `YOLO_YS_Sync::sync_all_yachts()` does NOT filter by Greek Ionian.
If someone calls this method directly (e.g., via custom code), they'll get unfiltered yachts.

**Recommendation:** Either:
1. Deprecate the old methods
2. Add Greek Ionian filtering to old methods too
3. Make old methods call progressive sync internally

---

## 6. Sync Flow Diagram

### Manual Sync (Admin Button Click):

```
[Click "Sync Yachts Now" button]
     ↓
[JavaScript calls: yolo_progressive_init_yacht_sync]
     ↓
[PHP: ajax_progressive_init_yacht_sync()]
     ↓
[PHP: YOLO_YS_Progressive_Sync::init_yacht_sync()]
     ↓
[JavaScript polls: yolo_progressive_sync_next_yacht]
     ↓
[PHP: YOLO_YS_Progressive_Sync::sync_next_yacht()]
     ↓
[Repeat until done]
```

### Auto-Sync (WordPress Cron):

```
[Cron triggers: yolo_ys_auto_sync_yachts]
     ↓
[PHP: YOLO_YS_Auto_Sync::start_progressive_yacht_sync()]
     ↓
[PHP: YOLO_YS_Progressive_Sync::init_yacht_sync()]
     ↓
[Cron schedules: yolo_ys_progressive_yacht_sync]
     ↓
[PHP: YOLO_YS_Auto_Sync::progressive_yacht_step()]
     ↓
[PHP: YOLO_YS_Progressive_Sync::sync_next_yacht()]
     ↓
[Cron schedules next step, repeats until done]
```

---

## 7. Summary

| Feature | Status | Notes |
|---------|--------|-------|
| Manual/Auto use same code | ✅ Yes | Both use `YOLO_YS_Progressive_Sync` |
| Greek Ionian filtering | ✅ Working | Filters friend companies by homeBaseId |
| Fail-safe (no base IDs) | ✅ Working | Skips friend companies if can't filter |
| Price sync API call | ✅ Correct | Uses array params + batch insert |
| Two-phase yacht sync | ✅ Working | Data first, then images |
| Phase 2 image sync | ⚠️ Bug | Uses undefined `yacht_data` key |
| Old sync methods | ⚠️ Legacy | Still exist but not called |

---

## 8. CRITICAL FIX REQUIRED - Phase 2 Image Sync Bug

### 🐛 Bug Location
**File:** `class-yolo-ys-progressive-sync.php`
**Method:** `start_image_sync_phase()` - Line 344
**Severity:** CRITICAL - Phase 2 will fail completely

### Bug Description

Line 344 tries to access `$yacht_item['yacht_data']`:
```php
$yacht_data = $yacht_item['yacht_data'];  // ❌ UNDEFINED KEY!
```

But the yacht queue (lines 171-179) only stores:
```php
$yacht_queue[] = array(
    'yacht_id' => $yacht['id'],
    'yacht_name' => $yacht['name'],
    'company_id' => $company_id,
    'image_count' => isset($yacht['images']) ? count($yacht['images']) : 0
    // ❌ NO yacht_data key!
);
```

### Fix Required

Replace lines 343-361 in `start_image_sync_phase()`:

```php
foreach ($yacht_queue as $yacht_item) {
    $yacht_id = $yacht_item['yacht_id'];
    $yacht_name = $yacht_item['yacht_name'];
    
    // v81.13 FIX: Fetch fresh yacht data since we don't store it in queue
    // (yacht_data was removed in v81.6 to prevent MySQL size limits)
    try {
        $yacht_result = $this->api->get_yacht($yacht_id);
        if (!$yacht_result['success'] || empty($yacht_result['data'])) {
            error_log("YOLO Progressive Sync: Failed to get yacht data for images: {$yacht_name}");
            continue;
        }
        $yacht_data = $yacht_result['data'];
    } catch (Exception $e) {
        error_log("YOLO Progressive Sync: Exception getting yacht for images: " . $e->getMessage());
        continue;
    }
    
    if (isset($yacht_data['images']) && is_array($yacht_data['images'])) {
        $images = $yacht_data['images'];
        $batches = array_chunk($images, self::IMAGES_PER_BATCH);
        
        foreach ($batches as $batch_index => $batch) {
            $image_queue[] = array(
                'yacht_id' => $yacht_id,
                'yacht_name' => $yacht_name,
                'batch_index' => $batch_index,
                'images' => $batch,
                'is_first_batch' => ($batch_index === 0)
            );
        }
    }
}
```

### Why This Bug Exists

In v81.6, the `yacht_data` was removed from the queue to prevent exceeding MySQL `max_allowed_packet` limits.
But the `start_image_sync_phase()` method was not updated to fetch yacht data fresh from the API.

