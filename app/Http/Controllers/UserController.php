<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Cache user data for DataTables
            $users = $this->cacheService->getNavigationData('all_users', function() {
                return User::latest()->get();
            });

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('status_badge', function($user) {
                    $class = $user->is_active ? 'success' : 'danger';
                    $text = $user->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-'.$class.'">'.$text.'</span>';
                })
                ->addColumn('role_badge', function($user) {
                    $class = match($user->role) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'user' => 'info',
                        default => 'secondary'
                    };
                    return '<span class="badge bg-'.$class.'">'.ucfirst($user->role).'</span>';
                })
                ->addColumn('verified_badge', function($user) {
                    return $user->email_verification_badge;
                })
                ->addColumn('last_login', function($user) {
                    return $user->updated_at ? $user->updated_at->diffForHumans() : 'Never';
                })
                ->addColumn('action', function($user) {
                    $currentUser = auth()->user();
                    $canEdit = $currentUser->isAdmin() || $currentUser->id === $user->id;
                    $canDelete = $currentUser->isAdmin() && $currentUser->id !== $user->id;

                    $actions = '
                        <div class="btn-group" role="group">
                            <a href="'.route('users.show', $user->id).'" class="btn btn-info btn-sm" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>';

                    if ($canEdit) {
                        $actions .= '
                            <a href="'.route('users.edit', $user->id).'" class="btn btn-warning btn-sm" title="Edit User">
                                <i class="fas fa-edit"></i>
                            </a>';
                    }

                    if ($canDelete) {
                        $actions .= '
                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteUser('.$user->id.', \''.$user->name.'\')" title="Delete User">
                                <i class="fas fa-trash"></i>
                            </button>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['status_badge', 'role_badge', 'verified_badge', 'action'])
                ->make(true);
        }

        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
        ];

        return view('users.index', compact('stats'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,manager,user'],
            'is_active' => ['boolean'],
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'is_active' => $request->boolean('is_active', true),
                'email_verified_at' => $request->boolean('verify_email', false) ? now() : null,
            ]);

            // Invalidate user cache
            $this->cacheService->invalidateNavigation();

            DB::commit();

            return redirect()->route('users.index')
                ->with('success', "User {$user->name} ({$user->email}) has been created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        // Users can only view their own profile unless they are admin
        if (!auth()->user()->isAdmin() && auth()->user()->id !== $user->id) {
            abort(403, 'Unauthorized to view this user profile.');
        }

        // Get user statistics (without evaluations relationship)
        $stats = [
            'total_logins' => 0, // We can implement this later with activity log
            'last_login' => $user->updated_at,
            'account_age' => $user->created_at->diffInDays(now()),
            'evaluations_created' => 0, // Placeholder - can be implemented later if needed
        ];

        return view('users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        // Users can only edit their own profile unless they are admin
        if (!auth()->user()->isAdmin() && auth()->user()->id !== $user->id) {
            abort(403, 'Unauthorized to edit this user.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        // Users can only update their own profile unless they are admin
        if (!auth()->user()->isAdmin() && auth()->user()->id !== $user->id) {
            abort(403, 'Unauthorized to update this user.');
        }

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
        ];

        // Only admin can change role and status
        if (auth()->user()->isAdmin()) {
            $rules['role'] = ['required', 'in:admin,manager,user'];
            $rules['is_active'] = ['boolean'];
        }

        // Password is optional for updates
        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Rules\Password::defaults()];
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Only admin can update role and status
            if (auth()->user()->isAdmin()) {
                $data['role'] = $request->role;
                $data['is_active'] = $request->boolean('is_active', true);

                if ($request->boolean('verify_email', false) && !$user->email_verified_at) {
                    $data['email_verified_at'] = now();
                }
            }

            // Update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Invalidate user cache
            $this->cacheService->invalidateNavigation();

            DB::commit();

            $message = auth()->user()->isAdmin()
                ? "User {$user->name} has been updated successfully!"
                : "Your profile has been updated successfully!";

            return redirect()->route('users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if (auth()->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account.'
            ], 400);
        }

        // Prevent deleting the last admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last admin user.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $userName = $user->name;
            $user->delete();

            // Invalidate user cache
            $this->cacheService->invalidateNavigation();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "User {$userName} has been deleted successfully."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleStatus(User $user)
    {
        // Prevent admin from deactivating themselves
        if (auth()->user()->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot deactivate your own account.'
            ], 400);
        }

        try {
            $user->update(['is_active' => !$user->is_active]);

            // Invalidate user cache
            $this->cacheService->invalidateNavigation();

            $status = $user->is_active ? 'activated' : 'deactivated';

            return response()->json([
                'success' => true,
                'message' => "User {$user->name} has been {$status}.",
                'new_status' => $user->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle user status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations for users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:activate,deactivate,delete'],
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['exists:users,id']
        ]);

        $userIds = $request->user_ids;
        $currentUserId = auth()->user()->id;

        // Remove current user from bulk operations
        $userIds = array_filter($userIds, fn($id) => $id != $currentUserId);

        if (empty($userIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid users selected for bulk operation.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $count = 0;

            switch ($request->action) {
                case 'activate':
                    $count = User::whereIn('id', $userIds)->update(['is_active' => true]);
                    $message = "{$count} users have been activated.";
                    break;

                case 'deactivate':
                    $count = User::whereIn('id', $userIds)->update(['is_active' => false]);
                    $message = "{$count} users have been deactivated.";
                    break;

                case 'delete':
                    // Prevent deleting all admins
                    $adminIds = User::whereIn('id', $userIds)->where('role', 'admin')->pluck('id');
                    $remainingAdmins = User::where('role', 'admin')->whereNotIn('id', $adminIds)->count();

                    if ($remainingAdmins < 1) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Cannot delete all admin users. At least one admin must remain.'
                        ], 400);
                    }

                    $count = User::whereIn('id', $userIds)->delete();
                    $message = "{$count} users have been deleted.";
                    break;
            }

            // Invalidate user cache
            $this->cacheService->invalidateNavigation();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send password reset email to user
     */
    public function sendPasswordReset(User $user)
    {
        try {
            // This would integrate with Laravel's password reset functionality
            $user->sendPasswordResetNotification(\Str::random(60));

            return response()->json([
                'success' => true,
                'message' => "Password reset email has been sent to {$user->email}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send password reset email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send email verification to user
     */
    public function sendEmailVerification(User $user)
    {
        try {
            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User email is already verified.'
                ], 400);
            }

            $user->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => "Email verification has been sent to {$user->email}."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email verification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Manually verify user email (Admin only)
     */
    public function manuallyVerifyEmail(User $user)
    {
        try {
            if (!auth()->user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can manually verify emails.'
                ], 403);
            }

            if ($user->hasVerifiedEmail()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User email is already verified.'
                ], 400);
            }

            $user->markEmailAsVerified();

            // Invalidate user cache
            $this->cacheService->invalidateNavigation();

            return response()->json([
                'success' => true,
                'message' => "Email for {$user->name} has been manually verified."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users by verification status
     */
    public function getByVerificationStatus(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = User::query();

        switch ($status) {
            case 'verified':
                $query->verified();
                break;
            case 'unverified':
                $query->unverified();
                break;
            case 'active':
                $query->active();
                break;
            case 'inactive':
                $query->where('is_active', false);
                break;
        }

        $users = $query->latest()->get(['id', 'name', 'email', 'role', 'is_active', 'email_verified_at']);

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count()
        ]);
    }
}
