<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheWarmupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:warmup
                           {--force : Force warm up even if cache exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm up application caches for better performance';

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔥 Starting cache warmup process...');

        $startTime = microtime(true);

        try {
            // Clear cache if force option is used
            if ($this->option('force')) {
                $this->warn('🧹 Force option detected, clearing existing caches...');
                $this->cacheService->clearAll();
            }

            // Warm up caches
            $this->info('📊 Warming up critical caches...');
            $this->cacheService->warmUp();

            // Display cache statistics
            $this->displayCacheStats();

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            $this->info("✅ Cache warmup completed successfully in {$duration} seconds!");

        } catch (\Exception $e) {
            $this->error('❌ Cache warmup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Display cache statistics
     */
    private function displayCacheStats()
    {
        $this->info('📈 Cache Statistics:');

        $stats = $this->cacheService->getStats();

        if (empty($stats)) {
            $this->warn('No cache statistics available');
            return;
        }

        $headers = ['Cache Type', 'Items Count'];
        $rows = [];

        foreach ($stats as $type => $count) {
            $rows[] = [$type, $count];
        }

        $this->table($headers, $rows);
    }
}
