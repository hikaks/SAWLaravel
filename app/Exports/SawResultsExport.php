<?php

namespace App\Exports;

use App\Models\EvaluationResult;
use App\Models\Criteria;
use App\Models\Evaluation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class SawResultsExport implements WithMultipleSheets
{
    protected $period;
    protected $results;
    protected $criterias;

    public function __construct($results, $period, $criterias)
    {
        $this->results = $results;
        $this->period = $period;
        $this->criterias = $criterias;
    }

    public function sheets(): array
    {
        return [
            new SawRankingSheet($this->results, $this->period),
            new SawDetailSheet($this->results, $this->period, $this->criterias),
            new SawCriteriaSheet($this->period)
        ];
    }
}

class SawRankingSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $period;
    protected $results;

    public function __construct($results, $period)
    {
        $this->results = $results;
        $this->period = $period;
    }

    public function collection()
    {
        return $this->results;
    }

    public function headings(): array
    {
        return [
            'Ranking',
            'Kode Karyawan',
            'Nama Lengkap',
            'Department',
            'Posisi',
            'Total Skor SAW',
            'Skor Persentase (%)',
            'Kategori Performance',
            'Status'
        ];
    }

    public function map($result): array
    {
        // Convert decimal score to percentage (0.7369 -> 73.69)
        $scorePercentage = $result->total_score * 100;

        $category = $scorePercentage >= 90 ? 'Excellent' :
                   ($scorePercentage >= 80 ? 'Good' :
                   ($scorePercentage >= 70 ? 'Average' : 'Poor'));

        $status = $result->ranking <= 3 ? 'Top Performer' :
                 ($result->ranking <= 10 ? 'High Performer' :
                 ($result->ranking <= 20 ? 'Good Performer' : 'Standard Performer'));

        return [
            $result->ranking,
            $result->employee->employee_code,
            $result->employee->name,
            $result->employee->department,
            $result->employee->position,
            number_format($result->total_score, 4),
            number_format($scorePercentage, 2),
            $category,
            $status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '198754']
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
        return 'Ranking SAW - ' . $this->period;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-fit columns
                foreach (range('A', 'I') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Insert title rows
                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', config('app.name'));
                $sheet->setCellValue('A2', 'Laporan Hasil Ranking SAW (Simple Additive Weighting)');
                $sheet->setCellValue('A3', 'Periode: ' . $this->period . ' | Export: ' . date('d F Y H:i:s'));

                // Style title
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '198754']],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                ]);

                $sheet->getStyle('A3')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => '666666']],
                ]);

                $lastRow = $sheet->getHighestRow();

                // Apply borders
                $sheet->getStyle('A5:I' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // Header style
                $sheet->getStyle('A5:I5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '198754']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                // Highlight top 3 performers
                for ($row = 6; $row <= min(8, $lastRow); $row++) {
                    $colors = ['FFD700', 'C0C0C0', 'CD7F32']; // Gold, Silver, Bronze
                    $colorIndex = $row - 6;

                    if (isset($colors[$colorIndex])) {
                        $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $colors[$colorIndex]]
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['rgb' => '000000']
                            ]
                        ]);
                    }
                }

                // Center align specific columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add performance statistics
                $statsRow = $lastRow + 2;
                $results = $this->collection();

                $excellentCount = $results->where('total_score', '>=', 0.90)->count();
                $goodCount = $results->whereBetween('total_score', [0.80, 0.8999])->count();
                $averageCount = $results->whereBetween('total_score', [0.70, 0.7999])->count();
                $poorCount = $results->where('total_score', '<', 0.70)->count();

                // Add period information for all-periods case
                if ($this->period === 'all-periods') {
                    $uniquePeriods = $results->pluck('evaluation_period')->unique()->count();
                    $sheet->setCellValue('A' . ($statsRow - 1), 'INFORMASI PERIODE:');
                    $sheet->setCellValue('A' . $statsRow, "Total Periode Evaluasi: {$uniquePeriods} periode");
                    $statsRow += 2;
                }

                $sheet->setCellValue('A' . $statsRow, 'STATISTIK PERFORMANCE:');
                $sheet->setCellValue('A' . ($statsRow + 1), 'Excellent (≥90%): ' . $excellentCount . ' orang');
                $sheet->setCellValue('A' . ($statsRow + 2), 'Good (80-89%): ' . $goodCount . ' orang');
                $sheet->setCellValue('A' . ($statsRow + 3), 'Average (70-79%): ' . $averageCount . ' orang');
                $sheet->setCellValue('A' . ($statsRow + 4), 'Poor (<70%): ' . $poorCount . ' orang');

                $sheet->getStyle('A' . $statsRow)->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                ]);
            }
        ];
    }
}

class SawDetailSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $period;
    protected $results;
    protected $criterias;

    public function __construct($results, $period, $criterias)
    {
        $this->results = $results;
        $this->period = $period;
        $this->criterias = $criterias;
    }

    public function collection()
    {
        return $this->results;
    }

    public function headings(): array
    {
        $headers = [
            'Rank',
            'Kode',
            'Nama Karyawan',
            'Department'
        ];

        foreach ($this->criterias as $criteria) {
            $headers[] = $criteria->name . ' (' . $criteria->weight . '%)';
        }

        $headers[] = 'Total Skor SAW';
        $headers[] = 'Persentase';

        return $headers;
    }

    public function map($result): array
    {
        $row = [
            $result->ranking,
            $result->employee->employee_code,
            $result->employee->name,
            $result->employee->department
        ];

        // Get evaluations for this employee and period
        if ($this->period === 'all-periods') {
            // For all periods, get evaluations from the employee's result period
            $evaluations = Evaluation::where('employee_id', $result->employee_id)
                ->where('evaluation_period', $result->evaluation_period)
                ->get()
                ->keyBy('criteria_id');
        } else {
            // For specific period
            $evaluations = Evaluation::where('employee_id', $result->employee_id)
                ->where('evaluation_period', $this->period)
                ->get()
                ->keyBy('criteria_id');
        }

        foreach ($this->criterias as $criteria) {
            $evaluation = $evaluations->get($criteria->id);
            $row[] = $evaluation ? $evaluation->score : '-';
        }

        $row[] = number_format($result->total_score, 4);
        $row[] = number_format($result->total_score * 100, 2) . '%';

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 11,
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
        return 'Detail Skor - ' . $this->period;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-fit columns
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(25);
                $sheet->getColumnDimension('D')->setWidth(20);

                // Auto-fit criteria columns
                $criterias = Criteria::orderBy('weight', 'desc')->get();
                $startCol = 'E';
                foreach ($criterias as $index => $criteria) {
                    $col = chr(ord($startCol) + $index);
                    $sheet->getColumnDimension($col)->setWidth(15);
                }

                $lastCol = chr(ord($startCol) + $criterias->count() + 1);
                $sheet->getColumnDimension(chr(ord($lastCol) - 1))->setWidth(15);
                $sheet->getColumnDimension($lastCol)->setWidth(12);

                $lastRow = $sheet->getHighestRow();

                // Apply borders
                $sheet->getStyle('A1:' . $lastCol . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // Center align most columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B:B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E:' . $lastCol)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        ];
    }
}

class SawCriteriaSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $period;

    public function __construct($period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        return Criteria::orderBy('weight', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Kriteria',
            'Bobot (%)',
            'Tipe',
            'Keterangan',
            'Jumlah Evaluasi',
            'Skor Min',
            'Skor Max',
            'Rata-rata Skor'
        ];
    }

    public function map($criteria): array
    {
        static $number = 1;

        // Handle all-periods case
        if ($this->period === 'all-periods') {
            // Get all evaluations for this criteria across all periods
            $evaluations = $criteria->evaluations()->get();
        } else {
            // Get evaluations for specific period
            $evaluations = $criteria->evaluations()->where('evaluation_period', $this->period)->get();
        }

        return [
            $number++,
            $criteria->name,
            $criteria->weight,
            ucfirst($criteria->type),
            $criteria->type === 'benefit' ? 'Semakin tinggi semakin baik' : 'Semakin rendah semakin baik',
            $evaluations->count(),
            $evaluations->count() > 0 ? $evaluations->min('score') : '-',
            $evaluations->count() > 0 ? $evaluations->max('score') : '-',
            $evaluations->count() > 0 ? number_format($evaluations->avg('score'), 2) : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFC107']
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
        return 'Kriteria - ' . $this->period;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Auto-fit columns
                foreach (range('A', 'I') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $lastRow = $sheet->getHighestRow();

                // Apply borders
                $sheet->getStyle('A1:I' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // Center align specific columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C:C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D:D')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Add summary information for all-periods case
                if ($this->period === 'all-periods') {
                    $summaryRow = $lastRow + 2;

                    $sheet->setCellValue('A' . $summaryRow, 'INFORMASI PERIODE:');
                    $sheet->getStyle('A' . $summaryRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                    ]);

                    $summaryRow++;
                    $sheet->setCellValue('A' . $summaryRow, 'Data kriteria ini mencakup semua periode evaluasi yang tersedia.');
                    $summaryRow++;
                    $sheet->setCellValue('A' . $summaryRow, 'Statistik (Min, Max, Rata-rata) dihitung dari seluruh data evaluasi.');

                    $sheet->getStyle('A' . $summaryRow)->applyFromArray([
                        'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '666666']],
                    ]);
                }
            }
        ];
    }
}




