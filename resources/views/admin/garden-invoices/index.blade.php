@extends('admin.layouts.app')

@section('title', 'Invoice Management - ' . $garden->garden_name)

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="text-primary mb-1">
                        <i class="fas fa-file-invoice me-2"></i>Invoice Management
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.gardens.index') }}">Gardens</a></li>
                            <li class="breadcrumb-item">{{ $garden->garden_name }}</li>
                            <li class="breadcrumb-item active">Invoices</li>
                        </ol>
                    </nav>
                </div>
                <div class="btn-group">
                    <a href="{{ route('admin.gardens.show', $garden) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Garden
                    </a>
                    <a href="{{ route('admin.gardens.invoices.create', $garden) }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Create Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Garden Info Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-info">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="text-info mb-2">
                                <i class="fas fa-seedling me-2"></i>{{ $garden->garden_name }}
                                <span class="badge bg-{{ $garden->garden_type == 'garden' ? 'success' : 'info' }} ms-2">
                                    {{ $garden->garden_type_text }}
                                </span>
                            </h6>
                            <p class="text-muted mb-0">{{ $garden->full_address }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="row text-center">
                                <div class="col-3">
                                    <div class="h5 text-primary mb-1">{{ $statistics['total_invoices'] }}</div>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-3">
                                    <div class="h5 text-warning mb-1">{{ $statistics['draft_invoices'] }}</div>
                                    <small class="text-muted">Draft</small>
                                </div>
                                <div class="col-3">
                                    <div class="h5 text-success mb-1">{{ $statistics['finalized_invoices'] }}</div>
                                    <small class="text-muted">Finalized</small>
                                </div>
                                <div class="col-3">
                                    <div class="h5 text-info mb-1">{{ number_format($statistics['total_weight'], 2) }}kg</div>
                                    <small class="text-muted">Total Weight</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.gardens.invoices.index', $garden) }}" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach(\App\Models\GardenInvoice::getStatusOptions() as $key => $label)
                                    <option value="{{ $key }}" {{ ($filters['status'] ?? '') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ $filters['search'] ?? '' }}" 
                                   placeholder="Invoice number, mark name...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" 
                                   value="{{ $filters['date_from'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" 
                                   value="{{ $filters['date_to'] ?? '' }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('admin.gardens.invoices.index', $garden) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-list me-2"></i>Invoices
                        <span class="badge bg-secondary ms-2">{{ $invoices->total() }}</span>
                    </h6>
                </div>
                <div class="card-body">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Invoice Number</th>
                                        <th>Status</th>
                                        <th>Packaging Date</th>
                                        <th>Bags/Packages</th>
                                        <th>Sample Details</th>
                                        <th>Total Weight</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $invoice->full_invoice_number }}</div>
                                                <small class="text-muted">{{ $invoice->mark_name }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $invoice->status_badge_class }}">
                                                    {{ $invoice->status_text }}
                                                </span>
                                            </td>
                                            <td>{{ $invoice->packaging_date->format('d M, Y') }}</td>
                                            <td class="text-center">{{ $invoice->bags_packages }}</td>
                                            <td>
                                                <div class="small">
                                                    <strong>Weight:</strong> {{ $invoice->sample_weight }}kg<br>
                                                    <strong>Sets:</strong> {{ $invoice->number_of_sets }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-primary">
                                                    {{ number_format($invoice->total_invoice_weight, 3) }}kg
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    {{ $invoice->creator->name }}<br>
                                                    <span class="text-muted">{{ $invoice->created_at->format('d M, Y') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.gardens.invoices.show', [$garden, $invoice]) }}" 
                                                       class="btn btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if($invoice->canEdit())
                                                        <a href="{{ route('admin.gardens.invoices.edit', [$garden, $invoice]) }}" 
                                                           class="btn btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif

                                                    @if($invoice->canFinalize())
                                                        <form action="{{ route('admin.gardens.invoices.finalize', [$garden, $invoice]) }}" 
                                                              method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to finalize this invoice?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-outline-success" title="Finalize">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    @if($invoice->canCancel())
                                                        <form action="{{ route('admin.gardens.invoices.cancel', [$garden, $invoice]) }}" 
                                                              method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to cancel this invoice?')">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" class="btn btn-outline-danger" title="Cancel">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $invoices->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">No invoices found</h6>
                            <p class="text-muted">Create your first invoice to get started.</p>
                            <a href="{{ route('admin.gardens.invoices.create', $garden) }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Create Invoice
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.garden-invoices.partials.scripts')
@endsection