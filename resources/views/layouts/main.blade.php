<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ session('theme', 'light') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous">

    <!-- DataTables CSS (Tailwind compatible) -->
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body class="bg-background-primary text-text-primary">
    <div class="app-layout" x-data="{ sidebarOpen: true }" @resize.window="sidebarOpen = window.innerWidth >= 1024">
        @auth
            <!-- Sidebar Navigation -->
            <aside class="sidebar" :class="{ 'sidebar-hidden': !sidebarOpen }">
                <div class="px-6 py-6 border-b border-gray-200">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 text-text-primary hover:text-primary-600 transition-colors no-underline">
                        <div class="w-8 h-8 bg-primary-600 text-white rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-sm"></i>
                        </div>
                        <span class="font-bold text-lg">SAW System</span>
                    </a>
                </div>

                <nav class="px-3 py-6 space-y-6">
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
                        <div class="nav-section-title">{{ __('Evaluation System') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('evaluations.index') }}" class="nav-link {{ request()->routeIs('evaluations.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                {{ __('Evaluations') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('results.index') }}" class="nav-link {{ request()->routeIs('results.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                {{ __('Results & Ranking') }}
                            </a>
                        </div>
                    </div>

                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Advanced Analysis') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('analysis.index') }}" class="nav-link {{ request()->routeIs('analysis.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                                {{ __('Analytics Dashboard') }}
                            </a>
                        </div>
                    </div>

                    @can('admin')
                    <div class="nav-section">
                        <div class="nav-section-title">{{ __('Administration') }}</div>
                        <div class="nav-item">
                            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-user-cog"></i>
                                </div>
                                {{ __('User Management') }}
                            </a>
                        </div>
                        <div class="nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                                <div class="nav-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                {{ __('System Admin') }}
                            </a>
                        </div>
                    </div>
                    @endcan
                </nav>
            </aside>

            <!-- Main Content Area -->
            <div class="main-content" :class="{ 'sidebar-hidden': !sidebarOpen }">
                <!-- Top Header -->
                <header class="top-header">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <!-- Mobile menu button -->
                            <button 
                                @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-bars text-lg"></i>
                            </button>
                            
                            <!-- Page title -->
                            <div>
                                <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
                                <p class="page-subtitle">@yield('page-subtitle', '')</p>
                            </div>
                        </div>

                        <!-- Header actions -->
                        <div class="flex items-center gap-3">
                            <!-- Language switcher -->
                            <div class="dropdown" x-data="{ open: false }">
                                <button @click="open = !open" class="dropdown-toggle">
                                    <i class="fas fa-globe"></i>
                                    {{ strtoupper(app()->getLocale()) }}
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                                    <a href="{{ route('language.switch', 'en') }}" class="dropdown-item">
                                        <i class="fas fa-flag-usa"></i> English
                                    </a>
                                    <a href="{{ route('language.switch', 'id') }}" class="dropdown-item">
                                        <i class="fas fa-flag"></i> Indonesia
                                    </a>
                                </div>
                            </div>

                            <!-- User menu -->
                            <div class="dropdown" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-sm"></i>
                                    </div>
                                    <div class="text-left">
                                        <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                        <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </button>
                                <div x-show="open" @click.away="open = false" x-transition class="dropdown-menu">
                                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                        <i class="fas fa-user-edit"></i> {{ __('Profile') }}
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item w-full text-left">
                                            <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Main Content -->
                <main class="content-container">
                    <!-- Flash Messages -->
                    @if(session('success'))
                        <div class="mb-6 animate-fade-in" x-data="{ show: true }" x-show="show">
                            <div class="alert alert-success flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-check-circle text-success-600"></i>
                                    <span>{{ session('success') }}</span>
                                </div>
                                <button @click="show = false" class="text-success-600 hover:text-success-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-6 animate-fade-in" x-data="{ show: true }" x-show="show">
                            <div class="alert alert-danger flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-circle text-danger-600"></i>
                                    <span>{{ session('error') }}</span>
                                </div>
                                <button @click="show = false" class="text-danger-600 hover:text-danger-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="mb-6 animate-fade-in" x-data="{ show: true }" x-show="show">
                            <div class="alert alert-warning flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-triangle text-warning-600"></i>
                                    <span>{{ session('warning') }}</span>
                                </div>
                                <button @click="show = false" class="text-warning-600 hover:text-warning-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    @endif

                    @if(session('import_errors'))
                        <div class="mb-6 animate-fade-in" x-data="{ show: true }" x-show="show">
                            <div class="alert alert-danger">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-exclamation-circle text-danger-600"></i>
                                        <span class="font-semibold">{{ __('Import Errors') }}</span>
                                    </div>
                                    <button @click="show = false" class="text-danger-600 hover:text-danger-700">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="max-h-72 overflow-y-auto space-y-2">
                                    @foreach(session('import_errors') as $error)
                                        <div class="bg-danger-25 rounded-md p-3 border border-danger-200">
                                            <div class="font-semibold text-danger-800">
                                                {{ __('Row') }} {{ $error['row'] }}:
                                            </div>
                                            <div class="text-danger-700 mt-1">
                                                {{ $error['error'] }}
                                            </div>
                                            @if(isset($error['data']) && is_array($error['data']))
                                                <div class="text-xs text-danger-600 mt-2 font-mono bg-danger-50 p-2 rounded border">
                                                    {{ __('Data') }}: {{ json_encode($error['data']) }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="mb-6 animate-fade-in" x-data="{ show: true }" x-show="show">
                            <div class="alert alert-info flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-info-circle text-info-600"></i>
                                    <span>{{ session('info') }}</span>
                                </div>
                                <button @click="show = false" class="text-info-600 hover:text-info-700">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Global CSRF Token Setup for AJAX -->
    <script>
        // Setup CSRF token for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Global Alpine.js data
        document.addEventListener('alpine:init', () => {
            Alpine.data('app', () => ({
                sidebarOpen: window.innerWidth >= 1024,
                
                init() {
                    // Initialize tooltips and other components
                    this.$nextTick(() => {
                        this.initializeTooltips();
                    });
                },
                
                initializeTooltips() {
                    // Initialize any tooltip libraries if needed
                },
                
                closeSidebar() {
                    if (window.innerWidth < 1024) {
                        this.sidebarOpen = false;
                    }
                }
            }));
        });
    </script>

    @stack('scripts')
</body>
</html>