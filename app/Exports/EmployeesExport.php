<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents, WithColumnWidths
{
    protected $employees;
    protected $filters;

    public function __construct($employees, $filters = 'Semua Data')
    {
        $this->employees = $employees;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Karyawan',
            'Nama Lengkap',
            'Posisi',
            'Departemen',
            'Email',
            'Status Email',
            'Tanggal Bergabung',
            'Status'
        ];
    }

    public function map($employee): array
    {
        static $rowNumber = 1;

        return [
            $rowNumber++,
            $employee->employee_code,
            $employee->name,
            $employee->position ?: '-',
            $employee->department ?: '-',
            $employee->email ?: 'Tidak ada email',
            $employee->email ? '✓ Ada' : '✗ Tidak ada',
            $employee->created_at ? $employee->created_at->format('d/m/Y') : '-',
            'Aktif'
        ];
    }

    public function title(): string
    {
        return 'Daftar Karyawan';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 15,  // Kode Karyawan
            'C' => 30,  // Nama Lengkap
            'D' => 20,  // Posisi
            'E' => 20,  // Departemen
            'F' => 35,  // Email
            'G' => 15,  // Status Email
            'H' => 15,  // Tanggal Bergabung
            'I' => 12,  // Status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styling
        $sheet->getStyle('A1:I1')->applyFromArray([
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

        // Data rows styling
        $lastRow = $this->employees->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle("A2:I{$lastRow}")->applyFromArray([
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

            // Alternate row colors
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:I{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->setStartColor(['rgb' => 'F8F9FA']);
                }
            }

            // Center align specific columns
            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G2:G{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("H2:H{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I2:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto-filter
        $sheet->setAutoFilter("A1:I1");

        return $sheet;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $this->employees->count() + 1;

                // Add title and info at the top
                $sheet->insertNewRowBefore(1, 5);

                // Main title
                $sheet->mergeCells('A1:I1');
                $sheet->setCellValue('A1', 'DAFTAR KARYAWAN LENGKAP');
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
                $sheet->mergeCells('A2:I2');
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

                // Export info
                $sheet->mergeCells('A3:I3');
                $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));
                $sheet->getStyle('A3')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Filter info
                $sheet->mergeCells('A4:I4');
                $sheet->setCellValue('A4', 'Filter: ' . $this->filters);
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '666666']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Total info
                $sheet->mergeCells('A5:I5');
                $sheet->setCellValue('A5', 'Total: ' . $this->employees->count() . ' Karyawan');
                $sheet->getStyle('A5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '198754']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                // Add empty row
                $sheet->insertNewRowBefore(7, 1);

                // Move headers to row 8
                $sheet->setCellValue('A8', 'No');
                $sheet->setCellValue('B8', 'Kode Karyawan');
                $sheet->setCellValue('C8', 'Nama Lengkap');
                $sheet->setCellValue('D8', 'Posisi');
                $sheet->setCellValue('E8', 'Departemen');
                $sheet->setCellValue('F8', 'Email');
                $sheet->setCellValue('G8', 'Status Email');
                $sheet->setCellValue('H8', 'Tanggal Bergabung');
                $sheet->setCellValue('I8', 'Status');

                // Style the new header row
                $sheet->getStyle('A8:I8')->applyFromArray([
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

                // Move data to start from row 9
                $dataStartRow = 9;
                $dataEndRow = $dataStartRow + $this->employees->count() - 1;

                // Copy data from original position to new position
                for ($row = 2; $row <= $lastRow; $row++) {
                    $newRow = $dataStartRow + ($row - 2);
                    for ($col = 'A'; $col <= 'I'; $col++) {
                        $value = $sheet->getCell($col . $row)->getValue();
                        $sheet->setCellValue($col . $newRow, $value);
                    }
                }

                // Clear original data rows
                $sheet->removeRow(2, $lastRow - 1);

                // Style the new data rows
                if ($dataEndRow >= $dataStartRow) {
                    $sheet->getStyle("A{$dataStartRow}:I{$dataEndRow}")->applyFromArray([
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

                    // Alternate row colors
                    for ($row = $dataStartRow; $row <= $dataEndRow; $row++) {
                        if ($row % 2 == 0) {
                            $sheet->getStyle("A{$row}:I{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->setStartColor(['rgb' => 'F8F9FA']);
                        }
                    }

                    // Center align specific columns
                    $sheet->getStyle("A{$dataStartRow}:A{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G{$dataStartRow}:G{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("H{$dataStartRow}:H{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("I{$dataStartRow}:I{$dataEndRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Set auto-filter on new header row
                $sheet->setAutoFilter("A8:I8");

                // Freeze panes
                $sheet->freezePane('A9');
            }
        ];
    }
}




