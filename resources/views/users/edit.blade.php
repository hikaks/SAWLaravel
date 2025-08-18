@extends('layouts.main')

@section('title', __('Edit User') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Edit User'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Edit User') }}</h1>
        <p class="text-gray-600">{{ __('Update user information') }}: <span class="font-semibold">{{ $user->name }}</span></p>
    </div>
    <div class="flex flex-wrap gap-2">
        <x-ui.button href="{{ route('users.show', $user->id) }}" variant="outline-info" icon="fas fa-eye">{{ __('View Details') }}</x-ui.button>
        <x-ui.button href="{{ route('users.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">{{ __('Back to List') }}</x-ui.button>
    </div>
</div>

<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-warning-500 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-edit text-white text-lg"></i>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">{{ __('Update User Information') }}</h5>
                    <p class="text-sm text-gray-500">{{ __('Modify the fields you want to update') }}</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-user"></i>{{ __('Basic Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Full Name') }} <span class="text-danger-500">*</span></label>
                            <input type="text" class="form-control @error('name') border-danger-500 @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger-500">*</span></label>
                            <input type="email" class="form-control @error('email') border-danger-500 @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('role') border-danger-500 @enderror" id="role" name="role" required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>{{ __('Administrator') }}</option>
                            </select>
                            @error('role')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('status') border-danger-500 @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                            @error('status')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-lock"></i>{{ __('Change Password') }}
                    </h6>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
                        <p class="text-sm text-gray-600">{{ __('Leave password fields empty if you don\'t want to change the password') }}</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="password" class="form-label">{{ __('New Password') }}</label>
                            <input type="password" class="form-control @error('password') border-danger-500 @enderror" id="password" name="password">
                            @error('password')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">{{ __('Confirm New Password') }}</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-6 border-t border-gray-200">
                    <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="primary"><i class="fas fa-save mr-2"></i>{{ __('Update User') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection