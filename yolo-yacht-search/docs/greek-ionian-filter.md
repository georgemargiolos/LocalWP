# YOLO Yacht Search - Greek Ionian Sea Only Filter

## ⚠️ IMPORTANT API FINDING

**Verified via API testing:**

| Endpoint | `sailingAreaId` filter | Status |
|----------|----------------------|--------|
| `/bases?sailingAreaId=7` | ⚠️ Partial | Returns bases but includes non-Ionian areas |
| `/yachts?sailingAreaId=7` | ❌ IGNORED | Returns ALL yachts regardless |

**Conclusion:** We must filter by specific **Greek Ionian base IDs**.

---

## Greek Ionian Base IDs (41 bases)

These are the actual Greek Ionian bases (Lefkada, Corfu, Kefalonia, Zakynthos, Ithaca, Preveza, Paxos area):

```php
// Greek Ionian Base IDs - verified from API
define('YOLO_YS_GREEK_IONIAN_BASE_IDS', array(
    // Lefkada
    194,                    // D-Marin Marina Lefkas - Lefkada
    1639588120000100000,    // Port of Lefkas - Lefkada
    757699090000100000,     // Nikiana Marina - Lefkada
    1392047590000100000,    // Marina Perigiali - Lefkada
    1470547850000100000,    // Nydri Port - Nydri
    5720014270000100000,    // Nydri Marina - Nydri
    7263354840000100000,    // Hotel Armonia - Nydri
    6100618350000100000,    // Port of Vasiliki - Vasiliki
    4038352310000100000,    // Vliho Yacht Club - Vliho
    5018374970000100000,    // Vliho Bay - Vliho
    2863072060000100000,    // Sivota Marina Lefkada - Sivota
    6647930080000100000,    // Lygia Marina - Ligia
    
    // Corfu
    14,                     // Corfu harbor - Corfu
    492201260000100000,     // D-Marin Marina Gouvia - Gouvia
    3129817260000100000,    // Marina Benitses - Corfu
    3885298680000100000,    // Old Port - Corfu
    3885342670000100000,    // Corfu Sailing Club - Mandraki
    3143071690000100000,    // NAOK Sailing Club - Corfu
    4483668290000100000,    // Alipa port - Palaiokastritsas
    
    // Kefalonia
    699817710000100000,     // Argostoli Yacht Marina - Argostoli
    6368129940000100000,    // Fiskardo Marina - Fiskardo
    133,                    // Sami Port - Sami
    1472810110000100000,    // Agia Effimia - Agia Effimia
    3969163100000100000,    // Agia Pelagia Marina - Kefalonia
    
    // Zakynthos
    153,                    // Zakynthos Marina - Zakynthos
    23,                     // Zante - Zakynthos
    5714550710000100000,    // Agios Sostis Harbor - Zakynthos
    
    // Ithaca
    155,                    // Marina Ithakis - Vathy
    4837974350000100000,    // Port of Ithaca - Vathy
    
    // Preveza area
    89,                     // Preveza Marina - Preveza
    1935994390000100000,    // Preveza Main Port - Preveza
    2491645230000100000,    // Cleopatra Marina - Preveza
    6192088780000100000,    // Port of Mitikas - Mitikas
    6827132820000100000,    // Mytikas Port - Preveza
    3838448700000100000,    // Port of Plataria - Plataria
    1976257640000100000,    // Marina Sivota - Syvota
    973630110000100000,     // Marina of Vonitsa - Vonitsa
    395874570000100000,     // Marina Paleros - Palairos
    96447290000100000,      // Vounaki - Palairos
    3868266710000100000,    // Marina Astakos - Astakos
    
    // Paxos
    18,                     // Paxos - Gaios
));
```

---

## Implementation

### File 1: `yolo-yacht-search/yolo-yacht-search.php`

Add the constant after other defines:

```php
// Greek Ionian Base IDs for filtering friend company yachts
define('YOLO_YS_GREEK_IONIAN_BASE_IDS', array(
    194, 1639588120000100000, 757699090000100000, 1392047590000100000,
    1470547850000100000, 5720014270000100000, 7263354840000100000,
    6100618350000100000, 4038352310000100000, 5018374970000100000,
    2863072060000100000, 6647930080000100000, 14, 492201260000100000,
    3129817260000100000, 3885298680000100000, 3885342670000100000,
    3143071690000100000, 4483668290000100000, 699817710000100000,
    6368129940000100000, 133, 1472810110000100000, 3969163100000100000,
    153, 23, 5714550710000100000, 155, 4837974350000100000, 89,
    1935994390000100000, 2491645230000100000, 6192088780000100000,
    6827132820000100000, 3838448700000100000, 1976257640000100000,
    973630110000100000, 395874570000100000, 96447290000100000,
    3868266710000100000, 18
));
```

### File 2: `yolo-yacht-search/includes/class-yolo-ys-sync.php`

Add helper function:

```php
/**
 * Check if a yacht is based in Greek Ionian
 */
private function is_greek_ionian_yacht($yacht) {
    if (!isset($yacht['homeBaseId'])) {
        return false;
    }
    return in_array((int)$yacht['homeBaseId'], YOLO_YS_GREEK_IONIAN_BASE_IDS);
}
```

Modify `sync_all_yachts()`:

```php
public function sync_all_yachts() {
    $my_company = get_option('yolo_ys_my_company_id');
    $friend_companies = get_option('yolo_ys_friend_company_ids', '');
    
    // Sync YOLO boats (no filter - they're all Ionian)
    $yolo_yachts = $this->api->get_yachts(array('companyId' => (int)$my_company));
    foreach ($yolo_yachts as $yacht) {
        $this->db->store_yacht($yacht);
    }
    
    // Sync friend companies - FILTER to Greek Ionian only
    $friend_ids = array_filter(array_map('trim', explode(',', $friend_companies)));
    foreach ($friend_ids as $company_id) {
        $yachts = $this->api->get_yachts(array('companyId' => (int)$company_id));
        
        if (!is_array($yachts)) continue;
        
        foreach ($yachts as $yacht) {
            // Only store if yacht is in Greek Ionian
            if ($this->is_greek_ionian_yacht($yacht)) {
                $this->db->store_yacht($yacht);
            } else {
                // Mark as inactive if previously synced
                $this->db->mark_yacht_inactive($yacht['id']);
            }
        }
    }
}
```

### File 3: `yolo-yacht-search/includes/class-yolo-ys-progressive-sync.php`

Add the same helper and filter in `init_yacht_sync()`:

```php
private function is_greek_ionian_yacht($yacht) {
    if (!isset($yacht['homeBaseId'])) {
        return false;
    }
    return in_array((int)$yacht['homeBaseId'], YOLO_YS_GREEK_IONIAN_BASE_IDS);
}

// In init_yacht_sync(), when building the queue for friend companies:
foreach ($yachts as $yacht) {
    // Skip non-Greek-Ionian yachts for friend companies
    if ($is_friend_company && !$this->is_greek_ionian_yacht($yacht)) {
        error_log("Skipping non-Ionian yacht: {$yacht['name']} (base: {$yacht['homeBaseId']})");
        continue;
    }
    
    $queue[] = array(
        'yacht_id' => $yacht['id'],
        'yacht_name' => $yacht['name'],
        'company_id' => $company_id,
        'image_count' => count($yacht['images'] ?? array())
    );
}
```

---

## Summary

1. **41 Greek Ionian base IDs** hardcoded (more reliable than API filtering)
2. **YOLO company (7850)**: Sync all yachts (they're already all in Ionian)
3. **Friend companies**: Filter yachts by `homeBaseId` - only sync if in Greek Ionian list
4. **Non-Ionian yachts**: Mark as inactive (soft delete)

### Bases Included:
- **Lefkada**: D-Marin, Nydri, Vasiliki, Vliho, Sivota, Lygia, Nikiana
- **Corfu**: Gouvia, Harbor, Benitses, Mandraki, Palaiokastritsas
- **Kefalonia**: Argostoli, Fiskardo, Sami, Agia Effimia
- **Zakynthos**: Marina, Agios Sostis
- **Ithaca**: Vathy
- **Preveza**: Marina, Main Port, Cleopatra, Plataria, Sivota, Vonitsa, Paleros, Astakos
- **Paxos**: Gaios
