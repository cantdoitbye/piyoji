@extends('admin.layouts.app')

@section('title', 'Tea Management')
@section('subtitle', 'Manage tea categories, types, and grades')

@section('breadcrumb')
    <li class="breadcrumb-item active">Teas</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.teas.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Tea
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['total'] }}</div>
                <div class="small">Total Teas</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['active'] }}</div>
                <div class="small">Active</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['inactive'] }}</div>
                <div class="small">Inactive</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['categories'] }}</div>
                <div class="small">Categories</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['tea_types'] }}</div>
                <div class="small">Tea Types</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-dark text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['grades'] }}</div>
                <div class="small">Grades</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.teas.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="Search teas...">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        @foreach($categories as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['category'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="tea_type" class="form-label">Tea Type</label>
                    <select class="form-select" id="tea_type" name="tea_type">
                        <option value="">All Types</option>
                        @foreach($teaTypes as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['tea_type'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.teas.index') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Teas Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-leaf me-2"></i>Teas List</h5>
        <span class="badge bg-primary">{{ $teas->total() }} Total</span>
    </div>
    <div class="card-body p-0">
        @if($teas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tea Details</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($teas as $tea)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $tea->sub_title }}</div>
                                    @if($tea->description)
                                        <small class="text-muted">{{ Str::limit($tea->description, 60) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $tea->category }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $tea->tea_type }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $tea->grade }}</span>
                                </td>
                                <td>
                                    <span class="status-badge {{ $tea->status ? 'status-active' : 'status-inactive' }}">
                                        <i class="fas fa-{{ $tea->status ? 'check-circle' : 'times-circle' }} me-1"></i>
                                        {{ $tea->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.teas.show', $tea->id) }}" 
                                           class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.teas.edit', $tea->id) }}" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-{{ $tea->status ? 'warning' : 'success' }}"
                                                onclick="toggleStatus({{ $tea->id }}, {{ $tea->status ? 'false' : 'true' }})"
                                                title="{{ $tea->status ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $tea->status ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteTea({{ $tea->id }})" title="Delete">
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
                    <div class="text-muted">
                        Showing {{ $teas->firstItem() }} to {{ $teas->lastItem() }} of {{ $teas->total() }} results
                    </div>
                    {{ $teas->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-leaf fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Teas Found</h5>
                <p class="text-muted">No teas match your current filters.</p>
                <a href="{{ route('admin.teas.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First Tea
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(teaId, newStatus) {
    if (confirm('Are you sure you want to change the status of this tea?')) {
        fetch(`/admin/teas/${teaId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            alert('An error occurred while updating the status.');
        });
    }
}

function deleteTea(teaId) {
    if (confirm('Are you sure you want to delete this tea? This action cannot be undone.')) {
        fetch(`/admin/teas/${teaId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            alert('An error occurred while deleting the tea.');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-active {
    background-color: #d1edff;
    color: #0c63e4;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush