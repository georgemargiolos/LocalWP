# GET /yachts Endpoint

## Endpoint
`GET /yachts`

## Description
Provides a list of yachts with all the detailed information and description of the boat. Description contains boat technical specifications, images and dedicated equipment categories. Also, information about extra services for different products (Bareboat, Crewed, Cabin, Flotilla, Regatta and All-inclusive) are visible. One boat can have multiple products assigned (e.g. Bareboat and Crewed) at the same time, therefore own combination of applicable extras.

## Parameters

| Name | Type | Description | Required |
|------|------|-------------|----------|
| language | query | Translation language | No |
| | | Available values: cs, de, el, en, en_US, es, fi, fr, hr, hu, it, iw, ko, lt, lv, nl, no, pl, pt, ro, ru, sk, sl, sv, tr, ua, zh | |
| **companyId** | query | **company id** | **No** |
| | | integer(int64) | |
| | | Example: 123456789 | |
| currency | query | currency | No |
| | | string | |
| | | Example: EUR | |

## Response
Returns an array of yacht objects with:
- id - unique resource id
- name - the name of the individual yacht
- model - name of the model
- shipyardId - id of the shipyard (boat manufacturer)
- images - yacht images
- equipment - yacht equipment
- products - different charter products (Bareboat, Crewed, etc.)
- extras - extra services for different products

## Usage for Company 7850

To get all yachts for YOLO (company ID 7850):

```
GET https://www.booking-manager.com/api/v2/yachts?companyId=7850
```

Headers:
```
Authorization: 1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe
Accept: application/json
```

This will return ALL yachts for company 7850 with complete information!
