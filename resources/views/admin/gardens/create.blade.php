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
            <i class="fas fa-seedling me-2"></i>Garden Information
        </h6>
    </div>
    
    <div class="col-md-6 mb-3">
        <label for="garden_name" class="form-label">
            Garden Name <span class="text-danger">*</span>
        </label>
        <input type="text" class="form-control @error('garden_name') is-invalid @enderror" 
               id="garden_name" name="garden_name" 
               value="{{ old('garden_name', $garden->garden_name ?? '') }}" required>
        @error('garden_name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="col-md-6 mb-3">
        <label for="garden_type" class="form-label">
            Garden Type <span class="text-danger">*</span>
        </label>
        <select class="form-select @error('garden_type') is-invalid @enderror" 
                id="garden_type" name="garden_type" required>
            <option value="">Select Garden Type</option>
            @foreach(\App\Models\Garden::getGardenTypeOptions() as $key => $label)
                <option value="{{ $key }}" 
                        {{ old('garden_type', $garden->garden_type ?? '') == $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('garden_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">
            <i class="fas fa-info-circle me-1"></i>
            Select whether this is a Garden or Mark type
        </div>
    </div>
</div>

<!-- Location Information with Map -->
<div class="row mb-4">
    <div class="col-12">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-map-marker-alt me-2"></i>Location Information
        </h6>
    </div>
    
    <div class="col-12 mb-3">
        <label for="address" class="form-label">
            Address <span class="text-danger">*</span>
        </label>
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
        <input type="text" class="form-control @error('state') is-invalid @enderror" 
               id="state" name="state" 
               value="{{ old('state', $garden->state ?? '') }}">
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
    
    <!-- Map Location Picker -->
    <div class="col-12 mb-3">
        <label class="form-label">
            <i class="fas fa-map-marked-alt me-2"></i>Select Location on Map
        </label>
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Click on the map to set location</h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-primary" onclick="getCurrentLocation()">
                            <i class="fas fa-crosshairs me-1"></i>Use Current Location
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="clearLocation()">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-6">
                        <label for="latitude" class="form-label">Latitude</label>
                        <input type="number" step="any" class="form-control @error('latitude') is-invalid @enderror" 
                               id="latitude" name="latitude" 
                               value="{{ old('latitude', $garden->latitude ?? '') }}" 
                               readonly>
                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="longitude" class="form-label">Longitude</label>
                        <input type="number" step="any" class="form-control @error('longitude') is-invalid @enderror" 
                               id="longitude" name="longitude" 
                               value="{{ old('longitude', $garden->longitude ?? '') }}" 
                               readonly>
                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="form-text mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Click on the map to set coordinates, or use the "Use Current Location" button
                </div>
            </div>
        </div>
    </div>
</div>

                 
<!-- Acceptable Invoice Types -->
<div class="row mb-4">
    <div class="col-12">
        <h6 class="text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-file-invoice me-2"></i>Acceptable Invoice Types & Variables
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
                               onchange="toggleVariableSection('{{ $key }}')"
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
            Select the types of invoices this garden can accept. You can then specify variables for each selected type.
        </div>
    </div>

    <!-- Variables Selection Sections -->
    @foreach(\App\Models\Garden::getInvoiceTypesOptions() as $key => $label)
        <div class="col-12 mb-4" id="variables_section_{{ $key }}" style="display: none;">
            <div class="card border-info">
                <div class="card-header bg-light">
                    <h6 class="mb-0 text-info">
                        <i class="fas fa-tags me-2"></i>{{ $label }} Variables
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Predefined Variables</label>
                            <div class="variables-checkboxes" data-type="{{ $key }}">
                                @foreach(\App\Models\Garden::getInvoiceTypeVariables()[$key] ?? [] as $variable)
                                    <div class="form-check form-check-inline mb-2">
                                        <input class="form-check-input variable-checkbox" 
                                               type="checkbox" 
                                               id="var_{{ $key }}_{{ $variable }}" 
                                               value="{{ $variable }}"
                                               data-type="{{ $key }}"
                                               onchange="updateVariablesInput('{{ $key }}')"
                                               {{ in_array($variable, old("invoice_type_variables.{$key}", $garden->invoice_type_variables[$key] ?? [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="var_{{ $key }}_{{ $variable }}">
                                            {{ $variable }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="custom_variables_{{ $key }}" class="form-label">Custom Variables</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="custom_variables_{{ $key }}"
                                   placeholder="Enter custom variables separated by commas"
                                   onblur="addCustomVariables('{{ $key }}')"
                                   value="">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Add custom variables separated by commas (e.g., VAR1, VAR2, VAR3)
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Selected Variables for {{ $label }}</label>
                            <div class="selected-variables-display" id="selected_vars_{{ $key }}">
                                <div class="alert alert-light mb-0">
                                    <small class="text-muted">No variables selected</small>
                                </div>
                            </div>
                            <!-- Hidden input to store the actual variables -->
                            <input type="hidden" 
                                   name="invoice_type_variables[{{ $key }}][]" 
                                   id="variables_input_{{ $key }}"
                                   value="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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
                                            id="tea_ids" name="tea_ids[]" multiple>
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
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// Global variables
let categoryIndex = 0;
let allSelectedFilters = [];
let map;
let marker;

$(document).ready(function() {
    // Initialize Select2 components
    initializeSelect2();
    
    // Initialize OpenStreetMap
    initializeMap();
    
    // Initialize Invoice Types sections
    initializeInvoiceTypes();
    
    // Add initial category selection for tea varieties
    addCategorySelection();

    // Load existing data if editing
    @if(isset($garden))
        loadExistingData();
    @endif
    
    // Initialize auto-save
    initializeAutoSave();
    
    // Add keyboard shortcuts
    initializeKeyboardShortcuts();
    
    // Handle responsive layout
    handleResponsiveLayout();
    
    // Initialize form validation
    initializeFormValidation();
});

// ============================================================================
// SELECT2 INITIALIZATION
// ============================================================================
function initializeSelect2() {
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
}

// ============================================================================
// OPENSTREETMAP INTEGRATION
// ============================================================================
function initializeMap() {
    // Get existing coordinates or use default
    const existingLat = {{ old('latitude', $garden->latitude ?? 'null') }};
    const existingLng = {{ old('longitude', $garden->longitude ?? 'null') }};
    
    // Default coordinates (India center)
    const defaultLat = existingLat || 20.5937;
    const defaultLng = existingLng || 78.9629;
    const defaultZoom = (existingLat && existingLng) ? 13 : 6;
    
    // Initialize map
    map = L.map('map').setView([defaultLat, defaultLng], defaultZoom);
    
    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);
    
    // Add marker if coordinates exist
    if (existingLat && existingLng) {
        marker = L.marker([existingLat, existingLng], {
            draggable: true
        }).addTo(map);
        
        // Update coordinates when marker is dragged
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
    }
    
    // Add click event to map
    map.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        
        // Remove existing marker
        if (marker) {
            map.removeLayer(marker);
        }
        
        // Add new marker
        marker = L.marker([lat, lng], {
            draggable: true
        }).addTo(map);
        
        // Update coordinates
        updateCoordinates(lat, lng);
        
        // Update marker drag event
        marker.on('dragend', function(e) {
            const position = e.target.getLatLng();
            updateCoordinates(position.lat, position.lng);
        });
    });
}

function updateCoordinates(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(8);
    document.getElementById('longitude').value = lng.toFixed(8);
    
    // Show success message
    showToast('Location updated successfully!', 'success');
    
    // Trigger auto-save
    autoSaveData();
}

function getCurrentLocation() {
    if (navigator.geolocation) {
        showToast('Getting your current location...', 'info');
        
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                // Remove existing marker
                if (marker) {
                    map.removeLayer(marker);
                }
                
                // Add new marker
                marker = L.marker([lat, lng], {
                    draggable: true
                }).addTo(map);
                
                // Update map view
                map.setView([lat, lng], 15);
                
                // Update coordinates
                updateCoordinates(lat, lng);
                
                // Update marker drag event
                marker.on('dragend', function(e) {
                    const position = e.target.getLatLng();
                    updateCoordinates(position.lat, position.lng);
                });
                
                showToast('Current location set successfully!', 'success');
            },
            function(error) {
                let errorMessage = 'Unable to retrieve your location';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorMessage = 'Location access denied by user';
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorMessage = 'Location information unavailable';
                        break;
                    case error.TIMEOUT:
                        errorMessage = 'Location request timed out';
                        break;
                }
                showToast(errorMessage, 'error');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 60000
            }
        );
    } else {
        showToast('Geolocation is not supported by this browser', 'error');
    }
}

function clearLocation() {
    // Remove marker
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
    
    // Clear coordinates
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
    
    // Reset map view to default
    map.setView([20.5937, 78.9629], 6);
    
    showToast('Location cleared successfully!', 'success');
    
    // Trigger auto-save
    autoSaveData();
}

// ============================================================================
// INVOICE TYPES FUNCTIONALITY
// ============================================================================
function initializeInvoiceTypes() {
    // Show variable sections for already selected invoice types
    @if(isset($garden) && $garden->acceptable_invoice_types)
        @foreach($garden->acceptable_invoice_types as $type)
            toggleVariableSection('{{ $type }}');
            updateVariablesInput('{{ $type }}');
        @endforeach
    @endif
    
    // Handle old input for new forms
    @if(old('acceptable_invoice_types'))
        @foreach(old('acceptable_invoice_types') as $type)
            toggleVariableSection('{{ $type }}');
            updateVariablesInput('{{ $type }}');
        @endforeach
    @endif
}

function toggleVariableSection(type) {
    const checkbox = document.getElementById('invoice_type_' + type);
    const section = document.getElementById('variables_section_' + type);
    
    if (checkbox && checkbox.checked) {
        section.style.display = 'block';
        section.style.animation = 'slideDown 0.3s ease-out';
    } else {
        section.style.display = 'none';
        // Clear all selected variables for this type
        clearVariablesForType(type);
    }
    
    // Trigger auto-save
    autoSaveData();
}

function updateVariablesInput(type) {
    const checkboxes = document.querySelectorAll(`.variable-checkbox[data-type="${type}"]:checked`);
    let selectedVars = Array.from(checkboxes).map(cb => cb.value);
    
    // Also get any existing hidden inputs (for custom variables)
    const hiddenInputs = document.querySelectorAll(`input[name="invoice_type_variables[${type}][]"]`);
    const hiddenVars = Array.from(hiddenInputs).map(input => input.value);
    
    // Combine and remove duplicates
    selectedVars = [...new Set([...selectedVars, ...hiddenVars])];
    
    // Update display
    updateVariablesDisplay(type, selectedVars);
    
    // Update hidden inputs
    updateHiddenInputs(type, selectedVars);
    
    // Trigger auto-save
    autoSaveData();
}

function updateVariablesDisplay(type, variables) {
    const displayDiv = document.getElementById('selected_vars_' + type);
    
    if (variables.length > 0) {
        let html = '<div class="d-flex flex-wrap gap-1">';
        variables.forEach(variable => {
            html += `<span class="badge bg-primary me-1 mb-1">
                        ${variable}
                        <button type="button" class="btn-close btn-close-white ms-1" 
                                onclick="removeVariable('${type}', '${variable}')" 
                                style="font-size: 0.6rem;"></button>
                     </span>`;
        });
        html += '</div>';
        displayDiv.innerHTML = html;
    } else {
        displayDiv.innerHTML = '<div class="alert alert-light mb-0"><small class="text-muted">No variables selected</small></div>';
    }
}

function updateHiddenInputs(type, variables) {
    // Remove existing hidden inputs for this type
    const existingInputs = document.querySelectorAll(`input[name="invoice_type_variables[${type}][]"]`);
    existingInputs.forEach(input => input.remove());
    
    // Add new hidden inputs
    const container = document.getElementById('variables_section_' + type);
    variables.forEach(variable => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `invoice_type_variables[${type}][]`;
        input.value = variable;
        container.appendChild(input);
    });
}

function addCustomVariables(type) {
    const customInput = document.getElementById('custom_variables_' + type);
    const customVars = customInput.value.split(',').map(v => v.trim()).filter(v => v);
    
    if (customVars.length > 0) {
        // Get currently selected variables
        const hiddenInputs = document.querySelectorAll(`input[name="invoice_type_variables[${type}][]"]`);
        const selectedVars = Array.from(hiddenInputs).map(input => input.value);
        
        // Add custom variables to selected list (avoid duplicates)
        customVars.forEach(customVar => {
            if (!selectedVars.includes(customVar)) {
                selectedVars.push(customVar);
            }
        });
        
        // Update display and hidden inputs
        updateVariablesDisplay(type, selectedVars);
        updateHiddenInputs(type, selectedVars);
        
        // Clear the custom input
        customInput.value = '';
        
        // Show success message
        showToast('Custom variables added successfully!', 'success');
        
        // Trigger auto-save
        autoSaveData();
    }
}

function removeVariable(type, variableToRemove) {
    // Uncheck the checkbox if it exists
    const checkbox = document.getElementById(`var_${type}_${variableToRemove}`);
    if (checkbox) {
        checkbox.checked = false;
    }
    
    // Get remaining selected variables
    const hiddenInputs = document.querySelectorAll(`input[name="invoice_type_variables[${type}][]"]`);
    const allVars = Array.from(hiddenInputs).map(input => input.value);
    
    // Remove the specific variable
    const selectedVars = allVars.filter(v => v !== variableToRemove);
    
    // Update display and hidden inputs
    updateVariablesDisplay(type, selectedVars);
    updateHiddenInputs(type, selectedVars);
    
    // Trigger auto-save
    autoSaveData();
}

function clearVariablesForType(type) {
    // Uncheck all checkboxes for this type
    const checkboxes = document.querySelectorAll(`.variable-checkbox[data-type="${type}"]`);
    checkboxes.forEach(cb => cb.checked = false);
    
    // Clear custom input
    const customInput = document.getElementById('custom_variables_' + type);
    if (customInput) {
        customInput.value = '';
    }
    
    // Clear display and hidden inputs
    updateVariablesDisplay(type, []);
    updateHiddenInputs(type, []);
}

// ============================================================================
// TEA VARIETIES SELECTION SYSTEM
// ============================================================================
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
    autoSaveData();
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
            showToast('Error loading tea varieties. Please try again.', 'error');
        }
    });
}

function updateSelectionSummary() {
    const summaryCard = document.getElementById('selection-summary-card');
    const summaryContent = document.getElementById('selection-summary-content');
    
    if (!summaryCard || !summaryContent) return;
    
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

// ============================================================================
// LOAD EXISTING DATA FOR EDIT FORM
// ============================================================================
@if(isset($garden))
function loadExistingData() {
    // Load existing category filters
    @if($garden->category_filters)
        loadExistingCategoryFilters();
    @endif
    
    // Load existing invoice type variables
    @if($garden->invoice_type_variables)
        loadExistingInvoiceVariables();
    @endif
}

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
                                filter.grade_codes.forEach(function(gradeCode) {
                                    const checkbox = document.getElementById(`grade_code_${index}_${gradeCode}`);
                                    if (checkbox) checkbox.checked = true;
                                });
                            }
                            updateFinalTeaSelection();
                        }, 1000);
                    }
                }, 500);
            }, 300);
        });
    } else {
        // Add one empty category selection if no existing filters
        addCategorySelection();
    }
}

function loadExistingInvoiceVariables() {
    const existingVariables = @json($garden->invoice_type_variables);
    
    if (existingVariables) {
        Object.keys(existingVariables).forEach(function(type) {
            const variables = existingVariables[type];
            if (variables && variables.length > 0) {
                // Check predefined checkboxes
                variables.forEach(function(variable) {
                    const checkbox = document.getElementById(`var_${type}_${variable}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                
                // Update the display and hidden inputs
                updateVariablesInput(type);
            }
        });
    }
}
@endif

// ============================================================================
// AUTO-SAVE FUNCTIONALITY
// ============================================================================
let autoSaveTimeout;

function initializeAutoSave() {
    // Add auto-save event listeners
    $(document).on('input change', '#garden_name, #garden_type, #latitude, #longitude, #contact_person_name, #mobile_no, #email, #address, #city, #state, #pincode', autoSaveData);
    $(document).on('change', 'input[name="acceptable_invoice_types[]"]', autoSaveData);
    
    // Load draft data on page load (only for new gardens)
    @if(!isset($garden))
        loadDraftData();
    @endif
}

function autoSaveData() {
    // Clear existing timeout
    if (autoSaveTimeout) {
        clearTimeout(autoSaveTimeout);
    }
    
    // Set new timeout for auto-save
    autoSaveTimeout = setTimeout(() => {
        const formData = {
            garden_name: $('#garden_name').val(),
            garden_type: $('#garden_type').val(),
            contact_person_name: $('#contact_person_name').val(),
            mobile_no: $('#mobile_no').val(),
            email: $('#email').val(),
            address: $('#address').val(),
            city: $('#city').val(),
            state: $('#state').val(),
            pincode: $('#pincode').val(),
            latitude: $('#latitude').val(),
            longitude: $('#longitude').val(),
            acceptable_invoice_types: $('input[name="acceptable_invoice_types[]"]:checked').map(function() {
                return this.value;
            }).get(),
            timestamp: new Date().toISOString()
        };
        
        // Save to localStorage for recovery
        localStorage.setItem('garden_form_draft', JSON.stringify(formData));
        
        // Show auto-save indicator
        showAutoSaveIndicator();
    }, 2000); // Auto-save after 2 seconds of inactivity
}

function showAutoSaveIndicator() {
    const indicator = document.createElement('div');
    indicator.className = 'alert alert-info position-fixed';
    indicator.style.cssText = `
        bottom: 20px; 
        right: 20px; 
        z-index: 9999; 
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        animation: fadeInOut 2s ease-out;
    `;
    indicator.innerHTML = '<i class="fas fa-save me-1"></i>Draft saved';
    
    document.body.appendChild(indicator);
    
    setTimeout(() => {
        if (indicator.parentElement) {
            indicator.remove();
        }
    }, 2000);
}

function loadDraftData() {
    const draftData = localStorage.getItem('garden_form_draft');
    if (draftData) {
        try {
            const data = JSON.parse(draftData);
            
            // Check if draft is recent (within 24 hours)
            const draftAge = new Date() - new Date(data.timestamp);
            const maxAge = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
            
            if (draftAge > maxAge) {
                localStorage.removeItem('garden_form_draft');
                return;
            }
            
            // Show restore option
            const restoreAlert = document.createElement('div');
            restoreAlert.className = 'alert alert-warning alert-dismissible';
            restoreAlert.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <div class="flex-grow-1">
                        Draft data found from ${new Date(data.timestamp).toLocaleString()}. Would you like to restore your previous work?
                    </div>
                    <div class="btn-group ms-2">
                        <button type="button" class="btn btn-sm btn-warning" onclick="restoreDraftData()">
                            <i class="fas fa-undo me-1"></i>Restore
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearDraftData()">
                            <i class="fas fa-times me-1"></i>Discard
                        </button>
                    </div>
                </div>
            `;
            
            // Insert at top of form
            const form = document.getElementById('gardenForm');
            if (form) {
                form.insertBefore(restoreAlert, form.firstChild);
            }
            
        } catch (e) {
            // Invalid draft data, clear it
            localStorage.removeItem('garden_form_draft');
        }
    }
}

function restoreDraftData() {
    try {
        const data = JSON.parse(localStorage.getItem('garden_form_draft'));
        
        if (data.garden_name) $('#garden_name').val(data.garden_name);
        if (data.garden_type) $('#garden_type').val(data.garden_type);
        if (data.contact_person_name) $('#contact_person_name').val(data.contact_person_name);
        if (data.mobile_no) $('#mobile_no').val(data.mobile_no);
        if (data.email) $('#email').val(data.email);
        if (data.address) $('#address').val(data.address);
        if (data.city) $('#city').val(data.city);
        if (data.state) $('#state').val(data.state);
        if (data.pincode) $('#pincode').val(data.pincode);
        if (data.latitude) $('#latitude').val(data.latitude);
        if (data.longitude) $('#longitude').val(data.longitude);
        
        if (data.acceptable_invoice_types) {
            data.acceptable_invoice_types.forEach(type => {
                const checkbox = document.getElementById('invoice_type_' + type);
                if (checkbox) {
                    checkbox.checked = true;
                    toggleVariableSection(type);
                }
            });
        }
        
        // Update map if coordinates exist
        if (data.latitude && data.longitude) {
            const lat = parseFloat(data.latitude);
            const lng = parseFloat(data.longitude);
            
            if (marker) {
                map.removeLayer(marker);
            }
            
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            map.setView([lat, lng], 13);
            
            marker.on('dragend', function(e) {
                const position = e.target.getLatLng();
                updateCoordinates(position.lat, position.lng);
            });
        }
        
        // Remove restore alert
        document.querySelector('.alert-warning').remove();
        
        showToast('Draft data restored successfully!', 'success');
        
    } catch (e) {
        showToast('Error restoring draft data', 'error');
    }
}

function clearDraftData() {
    localStorage.removeItem('garden_form_draft');
    document.querySelector('.alert-warning').remove();
    showToast('Draft data cleared', 'info');
}

// ============================================================================
// KEYBOARD SHORTCUTS
// ============================================================================
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl + L to get current location
        if (e.ctrlKey && e.key === 'l') {
            e.preventDefault();
            getCurrentLocation();
        }
        
        // Ctrl + M to clear location
        if (e.ctrlKey && e.key === 'm') {
            e.preventDefault();
            clearLocation();
        }
        
        // Ctrl + T to add new tea category
        if (e.ctrlKey && e.key === 't') {
            e.preventDefault();
            addCategorySelection();
        }
        
        // Ctrl + S to save (trigger auto-save)
        if (e.ctrlKey && e.key === 's') {
            e.preventDefault();
            autoSaveData();
            showToast('Form data saved!', 'success');
        }
    });
}

// ============================================================================
// RESPONSIVE HANDLING
// ============================================================================
function handleResponsiveLayout() {
    // Adjust map height on mobile
    if (window.innerWidth < 768) {
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.style.height = '300px';
        }
    }
    
    // Refresh map on resize
    if (map) {
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
    }
}

// Listen for window resize
window.addEventListener('resize', handleResponsiveLayout);

// ============================================================================
// FORM VALIDATION
// ============================================================================
function initializeFormValidation() {
    // Real-time validation
    $('#garden_name, #garden_type, #contact_person_name, #mobile_no, #address').on('blur', function() {
        validateField(this);
    });
    
    // Form submit validation
    $('#gardenForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate required fields
        const requiredFields = ['garden_name', 'garden_type', 'address'];
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
        // const selectedTeas = $('#tea_ids').val();
        // if (!selectedTeas || selectedTeas.length === 0) {
        //     showToast('Please select at least one tea variety.', 'error');
        //     isValid = false;
        // }
        
        // Validate coordinates if provided
        const latitude = $('#latitude').val();
        const longitude = $('#longitude').val();
        
        if (latitude && (latitude < -90 || latitude > 90)) {
            $('#latitude').addClass('is-invalid');
            isValid = false;
            showToast('Latitude must be between -90 and 90 degrees.', 'error');
        }
        
        if (longitude && (longitude < -180 || longitude > 180)) {
            $('#longitude').addClass('is-invalid');
            isValid = false;
            showToast('Longitude must be between -180 and 180 degrees.', 'error');
        }
        
        if (!isValid) {
            e.preventDefault();
            showToast('Please fill in all required fields correctly.', 'error');
            
            // Scroll to first invalid field
            const firstInvalid = $('.is-invalid:first');
            if (firstInvalid.length) {
                $('html, body').animate({
                    scrollTop: firstInvalid.offset().top - 100
                }, 500);
            }
        } else {
            // Clear draft data on successful submission
            localStorage.removeItem('garden_form_draft');
            showToast('Form submitted successfully!', 'success');
        }
    });
}

function validateField(field) {
    const $field = $(field);
    const value = $field.val().trim();
    
    if ($field.prop('required') && !value) {
        $field.addClass('is-invalid');
        return false;
    } else {
        $field.removeClass('is-invalid');
        return true;
    }
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidMobile(mobile) {
    const mobileRegex = /^[\+]?[1-9]?[\d\s\-\(\)]{7,15}$/;
    return mobileRegex.test(mobile);
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    const iconClass = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-exclamation-circle' : 
                     'fa-info-circle';
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}

// ============================================================================
// PERFORMANCE OPTIMIZATION
// ============================================================================
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction() {
        const context = this;
        const args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

// Debounced functions for better performance
const debouncedAutoSave = debounce(autoSaveData, 1000);
const debouncedUpdateTeaSelection = debounce(updateFinalTeaSelection, 500);

// Replace direct calls with debounced versions where appropriate
$(document).on('input', '#garden_name, #contact_person_name, #address', debouncedAutoSave);

// ============================================================================
// ERROR HANDLING
// ============================================================================
window.addEventListener('error', function(e) {
    console.error('Garden form error:', e.error);
    showToast('An unexpected error occurred. Please refresh the page if issues persist.', 'error');
});

// Handle AJAX errors globally
$(document).ajaxError(function(event, xhr, settings, thrownError) {
    console.error('AJAX Error:', xhr.status, thrownError);
    
    if (xhr.status === 419) {
        showToast('Session expired. Please refresh the page and try again.', 'error');
    } else if (xhr.status === 500) {
        showToast('Server error occurred. Please try again later.', 'error');
    } else {
        showToast('Network error. Please check your connection and try again.', 'error');
    }
});

// ============================================================================
// CSS STYLES
// ============================================================================
const style = document.createElement('style');
style.textContent = `
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    @keyframes fadeInOut {
        0% { opacity: 0; transform: translateY(20px); }
        20% { opacity: 1; transform: translateY(0); }
        80% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; transform: translateY(-20px); }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    .variable-checkbox:checked + label {
        font-weight: 600;
        color: #0d6efd;
    }
    
    .selected-variables-display .badge {
        transition: all 0.2s ease;
    }
    
    .selected-variables-display .badge:hover {
        transform: scale(1.05);
    }
    
    .variables-checkboxes {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
    }
    
    .leaflet-container {
        font-family: inherit;
    }
    
    .category-selection-item {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 1rem;
        margin-bottom: 1rem;
        position: relative;
        animation: slideDown 0.3s ease-out;
    }
    
    .remove-category-btn {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 10;
    }
    
    .checkbox-group {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        padding: 0.75rem;
        background: white;
    }
    
    .no-results-message {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 1rem;
    }
    
    .tea-type-checkbox,
    .grade-code-checkbox {
        margin-bottom: 0.5rem;
    }
    
    .form-check-input:checked + .form-check-label {
        font-weight: 500;
        color: #0d6efd;
    }
    
    #map {
        cursor: crosshair;
        border-radius: 0.375rem;
    }
    
    .map-controls {
        margin-bottom: 0.5rem;
    }
    
    .coordinates-display {
        font-family: monospace;
        font-size: 0.875rem;
    }
    
    .invoice-types-section .card {
        transition: all 0.3s ease;
    }
    
    .invoice-types-section .card:hover {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .selection-summary-card {
        border-left: 4px solid #0d6efd;
    }
    
    .btn-group-sm > .btn {
        font-size: 0.75rem;
    }
    
    .alert-dismissible {
        animation: slideDown 0.3s ease-out;
    }
    
    .leaflet-marker-icon {
        transition: all 0.3s ease;
    }
    
    .leaflet-marker-icon:hover {
        transform: scale(1.1);
    }
    
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .form-control:focus,
    .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .is-invalid {
        animation: shake 0.5s ease-in-out;
    }
    
    .loading-spinner {
        display: inline-block;
        width: 1rem;
        height: 1rem;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    @media (max-width: 768px) {
        .remove-category-btn {
            position: static;
            margin-bottom: 1rem;
            width: 100%;
        }
        
        .checkbox-group {
            max-height: 150px;
        }
        
        #map {
            height: 300px !important;
        }
        
        .category-selection-item {
            padding: 0.75rem;
        }
        
        .btn-group-sm > .btn {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .variables-checkboxes {
            max-height: 120px;
        }
        
        .selected-variables-display .badge {
            font-size: 0.7rem;
        }
    }
`;
document.head.appendChild(style);

// ============================================================================
// CLEANUP AND FINALIZATION
// ============================================================================

// Export functions for global access if needed
window.gardenFormHelpers = {
    getCurrentLocation,
    clearLocation,
    addCategorySelection,
    removeCategorySelection,
    toggleVariableSection,
    addCustomVariables,
    removeVariable,
    showToast,
    updateCoordinates,
    autoSaveData,
    restoreDraftData,
    clearDraftData
};

// Initialize tooltips if Bootstrap is available
if (typeof bootstrap !== 'undefined') {
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
}

// Show loading indicator during AJAX requests
$(document).ajaxStart(function() {
    $('body').addClass('loading');
}).ajaxStop(function() {
    $('body').removeClass('loading');
});

// Console log for debugging
console.log('Garden form script initialized successfully');
console.log('Available shortcuts: Ctrl+L (current location), Ctrl+M (clear location), Ctrl+T (add tea category), Ctrl+S (save)');

// Cleanup on page unload
window.addEventListener('beforeunload', function(e) {
    // Auto-save before leaving if there are unsaved changes
    const hasUnsavedChanges = localStorage.getItem('garden_form_draft');
    if (hasUnsavedChanges) {
        autoSaveData();
    }
});

</script>
@endpush
