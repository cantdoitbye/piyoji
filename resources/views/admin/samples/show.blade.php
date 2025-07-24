@extends('admin.layouts.app')

@section('title', 'Sample Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sample Details</h1>
            <p class="text-muted">{{ $sample->sample_name }} ({{ $sample->sample_id }})</p>
        </div>
        <div class="d-flex gap-2">
            @if($sample->evaluation_status === 'pending' || $sample->evaluation_status === 'in_progress')
                <a href="{{ route('admin.samples.evaluate', $sample->id) }}" class="btn btn-warning">
                    <i class="fas fa-clipboard-check me-1"></i> Evaluate Sample
                </a>
            @endif
            
            <a href="{{ route('admin.samples.edit', $sample->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            
            <a href="{{ route('admin.samples.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Samples
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-xl-8">
            <!-- Sample Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Sample Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Sample ID:</td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $sample->sample_id }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Sample Name:</td>
                                    <td>{{ $sample->sample_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Batch ID:</td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $sample->batch_id }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Sample Weight:</td>
                                    <td>{{ $sample->sample_weight ? $sample->sample_weight . ' kg' : 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Arrival Date:</td>
                                    <td>{{ $sample->arrival_date->format('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Status:</td>
                                    <td>
                                        <span class="badge {{ $sample->status_badge_class }}">
                                            {{ $sample->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Evaluation Status:</td>
                                    <td>
                                        <span class="badge {{ $sample->evaluation_status === 'completed' ? 'bg-success' : ($sample->evaluation_status === 'in_progress' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ $sample->evaluation_status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Received By:</td>
                                    <td>{{ $sample->receivedBy->name ?? 'Unknown' }}</td>
                                </tr>
                                @if($sample->evaluatedBy)
                                    <tr>
                                        <td class="fw-bold text-muted">Evaluated By:</td>
                                        <td>{{ $sample->evaluatedBy->name }}</td>
                                    </tr>
                                @endif
                                @if($sample->evaluated_at)
                                    <tr>
                                        <td class="fw-bold text-muted">Evaluated At:</td>
                                        <td>{{ $sample->evaluated_at->format('d M Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                    
                    @if($sample->remarks)
                        <div class="mt-3">
                            <h6 class="fw-bold">Remarks:</h6>
                            <p class="text-muted">{{ $sample->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Seller Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-store me-2"></i>Seller Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Seller Name:</td>
                                    <td>
                                        <a href="{{ route('admin.sellers.show', $sample->seller->id) }}" class="text-decoration-none">
                                            {{ $sample->seller->seller_name }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Tea Estate:</td>
                                    <td>{{ $sample->seller->tea_estate_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Contact Person:</td>
                                    <td>{{ $sample->seller->contact_person }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold text-muted">Email:</td>
                                    <td>
                                        <a href="mailto:{{ $sample->seller->email }}">{{ $sample->seller->email }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Phone:</td>
                                    <td>
                                        <a href="tel:{{ $sample->seller->phone }}">{{ $sample->seller->phone }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-muted">Location:</td>
                                    <td>{{ $sample->seller->city }}, {{ $sample->seller->state }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <h6 class="fw-bold">Tea Grades Handled:</h6>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($sample->seller->tea_grades as $grade)
                                <span class="badge bg-outline-primary">{{ $grade }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>


            <!-- Buyer Assignments Section -->
           <!-- Add this section after the Evaluation Results card in your existing sample show view -->
            
            <!-- Buyer Assignments Section -->
            @if($sample->status === 'assigned_to_buyers' && $sample->buyerAssignments && $sample->buyerAssignments->count() > 0)
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Buyer Assignments ({{ $sample->buyerAssignments->count() }})</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Buyer</th>
                                        <th>Type</th>
                                        <th>Assignment Date</th>
                                        <th>Dispatch Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sample->buyerAssignments as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment->buyer->buyer_name }}</strong>
                                            <br><small class="text-muted">{{ $assignment->buyer->contact_person }}</small>
                                        </td>
                                        <td>
                                            <span class="badge {{ $assignment->buyer->buyer_type === 'big' ? 'bg-primary' : 'bg-info' }}">
                                                {{ ucfirst($assignment->buyer->buyer_type) }} Buyer
                                            </span>
                                        </td>
                                        <td>
                                            {{ $assignment->assigned_at->format('M d, Y H:i') }}
                                            <br><small class="text-muted">by {{ $assignment->assignedBy->name }}</small>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($assignment->dispatch_status === 'awaiting_dispatch') bg-warning
                                                @elseif($assignment->dispatch_status === 'dispatched') bg-info
                                                @elseif($assignment->dispatch_status === 'delivered') bg-success
                                                @else bg-secondary
                                                @endif">
                                                {{ ucwords(str_replace('_', ' ', $assignment->dispatch_status)) }}
                                            </span>
                                            @if($assignment->tracking_id)
                                            <br><small class="text-info">Tracking: {{ $assignment->tracking_id }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->assignment_remarks)
                                                {{ Str::limit($assignment->assignment_remarks, 50) }}
                                            @else
                                                <small class="text-muted">No remarks</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <a href="{{ route('admin.samples.assign-buyers', $sample->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Manage Assignments
                            </a>
                        </div>
                    </div>
                </div>
            @elseif($sample->status === 'approved')
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Ready for Buyer Assignment</h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="mb-3">This sample has been approved and is ready to be assigned to buyers.</p>
                        <a href="{{ route('admin.samples.assign-buyers', $sample->id) }}" class="btn btn-success">
                            <i class="fas fa-users me-1"></i>Assign to Buyers
                        </a>
                    </div>
                </div>
            @endif

            <!-- Evaluation Results -->
            @if($sample->evaluation_status === 'completed')
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-star me-2"></i>Evaluation Results</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-nose fa-2x text-primary"></i>
                                    </div>
                                    <h6 class="fw-bold">Aroma Score</h6>
                                    <h3 class="text-primary">{{ $sample->aroma_score }}/10</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-tint fa-2x text-info"></i>
                                    </div>
                                    <h6 class="fw-bold">Liquor Score</h6>
                                    <h3 class="text-info">{{ $sample->liquor_score }}/10</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-eye fa-2x text-warning"></i>
                                    </div>
                                    <h6 class="fw-bold">Appearance Score</h6>
                                    <h3 class="text-warning">{{ $sample->appearance_score }}/10</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <div class="mb-2">
                                        <i class="fas fa-trophy fa-2x text-success"></i>
                                    </div>
                                    <h6 class="fw-bold">Overall Score</h6>
                                    <h3 class="text-success">{{ $sample->overall_score }}/10</h3>
                                    <span class="badge {{ $sample->overall_score >= 8 ? 'bg-success' : ($sample->overall_score >= 6 ? 'bg-primary' : ($sample->overall_score >= 4 ? 'bg-warning' : 'bg-danger')) }}">
                                        @if($sample->overall_score >= 8)
                                            Excellent
                                        @elseif($sample->overall_score >= 6)
                                            Good
                                        @elseif($sample->overall_score >= 4)
                                            Average
                                        @else
                                            Poor
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        @if($sample->evaluation_comments)
                            <div class="mt-4">
                                <h6 class="fw-bold">Evaluation Comments:</h6>
                                <div class="alert alert-light">
                                    {{ $sample->evaluation_comments }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($sample->evaluation_status === 'pending')
                            <a href="{{ route('admin.samples.evaluate', $sample->id) }}" class="btn btn-warning">
                                <i class="fas fa-clipboard-check me-1"></i> Start Evaluation
                            </a>
                        @elseif($sample->evaluation_status === 'in_progress')
                            <a href="{{ route('admin.samples.evaluate', $sample->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Continue Evaluation
                            </a>
                        @endif
                        
                        @if($sample->status === 'approved')
                            <button type="button" class="btn btn-success" disabled>
                                <i class="fas fa-check me-1"></i> Ready for Buyer Assignment
                            </button>
                        @endif
                        
                        <a href="{{ route('admin.samples.edit', $sample->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i> Edit Sample
                        </a>
                        
                        <button type="button" class="btn btn-outline-danger" onclick="deleteSample({{ $sample->id }})">
                            <i class="fas fa-trash me-1"></i> Delete Sample
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sample Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Sample Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Sample Received</h6>
                                <p class="timeline-text">{{ $sample->arrival_date->format('d M Y') }}</p>
                                <small class="text-muted">by {{ $sample->receivedBy->name ?? 'Unknown' }}</small>
                            </div>
                        </div>
                        
                        @if($sample->evaluation_status !== 'pending')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Evaluation Started</h6>
                                    <p class="timeline-text">{{ $sample->updated_at->format('d M Y') }}</p>
                                    <small class="text-muted">by {{ $sample->evaluatedBy->name ?? 'Unknown' }}</small>
                                </div>
                            </div>
                        @endif
                        
                        @if($sample->evaluation_status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Evaluation Completed</h6>
                                    <p class="timeline-text">{{ $sample->evaluated_at->format('d M Y H:i') }}</p>
                                    <small class="text-muted">Score: {{ $sample->overall_score }}/10</small>
                                </div>
                            </div>
                        @endif
                        
                        @if($sample->status === 'approved')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Sample Approved</h6>
                                    <p class="timeline-text">Ready for buyer assignment</p>
                                </div>
                            </div>
                        @elseif($sample->status === 'rejected')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Sample Rejected</h6>
                                    <p class="timeline-text">Score below threshold</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sample Statistics -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Seller Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border-end">
                                <h4 class="text-primary mb-0">0</h4>
                                <small class="text-muted">Total Samples</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <h4 class="text-success mb-0">0</h4>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-info mb-0">0.0</h4>
                                <small class="text-muted">Avg Score</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-warning mb-0">0</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                </div>
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
                <p>Are you sure you want to delete this sample? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <strong>Sample:</strong> {{ $sample->sample_name }} ({{ $sample->sample_id }})
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('admin.samples.destroy', $sample->id) }}" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
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
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -37px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    font-size: 13px;
}

.badge.bg-outline-primary {
    background-color: transparent !important;
    border: 1px solid #007bff;
    color: #007bff;
}
</style>
@endpush

@push('scripts')
<script>
function deleteSample(sampleId) {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-refresh evaluation status if pending
@if($sample->evaluation_status === 'in_progress')
    setInterval(function() {
        // Optional: Add AJAX to check if evaluation is completed
        // and refresh the page automatically
    }, 30000); // Check every 30 seconds
@endif
</script>
@endpush