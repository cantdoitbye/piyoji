@extends('admin.layouts.app')

@section('title', 'Contract Management')
@section('subtitle', 'Manage your tea trading operations')

@section('breadcrumb')
    <li class="breadcrumb-item active">Contracts</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <button type="button" class="btn btn-outline-warning" id="expiryAlertsBtn">
            <i class="fas fa-bell me-1"></i> Expiry Alerts
        </button>
        <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add New Contract
        </a>
        <a href="{{ route('admin.contracts.export') }}" class="btn btn-outline-success">
            <i class="fas fa-download me-1"></i> Export
        </a>
    </div>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-primary me-3">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['total'] }}</h3>
                    <p class="text-muted mb-0">Total</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-success me-3">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['active'] }}</h3>
                    <p class="text-muted mb-0">Active</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-warning me-3">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['draft'] }}</h3>
                    <p class="text-muted mb-0">Draft</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-danger me-3">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['expired'] }}</h3>
                    <p class="text-muted mb-0">Expired</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-warning me-3">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['expiring_soon'] }}</h3>
                    <p class="text-muted mb-0">Expiring Soon</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="stats-card">
            <div class="d-flex align-items-center">
                <div class="stats-icon bg-info me-3">
                    <i class="fas fa-calendar"></i>
                </div>
                <div>
                    <h3 class="mb-0">{{ $statistics['recent'] }}</h3>
                    <p class="text-muted mb-0">Recent</p>
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
        <form method="GET" action="{{ route('admin.contracts.index') }}" id="filterForm">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}" placeholder="Search contracts...">
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
                    <label for="seller_id" class="form-label">Seller</label>
                    <select class="form-select" id="seller_id" name="seller_id">
                        <option value="">All Sellers</option>
                        @foreach($sellers as $seller)
                            <option value="{{ $seller->id }}" {{ ($filters['seller_id'] ?? '') == $seller->id ? 'selected' : '' }}>
                                {{ $seller->seller_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="tea_grade" class="form-label">Tea Grade</label>
                    <select class="form-select" id="tea_grade" name="tea_grade">
                        <option value="">All Grades</option>
                        @foreach($teaGradeOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['tea_grade'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
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
                <div class="col-md-3 mb-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-3 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="expiring_soon" name="expiring_soon" value="1"
                               {{ ($filters['expiring_soon'] ?? '') ? 'checked' : '' }}>
                        <label class="form-check-label" for="expiring_soon">
                            Expiring Soon (30 days)
                        </label>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Data Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Contracts</h5>
        <div class="dropdown">
            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-1"></i> Actions
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" id="bulkActivate">Bulk Activate</a></li>
                <li><a class="dropdown-item" href="#" id="bulkCancel">Bulk Cancel</a></li>
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
                        <th>Contract Info</th>
                        <th>Seller</th>
                        <th>Validity Period</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Days Left</th>
                        <th width="120">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contracts as $contract)
                    <tr class="{{ $contract->is_expiring ? 'table-warning' : '' }}">
                        <td>
                            <div class="form-check">
                                <input class="form-check-input contract-checkbox" type="checkbox" value="{{ $contract->id }}">
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $contract->contract_number }}</strong>
                                <div class="text-muted small">{{ Str::limit($contract->contract_title, 30) }}</div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>{{ $contract->seller->seller_name }}</strong>
                                <div class="text-muted small">{{ $contract->seller->contact_person }}</div>
                            </div>
                        </td>
                        <td>
                            <small>
                                <strong>From:</strong> {{ $contract->effective_date->format('M d, Y') }}<br>
                                <strong>To:</strong> {{ $contract->expiry_date->format('M d, Y') }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $contract->total_items }} Total</span>
                            @if($contract->active_items < $contract->total_items)
                                <br><span class="badge bg-success">{{ $contract->active_items }} Active</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge status-{{ $contract->status }}">
                                {{ $contract->status_text }}
                            </span>
                            @if($contract->is_expiring)
                                <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Expiring Soon</small>
                            @endif
                        </td>
                        <td>
                            @if($contract->status === 'active')
                                @if($contract->days_remaining !== null)
                                    @if($contract->days_remaining > 0)
                                        <span class="text-{{ $contract->days_remaining <= 30 ? 'warning' : 'success' }}">
                                            {{ $contract->days_remaining }} days
                                        </span>
                                    @else
                                        <span class="text-danger">Expired</span>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.contracts.show', $contract->id) }}" 
                                   class="btn btn-info btn-sm" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.contracts.edit', $contract->id) }}" 
                                   class="btn btn-warning btn-sm" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($contract->status === 'draft')
                                    <button type="button" class="btn btn-success btn-sm activate-contract" 
                                            data-id="{{ $contract->id }}" title="Activate">
                                        <i class="fas fa-play"></i>
                                    </button>
                                @endif
                                @if($contract->status === 'active')
                                    <button type="button" class="btn btn-secondary btn-sm cancel-contract" 
                                            data-id="{{ $contract->id }}" title="Cancel">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-danger btn-sm delete-contract" 
                                        data-id="{{ $contract->id }}" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No contracts found.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contracts->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $contracts->appends($filters)->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Expiry Alerts Modal -->
<div class="modal fade" id="expiryAlertsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Contract Expiry Alerts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="expiryAlertsContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading alerts...
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="sendAlertsBtn">Send Email Alerts</button>
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
                Are you sure you want to delete this contract?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.status-draft {
    background-color: #fff3cd;
    color: #856404;
}

.status-active {
    background-color: #d4edda;
    color: #155724;
}

.status-expired {
    background-color: #f8d7da;
    color: #721c24;
}

.status-cancelled {
    background-color: #e2e3e5;
    color: #383d41;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all checkbox
    $('#selectAll').change(function() {
        $('.contract-checkbox').prop('checked', this.checked);
    });

    // Activate contract
    $('.activate-contract').click(function() {
        const id = $(this).data('id');
        
        $.ajax({
            url: `/admin/contracts/${id}/activate`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    location.reload();
                } else {
                    toastr.error('Error activating contract');
                }
            }
        });
    });

    // Cancel contract
    $('.cancel-contract').click(function() {
        if (confirm('Are you sure you want to cancel this contract?')) {
            const id = $(this).data('id');
            
            $.ajax({
                url: `/admin/contracts/${id}/cancel`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error('Error cancelling contract');
                    }
                }
            });
        }
    });

    // Delete contract
    let deleteId = null;
    
    $('.delete-contract').click(function() {
        deleteId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        if (deleteId) {
            $.ajax({
                url: `/admin/contracts/${deleteId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        location.reload();
                    } else {
                        toastr.error('Error deleting contract');
                    }
                }
            });
        }
        $('#deleteModal').modal('hide');
    });

    // Expiry alerts
    $('#expiryAlertsBtn').click(function() {
        $('#expiryAlertsModal').modal('show');
        
        $.ajax({
            url: '{{ route("admin.contracts.expiry-alerts") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let content = '';
                    if (response.alerts.length > 0) {
                        content = '<div class="table-responsive"><table class="table table-sm">';
                        content += '<thead><tr><th>Contract</th><th>Seller</th><th>Expiry Date</th><th>Days Left</th></tr></thead><tbody>';
                        
                        response.alerts.forEach(function(alert) {
                            content += `<tr>
                                <td><strong>${alert.contract_number}</strong><br><small>${alert.contract_title}</small></td>
                                <td>${alert.seller.seller_name}</td>
                                <td>${new Date(alert.expiry_date).toLocaleDateString()}</td>
                                <td><span class="badge bg-warning">${alert.days_remaining} days</span></td>
                            </tr>`;
                        });
                        
                        content += '</tbody></table></div>';
                    } else {
                        content = '<div class="text-center text-muted">No contracts expiring soon.</div>';
                    }
                    
                    $('#expiryAlertsContent').html(content);
                }
            }
        });
    });

    // Send alerts
    $('#sendAlertsBtn').click(function() {
        $.ajax({
            url: '{{ route("admin.contracts.send-expiry-alerts") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#expiryAlertsModal').modal('hide');
                } else {
                    toastr.error('Error sending alerts');
                }
            }
        });
    });

    // Bulk actions
    function getBulkIds() {
        return $('.contract-checkbox:checked').map(function() {
            return this.value;
        }).get();
    }

    $('#bulkActivate').click(function(e) {
        e.preventDefault();
        const ids = getBulkIds();
        if (ids.length === 0) {
            toastr.warning('Please select contracts to activate');
            return;
        }
        bulkAction('activate', ids);
    });

    $('#bulkCancel').click(function(e) {
        e.preventDefault();
        const ids = getBulkIds();
        if (ids.length === 0) {
            toastr.warning('Please select contracts to cancel');
            return;
        }
        if (confirm('Are you sure you want to cancel selected contracts?')) {
            bulkAction('cancel', ids);
        }
    });

    $('#bulkDelete').click(function(e) {
        e.preventDefault();
        const ids = getBulkIds();
        if (ids.length === 0) {
            toastr.warning('Please select contracts to delete');
            return;
        }
        if (confirm('Are you sure you want to delete selected contracts?')) {
            bulkAction('delete', ids);
        }
    });

    function bulkAction(action, ids) {
        $.ajax({
            url: '{{ route("admin.contracts.bulk-action") }}',
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
    $('#status, #seller_id, #tea_grade, #per_page, #expiring_soon').change(function() {
        $('#filterForm').submit();
    });
});
</script>
@endpush