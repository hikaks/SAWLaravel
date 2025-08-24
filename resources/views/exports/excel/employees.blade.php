<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Daftar Karyawan - {{ config('app.name') }}</title>
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

        .export-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            color: #1976d2;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid #2196f3;
        }

        .export-info h2 {
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

        .employees-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .employees-table th,
        .employees-table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 11px;
        }

        .employees-table th {
            background-color: #198754;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        .employees-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .employee-code {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            background-color: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .department-badge {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .position-badge {
            background-color: #d4edda;
            color: #155724;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .stats-summary {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }

        .stats-summary h3 {
            margin: 0 0 10px 0;
            color: #856404;
            text-align: center;
        }

        .stats-grid {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
            margin: 10px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #198754;
        }

        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <div class="subtitle">Daftar Karyawan Lengkap</div>
        <div class="subtitle">{{ date('d F Y') }}</div>
    </div>

    <div class="export-info">
        <h2>📋 Data Karyawan</h2>
        <p style="margin: 5px 0 0 0;">Total: {{ $employees->count() }} Karyawan</p>
        <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.9;">Filter: {{ $filters }}</p>
    </div>

    <div class="meta-info">
        <table>
            <tr>
                <td><strong>Total Karyawan:</strong></td>
                <td>{{ $employees->count() }} orang</td>
                <td><strong>Total Departemen:</strong></td>
                <td>{{ $employees->pluck('department')->unique()->count() }} departemen</td>
            </tr>
            <tr>
                <td><strong>Total Posisi:</strong></td>
                <td>{{ $employees->pluck('position')->unique()->count() }} posisi</td>
                <td><strong>Tanggal Export:</strong></td>
                <td>{{ date('d F Y H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Filter Aplikasi:</strong></td>
                <td colspan="3">{{ $filters }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistics Summary -->
    <div class="stats-summary">
        <h3>📊 Ringkasan Statistik</h3>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number">{{ $employees->count() }}</div>
                <div class="stat-label">Total Karyawan</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $employees->pluck('department')->unique()->count() }}</div>
                <div class="stat-label">Departemen</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $employees->pluck('position')->unique()->count() }}</div>
                <div class="stat-label">Posisi</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">{{ $employees->whereNotNull('email')->count() }}</div>
                <div class="stat-label">Dengan Email</div>
            </div>
        </div>
    </div>

    <!-- Department Distribution -->
    <div class="meta-info" style="background-color: #e8f5e8; border: 1px solid #c3e6cb;">
        <h3 style="margin: 0 0 10px 0; color: #155724; text-align: center;">🏢 Distribusi Departemen</h3>
        <table style="margin-bottom: 0;">
            @php
                $departmentStats = $employees->groupBy('department')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'percentage' => round(($group->count() / $employees->count()) * 100, 1)
                    ];
                })->sortByDesc('count');
            @endphp
            @foreach($departmentStats as $dept => $stats)
            <tr>
                <td><strong>{{ $dept ?: 'Tidak Diketahui' }}:</strong></td>
                <td>{{ $stats['count'] }} karyawan ({{ $stats['percentage'] }}%)</td>
                @if($loop->iteration % 2 == 0)
                    <td></td><td></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Position Distribution -->
    <div class="meta-info" style="background-color: #fff3cd; border: 1px solid #ffeaa7; margin-top: 15px;">
        <h3 style="margin: 0 0 10px 0; color: #856404; text-align: center;">👔 Distribusi Posisi</h3>
        <table style="margin-bottom: 0;">
            @php
                $positionStats = $employees->groupBy('position')->map(function($group) {
                    return [
                        'count' => $group->count(),
                        'percentage' => round(($group->count() / $employees->count()) * 100, 1)
                    ];
                })->sortByDesc('count');
            @endphp
            @foreach($positionStats as $pos => $stats)
            <tr>
                <td><strong>{{ $pos ?: 'Tidak Diketahui' }}:</strong></td>
                <td>{{ $stats['count'] }} karyawan ({{ $stats['percentage'] }}%)</td>
                @if($loop->iteration % 2 == 0)
                    <td></td><td></td>
                @endif
            </tr>
            @endforeach
        </table>
    </div>

    <!-- Employees Table -->
    <table class="employees-table">
        <thead>
            <tr>
                <th style="width: 5%">No</th>
                <th style="width: 12%">Kode</th>
                <th style="width: 25%">Nama Karyawan</th>
                <th style="width: 15%">Posisi</th>
                <th style="width: 15%">Departemen</th>
                <th style="width: 20%">Email</th>
                <th style="width: 8%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $index => $employee)
            <tr>
                <td style="text-align: center">{{ $index + 1 }}</td>
                <td>
                    <span class="employee-code">{{ $employee->employee_code }}</span>
                </td>
                <td style="font-weight: bold">{{ $employee->name }}</td>
                <td>
                    <span class="position-badge">{{ $employee->position ?: '-' }}</span>
                </td>
                <td>
                    <span class="department-badge">{{ $employee->department ?: '-' }}</span>
                </td>
                <td style="font-size: 10px;">
                    {{ $employee->email ?: 'Tidak ada email' }}
                </td>
                <td style="text-align: center">
                    @if($employee->email)
                        <span style="color: #28a745; font-weight: bold;">✓</span>
                    @else
                        <span style="color: #dc3545; font-weight: bold;">✗</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Additional Information -->
    <div class="meta-info" style="background-color: #f8f9fa; margin-top: 20px;">
        <h4 style="margin: 0 0 10px 0; color: #495057;">📋 Informasi Tambahan</h4>
        <ul style="margin: 0; padding-left: 20px; font-size: 11px;">
            <li>Data karyawan diurutkan berdasarkan kode karyawan</li>
            <li>Status email menunjukkan apakah karyawan memiliki alamat email</li>
            <li>Filter yang diterapkan: {{ $filters }}</li>
            <li>Total data yang diexport: {{ $employees->count() }} karyawan</li>
        </ul>
    </div>

    <div class="footer">
        <p>
            <strong>{{ config('app.name') }}</strong><br>
            Sistem Manajemen Karyawan<br>
            Digenerate pada: {{ date('d F Y H:i:s') }} | Filter: {{ $filters }}<br>
            <strong>CONFIDENTIAL</strong> - Data ini hanya untuk penggunaan internal
        </p>
    </div>
</body>
</html>
