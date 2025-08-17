<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CriteriaTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return collect([
            [
                'name' => 'Performance',
                'weight' => 30,
                'type' => 'benefit'
            ],
            [
                'name' => 'Attendance',
                'weight' => 25,
                'type' => 'benefit'
            ],
            [
                'name' => 'Discipline',
                'weight' => 25,
                'type' => 'benefit'
            ],
            [
                'name' => 'Tardiness',
                'weight' => 20,
                'type' => 'cost'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'name',
            'weight',
            'type'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A3:C3')->applyFromArray([
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
        $sheet->setCellValue('A1', 'CRITERIA IMPORT TEMPLATE');
        $sheet->setCellValue('A2', 'Instructions: Fill in criteria data. Total weight must equal 100%. Type must be "benefit" or "cost".');
        $sheet->setCellValue('A3', 'IMPORTANT: Do not modify the headers in row 4.');
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        $sheet->getStyle('A2:A3')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);

        // Merge cells for title and instructions
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        $sheet->mergeCells('A3:C3');

        // Add validation note
        $sheet->setCellValue('A8', 'Note: Total weight in this template = 100%');
        $sheet->getStyle('A8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '008000']],
        ]);

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,  // name
            'B' => 10,  // weight
            'C' => 15,  // type
        ];
    }
}