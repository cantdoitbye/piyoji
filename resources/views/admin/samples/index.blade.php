@extends('admin.layouts.app')

@section('title', 'Sample Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sample Management</h1>
            <p class="text-muted">Manage tea samples and evaluations</p>
        </div>
        <div class="d-flex gap-2">
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
                            <div class="h4 mb-0">{{ $statistics['pending_evaluation'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
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
                            <div class="small">Evaluated</div>
                            <div class="h4 mb-0">{{ $statistics['evaluated'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clipboard-check fa-2x opacity-75"></i>
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
                            <div class="h4 mb-0">{{ $statistics['approved'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Rejected</div>
                            <div class="h4 mb-0">{{ $statistics['rejected'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x opacity-75"></i>
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
                            <div class="small">This Month</div>
                            <div class="h4 mb-0">{{ $statistics['this_month'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.samples.index') }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="received" {{ $filters['status'] === 'received' ? 'selected' : '' }}>Received</option>
                            <option value="pending_evaluation" {{ $filters['status'] === 'pending_evaluation' ? 'selected' : '' }}>Pending Evaluation</option>
                            <option value="evaluated" {{ $filters['status'] === 'evaluated' ? 'selected' : '' }}>Evaluated</option>
                            <option value="approved" {{ $filters['status'] === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ $filters['status'] === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="assigned_to_buyers" {{ $filters['status'] === 'assigned_to_buyers' ? 'selected' : '' }}>Assigned to Buyers</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="evaluation_status" class="form-label">Evaluation Status</label>
                        <select name="evaluation_status" id="evaluation_status" class="form-select">
                            <option value="">All Evaluation Status</option>
                            <option value="pending" {{ $filters['evaluation_status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ $filters['evaluation_status'] === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ $filters['evaluation_status'] === 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="seller_id" class="form-label">Seller</label>
                        <select name="seller_id" id="seller_id" class="form-select">
                            <option value="">All Sellers</option>
                            @foreach($sellers as $seller)
                                <option value="{{ $seller->id }}" {{ $filters['seller_id'] == $seller->id ? 'selected' : '' }}>
                                    {{ $seller->seller_name }} ({{ $seller->tea_estate_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Sample name, ID, batch..." value="{{ $filters['search'] }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" 
                               value="{{ $filters['start_date'] }}">
                    </div>
                    
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" 
                               value="{{ $filters['end_date'] }}">
                    </div>
                    
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.samples.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                        <a href="{{ route('admin.samples.export') }}?{{ http_build_query($filters) }}" class="btn btn-outline-success">
                            <i class="fas fa-download me-1"></i> Export
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Links -->
   <div class="row mb-4">
    <div class="col-md-3">
        <a href="{{ route('admin.samples.index', ['evaluation_status' => 'pending']) }}" 
           class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h6>Pending Evaluations</h6>
                <p class="text-muted small mb-0">{{ $statistics['pending_evaluation'] }} samples</p>
            </div>
        </a>
    </div>
    
    <div class="col-md-3">
        <a href="{{ route('admin.samples.index', ['evaluation_status' => 'completed']) }}" 
           class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-check fa-2x text-info mb-2"></i>
                <h6>Evaluated Samples</h6>
                <p class="text-muted small mb-0">{{ $statistics['evaluated'] }} samples</p>
            </div>
        </a>
    </div>
    
    <div class="col-md-3">
        <a href="{{ route('admin.samples.index', ['status' => 'approved']) }}" 
           class="card text-decoration-none text-dark h-100">
            <div class="card-body text-center">
                <i class="fas fa-star fa-2x text-success mb-2"></i>
                <h6>Approved Samples</h6>
                <p class="text-muted small mb-0">{{ $statistics['approved'] }} samples</p>
            </div>
        </a>
    </div>
        
        <div class="col-md-3">
            <a href="#" class="card text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <i class="fas fa-chart-bar fa-2x text-primary mb-2"></i>
                    <h6>Tasting Report</h6>
                    <p class="text-muted small mb-0">Generate Reports</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Samples Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Samples</h5>
            <span class="badge bg-secondary">{{ $samples->total() }} total</span>
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
                                <th>Batch ID</th>
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
                                        <strong class="text-primary">{{ $sample->sample_id }}</strong>
                                    </td>
                                    <td>
                                        <div>{{ $sample->sample_name }}</div>
                                        @if($sample->sample_weight)
                                            <small class="text-muted">{{ $sample->sample_weight }} kg</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $sample->seller->seller_name }}</div>
                                        <small class="text-muted">{{ $sample->seller->tea_estate_name }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $sample->batch_id }}</span>
                                    </td>
                                    <td>
                                        {{ $sample->arrival_date->format('d M Y') }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $sample->status_badge_class }}">
                                            {{ $sample->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $sample->evaluation_status === 'completed' ? 'bg-success' : ($sample->evaluation_status === 'in_progress' ? 'bg-warning' : 'bg-secondary') }}">
                                            {{ $sample->evaluation_status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($sample->overall_score)
                                            <div class="d-flex align-items-center">
                                                <span class="badge {{ $sample->overall_score >= 8 ? 'bg-success' : ($sample->overall_score >= 6 ? 'bg-warning' : 'bg-danger') }}">
                                                    {{ $sample->overall_score }}/10
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.samples.show', $sample->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($sample->evaluation_status === 'pending' || $sample->evaluation_status === 'in_progress')
                                                <a href="{{ route('admin.samples.evaluate', $sample->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Evaluate">
                                                    <i class="fas fa-clipboard-check"></i>
                                                </a>
                                            @endif

                                             @if($sample->status === 'approved')
        <a href="{{ route('admin.samples.assign-buyers', $sample->id) }}" 
           class="btn btn-sm btn-success" title="Assign to Buyers">
            <i class="fas fa-users"></i>
        </a>
    @elseif($sample->status === 'assigned_to_buyers')
        <a href="{{ route('admin.samples.assign-buyers', $sample->id) }}" 
           class="btn btn-sm btn-outline-success" title="Manage Assignments">
            <i class="fas fa-edit"></i>
        </a>
    @endif
                                            
                                            <a href="{{ route('admin.samples.edit', $sample->id) }}" 
                                               class="btn btn-sm btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    title="Delete" onclick="deleteSample({{ $sample->id }})">
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
                <div class="d-flex justify-content-between align-items-center p-3">
                    <div class="text-muted">
                        Showing {{ $samples->firstItem() }} to {{ $samples->lastItem() }} of {{ $samples->total() }} results
                    </div>
                    {{ $samples->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No samples found</h5>
                    <p class="text-muted">Start by adding your first sample or adjust your filters.</p>
                    <a href="{{ route('admin.samples.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add First Sample
                    </a>
                </div>
            @endif
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteSample(sampleId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/admin/samples/${sampleId}`;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Auto-submit form on filter change for better UX
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status');
    const evaluationStatusSelect = document.getElementById('evaluation_status');
    const sellerSelect = document.getElementById('seller_id');
    
    [statusSelect, evaluationStatusSelect, sellerSelect].forEach(select => {
        if (select) {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
});
</script>
@endpush