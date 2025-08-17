<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get available locales from config
        $availableLocales = array_keys(config('app.available_locales', ['id', 'en']));

        // Determine locale from multiple sources (in order of priority)
        $locale = $this->determineLocale($request, $availableLocales);

        // Set application locale
        if ($locale && in_array($locale, $availableLocales)) {
            App::setLocale($locale);

            // Store in session for persistence
            if (!Session::has('locale') || Session::get('locale') !== $locale) {
                Session::put('locale', $locale);
            }
        }

        return $next($request);
    }

    /**
     * Determine the locale based on various sources
     */
    private function determineLocale(Request $request, array $availableLocales): ?string
    {
        // 1. Check if locale is explicitly set in request (for language switching)
        if ($request->has('locale') && in_array($request->get('locale'), $availableLocales)) {
            return $request->get('locale');
        }

        // 2. Check session for stored locale
        if (Session::has('locale') && in_array(Session::get('locale'), $availableLocales)) {
            return Session::get('locale');
        }

        // 3. Check user preference (if authenticated)
        if (auth()->check() && auth()->user()->locale ?? null) {
            $userLocale = auth()->user()->locale;
            if (in_array($userLocale, $availableLocales)) {
                return $userLocale;
            }
        }

        // 4. Check browser language preferences
        $browserLocale = $this->getBrowserLocale($request, $availableLocales);
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Fall back to default locale from config
        $defaultLocale = config('app.locale', 'id');
        return in_array($defaultLocale, $availableLocales) ? $defaultLocale : $availableLocales[0];
    }

    /**
     * Get preferred locale from browser Accept-Language header
     */
    private function getBrowserLocale(Request $request, array $availableLocales): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = [];
        foreach (explode(',', $acceptLanguage) as $lang) {
            $parts = explode(';', trim($lang));
            $locale = trim($parts[0]);

            // Extract language code (e.g., 'en' from 'en-US')
            $languageCode = explode('-', $locale)[0];

            // Check if we support this language
            if (in_array($languageCode, $availableLocales)) {
                return $languageCode;
            }

            // Also check full locale
            if (in_array($locale, $availableLocales)) {
                return $locale;
            }
        }

        return null;
    }

    /**
     * Get locale direction (for RTL languages)
     */
    public static function getDirection(string $locale = null): string
    {
        $locale = $locale ?? App::getLocale();
        $localeConfig = config("app.available_locales.{$locale}");

        return $localeConfig['dir'] ?? 'ltr';
    }

    /**
     * Check if current locale is RTL
     */
    public static function isRtl(string $locale = null): bool
    {
        return self::getDirection($locale) === 'rtl';
    }
}
