<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->index('evaluation_period', 'idx_evaluations_period');
            $table->index(['employee_id', 'criteria_id'], 'idx_evaluations_employee_criteria');
            $table->index(['evaluation_period', 'employee_id'], 'idx_evaluations_period_employee');
        });

        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->index(['evaluation_period', 'ranking'], 'idx_evaluation_results_period_ranking');
            $table->index('total_score', 'idx_evaluation_results_score');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('department', 'idx_employees_department');
            $table->index('employee_code', 'idx_employees_code');
        });

        Schema::table('criterias', function (Blueprint $table) {
            $table->index('type', 'idx_criterias_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex('idx_evaluations_period');
            $table->dropIndex('idx_evaluations_employee_criteria');
            $table->dropIndex('idx_evaluations_period_employee');
        });

        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->dropIndex('idx_evaluation_results_period_ranking');
            $table->dropIndex('idx_evaluation_results_score');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_department');
            $table->dropIndex('idx_employees_code');
        });

        Schema::table('criterias', function (Blueprint $table) {
            $table->dropIndex('idx_criterias_type');
        });
    }
};