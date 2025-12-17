# Test Booking Manager API for Lemon yacht availability
# First week of June 2026: June 1-8, 2026

$companyId = '7850'
$dateFrom = '2026-06-01T00:00:00'
$dateTo = '2026-06-08T00:00:00'
$apiKey = '1d40-2e6fd6f2efe772cf331d6b748aa2f2e5ada768f4a9ff1c176e457c26dfb05eb806b2d0eab200e3a1a6e49aebf9074d84347878823b4808faa1bd281b5db5f9fe'
$yachtId = '6362109340000107850'

$url = "https://www.booking-manager.com/api/v2/offers?companyId=$companyId&dateFrom=$dateFrom&dateTo=$dateTo"
$headers = @{
    'Authorization' = $apiKey
    'Accept' = 'application/json'
}

Write-Host "=== Lemon Yacht Availability Check ===" -ForegroundColor Cyan
Write-Host "Yacht: Lemon (ID: $yachtId)"
Write-Host "Date Range: June 1-8, 2026"
Write-Host "Company ID: $companyId"
Write-Host ""
Write-Host "Making API call..." -ForegroundColor Yellow

try {
    $response = Invoke-WebRequest -Uri $url -Headers $headers -Method Get -TimeoutSec 60
    Write-Host "HTTP Status: $($response.StatusCode)" -ForegroundColor Green
    
    $data = $response.Content | ConvertFrom-Json
    Write-Host "Response received. Total offers: $($data.Count)" -ForegroundColor Green
    
    # Filter for Lemon yacht
    $lemonOffers = $data | Where-Object { 
        $_.yachtId -eq $yachtId -or 
        $_.yacht -like '*Lemon*' -or
        $_.yacht -like '*LEMON*'
    }
    
    if ($lemonOffers.Count -gt 0) {
        Write-Host ""
        Write-Host "✅ AVAILABILITY: Lemon appears to be AVAILABLE for June 1-8, 2026" -ForegroundColor Green
        Write-Host "Found $($lemonOffers.Count) offer(s) for Lemon"
        Write-Host ""
        
        foreach ($offer in $lemonOffers) {
            Write-Host "---" -ForegroundColor Gray
            Write-Host "Yacht: $($offer.yacht)"
            Write-Host "Yacht ID: $($offer.yachtId)"
            Write-Host "Date From: $($offer.dateFrom)"
            Write-Host "Date To: $($offer.dateTo)"
            Write-Host "Price: $($offer.price) $($offer.currency)"
            Write-Host "Start Price: $($offer.startPrice) $($offer.currency)"
            if ($offer.discountPercentage) {
                Write-Host "Discount: $($offer.discountPercentage)%"
            }
            Write-Host ""
        }
    } else {
        Write-Host ""
        Write-Host "❌ AVAILABILITY: Lemon does NOT appear to be available for June 1-8, 2026" -ForegroundColor Red
        Write-Host "(No offers found matching yacht ID: $yachtId)"
        Write-Host ""
        Write-Host "Note: Total offers returned for company $companyId : $($data.Count)"
        if ($data.Count -gt 0) {
            Write-Host ""
            Write-Host "Sample yacht names from results:"
            $data | Select-Object -First 5 yacht | ForEach-Object {
                Write-Host "  - $($_.yacht)"
            }
        }
    }
    
} catch {
    Write-Host ""
    Write-Host "❌ ERROR: $($_.Exception.Message)" -ForegroundColor Red
    if ($_.Exception.Response) {
        $statusCode = $_.Exception.Response.StatusCode.value__
        Write-Host "Status Code: $statusCode" -ForegroundColor Red
        try {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $responseBody = $reader.ReadToEnd()
            Write-Host "Response Body: $responseBody"
        } catch {
            Write-Host "Could not read response body"
        }
    }
}

Write-Host ""
Write-Host "=== Test Complete ===" -ForegroundColor Cyan







