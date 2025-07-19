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

                            <!-- Batch ID -->
                            <div class="col-md-6">
                                <label for="batch_id" class="form-label">Batch ID <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('batch_id') is-invalid @enderror" 
                                       id="batch_id" name="batch_id" value="{{ old('batch_id') }}" 
                                       placeholder="Enter batch identifier" required>
                                @error('batch_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Unique batch identifier from seller</small>
                            </div>

                            <!-- Sample Weight -->
                            <div class="col-md-6">
                                <label for="sample_weight" class="form-label">Sample Weight (kg)</label>
                                <input type="number" class="form-control @error('sample_weight') is-invalid @enderror" 
                                       id="sample_weight" name="sample_weight" value="{{ old('sample_weight') }}" 
                                       step="0.01" min="0" max="999.99" placeholder="0.00">
                                @error('sample_weight')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Weight of the sample in kilograms (optional)</small>
                            </div>

                            <!-- Arrival Date -->
                            <div class="col-md-6">
                                <label for="arrival_date" class="form-label">Arrival Date</label>
                                <input type="date" class="form-control @error('arrival_date') is-invalid @enderror" 
                                       id="arrival_date" name="arrival_date" value="{{ old('arrival_date', date('Y-m-d')) }}">
                                @error('arrival_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Date when sample arrived</small>
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
            <!-- Help Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Sample Receiving Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Module 2.1 - Sample Receiving Process:</strong><br>
                            1. Enter sample details as received from seller<br>
                            2. Assign unique Sample ID (auto-generated)<br>
                            3. Record arrival date and weight<br>
                            4. Add any initial observations<br>
                            5. Sample will be marked as "Received" status
                        </small>
                    </div>
                    
                    <h6 class="small fw-bold mb-2">Required Fields:</h6>
                    <ul class="small mb-3">
                        <li>Sample Name</li>
                        <li>Source Seller</li>
                        <li>Batch ID</li>
                    </ul>
                    
                    <h6 class="small fw-bold mb-2">Auto-Generated:</h6>
                    <ul class="small mb-0">
                        <li>Sample ID (SMP + Year + Month + Sequential Number)</li>
                        <li>Initial Status: "Received"</li>
                        <li>Evaluation Status: "Pending"</li>
                    </ul>
                </div>
            </div>

            <!-- Selected Seller Info -->
            <div class="card" id="sellerInfoCard" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-store me-2"></i>Seller Information</h6>
                </div>
                <div class="card-body">
                    <div id="sellerDetails">
                        <!-- Seller details will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Recent Samples -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Samples</h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted">
                        <i class="fas fa-flask fa-2x mb-2"></i>
                        <p class="small">Recent samples will appear here after creation</p>
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
    const sellerInfoCard = document.getElementById('sellerInfoCard');
    const sellerDetails = document.getElementById('sellerDetails');

    // Show seller information when seller is selected
    sellerSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const sellerName = selectedOption.text.split(' (')[0];
            const teaEstate = selectedOption.text.match(/\(([^)]+)\)/)?.[1] || '';
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

    // Auto-generate batch ID suggestion based on current date
    const batchIdInput = document.getElementById('batch_id');
    if (!batchIdInput.value) {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        
        batchIdInput.placeholder = `e.g., BATCH${year}${month}${day}001`;
    }

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const sampleName = document.getElementById('sample_name').value.trim();
        const sellerId = document.getElementById('seller_id').value;
        const batchId = document.getElementById('batch_id').value.trim();

        if (!sampleName || !sellerId || !batchId) {
            e.preventDefault();
            alert('Please fill in all required fields (Sample Name, Seller, and Batch ID).');
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
});
</script>
@endpush