<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Hasil Ranking SAW - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            background-color: #198754;
            color: white;
            padding: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 12px;
        }

        .meta-info {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 10px;
        }

        .meta-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-info td {
            padding: 5px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .meta-info .label {
            background-color: #e9ecef;
            font-weight: bold;
            width: 20%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #198754;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .rank-1 { background-color: #ffd700; font-weight: bold; }
        .rank-2 { background-color: #c0c0c0; font-weight: bold; }
        .rank-3 { background-color: #cd7f32; font-weight: bold; color: white; }
        .rank-top10 { background-color: #d4edda; }
        .rank-other { background-color: #f8f9fa; }

        .score-excellent { background-color: #d1ecf1; color: #0c5460; font-weight: bold; }
        .score-good { background-color: #d4edda; color: #155724; font-weight: bold; }
        .score-average { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .score-poor { background-color: #f8d7da; color: #721c24; font-weight: bold; }

        .criteria-section {
            margin-top: 30px;
            background-color: #f8f9fa;
            padding: 15px;
        }

        .criteria-section h3 {
            margin: 0 0 15px 0;
            color: #0d6efd;
            background-color: #0d6efd;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .benefit { background-color: #d4edda; color: #155724; }
        .cost { background-color: #fff3cd; color: #856404; }

        .statistics-section {
            margin-top: 30px;
            background-color: #e3f2fd;
            padding: 15px;
        }

        .statistics-section h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
            background-color: #1976d2;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 2px solid #198754;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.name', 'SAW Employee Evaluation') }}</h1>
        <div class="subtitle">Laporan Hasil Ranking SAW (Simple Additive Weighting)</div>
        <div class="subtitle">Periode: {{ $period }} | Export: {{ date('d F Y H:i:s') }}</div>
    </div>

    <!-- Meta Information -->
    <div class="meta-info">
        <table>
            <tr>
                <td class="label">Total Karyawan</td>
                <td>{{ $results->count() }} orang</td>
                <td class="label">Total Kriteria</td>
                <td>{{ $criterias->count() }} kriteria</td>
            </tr>
            <tr>
                <td class="label">Periode Evaluasi</td>
                <td>{{ $period }}</td>
                <td class="label">Tanggal Export</td>
                <td>{{ date('d F Y H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">Metode</td>
                <td colspan="3">Simple Additive Weighting (SAW) - Normalisasi dan Pembobotan Kriteria</td>
            </tr>
            <tr>
                <td class="label">Rata-rata Skor</td>
                <td>{{ number_format($results->avg('total_score') * 100, 2) }}%</td>
                <td class="label">Skor Tertinggi</td>
                <td>{{ number_format($results->max('total_score') * 100, 2) }}%</td>
            </tr>
        </table>
    </div>

    <!-- Ranking Results Table -->
    <table>
        <thead>
            <tr>
                <th width="8%">Ranking</th>
                <th width="12%">Kode Karyawan</th>
                <th width="25%">Nama Karyawan</th>
                <th width="15%">Department</th>
                <th width="15%">Posisi</th>
                <th width="10%">Total Skor</th>
                <th width="15%">Kategori Performance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            @php
                $rowClass = match(true) {
                    $result->ranking == 1 => 'rank-1',
                    $result->ranking == 2 => 'rank-2',
                    $result->ranking == 3 => 'rank-3',
                    $result->ranking <= 10 => 'rank-top10',
                    default => 'rank-other'
                };

                $scoreClass = match(true) {
                    $result->score_percentage >= 90 => 'score-excellent',
                    $result->score_percentage >= 80 => 'score-good',
                    $result->score_percentage >= 70 => 'score-average',
                    default => 'score-poor'
                };

                $category = match(true) {
                    $result->score_percentage >= 90 => 'Outstanding',
                    $result->score_percentage >= 80 => 'Excellent',
                    $result->score_percentage >= 70 => 'Good',
                    $result->score_percentage >= 60 => 'Satisfactory',
                    default => 'Needs Improvement'
                };
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="center bold">{{ $result->ranking }}</td>
                <td class="center bold">{{ $result->employee->employee_code }}</td>
                <td>{{ $result->employee->name }}</td>
                <td>{{ $result->employee->department }}</td>
                <td>{{ $result->employee->position }}</td>
                <td class="center bold {{ $scoreClass }}">{{ number_format($result->score_percentage, 2) }}%</td>
                <td class="center {{ $scoreClass }}">{{ $category }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Criteria Information -->
    <div class="criteria-section">
        <h3>📊 KRITERIA PENILAIAN</h3>
        <table>
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="30%">Nama Kriteria</th>
                    <th width="10%">Bobot (%)</th>
                    <th width="10%">Tipe</th>
                    <th width="45%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($criterias as $index => $criteria)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $criteria->name }}</td>
                    <td class="center bold">{{ $criteria->weight }}%</td>
                    <td class="center {{ $criteria->type === 'benefit' ? 'benefit' : 'cost' }}">
                        {{ ucfirst($criteria->type) }}
                    </td>
                    <td>
                        {{ $criteria->type === 'benefit' ? 'Semakin tinggi nilai semakin baik' : 'Semakin rendah nilai semakin baik' }}
                        @if($criteria->weight >= 20)
                            <strong>(Kriteria Utama)</strong>
                        @elseif($criteria->weight >= 10)
                            (Kriteria Penting)
                        @else
                            (Kriteria Pendukung)
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr style="background-color: #198754; color: white; font-weight: bold;">
                    <td colspan="2" class="center">TOTAL BOBOT KRITERIA</td>
                    <td class="center">{{ $criterias->sum('weight') }}%</td>
                    <td colspan="2" class="center">
                        @if($criterias->sum('weight') == 100)
                            ✓ VALID (Total bobot = 100%)
                        @else
                            ⚠ TIDAK VALID (Total bobot ≠ 100%)
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Performance Statistics -->
    <div class="statistics-section">
        <h3>📈 STATISTIK PERFORMANCE</h3>
        <table>
            <thead>
                <tr>
                    <th width="25%">Kategori Performance</th>
                    <th width="15%">Jumlah Karyawan</th>
                    <th width="15%">Persentase</th>
                    <th width="20%">Rata-rata Skor</th>
                    <th width="25%">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $excellent = $results->where('score_percentage', '>=', 90);
                    $good = $results->where('score_percentage', '>=', 80)->where('score_percentage', '<', 90);
                    $average = $results->where('score_percentage', '>=', 70)->where('score_percentage', '<', 80);
                    $satisfactory = $results->where('score_percentage', '>=', 60)->where('score_percentage', '<', 70);
                    $poor = $results->where('score_percentage', '<', 60);
                    $total = $results->count();
                @endphp
                <tr class="score-excellent">
                    <td class="bold">Outstanding (90-100%)</td>
                    <td class="center bold">{{ $excellent->count() }}</td>
                    <td class="center bold">{{ $total > 0 ? number_format(($excellent->count() / $total) * 100, 1) : 0 }}%</td>
                    <td class="center bold">{{ $excellent->count() > 0 ? number_format($excellent->avg('score_percentage'), 1) : 0 }}%</td>
                    <td>Performance sangat baik, melebihi ekspektasi</td>
                </tr>
                <tr class="score-good">
                    <td class="bold">Excellent (80-89%)</td>
                    <td class="center bold">{{ $good->count() }}</td>
                    <td class="center bold">{{ $total > 0 ? number_format(($good->count() / $total) * 100, 1) : 0 }}%</td>
                    <td class="center bold">{{ $good->count() > 0 ? number_format($good->avg('score_percentage'), 1) : 0 }}%</td>
                    <td>Performance baik, sesuai ekspektasi</td>
                </tr>
                <tr class="score-average">
                    <td class="bold">Good (70-79%)</td>
                    <td class="center bold">{{ $average->count() }}</td>
                    <td class="center bold">{{ $total > 0 ? number_format(($average->count() / $total) * 100, 1) : 0 }}%</td>
                    <td class="center bold">{{ $average->count() > 0 ? number_format($average->avg('score_percentage'), 1) : 0 }}%</td>
                    <td>Performance cukup baik, ada ruang improvement</td>
                </tr>
                <tr style="background-color: #ffeaa7;">
                    <td class="bold">Satisfactory (60-69%)</td>
                    <td class="center bold">{{ $satisfactory->count() }}</td>
                    <td class="center bold">{{ $total > 0 ? number_format(($satisfactory->count() / $total) * 100, 1) : 0 }}%</td>
                    <td class="center bold">{{ $satisfactory->count() > 0 ? number_format($satisfactory->avg('score_percentage'), 1) : 0 }}%</td>
                    <td>Performance mencukupi, perlu peningkatan</td>
                </tr>
                <tr class="score-poor">
                    <td class="bold">Needs Improvement (<60%)</td>
                    <td class="center bold">{{ $poor->count() }}</td>
                    <td class="center bold">{{ $total > 0 ? number_format(($poor->count() / $total) * 100, 1) : 0 }}%</td>
                    <td class="center bold">{{ $poor->count() > 0 ? number_format($poor->avg('score_percentage'), 1) : 0 }}%</td>
                    <td>Performance di bawah standar, perlu improvement signifikan</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Department Performance -->
    @if($results->groupBy('employee.department')->count() > 1)
    <div class="statistics-section" style="margin-top: 20px;">
        <h3>🏢 PERFORMANCE PER DEPARTMENT</h3>
        <table>
            <thead>
                <tr>
                    <th width="30%">Department</th>
                    <th width="15%">Jumlah Karyawan</th>
                    <th width="20%">Rata-rata Skor</th>
                    <th width="15%">Skor Tertinggi</th>
                    <th width="20%">Ranking Tertinggi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results->groupBy('employee.department') as $department => $deptResults)
                <tr>
                    <td class="bold">{{ $department }}</td>
                    <td class="center">{{ $deptResults->count() }}</td>
                    <td class="center bold">{{ number_format($deptResults->avg('score_percentage'), 1) }}%</td>
                    <td class="center bold">{{ number_format($deptResults->max('score_percentage'), 1) }}%</td>
                    <td class="center bold">#{{ $deptResults->min('ranking') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Top Performers Details -->
    <div class="statistics-section" style="margin-top: 20px;">
        <h3>🏆 TOP 10 PERFORMERS DETAIL</h3>
        <table>
            <thead>
                <tr>
                    <th width="8%">Rank</th>
                    <th width="20%">Nama Karyawan</th>
                    <th width="15%">Department</th>
                    <th width="15%">Posisi</th>
                    <th width="12%">Total Skor</th>
                    <th width="30%">Kelebihan Utama</th>
                </tr>
            </thead>
            <tbody>
                @foreach($results->take(10) as $result)
                <tr class="{{ $result->ranking <= 3 ? 'rank-' . $result->ranking : 'rank-top10' }}">
                    <td class="center bold">{{ $result->ranking }}</td>
                    <td class="bold">{{ $result->employee->name }}</td>
                    <td>{{ $result->employee->department }}</td>
                    <td>{{ $result->employee->position }}</td>
                    <td class="center bold">{{ number_format($result->score_percentage, 2) }}%</td>
                    <td>
                        @if($result->ranking == 1)
                            🥇 Performer terbaik dengan skor tertinggi
                        @elseif($result->ranking == 2)
                            🥈 Performer sangat baik, runner up
                        @elseif($result->ranking == 3)
                            🥉 Performer baik, masuk top 3
                        @elseif($result->ranking <= 5)
                            ⭐ Performer konsisten dalam top 5
                        @else
                            ✨ Performer solid dalam top 10
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- SAW Method Information -->
    <div class="criteria-section" style="margin-top: 30px; background-color: #e3f2fd;">
        <h3>🔬 TENTANG METODE SAW (Simple Additive Weighting)</h3>
        <table>
            <tr>
                <td colspan="2" style="background-color: #1976d2; color: white; padding: 10px; font-weight: bold;">
                    LANGKAH-LANGKAH PERHITUNGAN SAW
                </td>
            </tr>
            <tr>
                <td width="20%" class="bold" style="background-color: #bbdefb;">Langkah 1</td>
                <td>Normalisasi matriks keputusan berdasarkan tipe kriteria (Benefit/Cost)</td>
            </tr>
            <tr>
                <td class="bold" style="background-color: #bbdefb;">Langkah 2</td>
                <td>Mengalikan nilai normalisasi dengan bobot masing-masing kriteria</td>
            </tr>
            <tr>
                <td class="bold" style="background-color: #bbdefb;">Langkah 3</td>
                <td>Menjumlahkan hasil perkalian untuk mendapat nilai preferensi akhir</td>
            </tr>
            <tr>
                <td class="bold" style="background-color: #bbdefb;">Langkah 4</td>
                <td>Mengurutkan alternatif berdasarkan nilai preferensi tertinggi</td>
            </tr>
            <tr>
                <td colspan="2" style="background-color: #1976d2; color: white; padding: 10px; font-weight: bold;">
                    FORMULA PERHITUNGAN
                </td>
            </tr>
            <tr>
                <td class="bold" style="background-color: #bbdefb;">Benefit</td>
                <td>Rij = Xij / Max(Xij) - Semakin tinggi semakin baik</td>
            </tr>
            <tr>
                <td class="bold" style="background-color: #bbdefb;">Cost</td>
                <td>Rij = Min(Xij) / Xij - Semakin rendah semakin baik</td>
            </tr>
            <tr>
                <td class="bold" style="background-color: #bbdefb;">Final Score</td>
                <td>Vi = Σ(Wj × Rij) dimana Wj = bobot kriteria, Rij = nilai normalisasi</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'SAW Employee Evaluation System') }}</strong><br>
            Sistem Penunjang Keputusan - Algoritma Simple Additive Weighting (SAW)<br>
            Digenerate pada: {{ date('d F Y H:i:s') }} | Periode Evaluasi: {{ $period }}<br>
            Total Karyawan: {{ $results->count() }} | Total Kriteria: {{ $criterias->count() }}<br>
            <strong>CONFIDENTIAL</strong> - Data ini hanya untuk penggunaan internal perusahaan
        </p>
    </div>
</body>
</html>


