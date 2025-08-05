@extends('admin.layouts.app')

@section('title', isset($tea) ? 'Edit Tea' : 'Add New Tea')
@section('subtitle', isset($tea) ? 'Update tea information' : 'Add a new tea to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.teas.index') }}">Teas</a></li>
    <li class="breadcrumb-item active">{{ isset($tea) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.teas.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($tea) ? 'Edit Tea Information' : 'Add New Tea' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($tea) ? route('admin.teas.update', $tea->id) : route('admin.teas.store') }}" id="teaForm">
                    @csrf
                    @if(isset($tea))
                        @method('PUT')
                    @endif

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Tea Information
                            </h6>
                        </div>
                        
                       
                        
                        
                        
                        <div class="col-md-6 mb-3">
                            <label for="sub_title" class="form-label">
                                Sub Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('sub_title') is-invalid @enderror" 
                                   id="sub_title" name="sub_title" 
                                   value="{{ old('sub_title', $tea->sub_title ?? '') }}" 
                                   placeholder="e.g., Earl Grey, Assam Bold" required>
                            @error('sub_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                       <!-- Tea Type Selection -->
<div class="row mb-3">
    <div class="col-md-4">
        <label for="tea_type_id" class="form-label">Tea Type <span class="text-danger">*</span></label>
        <select class="form-select @error('tea_type_id') is-invalid @enderror" 
                id="tea_type_id" name="tea_type_id" required>
            <option value="">Select Tea Type</option>
            @foreach(\App\Models\Tea::getTeaTypeOptions() as $key => $value)
                <option value="{{ $key }}" {{ old('tea_type_id', $tea->tea_type_id ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
        @error('tea_type_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="sub_tea_type_id" class="form-label">Sub Tea Type <span class="text-danger">*</span></label>
        <select class="form-select @error('sub_tea_type_id') is-invalid @enderror" 
                id="sub_tea_type_id" name="sub_tea_type_id" required>
            <option value="">Select Sub Tea Type</option>
            @foreach(\App\Models\Tea::getSubTeaTypeOptions() as $key => $value)
                <option value="{{ $key }}" {{ old('sub_tea_type_id', $tea->sub_tea_type_id ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
        @error('sub_tea_type_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
        <select class="form-select @error('category_id') is-invalid @enderror" 
                id="category_id" name="category_id" required>
            <option value="">Select Category</option>
            @foreach(\App\Models\Tea::getCategoryOptions() as $key => $value)
                <option value="{{ $key }}" {{ old('category_id', $tea->category_id ?? '') == $key ? 'selected' : '' }}>
                    {{ $value }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<!-- Grade Code -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="grade_code" class="form-label">Grade Code <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('grade_code') is-invalid @enderror" 
               id="grade_code" name="grade_code" 
               value="{{ old('grade_code', $tea->grade_code ?? '') }}" 
               placeholder="Enter grade code (e.g., BP, BOP, PD)" required>
        <div class="form-text">
            You can enter free text or select from predefined list based on tea type selection.
        </div>
        @error('grade_code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('status', $tea->status ?? 1) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the tea's flavor profile, origin, or other details">{{ old('description', $tea->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="characteristics" class="form-label">Characteristics</label>
                            <input type="text" class="form-control @error('characteristics') is-invalid @enderror" 
                                   id="characteristics" name="characteristics" 
                                   value="{{ old('characteristics', is_array($tea->characteristics ?? null) ? implode(', ', $tea->characteristics) : '') }}" 
                                   placeholder="e.g., Fruity, Floral, Bold, Malty (separate with commas)">
                            @error('characteristics')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter characteristics separated by commas</div>
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
                                      placeholder="Any additional notes about this tea">{{ old('remarks', $tea->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.teas.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> 
                                    {{ isset($tea) ? 'Update Tea' : 'Create Tea' }}
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
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tea Information</h6>
            </div>
            <div class="card-body">
                <p class="small mb-3">
                    Define tea varieties with their categories, types, grades, and characteristics 
                    to maintain a comprehensive tea catalog.
                </p>

                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="text-primary">Field Guidelines</h6>
                        <ul class="mb-0 small">
                            <li><strong>Category:</strong> Main tea classification (Black, Green, etc.)</li>
                            <li><strong>Type:</strong> Processing method (Orthodox, CTC, etc.)</li>
                            <li><strong>Sub Title:</strong> Specific tea name or blend</li>
                            <li><strong>Grade:</strong> Quality classification (BP, BOP, etc.)</li>
                            <li><strong>Characteristics:</strong> Flavor notes and attributes</li>
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
    $('#teaForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['category', 'tea_type', 'sub_title', 'grade'];
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
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