<?php

use Illuminate\Support\Facades\Route;
use App\Models\EvaluationResult;
use Yajra\DataTables\DataTables;

Route::get('/test-all-periods', function() {
    // Simulate the exact same query as in EvaluationResultController
    $results = EvaluationResult::with('employee:id,name,employee_code,department,position')
        ->select('id', 'employee_id', 'total_score', 'ranking', 'evaluation_period')
        ->orderBy('evaluation_period', 'desc')
        ->orderBy('ranking')
        ->get();
    
    return response()->json([
        'success' => true,
        'count' => $results->count(),
        'data' => $results->take(5)->map(function($result) {
            return [
                'id' => $result->id,
                'employee_name' => $result->employee->name ?? '-',
                'employee_code' => $result->employee->employee_code ?? '-',
                'department' => $result->employee->department ?? '-',
                'total_score' => $result->total_score,
                'ranking' => $result->ranking,
                'evaluation_period' => $result->evaluation_period
            ];
        })
    ]);
});

Route::get('/test-datatable-all', function() {
    // Simulate DataTable response for all periods
    $results = EvaluationResult::with('employee:id,name,employee_code,department,position')
        ->select('id', 'employee_id', 'total_score', 'ranking', 'evaluation_period')
        ->orderBy('evaluation_period', 'desc')
        ->orderBy('ranking')
        ->get();
    
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
        ->rawColumns(['ranking_badge'])
        ->make(true);
});