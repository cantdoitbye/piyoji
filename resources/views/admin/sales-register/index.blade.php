@extends('admin.layouts.app')

@section('title', 'Sales Register')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sales Register</h1>
            <p class="text-muted">Manage direct sales entries from big clients</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales-register.report') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> Sales Report
            </a>
            <a href="{{ route('admin.sales-register.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Sales Entry
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Total Entries</div>
                            <div class="h4 mb-0">{{ $statistics['total'] }}</div>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Pending Approval</div>
                            <div class="h4 mb-0">{{ $statistics['pending'] }}</div>
                            <small>₹{{ number_format($statistics['pending_amount'], 2) }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x opacity-75"></i>
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
                            <div class="small">Approved</div>
                            <div class="h4 mb-0">{{ $statistics['approved'] }}</div>
                            <small>₹{{ number_format($statistics['total_approved_amount'], 2) }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="small">Rejected</div>
                            <div class="h4 mb-0">{{ $statistics['rejected'] }}</div>
                            <small>Today: {{ $statistics['today_entries'] }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-times-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Entry ID, product, buyer...">
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="buyer_id" class="form-label">Buyer</label>
                    <select class="form-select" id="buyer_id" name="buyer_id">
                        <option value="">All Buyers</option>
                        @foreach($buyers as $buyer)
                            <option value="{{ $buyer->id }}" {{ request('buyer_id') == $buyer->id ? 'selected' : '' }}>
                                {{ $buyer->buyer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="tea_grade" class="form-label">Tea Grade</label>
                    <select class="form-select" id="tea_grade" name="tea_grade">
                        <option value="">All Grades</option>
                        @foreach($teaGrades as $code => $name)
                            <option value="{{ $code }}" {{ request('tea_grade') === $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" 
                           value="{{ request('start_date') }}">
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

    <!-- Sales Entries Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Sales Entries</h5>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-secondary">{{ $salesEntries->total() }} total</span>
                <a href="{{ route('admin.sales-register.export', request()->query()) }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-download me-1"></i>Export
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($salesEntries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entry ID</th>
                                <th>Buyer</th>
                                <th>Product & Grade</th>
                                <th>Quantity</th>
                                <th>Rate/KG</th>
                                <th>Total Amount</th>
                                <th>Entry Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesEntries as $entry)
                            <tr>
                                <td>
                                    <strong class="text-primary">#{{ $entry->sales_entry_id }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $entry->buyer->buyer_name }}</strong>
                                        <br><small class="text-muted">{{ $entry->buyer->buyer_type }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $entry->product_name }}</strong>
                                        <br><small class="text-muted">Grade: {{ $entry->tea_grade }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $entry->quantity_kg }} kg</span>
                                </td>
                                <td>
                                    <strong>{{ $entry->formatted_rate_per_kg }}</strong>
                                </td>
                                <td>
                                    <strong class="text-success">{{ $entry->formatted_total_amount }}</strong>
                                </td>
                                <td>
                                    {{ $entry->entry_date->format('M d, Y') }}
                                </td>
                                <td>
                                    <span class="badge {{ $entry->status_badge_class }}">
                                        {{ $entry->status_label }}
                                    </span>
                                    @if($entry->status === 'approved' && $entry->approved_at)
                                        <br><small class="text-muted">{{ $entry->approved_at->format('M d, H:i') }}</small>
                                    @elseif($entry->status === 'rejected' && $entry->rejected_at)
                                        <br><small class="text-muted">{{ $entry->rejected_at->format('M d, H:i') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.sales-register.show', $entry->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($entry->status === 'pending')
                                        <a href="{{ route('admin.sales-register.edit', $entry->id) }}" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Edit Entry">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-success" 
                                                onclick="approveEntry({{ $entry->id }})"
                                                title="Approve Entry">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="rejectEntry({{ $entry->id }})"
                                                title="Reject Entry">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $salesEntries->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No sales entries found</h5>
                    <p class="text-muted">Start by adding your first sales entry.</p>
                    <a href="{{ route('admin.sales-register.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Sales Entry
                    </a>
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