# Booking Manager API Manual

## Your API Credentials

**API Key:** `1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe`

**Base URL:** `https://www.booking-manager.com/api/v2`

---

## Table of Contents

1. [Overview](#overview)
2. [API Documentation Resources](#api-documentation-resources)
3. [Authentication](#authentication)
4. [Key Endpoints](#key-endpoints)
5. [Example: Search for Available Boats](#example-search-for-available-boats)
6. [yolo-charters.com Integration Analysis](#yolo-charterscom-integration-analysis)
7. [Code Examples](#code-examples)

---

## Overview

The Booking Manager API is a RESTful web service provided by **MMK Systems** that enables yacht charter companies and agencies to:

- Publish yacht availabilities
- Automate booking processes
- Connect with external systems (websites, CRM, accounting software)
- Provide seamless online booking experiences

**Key Features:**
- Real-time availability data
- Pricing and special offers
- Reservation management
- Invoice and payment handling
- Fleet and yacht information

---

## API Documentation Resources

### Official Documentation

1. **Support Site:** https://support.booking-manager.com/
   - Introduction to REST API: https://support.booking-manager.com/hc/en-us/articles/360015613639-Introduction-to-REST-API
   - API User Manual: https://support.booking-manager.com/hc/en-us/articles/360011832159-Booking-Manager-API-User-Manual-REST

2. **Swagger Interactive Documentation:** https://app.swaggerhub.com/apis-docs/mmksystems/bm-api/
   - Live API testing interface
   - Complete endpoint reference
   - Request/response schemas

### API Servers

- **Production:** `https://www.booking-manager.com/api/v2`
- **Beta:** `http://beta.booking-manager.com/api/v2`

---

## Authentication

All API requests require authentication using your API key in the `Authorization` header.

### Header Format

```
Authorization: YOUR_API_KEY
```

### Example Request

```bash
curl -X GET "https://www.booking-manager.com/api/v2/companies" \
  -H "Authorization: 1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe" \
  -H "Accept: application/json"
```

---

## Key Endpoints

### Booking & Search

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/offers` | Search for available yacht offers |
| GET | `/specialOffers` | Get special offers and discounts |
| GET | `/availability/{year}` | Get detailed availability for a year |
| GET | `/shortAvailability/{year}` | Get simplified availability status |
| POST | `/reservation` | Create a new reservation |
| GET | `/reservation/{reservationId}` | Get reservation details |
| PUT | `/reservation/{reservationId}` | Update/confirm a reservation |
| DELETE | `/reservation/{reservationId}` | Cancel a reservation |

### Fleet & Yacht Information

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/companies` | List all charter companies |
| GET | `/company/{id}` | Get specific company details |
| GET | `/yachts` | List all yachts with filters |
| GET | `/yacht/{id}` | Get specific yacht details |
| GET | `/yachtTypes` | List yacht types (sailboat, catamaran, etc.) |
| GET | `/bases` | List all bases/marinas |
| GET | `/sailingAreas` | List sailing areas |

### Pricing & Payments

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/prices` | Get yacht pricing |
| GET | `/payments` | List all payments |
| POST | `/reservation/{id}/payments` | Create payment for reservation |
| GET | `/invoices/{type}` | Export invoices |

---

## Example: Search for Available Boats

### Endpoint

```
GET /offers
```

### Required Parameters

- `dateFrom` - Start date in format `yyyy-MM-dd'T'HH:mm:ss` (e.g., `2026-05-25T00:00:00`)
- `dateTo` - End date in format `yyyy-MM-dd'T'HH:mm:ss` (e.g., `2026-06-01T00:00:00`)

### Optional Parameters

- `kind` - Yacht type (e.g., "Sail boat", "Catamaran", "Motor yacht")
- `baseId` - Filter by specific base/marina
- `sailingAreaId` - Filter by sailing area
- `minLength` - Minimum yacht length
- `maxLength` - Maximum yacht length
- `minCabins` - Minimum number of cabins
- `maxPrice` - Maximum price
- `companyId` - Filter by specific charter company

### Example Request: Last Week of May 2026

```bash
curl -X GET "https://www.booking-manager.com/api/v2/offers?dateFrom=2026-05-25T00:00:00&dateTo=2026-06-01T00:00:00" \
  -H "Authorization: 1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe" \
  -H "Accept: application/json"
```

### Response Summary

**Total Results:** 1,255 available yacht offers

### Sample Response Structure

```json
[
  {
    "yachtId": 6249881370000104674,
    "yacht": "PARADISE",
    "startBaseId": 2687769540000100000,
    "endBaseId": 2687769540000100000,
    "startBase": "Dubrovnik / Marina Frapa Dubrovnik",
    "endBase": "Dubrovnik / Marina Frapa Dubrovnik",
    "dateFrom": "2026-05-25 12:00:00",
    "dateTo": "2026-06-01 11:59:00",
    "status": 0,
    "product": "Crewed",
    "price": 31640.0,
    "currency": "EUR",
    "startPrice": 31640.0,
    "obligatoryExtrasPrice": 11200.0,
    "obligatoryExtras": [
      {
        "id": 7126502880000104674,
        "name": "APA PARADISE 1 2026",
        "price": 11200.0,
        "currency": "EUR",
        "payableInBase": true,
        "description": ""
      }
    ],
    "paymentPlan": [
      {
        "date": "2025-12-05 17:15:57",
        "amount": 15820.0
      },
      {
        "date": "2026-04-25 00:00:00",
        "amount": 15820.0
      }
    ],
    "discounts": [],
    "securityDeposit": 0.0,
    "commissionPercentage": 15.0,
    "commissionValue": 4200.0,
    "discountPercentage": 0.0
  }
]
```

### Response Fields Explained

| Field | Description |
|-------|-------------|
| `yachtId` | Unique identifier for the yacht |
| `yacht` | Yacht name |
| `startBase` / `endBase` | Departure and arrival marina |
| `dateFrom` / `dateTo` | Charter period |
| `status` | Availability status (0 = available) |
| `product` | Charter type (Bareboat, Crewed, etc.) |
| `price` | Total charter price |
| `currency` | Price currency |
| `obligatoryExtras` | Required additional costs |
| `paymentPlan` | Payment schedule |
| `discounts` | Applied discounts |
| `securityDeposit` | Required security deposit |
| `commissionPercentage` | Agency commission rate |

---

## yolo-charters.com Integration Analysis

### Architecture Overview

The yolo-charters.com website uses a **hybrid server-side/client-side architecture** to integrate with the Booking Manager API:

```
User Browser (JavaScript)
    ↓ (XMLHttpRequest)
yolo-charters.com/templates/*.jsp (Server-side middleware)
    ↓ (REST API call)
Booking Manager REST API (External service)
    ↓ (JSON Response)
Back to user's browser
```

### Why This Architecture?

1. **Security:** API keys remain on the server, not exposed in client-side code
2. **Flexibility:** Server-side code can transform/filter API responses
3. **Performance:** Server can cache responses and reduce API calls
4. **Compliance:** Easier to implement business logic and validation

### Client-Side Code (Inline Scripts)

The homepage contains inline JavaScript that makes AJAX calls to server-side JSP templates:

#### Function: `executeRequest(link)`

```javascript
function executeRequest(link){
    var xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    xmlhttp.onreadystatechange=function(){
        if (xmlhttp.readyState==4 && xmlhttp.status==200){
            document.getElementById("queueContainer").innerHTML = xmlhttp.responseText;
            // ... additional DOM manipulation
        }
    }
    xmlhttp.open("POST",link,true);
    xmlhttp.send();
}
```

#### Server-Side Endpoints

1. **`templates/priceQuoteList.jsp`** - Manages quote/wishlist
   - Actions: `getQueue`, `addToQueue`, `deleteFromQueue`, `changeDiscount`, `clearQueue`
   
2. **`templates/sendPriceQuote.jsp`** - Sends price quotes via email

### Search Flow

1. User fills out search form with dates and preferences
2. Form submits to: `/en/charter/search-results.html?view=SearchResult&filter_date=...`
3. Server-side code receives parameters
4. Server makes API call to Booking Manager `/offers` endpoint
5. Server receives yacht availability data
6. Server renders HTML with results
7. HTML is sent back to user's browser

### External Scripts Analysis

| File | Purpose | API Calls? |
|------|---------|------------|
| `plugins.min.js` | Bundled third-party libraries (jQuery, Litepicker, etc.) | Contains `XMLHttpRequest` but no direct Booking Manager calls |
| `custom.min.js` | Custom UI/UX logic, form validation, navigation | No direct API calls |
| `mobilefriendly.js` | Litepicker date picker plugin for mobile | No API calls |

**Key Finding:** The minified JavaScript files do not contain direct calls to the Booking Manager API. All API communication happens server-side through JSP templates.

---

## Code Examples

### Python Example: Search for Boats

```python
import requests
from datetime import datetime

# Your API credentials
API_KEY = "1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe"
BASE_URL = "https://www.booking-manager.com/api/v2"

# Set up headers
headers = {
    "Authorization": API_KEY,
    "Accept": "application/json"
}

# Search parameters
params = {
    "dateFrom": "2026-05-25T00:00:00",
    "dateTo": "2026-06-01T00:00:00",
    "kind": "Sail boat",  # Optional: filter by yacht type
    "minCabins": 3        # Optional: minimum cabins
}

# Make API request
response = requests.get(f"{BASE_URL}/offers", headers=headers, params=params)

# Check response
if response.status_code == 200:
    offers = response.json()
    print(f"Found {len(offers)} available yachts")
    
    # Print first offer
    if offers:
        first_offer = offers[0]
        print(f"\nExample yacht:")
        print(f"  Name: {first_offer['yacht']}")
        print(f"  Base: {first_offer['startBase']}")
        print(f"  Price: {first_offer['price']} {first_offer['currency']}")
        print(f"  Product: {first_offer['product']}")
else:
    print(f"Error: {response.status_code}")
    print(response.text)
```

### JavaScript Example: Search for Boats

```javascript
const API_KEY = "1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe";
const BASE_URL = "https://www.booking-manager.com/api/v2";

async function searchYachts(dateFrom, dateTo) {
    const url = new URL(`${BASE_URL}/offers`);
    url.searchParams.append('dateFrom', dateFrom);
    url.searchParams.append('dateTo', dateTo);
    
    try {
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': API_KEY,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const offers = await response.json();
        console.log(`Found ${offers.length} available yachts`);
        
        return offers;
    } catch (error) {
        console.error('Error fetching offers:', error);
        throw error;
    }
}

// Usage
searchYachts('2026-05-25T00:00:00', '2026-06-01T00:00:00')
    .then(offers => {
        if (offers.length > 0) {
            console.log('First yacht:', offers[0].yacht);
            console.log('Price:', offers[0].price, offers[0].currency);
        }
    });
```

### cURL Example: Get Company Information

```bash
curl -X GET "https://www.booking-manager.com/api/v2/companies" \
  -H "Authorization: 1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe" \
  -H "Accept: application/json"
```

### cURL Example: Get Specific Yacht Details

```bash
curl -X GET "https://www.booking-manager.com/api/v2/yacht/6249881370000104674" \
  -H "Authorization: 1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe" \
  -H "Accept: application/json"
```

---

## Common API Patterns

### Date Format

All date parameters must use the format: `yyyy-MM-dd'T'HH:mm:ss`

**Examples:**
- `2026-05-25T00:00:00` (May 25, 2026 at midnight)
- `2026-06-01T23:59:59` (June 1, 2026 at 11:59 PM)

### Error Handling

The API returns descriptive error messages when parameters are invalid:

```
dateFrom is obligatory parameter and must be in format yyyy-MM-dd'T'HH:mm:ss!
```

### Pagination

For large result sets, the API may support pagination (check the Swagger documentation for specific endpoints).

### Rate Limiting

Check with Booking Manager support for any rate limiting policies on API requests.

---

## Testing the API

### Using Swagger UI

1. Go to https://app.swaggerhub.com/apis-docs/mmksystems/bm-api/
2. Click the **"Authorize"** button
3. Enter your API key: `1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe`
4. Navigate to any endpoint (e.g., `/offers`)
5. Click **"Try it out"**
6. Fill in parameters
7. Click **"Execute"**
8. View the response

### Using Postman

1. Create a new GET request
2. URL: `https://www.booking-manager.com/api/v2/offers`
3. Add header: `Authorization` = `1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe`
4. Add query parameters: `dateFrom`, `dateTo`
5. Send request

---

## Support & Resources

### Official Support

- **Support Site:** https://support.booking-manager.com/
- **Contact:** Check the Booking Manager website for contact information
- **Documentation:** https://app.swaggerhub.com/apis-docs/mmksystems/bm-api/

### Related Resources

- **MMK Systems Website:** https://www.mmksystems.com/
- **Booking Manager Website:** https://www.booking-manager.com/
- **API Product Page:** https://www.booking-manager.com/en/products/rest-api.html

---

## Appendix: API Test Results

### Test Date: November 28, 2025

**Test Query:** Search for available yachts from May 25 - June 1, 2026

**API Endpoint:**
```
GET https://www.booking-manager.com/api/v2/offers?dateFrom=2026-05-25T00:00:00&dateTo=2026-06-01T00:00:00
```

**Results:**
- **Total Offers:** 1,255 available yachts
- **Response Time:** ~2 seconds
- **Status:** Success (200 OK)

**Sample Yachts Found:**
1. PARADISE - Dubrovnik - €31,640 (Crewed)
2. Karla - Paros - €7,600 (Bareboat)
3. 24M Deluxe Maiora Motoryacht - Bodrum - €23,275 (Crewed)
4. CARIBBEAN DANDY - Bormes-les-Mimosas - €4,100 (Bareboat)
5. Eragon - Seget Donji - (Price varies)

---

## Security Notes

⚠️ **Important:** Keep your API key secure!

- Do not commit API keys to public repositories
- Do not expose API keys in client-side code
- Use environment variables or secure configuration files
- Rotate API keys periodically
- Use server-side middleware for production applications (like yolo-charters.com does)

---

**Document Version:** 1.0  
**Last Updated:** November 28, 2025  
**Author:** Manus AI Agent  
**For:** yolo-charters.com API Integration Analysis
