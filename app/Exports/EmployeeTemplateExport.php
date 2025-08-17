<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class EmployeeTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return collect([
            [
                'employee_code' => 'EMP001',
                'name' => 'John Doe',
                'position' => 'Software Engineer',
                'department' => 'IT',
                'email' => 'john.doe@company.com'
            ],
            [
                'employee_code' => 'EMP002',
                'name' => 'Jane Smith',
                'position' => 'Project Manager',
                'department' => 'IT',
                'email' => 'jane.smith@company.com'
            ],
            [
                'employee_code' => 'EMP003',
                'name' => 'Mike Johnson',
                'position' => 'Business Analyst',
                'department' => 'Business',
                'email' => 'mike.johnson@company.com'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'employee_code',
            'name',
            'position',
            'department',
            'email'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style header row
        $sheet->getStyle('A1:E1')->applyFromArray([
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

        // Add instructions in row above data
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', 'EMPLOYEE IMPORT TEMPLATE');
        $sheet->setCellValue('A2', 'Instructions: Fill in the data below. Do not modify the headers in row 3.');
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'color' => ['rgb' => '666666']],
        ]);

        // Merge cells for title and instructions
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // employee_code
            'B' => 25,  // name
            'C' => 20,  // position
            'D' => 15,  // department
            'E' => 30,  // email
        ];
    }
}