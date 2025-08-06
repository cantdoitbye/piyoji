@extends('admin.layouts.app')

@section('title', 'Billing Companies')
@section('subtitle', 'Manage billing companies and their information')

@section('breadcrumb')
    <li class="breadcrumb-item active">Billing Companies</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.billing-companies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Billing Company
        </a>
        <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">
            <span class="visually-hidden">Toggle Dropdown</span>
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="{{ route('admin.billing-companies.export') }}"><i class="fas fa-download me-2"></i>Export Data</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item" onclick="bulkAction('activate')"><i class="fas fa-check me-2"></i>Bulk Activate</button></li>
            <li><button class="dropdown-item" onclick="bulkAction('deactivate')"><i class="fas fa-times me-2"></i>Bulk Deactivate</button></li>
            <li><button class="dropdown-item text-danger" onclick="bulkAction('delete')"><i class="fas fa-trash me-2"></i>Bulk Delete</button></li>
        </ul>
    </div>
@endsection

@section('content')
<div class="row mb-4">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <div class="text-end">
                    <h3 class="mb-1">{{ $statistics['total'] ?? 0 }}</h3>
                    <p class="mb-0">Total Companies</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="text-end">
                    <h3 class="mb-1">{{ $statistics['active'] ?? 0 }}</h3>
                    <p class="mb-0">Active</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-warning text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <div class="text-end">
                    <h3 class="mb-1">{{ $statistics['by_type']['sellers'] ?? 0 }}</h3>
                    <p class="mb-0">Sellers</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="stats-card bg-info text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stats-icon bg-white bg-opacity-25">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                </div>
                <div class="text-end">
                    <h3 class="mb-1">{{ $statistics['by_type']['buyers'] ?? 0 }}</h3>
                    <p class="mb-0">Buyers</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ $filters['search'] ?? '' }}" 
                       placeholder="Company name, contact person, email...">
            </div>
            
            <div class="col-md-2">
                <label for="type" class="form-label">Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="">All Types</option>
                    @foreach($typeOptions as $key => $label)
                        <option value="{{ $key }}" {{ ($filters['type'] ?? '') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    @foreach($statusOptions as $key => $label)
                        <option value="{{ $key }}" {{ ($filters['status'] ?? '') === (string)$key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="state" class="form-label">State</label>
                <input type="text" class="form-control" id="state" name="state" 
                       value="{{ $filters['state'] ?? '' }}" placeholder="State">
            </div>
            
            <div class="col-md-2">
                <label for="per_page" class="form-label">Per Page</label>
                <select class="form-select" id="per_page" name="per_page">
                    <option value="15" {{ ($filters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ ($filters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($filters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ ($filters['per_page'] ?? 15) == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Billing Companies Table -->
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-building me-2"></i>Billing Companies
                <span class="badge bg-primary ms-2">{{ $billingCompanies->total() }}</span>
            </h5>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="selectAll">
                <label class="form-check-label" for="selectAll">
                    Select All
                </label>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th width="50">
                            <input type="checkbox" class="form-check-input" id="selectAllCheck">
                        </th>
                        <th>Company Details</th>
                        <th>Contact Information</th>
                        <th>Type & Status</th>
                        <th>Business Info</th>
                        <th>Addresses</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billingCompanies as $billingCompany)
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input row-select" value="{{ $billingCompany->id }}">
                        </td>
                        <td>
                            <div>
                                <strong class="text-primary">{{ $billingCompany->company_name }}</strong>
                                @if($billingCompany->remarks)
                                    <br><small class="text-muted">{{ Str::limit($billingCompany->remarks, 50) }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <i class="fas fa-user me-1"></i>{{ $billingCompany->contact_person }}<br>
                                <i class="fas fa-envelope me-1"></i>{{ $billingCompany->email }}<br>
                                <i class="fas fa-phone me-1"></i>{{ $billingCompany->phone }}
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $billingCompany->type === 'seller' ? 'warning' : ($billingCompany->type === 'buyer' ? 'info' : 'secondary') }}">
                                {{ $billingCompany->type_text }}
                            </span>
                            <br>
                            <span class="badge bg-{{ $billingCompany->status ? 'success' : 'danger' }} mt-1">
                                {{ $billingCompany->status_text }}
                            </span>
                        </td>
                        <td>
                            <div class="small">
                                @if($billingCompany->gstin)
                                    <strong>GSTIN:</strong> {{ $billingCompany->formatted_gstin }}<br>
                                @endif
                                @if($billingCompany->pan)
                                    <strong>PAN:</strong> {{ $billingCompany->formatted_pan }}
                                @endif
                                @if(!$billingCompany->gstin && !$billingCompany->pan)
                                    <span class="text-muted">Not provided</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="small">
                                <strong>Billing:</strong> {{ $billingCompany->billing_city }}, {{ $billingCompany->billing_state }}
                                @if($billingCompany->canHaveShippingAddresses())
                                    <br><strong>Shipping:</strong> 
                                    <span class="badge bg-light text-dark">{{ $billingCompany->getShippingAddressesCount() }} addresses</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.billing-companies.show', $billingCompany->id) }}" 
                                   class="btn btn-outline-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.billing-companies.edit', $billingCompany->id) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-outline-{{ $billingCompany->status ? 'warning' : 'success' }}" 
                                        onclick="toggleStatus({{ $billingCompany->id }}, {{ $billingCompany->status ? 'false' : 'true' }})"
                                        title="{{ $billingCompany->status ? 'Deactivate' : 'Activate' }}">
                                    <i class="fas fa-{{ $billingCompany->status ? 'pause' : 'play' }}"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" 
                                        onclick="deleteBillingCompany({{ $billingCompany->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-building fa-3x mb-3"></i>
                                <h5>No billing companies found</h5>
                                <p>Start by creating your first billing company.</p>
                                <a href="{{ route('admin.billing-companies.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add New Billing Company
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($billingCompanies->hasPages())
    <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Showing {{ $billingCompanies->firstItem() }} to {{ $billingCompanies->lastItem() }} of {{ $billingCompanies->total() }} results
            </div>
            {{ $billingCompanies->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Filter form submission
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        window.location.href = '{{ route("admin.billing-companies.index") }}?' + formData;
    });

    // Auto-submit on filter change
    $('#type, #status, #per_page').on('change', function() {
        $('#filterForm').submit();
    });

    // Select all functionality
    $('#selectAllCheck').on('change', function() {
        $('.row-select').prop('checked', $(this).prop('checked'));
    });

    $('.row-select').on('change', function() {
        const totalRows = $('.row-select').length;
        const checkedRows = $('.row-select:checked').length;
        $('#selectAllCheck').prop('checked', totalRows === checkedRows);
    });
});

function toggleStatus(id, status) {
    const action = status ? 'activate' : 'deactivate';
    const message = status ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${message} this billing company?`)) {
        $.ajax({
            url: `{{ route('admin.billing-companies.index') }}/${id}/status`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while updating the status.');
            }
        });
    }
}

function deleteBillingCompany(id) {
    if (confirm('Are you sure you want to delete this billing company? This action cannot be undone.')) {
        $.ajax({
            url: `{{ route('admin.billing-companies.index') }}/${id}`,
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while deleting the billing company.');
            }
        });
    }
}

function bulkAction(action) {
    const selectedIds = $('.row-select:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('Please select at least one billing company.');
        return;
    }

    const actionText = action === 'delete' ? 'delete' : action;
    const confirmMessage = `Are you sure you want to ${actionText} ${selectedIds.length} billing company(ies)?`;
    
    if (confirm(confirmMessage)) {
        $.ajax({
            url: '{{ route("admin.billing-companies.bulk-action") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                action: action,
                ids: selectedIds
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while performing the bulk action.');
            }
        });
    }
}
</script>
@endpush