<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Criteria;
use App\Models\Evaluation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::all();
        $criterias = Criteria::all();
        $evaluationPeriod = '2024-01';

        // Data evaluasi sample untuk setiap karyawan
        $evaluationData = [
            'EMP001' => [85, 90, 95, 80, 85, 75], // Ahmad Santoso
            'EMP002' => [80, 85, 70, 90, 80, 85], // Siti Nurhaliza
            'EMP003' => [90, 95, 75, 95, 90, 90], // Budi Prasetyo
            'EMP004' => [75, 80, 80, 85, 75, 80], // Maya Indira
            'EMP005' => [85, 75, 90, 70, 80, 70], // Joko Widodo
            'EMP006' => [80, 90, 85, 85, 85, 80], // Lisa Maharani
            'EMP007' => [70, 75, 65, 75, 70, 75], // Rian Pratama
            'EMP008' => [85, 95, 70, 95, 90, 95], // Devi Safitri
            'EMP009' => [90, 85, 75, 90, 85, 90], // Eko Prasetio
            'EMP010' => [80, 80, 85, 80, 80, 85], // Rina Kurniawan
        ];

        foreach ($employees as $employee) {
            $scores = $evaluationData[$employee->employee_code] ?? [75, 75, 75, 75, 75, 75];

            foreach ($criterias as $index => $criteria) {
                Evaluation::create([
                    'employee_id' => $employee->id,
                    'criteria_id' => $criteria->id,
                    'score' => $scores[$index] ?? 75,
                    'evaluation_period' => $evaluationPeriod,
                ]);
            }
        }

        // Tambahkan data untuk periode 2024-02 dengan variasi skor
        $evaluationPeriod2 = '2024-02';

        foreach ($employees as $employee) {
            $scores = $evaluationData[$employee->employee_code] ?? [75, 75, 75, 75, 75, 75];

            foreach ($criterias as $index => $criteria) {
                // Variasi skor untuk periode kedua (±5 dari periode pertama)
                $baseScore = $scores[$index] ?? 75;
                $variation = rand(-5, 5);
                $newScore = max(60, min(100, $baseScore + $variation));

                Evaluation::create([
                    'employee_id' => $employee->id,
                    'criteria_id' => $criteria->id,
                    'score' => $newScore,
                    'evaluation_period' => $evaluationPeriod2,
                ]);
            }
        }

        $totalEvaluations = $employees->count() * $criterias->count() * 2;
        $this->command->info("{$totalEvaluations} data evaluasi berhasil ditambahkan untuk periode 2024-01 dan 2024-02.");
    }
}
