@extends('admin.layouts.app')

@section('title', isset($billingCompany) ? 'Edit Billing Company' : 'Add New Billing Company')
@section('subtitle', isset($billingCompany) ? 'Update billing company information' : 'Create a new billing company')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.billing-companies.index') }}">Billing Companies</a></li>
    <li class="breadcrumb-item active">{{ isset($billingCompany) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <div class="btn-group">
        <a href="{{ route('admin.billing-companies.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Billing Companies
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-building me-2"></i>
                    {{ isset($billingCompany) ? 'Edit Billing Company' : 'Billing Company Information' }}
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ isset($billingCompany) ? route('admin.billing-companies.update', $billingCompany->id) : route('admin.billing-companies.store') }}" 
                      method="POST" id="billingCompanyForm">
                    @csrf
                    @if(isset($billingCompany))
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
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                   id="company_name" name="company_name" 
                                   value="{{ old('company_name', $billingCompany->company_name ?? '') }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="type" class="form-label">Company Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                    id="type" name="type" required onchange="toggleShippingAddresses()">
                                <option value="">Select Type</option>
                                @foreach($typeOptions as $key => $label)
                                    <option value="{{ $key }}" {{ old('type', $billingCompany->type ?? '') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_person" class="form-label">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                   id="contact_person" name="contact_person" 
                                   value="{{ old('contact_person', $billingCompany->contact_person ?? '') }}" required>
                            @error('contact_person')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $billingCompany->email ?? '') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" 
                                   value="{{ old('phone', $billingCompany->phone ?? '') }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                @foreach($statusOptions as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', ($billingCompany->status ?? true) ? '1' : '0') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Billing Address -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Billing Address
                            </h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="billing_address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('billing_address') is-invalid @enderror" 
                                      id="billing_address" name="billing_address" rows="3" required>{{ old('billing_address', $billingCompany->billing_address ?? '') }}</textarea>
                            @error('billing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="billing_city" class="form-label">City <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('billing_city') is-invalid @enderror" 
                                   id="billing_city" name="billing_city" 
                                   value="{{ old('billing_city', $billingCompany->billing_city ?? '') }}" required>
                            @error('billing_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="billing_state" class="form-label">State <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('billing_state') is-invalid @enderror" 
                                   id="billing_state" name="billing_state" 
                                   value="{{ old('billing_state', $billingCompany->billing_state ?? '') }}" required>
                            @error('billing_state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="billing_pincode" class="form-label">Pincode <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('billing_pincode') is-invalid @enderror" 
                                   id="billing_pincode" name="billing_pincode" 
                                   value="{{ old('billing_pincode', $billingCompany->billing_pincode ?? '') }}" required>
                            @error('billing_pincode')
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
                            <label for="gstin" class="form-label">GSTIN</label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                   id="gstin" name="gstin" 
                                   value="{{ old('gstin', $billingCompany->gstin ?? '') }}" 
                                   placeholder="22AAAAA0000A1Z5" maxlength="15">
                            @error('gstin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format: 22AAAAA0000A1Z5</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="pan" class="form-label">PAN</label>
                            <input type="text" class="form-control @error('pan') is-invalid @enderror" 
                                   id="pan" name="pan" 
                                   value="{{ old('pan', $billingCompany->pan ?? '') }}" 
                                   placeholder="AAAAA0000A" maxlength="10">
                            @error('pan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Format: AAAAA0000A</div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3">{{ old('remarks', $billingCompany->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Shipping Addresses (Only for Buyers) -->
                    <div class="row mb-4" id="shipping-addresses-section" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-truck me-2"></i>Shipping Addresses
                                <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="addShippingAddress()">
                                    <i class="fas fa-plus me-1"></i>Add Address
                                </button>
                            </h6>
                        </div>

                        <div class="col-12" id="shipping-addresses-container">
                            <!-- Shipping addresses will be added dynamically -->
                        </div>
                    </div>

                    <!-- POC Assignments -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-users me-2"></i>Point of Contact Assignments
                                <button type="button" class="btn btn-sm btn-outline-primary float-end" onclick="addPocAssignment()">
                                    <i class="fas fa-plus me-1"></i>Add POC
                                </button>
                            </h6>
                        </div>

                        <div class="col-12" id="poc-assignments-container">
                            <!-- POC assignments will be added dynamically -->
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="button" class="btn btn-outline-secondary me-md-2" onclick="window.history.back()">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>
                                    {{ isset($billingCompany) ? 'Update Billing Company' : 'Create Billing Company' }}
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
                        <li>A seller can be assigned multiple billing companies</li>
                        <li>Each billing company has details like GST Number, PAN</li>
                        <li>Type field determines functionality: Seller, Buyer, or Both</li>
                        <li>If type is Buyer, multiple shipping addresses can be added</li>
                        <li>POC can be assigned to billing company under specific seller</li>
                        <li>Same POC cannot be assigned to another seller</li>
                    </ul>
                </div>

                @if(isset($billingCompany))
                <div class="alert alert-success">
                    <h6><i class="fas fa-calendar me-2"></i>Company Details:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $billingCompany->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $billingCompany->updated_at->format('M d, Y') }}</li>
                        <li><strong>Type:</strong> {{ $billingCompany->type_text }}</li>
                        <li><strong>Status:</strong> {{ $billingCompany->status_text }}</li>
                        @if($billingCompany->canHaveShippingAddresses())
                            <li><strong>Shipping Addresses:</strong> {{ $billingCompany->getShippingAddressesCount() }}</li>
                        @endif
                    </ul>
                </div>
                @endif

                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Company Types:</h6>
                    <p class="mb-2 small">
                        <strong>Seller:</strong> Companies that supply tea products.
                    </p>
                    <p class="mb-2 small">
                        <strong>Buyer:</strong> Companies that purchase tea products. Can have multiple shipping addresses.
                    </p>
                    <p class="mb-0 small">
                        <strong>Both:</strong> Companies that both buy and sell tea products.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.shipping-address-item, .poc-assignment-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    position: relative;
}

.remove-item-btn {
    position: absolute;
    top: 10px;
    right: 10px;
}

.form-text {
    font-size: 0.875rem;
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let shippingAddressIndex = 0;
let pocAssignmentIndex = 0;

$(document).ready(function() {
    // Initialize existing data if editing
    @if(isset($billingCompany))
        toggleShippingAddresses();
        
        // Load existing shipping addresses
        @if($billingCompany->shippingAddresses)
            @foreach($billingCompany->shippingAddresses as $address)
                addShippingAddress({
                    address_label: '{{ $address->address_label }}',
                    shipping_address: '{{ $address->shipping_address }}',
                    shipping_city: '{{ $address->shipping_city }}',
                    shipping_state: '{{ $address->shipping_state }}',
                    shipping_pincode: '{{ $address->shipping_pincode }}',
                    contact_person: '{{ $address->contact_person }}',
                    contact_phone: '{{ $address->contact_phone }}',
                    is_default: {{ $address->is_default ? 'true' : 'false' }}
                });
            @endforeach
        @endif

        // Load existing POC assignments
        @if($billingCompany->pocAssignments)
            @foreach($billingCompany->pocAssignments as $assignment)
                addPocAssignment({
                    poc_id: {{ $assignment->poc_id }},
                    seller_id: {{ $assignment->seller_id }},
                    is_primary: {{ $assignment->is_primary ? 'true' : 'false' }}
                });
            @endforeach
        @endif
    @else
        toggleShippingAddresses();
    @endif

    // Format GSTIN and PAN inputs
    $('#gstin').on('input', function() {
        this.value = this.value.toUpperCase();
    });

    $('#pan').on('input', function() {
        this.value = this.value.toUpperCase();
    });
});

function toggleShippingAddresses() {
    const type = document.getElementById('type').value;
    const section = document.getElementById('shipping-addresses-section');
    
    if (type === 'buyer' || type === 'both') {
        section.style.display = 'block';
    } else {
        section.style.display = 'none';
    }
}

function addShippingAddress(data = {}) {
    const container = document.getElementById('shipping-addresses-container');
    const index = shippingAddressIndex++;
    
    const html = `
        <div class="shipping-address-item" id="shipping-address-${index}">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" onclick="removeShippingAddress(${index})">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Address Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_addresses[${index}][address_label]" 
                           value="${data.address_label || ''}" placeholder="e.g., Main Warehouse" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" class="form-control" name="shipping_addresses[${index}][contact_person]" 
                           value="${data.contact_person || ''}">
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">Shipping Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="shipping_addresses[${index}][shipping_address]" 
                              rows="2" required>${data.shipping_address || ''}</textarea>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">City <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_addresses[${index}][shipping_city]" 
                           value="${data.shipping_city || ''}" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">State <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_addresses[${index}][shipping_state]" 
                           value="${data.shipping_state || ''}" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="shipping_addresses[${index}][shipping_pincode]" 
                           value="${data.shipping_pincode || ''}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" class="form-control" name="shipping_addresses[${index}][contact_phone]" 
                           value="${data.contact_phone || ''}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="shipping_addresses[${index}][is_default]" 
                               id="is_default_${index}" value="1" ${data.is_default ? 'checked' : ''}>
                        <label class="form-check-label" for="is_default_${index}">
                            Default Address
                        </label>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removeShippingAddress(index) {
    document.getElementById(`shipping-address-${index}`).remove();
}

function addPocAssignment(data = {}) {
    const container = document.getElementById('poc-assignments-container');
    const index = pocAssignmentIndex++;
    
    const pocOptions = @json($pocs->pluck('poc_name', 'id'));
    const sellerOptions = @json($sellers->pluck('seller_name', 'id'));
    
    let pocOptionsHtml = '<option value="">Select POC</option>';
    Object.entries(pocOptions).forEach(([id, name]) => {
        pocOptionsHtml += `<option value="${id}" ${data.poc_id == id ? 'selected' : ''}>${name}</option>`;
    });
    
    let sellerOptionsHtml = '<option value="">Select Seller</option>';
    Object.entries(sellerOptions).forEach(([id, name]) => {
        sellerOptionsHtml += `<option value="${id}" ${data.seller_id == id ? 'selected' : ''}>${name}</option>`;
    });
    
    const html = `
        <div class="poc-assignment-item" id="poc-assignment-${index}">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item-btn" onclick="removePocAssignment(${index})">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row">
                <div class="col-md-5 mb-3">
                    <label class="form-label">POC <span class="text-danger">*</span></label>
                    <select class="form-select" name="poc_assignments[${index}][poc_id]" required>
                        ${pocOptionsHtml}
                    </select>
                </div>
                
                <div class="col-md-5 mb-3">
                    <label class="form-label">Seller <span class="text-danger">*</span></label>
                    <select class="form-select" name="poc_assignments[${index}][seller_id]" required>
                        ${sellerOptionsHtml}
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="poc_assignments[${index}][is_primary]" 
                               id="is_primary_poc_${index}" value="1" ${data.is_primary ? 'checked' : ''}>
                        <label class="form-check-label" for="is_primary_poc_${index}">
                            Primary
                        </label>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
}

function removePocAssignment(index) {
    document.getElementById(`poc-assignment-${index}`).remove();
}
</script>
@endpush