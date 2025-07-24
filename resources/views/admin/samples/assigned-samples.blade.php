@extends('admin.layouts.app')

@section('title', 'Assigned Samples')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h4 mb-0">
                <i class="fas fa-check-circle me-2 text-success"></i>Samples Assigned to Buyers
            </h2>
            <div>
                <a href="{{ route('admin.samples.ready-for-assignment') }}" class="btn btn-outline-primary btn-sm me-2">
                    <i class="fas fa-plus me-1"></i>Ready for Assignment
                </a>
                <a href="{{ route('admin.samples.awaiting-dispatch') }}" class="btn btn-outline-warning btn-sm">
                    <i class="fas fa-shipping-fast me-1"></i>Awaiting Dispatch
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $samples->count() }}</h4>
                                <p class="mb-0">Assigned Samples</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $statistics['awaiting_dispatch'] ?? 0 }}</h4>
                                <p class="mb-0">Awaiting Dispatch</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
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
        </div>

        <!-- Assigned Samples Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Assigned Samples Overview
                </h5>
            </div>
            <div class="card-body">
                @if($samples->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="assignedSamplesTable">
                        <thead>
                            <tr>
                                <th>Sample ID</th>
                                <th>Sample Name</th>
                                <th>Seller</th>
                                <th>Score</th>
                                <th>Assigned Buyers</th>
                                <th>Assignment Date</th>
                                <th>Status Summary</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($samples as $sample)
                            <tr>
                                <td>
                                    <strong class="text-primary">#{{ $sample->sample_id }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $sample->sample_name }}</strong>
                                        <br><small class="text-muted">Batch: {{ $sample->batch_id }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $sample->seller->seller_name }}</strong>
                                        @if($sample->seller->tea_estate)
                                        <br><small class="text-muted">{{ $sample->seller->tea_estate }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge 
                                        @if($sample->overall_score >= 9) bg-success
                                        @elseif($sample->overall_score >= 8) bg-primary
                                        @elseif($sample->overall_score >= 7) bg-info
                                        @else bg-warning
                                        @endif">
                                        {{ $sample->overall_score }}/10
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($sample->buyerAssignments as $assignment)
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark me-1">
                                                {{ $assignment->buyer->buyer_name }}
                                            </span>
                                            <span class="{{ $assignment->dispatch_status_badge }} small">
                                                {{ $assignment->dispatch_status_text }}
                                            </span>
                                        </div>
                                        @if(!$loop->last)<br>@endif
                                        @endforeach
                                    </div>
                                    <small class="text-muted">
                                        {{ $sample->buyerAssignments->count() }} buyer(s)
                                    </small>
                                </td>
                                <td>
                                    @if($sample->buyerAssignments->first())
                                    {{ $sample->buyerAssignments->first()->assigned_at->format('M d, Y') }}
                                    <br><small class="text-muted">
                                        {{ $sample->buyerAssignments->first()->assigned_at->format('H:i A') }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $awaitingCount = $sample->buyerAssignments->where('dispatch_status', 'awaiting_dispatch')->count();
                                        $dispatchedCount = $sample->buyerAssignments->where('dispatch_status', 'dispatched')->count();
                                        $deliveredCount = $sample->buyerAssignments->where('dispatch_status', 'delivered')->count();
                                    @endphp
                                    
                                    <div class="small">
                                        @if($awaitingCount > 0)
                                        <span class="badge bg-warning">{{ $awaitingCount }} Awaiting</span>
                                        @endif
                                        @if($dispatchedCount > 0)
                                        <span class="badge bg-info">{{ $dispatchedCount }} Dispatched</span>
                                        @endif
                                        @if($deliveredCount > 0)
                                        <span class="badge bg-success">{{ $deliveredCount }} Delivered</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.samples.ready-for-assignment') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Assign Samples to Buyers
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Assignment Details Modal -->
<div class="modal fade" id="assignmentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle me-2"></i>Assignment Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#assignedSamplesTable').DataTable({
        responsive: true,
        order: [[5, 'desc']], // Sort by assignment date descending
        pageLength: 25,
        language: {
            search: "Search assigned samples:",
            lengthMenu: "Show _MENU_ samples per page",
            info: "Showing _START_ to _END_ of _TOTAL_ assigned samples",
            emptyTable: "No assigned samples found"
        },
        columnDefs: [
            { orderable: false, targets: [4, 6, 7] } // Disable sorting on certain columns
        ]
    });
});

function viewAssignmentDetails(sampleId) {
    fetch(`{{ url('admin/samples/') }}/${sampleId}/assignments`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('assignmentDetailsContent').innerHTML = data.html;
            new bootstrap.Modal(document.getElementById('assignmentDetailsModal')).show();
        } else {
            alert('Error loading assignment details: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while loading assignment details');
    });
}
</script>
@endpush 