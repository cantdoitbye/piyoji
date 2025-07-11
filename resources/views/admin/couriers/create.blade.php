@extends('admin.layouts.app')

@section('title', isset($courier) ? 'Edit Courier Service' : 'Add New Courier Service')
@section('subtitle', isset($courier) ? 'Update courier service information' : 'Add a new courier service to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.couriers.index') }}">Courier Services</a></li>
    <li class="breadcrumb-item active">{{ isset($courier) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($courier) ? 'Edit Courier Service Information' : 'Add New Courier Service' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($courier) ? route('admin.couriers.update', $courier->id) : route('admin.couriers.store') }}" id="courierForm">
                    @csrf
                    @if(isset($courier))
                        @method('PUT')
                    @endif

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
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" name="contact_person" 
                                   value="{{ old('contact_person', $courier->contact_person ?? '') }}" required>
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
                                   value="{{ old('email', $courier->email ?? '') }}" required>
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
                                   value="{{ old('phone', $courier->phone ?? '') }}" required>
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
                                            {{ old('status', $courier->status ?? 1) == $value ? 'selected' : '' }}>
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
                                    <i class="fas fa-code me-2"></i>API Integration (Optional)
                                </h6>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="enable_api" 
                                           {{ (old('api_endpoint', $courier->api_endpoint ?? '')) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_api">
                                        Enable API Integration
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div id="api_fields" style="display: {{ (old('api_endpoint', $courier->api_endpoint ?? '')) ? 'block' : 'none' }};">
                            <div class="col-12 mb-3">
                                <label for="api_endpoint" class="form-label">
                                    API Endpoint URL
                                </label>
                                <input type="url" class="form-control @error('api_endpoint') is-invalid @enderror" 
                                       id="api_endpoint" name="api_endpoint" 
                                       value="{{ old('api_endpoint', $courier->api_endpoint ?? '') }}"
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
                                           value="{{ old('api_username', $courier->api_username ?? '') }}">
                                    @error('api_username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="api_password" class="form-label">API Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('api_password') is-invalid @enderror" 
                                               id="api_password" name="api_password" 
                                               placeholder="{{ isset($courier) && $courier->api_password ? 'Leave blank to keep current password' : 'Enter API password' }}">
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
                                          placeholder="{{ isset($courier) && $courier->api_token ? 'Leave blank to keep current token' : 'Enter API token or key' }}">{{ old('api_token') }}</textarea>
                                @error('api_token')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-12 mb-3">
                                <label for="webhook_url" class="form-label">Webhook URL</label>
                                <input type="url" class="form-control @error('webhook_url') is-invalid @enderror" 
                                       id="webhook_url" name="webhook_url" 
                                       value="{{ old('webhook_url', $courier->webhook_url ?? '') }}"
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
                                       value="{{ old('tracking_url_template', $courier->tracking_url_template ?? '') }}"
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
                                      placeholder="Any additional notes, pricing info, or special instructions">{{ old('remarks', $courier->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <hr>
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.couriers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                @if(isset($courier) && $courier->api_endpoint)
                                    <button type="button" class="btn btn-outline-info" onclick="testApiConnection()">
                                        <i class="fas fa-vial me-1"></i> Test API
                                    </button>
                                @endif
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> 
                                    {{ isset($courier) ? 'Update Courier Service' : 'Create Courier Service' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar with Help -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Information</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Tips:</h6>
                    <ul class="mb-0 small">
                        <li>API integration enables automatic tracking updates</li>
                        <li>Webhook URL receives real-time shipment status</li>
                        <li>Use {tracking_number} placeholder in tracking URL</li>
                        <li>All API credentials are encrypted for security</li>
                        <li>Service areas can be regions or specific cities</li>
                    </ul>
                </div>

                @if(isset($courier))
                <div class="alert alert-success">
                    <h6><i class="fas fa-calendar me-2"></i>Courier Details:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $courier->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $courier->updated_at->format('M d, Y') }}</li>
                        <li><strong>Status:</strong> {{ $courier->status_text }}</li>
                        <li><strong>API Integration:</strong> {{ $courier->api_endpoint ? 'Enabled' : 'Disabled' }}</li>
                    </ul>
                </div>
                @endif

                <div class="alert alert-warning">
                    <h6><i class="fas fa-shield-alt me-2"></i>Security:</h6>
                    <p class="mb-0 small">
                        All API credentials (tokens, passwords) are encrypted before storage. Leave password/token fields blank when editing to keep existing values.
                    </p>
                </div>

                <div class="alert alert-primary">
                    <h6><i class="fas fa-code me-2"></i>API Features:</h6>
                    <ul class="mb-0 small">
                        <li>Automatic shipment tracking</li>
                        <li>Real-time status updates</li>
                        <li>Delivery confirmations</li>
                        <li>Rate calculations (if supported)</li>
                    </ul>
                </div>
            </div>
        </div>

        @if(isset($courier) && $courier->shipments && $courier->shipments->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-box me-2"></i>Recent Shipments</h6>
            </div>
            <div class="card-body">
                @foreach($courier->shipments->take(3) as $shipment)
                <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <div class="fw-bold">{{ $shipment->tracking_number }}</div>
                        <small class="text-muted">{{ $shipment->created_at->format('M d, Y') }}</small>
                    </div>
                    <span class="badge bg-{{ $shipment->status === 'delivered' ? 'success' : 'primary' }}">
                        {{ ucfirst($shipment->status) }}
                    </span>
                </div>
                @endforeach
                @if($courier->shipments->count() > 3)
                <div class="text-center mt-2">
                    <a href="#" class="btn btn-sm btn-outline-primary">View All Shipments</a>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
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
            // Clear API fields
            $('#api_endpoint, #api_username, #api_password, #api_token, #webhook_url, #tracking_url_template').val('');
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
    @if(isset($courier))
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
    @endif
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