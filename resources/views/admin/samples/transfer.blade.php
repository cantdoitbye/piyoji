{{-- resources/views/admin/samples/transfer.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Transfer Sample to Another Batch')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Transfer Sample to Another Batch</h1>
            <p class="text-muted">Transfer portion of sample for retesting</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sample
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Current Sample Information -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Current Sample: {{ $sample->sample_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Sample ID:</td>
                                    <td>{{ $sample->sample_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Current Batch:</td>
                                    <td>{{ $sample->batch_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Available Quantity:</td>
                                    <td>{{ $sample->number_of_samples }} samples</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Available Weight:</td>
                                    <td>{{ $sample->sample_weight }} kg</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Seller:</td>
                                    <td>{{ $sample->seller->seller_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tea Estate:</td>
                                    <td>{{ $sample->seller->tea_estate_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Evaluation Score:</td>
                                    <td>{{ $sample->overall_score }}/10</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td><span class="badge {{ $sample->status_badge_class }}">{{ $sample->status_label }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transfer Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Transfer Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.samples.transfer-to-batch', $sample->id) }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transferred_quantity" class="form-label">
                                        <i class="fas fa-sort-numeric-up me-1"></i>Quantity to Transfer <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('transferred_quantity') is-invalid @enderror" 
                                           id="transferred_quantity" 
                                           name="transferred_quantity" 
                                           min="1" 
                                           {{-- max="{{ $sample->number_of_samples - 1 }}" --}}
                                           value="{{ old('transferred_quantity') }}"
                                           required>
                                    <div class="form-text">Number of samples to transfer (max {{ $sample->number_of_samples - 1 }})</div>
                                    @error('transferred_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="transferred_weight" class="form-label">
                                        <i class="fas fa-weight me-1"></i>Weight to Transfer (kg) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('transferred_weight') is-invalid @enderror" 
                                           id="transferred_weight" 
                                           name="transferred_weight" 
                                           min="0.01" 
                                           max="{{ $sample->sample_weight - 0.01 }}"
                                           step="0.01"
                                           value="{{ old('transferred_weight') }}"
                                           required>
                                    <div class="form-text">Weight to transfer (max {{ $sample->sample_weight - 0.01 }} kg)</div>
                                    @error('transferred_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="transfer_reason" class="form-label">
                                <i class="fas fa-question-circle me-1"></i>Transfer Reason <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('transfer_reason') is-invalid @enderror" 
                                    id="transfer_reason" 
                                    name="transfer_reason" 
                                    required>
                                <option value="">Select reason for transfer...</option>
                                <option value="retesting" {{ old('transfer_reason') == 'retesting' ? 'selected' : '' }}>Retesting</option>
                                <option value="quality_check" {{ old('transfer_reason') == 'quality_check' ? 'selected' : '' }}>Quality Check</option>
                                <option value="additional_evaluation" {{ old('transfer_reason') == 'additional_evaluation' ? 'selected' : '' }}>Additional Evaluation</option>
                                <option value="other" {{ old('transfer_reason') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('transfer_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="transfer_remarks" class="form-label">
                                <i class="fas fa-comment me-1"></i>Transfer Remarks
                            </label>
                            <textarea class="form-control @error('transfer_remarks') is-invalid @enderror" 
                                      id="transfer_remarks" 
                                      name="transfer_remarks" 
                                      rows="3" 
                                      placeholder="Optional remarks about this transfer...">{{ old('transfer_remarks') }}</textarea>
                            @error('transfer_remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to transfer this portion of the sample to another batch for retesting?')">
                                <i class="fas fa-exchange-alt me-1"></i>Transfer Sample
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Transfer Guidelines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Transfer Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Sample Transfer Process:</strong><br>
                            • Transfer creates a new sample for retesting<br>
                            • Original sample keeps remaining portion<br>
                            • Both samples maintain link to original<br>
                            • New sample will need to be batched separately<br>
                            • Transfer is tracked for audit purposes
                        </small>
                    </div>
                    
                    <h6 class="small fw-bold mb-2">Important Notes:</h6>
                    <ul class="small mb-3">
                        <li>Cannot transfer entire sample quantity</li>
                        <li>Some portion must remain in original</li>
                        <li>Only evaluated samples can be transferred</li>
                        <li>Sample must be batched before transfer</li>
                    </ul>
                    
                    <h6 class="small fw-bold mb-2">After Transfer:</h6>
                    <ul class="small mb-0">
                        <li>New sample will be created with transferred portion</li>
                        <li>New sample status: "Received" (ready for batching)</li>
                        <li>Evaluation status: "Pending" (needs re-evaluation)</li>
                        <li>Original sample retains remaining weight/quantity</li>
                    </ul>
                </div>
            </div>

            <!-- Sample History -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Sample History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-wrapper">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Sample Received</h6>
                                <p class="timeline-text">{{ $sample->arrival_date->format('d M Y') }}</p>
                                <small class="text-muted">By: {{ $sample->receivedBy->name }}</small>
                            </div>
                        </div>
                        
                        @if($sample->batch_group_id)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Sample Batched</h6>
                                    <p class="timeline-text">Batch: {{ $sample->batch_id }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($sample->evaluation_status === 'completed')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Evaluation Completed</h6>
                                    <p class="timeline-text">{{ $sample->evaluated_at->format('d M Y H:i') }}</p>
                                    <small class="text-muted">Score: {{ $sample->overall_score }}/10</small>
                                </div>
                            </div>
                        @endif
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
    const quantityInput = document.getElementById('transferred_quantity');
    const weightInput = document.getElementById('transferred_weight');
    const maxQuantity = {{ $sample->number_of_samples - 1 }};
    const maxWeight = {{ $sample->sample_weight - 0.01 }};
    const weightPerSample = {{ $sample->weight_per_sample ?? 0 }};

    // Auto-calculate weight based on quantity if weight per sample is known
    if (weightPerSample > 0) {
        quantityInput.addEventListener('input', function() {
            const quantity = parseInt(this.value) || 0;
            if (quantity > 0 && quantity <= maxQuantity) {
                const calculatedWeight = (quantity * weightPerSample).toFixed(2);
                if (calculatedWeight <= maxWeight) {
                    weightInput.value = calculatedWeight;
                }
            }
        });
    }

    // Validate inputs
    quantityInput.addEventListener('input', function() {
        const quantity = parseInt(this.value) || 0;
        if (quantity >= maxQuantity) {
            this.setCustomValidity(`Maximum quantity is ${maxQuantity}`);
        } else {
            this.setCustomValidity('');
        }
    });

    weightInput.addEventListener('input', function() {
        const weight = parseFloat(this.value) || 0;
        if (weight >= maxWeight) {
            this.setCustomValidity(`Maximum weight is ${maxWeight} kg`);
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
@endpush