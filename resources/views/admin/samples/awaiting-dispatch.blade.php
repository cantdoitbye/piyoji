@extends('admin.layouts.app')

@section('title', 'Assignments Awaiting Dispatch')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">
                <i class="fas fa-shipping-fast me-2 text-warning"></i>Sample Assignments Awaiting Dispatch
            </h2>
            <div>
                <a href="{{ route('admin.samples.ready-for-assignment') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-plus me-1"></i>Ready for Assignment
                </a>
                <a href="{{ route('admin.samples.assigned-samples') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-check-circle me-1"></i>All Assigned
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $assignments->count() }}</h4>
                                <p class="mb-0">Awaiting Dispatch</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $statistics['dispatched'] ?? 0 }}</h4>
                                <p class="mb-0">Dispatched</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-shipping-fast fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $statistics['delivered'] ?? 0 }}</h4>
                                <p class="mb-0">Delivered</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-double fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $assignments->unique('sample_id')->count() }}</h4>
                                <p class="mb-0">Unique Samples</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-vial fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Sample Assignments Ready for Dispatch
                    </h5>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="bulkDispatch()">
                            <i class="fas fa-paper-plane me-1"></i>Bulk Dispatch
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($assignments->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="awaitingDispatchTable">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Sample ID</th>
                                <th>Sample Details</th>
                                <th>Seller</th>
                                <th>Buyer</th>
                                <th>Score</th>
                                <th>Assignment Date</th>
                                <th>Assigned By</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input assignment-checkbox" 
                                           value="{{ $assignment->id }}">
                                </td>
                                <td>
                                    <strong class="text-primary">#{{ $assignment->sample->sample_id }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $assignment->sample->sample_name }}</strong>
                                        <br><small class="text-muted">Batch: {{ $assignment->sample->batch_id }}</small>
                                        @if($assignment->sample->sample_weight)
                                        <br><small class="text-muted">Weight: {{ $assignment->sample->sample_weight }}g</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $assignment->sample->seller->seller_name }}</strong>
                                        @if($assignment->sample->seller->tea_estate)
                                        <br><small class="text-muted">{{ $assignment->sample->seller->tea_estate }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $assignment->buyer->buyer_name }}</strong>
                                        <br><span class="badge {{ $assignment->buyer->buyer_type === 'big' ? 'bg-primary' : 'bg-info' }}">
                                            {{ $assignment->buyer->buyer_type_text }}
                                        </span>
                                        <br><small class="text-muted">{{ $assignment->buyer->email }}</small>
                                        <br><small class="text-muted">{{ $assignment->buyer->phone }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($assignment->sample->overall_score >= 9) bg-success
                                        @elseif($assignment->sample->overall_score >= 8) bg-primary
                                        @elseif($assignment->sample->overall_score >= 7) bg-info
                                        @else bg-warning
                                        @endif">
                                        {{ $assignment->sample->overall_score }}/10
                                    </span>
                                    <br><small class="text-muted">
                                        A:{{ $assignment->sample->aroma_score }} 
                                        L:{{ $assignment->sample->liquor_score }} 
                                        Ap:{{ $assignment->sample->appearance_score }}
                                    </small>
                                </td>
                                <td>
                                    {{ $assignment->assigned_at->format('M d, Y') }}
                                    <br><small class="text-muted">
                                        {{ $assignment->assigned_at->format('H:i A') }}
                                    </small>
                                    <br><small class="text-info">
                                        {{ $assignment->assigned_at->diffForHumans() }}
                                    </small>
                                </td>
                                <td>
                                    {{ $assignment->assignedBy->name }}
                                </td>
                                <td>
                                    @if($assignment->assignment_remarks)
                                    <small class="text-muted">{{ Str::limit($assignment->assignment_remarks, 50) }}</small>
                                    @else
                                    <small class="text-muted fst-italic">No remarks</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="markAsDispatched({{ $assignment->id }})" 
                                                title="Mark as Dispatched">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                        <a href="{{ route('admin.samples.show', $assignment->sample->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Sample">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="removeAssignment({{ $assignment->id }})" 
                                                title="Remove Assignment">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-check-circle fa-4x text-success"></i>
                    </div>
                    <h5 class="text-muted">All Assignments Dispatched!</h5>
                    <p class="text-muted">
                        There are no sample assignments awaiting dispatch at the moment.
                    </p>
                    <a href="{{ route('admin.samples.ready-for-assignment') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Assign More Samples
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Dispatch Modal -->
<div class="modal fade" id="dispatchModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane me-2"></i>Mark as Dispatched
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="dispatchForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tracking_id" class="form-label">Tracking ID (Optional)</label>
                        <input type="text" class="form-control" id="tracking_id" name="tracking_id" 
                               placeholder="Enter courier tracking ID">
                        <div class="form-text">Add tracking ID if available from courier service</div>
                    </div>
                    <input type="hidden" id="assignment_id" name="assignment_id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-paper-plane me-1"></i>Mark as Dispatched
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#awaitingDispatchTable').DataTable({
        responsive: true,
        order: [[6, 'asc']], // Sort by assignment date ascending (oldest first)
        pageLength: 25,
        language: {
            search: "Search assignments:",
            lengthMenu: "Show _MENU_ assignments per page",
            info: "Showing _START_ to _END_ of _TOTAL_ assignments",
            emptyTable: "No assignments awaiting dispatch"
        },
        columnDefs: [
            { orderable: false, targets: [0, 8, 9] } // Disable sorting on checkbox, remarks, and actions
        ]
    });

    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.assignment-checkbox').prop('checked', this.checked);
    });

    $('.assignment-checkbox').change(function() {
        if (!this.checked) {
            $('#selectAll').prop('checked', false);
        } else if ($('.assignment-checkbox:checked').length === $('.assignment-checkbox').length) {
            $('#selectAll').prop('checked', true);
        }
    });

    // Dispatch form submission
    $('#dispatchForm').submit(function(e) {
        e.preventDefault();
        
        const assignmentId = $('#assignment_id').val();
        const trackingId = $('#tracking_id').val();
        
        updateDispatchStatus(assignmentId, 'dispatched', trackingId);
    });
});

function markAsDispatched(assignmentId) {
    $('#assignment_id').val(assignmentId);
    $('#tracking_id').val('');
    new bootstrap.Modal(document.getElementById('dispatchModal')).show();
}

function updateDispatchStatus(assignmentId, status, trackingId = null) {
    const formData = new FormData();
    formData.append('status', status);
    if (trackingId) {
        formData.append('tracking_id', trackingId);
    }

    fetch(`{{ url('admin/samples/assignments/') }}/${assignmentId}/dispatch-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal if open
            const modal = bootstrap.Modal.getInstance(document.getElementById('dispatchModal'));
            if (modal) modal.hide();
            
            // Show success message and reload
            alert('Assignment status updated successfully!');
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating the status');
    });
}

function removeAssignment(assignmentId) {
    if (confirm('Are you sure you want to remove this assignment?\n\nThis action cannot be undone.')) {
        fetch(`{{ url('admin/samples/assignments/') }}/${assignmentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Assignment removed successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the assignment');
        });
    }
}

function bulkDispatch() {
    const selectedIds = Array.from(document.querySelectorAll('.assignment-checkbox:checked'))
                            .map(cb => cb.value);
    
    if (selectedIds.length === 0) {
        alert('Please select at least one assignment to dispatch');
        return;
    }
    
    if (confirm(`Are you sure you want to mark ${selectedIds.length} assignment(s) as dispatched?`)) {
        // Process each selected assignment
        let processed = 0;
        let errors = 0;
        
        selectedIds.forEach(id => {
            updateDispatchStatus(id, 'dispatched');
            processed++;
        });
        
        // Reload after a short delay to allow all requests to complete
        setTimeout(() => {
            location.reload();
        }, 1000);
    }
}
</script>
@endpush