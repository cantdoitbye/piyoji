@extends('admin.layouts.app')

@section('title', isset($seller) ? 'Edit Seller' : 'Add New Seller')
@section('subtitle', isset($seller) ? 'Update seller information' : 'Add a new seller to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sellers.index') }}">Sellers</a></li>
    <li class="breadcrumb-item active">{{ isset($seller) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($seller) ? 'Edit Seller Information' : 'Add New Seller' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($seller) ? route('admin.sellers.update', $seller->id) : route('admin.sellers.store') }}" id="sellerForm">
                    @csrf
                    @if(isset($seller))
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
                            <label for="seller_name" class="form-label">
                                Seller Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('seller_name') is-invalid @enderror" 
                                   id="seller_name" name="seller_name" 
                                   value="{{ old('seller_name', $seller->seller_name ?? '') }}" required>
                            @error('seller_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tea_estate_name" class="form-label">
                                Tea Estate/Garden Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('tea_estate_name') is-invalid @enderror" 
                                   id="tea_estate_name" name="tea_estate_name" 
                                   value="{{ old('tea_estate_name', $seller->tea_estate_name ?? '') }}" required>
                            @error('tea_estate_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">
                                Contact Person <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" name="contact_person" 
                                   value="{{ old('contact_person', $seller->contact_person ?? '') }}" required>
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('status', $seller->status ?? 1) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-phone me-2"></i>Contact Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $seller->email ?? '') }}" required>
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
                                   value="{{ old('phone', $seller->phone ?? '') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">
                                Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address', $seller->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">
                                City <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" 
                                   value="{{ old('city', $seller->city ?? '') }}" required>
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">
                                State <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" 
                                   value="{{ old('state', $seller->state ?? '') }}" required>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="pincode" class="form-label">
                                Pincode <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   id="pincode" name="pincode" 
                                   value="{{ old('pincode', $seller->pincode ?? '') }}" required>
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-building me-2"></i>Business Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gstin" class="form-label">
                                GSTIN <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                   id="gstin" name="gstin" 
                                   value="{{ old('gstin', $seller->gstin ?? '') }}" 
                                   pattern="[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}"
                                   title="Please enter a valid GSTIN (15 characters)" required>
                            @error('gstin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="pan" class="form-label">
                                PAN <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('pan') is-invalid @enderror" 
                                   id="pan" name="pan" 
                                   value="{{ old('pan', $seller->pan ?? '') }}" 
                                   pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}"
                                   title="Please enter a valid PAN (10 characters)" required>
                            @error('pan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="tea_grades" class="form-label">
                                Tea Grades Handled <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2 @error('tea_grades') is-invalid @enderror" 
                                    id="tea_grades" name="tea_grades[]" multiple required>
                                @foreach($teaGrades as $key => $grade)
                                    <option value="{{ $key }}" 
                                            {{ in_array($key, old('tea_grades', $seller->tea_grades ?? [])) ? 'selected' : '' }}>
                                        {{ $key }} - {{ $grade }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tea_grades')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select all tea grades that this seller handles</div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Any additional notes or remarks">{{ old('remarks', $seller->remarks ?? '') }}</textarea>
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
                                <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> 
                                    {{ isset($seller) ? 'Update Seller' : 'Create Seller' }}
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
                        <li>GSTIN should be 15 characters long</li>
                        <li>PAN should be 10 characters long</li>
                        <li>Select multiple tea grades that the seller handles</li>
                        <li>All fields marked with <span class="text-danger">*</span> are required</li>
                    </ul>
                </div>

                @if(isset($seller))
                <div class="alert alert-success">
                    <h6><i class="fas fa-calendar me-2"></i>Seller Details:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $seller->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $seller->updated_at->format('M d, Y') }}</li>
                        <li><strong>Status:</strong> {{ $seller->status_text }}</li>
                    </ul>
                </div>
                @endif

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important:</h6>
                    <p class="mb-0 small">
                        Ensure all business information is accurate as it will be used for contracts and invoicing.
                    </p>
                </div>
            </div>
        </div>

        @if(isset($seller) && $seller->contracts->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-file-contract me-2"></i>Recent Contracts</h6>
            </div>
            <div class="card-body">
                @foreach($seller->contracts->take(3) as $contract)
                <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <div class="fw-bold">Contract #{{ $contract->id }}</div>
                        <small class="text-muted">{{ $contract->created_at->format('M d, Y') }}</small>
                    </div>
                    <span class="badge bg-primary">{{ $contract->status }}</span>
                </div>
                @endforeach
                @if($seller->contracts->count() > 3)
                <div class="text-center mt-2">
                    <a href="#" class="btn btn-sm btn-outline-primary">View All Contracts</a>
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
    // Initialize Select2 for tea grades
    $('#tea_grades').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select tea grades...',
        allowClear: true
    });

    // Format GSTIN input
    $('#gstin').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Format PAN input
    $('#pan').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    // Form validation
    $('#sellerForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate GSTIN
        // const gstin = $('#gstin').val();
        // if (gstin && !validateGSTIN(gstin)) {
        //     showError('gstin', 'Please enter a valid GSTIN');
        //     isValid = false;
        // }
        
        // Validate PAN
        // const pan = $('#pan').val();
        // if (pan && !validatePAN(pan)) {
        //     showError('pan', 'Please enter a valid PAN');
        //     isValid = false;
        // }
        
        // Validate tea grades selection
        const teaGrades = $('#tea_grades').val();
        if (!teaGrades || teaGrades.length === 0) {
            showError('tea_grades', 'Please select at least one tea grade');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            toastr.error('Please fix the errors and try again');
        } else {
            showLoading();
        }
    });
});

function validateGSTIN(gstin) {
    // const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
    // return gstinRegex.test(gstin);
}

function validatePAN(pan) {
    // const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    // return panRegex.test(pan);
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