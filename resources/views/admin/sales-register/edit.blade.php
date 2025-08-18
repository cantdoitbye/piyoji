@extends('admin.layouts.app')

@section('title', 'Edit Sales Entry')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Edit Sales Entry</h1>
            <p class="text-muted">{{ $salesEntry->sales_entry_id }} - Update sales entry details</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.sales-register.show', $salesEntry->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> View Details
            </a>
            <a href="{{ route('admin.sales-register.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sales Register
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Sales Entry Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Sales Entry Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sales-register.update', $salesEntry->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <!-- Entry ID (Read-only) -->
                            <div class="col-md-6">
                                <label for="sales_entry_id" class="form-label">Entry ID</label>
                                <input type="text" class="form-control bg-light" 
                                       id="sales_entry_id" value="{{ $salesEntry->sales_entry_id }}" readonly>
                                <small class="form-text text-muted">Status will be managed separately</small>
                            </div>

                            <!-- Buyer Selection -->
                            <div class="col-md-6">
                                <label for="buyer_id" class="form-label">Buyer <span class="text-danger">*</span></label>
                                <select class="form-select @error('buyer_id') is-invalid @enderror" 
                                        id="buyer_id" name="buyer_id" required>
                                    <option value="">Select Buyer</option>
                                    @foreach($buyers as $buyer)
                                        <option value="{{ $buyer->id }}" 
                                                {{ (old('buyer_id', $salesEntry->buyer_id) == $buyer->id) ? 'selected' : '' }}
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
                                       id="product_name" name="product_name" 
                                       value="{{ old('product_name', $salesEntry->product_name) }}" 
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
                                        <option value="{{ $code }}" 
                                                {{ (old('tea_grade', $salesEntry->tea_grade) === $code) ? 'selected' : '' }}>
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
                                       id="entry_date" name="entry_date" 
                                       value="{{ old('entry_date', $salesEntry->entry_date->format('Y-m-d')) }}">
                                @error('entry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Date of the sales entry</small>
                            </div>

                            <!-- Quantity -->
                            <div class="col-md-6">
                                <label for="quantity_kg" class="form-label">Quantity (KG) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('quantity_kg') is-invalid @enderror" 
                                       id="quantity_kg" name="quantity_kg" 
                                       value="{{ old('quantity_kg', $salesEntry->quantity_kg) }}" 
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
                                       id="rate_per_kg" name="rate_per_kg" 
                                       value="{{ old('rate_per_kg', $salesEntry->rate_per_kg) }}" 
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
                                       value="{{ $salesEntry->formatted_total_amount }}" readonly>
                                <small class="form-text text-muted">Automatically calculated: Quantity × Rate per KG</small>
                            </div>

                            <!-- Remarks -->
                            <div class="col-12">
                                <label for="remarks" class="form-label">Remarks</label>
                                <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                          id="remarks" name="remarks" rows="3" 
                                          placeholder="Any additional notes about this sales entry">{{ old('remarks', $salesEntry->remarks) }}</textarea>
                                @error('remarks')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">Optional notes or observations</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.sales-register.show', $salesEntry->id) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Sales Entry
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
            <!-- Current Values Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Current Values</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <td class="fw-bold">Entry ID:</td>
                            <td>{{ $salesEntry->sales_entry_id }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Current Status:</td>
                            <td>
                                <span class="badge {{ $salesEntry->status_badge_class }}">
                                    {{ $salesEntry->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Current Total:</td>
                            <td><strong class="text-success">{{ $salesEntry->formatted_total_amount }}</strong></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Created:</td>
                            <td>{{ $salesEntry->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Last Updated:</td>
                            <td>{{ $salesEntry->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Updated Calculation Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Updated Calculation</h6>
                </div>
                <div class="card-body">
                    <div id="calculation-summary">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-muted mb-0">Quantity</h6>
                                    <span id="display-quantity" class="h5 text-primary">{{ $salesEntry->quantity_kg }} KG</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-muted mb-0">Rate/KG</h6>
                                <span id="display-rate" class="h5 text-info">{{ $salesEntry->formatted_rate_per_kg }}</span>
                            </div>
                        </div>
                        <hr>
                        <div class="text-center">
                            <h6 class="text-muted mb-0">New Total Amount</h6>
                            <span id="display-total" class="h4 text-success">{{ $salesEntry->formatted_total_amount }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning Card -->
            @if($salesEntry->status === 'approved')
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <small>
                            <strong>Note:</strong> This entry is already approved. Any changes will require re-approval from an administrator.
                        </small>
                    </div>
                </div>
            </div>
            @else
            <!-- Help Card -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Edit Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Editing Sales Entry:</strong><br>
                            1. Update any field as needed<br>
                            2. Total amount will be recalculated automatically<br>
                            3. Entry will remain in {{ $salesEntry->status_label }} status<br>
                            4. Changes will be tracked for audit purposes
                        </small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
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
        if (!confirm(`Are you sure you want to update this sales entry?\n\nNew Total Amount: ₹${totalAmount.toFixed(2)}`)) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize calculation
    calculateTotalAmount();
});
</script>
@endpush