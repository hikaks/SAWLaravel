@extends('layouts.main')

@section('title', 'User Profile - ' . $user->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-user text-primary me-2"></i>User Profile
                    </h1>
                    <p class="text-muted mt-1">View detailed user information and activity</p>
                </div>
                <div class="btn-group">
                    @if(auth()->user()->isAdmin() || auth()->user()->id === $user->id)
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Edit Profile
                        </a>
                    @endif
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column - Profile Information -->
        <div class="col-md-4">
            <!-- Profile Card -->
            <div class="card profile-card shadow mb-4">
                <div class="card-body text-center">
                    <div class="profile-avatar mb-3">
                        <div class="avatar-circle mx-auto">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    </div>
                    <h4 class="card-title mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-3">{{ $user->email }}</p>

                    <!-- Status Badges -->
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }} px-3 py-2">
                            <i class="fas fa-user-tag me-1"></i>{{ ucfirst($user->role) }}
                        </span>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'secondary' }} px-3 py-2">
                            <i class="fas fa-{{ $user->is_active ? 'check-circle' : 'times-circle' }} me-1"></i>
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    @if($user->email_verified_at)
                        <div class="alert alert-success py-2">
                            <i class="fas fa-shield-check me-2"></i>
                            <strong>Email Verified</strong>
                            <small class="d-block">{{ $user->email_verified_at->format('M d, Y') }}</small>
                        </div>
                    @else
                        <div class="alert alert-warning py-2">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Email Not Verified</strong>
                            @if(auth()->user()->isAdmin())
                                <button class="btn btn-sm btn-outline-warning ms-2" onclick="sendVerificationEmail({{ $user->id }})">
                                    Send Verification
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- Quick Actions -->
                    @if(auth()->user()->isAdmin() && auth()->user()->id !== $user->id)
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-{{ $user->is_active ? 'warning' : 'success' }} btn-sm"
                                    onclick="toggleUserStatus({{ $user->id }}, '{{ $user->name }}', {{ $user->is_active ? 'false' : 'true' }})">
                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }} me-1"></i>
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                            </button>
                            <button class="btn btn-outline-primary btn-sm" onclick="sendPasswordReset({{ $user->id }})">
                                <i class="fas fa-key me-1"></i>Send Password Reset
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Information -->
            <div class="card info-card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Information</h6>
                </div>
                <div class="card-body">
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value">
                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                            {{ $user->created_at->format('F d, Y') }}
                            <small class="text-muted d-block">{{ $user->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Last Activity</div>
                        <div class="info-value">
                            <i class="fas fa-clock text-muted me-2"></i>
                            {{ $stats['last_login'] ? $stats['last_login']->diffForHumans() : 'Never' }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Account Age</div>
                        <div class="info-value">
                            <i class="fas fa-hourglass-half text-muted me-2"></i>
                            {{ $stats['account_age'] }} days
                        </div>
                    </div>
                    @if(auth()->user()->isAdmin())
                    <div class="info-item">
                        <div class="info-label">User ID</div>
                        <div class="info-value">
                            <i class="fas fa-hashtag text-muted me-2"></i>
                            #{{ $user->id }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column - Activity & Statistics -->
        <div class="col-md-8">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="stats-content">
                            <div class="stats-number">{{ $stats['total_logins'] }}</div>
                            <div class="stats-label">Total Logins</div>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="stats-content">
                            <div class="stats-number">{{ $stats['evaluations_created'] }}</div>
                            <div class="stats-label">Evaluations Created</div>
                        </div>
                        <div class="stats-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Permissions -->
            <div class="card permissions-card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>Role Permissions
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info') }} ms-2">
                            {{ ucfirst($user->role) }}
                        </span>
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $permissions = [];
                        switch($user->role) {
                            case 'admin':
                                $permissions = [
                                    ['icon' => 'users', 'text' => 'Full user management (create, edit, delete users)', 'level' => 'admin'],
                                    ['icon' => 'cogs', 'text' => 'System configuration and settings', 'level' => 'admin'],
                                    ['icon' => 'shield-alt', 'text' => 'Access to all administrative features', 'level' => 'admin'],
                                    ['icon' => 'database', 'text' => 'Backup and maintenance operations', 'level' => 'admin'],
                                    ['icon' => 'chart-line', 'text' => 'Advanced analytics and reporting', 'level' => 'manager'],
                                    ['icon' => 'clipboard-list', 'text' => 'Manage evaluations and criteria', 'level' => 'manager'],
                                    ['icon' => 'eye', 'text' => 'View and create evaluations', 'level' => 'user'],
                                    ['icon' => 'user-edit', 'text' => 'Update profile information', 'level' => 'user'],
                                ];
                                break;
                            case 'manager':
                                $permissions = [
                                    ['icon' => 'chart-line', 'text' => 'Generate and export detailed reports', 'level' => 'manager'],
                                    ['icon' => 'clipboard-list', 'text' => 'Manage employee data and criteria', 'level' => 'manager'],
                                    ['icon' => 'calculator', 'text' => 'Manage evaluation periods and calculations', 'level' => 'manager'],
                                    ['icon' => 'chart-bar', 'text' => 'View advanced analytics and charts', 'level' => 'manager'],
                                    ['icon' => 'eye', 'text' => 'View and create evaluations', 'level' => 'user'],
                                    ['icon' => 'tachometer-alt', 'text' => 'Access basic dashboard features', 'level' => 'user'],
                                    ['icon' => 'user-edit', 'text' => 'Update profile information', 'level' => 'user'],
                                ];
                                break;
                            case 'user':
                                $permissions = [
                                    ['icon' => 'eye', 'text' => 'View and create employee evaluations', 'level' => 'user'],
                                    ['icon' => 'file-alt', 'text' => 'View evaluation results and reports', 'level' => 'user'],
                                    ['icon' => 'tachometer-alt', 'text' => 'Access basic dashboard features', 'level' => 'user'],
                                    ['icon' => 'user-edit', 'text' => 'Update own profile information', 'level' => 'user'],
                                ];
                                break;
                        }
                    @endphp

                    <div class="permissions-list">
                        @foreach($permissions as $permission)
                            <div class="permission-item">
                                <div class="permission-icon">
                                    <i class="fas fa-{{ $permission['icon'] }} text-{{ $permission['level'] === 'admin' ? 'danger' : ($permission['level'] === 'manager' ? 'warning' : 'info') }}"></i>
                                </div>
                                <div class="permission-text">
                                    {{ $permission['text'] }}
                                    <span class="badge bg-{{ $permission['level'] === 'admin' ? 'danger' : ($permission['level'] === 'manager' ? 'warning' : 'info') }} ms-2">
                                        {{ ucfirst($permission['level']) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Activity (Placeholder) -->
            <div class="card activity-card shadow">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h6>
                </div>
                <div class="card-body">
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <h6 class="text-muted">Activity Tracking Coming Soon</h6>
                        <p class="text-muted mb-0">User activity logging will be available in a future update.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Profile Card Styling */
.profile-card {
    border: none;
    border-radius: 1rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.profile-card .card-body {
    padding: 2rem;
}

.avatar-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

/* Info Card Styling */
.info-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.info-card .card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: start;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #495057;
    min-width: 120px;
}

.info-value {
    flex: 1;
    text-align: right;
    color: #6c757d;
}

/* Stats Cards */
.stats-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    color: white;
    border: none;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 1rem;
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stats-content {
    position: relative;
    z-index: 2;
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.stats-label {
    font-size: 0.875rem;
    font-weight: 500;
    opacity: 0.9;
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2.5rem;
    opacity: 0.3;
    z-index: 1;
}

/* Permissions Card */
.permissions-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.permissions-card .card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
}

.permission-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.permission-item:last-child {
    border-bottom: none;
}

.permission-icon {
    width: 40px;
    text-align: center;
    font-size: 1.1rem;
}

.permission-text {
    flex: 1;
    margin-left: 0.75rem;
    color: #495057;
}

/* Activity Card */
.activity-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.activity-card .card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
}

/* Button Enhancements */
.btn {
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Badge Enhancements */
.badge {
    font-weight: 600;
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
}

/* Alert Enhancements */
.alert {
    border-radius: 0.75rem;
    border: none;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }

    .profile-card .card-body {
        padding: 1.5rem;
    }

    .avatar-circle {
        width: 60px;
        height: 60px;
        font-size: 1.5rem;
    }

    .info-item {
        flex-direction: column;
        align-items: start;
    }

    .info-value {
        text-align: left;
        margin-top: 0.25rem;
    }
}

/* Animation */
.card {
    animation: fadeInUp 0.5s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
@endpush

@push('scripts')
<script>
// Toggle user status
function toggleUserStatus(userId, userName, newStatus) {
    const actionText = newStatus ? 'activate' : 'deactivate';
    const statusText = newStatus ? 'Active' : 'Inactive';

    Swal.fire({
        title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)} User?`,
        html: `
            <div class="text-start">
                <p class="mb-3">Are you sure you want to <strong>${actionText}</strong> user <strong>${userName}</strong>?</p>
                <div class="alert alert-${newStatus ? 'success' : 'warning'}">
                    <i class="fas fa-${newStatus ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    The user will be marked as <strong>${statusText}</strong> and ${newStatus ? 'will be able to' : 'will not be able to'} login to the system.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `<i class="fas fa-${newStatus ? 'check' : 'ban'} me-1"></i>${actionText.charAt(0).toUpperCase() + actionText.slice(1)}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: newStatus ? '#198754' : '#ffc107',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: `${actionText.charAt(0).toUpperCase() + actionText.slice(1)}ing User...`,
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Send toggle request
            $.ajax({
                url: `/users/${userId}/toggle-status`,
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        // Reload page to update status
                        location.reload();
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Action Failed',
                        text: response.message || 'An error occurred while updating user status.'
                    });
                }
            });
        }
    });
}

// Send password reset
function sendPasswordReset(userId) {
    Swal.fire({
        title: 'Send Password Reset?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will send a password reset email to the user's registered email address.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    The user will receive an email with instructions to reset their password.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-envelope me-1"></i>Send Reset Email',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Sending Email...',
                html: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Send reset request
            $.ajax({
                url: `/users/${userId}/send-password-reset`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Sent!',
                        text: response.message,
                        timer: 4000,
                        timerProgressBar: true
                    });
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Swal.fire({
                        icon: 'error',
                        title: 'Send Failed',
                        text: response.message || 'An error occurred while sending the password reset email.'
                    });
                }
            });
        }
    });
}

// Send verification email
function sendVerificationEmail(userId) {
    Swal.fire({
        title: 'Send Verification Email?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will send an email verification link to the user's email address.</p>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    The user will receive an email with a link to verify their email address.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-envelope me-1"></i>Send Verification',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#0d6efd',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                icon: 'info',
                title: 'Feature Coming Soon',
                text: 'Email verification sending will be implemented in a future update.',
                timer: 3000,
                timerProgressBar: true
            });
        }
    });
}
</script>
@endpush


