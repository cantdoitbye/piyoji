@extends('admin.layouts.app')

@section('title', isset($garden) ? 'Edit Garden' : 'Add New Garden')
@section('subtitle', isset($garden) ? 'Update garden information' : 'Add a new tea garden to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.gardens.index') }}">Gardens</a></li>
    <li class="breadcrumb-item active">{{ isset($garden) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.gardens.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($garden) ? 'Edit Garden Information' : 'Add New Garden' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($garden) ? route('admin.gardens.update', $garden->id) : route('admin.gardens.store') }}" id="gardenForm">
                    @csrf
                    @if(isset($garden))
                        @method('PUT')
                    @endif

                    <!-- 3-Level Dependent Dropdown Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-filter me-2"></i>Tea Selection Filters
                            </h6>
                        </div>
                        
                        <!-- Step 1: Category Selection -->
                        <div class="col-md-4 mb-3">
                            <label for="selected_category" class="form-label">
                                1. Select Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('selected_category') is-invalid @enderror" 
                                    id="selected_category" name="selected_category" required>
                                <option value="">Choose Category</option>
                                @foreach(\App\Models\Tea::getCategoryOptions() as $key => $value)
                                    <option value="{{ $key }}" {{ old('selected_category', $garden->selected_category ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selected_category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Step 2: Tea Type Selection (Dependent on Category) -->
                        <div class="col-md-4 mb-3">
                            <label for="selected_tea_type" class="form-label">
                                2. Select Tea Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('selected_tea_type') is-invalid @enderror" 
                                    id="selected_tea_type" name="selected_tea_type" required disabled>
                                <option value="">First select category</option>
                            </select>
                            @error('selected_tea_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Step 3: Grade Codes Selection (Dependent on Tea Type) -->
                        <div class="col-md-4 mb-3">
                            <label for="filtered_grade_codes" class="form-label">
                                3. Available Grade Codes
                            </label>
                            <select class="form-select @error('filtered_grade_codes') is-invalid @enderror" 
                                    id="filtered_grade_codes" name="filtered_grade_codes[]" multiple disabled>
                                <option value="">First select tea type</option>
                            </select>
                            <div class="form-text">
                                Grade codes will be filtered based on your tea type selection
                            </div>
                            @error('filtered_grade_codes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Sub Tea Type (Static - Not part of dependent logic) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="sub_tea_type" class="form-label">Sub Tea Type</label>
                            <select class="form-select" id="sub_tea_type" name="sub_tea_type">
                                <option value="">Select Sub Tea Type (Optional)</option>
                                @foreach(\App\Models\Tea::getSubTeaTypeOptions() as $key => $value)
                                    <option value="{{ $key }}" {{ old('sub_tea_type', $garden->sub_tea_type ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">
                                This field is independent of the dependent selection logic above
                            </div>
                        </div>
                    </div>

                    <!-- Final Tea Selection (Based on Filters) - ONLY THIS ONE -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Filtered Tea Varieties <span class="text-danger">*</span>
                            </h6>
                        </div>
                        
                        <div class="col-12" id="filtered-tea-selection">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Please complete the category and tea type selection above to see available tea varieties.
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-seedling me-2"></i>Garden Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="garden_name" class="form-label">
                                Garden Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('garden_name') is-invalid @enderror" 
                                   id="garden_name" name="garden_name" 
                                   value="{{ old('garden_name', $garden->garden_name ?? '') }}" required>
                            @error('garden_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('status', $garden->status ?? 1) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                     


                         <div class="col-md-6 mb-3">
                            <label for="poc" class="form-label">POC</label>
                            <select class="form-select @error('poc') is-invalid @enderror" 
                                    id="poc" name="poc">
                                    <option value="">** Select Poc **</option>
                                @foreach($pocs as $poc)
                                    <option value="{{ $poc->id }}" 
                                            {{ old('poc') ? 'selected' : '' }}>
                                        {{ $poc->poc_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poc')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">
                                Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address', $garden->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2"></i>Contact Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contact_person_name" class="form-label">
                                Contact Person Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('contact_person_name') is-invalid @enderror" 
                                   id="contact_person_name" name="contact_person_name" 
                                   value="{{ old('contact_person_name', $garden->contact_person_name ?? '') }}" required>
                            @error('contact_person_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="mobile_no" class="form-label">
                                Mobile Number <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('mobile_no') is-invalid @enderror" 
                                   id="mobile_no" name="mobile_no" 
                                   value="{{ old('mobile_no', $garden->mobile_no ?? '') }}" required>
                            @error('mobile_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $garden->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Location Information -->
                   <div class="row mb-4">
    <div class="col-12">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-map-marker-alt me-2"></i>Location Information
        </h6>
    </div>
    
    <!-- Address Fields Row -->
    <div class="col-md-4 mb-3">
        <label for="city" class="form-label">City</label>
        <input type="text" class="form-control @error('city') is-invalid @enderror" 
               id="city" name="city" 
               value="{{ old('city', $garden->city ?? '') }}">
        @error('city')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="state" class="form-label">State</label>
        <select class="form-select @error('state') is-invalid @enderror" 
                id="state" name="state">
            <option value="">Select State</option>
            @foreach($states as $value => $label)
                <option value="{{ $value }}" 
                        {{ old('state', $garden->state ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('state')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-4 mb-3">
        <label for="pincode" class="form-label">Pincode</label>
        <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
               id="pincode" name="pincode" 
               value="{{ old('pincode', $garden->pincode ?? '') }}">
        @error('pincode')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Location Picker Section -->
    <div class="col-12 mb-4">
        <div class="card border">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-map me-2"></i>Location Picker
                    <small class="text-muted ms-2">Click on the map to set garden location</small>
                </h6>
            </div>
            <div class="card-body">
                <!-- Coordinate Display -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="latitude" class="form-label">
                            Latitude
                            <span class="text-info ms-1" title="Click on map to set location">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        </label>
                        <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" 
                               id="latitude" name="latitude" 
                               value="{{ old('latitude', $garden->latitude ?? '') }}" 
                               placeholder="e.g., 28.6139" readonly>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-4">
                        <label for="longitude" class="form-label">
                            Longitude
                            <span class="text-info ms-1" title="Click on map to set location">
                                <i class="fas fa-info-circle"></i>
                            </span>
                        </label>
                        <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" 
                               id="longitude" name="longitude" 
                               value="{{ old('longitude', $garden->longitude ?? '') }}" 
                               placeholder="e.g., 77.2090" readonly>
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-outline-primary" id="getCurrentLocation">
                                <i class="fas fa-crosshairs me-1"></i> My Location
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="clearLocation">
                                <i class="fas fa-times me-1"></i> Clear
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Map Container -->
                <div class="row">
                    <div class="col-12">
                        <div id="locationMap" style="height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem;">
                            <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                <div class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading map...</span>
                                    </div>
                                    <div class="mt-2 text-muted">Loading map...</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-text mt-2">
                            <i class="fas fa-mouse-pointer me-1"></i>
                            <strong>Instructions:</strong> Click anywhere on the map to set the garden location. 
                            Use the "My Location" button to get your current position, or manually click to place the marker.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Other Fields -->
    <div class="col-md-6 mb-3">
        <label for="altitude" class="form-label">Altitude (in meters)</label>
        <input type="number" step="0.01" min="0" class="form-control @error('altitude') is-invalid @enderror" 
               id="altitude" name="altitude" 
               value="{{ old('altitude', $garden->altitude ?? '') }}">
        @error('altitude')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-6 mb-3">
        <label for="speciality" class="form-label">Garden Speciality</label>
        <input type="text" class="form-control @error('speciality') is-invalid @enderror" 
               id="speciality" name="speciality" 
               value="{{ old('speciality', $garden->speciality ?? '') }}"
               placeholder="e.g., Organic, High Altitude, Award Winning">
        @error('speciality')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-clipboard me-2"></i>Additional Information
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Any additional notes about this garden">{{ old('remarks', $garden->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.gardens.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> 
                                    {{ isset($garden) ? 'Update Garden' : 'Create Garden' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Help Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Garden Information</h6>
            </div>
            <div class="card-body">
                <p class="small mb-3">
                    Register tea gardens with their contact information and associate them 
                    with the specific tea varieties they produce.
                </p>

                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="text-primary">Required Information</h6>
                        <ul class="mb-0 small">
                            <li><strong>Garden Name:</strong> Official name of the tea garden</li>
                            <li><strong>Address:</strong> Complete physical address</li>
                            <li><strong>Contact Person:</strong> Primary contact for communication</li>
                            <li><strong>Mobile Number:</strong> Contact number for the person</li>
                            <li><strong>Tea Selection:</strong> Complete the filters to select tea varieties</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
<style>
.select2-selection.is-invalid {
    border-color: #dc3545 !important;
}

#filtered_grade_codes {
    min-height: 80px;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}

.leaflet-container {
    font-family: inherit;
}

.map-marker-popup {
    text-align: center;
}

.map-marker-popup .btn {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

#locationMap .leaflet-control-attribution {
    font-size: 10px;
    background: rgba(255, 255, 255, 0.8);
}

.location-info-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 1000;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    display: none;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" 
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" 
        crossorigin=""></script>
<script>

    let map;
let marker;
let defaultLat = 28.6139;
let defaultLng = 77.2090;
$(document).ready(function() {
    // Initialize Select2 for POC dropdown
    $('#poc').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select POC...',
        allowClear: true
    });

        initializeLocationMap();


    // Initialize Select2 for multi-select grade codes
    $('#filtered_grade_codes').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select grade codes...',
        allowClear: true,
        closeOnSelect: false
    });

    // Dependent dropdown logic
    $('#selected_category').on('change', function() {
        const category = $(this).val();
        const teaTypeSelect = $('#selected_tea_type');
        const gradeCodesSelect = $('#filtered_grade_codes');
        
        // Reset dependent dropdowns
        teaTypeSelect.html('<option value="">Select Tea Type</option>').prop('disabled', !category);
        gradeCodesSelect.html('<option value="">Select grade codes</option>').prop('disabled', true);
        
        // Reset Select2 for grade codes
        gradeCodesSelect.select2('destroy');
        gradeCodesSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'First select tea type...',
            allowClear: true,
            closeOnSelect: false
        });
        
        if (category) {
            // Fetch tea types for selected category
            $.ajax({
                url: '{{ route("admin.tea-types-by-category") }}',
                method: 'GET',
                data: { category: category },
                success: function(response) {
                    $.each(response.tea_types, function(key, value) {
                        teaTypeSelect.append(`<option value="${key}">${value}</option>`);
                    });
                    teaTypeSelect.prop('disabled', false);
                },
                error: function() {
                    alert('Error loading tea types. Please try again.');
                }
            });
        }
        
        updateFilteredTeaSelection();
    });
    
    $('#selected_tea_type').on('change', function() {
        const teaType = $(this).val();
        const gradeCodesSelect = $('#filtered_grade_codes');
        
        // Reset grade codes
        gradeCodesSelect.html('<option value="">Select grade codes</option>').prop('disabled', !teaType);
        
        // Reset Select2
        gradeCodesSelect.select2('destroy');
        gradeCodesSelect.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: teaType ? 'Select grade codes...' : 'First select tea type...',
            allowClear: true,
            closeOnSelect: false
        });
        
        if (teaType) {
            // Fetch grade codes for selected tea type
            $.ajax({
                url: '{{ route("admin.grade-codes-by-tea-type") }}',
                method: 'GET', 
                data: { tea_type: teaType },
                success: function(response) {
                    gradeCodesSelect.empty(); // Clear options first
                    $.each(response.grade_codes, function(index, value) {
                        gradeCodesSelect.append(`<option value="${value}">${value}</option>`);
                    });
                    gradeCodesSelect.prop('disabled', false);
                    
                    // Refresh Select2
                    gradeCodesSelect.select2('destroy');
                    gradeCodesSelect.select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: 'Select grade codes...',
                        allowClear: true,
                        closeOnSelect: false
                    });
                },
                error: function() {
                    alert('Error loading grade codes. Please try again.');
                }
            });
        }
        
        updateFilteredTeaSelection();
    });
    
    $('#filtered_grade_codes').on('change', function() {
        updateFilteredTeaSelection();
    });
    
    function updateFilteredTeaSelection() {
        const category = $('#selected_category').val();
        const teaType = $('#selected_tea_type').val();
        const gradeCodes = $('#filtered_grade_codes').val() || [];
        
        if (category && teaType) {
            // Show loading message
            $('#filtered-tea-selection').html(`
                <div class="alert alert-info">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Loading tea varieties...
                </div>
            `);
            
            // Fetch filtered teas based on selections
            $.ajax({
                url: '{{ route("admin.filtered-teas") }}',
                method: 'GET',
                data: { 
                    category: category, 
                    tea_type: teaType,
                    grade_codes: gradeCodes
                },
                success: function(response) {
                    if (response.teas && response.teas.length > 0) {
                        let html = `
                            <label class="form-label">Select Tea Varieties <span class="text-danger">*</span></label>
                            <select class="form-select" id="tea_ids" name="tea_ids[]" multiple required>
                        `;
                        
                        $.each(response.teas, function(index, tea) {
                            html += `<option value="${tea.id}">${tea.full_name}</option>`;
                        });
                        
                        html += '</select>';
                        html += '<div class="form-text">Hold Ctrl (or Cmd) to select multiple tea varieties</div>';
                        
                        $('#filtered-tea-selection').html(html);
                        
                        // Initialize Select2 for the new select
                        $('#tea_ids').select2({
                            theme: 'bootstrap-5',
                            width: '100%',
                            placeholder: 'Select tea varieties...',
                            allowClear: true,
                            closeOnSelect: false
                        });
                    } else {
                        $('#filtered-tea-selection').html(`
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                No tea varieties found for the selected filters.
                            </div>
                        `);
                    }
                },
                error: function() {
                    $('#filtered-tea-selection').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            Error loading tea varieties. Please try again.
                        </div>
                    `);
                }
            });
        } else {
            $('#filtered-tea-selection').html(`
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Please complete the category and tea type selection above to see available tea varieties.
                </div>
            `);
        }
    }

    // Form validation
    $('#gardenForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['garden_name', 'address', 'contact_person_name', 'mobile_no', 'selected_category', 'selected_tea_type'];
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val() || !input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        // Check tea selection
        const selectedTeas = $('#tea_ids').val();
        if (!selectedTeas || selectedTeas.length === 0) {
            if ($('#tea_ids').length > 0) {
                $('#tea_ids').next('.select2-container').find('.select2-selection').addClass('is-invalid');
                isValid = false;
            } else {
                alert('Please complete the tea selection filters and select at least one tea variety.');
                isValid = false;
            }
        } else if ($('#tea_ids').length > 0) {
            $('#tea_ids').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }
        
        // Email validation if provided
        const email = $('#email').val();
        if (email && email.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $('#email').addClass('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });
    
    // Real-time validation
    // $('.form-control, .form-select').on('input change', function() {
    //     if ($(this).val() && $(this).val().trim()) {
    //         $(this).removeClass('is-invalid');
    //     }
    // });
    
    // Select2 validation for tea selection
    $(document).on('change', '#tea_ids', function() {
        const selectedTeas = $(this).val();
        if (selectedTeas && selectedTeas.length > 0) {
            $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }
    });

    // Initialize if editing existing garden
    @if(isset($garden) && $garden->selected_category)
        $('#selected_category').trigger('change');
        setTimeout(function() {
            $('#selected_tea_type').val('{{ $garden->selected_tea_type }}').trigger('change');
            
            setTimeout(function() {
                @if(isset($garden->filtered_grade_codes))
                    $('#filtered_grade_codes').val({!! json_encode($garden->filtered_grade_codes) !!}).trigger('change');
                @endif
            }, 1000);
        }, 500);
    @endif
});

function initializeLocationMap() {
    // Initialize map with default center (Delhi, India)
    map = L.map('locationMap').setView([defaultLat, defaultLng], 6);
    
    // Add OpenStreetMap tiles (no API key required)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    // Initialize marker if coordinates exist
    const existingLat = $('#latitude').val();
    const existingLng = $('#longitude').val();
    
    if (existingLat && existingLng) {
        setMarker(parseFloat(existingLat), parseFloat(existingLng));
        map.setView([existingLat, existingLng], 13);
    }

    // Map click event to set location
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        setMarker(lat, lng);
        updateCoordinateFields(lat, lng);
    });

    // Get current location button
    $('#getCurrentLocation').on('click', function() {
        const btn = $(this);
        const originalHtml = btn.html();
        
        btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Getting Location...').prop('disabled', true);
        
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;
                    
                    setMarker(lat, lng);
                    updateCoordinateFields(lat, lng);
                    map.setView([lat, lng], 15);
                    
                    btn.html(originalHtml).prop('disabled', false);
                    showNotification('Location set successfully!', 'success');
                },
                function(error) {
                    btn.html(originalHtml).prop('disabled', false);
                    let errorMsg = 'Unable to get your location. ';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorMsg += 'Location access denied by user.';
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorMsg += 'Location information unavailable.';
                            break;
                        case error.TIMEOUT:
                            errorMsg += 'Location request timed out.';
                            break;
                        default:
                            errorMsg += 'An unknown error occurred.';
                    }
                    showNotification(errorMsg, 'error');
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 300000
                }
            );
        } else {
            btn.html(originalHtml).prop('disabled', false);
            showNotification('Geolocation is not supported by this browser.', 'error');
        }
    });

    // Clear location button
    $('#clearLocation').on('click', function() {
        clearMarker();
        updateCoordinateFields('', '');
        map.setView([defaultLat, defaultLng], 6);
        showNotification('Location cleared.', 'info');
    });
}

function setMarker(lat, lng) {
    // Remove existing marker
    if (marker) {
        map.removeLayer(marker);
    }
    
    // Create new marker
    marker = L.marker([lat, lng], {
        draggable: true
    }).addTo(map);
    
    // Marker popup
    marker.bindPopup(`
        <div class="map-marker-popup">
            <strong>Garden Location</strong><br>
            <small>Lat: ${lat.toFixed(6)}<br>
            Lng: ${lng.toFixed(6)}</small><br>
            <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="clearMarker()">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
    `).openPopup();
    
    // Handle marker drag
    marker.on('dragend', function(e) {
        const newLat = e.target.getLatLng().lat;
        const newLng = e.target.getLatLng().lng;
        updateCoordinateFields(newLat, newLng);
        
        // Update popup
        marker.setPopupContent(`
            <div class="map-marker-popup">
                <strong>Garden Location</strong><br>
                <small>Lat: ${newLat.toFixed(6)}<br>
                Lng: ${newLng.toFixed(6)}</small><br>
                <button type="button" class="btn btn-sm btn-outline-danger mt-1" onclick="clearMarker()">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
        `);
    });
}

function clearMarker() {
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
}

function updateCoordinateFields(lat, lng) {
    $('#latitude').val(lat ? lat.toFixed(6) : '');
    $('#longitude').val(lng ? lng.toFixed(6) : '');
    
    // Remove validation errors when coordinates are set
    if (lat && lng) {
        $('#latitude, #longitude').removeClass('is-invalid');
    }
}

function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'error' ? 'alert-danger' : 'alert-info';
    
    const notification = $(`
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto remove after 5 seconds
    setTimeout(function() {
        notification.alert('close');
    }, 5000);
}

// Enhanced form validation to include coordinates
$('#gardenForm').on('submit', function(e) {
    let isValid = true;
    
    // ... your existing validation code ...
    
    // Optional: Validate coordinates if required
    const lat = $('#latitude').val();
    const lng = $('#longitude').val();
    
    // Uncomment below if you want to make location mandatory
    /*
    if (!lat || !lng) {
        $('#latitude, #longitude').addClass('is-invalid');
        showNotification('Please select a location on the map.', 'error');
        isValid = false;
    }
    */
    
    if (!isValid) {
        e.preventDefault();
    }
});
</script>
@endpush