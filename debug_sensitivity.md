# Debugging Sensitivity Analysis Issues

## Current Status Analysis

### ✅ What's Working:
1. **Route Configuration** - `analysis.sensitivity` route is properly defined
2. **Controller Method** - `sensitivityAnalysis()` method exists and handles requests
3. **Service Layer** - `AdvancedAnalysisService::sensitivityAnalysis()` is implemented
4. **Frontend UI** - Form, buttons, and view toggles are properly set up
5. **Chart.js Integration** - Chart.js library is loaded from CDN
6. **AJAX Setup** - jQuery AJAX calls are properly configured

### ❌ Potential Issues:

#### 1. **Data Availability**
- **Problem:** No evaluation data for selected period
- **Check:** Verify `Evaluation` table has data for the period
- **Solution:** Run seeder or create test data

#### 2. **JavaScript Console Errors**
- **Problem:** Chart.js not loading or JavaScript errors
- **Check:** Browser console for errors
- **Solution:** Verify Chart.js CDN is accessible

#### 3. **AJAX Response Format**
- **Problem:** Response data structure doesn't match expected format
- **Check:** Network tab in browser dev tools
- **Solution:** Verify controller returns correct JSON structure

#### 4. **Chart Rendering Issues**
- **Problem:** Canvas element not created or chart not displaying
- **Check:** HTML structure and Chart.js initialization
- **Solution:** Verify canvas element exists and chart data is valid

## Step-by-Step Debugging:

### Step 1: Check Browser Console
```javascript
// Add this to sensitivity.blade.php for debugging
console.log('Sensitivity analysis script loaded');
console.log('Chart.js available:', typeof Chart !== 'undefined');
```

### Step 2: Check AJAX Request
```javascript
// Add logging to AJAX calls
$.ajax({
    // ... existing config ...
    beforeSend: function() {
        console.log('Sending request:', requestData);
    },
    success: function(response) {
        console.log('Response received:', response);
        // ... existing code ...
    },
    error: function(xhr, status, error) {
        console.error('AJAX Error:', {xhr, status, error});
        // ... existing code ...
    }
});
```

### Step 3: Check Chart Data
```javascript
// Add logging to chart creation
function showResultsChart() {
    console.log('Creating chart with data:', analysisResults);
    // ... existing code ...
}
```

### Step 4: Verify Data Structure
Expected response structure:
```json
{
    "success": true,
    "data": {
        "original_results": [...],
        "original_weights": {...},
        "sensitivity_scenarios": {...},
        "summary": {...}
    }
}
```

## Quick Fixes to Try:

### 1. Clear View Cache
```bash
php artisan view:clear
```

### 2. Check Database Data
```bash
php artisan tinker
# Then run:
App\Models\Evaluation::count()
App\Models\Evaluation::distinct('evaluation_period')->pluck('evaluation_period')
```

### 3. Test Route Manually
```bash
curl -X POST http://127.0.0.1:8000/analysis/sensitivity \
  -H "Content-Type: application/json" \
  -H "X-CSRF-TOKEN: [token]" \
  -d '{"evaluation_period": "2024-01"}'
```

### 4. Add Error Logging
```php
// In AnalysisController::sensitivityAnalysis()
Log::info('Sensitivity analysis request:', $request->all());
Log::info('Analysis results:', $results);
```

## Common Issues and Solutions:

### Issue 1: "Chart is not defined"
**Solution:** Ensure Chart.js loads before your script

### Issue 2: "Cannot read property of undefined"
**Solution:** Check if `analysisResults` exists before accessing properties

### Issue 3: "Canvas element not found"
**Solution:** Verify canvas element is created in HTML before chart initialization

### Issue 4: "AJAX request failed"
**Solution:** Check CSRF token and route availability
