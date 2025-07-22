@extends('admin.layouts.app')

@section('title', 'POC Management')
@section('subtitle', 'Manage Person of Contact (POC) for sellers and buyers')

@section('breadcrumb')
    <li class="breadcrumb-item active">POCs</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.pocs.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New POC
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['total'] }}</div>
                <div class="small">Total POCs</div>
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
                <div class="fw-bold fs-4">{{ $statistics['sellers'] }}</div>
                <div class="small">For Sellers</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['buyers'] }}</div>
                <div class="small">For Buyers</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-dark text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['both'] }}</div>
                <div class="small">Both</div>
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
        <form method="GET" action="{{ route('admin.pocs.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="Search POCs...">
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
                    <label for="poc_type" class="form-label">POC Type</label>
                    <select class="form-select" id="poc_type" name="poc_type">
                        <option value="">All Types</option>
                        @foreach($pocTypes as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['poc_type'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="per_page" class="form-label">Per Page</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="15" {{ ($filters['per_page'] ?? '15') == '15' ? 'selected' : '' }}>15</option>
                        <option value="25" {{ ($filters['per_page'] ?? '15') == '25' ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($filters['per_page'] ?? '15') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($filters['per_page'] ?? '15') == '100' ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.pocs.index') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- POCs Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-users me-2"></i>POCs List</h5>
        <span class="badge bg-primary">{{ $pocs->total() }} Total</span>
    </div>
    <div class="card-body p-0">
        @if($pocs->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>POC Name</th>
                            <th>Type</th>
                            <th>Contact Info</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pocs as $poc)
                            <tr>
                                <td>{{ $poc->email }}</td>
                                <td>
                                    <div><i class="fas fa-envelope me-1"></i>{{ $poc->email }}</div>
                                    <div><i class="fas fa-phone me-1"></i>{{ $poc->phone }}</div>
                                </td>
                                <td>
                                    @if($poc->city || $poc->state)
                                        <div>{{ $poc->city }}{{ $poc->city && $poc->state ? ', ' : '' }}{{ $poc->state }}</div>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $poc->status ? 'status-active' : 'status-inactive' }}">
                                        <i class="fas fa-{{ $poc->status ? 'check-circle' : 'times-circle' }} me-1"></i>
                                        {{ $poc->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.pocs.show', $poc->id) }}" 
                                           class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.pocs.edit', $poc->id) }}" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-{{ $poc->status ? 'warning' : 'success' }}"
                                                onclick="toggleStatus({{ $poc->id }}, {{ $poc->status ? 'false' : 'true' }})"
                                                title="{{ $poc->status ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $poc->status ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deletePoc({{ $poc->id }})" title="Delete">
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
                        Showing {{ $pocs->firstItem() }} to {{ $pocs->lastItem() }} of {{ $pocs->total() }} results
                    </div>
                    {{ $pocs->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No POCs Found</h5>
                <p class="text-muted">No POCs match your current filters.</p>
                <a href="{{ route('admin.pocs.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First POC
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(pocId, newStatus) {
    if (confirm('Are you sure you want to change the status of this POC?')) {
        fetch(`/admin/pocs/${pocId}/toggle-status`, {
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

function deletePoc(pocId) {
    if (confirm('Are you sure you want to delete this POC? This action cannot be undone.')) {
        fetch(`/admin/pocs/${pocId}`, {
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
            alert('An error occurred while deleting the POC.');
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