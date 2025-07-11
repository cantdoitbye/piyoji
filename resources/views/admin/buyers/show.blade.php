@extends('admin.layouts.app')

@section('title', 'Buyer Details')
@section('subtitle', 'View buyer information and purchase history')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.buyers.index') }}">Buyers</a></li>
    <li class="breadcrumb-item active">{{ $buyer->buyer_name }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.buyers.edit', $buyer->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Buyer
        </a>
        <button type="button" class="btn btn-outline-{{ $buyer->status ? 'warning' : 'success' }}" 
                onclick="toggleStatus({{ $buyer->id }}, {{ $buyer->status ? 'false' : 'true' }})">
            <i class="fas fa-{{ $buyer->status ? 'pause' : 'play' }} me-1"></i>
            {{ $buyer->status ? 'Deactivate' : 'Activate' }}
        </button>
        <a href="{{ route('admin.buyers.index') }}" class="btn btn-outline-secondary">
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
                        <label class="form-label text-muted">Buyer Name</label>
                        <div class="fw-bold">{{ $buyer->buyer_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Buyer Type</label>
                        <div>
                            <span class="badge bg-{{ $buyer->buyer_type === 'big' ? 'primary' : 'secondary' }} fs-6">
                                <i class="fas fa-{{ $buyer->buyer_type === 'big' ? 'building' : 'user' }} me-1"></i>
                                {{ $buyer->buyer_type_text }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Contact Person</label>
                        <div>{{ $buyer->contact_person }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="status-badge {{ $buyer->status ? 'status-active' : 'status-inactive' }}">
                                <i class="fas fa-{{ $buyer->status ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                {{ $buyer->status_text }}
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
                            <a href="mailto:{{ $buyer->email }}" class="text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>{{ $buyer->email }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <div>
                            <a href="tel:{{ $buyer->phone }}" class="text-decoration-none">
                                <i class="fas fa-phone me-1"></i>{{ $buyer->phone }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Billing Address -->
                    <div class="col-md-6 mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-file-invoice me-1"></i>Billing Address
                        </h6>
                        <div class="border rounded p-3 bg-light">
                            <div>{{ $buyer->billing_address }}</div>
                            <div class="mt-2">
                                <strong>{{ $buyer->billing_city }}, {{ $buyer->billing_state }}</strong><br>
                                <span class="text-muted">PIN: {{ $buyer->billing_pincode }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Address -->
                    <div class="col-md-6 mb-4">
                        <h6 class="text-success mb-3">
                            <i class="fas fa-shipping-fast me-1"></i>Shipping Address
                        </h6>
                        <div class="border rounded p-3 bg-light">
                            <div>{{ $buyer->shipping_address }}</div>
                            <div class="mt-2">
                                <strong>{{ $buyer->shipping_city }}, {{ $buyer->shipping_state }}</strong><br>
                                <span class="text-muted">PIN: {{ $buyer->shipping_pincode }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tea Preferences -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-leaf me-2"></i>Tea Preferences</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Preferred Tea Grades</label>
                        <div>
                            @if(is_array($buyer->preferred_tea_grades))
                                @foreach($buyer->preferred_tea_grades as $grade)
                                    <span class="badge bg-success me-1 mb-1">{{ $grade }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No tea grade preferences specified</span>
                            @endif
                        </div>
                    </div>
                    @if($buyer->remarks)
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Remarks</label>
                        <div class="border rounded p-3 bg-light">{{ $buyer->remarks }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Purchase History -->
        <!-- Note: Purchase functionality will be available in Module 4 -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Purchase History</h5>
                <span class="badge bg-secondary">Coming Soon</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                    {{-- <p class="text-muted">Purchase tracking will be available in Module 4</p> --}}
                    <small class="text-muted">Track orders, quantities, and payments</small>
                </div>
            </div>
        </div>

        <!-- Feedback History -->
        <!-- Note: Feedback functionality will be available in Module 3 -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-comments me-2"></i>Feedback History</h5>
                <span class="badge bg-secondary">Coming Soon</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                    {{-- <p class="text-muted">Feedback management will be available in Module 3</p> --}}
                    <small class="text-muted">Track sample feedback and lab test results</small>
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
                            <small class="text-muted">Total Orders</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-0">0</h4>
                        <small class="text-muted">Feedbacks</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-info mb-0">₹0</h4>
                            <small class="text-muted">Total Spent</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning mb-0">0</h4>
                        <small class="text-muted">Satisfied</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buyer Type Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user-tag me-2"></i>Buyer Type Information</h6>
            </div>
            <div class="card-body">
                @if($buyer->buyer_type === 'big')
                <div class="alert alert-primary">
                    <h6><i class="fas fa-building me-2"></i>Big Buyer</h6>
                    <ul class="mb-0 small">
                        <li>Detailed sample evaluation process</li>
                        <li>Lab testing requirements</li>
                        <li>Bulk purchase capabilities</li>
                        <li>Extended feedback timeline</li>
                    </ul>
                </div>
                @else
                <div class="alert alert-secondary">
                    <h6><i class="fas fa-user me-2"></i>Small Buyer</h6>
                    <ul class="mb-0 small">
                        <li>Simplified feedback process</li>
                        <li>Quick decision making</li>
                        <li>Direct communication</li>
                        <li>No lab testing required</li>
                    </ul>
                </div>
                @endif
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
                    <div>{{ $buyer->created_at->format('F j, Y') }}</div>
                    <small class="text-muted">{{ $buyer->created_at->diffForHumans() }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Last Updated</label>
                    <div>{{ $buyer->updated_at->format('F j, Y') }}</div>
                    <small class="text-muted">{{ $buyer->updated_at->diffForHumans() }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Account Status</label>
                    <div>
                        <span class="status-badge {{ $buyer->status ? 'status-active' : 'status-inactive' }}">
                            {{ $buyer->status_text }}
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
                    <a href="{{ route('admin.buyers.edit', $buyer->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Buyer
                    </a>
                    <a href="#" class="btn btn-outline-success">
                        <i class="fas fa-paper-plane me-2"></i>Send Sample
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="fas fa-comments me-2"></i>View Feedback
                    </a>
                    <button type="button" class="btn btn-outline-{{ $buyer->status ? 'warning' : 'success' }}" 
                            onclick="toggleStatus({{ $buyer->id }}, {{ $buyer->status ? 'false' : 'true' }})">
                        <i class="fas fa-{{ $buyer->status ? 'pause' : 'play' }} me-2"></i>
                        {{ $buyer->status ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteBuyer({{ $buyer->id }})">
                        <i class="fas fa-trash me-2"></i>Delete Buyer
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
    if (!confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this buyer?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/buyers/${id}/status`,
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
                window.location.href = '{{ route("admin.buyers.index") }}';
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
</script>
@endpush