<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AdvancedAnalysisService;
use App\Services\SAWCalculationService;
use App\Services\CacheService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register CacheService
        $this->app->singleton(CacheService::class, function ($app) {
            return new CacheService();
        });

        // Register SAWCalculationService
        $this->app->singleton(SAWCalculationService::class, function ($app) {
            return new SAWCalculationService();
        });

        // Register AdvancedAnalysisService
        $this->app->singleton(AdvancedAnalysisService::class, function ($app) {
            return new AdvancedAnalysisService(
                $app->make(SAWCalculationService::class),
                $app->make(CacheService::class),
                $app->make(\App\Services\AnalysisHistoryService::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Mail configuration will use .env settings
    }
}
