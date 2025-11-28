# Current Yacht Data Summary

## Data Source
**API Endpoint:** `GET /yachts?company={companyId}`

**Example for YOLO (Company 7850):**
```
https://api.bookingmanager.com/yachts?company=7850
```

---

## What We Currently Store in Database

### 1. Basic Yacht Information
| Field | Example | Stored In DB |
|-------|---------|--------------|
| ID | 7175166040000000001 | ✅ Yes |
| Company ID | 7850 | ✅ Yes |
| Name | "Strawberry" | ✅ Yes |
| Model | "Lagoon 440" | ✅ Yes |
| Shipyard ID | 1935994390000100000 | ✅ Yes |

### 2. Year Information
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Year of Build | 2008 | ✅ Yes |
| Refit Year | 2026 (parsed from "yearNote") | ✅ Yes |

### 3. Location
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Home Base | "Preveza Main Port" | ✅ Yes |

### 4. Dimensions (in meters, converted to feet for display)
| Field | Example (meters) | Stored In DB |
|-------|------------------|--------------|
| Length | 13.61 m | ✅ Yes |
| Beam | 7.70 m | ✅ Yes |
| Draft (Draught) | 1.30 m | ✅ Yes |

### 5. Capacity
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Cabins | 4 | ✅ Yes |
| WC (Heads) | 4 | ✅ Yes |
| Berths | 10 | ✅ Yes |
| Max People on Board | 10 | ✅ Yes |

### 6. Technical Specs
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Engine Power | 54 hp | ✅ Yes |
| Fuel Capacity | 400 l | ✅ Yes |
| Water Capacity | 700 l | ✅ Yes |

### 7. Description
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Description | "Solar Panels, Espresso Coffee..." | ✅ Yes |

### 8. Images (Separate Table)
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Image URL | https://... | ✅ Yes |
| Is Primary | true/false | ✅ Yes |
| Sort Order | 1, 2, 3... | ✅ Yes |

**Example for Strawberry:**
- 9 images total
- URLs like: `https://s3.eu-central-1.amazonaws.com/media.bookingmanager.com/...`

### 9. Products (Charter Types - Separate Table)
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Product Type | "Bareboat" | ✅ Yes |
| Is Default | true | ✅ Yes |
| Crewed by Default | false | ✅ Yes |

**Note:** Products table stores charter type info, but **NOT prices**

### 10. Equipment (Separate Table)
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Equipment Name | "Solar Panels" | ✅ Yes |
| Category | "Electronics" | ✅ Yes |

**Example Equipment List for Strawberry:**
- Solar Panels
- Espresso Coffee Machine
- Heating System
- Wi-Fi
- Outboard Engine
- Freezer
- Inverter
- Fridge
- Bathing platform
- Cooker/Stove
- Oven
- Sun Pads
- Outside Shower
- Radio CD / MP3 player / Audio system
- Flat screen TV
- Outside Deck Speakers
- Bimini
- Outside GPS plotter
- Liferaft
- Autopilot
- Electric winches
- Inside speakers
- Bow thruster
- Kitchen Utensils
- Dinghy
- Stand-up Paddle
- Snorkel kit
- Inside (saloon) GPS plotter
- Dinghy Engine (outboard)

### 11. Extras (Optional Add-ons - Separate Table)
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Extra Name | "Skipper" | ✅ Yes |
| Price | 200 | ✅ Yes |
| Currency | "EUR" | ✅ Yes |
| Unit | "per_night" | ✅ Yes |
| Obligatory | false | ✅ Yes |

**Example Extras for Strawberry:**
- Skipper - 200 EUR per_night
- Fishing Rod Set - 80 EUR per_week
- Inflatable Kayak - 120 EUR per_week
- Extra SUP - 80 EUR per_week
- Snorkeling Set - 50 EUR per_booking
- Beach Towels - 40 EUR per_person
- Crew Change - 200 EUR per_booking
- Wifi - 100 EUR per_booking
- Early Check-in - 100 EUR per_booking
- Extra Bed Linen - 80 EUR per_booking

### 12. Raw Data
| Field | Stored In DB |
|-------|--------------|
| Complete JSON | ✅ Yes (for backup/debugging) |

### 13. Sync Metadata
| Field | Example | Stored In DB |
|-------|---------|--------------|
| Last Synced | 2025-11-28 12:30:00 | ✅ Yes |

---

## What We DON'T Get (Not Available in /yachts Endpoint)

### ❌ Charter Prices
- **Weekly base price** - NOT available
- **Seasonal pricing** - NOT available
- **Discounts** - NOT available
- **Special offers** - NOT available

**Why?** Prices depend on:
- Specific dates (high/low season)
- Duration (1 week, 2 weeks, etc.)
- Base location
- Availability

**To get prices, we need to use:**
- `/offers` endpoint with date range and search criteria

---

## Database Tables

### 1. `wp_yolo_yachts` (Main Table)
Stores: ID, company_id, name, model, year, dimensions, capacity, technical specs, description

### 2. `wp_yolo_yacht_images`
Stores: yacht_id, image_url, is_primary, sort_order

### 3. `wp_yolo_yacht_products`
Stores: yacht_id, product_type, is_default, crewed_by_default

### 4. `wp_yolo_yacht_equipment`
Stores: yacht_id, equipment_name, category

### 5. `wp_yolo_yacht_extras`
Stores: yacht_id, name, price, currency, unit, obligatory

---

## Example: Complete Data for "Strawberry"

```json
{
  "id": "7175166040000000001",
  "company_id": 7850,
  "name": "Strawberry",
  "model": "Lagoon 440",
  "year_of_build": 2008,
  "refit_year": 2026,
  "home_base": "Preveza Main Port",
  "length": 13.61,
  "beam": 7.70,
  "draft": 1.30,
  "cabins": 4,
  "wc": 4,
  "berths": 10,
  "max_people_on_board": 10,
  "engine_power": 54,
  "fuel_capacity": 400,
  "water_capacity": 700,
  "description": "Solar Panels, Espresso Coffee Machine...",
  "images": [9 images],
  "products": ["Bareboat"],
  "equipment": [29 items],
  "extras": [10 items with prices]
}
```

---

## Summary

✅ **We have:** Complete yacht specifications, equipment, images, extras pricing

❌ **We don't have:** Weekly charter base prices (need `/offers` endpoint for that)

**Current display:** Yacht details page shows everything except weekly charter price
