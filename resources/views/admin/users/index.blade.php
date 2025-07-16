@extends('admin.layouts.app')

@section('title', 'User Management')
@section('subtitle', 'Manage system users and their permissions')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New User
        </a>
        <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
            <span class="visually-hidden">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" onclick="exportUsers()">
                <i class="fas fa-download me-2"></i>Export Users
            </a></li>
            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                <i class="fas fa-tasks me-2"></i>Bulk Actions
            </a></li>
        </ul>
    </div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-gradient-primary text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <p class="mb-0">Total Users</p>
                    <small class="opacity-75">{{ $stats['active'] }} Active</small>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-gradient-success text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">{{ $stats['roles']['data_entry'] ?? 0 }}</h3>
                    <p class="mb-0">Data Entry</p>
                    <small class="opacity-75">Staff Members</small>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-keyboard"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-gradient-info text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">{{ $stats['roles']['supervisor'] ?? 0 }}</h3>
                    <p class="mb-0">Supervisors</p>
                    <small class="opacity-75">Team Leaders</small>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-gradient-warning text-white">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="mb-0">{{ $stats['roles']['viewer'] ?? 0 }}</h3>
                    <p class="mb-0">Viewers</p>
                    <small class="opacity-75">Read-only Access</small>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-eye"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, phone...">
            </div>
            <div class="col-md-2">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" id="role" name="role">
                    <option value="">All Roles</option>
                    <option value="data_entry" {{ ($filters['role'] ?? '') === 'data_entry' ? 'selected' : '' }}>Data Entry</option>
                    <option value="supervisor" {{ ($filters['role'] ?? '') === 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                    <option value="viewer" {{ ($filters['role'] ?? '') === 'viewer' ? 'selected' : '' }}>Viewer</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="department" class="form-label">Department</label>
                <input type="text" class="form-control" id="department" name="department" 
                       value="{{ $filters['department'] ?? '' }}" placeholder="Department name">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Users List</h5>
    </div>
    <div class="card-body p-0">
        @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>User Details</th>
                            <th>Role & Department</th>
                            <th>Contact Info</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th>Created</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">
                                        <div class="avatar-circle">
                                            {{ substr($user->name, 0, 2) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                        <small class="text-muted">{{ $user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary mb-1">{{ $user->role_text }}</span><br>
                                <small class="text-muted">{{ $user->department ?? 'Not assigned' }}</small>
                            </td>
                            <td>
                                <div>
                                    <i class="fas fa-envelope me-1 text-muted"></i>{{ $user->email }}<br>
                                    @if($user->phone)
                                        <i class="fas fa-phone me-1 text-muted"></i>{{ $user->phone }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span class="status-badge {{ $user->status === 'active' ? 'status-active' : 'status-inactive' }}">
                                    <i class="fas fa-{{ $user->status === 'active' ? 'check-circle' : 'times-circle' }} me-1"></i>
                                    {{ $user->status_text }}
                                </span>
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    <span title="{{ $user->last_login_at->format('M d, Y \a\t h:i A') }}">
                                        {{ $user->last_login_at->diffForHumans() }}
                                    </span>
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $user->created_at->format('M d, Y \a\t h:i A') }}">
                                    {{ $user->created_at->diffForHumans() }}
                                </span>
                                @if($user->creator)
                                    <br><small class="text-muted">by {{ $user->creator->name }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-{{ $user->status === 'active' ? 'warning' : 'success' }}" 
                                            onclick="toggleStatus({{ $user->id }}, '{{ $user->status === 'active' ? 'inactive' : 'active' }}')" 
                                            title="{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $user->status === 'active' ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
                    </small>
                    {{ $users->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users text-muted fa-3x mb-3"></i>
                <h5 class="text-muted">No users found</h5>
                <p class="text-muted">No users match your current filters.</p>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add First User
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionForm">
                    @csrf
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Select Action</label>
                        <select class="form-select" id="bulkAction" name="action" required>
                            <option value="">Choose an action...</option>
                            <option value="activate">Activate Selected Users</option>
                            <option value="deactivate">Deactivate Selected Users</option>
                            <option value="delete">Delete Selected Users</option>
                        </select>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action will be applied to all selected users. This cannot be undone.
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Execute Action</button>
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
@endsection

@push('styles')
<style>
.stats-card {
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    color: white;
}

.stats-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.user-avatar .avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.75rem;
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

.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.bg-gradient-info {
    background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
}

.bg-gradient-warning {
    background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
}
</style>
@endpush

@push('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

// Toggle user status
function toggleStatus(userId, newStatus) {
    if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this user?`)) {
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
        alert('An error occurred while deleting user.');
    });
}

// Execute bulk action
function executeBulkAction() {
    const selectedUsers = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
    const action = document.getElementById('bulkAction').value;
    
    if (selectedUsers.length === 0) {
        alert('Please select at least one user.');
        return;
    }
    
    if (!action) {
        alert('Please select an action.');
        return;
    }
    
    fetch('{{ route('admin.users.bulk-action') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            action: action,
            user_ids: selectedUsers
        })
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
        alert('An error occurred while executing bulk action.');
    });
}

// Export users
function exportUsers() {
    const params = new URLSearchParams(window.location.search);
    window.location.href = '{{ route('admin.users.export') }}?' + params.toString();
}
</script>
@endpush