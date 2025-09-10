@extends('admin.layouts.app')

@section('title', isset($poc) ? 'Edit POC' : 'Add New POC')
@section('subtitle', isset($poc) ? 'Update POC information' : 'Add a new Person of Contact to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pocs.index') }}">POCs</a></li>
    <li class="breadcrumb-item active">{{ isset($poc) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.pocs.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($poc) ? 'Edit POC Information' : 'Add New POC' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($poc) ? route('admin.pocs.update', $poc->id) : route('admin.pocs.store') }}" id="pocForm">
                    @csrf
                    @if(isset($poc))
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
                            <label for="poc_name" class="form-label">
                                POC Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('poc_name') is-invalid @enderror" 
                                   id="poc_name" name="poc_name" 
                                   value="{{ old('poc_name', $poc->poc_name ?? '') }}" required>
                            @error('poc_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <input type="text" class="form-control @error('designation') is-invalid @enderror" 
                                   id="designation" name="designation" 
                                   value="{{ old('designation', $poc->designation ?? '') }}">
                            @error('designation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $poc->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">
                                Phone <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" 
                                   value="{{ old('phone', $poc->phone ?? '') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                          <div class="col-md-6 mb-3">
                            <label for="poc_type" class="form-label">
                                 Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" name="type" required>
                                <option value="">Select POC Type</option>
                                @foreach($types as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('type', $poc->type ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poc_type')
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
                                @foreach($pocTypes as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('poc_type', $poc->poc_type ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poc_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('status', $poc->status ?? 1) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
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
                                      id="address" name="address" rows="3">{{ old('address', $poc->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" 
                                   value="{{ old('city', $poc->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" 
                                   value="{{ old('state', $poc->state ?? '') }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   id="pincode" name="pincode" 
                                   value="{{ old('pincode', $poc->pincode ?? '') }}">
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
                                      placeholder="Any additional notes or remarks about this POC">{{ old('remarks', $poc->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.pocs.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> 
                                    {{ isset($poc) ? 'Update POC' : 'Create POC' }}
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
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>POC Information</h6>
            </div>
            <div class="card-body">
                <p class="small mb-3">
                    Person of Contact (POC) represents individuals who act as intermediaries between 
                    your company and sellers/buyers in the tea trading process.
                </p>

                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="text-primary">POC Types</h6>
                        <ul class="mb-0 small">
                            <li><strong>Seller:</strong> POC for seller companies only</li>
                            <li><strong>Buyer:</strong> POC for buyer companies only</li>
                            <li><strong>Both:</strong> POC can handle both sellers and buyers</li>
                        </ul>
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
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });
    
    // Real-time validation
    $('.form-control, .form-select').on('input change', function() {
        if ($(this).val().trim()) {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush