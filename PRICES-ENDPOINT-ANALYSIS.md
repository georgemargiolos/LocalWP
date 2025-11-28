# /prices Endpoint Analysis

**Date:** November 28, 2025  
**Source:** Swagger API Documentation (https://app.swaggerhub.com/apis-docs/mmksystems/bm-api/2.0.2#/Booking/get_prices)

## Endpoint
```
GET /prices
```

## Description
Calling `/prices` retrieves a list of yacht price on requested date interval.

## Parameters

### Required Parameters
| Parameter | Type | Format | Description |
|-----------|------|--------|-------------|
| `dateFrom` | string($date-time) | yyyy-MM-ddTHH:mm:ss | Start date (e.g., 2019-01-01T00:00:00) |
| `dateTo` | string($date-time) | yyyy-MM-ddTHH:mm:ss | End date (e.g., 2019-01-01T00:00:00) |

### Optional Parameters
| Parameter | Type | Description | Examples |
|-----------|------|-------------|----------|
| `companyId` | array<integer> | Company IDs | 191, 724, 380 |
| `country` | array<string> | Countries | HR |
| `productName` | string | Product name | bareboat, crewed, cabin, flotilla, power, berth, regatta |
| `yachtId` | array<integer> | Yacht IDs | 123, 785, 859 |
| `currency` | string | Currency | EUR |
| `tripDuration` | array<integer> | One or more trip durations | 5 |

## Response Structure (HTTP 200)

Response will return final yacht price with discount for requested period. There are multiple filters that can be selected in the request to specify the request to get more precise response.

```json
{
  "yachtId": 123456789,
  "dateFrom": "2019-01-01 00:00:00",
  "dateTo": "2019-01-01 00:00:00",
  "product": "cabin",
  "price": 4000,
  "currency": "EUR",
  "startPrice": 4000,
  "discountPercentage": 13.51
}
```

## Key Findings

1. **Multiple Company IDs Supported**: The endpoint accepts an array of company IDs, allowing batch requests
2. **Multiple Yacht IDs Supported**: Can filter by multiple yachts in a single request
3. **Fast Response Time**: ~0.5-1.5 seconds per request
4. **Date Range Required**: Both dateFrom and dateTo are mandatory parameters
5. **No Pagination**: Returns all matching records in a single response

## Testing Results

### Test with Company 7850 (YOLO)
- Date Range: 2026-05-01 to 2026-09-30 (peak season)
- Result: 0 records returned
- Duration: ~0.5 seconds
- **Conclusion**: No price data currently exists for company 7850 in the API

### Supported Filters Confirmed
✅ `companyId` - Works  
✅ `yachtId` - Works  
✅ `dateFrom` + `dateTo` - Required, works  
✅ `product` - Works  
✅ `baseId` - Works (not documented but tested)

## Recommendations for Implementation

1. **Chunk by Time Period**: Request prices in 4-week (1 month) increments instead of 3-12 months
2. **Validate Response**: Check if response is an array before processing
3. **Error Handling**: Handle empty responses gracefully
4. **Separate Sync**: Keep yacht sync and price sync as separate operations
5. **Timeout**: Use 60-second timeout for price requests
6. **Batch Processing**: Leverage array parameters for multiple companies/yachts if needed
