# Create Word document from markdown content

$downloadsPath = [Environment]::GetFolderPath("UserProfile") + "\Downloads"
$docPath = "$downloadsPath\Lemon-Yacht-Availability-Report.docx"

Write-Host "Creating Word document..." -ForegroundColor Yellow

try {
    # Create Word application
    $word = New-Object -ComObject Word.Application
    $word.Visible = $false
    
    # Create new document
    $doc = $word.Documents.Add()
    
    # Set document properties
    $doc.BuiltInDocumentProperties("Title").Value = "Lemon Yacht Availability Report"
    $doc.BuiltInDocumentProperties("Subject").Value = "API Call Details - First Week of June 2026"
    $doc.BuiltInDocumentProperties("Author").Value = "YOLO Yacht Search System"
    
    $selection = $word.Selection
    
    # Title
    $selection.Style = "Title"
    $selection.TypeText("Live API Call Details - Lemon Yacht Availability Check")
    $selection.TypeParagraph()
    $selection.TypeText("Date: First Week of June 2026 (June 1-8, 2026)")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # API REQUEST DETAILS
    $selection.Style = "Heading 1"
    $selection.TypeText("API REQUEST DETAILS")
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Endpoint")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.Font.Name = "Courier New"
    $selection.TypeText("GET https://www.booking-manager.com/api/v2/offers")
    $selection.TypeParagraph()
    $selection.Font.Name = "Calibri"
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Query Parameters")
    $selection.TypeParagraph()
    
    # Create table for parameters
    $table = $doc.Tables.Add($selection.Range, 6, 3)
    $table.Borders.OutsideLineStyle = 1
    $table.Borders.InsideLineStyle = 1
    $table.Rows[1].HeadingFormat = $true
    $table.Rows[1].Shading.BackgroundPatternColor = 12632256  # Light gray
    
    $table.Cell(1, 1).Range.Text = "Parameter"
    $table.Cell(1, 2).Range.Text = "Value"
    $table.Cell(1, 3).Range.Text = "Description"
    
    $table.Cell(2, 1).Range.Text = "companyId"
    $table.Cell(2, 2).Range.Text = "7850"
    $table.Cell(2, 3).Range.Text = "YOLO company ID"
    
    $table.Cell(3, 1).Range.Text = "dateFrom"
    $table.Cell(3, 2).Range.Text = "2026-06-01T00:00:00"
    $table.Cell(3, 3).Range.Text = "Start date (June 1, 2026)"
    
    $table.Cell(4, 1).Range.Text = "dateTo"
    $table.Cell(4, 2).Range.Text = "2026-06-30T23:59:59"
    $table.Cell(4, 3).Range.Text = "End date (June 30, 2026)"
    
    $table.Cell(5, 1).Range.Text = "flexibility"
    $table.Cell(5, 2).Range.Text = "6"
    $table.Cell(5, 3).Range.Text = "In year - returns all Saturday departures"
    
    $table.Cell(6, 1).Range.Text = "productName"
    $table.Cell(6, 2).Range.Text = "bareboat"
    $table.Cell(6, 3).Range.Text = "Bareboat charter product"
    
    $selection.EndKey(6)  # Move to end of document
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Full URL")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.Font.Name = "Courier New"
    $selection.Font.Size = 9
    $selection.TypeText("https://www.booking-manager.com/api/v2/offers?companyId=7850&dateFrom=2026-06-01T00%3A00%3A00&dateTo=2026-06-30T23%3A59%3A59&flexibility=6&productName=bareboat")
    $selection.TypeParagraph()
    $selection.Font.Name = "Calibri"
    $selection.Font.Size = 11
    $selection.TypeParagraph()
    
    # API RESPONSE DETAILS
    $selection.Style = "Heading 1"
    $selection.TypeText("API RESPONSE DETAILS")
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Response Status")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.TypeText("• HTTP Status Code: 200 OK")
    $selection.TypeParagraph()
    $selection.TypeText("• Response Time: 6.71 seconds")
    $selection.TypeParagraph()
    $selection.TypeText("• Content Length: 119,073 bytes (~116 KB)")
    $selection.TypeParagraph()
    $selection.TypeText("• Response Format: JSON array")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Response Summary")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.TypeText("• Total Offers Returned: 76 offers (for company 7850)")
    $selection.TypeParagraph()
    $selection.TypeText("• Lemon Yacht Offers Found: 27 offers")
    $selection.TypeParagraph()
    $selection.TypeText("• Target Yacht ID: 6362109340000107850")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # AVAILABILITY RESULT
    $selection.Style = "Heading 1"
    $selection.TypeText("AVAILABILITY RESULT")
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("First Week of June 2026 (June 1-8, 2026)")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.Font.Bold = $true
    $selection.Font.Color = 255  # Red
    $selection.TypeText("STATUS: NOT AVAILABLE")
    $selection.Font.Bold = $false
    $selection.Font.Color = 0  # Black
    $selection.TypeParagraph()
    $selection.TypeText("• Offers Found for First Week: 0")
    $selection.TypeParagraph()
    $selection.TypeText("• Conclusion: Lemon yacht is NOT available for the first week of June 2026")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # CLOSEST AVAILABLE DATES
    $selection.Style = "Heading 1"
    $selection.TypeText("CLOSEST AVAILABLE DATES")
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Next Available Dates in June 2026")
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 3"
    $selection.TypeText("Week 1: June 13-20, 2026")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
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
    
    $selection.Style = "Heading 3"
    $selection.TypeText("Week 2: June 20-27, 2026")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
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
    
    $selection.Style = "Heading 3"
    $selection.TypeText("Week 3: June 27 - July 4, 2026")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
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
    
    # ALL LEMON OFFERS TABLE
    $selection.Style = "Heading 1"
    $selection.TypeText("ALL LEMON OFFERS IN DATE RANGE")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.TypeText("The API returned 27 total offers for Lemon yacht. Here are all offers that fall within or near June 2026:")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # Create large table for all offers
    $selection.EndKey(6)
    $offersTable = $doc.Tables.Add($selection.Range, 28, 5)
    $offersTable.Borders.OutsideLineStyle = 1
    $offersTable.Borders.InsideLineStyle = 1
    $offersTable.Rows[1].HeadingFormat = $true
    $offersTable.Rows[1].Shading.BackgroundPatternColor = 12632256
    
    # Header row
    $offersTable.Cell(1, 1).Range.Text = "Date From"
    $offersTable.Cell(1, 2).Range.Text = "Date To"
    $offersTable.Cell(1, 3).Range.Text = "Price (EUR)"
    $offersTable.Cell(1, 4).Range.Text = "Start Price (EUR)"
    $offersTable.Cell(1, 5).Range.Text = "Discount"
    
    # Data rows
    $offersData = @(
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
    
    for ($i = 0; $i -lt $offersData.Length; $i++) {
        $row = $i + 2
        for ($col = 0; $col -lt 5; $col++) {
            $cellText = $offersData[$i][$col]
            # Bold June dates
            if ($cellText -like "2026-06-*") {
                $offersTable.Cell($row, $col + 1).Range.Font.Bold = $true
            }
            $offersTable.Cell($row, $col + 1).Range.Text = $cellText
        }
    }
    
    $selection.EndKey(6)
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.Font.Italic = $true
    $selection.TypeText("Note: Bold rows indicate offers in June 2026.")
    $selection.Font.Italic = $false
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # ANALYSIS
    $selection.Style = "Heading 1"
    $selection.TypeText("ANALYSIS")
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Why No Offers for First Week?")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.TypeText("Looking at the pattern:")
    $selection.TypeParagraph()
    $selection.TypeText("• Last offer before June: May 23-30, 2026 (ends May 30)")
    $selection.TypeParagraph()
    $selection.TypeText("• First offer in June: June 13-20, 2026 (starts June 13)")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    $selection.Font.Bold = $true
    $selection.TypeText("Gap Analysis:")
    $selection.Font.Bold = $false
    $selection.TypeParagraph()
    $selection.TypeText("• May 30 to June 13 = 14 days gap")
    $selection.TypeParagraph()
    $selection.TypeText("This suggests the yacht is either:")
    $selection.TypeParagraph()
    $selection.TypeText("1. Already booked for June 1-8, 2026")
    $selection.TypeParagraph()
    $selection.TypeText("2. Undergoing maintenance")
    $selection.TypeParagraph()
    $selection.TypeText("3. Not available for that specific week")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    $selection.Style = "Heading 2"
    $selection.TypeText("Charter Week Pattern")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.TypeText("All offers follow the Saturday-to-Saturday pattern:")
    $selection.TypeParagraph()
    $selection.TypeText("• Check-in: Saturday at 17:00 (5 PM)")
    $selection.TypeParagraph()
    $selection.TypeText("• Check-out: Saturday at 09:00 (9 AM)")
    $selection.TypeParagraph()
    $selection.TypeText("This is standard for bareboat charters in the Mediterranean.")
    $selection.TypeParagraph()
    $selection.TypeParagraph()
    
    # SUMMARY
    $selection.Style = "Heading 1"
    $selection.TypeText("SUMMARY")
    $selection.TypeParagraph()
    $selection.Style = "Normal"
    $selection.Font.Bold = $true
    $selection.TypeText("Question: ")
    $selection.Font.Bold = $false
    $selection.TypeText("Is Lemon yacht available for the first week of June 2026?")
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
    
    # Save document
    $doc.SaveAs([ref]$docPath, [ref]16)  # 16 = wdFormatDocumentDefault (.docx)
    $doc.Close()
    $word.Quit()
    
    # Release COM objects
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
    [System.GC]::Collect()
    [System.GC]::WaitForPendingFinalizers()
    
    Write-Host "Word document created successfully!" -ForegroundColor Green
    Write-Host "Location: $docPath" -ForegroundColor Cyan
    
} catch {
    Write-Host "Error creating Word document: $($_.Exception.Message)" -ForegroundColor Red
    if ($word) {
        $word.Quit([ref]$false)
    }
}







