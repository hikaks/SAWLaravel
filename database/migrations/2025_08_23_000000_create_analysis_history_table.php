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
        Schema::create('analysis_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('analysis_type'); // sensitivity, what-if, comparison, forecast, statistics
            $table->string('evaluation_period')->nullable();
            $table->json('parameters'); // Store analysis parameters
            $table->json('results_summary'); // Store key results summary
            $table->integer('execution_time_ms')->default(0); // Execution time in milliseconds
            $table->string('status')->default('completed'); // completed, failed, cancelled
            $table->text('error_message')->nullable(); // Error message if failed
            $table->timestamps();
            $table->softDeletes(); // Add soft deletes support
            
            // Indexes for performance
            $table->index(['user_id', 'analysis_type']);
            $table->index(['analysis_type', 'evaluation_period']);
            $table->index(['created_at']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_history');
    }
};
