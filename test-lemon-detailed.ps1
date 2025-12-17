# Detailed API Call Report for Lemon Yacht - First Week of June 2026
# This shows the EXACT live API call made

$companyId = '7850'
$dateFrom = '2026-06-01T00:00:00'
$dateTo = '2026-06-30T23:59:59'
$apiKey = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe'
$yachtId = '6362109340000107850'

# Build the exact URL that was called
$baseUrl = "https://www.booking-manager.com/api/v2/offers"
# URL encode the dates (T becomes %54, : becomes %3A)
$url = $baseUrl + "?companyId=" + $companyId + "&dateFrom=" + [System.Uri]::EscapeDataString($dateFrom) + "&dateTo=" + [System.Uri]::EscapeDataString($dateTo) + "&flexibility=6&productName=bareboat"

$headers = @{
    'Authorization' = $apiKey
    'Accept' = 'application/json'
}

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "LIVE API CALL DETAILS" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "REQUEST DETAILS:" -ForegroundColor Yellow
Write-Host "----------------"
Write-Host "Method: GET"
Write-Host "Base URL: $baseUrl"
Write-Host ""
Write-Host "Query Parameters:"
Write-Host "  - companyId: $companyId"
Write-Host "  - dateFrom: $dateFrom"
Write-Host "  - dateTo: $dateTo"
Write-Host "  - flexibility: 6 (in year - all Saturday departures)"
Write-Host "  - productName: bareboat"
Write-Host ""
Write-Host "Full URL:" -ForegroundColor Green
Write-Host $url -ForegroundColor White
Write-Host ""
Write-Host "Headers:"
Write-Host "  - Authorization: [API_KEY_HIDDEN]"
Write-Host "  - Accept: application/json"
Write-Host ""
Write-Host "Making live API call..." -ForegroundColor Yellow
Write-Host ""

try {
    $startTime = Get-Date
    $response = Invoke-WebRequest -Uri $url -Headers $headers -Method Get -TimeoutSec 60
    $endTime = Get-Date
    $duration = ($endTime - $startTime).TotalSeconds
    
    Write-Host "RESPONSE DETAILS:" -ForegroundColor Yellow
    Write-Host "----------------"
    Write-Host "HTTP Status Code: $($response.StatusCode)" -ForegroundColor Green
    Write-Host "Response Time: $([math]::Round($duration, 2)) seconds"
    Write-Host "Content Length: $($response.Content.Length) bytes"
    Write-Host ""
    
    # Parse JSON response
    $data = $response.Content | ConvertFrom-Json
    
    Write-Host "RESPONSE DATA:" -ForegroundColor Yellow
    Write-Host "--------------"
    Write-Host "Total Offers Returned: $($data.Count)" -ForegroundColor Green
    Write-Host ""
    
    # Filter for Lemon yacht
    $lemonOffers = $data | Where-Object { 
        $_.yachtId -eq $yachtId -or 
        $_.yacht -like '*Lemon*' -or
        $_.yacht -like '*LEMON*'
    }
    
    Write-Host "LEMON YACHT FILTERING:" -ForegroundColor Yellow
    Write-Host "----------------------"
    Write-Host "Target Yacht ID: $yachtId"
    Write-Host "Lemon Offers Found: $($lemonOffers.Count)" -ForegroundColor $(if ($lemonOffers.Count -gt 0) { "Green" } else { "Red" })
    Write-Host ""
    
    # Check specifically for first week of June
    $firstWeekOffers = $lemonOffers | Where-Object {
        $offerDateFrom = [DateTime]::Parse($_.dateFrom)
        $offerDateTo = [DateTime]::Parse($_.dateTo)
        # Check if offer overlaps with June 1-8, 2026
        ($offerDateFrom -le [DateTime]::Parse('2026-06-08') -and $offerDateTo -ge [DateTime]::Parse('2026-06-01'))
    }
    
    Write-Host "FIRST WEEK OF JUNE 2026 CHECK:" -ForegroundColor Yellow
    Write-Host "------------------------------"
    Write-Host "Target Date Range: June 1-8, 2026"
    Write-Host "Offers Found for First Week: $($firstWeekOffers.Count)" -ForegroundColor $(if ($firstWeekOffers.Count -gt 0) { "Green" } else { "Red" })
    Write-Host ""
    
    if ($firstWeekOffers.Count -gt 0) {
        Write-Host "✅ AVAILABILITY: AVAILABLE" -ForegroundColor Green
        Write-Host ""
        foreach ($offer in $firstWeekOffers) {
            Write-Host "Offer Details:" -ForegroundColor Cyan
            Write-Host "  Yacht: $($offer.yacht)"
            Write-Host "  Yacht ID: $($offer.yachtId)"
            Write-Host "  Date From: $($offer.dateFrom)"
            Write-Host "  Date To: $($offer.dateTo)"
            Write-Host "  Price: $($offer.price) $($offer.currency)"
            Write-Host "  Start Price: $($offer.startPrice) $($offer.currency)"
            if ($offer.discountPercentage) {
                Write-Host "  Discount: $($offer.discountPercentage)%"
            }
            Write-Host ""
        }
    } else {
        Write-Host "❌ AVAILABILITY: NOT AVAILABLE" -ForegroundColor Red
        Write-Host ""
        Write-Host "No offers found for Lemon yacht during June 1-8, 2026"
        Write-Host ""
        
        # Show closest available dates
        if ($lemonOffers.Count -gt 0) {
            Write-Host "CLOSEST AVAILABLE DATES:" -ForegroundColor Yellow
            Write-Host "------------------------"
            
            # Find offers in June 2026
            $juneOffers = $lemonOffers | Where-Object {
                $offerDateFrom = [DateTime]::Parse($_.dateFrom)
                $offerDateFrom.Month -eq 6 -and $offerDateFrom.Year -eq 2026
            } | Sort-Object { [DateTime]::Parse($_.dateFrom) }
            
            if ($juneOffers.Count -gt 0) {
                Write-Host "Next available dates in June 2026:" -ForegroundColor Green
                foreach ($offer in $juneOffers | Select-Object -First 3) {
                    $dateFrom = [DateTime]::Parse($offer.dateFrom)
                    $dateTo = [DateTime]::Parse($offer.dateTo)
                    Write-Host ""
                    Write-Host "  Week: $($dateFrom.ToString('MMMM d')) - $($dateTo.ToString('MMMM d, yyyy'))"
                    Write-Host "  Price: $($offer.price) $($offer.currency) (Start: $($offer.startPrice) $($offer.currency))"
                    if ($offer.discountPercentage) {
                        Write-Host "  Discount: $($offer.discountPercentage)%"
                    }
                }
            } else {
                Write-Host "No offers found in June 2026"
            }
        }
    }
    
    Write-Host ""
    Write-Host "ALL LEMON OFFERS IN DATE RANGE:" -ForegroundColor Yellow
    Write-Host "-------------------------------"
    if ($lemonOffers.Count -gt 0) {
        Write-Host "Total Lemon offers: $($lemonOffers.Count)"
        Write-Host ""
        foreach ($offer in $lemonOffers | Sort-Object { [DateTime]::Parse($_.dateFrom) }) {
            $dateFrom = [DateTime]::Parse($offer.dateFrom)
            $dateTo = [DateTime]::Parse($offer.dateTo)
            $isFirstWeek = ($dateFrom -le [DateTime]::Parse('2026-06-08') -and $dateTo -ge [DateTime]::Parse('2026-06-01'))
            $marker = if ($isFirstWeek) { " ⭐ FIRST WEEK" } else { "" }
            Write-Host "  $($dateFrom.ToString('yyyy-MM-dd')) to $($dateTo.ToString('yyyy-MM-dd')) - $($offer.price) $($offer.currency)$marker"
        }
    } else {
        Write-Host "No Lemon offers found in the requested date range"
    }
    
    # Save full response to file
    $outputFile = "lemon-api-response-full.json"
    $response.Content | Out-File -FilePath $outputFile -Encoding UTF8
    Write-Host ""
    Write-Host "Full API response saved to: $outputFile" -ForegroundColor Cyan
    
} catch {
    Write-Host ""
    Write-Host "❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Status Code: $statusCode" -ForegroundColor Red
        try {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            Write-Host "Error Response Body:"
            Write-Host $responseBody
        } catch {
            Write-Host "Could not read error response body"
        }
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "END OF REPORT" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

