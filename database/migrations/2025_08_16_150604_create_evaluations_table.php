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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('criteria_id')->constrained('criterias')->onDelete('cascade');
            $table->integer('score')->comment('Nilai penilaian 1-100');
            $table->string('evaluation_period')->comment('Periode penilaian misal: 2024-01');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'criteria_id', 'evaluation_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
