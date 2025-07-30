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
                            <label for="status" class="form-label">POC</label>
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

                    <!-- Tea Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Tea Varieties <span class="text-danger">*</span>
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="tea_ids" class="form-label">
                                Select Teas <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('tea_ids') is-invalid @enderror" 
                                    id="tea_ids" name="tea_ids[]" multiple required>
                                @foreach($teas as $tea)
                                    <option value="{{ $tea->id }}" 
                                            {{ in_array($tea->id, old('tea_ids', $garden->tea_ids ?? [])) ? 'selected' : '' }}>
                                        {{ $tea->category }} - {{ $tea->tea_type }} - {{ $tea->sub_title }} ({{ $tea->grade }})
                                    </option>
                                @endforeach
                            </select>
                            @error('tea_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('tea_ids.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Hold Ctrl (or Cmd) to select multiple teas</div>
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
                            <li><strong>Tea Selection:</strong> At least one tea variety must be selected</li>
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

@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for tea selection
   


      $('#tea_ids').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: 'Select POCs...',
    allowClear: true,
    closeOnSelect: false
});

    // Form validation
    $('#gardenForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['garden_name', 'address', 'contact_person_name', 'mobile_no'];
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        // Check tea selection
        const selectedTeas = $('#tea_ids').val();
        if (!selectedTeas || selectedTeas.length === 0) {
            $('#tea_ids').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            isValid = false;
        } else {
            $('#tea_ids').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }
        
        // Email validation if provided
        const email = $('#email').val();
        if (email) {
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
    //     if ($(this).val().trim()) {
    //         $(this).removeClass('is-invalid');
    //     }
    // });
    
    // Select2 validation
    $('#tea_ids').on('change', function() {
        const selectedTeas = $(this).val();
        if (selectedTeas && selectedTeas.length > 0) {
            $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.select2-selection.is-invalid {
    border-color: #dc3545 !important;
}
</style>
@endpush