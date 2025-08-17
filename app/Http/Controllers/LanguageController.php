<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, $locale = null)
    {
        // Get locale from parameter or request
        $locale = $locale ?? $request->get('locale');

        // Validate locale
        $availableLocales = array_keys(config('app.available_locales', ['id', 'en']));

        if (!in_array($locale, $availableLocales)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid locale provided'
            ], 400);
        }

        // Set locale in session
        Session::put('locale', $locale);

        // Set application locale
        App::setLocale($locale);

        // Get redirect URL
        $redirectUrl = $request->get('redirect') ?? url()->previous() ?? route('dashboard');

        // Handle AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'locale' => $locale,
                'locale_name' => config("app.available_locales.{$locale}.name"),
                'message' => __('Language changed successfully'),
                'redirect_url' => $redirectUrl
            ]);
        }

        // Handle regular requests
        return redirect($redirectUrl)->with('success', __('Language changed successfully'));
    }

    /**
     * Get current locale information
     */
    public function current()
    {
        $currentLocale = App::getLocale();
        $availableLocales = config('app.available_locales', []);

        return response()->json([
            'current_locale' => $currentLocale,
            'current_locale_info' => $availableLocales[$currentLocale] ?? null,
            'available_locales' => $availableLocales
        ]);
    }

    /**
     * Get all available locales
     */
    public function available()
    {
        $availableLocales = config('app.available_locales', []);

        return response()->json([
            'locales' => $availableLocales
        ]);
    }

    /**
     * Get translated strings for JavaScript
     */
    public function getTranslations(Request $request)
    {
        $locale = $request->get('locale', App::getLocale());

        // Common translations needed in JavaScript
        $jsTranslations = [
            // Common actions
            'Save' => __('Save'),
            'Cancel' => __('Cancel'),
            'Delete' => __('Delete'),
            'Edit' => __('Edit'),
            'View' => __('View'),
            'Close' => __('Close'),
            'Yes' => __('Yes'),
            'No' => __('No'),
            'OK' => __('OK'),
            'Loading' => __('Loading'),
            'Success' => __('Success'),
            'Error' => __('Error'),
            'Warning' => __('Warning'),

            // Messages
            'Confirmation Required' => __('Confirmation Required'),
            'Are you sure you want to delete this item?' => __('Are you sure you want to delete this item?'),
            'This action cannot be undone' => __('This action cannot be undone'),
            'Data saved successfully' => __('Data saved successfully'),
            'Data updated successfully' => __('Data updated successfully'),
            'Data deleted successfully' => __('Data deleted successfully'),
            'Error occurred while processing' => __('Error occurred while processing'),
            'Please fill in required fields' => __('Please fill in required fields'),
            'No data available' => __('No data available'),
            'Data refreshed successfully' => __('Data refreshed successfully'),

            // DataTables translations
            'Processing' => __('Processing'),
            'Search' => __('Search'),
            'Previous' => __('Previous'),
            'Next' => __('Next'),
            'First' => __('First'),
            'Last' => __('Last'),

            // Form validation
            'Field is required' => __('Field is required'),
            'Invalid email format' => __('Invalid email format'),
            'Value must be numeric' => __('Value must be numeric'),

            // File operations
            'File uploaded successfully' => __('File uploaded successfully'),
            'Export completed successfully' => __('Export completed successfully'),
            'File format not supported' => __('File format not supported'),
            'File size too large' => __('File size too large'),

            // Specific to application
            'Employee' => __('Data Karyawan'),
            'Criteria' => __('Kriteria Penilaian'),
            'Evaluation' => __('Input Penilaian'),
            'Results' => __('Hasil Ranking'),
            'Dashboard' => __('Dashboard'),
            'Language changed successfully' => __('Language changed successfully'),
            'Theme changed successfully' => __('Theme changed successfully'),
        ];

        return response()->json([
            'locale' => $locale,
            'translations' => $jsTranslations
        ]);
    }
}
