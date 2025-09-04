@extends('admin.layouts.app')

@section('title', 'Batch Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Batch Management</h1>
            <p class="text-muted">Manage sample batches and view day-wise batch information</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.samples.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-flask me-1"></i> Back to Samples
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBatchModal">
                <i class="fas fa-plus me-1"></i> Create Batch
            </button>
        </div>
    </div>

    <!-- Overview Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Today's Batches</div>
                            <div class="h4 mb-0">{{ $overview['today']['batches'] }}</div>
                            <small>{{ $overview['today']['samples'] }} samples</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">This Week</div>
                            <div class="h4 mb-0">{{ $overview['this_week']['batches'] }}</div>
                            <small>{{ $overview['this_week']['samples'] }} samples</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">This Month</div>
                            <div class="h4 mb-0">{{ $overview['this_month']['batches'] }}</div>
                            <small>{{ $overview['this_month']['samples'] }} samples</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Batches</div>
                            <div class="h4 mb-0">{{ $overview['total']['batches'] }}</div>
                            <small>{{ $overview['total']['samples'] }} samples</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-layer-group fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unbatched Samples Alert -->
    @if($overview['today']['unbatched_samples'] > 0)
    <div class="alert alert-warning mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>{{ $overview['today']['unbatched_samples'] }} samples</strong> from today are waiting to be batched.
            </div>
            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#createBatchModal">
                <i class="fas fa-layer-group me-1"></i>Create Batches
            </button>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Batch number...">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="full" {{ request('status') === 'full' ? 'selected' : '' }}>Full</option>
                        <option value="processing" {{ request('status') === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ request('start_date') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" 
                           value="{{ request('end_date') }}">
                </div>
                
                <div class="col-md-2">
                    <label for="per_page" class="form-label">Per Page</label>
                    <select class="form-select" id="per_page" name="per_page">
                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    </select>
                </div>
                
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Batches Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-layer-group me-2"></i>Batches</h5>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-secondary">{{ $batches->total() }} total</span>
                <a href="{{ route('admin.batches.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download me-1"></i>Export
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($batches->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Batch Number</th>
                                <th>Date</th>
                                <th>Samples</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Evaluation</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td>
                                    <strong class="text-primary">{{ $batch->batch_number }}</strong>
                                    <br><small class="text-muted">Seq #{{ $batch->batch_sequence }}</small>
                                </td>
                                <td>
                                    <strong>{{ $batch->batch_date->format('M d, Y') }}</strong>
                                    <br><small class="text-muted">{{ $batch->batch_date->format('l') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-info me-2">{{ $batch->total_samples }}</span>
                                        <small class="text-muted">/ {{ $batch->max_samples }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar {{ $batch->capacity_percentage >= 100 ? 'bg-success' : 'bg-info' }}" 
                                             role="progressbar" 
                                             style="width: {{ $batch->capacity_percentage }}%"
                                             aria-valuenow="{{ $batch->capacity_percentage }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            {{ number_format($batch->capacity_percentage, 1) }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $batch->available_capacity }} slots available</small>
                                </td>
                                <td>
                                    <span class="badge {{ $batch->status_badge_class }}">
                                        {{ $batch->status_label }}
                                    </span>
                                    @if($batch->completed_at)
                                        <br><small class="text-muted">{{ $batch->completed_at->format('M d, H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $batch->createdBy ? $batch->createdBy->name : 'System' }}
                                </td>
                                <td>
                                    {{ $batch->created_at->format('M d, Y H:i') }}
                                </td>
                                <td>
    @php
        $evaluation = \App\Models\BatchEvaluation::where('batch_group_id', $batch->id)->first();
    @endphp
    
    @if($evaluation)
        <span class="badge {{ $evaluation->status_badge_class }}">
            {{ $evaluation->status_label }}
        </span>
        @if($evaluation->isCompleted())
            <br><small class="text-muted">
                {{ $evaluation->evaluation_completed_at->format('M d, H:i') }}
            </small>
        @endif
    @else
        @if($batch->total_samples > 0)
            <span class="badge bg-secondary">Not Started</span>
        @else
            <span class="text-muted">-</span>
        @endif
    @endif
</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.batches.show', $batch->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Batch Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                          <!-- Evaluation Button -->
        @if($batch->total_samples > 0)
            @php
                $hasEvaluation = \App\Models\BatchEvaluation::where('batch_group_id', $batch->id)->exists();
            @endphp
            
            @if($hasEvaluation)
                <!-- View Evaluation Results -->
                <a href="{{ route('admin.batches.evaluation-results', $batch->id) }}" 
                   class="btn btn-outline-success btn-sm" title="View Evaluation Results">
                    <i class="fas fa-chart-line"></i>
                </a>
            @else
                <!-- Start Evaluation -->
                <a href="{{ route('admin.batches.evaluation-form', $batch->id) }}" 
                   class="btn btn-outline-warning btn-sm" title="Start Batch Evaluation">
                    <i class="fas fa-clipboard-check"></i>
                </a>
            @endif
        @endif
                                        
                                        @if($batch->status !== 'completed')
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success" 
                                                onclick="updateBatchStatus({{ $batch->id }}, 'completed')"
                                                title="Mark as Completed">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        @endif
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteBatch({{ $batch->id }})"
                                                title="Delete Batch">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $batches->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-layer-group fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No batches found</h5>
                    <p class="text-muted">Create your first batch to get started.</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBatchModal">
                        <i class="fas fa-plus me-1"></i> Create Batch
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Batch Modal -->
<div class="modal fade" id="createBatchModal" tabindex="-1" aria-labelledby="createBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBatchModalLabel">
                    <i class="fas fa-layer-group me-2"></i>Create Batches for Date
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createBatchForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="batch_date" class="form-label">Select Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="batch_date" name="batch_date" 
                                   value="{{ date('Y-m-d') }}" required>
                            <small class="form-text text-muted">Batches will be created for samples received on this date</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batch Configuration</label>
                            <div class="alert alert-info mb-0">
                                <small>
                                    <strong>Batch Size:</strong> 48 samples per batch<br>
                                    <strong>Auto-naming:</strong> BATCH + Date + Sequence<br>
                                    <strong>Sample IDs:</strong> Batch Number + Sample Number
                                </small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div id="batchStatistics" class="alert alert-secondary" style="display: none;">
                                <!-- Batch statistics will be loaded here -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="loadBatchStats">
                    <i class="fas fa-search me-1"></i>Check Statistics
                </button>
                <button type="button" class="btn btn-primary" id="createBatchesBtn">
                    <i class="fas fa-layer-group me-1"></i>Create Batches
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const batchDateInput = document.getElementById('batch_date');
    const loadStatsBtn = document.getElementById('loadBatchStats');
    const createBatchesBtn = document.getElementById('createBatchesBtn');
    const batchStatistics = document.getElementById('batchStatistics');

    // Load batch statistics
    loadStatsBtn.addEventListener('click', function() {
        const date = batchDateInput.value;
        if (!date) {
            alert('Please select a date first.');
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';

        fetch(`{{ route('admin.batches.date-statistics') }}?date=${date}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayBatchStatistics(data.data);
                } else {
                    alert('Error loading statistics: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading statistics. Please try again.');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-search me-1"></i>Check Statistics';
            });
    });

    // Create batches
    createBatchesBtn.addEventListener('click', function() {
        const date = batchDateInput.value;
        if (!date) {
            alert('Please select a date first.');
            return;
        }

        if (!confirm(`Are you sure you want to create batches for samples on ${date}? This action cannot be undone.`)) {
            return;
        }

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Creating Batches...';

        fetch(`{{ route('admin.batches.create-for-date') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ date: date })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Success! Created ${data.data.batches_created} batches for ${data.data.samples_processed} samples.`);
                window.location.reload();
            } else {
                alert('Error creating batches: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error creating batches. Please try again.');
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-layer-group me-1"></i>Create Batches';
        });
    });

    function displayBatchStatistics(stats) {
        const estimatedBatches = Math.ceil(stats.unbatched_samples / 48);
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Date:</strong> ${stats.date}<br>
                    <strong>Unbatched Samples:</strong> <span class="badge bg-warning">${stats.unbatched_samples}</span><br>
                    <strong>Already Batched:</strong> <span class="badge bg-success">${stats.total_batched_samples}</span>
                </div>
                <div class="col-md-6">
                    <strong>Batches to Create:</strong> <span class="badge bg-primary">${estimatedBatches}</span><br>
                    <strong>Existing Batches:</strong> <span class="badge bg-info">${stats.total_batches}</span>
                </div>
            </div>
            ${stats.unbatched_samples === 0 ? 
                '<div class="alert alert-warning mt-3 mb-0"><small><i class="fas fa-info-circle me-1"></i>No unbatched samples found for this date.</small></div>' : 
                stats.unbatched_samples < 48 ?
                '<div class="alert alert-info mt-3 mb-0"><small><i class="fas fa-info-circle me-1"></i>Less than 48 samples - will create 1 partial batch.</small></div>' :
                ''}
        `;
        batchStatistics.innerHTML = html;
        batchStatistics.style.display = 'block';
        
        createBatchesBtn.disabled = stats.unbatched_samples === 0;
    }

    // Auto-load statistics when date changes
    batchDateInput.addEventListener('change', function() {
        batchStatistics.style.display = 'none';
        createBatchesBtn.disabled = false;
    });

    // Auto-load today's statistics
    if (batchDateInput.value === '{{ date("Y-m-d") }}') {
        loadStatsBtn.click();
    }
});

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

// Function to delete batch
function deleteBatch(batchId) {
    if (!confirm('Are you sure you want to delete this batch? All samples in this batch will be unbatched. This action cannot be undone.')) {
        return;
    }

    fetch(`{{ url('admin/batches') }}/${batchId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (response.ok) {
            location.reload();
        } else {
            throw new Error('Failed to delete batch');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting batch. Please try again.');
    });
}
</script>
@endpush