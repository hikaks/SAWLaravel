<?php

namespace App\Http\Controllers;

use App\Models\EvaluationResult;
use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use App\Services\SAWCalculationService;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\SawResultsExport;
use Maatwebsite\Excel\Facades\Excel;

class EvaluationResultController extends Controller
{
    protected $sawService;
    protected $cacheService;

    public function __construct(SAWCalculationService $sawService, CacheService $cacheService)
    {
        $this->sawService = $sawService;
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of results.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $period = $request->get('period');

            // Use cache for results if period is specified
            if ($period) {
                $results = $this->cacheService->getSAWResults($period, function() use ($period) {
                    return EvaluationResult::with('employee:id,name,employee_code,department,position')
                        ->select('id', 'employee_id', 'total_score', 'ranking', 'evaluation_period')
                        ->where('evaluation_period', $period)
                        ->orderBy('ranking')
                        ->get();
                });
            } else {
                // For all periods, cache with different key
                $results = $this->cacheService->getSAWResults('all_periods', function() {
                    return EvaluationResult::with('employee:id,name,employee_code,department,position')
                        ->select('id', 'employee_id', 'total_score', 'ranking', 'evaluation_period')
                        ->orderBy('ranking')
                        ->get();
                });
            }

            return DataTables::of($results)
                ->addIndexColumn()
                ->addColumn('employee_name', function($result) {
                    return $result->employee->name ?? '-';
                })
                ->addColumn('employee_code', function($result) {
                    return $result->employee->employee_code ?? '-';
                })
                ->addColumn('department', function($result) {
                    return $result->employee->department ?? '-';
                })
                ->addColumn('ranking_badge', function($result) {
                    $class = $result->ranking <= 3 ? 'success' :
                            ($result->ranking <= 10 ? 'warning' : 'secondary');
                    return '<span class="badge bg-'.$class.'">#'.$result->ranking.'</span>';
                })
                ->addColumn('score_percentage', function($result) {
                    return round($result->total_score * 100, 2) . '%';
                })
                ->addColumn('action', function($result) {
                    $detailsUrl = route('results.details', ['employee' => $result->employee->id, 'period' => $result->evaluation_period]);
                    $showUrl = route('results.show', $result->id);
                    $exportUrl = route('results.export-employee', $result->employee->id);

                    return '
                        <div class="btn-group" role="group">
                            <a href="'.$showUrl.'" class="btn btn-info btn-sm" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="'.$detailsUrl.'" class="btn btn-primary btn-sm" title="Performance Analysis">
                                <i class="fas fa-chart-line"></i>
                            </a>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Export Report">
                                    <i class="fas fa-download"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="'.$exportUrl.'?period='.$result->evaluation_period.'&format=pdf">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>Export PDF
                                    </a></li>
                                    <li><a class="dropdown-item" href="'.$exportUrl.'?period='.$result->evaluation_period.'&format=excel">
                                        <i class="fas fa-file-excel text-success me-2"></i>Export Excel
                                    </a></li>
                                </ul>
                            </div>
                        </div>
                    ';
                })
                ->rawColumns(['ranking_badge', 'action'])
                ->make(true);
        }

        // Cache evaluation periods for navigation
        $periods = $this->cacheService->getEvaluationPeriods(function() {
            return EvaluationResult::distinct('evaluation_period')
                ->orderByDesc('evaluation_period')
                ->pluck('evaluation_period');
        });

        return view('results.index', compact('periods'));
    }

    /**
     * Display the specified result.
     */
    public function show(EvaluationResult $result)
    {
        $result->load(['employee']);

        $evaluations = Evaluation::with('criteria')
            ->where('employee_id', $result->employee_id)
            ->where('evaluation_period', $result->evaluation_period)
            ->orderBy('criteria_id')
            ->get();

        return view('results.show', compact('result', 'evaluations'));
    }

    /**
     * Export results to Excel.
     */
    public function exportExcelOld(Request $request)
    {
        $period = $request->get('period');

        $results = EvaluationResult::with('employee')
            ->when($period, function($query) use ($period) {
                return $query->where('evaluation_period', $period);
            })
            ->orderBy('ranking')
            ->get();

        // This would be implemented with maatwebsite/excel
        // For now, redirect to PDF export
        return $this->exportPdf($request);
    }

    /**
     * Get chart data for visualization.
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type');
        $period = $request->get('period', 'all');

        // Cache chart data based on type and period
        $chartData = $this->cacheService->getChartData($type, $period, function() use ($type, $period) {
            switch ($type) {
                case 'top_performers':
                    return $this->getTopPerformersChart($period);
                case 'department_comparison':
                    return $this->getDepartmentComparisonChart($period);
                default:
                    return response()->json(['error' => 'Invalid chart type'], 400);
            }
        });

        return $chartData;
    }

    /**
     * Get top performers chart data.
     */
    private function getTopPerformersChart($period)
    {
        $results = EvaluationResult::with('employee')
            ->when($period, function($query) use ($period) {
                return $query->where('evaluation_period', $period);
            })
            ->orderBy('ranking')
            ->limit(10)
            ->get();

        return response()->json([
            'labels' => $results->pluck('employee.name'),
            'datasets' => [
                [
                    'label' => 'Skor Total (%)',
                    'data' => $results->pluck('total_score')->map(function($score) {
                        return round($score * 100, 2);
                    }),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                    'borderColor' => '#36A2EB',
                    'borderWidth' => 1
                ]
            ]
        ]);
    }

    /**
     * Get department comparison chart data.
     */
    private function getDepartmentComparisonChart($period)
    {
        $departmentAvg = EvaluationResult::with('employee')
            ->when($period, function($query) use ($period) {
                return $query->where('evaluation_period', $period);
            })
            ->join('employees', 'evaluation_results.employee_id', '=', 'employees.id')
            ->select('employees.department', DB::raw('AVG(evaluation_results.total_score) as avg_score'))
            ->groupBy('employees.department')
            ->orderBy('avg_score', 'desc')
            ->get();

        return response()->json([
            'labels' => $departmentAvg->pluck('department'),
            'datasets' => [
                [
                    'label' => 'Rata-rata Skor (%)',
                    'data' => $departmentAvg->pluck('avg_score')->map(function($score) {
                        return round($score * 100, 2);
                    }),
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0',
                        '#9966FF', '#FF9F40'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Export SAW results to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            $period = $request->get('period');
            $debug = $request->get('debug', false);

            if (!$period) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode evaluasi wajib dipilih untuk export.'
                ], 400);
            }

            $results = EvaluationResult::with('employee')
                ->forPeriod($period)
                ->orderBy('ranking')
                ->get();

            if ($results->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak ada hasil SAW untuk periode {$period}."
                ], 404);
            }

            $criterias = Criteria::orderBy('weight', 'desc')->get();

            // Prepare data for PDF
            $data = [
                'results' => $results,
                'period' => $period,
                'criterias' => $criterias
            ];

            // Debug mode: return HTML instead of PDF
            if ($debug) {
                return view('exports.pdf.saw-results', $data);
            }

            // Configure DomPDF options
            $options = [
                'dpi' => 150,
                'defaultFont' => 'Arial',
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => false,
                'isRemoteEnabled' => false,
                'enable_font_subsetting' => false,
                'default_media_type' => 'print',
                'default_paper_size' => 'a4',
                'default_font_size' => 12,
                'enable_javascript' => false,
                'enable_css_float' => false,
                'enable_html5_parser' => true,
                'chroot' => realpath(base_path()),
            ];

            // Create PDF with error handling
            $pdf = Pdf::setOptions($options);
            $pdf->loadView('exports.pdf.saw-results', $data);
            $pdf->setPaper('a4', 'portrait');

            $filename = "hasil-ranking-saw-{$period}-" . date('Y-m-d-His') . '.pdf';

            // Return the PDF download response
            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'private, max-age=0, must-revalidate',
                'Pragma' => 'public',
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('PDF Export Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'period' => $request->get('period'),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true)
            ]);

            // Return JSON error for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengexport PDF: ' . $e->getMessage(),
                    'error_details' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'memory_usage' => memory_get_usage(true)
                    ]
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Gagal mengexport PDF: ' . $e->getMessage());
        }
    }

    /**
     * Debug PDF template - return HTML instead of PDF
     */
    public function debugPdfTemplate(Request $request)
    {
        $period = $request->get('period');

        if (!$period) {
            return response('<h1>Error: Period required</h1>', 400);
        }

        $results = EvaluationResult::with('employee')
            ->forPeriod($period)
            ->orderBy('ranking')
            ->get();

        if ($results->isEmpty()) {
            return response('<h1>Error: No data found for period ' . $period . '</h1>', 404);
        }

        $criterias = Criteria::orderBy('weight', 'desc')->get();

        return view('exports.pdf.saw-results', [
            'results' => $results,
            'period' => $period,
            'criterias' => $criterias
        ]);
    }

    /**
     * Simple PDF export method as fallback
     */
    public function exportPdfSimple(Request $request)
    {
        try {
            $period = $request->get('period');

            if (!$period) {
                return back()->with('error', 'Periode evaluasi wajib dipilih untuk export.');
            }

            $results = EvaluationResult::with('employee')
                ->forPeriod($period)
                ->orderBy('ranking')
                ->get();

            if ($results->isEmpty()) {
                return back()->with('error', "Tidak ada hasil SAW untuk periode {$period}.");
            }

            $criterias = Criteria::orderBy('weight', 'desc')->get();

            // Create simple PDF without complex options
            $pdf = Pdf::loadHTML(view('exports.pdf.saw-results', [
                'results' => $results,
                'period' => $period,
                'criterias' => $criterias
            ])->render());

            $pdf->setPaper('a4', 'portrait');

            $filename = "hasil-ranking-saw-{$period}-" . date('Y-m-d-His') . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Simple PDF Export Error: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengexport PDF (Simple): ' . $e->getMessage());
        }
    }

        /**
     * Export SAW results to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $period = $request->get('period');

            if (!$period) {
                return redirect()->back()
                    ->with('error', 'Periode evaluasi wajib dipilih untuk export Excel.');
            }

            $results = EvaluationResult::with('employee')
                ->forPeriod($period)
                ->orderBy('ranking')
                ->get();

            if ($results->isEmpty()) {
                return redirect()->back()
                    ->with('error', "Tidak ada hasil SAW untuk periode {$period}.");
            }

            $criterias = Criteria::orderBy('weight', 'desc')->get();
            
            $filename = "hasil-ranking-saw-{$period}-" . date('Y-m-d-His') . '.xlsx';

            return Excel::download(new SawResultsExport($results, $period, $criterias), $filename);
        } catch (\Exception $e) {
            Log::error('SAW Results Excel export failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengexport Excel: ' . $e->getMessage());
        }
    }

    /**
     * Get employee performance details for specific period
     */
    public function details(Employee $employee, $period)
    {
        try {
            $result = EvaluationResult::where('employee_id', $employee->id)
                ->where('evaluation_period', $period)
                ->with('employee')
                ->first();

            if (!$result) {
                return redirect()->route('results.index')
                    ->with('error', "Hasil evaluasi untuk {$employee->name} periode {$period} tidak ditemukan.");
            }

            $evaluations = Evaluation::with('criteria')
                ->where('employee_id', $employee->id)
                ->where('evaluation_period', $period)
                ->get()
                ->keyBy('criteria_id');

            $criterias = Criteria::orderBy('weight', 'desc')->get();

            // Calculate normalized scores for each criteria
            $normalizedScores = [];
            foreach ($criterias as $criteria) {
                $evaluation = $evaluations->get($criteria->id);
                if ($evaluation) {
                    // Get all scores for this criteria in this period for normalization
                    $allScores = Evaluation::where('criteria_id', $criteria->id)
                        ->where('evaluation_period', $period)
                        ->pluck('score');

                    $normalizedScore = $evaluation->getNormalizedScore($allScores->toArray());
                    $weightedScore = $normalizedScore * ($criteria->weight / 100);

                    $normalizedScores[] = [
                        'criteria' => $criteria,
                        'raw_score' => $evaluation->score,
                        'normalized_score' => $normalizedScore,
                        'weighted_score' => $weightedScore,
                        'contribution_percentage' => ($weightedScore / $result->total_score) * 100
                    ];
                }
            }

            return view('results.details', compact(
                'employee', 'result', 'period', 'criterias',
                'evaluations', 'normalizedScores'
            ));
        } catch (\Exception $e) {
            return redirect()->route('results.index')
                ->with('error', 'Gagal memuat detail performance: ' . $e->getMessage());
        }
    }

    /**
     * Export individual employee performance report
     */
    public function exportEmployeeReport(Employee $employee, Request $request)
    {
        try {
            $period = $request->get('period');
            $format = $request->get('format', 'pdf');

            if (!$period) {
                return redirect()->back()
                    ->with('error', 'Periode evaluasi wajib dipilih untuk export.');
            }

            $result = EvaluationResult::where('employee_id', $employee->id)
                ->where('evaluation_period', $period)
                ->with('employee')
                ->first();

            if (!$result) {
                return redirect()->back()
                    ->with('error', "Hasil evaluasi untuk {$employee->name} periode {$period} tidak ditemukan.");
            }

            $evaluations = Evaluation::with('criteria')
                ->where('employee_id', $employee->id)
                ->where('evaluation_period', $period)
                ->get();

            $criterias = Criteria::orderBy('weight', 'desc')->get();

            $data = [
                'employee' => $employee,
                'result' => $result,
                'period' => $period,
                'evaluations' => $evaluations,
                'criterias' => $criterias
            ];

            $filename = "performance-report-{$employee->employee_code}-{$period}-" . date('Y-m-d-His');

            if ($format === 'pdf') {
                $pdf = Pdf::loadView('exports.pdf.employee-performance', $data);
                $pdf->setPaper('a4', 'portrait');
                return $pdf->download($filename . '.pdf');
            } else {
                $html = view('exports.excel.employee-performance', $data)->render();
                return response($html)
                    ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
                    ->header('Content-Disposition', 'attachment; filename="' . $filename . '.xls"')
                    ->header('Pragma', 'no-cache')
                    ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                    ->header('Expires', '0');
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengexport laporan: ' . $e->getMessage());
        }
    }
}
