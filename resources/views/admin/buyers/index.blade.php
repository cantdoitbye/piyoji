@extends('admin.layouts.app')

@section('title', 'Buyers')
@section('subtitle', 'Manage tea buyers and their information')

@section('breadcrumb')
    <li class="breadcrumb-item active">Buyers</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.buyers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Buyer
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
                    <p class="text-muted mb-0">Total Buyers</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-users"></i>
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
                    <p class="text-muted mb-0">Active Buyers</p>
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
                    <h3 class="text-info mb-0">{{ $statistics['big_buyers'] }}</h3>
                    <p class="text-muted mb-0">Big Buyers</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-warning mb-0">{{ $statistics['small_buyers'] }}</h3>
                    <p class="text-muted mb-0">Small Buyers</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.buyers.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" id="search" 
                           placeholder="Search buyers..." value="{{ $filters['search'] ?? '' }}">
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
                    <label for="buyer_type" class="form-label">Buyer Type</label>
                    <select class="form-select" name="buyer_type" id="buyer_type">
                        <option value="">All Types</option>
                        @foreach($buyerTypes as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['buyer_type'] ?? '') == $value ? 'selected' : '' }}>
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
                    <a href="{{ route('admin.buyers.index') }}" class="btn btn-outline-secondary">
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
                <span id="selectedCount">0</span> buyer(s) selected
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

<!-- Buyers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Buyers List</h5>
        <div class="action-buttons">
            <a href="{{ route('admin.buyers.export') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-download me-1"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="buyersTable">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Buyer Name</th>
                        <th>Type</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Preferred Grades</th>
                        <th>Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($buyers as $buyer)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $buyer->id }}">
                            </td>
                            <td>
                                <strong>{{ $buyer->buyer_name }}</strong><br>
                                <small class="text-muted">{{ $buyer->buyer_type_text }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $buyer->buyer_type === 'big' ? 'primary' : 'secondary' }}">
                                    {{ $buyer->buyer_type_text }}
                                </span>
                            </td>
                            <td>{{ $buyer->contact_person }}</td>
                            <td>
                                <a href="mailto:{{ $buyer->email }}">{{ $buyer->email }}</a>
                            </td>
                            <td>
                                <a href="tel:{{ $buyer->phone }}">{{ $buyer->phone }}</a>
                            </td>
                            <td>
                                <strong>Billing:</strong> {{ $buyer->billing_city }}<br>
                                <small class="text-muted">Shipping: {{ $buyer->shipping_city }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $buyer->preferred_tea_grades_text }}</span>
                            </td>
                            <td>
                                <span class="status-badge {{ $buyer->status ? 'status-active' : 'status-inactive' }}">
                                    {{ $buyer->status_text }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.buyers.show', $buyer->id) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.buyers.edit', $buyer->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-{{ $buyer->status ? 'warning' : 'success' }}" 
                                            onclick="toggleStatus({{ $buyer->id }}, {{ $buyer->status ? 'false' : 'true' }})" 
                                            title="{{ $buyer->status ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $buyer->status ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteBuyer({{ $buyer->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No buyers found</p>
                                <a href="{{ route('admin.buyers.create') }}" class="btn btn-primary">
                                    Add First Buyer
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($buyers->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing {{ $buyers->firstItem() }} to {{ $buyers->lastItem() }} of {{ $buyers->total() }} results
                </div>
                <div>
                    {{ $buyers->appends(request()->query())->links() }}
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
    $('#status, #buyer_type, #tea_grade, #per_page').change(function() {
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
    if (!confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this buyer?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/buyers/${id}/status`,
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
            toastr.error('Error updating buyer status');
        }
    });
}

function deleteBuyer(id) {
    if (!confirmDelete('Are you sure you want to delete this buyer? This action cannot be undone.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/buyers/${id}`,
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
            toastr.error('Error deleting buyer');
        }
    });
}

function bulkAction(action) {
    const selectedIds = $('.row-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        toastr.warning('Please select buyers first');
        return;
    }
    
    let message = `Are you sure you want to ${action} ${selectedIds.length} buyer(s)?`;
    if (action === 'delete') {
        message += ' This action cannot be undone.';
    }
    
    if (!confirm(message)) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '{{ route("admin.buyers.bulk-action") }}',
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