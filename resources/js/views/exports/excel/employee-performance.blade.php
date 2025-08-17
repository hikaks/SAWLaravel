<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Performance Report - {{ $employee->name }} - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            background-color: #0366d6;
            color: white;
            padding: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 16px;
            font-weight: bold;
        }

        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 12px;
        }

        .employee-info {
            margin-bottom: 20px;
            background-color: #e3f2fd;
            padding: 15px;
        }

        .employee-info h2 {
            margin: 0 0 10px 0;
            color: #0366d6;
            font-size: 14px;
        }

        .employee-info table {
            width: 100%;
            border-collapse: collapse;
        }

        .employee-info td {
            padding: 5px;
            border: 1px solid #dee2e6;
            text-align: left;
        }

        .employee-info .label {
            background-color: #bbdefb;
            font-weight: bold;
            width: 20%;
        }

        .score-summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .score-summary h3 {
            margin: 0 0 10px 0;
            color: #0366d6;
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
            background-color: #0366d6;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: bold; }

        .score-excellent { background-color: #d1ecf1; color: #0c5460; font-weight: bold; }
        .score-good { background-color: #d4edda; color: #155724; font-weight: bold; }
        .score-average { background-color: #fff3cd; color: #856404; font-weight: bold; }
        .score-poor { background-color: #f8d7da; color: #721c24; font-weight: bold; }

        .benefit { background-color: #d4edda; color: #155724; }
        .cost { background-color: #fff3cd; color: #856404; }

        .analysis-section {
            margin-top: 20px;
            background-color: #f8f9fa;
            padding: 15px;
        }

        .analysis-section h3 {
            margin: 0 0 15px 0;
            color: #0366d6;
            background-color: #0366d6;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .strength-area {
            background-color: #d4edda;
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #198754;
        }

        .improvement-area {
            background-color: #fff3cd;
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid #ffc107;
        }

        .saw-section {
            margin-top: 30px;
            background-color: #e3f2fd;
            padding: 15px;
        }

        .saw-section h3 {
            margin: 0 0 15px 0;
            color: #1976d2;
            background-color: #1976d2;
            color: white;
            padding: 10px;
            text-align: center;
        }

        .formula {
            background-color: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            font-family: monospace;
            margin: 10px 0;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 2px solid #0366d6;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.name', 'SAW Employee Evaluation') }}</h1>
        <div class="subtitle">Laporan Performance Individual - {{ $employee->name }}</div>
        <div class="subtitle">Periode: {{ $period }} | Export: {{ date('d F Y H:i:s') }}</div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <h2>INFORMASI KARYAWAN</h2>
        <table>
            <tr>
                <td class="label">Nama Karyawan</td>
                <td>{{ $employee->name }}</td>
                <td class="label">Kode Karyawan</td>
                <td>{{ $employee->employee_code }}</td>
            </tr>
            <tr>
                <td class="label">Posisi/Jabatan</td>
                <td>{{ $employee->position }}</td>
                <td class="label">Department</td>
                <td>{{ $employee->department }}</td>
            </tr>
            <tr>
                <td class="label">Email</td>
                <td>{{ $employee->email }}</td>
                <td class="label">Periode Evaluasi</td>
                <td>{{ $period }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal Export</td>
                <td>{{ date('d F Y H:i:s') }}</td>
                <td class="label">Total Kriteria</td>
                <td>{{ $evaluations->count() }} kriteria</td>
            </tr>
        </table>
    </div>

    <!-- Score Summary -->
    <div class="score-summary">
        <h3>RINGKASAN PERFORMANCE</h3>
        <table>
            <tr>
                <td class="label center" style="background-color: #0366d6; color: white;">TOTAL SKOR FINAL</td>
                <td class="center bold" style="font-size: 16px; background-color: #e3f2fd;">{{ number_format($result->score_percentage, 2) }}%</td>
                <td class="label center" style="background-color: #198754; color: white;">RANKING POSISI</td>
                <td class="center bold" style="font-size: 16px; background-color: #d4edda;">#{{ $result->ranking }}</td>
            </tr>
            <tr>
                <td class="label center">Kategori Performance</td>
                <td class="center bold {{
                    $result->score_percentage >= 90 ? 'score-excellent' :
                    ($result->score_percentage >= 80 ? 'score-good' :
                    ($result->score_percentage >= 70 ? 'score-average' : 'score-poor'))
                }}">
                    {{ $result->score_percentage >= 90 ? 'OUTSTANDING' :
                       ($result->score_percentage >= 80 ? 'EXCELLENT' :
                       ($result->score_percentage >= 70 ? 'GOOD' : 'NEEDS IMPROVEMENT')) }}
                </td>
                <td class="label center">Status Evaluasi</td>
                <td class="center bold" style="background-color: #d4edda; color: #155724;">COMPLETED</td>
            </tr>
        </table>
    </div>

    <!-- Quick Statistics -->
    <table>
        <thead>
            <tr>
                <th colspan="4">STATISTIK PERFORMANCE</th>
            </tr>
            <tr>
                <th>Rata-rata Skor</th>
                <th>Skor Tertinggi</th>
                <th>Skor Terendah</th>
                <th>Standar Deviasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center bold">{{ $evaluations->count() > 0 ? number_format($evaluations->avg('score'), 1) : 0 }}</td>
                <td class="center bold">{{ $evaluations->max('score') ?: 0 }}</td>
                <td class="center bold">{{ $evaluations->min('score') ?: 0 }}</td>
                <td class="center bold">
                    @php
                        $scores = $evaluations->pluck('score');
                        $mean = $scores->avg();
                        $variance = $scores->map(function($score) use ($mean) {
                            return pow($score - $mean, 2);
                        })->avg();
                        $stdDev = sqrt($variance);
                    @endphp
                    {{ number_format($stdDev, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Detailed Criteria Performance -->
    <table>
        <thead>
            <tr>
                <th colspan="8">DETAIL PERFORMANCE PER KRITERIA</th>
            </tr>
            <tr>
                <th width="5%">No</th>
                <th width="25%">Nama Kriteria</th>
                <th width="10%">Tipe</th>
                <th width="10%">Bobot (%)</th>
                <th width="10%">Skor Raw</th>
                <th width="12%">Skor Normal</th>
                <th width="12%">Skor Weighted</th>
                <th width="16%">Kategori Performance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($evaluations as $index => $evaluation)
            @php
                $scoreClass = $evaluation->score >= 90 ? 'score-excellent' :
                             ($evaluation->score >= 80 ? 'score-good' :
                             ($evaluation->score >= 70 ? 'score-average' : 'score-poor'));

                // Calculate normalized and weighted scores
                $allScores = $evaluations->where('criteria_id', $evaluation->criteria_id)->pluck('score');
                $normalizedScore = $evaluation->getNormalizedScore($allScores->toArray());
                $weightedScore = $normalizedScore * ($evaluation->criteria->weight / 100);
            @endphp
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="bold">{{ $evaluation->criteria->name }}</td>
                <td class="center {{ $evaluation->criteria->type === 'benefit' ? 'benefit' : 'cost' }}">
                    {{ ucfirst($evaluation->criteria->type) }}
                </td>
                <td class="center bold">{{ $evaluation->criteria->weight }}%</td>
                <td class="center bold {{ $scoreClass }}">{{ $evaluation->score }}</td>
                <td class="center">{{ number_format($normalizedScore, 4) }}</td>
                <td class="center">{{ number_format($weightedScore, 4) }}</td>
                <td class="center {{ $scoreClass }}">
                    {{ $evaluation->score >= 90 ? 'Outstanding' :
                       ($evaluation->score >= 80 ? 'Excellent' :
                       ($evaluation->score >= 70 ? 'Good' :
                       ($evaluation->score >= 60 ? 'Satisfactory' : 'Poor'))) }}
                </td>
            </tr>
            @endforeach
            <tr style="background-color: #0366d6; color: white; font-weight: bold;">
                <td colspan="6" class="center">TOTAL SKOR FINAL (SAW)</td>
                <td class="center">{{ number_format($result->total_score, 4) }}</td>
                <td class="center">{{ number_format($result->score_percentage, 2) }}%</td>
            </tr>
        </tbody>
    </table>

    <!-- Performance Analysis -->
    <div class="analysis-section">
        <h3>ANALISIS PERFORMANCE</h3>
        <table>
            <thead>
                <tr>
                    <th width="50%">KEKUATAN UTAMA (Skor ≥ 80)</th>
                    <th width="50%">AREA PENGEMBANGAN (Skor < 70)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="vertical-align: top;">
                        @php
                            $strongAreas = $evaluations->where('score', '>=', 80);
                        @endphp
                        @if($strongAreas->count() > 0)
                            @foreach($strongAreas as $area)
                            <div class="strength-area">
                                <strong>{{ $area->criteria->name }}</strong><br>
                                Skor: {{ $area->score }} ({{ $area->criteria->weight }}% bobot)
                            </div>
                            @endforeach
                        @else
                            <div class="strength-area">
                                Belum ada area yang mencapai skor excellent (≥80).<br>
                                Fokus pada peningkatan performance secara umum.
                            </div>
                        @endif
                    </td>
                    <td style="vertical-align: top;">
                        @php
                            $improvementAreas = $evaluations->where('score', '<', 70);
                        @endphp
                        @if($improvementAreas->count() > 0)
                            @foreach($improvementAreas as $area)
                            <div class="improvement-area">
                                <strong>{{ $area->criteria->name }}</strong><br>
                                Skor: {{ $area->score }} - Perlu peningkatan ({{ $area->criteria->weight }}% bobot)
                            </div>
                            @endforeach
                        @else
                            <div class="improvement-area">
                                Excellent! Semua kriteria sudah perform di atas average (≥70).<br>
                                Pertahankan konsistensi dan tingkatkan ke level outstanding.
                            </div>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Recommendations -->
        <table style="margin-top: 15px;">
            <thead>
                <tr>
                    <th>REKOMENDASI PENGEMBANGAN</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding: 15px;">
                        @if($result->score_percentage >= 90)
                            <strong>🏆 OUTSTANDING PERFORMANCE!</strong><br>
                            • Anda menunjukkan performance yang sangat baik di semua area<br>
                            • Pertahankan konsistensi dan standar tinggi<br>
                            • Berbagi best practice dengan rekan kerja<br>
                            • Explore peluang untuk role yang lebih challenging
                        @elseif($result->score_percentage >= 80)
                            <strong>⭐ EXCELLENT PERFORMANCE!</strong><br>
                            • Performance sangat baik dengan beberapa area untuk optimasi<br>
                            • Fokus pada kriteria yang masih di bawah 90 untuk mencapai outstanding<br>
                            • Maintain strength areas dan terus tingkatkan<br>
                            • Siap untuk tanggung jawab yang lebih besar
                        @elseif($result->score_percentage >= 70)
                            <strong>👍 GOOD PERFORMANCE</strong><br>
                            • Performance solid dengan ruang untuk improvement<br>
                            • Prioritaskan peningkatan di area dengan skor < 80<br>
                            • Diskusikan training/development plan dengan supervisor<br>
                            • Set target spesifik untuk quarter berikutnya
                        @else
                            <strong>📈 FOCUS REQUIRED</strong><br>
                            • Performance perlu peningkatan signifikan<br>
                            • Buat action plan detail dengan supervisor<br>
                            • Identifikasi root cause dari performance yang rendah<br>
                            • Consider additional training, mentoring, atau support
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Criteria Information -->
    <table style="margin-top: 20px;">
        <thead>
            <tr>
                <th colspan="4">INFORMASI KRITERIA PENILAIAN</th>
            </tr>
            <tr>
                <th width="40%">Nama Kriteria</th>
                <th width="15%">Bobot (%)</th>
                <th width="15%">Tipe</th>
                <th width="30%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($criterias as $criteria)
            <tr>
                <td class="bold">{{ $criteria->name }}</td>
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
            <tr style="background-color: #0366d6; color: white; font-weight: bold;">
                <td>TOTAL BOBOT</td>
                <td class="center">{{ $criterias->sum('weight') }}%</td>
                <td colspan="2" class="center">
                    @if($criterias->sum('weight') == 100)
                        ✓ VALID - Total bobot kriteria = 100%
                    @else
                        ⚠ TIDAK VALID - Total bobot ≠ 100%
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SAW Method Information -->
    <div class="saw-section">
        <h3>METODE PERHITUNGAN SAW (Simple Additive Weighting)</h3>
        <table>
            <tr>
                <td width="50%" style="vertical-align: top;">
                    <h4 style="color: #1976d2; margin: 0 0 10px 0;">Langkah Perhitungan:</h4>
                    <ol>
                        <li><strong>Input Data:</strong> Skor mentah untuk setiap kriteria</li>
                        <li><strong>Normalisasi:</strong> Konversi skor ke skala 0-1</li>
                        <li><strong>Pembobotan:</strong> Kalikan dengan bobot kriteria</li>
                        <li><strong>Agregasi:</strong> Jumlahkan semua skor berbobot</li>
                        <li><strong>Ranking:</strong> Urutkan berdasarkan skor tertinggi</li>
                    </ol>
                </td>
                <td width="50%" style="vertical-align: top;">
                    <h4 style="color: #1976d2; margin: 0 0 10px 0;">Formula Normalisasi:</h4>
                    <div class="formula">
                        <strong>Benefit Criteria:</strong><br>
                        Rij = Xij / Max(Xij)<br><br>
                        <strong>Cost Criteria:</strong><br>
                        Rij = Min(Xij) / Xij<br><br>
                        <strong>Final Score:</strong><br>
                        Vi = Σ(Wj × Rij)
                    </div>
                </td>
            </tr>
        </table>

        <table style="margin-top: 15px;">
            <tr>
                <td style="background-color: #fff3cd; padding: 10px;">
                    <strong>Catatan Penting:</strong><br>
                    • Xij = Skor mentah kriteria j untuk alternative i<br>
                    • Rij = Skor ternormalisasi<br>
                    • Wj = Bobot kriteria j<br>
                    • Vi = Nilai preferensi akhir (Total Score)<br>
                    • Semakin tinggi Vi, semakin baik performance
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'SAW Employee Evaluation System') }}</strong><br>
            Performance Report - {{ $employee->name }} ({{ $employee->employee_code }})<br>
            Periode: {{ $period }} | Generated: {{ date('d F Y H:i:s') }}<br>
            Metode: Simple Additive Weighting (SAW) | {{ $criterias->count() }} Kriteria Penilaian<br>
            <strong>CONFIDENTIAL</strong> - Dokumen ini hanya untuk penggunaan internal perusahaan
        </p>
    </div>
</body>
</html>


