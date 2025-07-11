@extends('admin.layouts.app')

@section('title', isset($buyer) ? 'Edit Buyer' : 'Add New Buyer')
@section('subtitle', isset($buyer) ? 'Update buyer information' : 'Add a new buyer to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.buyers.index') }}">Buyers</a></li>
    <li class="breadcrumb-item active">{{ isset($buyer) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.buyers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($buyer) ? 'Edit Buyer Information' : 'Add New Buyer' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($buyer) ? route('admin.buyers.update', $buyer->id) : route('admin.buyers.store') }}" id="buyerForm">
                    @csrf
                    @if(isset($buyer))
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
                            <label for="buyer_name" class="form-label">
                                Buyer Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('buyer_name') is-invalid @enderror" 
                                   id="buyer_name" name="buyer_name" 
                                   value="{{ old('buyer_name', $buyer->buyer_name ?? '') }}" required>
                            @error('buyer_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="buyer_type" class="form-label">
                                Buyer Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('buyer_type') is-invalid @enderror" 
                                    id="buyer_type" name="buyer_type" required>
                                <option value="">Select Buyer Type</option>
                                @foreach($buyerTypes as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('buyer_type', $buyer->buyer_type ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('buyer_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">
                                Contact Person <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" name="contact_person" 
                                   value="{{ old('contact_person', $buyer->contact_person ?? '') }}" required>
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
                                            {{ old('status', $buyer->status ?? 1) == $value ? 'selected' : '' }}>
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
                                   value="{{ old('email', $buyer->email ?? '') }}" required>
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
                                   value="{{ old('phone', $buyer->phone ?? '') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-file-invoice me-2"></i>Billing Address
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="billing_address" class="form-label">
                                Billing Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('billing_address') is-invalid @enderror" 
                                      id="billing_address" name="billing_address" rows="3" required>{{ old('billing_address', $buyer->billing_address ?? '') }}</textarea>
                            @error('billing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="billing_city" class="form-label">
                                City <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('billing_city') is-invalid @enderror" 
                                   id="billing_city" name="billing_city" 
                                   value="{{ old('billing_city', $buyer->billing_city ?? '') }}" required>
                            @error('billing_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="billing_state" class="form-label">
                                State <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('billing_state') is-invalid @enderror" 
                                   id="billing_state" name="billing_state" 
                                   value="{{ old('billing_state', $buyer->billing_state ?? '') }}" required>
                            @error('billing_state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="billing_pincode" class="form-label">
                                Pincode <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('billing_pincode') is-invalid @enderror" 
                                   id="billing_pincode" name="billing_pincode" 
                                   value="{{ old('billing_pincode', $buyer->billing_pincode ?? '') }}" required>
                            @error('billing_pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="text-primary mb-0">
                                    <i class="fas fa-shipping-fast me-2"></i>Shipping Address
                                </h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="same_as_billing" name="same_as_billing">
                                    <label class="form-check-label" for="same_as_billing">
                                        Same as billing address
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="shipping_address" class="form-label">
                                Shipping Address <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                      id="shipping_address" name="shipping_address" rows="3" required>{{ old('shipping_address', $buyer->shipping_address ?? '') }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="shipping_city" class="form-label">
                                City <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('shipping_city') is-invalid @enderror" 
                                   id="shipping_city" name="shipping_city" 
                                   value="{{ old('shipping_city', $buyer->shipping_city ?? '') }}" required>
                            @error('shipping_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="shipping_state" class="form-label">
                                State <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('shipping_state') is-invalid @enderror" 
                                   id="shipping_state" name="shipping_state" 
                                   value="{{ old('shipping_state', $buyer->shipping_state ?? '') }}" required>
                            @error('shipping_state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="shipping_pincode" class="form-label">
                                Pincode <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('shipping_pincode') is-invalid @enderror" 
                                   id="shipping_pincode" name="shipping_pincode" 
                                   value="{{ old('shipping_pincode', $buyer->shipping_pincode ?? '') }}" required>
                            @error('shipping_pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tea Preferences -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Tea Preferences
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="preferred_tea_grades" class="form-label">
                                Preferred Tea Grades <span class="text-danger">*</span>
                            </label>
                            <select class="form-select select2 @error('preferred_tea_grades') is-invalid @enderror" 
                                    id="preferred_tea_grades" name="preferred_tea_grades[]" multiple required>
                                @foreach($teaGrades as $key => $grade)
                                    <option value="{{ $key }}" 
                                            {{ in_array($key, old('preferred_tea_grades', $buyer->preferred_tea_grades ?? [])) ? 'selected' : '' }}>
                                        {{ $key }} - {{ $grade }}
                                    </option>
                                @endforeach
                            </select>
                            @error('preferred_tea_grades')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Select tea grades that this buyer prefers to purchase</div>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Any additional notes or remarks">{{ old('remarks', $buyer->remarks ?? '') }}</textarea>
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
                                <a href="{{ route('admin.buyers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="reset" class="btn btn-outline-warning">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> 
                                    {{ isset($buyer) ? 'Update Buyer' : 'Create Buyer' }}
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
                        <li>Choose buyer type carefully as it affects the feedback process</li>
                        <li>Big buyers go through detailed lab testing process</li>
                        <li>Small buyers have simplified feedback process</li>
                        <li>Shipping address can be different from billing address</li>
                        <li>All fields marked with <span class="text-danger">*</span> are required</li>
                    </ul>
                </div>

                @if(isset($buyer))
                <div class="alert alert-success">
                    <h6><i class="fas fa-calendar me-2"></i>Buyer Details:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $buyer->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $buyer->updated_at->format('M d, Y') }}</li>
                        <li><strong>Type:</strong> {{ $buyer->buyer_type_text }}</li>
                        <li><strong>Status:</strong> {{ $buyer->status_text }}</li>
                    </ul>
                </div>
                @endif

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Buyer Types:</h6>
                    <p class="mb-2 small">
                        <strong>Big Buyers:</strong> Corporate clients with detailed sample evaluation and lab testing requirements.
                    </p>
                    <p class="mb-0 small">
                        <strong>Small Buyers:</strong> Individual or small business clients with simplified feedback process.
                    </p>
                </div>
            </div>
        </div>

        @if(isset($buyer) && $buyer->feedbacks && $buyer->feedbacks->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Recent Feedback</h6>
            </div>
            <div class="card-body">
                @foreach($buyer->feedbacks->take(3) as $feedback)
                <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <div class="fw-bold">Sample #{{ $feedback->sample_id }}</div>
                        <small class="text-muted">{{ $feedback->created_at->format('M d, Y') }}</small>
                    </div>
                    <span class="badge bg-{{ $feedback->status === 'satisfactory' ? 'success' : 'warning' }}">
                        {{ ucfirst($feedback->status) }}
                    </span>
                </div>
                @endforeach
                @if($buyer->feedbacks->count() > 3)
                <div class="text-center mt-2">
                    <a href="#" class="btn btn-sm btn-outline-primary">View All Feedback</a>
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
    $('#preferred_tea_grades').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select preferred tea grades...',
        allowClear: true
    });

    // Handle same as billing checkbox
    $('#same_as_billing').change(function() {
        if (this.checked) {
            // Copy billing address to shipping
            $('#shipping_address').val($('#billing_address').val());
            $('#shipping_city').val($('#billing_city').val());
            $('#shipping_state').val($('#billing_state').val());
            $('#shipping_pincode').val($('#billing_pincode').val());
            
            // Disable shipping fields
            $('#shipping_address, #shipping_city, #shipping_state, #shipping_pincode').prop('readonly', true);
        } else {
            // Enable shipping fields
            $('#shipping_address, #shipping_city, #shipping_state, #shipping_pincode').prop('readonly', false);
        }
    });

    // Auto-copy billing to shipping when billing fields change (if checkbox is checked)
    $('#billing_address, #billing_city, #billing_state, #billing_pincode').on('input', function() {
        if ($('#same_as_billing').is(':checked')) {
            const fieldName = this.id.replace('billing_', 'shipping_');
            $('#' + fieldName).val(this.value);
        }
    });

    // Form validation
    $('#buyerForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate preferred tea grades selection
        const teaGrades = $('#preferred_tea_grades').val();
        if (!teaGrades || teaGrades.length === 0) {
            showError('preferred_tea_grades', 'Please select at least one preferred tea grade');
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