@extends('admin.layouts.app')

@section('title', 'Add Sales Entry')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Add Sales Entry</h1>
            <p class="text-muted">Create new sales entry for direct client orders</p>
        </div>
        <a href="{{ route('admin.sales-register.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Sales Register
        </a>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Sales Entry Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-invoice me-2"></i>Sales Entry Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sales-register.store') }}" method="POST">
                        @csrf
                        
                        <div class="row g-3">
                            <!-- Buyer Selection -->
                            <div class="col-md-6">
                                <label for="buyer_id" class="form-label">Buyer <span class="text-danger">*</span></label>
                                <select class="form-select @error('buyer_id') is-invalid @enderror" 
                                        id="buyer_id" name="buyer_id" required>
                                    <option value="">Select Buyer</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}" {{ old('buyer_id') == $buyer->id ? 'selected' : '' }}
                                                data-buyer-type="{{ $buyer->buyer_type }}">
                                            {{ $buyer->buyer_name }} ({{ ucfirst($buyer->buyer_type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('buyer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the buyer for this sales entry</small>
                            </div>

                            <!-- Product Name -->
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('product_name') is-invalid @enderror" 
                                       id="product_name" name="product_name" value="{{ old('product_name') }}" 
                                       placeholder="Enter product name" required>
                                @error('product_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Name of the tea product</small>
                            </div>

                            <!-- Tea Grade -->
                            <div class="col-md-6">
                                <label for="tea_grade" class="form-label">Tea Grade <span class="text-danger">*</span></label>
                                <select class="form-select @error('tea_grade') is-invalid @enderror" 
                                        id="tea_grade" name="tea_grade" required>
                                    <option value="">Select Tea Grade</option>
                                    @foreach($teaGrades as $code => $name)
                                        <option value="{{ $code }}" {{ old('tea_grade') === $code ? 'selected' : '' }}>
                                            {{ $code }} - {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tea_grade')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Select the grade of tea</small>
                            </div>

                            <!-- Entry Date -->
                            <div class="col-md-6">
                                <label for="entry_date" class="form-label">Entry Date</label>
                                <input type="date" class="form-control @error('entry_date') is-invalid @enderror" 
                                       id="entry_date" name="entry_date" value="{{ old('entry_date', date('Y-m-d')) }}">
                                @error('entry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Date of the sales entry</small>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label for="quantity_kg" class="form-label">Quantity (KG) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity_kg') is-invalid @enderror" 
                                       id="quantity_kg" name="quantity_kg" value="{{ old('quantity_kg') }}" 
                                       step="0.01" min="0.01" placeholder="0.00" required>
                                @error('quantity_kg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Quantity in kilograms</small>
                            </div>

                            <!-- Rate per KG -->
                            <div class="col-md-6">
                                <label for="rate_per_kg" class="form-label">Rate per KG (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('rate_per_kg') is-invalid @enderror" 
                                       id="rate_per_kg" name="rate_per_kg" value="{{ old('rate_per_kg') }}" 
                                       step="0.01" min="0.01" placeholder="0.00" required>
                                @error('rate_per_kg')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Rate per kilogram in rupees</small>
                            </div>

                            <!-- Total Amount (Auto-calculated, read-only) -->
                            <div class="col-md-6">
                                <label for="total_amount_display" class="form-label">Total Amount (₹)</label>
                                <input type="text" class="form-control bg-light" 
                                       id="total_amount_display" 
                                       placeholder="Auto-calculated" readonly>
                                <small class="form-text text-muted">Automatically calculated: Quantity × Rate per KG</small>
                            </div>

                            <!-- Remarks -->
                            <div class="col-12">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" rows="3" 
                                          placeholder="Any additional notes about this sales entry">{{ old('remarks') }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional notes or observations</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.sales-register.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Sales Entry
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
            <!-- Buyer Information Card -->
            <div class="card mb-4" id="buyer-info-card" style="display: none;">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Buyer Information</h6>
                </div>
                <div class="card-body" id="buyer-details">
                    <!-- Buyer details will be populated via JavaScript -->
                </div>
            </div>

            <!-- Calculation Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Calculation Summary</h6>
                </div>
                <div class="card-body">
                    <div id="calculation-summary">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-muted mb-0">Quantity</h6>
                                    <span id="display-quantity" class="h5 text-primary">0 KG</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-0">Rate/KG</h6>
                                <span id="display-rate" class="h5 text-info">₹0</span>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h6 class="text-muted mb-0">Total Amount</h6>
                            <span id="display-total" class="h4 text-success">₹0.00</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Sales Entry Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Direct Sales Process:</strong><br>
                            1. Select buyer (big clients who don't prefer samples)<br>
                            2. Enter product details and tea grade<br>
                            3. Specify quantity and rate per KG<br>
                            4. Total amount will be calculated automatically<br>
                            5. Entry will be pending approval initially
                        </small>
                    </div>
                </div>
            </div>

            <!-- Recent Entries -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Entries</h6>
                </div>
                <div class="card-body">
                    <div class="text-muted small">
                        Recent sales entries will be shown here for reference.
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
    const buyerSelect = document.getElementById('buyer_id');
    const buyerInfoCard = document.getElementById('buyer-info-card');
    const buyerDetails = document.getElementById('buyer-details');
    const quantityInput = document.getElementById('quantity_kg');
    const rateInput = document.getElementById('rate_per_kg');
    const totalAmountDisplay = document.getElementById('total_amount_display');
    
    // Display elements
    const displayQuantity = document.getElementById('display-quantity');
    const displayRate = document.getElementById('display-rate');
    const displayTotal = document.getElementById('display-total');

    // Function to calculate and display total amount
    function calculateTotalAmount() {
        const quantity = parseFloat(quantityInput.value) || 0;
        const rate = parseFloat(rateInput.value) || 0;
        const totalAmount = quantity * rate;
        
        // Update main display
        if (totalAmount > 0) {
            totalAmountDisplay.value = '₹' + totalAmount.toFixed(2);
        } else {
            totalAmountDisplay.value = '';
            totalAmountDisplay.placeholder = 'Auto-calculated';
        }
        
        // Update calculation summary
        displayQuantity.textContent = quantity > 0 ? quantity + ' KG' : '0 KG';
        displayRate.textContent = rate > 0 ? '₹' + rate.toFixed(2) : '₹0';
        displayTotal.textContent = totalAmount > 0 ? '₹' + totalAmount.toFixed(2) : '₹0.00';
    }

    // Event listeners for amount calculation
    quantityInput.addEventListener('input', calculateTotalAmount);
    rateInput.addEventListener('input', calculateTotalAmount);

    // Buyer selection handler
    buyerSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const buyerType = selectedOption.dataset.buyerType || '';
            const buyerName = selectedOption.textContent.split(' (')[0];

            buyerDetails.innerHTML = `
                <div class="mb-2">
                    <strong>Buyer Name:</strong><br>
                    <span class="text-muted">${buyerName}</span>
                </div>
                <div class="mb-0">
                    <strong>Buyer Type:</strong><br>
                    <span class="badge ${buyerType === 'big' ? 'bg-success' : 'bg-info'}">${buyerType.charAt(0).toUpperCase() + buyerType.slice(1)} Client</span>
                </div>
            `;
            
            buyerInfoCard.style.display = 'block';
        } else {
            buyerInfoCard.style.display = 'none';
        }
    });

    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const buyerId = document.getElementById('buyer_id').value;
        const productName = document.getElementById('product_name').value.trim();
        const teaGrade = document.getElementById('tea_grade').value;
        const quantity = document.getElementById('quantity_kg').value;
        const rate = document.getElementById('rate_per_kg').value;

        if (!buyerId || !productName || !teaGrade || !quantity || !rate) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }

        if (parseFloat(quantity) <= 0 || parseFloat(rate) <= 0) {
            e.preventDefault();
            alert('Quantity and rate must be greater than 0.');
            return false;
        }

        // Confirm submission
        const totalAmount = parseFloat(quantity) * parseFloat(rate);
        if (!confirm(`Are you sure you want to create this sales entry?\n\nTotal Amount: ₹${totalAmount.toFixed(2)}`)) {
            e.preventDefault();
            return false;
        }
    });

    // Auto-focus on first input
    document.getElementById('buyer_id').focus();

    // Initialize calculation
    calculateTotalAmount();
});
</script>
@endpush