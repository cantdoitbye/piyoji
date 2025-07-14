@extends('admin.layouts.app')

@section('title', 'Edit Logistic Company')
@section('subtitle', 'Update logistic company information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.logistics.index') }}">Logistic Companies</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.logistics.show', $company->id) }}">{{ $company->company_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.logistics.show', $company->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> View Details
        </a>
        <a href="{{ route('admin.logistics.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Company Information</h5>
                <div class="text-muted small">
                    <i class="fas fa-calendar me-1"></i>
                    Last updated: {{ $company->updated_at->format('M d, Y \a\t g:i A') }}
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.logistics.update', $company->id) }}" method="POST" id="logisticForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" value="{{ old('company_name', $company->company_name) }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" name="contact_person" value="{{ old('contact_person', $company->contact_person) }}" required>
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $company->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $company->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address Information</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address', $company->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city', $company->city) }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" value="{{ old('state', $company->state) }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   id="pincode" name="pincode" value="{{ old('pincode', $company->pincode) }}" required>
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Service Coverage -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-route me-2"></i>Service Coverage</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supported_regions" class="form-label">Supported Regions <span class="text-danger">*</span></label>
                            <select class="form-select @error('supported_regions') is-invalid @enderror" 
                                    id="supported_regions" name="supported_regions[]" multiple required>
                                @foreach($regionOptions as $region)
                                    <option value="{{ $region }}" 
                                        {{ in_array($region, old('supported_regions', $company->supported_regions ?? [])) ? 'selected' : '' }}>
                                        {{ $region }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supported_regions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select multiple regions</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="supported_routes" class="form-label">Supported Routes <span class="text-danger">*</span></label>
                            <select class="form-select @error('supported_routes') is-invalid @enderror" 
                                    id="supported_routes" name="supported_routes[]" multiple required>
                                @foreach($routeOptions as $route)
                                    <option value="{{ $route }}" 
                                        {{ in_array($route, old('supported_routes', $company->supported_routes ?? [])) ? 'selected' : '' }}>
                                        {{ $route }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supported_routes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select multiple routes</div>
                        </div>
                    </div>

                    <!-- Pricing Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-rupee-sign me-2"></i>Pricing Information</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="pricing_type" class="form-label">Pricing Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('pricing_type') is-invalid @enderror" 
                                    id="pricing_type" name="pricing_type" required>
                                <option value="">Select Pricing Type</option>
                                @foreach($pricingTypeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('pricing_type', $company->pricing_type) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pricing_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row pricing-fields">
                            <div class="col-md-4">
                                <div class="mb-3" id="per_kg_rate_group">
                                    <label for="per_kg_rate" class="form-label">Rate per Kg (₹)</label>
                                    <input type="number" step="0.01" class="form-control @error('per_kg_rate') is-invalid @enderror" 
                                           id="per_kg_rate" name="per_kg_rate" value="{{ old('per_kg_rate', $company->per_kg_rate) }}">
                                    @error('per_kg_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3" id="per_km_rate_group">
                                    <label for="per_km_rate" class="form-label">Rate per Km (₹)</label>
                                    <input type="number" step="0.01" class="form-control @error('per_km_rate') is-invalid @enderror" 
                                           id="per_km_rate" name="per_km_rate" value="{{ old('per_km_rate', $company->per_km_rate) }}">
                                    @error('per_km_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3" id="base_rate_group">
                                    <label for="base_rate" class="form-label">Base Rate (₹)</label>
                                    <input type="number" step="0.01" class="form-control @error('base_rate') is-invalid @enderror" 
                                           id="base_rate" name="base_rate" value="{{ old('base_rate', $company->base_rate) }}">
                                    @error('base_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="mb-3" id="pricing_structure_group">
                                <label for="pricing_structure" class="form-label">Custom Pricing Structure</label>
                                <textarea class="form-control @error('pricing_structure') is-invalid @enderror" 
                                          id="pricing_structure" name="pricing_structure" rows="4" 
                                          placeholder="Describe your custom pricing structure...">{{ old('pricing_structure', $company->pricing_structure) }}</textarea>
                                @error('pricing_structure')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-plus-circle me-2"></i>Additional Information</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="service_description" class="form-label">Service Description</label>
                            <textarea class="form-control @error('service_description') is-invalid @enderror" 
                                      id="service_description" name="service_description" rows="3" 
                                      placeholder="Describe the services offered...">{{ old('service_description', $company->service_description) }}</textarea>
                            @error('service_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gstin" class="form-label">GSTIN</label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                   id="gstin" name="gstin" value="{{ old('gstin', $company->gstin) }}" 
                                   placeholder="22AAAAA0000A1Z5">
                            @error('gstin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pan" class="form-label">PAN</label>
                            <input type="text" class="form-control @error('pan') is-invalid @enderror" 
                                   id="pan" name="pan" value="{{ old('pan', $company->pan) }}" 
                                   placeholder="AAAAA0000A">
                            @error('pan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Any additional remarks...">{{ old('remarks', $company->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="status" name="status" value="1" 
                                       {{ old('status', $company->status) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    Active Status
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-save me-1"></i> Update Company
                            </button>
                            <a href="{{ route('admin.logistics.show', $company->id) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-1"></i> View
                            </a>
                            <a href="{{ route('admin.logistics.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Information Panel -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Company Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="me-3">
                        <span class="status-badge status-{{ $company->status ? 'active' : 'inactive' }}">
                            {{ $company->status_text }}
                        </span>
                    </div>
                    <div>
                        <small class="text-muted">Current Status</small>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="mb-0">{{ count($company->supported_regions ?? []) }}</h5>
                        <small class="text-muted">Regions</small>
                    </div>
                    <div class="col-6">
                        <h5 class="mb-0">{{ count($company->supported_routes ?? []) }}</h5>
                        <small class="text-muted">Routes</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Timeline</h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <strong>Created:</strong>
                        <div class="text-muted small">{{ $company->created_at->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                    <div class="timeline-item mt-2">
                        <strong>Last Updated:</strong>
                        <div class="text-muted small">{{ $company->updated_at->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Quick Tips</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <small>
                        <strong>Editing Tips:</strong><br>
                        • GSTIN format: 22AAAAA0000A1Z5<br>
                        • PAN format: AAAAA0000A<br>
                        • Choose appropriate pricing type<br>
                        • Select multiple regions for better coverage
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#supported_regions, #supported_routes').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select options...',
        allowClear: true,
        tags: true
    });

    // Show/hide pricing fields based on pricing type
    function togglePricingFields() {
        const pricingType = $('#pricing_type').val();
        
        // Hide all pricing fields
        $('.pricing-fields .mb-3').hide();
        $('#pricing_structure_group').hide();
        
        // Show relevant fields based on pricing type
        switch(pricingType) {
            case 'per_kg':
                $('#per_kg_rate_group').show();
                break;
            case 'per_km':
                $('#per_km_rate_group').show();
                break;
            case 'flat_rate':
                $('#base_rate_group').show();
                break;
            case 'custom':
                $('#pricing_structure_group').show();
                break;
        }
    }

    $('#pricing_type').change(togglePricingFields);
    
    // Trigger change event on page load to show relevant fields
    togglePricingFields();

    // Format GSTIN input
    $('#gstin').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Format PAN input
    $('#pan').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Form validation
    $('#logisticForm').on('submit', function(e) {
        const pricingType = $('#pricing_type').val();
        let isValid = true;
        
        // Reset previous validation
        $('.is-invalid').removeClass('is-invalid');
        
        // Validate pricing fields based on type
        switch(pricingType) {
            case 'per_kg':
                if (!$('#per_kg_rate').val() || $('#per_kg_rate').val() <= 0) {
                    $('#per_kg_rate').addClass('is-invalid');
                    isValid = false;
                }
                break;
            case 'per_km':
                if (!$('#per_km_rate').val() || $('#per_km_rate').val() <= 0) {
                    $('#per_km_rate').addClass('is-invalid');
                    isValid = false;
                }
                break;
            case 'flat_rate':
                if (!$('#base_rate').val() || $('#base_rate').val() <= 0) {
                    $('#base_rate').addClass('is-invalid');
                    isValid = false;
                }
                break;
            case 'custom':
                if (!$('#pricing_structure').val().trim()) {
                    $('#pricing_structure').addClass('is-invalid');
                    isValid = false;
                }
                break;
        }
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fill in all required pricing information.');
        }
    });
});
</script>
@endpush