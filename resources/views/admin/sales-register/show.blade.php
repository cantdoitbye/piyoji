@extends('admin.layouts.app')

@section('title', 'Sales Entry Details - ' . $salesEntry->sales_entry_id)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sales Entry Details</h1>
            <p class="text-muted">{{ $salesEntry->sales_entry_id }} - {{ $salesEntry->entry_date->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales-register.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sales Register
            </a>
            
            @if($salesEntry->status === 'pending')
            <a href="{{ route('admin.sales-register.edit', $salesEntry->id) }}" class="btn btn-outline-warning">
                <i class="fas fa-edit me-1"></i> Edit Entry
            </a>
            
            <button type="button" class="btn btn-success" onclick="approveEntry({{ $salesEntry->id }})">
                <i class="fas fa-check me-1"></i> Approve
            </button>
            
            <button type="button" class="btn btn-danger" onclick="rejectEntry({{ $salesEntry->id }})">
                <i class="fas fa-times me-1"></i> Reject
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Sales Entry Information -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Sales Entry Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Entry ID:</td>
                                    <td>{{ $salesEntry->sales_entry_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Product Name:</td>
                                    <td>{{ $salesEntry->product_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tea Grade:</td>
                                    <td>{{ $salesEntry->tea_grade }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Entry Date:</td>
                                    <td>{{ $salesEntry->entry_date->format('M d, Y (l)') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td>
                                        <span class="badge {{ $salesEntry->status_badge_class }}">
                                            {{ $salesEntry->status_label }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Quantity:</td>
                                    <td>{{ $salesEntry->quantity_kg }} kg</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Rate per KG:</td>
                                    <td>{{ $salesEntry->formatted_rate_per_kg }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Amount:</td>
                                    <td><strong class="text-success">{{ $salesEntry->formatted_total_amount }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Created By:</td>
                                    <td>{{ $salesEntry->createdBy ? $salesEntry->createdBy->name : 'System' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Created At:</td>
                                    <td>{{ $salesEntry->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($salesEntry->remarks)
                    <div class="mt-3">
                        <h6 class="fw-bold">Remarks:</h6>
                        <div class="alert alert-info">
                            {{ $salesEntry->remarks }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Buyer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-tie me-2"></i>Buyer Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Buyer Name:</td>
                                    <td>{{ $salesEntry->buyer->buyer_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Buyer Type:</td>
                                    <td>
                                        <span class="badge {{ $salesEntry->buyer->buyer_type === 'big' ? 'bg-success' : 'bg-info' }}">
                                            {{ ucfirst($salesEntry->buyer->buyer_type) }} Client
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Contact Person:</td>
                                    <td>{{ $salesEntry->buyer->contact_person }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Email:</td>
                                    <td>{{ $salesEntry->buyer->email }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Phone:</td>
                                    <td>{{ $salesEntry->buyer->phone }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold">Billing Address:</h6>
                            <address class="text-muted">
                                {{ $salesEntry->buyer->billing_address }}<br>
                                {{ $salesEntry->buyer->billing_city }}, {{ $salesEntry->buyer->billing_state }}<br>
                                {{ $salesEntry->buyer->billing_pincode }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>

            @if($salesEntry->status !== 'pending')
            <!-- Status History -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Status History</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Entry Created</h6>
                                <p class="timeline-description">
                                    Sales entry created by {{ $salesEntry->createdBy ? $salesEntry->createdBy->name : 'System' }}
                                </p>
                                <small class="text-muted">{{ $salesEntry->created_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        
                        @if($salesEntry->status === 'approved')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Entry Approved</h6>
                                <p class="timeline-description">
                                    Approved by {{ $salesEntry->approvedBy ? $salesEntry->approvedBy->name : 'System' }}
                                    @if($salesEntry->remarks)
                                        <br><strong>Remarks:</strong> {{ $salesEntry->remarks }}
                                    @endif
                                </p>
                                <small class="text-muted">{{ $salesEntry->approved_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @elseif($salesEntry->status === 'rejected')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Entry Rejected</h6>
                                <p class="timeline-description">
                                    Rejected by {{ $salesEntry->rejectedBy ? $salesEntry->rejectedBy->name : 'System' }}
                                    @if($salesEntry->rejection_reason)
                                        <br><strong>Reason:</strong> {{ $salesEntry->rejection_reason }}
                                    @endif
                                </p>
                                <small class="text-muted">{{ $salesEntry->rejected_at->format('M d, Y H:i') }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Status Information</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <span class="badge {{ $salesEntry->status_badge_class }} fs-6 p-3">
                            {{ $salesEntry->status_label }}
                        </span>
                    </div>
                    
                    @if($salesEntry->status === 'pending')
                    <p class="text-muted small">This entry is awaiting approval</p>
                    @elseif($salesEntry->status === 'approved')
                    <p class="text-success small">
                        Approved on {{ $salesEntry->approved_at->format('M d, Y') }}
                        @if($salesEntry->approvedBy)
                            by {{ $salesEntry->approvedBy->name }}
                        @endif
                    </p>
                    @elseif($salesEntry->status === 'rejected')
                    <p class="text-danger small">
                        Rejected on {{ $salesEntry->rejected_at->format('M d, Y') }}
                        @if($salesEntry->rejectedBy)
                            by {{ $salesEntry->rejectedBy->name }}
                        @endif
                    </p>
                    @endif
                </div>
            </div>

            <!-- Amount Breakdown -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Amount Breakdown</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-3">
                        <div class="col-6">
                            <div class="border-end">
                                <h6 class="text-muted mb-0">Quantity</h6>
                                <span class="h5 text-primary">{{ $salesEntry->quantity_kg }} KG</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <h6 class="text-muted mb-0">Rate/KG</h6>
                            <span class="h5 text-info">{{ $salesEntry->formatted_rate_per_kg }}</span>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h6 class="text-muted mb-0">Total Amount</h6>
                        <span class="h4 text-success">{{ $salesEntry->formatted_total_amount }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            @if($salesEntry->status === 'pending')
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-success" onclick="approveEntry({{ $salesEntry->id }})">
                            <i class="fas fa-check me-1"></i> Approve Entry
                        </button>
                        <button type="button" class="btn btn-danger" onclick="rejectEntry({{ $salesEntry->id }})">
                            <i class="fas fa-times me-1"></i> Reject Entry
                        </button>
                        <a href="{{ route('admin.sales-register.edit', $salesEntry->id) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-1"></i> Edit Entry
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveModalLabel">
                    <i class="fas fa-check-circle text-success me-2"></i>Approve Sales Entry
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Entry ID:</strong> {{ $salesEntry->sales_entry_id }}<br>
                        <strong>Total Amount:</strong> {{ $salesEntry->formatted_total_amount }}
                    </div>
                    <div class="mb-3">
                        <label for="approve_remarks" class="form-label">Approval Remarks (Optional)</label>
                        <textarea class="form-control" id="approve_remarks" name="remarks" rows="3" 
                                  placeholder="Add any approval notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i>Approve Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">
                    <i class="fas fa-times-circle text-danger me-2"></i>Reject Sales Entry
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Entry ID:</strong> {{ $salesEntry->sales_entry_id }}<br>
                        <strong>Total Amount:</strong> {{ $salesEntry->formatted_total_amount }}
                    </div>
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" 
                                  placeholder="Please provide reason for rejection..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times me-1"></i>Reject Entry
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -23px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e9ecef;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-description {
    margin-bottom: 5px;
    font-size: 13px;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
function approveEntry(entryId) {
    const form = document.getElementById('approveForm');
    form.action = `{{ url('admin/sales-register') }}/${entryId}/approve`;
    
    const modal = new bootstrap.Modal(document.getElementById('approveModal'));
    modal.show();
}

function rejectEntry(entryId) {
    const form = document.getElementById('rejectForm');
    form.action = `{{ url('admin/sales-register') }}/${entryId}/reject`;
    
    const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}
</script>
@endpush