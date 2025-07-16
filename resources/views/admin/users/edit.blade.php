@extends('admin.layouts.app')

@section('title', 'Edit User')
@section('subtitle', 'Update user information and permissions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.show', $user->id) }}">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-eye me-1"></i> View User
        </a>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Users
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Edit User Information</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select @error('department') is-invalid @enderror" 
                                    id="department" name="department">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept }}" 
                                            {{ old('department', $user->department) === $dept ? 'selected' : '' }}>
                                        {{ $dept }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                            <select class="form-select @error('role') is-invalid @enderror" 
                                    id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="data_entry" {{ old('role', $user->role) === 'data_entry' ? 'selected' : '' }}>
                                    Data Entry
                                </option>
                                <option value="supervisor" {{ old('role', $user->role) === 'supervisor' ? 'selected' : '' }}>
                                    Supervisor
                                </option>
                                <option value="viewer" {{ old('role', $user->role) === 'viewer' ? 'selected' : '' }}>
                                    Viewer
                                </option>
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="active" {{ old('status', $user->status) === 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $user->status) === 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            <small class="form-text text-muted">Leave blank to keep current password</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            @foreach($permissions as $key => $label)
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="permission_{{ $key }}" 
                                               name="permissions[]" 
                                               value="{{ $key }}"
                                               {{ in_array($key, old('permissions', $user->permissions ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="permission_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('permissions')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Current User Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Current Information</h5>
            </div>
            <div class="card-body">
                <div class="current-info">
                    <div class="info-item">
                        <label>Current Status:</label>
                        <span class="badge bg-{{ $user->status === 'active' ? 'success' : 'danger' }}">
                            {{ $user->status_text }}
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Current Role:</label>
                        <span class="badge bg-primary">{{ $user->role_text }}</span>
                    </div>
                    <div class="info-item">
                        <label>Account Created:</label>
                        <span>{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-item">
                        <label>Last Login:</label>
                        <span>
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('M d, Y') }}
                            @else
                                Never
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Log -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Change History</h5>
            </div>
            <div class="card-body">
                <div class="change-log">
                    <div class="log-item">
                        <div class="log-date">{{ $user->created_at->format('M d, Y') }}</div>
                        <div class="log-action">Account Created</div>
                        <div class="log-user">
                            @if($user->creator)
                                by {{ $user->creator->name }}
                            @else
                                by System
                            @endif
                        </div>
                    </div>
                    
                    @if($user->updated_at != $user->created_at)
                    <div class="log-item">
                        <div class="log-date">{{ $user->updated_at->format('M d, Y') }}</div>
                        <div class="log-action">Last Updated</div>
                        <div class="log-user">
                            @if($user->updater)
                                by {{ $user->updater->name }}
                            @else
                                by System
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Security Notes -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security Notes</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-key me-2"></i>Password Policy:</h6>
                    <ul class="mb-0">
                        <li>Minimum 8 characters</li>
                        <li>Leave blank to keep current password</li>
                        <li>User will need to use new password for mobile app</li>
                    </ul>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Permission Changes:</h6>
                    <p class="mb-0">Changes to permissions will take effect immediately for new login sessions.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.current-info .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.current-info .info-item:last-child {
    border-bottom: none;
}

.current-info .info-item label {
    font-weight: 500;
    color: #333;
    margin: 0;
}

.change-log .log-item {
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.change-log .log-item:last-child {
    border-bottom: none;
}

.log-date {
    font-size: 0.875rem;
    color: #666;
}

.log-action {
    font-weight: 500;
    color: #333;
    margin: 2px 0;
}

.log-user {
    font-size: 0.8rem;
    color: #999;
}
</style>
@endpush

@push('scripts')
<script>
// Role-based permission suggestions
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    
    // Ask user if they want to apply default permissions for this role
    if (role && confirm('Do you want to apply default permissions for this role?')) {
        const checkboxes = document.querySelectorAll('input[name="permissions[]"]');
        
        // Clear all checkboxes first
        checkboxes.forEach(checkbox => checkbox.checked = false);
        
        // Set default permissions based on role
        const rolePermissions = {
            'data_entry': ['manage_samples', 'view_samples', 'view_sellers', 'view_buyers'],
            'supervisor': ['manage_samples', 'view_samples', 'manage_sellers', 'view_sellers', 'manage_buyers', 'view_buyers', 'manage_dispatch', 'view_dispatch', 'view_reports'],
            'viewer': ['view_samples', 'view_sellers', 'view_buyers', 'view_reports']
        };
        
        if (rolePermissions[role]) {
            rolePermissions[role].forEach(permission => {
                const checkbox = document.getElementById('permission_' + permission);
                if (checkbox) {
                    checkbox.checked = true;
                }
            });
        }
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('password_confirmation').value;
    
    if (password && password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match');
        return false;
    }
    
    if (password && password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return false;
    }
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = calculatePasswordStrength(password);
    
    // Remove existing strength indicator
    const existingIndicator = document.querySelector('.password-strength');
    if (existingIndicator) {
        existingIndicator.remove();
    }
    
    if (password.length > 0) {
        // Create strength indicator
        const indicator = document.createElement('div');
        indicator.className = 'password-strength mt-1';
        
        const strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const strengthColors = ['danger', 'warning', 'info', 'success', 'success'];
        
        indicator.innerHTML = `
            <small class="text-${strengthColors[strength]}">
                <i class="fas fa-shield-alt me-1"></i>
                Password Strength: ${strengthText[strength]}
            </small>
        `;
        
        this.parentNode.appendChild(indicator);
    }
});

function calculatePasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return Math.min(strength, 4);
}

// Confirm navigation away with unsaved changes
let formChanged = false;

document.querySelectorAll('input, select, textarea').forEach(function(element) {
    element.addEventListener('change', function() {
        formChanged = true;
    });
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged) {
        e.preventDefault();
        e.returnValue = '';
    }
});

document.querySelector('form').addEventListener('submit', function() {
    formChanged = false;
});
</script>
@endpush