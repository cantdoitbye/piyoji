{{-- resources/views/admin/samples/transfer.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Transfer Sample for Retesting')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Transfer Sample for Retesting</h1>
            <p class="text-muted">Allocate 10gm for retesting in new batch</p>
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
                    <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Sample: {{ $sample->sample_name }}</h5>
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
                                    <td class="fw-bold">Catalog Weight:</td>
                                    <td><strong>{{ $sample->catalog_weight }} kg</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Available Weight:</td>
                                    <td>
                                        <span class="badge {{ $sample->has_sufficient_weight ? 'bg-success' : 'bg-danger' }}">
                                            {{ $sample->available_weight }} kg
                                        </span>
                                    </td>
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
                                    <td class="fw-bold">Evaluation Score:</td>
                                    <td>{{ $sample->overall_score }}/10</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Previous Allocations:</td>
                                    <td>{{ $sample->allocation_count }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Allocation Status:</td>
                                    <td>{{ $sample->allocation_status }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if(!$sample->has_sufficient_weight)
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Insufficient Weight:</strong> This sample doesn't have enough weight available for retesting allocation. 
                        Required: {{ \App\Models\Sample::FIXED_ALLOCATION_WEIGHT }}kg, Available: {{ $sample->available_weight }}kg
                    </div>
                    @endif
                </div>
            </div>

            <!-- Transfer Form -->
            @if($sample->has_sufficient_weight)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Retesting Allocation</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fixed Allocation:</strong> Exactly <strong>10gm (0.01kg)</strong> will be allocated from this sample for retesting. 
                        The remaining {{ $sample->available_weight - \App\Models\Sample::FIXED_ALLOCATION_WEIGHT }}kg will stay in the sample catalog.
                    </div>

                    <form action="{{ route('admin.samples.transfer-to-batch', $sample->id) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="transfer_reason" class="form-label">
                                <i class="fas fa-question-circle me-1"></i>Retesting Reason <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('transfer_reason') is-invalid @enderror" 
                                    id="transfer_reason" 
                                    name="transfer_reason" 
                                    required>
                                <option value="">Select reason for retesting...</option>
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
                                      placeholder="Optional remarks about this retesting allocation...">{{ old('transfer_remarks') }}</textarea>
                            @error('transfer_remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Hidden fields for fixed allocation -->
                        <input type="hidden" name="transferred_weight" value="{{ \App\Models\Sample::FIXED_ALLOCATION_WEIGHT }}">
                        <input type="hidden" name="transferred_quantity" value="1">

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure you want to allocate 10gm from this sample for retesting?')">
                                <i class="fas fa-exchange-alt me-1"></i>Allocate for Retesting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Allocation Guidelines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>10gm Allocation System</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Fixed Allocation Process:</strong><br>
                            • Exactly 10gm allocated for retesting<br>
                            • Remaining weight stays in catalog<br>
                            • New sample created with 10gm<br>
                            • Original sample retains full catalog<br>
                            • All allocations tracked automatically
                        </small>
                    </div>
                    
                    <h6 class="small fw-bold mb-2">Key Features:</h6>
                    <ul class="small mb-3">
                        <li>No manual weight calculation needed</li>
                        <li>Fixed 10gm allocation for all testing</li>
                        <li>Catalog weight preserved</li>
                        <li>Multiple retesting possible if sufficient weight</li>
                    </ul>
                    
                    <h6 class="small fw-bold mb-2">After Allocation:</h6>
                    <ul class="small mb-0">
                        <li>New sample: 10gm, status "Received"</li>
                        <li>New sample needs batching for evaluation</li>
                        <li>Original catalog updated automatically</li>
                        <li>Allocation tracked for audit</li>
                    </ul>
                </div>
            </div>

            <!-- Allocation History -->
            @if($sample->allocation_count > 0)
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Allocation History</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-wrapper">
                        @foreach($sample->allocations()->latest()->take(5)->get() as $allocation)
                        <div class="timeline-item">
                            <div class="timeline-marker {{ $allocation->status_badge_class }}"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">{{ $allocation->allocation_type_label }}</h6>
                                <p class="timeline-text">{{ $allocation->allocation_date->format('d M Y H:i') }}</p>
                                <small class="text-muted">
                                    Weight: {{ $allocation->allocated_weight }}kg | 
                                    Status: {{ $allocation->status_label }}
                                </small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    @if($sample->allocation_count > 5)
                    <div class="text-center mt-3">
                        <small class="text-muted">... and {{ $sample->allocation_count - 5 }} more allocations</small>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection