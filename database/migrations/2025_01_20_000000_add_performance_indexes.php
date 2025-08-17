<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Evaluations table indexes
        if (!$this->indexExists('evaluations', 'idx_evaluations_period')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->index('evaluation_period', 'idx_evaluations_period');
            });
        }

        if (!$this->indexExists('evaluations', 'idx_evaluations_employee_criteria')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->index(['employee_id', 'criteria_id'], 'idx_evaluations_employee_criteria');
            });
        }

        if (!$this->indexExists('evaluations', 'idx_evaluations_period_employee')) {
            Schema::table('evaluations', function (Blueprint $table) {
                $table->index(['evaluation_period', 'employee_id'], 'idx_evaluations_period_employee');
            });
        }

        // Evaluation results table indexes
        if (!$this->indexExists('evaluation_results', 'idx_evaluation_results_period_ranking')) {
            Schema::table('evaluation_results', function (Blueprint $table) {
                $table->index(['evaluation_period', 'ranking'], 'idx_evaluation_results_period_ranking');
            });
        }

        if (!$this->indexExists('evaluation_results', 'idx_evaluation_results_employee')) {
            Schema::table('evaluation_results', function (Blueprint $table) {
                $table->index('employee_id', 'idx_evaluation_results_employee');
            });
        }

        // Employees table indexes
        if (!$this->indexExists('employees', 'idx_employees_department')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->index('department', 'idx_employees_department');
            });
        }

        if (!$this->indexExists('employees', 'idx_employees_position')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->index('position', 'idx_employees_position');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_evaluations_period');
            $table->dropIndexIfExists('idx_evaluations_employee_criteria');
            $table->dropIndexIfExists('idx_evaluations_period_employee');
        });

        Schema::table('evaluation_results', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_evaluation_results_period_ranking');
            $table->dropIndexIfExists('idx_evaluation_results_employee');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndexIfExists('idx_employees_department');
            $table->dropIndexIfExists('idx_employees_position');
        });
    }

    /**
     * Check if index exists
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$indexName}'");
        return count($indexes) > 0;
    }
};
