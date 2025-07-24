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
    
    <!-- NEW IMPROVED TEA GRADES UI -->
    <div class="col-12 mb-3">
        <div class="tea-grade-selector @error('tea_grades') border-danger @enderror" id="teaGradeSelector">
            <div class="d-flex align-items-center mb-3">
                <i class="fas fa-leaf text-success me-2"></i>
                <strong>Tea Grades Handled <span class="text-danger">*</span></strong>
                <span class="ms-auto text-muted" id="selectionCount">0 selected</span>
            </div>
            
            <!-- Search Box -->
            <input type="text" class="search-box" placeholder="Search tea grades by code or name..." id="searchBox">
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">
                    <i class="fas fa-check-double me-1"></i> Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAll()">
                    <i class="fas fa-times me-1"></i> Clear All
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="selectCommon()">
                    <i class="fas fa-star me-1"></i> Select Common Grades
                </button>
            </div>
            
            <!-- Tea Grades Grid -->
            <div class="tea-grade-grid" id="teaGradeGrid">
                @foreach($teaGrades as $key => $grade)
                    <div class="tea-grade-item {{ in_array($key, old('tea_grades', $seller->tea_grades ?? [])) ? 'selected' : '' }}" 
                         data-grade="{{ $key }}" onclick="toggleGrade('{{ $key }}')">
                        <div class="grade-code">{{ $key }}</div>
                        <div class="grade-name">{{ $grade }}</div>
                        <i class="fas fa-check check-icon"></i>
                    </div>
                @endforeach
            </div>
            
            <!-- Selection Summary -->
            <div class="selection-summary d-none" id="selectionSummary">
                <div class="d-flex align-items-center mb-2">
                    <i class="fas fa-info-circle text-primary me-2"></i>
                    <strong>Selected Tea Grades:</strong>
                </div>
                <div id="selectedTags"></div>
            </div>
        </div>
        
        <!-- Hidden select for form submission -->
        <select class="d-none @error('tea_grades') is-invalid @enderror" 
                name="tea_grades[]" id="hiddenTeaGrades" multiple>
            @foreach($teaGrades as $key => $grade)
                <option value="{{ $key }}" 
                        {{ in_array($key, old('tea_grades', $seller->tea_grades ?? [])) ? 'selected' : '' }}>
                    {{ $key }} - {{ $grade }}
                </option>
            @endforeach
        </select>
        
        @error('tea_grades')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text">Select all tea grades that this seller handles. Use search or quick actions for easier selection.</div>
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
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
.select2-container--bootstrap-5 .select2-selection {
    min-height: 38px;
}
.tea-grade-selector {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .tea-grade-selector.has-selections {
        border-color: #198754;
        background: #f0f9ff;
    }
    
    .tea-grade-selector.border-danger {
        border-color: #dc3545 !important;
        background: #ffeaea;
    }
    
    .tea-grade-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 12px;
        margin-top: 15px;
    }
    
    .tea-grade-item {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
        position: relative;
        min-height: 70px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .tea-grade-item:hover {
        border-color: #0d6efd;
        box-shadow: 0 2px 8px rgba(13, 110, 253, 0.15);
        transform: translateY(-1px);
    }
    
    .tea-grade-item.selected {
        border-color: #198754;
        background: #d1e7dd;
        box-shadow: 0 2px 8px rgba(25, 135, 84, 0.25);
    }
    
    .tea-grade-item .grade-code {
        font-weight: bold;
        color: #495057;
        font-size: 14px;
        margin-bottom: 4px;
    }
    
    .tea-grade-item .grade-name {
        color: #6c757d;
        font-size: 12px;
        line-height: 1.3;
    }
    
    .tea-grade-item .check-icon {
        position: absolute;
        top: 8px;
        right: 8px;
        color: #198754;
        opacity: 0;
        transition: opacity 0.2s ease;
        font-size: 14px;
    }
    
    .tea-grade-item.selected .check-icon {
        opacity: 1;
    }
    
    .selection-summary {
        background: #e7f3ff;
        border: 1px solid #b6d7ff;
        border-radius: 6px;
        padding: 12px;
        margin-top: 15px;
    }
    
    .selected-grade-tag {
        display: inline-block;
        background: #198754;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        margin: 2px;
        font-weight: 500;
    }
    
    .search-box {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 10px 12px;
        width: 100%;
        margin-bottom: 15px;
        font-size: 14px;
    }
    
    .search-box:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .quick-actions {
        margin-bottom: 15px;
    }
    
    .quick-actions button {
        margin-right: 8px;
        margin-bottom: 8px;
        font-size: 13px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .tea-grade-grid {
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 10px;
        }
        
        .tea-grade-item {
            padding: 10px;
            min-height: 60px;
        }
        
        .quick-actions button {
            font-size: 12px;
            padding: 4px 8px;
        }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for tea grades
  
 initializeTeaGradeSelector();
    // Format GSTIN input
     $('#gstin').on('input', function() {
        this.value = this.value.toUpperCase();
    });

   $('#poc_ids').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: 'Select POCs...',
    allowClear: true,
    closeOnSelect: false
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
          if (!validateTeaGrades()) {
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

function validateGSTIN(gstin) {
    // const gstinRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/;
    // return gstinRegex.test(gstin);
}

function validatePAN(pan) {
    // const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/;
    // return panRegex.test(pan);
}

const teaGrades = @json($teaGrades);
const commonGrades = ['BP', 'BOP', 'PD', 'Dust', 'FOP', 'OP'];
let selectedGrades = @json(old('tea_grades', $seller->tea_grades ?? []));

function initializeTeaGradeSelector() {
    updateUI();
    
    // Initialize search functionality
    document.getElementById('searchBox').addEventListener('input', filterGrades);
}

function toggleGrade(grade) {
    const index = selectedGrades.indexOf(grade);
    if (index > -1) {
        selectedGrades.splice(index, 1);
    } else {
        selectedGrades.push(grade);
    }
    updateUI();
    
    // Clear any previous validation errors
    const selector = document.getElementById('teaGradeSelector');
    selector.classList.remove('border-danger');
    const errorElements = document.querySelectorAll('.invalid-feedback');
    errorElements.forEach(el => el.remove());
}

function selectAll() {
    selectedGrades = Object.keys(teaGrades);
    updateUI();
}

function clearAll() {
    selectedGrades = [];
    updateUI();
}

function selectCommon() {
    selectedGrades = [...commonGrades];
    updateUI();
}

function updateUI() {
    // Update visual selection
    document.querySelectorAll('.tea-grade-item').forEach(item => {
        const grade = item.dataset.grade;
        if (selectedGrades.includes(grade)) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
    
    // Update selector container
    const selector = document.getElementById('teaGradeSelector');
    if (selectedGrades.length > 0) {
        selector.classList.add('has-selections');
    } else {
        selector.classList.remove('has-selections');
    }
    
    // Update count
    const countElement = document.getElementById('selectionCount');
    countElement.textContent = `${selectedGrades.length} selected`;
    
    // Update summary
    const summary = document.getElementById('selectionSummary');
    const tagsContainer = document.getElementById('selectedTags');
    
    if (selectedGrades.length > 0) {
        summary.classList.remove('d-none');
        tagsContainer.innerHTML = selectedGrades.map(grade => 
            `<span class="selected-grade-tag">${grade} - ${teaGrades[grade]}</span>`
        ).join('');
    } else {
        summary.classList.add('d-none');
    }
    
    // Update hidden form field
    const hiddenSelect = document.getElementById('hiddenTeaGrades');
    hiddenSelect.innerHTML = selectedGrades.map(grade => 
        `<option value="${grade}" selected>${grade}</option>`
    ).join('');
}

function filterGrades() {
    const searchTerm = document.getElementById('searchBox').value.toLowerCase();
    document.querySelectorAll('.tea-grade-item').forEach(item => {
        const grade = item.dataset.grade;
        const name = teaGrades[grade];
        const matches = grade.toLowerCase().includes(searchTerm) || 
                       name.toLowerCase().includes(searchTerm);
        item.style.display = matches ? 'block' : 'none';
    });
}

function validateTeaGrades() {
    if (selectedGrades.length === 0) {
        const selector = document.getElementById('teaGradeSelector');
        selector.classList.add('border-danger');
        return false;
    }
    return true;
}

function showError(fieldId, message) {
    if (fieldId === 'tea_grades') {
        const selector = document.getElementById('teaGradeSelector');
        selector.classList.add('border-danger');
        
        // Remove existing error message
        const existingError = selector.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        // Add new error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback d-block';
        errorDiv.textContent = message;
        selector.parentNode.appendChild(errorDiv);
    } else {
        const field = $('#' + fieldId);
        field.addClass('is-invalid');
        
        // Remove existing error message
        field.siblings('.invalid-feedback').remove();
        
        // Add new error message
        field.after('<div class="invalid-feedback">' + message + '</div>');
    }
}

// Clear error state on input
$('.form-control, .form-select').on('input change', function() {
    $(this).removeClass('is-invalid');
    $(this).siblings('.invalid-feedback').remove();
});
</script>
@endpush