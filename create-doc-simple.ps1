# Simple Word document creation

$downloadsPath = [Environment]::GetFolderPath("UserProfile") + "\Downloads"
$docPath = "$downloadsPath\Lemon-Yacht-Availability-Report.docx"

Write-Host "Creating Word document..." -ForegroundColor Yellow

try {
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    $doc = $word.Documents.Add()
    $selection = $word.Selection
    
    # Title
    $selection.Font.Size = 16
    $selection.Font.Bold = $true
    $selection.TypeText("Live API Call Details - Lemon Yacht Availability Check")
    $selection.TypeParagraph()
    $selection.Font.Size = 12
    $selection.Font.Bold = $false
    $selection.TypeText("Date: First Week of June 2026 (June 1-8, 2026)")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # API REQUEST
    $selection.Font.Size = 14
    $selection.Font.Bold = $true
    $selection.TypeText("API REQUEST DETAILS")
    $selection.TypeParagraph()
    $selection.Font.Size = 11
    $selection.Font.Bold = $false
    
    $selection.TypeText("Endpoint:")
    $selection.TypeParagraph()
    $selection.Font.Name = "Courier New"
    $selection.TypeText("GET https://www.booking-manager.com/api/v2/offers")
    $selection.TypeParagraph()
    $selection.Font.Name = "Calibri"
    $selection.TypeParagraph()
    
    $selection.TypeText("Query Parameters:")
    $selection.TypeParagraph()
    $selection.TypeText("• companyId: 7850 (YOLO company ID)")
    $selection.TypeParagraph()
    $selection.TypeText("• dateFrom: 2026-06-01T00:00:00 (Start date)")
    $selection.TypeParagraph()
    $selection.TypeText("• dateTo: 2026-06-30T23:59:59 (End date)")
    $selection.TypeParagraph()
    $selection.TypeText("• flexibility: 6 (In year - returns all Saturday departures)")
    $selection.TypeParagraph()
    $selection.TypeText("• productName: bareboat (Bareboat charter product)")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.TypeText("Full URL:")
    $selection.TypeParagraph()
    $selection.Font.Name = "Courier New"
    $selection.Font.Size = 9
    $selection.TypeText("https://www.booking-manager.com/api/v2/offers?companyId=7850&dateFrom=2026-06-01T00%3A00%3A00&dateTo=2026-06-30T23%3A59%3A59&flexibility=6&productName=bareboat")
    $selection.TypeParagraph()
    $selection.Font.Name = "Calibri"
    $selection.Font.Size = 11
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # API RESPONSE
    $selection.Font.Size = 14
    $selection.Font.Bold = $true
    $selection.TypeText("API RESPONSE DETAILS")
    $selection.TypeParagraph()
    $selection.Font.Size = 11
    $selection.Font.Bold = $false
    
    $selection.TypeText("Response Status:")
    $selection.TypeParagraph()
    $selection.TypeText("• HTTP Status Code: 200 OK")
    $selection.TypeParagraph()
    $selection.TypeText("• Response Time: 6.71 seconds")
    $selection.TypeParagraph()
    $selection.TypeText("• Content Length: 119,073 bytes (~116 KB)")
    $selection.TypeParagraph()
    $selection.TypeText("• Response Format: JSON array")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.TypeText("Response Summary:")
    $selection.TypeParagraph()
    $selection.TypeText("• Total Offers Returned: 76 offers (for company 7850)")
    $selection.TypeParagraph()
    $selection.TypeText("• Lemon Yacht Offers Found: 27 offers")
    $selection.TypeParagraph()
    $selection.TypeText("• Target Yacht ID: 6362109340000107850")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # AVAILABILITY RESULT
    $selection.Font.Size = 14
    $selection.Font.Bold = $true
    $selection.TypeText("AVAILABILITY RESULT")
    $selection.TypeParagraph()
    $selection.Font.Size = 12
    $selection.TypeText("First Week of June 2026 (June 1-8, 2026)")
    $selection.TypeParagraph()
    $selection.Font.Size = 11
    $selection.Font.Bold = $true
    $selection.Font.Color = 255
    $selection.TypeText("STATUS: NOT AVAILABLE")
    $selection.Font.Bold = $false
    $selection.Font.Color = 0
    $selection.TypeParagraph()
    $selection.TypeText("• Offers Found for First Week: 0")
    $selection.TypeParagraph()
    $selection.TypeText("• Conclusion: Lemon yacht is NOT available for the first week of June 2026")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # CLOSEST AVAILABLE
    $selection.Font.Size = 14
    $selection.Font.Bold = $true
    $selection.TypeText("CLOSEST AVAILABLE DATES")
    $selection.TypeParagraph()
    $selection.Font.Size = 12
    $selection.TypeText("Next Available Dates in June 2026")
    $selection.TypeParagraph()
    $selection.Font.Size = 11
    $selection.Font.Bold = $false
    
    $selection.TypeText("Week 1: June 13-20, 2026")
    $selection.TypeParagraph()
    $selection.TypeText("• Date From: 2026-06-13 17:00:00")
    $selection.TypeParagraph()
    $selection.TypeText("• Date To: 2026-06-20 09:00:00")
    $selection.TypeParagraph()
    $selection.TypeText("• Price: 3,870.00 EUR")
    $selection.TypeParagraph()
    $selection.TypeText("• Start Price: 4,300.00 EUR")
    $selection.TypeParagraph()
    $selection.TypeText("• Discount: 10%")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.TypeText("Week 2: June 20-27, 2026")
    $selection.TypeParagraph()
    $selection.TypeText("• Date From: 2026-06-20 17:00:00")
    $selection.TypeParagraph()
    $selection.TypeText("• Date To: 2026-06-27 09:00:00")
    $selection.TypeParagraph()
    $selection.TypeText("• Price: 4,050.00 EUR")
    $selection.TypeParagraph()
    $selection.TypeText("• Start Price: 4,500.00 EUR")
    $selection.TypeParagraph()
    $selection.TypeText("• Discount: 10%")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.TypeText("Week 3: June 27 - July 4, 2026")
    $selection.TypeParagraph()
    $selection.TypeText("• Date From: 2026-06-27 17:00:00")
    $selection.TypeParagraph()
    $selection.TypeText("• Date To: 2026-07-04 09:00:00")
    $selection.TypeParagraph()
    $selection.TypeText("• Price: 4,050.00 EUR")
    $selection.TypeParagraph()
    $selection.TypeText("• Start Price: 4,500.00 EUR")
    $selection.TypeParagraph()
    $selection.TypeText("• Discount: 10%")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # ALL OFFERS TABLE
    $selection.Font.Size = 14
    $selection.Font.Bold = $true
    $selection.TypeText("ALL LEMON OFFERS IN DATE RANGE")
    $selection.TypeParagraph()
    $selection.Font.Size = 11
    $selection.Font.Bold = $false
    $selection.TypeText("The API returned 27 total offers for Lemon yacht. Here are all offers that fall within or near June 2026:")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # Create table
    $selection.EndKey(6)
    $table = $doc.Tables.Add($selection.Range, 28, 5)
    $table.Borders.OutsideLineStyle = 1
    $table.Borders.InsideLineStyle = 1
    $table.Rows[1].HeadingFormat = $true
    $table.Rows[1].Shading.BackgroundPatternColor = 12632256
    
    # Headers
    $table.Cell(1, 1).Range.Text = "Date From"
    $table.Cell(1, 2).Range.Text = "Date To"
    $table.Cell(1, 3).Range.Text = "Price (EUR)"
    $table.Cell(1, 4).Range.Text = "Start Price (EUR)"
    $table.Cell(1, 5).Range.Text = "Discount"
    
    # Data
    $data = @(
        @("2026-04-18", "2026-04-25", "2,925.00", "3,250.00", "10%"),
        @("2026-04-25", "2026-05-02", "2,925.00", "3,250.00", "10%"),
        @("2026-05-02", "2026-05-09", "2,925.00", "3,250.00", "10%"),
        @("2026-05-09", "2026-05-16", "2,925.00", "3,250.00", "10%"),
        @("2026-05-16", "2026-05-23", "3,870.00", "4,300.00", "10%"),
        @("2026-05-23", "2026-05-30", "3,870.00", "4,300.00", "10%"),
        @("2026-06-13", "2026-06-20", "3,870.00", "4,300.00", "10%"),
        @("2026-06-20", "2026-06-27", "4,050.00", "4,500.00", "10%"),
        @("2026-06-27", "2026-07-04", "4,050.00", "4,500.00", "10%"),
        @("2026-07-04", "2026-07-11", "4,050.00", "4,500.00", "10%"),
        @("2026-07-18", "2026-07-25", "4,050.00", "4,500.00", "10%"),
        @("2026-07-25", "2026-08-01", "4,320.00", "4,800.00", "10%"),
        @("2026-08-01", "2026-08-08", "4,320.00", "4,800.00", "10%"),
        @("2026-08-08", "2026-08-15", "4,320.00", "4,800.00", "10%"),
        @("2026-08-15", "2026-08-22", "4,320.00", "4,800.00", "10%"),
        @("2026-08-22", "2026-08-29", "4,005.00", "4,450.00", "10%"),
        @("2026-08-29", "2026-09-05", "4,005.00", "4,450.00", "10%"),
        @("2026-09-05", "2026-09-12", "3,735.00", "4,150.00", "10%"),
        @("2026-09-12", "2026-09-19", "3,735.00", "4,150.00", "10%"),
        @("2026-09-19", "2026-09-26", "3,735.00", "4,150.00", "10%"),
        @("2026-09-26", "2026-10-03", "2,880.00", "3,200.00", "10%"),
        @("2026-10-03", "2026-10-10", "2,880.00", "3,200.00", "10%"),
        @("2026-10-10", "2026-10-17", "2,880.00", "3,200.00", "10%"),
        @("2026-10-17", "2026-10-24", "2,880.00", "3,200.00", "10%"),
        @("2026-10-24", "2026-10-31", "2,880.00", "3,200.00", "10%"),
        @("2026-10-31", "2026-11-07", "2,880.00", "3,200.00", "10%"),
        @("2026-12-19", "2026-12-26", "18.00", "20.00", "10%")
    )
    
    for ($i = 0; $i -lt $data.Length; $i++) {
        $row = $i + 2
        for ($col = 0; $col -lt 5; $col++) {
            $cellText = $data[$i][$col]
            if ($cellText -like "2026-06-*") {
                $table.Cell($row, $col + 1).Range.Font.Bold = $true
            }
            $table.Cell($row, $col + 1).Range.Text = $cellText
        }
    }
    
    $selection.EndKey(6)
    $selection.TypeParagraph()
    $selection.Font.Italic = $true
    $selection.TypeText("Note: Bold rows indicate offers in June 2026.")
    $selection.Font.Italic = $false
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # SUMMARY
    $selection.Font.Size = 14
    $selection.Font.Bold = $true
    $selection.TypeText("SUMMARY")
    $selection.TypeParagraph()
    $selection.Font.Size = 11
    $selection.Font.Bold = $false
    $selection.TypeText("Question: Is Lemon yacht available for the first week of June 2026?")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    $selection.Font.Bold = $true
    $selection.Font.Color = 255
    $selection.TypeText("Answer: NO")
    $selection.Font.Color = 0
    $selection.Font.Bold = $false
    $selection.TypeText(" - Lemon is NOT available for June 1-8, 2026.")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    $selection.Font.Bold = $true
    $selection.TypeText("Next Available: ")
    $selection.Font.Bold = $false
    $selection.TypeText("June 13-20, 2026 at 3,870 EUR (with 10% discount from 4,300 EUR).")
    $selection.TypeParagraph()
    
    # Save
    $doc.SaveAs([ref]$docPath, [ref]16)
    $doc.Close()
    $word.Quit()
    
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
    [System.GC]::Collect()
    [System.GC]::WaitForPendingFinalizers()
    
    Write-Host "Word document created successfully!" -ForegroundColor Green
    Write-Host "Location: $docPath" -ForegroundColor Cyan
    
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Stack: $($_.Exception.StackTrace)" -ForegroundColor Red
    if ($word) {
        try { $word.Quit([ref]$false) } catch {}
    }
}







