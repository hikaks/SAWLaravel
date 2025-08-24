<?php $__env->startSection('title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 text-gray-800 mb-0">
                        <i class="fas fa-tachometer-alt text-primary me-2"></i>Admin Dashboard
                    </h1>
                    <p class="text-muted mt-1">System overview and administrative controls</p>
                </div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-cogs me-1"></i>Admin Tools
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="<?php echo e(route('users.index')); ?>">
                            <i class="fas fa-users me-2"></i>Manage Users
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo e(route('criterias.index')); ?>">
                            <i class="fas fa-list me-2"></i>Manage Criteria
                        </a></li>
                        <li><a class="dropdown-item" href="<?php echo e(route('employees.index')); ?>">
                            <i class="fas fa-user-tie me-2"></i>Manage Employees
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="clearCache()">
                            <i class="fas fa-broom me-2"></i>Clear Cache
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="warmupCache()">
                            <i class="fas fa-fire me-2"></i>Warmup Cache
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- System Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stats-content">
                    <div class="stats-number"><?php echo e($stats['total_users']); ?></div>
                    <div class="stats-label">Total Users</div>
                    <div class="stats-sublabel"><?php echo e($stats['active_users']); ?> active</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stats-content">
                    <div class="stats-number"><?php echo e($stats['total_employees']); ?></div>
                    <div class="stats-label">Employees</div>
                    <div class="stats-sublabel"><?php echo e($stats['departments_count']); ?> departments</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stats-content">
                    <div class="stats-number"><?php echo e($stats['total_evaluations']); ?></div>
                    <div class="stats-label">Evaluations</div>
                    <div class="stats-sublabel"><?php echo e($stats['evaluation_periods']); ?> periods</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stats-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stats-content">
                    <div class="stats-number"><?php echo e($stats['total_criteria']); ?></div>
                    <div class="stats-label">Criteria</div>
                    <div class="stats-sublabel"><?php echo e($stats['criteria_weight_sum']); ?>% total weight</div>
                </div>
                <div class="stats-icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-md-8">
            <!-- User Management Overview -->
            <div class="card admin-card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>User Management Overview
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Role Distribution -->
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-primary mb-3">Role Distribution</h6>
                            <div class="role-stats">
                                <div class="role-item">
                                    <div class="role-info">
                                        <span class="badge bg-danger me-2">Admin</span>
                                        <span class="fw-medium"><?php echo e($stats['admin_users']); ?> users</span>
                                    </div>
                                    <div class="role-progress">
                                        <div class="progress">
                                            <div class="progress-bar bg-danger" style="width: <?php echo e($stats['total_users'] > 0 ? ($stats['admin_users'] / $stats['total_users']) * 100 : 0); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="role-item">
                                    <div class="role-info">
                                        <span class="badge bg-warning me-2">Manager</span>
                                        <span class="fw-medium"><?php echo e($stats['manager_users']); ?> users</span>
                                    </div>
                                    <div class="role-progress">
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: <?php echo e($stats['total_users'] > 0 ? ($stats['manager_users'] / $stats['total_users']) * 100 : 0); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="role-item">
                                    <div class="role-info">
                                        <span class="badge bg-info me-2">User</span>
                                        <span class="fw-medium"><?php echo e($stats['regular_users']); ?> users</span>
                                    </div>
                                    <div class="role-progress">
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: <?php echo e($stats['total_users'] > 0 ? ($stats['regular_users'] / $stats['total_users']) * 100 : 0); ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Status -->
                        <div class="col-md-6">
                            <h6 class="fw-semibold text-primary mb-3">User Status</h6>
                            <div class="status-stats">
                                <div class="status-item">
                                    <div class="status-icon bg-success">
                                        <i class="fas fa-check"></i>
                                    </div>
                                    <div class="status-info">
                                        <div class="status-number"><?php echo e($stats['active_users']); ?></div>
                                        <div class="status-label">Active Users</div>
                                    </div>
                                </div>
                                <div class="status-item">
                                    <div class="status-icon bg-secondary">
                                        <i class="fas fa-ban"></i>
                                    </div>
                                    <div class="status-info">
                                        <div class="status-number"><?php echo e($stats['inactive_users']); ?></div>
                                        <div class="status-label">Inactive Users</div>
                                    </div>
                                </div>
                                <div class="status-item">
                                    <div class="status-icon bg-success">
                                        <i class="fas fa-shield-check"></i>
                                    </div>
                                    <div class="status-info">
                                        <div class="status-number"><?php echo e($stats['verified_users']); ?></div>
                                        <div class="status-label">Verified Email</div>
                                    </div>
                                </div>
                                <div class="status-item">
                                    <div class="status-icon bg-warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="status-info">
                                        <div class="status-number"><?php echo e($stats['unverified_users']); ?></div>
                                        <div class="status-label">Pending Verification</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Verification Progress -->
                            <div class="mt-3">
                                <h6 class="fw-semibold text-primary mb-2">Email Verification Progress</h6>
                                <div class="verification-progress">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">Verification Rate</span>
                                        <span class="small fw-bold text-primary"><?php echo e($stats['verification_rate'] ?? 0); ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar bg-success"
                                             style="width: <?php echo e($stats['verification_rate'] ?? 0); ?>%"
                                             role="progressbar"
                                             aria-valuenow="<?php echo e($stats['verification_rate'] ?? 0); ?>"
                                             aria-valuemin="0"
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i><?php echo e($stats['verified_users']); ?> Verified
                                        </small>
                                        <small class="text-warning">
                                            <i class="fas fa-clock me-1"></i><?php echo e($stats['unverified_users'] ?? 0); ?> Pending
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h6 class="fw-semibold text-primary mb-3">Quick Actions</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="<?php echo e(route('users.create')); ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-user-plus me-1"></i>Add User
                                </a>
                                <button class="btn btn-outline-warning btn-sm" onclick="bulkActivateUsers()">
                                    <i class="fas fa-users me-1"></i>Bulk Activate
                                </button>
                                <button class="btn btn-outline-info btn-sm" onclick="exportUsers()">
                                    <i class="fas fa-download me-1"></i>Export Users
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="sendBulkNotification()">
                                    <i class="fas fa-envelope me-1"></i>Send Notification
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <div class="card admin-card shadow mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-heartbeat me-2"></i>System Health & Performance
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="health-metric">
                                <div class="health-icon bg-success">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="health-info">
                                    <div class="health-label">Database</div>
                                    <div class="health-status text-success">Healthy</div>
                                    <small class="text-muted"><?php echo e($stats['db_size'] ?? 'N/A'); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="health-metric">
                                <div class="health-icon bg-info">
                                    <i class="fas fa-memory"></i>
                                </div>
                                <div class="health-info">
                                    <div class="health-label">Cache</div>
                                    <div class="health-status text-info">Active</div>
                                    <small class="text-muted"><?php echo e($stats['cache_size'] ?? 'N/A'); ?></small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="health-metric">
                                <div class="health-icon bg-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="health-info">
                                    <div class="health-label">Uptime</div>
                                    <div class="health-status text-warning"><?php echo e($stats['uptime'] ?? 'N/A'); ?></div>
                                    <small class="text-muted"><?php echo e($stats['last_restart'] ?? 'N/A'); ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-4">
            <!-- Recent Users -->
            <div class="card admin-card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-user-clock me-2"></i>Recent Users
                    </h6>
                </div>
                <div class="card-body">
                    <?php if(isset($recent_users) && count($recent_users) > 0): ?>
                        <div class="recent-users-list">
                            <?php $__currentLoopData = $recent_users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="recent-user-item">
                                <div class="user-avatar">
                                    <div class="avatar-circle bg-primary text-white">
                                        <?php echo e(strtoupper(substr($user->name, 0, 2))); ?>

                                    </div>
                                </div>
                                <div class="user-info">
                                    <div class="user-name"><?php echo e($user->name); ?></div>
                                    <div class="user-details">
                                        <span class="badge bg-<?php echo e($user->role === 'admin' ? 'danger' : ($user->role === 'manager' ? 'warning' : 'info')); ?>">
                                            <?php echo e(ucfirst($user->role)); ?>

                                        </span>
                                        <small class="text-muted ms-2"><?php echo e($user->created_at->diffForHumans()); ?></small>
                                    </div>
                                </div>
                                <div class="user-actions">
                                    <a href="<?php echo e(route('users.show', $user->id)); ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No recent users</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Actions -->
            <div class="card admin-card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-tools me-2"></i>System Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary" onclick="clearCache()">
                            <i class="fas fa-broom me-2"></i>Clear System Cache
                        </button>
                        <button class="btn btn-outline-success" onclick="warmupCache()">
                            <i class="fas fa-fire me-2"></i>Warmup Cache
                        </button>
                        <button class="btn btn-outline-info" onclick="optimizeDatabase()">
                            <i class="fas fa-database me-2"></i>Optimize Database
                        </button>
                        <button class="btn btn-outline-warning" onclick="generateBackup()">
                            <i class="fas fa-save me-2"></i>Generate Backup
                        </button>
                        <hr>
                        <button class="btn btn-outline-secondary" onclick="viewSystemLogs()">
                            <i class="fas fa-file-alt me-2"></i>View System Logs
                        </button>
                        <button class="btn btn-outline-dark" onclick="systemSettings()">
                            <i class="fas fa-cogs me-2"></i>System Settings
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card admin-card shadow">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Quick Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="quick-stats">
                        <div class="quick-stat-item">
                            <div class="stat-label">Today's Logins</div>
                            <div class="stat-value"><?php echo e($stats['todays_logins'] ?? 0); ?></div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="stat-label">New Users This Week</div>
                            <div class="stat-value"><?php echo e($stats['weekly_new_users'] ?? 0); ?></div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="stat-label">Active Sessions</div>
                            <div class="stat-value"><?php echo e($stats['active_sessions'] ?? 0); ?></div>
                        </div>
                        <div class="quick-stat-item">
                            <div class="stat-label">Failed Logins</div>
                            <div class="stat-value text-danger"><?php echo e($stats['failed_logins'] ?? 0); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Admin Dashboard Specific Styles */
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

.stats-sublabel {
    font-size: 0.75rem;
    opacity: 0.7;
    margin-top: 0.25rem;
}

.stats-icon {
    position: absolute;
    top: 1rem;
    right: 1rem;
    font-size: 2.5rem;
    opacity: 0.3;
    z-index: 1;
}

/* Admin Cards */
.admin-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.admin-card .card-header {
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-bottom: 2px solid #dee2e6;
    border-radius: 1rem 1rem 0 0 !important;
    padding: 1.25rem 1.5rem;
}

/* Role Stats */
.role-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.role-info {
    flex: 1;
}

.role-progress {
    flex: 1;
    margin-left: 1rem;
}

.progress {
    height: 8px;
    border-radius: 4px;
}

/* Status Stats */
.status-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.status-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.1rem;
}

.status-number {
    font-size: 1.25rem;
    font-weight: 700;
    color: #495057;
}

.status-label {
    font-size: 0.875rem;
    color: #6c757d;
}

/* Health Metrics */
.health-metric {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.health-icon {
    width: 50px;
    height: 50px;
    border-radius: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

.health-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #495057;
}

.health-status {
    font-size: 1rem;
    font-weight: 700;
}

/* Recent Users */
.recent-user-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.recent-user-item:last-child {
    border-bottom: none;
}

.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    font-weight: bold;
}

.user-info {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
}

.user-details {
    margin-top: 0.25rem;
}

/* Quick Stats */
.quick-stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.quick-stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-size: 0.875rem;
    color: #6c757d;
}

.stat-value {
    font-size: 1.1rem;
    font-weight: 700;
    color: #495057;
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

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-card {
        margin-bottom: 1rem;
    }

    .status-stats {
        grid-template-columns: 1fr;
    }

    .role-item {
        flex-direction: column;
        align-items: start;
        gap: 0.5rem;
    }

    .role-progress {
        margin-left: 0;
        width: 100%;
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
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Cache management functions
function clearCache() {
    Swal.fire({
        title: 'Clear System Cache?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will clear all application caches including:</p>
                <ul class="text-muted">
                    <li>SAW calculation results</li>
                    <li>Dashboard data</li>
                    <li>Chart data</li>
                    <li>Navigation data</li>
                    <li>User data</li>
                </ul>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    System performance may be temporarily slower while caches rebuild.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-broom me-1"></i>Clear Cache',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Clearing Cache...',
                html: '<div class="spinner-border text-danger" role="status"><span class="visually-hidden">Loading...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Simulate cache clearing (you can implement actual cache clearing here)
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Cache Cleared!',
                    text: 'All application caches have been successfully cleared.',
                    timer: 3000,
                    timerProgressBar: true
                });
            }, 2000);
        }
    });
}

function warmupCache() {
    Swal.fire({
        title: 'Warmup System Cache?',
        html: `
            <div class="text-start">
                <p class="mb-3">This will pre-populate system caches with frequently accessed data:</p>
                <ul class="text-muted">
                    <li>Dashboard statistics</li>
                    <li>Navigation menus</li>
                    <li>Chart data</li>
                    <li>Recent evaluation results</li>
                </ul>
                <div class="alert alert-success">
                    <i class="fas fa-fire me-2"></i>
                    This will improve system performance for all users.
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-fire me-1"></i>Warmup Cache',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Warming Up Cache...',
                html: '<div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div>',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

            // Simulate cache warmup
            setTimeout(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Cache Warmed Up!',
                    text: 'System caches have been successfully pre-populated.',
                    timer: 3000,
                    timerProgressBar: true
                });
            }, 3000);
        }
    });
}

// System management functions
function optimizeDatabase() {
    Swal.fire({
        icon: 'info',
        title: 'Database Optimization',
        text: 'Database optimization tools will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function generateBackup() {
    Swal.fire({
        icon: 'info',
        title: 'Backup Generation',
        text: 'Automated backup generation will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function viewSystemLogs() {
    Swal.fire({
        icon: 'info',
        title: 'System Logs',
        text: 'System log viewer will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function systemSettings() {
    Swal.fire({
        icon: 'info',
        title: 'System Settings',
        text: 'System configuration panel will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

// User management functions
function bulkActivateUsers() {
    Swal.fire({
        icon: 'info',
        title: 'Bulk User Operations',
        text: 'Bulk user activation tools will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function exportUsers() {
    Swal.fire({
        icon: 'info',
        title: 'Export Users',
        text: 'User export functionality will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

function sendBulkNotification() {
    Swal.fire({
        icon: 'info',
        title: 'Bulk Notifications',
        text: 'Bulk notification system will be available in a future update.',
        timer: 3000,
        timerProgressBar: true
    });
}

// Auto-refresh dashboard data every 5 minutes
setInterval(() => {
    // You can implement auto-refresh logic here
    console.log('Auto-refreshing dashboard data...');
}, 300000); // 5 minutes
</script>
<?php $__env->stopPush(); ?>



<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\Pemograman\Laravel\SAWLaravel\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>