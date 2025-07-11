@extends('admin.layouts.app')

@section('title', 'Edit Courier Service')
@section('subtitle', 'Update courier service information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.couriers.index') }}">Courier Services</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.couriers.show', $courier->id) }}">{{ $courier->company_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.couriers.show', $courier->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> View Details
        </a>
        @if($courier->api_endpoint)
            <button type="button" class="btn btn-outline-success" onclick="testApiConnection()">
                <i class="fas fa-vial me-1"></i> Test API
            </button>
        @endif
        <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Courier Service Information</h5>
                <div class="text-muted small">
                    <i class="fas fa-calendar me-1"></i>
                    Last updated: {{ $courier->updated_at->format('M d, Y \a\t g:i A') }}
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.couriers.update', $courier->id) }}" id="courierForm">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" 
                                   value="{{ old('company_name', $courier->company_name) }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">
                                Contact Person <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" name="contact_person" 
                                   value="{{ old('contact_person', $courier->contact_person) }}" required>
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $courier->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                Phone Number <span class="text-danger">*</span>
                            </label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" 
                                   value="{{ old('phone', $courier->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-12 mb-3">
                            <label for="service_areas" class="form-label">
                                Service Areas <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2 @error('service_areas') is-invalid @enderror" 
                                    id="service_areas" name="service_areas[]" multiple required>
                                @foreach($serviceAreas as $area)
                                    <option value="{{ $area }}" 
                                            {{ in_array($area, old('service_areas', $courier->service_areas ?? [])) ? 'selected' : '' }}>
                                        {{ $area }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_areas')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select all areas where this courier service operates</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('status', $courier->status) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- API Integration -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="text-primary mb-0">
                                    <i class="fas fa-code me-2"></i>API Integration
                                </h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_api" 
                                           {{ old('api_endpoint', $courier->api_endpoint) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_api">
                                        Enable API Integration
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="api_fields" style="display: {{ old('api_endpoint', $courier->api_endpoint) ? 'block' : 'none' }};">
                            <div class="col-12 mb-3">
                                <label for="api_endpoint" class="form-label">
                                    API Endpoint URL
                                </label>
                                <input type="url" class="form-control @error('api_endpoint') is-invalid @enderror" 
                                       id="api_endpoint" name="api_endpoint" 
                                       value="{{ old('api_endpoint', $courier->api_endpoint) }}"
                                       placeholder="https://api.courierservice.com/v1">
                                @error('api_endpoint')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="api_username" class="form-label">API Username</label>
                                    <input type="text" class="form-control @error('api_username') is-invalid @enderror" 
                                           id="api_username" name="api_username" 
                                           value="{{ old('api_username', $courier->api_username) }}">
                                    @error('api_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="api_password" class="form-label">API Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('api_password') is-invalid @enderror" 
                                               id="api_password" name="api_password" 
                                               placeholder="Leave blank to keep current password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    @error('api_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="api_token" class="form-label">API Token/Key</label>
                                <textarea class="form-control @error('api_token') is-invalid @enderror" 
                                          id="api_token" name="api_token" rows="3"
                                          placeholder="Leave blank to keep current token">{{ old('api_token') }}</textarea>
                                @error('api_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    @if($courier->api_token)
                                        <span class="text-success"><i class="fas fa-check me-1"></i>API token is currently configured</span>
                                    @else
                                        <span class="text-muted">No API token configured</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="webhook_url" class="form-label">Webhook URL</label>
                                <input type="url" class="form-control @error('webhook_url') is-invalid @enderror" 
                                       id="webhook_url" name="webhook_url" 
                                       value="{{ old('webhook_url', $courier->webhook_url) }}"
                                       placeholder="https://yoursite.com/webhook/courier-updates">
                                @error('webhook_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">URL where courier will send shipment status updates</div>
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="tracking_url_template" class="form-label">Tracking URL Template</label>
                                <input type="text" class="form-control @error('tracking_url_template') is-invalid @enderror" 
                                       id="tracking_url_template" name="tracking_url_template" 
                                       value="{{ old('tracking_url_template', $courier->tracking_url_template) }}"
                                       placeholder="https://track.courierservice.com/track/{tracking_number}">
                                @error('tracking_url_template')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Use {tracking_number} as placeholder for tracking number</div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-sticky-note me-2"></i>Additional Information
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Any additional notes, pricing info, or special instructions">{{ old('remarks', $courier->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-between">
                                <div>
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteCourier({{ $courier->id }})">
                                        <i class="fas fa-trash me-1"></i> Delete Courier Service
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.couriers.show', $courier->id) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="reset" class="btn btn-outline-warning">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                    @if($courier->api_endpoint)
                                        <button type="button" class="btn btn-outline-info" onclick="testApiConnection()">
                                            <i class="fas fa-vial me-1"></i> Test API
                                        </button>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Courier Service
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar with Help and History -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Edit History</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Service Added</h6>
                            <p class="timeline-description">{{ $courier->created_at->format('F j, Y \a\t g:i A') }}</p>
                            <span class="timeline-date">{{ $courier->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Last Updated</h6>
                            <p class="timeline-description">{{ $courier->updated_at->format('F j, Y \a\t g:i A') }}</p>
                            <span class="timeline-date">{{ $courier->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Information</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>API Integration Tips:</h6>
                    <ul class="mb-0 small">
                        <li>Test API connection after updating credentials</li>
                        <li>Webhook URL should be publicly accessible</li>
                        <li>Use {tracking_number} in tracking URL template</li>
                        <li>Leave password/token blank to keep existing values</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt me-2"></i>Security:</h6>
                    <p class="mb-0 small">
                        All API credentials are encrypted. Existing passwords and tokens are preserved when fields are left blank.
                    </p>
                </div>
            </div>
        </div>

        <!-- Current API Status -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-code me-2"></i>Current API Status</h6>
            </div>
            <div class="card-body">
                @if($courier->api_endpoint)
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-2"></i>API Enabled</h6>
                    <div class="small">
                        <strong>Endpoint:</strong> {{ $courier->api_endpoint }}<br>
                        <strong>Username:</strong> {{ $courier->api_username ?: 'Not set' }}<br>
                        <strong>Token:</strong> {{ $courier->api_token ? 'Configured' : 'Not set' }}<br>
                        <strong>Webhook:</strong> {{ $courier->webhook_url ?: 'Not set' }}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success mt-2" onclick="testApiConnection()">
                        <i class="fas fa-vial me-1"></i>Test Connection
                    </button>
                </div>
                @else
                <div class="alert alert-secondary">
                    <h6><i class="fas fa-times-circle me-2"></i>API Not Configured</h6>
                    <p class="mb-2 small">Enable API integration to access automated features.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="$('#enable_api').click()">
                        <i class="fas fa-plus me-1"></i>Setup API
                    </button>
                </div>
                @endif
            </div>
        </div>

        <!-- Current Statistics -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Current Statistics</h6>
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
                        <h4 class="text-success mb-0">{{ $courier->service_areas ? count($courier->service_areas) : 0 }}</h4>
                        <small class="text-muted">Service Areas</small>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded">
                            <strong>Current Status:</strong>
                            <span class="status-badge {{ $courier->status ? 'status-active' : 'status-inactive' }} ms-2">
                                {{ $courier->status_text }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    height: 100%;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-title {
    font-size: 0.9rem;
    margin-bottom: 5px;
}

.timeline-description {
    font-size: 0.8rem;
    margin-bottom: 5px;
    color: #6c757d;
}

.timeline-date {
    font-size: 0.75rem;
    color: #adb5bd;
}

.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for service areas
    $('#service_areas').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select service areas...',
        allowClear: true,
        tags: true // Allow custom entries
    });

    // Handle API integration toggle
    $('#enable_api').change(function() {
        if (this.checked) {
            $('#api_fields').slideDown();
            $('#api_endpoint').attr('required', true);
        } else {
            $('#api_fields').slideUp();
            $('#api_endpoint').attr('required', false);
            // Clear API fields except those that might have existing values
            $('#api_endpoint, #api_username, #webhook_url, #tracking_url_template').val('');
            $('#api_password, #api_token').val('');
        }
    });

    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#api_password');
        const icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form validation
    $('#courierForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate service areas selection
        const serviceAreas = $('#service_areas').val();
        if (!serviceAreas || serviceAreas.length === 0) {
            showError('service_areas', 'Please select at least one service area');
            isValid = false;
        }
        
        // Validate API fields if enabled
        if ($('#enable_api').is(':checked')) {
            const apiEndpoint = $('#api_endpoint').val();
            if (!apiEndpoint) {
                showError('api_endpoint', 'API endpoint is required when API integration is enabled');
                isValid = false;
            } else if (!isValidUrl(apiEndpoint)) {
                showError('api_endpoint', 'Please enter a valid API endpoint URL');
                isValid = false;
            }
            
            const trackingTemplate = $('#tracking_url_template').val();
            if (trackingTemplate && !trackingTemplate.includes('{tracking_number}')) {
                showError('tracking_url_template', 'Tracking URL template must contain {tracking_number} placeholder');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fix the errors and try again');
        } else {
            showLoading();
        }
    });
});

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

function testApiConnection() {
    showLoading();
    
    $.ajax({
        url: '{{ route("admin.couriers.test-api", $courier->id) }}',
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
    if (!confirmDelete('Are you sure you want to delete this courier service? This action cannot be undone and will affect all related shipments.')) {
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

function showError(fieldId, message) {
    const field = $('#' + fieldId);
    field.addClass('is-invalid');
    
    // Remove existing error message
    field.siblings('.invalid-feedback').remove();
    
    // Add new error message
    field.after('<div class="invalid-feedback">' + message + '</div>');
}

// Clear error state on input
$('.form-control, .form-select').on('input change', function() {
    $(this).removeClass('is-invalid');
    $(this).siblings('.invalid-feedback').remove();
});
</script>
@endpush