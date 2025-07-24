@extends('admin.layouts.app')

@section('title', 'Samples Ready for Buyer Assignment')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $samples->count() }}</h4>
                                <p class="mb-0">Ready for Assignment</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-vial fa-2x opacity-75"></i>
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
                                <h4 class="mb-0">{{ $statistics['total_assignments'] ?? 0 }}</h4>
                                <p class="mb-0">Total Assignments</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
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
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $statistics['today_assignments'] ?? 0 }}</h4>
                                <p class="mb-0">Today's Assignments</p>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Samples Table -->
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Approved Samples Ready for Buyer Assignment
                    </h5>
                    <div>
                        <a href="{{ route('admin.samples.assigned-samples') }}" class="btn btn-outline-success btn-sm me-2">
                            <i class="fas fa-check-circle me-1"></i>View Assigned
                        </a>
                        <a href="{{ route('admin.samples.awaiting-dispatch') }}" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-shipping-fast me-1"></i>Awaiting Dispatch
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if($samples->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="samplesTable">
                        <thead>
                            <tr>
                                <th>Sample ID</th>
                                <th>Sample Name</th>
                                <th>Seller</th>
                                <th>Batch ID</th>
                                <th>Overall Score</th>
                                <th>Evaluation Date</th>
                                <th>Evaluated By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($samples as $sample)
                            <tr>
                                <td>
                                    <strong class="text-primary">#{{ $sample->sample_id }}</strong>
                                </td>
                                <td>{{ $sample->sample_name }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $sample->seller->seller_name }}</strong>
                                        @if($sample->seller->tea_estate)
                                        <br><small class="text-muted">{{ $sample->seller->tea_estate }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $sample->batch_id }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge 
                                            @if($sample->overall_score >= 9) bg-success
                                            @elseif($sample->overall_score >= 8) bg-primary
                                            @elseif($sample->overall_score >= 7) bg-info
                                            @else bg-warning
                                            @endif me-2">
                                            {{ $sample->overall_score }}/10
                                        </span>
                                        <div class="progress" style="width: 60px; height: 6px;">
                                            <div class="progress-bar 
                                                @if($sample->overall_score >= 9) bg-success
                                                @elseif($sample->overall_score >= 8) bg-primary
                                                @elseif($sample->overall_score >= 7) bg-info
                                                @else bg-warning
                                                @endif" 
                                                style="width: {{ $sample->overall_score * 10 }}%"></div>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        A:{{ $sample->aroma_score }} L:{{ $sample->liquor_score }} Ap:{{ $sample->appearance_score }}
                                    </small>
                                </td>
                                <td>
                                    {{ $sample->evaluated_at ? $sample->evaluated_at->format('M d, Y') : 'N/A' }}
                                    <br><small class="text-muted">
                                        {{ $sample->evaluated_at ? $sample->evaluated_at->format('H:i A') : '' }}
                                    </small>
                                </td>
                                <td>
                                    {{ $sample->evaluatedBy->name ?? 'N/A' }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.samples.show', $sample->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.samples.assign-buyers', $sample->id) }}" 
                                           class="btn btn-sm btn-success" title="Assign to Buyers">
                                            <i class="fas fa-users"></i>
                                        </a>
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
                        <i class="fas fa-inbox fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Samples Ready for Assignment</h5>
                    <p class="text-muted">
                        Samples will appear here once they are approved after evaluation.
                    </p>
                    <a href="{{ route('admin.samples.pending-evaluations') }}" class="btn btn-primary">
                        <i class="fas fa-vial me-1"></i>View Pending Evaluations
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#samplesTable').DataTable({
        responsive: true,
        order: [[4, 'desc']], // Sort by overall score descending
        pageLength: 25,
        language: {
            search: "Search samples:",
            lengthMenu: "Show _MENU_ samples per page",
            info: "Showing _START_ to _END_ of _TOTAL_ samples",
            emptyTable: "No samples ready for assignment"
        },
        columnDefs: [
            { orderable: false, targets: [7] } // Disable sorting on Actions column
        ]
    });
});
</script>
@endpush