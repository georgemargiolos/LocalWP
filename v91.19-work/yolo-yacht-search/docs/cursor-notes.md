# YOLO Yacht Search - Advanced Filters Implementation

## Filters to Add:
1. Cabins
2. Max People
3. Heads/WC
4. Year Built
5. Year Refit
6. Length
7. Home Base
8. Solar Panels (equipment ID 46)
9. Bimini (equipment ID 7)

---

## File 1: Frontend Filter UI

### `yolo-yacht-search/public/templates/search-results.php`

Add this filter section after the existing search form:

```php
<!-- Advanced Filters Section -->
<div class="yolo-advanced-filters" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
    <div class="filters-toggle" style="cursor: pointer; font-weight: bold; margin-bottom: 15px;">
        <span class="toggle-icon">▼</span> Advanced Filters
    </div>
    
    <div class="filters-content" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
        
        <!-- Cabins Filter -->
        <div class="filter-group">
            <label for="filter-cabins">Cabins</label>
            <select id="filter-cabins" name="cabins" class="yolo-filter">
                <option value="">Any</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
                <option value="6">6+</option>
            </select>
        </div>
        
        <!-- Max People Filter -->
        <div class="filter-group">
            <label for="filter-max-people">Max People</label>
            <select id="filter-max-people" name="maxPeople" class="yolo-filter">
                <option value="">Any</option>
                <option value="2">2</option>
                <option value="4">4</option>
                <option value="6">6</option>
                <option value="8">8</option>
                <option value="10">10</option>
                <option value="12">12+</option>
            </select>
        </div>
        
        <!-- Heads/WC Filter -->
        <div class="filter-group">
            <label for="filter-wc">Heads/WC</label>
            <select id="filter-wc" name="wc" class="yolo-filter">
                <option value="">Any</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4+</option>
            </select>
        </div>
        
        <!-- Year Built Filter -->
        <div class="filter-group">
            <label for="filter-year-built">Year Built</label>
            <select id="filter-year-built" name="yearBuilt" class="yolo-filter">
                <option value="">Any</option>
                <option value="2024">2024+</option>
                <option value="2022">2022+</option>
                <option value="2020">2020+</option>
                <option value="2018">2018+</option>
                <option value="2015">2015+</option>
            </select>
        </div>
        
        <!-- Year Refit Filter -->
        <div class="filter-group">
            <label for="filter-year-refit">Year Refit</label>
            <select id="filter-year-refit" name="yearRefit" class="yolo-filter">
                <option value="">Any</option>
                <option value="2024">2024+</option>
                <option value="2023">2023+</option>
                <option value="2022">2022+</option>
                <option value="2020">2020+</option>
            </select>
        </div>
        
        <!-- Length Filter -->
        <div class="filter-group">
            <label for="filter-length">Length (m)</label>
            <select id="filter-length" name="length" class="yolo-filter">
                <option value="">Any</option>
                <option value="10">10m+</option>
                <option value="12">12m+</option>
                <option value="14">14m+</option>
                <option value="16">16m+</option>
                <option value="18">18m+</option>
            </select>
        </div>
        
        <!-- Home Base Filter -->
        <div class="filter-group">
            <label for="filter-home-base">Home Base</label>
            <select id="filter-home-base" name="homeBase" class="yolo-filter">
                <option value="">Any</option>
                <option value="Lefkada">Lefkada</option>
                <option value="Preveza">Preveza</option>
                <option value="Corfu">Corfu</option>
                <option value="Gouvia">Gouvia</option>
                <option value="Kefalonia">Kefalonia</option>
                <option value="Zakynthos">Zakynthos</option>
            </select>
        </div>
        
        <!-- Equipment Checkboxes -->
        <div class="filter-group filter-equipment">
            <label>Equipment</label>
            <div class="equipment-checkboxes">
                <label class="checkbox-label">
                    <input type="checkbox" id="filter-solar" name="equipment[]" value="46" class="yolo-filter-checkbox">
                    Solar Panels
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" id="filter-bimini" name="equipment[]" value="7" class="yolo-filter-checkbox">
                    Bimini
                </label>
            </div>
        </div>
        
    </div>
    
    <div class="filter-actions" style="margin-top: 15px;">
        <button type="button" id="apply-filters" class="btn btn-primary">Apply Filters</button>
        <button type="button" id="clear-filters" class="btn btn-secondary">Clear All</button>
    </div>
</div>

<style>
.yolo-advanced-filters .filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.yolo-advanced-filters label {
    font-weight: 500;
    font-size: 14px;
    color: #333;
}
.yolo-advanced-filters select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}
.yolo-advanced-filters .checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal;
    cursor: pointer;
}
.yolo-advanced-filters .equipment-checkboxes {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
</style>
```

---

## File 2: JavaScript - Collect & Send Filters

### `yolo-yacht-search/public/js/yolo-yacht-search-public.js`

Add/modify the search function to include filters:

```javascript
// Collect all filter values
function getFilterValues() {
    return {
        cabins: document.getElementById('filter-cabins')?.value || '',
        maxPeople: document.getElementById('filter-max-people')?.value || '',
        wc: document.getElementById('filter-wc')?.value || '',
        yearBuilt: document.getElementById('filter-year-built')?.value || '',
        yearRefit: document.getElementById('filter-year-refit')?.value || '',
        length: document.getElementById('filter-length')?.value || '',
        homeBase: document.getElementById('filter-home-base')?.value || '',
        equipment: Array.from(document.querySelectorAll('.yolo-filter-checkbox:checked'))
                       .map(cb => cb.value)
    };
}

// Modify existing search function to include filters
function performSearch() {
    const filters = getFilterValues();
    
    const formData = new FormData();
    formData.append('action', 'yolo_ys_search_yachts');
    formData.append('dateFrom', dateFrom);
    formData.append('dateTo', dateTo);
    formData.append('kind', kind);
    
    // Add filter parameters
    formData.append('cabins', filters.cabins);
    formData.append('maxPeople', filters.maxPeople);
    formData.append('wc', filters.wc);
    formData.append('yearBuilt', filters.yearBuilt);
    formData.append('yearRefit', filters.yearRefit);
    formData.append('length', filters.length);
    formData.append('homeBase', filters.homeBase);
    formData.append('equipment', JSON.stringify(filters.equipment));
    
    // ... rest of AJAX call
}

// Apply Filters button
document.getElementById('apply-filters')?.addEventListener('click', function() {
    performSearch();
});

// Clear Filters button
document.getElementById('clear-filters')?.addEventListener('click', function() {
    document.querySelectorAll('.yolo-filter').forEach(el => el.value = '');
    document.querySelectorAll('.yolo-filter-checkbox').forEach(el => el.checked = false);
    performSearch();
});

// Auto-apply on filter change (optional)
document.querySelectorAll('.yolo-filter, .yolo-filter-checkbox').forEach(el => {
    el.addEventListener('change', function() {
        performSearch();
    });
});
```

---

## File 3: PHP Backend - Process Filters

### `yolo-yacht-search/public/class-yolo-ys-public-search.php`

Modify `yolo_ys_ajax_search_yachts()`:

```php
function yolo_ys_ajax_search_yachts() {
    global $wpdb;
    
    // Existing parameters
    $date_from = isset($_POST['dateFrom']) ? sanitize_text_field($_POST['dateFrom']) : '';
    $date_to = isset($_POST['dateTo']) ? sanitize_text_field($_POST['dateTo']) : '';
    $kind = isset($_POST['kind']) ? sanitize_text_field($_POST['kind']) : '';
    
    // NEW: Filter parameters
    $cabins = isset($_POST['cabins']) ? intval($_POST['cabins']) : 0;
    $max_people = isset($_POST['maxPeople']) ? intval($_POST['maxPeople']) : 0;
    $wc = isset($_POST['wc']) ? intval($_POST['wc']) : 0;
    $year_built = isset($_POST['yearBuilt']) ? intval($_POST['yearBuilt']) : 0;
    $year_refit = isset($_POST['yearRefit']) ? intval($_POST['yearRefit']) : 0;
    $length = isset($_POST['length']) ? floatval($_POST['length']) : 0;
    $home_base = isset($_POST['homeBase']) ? sanitize_text_field($_POST['homeBase']) : '';
    $equipment_ids = isset($_POST['equipment']) ? json_decode(stripslashes($_POST['equipment']), true) : array();
    
    // Validate equipment IDs
    if (!is_array($equipment_ids)) {
        $equipment_ids = array();
    }
    $equipment_ids = array_map('intval', $equipment_ids);
    
    // Check required fields
    if (empty($date_from) || empty($date_to)) {
        wp_send_json_error(array('message' => 'Missing required date parameters'));
        return;
    }
    
    // Get company IDs
    $my_company_id = (int) get_option('yolo_ys_my_company_id', '7850');
    $friend_companies = get_option('yolo_ys_friend_companies', '4366,3604,6711');
    $friend_ids = array_map('intval', array_map('trim', explode(',', $friend_companies)));
    
    // Extract dates
    $search_date_from = substr($date_from, 0, 10);
    $search_date_to = substr($date_to, 0, 10);
    
    // Table names
    $prices_table = $wpdb->prefix . 'yolo_yacht_prices';
    $yachts_table = $wpdb->prefix . 'yolo_yachts';
    $images_table = $wpdb->prefix . 'yolo_yacht_images';
    $equipment_table = $wpdb->prefix . 'yolo_yacht_equipment';
    
    // Build SQL query
    $sql = "SELECT DISTINCT 
                y.id as yacht_id,
                y.name as yacht,
                y.model,
                y.slug,
                y.company_id,
                y.home_base as startBase,
                y.length,
                y.cabins,
                y.wc,
                y.berths,
                y.max_people_on_board,
                y.year_of_build,
                y.refit_year,
                y.raw_data,
                p.date_from,
                p.date_to,
                p.price,
                p.start_price,
                p.currency,
                p.discount_percentage as discount,
                'Bareboat' as product,
                (SELECT image_url FROM {$images_table} img WHERE img.yacht_id = y.id ORDER BY is_primary DESC, sort_order ASC LIMIT 1) as image_url
            FROM {$yachts_table} y
            INNER JOIN {$prices_table} p ON y.id = p.yacht_id
            WHERE p.date_from >= %s 
            AND p.date_from <= %s
            AND (y.status = 'active' OR y.status IS NULL)";
    
    $params = array($search_date_from, $search_date_to);
    
    // Filter by boat type
    if (!empty($kind)) {
        $type_map = array(
            'Sailing yacht' => 'Sail boat',
            'Catamaran' => 'Catamaran'
        );
        $db_type = isset($type_map[$kind]) ? $type_map[$kind] : $kind;
        $sql .= " AND y.type = %s";
        $params[] = $db_type;
    }
    
    // NEW: Cabins filter
    if ($cabins > 0) {
        if ($cabins >= 6) {
            $sql .= " AND y.cabins >= %d";
        } else {
            $sql .= " AND y.cabins = %d";
        }
        $params[] = $cabins;
    }
    
    // NEW: Max People filter
    if ($max_people > 0) {
        if ($max_people >= 12) {
            $sql .= " AND y.max_people_on_board >= %d";
        } else {
            $sql .= " AND y.max_people_on_board >= %d";
        }
        $params[] = $max_people;
    }
    
    // NEW: Heads/WC filter
    if ($wc > 0) {
        if ($wc >= 4) {
            $sql .= " AND y.wc >= %d";
        } else {
            $sql .= " AND y.wc >= %d";
        }
        $params[] = $wc;
    }
    
    // NEW: Year Built filter (minimum year)
    if ($year_built > 0) {
        $sql .= " AND y.year_of_build >= %d";
        $params[] = $year_built;
    }
    
    // NEW: Year Refit filter (minimum refit year)
    // Note: refit_year may be stored as int OR extracted from yearNote field
    // If your DB stores it as int, use this:
    if ($year_refit > 0) {
        $sql .= " AND y.refit_year >= %d";
        $params[] = $year_refit;
    }
    
    // Alternative if refit is stored in raw_data->yearNote as text like "Refit 2026":
    // if ($year_refit > 0) {
    //     $sql .= " AND CAST(REGEXP_SUBSTR(JSON_UNQUOTE(JSON_EXTRACT(y.raw_data, '$.yearNote')), '[0-9]{4}') AS UNSIGNED) >= %d";
    //     $params[] = $year_refit;
    // }
    
    // NEW: Length filter (minimum length)
    if ($length > 0) {
        $sql .= " AND y.length >= %f";
        $params[] = $length;
    }
    
    // NEW: Home Base filter (partial match)
    if (!empty($home_base)) {
        $sql .= " AND y.home_base LIKE %s";
        $params[] = '%' . $wpdb->esc_like($home_base) . '%';
    }
    
    // NEW: Equipment filter (yacht must have ALL selected equipment)
    if (!empty($equipment_ids)) {
        foreach ($equipment_ids as $eq_id) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM {$equipment_table} eq 
                WHERE eq.yacht_id = y.id AND eq.equipment_id = %d
            )";
            $params[] = $eq_id;
        }
    }
    
    // Order by YOLO first, then price
    $sql .= " ORDER BY CASE WHEN y.company_id = %d THEN 0 ELSE 1 END, p.price ASC";
    $params[] = $my_company_id;
    
    $results = $wpdb->get_results($wpdb->prepare($sql, $params));
    
    // ... rest of existing code to process results and send response
```

---

## File 4: CSS Styling

### `yolo-yacht-search/public/css/search-filters.css` (new file)

```css
/* Advanced Filters Styles */
.yolo-advanced-filters {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.filters-toggle {
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
    user-select: none;
}

.filters-toggle:hover {
    color: #3498db;
}

.toggle-icon {
    transition: transform 0.3s ease;
}

.filters-collapsed .toggle-icon {
    transform: rotate(-90deg);
}

.filters-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.filter-group label {
    font-weight: 500;
    font-size: 13px;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.filter-group select {
    padding: 10px 14px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.filter-group select:hover {
    border-color: #80bdff;
}

.filter-group select:focus {
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.15);
    outline: none;
}

.filter-equipment {
    grid-column: span 2;
}

.equipment-checkboxes {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: normal !important;
    font-size: 14px !important;
    text-transform: none !important;
    cursor: pointer;
    padding: 8px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #ced4da;
    transition: all 0.2s;
}

.checkbox-label:hover {
    border-color: #0066cc;
    background: #f0f7ff;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.filter-actions {
    margin-top: 20px;
    display: flex;
    gap: 12px;
    padding-top: 15px;
    border-top: 1px solid #dee2e6;
}

.filter-actions .btn {
    padding: 10px 24px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
}

.filter-actions .btn-primary {
    background: #0066cc;
    color: white;
    border: none;
}

.filter-actions .btn-primary:hover {
    background: #0052a3;
}

.filter-actions .btn-secondary {
    background: white;
    color: #495057;
    border: 1px solid #ced4da;
}

.filter-actions .btn-secondary:hover {
    background: #f8f9fa;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .filters-content {
        grid-template-columns: 1fr 1fr;
    }
    
    .filter-equipment {
        grid-column: span 2;
    }
    
    .filter-actions {
        flex-direction: column;
    }
    
    .filter-actions .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .filters-content {
        grid-template-columns: 1fr;
    }
    
    .filter-equipment {
        grid-column: span 1;
    }
}
```

---

## File 5: Enqueue CSS

### `yolo-yacht-search/public/class-yolo-ys-public.php`

Add to the `enqueue_styles()` method:

```php
public function enqueue_styles() {
    // Existing styles...
    
    // NEW: Search filters CSS
    wp_enqueue_style(
        'yolo-ys-search-filters',
        plugin_dir_url(__FILE__) . 'css/search-filters.css',
        array(),
        YOLO_YS_VERSION,
        'all'
    );
}
```

---

## Summary of Changes

| File | Changes |
|------|---------|
| `search-results.php` | Add filter HTML UI |
| `yolo-yacht-search-public.js` | Collect filter values, send with AJAX |
| `class-yolo-ys-public-search.php` | Process filters, modify SQL query |
| `css/search-filters.css` | NEW file for filter styling |
| `class-yolo-ys-public.php` | Enqueue new CSS file |

## Equipment IDs Reference

| ID | Equipment |
|----|-----------|
| 7 | Bimini |
| 46 | Solar Panels |

These are the two equipment filters requested. Additional equipment can be added using the same pattern with different IDs.

