<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Models\EvaluationResult;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Cache basic statistics
        $stats = $this->cacheService->getDashboardData('basic_stats', function() {
            return [
                'total_employees' => Employee::count(),
                'total_criterias' => Criteria::count(),
                'total_evaluations' => Evaluation::count(),
                'total_weight' => Criteria::sum('weight'),
            ];
        });

        // Cache recent evaluation periods
        $recentPeriods = $this->cacheService->getDashboardData('recent_periods', function() {
            return Evaluation::select('evaluation_period')
                ->distinct()
                ->orderByDesc('evaluation_period')
                ->limit(5)
                ->pluck('evaluation_period');
        });

        // Cache top performers
        $topPerformers = $this->cacheService->getDashboardData('top_performers', function() {
            return EvaluationResult::with('employee')
                ->orderBy('ranking')
                ->limit(5)
                ->get();
        });

        // Cache department distribution
        $departmentStats = $this->cacheService->getDashboardData('department_stats', function() {
            return Employee::select('department', DB::raw('count(*) as count'))
                ->groupBy('department')
                ->get();
        });

        // Cache criteria distribution by type
        $criteriaStats = $this->cacheService->getDashboardData('criteria_stats', function() {
            return Criteria::select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get();
        });

        // Cache latest evaluations
        $latestEvaluations = $this->cacheService->getDashboardData('latest_evaluations', function() {
            return Evaluation::with(['employee', 'criteria'])
                ->latest()
                ->limit(5)
                ->get();
        });

        // Cache evaluation completion rate by period
        $evaluationCompletion = $this->cacheService->getDashboardData('evaluation_completion', function() use ($recentPeriods, $stats) {
            $completion = [];
            foreach ($recentPeriods as $period) {
                $totalPossible = $stats['total_employees'] * $stats['total_criterias'];
                $completed = Evaluation::where('evaluation_period', $period)->count();
                $completion[$period] = [
                    'total' => $totalPossible,
                    'completed' => $completed,
                    'percentage' => $totalPossible > 0 ? round(($completed / $totalPossible) * 100, 2) : 0
                ];
            }
            return $completion;
        });

        return view('dashboard', compact(
            'stats',
            'recentPeriods',
            'topPerformers',
            'departmentStats',
            'criteriaStats',
            'latestEvaluations',
            'evaluationCompletion'
        ));
    }

    /**
     * Get chart data for dashboard
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type');

        switch ($type) {
            case 'department':
                return $this->getDepartmentChartData();
            case 'evaluation_trend':
                return $this->getEvaluationTrendData();
            case 'criteria_distribution':
                return $this->getCriteriaDistributionData();
            case 'performance_overview':
                return $this->getPerformanceOverviewData();
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Get department distribution chart data
     */
    private function getDepartmentChartData()
    {
        $data = Employee::select('department', DB::raw('count(*) as count'))
            ->groupBy('department')
            ->get();

        return response()->json([
            'labels' => $data->pluck('department'),
            'data' => $data->pluck('count'),
            'backgroundColor' => [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                '#9966FF', '#FF9F40', '#FF6384', '#C9CBCF'
            ]
        ]);
    }

    /**
     * Get evaluation trend chart data
     */
    private function getEvaluationTrendData()
    {
        $data = Evaluation::select('evaluation_period', DB::raw('count(*) as count'))
            ->groupBy('evaluation_period')
            ->orderBy('evaluation_period')
            ->get();

        return response()->json([
            'labels' => $data->pluck('evaluation_period'),
            'datasets' => [
                [
                    'label' => 'Jumlah Evaluasi',
                    'data' => $data->pluck('count'),
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
                    'tension' => 0.4
                ]
            ]
        ]);
    }

    /**
     * Get criteria distribution chart data
     */
    private function getCriteriaDistributionData()
    {
        $data = Criteria::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get();

        return response()->json([
            'labels' => $data->pluck('type')->map(function($type) {
                return ucfirst($type);
            }),
            'data' => $data->pluck('count'),
            'backgroundColor' => ['#4BC0C0', '#FFCE56']
        ]);
    }

    /**
     * Get performance overview data
     */
    private function getPerformanceOverviewData()
    {
        $results = EvaluationResult::with('employee')
            ->orderBy('ranking')
            ->limit(5)
            ->get();

        return response()->json([
            'labels' => $results->pluck('employee.name'),
            'datasets' => [
                [
                    'label' => 'Skor Total',
                    'data' => $results->pluck('total_score'),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => '#36A2EB',
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    /**
     * Get summary statistics for API
     */
    public function getStats()
    {
        $stats = [
            'employees' => [
                'total' => Employee::count(),
                'by_department' => Employee::select('department', DB::raw('count(*) as count'))
                    ->groupBy('department')
                    ->get()
            ],
            'criterias' => [
                'total' => Criteria::count(),
                'total_weight' => Criteria::sum('weight'),
                'by_type' => Criteria::select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->get()
            ],
            'evaluations' => [
                'total' => Evaluation::count(),
                'periods' => Evaluation::distinct('evaluation_period')
                    ->orderByDesc('evaluation_period')
                    ->pluck('evaluation_period')
            ],
            'results' => [
                'total' => EvaluationResult::count(),
                'latest_period' => EvaluationResult::orderByDesc('evaluation_period')
                    ->value('evaluation_period')
            ]
        ];

        return response()->json($stats);
    }
}
