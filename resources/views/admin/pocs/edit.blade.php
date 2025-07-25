@extends('admin.layouts.app')

@section('title', 'Edit POC')
@section('subtitle', 'Update Point of Contact information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pocs.index') }}">POCs</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.pocs.show', $poc->id) }}">{{ $poc->poc_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.pocs.show', $poc->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Details
        </a>
        <a href="{{ route('admin.pocs.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-list me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit POC Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.pocs.update', $poc->id) }}" id="pocForm">
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
                            <label for="poc_name" class="form-label">
                                POC Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('poc_name') is-invalid @enderror" 
                                   id="poc_name" name="poc_name" 
                                   value="{{ old('poc_name', $poc->poc_name) }}" required>
                            @error('poc_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <input type="text" class="form-control @error('designation') is-invalid @enderror" 
                                   id="designation" name="designation" 
                                   value="{{ old('designation', $poc->designation) }}"
                                   placeholder="e.g., Manager, Executive, Director">
                            @error('designation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="poc_type" class="form-label">
                                POC Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('poc_type') is-invalid @enderror" 
                                    id="poc_type" name="poc_type" required>
                                <option value="">Select POC Type</option>
                                <option value="seller" {{ old('poc_type', $poc->poc_type) == 'seller' ? 'selected' : '' }}>
                                    Seller
                                </option>
                                <option value="buyer" {{ old('poc_type', $poc->poc_type) == 'buyer' ? 'selected' : '' }}>
                                    Buyer
                                </option>
                                <option value="both" {{ old('poc_type', $poc->poc_type) == 'both' ? 'selected' : '' }}>
                                    Both
                                </option>
                            </select>
                            @error('poc_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="1" {{ old('status', $poc->status ? '1' : '0') == '1' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="0" {{ old('status', $poc->status ? '1' : '0') == '0' ? 'selected' : '' }}>
                                    Inactive
                                </option>
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
                                   value="{{ old('email', $poc->email) }}" required>
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
                                   value="{{ old('phone', $poc->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Address Information
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" 
                                      placeholder="Complete address">{{ old('address', $poc->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" 
                                   value="{{ old('city', $poc->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" 
                                   value="{{ old('state', $poc->state) }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   id="pincode" name="pincode" 
                                   value="{{ old('pincode', $poc->pincode) }}">
                            @error('pincode')
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
                                      placeholder="Any additional notes or remarks about this POC">{{ old('remarks', $poc->remarks) }}</textarea>
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
                                <a href="{{ route('admin.pocs.show', $poc->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update POC
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
        <!-- Current POC Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Current POC Info
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-user me-2"></i>{{ $poc->poc_name }}</h6>
                    <ul class="mb-0 small">
                        <li><strong>Type:</strong> {{ $poc->poc_type_text }}</li>
                        <li><strong>Status:</strong> {{ $poc->status_text }}</li>
                        <li><strong>Email:</strong> {{ $poc->email }}</li>
                        <li><strong>Phone:</strong> {{ $poc->phone }}</li>
                        <li><strong>Created:</strong> {{ $poc->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $poc->updated_at->format('M d, Y') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Help Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important:</h6>
                    <ul class="mb-0 small">
                        <li>Email must be unique across all POCs</li>
                        <li>POC type determines which forms this POC appears in</li>
                        <li>All fields marked with <span class="text-danger">*</span> are required</li>
                        <li>Changes will affect existing associations</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- POC Type Guide -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>POC Type Guide</h6>
            </div>
            <div class="card-body">
                <div class="small">
                    <div class="mb-3">
                        <strong class="text-info">Seller:</strong>
                        <p class="mb-0">POC will only appear in seller forms and handle seller-related communications.</p>
                    </div>
                    <div class="mb-3">
                        <strong class="text-secondary">Buyer:</strong>
                        <p class="mb-0">POC will only appear in buyer forms and handle buyer-related communications.</p>
                    </div>
                    <div class="mb-0">
                        <strong class="text-primary">Both:</strong>
                        <p class="mb-0">POC can handle both sellers and buyers, appearing in all relevant forms.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#pocForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['poc_name', 'email', 'phone', 'poc_type'];
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        // Email validation
        const email = $('#email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#email').addClass('is-invalid');
            isValid = false;
        }
        
        // Phone validation (basic)
        const phone = $('#phone').val();
        if (phone && phone.length < 10) {
            $('#phone').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            if (typeof toastr !== 'undefined') {
                toastr.error('Please fix the errors and try again');
            } else {
                alert('Please fix the errors and try again');
            }
        } else {
            if (typeof showLoading === 'function') {
                showLoading();
            }
        }
    });

    // Real-time validation
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Email format validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
        }
    });

    // Phone format validation
    $('#phone').on('input', function() {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9+\-\s]/g, '');
    });

    // Pincode validation
    $('#pincode').on('input', function() {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
        // Limit to 6 digits
        if (this.value.length > 6) {
            this.value = this.value.slice(0, 6);
        }
    });
});

// Helper function for loading state
function showLoading() {
    const submitBtn = $('#pocForm button[type="submit"]');
    submitBtn.prop('disabled', true);
    submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');
}
</script>
@endpush