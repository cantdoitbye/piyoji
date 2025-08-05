@extends('admin.layouts.app')

@section('title', 'Edit Contract')
@section('subtitle', 'Update contract information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.contracts.index') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.contracts.show', $contract->id) }}">{{ $contract->contract_number }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.contracts.show', $contract->id) }}" class="btn btn-outline-info">
            <i class="fas fa-eye me-1"></i> View Details
        </a>
        <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.contracts.update', $contract->id) }}" method="POST" id="contractForm">
            @csrf
            @method('PUT')
            
            <!-- Contract Basic Information -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Contract Information</h5>
                    <div>
                        <span class="status-badge status-{{ $contract->status }}">{{ $contract->status_text }}</span>
                        <small class="text-muted ms-2">
                            <i class="fas fa-calendar me-1"></i>
                            Last updated: {{ $contract->updated_at->format('M d, Y \a\t g:i A') }}
                        </small>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="seller_id" class="form-label">Seller <span class="text-danger">*</span></label>
                            <select class="form-select @error('seller_id') is-invalid @enderror" 
                                    id="seller_id" name="seller_id" required>
                                <option value="">Select Seller</option>
                                @foreach($sellers as $seller)
                                    <option value="{{ $seller->id }}" {{ old('seller_id', $contract->seller_id) == $seller->id ? 'selected' : '' }}>
                                        {{ $seller->seller_name }} - {{ $seller->tea_estate_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('seller_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contract_number" class="form-label">Contract Number</label>
                            <input type="text" class="form-control @error('contract_number') is-invalid @enderror" 
                                   id="contract_number" name="contract_number" value="{{ old('contract_number', $contract->contract_number) }}" 
                                   readonly>
                            @error('contract_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Contract number cannot be changed</div>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="contract_title" class="form-label">Contract Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('contract_title') is-invalid @enderror" 
                                   id="contract_title" name="contract_title" value="{{ old('contract_title', $contract->contract_title) }}" required>
                            @error('contract_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contract Dates and Status -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-calendar me-2"></i>Contract Dates & Status</h6>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="effective_date" class="form-label">Effective Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('effective_date') is-invalid @enderror" 
                                   id="effective_date" name="effective_date" value="{{ old('effective_date', $contract->effective_date->format('Y-m-d')) }}" required>
                            @error('effective_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="expiry_date" class="form-label">Expiry Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" value="{{ old('expiry_date', $contract->expiry_date->format('Y-m-d')) }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                @foreach($statusOptions as $value => $label)
                                    <option value="{{ $value }}" {{ old('status', $contract->status) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Terms and Remarks -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary mb-3"><i class="fas fa-file-alt me-2"></i>Terms & Remarks</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="terms_and_conditions" class="form-label">Terms and Conditions</label>
                            <textarea class="form-control @error('terms_and_conditions') is-invalid @enderror" 
                                      id="terms_and_conditions" name="terms_and_conditions" rows="4" 
                                      placeholder="Enter contract terms and conditions...">{{ old('terms_and_conditions', $contract->terms_and_conditions) }}</textarea>
                            @error('terms_and_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Any additional remarks...">{{ old('remarks', $contract->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contract Items -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Contract Items</h5>
                    <button type="button" class="btn btn-primary btn-sm" id="addItemBtn">
                        <i class="fas fa-plus me-1"></i> Add Item
                    </button>
                </div>
                <div class="card-body">
                    <div id="contractItems">
                        @if(old('contract_items') || $contract->contractItems->isNotEmpty())
                            @php
                                $items = old('contract_items') ?: $contract->contractItems;
                            @endphp
                            @foreach($items as $index => $item)
                                @php
                                    if (is_object($item)) {
                                        $itemData = $item;
                                    } else {
                                        $itemData = (object) $item;
                                    }
                                @endphp
                                <div class="contract-item border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-primary mb-0">Tea Grade Item</h6>
                                        <button type="button" class="btn btn-danger btn-sm remove-item">
                                            <i class="fas fa-trash me-1"></i> Remove
                                        </button>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Tea Grade <span class="text-danger">*</span></label>
                                            <select class="form-select tea-grade-select" name="contract_items[{{ $index }}][tea_grade]" required>
                                                <option value="">Select Grade</option>
                                                @foreach($teaGradeOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ ($itemData->tea_grade ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Price per Kg (₹) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" 
                                                   name="contract_items[{{ $index }}][price_per_kg]" 
                                                   value="{{ $itemData->price_per_kg ?? '' }}" required min="0">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Currency</label>
                                            <select class="form-select" name="contract_items[{{ $index }}][currency]">
                                                @foreach($currencyOptions as $value => $label)
                                                    <option value="{{ $value }}" {{ ($itemData->currency ?? 'INR') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="contract_items[{{ $index }}][is_active]" value="1" 
                                                       {{ ($itemData->is_active ?? true) ? 'checked' : '' }}>
                                                <label class="form-check-label">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tea Grade Description</label>
                                            <input type="text" class="form-control" 
                                                   name="contract_items[{{ $index }}][tea_grade_description]" 
                                                   value="{{ $itemData->tea_grade_description ?? '' }}"
                                                   placeholder="Optional description">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Quantity (Kg)</label>
                                            <input type="number" step="0.01" class="form-control" 
                                                   name="contract_items[{{ $index }}][quantity]" 
                                                   value="{{ $itemData->quantity ?? '' }}" min="0">
                                        </div>
                                        {{-- <div class="col-md-3 mb-3">
                                            <label class="form-label">Max Quantity (Kg)</label>
                                            <input type="number" step="0.01" class="form-control" 
                                                   name="contract_items[{{ $index }}][maximum_quantity]" 
                                                   value="{{ $itemData->maximum_quantity ?? '' }}" min="0">
                                        </div> --}}
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Quality Parameters</label>
                                            <textarea class="form-control" rows="2" 
                                                      name="contract_items[{{ $index }}][quality_parameters]" 
                                                      placeholder="Quality specifications...">{{ $itemData->quality_parameters ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Special Terms</label>
                                            <textarea class="form-control" rows="2" 
                                                      name="contract_items[{{ $index }}][special_terms]" 
                                                      placeholder="Special terms for this item...">{{ $itemData->special_terms ?? '' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-muted text-center">No items added yet. Click "Add Item" to start.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-save me-1"></i> Update Contract
                    </button>
                    <a href="{{ route('admin.contracts.show', $contract->id) }}" class="btn btn-outline-info me-2">
                        <i class="fas fa-eye me-1"></i> View
                    </a>
                    <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Information Panel -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contract Status</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <span class="status-badge status-{{ $contract->status }} fs-6">{{ $contract->status_text }}</span>
                </div>
                
                @if($contract->status === 'active' && $contract->is_expiring)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Expiring Soon!</strong><br>
                        This contract will expire in {{ $contract->days_remaining }} days.
                    </div>
                @endif

                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="mb-0">{{ $contract->active_items }}</h5>
                        <small class="text-muted">Active Items</small>
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
                    <div class="timeline-item mb-2">
                        <strong>Effective Date:</strong>
                        <div class="text-muted small">{{ $contract->effective_date->format('M d, Y') }}</div>
                    </div>
                    <div class="timeline-item mb-2">
                        <strong>Expiry Date:</strong>
                        <div class="text-muted small">{{ $contract->expiry_date->format('M d, Y') }}</div>
                    </div>
                    <div class="timeline-item mb-2">
                        <strong>Created:</strong>
                        <div class="text-muted small">{{ $contract->created_at->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                    <div class="timeline-item">
                        <strong>Last Updated:</strong>
                        <div class="text-muted small">{{ $contract->updated_at->format('M d, Y \a\t g:i A') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Editing Tips</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <small>
                        <strong>Important Notes:</strong><br>
                        • Contract number cannot be changed<br>
                        • Expiry date must be after effective date<br>
                        • Changing status affects contract validity<br>
                        • Add/remove items as needed<br>
                        • Ensure all prices are accurate
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contract Item Template -->
<template id="contractItemTemplate">
    <div class="contract-item border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <h6 class="text-primary mb-0">Tea Grade Item</h6>
            <button type="button" class="btn btn-danger btn-sm remove-item">
                <i class="fas fa-trash me-1"></i> Remove
            </button>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Tea Grade <span class="text-danger">*</span></label>
                <select class="form-select tea-grade-select" name="contract_items[INDEX][tea_grade]" required>
                    <option value="">Select Grade</option>
                    @foreach($teaGradeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Price per Kg (₹) <span class="text-danger">*</span></label>
                <input type="number" step="0.01" class="form-control" 
                       name="contract_items[INDEX][price_per_kg]" required min="0">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Currency</label>
                <select class="form-select" name="contract_items[INDEX][currency]">
                    @foreach($currencyOptions as $value => $label)
                        <option value="{{ $value }}" {{ $value === 'INR' ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" 
                           name="contract_items[INDEX][is_active]" value="1" checked>
                    <label class="form-check-label">Active</label>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Tea Grade Description</label>
                <input type="text" class="form-control" 
                       name="contract_items[INDEX][tea_grade_description]" 
                       placeholder="Optional description">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Quantity (Kg)</label>
                <input type="number" step="0.01" class="form-control" 
                       name="contract_items[INDEX][quantity]" min="0">
            </div>
            {{-- <div class="col-md-3 mb-3">
                <label class="form-label">Max Quantity (Kg)</label>
                <input type="number" step="0.01" class="form-control" 
                       name="contract_items[INDEX][maximum_quantity]" min="0">
            </div> --}}
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Quality Parameters</label>
                <textarea class="form-control" rows="2" 
                          name="contract_items[INDEX][quality_parameters]" 
                          placeholder="Quality specifications..."></textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Special Terms</label>
                <textarea class="form-control" rows="2" 
                          name="contract_items[INDEX][special_terms]" 
                          placeholder="Special terms for this item..."></textarea>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = {{ ($contract->contractItems->count() > 0) ? $contract->contractItems->count() : 1 }};

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
            toastr.warning('Expiry date must be after effective date');
            $('#expiry_date').val('');
        }
    });

    // Form validation
    $('#contractForm').on('submit', function(e) {
        const items = $('#contractItems .contract-item').length;
        
        if (items === 0) {
            e.preventDefault();
            toastr.error('Please add at least one contract item');
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
            toastr.error('Duplicate tea grades are not allowed in the same contract');
            return false;
        }

        // Validate quantity ranges
        let hasInvalidRange = false;
        
        $('#contractItems .contract-item').each(function() {
            const Qty = parseFloat($(this).find('input[name*="quantity"]').val()) || 0;
            // const maxQty = parseFloat($(this).find('input[name*="maximum_quantity"]').val()) || 0;
            
            if (Qty == 0) {
                hasInvalidRange = true;
                return false;
            }
        });
        
        if (hasInvalidRange) {
            e.preventDefault();
            toastr.error('Quantity can not be 0');
            return false;
        }
    });

    // Status change warning
    $('#status').change(function() {
        const currentStatus = '{{ $contract->status }}';
        const newStatus = $(this).val();
        
        if (currentStatus === 'active' && newStatus !== 'active') {
            if (!confirm('Changing status from Active will affect the contract validity. Are you sure?')) {
                $(this).val(currentStatus);
            }
        }
    });

    // Update item indices when items are removed
    function updateItemIndices() {
        $('#contractItems .contract-item').each(function(index) {
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });
    }

    // Update indices when item is removed
    $(document).on('click', '.remove-item', function() {
        setTimeout(updateItemIndices, 100);
    });
});
</script>
@endpush