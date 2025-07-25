@extends('admin.layouts.app')

@section('title', 'POC Details')
@section('subtitle', 'View Point of Contact information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pocs.index') }}">POCs</a></li>
    <li class="breadcrumb-item active">{{ $poc->poc_name }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.pocs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
        <a href="{{ route('admin.pocs.edit', $poc->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit POC
        </a>
        <button type="button" class="btn btn-outline-{{ $poc->status ? 'warning' : 'success' }}"
                onclick="toggleStatus({{ $poc->id }}, {{ $poc->status ? 'false' : 'true' }})">
            <i class="fas fa-{{ $poc->status ? 'pause' : 'play' }} me-1"></i>
            {{ $poc->status ? 'Deactivate' : 'Activate' }}
        </button>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>Basic Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">POC Name</label>
                        <div class="fw-bold">{{ $poc->poc_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Designation</label>
                        <div class="fw-bold">{{ $poc->designation ?: 'Not specified' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">POC Type</label>
                        <div>
                            <span class="badge bg-{{ $poc->poc_type === 'both' ? 'primary' : ($poc->poc_type === 'seller' ? 'info' : 'secondary') }}">
                                {{ $poc->poc_type_text }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="badge bg-{{ $poc->status ? 'success' : 'danger' }}">
                                <i class="fas fa-{{ $poc->status ? 'check-circle' : 'times-circle' }} me-1"></i>
                                {{ $poc->status_text }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-phone me-2"></i>Contact Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email Address</label>
                        <div class="fw-bold">
                            <a href="mailto:{{ $poc->email }}" class="text-decoration-none">
                                {{ $poc->email }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <div class="fw-bold">
                            <a href="tel:{{ $poc->phone }}" class="text-decoration-none">
                                {{ $poc->phone }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        @if($poc->address || $poc->city || $poc->state || $poc->pincode)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Address Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($poc->address)
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Address</label>
                        <div class="fw-bold">{{ $poc->address }}</div>
                    </div>
                    @endif
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">City</label>
                        <div class="fw-bold">{{ $poc->city ?: 'Not specified' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">State</label>
                        <div class="fw-bold">{{ $poc->state ?: 'Not specified' }}</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label text-muted">Pincode</label>
                        <div class="fw-bold">{{ $poc->pincode ?: 'Not specified' }}</div>
                    </div>
                </div>
                
                @if($poc->full_address)
                <div class="mt-3">
                    <label class="form-label text-muted">Complete Address</label>
                    <div class="alert alert-light mb-0">
                        <i class="fas fa-map-pin me-2"></i>{{ $poc->full_address }}
                    </div>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Remarks -->
        @if($poc->remarks)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-sticky-note me-2"></i>Remarks
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $poc->remarks }}</p>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.pocs.edit', $poc->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit POC
                    </a>
                    <button type="button" class="btn btn-outline-{{ $poc->status ? 'warning' : 'success' }}"
                            onclick="toggleStatus({{ $poc->id }}, {{ $poc->status ? 'false' : 'true' }})">
                        <i class="fas fa-{{ $poc->status ? 'pause' : 'play' }} me-2"></i>
                        {{ $poc->status ? 'Deactivate' : 'Activate' }} POC
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="deletePoc({{ $poc->id }})">
                        <i class="fas fa-trash me-2"></i>Delete POC
                    </button>
                </div>
            </div>
        </div>

        <!-- POC Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>POC Details
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <div class="fw-bold text-primary fs-5">{{ $poc->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">Created Date</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <div class="fw-bold text-info fs-5">{{ $poc->updated_at->format('M d, Y') }}</div>
                            <small class="text-muted">Last Updated</small>
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Created:</span>
                        <span class="fw-bold">{{ $poc->created_at->format('M d, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Last Modified:</span>
                        <span class="fw-bold">{{ $poc->updated_at->format('M d, Y \a\t g:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Time Ago:</span>
                        <span class="fw-bold">{{ $poc->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- POC Type Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>POC Type Guide
                </h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="mb-3">
                        <span class="badge bg-info me-2">Seller</span>
                        <span>Handles seller companies only</span>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-secondary me-2">Buyer</span>
                        <span>Handles buyer companies only</span>
                    </div>
                    <div class="mb-0">
                        <span class="badge bg-primary me-2">Both</span>
                        <span>Can handle both sellers and buyers</span>
                    </div>
                </div>
            </div>
        </div>
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
                window.location.href = '/admin/pocs';
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