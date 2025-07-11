@extends('admin.layouts.app')

@section('title', 'Seller Details')
@section('subtitle', 'View seller information and history')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sellers.index') }}">Sellers</a></li>
    <li class="breadcrumb-item active">{{ $seller->seller_name }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Seller
        </a>
        <button type="button" class="btn btn-outline-{{ $seller->status ? 'warning' : 'success' }}" 
                onclick="toggleStatus({{ $seller->id }}, {{ $seller->status ? 'false' : 'true' }})">
            <i class="fas fa-{{ $seller->status ? 'pause' : 'play' }} me-1"></i>
            {{ $seller->status ? 'Deactivate' : 'Activate' }}
        </button>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
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
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Seller Name</label>
                        <div class="fw-bold">{{ $seller->seller_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Tea Estate/Garden Name</label>
                        <div class="fw-bold">{{ $seller->tea_estate_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Contact Person</label>
                        <div>{{ $seller->contact_person }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="status-badge {{ $seller->status ? 'status-active' : 'status-inactive' }}">
                                <i class="fas fa-{{ $seller->status ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                {{ $seller->status_text }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-phone me-2"></i>Contact Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email Address</label>
                        <div>
                            <a href="mailto:{{ $seller->email }}" class="text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>{{ $seller->email }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <div>
                            <a href="tel:{{ $seller->phone }}" class="text-decoration-none">
                                <i class="fas fa-phone me-1"></i>{{ $seller->phone }}
                            </a>
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <div>{{ $seller->address }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">City</label>
                        <div>{{ $seller->city }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">State</label>
                        <div>{{ $seller->state }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Pincode</label>
                        <div>{{ $seller->pincode }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Business Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">GSTIN</label>
                        <div class="fw-bold">{{ $seller->gstin }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">PAN</label>
                        <div class="fw-bold">{{ $seller->pan }}</div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Tea Grades Handled</label>
                        <div>
                            @if(is_array($seller->tea_grades))
                                @foreach($seller->tea_grades as $grade)
                                    <span class="badge bg-primary me-1 mb-1">{{ $grade }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No tea grades specified</span>
                            @endif
                        </div>
                    </div>
                    @if($seller->remarks)
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Remarks</label>
                        <div class="border rounded p-3 bg-light">{{ $seller->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Contracts History -->
        <!-- Note: Contract functionality will be available in Module 1.5 -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Contract History</h5>
                <span class="badge bg-secondary">Coming Soon</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    {{-- <p class="text-muted">Contract management will be available in Module 1.5</p> --}}
                    <small class="text-muted">Track seller contracts, pricing, and terms</small>
                </div>
            </div>
        </div>

        <!-- Note: Sample functionality will be available in Module 2 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Recent Samples</h5>
                <span class="badge bg-secondary">Coming Soon</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                    {{-- <p class="text-muted">Sample management will be available in Module 2</p> --}}
                    <small class="text-muted">Track samples, evaluations, and scores</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-primary mb-0">0</h4>
                            <small class="text-muted">Contracts</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-0">0</h4>
                        <small class="text-muted">Samples</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-info mb-0">0</h4>
                            <small class="text-muted">Avg Score</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning mb-0">0</h4>
                        <small class="text-muted">Approved</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Account Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Created Date</label>
                    <div>{{ $seller->created_at->format('F j, Y') }}</div>
                    <small class="text-muted">{{ $seller->created_at->diffForHumans() }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Last Updated</label>
                    <div>{{ $seller->updated_at->format('F j, Y') }}</div>
                    <small class="text-muted">{{ $seller->updated_at->diffForHumans() }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Account Status</label>
                    <div>
                        <span class="status-badge {{ $seller->status ? 'status-active' : 'status-inactive' }}">
                            {{ $seller->status_text }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Seller
                    </a>
                    <a href="#" class="btn btn-outline-success">
                        <i class="fas fa-plus me-2"></i>Add Contract
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="fas fa-flask me-2"></i>View Samples
                    </a>
                    <button type="button" class="btn btn-outline-{{ $seller->status ? 'warning' : 'success' }}" 
                            onclick="toggleStatus({{ $seller->id }}, {{ $seller->status ? 'false' : 'true' }})">
                        <i class="fas fa-{{ $seller->status ? 'pause' : 'play' }} me-2"></i>
                        {{ $seller->status ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteSeller({{ $seller->id }})">
                        <i class="fas fa-trash me-2"></i>Delete Seller
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(id, status) {
    if (!confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this seller?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/sellers/${id}/status`,
        method: 'PATCH',
        data: { status: status },
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
                window.location.href = '{{ route("admin.sellers.index") }}';
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
</script>
@endpush