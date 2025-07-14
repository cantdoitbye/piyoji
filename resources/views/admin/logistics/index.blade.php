@extends('admin.layouts.app')

@section('title', 'Logistic Company Management')
@section('subtitle', 'Manage your tea trading operations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Logistic Companies</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.logistics.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Logistic Company
        </a>
        <a href="{{ route('admin.logistics.export') }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-primary me-3">
                    <i class="fas fa-truck"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['total'] }}</h3>
                    <p class="text-muted mb-0">Total Companies</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-success me-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['active'] }}</h3>
                    <p class="text-muted mb-0">Active Companies</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-warning me-3">
                    <i class="fas fa-weight"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['per_kg_pricing'] }}</h3>
                    <p class="text-muted mb-0">Per Kg Pricing</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-info me-3">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['recent'] }}</h3>
                    <p class="text-muted mb-0">Recent (30 days)</p>
                </div>
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
        <form method="GET" action="{{ route('admin.logistics.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}" placeholder="Search companies...">
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
                    <label for="pricing_type" class="form-label">Pricing Type</label>
                    <select class="form-select" id="pricing_type" name="pricing_type">
                        <option value="">All Types</option>
                        @foreach($pricingTypeOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['pricing_type'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="region" class="form-label">Region</label>
                    <select class="form-select" id="region" name="region">
                        <option value="">All Regions</option>
                        @foreach($regionOptions as $region)
                            <option value="{{ $region }}" {{ ($filters['region'] ?? '') == $region ? 'selected' : '' }}>
                                {{ $region }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="per_page" class="form-label">Per Page</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($filters['per_page'] ?? 15) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.logistics.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Logistic Companies</h5>
        <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-1"></i> Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" id="bulkActivate">Bulk Activate</a></li>
                <li><a class="dropdown-item" href="#" id="bulkDeactivate">Bulk Deactivate</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#" id="bulkDelete">Bulk Delete</a></li>
            </ul>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="dataTable">
                <thead>
                    <tr>
                        <th width="50">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                            </div>
                        </th>
                        <th>Company Name</th>
                        <th>Contact Person</th>
                        <th>Phone</th>
                        <th>Location</th>
                        <th>Pricing Type</th>
                        <th>Regions</th>
                        <th>Status</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                    <tr>
                        <td>
                            <div class="form-check">
                                <input class="form-check-input company-checkbox" type="checkbox" value="{{ $company->id }}">
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $company->company_name }}</strong>
                                <div class="text-muted small">{{ $company->email }}</div>
                            </div>
                        </td>
                        <td>{{ $company->contact_person }}</td>
                        <td>{{ $company->phone }}</td>
                        <td>{{ $company->city }}, {{ $company->state }}</td>
                        <td>
                            <span class="badge bg-info">{{ $company->pricing_type_text }}</span>
                            <div class="text-muted small">{{ $company->formatted_pricing }}</div>
                        </td>
                        <td>
                            <small>{{ Str::limit($company->supported_regions_text, 50) }}</small>
                        </td>
                        <td>
                            <span class="status-badge status-{{ $company->status ? 'active' : 'inactive' }}">
                                {{ $company->status_text }}
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.logistics.show', $company->id) }}" 
                                   class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.logistics.edit', $company->id) }}" 
                                   class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-{{ $company->status ? 'secondary' : 'success' }} btn-sm toggle-status" 
                                        data-id="{{ $company->id }}" data-status="{{ $company->status ? 0 : 1 }}" 
                                        title="{{ $company->status ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $company->status ? 'ban' : 'check' }}"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm delete-company" 
                                        data-id="{{ $company->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No logistic companies found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($companies->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $companies->appends($filters)->links() }}
            </div>
        @endif
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
                Are you sure you want to delete this logistic company?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.company-checkbox').prop('checked', this.checked);
    });

    // Toggle status
    $('.toggle-status').click(function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        
        $.ajax({
            url: `/admin/logistics/${id}/status`,
            method: 'PATCH',
            data: {
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    toastr.error('Error updating status');
                }
            }
        });
    });

    // Delete company
    let deleteId = null;
    
    $('.delete-company').click(function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        if (deleteId) {
            $.ajax({
                url: `/admin/logistics/${deleteId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        toastr.error('Error deleting company');
                    }
                }
            });
        }
        $('#deleteModal').modal('hide');
    });

    // Bulk actions
    function getBulkIds() {
        return $('.company-checkbox:checked').map(function() {
            return this.value;
        }).get();
    }

    $('#bulkActivate').click(function(e) {
        e.preventDefault();
        const ids = getBulkIds();
        if (ids.length === 0) {
            toastr.warning('Please select companies to activate');
            return;
        }
        bulkAction('activate', ids);
    });

    $('#bulkDeactivate').click(function(e) {
        e.preventDefault();
        const ids = getBulkIds();
        if (ids.length === 0) {
            toastr.warning('Please select companies to deactivate');
            return;
        }
        bulkAction('deactivate', ids);
    });

    $('#bulkDelete').click(function(e) {
        e.preventDefault();
        const ids = getBulkIds();
        if (ids.length === 0) {
            toastr.warning('Please select companies to delete');
            return;
        }
        if (confirm('Are you sure you want to delete selected companies?')) {
            bulkAction('delete', ids);
        }
    });

    function bulkAction(action, ids) {
        $.ajax({
            url: '{{ route("admin.logistics.bulk-action") }}',
            method: 'POST',
            data: {
                action: action,
                ids: ids,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error('Error performing bulk action');
                }
            }
        });
    }

    // Auto-submit filter form on change
    $('#status, #pricing_type, #region, #per_page').change(function() {
        $('#filterForm').submit();
    });
});
</script>
@endpush