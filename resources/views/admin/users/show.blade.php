@extends('admin.layouts.app')

@section('title', 'User Details')
@section('subtitle', 'View user information and activity')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit User
        </a>
        <button type="button" class="btn btn-outline-{{ $user->status === 'active' ? 'warning' : 'success' }}" 
                onclick="toggleStatus({{ $user->id }}, '{{ $user->status === 'active' ? 'inactive' : 'active' }}')">
            <i class="fas fa-{{ $user->status === 'active' ? 'pause' : 'play' }} me-1"></i>
            {{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}
        </button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Full Name</label>
                        <div class="fw-bold">{{ $user->name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email Address</label>
                        <div>{{ $user->email }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <div>{{ $user->phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Department</label>
                        <div>{{ $user->department ?? 'Not assigned' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Role</label>
                        <div>
                            <span class="badge bg-primary fs-6">
                                <i class="fas fa-user-tag me-1"></i>{{ $user->role_text }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                <i class="fas fa-{{ $user->status === 'active' ? 'check-circle' : 'times-circle' }} me-1"></i>
                                {{ $user->status_text }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Permissions</h5>
            </div>
            <div class="card-body">
                @if($user->permissions && count($user->permissions) > 0)
                    <div class="row">
                        @foreach($user->permissions as $permission)
                            <div class="col-md-6 mb-2">
                                <span class="badge bg-success me-1">
                                    <i class="fas fa-check me-1"></i>{{ ucfirst(str_replace('_', ' ', $permission)) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-info-circle text-muted fa-2x mb-2"></i>
                        <p class="text-muted">No specific permissions assigned</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Login Activity -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Login Activity</h5>
            </div>
            <div class="card-body">
                @if($recentLogins->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>IP Address</th>
                                    <th>User Agent</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLogins as $log)
                                <tr>
                                    <td>{{ $log->login_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $log->login_status === 'success' ? 'success' : 'danger' }}">
                                            {{ $log->login_status_text }}
                                        </span>
                                        @if($log->login_status === 'failed' && $log->failure_reason)
                                            <br><small class="text-muted">{{ $log->failure_reason }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $log->ip_address }}</td>
                                    <td>
                                        <span title="{{ $log->user_agent }}">
                                            {{ Str::limit($log->user_agent, 30) }}
                                        </span>
                                    </td>
                                    <td>{{ $log->session_duration ?? 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history text-muted fa-3x mb-3"></i>
                        <h5 class="text-muted">No login activity</h5>
                        <p class="text-muted">This user has not logged in yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- User Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>User Summary</h5>
            </div>
            <div class="card-body">
                <div class="user-summary">
                    <div class="summary-item">
                        <div class="summary-label">Account Created</div>
                        <div class="summary-value">{{ $user->created_at->format('M d, Y') }}</div>
                        <div class="summary-sublabel">{{ $user->created_at->diffForHumans() }}</div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Last Login</div>
                        <div class="summary-value">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('M d, Y') }}
                            @else
                                Never
                            @endif
                        </div>
                        <div class="summary-sublabel">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->diffForHumans() }}
                            @else
                                No login recorded
                            @endif
                        </div>
                    </div>
                    
                    <div class="summary-item">
                        <div class="summary-label">Created By</div>
                        <div class="summary-value">
                            @if($user->creator)
                                {{ $user->creator->name }}
                            @else
                                System
                            @endif
                        </div>
                        <div class="summary-sublabel">Administrator</div>
                    </div>
                    
                    @if($user->updated_at != $user->created_at)
                    <div class="summary-item">
                        <div class="summary-label">Last Updated</div>
                        <div class="summary-value">{{ $user->updated_at->format('M d, Y') }}</div>
                        <div class="summary-sublabel">
                            @if($user->updater)
                                by {{ $user->updater->name }}
                            @else
                                {{ $user->updated_at->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Profile
                    </a>
                    
                    <button type="button" class="btn btn-outline-{{ $user->status === 'active' ? 'warning' : 'success' }} btn-sm" 
                            onclick="toggleStatus({{ $user->id }}, '{{ $user->status === 'active' ? 'inactive' : 'active' }}')">
                        <i class="fas fa-{{ $user->status === 'active' ? 'pause' : 'play' }} me-1"></i>
                        {{ $user->status === 'active' ? 'Deactivate Account' : 'Activate Account' }}
                    </button>
                    
                    <button type="button" class="btn btn-outline-info btn-sm" onclick="resetPassword({{ $user->id }})">
                        <i class="fas fa-key me-1"></i>Reset Password
                    </button>
                    
                    <hr>
                    
                    <button type="button" class="btn btn-outline-danger btn-sm" 
                            onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                        <i class="fas fa-trash me-1"></i>Delete User
                    </button>
                </div>
            </div>
        </div>

        <!-- System Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>System Information</h5>
            </div>
            <div class="card-body">
                <div class="system-info">
                    <div class="info-item">
                        <label>User ID:</label>
                        <span>{{ $user->id }}</span>
                    </div>
                    <div class="info-item">
                        <label>Email Verified:</label>
                        <span>
                            @if($user->email_verified_at)
                                <i class="fas fa-check text-success"></i> Yes
                            @else
                                <i class="fas fa-times text-danger"></i> No
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <label>Total Logins:</label>
                        <span>{{ $user->loginLogs()->where('login_status', 'success')->count() }}</span>
                    </div>
                    <div class="info-item">
                        <label>Failed Attempts:</label>
                        <span>{{ $user->loginLogs()->where('login_status', 'failed')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the user <strong id="deleteUserName"></strong>?</p>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    This action cannot be undone. All user data will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete User</button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="resetPasswordForm">
                    @csrf
                    <div class="mb-3">
                        <label for="newPassword" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="newPassword" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executePasswordReset()">Reset Password</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-active {
    background-color: #d4edda;
    color: #155724;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
}

.user-summary .summary-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.user-summary .summary-item:last-child {
    border-bottom: none;
}

.summary-label {
    font-size: 0.875rem;
    color: #6c757d;
    margin-bottom: 5px;
}

.summary-value {
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.summary-sublabel {
    font-size: 0.8rem;
    color: #999;
}

.system-info .info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.system-info .info-item:last-child {
    border-bottom: none;
}

.system-info .info-item label {
    font-weight: 500;
    color: #333;
    margin: 0;
}

.system-info .info-item span {
    color: #666;
}
</style>
@endpush

@push('scripts')
<script>
// Toggle user status
function toggleStatus(userId, newStatus) {
    const action = newStatus === 'active' ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        fetch(`/admin/users/${userId}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating user status.');
        });
    }
}

// Confirm delete
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('confirmDeleteBtn').onclick = function() {
        deleteUser(userId);
    };
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Delete user
function deleteUser(userId) {
    fetch(`/admin/users/${userId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        if (response.ok) {
            window.location.href = '/admin/users';
        } else {
            alert('Error occurred while deleting user.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting user.');
    });
}

// Reset password
function resetPassword(userId) {
    new bootstrap.Modal(document.getElementById('resetPasswordModal')).show();
}

// Execute password reset
function executePasswordReset() {
    const password = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (password !== confirmPassword) {
        alert('Passwords do not match');
        return;
    }
    
    if (password.length < 8) {
        alert('Password must be at least 8 characters long');
        return;
    }
    
    // Here you would typically send the request to reset password
    // For now, we'll just show a success message
    alert('Password reset functionality would be implemented here');
    bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
}
</script>
@endpush