@extends('admin.layouts.app')

@section('title', 'Garden Management')
@section('subtitle', 'Manage tea gardens and their tea varieties')

@section('breadcrumb')
    <li class="breadcrumb-item active">Gardens</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.gardens.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Garden
    </a>
@endsection

@section('content')
<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['total'] }}</div>
                <div class="small">Total Gardens</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['active'] }}</div>
                <div class="small">Active</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['inactive'] }}</div>
                <div class="small">Inactive</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['states'] }}</div>
                <div class="small">States</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-secondary text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['with_speciality'] }}</div>
                <div class="small">With Speciality</div>
            </div>
        </div>
    </div>
    <div class="col-xl-2 col-md-4 col-6 mb-3">
        <div class="card bg-dark text-white">
            <div class="card-body text-center">
                <div class="fw-bold fs-4">{{ $statistics['recent'] }}</div>
                <div class="small">Added Recently</div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.gardens.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $filters['search'] ?? '' }}" 
                           placeholder="Search gardens...">
                </div>
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="state" class="form-label">State</label>
                    <select class="form-select" id="state" name="state">
                        <option value="">All States</option>
                        @foreach($states as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['state'] ?? '') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="tea_id" class="form-label">Tea</label>
                    <select class="form-select" id="tea_id" name="tea_id">
                        <option value="">All Teas</option>
                        @foreach($teas as $tea)
                            <option value="{{ $tea->id }}" {{ ($filters['tea_id'] ?? '') == $tea->id ? 'selected' : '' }}>
                                {{ $tea->sub_title }} ({{ $tea->grade }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary flex-fill">
                            <i class="fas fa-search me-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.gardens.index') }}" class="btn btn-outline-secondary flex-fill">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Gardens Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Gardens List</h5>
        <span class="badge bg-primary">{{ $gardens->total() }} Total</span>
    </div>
    <div class="card-body p-0">
        @if($gardens->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Garden Name</th>
                            <th class="text-center" style="min-width: 120px;">
    <i class="fas fa-tag me-1"></i>Garden Type
</th>
<th class="text-center" style="min-width: 150px;">
    <i class="fas fa-map-marker-alt me-1"></i>Location
    <button type="button" class="btn btn-sm btn-link p-0 ms-1" 
            data-bs-toggle="tooltip" 
            title="Garden location coordinates">
        <i class="fas fa-info-circle text-muted"></i>
    </button>
</th>
                            <th>Contact Person</th>
                            <th>Location</th>
                            <th>Tea Varieties</th>
                            <th>Status</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($gardens as $garden)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $garden->garden_name }}</div>
                                    @if($garden->speciality)
                                        <small class="text-muted">
                                            <i class="fas fa-star me-1"></i>{{ $garden->speciality }}
                                        </small>
                                    @endif
                                </td>
                                <td class="text-center">
    <span class="badge bg-{{ $garden->garden_type == 'garden' ? 'success' : 'info' }}">
        {{ $garden->garden_type_text }}
    </span>
</td>
<td class="text-center">
    @if($garden->has_location)
        <div class="location-cell">
            <div class="mb-1">
                <small class="text-muted">{{ $garden->formatted_location }}</small>
            </div>
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-primary btn-sm" 
                        onclick="showLocationModal('{{ $garden->id }}', '{{ $garden->garden_name }}', {{ $garden->latitude }}, {{ $garden->longitude }})">
                    <i class="fas fa-map me-1"></i>View
                </button>
                <a href="{{ $garden->google_maps_url }}" target="_blank" 
                   class="btn btn-outline-info btn-sm"
                   data-bs-toggle="tooltip" title="Open in Google Maps">
                    <i class="fas fa-external-link-alt"></i>
                </a>
            </div>
        </div>
    @else
        <div class="text-muted">
            <i class="fas fa-map-marker-alt me-1"></i>
            <small>Not set</small>
            <br>
            <a href="{{ route('admin.gardens.edit', $garden->id) }}" 
               class="btn btn-outline-secondary btn-sm mt-1">
                <i class="fas fa-plus me-1"></i>Add Location
            </a>
        </div>
    @endif
</td>
                                <td>
                                    <div>{{ $garden->contact_person_name }}</div>
                                    <small class="text-muted">
                                        <i class="fas fa-phone me-1"></i>{{ $garden->mobile_no }}
                                    </small>
                                </td>
                                <td>
                                    <div>{{ $garden->state ?? 'Not specified' }}</div>
                                    @if($garden->altitude)
                                        <small class="text-muted">
                                            <i class="fas fa-mountain me-1"></i>{{ $garden->altitude_text }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($garden->tea_ids && count($garden->tea_ids) > 0)
                                        <span class="badge bg-info">{{ count($garden->tea_ids) }} Tea(s)</span>
                                        <div class="small text-muted mt-1">
                                            {{ Str::limit($garden->tea_names, 50) }}
                                        </div>
                                    @else
                                        <span class="text-muted">No teas assigned</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge {{ $garden->status ? 'status-active' : 'status-inactive' }}">
                                        <i class="fas fa-{{ $garden->status ? 'check-circle' : 'times-circle' }} me-1"></i>
                                        {{ $garden->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.gardens.show', $garden->id) }}" 
                                           class="btn btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.gardens.edit', $garden->id) }}" 
                                           class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                          <a href="{{ route('admin.gardens.invoices.index', $garden) }}" 
           class="btn btn-outline-success" 
           data-bs-toggle="tooltip" title="Manage Invoices">
            <i class="fas fa-file-invoice"></i>
        </a>

          <a href="{{ route('admin.gardens.manage-attachments', $garden) }}" class="btn btn-outline-info" title="Manage Attachments">
            <i class="fas fa-paperclip"></i>
          </a>
                                        <button type="button" class="btn btn-outline-{{ $garden->status ? 'warning' : 'success' }}"
                                                onclick="toggleStatus({{ $garden->id }}, {{ $garden->status ? 'false' : 'true' }})"
                                                title="{{ $garden->status ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $garden->status ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" 
                                                onclick="deleteGarden({{ $garden->id }})" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $gardens->firstItem() }} to {{ $gardens->lastItem() }} of {{ $gardens->total() }} results
                    </div>
                    {{ $gardens->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-seedling fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Gardens Found</h5>
                <p class="text-muted">No gardens match your current filters.</p>
                <a href="{{ route('admin.gardens.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First Garden
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(gardenId, newStatus) {
    if (confirm('Are you sure you want to change the status of this garden?')) {
        fetch(`/admin/gardens/${gardenId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: newStatus })
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
            alert('An error occurred while updating the status.');
        });
    }
}

function deleteGarden(gardenId) {
    if (confirm('Are you sure you want to delete this garden? This action cannot be undone.')) {
        fetch(`/admin/gardens/${gardenId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
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
            alert('An error occurred while deleting the garden.');
        });
    }
}
</script>
@endpush

@push('styles')
<style>
.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-active {
    background-color: #d1edff;
    color: #0c63e4;
}

.status-inactive {
    background-color: #f8d7da;
    color: #721c24;
}
</style>
@endpush