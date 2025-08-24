<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Hasil Ranking SAW - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #198754;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #198754;
            margin: 0;
            font-size: 26px;
            font-weight: bold;
        }

        .header .subtitle {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
        }

        .period-info {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .period-info h2 {
            margin: 0;
            font-size: 18px;
        }

        .meta-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-info td {
            padding: 5px;
            border: none;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .ranking-table th,
        .ranking-table td {
            border: 1px solid #dee2e6;
            padding: 10px;
            text-align: left;
        }

        .ranking-table th {
            background-color: #198754;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .ranking-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .rank-badge {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 50%;
            color: white;
            text-align: center;
            display: inline-block;
            min-width: 30px;
        }

        .rank-1 { background-color: #ffd700; color: #333; }
        .rank-2 { background-color: #c0c0c0; color: #333; }
        .rank-3 { background-color: #cd7f32; color: white; }
        .rank-other { background-color: #6c757d; }

        .score-bar {
            background-color: #e9ecef;
            height: 20px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
        }

        .score-fill {
            height: 100%;
            border-radius: 10px;
            position: relative;
        }

        .score-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 10px;
            color: white;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
        }

        .top-performers {
            background-color: #d4edda;
            border: 2px solid #c3e6cb;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }

        .performance-category {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-excellent { background-color: #d1ecf1; color: #0c5460; }
        .category-good { background-color: #d4edda; color: #155724; }
        .category-average { background-color: #fff3cd; color: #856404; }
        .category-poor { background-color: #f8d7da; color: #721c24; }

        .criteria-summary {
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .criteria-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .criteria-table th,
        .criteria-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: center;
        }

        .criteria-table th {
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .saw-explanation {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin: 20px 0;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <div class="subtitle">Laporan Hasil Ranking SAW (Simple Additive Weighting)</div>
        <div class="subtitle">{{ date('d F Y') }}</div>
    </div>

    <div class="period-info">
        <h2>Periode Evaluasi: {{ $period }}</h2>
        <p style="margin: 5px 0 0 0;">{{ $results->count() }} Karyawan Dinilai</p>
        @if($period === 'all-periods')
            @php
                $uniquePeriods = $results->pluck('evaluation_period')->unique();
                $totalPeriods = $uniquePeriods->count();
            @endphp
            <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.9;">Mencakup {{ $totalPeriods }} Periode Evaluasi</p>
        @endif
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td><strong>Total Karyawan:</strong></td>
                <td>{{ $results->count() }} orang</td>
                <td><strong>Total Kriteria:</strong></td>
                <td>{{ $criterias->count() }} kriteria</td>
            </tr>
            <tr>
                <td><strong>Periode Evaluasi:</strong></td>
                <td>
                    @if($period === 'all-periods')
                        Semua Periode ({{ $uniquePeriods->count() }} periode)
                    @else
                        {{ $period }}
                    @endif
                </td>
                <td><strong>Tanggal Export:</strong></td>
                <td>{{ date('d F Y H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Metode:</strong></td>
                <td colspan="3">Simple Additive Weighting (SAW) - Normalisasi dan Pembobotan</td>
            </tr>
        </table>
    </div>

    <!-- Performance Statistics -->
    <div class="meta-info" style="background-color: #e3f2fd; border: 1px solid #2196f3;">
        <h3 style="margin: 0 0 10px 0; color: #1976d2; text-align: center;">📊 Statistik Performance</h3>
        <table style="margin-bottom: 0;">
                    @php
            $excellent = $results->where('total_score', '>=', 0.90);
            $good = $results->where('total_score', '>=', 0.80)->where('total_score', '<', 0.90);
            $average = $results->where('total_score', '>=', 0.70)->where('total_score', '<', 0.80);
            $poor = $results->where('total_score', '<', 0.70);
            $total = $results->count();
        @endphp
            <tr>
                <td><strong>Outstanding (90%+):</strong></td>
                <td>{{ $excellent->count() }} orang ({{ $total > 0 ? number_format(($excellent->count() / $total) * 100, 1) : 0 }}%)</td>
                <td><strong>Rata-rata Skor:</strong></td>
                <td>{{ number_format($results->avg('total_score') * 100, 1) }}%</td>
            </tr>
            <tr>
                <td><strong>Good (80-89%):</strong></td>
                <td>{{ $good->count() }} orang ({{ $total > 0 ? number_format(($good->count() / $total) * 100, 1) : 0 }}%)</td>
                <td><strong>Skor Tertinggi:</strong></td>
                <td>{{ number_format($results->max('total_score') * 100, 1) }}%</td>
            </tr>
            <tr>
                <td><strong>Average (70-79%):</strong></td>
                <td>{{ $average->count() }} orang ({{ $total > 0 ? number_format(($average->count() / $total) * 100, 1) : 0 }}%)</td>
                <td><strong>Skor Terendah:</strong></td>
                <td>{{ number_format($results->min('total_score') * 100, 1) }}%</td>
            </tr>
            <tr>
                <td><strong>Below Average (<70%):</strong></td>
                <td>{{ $poor->count() }} orang ({{ $total > 0 ? number_format(($poor->count() / $total) * 100, 1) : 0 }}%)</td>
                <td><strong>Standar Deviasi:</strong></td>
                @php
                    $mean = $results->avg('total_score') * 100;
                    $variance = $results->reduce(function($carry, $item) use ($mean) {
                        return $carry + pow(($item->total_score * 100) - $mean, 2);
                    }, 0) / $total;
                    $stdDev = sqrt($variance);
                @endphp
                <td>{{ number_format($stdDev, 2) }}</td>
            </tr>
        </table>
    </div>

    <!-- Top 3 Performers -->
    <div class="top-performers">
        <h3 style="margin: 0 0 15px 0; color: #198754;">🏆 Top 3 Performers</h3>
        <div style="display: flex; justify-content: space-between;">
            @foreach($results->take(3) as $index => $result)
            <div style="text-align: center; flex: 1;">
                <div class="rank-badge rank-{{ $index + 1 }}">{{ $result->ranking }}</div>
                <div style="margin-top: 8px; font-weight: bold;">{{ $result->employee->name }}</div>
                <div style="font-size: 10px; color: #666;">{{ $result->employee->department }}</div>
                <div style="font-weight: bold; color: #198754;">{{ number_format($result->total_score * 100, 1) }}%</div>
                <div style="font-size: 9px; color: #999;">{{ $result->employee->position }}</div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Ranking Table -->
    <table class="ranking-table">
        <thead>
            <tr>
                <th style="width: 8%">Rank</th>
                <th style="width: 12%">Kode</th>
                <th style="width: 22%">Nama Karyawan</th>
                <th style="width: 15%">Department</th>
                <th style="width: 15%">Posisi</th>
                @if($period === 'all-periods')
                    <th style="width: 10%">Periode</th>
                    <th style="width: 8%">Total Skor</th>
                @else
                    <th style="width: 10%">Total Skor</th>
                @endif
                <th style="width: 18%">Skor Visual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td style="text-align: center">
                    <span class="rank-badge {{ $result->ranking <= 3 ? 'rank-' . $result->ranking : 'rank-other' }}">
                        {{ $result->ranking }}
                    </span>
                </td>
                <td style="font-weight: bold">{{ $result->employee->employee_code }}</td>
                <td>{{ $result->employee->name }}</td>
                <td>{{ $result->employee->department }}</td>
                <td style="font-size: 10px;">{{ $result->employee->position }}</td>
                @if($period === 'all-periods')
                    <td style="text-align: center; font-size: 10px;">{{ $result->evaluation_period }}</td>
                @endif
                <td style="text-align: center; font-weight: bold">
                    {{ number_format($result->total_score * 100, 2) }}%
                    <br>
                    <span class="performance-category category-{{
                        ($result->total_score * 100) >= 90 ? 'excellent' :
                        (($result->total_score * 100) >= 80 ? 'good' :
                        (($result->total_score * 100) >= 70 ? 'average' : 'poor'))
                    }}">
                        {{
                            ($result->total_score * 100) >= 90 ? 'Outstanding' :
                            (($result->total_score * 100) >= 80 ? 'Excellent' :
                            (($result->total_score * 100) >= 70 ? 'Good' : 'Poor'))
                        }}
                    </span>
                </td>
                <td>
                    <div class="score-bar">
                        <div class="score-fill" style="
                            width: {{ ($result->total_score * 100) }}%;
                            background-color: {{
                                ($result->total_score * 100) >= 90 ? '#28a745' :
                                (($result->total_score * 100) >= 80 ? '#17a2b8' :
                                (($result->total_score * 100) >= 70 ? '#ffc107' : '#dc3545'))
                            }}">
                            <div class="score-text">{{ number_format($result->total_score * 100, 1) }}%</div>
                        </div>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Criteria Summary -->
    <div class="criteria-summary">
        <h3 style="margin: 0 0 10px 0; color: #0d6efd;">📊 Kriteria Penilaian</h3>
        <table class="criteria-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kriteria</th>
                    <th>Bobot (%)</th>
                    <th>Tipe</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criterias as $index => $criteria)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td style="text-align: left">{{ $criteria->name }}</td>
                    <td><strong>{{ $criteria->weight }}%</strong></td>
                    <td>
                        <span style="
                            padding: 3px 6px;
                            border-radius: 3px;
                            font-size: 10px;
                            font-weight: bold;
                            background-color: {{ $criteria->type === 'benefit' ? '#d4edda' : '#fff3cd' }};
                            color: {{ $criteria->type === 'benefit' ? '#155724' : '#856404' }};
                        ">
                            {{ ucfirst($criteria->type) }}
                        </span>
                    </td>
                    <td style="font-size: 10px; text-align: left">
                        {{ $criteria->type === 'benefit' ? 'Semakin tinggi semakin baik' : 'Semakin rendah semakin baik' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- SAW Method Explanation -->
    <div class="saw-explanation">
        <h4 style="margin: 0 0 10px 0; color: #1976d2;">Tentang Metode SAW</h4>
        <p style="margin: 0 0 8px 0;"><strong>Simple Additive Weighting (SAW)</strong> adalah metode pengambilan keputusan multi-kriteria yang:</p>
        <ol style="margin: 0; padding-left: 20px;">
            <li>Melakukan normalisasi matriks keputusan berdasarkan tipe kriteria (benefit/cost)</li>
            <li>Mengalikan nilai normalisasi dengan bobot masing-masing kriteria</li>
            <li>Menjumlahkan hasil perkalian untuk mendapat nilai preferensi akhir</li>
            <li>Mengurutkan alternatif berdasarkan nilai preferensi tertinggi</li>
        </ol>
        <p style="margin: 8px 0 0 0;"><strong>Formula:</strong> Vi = Sigma(Wj x Rij) dimana Wj = bobot kriteria, Rij = nilai normalisasi</p>
    </div>

    <div class="footer">
        <p>
            <strong>{{ config('app.name') }}</strong><br>
            Sistem Penunjang Keputusan - Algoritma SAW<br>
            Digenerate pada: {{ date('d F Y H:i:s') }} | Periode: {{ $period }}<br>
            <strong>CONFIDENTIAL</strong> - Data ini hanya untuk penggunaan internal
        </p>
    </div>
</body>
</html>

