@extends('admin.layouts.app')

@section('title', 'Edit Sample')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit me-2"></i>Edit Sample - {{ $sample->sample_id }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.samples.update', $sample->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sample_name" class="form-label">Sample Name *</label>
                                    <input type="text" class="form-control @error('sample_name') is-invalid @enderror" 
                                           id="sample_name" name="sample_name" value="{{ old('sample_name', $sample->sample_name) }}" required>
                                    @error('sample_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="seller_id" class="form-label">Seller *</label>
                                    <select class="form-select @error('seller_id') is-invalid @enderror" 
                                            id="seller_id" name="seller_id" required>
                                        <option value="">Select Seller</option>
                                        @foreach($sellers as $seller)
                                            <option value="{{ $seller->id }}" 
                                                {{ old('seller_id', $sample->seller_id) == $seller->id ? 'selected' : '' }}>
                                                {{ $seller->seller_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('seller_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="batch_id" class="form-label">Batch ID *</label>
                                    <input type="text" class="form-control @error('batch_id') is-invalid @enderror" 
                                           id="batch_id" name="batch_id" value="{{ old('batch_id', $sample->batch_id) }}" required>
                                    @error('batch_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sample_weight" class="form-label">Sample Weight (kg)</label>
                                    <input type="number" step="0.01" class="form-control @error('sample_weight') is-invalid @enderror" 
                                           id="sample_weight" name="sample_weight" value="{{ old('sample_weight', $sample->sample_weight) }}">
                                    @error('sample_weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="arrival_date" class="form-label">Arrival Date *</label>
                                    <input type="date" class="form-control @error('arrival_date') is-invalid @enderror" 
                                           id="arrival_date" name="arrival_date" 
                                           value="{{ old('arrival_date', $sample->arrival_date->format('Y-m-d')) }}" required>
                                    @error('arrival_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status">
                                        <option value="received" {{ old('status', $sample->status) == 'received' ? 'selected' : '' }}>Received</option>
                                        <option value="pending_evaluation" {{ old('status', $sample->status) == 'pending_evaluation' ? 'selected' : '' }}>Pending Evaluation</option>
                                        <option value="evaluated" {{ old('status', $sample->status) == 'evaluated' ? 'selected' : '' }}>Evaluated</option>
                                        <option value="approved" {{ old('status', $sample->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                        <option value="rejected" {{ old('status', $sample->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="assigned_to_buyers" {{ old('status', $sample->status) == 'assigned_to_buyers' ? 'selected' : '' }}>Assigned to Buyers</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea class="form-control @error('remarks') is-invalid @enderror" 
                                      id="remarks" name="remarks" rows="3" 
                                      placeholder="Optional remarks about the sample">{{ old('remarks', $sample->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Sample
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Sample Information</h6>
                </div>
                <div class="card-body">
                    <p><strong>Sample ID:</strong> {{ $sample->sample_id }}</p>
                    <p><strong>Current Status:</strong> 
                        <span class="badge bg-info">{{ ucwords(str_replace('_', ' ', $sample->status)) }}</span>
                    </p>
                    <p><strong>Evaluation Status:</strong> 
                        <span class="badge bg-success">{{ ucwords($sample->evaluation_status) }}</span>
                    </p>
                    @if($sample->overall_score)
                    <p><strong>Overall Score:</strong> {{ $sample->overall_score }}/10</p>
                    @endif
                    
                    <div class="alert alert-warning">
                        <small>
                            <strong>Note:</strong> Be careful when changing the status. 
                            Make sure it matches the current state of the sample.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection