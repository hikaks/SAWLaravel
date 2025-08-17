<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Performance Report - {{ $employee->name }} - {{ $period }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0366d6;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #0366d6;
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }

        .header .subtitle {
            color: #666;
            margin: 5px 0;
            font-size: 14px;
        }

        .employee-info {
            background: linear-gradient(135deg, #0366d6, #0256c7);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .employee-info h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
        }

        .employee-info table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .employee-info td {
            padding: 5px 10px;
            border: none;
            color: white;
        }

        .employee-info .label {
            font-weight: bold;
            opacity: 0.8;
            width: 25%;
        }

        .score-summary {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }

        .score-summary .big-score {
            font-size: 48px;
            font-weight: bold;
            color: #0366d6;
            margin: 0;
        }

        .score-summary .rank {
            font-size: 24px;
            font-weight: bold;
            color: #198754;
            margin: 10px 0;
        }

        .score-summary .category {
            font-size: 16px;
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            display: inline-block;
            margin-top: 10px;
        }

        .category-excellent { background-color: #198754; }
        .category-good { background-color: #0dcaf0; }
        .category-average { background-color: #ffc107; }
        .category-poor { background-color: #dc3545; }

        .criteria-section {
            margin-bottom: 30px;
        }

        .criteria-section h3 {
            color: #0366d6;
            margin: 0 0 15px 0;
            font-size: 18px;
            border-bottom: 2px solid #0366d6;
            padding-bottom: 5px;
        }

        .criteria-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .criteria-table th,
        .criteria-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }

        .criteria-table th {
            background-color: #0366d6;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .criteria-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .score-cell {
            text-align: center;
            font-weight: bold;
        }

        .score-excellent { background-color: #d1ecf1; color: #0c5460; }
        .score-good { background-color: #d4edda; color: #155724; }
        .score-average { background-color: #fff3cd; color: #856404; }
        .score-poor { background-color: #f8d7da; color: #721c24; }

        .progress-bar {
            background-color: #e9ecef;
            height: 20px;
            border-radius: 10px;
            position: relative;
            overflow: hidden;
            margin: 5px 0;
        }

        .progress-fill {
            height: 100%;
            border-radius: 10px;
            position: relative;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-weight: bold;
            font-size: 10px;
            color: white;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.5);
        }

        .analysis-section {
            background-color: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .analysis-section h3 {
            color: #1976d2;
            margin: 0 0 15px 0;
            font-size: 16px;
        }

        .analysis-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .analysis-item {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }

        .strength-area {
            background-color: #d4edda;
            border-left: 4px solid #198754;
            padding: 10px;
            margin-bottom: 10px;
        }

        .improvement-area {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin-bottom: 10px;
        }

        .saw-explanation {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .saw-explanation h4 {
            color: #0366d6;
            margin: 0 0 10px 0;
            font-size: 14px;
        }

        .formula {
            background-color: #e9ecef;
            padding: 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 5px 0;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .page-break {
            page-break-before: always;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin: 2px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #0366d6;
        }

        .stat-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.name', 'SAW Employee Evaluation') }}</h1>
        <div class="subtitle">Laporan Performance Individual</div>
        <div class="subtitle">{{ date('d F Y') }}</div>
    </div>

    <!-- Employee Information -->
    <div class="employee-info">
        <h2>{{ $employee->name }}</h2>
        <table>
            <tr>
                <td class="label">Kode Karyawan:</td>
                <td>{{ $employee->employee_code }}</td>
                <td class="label">Periode Evaluasi:</td>
                <td>{{ $period }}</td>
            </tr>
            <tr>
                <td class="label">Posisi:</td>
                <td>{{ $employee->position }}</td>
                <td class="label">Tanggal Export:</td>
                <td>{{ date('d F Y H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">Department:</td>
                <td>{{ $employee->department }}</td>
                <td class="label">Email:</td>
                <td>{{ $employee->email }}</td>
            </tr>
        </table>
    </div>

    <!-- Score Summary -->
    <div class="score-summary">
        <div class="big-score">{{ number_format($result->score_percentage, 1) }}%</div>
        <div class="rank">Ranking #{{ $result->ranking }}</div>
        @php
            $category = $result->score_percentage >= 90 ? 'excellent' :
                       ($result->score_percentage >= 80 ? 'good' :
                       ($result->score_percentage >= 70 ? 'average' : 'poor'));
            $categoryText = $result->score_percentage >= 90 ? 'Outstanding Performance' :
                           ($result->score_percentage >= 80 ? 'Excellent Performance' :
                           ($result->score_percentage >= 70 ? 'Good Performance' : 'Needs Improvement'));
        @endphp
        <div class="category category-{{ $category }}">{{ $categoryText }}</div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-number">{{ $evaluations->count() }}</div>
            <div class="stat-label">Kriteria Dinilai</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $evaluations->avg('score') ? number_format($evaluations->avg('score'), 1) : 0 }}</div>
            <div class="stat-label">Rata-rata Skor</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $evaluations->max('score') ?: 0 }}</div>
            <div class="stat-label">Skor Tertinggi</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $evaluations->min('score') ?: 0 }}</div>
            <div class="stat-label">Skor Terendah</div>
        </div>
    </div>

    <!-- Detailed Criteria Performance -->
    <div class="criteria-section">
        <h3>📊 Detail Performance per Kriteria</h3>
        <table class="criteria-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th width="25%">Nama Kriteria</th>
                    <th width="10%">Tipe</th>
                    <th width="10%">Bobot</th>
                    <th width="15%">Skor Raw</th>
                    <th width="15%">Skor Normal</th>
                    <th width="20%">Progress Visual</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluations as $index => $evaluation)
                @php
                    $scoreClass = $evaluation->score >= 90 ? 'excellent' :
                                 ($evaluation->score >= 80 ? 'good' :
                                 ($evaluation->score >= 70 ? 'average' : 'poor'));
                @endphp
                <tr>
                    <td class="score-cell">{{ $index + 1 }}</td>
                    <td><strong>{{ $evaluation->criteria->name }}</strong></td>
                    <td class="score-cell">
                        <span style="
                            padding: 3px 6px;
                            border-radius: 3px;
                            font-size: 10px;
                            font-weight: bold;
                            background-color: {{ $evaluation->criteria->type === 'benefit' ? '#d4edda' : '#fff3cd' }};
                            color: {{ $evaluation->criteria->type === 'benefit' ? '#155724' : '#856404' }};
                        ">
                            {{ ucfirst($evaluation->criteria->type) }}
                        </span>
                    </td>
                    <td class="score-cell"><strong>{{ $evaluation->criteria->weight }}%</strong></td>
                    <td class="score-cell score-{{ $scoreClass }}">
                        <strong>{{ $evaluation->score }}</strong>
                    </td>
                    <td class="score-cell">
                        @php
                            $allScores = $evaluations->where('criteria_id', $evaluation->criteria_id)->pluck('score');
                            $normalizedScore = $evaluation->getNormalizedScore($allScores->toArray());
                        @endphp
                        {{ number_format($normalizedScore, 3) }}
                    </td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress-fill" style="
                                width: {{ $evaluation->score }}%;
                                background-color: {{
                                    $evaluation->score >= 90 ? '#198754' :
                                    ($evaluation->score >= 80 ? '#0dcaf0' :
                                    ($evaluation->score >= 70 ? '#ffc107' : '#dc3545'))
                                }}">
                                <div class="progress-text">{{ $evaluation->score }}%</div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Performance Analysis -->
    <div class="analysis-section">
        <h3>🎯 Analisis Performance</h3>
        <div class="analysis-grid">
            <div class="analysis-item">
                <h4 style="color: #198754; margin: 0 0 10px 0;">✅ Kekuatan Utama</h4>
                @php
                    $strongAreas = $evaluations->where('score', '>=', 80);
                @endphp
                @if($strongAreas->count() > 0)
                    @foreach($strongAreas as $area)
                    <div class="strength-area">
                        <strong>{{ $area->criteria->name }}</strong><br>
                        <small>Skor: {{ $area->score }} | Bobot: {{ $area->criteria->weight }}%</small>
                    </div>
                    @endforeach
                @else
                    <div class="strength-area">
                        <small>Perlu fokus membangun area kekuatan yang lebih solid</small>
                    </div>
                @endif
            </div>
            <div class="analysis-item">
                <h4 style="color: #ffc107; margin: 0 0 10px 0;">📈 Area Pengembangan</h4>
                @php
                    $improvementAreas = $evaluations->where('score', '<', 70);
                @endphp
                @if($improvementAreas->count() > 0)
                    @foreach($improvementAreas as $area)
                    <div class="improvement-area">
                        <strong>{{ $area->criteria->name }}</strong><br>
                        <small>Skor: {{ $area->score }} | Perlu ditingkatkan</small>
                    </div>
                    @endforeach
                @else
                    <div class="improvement-area">
                        <small>Excellent! Semua area sudah perform dengan baik</small>
                    </div>
                @endif
            </div>
        </div>

        <div style="background-color: #fff; padding: 15px; border-radius: 5px; margin-top: 15px;">
            <h4 style="color: #0366d6; margin: 0 0 10px 0;">📋 Rekomendasi</h4>
            <p style="margin: 0; line-height: 1.6;">
                @if($result->score_percentage >= 90)
                    <strong>Outstanding!</strong> Performance Anda sangat baik. Pertahankan konsistensi dan jadilah mentor untuk rekan lain.
                @elseif($result->score_percentage >= 80)
                    <strong>Excellent!</strong> Performance baik dengan beberapa area yang bisa dioptimalkan untuk mencapai level outstanding.
                @elseif($result->score_percentage >= 70)
                    <strong>Good.</strong> Performance cukup baik. Fokus pada peningkatan di area yang skornya masih rendah.
                @else
                    <strong>Focus Required.</strong> Perlu peningkatan signifikan. Diskusikan dengan supervisor untuk action plan yang tepat.
                @endif
            </p>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- SAW Calculation Explanation -->
    <div class="saw-explanation">
        <h4>🔬 Tentang Metode Perhitungan SAW</h4>
        <p><strong>Simple Additive Weighting (SAW)</strong> adalah metode yang digunakan untuk menghitung performance score dengan langkah:</p>
        <ol style="margin: 10px 0; padding-left: 20px;">
            <li><strong>Normalisasi:</strong> Mengkonversi skor mentah menjadi nilai terstandar (0-1)</li>
            <li><strong>Pembobotan:</strong> Mengalikan nilai normal dengan bobot kriteria</li>
            <li><strong>Penjumlahan:</strong> Menjumlahkan semua nilai berbobot</li>
            <li><strong>Ranking:</strong> Mengurutkan berdasarkan total skor tertinggi</li>
        </ol>

        <h4 style="margin: 15px 0 5px 0;">Formula yang Digunakan:</h4>
        <div class="formula">
            <strong>Benefit Criteria:</strong> Rij = Xij / Max(Xij)<br>
            <strong>Cost Criteria:</strong> Rij = Min(Xij) / Xij<br>
            <strong>Final Score:</strong> Vi = Σ(Wj × Rij)
        </div>

        <p style="margin: 10px 0 0 0;"><small>
            Dimana: Xij = skor mentah, Rij = skor normalisasi, Wj = bobot kriteria, Vi = skor akhir
        </small></p>
    </div>

    <!-- Criteria Weight Information -->
    <div class="criteria-section">
        <h3>⚖️ Informasi Bobot Kriteria</h3>
        <table class="criteria-table">
            <thead>
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
                    <td><strong>{{ $criteria->name }}</strong></td>
                    <td class="score-cell"><strong>{{ $criteria->weight }}%</strong></td>
                    <td class="score-cell">
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
                    <td style="font-size: 11px;">
                        {{ $criteria->type === 'benefit' ? 'Semakin tinggi semakin baik' : 'Semakin rendah semakin baik' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            <strong>{{ config('app.name', 'SAW Employee Evaluation System') }}</strong><br>
            Performance Report - {{ $employee->name }} ({{ $employee->employee_code }})<br>
            Periode: {{ $period }} | Generated: {{ date('d F Y H:i:s') }}<br>
            <strong>CONFIDENTIAL</strong> - Dokumen ini hanya untuk penggunaan internal
        </p>
    </div>
</body>
</html>


