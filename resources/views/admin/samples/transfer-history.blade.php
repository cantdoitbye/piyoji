{{-- resources/views/admin/samples/transfer-history.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Sample Transfer History')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sample Transfer History</h1>
            <p class="text-muted">{{ $history['sample']->sample_name }} ({{ $history['sample']->sample_id }})</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.samples.show', $history['sample']->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Sample
            </a>
        </div>
    </div>

    <!-- Sample Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Sample Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <p><strong>Sample ID:</strong> {{ $history['sample']->sample_id }}</p>
                    <p><strong>Current Weight:</strong> {{ $history['sample']->sample_weight }} kg</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Current Quantity:</strong> {{ $history['sample']->number_of_samples }} samples</p>
                    <p><strong>Current Batch:</strong> {{ $history['sample']->batch_id ?? 'Not Batched' }}</p>
                </div>
                <div class="col-md-3">
                    <p><strong>Seller:</strong> {{ $history['sample']->seller->seller_name }}</p>
                    <p><strong>Status:</strong> <span class="badge {{ $history['sample']->status_badge_class }}">{{ $history['sample']->status_label }}</span></p>
                </div>
                <div class="col-md-3">
                    <p><strong>Total Transfers:</strong> {{ $history['total_transfers'] }}</p>
                    <p><strong>Evaluation:</strong> <span class="badge bg-success">{{ $history['sample']->evaluation_status_label }}</span></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Transfers FROM this sample -->
        @if($history['transfers_from']->count() > 0)
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-arrow-right me-2"></i>Transfers FROM This Sample</h6>
                </div>
                <div class="card-body">
                    @foreach($history['transfers_from'] as $transfer)
                    <div class="transfer-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-arrow-right text-warning me-1"></i>
                                    To: {{ $transfer->newSample->sample_id }}
                                </h6>
                                <small class="text-muted">{{ $transfer->transfer_date->format('M d, Y H:i') }}</small>
                            </div>
                            <span class="badge {{ $transfer->status_badge_class }}">
                                {{ $transfer->status_label }}
                            </span>
                        </div>
                        
                        <div class="row text-sm">
                            <div class="col-6">
                                <p class="mb-1"><strong>Weight Transferred:</strong> {{ $transfer->transferred_weight }} kg</p>
                                <p class="mb-1"><strong>Quantity Transferred:</strong> {{ $transfer->transferred_quantity }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>Reason:</strong> {{ $transfer->transfer_reason_label }}</p>
                                <p class="mb-1"><strong>By:</strong> {{ $transfer->transferredBy->name }}</p>
                            </div>
                        </div>
                        
                        @if($transfer->transfer_remarks)
                        <div class="mt-2">
                            <small class="text-muted"><strong>Remarks:</strong> {{ $transfer->transfer_remarks }}</small>
                        </div>
                        @endif
                        
                        <div class="mt-2">
                            <a href="{{ route('admin.samples.show', $transfer->newSample->id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View New Sample
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Transfers TO this sample -->
        @if($history['transfers_to']->count() > 0)
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-arrow-left me-2"></i>Transfers TO This Sample</h6>
                </div>
                <div class="card-body">
                    @foreach($history['transfers_to'] as $transfer)
                    <div class="transfer-item mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-arrow-left text-info me-1"></i>
                                    From: {{ $transfer->originalSample->sample_id }}
                                </h6>
                                <small class="text-muted">{{ $transfer->transfer_date->format('M d, Y H:i') }}</small>
                            </div>
                            <span class="badge {{ $transfer->status_badge_class }}">
                                {{ $transfer->status_label }}
                            </span>
                        </div>
                        
                        <div class="row text-sm">
                            <div class="col-6">
                                <p class="mb-1"><strong>Weight Received:</strong> {{ $transfer->transferred_weight }} kg</p>
                                <p class="mb-1"><strong>Quantity Received:</strong> {{ $transfer->transferred_quantity }}</p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><strong>Reason:</strong> {{ $transfer->transfer_reason_label }}</p>
                                <p class="mb-1"><strong>By:</strong> {{ $transfer->transferredBy->name }}</p>
                            </div>
                        </div>
                        
                        @if($transfer->transfer_remarks)
                        <div class="mt-2">
                            <small class="text-muted"><strong>Remarks:</strong> {{ $transfer->transfer_remarks }}</small>
                        </div>
                        @endif
                        
                        <div class="mt-2">
                            <a href="{{ route('admin.samples.show', $transfer->originalSample->id) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>View Original Sample
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- No transfers -->
        @if($history['total_transfers'] == 0)
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-exchange-alt fa-4x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Transfer History</h5>
                    <p class="text-muted">This sample has not been transferred to or from any other batches.</p>
                    
                    @if($history['sample']->batch_group_id && $history['sample']->evaluation_status === 'completed' && $history['sample']->sample_weight > 0.01 && $history['sample']->number_of_samples > 1)
                    <a href="{{ route('admin.samples.transfer-form', $history['sample']->id) }}" 
                       class="btn btn-warning">
                        <i class="fas fa-exchange-alt me-1"></i>Transfer Sample
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection