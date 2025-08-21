@extends('admin.layouts.app')

@section('title', 'Create Invoice - ' . $garden->garden_name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-primary mb-1">
                        <i class="fas fa-plus me-2"></i>Create New Invoice
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.gardens.index') }}">Gardens</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.gardens.invoices.index', $garden) }}">{{ $garden->garden_name }}</a></li>
                            <li class="breadcrumb-item active">Create Invoice</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('admin.gardens.invoices.index', $garden) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Back to Invoices
                </a>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.gardens.invoices.store', $garden) }}" method="POST" id="invoiceForm">
        @csrf
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Invoice Specific Details -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-file-invoice me-2"></i>Invoice Specific Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Mark Name -->
                            <div class="col-md-6 mb-3">
                                <label for="mark_name" class="form-label">
                                    Mark Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('mark_name') is-invalid @enderror" 
                                       id="mark_name" name="mark_name" 
                                       value="{{ old('mark_name', $garden->garden_name) }}" readonly>
                                <div class="form-text">Garden name at time of invoice creation</div>
                                @error('mark_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Invoice Prefix -->
                            <div class="col-md-6 mb-3">
                                <label for="invoice_prefix" class="form-label">
                                    Invoice Prefix <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('invoice_prefix') is-invalid @enderror" 
                                        id="invoice_prefix" name="invoice_prefix" required>
                                    <option value="">Select Prefix</option>
                                    @foreach($invoicePrefixes as $value => $label)
                                        <option value="{{ $value }}" {{ old('invoice_prefix') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Invoice number will be auto-generated</div>
                                @error('invoice_prefix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Bags/Packages -->
                            <div class="col-md-6 mb-3">
                                <label for="bags_packages" class="form-label">
                                    Bags / Packages <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('bags_packages') is-invalid @enderror" 
                                       id="bags_packages" name="bags_packages" 
                                       value="{{ old('bags_packages') }}" 
                                       min="0" required>
                                @error('bags_packages')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Packaging Date -->
                            <div class="col-md-6 mb-3">
                                <label for="packaging_date" class="form-label">
                                    Packaging Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control @error('packaging_date') is-invalid @enderror" 
                                       id="packaging_date" name="packaging_date" 
                                       value="{{ old('packaging_date', date('Y-m-d')) }}" required>
                                @error('packaging_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">
                                    Invoice Notes <small class="text-muted">(Optional)</small>
                                </label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="2" 
                                          maxlength="1000">{{ old('notes') }}</textarea>
                                <div class="form-text">General notes for this invoice</div>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sample Details Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="fas fa-vial me-2"></i>Sample Details
                            </h6>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSample()">
                                <i class="fas fa-plus me-1"></i>Add Sample
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="samples-container">
                            <!-- Samples will be added here dynamically -->
                        </div>
                        
                        <!-- Total Weight Display -->
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <div class="row text-center">
                                        <div class="col-4">
                                            <div class="h6 mb-1" id="total-samples">0</div>
                                            <small>Total Samples</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h6 mb-1" id="total-sets">0</div>
                                            <small>Total Sets</small>
                                        </div>
                                        <div class="col-4">
                                            <div class="h6 mb-1 text-primary" id="total-weight">0.000 kg</div>
                                            <small>Total Weight</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Garden Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-seedling me-2"></i>Garden Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Garden Name:</strong><br>
                            <span class="text-primary">{{ $garden->garden_name }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Garden Type:</strong><br>
                            <span class="badge bg-{{ $garden->garden_type == 'garden' ? 'success' : 'info' }}">
                                {{ $garden->garden_type_text }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <strong>Address:</strong><br>
                            <small class="text-muted">{{ $garden->full_address }}</small>
                        </div>
                        @if($garden->has_location)
                            <div class="mb-3">
                                <strong>Location:</strong><br>
                                <small class="text-muted">{{ $garden->formatted_location }}</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" id="submit-btn">
                                <i class="fas fa-save me-1"></i>Create Invoice
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo me-1"></i>Reset Form
                            </button>
                            <a href="{{ route('admin.gardens.invoices.index', $garden) }}" class="btn btn-outline-danger">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                        
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Add at least one sample to create invoice
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
let sampleIndex = 0;
let samples = [];

// Add sample row
function addSample() {
    const container = document.getElementById('samples-container');
    const index = sampleIndex++;
    
    const html = `
        <div class="sample-row border rounded p-3 mb-3" id="sample-${index}">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-secondary mb-0">
                    <i class="fas fa-vial me-2"></i>Sample ${index + 1}
                </h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeSample(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sample Code <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" 
                           name="samples[${index}][sample_code]" 
                           placeholder="S001, A1, etc.">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Sample Weight (kg) <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" class="form-control sample-weight" 
                           name="samples[${index}][sample_weight]" 
                           min="0.001" max="999999.999" 
                           onchange="updateSampleTotal(${index})" required>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Number of Sets <span class="text-danger">*</span></label>
                    <input type="number" class="form-control sample-sets" 
                           name="samples[${index}][number_of_sets]" 
                           min="1" value="1"
                           onchange="updateSampleTotal(${index})" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Total Sample Weight</label>
                    <input type="text" class="form-control bg-light sample-total" 
                           id="sample-total-${index}" readonly>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Sample Notes <small class="text-muted">(Optional)</small></label>
                    <input type="text" class="form-control" 
                           name="samples[${index}][sample_notes]" 
                           placeholder="Additional notes...">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    updateTotals();
    updateSubmitButton();
}

// Remove sample row
function removeSample(index) {
    if (confirm('Are you sure you want to remove this sample?')) {
        document.getElementById(`sample-${index}`).remove();
        updateTotals();
        updateSubmitButton();
    }
}

// Update individual sample total
function updateSampleTotal(index) {
    const weightInput = document.querySelector(`input[name="samples[${index}][sample_weight]"]`);
    const setsInput = document.querySelector(`input[name="samples[${index}][number_of_sets]"]`);
    const totalInput = document.getElementById(`sample-total-${index}`);
    
    const weight = parseFloat(weightInput.value) || 0;
    const sets = parseInt(setsInput.value) || 0;
    const total = weight * sets;
    
    totalInput.value = total.toFixed(3) + ' kg';
    updateTotals();
}

// Update overall totals
function updateTotals() {
    const sampleRows = document.querySelectorAll('.sample-row');
    let totalSamples = sampleRows.length;
    let totalSets = 0;
    let totalWeight = 0;
    
    sampleRows.forEach((row, index) => {
        const weightInput = row.querySelector('.sample-weight');
        const setsInput = row.querySelector('.sample-sets');
        
        const weight = parseFloat(weightInput?.value) || 0;
        const sets = parseInt(setsInput?.value) || 0;
        
        totalSets += sets;
        totalWeight += (weight * sets);
    });
    
    document.getElementById('total-samples').textContent = totalSamples;
    document.getElementById('total-sets').textContent = totalSets;
    document.getElementById('total-weight').textContent = totalWeight.toFixed(3) + ' kg';
}

// Update submit button state
function updateSubmitButton() {
    const submitBtn = document.getElementById('submit-btn');
    const sampleCount = document.querySelectorAll('.sample-row').length;
    
    if (sampleCount === 0) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Add Samples First';
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i>Create Invoice';
    }
}

// Reset form
function resetForm() {
    if (confirm('Are you sure you want to reset the form? All data will be lost.')) {
        document.getElementById('invoiceForm').reset();
        document.getElementById('samples-container').innerHTML = '';
        sampleIndex = 0;
        updateTotals();
        updateSubmitButton();
    }
}

// Form validation
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    const sampleCount = document.querySelectorAll('.sample-row').length;
    
    if (sampleCount === 0) {
        e.preventDefault();
        alert('Please add at least one sample to create the invoice.');
        return;
    }
    
    // Validate each sample
    let isValid = true;
    document.querySelectorAll('.sample-row').forEach((row, index) => {
        const weightInput = row.querySelector('.sample-weight');
        const setsInput = row.querySelector('.sample-sets');
        
        if (!weightInput.value || parseFloat(weightInput.value) <= 0) {
            isValid = false;
            weightInput.classList.add('is-invalid');
        } else {
            weightInput.classList.remove('is-invalid');
        }
        
        if (!setsInput.value || parseInt(setsInput.value) <= 0) {
            isValid = false;
            setsInput.classList.add('is-invalid');
        } else {
            setsInput.classList.remove('is-invalid');
        }
    });
    
    if (!isValid) {
        e.preventDefault();
        alert('Please fill in all required sample fields correctly.');
    }
});

// Initialize with one sample
document.addEventListener('DOMContentLoaded', function() {
    addSample();
});
</script>

<style>
.sample-row {
    background-color: #f8f9fa;
    transition: all 0.3s ease;
}

.sample-row:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.is-invalid {
    border-color: #dc3545;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.sample-total {
    font-weight: 600;
    color: #0d6efd;
}

@media (max-width: 768px) {
    .sample-row {
        padding: 1rem;
    }
    
    .btn-group {
        flex-direction: column;
    }
}
</style>
@endsection