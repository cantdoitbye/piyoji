@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>

    <form action="{{ route('admin.contracts.store') }}" method="POST" id="contractForm">
        @csrf
        
        <!-- Contract Basic Information -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Contract Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="seller_id">Seller <span class="text-danger">*</span></label>
                            <select class="form-control @error('seller_id') is-invalid @enderror" 
                                    id="seller_id" name="seller_id" required>
                                <option value="">Select Seller</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->seller_name }} - {{ $seller->tea_estate_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seller_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="contract_number">Contract Number</label>
                            <input type="text" class="form-control @error('contract_number') is-invalid @enderror" 
                                   id="contract_number" name="contract_number" value="{{ old('contract_number') }}" 
                                   placeholder="Auto-generated if left blank">
                            @error('contract_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Leave blank to auto-generate</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="contract_title">Contract Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('contract_title') is-invalid @enderror" 
                           id="contract_title" name="contract_title" value="{{ old('contract_title') }}" required>
                    @error('contract_title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('effective_date') is-invalid @enderror" 
                                   id="effective_date" name="effective_date" value="{{ old('effective_date', date('Y-m-d')) }}" required>
                            @error('effective_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="expiry_date">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" value="{{ old('expiry_date') }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', 'draft') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="terms_and_conditions">Terms and Conditions</label>
                    <textarea class="form-control @error('terms_and_conditions') is-invalid @enderror" 
                              id="terms_and_conditions" name="terms_and_conditions" rows="4" 
                              placeholder="Enter contract terms and conditions...">{{ old('terms_and_conditions') }}</textarea>
                    @error('terms_and_conditions')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="remarks">Remarks</label>
                    <textarea class="form-control @error('remarks') is-invalid @enderror" 
                              id="remarks" name="remarks" rows="3" 
                              placeholder="Any additional remarks...">{{ old('remarks') }}</textarea>
                    @error('remarks')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Contract Items -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Contract Items</h6>
                <button type="button" class="btn btn-primary btn-sm" id="addItemBtn">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
            <div class="card-body">
                <div id="contractItems">
                    <div class="text-muted text-center">No items added yet. Click "Add Item" to start.</div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Contract
                </button>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Contract Item Template -->
<template id="contractItemTemplate">
    <div class="contract-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="text-primary mb-0">Tea Grade Item</h6>
            <button type="button" class="btn btn-danger btn-sm remove-item">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Tea Grade <span class="text-danger">*</span></label>
                    <select class="form-control tea-grade-select" name="contract_items[INDEX][tea_grade]" required>
                        <option value="">Select Grade</option>
                        @foreach($teaGradeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Price per Kg (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" 
                           name="contract_items[INDEX][price_per_kg]" required min="0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Currency</label>
                    <select class="form-control" name="contract_items[INDEX][currency]">
                        @foreach($currencyOptions as $value => $label)
                            <option value="{{ $value }}" {{ $value === 'INR' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <div class="form-check mt-4">
                        <input type="checkbox" class="form-check-input" 
                               name="contract_items[INDEX][is_active]" value="1" checked>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Quality Parameters</label>
                    <textarea class="form-control" rows="2" 
                              name="contract_items[INDEX][quality_parameters]" 
                              placeholder="Quality specifications..."></textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Special Terms</label>
                    <textarea class="form-control" rows="2" 
                              name="contract_items[INDEX][special_terms]" 
                              placeholder="Special terms for this item..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = 0;

    // Add new contract item
    $('#addItemBtn').click(function() {
        const template = $('#contractItemTemplate').html();
        const itemHtml = template.replace(/INDEX/g, itemIndex);
        
        if ($('#contractItems .text-muted').length) {
            $('#contractItems').html('');
        }
        
        $('#contractItems').append(itemHtml);
        itemIndex++;
    });

    // Remove contract item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.contract-item').remove();
        
        if ($('#contractItems .contract-item').length === 0) {
            $('#contractItems').html('<div class="text-muted text-center">No items added yet. Click "Add Item" to start.</div>');
        }
    });

    // Date validation
    $('#effective_date, #expiry_date').change(function() {
        const effectiveDate = new Date($('#effective_date').val());
        const expiryDate = new Date($('#expiry_date').val());
        
        if (effectiveDate && expiryDate && expiryDate <= effectiveDate) {
            alert('Expiry date must be after effective date');
            $('#expiry_date').val('');
        }
    });

    // Form validation
    $('#contractForm').on('submit', function(e) {
        const items = $('#contractItems .contract-item').length;
        
        if (items === 0) {
            e.preventDefault();
            alert('Please add at least one contract item');
            return false;
        }

        // Validate tea grade uniqueness
        const teaGrades = [];
        let hasDuplicate = false;
        
        $('#contractItems .tea-grade-select').each(function() {
            const grade = $(this).val();
            if (grade) {
                if (teaGrades.includes(grade)) {
                    hasDuplicate = true;
                    return false;
                }
                teaGrades.push(grade);
            }
        });
        
        if (hasDuplicate) {
            e.preventDefault();
            alert('Duplicate tea grades are not allowed in the same contract');
            return false;
        }

        // Validate quantity ranges
        let hasInvalidRange = false;
        
        $('#contractItems .contract-item').each(function() {
            const minQty = parseFloat($(this).find('input[name*="minimum_quantity"]').val()) || 0;
            const maxQty = parseFloat($(this).find('input[name*="maximum_quantity"]').val()) || 0;
            
            if (minQty > 0 && maxQty > 0 && minQty > maxQty) {
                hasInvalidRange = true;
                return false;
            }
        });
        
        if (hasInvalidRange) {
            e.preventDefault();
            alert('Minimum quantity cannot be greater than maximum quantity');
            return false;
        }
    });

    // Auto-set expiry date to 1 year from effective date
    $('#effective_date').change(function() {
        const effectiveDate = new Date($(this).val());
        if (effectiveDate && !$('#expiry_date').val()) {
            const expiryDate = new Date(effectiveDate);
            expiryDate.setFullYear(expiryDate.getFullYear() + 1);
            $('#expiry_date').val(expiryDate.toISOString().split('T')[0]);
        }
    });

    // Auto-generate contract title based on seller selection
    $('#seller_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const sellerName = selectedOption.text().split(' - ')[0];
        
        if (sellerName && !$('#contract_title').val()) {
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            $('#contract_title').val(`Tea Supply Contract - ${sellerName} - ${year}`);
        }
    });

    // Add first item automatically
    $('#addItemBtn').click();
});
</script>
@endpush