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
        Schema::create('evaluation_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->decimal('total_score', 8, 4)->comment('Skor total hasil SAW');
            $table->integer('ranking')->comment('Ranking berdasarkan total_score');
            $table->string('evaluation_period')->comment('Periode penilaian misal: 2024-01');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['employee_id', 'evaluation_period']);
            $table->index(['evaluation_period', 'ranking']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_results');
    }
};
