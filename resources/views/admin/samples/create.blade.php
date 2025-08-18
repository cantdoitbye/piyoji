@extends('admin.layouts.app')

@section('title', 'Add New Sample')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Add New Sample</h1>
            <p class="text-muted">Register a new tea sample (Module 2.1 - Sample Receiving)</p>
        </div>
        <a href="{{ route('admin.samples.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Samples
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Sample Information Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Sample Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.samples.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Sample Name -->
                            <div class="col-md-6">
                                <label for="sample_name" class="form-label">Sample Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('sample_name') is-invalid @enderror" 
                                       id="sample_name" name="sample_name" value="{{ old('sample_name') }}" 
                                       placeholder="Enter sample name" required>
                                @error('sample_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Descriptive name for the tea sample</small>
                            </div>

                            <!-- Seller -->
                            <div class="col-md-6">
                                <label for="seller_id" class="form-label">Source Seller <span class="text-danger">*</span></label>
                                <select class="form-select @error('seller_id') is-invalid @enderror" 
                                        id="seller_id" name="seller_id" required>
                                    <option value="">Select Seller</option>
                                    @foreach($sellers as $seller)
                                        <option value="{{ $seller->id }}" {{ old('seller_id') == $seller->id ? 'selected' : '' }}
                                                data-seller-name="{{ $seller->seller_name }}"
                                                data-tea-estate="{{ $seller->tea_estate_name }}"
                                                data-tea-grades="{{ implode(', ', $seller->tea_grades) }}">
                                            {{ $seller->seller_name }} ({{ $seller->tea_estate_name }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('seller_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the seller who provided this sample</small>
                            </div>

                            <!-- Number of Samples -->
                            <div class="col-md-6">
                                <label for="number_of_samples" class="form-label">Number of Samples <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('number_of_samples') is-invalid @enderror" 
                                       id="number_of_samples" name="number_of_samples" value="{{ old('number_of_samples', 1) }}" 
                                       min="1" max="1000" required>
                                @error('number_of_samples')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Total number of samples received</small>
                            </div>

                            <!-- Weight Per Sample -->
                            <div class="col-md-6">
                                <label for="weight_per_sample" class="form-label">Weight Per Sample (kg)</label>
                                <input type="number" class="form-control @error('weight_per_sample') is-invalid @enderror" 
                                       id="weight_per_sample" name="weight_per_sample" value="{{ old('weight_per_sample') }}" 
                                       step="0.01" min="0" max="999.99" placeholder="e.g., 0.50">
                                @error('weight_per_sample')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Weight of each individual sample in kg</small>
                            </div>

                            <!-- Total Weight (Auto-calculated, read-only) -->
                            <div class="col-md-6">
                                <label for="total_weight_display" class="form-label">Total Weight (kg)</label>
                                <input type="text" class="form-control bg-light" 
                                       id="total_weight_display" 
                                       placeholder="Auto-calculated" readonly>
                                <small class="form-text text-muted">Automatically calculated: Weight per sample × Number of samples</small>
                            </div>

                            <!-- Arrival Date -->
                            <div class="col-md-6">
                                <label for="arrival_date" class="form-label">Arrival Date</label>
                                <input type="date" class="form-control @error('arrival_date') is-invalid @enderror" 
                                       id="arrival_date" name="arrival_date" value="{{ old('arrival_date', date('Y-m-d')) }}">
                                @error('arrival_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Date when the sample was received</small>
                            </div>

                            <!-- Remarks -->
                            <div class="col-12">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" rows="3" 
                                          placeholder="Any additional notes about the sample">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional notes or observations</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.samples.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Sample
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Seller Information Card -->
            <div class="card mb-4" id="seller-info-card" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Seller Information</h6>
                </div>
                <div class="card-body" id="seller-details">
                    <!-- Seller details will be populated via JavaScript -->
                </div>
            </div>

            <!-- Important Notice Card -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <small>
                            <strong>Batch Management:</strong><br>
                            Samples will be automatically assigned to batches later through the batch management system. Each batch contains exactly 48 samples and is processed by date.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Sample Receiving Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Module 2.1 - Sample Receiving Process:</strong><br>
                            1. Enter sample details as received from seller<br>
                            2. Assign unique Sample ID (auto-generated)<br>
                            3. Record number of samples and weight per sample<br>
                            4. Total weight will be calculated automatically<br>
                            5. Samples will be batched later via batch management
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Today's Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-primary mb-0">{{ $todayStats['samples'] ?? 0 }}</h5>
                                <small class="text-muted">Samples Added</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-success mb-0">{{ $todayStats['unbatched'] ?? 0 }}</h5>
                            <small class="text-muted">Awaiting Batch</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sellerSelect = document.getElementById('seller_id');
    const sellerInfoCard = document.getElementById('seller-info-card');
    const sellerDetails = document.getElementById('seller-details');
    const numberOfSamplesInput = document.getElementById('number_of_samples');
    const weightPerSampleInput = document.getElementById('weight_per_sample');
    const totalWeightDisplay = document.getElementById('total_weight_display');

    // Function to calculate and display total weight
    function calculateTotalWeight() {
        const numberOfSamples = parseFloat(numberOfSamplesInput.value) || 0;
        const weightPerSample = parseFloat(weightPerSampleInput.value) || 0;
        const totalWeight = numberOfSamples * weightPerSample;
        
        if (totalWeight > 0) {
            totalWeightDisplay.value = totalWeight.toFixed(2) + ' kg';
        } else {
            totalWeightDisplay.value = '';
            totalWeightDisplay.placeholder = 'Auto-calculated';
        }
    }

    // Event listeners for weight calculation
    numberOfSamplesInput.addEventListener('input', calculateTotalWeight);
    weightPerSampleInput.addEventListener('input', calculateTotalWeight);

    // Seller selection handler
    sellerSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const sellerName = selectedOption.dataset.sellerName || '';
            const teaEstate = selectedOption.dataset.teaEstate || '';
            const teaGrades = selectedOption.dataset.teaGrades || '';

            sellerDetails.innerHTML = `
                <div class="mb-2">
                    <strong>Seller:</strong><br>
                    <span class="text-muted">${sellerName}</span>
                </div>
                <div class="mb-2">
                    <strong>Tea Estate:</strong><br>
                    <span class="text-muted">${teaEstate}</span>
                </div>
                <div class="mb-0">
                    <strong>Tea Grades:</strong><br>
                    <span class="text-muted">${teaGrades}</span>
                </div>
            `;
            
            sellerInfoCard.style.display = 'block';
        } else {
            sellerInfoCard.style.display = 'none';
        }
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const sampleName = document.getElementById('sample_name').value.trim();
        const sellerId = document.getElementById('seller_id').value;
        const numberOfSamples = document.getElementById('number_of_samples').value;

        if (!sampleName || !sellerId || !numberOfSamples || numberOfSamples < 1) {
            e.preventDefault();
            alert('Please fill in all required fields (Sample Name, Seller, and Number of Samples).');
            return false;
        }

        // Confirm submission
        if (!confirm('Are you sure you want to add this sample?')) {
            e.preventDefault();
            return false;
        }
    });

    // Auto-focus on first input
    document.getElementById('sample_name').focus();

    // Initialize total weight calculation
    calculateTotalWeight();
});
</script>
@endpush