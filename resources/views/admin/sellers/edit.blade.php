@extends('admin.layouts.app')

@section('title', 'Edit Seller')
@section('subtitle', 'Update seller information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sellers.index') }}">Sellers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sellers.show', $seller->id) }}">{{ $seller->seller_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> View Details
        </a>
        <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Seller Information</h5>
                <div class="text-muted small">
                    <i class="fas fa-calendar me-1"></i>
                    Last updated: {{ $seller->updated_at->format('M d, Y \a\t g:i A') }}
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.sellers.update', $seller->id) }}" id="sellerForm">
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
                            <label for="seller_name" class="form-label">
                                Seller Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('seller_name') is-invalid @enderror" 
                                   id="seller_name" name="seller_name" 
                                   value="{{ old('seller_name', $seller->seller_name) }}" required>
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
                                   value="{{ old('tea_estate_name', $seller->tea_estate_name) }}" required>
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
                                   value="{{ old('contact_person', $seller->contact_person) }}" required>
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
                                            {{ old('status', $seller->status) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                           <!-- POC Selection -->
                        <div class="col-12 mb-3">
                            <label for="poc_ids" class="form-label">
                                <i class="fas fa-user-tie me-1"></i>Point of Contact (POC)
                            </label>
                            <select class="form-select @error('poc_ids') is-invalid @enderror" 
                                    id="poc_ids" name="poc_ids[]" multiple>
                                <option value="">Select POCs...</option>
                                @if(isset($pocs) && $pocs->count() > 0)
                                    @foreach($pocs as $poc)
                                        <option value="{{ $poc->id }}" 
                                                {{ in_array($poc->id, old('poc_ids', $seller->poc_ids ?? [])) ? 'selected' : '' }}>
                                            {{ $poc->poc_name }}{{ $poc->designation ? ' (' . $poc->designation . ')' : '' }} - {{ $poc->poc_type_text }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('poc_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Select one or more POCs who will handle this seller. 
                                @if(!isset($pocs) || $pocs->count() == 0)
                                    <span class="text-warning">No POCs available. Please create POCs first.</span>
                                @endif
                            </div>
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
                                   value="{{ old('email', $seller->email) }}" required>
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
                                   value="{{ old('phone', $seller->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">
                                Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address', $seller->address) }}</textarea>
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
                                   value="{{ old('city', $seller->city) }}" required>
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
                                   value="{{ old('state', $seller->state) }}" required>
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
                                   value="{{ old('pincode', $seller->pincode) }}" required>
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
                                   value="{{ old('gstin', $seller->gstin) }}" 
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
                                   value="{{ old('pan', $seller->pan) }}" 
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
                                      placeholder="Any additional notes or remarks">{{ old('remarks', $seller->remarks) }}</textarea>
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
                                    <button type="button" class="btn btn-outline-danger" onclick="deleteSeller({{ $seller->id }})">
                                        <i class="fas fa-trash me-1"></i> Delete Seller
                                    </button>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.sellers.show', $seller->id) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="reset" class="btn btn-outline-warning">
                                        <i class="fas fa-undo me-1"></i> Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Seller
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
                            <h6 class="timeline-title">Account Created</h6>
                            <p class="timeline-description">{{ $seller->created_at->format('F j, Y \a\t g:i A') }}</p>
                            <span class="timeline-date">{{ $seller->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Last Updated</h6>
                            <p class="timeline-description">{{ $seller->updated_at->format('F j, Y \a\t g:i A') }}</p>
                            <span class="timeline-date">{{ $seller->updated_at->diffForHumans() }}</span>
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
                    <h6><i class="fas fa-lightbulb me-2"></i>Tips:</h6>
                    <ul class="mb-0 small">
                        <li>GSTIN and PAN must be unique in the system</li>
                        <li>Email address will be used for communications</li>
                        <li>Changing status affects seller's visibility</li>
                        <li>Tea grades determine which samples can be assigned</li>
                    </ul>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Important:</h6>
                    <p class="mb-0 small">
                        Changes to business information (GSTIN/PAN) may affect existing contracts and samples.
                    </p>
                </div>
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
                            <small class="text-muted">Total Contracts</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <h4 class="text-success mb-0">0</h4>
                        <small class="text-muted">Total Samples</small>
                    </div>
                    <div class="col-12">
                        <div class="bg-light p-3 rounded">
                            <strong>Current Status:</strong>
                            <span class="status-badge {{ $seller->status ? 'status-active' : 'status-inactive' }} ms-2">
                                {{ $seller->status_text }}
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
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
        const gstin = $('#gstin').val();
        if (gstin && !validateGSTIN(gstin)) {
            showError('gstin', 'Please enter a valid GSTIN');
            isValid = false;
        }
        
        // Validate PAN
        const pan = $('#pan').val();
        if (pan && !validatePAN(pan)) {
            showError('pan', 'Please enter a valid PAN');
            isValid = false;
        }
        
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
    const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
    return gstinRegex.test(gstin);
}

function validatePAN(pan) {
    const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    return panRegex.test(pan);
}

function showError(fieldId, message) {
    const field = $('#' + fieldId);
    field.addClass('is-invalid');
    
    // Remove existing error message
    field.siblings('.invalid-feedback').remove();
    
    // Add new error message
    field.after('<div class="invalid-feedback">' + message + '</div>');
}

function deleteSeller(id) {
    if (!confirmDelete('Are you sure you want to delete this seller? This action cannot be undone and will affect all related contracts and samples.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/sellers/${id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                window.location.href = '{{ route("admin.sellers.index") }}';
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error deleting seller');
        }
    });
}

// Clear error state on input
$('.form-control, .form-select').on('input change', function() {
    $(this).removeClass('is-invalid');
    $(this).siblings('.invalid-feedback').remove();
});
</script>
@endpush