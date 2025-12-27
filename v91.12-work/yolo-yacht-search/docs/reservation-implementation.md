# Booking Manager API - Quick Reference Guide

## Base URLs

| Environment | URL |
|-------------|-----|
| **Production** | `https://www.booking-manager.com/api/v2` |
| **Beta** | `http://beta.booking-manager.com/api/v2` |

## Authentication

```
Authorization: YOUR_API_KEY
```

Raw API key in the `Authorization` header (NOT "Bearer" prefix).

---

## ⚠️ CRITICAL: Reservations vs Options

### The Key Insight

**POST /reservation ALWAYS creates an OPTION (status 2), not a confirmed reservation!**

### Status Codes

| Status | Meaning |
|--------|---------|
| **0** | Available |
| **1** | Confirmed Reservation |
| **2** | Option |
| **3** | Option in expiration |
| **4** | Service |
| **B** | Sleepboard (for charters) |

### Correct Booking Flow

```
1. POST /reservation
   → Creates OPTION (status=2)
   → Returns reservation ID
   
2. PUT /reservation/{id}
   → Confirms option into RESERVATION (status=1)
```

### Code Example (PHP)

```php
// WRONG - Creates option only!
$result = $api->post('/reservation', $data);

// CORRECT - Creates and confirms reservation
// Step 1: Create option
$option = $api->post('/reservation', $data);
$reservation_id = $option['id'];

// Step 2: Confirm to reservation
$confirmed = $api->put('/reservation/' . $reservation_id);
```

---

## Key Endpoints

### Booking & Reservations

| Method | Endpoint | Description |
|--------|----------|-------------|
| **POST** | `/reservation` | Create OPTION (not reservation!) |
| **PUT** | `/reservation/{id}` | Confirm option → reservation |
| **GET** | `/reservation/{id}` | Get reservation details |
| **DELETE** | `/reservation/{id}` | Cancel option (cannot cancel confirmed) |
| **GET** | `/reservations/{year}` | List all reservations for year |

### Availability & Offers

| Method | Endpoint | Description |
|--------|----------|-------------|
| **GET** | `/offers` | Search available yachts by date |
| **GET** | `/specialOffers` | One-way and short-term offers |
| **GET** | `/availability/{year}` | Full availability data |
| **GET** | `/shortAvailability/{year}` | Binary/hex availability |
| **GET** | `/prices` | Get yacht pricing |

### Fleet Information

| Method | Endpoint | Description |
|--------|----------|-------------|
| **GET** | `/yachts` | List all yachts |
| **GET** | `/yacht/{id}` | Get yacht details |
| **GET** | `/companies` | List charter companies |
| **GET** | `/bases` | List bases/marinas |
| **GET** | `/sailingAreas` | List sailing areas |

### Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| **POST** | `/reservation/{id}/payments` | Create payment |
| **GET** | `/payments` | List payments |
| **PUT** | `/payments/{id}` | Update payment |
| **DELETE** | `/payments/{id}` | Delete payment |

---

## Reservation Schema

### Create Reservation (POST /reservation)

```json
{
  "dateFrom": "2025-07-05T12:00:00",
  "dateTo": "2025-07-12T11:59:00",
  "yachtId": 978989630000100225,
  "productName": "Bareboat",
  "baseFromId": 667400400000100000,
  "baseToId": 667400400000100000,
  "clientName": "John Smith",
  "clientId": 12345,
  "passengersOnBoard": 6,
  "currency": "EUR",
  "sendNotification": true
}
```

### Reservation Response

```json
{
  "id": 21982481150300797,
  "reservationCode": "25-00529",
  "dateFrom": "2025-07-05T12:00:00",
  "dateTo": "2025-07-12T11:59:00",
  "yachtId": 978989630000100225,
  "status": 2,
  "productName": "Bareboat",
  "baseFromId": 667400400000100000,
  "baseToId": 667400400000100000,
  "currency": "EUR",
  "clientName": "John Smith",
  "clientId": 18150190000100262,
  "basePrice": 1000.0,
  "discount": 25.0,
  "commission": 500.0,
  "finalPrice": 750.0,
  "clientPrice": 1250.0,
  "expirationDate": "2025-01-12T00:00:00",
  "creationDate": "2025-01-11T00:00:00",
  "confirmationDate": "2025-01-11T00:00:00",
  "securityDeposit": 2000.0,
  "items": [...],
  "paymentPlan": [...]
}
```

### Status Field Values

```
0 = Available
1 = Confirmed Reservation
2 = Option (DEFAULT when creating via API)
3 = Option Expiring
4 = Service
```

---

## Offers/Availability Search

### GET /offers Parameters

| Parameter | Required | Description |
|-----------|----------|-------------|
| `dateFrom` | ✅ | Start date (yyyy-MM-ddTHH:mm:ss) |
| `dateTo` | ✅ | End date |
| `companyId` | | Filter by company |
| `yachtId` | | Filter by yacht |
| `baseFromId` | | Filter by departure base |
| `sailingAreaId` | | Filter by sailing area |
| `minCabins` | | Minimum cabins |
| `maxCabins` | | Maximum cabins |
| `minLength` | | Minimum yacht length |
| `maxLength` | | Maximum yacht length |
| `kind` | | Yacht type (sailboat, catamaran, etc.) |
| `productName` | | bareboat, crewed, cabin |
| `showOptions` | | Show existing options |

### Offer Response

```json
{
  "yachtId": 6249881370000104674,
  "yacht": "PARADISE",
  "startBaseId": 2687769540000100000,
  "endBaseId": 2687769540000100000,
  "startBase": "Dubrovnik / Marina Frapa",
  "endBase": "Dubrovnik / Marina Frapa",
  "dateFrom": "2026-05-25 12:00:00",
  "dateTo": "2026-06-01 11:59:00",
  "product": "Crewed",
  "price": 31640.0,
  "currency": "EUR",
  "startPrice": 31640.0,
  "obligatoryExtrasPrice": 11200.0,
  "obligatoryExtras": [...],
  "paymentPlan": [...],
  "securityDeposit": 2000.0,
  "commissionPercentage": 15.0,
  "commissionValue": 4200.0,
  "discountPercentage": 0.0
}
```

---

## Date Format

**All dates must use:** `yyyy-MM-ddTHH:mm:ss`

Examples:
- `2025-07-05T12:00:00` (check-in at noon)
- `2025-07-12T11:59:00` (check-out before noon)

---

## Common Company IDs (YOLO)

| Company | ID |
|---------|-----|
| **YOLO Charters** | 7850 |
| Partner 1 | 4366 |
| Partner 2 | 3604 |
| Partner 3 | 6711 |

---

## Yacht Data Structure

```json
{
  "id": 8860335000797,
  "name": "Strawberry",
  "model": "Lagoon 440",
  "modelId": 428155400797,
  "kind": "Catamaran",
  "year": 2008,
  "homeBaseId": 429492270000100000,
  "homeBase": "Lefkada Marina",
  "companyId": 7850,
  "company": "YOLO Charters",
  "draught": 1.3,
  "beam": 7.4,
  "length": 13.6,
  "waterCapacity": 600,
  "fuelCapacity": 400,
  "wc": 4,
  "berths": 12,
  "cabins": 4,
  "maxPeopleOnBoard": 12,
  "deposit": 3000,
  "currency": "EUR",
  "images": [...],
  "equipment": [...],
  "products": [
    {
      "name": "Bareboat",
      "extras": [...]
    }
  ]
}
```

---

## Error Responses

| Code | Description |
|------|-------------|
| 400 | Bad request |
| 401 | Authorization missing or invalid |
| 404 | Entity not found |
| 422 | Unprocessable field |

---

## Recent API Changes (2024-2025)

### November 2025
- Added `kind` and `percentage` fields to Extras
- Added POST `/requests` for option extension requests
- Added payment CRUD operations

### December 2024
- Added `securityDeposit` to ReservationResponse

### October 2024
- Added `creationDate` and `confirmationDate` to ReservationResponse
- Note: `confirmationDate` on options equals `creationDate`

### August 2024
- Added `sendNotification` parameter to POST/PUT `/reservation`

---

## Resources

- **Swagger Docs:** https://app.swaggerhub.com/apis-docs/mmksystems/bm-api/
- **Support:** https://support.booking-manager.com/
- **API Introduction:** https://support.booking-manager.com/hc/en-us/articles/360015613639
