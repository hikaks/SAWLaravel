<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Criteria;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EvaluationTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        // Get sample data from existing records or use defaults
        $sampleEmployee = Employee::first();
        $sampleCriterias = Criteria::take(3)->get();
        
        $data = [];
        $currentPeriod = date('Y-m');
        
        if ($sampleEmployee && $sampleCriterias->count() > 0) {
            foreach ($sampleCriterias as $criteria) {
                $data[] = [
                    'employee_code' => $sampleEmployee->employee_code,
                    'criteria_name' => $criteria->name,
                    'score' => rand(75, 95),
                    'evaluation_period' => $currentPeriod
                ];
            }
        } else {
            // Default template data
            $data = [
                [
                    'employee_code' => 'EMP001',
                    'criteria_name' => 'Performance',
                    'score' => 85,
                    'evaluation_period' => $currentPeriod
                ],
                [
                    'employee_code' => 'EMP001',
                    'criteria_name' => 'Attendance',
                    'score' => 90,
                    'evaluation_period' => $currentPeriod
                ],
                [
                    'employee_code' => 'EMP002',
                    'criteria_name' => 'Performance',
                    'score' => 88,
                    'evaluation_period' => $currentPeriod
                ]
            ];
        }
        
        return collect($data);
    }

    public function headings(): array
    {
        return [
            'employee_code',
            'criteria_name',
            'score',
            'evaluation_period'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A3:D3')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Add instructions
        $sheet->insertNewRowBefore(1, 3);
        $sheet->setCellValue('A1', 'EVALUATION IMPORT TEMPLATE');
        $sheet->setCellValue('A2', 'Instructions: Employee codes and criteria names must exist in the system. Score: 0-100. Period format: YYYY-MM');
        $sheet->setCellValue('A3', 'IMPORTANT: Do not modify the headers in row 4.');
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);

        // Merge cells for title and instructions
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // employee_code
            'B' => 20,  // criteria_name
            'C' => 10,  // score
            'D' => 18,  // evaluation_period
        ];
    }
}