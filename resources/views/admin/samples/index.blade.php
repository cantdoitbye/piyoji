@extends('admin.layouts.app')

@section('title', 'Sample Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sample Management</h1>
            <p class="text-muted">Manage tea samples and batch assignments</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-info">
                <i class="fas fa-layer-group me-1"></i> Batch Management
            </a>
            <a href="{{ route('admin.samples.bulk-upload') }}" class="btn btn-outline-primary">
                <i class="fas fa-upload me-1"></i> Bulk Upload
            </a>
            <a href="{{ route('admin.samples.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Sample
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Samples</div>
                            <div class="h4 mb-0">{{ $statistics['total'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-flask fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Pending Evaluation</div>
                            <div class="h4 mb-0">{{ $statistics['pending_evaluation'] ?? 0 }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Approved</div>
                            <div class="h4 mb-0">{{ $statistics['approved'] ?? 0 }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Unbatched</div>
                            <div class="h4 mb-0">{{ $statistics['unbatched'] ?? 0 }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-layer-group fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Batched</div>
                            <div class="h4 mb-0">{{ $statistics['batched'] ?? 0 }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-boxes fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-dark text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Today's Samples</div>
                            <div class="h4 mb-0">{{ $statistics['today'] ?? 0 }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Actions Card -->
    @if($statistics['unbatched'] > 0)
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Batch Management Actions</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-2"><strong>{{ $statistics['unbatched'] }} samples</strong> are waiting to be assigned to batches.</p>
                    <p class="mb-0 text-muted small">Create batches for samples by date. Each batch will contain exactly 48 samples.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button type="button" class="btn btn-warning btn-lg" data-bs-toggle="modal" data-bs-target="#batchAssignmentModal">
                        <i class="fas fa-layer-group me-2"></i>Assign Batches
                    </button>
                </div>
            </div>
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
                           value="{{ request('search') }}" placeholder="Sample name, ID, or batch...">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                        <option value="pending_evaluation" {{ request('status') === 'pending_evaluation' ? 'selected' : '' }}>Pending Evaluation</option>
                        <option value="evaluated" {{ request('status') === 'evaluated' ? 'selected' : '' }}>Evaluated</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="batch_status" class="form-label">Batch Status</label>
                    <select class="form-select" id="batch_status" name="batch_status">
                        <option value="">All</option>
                        <option value="unbatched" {{ request('batch_status') === 'unbatched' ? 'selected' : '' }}>Unbatched</option>
                        <option value="batched" {{ request('batch_status') === 'batched' ? 'selected' : '' }}>Batched</option>
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

    <!-- Samples Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Samples</h5>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-secondary">{{ $samples->total() }} total</span>
                <a href="{{ route('admin.samples.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download me-1"></i>Export
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($samples->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sample ID</th>
                                <th>Sample Name</th>
                                <th>Seller</th>
                                <th>Quantity</th>
                                <th>Weight</th>
                                <th>Batch Status</th>
                                <th>Arrival Date</th>
                                <th>Status</th>
                                <th>Evaluation</th>
                                <th>Score</th>
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
                                        @if($sample->batch_id)
                                            <br><small class="text-muted">{{ $sample->batch_id }}</small>
                                        @endif
                                    </div>
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
                                    @if($sample->batch_group_id)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Batched
                                        </span>
                                        @if($sample->batchGroup)
                                            <br><small class="text-muted">{{ $sample->batchGroup->batch_number }}</small>
                                        @endif
                                    @else
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    {{ $sample->arrival_date ? $sample->arrival_date->format('M d, Y') : '-' }}
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
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($sample->evaluation_status === 'pending')
                                        <a href="{{ route('admin.samples.evaluate', $sample->id) }}" 
                                           class="btn btn-sm btn-outline-success" 
                                           title="Evaluate Sample">
                                            <i class="fas fa-clipboard-check"></i>
                                        </a>
                                        @endif
                                        
                                        <a href="{{ route('admin.samples.edit', $sample->id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Edit Sample">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $samples->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Batch Assignment Modal -->
<div class="modal fade" id="batchAssignmentModal" tabindex="-1" aria-labelledby="batchAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="batchAssignmentModalLabel">
                    <i class="fas fa-layer-group me-2"></i>Assign Samples to Batches
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="batchAssignmentForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="batch_date" class="form-label">Select Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="batch_date" name="batch_date" 
                                   value="{{ date('Y-m-d') }}" required>
                            <small class="form-text text-muted">Batches will be created for samples received on this date</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Batch Information</label>
                            <div class="alert alert-info mb-0">
                                <small>
                                    <strong>Batch Size:</strong> 48 samples per batch<br>
                                    <strong>Batch ID Format:</strong> BATCH20250811001<br>
                                    <strong>Sample ID Format:</strong> BATCH20250811001-01
                                </small>
                            </div>
                        </div>
                        <div class="col-12">
                            <div id="dateStatistics" class="alert alert-secondary" style="display: none;">
                                <!-- Date statistics will be loaded here -->
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="loadDateStats">
                    <i class="fas fa-search me-1"></i>Check Date Statistics
                </button>
                <button type="button" class="btn btn-primary" id="createBatches">
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
    const loadStatsBtn = document.getElementById('loadDateStats');
    const createBatchesBtn = document.getElementById('createBatches');
    const dateStatistics = document.getElementById('dateStatistics');

    // Load date statistics
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
                    displayDateStatistics(data.data);
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
                this.innerHTML = '<i class="fas fa-search me-1"></i>Check Date Statistics';
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

    function displayDateStatistics(stats) {
        const html = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Date:</strong> ${stats.date}<br>
                    <strong>Unbatched Samples:</strong> ${stats.unbatched_samples}<br>
                    <strong>Already Batched:</strong> ${stats.total_batched_samples}
                </div>
                <div class="col-md-6">
                    <strong>Estimated Batches:</strong> ${Math.ceil(stats.unbatched_samples / 48)}<br>
                    <strong>Existing Batches:</strong> ${stats.total_batches}
                </div>
            </div>
            ${stats.unbatched_samples === 0 ? 
                '<div class="alert alert-warning mt-2 mb-0"><small>No unbatched samples found for this date.</small></div>' : 
                ''}
        `;
        dateStatistics.innerHTML = html;
        dateStatistics.style.display = 'block';
        
        createBatchesBtn.disabled = stats.unbatched_samples === 0;
    }

    // Auto-load statistics when date changes
    batchDateInput.addEventListener('change', function() {
        dateStatistics.style.display = 'none';
        createBatchesBtn.disabled = false;
    });
});
</script>
@endpush
             