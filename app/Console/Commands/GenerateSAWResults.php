<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SAWCalculationService;
use App\Models\EvaluationResult;
use App\Models\Evaluation;

class GenerateSAWResults extends Command
{
    protected $signature = 'saw:generate {period?}';
    protected $description = 'Generate SAW calculation results for evaluation period';

    public function handle()
    {
        $period = $this->argument('period');
        
        if (!$period) {
            // Get available periods
            $periods = Evaluation::select('evaluation_period')
                ->distinct()
                ->orderBy('evaluation_period', 'desc')
                ->pluck('evaluation_period')
                ->toArray();
            
            if (empty($periods)) {
                $this->error('No evaluation periods found.');
                return 1;
            }
            
            $this->info('Available periods:');
            foreach ($periods as $p) {
                $this->line("  - {$p}");
            }
            
            $period = $this->choice('Select period to generate results:', $periods, 0);
        }
        
        $this->info("Generating SAW results for period: {$period}");
        
        try {
            $sawService = app(SAWCalculationService::class);
            
            // Check current results count
            $beforeCount = EvaluationResult::where('evaluation_period', $period)->count();
            $this->info("Current results count for {$period}: {$beforeCount}");
            
            // Generate results
            $results = $sawService->calculateSAW($period);
            
            // Check after count
            $afterCount = EvaluationResult::where('evaluation_period', $period)->count();
            $this->info("Results count after generation: {$afterCount}");
            
            $this->info("SAW calculation completed successfully!");
            $this->info("Total employees processed: " . count($results));
            
            // Show top 5 results
            $this->info("\nTop 5 performers:");
            $topResults = EvaluationResult::where('evaluation_period', $period)
                ->orderBy('total_score', 'desc')
                ->with('employee')
                ->limit(5)
                ->get();
            
            foreach ($topResults as $index => $result) {
                $rank = $index + 1;
                $name = $result->employee->name ?? 'Unknown';
                $score = number_format($result->total_score, 4);
                $this->line("  {$rank}. {$name} - Score: {$score}");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("SAW calculation failed: " . $e->getMessage());
            return 1;
        }
    }
}