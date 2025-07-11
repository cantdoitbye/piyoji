@extends('admin.layouts.app')

@section('title', 'Courier Service Details')
@section('subtitle', 'View courier service information and shipment history')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.couriers.index') }}">Courier Services</a></li>
    <li class="breadcrumb-item active">{{ $courier->company_name }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.couriers.edit', $courier->id) }}" class="btn btn-primary">
            <i class="fas fa-edit me-1"></i> Edit Courier
        </a>
        @if($courier->api_endpoint)
            <button type="button" class="btn btn-outline-info" onclick="testApi({{ $courier->id }})">
                <i class="fas fa-vial me-1"></i> Test API
            </button>
        @endif
        <button type="button" class="btn btn-outline-{{ $courier->status ? 'warning' : 'success' }}" 
                onclick="toggleStatus({{ $courier->id }}, {{ $courier->status ? 'false' : 'true' }})">
            <i class="fas fa-{{ $courier->status ? 'pause' : 'play' }} me-1"></i>
            {{ $courier->status ? 'Deactivate' : 'Activate' }}
        </button>
        <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Company Name</label>
                        <div class="fw-bold">{{ $courier->company_name }}</div>
                    <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Company Name</label>
                        <div class="fw-bold">{{ $courier->company_name }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Contact Person</label>
                        <div>{{ $courier->contact_person }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Email Address</label>
                        <div>
                            <a href="mailto:{{ $courier->email }}" class="text-decoration-none">
                                <i class="fas fa-envelope me-1"></i>{{ $courier->email }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Phone Number</label>
                        <div>
                            <a href="tel:{{ $courier->phone }}" class="text-decoration-none">
                                <i class="fas fa-phone me-1"></i>{{ $courier->phone }}
                            </a>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            <span class="status-badge {{ $courier->status ? 'status-active' : 'status-inactive' }}">
                                <i class="fas fa-{{ $courier->status ? 'check-circle' : 'pause-circle' }} me-1"></i>
                                {{ $courier->status_text }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">API Integration</label>
                        <div>
                            @if($courier->api_endpoint)
                                <span class="badge bg-success fs-6">
                                    <i class="fas fa-check me-1"></i>Integrated
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-info ms-2" onclick="testApi({{ $courier->id }})">
                                    <i class="fas fa-vial"></i> Test
                                </button>
                            @else
                                <span class="badge bg-secondary fs-6">
                                    <i class="fas fa-times me-1"></i>Manual
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Areas -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-map me-2"></i>Service Areas</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <label class="form-label text-muted">Coverage Areas</label>
                        <div>
                            @if(is_array($courier->service_areas))
                                @foreach($courier->service_areas as $area)
                                    <span class="badge bg-primary me-1 mb-1">{{ $area }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No service areas specified</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Integration Details -->
        @if($courier->api_endpoint)
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-code me-2"></i>API Integration Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">API Endpoint</label>
                        <div class="font-monospace">{{ $courier->api_endpoint }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">API Username</label>
                        <div>{{ $courier->api_username ?: 'Not specified' }}</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">API Token</label>
                        <div>
                            @if($courier->api_token)
                                <span class="badge bg-success">
                                    <i class="fas fa-key me-1"></i>Configured
                                </span>
                            @else
                                <span class="text-muted">Not configured</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted">Webhook URL</label>
                        <div class="font-monospace">{{ $courier->webhook_url ?: 'Not specified' }}</div>
                    </div>
                    @if($courier->tracking_url_template)
                    <div class="col-12 mb-3">
                        <label class="form-label text-muted">Tracking URL Template</label>
                        <div class="font-monospace">{{ $courier->tracking_url_template }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Shipments -->
        <!-- Note: Shipment functionality will be available in Module 3 -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-box me-2"></i>Recent Shipments</h5>
                <span class="badge bg-secondary">Coming Soon</span>
            </div>
            <div class="card-body">
                <div class="text-center py-4">
                    <i class="fas fa-box fa-3x text-muted mb-3"></i>
                    {{-- <p class="text-muted">Shipment tracking will be available in Module 3</p> --}}
                    <small class="text-muted">Track sample dispatches and deliveries</small>
                </div>
            </div>
        </div>                <th>From</th>
                                <th>To</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courier->shipments->take(5) as $shipment)
                            <tr>
                                <td class="font-monospace">{{ $shipment->tracking_number }}</td>
                                <td>{{ $shipment->created_at->format('M d, Y') }}</td>
                                <td>{{ $shipment->sender_city ?? 'N/A' }}</td>
                                <td>{{ $shipment->receiver_city ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-{{ $shipment->status === 'delivered' ? 'success' : ($shipment->status === 'in_transit' ? 'primary' : 'warning') }}">
                                        {{ ucfirst(str_replace('_', ' ', $shipment->status ?? 'pending')) }}
                                    </span>
                                </td>
                                <td>
                                    @if($courier->tracking_url_template)
                                        <a href="{{ $courier->generateTrackingUrl($shipment->tracking_number) }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-external-link-alt"></i> Track
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($courier->shipments->count() > 5)
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-outline-primary">View All Shipments</a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Additional Information -->
        @if($courier->remarks)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Remarks</h5>
            </div>
            <div class="card-body">
                <div class="border rounded p-3 bg-light">{{ $courier->remarks }}</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border-end">
                            <h4 class="text-primary mb-0">0</h4>
                            <small class="text-muted">Total Shipments</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-0">0</h4>
                        <small class="text-muted">Delivered</small>
                    </div>
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-info mb-0">
                                {{ $courier->service_areas ? count($courier->service_areas) : 0 }}
                            </h4>
                            <small class="text-muted">Service Areas</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-warning mb-0">0</h4>
                        <small class="text-muted">In Transit</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- API Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-code me-2"></i>API Status</h6>
            </div>
            <div class="card-body">
                @if($courier->api_endpoint)
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>API Integrated</h6>
                    <ul class="mb-0 small">
                        <li>Automatic tracking updates</li>
                        <li>Real-time status sync</li>
                        <li>Webhook notifications</li>
                        <li>Rate calculations</li>
                    </ul>
                    <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="testApi({{ $courier->id }})">
                        <i class="fas fa-vial me-1"></i>Test Connection
                    </button>
                </div>
                @else
                <div class="alert alert-secondary">
                    <h6><i class="fas fa-tools me-2"></i>Manual Operations</h6>
                    <ul class="mb-0 small">
                        <li>Manual tracking updates</li>
                        <li>Email/phone communication</li>
                        <li>Manual status updates</li>
                        <li>No automated features</li>
                    </ul>
                    <a href="{{ route('admin.couriers.edit', $courier->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="fas fa-plus me-1"></i>Setup API
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Account Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Account Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Added Date</label>
                    <div>{{ $courier->created_at->format('F j, Y') }}</div>
                    <small class="text-muted">{{ $courier->created_at->diffForHumans() }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Last Updated</label>
                    <div>{{ $courier->updated_at->format('F j, Y') }}</div>
                    <small class="text-muted">{{ $courier->updated_at->diffForHumans() }}</small>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Service Status</label>
                    <div>
                        <span class="status-badge {{ $courier->status ? 'status-active' : 'status-inactive' }}">
                            {{ $courier->status_text }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.couriers.edit', $courier->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit Courier Service
                    </a>
                    @if($courier->api_endpoint)
                        <button type="button" class="btn btn-outline-info" onclick="testApi({{ $courier->id }})">
                            <i class="fas fa-vial me-2"></i>Test API Connection
                        </button>
                    @endif
                    <a href="#" class="btn btn-outline-success">
                        <i class="fas fa-plus me-2"></i>Create Shipment
                    </a>
                    <a href="#" class="btn btn-outline-info">
                        <i class="fas fa-history me-2"></i>View All Shipments
                    </a>
                    <button type="button" class="btn btn-outline-{{ $courier->status ? 'warning' : 'success' }}" 
                            onclick="toggleStatus({{ $courier->id }}, {{ $courier->status ? 'false' : 'true' }})">
                        <i class="fas fa-{{ $courier->status ? 'pause' : 'play' }} me-2"></i>
                        {{ $courier->status ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="deleteCourier({{ $courier->id }})">
                        <i class="fas fa-trash me-2"></i>Delete Courier Service
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(id, status) {
    if (!confirm('Are you sure you want to ' + (status ? 'activate' : 'deactivate') + ' this courier service?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/couriers/${id}/status`,
        method: 'PATCH',
        data: { status: status },
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error updating courier service status');
        }
    });
}

function testApi(id) {
    showLoading();
    
    $.ajax({
        url: `/admin/couriers/${id}/test-api`,
        method: 'POST',
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error testing API connection');
        }
    });
}

function deleteCourier(id) {
    if (!confirmDelete('Are you sure you want to delete this courier service? This action cannot be undone.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/couriers/${id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                window.location.href = '{{ route("admin.couriers.index") }}';
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error deleting courier service');
        }
    });
}
</script>
@endpush
              