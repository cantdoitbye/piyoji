@extends('admin.layouts.app')

@section('title', isset($tea) ? 'Edit Tea' : 'Add New Tea')
@section('subtitle', isset($tea) ? 'Update tea information' : 'Create a new tea variety')

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

                    <!-- Tea Grading System -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-leaf me-2"></i>Tea Grading Information
                            </h6>
                        </div>
                        
                        <!-- Step 1: Category Selection -->
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">Select Category</option>
                                @foreach(\App\Models\Tea::getCategoryOptions() as $key => $value)
                                    <option value="{{ $key }}" {{ old('category', $tea->category ?? '') == $key ? 'selected' : '' }}>
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Step 2: Tea Type Selection (Dependent on Category) -->
                        <div class="col-md-4 mb-3">
                            <label for="tea_type" class="form-label">Tea Type <span class="text-danger">*</span></label>
                            <select class="form-select @error('tea_type') is-invalid @enderror" 
                                    id="tea_type" name="tea_type" required disabled>
                                <option value="">First select category</option>
                            </select>
                            @error('tea_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Step 3: Grade Code Input (Free text with suggestions) -->
                        <div class="col-md-4 mb-3">
                            <label for="grade_code" class="form-label">Grade Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('grade_code') is-invalid @enderror" 
                                   id="grade_code" name="grade_code" 
                                   value="{{ old('grade_code', $tea->grade_code ?? '') }}" 
                                   placeholder="Enter grade code" required autocomplete="off">
                            @error('grade_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <span id="grade-suggestions">Select tea type to see common grade codes</span>
                            </div>
                            
                            <!-- Grade Code Suggestions -->
                            <div id="grade-suggestions-dropdown" class="dropdown-menu w-100" style="display: none;">
                                <!-- Suggestions will be populated here -->
                            </div>
                        </div>

                        <!-- Sub Title -->
                        <div class="col-md-6 mb-3">
                            <label for="sub_title" class="form-label">Sub Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('sub_title') is-invalid @enderror" 
                                   id="sub_title" name="sub_title" 
                                   value="{{ old('sub_title', $tea->sub_title ?? '') }}" 
                                   placeholder="e.g., Premium Quality, Estate Special" required>
                            @error('sub_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                @foreach(\App\Models\Tea::getStatusOptions() as $key => $label)
                                    <option value="{{ $key }}" {{ old('status', ($tea->status ?? true) ? '1' : '0') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-info-circle me-2"></i>Additional Information
                            </h6>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3" 
                                      placeholder="Describe the tea variety, its origin, processing method, etc.">{{ old('description', $tea->description ?? '') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="characteristics" class="form-label">Characteristics</label>
                            <textarea class="form-control @error('characteristics') is-invalid @enderror" 
                                      id="characteristics" name="characteristics" rows="3" 
                                      placeholder="Flavor profile, aroma, color, brewing notes, etc.">{{ old('characteristics', $tea->characteristics ?? '') }}</textarea>
                            @error('characteristics')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="2" 
                                      placeholder="Any additional notes or comments">{{ old('remarks', $tea->remarks ?? '') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="row mb-4" id="tea-preview" style="display: none;">
                        <div class="col-12">
                            <h6 class="text-success border-bottom pb-2 mb-3">
                                <i class="fas fa-eye me-2"></i>Tea Preview
                            </h6>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-success">
                                <strong>Full Name: </strong><span id="preview-full-name"></span><br>
                                <strong>Short Name: </strong><span id="preview-short-name"></span>
                            </div>
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
                                    {{ isset($tea) ? 'Update Tea' : 'Create Tea' }}
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
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Tea Grading Guide</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb me-2"></i>Tea Grading System:</h6>
                    <ol class="mb-0 small">
                        <li><strong>Category:</strong> Main tea category (Black, Green, White, etc.)</li>
                        <li><strong>Tea Type:</strong> Processing method within category</li>
                        <li><strong>Grade Code:</strong> Specific grade (free text, suggestions provided)</li>
                        <li><strong>Sub Title:</strong> Additional description or quality indicator</li>
                    </ol>
                </div>

                <div class="alert alert-warning">
                    <h6><i class="fas fa-code me-2"></i>Grade Code Examples:</h6>
                    <div class="small" id="grade-examples">
                        <p><strong>Orthodox:</strong> FTGFOP1, TGFOP, GFOP, FOP, OP</p>
                        <p><strong>CTC:</strong> BP, BOP, BOPF, OF, F, PF</p>
                        <p><strong>Dust:</strong> PD, D, D1, RD, CD, SRD</p>
                        <p class="mb-0">Select a tea type to see specific examples.</p>
                    </div>
                </div>

                @if(isset($tea))
                <div class="alert alert-success">
                    <h6><i class="fas fa-calendar me-2"></i>Tea Details:</h6>
                    <ul class="mb-0 small">
                        <li><strong>Created:</strong> {{ $tea->created_at->format('M d, Y') }}</li>
                        <li><strong>Last Updated:</strong> {{ $tea->updated_at->format('M d, Y') }}</li>
                        <li><strong>Status:</strong> {{ $tea->status_text }}</li>
                        <li><strong>Full Name:</strong> {{ $tea->full_name }}</li>
                    </ul>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.grade-suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}

.grade-suggestion-item:hover {
    background-color: #f8f9fa;
}

.grade-suggestion-item:last-child {
    border-bottom: none;
}

#grade-suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    max-height: 200px;
    overflow-y: auto;
    border: 1px solid #ccc;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-control:focus + #grade-suggestions-dropdown {
    display: block !important;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Category change handler
    $('#category').on('change', function() {
        const category = $(this).val();
        const teaTypeSelect = $('#tea_type');
        
        // Reset tea type and grade code
        teaTypeSelect.html('<option value="">Select Tea Type</option>').prop('disabled', !category);
        $('#grade_code').val('');
        hideGradeSuggestions();
        updatePreview();
        
        if (category) {
            // Fetch tea types for selected category
            $.ajax({
                url: '{{ route("admin.teas.tea-types-by-category") }}',
                method: 'GET',
                data: { category: category },
                success: function(response) {
                    $.each(response.tea_types, function(key, value) {
                        teaTypeSelect.append(`<option value="${key}">${value}</option>`);
                    });
                    teaTypeSelect.prop('disabled', false);
                    
                    // If editing, restore tea type value
                    @if(isset($tea))
                        teaTypeSelect.val('{{ $tea->tea_type }}').trigger('change');
                    @endif
                },
                error: function() {
                    alert('Error loading tea types. Please try again.');
                }
            });
        }
    });

    // Tea type change handler
    $('#tea_type').on('change', function() {
        const teaType = $(this).val();
        updateGradeSuggestions(teaType);
        updatePreview();
    });

    // Grade code input handlers
    $('#grade_code').on('input', function() {
        updatePreview();
    }).on('focus', function() {
        const teaType = $('#tea_type').val();
        if (teaType) {
            showGradeSuggestions();
        }
    }).on('blur', function() {
        // Delay hiding to allow clicking on suggestions
        setTimeout(hideGradeSuggestions, 200);
    });

    // Sub title change handler
    $('#sub_title').on('input', function() {
        updatePreview();
    });

    function updateGradeSuggestions(teaType) {
        if (!teaType) {
            $('#grade-suggestions').text('Select tea type to see common grade codes');
            return;
        }

        // Fetch common grade codes for selected tea type
        $.ajax({
            url: '{{ route("admin.teas.grade-codes-by-tea-type") }}',
            method: 'GET',
            data: { tea_type: teaType },
            success: function(response) {
                const suggestions = response.grade_codes || [];
                
                if (suggestions.length > 0) {
                    $('#grade-suggestions').html(`Common codes: ${suggestions.join(', ')}`);
                    populateGradeSuggestionsDropdown(suggestions);
                    updateGradeExamples(teaType, suggestions);
                } else {
                    $('#grade-suggestions').text('No common grade codes found for this tea type');
                }
            },
            error: function() {
                $('#grade-suggestions').text('Error loading grade codes');
            }
        });
    }

    function populateGradeSuggestionsDropdown(suggestions) {
        const dropdown = $('#grade-suggestions-dropdown');
        dropdown.empty();
        
        suggestions.forEach(function(code) {
            const item = $(`<div class="grade-suggestion-item" data-code="${code}">${code}</div>`);
            item.on('click', function() {
                $('#grade_code').val(code);
                hideGradeSuggestions();
                updatePreview();
            });
            dropdown.append(item);
        });
    }

    function showGradeSuggestions() {
        const dropdown = $('#grade-suggestions-dropdown');
        if (dropdown.children().length > 0) {
            dropdown.show();
        }
    }

    function hideGradeSuggestions() {
        $('#grade-suggestions-dropdown').hide();
    }

    function updateGradeExamples(teaType, suggestions) {
        const examplesDiv = $('#grade-examples');
        if (suggestions.length > 0) {
            examplesDiv.html(`<p><strong>${teaType}:</strong> ${suggestions.join(', ')}</p>`);
        }
    }

    function updatePreview() {
        const category = $('#category option:selected').text();
        const teaType = $('#tea_type option:selected').text();
        const gradeCode = $('#grade_code').val();
        const subTitle = $('#sub_title').val();

        if (category && category !== 'Select Category' && teaType && teaType !== 'Select Tea Type' && gradeCode) {
            const fullName = [category, teaType, gradeCode, subTitle].filter(Boolean).join(' - ');
            const shortName = [$('#category').val(), $('#tea_type').val(), gradeCode].filter(Boolean).join('-');
            
            $('#preview-full-name').text(fullName);
            $('#preview-short-name').text(shortName);
            $('#tea-preview').show();
        } else {
            $('#tea-preview').hide();
        }
    }

    // Initialize if editing
    @if(isset($tea))
        $('#category').trigger('change');
    @endif

    // Form validation
    $('#teaForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate required fields
        const requiredFields = ['category', 'tea_type', 'grade_code', 'sub_title'];
        requiredFields.forEach(function(field) {
            const input = $(`#${field}`);
            if (!input.val() || !input.val().trim()) {
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
});
</script>
@endpush