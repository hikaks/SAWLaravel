@extends('layouts.main')

@section('title', __('Add User') . ' - ' . __('SAW Employee Evaluation'))
@section('page-title', __('Add User'))

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('Add New User') }}</h1>
        <p class="text-gray-600">{{ __('Create a new system user account') }}</p>
    </div>
    <x-ui.button href="{{ route('users.index') }}" variant="outline-secondary" icon="fas fa-arrow-left">
        {{ __('Back to List') }}
    </x-ui.button>
</div>

<div class="max-w-4xl mx-auto">
    <div class="card">
        <div class="card-header">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div>
                    <h5 class="text-lg font-semibold text-gray-900">{{ __('User Information') }}</h5>
                    <p class="text-sm text-gray-500">{{ __('Fill in all required fields marked with *') }}</p>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST" x-data="userForm()">
                @csrf
                
                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-user"></i>{{ __('Basic Information') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="name" class="form-label">{{ __('Full Name') }} <span class="text-danger-500">*</span></label>
                            <input type="text" class="form-control @error('name') border-danger-500 @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger-500">*</span></label>
                            <input type="email" class="form-control @error('email') border-danger-500 @enderror" id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="role" class="form-label">{{ __('Role') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('role') border-danger-500 @enderror" id="role" name="role" required>
                                <option value="">{{ __('Select Role') }}</option>
                                <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>{{ __('User') }}</option>
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>{{ __('Administrator') }}</option>
                            </select>
                            @error('role')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="status" class="form-label">{{ __('Status') }} <span class="text-danger-500">*</span></label>
                            <select class="form-select @error('status') border-danger-500 @enderror" id="status" name="status" required>
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>{{ __('Active') }}</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                            </select>
                            @error('status')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <h6 class="flex items-center gap-2 text-lg font-semibold text-primary-600 mb-4">
                        <i class="fas fa-lock"></i>{{ __('Security') }}
                    </h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="form-group">
                            <label for="password" class="form-label">{{ __('Password') }} <span class="text-danger-500">*</span></label>
                            <input type="password" class="form-control @error('password') border-danger-500 @enderror" id="password" name="password" required>
                            @error('password')<p class="text-sm text-danger-600 mt-1">{{ $message }}</p>@enderror
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }} <span class="text-danger-500">*</span></label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-6 border-t border-gray-200">
                    <x-ui.button type="button" variant="outline-secondary" onclick="window.history.back()">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="primary"><i class="fas fa-save mr-2"></i>{{ __('Save User') }}</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function userForm() {
    return {
        init() {
            this.setupPasswordValidation();
        },
        
        setupPasswordValidation() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            
            confirmPassword.addEventListener('input', () => {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('{{ __("Passwords do not match") }}');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            });
        }
    }
}
</script>
@endpush