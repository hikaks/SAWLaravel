<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;

class EmployeeTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    public function collection()
    {
        // Return sample data for template
        return collect([
            [
                'EMP001',
                'John Doe',
                'Software Engineer',
                'IT Department',
                'john.doe@company.com'
            ],
            [
                'EMP002',
                'Jane Smith',
                'Product Manager',
                'Product Department',
                'jane.smith@company.com'
            ],
            [
                'EMP003',
                'Bob Johnson',
                'Sales Representative',
                'Sales Department',
                'bob.johnson@company.com'
            ],
            [
                'EMP004',
                'Alice Brown',
                'HR Specialist',
                'HR Department',
                'alice.brown@company.com'
            ],
            [
                'EMP005',
                'Charlie Wilson',
                'Marketing Coordinator',
                'Marketing Department',
                'charlie.wilson@company.com'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Kode Karyawan *',
            'Nama Lengkap *',
            'Posisi',
            'Departemen',
            'Email *'
        ];
    }

    public function title(): string
    {
        return 'Template Import Karyawan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Kode Karyawan
            'B' => 30,  // Nama Lengkap
            'C' => 25,  // Posisi
            'D' => 25,  // Departemen
            'E' => 35,  // Email
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Sample data styling
        $lastRow = 6;
        $sheet->getStyle("A2:E{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC']
                ]
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Alternate row colors for sample data
        for ($row = 2; $row <= $lastRow; $row++) {
            if ($row % 2 == 0) {
                $sheet->getStyle("A{$row}:E{$row}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->setStartColor(['rgb' => 'F8F9FA']);
            }
        }

        return $sheet;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;

                // Add title and instructions at the top
                $sheet->insertNewRowBefore(1, 8);

                // Main title
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'TEMPLATE IMPORT KARYAWAN');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '198754']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Subtitle
                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', config('app.name') . ' - Sistem Manajemen Karyawan');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'size' => 12,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Instructions title
                $sheet->mergeCells('A3:E3');
                $sheet->setCellValue('A3', '📋 PANDUAN PENGGUNAAN TEMPLATE');
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '0D6EFD']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Instructions
                $sheet->mergeCells('A4:E4');
                $sheet->setCellValue('A4', '1. Kolom dengan tanda * adalah WAJIB diisi');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => 'DC3545']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                $sheet->mergeCells('A5:E5');
                $sheet->setCellValue('A5', '2. Kode Karyawan harus UNIK (tidak boleh duplikat)');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                $sheet->mergeCells('A6:E6');
                $sheet->setCellValue('A6', '3. Email harus valid dan UNIK (tidak boleh duplikat)');
                $sheet->getStyle('A6')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                $sheet->mergeCells('A7:E7');
                $sheet->setCellValue('A7', '4. Hapus baris contoh sebelum mengisi data karyawan baru');
                $sheet->getStyle('A7')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                // Add empty row
                $sheet->insertNewRowBefore(9, 1);

                // Move headers to row 10
                $sheet->setCellValue('A10', 'Kode Karyawan *');
                $sheet->setCellValue('B10', 'Nama Lengkap *');
                $sheet->setCellValue('C10', 'Posisi');
                $sheet->setCellValue('D10', 'Departemen');
                $sheet->setCellValue('E10', 'Email *');

                // Style the new header row
                $sheet->getStyle('A10:E10')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '198754']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);

                // Move sample data to start from row 11
                $dataStartRow = 11;
                $dataEndRow = $dataStartRow + 4; // 5 sample rows

                // Copy sample data from original position to new position
                for ($row = 2; $row <= 6; $row++) {
                    $newRow = $dataStartRow + ($row - 2);
                    for ($col = 'A'; $col <= 'E'; $col++) {
                        $value = $sheet->getCell($col . $row)->getValue();
                        $sheet->setCellValue($col . $newRow, $value);
                    }
                }

                // Clear original data rows
                $sheet->removeRow(2, 5);

                // Style the new sample data rows
                $sheet->getStyle("A{$dataStartRow}:E{$dataEndRow}")->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                // Alternate row colors for sample data
                for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle("A{$row}:E{$row}")->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->setStartColor(['rgb' => 'F8F9FA']);
                    }
                }

                // Add validation notes at the bottom
                $noteRow = $dataEndRow + 2;
                $sheet->mergeCells("A{$noteRow}:E{$noteRow}");
                $sheet->setCellValue("A{$noteRow}", '📝 CATATAN VALIDASI:');
                $sheet->getStyle("A{$noteRow}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '0D6EFD']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                $noteRow++;
                $sheet->mergeCells("A{$noteRow}:E{$noteRow}");
                $sheet->setCellValue("A{$noteRow}", '• Kode Karyawan: Hanya huruf dan angka, minimal 3 karakter');
                $sheet->getStyle("A{$noteRow}")->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                $noteRow++;
                $sheet->mergeCells("A{$noteRow}:E{$noteRow}");
                $sheet->setCellValue("A{$noteRow}", '• Nama: Hanya huruf, spasi, dan tanda baca');
                $sheet->getStyle("A{$noteRow}")->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                $noteRow++;
                $sheet->mergeCells("A{$noteRow}:E{$noteRow}");
                $sheet->setCellValue("A{$noteRow}", '• Email: Format email yang valid (contoh: user@domain.com)');
                $sheet->getStyle("A{$noteRow}")->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ]
                ]);

                // Set auto-filter on header row
                $sheet->setAutoFilter("A10:E10");

                // Freeze panes
                $sheet->freezePane('A11');

                // Add data validation for required fields
                $this->addDataValidation($sheet, $dataStartRow, $dataEndRow);
            }
        ];
    }

    private function addDataValidation($sheet, $startRow, $endRow)
    {
        // Add data validation for required fields
        // This is a placeholder - actual validation would be implemented
        // based on the specific requirements
    }
}
