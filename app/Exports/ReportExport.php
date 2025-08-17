<?php

namespace App\Exports;

class ReportExport
{
    protected $data;
    protected $type;
    protected $period;

    public function __construct($data, string $type, string $period = null)
    {
        $this->data = $data;
        $this->type = $type;
        $this->period = $period;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPeriod(): ?string
    {
        return $this->period;
    }

    public function getHeadings(): array
    {
        switch ($this->type) {
            case 'saw_results':
                return [
                    'Ranking',
                    'Employee Code',
                    'Employee Name',
                    'Department',
                    'Position',
                    'Total Score',
                    'Normalized Score',
                    'Evaluation Period'
                ];
            case 'evaluation_summary':
                return [
                    'Employee Code',
                    'Employee Name',
                    'Department',
                    'Position',
                    'Evaluations Count',
                    'Average Score',
                    'Last Evaluation Date'
                ];
            case 'performance_report':
                return [
                    'Employee Code',
                    'Employee Name',
                    'Department',
                    'Position',
                    'Performance Score',
                    'Ranking',
                    'Status',
                    'Recommendations'
                ];
            default:
                return ['Data'];
        }
    }

    public function getMappedData(): array
    {
        $mapped = [];

        foreach ($this->data as $row) {
            $mapped[] = $this->mapRow($row);
        }

        return $mapped;
    }

    private function mapRow($row): array
    {
        switch ($this->type) {
            case 'saw_results':
                return [
                    $row->ranking ?? 'N/A',
                    $row->employee->employee_code ?? 'N/A',
                    $row->employee->name ?? 'N/A',
                    $row->employee->department ?? 'N/A',
                    $row->employee->position ?? 'N/A',
                    number_format($row->total_score ?? 0, 4),
                    number_format(($row->total_score ?? 0) * 100, 2) . '%',
                    $row->evaluation_period ?? 'N/A'
                ];
            case 'evaluation_summary':
                return [
                    $row->employee_code ?? 'N/A',
                    $row->name ?? 'N/A',
                    $row->department ?? 'N/A',
                    $row->position ?? 'N/A',
                    $row->evaluations_count ?? 0,
                    number_format($row->average_score ?? 0, 2),
                    $row->last_evaluation_date ?? 'N/A'
                ];
            case 'performance_report':
                $score = $row->total_score ?? 0;
                $status = $score >= 0.8 ? 'Excellent' : ($score >= 0.6 ? 'Good' : 'Needs Improvement');
                return [
                    $row->employee->employee_code ?? 'N/A',
                    $row->employee->name ?? 'N/A',
                    $row->employee->department ?? 'N/A',
                    $row->employee->position ?? 'N/A',
                    number_format($score * 100, 2) . '%',
                    $row->ranking ?? 'N/A',
                    $status,
                    $this->getRecommendations($score)
                ];
            default:
                return [$row];
        }
    }

    public function getTitle(): string
    {
        $title = ucfirst(str_replace('_', ' ', $this->type));
        if ($this->period) {
            $title .= " - {$this->period}";
        }
        return $title;
    }

    private function getRecommendations($score): string
    {
        if ($score >= 0.8) {
            return 'Consider for promotion or special projects';
        } elseif ($score >= 0.6) {
            return 'Continue current performance, identify growth areas';
        } else {
            return 'Develop improvement plan, consider additional training';
        }
    }

    public function exportToCsv(): string
    {
        $csv = '';

        // Add headers
        $csv .= implode(',', $this->getHeadings()) . "\n";

        // Add data
        foreach ($this->getMappedData() as $row) {
            $csv .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return $csv;
    }

    public function exportToJson(): string
    {
        return json_encode([
            'title' => $this->getTitle(),
            'headers' => $this->getHeadings(),
            'data' => $this->getMappedData(),
            'exported_at' => now()->toISOString()
        ], JSON_PRETTY_PRINT);
    }
}
