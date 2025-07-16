@extends('admin.layouts.app')

@section('title', 'Courier Services')
@section('subtitle', 'Manage courier services and shipping partners')

@section('breadcrumb')
    <li class="breadcrumb-item active">Courier Services</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Courier Service
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
                    <p class="text-muted mb-0">Total Courier Services</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-shipping-fast"></i>
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
                    <p class="text-muted mb-0">Active Services</p>
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
                    <h3 class="text-info mb-0">{{ $statistics['with_api'] }}</h3>
                    <p class="text-muted mb-0">API Integrated</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-info">
                        <i class="fas fa-code"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="text-warning mb-0">{{ $statistics['recent'] }}</h3>
                    <p class="text-muted mb-0">Added This Month</p>
                </div>
                <div class="col-auto">
                    <div class="stats-icon bg-warning">
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
        <form method="GET" action="{{ route('admin.couriers.index') }}" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" id="search" 
                           placeholder="Search courier services..." value="{{ $filters['search'] ?? '' }}">
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
                    <label for="service_area" class="form-label">Service Area</label>
                    <select class="form-select" name="service_area" id="service_area">
                        <option value="">All Areas</option>
                        @foreach($serviceAreas as $area)
                            <option value="{{ $area }}" {{ ($filters['service_area'] ?? '') == $area ? 'selected' : '' }}>
                                {{ $area }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="has_api" class="form-label">API Integration</label>
                    <select class="form-select" name="has_api" id="has_api">
                        <option value="">All</option>
                        <option value="1" {{ ($filters['has_api'] ?? '') == '1' ? 'selected' : '' }}>With API</option>
                        <option value="0" {{ ($filters['has_api'] ?? '') == '0' ? 'selected' : '' }}>Without API</option>
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
                    <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
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
                <span id="selectedCount">0</span> courier service(s) selected
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

<!-- Couriers Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Courier Services List</h5>
        <div class="action-buttons">
            <a href="{{ route('admin.couriers.export') }}" class="btn btn-outline-success btn-sm">
                <i class="fas fa-download me-1"></i> Export
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="couriersTable">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Service Areas</th>
                        <th>API Integration</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($couriers as $courier)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $courier->id }}">
                            </td>
                            <td>
                                <strong>{{ $courier->company_name }}</strong><br>
                                <small class="text-muted">
                                    @if($courier->api_endpoint)
                                        <i class="fas fa-code text-success" title="API Integrated"></i>
                                    @endif
                                    Courier Service
                                </small>
                            </td>
                            <td>{{ $courier->contact_person }}</td>
                            <td>
                                <a href="mailto:{{ $courier->email }}">{{ $courier->email }}</a>
                            </td>
                            <td>
                                <a href="tel:{{ $courier->phone }}">{{ $courier->phone }}</a>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $courier->service_areas_text }}</span>
                            </td>
                            <td>
                                @if($courier->api_endpoint)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check me-1"></i>Integrated
                                    </span>
                                    <br>
                                    <button type="button" class="btn btn-sm btn-outline-info mt-1" 
                                            onclick="testApi({{ $courier->id }})" title="Test API">
                                        <i class="fas fa-vial"></i> Test
                                    </button>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-times me-1"></i>Manual
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="status-badge {{ $courier->status ? 'status-active' : 'status-inactive' }}">
                                    {{ $courier->status_text }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.couriers.show', $courier->id) }}" class="btn btn-outline-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.couriers.edit', $courier->id) }}" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-{{ $courier->status ? 'warning' : 'success' }}" 
                                            onclick="toggleStatus({{ $courier->id }}, {{ $courier->status ? 'false' : 'true' }})" 
                                            title="{{ $courier->status ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $courier->status ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteCourier({{ $courier->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-shipping-fast fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No courier services found</p>
                                <a href="{{ route('admin.couriers.create') }}" class="btn btn-primary">
                                    Add First Courier Service
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($couriers->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div>
                    Showing {{ $couriers->firstItem() }} to {{ $couriers->lastItem() }} of {{ $couriers->total() }} results
                </div>
                <div>
                    {{ $couriers->appends(request()->query())->links() }}
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
    $('#status, #service_area, #has_api, #per_page').change(function() {
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
    if (!confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this courier service?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/couriers/${id}/status`,
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
            toastr.error('Error updating courier service status');
        }
    });
}

function testApi(id) {
    showLoading();
    
    $.ajax({
        url: `/admin/couriers/${id}/test-api`,
        method: 'POST',
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error testing API connection');
        }
    });
}

function deleteCourier(id) {
    if (!confirmDelete('Are you sure you want to delete this courier service? This action cannot be undone.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/couriers/${id}`,
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
            toastr.error('Error deleting courier service');
        }
    });
}

function bulkAction(action) {
    const selectedIds = $('.row-checkbox:checked').map(function() {
        return this.value;
    }).get();
    
    if (selectedIds.length === 0) {
        toastr.warning('Please select courier services first');
        return;
    }
    
    let message = `Are you sure you want to ${action} ${selectedIds.length} courier service(s)?`;
    if (action === 'delete') {
        message += ' This action cannot be undone.';
    }
    
    if (!confirm(message)) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: '{{ route("admin.couriers.bulk-action") }}',
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