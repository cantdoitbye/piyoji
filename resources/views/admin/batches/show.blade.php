@extends('admin.layouts.app')

@section('title', 'Batch Details - ' . $batch->batch_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Batch Details</h1>
            <p class="text-muted">{{ $batch->batch_number }} - {{ $batch->batch_date->format('M d, Y') }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Batches
            </a>
            @if($batch->status !== 'completed')
            <button type="button" class="btn btn-success" onclick="updateBatchStatus({{ $batch->id }}, 'completed')">
                <i class="fas fa-check me-1"></i> Mark Complete
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Batch Information -->
        <div class="col-xl-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Batch Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="fw-bold">Batch Number:</td>
                            <td>{{ $batch->batch_number }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Batch Date:</td>
                            <td>{{ $batch->batch_date->format('M d, Y (l)') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Sequence:</td>
                            <td># {{ $batch->batch_sequence }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Status:</td>
                            <td>
                                <span class="badge {{ $batch->status_badge_class }}">
                                    {{ $batch->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Total Samples:</td>
                            <td>
                                <strong>{{ $batch->total_samples }}</strong> / {{ $batch->max_samples }}
                                <small class="text-muted">({{ number_format($batch->capacity_percentage, 1) }}%)</small>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Available Slots:</td>
                            <td>{{ $batch->available_capacity }}</td>
                        </tr>
                        @if($batch->completed_at)
                        <tr>
                            <td class="fw-bold">Completed:</td>
                            <td>{{ $batch->completed_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="fw-bold">Created By:</td>
                            <td>{{ $batch->createdBy ? $batch->createdBy->name : 'System' }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Created At:</td>
                            <td>{{ $batch->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($batch->remarks)
                        <tr>
                            <td class="fw-bold">Remarks:</td>
                            <td>{{ $batch->remarks }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- Capacity Visualization -->
        <div class="col-xl-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Batch Capacity</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-bold">Capacity Utilization</span>
                                    <span class="small">{{ $batch->total_samples }}/{{ $batch->max_samples }} samples</span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar {{ $batch->capacity_percentage >= 100 ? 'bg-success' : ($batch->capacity_percentage >= 80 ? 'bg-warning' : 'bg-info') }}" 
                                         role="progressbar" 
                                         style="width: {{ $batch->capacity_percentage }}%">
                                        {{ number_format($batch->capacity_percentage, 1) }}%
                                    </div>
                                </div>
                            </div>
                            
                            @if($batch->remarks)
                            <div class="alert alert-info">
                                <small><strong>Remarks:</strong> {{ $batch->remarks }}</small>
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="text-center">
                                <div class="border rounded p-3">
                                    <h2 class="text-primary mb-0">{{ $batch->total_samples }}</h2>
                                    <small class="text-muted">Total Samples</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-info">{{ $batch->samples->where('evaluation_status', 'pending')->count() }}</h4>
                    <small class="text-muted">Pending Evaluation</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-success">{{ $batch->samples->where('status', 'approved')->count() }}</h4>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-danger">{{ $batch->samples->where('status', 'rejected')->count() }}</h4>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h4 class="text-warning">{{ $batch->samples->where('status', 'assigned_to_buyers')->count() }}</h4>
                    <small class="text-muted">Assigned to Buyers</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Samples -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Samples in this Batch</h5>
            <div class="d-flex gap-2">
                <span class="badge bg-primary">{{ $batch->samples->count() }} samples</span>
                <button class="btn btn-sm btn-outline-success" onclick="exportBatchSamples({{ $batch->id }})">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($batch->samples->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sample ID</th>
                                <th>Batch Sample ID</th>
                                <th>Sample Name</th>
                                <th>Seller</th>
                                <th>Quantity</th>
                                <th>Weight</th>
                                <th>Status</th>
                                <th>Evaluation</th>
                                <th>C-T-S-B</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->samples as $sample)
                            <tr>
                                <td>
                                    <strong class="text-primary">#{{ $sample->sample_id }}</strong>
                                </td>
                                <td>
                                    <code class="small">{{ $sample->batch_id }}</code>
                                </td>
                                <td>
                                    <strong>{{ $sample->sample_name }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $sample->seller->seller_name }}</strong>
                                        <br><small class="text-muted">{{ $sample->seller->tea_estate_name }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $sample->number_of_samples ?? 1 }}</span>
                                    @if($sample->weight_per_sample)
                                        <br><small class="text-muted">{{ $sample->weight_per_sample }}kg each</small>
                                    @endif
                                </td>
                                <td>
                                    @if($sample->sample_weight)
                                        <strong>{{ $sample->sample_weight }} kg</strong>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $sample->status_badge_class }}">
                                        {{ $sample->status_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $sample->evaluation_status === 'completed' ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $sample->evaluation_status_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($sample->overall_score)
                                        <span class="badge {{ $sample->overall_score >= 8 ? 'bg-success' : ($sample->overall_score >= 6 ? 'bg-warning' : 'bg-danger') }}">
                                            {{ $sample->overall_score }}/10
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.samples.show', $sample->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Sample">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($sample->evaluation_status === 'pending')
                                        <a href="{{ route('admin.samples.evaluate', $sample->id) }}" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Evaluate Sample">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-flask fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No samples found in this batch.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Function to update batch status
function updateBatchStatus(batchId, status) {
    if (!confirm(`Are you sure you want to mark this batch as ${status}?`)) {
        return;
    }

    fetch(`{{ url('admin/batches') }}/${batchId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error updating batch status: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating batch status. Please try again.');
    });
}

// Function to export batch samples
function exportBatchSamples(batchId) {
    window.location.href = `{{ url('admin/batches') }}/${batchId}/export-samples`;
}
</script>
@endpush