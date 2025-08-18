@extends('layouts.main')

@section('title', __('User Details') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('User Details'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('User Profile') }}</h1>
        <p class="text-gray-600">{{ __('Complete user information and activity') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('users.edit', $user->id) }}" variant="warning" icon="fas fa-edit">{{ __('Edit User') }}</x-ui.button>
        <x-ui.button href="{{ route('users.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">{{ __('Back to List') }}</x-ui.button>
    </div>
</div>

<!-- User Profile Card -->
<div class="card mb-6 bg-gradient-to-br from-primary-500 to-primary-600 text-white overflow-hidden">
    <div class="card-body py-8">
        <div class="flex flex-col md:flex-row items-center gap-6">
            <div class="relative">
                <div class="w-24 h-24 bg-white/25 rounded-full flex items-center justify-center text-3xl font-bold text-white">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                @if($user->status === 'active')
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-success-500 border-2 border-white rounded-full"></span>
                @else
                    <span class="absolute -bottom-1 -right-1 w-6 h-6 bg-gray-400 border-2 border-white rounded-full"></span>
                @endif
            </div>
            <div class="text-center md:text-left flex-1">
                <h1 class="text-3xl font-bold mb-2">{{ $user->name }}</h1>
                <p class="text-xl mb-2 text-white/75">{{ $user->email }}</p>
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-4 text-white/90">
                    <div class="flex items-center gap-2">
                        <i class="fas fa-{{ $user->role === 'admin' ? 'shield-alt' : 'user' }}"></i>
                        <span>{{ $user->role === 'admin' ? __('Administrator') : __('User') }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar"></i>
                        <span>{{ __('Joined') }} {{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- User Information -->
    <div class="lg:col-span-2">
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-user text-primary-500"></i>{{ __('User Information') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Full Name') }}</label>
                            <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Email Address') }}</label>
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Role') }}</label>
                            @if($user->role === 'admin')
                                <span class="badge badge-danger"><i class="fas fa-shield-alt mr-1"></i>{{ __('Administrator') }}</span>
                            @else
                                <span class="badge badge-primary"><i class="fas fa-user mr-1"></i>{{ __('User') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Status') }}</label>
                            @if($user->status === 'active')
                                <span class="badge badge-success">{{ __('Active') }}</span>
                            @else
                                <span class="badge badge-danger">{{ __('Inactive') }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Email Verified') }}</label>
                            @if($user->email_verified_at)
                                <span class="badge badge-success">{{ __('Verified') }}</span>
                            @else
                                <span class="badge badge-warning">{{ __('Not Verified') }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">{{ __('Last Login') }}</label>
                            <p class="text-gray-900">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : __('Never') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-bolt text-warning-500"></i>{{ __('Quick Actions') }}
                </h6>
            </div>
            <div class="card-body space-y-3">
                <x-ui.button href="{{ route('users.edit', $user->id) }}" variant="outline-warning" size="sm" icon="fas fa-edit" class="w-full justify-start">
                    {{ __('Edit Information') }}
                </x-ui.button>
                @if(!$user->email_verified_at)
                    <x-ui.button onclick="sendEmailVerification({{ $user->id }})" variant="outline-info" size="sm" icon="fas fa-envelope" class="w-full justify-start">
                        {{ __('Send Verification Email') }}
                    </x-ui.button>
                @endif
                <x-ui.button onclick="sendPasswordReset({{ $user->id }})" variant="outline-primary" size="sm" icon="fas fa-key" class="w-full justify-start">
                    {{ __('Send Password Reset') }}
                </x-ui.button>
                <div class="pt-2 border-t border-gray-200">
                    <x-ui.button onclick="confirmDelete()" variant="outline-danger" size="sm" icon="fas fa-trash" class="w-full justify-start">
                        {{ __('Delete User') }}
                    </x-ui.button>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="flex items-center gap-2 font-semibold text-gray-900">
                    <i class="fas fa-chart-pie text-info-500"></i>{{ __('Statistics') }}
                </h6>
            </div>
            <div class="card-body space-y-4 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Account Created') }}</span>
                    <span class="text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('Last Updated') }}</span>
                    <span class="text-gray-900">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">{{ __('User ID') }}</span>
                    <span class="text-gray-900 font-mono">#{{ $user->id }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function confirmDelete() {
    Swal.fire({
        title: '{{ __("Delete User") }}',
        text: '{{ __("Are you sure you want to delete this user? This action cannot be undone.") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '{{ __("Yes, Delete") }}',
        cancelButtonText: '{{ __("Cancel") }}',
        confirmButtonColor: '#dc2626'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("users.destroy", $user->id) }}';
            form.innerHTML = `
                @csrf
                @method('DELETE')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function sendEmailVerification(userId) {
    fetch(`/users/${userId}/send-email-verification`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? '{{ __("Success") }}' : '{{ __("Error") }}',
            text: data.message
        });
    });
}

function sendPasswordReset(userId) {
    fetch(`/users/${userId}/send-password-reset`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        Swal.fire({
            icon: data.success ? 'success' : 'error',
            title: data.success ? '{{ __("Success") }}' : '{{ __("Error") }}',
            text: data.message
        });
    });
}
</script>
@endpush