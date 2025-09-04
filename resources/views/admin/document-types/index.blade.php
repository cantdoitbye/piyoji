@extends('admin.layouts.app')

@section('title', 'Document Types')
@section('subtitle', 'Manage document type categories')

@section('breadcrumb')
    <li class="breadcrumb-item active">Document Types</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.document-types.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add Document Type
    </a>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        @if($documentTypes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documentTypes as $documentType)
                        <tr>
                            <td>
                                <strong>{{ $documentType->name }}</strong>
                            </td>
                            <td>
                                <span class="text-muted">{{ $documentType->description ?? 'No description' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $documentType->status_badge }}">
                                    {{ $documentType->status_text }}
                                </span>
                            </td>
                            <td>{{ $documentType->sort_order }}</td>
                            <td>
                                <small class="text-muted">{{ $documentType->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.document-types.edit', $documentType->id) }}" 
                                       class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-{{ $documentType->status ? 'warning' : 'success' }}"
                                            onclick="toggleStatus({{ $documentType->id }}, {{ $documentType->status ? 'false' : 'true' }})"
                                            title="{{ $documentType->status ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $documentType->status ? 'pause' : 'play' }}"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteDocumentType({{ $documentType->id }})" title="Delete">
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
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing {{ $documentTypes->firstItem() }} to {{ $documentTypes->lastItem() }} 
                    of {{ $documentTypes->total() }} results
                </div>
                {{ $documentTypes->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Document Types Found</h5>
                <p class="text-muted">Create your first document type to get started.</p>
                <a href="{{ route('admin.document-types.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add Document Type
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleStatus(documentTypeId, newStatus) {
    if (confirm('Are you sure you want to change the status of this document type?')) {
        fetch(`/admin/document-types/${documentTypeId}/toggle-status`, {
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

function deleteDocumentType(documentTypeId) {
    if (confirm('Are you sure you want to delete this document type? This action cannot be undone and may affect existing attachments.')) {
        fetch(`/admin/document-types/${documentTypeId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
                location.reload();
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            toastr.error('An error occurred while deleting the document type.');
        });
    }
}
</script>
@endpush