@extends('admin.layouts.app')

@section('title', 'Logistic Company Details')
@section('subtitle', 'View logistic company information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.logistics.index') }}">Logistic Companies</a></li>
    <li class="breadcrumb-item active">{{ $company->company_name }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.logistics.edit', $company->id) }}" class="btn btn-outline-warning">
            <i class="fas fa-edit me-1"></i> Edit Company
        </a>
        <button type="button" class="btn btn-outline-info" id="calculateCostBtn">
            <i class="fas fa-calculator me-1"></i> Calculate Cost
        </button>
        <a href="{{ route('admin.logistics.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Company Information -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Company Information</h5>
                <span class="status-badge status-{{ $company->status ? 'active' : 'inactive' }}">
                    {{ $company->status_text }}
                </span>
            </div>
            <div class="card-body">
                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="140"><strong>Company Name:</strong></td>
                                <td>{{ $company->company_name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Contact Person:</strong></td>
                                <td>{{ $company->contact_person }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>
                                    <a href="mailto:{{ $company->email }}" class="text-primary">{{ $company->email }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>
                                    <a href="tel:{{ $company->phone }}" class="text-primary">{{ $company->phone }}</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td width="120"><strong>Pricing Type:</strong></td>
                                <td>
                                    <span class="badge bg-info">{{ $company->pricing_type_text }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Pricing:</strong></td>
                                <td>{{ $company->formatted_pricing }}</td>
                            </tr>
                            <tr>
                                <td><strong>GSTIN:</strong></td>
                                <td>{{ $company->gstin ?: 'Not provided' }}</td>
                            </tr>
                            <tr>
                                <td><strong>PAN:</strong></td>
                                <td>{{ $company->pan ?: 'Not provided' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h6>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            <strong>{{ $company->address }}</strong><br>
                            {{ $company->city }}, {{ $company->state }} - {{ $company->pincode }}
                        </div>
                    </div>
                </div>

                <!-- Service Description -->
                @if($company->service_description)
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-clipboard-list me-2"></i>Service Description</h6>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($company->service_description)) !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Remarks -->
                @if($company->remarks)
                <div class="row mb-4">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-sticky-note me-2"></i>Remarks</h6>
                    </div>
                    <div class="col-12">
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($company->remarks)) !!}
                        </div>
                    </div>
                </div>
                @endif

                <!-- Timeline -->
                <div class="row">
                    <div class="col-12">
                        <h6 class="text-primary mb-3"><i class="fas fa-clock me-2"></i>Timeline</h6>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Created:</strong> {{ $company->created_at->format('M d, Y H:i A') }}
                        </small>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">
                            <strong>Last Updated:</strong> {{ $company->updated_at->format('M d, Y H:i A') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Coverage -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-route me-2"></i>Service Coverage</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Supported Regions</h6>
                        <div class="d-flex flex-wrap">
                            @forelse($company->supported_regions ?? [] as $region)
                                <span class="badge bg-primary me-1 mb-1">{{ $region }}</span>
                            @empty
                                <span class="text-muted">No regions specified</span>
                            @endforelse
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Supported Routes</h6>
                        <div class="d-flex flex-wrap">
                            @forelse($company->supported_routes ?? [] as $route)
                                <span class="badge bg-secondary me-1 mb-1">{{ $route }}</span>
                            @empty
                                <span class="text-muted">No routes specified</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.logistics.edit', $company->id) }}" class="btn btn-warning btn-sm w-100 mb-2">
                    <i class="fas fa-edit me-1"></i> Edit Company
                </a>

                <button type="button" class="btn btn-{{ $company->status ? 'secondary' : 'success' }} btn-sm w-100 mb-2" 
                        id="toggleStatus" data-status="{{ $company->status ? 0 : 1 }}">
                    <i class="fas fa-{{ $company->status ? 'ban' : 'check' }} me-1"></i> 
                    {{ $company->status ? 'Deactivate' : 'Activate' }}
                </button>

                <button type="button" class="btn btn-info btn-sm w-100 mb-2" id="calculateCostBtn">
                    <i class="fas fa-calculator me-1"></i> Calculate Shipping Cost
                </button>

                <hr>

                <button type="button" class="btn btn-danger btn-sm w-100" id="deleteBtn">
                    <i class="fas fa-trash me-1"></i> Delete Company
                </button>
            </div>
        </div>

        <!-- Company Statistics -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="stats-card border">
                            <h4 class="mb-0 text-primary">{{ count($company->supported_regions ?? []) }}</h4>
                            <small class="text-muted">Regions Covered</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="stats-card border">
                            <h4 class="mb-0 text-success">{{ count($company->supported_routes ?? []) }}</h4>
                            <small class="text-muted">Routes Available</small>
                        </div>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Pricing Type
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                            {{ $company->pricing_type_text }}
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Status
                        </div>
                        <div class="h6 mb-0 font-weight-bold text-gray-800">
                            {{ $company->status_text }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-address-card me-2"></i>Contact Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <i class="fas fa-user-tie fa-2x text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-bold">{{ $company->contact_person }}</div>
                        <div class="text-muted small">Contact Person</div>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <i class="fas fa-envelope fa-2x text-primary"></i>
                    </div>
                    <div>
                        <a href="mailto:{{ $company->email }}" class="text-primary fw-bold">{{ $company->email }}</a>
                        <div class="text-muted small">Email Address</div>
                    </div>
                </div>

                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <i class="fas fa-phone fa-2x text-primary"></i>
                    </div>
                    <div>
                        <a href="tel:{{ $company->phone }}" class="text-primary fw-bold">{{ $company->phone }}</a>
                        <div class="text-muted small">Phone Number</div>
                    </div>
                </div>

                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="fas fa-map-marker-alt fa-2x text-primary"></i>
                    </div>
                    <div>
                        <div>{{ $company->full_address }}</div>
                        <div class="text-muted small">Address</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Details -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Pricing Details</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="badge bg-info fs-6">{{ $company->pricing_type_text }}</span>
                </div>
                
                <div class="text-center">
                    <h5 class="text-primary mb-0">{{ $company->formatted_pricing }}</h5>
                    <small class="text-muted">Current Rate</small>
                </div>

                @if($company->pricing_type === 'custom' && $company->pricing_structure)
                    <hr>
                    <div class="bg-light p-2 rounded">
                        <small>{{ $company->pricing_structure }}</small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Shipping Cost Calculator Modal -->
<div class="modal fade" id="costCalculatorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Shipping Cost Calculator</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <small><strong>Pricing Type:</strong> {{ $company->pricing_type_text }}</small>
                </div>

                @if($company->pricing_type === 'per_kg')
                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight (Kg)</label>
                        <input type="number" class="form-control" id="weight" min="0" step="0.01" placeholder="Enter weight">
                    </div>
                @elseif($company->pricing_type === 'per_km')
                    <div class="mb-3">
                        <label for="distance" class="form-label">Distance (Km)</label>
                        <input type="number" class="form-control" id="distance" min="0" step="0.01" placeholder="Enter distance">
                    </div>
                @elseif($company->pricing_type === 'flat_rate')
                    <div class="alert alert-success">
                        <strong>Flat Rate:</strong> {{ $company->formatted_pricing }}
                    </div>
                @else
                    <div class="alert alert-warning">
                        <strong>Custom Pricing:</strong><br>
                        {{ $company->pricing_structure }}
                    </div>
                @endif

                <hr>
                <div id="costResult" class="text-center" style="display: none;">
                    <h4 class="text-primary">Estimated Cost: ₹<span id="calculatedCost">0.00</span></h4>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                Are you sure you want to delete this logistic company? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('#toggleStatus').click(function() {
        const status = $(this).data('status');
        
        if (confirm(`Are you sure you want to ${status ? 'activate' : 'deactivate'} this company?`)) {
            $.ajax({
                url: `/admin/logistics/{{ $company->id }}/status`,
                method: 'PATCH',
                data: {
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Status updated successfully');
                        location.reload();
                    } else {
                        toastr.error('Error updating status');
                    }
                }
            });
        }
    });

    // Delete company
    $('#deleteBtn').click(function() {
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').click(function() {
        $.ajax({
            url: `/admin/logistics/{{ $company->id }}`,
            method: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Company deleted successfully');
                    window.location.href = '{{ route("admin.logistics.index") }}';
                } else {
                    toastr.error('Error deleting company');
                }
            }
        });
        $('#deleteModal').modal('hide');
    });

    // Cost calculator
    $('#calculateCostBtn').click(function() {
        $('#costCalculatorModal').modal('show');
    });

    @if($company->pricing_type === 'per_kg')
        $('#weight').on('input', function() {
            const weight = parseFloat($(this).val()) || 0;
            const rate = {{ $company->per_kg_rate ?? 0 }};
            
            if (weight > 0 && rate > 0) {
                const cost = weight * rate;
                $('#calculatedCost').text(cost.toFixed(2));
                $('#costResult').show();
            } else {
                $('#costResult').hide();
            }
        });
    @elseif($company->pricing_type === 'per_km')
        $('#distance').on('input', function() {
            const distance = parseFloat($(this).val()) || 0;
            const rate = {{ $company->per_km_rate ?? 0 }};
            
            if (distance > 0 && rate > 0) {
                const cost = distance * rate;
                $('#calculatedCost').text(cost.toFixed(2));
                $('#costResult').show();
            } else {
                $('#costResult').hide();
            }
        });
    @elseif($company->pricing_type === 'flat_rate')
        // Show flat rate immediately
        $('#calculatedCost').text('{{ $company->base_rate ?? 0 }}');
        $('#costResult').show();
    @endif
});
</script>
@endpush