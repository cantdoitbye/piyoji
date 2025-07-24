@extends('admin.layouts.app')

@section('title', 'Assign Buyers - Sample #' . $sample->sample_id)

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Assign Sample to Buyers
                </h5>
            </div>
            <div class="card-body">
                <!-- Sample Details Summary -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="text-muted">Sample Information</h6>
                        <p><strong>Sample ID:</strong> {{ $sample->sample_id }}</p>
                        <p><strong>Sample Name:</strong> {{ $sample->sample_name }}</p>
                        <p><strong>Seller:</strong> {{ $sample->seller->seller_name }}</p>
                        <p><strong>Overall Score:</strong> 
                            <span class="badge bg-success">{{ $sample->overall_score }}/10</span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Evaluation Details</h6>
                        <p><strong>Aroma:</strong> {{ $sample->aroma_score }}/10</p>
                        <p><strong>Liquor:</strong> {{ $sample->liquor_score }}/10</p>
                        <p><strong>Appearance:</strong> {{ $sample->appearance_score }}/10</p>
                        <p><strong>Evaluated By:</strong> {{ $sample->evaluatedBy->name }}</p>
                    </div>
                </div>

                <hr>

                <!-- Buyer Assignment Form -->
                <form id="buyerAssignmentForm" action="{{ route('admin.samples.store-buyer-assignments', $sample->id) }}" method="POST">
                    @csrf
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Select Buyers</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addBuyerBtn">
                            <i class="fas fa-plus me-1"></i>Add Buyer
                        </button>
                    </div>

                    <div id="buyerAssignments">
                        <!-- Dynamic buyer assignment rows will be added here -->
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-secondary me-2">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                            <i class="fas fa-check me-1"></i>Assign to Buyers
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar with Buyer List & Existing Assignments -->
    <div class="col-lg-4">
        <!-- Available Buyers -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-list me-2"></i>Available Buyers</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Buyer Name</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($buyers as $buyer)
                            <tr>
                                <td>{{ $buyer->buyer_name }}</td>
                                <td>
                                    <span class="badge {{ $buyer->buyer_type === 'big' ? 'bg-primary' : 'bg-info' }}">
                                        {{ $buyer->buyer_type_text }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success select-buyer-btn" 
                                            data-buyer-id="{{ $buyer->id }}" 
                                            data-buyer-name="{{ $buyer->buyer_name }}">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No active buyers found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Existing Assignments -->
        @if($existingAssignments->count() > 0)
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Current Assignments</h6>
            </div>
            <div class="card-body">
                @foreach($existingAssignments as $assignment)
                <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                    <div>
                        <strong>{{ $assignment->buyer->buyer_name }}</strong><br>
                        <small class="text-muted">
                            Assigned: {{ $assignment->assigned_at->format('M d, Y H:i') }}
                        </small>
                        @if($assignment->assignment_remarks)
                        <br><small class="text-info">{{ $assignment->assignment_remarks }}</small>
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="{{ $assignment->dispatch_status_badge }}">
                            {{ $assignment->dispatch_status_text }}
                        </span>
                        @if($assignment->dispatch_status === 'awaiting_dispatch')
                        <br>
                        <button type="button" class="btn btn-sm btn-outline-danger mt-1 remove-assignment-btn" 
                                data-assignment-id="{{ $assignment->id }}">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Help Section -->
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Assignment Guidelines</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <small>
                        <strong>Tips:</strong><br>
                        • Select multiple buyers for better market coverage<br>
                        • Add specific remarks for each buyer if needed<br>
                        • Big buyers typically require larger sample quantities<br>
                        • Only approved samples can be assigned
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let buyerIndex = 0;
let selectedBuyers = new Set();
let buyersData = @json($buyers); // Pass buyers data to JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const addBuyerBtn = document.getElementById('addBuyerBtn');
    const buyerAssignments = document.getElementById('buyerAssignments');
    const submitBtn = document.getElementById('submitBtn');

    // Add buyer row
    addBuyerBtn.addEventListener('click', function() {
        addBuyerRow();
    });

    // Select buyer from sidebar
    document.querySelectorAll('.select-buyer-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const buyerId = this.dataset.buyerId;
            const buyerName = this.dataset.buyerName;
            
            if (!selectedBuyers.has(buyerId)) {
                addBuyerRow(buyerId, buyerName);
            }
        });
    });

    // Remove assignment
    document.querySelectorAll('.remove-assignment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const assignmentId = this.dataset.assignmentId;
            removeAssignment(assignmentId);
        });
    });

    function addBuyerRow(selectedBuyerId = null, selectedBuyerName = null) {
        // Create buyer options HTML
        let buyerOptions = '<option value="">Select Buyer</option>';
        buyersData.forEach(buyer => {
            buyerOptions += `<option value="${buyer.id}">${buyer.buyer_name} (${buyer.buyer_type === 'big' ? 'Big Buyer' : 'Small Buyer'})</option>`;
        });

        // Create the buyer row HTML
        const html = `
            <div class="buyer-assignment-row border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Buyer *</label>
                        <select name="buyers[${buyerIndex}][buyer_id]" class="form-select buyer-select" required>
                            ${buyerOptions}
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <div class="input-group">
                            <textarea name="buyers[${buyerIndex}][remarks]" class="form-control" rows="2" 
                                      placeholder="Optional remarks for this buyer"></textarea>
                            <button type="button" class="btn btn-outline-danger remove-buyer-btn">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const div = document.createElement('div');
        div.innerHTML = html;
        const row = div.firstElementChild;
        
        buyerAssignments.appendChild(row);

        // Set selected buyer if provided
        if (selectedBuyerId) {
            const select = row.querySelector('.buyer-select');
            select.value = selectedBuyerId;
            selectedBuyers.add(selectedBuyerId);
            updateBuyerOptions();
        }

        // Add event listeners
        const select = row.querySelector('.buyer-select');
        const removeBtn = row.querySelector('.remove-buyer-btn');

        select.addEventListener('change', function() {
            updateSelectedBuyers();
            updateBuyerOptions();
            toggleSubmitButton();
        });

        removeBtn.addEventListener('click', function() {
            const buyerId = select.value;
            if (buyerId) {
                selectedBuyers.delete(buyerId);
            }
            row.remove();
            updateBuyerOptions();
            toggleSubmitButton();
        });

        buyerIndex++;
        toggleSubmitButton();
    }

    function updateSelectedBuyers() {
        selectedBuyers.clear();
        document.querySelectorAll('.buyer-select').forEach(select => {
            if (select.value) {
                selectedBuyers.add(select.value);
            }
        });
    }

    function updateBuyerOptions() {
        document.querySelectorAll('.buyer-select').forEach(select => {
            const currentValue = select.value;
            Array.from(select.options).forEach(option => {
                if (option.value && option.value !== currentValue) {
                    option.disabled = selectedBuyers.has(option.value);
                }
            });
        });

        // Update sidebar buttons
        document.querySelectorAll('.select-buyer-btn').forEach(btn => {
            const buyerId = btn.dataset.buyerId;
            btn.disabled = selectedBuyers.has(buyerId);
            btn.innerHTML = selectedBuyers.has(buyerId) ? 
                '<i class="fas fa-check"></i>' : '<i class="fas fa-plus"></i>';
        });
    }

    function toggleSubmitButton() {
        const hasValidBuyers = document.querySelectorAll('.buyer-select').length > 0 &&
                              Array.from(document.querySelectorAll('.buyer-select')).some(s => s.value);
        submitBtn.disabled = !hasValidBuyers;
    }

    function removeAssignment(assignmentId) {
        if (confirm('Are you sure you want to remove this assignment?')) {
            fetch(`{{ url('admin/samples/assignments/') }}/${assignmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while removing the assignment');
            });
        }
    }
});
</script>
@endpush