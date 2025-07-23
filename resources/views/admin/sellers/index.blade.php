@extends('admin.layouts.app')

@section('title', 'Sellers')
@section('subtitle', 'Manage tea sellers and their information')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sellers</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.sellers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Seller
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-primary mb-0">{{ $statistics['total'] }}</h3>
                    <p class="text-muted mb-0">Total Sellers</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-success mb-0">{{ $statistics['active'] }}</h3>
                    <p class="text-muted mb-0">Active Sellers</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-warning mb-0">{{ $statistics['inactive'] }}</h3>
                    <p class="text-muted mb-0">Inactive Sellers</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-info mb-0">{{ $statistics['recent'] }}</h3>
                    <p class="text-muted mb-0">Added This Month</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.sellers.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" id="search" 
                           placeholder="Search sellers..." value="{{ $filters['search'] ?? '' }}">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" name="status" id="status">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="tea_grade" class="form-label">Tea Grade</label>
                    <select class="form-select" name="tea_grade" id="tea_grade">
                        <option value="">All Grades</option>
                        @foreach($teaGrades as $key => $grade)
                            <option value="{{ $key }}" {{ ($filters['tea_grade'] ?? '') == $key ? 'selected' : '' }}>
                                {{ $grade }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="state" class="form-label">State</label>
                    <input type="text" class="form-control" name="state" id="state" 
                           placeholder="State" value="{{ $filters['state'] ?? '' }}">
                </div>
                
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Per Page</label>
                    <select class="form-select" name="per_page" id="per_page">
                        <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($filters['per_page'] ?? 15) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i>
                    </button>
                    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions -->
<div class="card mb-4" id="bulkActionsCard" style="display: none;">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col">
                <span id="selectedCount">0</span> seller(s) selected
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                        <i class="fas fa-check"></i> Activate
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')">
                        <i class="fas fa-pause"></i> Deactivate
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sellers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Sellers List</h5>
        <div class="action-buttons">
            <a href="{{ route('admin.sellers.export') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-download me-1"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="sellersTable">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Seller Name</th>
                        <th>Tea Estate</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Tea Grades</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $seller->id }}">
                            </td>
                            <td>
                                <strong>{{ $seller->seller_name }}</strong><br>
                                <small class="text-muted">GSTIN: {{ $seller->gstin }}</small>
                            </td>
                            <td>{{ $seller->tea_estate_name }}</td>
                            <td>{{ $seller->contact_person }}</td>
                            <td>
                                <a href="mailto:{{ $seller->email }}">{{ $seller->email }}</a>
                            </td>
                            <td>
                                <a href="tel:{{ $seller->phone }}">{{ $seller->phone }}</a>
                            </td>
                            <td>{{ $seller->city }}, {{ $seller->state }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $seller->tea_grades_text }}</span>
                            </td>
                            <td>
                                <span class="status-badge {{ $seller->status ? 'status-active' : 'status-inactive' }}">
                                    {{ $seller->status_text }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-{{ $seller->status ? 'warning' : 'success' }}" 
                                            onclick="toggleStatus({{ $seller->id }}, {{ $seller->status ? 'false' : 'true' }})" 
                                            title="{{ $seller->status ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $seller->status ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteSeller({{ $seller->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-store fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No sellers found</p>
                                <a href="{{ route('admin.sellers.create') }}" class="btn btn-primary">
                                    Add First Seller
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($sellers->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing {{ $sellers->firstItem() }} to {{ $sellers->lastItem() }} of {{ $sellers->total() }} results
                </div>
                <div>
                    {{ $sellers->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle select all checkbox
    $('#selectAll').change(function() {
        $('.row-checkbox').prop('checked', this.checked);
        updateBulkActions();
    });
    
    // Handle individual row checkboxes
    $(document).on('change', '.row-checkbox', function() {
        updateBulkActions();
    });
    
    // Auto-submit form on filter change
    $('#status, #tea_grade, #per_page').change(function() {
        $('#filterForm').submit();
    });
    
    // Search with delay
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });
});

function updateBulkActions() {
    const checked = $('.row-checkbox:checked').length;
    $('#selectedCount').text(checked);
    
    if (checked > 0) {
        $('#bulkActionsCard').show();
    } else {
        $('#bulkActionsCard').hide();
    }
    
    // Update select all checkbox state
    const total = $('.row-checkbox').length;
    $('#selectAll').prop('indeterminate', checked > 0 && checked < total);
    $('#selectAll').prop('checked', checked === total);
}

function toggleStatus(id, status) {
    if (!confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this seller?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/public/admin/sellers/${id}/status`,
        method: 'PATCH',
        // data: { status: status },
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: JSON.stringify({ 
            status: status === true || status === 'true' ? true : false
        }),
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error updating seller status');
        }
    });
}

function deleteSeller(id) {
    if (!confirmDelete('Are you sure you want to delete this seller? This action cannot be undone.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/sellers/${id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error deleting seller');
        }
    });
}

function bulkAction(action) {
    const selectedIds = $('.row-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        toastr.warning('Please select sellers first');
        return;
    }
    
    let message = `Are you sure you want to ${action} ${selectedIds.length} seller(s)?`;
    if (action === 'delete') {
        message += ' This action cannot be undone.';
    }
    
    if (!confirm(message)) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '{{ route("admin.sellers.bulk-action") }}',
        method: 'POST',
        data: {
            action: action,
            ids: selectedIds
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error performing bulk action');
        }
    });
}
</script>
@endpush