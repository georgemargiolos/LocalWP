# JavaScript Libraries Analysis - Yolo Charters

## Downloaded Libraries

Successfully extracted the following JavaScript libraries from yolo-charters.com:

### Core Libraries
1. **litepicker.js** (63KB)
   - Version: 2.0.12
   - License: MIT
   - Purpose: Date range picker for charter bookings
   - GitHub: https://github.com/wakirin/Litepicker
   - NPM: https://www.npmjs.com/package/litepicker

2. **mobilefriendly.js** (13KB)
   - Litepicker plugin for mobile responsiveness
   - Breakpoint: 480px

3. **plugins.min.js** (559KB)
   - Bundled third-party libraries including:
     - jQuery
     - jQuery UI
     - PhotoSwipe
     - Magnific Popup
     - Select2

4. **custom.min.js** (9KB)
   - Custom UI/UX logic
   - Form validation
   - Navigation handlers

## Litepicker Initialization Code

The search form uses Litepicker with the following configuration:

```javascript
const allDaysPicker = new Litepicker({
    element: document.getElementById('all-days'),
    plugins: ['mobilefriendly'],
    mobilefriendly: {
        breakpoint: 480,
    },
    format: "DD.MM.YYYY",
    firstDay: 0,
    singleMode: false,
    minDate: new Date(),
    numberOfColumns: 2,
    numberOfMonths: 2,
    startDate: firstSaturday,
    endDate: nextSaturday,
    disallowLockDaysInRange: true,
    position: "top",
    tooltipNumber: (totalDays) => {
       return totalDays - 1;
    },
    tooltipText: { "one":"night", "other":"nights" },
    lockDaysFilter: (date, date2, date3) => {
        let today = new Date();
        let saturday = date.getTime() > today.getTime() && date.getDay() == 6;
        let allDays = date.getTime() > today.getTime();

        if(allDaysToBook === "true"){
            if(allDays){return false}else{return true};
        } else {
            if(saturday){return false}else{return true};
        }
    }
});
```

## Key Features

### Date Picker Configuration
- **Range Mode**: Two-date selection (start and end)
- **Format**: DD.MM.YYYY (e.g., 29.11.2025 - 06.12.2025)
- **Minimum Date**: Current date (no past dates)
- **Display**: 2 columns, 2 months visible
- **Position**: Top (calendar appears above input field)
- **Mobile Responsive**: Switches layout at 480px breakpoint

### Business Logic
- **Saturday-to-Saturday Bookings**: By default, only Saturdays can be selected
- **Lock Days Filter**: Prevents selection of non-Saturday dates (unless `allDaysToBook` is true)
- **Duration Calculation**: Automatically calculates nights between dates
- **Week Enforcement**: If not in "all days" mode and duration is not a multiple of 7, it defaults to 7 days

### Form Submission Function

```javascript
function submitForm(){
    let startDate = allDaysPicker.getStartDate();
    let finishDate = allDaysPicker.getEndDate();
    document.forms["box"]['filter_date'].value = startDate.getDate();
    document.forms["box"]['filter_month'].value = startDate.getMonth();
    document.forms["box"]['filter_year'].value = startDate.getFullYear();
    document.forms["box"]['filter_duration'].value = Math.round((finishDate.getTime() - startDate.getTime())/(1000*60*60*24));
    if(allDaysToBook!=='true' && Math.round((finishDate.getTime() - startDate.getTime())/(1000*60*60*24))%7!=0) {
        document.forms["box"]['filter_duration'].value = 7
    } else {
        document.forms["box"]['filter_duration'].value = Math.round((finishDate.getTime() - startDate.getTime())/(1000*60*60*24));
    }
}
```

## WordPress Plugin Integration Plan

For the WordPress plugin, we can use these exact libraries and configuration:

1. **Enqueue Litepicker** in WordPress
   - Register litepicker.js and mobilefriendly.js
   - Add to plugin assets folder
   - Enqueue on pages with search shortcode

2. **Adapt the Configuration**
   - Use WordPress localization for date formats
   - Make Saturday-only booking optional (admin setting)
   - Add support for flexible date ranges

3. **Form Handling**
   - Convert form submission to AJAX
   - Send data to WordPress REST API endpoint
   - Process search via PHP backend
   - Call Booking Manager API
   - Return results without page reload

4. **CSS Styling**
   - Include Litepicker default CSS
   - Add custom WordPress theme-compatible styles
   - Ensure mobile responsiveness

## Files Downloaded
- ✅ litepicker.js
- ✅ mobilefriendly.js
- ✅ plugins.min.js
- ✅ custom.min.js
- ✅ yolo-homepage.html (full source for reference)
