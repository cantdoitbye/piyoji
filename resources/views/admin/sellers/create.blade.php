@extends('admin.layouts.app')

@section('title', isset($seller) ? 'Edit Seller' : 'Add New Seller')
@section('subtitle', isset($seller) ? 'Update seller information' : 'Add a new tea seller to the system')

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
                                <i class="fas fa-building me-2"></i>Seller Information
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
                                Tea Estate Name <span class="text-danger">*</span>
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

                          <div class="col-md-6 mb-3">
    <label for="status" class="form-label">Type</label>
    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
        @php
            $selectedType = old('type', $seller->type ?? '');
        @endphp
        <option value="">-- Select Type --</option>
        <option value="group" {{ $selectedType === 'group' ? 'selected' : '' }}>Group</option>
        <option value="individual" {{ $selectedType === 'individual' ? 'selected' : '' }}>Individual</option>
    </select>
    @error('type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
                    </div>

                    <!-- Garden Selection - NEW SECTION -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-seedling me-2"></i>Associated Gardens
                            </h6>
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="garden_ids" class="form-label">
                                <i class="fas fa-leaf me-1"></i>Select Gardens <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('garden_ids') is-invalid @enderror" 
                                    id="garden_ids" name="garden_ids[]" multiple required>
                                @if(isset($gardens) && $gardens->count() > 0)
                                    @foreach($gardens as $garden)
                                        <option value="{{ $garden->id }}" 
                                                {{ in_array($garden->id, old('garden_ids', $seller->garden_ids ?? [])) ? 'selected' : '' }}>
                                            {{ $garden->garden_name }}
                                            @if($garden->state)
                                                - {{ $garden->state }}
                                            @endif
                                            @if($garden->speciality)
                                                ({{ $garden->speciality }})
                                            @endif
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No gardens available</option>
                                @endif
                            </select>
                            @error('garden_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('garden_ids.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Select one or more tea gardens that this seller owns or manages. This helps track the source of tea varieties.
                            </div>
                        </div>

                        <!-- Garden Information Display -->
                        <div class="col-12" id="selected-gardens-info" style="display: none;">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle me-2"></i>Selected Gardens Information</h6>
                                <div id="gardens-summary"></div>
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
                                Email <span class="text-danger">*</span>
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
                                Phone <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
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
                    </div>

                    <!-- Location Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-map-marker-alt me-2"></i>Location Information
                            </h6>
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
                            <select class="form-select @error('state') is-invalid @enderror" 
                                    id="state" name="state" required>
                                <option value="">Select State</option>
                                @foreach($states as $value => $label)
                                    <option value="{{ $value }}" 
                                            {{ old('state', $seller->state ?? '') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
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
                                <i class="fas fa-file-invoice me-2"></i>Business Information
                            </h6>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="gstin" class="form-label">
                                GSTIN <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control @error('gstin') is-invalid @enderror" 
                                   id="gstin" name="gstin" 
                                   value="{{ old('gstin', $seller->gstin ?? '') }}" 
                                   placeholder="e.g., 27AAAAA0000A1Z5" required>
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
                                   placeholder="e.g., ABCDE1234F" required>
                            @error('pan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Tea Grades Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Tea Grades Handled
                            </h6>
                        </div>
                        
                        <div class="col-12" id="teaGradeSelector">
                            <div class="d-flex align-items-center mb-3">
                                <i class="fas fa-tags text-success me-2"></i>
                                <strong>Tea Grades <span class="text-danger">*</span></strong>
                                <span class="ms-auto text-muted" id="selectionCount">0 selected</span>
                            </div>
                            
                            <!-- Search Box -->
                            <input type="text" class="form-control mb-3" placeholder="Search tea grades by code or name..." id="searchBox">
                            
                            <!-- Quick Actions -->
                            <div class="d-flex gap-2 mb-3">
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
                            <div class="row" id="teaGradeGrid">
                                @foreach($teaGrades as $key => $grade)
                                    <div class="col-md-3 mb-2">
                                        <div class="tea-grade-item form-check" data-grade="{{ $key }}">
                                            <input class="form-check-input" type="checkbox" 
                                                   id="grade_{{ $key }}" name="tea_grades[]" value="{{ $key }}"
                                                   {{ in_array($key, old('tea_grades', $seller->tea_grades ?? [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="grade_{{ $key }}">
                                                <strong>{{ $key }}</strong><br>
                                                <small class="text-muted">{{ $grade }}</small>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('tea_grades')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            
                            <!-- Selected Summary -->
                            <div id="selectionSummary" class="mt-3 d-none">
                                <div class="alert alert-success">
                                    <h6><i class="fas fa-check-circle me-2"></i>Selected Tea Grades</h6>
                                    <div id="selectedTags"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
    <div class="col-12">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-seedling me-2"></i>Associated Gardens
        </h6>
    </div>
    
    <div class="col-12 mb-3">
        <label for="garden_ids" class="form-label">
            Select Gardens <span class="text-muted">(Optional)</span>
        </label>
        <select class="form-select @error('garden_ids') is-invalid @enderror" 
                id="garden_ids" name="garden_ids[]" multiple>
            @foreach($gardens as $garden)
                <option value="{{ $garden->id }}" 
                        {{ in_array($garden->id, old('garden_ids', $seller->garden_ids ?? [])) ? 'selected' : '' }}>
                    {{ $garden->garden_name }} 
                    @if($garden->state)
                        - {{ $garden->state }}
                    @endif
                    @if($garden->contact_person_name)
                        ({{ $garden->contact_person_name }})
                    @endif
                </option>
            @endforeach
        </select>
        @error('garden_ids')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        @error('garden_ids.*')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            <i class="fas fa-info-circle me-1"></i>
            Select gardens that this seller is associated with. Hold Ctrl (or Cmd) to select multiple gardens.
        </div>
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
                                      placeholder="Any additional notes about this seller">{{ old('remarks', $seller->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
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

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Help Information -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Seller Information</h6>
            </div>
            <div class="card-body">
                <p class="small mb-3">
                    Register tea sellers/producers with their garden associations and business details 
                    to track tea sources and manage trading relationships.
                </p>

                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h6 class="text-primary">Required Information</h6>
                        <ul class="mb-0 small">
                            <li><strong>Seller Name:</strong> Official business name</li>
                            <li><strong>Tea Estate:</strong> Primary estate/garden name</li>
                            <li><strong>Gardens:</strong> Associated tea gardens</li>
                            <li><strong>Contact Details:</strong> Email, phone, address</li>
                            <li><strong>Business Info:</strong> GSTIN, PAN for compliance</li>
                            <li><strong>Tea Grades:</strong> Types of tea they can supply</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Garden Summary Card -->
        <div class="card" id="garden-summary-card" style="display: none;">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-seedling me-2"></i>Garden Summary</h6>
            </div>
            <div class="card-body" id="garden-summary-content">
                <!-- Dynamic content will be populated here -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
.select2-selection.is-invalid {
    border-color: #dc3545 !important;
}

.tea-grade-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.75rem;
    transition: all 0.2s ease;
    cursor: pointer;
}

.tea-grade-item:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.tea-grade-item.form-check input:checked + label {
    color: #0d6efd;
    font-weight: 600;
}

.tea-grade-item input:checked ~ label {
    color: #0d6efd;
}

.selected-grade-tag {
    display: inline-block;
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    margin: 0.125rem;
    border: 1px solid #bbdefb;
}

#garden_ids {
    min-height: 120px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for garden selection
   

    $('#garden_ids').select2({
    theme: 'bootstrap-5',
    width: '100%',
    placeholder: 'Select associated gardens...',
    allowClear: true,
    closeOnSelect: false
});

// UPDATE form validation to include garden validation:
$('#garden_ids').on('change', function() {
    const selectedGardens = $(this).val();
    if (selectedGardens && selectedGardens.length > 0) {
        $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
    }
});

    // Garden selection change handler
    $('#garden_ids').on('change', function() {
        updateGardenSummary();
    });

    // Tea grades functionality
    let selectedGrades = @json(old('tea_grades', $seller->tea_grades ?? []));
    let teaGrades = @json($teaGrades);
    let commonGrades = ['BP', 'BOP', 'PD', 'Dust', 'FTGFOP', 'TGFOP']; // Define common grades

    initializeTeaGradeSelector();

    function initializeTeaGradeSelector() {
        updateUI();
        
        // Initialize search functionality
        $('#searchBox').on('input', filterGrades);
        
        // Handle checkbox changes
        $('input[name="tea_grades[]"]').on('change', function() {
            updateSelectedGrades();
        });
    }

    function updateSelectedGrades() {
        selectedGrades = [];
        $('input[name="tea_grades[]"]:checked').each(function() {
            selectedGrades.push($(this).val());
        });
        updateUI();
    }

    function selectAll() {
        $('input[name="tea_grades[]"]').prop('checked', true);
        updateSelectedGrades();
    }

    function clearAll() {
        $('input[name="tea_grades[]"]').prop('checked', false);
        updateSelectedGrades();
    }

    function selectCommon() {
        $('input[name="tea_grades[]"]').prop('checked', false);
        commonGrades.forEach(function(grade) {
            $(`input[name="tea_grades[]"][value="${grade}"]`).prop('checked', true);
        });
        updateSelectedGrades();
    }

    function updateUI() {
        // Update count
        $('#selectionCount').text(`${selectedGrades.length} selected`);
        
        // Update summary
        if (selectedGrades.length > 0) {
            $('#selectionSummary').removeClass('d-none');
            let tags = selectedGrades.map(grade => 
                `<span class="selected-grade-tag">${grade} - ${teaGrades[grade] || grade}</span>`
            ).join('');
            $('#selectedTags').html(tags);
        } else {
            $('#selectionSummary').addClass('d-none');
        }
    }

    function filterGrades() {
        const searchTerm = $('#searchBox').val().toLowerCase();
        $('.tea-grade-item').each(function() {
            const grade = $(this).data('grade');
            const name = teaGrades[grade] || '';
            const matches = grade.toLowerCase().includes(searchTerm) || 
                           name.toLowerCase().includes(searchTerm);
            $(this).parent().toggle(matches);
        });
    }

    function updateGardenSummary() {
        const selectedGardens = $('#garden_ids').val();
        
        if (selectedGardens && selectedGardens.length > 0) {
            // Show garden info
            $('#selected-gardens-info').show();
            $('#garden-summary-card').show();
            
            // Create summary
            let summary = `<strong>${selectedGardens.length}</strong> garden(s) selected:<br>`;
            selectedGardens.forEach(function(gardenId) {
                const gardenText = $(`#garden_ids option[value="${gardenId}"]`).text();
                summary += `<small class="text-muted">• ${gardenText}</small><br>`;
            });
            
            $('#gardens-summary').html(summary);
            $('#garden-summary-content').html(summary);
        } else {
            $('#selected-gardens-info').hide();
            $('#garden-summary-card').hide();
        }
    }

    // Form validation
    $('#sellerForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        const requiredFields = ['seller_name', 'tea_estate_name', 'contact_person', 'email', 'phone', 'address', 'city', 'state', 'pincode', 'gstin', 'pan'];
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val() || !input.val().trim()) {
                input.addClass('is-invalid');
                isValid = false;
            } else {
                input.removeClass('is-invalid');
            }
        });
        
        // Check garden selection
        const selectedGardens = $('#garden_ids').val();
        if (!selectedGardens || selectedGardens.length === 0) {
            $('#garden_ids').next('.select2-container').find('.select2-selection').addClass('is-invalid');
            alert('Please select at least one garden.');
            isValid = false;
        } else {
            $('#garden_ids').next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }
        
        // Check tea grades selection
        const selectedTeaGrades = $('input[name="tea_grades[]"]:checked').length;
        if (selectedTeaGrades === 0) {
            $('#teaGradeSelector').addClass('border border-danger rounded p-2');
            alert('Please select at least one tea grade.');
            isValid = false;
        } else {
            $('#teaGradeSelector').removeClass('border border-danger rounded p-2');
        }
        
        // Email validation
        const email = $('#email').val();
        if (email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $('#email').addClass('is-invalid');
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Real-time validation
    // $('.form-control, .form-select').on('input change', function() {
    //     if ($(this).val() && $(this).val().trim()) {
    //         $(this).removeClass('is-invalid');
    //     }
    // });
    
    // Select2 validation for gardens
    $('#garden_ids').on('change', function() {
        const selectedGardens = $(this).val();
        if (selectedGardens && selectedGardens.length > 0) {
            $(this).next('.select2-container').find('.select2-selection').removeClass('is-invalid');
        }
    });

    // Initialize garden summary if editing
    @if(isset($seller) && $seller->garden_ids)
        updateGardenSummary();
    @endif

    // Make functions global for button onclick
    window.selectAll = selectAll;
    window.clearAll = clearAll;
    window.selectCommon = selectCommon;
});
</script>
@endpush