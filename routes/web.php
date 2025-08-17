<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\CriteriaController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\EvaluationResultController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;

// Public routes
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes - require authentication
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Language switching routes
    Route::prefix('language')->name('language.')->group(function () {
        Route::get('/switch/{locale}', [LanguageController::class, 'switch'])->name('switch');
        Route::post('/switch', [LanguageController::class, 'switch'])->name('switch-post');
        Route::get('/current', [LanguageController::class, 'current'])->name('current');
        Route::get('/available', [LanguageController::class, 'available'])->name('available');
        Route::get('/translations', [LanguageController::class, 'getTranslations'])->name('translations');
    });

    // Employee management routes
    Route::resource('employees', EmployeeController::class);
    Route::get('/employees/department/{department}', [EmployeeController::class, 'getByDepartment'])->name('employees.by-department');
    Route::get('/api/departments', [EmployeeController::class, 'getDepartments'])->name('api.departments');
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
    Route::get('/employees/export/pdf', [EmployeeController::class, 'exportPdf'])->name('employees.export-pdf');
    Route::get('/employees/export/excel', [EmployeeController::class, 'exportExcel'])->name('employees.export-excel');
    Route::get('/employees/import/template', [EmployeeController::class, 'downloadTemplate'])->name('employees.import-template');
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.import');
    Route::post('/employees/restore', [EmployeeController::class, 'restore'])->name('employees.restore');
    Route::post('/employees/force-delete', [EmployeeController::class, 'forceDelete'])->name('employees.force-delete');

    // Criteria management routes
    Route::resource('criterias', CriteriaController::class);
    Route::get('/criterias/total-weight', [CriteriaController::class, 'getTotalWeight'])->name('criterias.total-weight');
    Route::post('/criterias/validate-weight', [CriteriaController::class, 'validateWeight'])->name('criterias.validate-weight');
    Route::get('/criterias/import/template', [CriteriaController::class, 'downloadTemplate'])->name('criterias.import-template');
    Route::post('/criterias/import', [CriteriaController::class, 'import'])->name('criterias.import');
    Route::post('/criterias/restore', [CriteriaController::class, 'restore'])->name('criterias.restore');
    Route::post('/criterias/force-delete', [CriteriaController::class, 'forceDelete'])->name('criterias.force-delete');

    // Evaluation system routes
    Route::resource('evaluations', EvaluationController::class);
    Route::get('/evaluations/batch/create', [EvaluationController::class, 'batchCreate'])->name('evaluations.batch-create');
    Route::post('/evaluations/batch/store', [EvaluationController::class, 'batchStore'])->name('evaluations.batch-store');
    Route::post('/evaluations/generate-results', [EvaluationController::class, 'generateResults'])->name('evaluations.generate-results');
    Route::get('/evaluations/periods', [EvaluationController::class, 'getPeriods'])->name('evaluations.periods');
    Route::get('/evaluations/matrix', [EvaluationController::class, 'getEvaluationMatrix'])->name('evaluations.matrix');
    Route::get('/evaluations/import/template', [EvaluationController::class, 'downloadTemplate'])->name('evaluations.import-template');
    Route::post('/evaluations/import', [EvaluationController::class, 'import'])->name('evaluations.import');
    Route::post('/evaluations/restore', [EvaluationController::class, 'restore'])->name('evaluations.restore');
    Route::post('/evaluations/force-delete', [EvaluationController::class, 'forceDelete'])->name('evaluations.force-delete');

    // Results and ranking routes
    Route::resource('results', EvaluationResultController::class);
    Route::get('/results/export/pdf', [EvaluationResultController::class, 'exportPdf'])->name('results.export-pdf');
    Route::get('/results/export/pdf-simple', [EvaluationResultController::class, 'exportPdfSimple'])->name('results.export-pdf-simple');
    Route::get('/results/export/excel', [EvaluationResultController::class, 'exportExcel'])->name('results.export-excel');
    Route::get('/results/chart-data', [EvaluationResultController::class, 'getChartData'])->name('results.chart-data');
    Route::get('/results/employee/{employee}/period/{period}', [EvaluationResultController::class, 'details'])->name('results.details');
    Route::get('/results/employee/{employee}/export', [EvaluationResultController::class, 'exportEmployeeReport'])->name('results.export-employee');
    Route::get('/results/debug/pdf-template', [EvaluationResultController::class, 'debugPdfTemplate'])->name('results.debug-pdf');

    // User Management routes (Admin and users can manage certain aspects)
    Route::resource('users', UserController::class);
    Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::post('/users/bulk-action', [UserController::class, 'bulkAction'])->name('users.bulk-action');
    Route::post('/users/{user}/send-password-reset', [UserController::class, 'sendPasswordReset'])->name('users.send-password-reset');

    // Email Verification routes
    Route::post('/users/{user}/send-email-verification', [UserController::class, 'sendEmailVerification'])->name('users.send-email-verification');
    Route::post('/users/{user}/manually-verify-email', [UserController::class, 'manuallyVerifyEmail'])->name('users.manually-verify-email');
    Route::get('/users/verification-status/{status}', [UserController::class, 'getByVerificationStatus'])->name('users.verification-status');

    // Admin-only routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // System Management
        Route::get('/cache', [AdminController::class, 'cacheManagement'])->name('cache.index');
        Route::post('/cache/clear', [AdminController::class, 'clearCache'])->name('cache.clear');
        Route::post('/cache/warmup', [AdminController::class, 'warmupCache'])->name('cache.warmup');
        Route::get('/cache/stats', [AdminController::class, 'cacheStats'])->name('cache.stats');

        // System Health & Information
        Route::get('/health', [AdminController::class, 'healthCheck'])->name('health');
        Route::get('/system-info', [AdminController::class, 'systemInfo'])->name('system-info');
    });
});

// Admin Job Monitoring Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/jobs', [App\Http\Controllers\Admin\JobMonitorController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/{id}', [App\Http\Controllers\Admin\JobMonitorController::class, 'show'])->name('jobs.show');
    Route::post('/jobs/{id}/retry', [App\Http\Controllers\Admin\JobMonitorController::class, 'retry'])->name('jobs.retry');
    Route::post('/jobs/clear-completed', [App\Http\Controllers\Admin\JobMonitorController::class, 'clearCompleted'])->name('jobs.clear-completed');
    Route::post('/jobs/clear-all', [App\Http\Controllers\Admin\JobMonitorController::class, 'clearAll'])->name('jobs.clear-all');
});

// Report Download Routes
Route::middleware(['auth'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/download', [App\Http\Controllers\ReportController::class, 'download'])->name('download');
});
