@extends('admin.layouts.app')

@section('title', 'Manage Attachments - ' . $garden->garden_name)
@section('subtitle', 'Upload and manage garden documents')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.gardens.index') }}">Gardens</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.gardens.show', $garden->id) }}">{{ $garden->garden_name }}</a></li>
    <li class="breadcrumb-item active">Manage Attachments</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.gardens.show', $garden->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Garden
    </a>
@endsection

@section('content')
<!-- Garden Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1">{{ $garden->garden_name }}</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-{{ $garden->garden_type === 'garden' ? 'success' : 'info' }} me-2">
                                {{ $garden->garden_type_text }}
                            </span>
                            {{ $garden->contact_person_name }}
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="mb-1">
                            <i class="fas fa-phone text-muted me-1"></i>
                            {{ $garden->mobile_no }}
                        </div>
                        @if($garden->email)
                        <div class="text-muted">
                            <i class="fas fa-envelope text-muted me-1"></i>
                            {{ $garden->email }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Upload New Files -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-cloud-upload-alt me-2"></i>Upload Documents
                </h5>
                <small class="text-muted">Max 10 files, 10MB each</small>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div id="fileInputsContainer">
                        <!-- Initial file input will be added by JavaScript -->
                    </div>
                    
                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-outline-primary" onclick="addFileInput()">
                            <i class="fas fa-plus me-1"></i> Add More Files
                        </button>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <i class="fas fa-upload me-1"></i> Upload Files
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Existing Attachments -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-paperclip me-2"></i>Attachments
                    <span class="badge bg-secondary ms-2" id="totalAttachments">{{ $garden->attachments ? $garden->attachments->count()  : 0 }}</span>
                </h5>
            </div>
            <div class="card-body" id="attachmentsContainer">
                <!-- Attachments will be loaded here -->
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Garden Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Garden Summary</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6 text-center">
                        <div class="mb-2">
                            <h4 class="text-primary mb-0" id="totalAttachments">{{ $garden->attachments ? $garden->attachments->count() : 0}}</h4>
                            <small class="text-muted">Total Files</small>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="mb-2">
                            <h4 class="text-success mb-0">{{ $garden->verifiedAttachments ? $garden->verifiedAttachments->count() : 0}}</h4>
                            <small class="text-muted">Verified</small>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6 text-center">
                        <div>
                            <h4 class="text-warning mb-0">{{ $garden->unverifiedAttachments ? $garden->unverifiedAttachments->count() : 0 }}</h4>
                            <small class="text-muted">Pending</small>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div>
                            <h4 class="text-info mb-0">
                                {{ number_format($garden->attachments->sum('file_size') / 1024 / 1024, 1) }} MB
                            </h4>
                            <small class="text-muted">Total Size</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Types -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Document Types</h6>
            </div>
            <div class="card-body">
                @php
                    $typeGroups = $garden->attachments->groupBy('document_type_id');
                @endphp
                @if($typeGroups->count() > 0)
                    @foreach($typeGroups as $typeId => $attachments)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>{{ $attachments->first()->document_type_text }}</span>
                            <span class="badge bg-secondary">{{ $attachments->count() }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-file-alt fa-2x mb-2"></i>
                        <p class="mb-0">No documents uploaded yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add New Document Type Modal -->
<div class="modal fade" id="addDocumentTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Document Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addDocumentTypeForm">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3">
                        <label for="documentTypeName" class="form-label">Document Type Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="documentTypeName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="documentTypeDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="documentTypeDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Document Type</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const gardenId = {{ $garden->id }};
let fileIndex = 0;

document.addEventListener('DOMContentLoaded', function() {
    // Add initial file input
    addFileInput();
    // Load existing attachments
    loadAttachments();
});

function addFileInput() {
    if (document.querySelectorAll('.file-input-row').length >= 10) {
        toastr.warning('Maximum 10 files can be uploaded at once');
        return;
    }

    const container = document.getElementById('fileInputsContainer');
    const index = fileIndex++;
    
    const html = `
        <div class="file-input-row mb-3" id="fileRow${index}">
            <div class="row">
                <div class="col-md-4">
                    <input type="file" class="form-control" name="attachments[]" 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.bmp,.txt" required>
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="document_type_ids[]">
                        <option value="">Select Document Type</option>
                        @foreach($documentTypes as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                        <option value="add_new" style="color: #007bff; font-weight: bold;">
                            <i class="fas fa-plus"></i> Add New Document Type
                        </option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="attachment_descriptions[]" 
                           placeholder="Description (optional)">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-outline-danger" onclick="removeFileInput(${index})">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', html);
    
    // Add event listener for document type select
    const select = container.querySelector(`#fileRow${index} select[name="document_type_ids[]"]`);
    select.addEventListener('change', function() {
        if (this.value === 'add_new') {
            this.value = ''; // Reset select
            $('#addDocumentTypeModal').modal('show');
        }
    });
}

function removeFileInput(index) {
    const row = document.getElementById(`fileRow${index}`);
    if (row) {
        row.remove();
    }
}

function clearFiles() {
    document.getElementById('fileInputsContainer').innerHTML = '';
    fileIndex = 0;
    addFileInput();
}

// Handle document type form submission
document.getElementById('addDocumentTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/admin/document-types', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            toastr.success('Document type added successfully');
            $('#addDocumentTypeModal').modal('hide');
            this.reset();
            
            // Update all document type selects
            updateDocumentTypeSelects(data.data);
        } else {
            toastr.error(data.message || 'Error adding document type');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        toastr.error('Error adding document type');
    });
});

function updateDocumentTypeSelects(newDocumentType) {
    const selects = document.querySelectorAll('select[name="document_type_ids[]"]');
    
    selects.forEach(select => {
        // Find the "Add New" option
        const addNewOption = select.querySelector('option[value="add_new"]');
        
        if (addNewOption) {
            // Insert the new option before "Add New"
            const newOption = document.createElement('option');
            newOption.value = newDocumentType.id;
            newOption.textContent = newDocumentType.name;
            
            addNewOption.parentNode.insertBefore(newOption, addNewOption);
        }
    });
}

// Handle file upload
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    // Validate that at least one file is selected
    const fileInputs = form.querySelectorAll('input[type="file"]');
    let hasFiles = false;
    
    fileInputs.forEach(input => {
        if (input.files.length > 0) {
            hasFiles = true;
        }
    });
    
    if (!hasFiles) {
        toastr.error('Please select at least one file to upload');
        return;
    }
    
    // Add types and descriptions
    const types = form.querySelectorAll('select[name="document_type_ids[]"]');
    const descriptions = form.querySelectorAll('input[name="attachment_descriptions[]"]');
    
    types.forEach(select => {
        formData.append('document_type_ids[]', select.value);
    });
    
    descriptions.forEach(input => {
        formData.append('attachment_descriptions[]', input.value);
    });

    showLoading();

    $.ajax({
        url: `/admin/gardens/${gardenId}/attachments`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                clearFiles();
                loadAttachments();
                updateAttachmentCount(response.attachments_count);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                Object.keys(errors).forEach(key => {
                    toastr.error(errors[key][0]);
                });
            } else {
                toastr.error('Error uploading files');
            }
        }
    });
});

function loadAttachments() {
    $.ajax({
        url: `/admin/gardens/${gardenId}/attachments`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                renderAttachments(response.attachments);
            }
        },
        error: function() {
            $('#attachmentsContainer').html('<div class="alert alert-danger">Error loading attachments</div>');
        }
    });
}

function renderAttachments(attachments) {
    const container = $('#attachmentsContainer');
    
    if (attachments.length === 0) {
        container.html(`
            <div class="text-center py-4">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h6 class="text-muted">No attachments uploaded yet</h6>
                <p class="text-muted">Upload some documents to get started</p>
            </div>
        `);
        return;
    }
    
    let html = '<div class="list-group list-group-flush">';
    
    attachments.forEach(attachment => {
        const isImage = attachment.file_type.startsWith('image/');
        const isPdf = attachment.file_type === 'application/pdf';
        const canPreview = isImage || isPdf;
        const verificationBadge = attachment.is_verified ? 
            '<span class="badge bg-success ms-2"><i class="fas fa-check"></i> Verified</span>' :
            '<span class="badge bg-warning ms-2"><i class="fas fa-clock"></i> Pending</span>';
        
        html += `
            <div class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-1">
                            <i class="fas fa-file-${getFileIcon(attachment.file_type)} me-2"></i>
                            <strong>${attachment.file_name}</strong>
                            ${verificationBadge}
                        </div>
                        <small class="text-muted">
                            ${attachment.document_type_text || 'No Type'} • 
                            ${attachment.file_size_formatted} • 
                            Uploaded ${formatDate(attachment.created_at)} by ${attachment.uploaded_by_user.name}
                        </small>
                        ${attachment.description ? `<div class="mt-1"><small class="text-info">${attachment.description}</small></div>` : ''}
                    </div>
                    <div class="btn-group">
                        ${canPreview ? `<a href="${attachment.preview_url}" target="_blank" class="btn btn-sm btn-outline-info" title="Preview"><i class="fas fa-eye"></i></a>` : ''}
                        <a href="${attachment.download_url}" class="btn btn-sm btn-outline-primary" title="Download"><i class="fas fa-download"></i></a>
                        ${!attachment.is_verified ? `<button type="button" class="btn btn-sm btn-outline-success" onclick="verifyAttachment(${attachment.id})" title="Verify"><i class="fas fa-check"></i></button>` : ''}
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="editAttachment(${attachment.id}, '${attachment.document_type_id}', '${attachment.description || ''}')" title="Edit"><i class="fas fa-edit"></i></button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteAttachment(${attachment.id})" title="Delete"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.html(html);
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) return 'image';
    if (mimeType === 'application/pdf') return 'pdf';
    if (mimeType.includes('word')) return 'word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'excel';
    return 'alt';
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString();
}

function verifyAttachment(attachmentId) {
    if (!confirm('Are you sure you want to verify this attachment?')) return;
    
    $.ajax({
        url: `/admin/gardens/${gardenId}/attachments/${attachmentId}/verify`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                loadAttachments();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error verifying attachment');
        }
    });
}

function editAttachment(attachmentId, currentTypeId, currentDescription) {
    const modal = `
        <div class="modal fade" id="editAttachmentModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Attachment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editAttachmentForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Document Type</label>
                                <select class="form-select" name="document_type_id" required>
                                    @foreach($documentTypes as $id => $name)
                                    <option value="{{ $id }}" ${currentTypeId == '{{ $id }}' ? 'selected' : ''}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3">${currentDescription}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal
    $('#editAttachmentModal').remove();
    
    // Add new modal
    $('body').append(modal);
    $('#editAttachmentModal').modal('show');
    
    // Handle form submission
    $('#editAttachmentForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: `/admin/gardens/${gardenId}/attachments/${attachmentId}`,
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    $('#editAttachmentModal').modal('hide');
                    loadAttachments();
                } else {
                    toastr.error(response.message);
                }
            },
            error: function() {
                toastr.error('Error updating attachment');
            }
        });
    });
}

function deleteAttachment(attachmentId) {
    if (!confirm('Are you sure you want to delete this attachment? This action cannot be undone.')) return;
    
    $.ajax({
        url: `/admin/gardens/${gardenId}/attachments/${attachmentId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                loadAttachments();
                updateAttachmentCount();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Error deleting attachment');
        }
    });
}

function updateAttachmentCount(count = null) {
    if (count === null) {
        // Reload to get current count
        $.ajax({
            url: `/admin/gardens/${gardenId}/attachments`,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#totalAttachments').text(response.attachments.length);
                }
            }
        });
    } else {
        $('#totalAttachments').text(count);
    }
}

// Loading helpers
function showLoading() {
    $('body').append('<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
}

function hideLoading() {
    $('#loadingOverlay').remove();
}
</script>
@endpush