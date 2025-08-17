<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    <!-- Modern Minimalist Theme CSS -->
    <style>
        :root {
            /* Light Theme - Ultra Clean */
            --bg-primary: #fafbfc;
            --bg-secondary: #ffffff;
            --bg-elevated: #ffffff;
            --sidebar-bg: rgba(255, 255, 255, 0.85);
            --header-bg: rgba(255, 255, 255, 0.9);
            --text-primary: #1a1d21;
            --text-secondary: #656d76;
            --text-tertiary: #8b949e;
            --accent-primary: #0366d6;
            --accent-hover: #0256c7;
            --accent-light: rgba(3, 102, 214, 0.08);
            --border-light: rgba(27, 31, 36, 0.08);
            --border-medium: rgba(27, 31, 36, 0.12);
            --shadow-small: 0 1px 3px rgba(27, 31, 36, 0.04), 0 8px 20px rgba(27, 31, 36, 0.06);
            --shadow-medium: 0 3px 12px rgba(27, 31, 36, 0.08), 0 16px 32px rgba(27, 31, 36, 0.10);
            --shadow-large: 0 8px 32px rgba(27, 31, 36, 0.12), 0 24px 60px rgba(27, 31, 36, 0.15);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            font-weight: 400;
            margin: 0;
            padding: 0;
            font-size: 14px;
            letter-spacing: -0.01em;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Layout Structure */
        .app-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Modern Minimalist Sidebar */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-right: 1px solid var(--border-light);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-light);
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 700;
            font-size: 18px;
            letter-spacing: -0.025em;
            transition: all 0.3s ease;
        }

        .sidebar-brand:hover {
            color: var(--accent-primary);
            text-decoration: none;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-hover));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: 600;
        }

        .sidebar-nav {
            padding: 16px 0;
        }

        .nav-section {
            margin-bottom: 32px;
        }

        .nav-section-title {
            padding: 0 24px 8px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-tertiary);
        }

        .nav-item {
            margin: 2px 16px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            text-decoration: none;
            color: var(--text-secondary);
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .nav-link:hover {
            background: var(--accent-light);
            color: var(--accent-primary);
            text-decoration: none;
        }

        .nav-link.active {
            background: var(--accent-light);
            color: var(--accent-primary);
            font-weight: 600;
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Header */
        .top-header {
            background: var(--header-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid var(--border-light);
            padding: 16px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .header-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.025em;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--accent-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .user-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(3, 102, 214, 0.3);
        }

        /* Content Container */
        .content-container {
            flex: 1;
            padding: 32px;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Custom Cards */
        .card {
            background: var(--bg-elevated);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-small);
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-medium);
        }

        .card-header {
            padding: 24px 24px 0;
            border-bottom: none;
            background: transparent;
        }

        .card-body {
            padding: 24px;
        }

        /* Modern Buttons */
        .btn {
            font-weight: 500;
            border-radius: var(--radius-md);
            padding: 10px 20px;
            font-size: 14px;
            letter-spacing: -0.01em;
            transition: all 0.2s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--accent-primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(3, 102, 214, 0.3);
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            font-size: 14px;
            background: var(--bg-elevated);
            color: var(--text-primary);
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 0.2rem rgba(3, 102, 214, 0.15);
            background: var(--bg-elevated);
            color: var(--text-primary);
        }

        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 8px;
            font-size: 14px;
        }

        /* Alert Components */
        .alert {
            border: none;
            border-radius: var(--radius-md);
            padding: 16px 20px;
            margin-bottom: 24px;
            border-left: 4px solid;
            background: var(--bg-elevated);
        }

        .alert-success {
            border-left-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
            color: #065f46;
        }

        .alert-warning {
            border-left-color: #f59e0b;
            background: rgba(245, 158, 11, 0.05);
            color: #92400e;
        }

        .alert-danger {
            border-left-color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
            color: #991b1b;
        }

        .alert-info {
            border-left-color: var(--accent-primary);
            background: var(--accent-light);
            color: #1e40af;
        }

        /* Stats Cards */
        .stats-card {
            background: var(--bg-elevated);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: flex;
            align-items: center;
            justify-content: between;
            box-shadow: var(--shadow-small);
            transition: all 0.3s ease;
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .stats-content {
            flex: 1;
        }

        .stats-number {
            font-size: 28px;
            font-weight: 800;
            color: white;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stats-label {
            font-size: 14px;
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: rgba(255,255,255,0.8);
            background: rgba(255,255,255,0.1);
            border-radius: var(--radius-md);
        }

        /* Modern Tables */
        .table {
            --bs-table-bg: var(--bg-elevated);
            --bs-table-border-color: var(--border-light);
            color: var(--text-primary);
        }

        .table th {
            background: var(--bg-secondary);
            border-bottom: 2px solid var(--border-light);
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-secondary);
            padding: 16px;
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: var(--accent-light);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .content-container {
                padding: 16px;
            }

            .top-header {
                padding: 12px 16px;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="app-layout">
        @auth
            <!-- Sidebar Navigation -->
            <aside class="sidebar">
                <div class="sidebar-header">
                    <a href="{{ route('dashboard') }}" class="sidebar-brand">
                        <div class="sidebar-brand-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span>SAW System</span>
                    </a>
                </div>

                <nav class="sidebar-nav">
                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Main') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-tachometer-alt"></i>
                                </div>
                                {{ __('Dashboard') }}
                            </a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Data Management') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                {{ __('Employees') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('criterias.index') }}" class="nav-link {{ request()->routeIs('criterias.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-sliders"></i>
                                </div>
                                {{ __('Criteria') }}
                            </a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Evaluation') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('evaluations.index') }}" class="nav-link {{ request()->routeIs('evaluations.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-clipboard-check"></i>
                                </div>
                                {{ __('Evaluations') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('results.index') }}" class="nav-link {{ request()->routeIs('results.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                {{ __('Results') }}
                            </a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Advanced Analytics') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('analysis.index') }}" class="nav-link {{ request()->routeIs('analysis.index') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                {{ __('Analysis Dashboard') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('analysis.sensitivity.view') }}" class="nav-link {{ request()->routeIs('analysis.sensitivity*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-balance-scale"></i>
                                </div>
                                {{ __('Sensitivity Analysis') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('analysis.what-if.view') }}" class="nav-link {{ request()->routeIs('analysis.what-if*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-question-circle"></i>
                                </div>
                                {{ __('What-if Scenarios') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('analysis.comparison.view') }}" class="nav-link {{ request()->routeIs('analysis.comparison*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                {{ __('Multi-period Comparison') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('analysis.forecast.view') }}" class="nav-link {{ request()->routeIs('analysis.forecast*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-chart-area"></i>
                                </div>
                                {{ __('Performance Forecasting') }}
                            </a>
                        </div>
                    </div>

                    @if(auth()->user()->canManage())
                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('User Management') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                {{ __('Users') }}
                            </a>
                        </div>
                    </div>
                    @endif

                    @if(auth()->user()->isAdmin())
                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Administration') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                {{ __('Admin Dashboard') }}
                            </a>
                        </div>
                    </div>
                    @endif
                </nav>
            </aside>

            <!-- Main Content -->
            <div class="main-content">
                <!-- Top Header -->
                <header class="top-header">
                    <h1 class="header-title">@yield('page-title', __('Dashboard'))</h1>

                    <div class="header-actions">
                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="user-avatar dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <div class="dropdown-header">
                                        <strong>{{ auth()->user()->name }}</strong><br>
                                        <small class="text-muted">{{ auth()->user()->role_display }}</small>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user me-2"></i>
                                        {{ __('Profile') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>
                                            {{ __('Logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="content-container">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('import_errors'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-circle me-2"></i>Import Errors:</h6>
                            <div style="max-height: 300px; overflow-y: auto;">
                                @foreach(session('import_errors') as $error)
                                    <div class="mb-2">
                                        <strong>Row {{ $error['row'] }}:</strong> {{ $error['error'] }}
                                        @if(isset($error['data']) && is_array($error['data']))
                                            <br><small class="text-muted">Data: {{ json_encode($error['data']) }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        @else
            <!-- Guest Content for non-authenticated users -->
            @yield('content')
        @endauth
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>


    @stack('scripts')
</body>
</html>
