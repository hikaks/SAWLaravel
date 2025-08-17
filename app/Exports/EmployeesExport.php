<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Employee::query();

        // Apply filters
        if (isset($this->filters['department']) && $this->filters['department']) {
            $query->where('department', $this->filters['department']);
        }

        if (isset($this->filters['position']) && $this->filters['position']) {
            $query->where('position', $this->filters['position']);
        }

        return $query->orderBy('employee_code')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Karyawan',
            'Nama Lengkap',
            'Posisi',
            'Department',
            'Email',
            'Tanggal Bergabung',
            'Total Evaluasi',
            'Ranking Terakhir',
            'Skor Terakhir (%)',
            'Status'
        ];
    }

    public function map($employee): array
    {
        static $number = 1;

        $latestResult = $employee->latestResult();

        return [
            $number++,
            $employee->employee_code,
            $employee->name,
            $employee->position,
            $employee->department,
            $employee->email,
            $employee->created_at->format('d/m/Y'),
            $employee->evaluations->count(),
            $latestResult ? '#' . $latestResult->ranking : '-',
            $latestResult ? number_format($latestResult->score_percentage, 2) . '%' : '-',
            $latestResult ?
                ($latestResult->score_percentage >= 80 ? 'Excellent' :
                ($latestResult->score_percentage >= 70 ? 'Good' :
                ($latestResult->score_percentage >= 60 ? 'Average' : 'Poor'))) : 'No Data'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header style
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D6EFD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    public function title(): string
    {
        return 'Data Karyawan';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-fit columns
                foreach (range('A', 'K') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Set row height for header
                $sheet->getRowDimension(1)->setRowHeight(25);

                // Apply borders to all data
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A1:K' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // Center align specific columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('I:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add title and metadata
                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', config('app.name'));
                $sheet->setCellValue('A2', 'Laporan Data Karyawan');
                $sheet->setCellValue('A3', 'Tanggal Export: ' . date('d F Y H:i:s'));

                // Style title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '0D6EFD']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);

                // Adjust header row
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:K5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '0D6EFD']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                // Apply alternate row colors
                for ($row = 6; $row <= $lastRow; $row++) {
                    if (($row - 5) % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA']
                            ]
                        ]);
                    }
                }

                // Add summary at the bottom
                $summaryRow = $lastRow + 2;
                $totalEmployees = $lastRow - 5;

                $sheet->setCellValue('A' . $summaryRow, 'RINGKASAN:');
                $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Karyawan: ' . $totalEmployees . ' orang');
                $sheet->setCellValue('A' . ($summaryRow + 2), 'Export Date: ' . date('d F Y H:i:s'));

                $sheet->getStyle('A' . $summaryRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT]
                ]);
            }
        ];
    }
}




