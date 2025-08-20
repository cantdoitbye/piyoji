@extends('admin.layouts.app')

@section('title', isset($garden) ? 'Edit Garden' : 'Add New Garden')
@section('subtitle', isset($garden) ? 'Update garden information' : 'Add a new tea garden to the system')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.gardens.index') }}">Gardens</a></li>
    <li class="breadcrumb-item active">{{ isset($garden) ? 'Edit' : 'Add New' }}</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.gardens.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ isset($garden) ? 'Edit Garden Information' : 'Add New Garden' }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ isset($garden) ? route('admin.gardens.update', $garden->id) : route('admin.gardens.store') }}" id="gardenForm">
                    @csrf
                    @if(isset($garden))
                        @method('PUT')
                    @endif

                    <!-- Basic Garden Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>Basic Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="garden_name" class="form-label">Garden Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('garden_name') is-invalid @enderror" 
                                   id="garden_name" name="garden_name" 
                                   value="{{ old('garden_name', $garden->garden_name ?? '') }}" required>
                            @error('garden_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="contact_person_name" class="form-label">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('contact_person_name') is-invalid @enderror" 
                                   id="contact_person_name" name="contact_person_name" 
                                   value="{{ old('contact_person_name', $garden->contact_person_name ?? '') }}" required>
                            @error('contact_person_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="mobile_no" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('mobile_no') is-invalid @enderror" 
                                   id="mobile_no" name="mobile_no" 
                                   value="{{ old('mobile_no', $garden->mobile_no ?? '') }}" required>
                            @error('mobile_no')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email', $garden->email ?? '') }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="address" class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" required>{{ old('address', $garden->address ?? '') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" 
                                   value="{{ old('city', $garden->city ?? '') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <select class="form-select @error('state') is-invalid @enderror" id="state" name="state">
                                <option value="">Select State</option>
                                @foreach(\App\Models\Garden::getStatesOptions() as $key => $value)
                                    <option value="{{ $key }}" {{ old('state', $garden->state ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="pincode" class="form-label">Pincode</label>
                            <input type="text" class="form-control @error('pincode') is-invalid @enderror" 
                                   id="pincode" name="pincode" 
                                   value="{{ old('pincode', $garden->pincode ?? '') }}">
                            @error('pincode')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Add this to your garden create form blade file -->
<!-- Add this section after the Tea Selection section in create.blade.php -->

<!-- Acceptable Invoice Types -->
<div class="row mb-4">
    <div class="col-12">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-file-invoice me-2"></i>Acceptable Invoice Types
        </h6>
    </div>

    <div class="col-12 mb-3">
        <label class="form-label">Select Acceptable Invoice Types</label>
        <div class="row">
            @foreach(\App\Models\Garden::getInvoiceTypesOptions() as $key => $label)
                <div class="col-md-4 mb-2">
                    <div class="form-check">
                        <input class="form-check-input @error('acceptable_invoice_types') is-invalid @enderror" 
                               type="checkbox" 
                               name="acceptable_invoice_types[]" 
                               value="{{ $key }}" 
                               id="invoice_type_{{ $key }}"
                               {{ in_array($key, old('acceptable_invoice_types', $garden->acceptable_invoice_types ?? [])) ? 'checked' : '' }}>
                        <label class="form-check-label" for="invoice_type_{{ $key }}">
                            {{ $label }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
        @error('acceptable_invoice_types')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
        <div class="form-text">
            <i class="fas fa-info-circle me-1"></i>
            Select the types of invoices this garden can accept.
        </div>
    </div>
</div>

                    <!-- Multiple Category Tea Selection System -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Tea Varieties Selection
                                <button type="button" class="btn btn-sm btn-outline-success float-end" onclick="addCategorySelection()">
                                    <i class="fas fa-plus me-1"></i>Add Category
                                </button>
                            </h6>
                        </div>
                        
                        <div class="col-12" id="category-selections-container">
                            <!-- Category selections will be added here dynamically -->
                        </div>

                        <!-- Final Tea Selection Based on All Filters -->
                        <div class="col-12 mt-4" id="final-tea-selection" style="display: none;">
                            <div class="card border-success">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-success">
                                        <i class="fas fa-check-circle me-2"></i>Available Tea Varieties
                                        <span class="badge bg-success ms-2" id="available-tea-count">0</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <label class="form-label">Select Tea Varieties <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tea_ids') is-invalid @enderror" 
                                            id="tea_ids" name="tea_ids[]" multiple required>
                                        <!-- Options will be populated dynamically -->
                                    </select>
                                    @error('tea_ids')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Tea varieties are filtered based on your category, tea type, and grade code selections above.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- POC Assignment -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user-tie me-2"></i>Point of Contact
                            </h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="poc_ids" class="form-label">
                                <i class="fas fa-users me-1"></i>Assigned POCs
                            </label>
                            <select class="form-select @error('poc_ids') is-invalid @enderror" 
                                    id="poc_ids" name="poc_ids[]" multiple>
                                @foreach($pocs as $poc)
                                    <option value="{{ $poc->id }}" 
                                            {{ in_array($poc->id, old('poc_ids', $garden->poc_ids ?? [])) ? 'selected' : '' }}>
                                        {{ $poc->poc_name }} ({{ $poc->designation }}) - {{ $poc->poc_type_text }}
                                    </option>
                                @endforeach
                            </select>
                            @error('poc_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Select one or more POCs who will handle this garden.
                            </div>
                        </div>
                    </div>

                    <!-- Status & Remarks -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-cog me-2"></i>Additional Information
                            </h6>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                @foreach(\App\Models\Garden::getStatusOptions() as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', ($garden->status ?? true) ? '1' : '0') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3">{{ old('remarks', $garden->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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
                                    {{ isset($garden) ? 'Update Garden' : 'Create Garden' }}
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
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Garden Information</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Multi-Category Selection:</h6>
                    <ul class="mb-0 small">
                        <li>Gardens can produce <strong>multiple tea categories</strong></li>
                        <li>Add each category separately with its tea types and grade codes</li>
                        <li>System will show all matching tea varieties for selection</li>
                        <li>Use filters to narrow down available options</li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    <h6><i class="fas fa-cogs me-2"></i>How it works:</h6>
                    <ol class="mb-0 small">
                        <li>Click "Add Category" to add tea categories</li>
                        <li>For each category, select tea types</li>
                        <li>Optionally filter by specific grade codes</li>
                        <li>View and select from filtered tea varieties</li>
                    </ol>
                </div>

                @if(isset($garden))
                <div class="alert alert-warning">
                    <h6><i class="fas fa-calendar me-2"></i>Garden Details:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $garden->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $garden->updated_at->format('M d, Y') }}</li>
                        <li><strong>Current Tea Varieties:</strong> {{ $garden->teas ? $garden->teas->count() : 0 }}</li>
                        <li><strong>Status:</strong> {{ $garden->status ? 'Active' : 'Inactive' }}</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>

        <!-- Selection Summary -->
        <div class="card mt-3" id="selection-summary-card" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list me-2"></i>Selection Summary
                </h6>
            </div>
            <div class="card-body" id="selection-summary-content">
                <!-- Dynamic content will be populated here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.category-selection-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    background-color: #f8f9fa;
}

.category-selection-item .remove-category-btn {
    position: absolute;
    top: 10px;
    right: 10px;
}

.tea-type-checkbox, .grade-code-checkbox {
    margin: 0.25rem;
}

.checkbox-group {
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
    background-color: white;
}

.select2-selection.is-invalid {
    border-color: #dc3545 !important;
}

.no-results-message {
    padding: 1rem;
    text-align: center;
    color: #6c757d;
    font-style: italic;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let categoryIndex = 0;
let allSelectedFilters = [];

$(document).ready(function() {
    // Initialize POC Select2
    $('#poc_ids').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select POCs...',
        allowClear: true,
        closeOnSelect: false
    });

    // Initialize final tea selection Select2
    $('#tea_ids').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'No tea varieties available yet...',
        allowClear: true,
        closeOnSelect: false
    });

    // Add initial category selection
    addCategorySelection();

    // Load existing data if editing
    @if(isset($garden) && $garden->category_filters)
        // Load existing category filters if available
        loadExistingCategoryFilters();
    @endif
});

function addCategorySelection() {
    const container = document.getElementById('category-selections-container');
    const index = categoryIndex++;
    
    const html = `
        <div class="category-selection-item" id="category-selection-${index}">
            <button type="button" class="btn btn-sm btn-outline-danger remove-category-btn" onclick="removeCategorySelection(${index})">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="text-secondary">
                        <i class="fas fa-tag me-2"></i>Category Set ${index + 1}
                    </h6>
                </div>
                
                <!-- Category Selection -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Select Category <span class="text-danger">*</span></label>
                    <select class="form-select" id="category_${index}" name="category_filters[${index}][category]" 
                            onchange="loadTeaTypes(${index})" required>
                        <option value="">Choose Category</option>
                        @foreach(\App\Models\Tea::getCategoryOptions() as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Tea Types (Multiple Selection) -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Select Tea Types</label>
                    <div class="checkbox-group" id="tea_types_${index}">
                        <div class="no-results-message">Select a category first</div>
                    </div>
                </div>
                
                <!-- Grade Codes (Multiple Selection) -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Filter by Grade Codes <small class="text-muted">(Optional)</small></label>
                    <div class="checkbox-group" id="grade_codes_${index}">
                        <div class="no-results-message">Select tea types first</div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    updateSelectionSummary();
}

function removeCategorySelection(index) {
    document.getElementById(`category-selection-${index}`).remove();
    updateFinalTeaSelection();
    updateSelectionSummary();
}

function loadTeaTypes(categoryIndex) {
    const category = document.getElementById(`category_${categoryIndex}`).value;
    const container = document.getElementById(`tea_types_${categoryIndex}`);
    
    // Clear existing selections
    container.innerHTML = '<div class="no-results-message">Loading...</div>';
    document.getElementById(`grade_codes_${categoryIndex}`).innerHTML = '<div class="no-results-message">Select tea types first</div>';
    
    if (!category) {
        container.innerHTML = '<div class="no-results-message">Select a category first</div>';
        return;
    }
    
    // Fetch tea types for category
    $.ajax({
        url: '{{ route("admin.teas.tea-types-by-category") }}',
        method: 'GET',
        data: { category: category },
        success: function(response) {
            let html = '';
            $.each(response.tea_types, function(key, value) {
                html += `
                    <div class="form-check tea-type-checkbox">
                        <input class="form-check-input" type="checkbox" value="${key}" 
                               id="tea_type_${categoryIndex}_${key}" 
                               name="category_filters[${categoryIndex}][tea_types][]"
                               onchange="loadGradeCodes(${categoryIndex})">
                        <label class="form-check-label" for="tea_type_${categoryIndex}_${key}">
                            ${value}
                        </label>
                    </div>
                `;
            });
            
            container.innerHTML = html || '<div class="no-results-message">No tea types found</div>';
        },
        error: function() {
            container.innerHTML = '<div class="no-results-message">Error loading tea types</div>';
        }
    });
}

function loadGradeCodes(categoryIndex) {
    const selectedTeaTypes = [];
    document.querySelectorAll(`input[name="category_filters[${categoryIndex}][tea_types][]"]:checked`).forEach(function(checkbox) {
        selectedTeaTypes.push(checkbox.value);
    });
    
    const container = document.getElementById(`grade_codes_${categoryIndex}`);
    
    if (selectedTeaTypes.length === 0) {
        container.innerHTML = '<div class="no-results-message">Select tea types first</div>';
        updateFinalTeaSelection();
        return;
    }
    
    container.innerHTML = '<div class="no-results-message">Loading grade codes...</div>';
    
    // Fetch existing grade codes for selected tea types
    $.ajax({
        url: '{{ route("admin.teas.existing-grade-codes") }}',
        method: 'GET',
        data: { tea_types: selectedTeaTypes },
        success: function(response) {
            let html = '';
            if (response.grade_codes && response.grade_codes.length > 0) {
                response.grade_codes.forEach(function(gradeCode) {
                    html += `
                        <div class="form-check grade-code-checkbox">
                            <input class="form-check-input" type="checkbox" value="${gradeCode}" 
                                   id="grade_code_${categoryIndex}_${gradeCode}" 
                                   name="category_filters[${categoryIndex}][grade_codes][]"
                                   onchange="updateFinalTeaSelection()">
                            <label class="form-check-label" for="grade_code_${categoryIndex}_${gradeCode}">
                                ${gradeCode}
                            </label>
                        </div>
                    `;
                });
                html += `
                    <hr>
                    <div class="text-center">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllGradeCodes(${categoryIndex})">
                            Select All
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAllGradeCodes(${categoryIndex})">
                            Clear All
                        </button>
                    </div>
                `;
            } else {
                html = '<div class="no-results-message">No existing grade codes found for selected tea types</div>';
            }
            
            container.innerHTML = html;
            updateFinalTeaSelection();
        },
        error: function() {
            container.innerHTML = '<div class="no-results-message">Error loading grade codes</div>';
        }
    });
}

function selectAllGradeCodes(categoryIndex) {
    document.querySelectorAll(`#grade_codes_${categoryIndex} input[type="checkbox"]`).forEach(function(checkbox) {
        checkbox.checked = true;
    });
    updateFinalTeaSelection();
}

function clearAllGradeCodes(categoryIndex) {
    document.querySelectorAll(`#grade_codes_${categoryIndex} input[type="checkbox"]`).forEach(function(checkbox) {
        checkbox.checked = false;
    });
    updateFinalTeaSelection();
}

function updateFinalTeaSelection() {
    // Collect all filter criteria
    const allFilters = [];
    
    document.querySelectorAll('.category-selection-item').forEach(function(item, index) {
        const categorySelect = item.querySelector('select[name*="[category]"]');
        const category = categorySelect ? categorySelect.value : '';
        
        if (!category) return;
        
        const teaTypes = [];
        item.querySelectorAll('input[name*="[tea_types]"]:checked').forEach(function(checkbox) {
            teaTypes.push(checkbox.value);
        });
        
        const gradeCodes = [];
        item.querySelectorAll('input[name*="[grade_codes]"]:checked').forEach(function(checkbox) {
            gradeCodes.push(checkbox.value);
        });
        
        if (teaTypes.length > 0) {
            allFilters.push({
                categories: [category],
                tea_types: teaTypes,
                grade_codes: gradeCodes
            });
        }
    });
    
    allSelectedFilters = allFilters;
    
    if (allFilters.length === 0) {
        $('#final-tea-selection').hide();
        $('#tea_ids').empty().trigger('change');
        updateSelectionSummary();
        return;
    }
    
    // Fetch filtered tea varieties
    $.ajax({
        url: '{{ route("admin.teas.filtered-teas-multiple") }}',
        method: 'POST',
        data: { 
            filters: allFilters,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            const teaSelect = $('#tea_ids');
            teaSelect.empty();
            
            if (response.teas && response.teas.length > 0) {
                response.teas.forEach(function(tea) {
                    teaSelect.append(`<option value="${tea.id}">${tea.full_name}</option>`);
                });
                
                $('#available-tea-count').text(response.teas.length);
                $('#final-tea-selection').show();
                
                // Restore selected values if editing
                @if(isset($garden) && $garden->tea_ids)
                    teaSelect.val(@json($garden->tea_ids)).trigger('change');
                @endif
            } else {
                $('#final-tea-selection').hide();
            }
            
            updateSelectionSummary();
        },
        error: function() {
            $('#final-tea-selection').hide();
            alert('Error loading tea varieties. Please try again.');
        }
    });
}

function updateSelectionSummary() {
    const summaryCard = document.getElementById('selection-summary-card');
    const summaryContent = document.getElementById('selection-summary-content');
    
    if (allSelectedFilters.length === 0) {
        summaryCard.style.display = 'none';
        return;
    }
    
    let html = '<div class="small">';
    allSelectedFilters.forEach(function(filter, index) {
        html += `
            <div class="mb-2">
                <strong>Set ${index + 1}:</strong><br>
                <span class="text-muted">Categories:</span> ${filter.categories.join(', ')}<br>
                <span class="text-muted">Tea Types:</span> ${filter.tea_types.join(', ')}<br>
                ${filter.grade_codes.length > 0 ? '<span class="text-muted">Grade Codes:</span> ' + filter.grade_codes.join(', ') : '<span class="text-muted">All Grade Codes</span>'}
            </div>
        `;
    });
    html += '</div>';
    
    summaryContent.innerHTML = html;
    summaryCard.style.display = 'block';
}

// Form validation
$('#gardenForm').on('submit', function(e) {
    let isValid = true;
    
    // Validate required fields
    const requiredFields = ['garden_name', 'contact_person_name', 'mobile_no', 'address'];
    requiredFields.forEach(function(field) {
        const input = $(`#${field}`);
        if (!input.val() || !input.val().trim()) {
            input.addClass('is-invalid');
            isValid = false;
        } else {
            input.removeClass('is-invalid');
        }
    });
    
    // Validate tea selection
    const selectedTeas = $('#tea_ids').val();
    if (!selectedTeas || selectedTeas.length === 0) {
        alert('Please select at least one tea variety.');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required fields and select tea varieties.');
    }
});

@if(isset($garden) && $garden->category_filters)
function loadExistingCategoryFilters() {
    const existingFilters = @json($garden->category_filters);
    
    // Clear initial empty category selection
    document.getElementById('category-selections-container').innerHTML = '';
    categoryIndex = 0;
    
    if (existingFilters && existingFilters.length > 0) {
        existingFilters.forEach(function(filter, index) {
            addCategorySelection();
            
            // Set category
            setTimeout(function() {
                document.getElementById(`category_${index}`).value = filter.category;
                loadTeaTypes(index);
                
                // Set tea types after a delay
                setTimeout(function() {
                    if (filter.tea_types) {
                        filter.tea_types.forEach(function(teaType) {
                            const checkbox = document.getElementById(`tea_type_${index}_${teaType}`);
                            if (checkbox) checkbox.checked = true;
                        });
                        loadGradeCodes(index);
                        
                        // Set grade codes after another delay
                        setTimeout(function() {
                            if (filter.grade_codes) {
                                    const checkbox = document.getElementById(`grade_code_${index}_${gradeCode}`);
                                    if (checkbox) checkbox.checked = true;
                                });
                            }
                            updateFinalTeaSelection();
                        }, 1000);
                    }, 500);
                }, 300);
            });
        });
    } else {
        // Add one empty category selection if no existing filters
        addCategorySelection();
    }
}
@endif
</script>
@endpush