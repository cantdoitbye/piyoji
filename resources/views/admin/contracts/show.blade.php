@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $title }}</h1>
        <div>
            <a href="{{ route('admin.contracts.edit', $contract->id) }}" class="btn btn-warning btn-sm shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Edit
            </a>
            <a href="{{ route('admin.contracts.index') }}" class="btn btn-secondary btn-sm shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Contract Information -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Contract Details</h6>
                    <div>
                        <span class="badge {{ $contract->status_badge_class }} mr-2">{{ $contract->status_text }}</span>
                        @if($contract->is_expiring)
                            <span class="badge badge-warning">
                                <i class="fas fa-exclamation-triangle"></i> Expiring Soon
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Contract Number:</strong></td>
                                    <td>{{ $contract->contract_number }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Contract Title:</strong></td>
                                    <td>{{ $contract->contract_title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Seller:</strong></td>
                                    <td>
                                        <a href="{{ route('admin.sellers.show', $contract->seller->id) }}" class="text-primary">
                                            {{ $contract->seller->seller_name }}
                                        </a><br>
                                        <small class="text-muted">{{ $contract->seller->tea_estate_name }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Contact Person:</strong></td>
                                    <td>
                                        {{ $contract->seller->contact_person }}<br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope"></i> {{ $contract->seller->email }}<br>
                                            <i class="fas fa-phone"></i> {{ $contract->seller->phone }}
                                        </small>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Effective Date:</strong></td>
                                    <td>{{ $contract->effective_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Expiry Date:</strong></td>
                                    <td>{{ $contract->expiry_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Duration:</strong></td>
                                    <td>{{ $contract->effective_date->diffInDays($contract->expiry_date) }} days</td>
                                </tr>
                                <tr>
                                    <td><strong>Days Remaining:</strong></td>
                                    <td>
                                        @if($contract->status === 'active')
                                            @if($contract->days_remaining !== null)
                                                @if($contract->days_remaining > 0)
                                                    <span class="text-{{ $contract->days_remaining <= 30 ? 'warning' : 'success' }}">
                                                        {{ $contract->days_remaining }} days
                                                    </span>
                                                @else
                                                    <span class="text-danger">Expired</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Total Items:</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ $contract->total_items }}</span>
                                        <span class="badge badge-success">{{ $contract->active_items }} Active</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($contract->terms_and_conditions)
                        <hr>
                        <h6 class="text-primary">Terms and Conditions</h6>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($contract->terms_and_conditions)) !!}
                        </div>
                    @endif

                    @if($contract->remarks)
                        <hr>
                        <h6 class="text-primary">Remarks</h6>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($contract->remarks)) !!}
                        </div>
                    @endif

                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">
                                <strong>Created by:</strong> {{ $contract->createdBy->name ?? 'System' }}<br>
                                <strong>Created at:</strong> {{ $contract->created_at->format('M d, Y H:i A') }}
                            </small>
                        </div>
                        <div class="col-md-6">
                            @if($contract->updatedBy)
                                <small class="text-muted">
                                    <strong>Last updated by:</strong> {{ $contract->updatedBy->name }}<br>
                                    <strong>Updated at:</strong> {{ $contract->updated_at->format('M d, Y H:i A') }}
                                </small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contract Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contract Items</h6>
                </div>
                <div class="card-body">
                    @if($contract->contractItems->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tea Grade</th>
                                        <th>Description</th>
                                        <th>Price per Kg</th>
                                        <th>Quantity Range</th>
                                        <th>Quality Parameters</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contract->contractItems as $item)
                                        <tr class="{{ !$item->is_active ? 'table-secondary' : '' }}">
                                            <td>
                                                <strong>{{ $item->tea_grade }}</strong>
                                            </td>
                                            <td>{{ $item->tea_grade_description ?: '-' }}</td>
                                            <td>
                                                <strong>{{ $item->formatted_price }}</strong>
                                            </td>
                                            <td>{{ $item->quantity_range }}</td>
                                            <td>
                                                @if($item->quality_parameters)
                                                    <small>{{ Str::limit($item->quality_parameters, 50) }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $item->is_active ? 'badge-success' : 'badge-secondary' }}">
                                                    {{ $item->status_text }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($item->special_terms)
                                            <tr class="{{ !$item->is_active ? 'table-secondary' : '' }}">
                                                <td colspan="6">
                                                    <small class="text-muted">
                                                        <strong>Special Terms:</strong> {{ $item->special_terms }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>No contract items found.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($contract->status === 'draft')
                        <button type="button" class="btn btn-success btn-sm btn-block mb-2" id="activateBtn">
                            <i class="fas fa-play"></i> Activate Contract
                        </button>
                    @endif

                    @if($contract->status === 'active')
                        <button type="button" class="btn btn-secondary btn-sm btn-block mb-2" id="cancelBtn">
                            <i class="fas fa-ban"></i> Cancel Contract
                        </button>
                    @endif

                    <a href="{{ route('admin.contracts.edit', $contract->id) }}" class="btn btn-warning btn-sm btn-block mb-2">
                        <i class="fas fa-edit"></i> Edit Contract
                    </a>

                    <button type="button" class="btn btn-info btn-sm btn-block mb-2" id="uploadFileBtn">
                        <i class="fas fa-upload"></i> Upload File
                    </button>

                    @if($contract->uploaded_file_path)
                        <a href="{{ Storage::url($contract->uploaded_file_path) }}" target="_blank" 
                           class="btn btn-outline-info btn-sm btn-block mb-2">
                            <i class="fas fa-download"></i> Download File
                        </a>
                    @endif

                    <button type="button" class="btn btn-outline-primary btn-sm btn-block mb-2" id="priceCalculatorBtn">
                        <i class="fas fa-calculator"></i> Price Calculator
                    </button>

                    <hr>

                    <button type="button" class="btn btn-danger btn-sm btn-block" id="deleteBtn">
                        <i class="fas fa-trash"></i> Delete Contract
                    </button>
                </div>
            </div>

            <!-- Contract Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Summary</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="mb-0">{{ $contract->total_items }}</h4>
                        <small class="text-muted">Total Tea Grades</small>
                    </div>
                    <hr>
                    
                    @if($contract->contractItems->isNotEmpty())
                        <h6 class="text-primary">Price Range</h6>
                        @php
                            $prices = $contract->contractItems->pluck('price_per_kg');
                            $minPrice = $prices->min();
                            $maxPrice = $prices->max();
                        @endphp
                        <p class="mb-1">
                            <strong>Min:</strong> ₹{{ number_format($minPrice, 2) }} per kg<br>
                            <strong>Max:</strong> ₹{{ number_format($maxPrice, 2) }} per kg
                        </p>
                        
                        <hr>
                        <h6 class="text-primary">Available Tea Grades</h6>
                        <div class="d-flex flex-wrap">
                            @foreach($contract->contractItems->where('is_active', true) as $item)
                                <span class="badge badge-primary mr-1 mb-1">{{ $item->tea_grade }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if($contract->status === 'active' && $contract->is_expiring)
                <!-- Expiry Alert -->
                <div class="card border-left-warning shadow mb-4">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Contract Expiring
                                </div>
                                <div class="text-xs mb-0">
                                    This contract will expire in {{ $contract->days_remaining }} days.
                                    Consider renewing or creating a new contract.
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- File Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Contract File</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="uploadForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" class="form-control-file" id="file" name="file" 
                               accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        <small class="form-text text-muted">
                            Supported formats: PDF, DOC, DOCX, XLS, XLSX (Max: 10MB)
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Price Calculator Modal -->
<div class="modal fade" id="calculatorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Price Calculator</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="calc_tea_grade">Tea Grade</label>
                    <select class="form-control" id="calc_tea_grade">
                        <option value="">Select Tea Grade</option>
                        @foreach($contract->contractItems->where('is_active', true) as $item)
                            <option value="{{ $item->tea_grade }}" data-price="{{ $item->price_per_kg }}">
                                {{ $item->tea_grade }} - ₹{{ $item->price_per_kg }}/kg
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="calc_quantity">Quantity (Kg)</label>
                    <input type="number" class="form-control" id="calc_quantity" min="0" step="0.01">
                </div>
                <hr>
                <div id="calculationResult" class="text-center" style="display: none;">
                    <h4 class="text-primary">Total Amount: ₹<span id="totalAmount">0.00</span></h4>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Activate contract
    $('#activateBtn').click(function() {
        if (confirm('Are you sure you want to activate this contract?')) {
            $.ajax({
                url: `/admin/contracts/{{ $contract->id }}/activate`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error activating contract');
                    }
                }
            });
        }
    });

    // Cancel contract
    $('#cancelBtn').click(function() {
        if (confirm('Are you sure you want to cancel this contract?')) {
            $.ajax({
                url: `/admin/contracts/{{ $contract->id }}/cancel`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error cancelling contract');
                    }
                }
            });
        }
    });

    // Delete contract
    $('#deleteBtn').click(function() {
        if (confirm('Are you sure you want to delete this contract? This action cannot be undone.')) {
            $.ajax({
                url: `/admin/contracts/{{ $contract->id }}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '{{ route("admin.contracts.index") }}';
                    } else {
                        alert('Error deleting contract');
                    }
                }
            });
        }
    });

    // Upload file
    $('#uploadFileBtn').click(function() {
        $('#uploadModal').modal('show');
    });

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: `/admin/contracts/{{ $contract->id }}/upload-file`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $('#uploadModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error uploading file');
                }
            },
            error: function() {
                alert('Error uploading file');
            }
        });
    });

    // Price calculator
    $('#priceCalculatorBtn').click(function() {
        $('#calculatorModal').modal('show');
    });

    $('#calc_tea_grade, #calc_quantity').on('input change', function() {
        const selectedOption = $('#calc_tea_grade option:selected');
        const price = parseFloat(selectedOption.data('price')) || 0;
        const quantity = parseFloat($('#calc_quantity').val()) || 0;
        
        if (price > 0 && quantity > 0) {
            const total = price * quantity;
            $('#totalAmount').text(total.toFixed(2));
            $('#calculationResult').show();
        } else {
            $('#calculationResult').hide();
        }
    });
});
</script>
@endpush