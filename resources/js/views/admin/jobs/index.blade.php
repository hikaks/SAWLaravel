@extends('layouts.main')

@section('title', 'Job Monitor - Admin Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">🚀 Job Monitor Dashboard</h1>
        <p class="text-gray-600">Monitor and manage background jobs and queue status</p>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending Jobs</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Running Jobs</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_running'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Failed Jobs</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_failed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Success Rate</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $stats['success_rate'] }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Queue Status -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">📊 Queue Status</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach(['high', 'default', 'low'] as $queue)
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900 mb-2">{{ ucfirst($queue) }}</div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Pending:</span>
                            <span class="font-medium">{{ $queueStatus[$queue]['pending'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Running:</span>
                            <span class="font-medium">{{ $queueStatus[$queue]['running'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Failed:</span>
                            <span class="font-medium text-red-600">{{ $queueStatus[$queue]['failed'] }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">⚡ Quick Actions</h2>
        </div>
        <div class="p-6">
            <div class="flex flex-wrap gap-4">
                <form action="{{ route('admin.jobs.clear-completed') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        🧹 Clear Completed
                    </button>
                </form>

                <form action="{{ route('admin.jobs.clear-all') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to clear ALL jobs? This action cannot be undone.')">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        🗑️ Clear All Jobs
                    </button>
                </form>

                <a href="{{ route('admin.jobs.index') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    🔄 Refresh
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Jobs -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">⏳ Recent Jobs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentJobs as $job)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $job['id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="text-sm">{{ $job['type'] ?? $job['job_class'] }}</span>
                                @if(isset($job['period']))
                                    <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $job['period'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($job['queue'] === 'high') bg-red-100 text-red-800
                                @elseif($job['queue'] === 'default') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $job['queue'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($job['status'] === 'running') bg-green-100 text-green-800
                                @elseif($job['status'] === 'failed') bg-red-100 text-red-800
                                @else bg-yellow-100 text-yellow-800
                                @endif">
                                {{ ucfirst($job['status']) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $job['created_at'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('admin.jobs.show', $job['id']) }}" class="text-blue-600 hover:text-blue-900">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">No jobs found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Failed Jobs -->
    @if($failedJobs->count() > 0)
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">❌ Failed Jobs</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Failed At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($failedJobs as $job)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $job['id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center">
                                <span class="text-sm">{{ $job['type'] ?? $job['job_class'] }}</span>
                                @if(isset($job['period']))
                                    <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ $job['period'] }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($job['queue'] === 'high') bg-red-100 text-red-800
                                @elseif($job['queue'] === 'default') bg-blue-100 text-blue-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ $job['queue'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $job['failed_at'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <form action="{{ route('admin.jobs.retry', $job['id']) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-900">Retry</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Job Type Distribution -->
    @if(!empty($stats['job_types']))
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">📊 Job Type Distribution</h2>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($stats['job_types'] as $type => $count)
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-600">{{ class_basename($type) }}</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $count }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
