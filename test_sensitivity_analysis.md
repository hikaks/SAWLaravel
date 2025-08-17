# Test Case: Sensitivity Analysis Functionality

## Test Scenario 1: Basic Sensitivity Analysis
**Objective:** Verify that sensitivity analysis can run and display results

### Steps:
1. Navigate to `/analysis/sensitivity`
2. Select an evaluation period from dropdown
3. Choose "Standard Scenarios" analysis type
4. Click "Run Analysis" button

### Expected Results:
- Loading spinner should appear
- Analysis should complete successfully
- Results should be displayed in table view by default
- Chart view button should be available
- Sensitivity summary should show metrics

## Test Scenario 2: Chart View Display
**Objective:** Verify that chart view works correctly

### Steps:
1. Complete basic analysis (from Scenario 1)
2. Click "Chart View" button
3. Verify chart is displayed

### Expected Results:
- Chart should render with employee performance data
- Chart should be responsive
- Chart should show original scores as bars

## Test Scenario 3: Custom Weights Analysis
**Objective:** Verify custom weights functionality

### Steps:
1. Select "Custom Weights" analysis type
2. Modify criteria weights using sliders
3. Click "Run Analysis"

### Expected Results:
- Custom weights section should be visible
- Weight changes should be applied
- Analysis should run with modified weights

## Test Scenario 4: Error Handling
**Objective:** Verify error handling works

### Steps:
1. Try to run analysis without selecting period
2. Check browser console for errors

### Expected Results:
- Form validation should prevent submission
- No JavaScript errors in console

## Potential Issues to Check:

### 1. JavaScript Errors
- Check browser console for JavaScript errors
- Verify Chart.js is loaded correctly
- Check for AJAX request failures

### 2. Data Issues
- Verify evaluation data exists for selected period
- Check if criteria weights are properly loaded
- Ensure employee data is available

### 3. Chart Rendering
- Verify Chart.js canvas element is created
- Check if chart data is properly formatted
- Ensure chart options are valid

### 4. AJAX Response
- Check network tab for failed requests
- Verify response format matches expected structure
- Check for server-side errors

## Debug Commands:
```bash
# Check route availability
php artisan route:list | findstr sensitivity

# Check view compilation
php artisan view:clear

# Check database data
php artisan tinker
# Then run: App\Models\Evaluation::count()
```

