@extends('admin.layouts.app')

@section('title', 'Manage Attachments - ' . $buyer->buyer_name)
@section('subtitle', 'Upload and manage buyer documents')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.buyers.index') }}">Buyers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.buyers.show', $buyer->id) }}">{{ $buyer->buyer_name }}</a></li>
    <li class="breadcrumb-item active">Manage Attachments</li>
@endsection

@section('header-actions')
    <a href="{{ route('admin.buyers.show', $buyer->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Buyer
    </a>
@endsection

@section('content')
<!-- Buyer Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="mb-1">{{ $buyer->buyer_name }}</h5>
                        <p class="mb-0 text-muted">
                            <span class="badge bg-{{ $buyer->buyer_type === 'big' ? 'primary' : 'secondary' }} me-2">
                                {{ $buyer->buyer_type_text }}
                            </span>
                            {{ $buyer->contact_person }} • {{ $buyer->email }}
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-info" onclick="refreshAttachments()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                            <button type="button" class="btn btn-outline-success" onclick="showBulkUploadModal()">
                                <i class="fas fa-upload me-1"></i>Bulk Upload
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload New Files Section -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Upload New Documents
        </h5>
    </div>
    <div class="card-body">
        <form id="uploadForm" enctype="multipart/form-data">
            @csrf
            <div class="upload-area" id="uploadArea">
                <div class="text-center py-4">
                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                    <h5>Upload Documents</h5>
                    <p class="text-muted mb-3">
                        Drag and drop files here or click to browse<br>
                        <small>Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max: 10MB each)</small>
                    </p>
                    <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-folder-open me-2"></i>Choose Files
                    </button>
                    <input type="file" id="fileInput" name="attachments[]" multiple 
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif,.bmp,.txt"
                           style="display: none;" onchange="handleFileSelection(this)">
                </div>
            </div>
            
            <!-- File Preview Area -->
            <div id="filePreviewArea" style="display: none;" class="mt-4">
                <h6><i class="fas fa-files-o me-2"></i>Selected Files <span id="fileCount" class="badge bg-primary">0</span></h6>
                <div id="fileList" class="row mt-3"></div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-upload me-2"></i>Upload Files
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="clearFiles()">
                        <i class="fas fa-times me-2"></i>Clear All
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Existing Attachments -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-archive me-2"></i>Existing Attachments 
            <span class="badge bg-info ms-2" id="totalAttachments">{{ $buyer->attachments->count() }}</span>
        </h5>
        <div class="btn-group btn-group-sm">
            <button type="button" class="btn btn-outline-secondary" onclick="toggleView('grid')">
                <i class="fas fa-th-large"></i> Grid
            </button>
            <button type="button" class="btn btn-outline-secondary active" onclick="toggleView('list')">
                <i class="fas fa-list"></i> List
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="attachmentsContainer">
            <!-- Attachments will be loaded here -->
        </div>
        
        <!-- Empty State -->
        <div id="emptyState" class="text-center py-5" style="display: none;">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No documents uploaded yet</h5>
            <p class="text-muted">Upload your first document using the form above.</p>
        </div>
    </div>
</div>

<!-- Edit Attachment Modal -->
<div class="modal fade" id="editAttachmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Attachment Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAttachmentForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select class="form-select" name="document_type" required>
                            @foreach($documentTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" 
                                  placeholder="Brief description (optional)"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="downloadFromPreview" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download me-1"></i>Download
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 0.375rem;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.upload-area:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.upload-area.dragover {
    border-color: #0d6efd;
    background-color: #e3f2fd;
}

.file-item {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    margin-bottom: 1rem;
    position: relative;
    background-color: #f8f9fa;
}

.file-item .remove-file {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

.attachment-card {
    transition: transform 0.2s ease;
    border: 1px solid #dee2e6;
}

.attachment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.attachment-list-item {
    border-bottom: 1px solid #eee;
    padding: 1rem 0;
}

.attachment-list-item:last-child {
    border-bottom: none;
}

.file-icon {
    font-size: 2rem;
}

.document-type-badge {
    font-size: 0.75rem;
}

.view-mode-grid .attachment-item {
    margin-bottom: 1rem;
}

.view-mode-list .attachment-item {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script>
let selectedFiles = [];
let fileIndex = 0;
let viewMode = 'list';
let buyerId = {{ $buyer->id }};

$(document).ready(function() {
    initializePage();
    setupDragAndDrop();
    loadAttachments();
});

function initializePage() {
    // Upload form submission
    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();
        uploadFiles();
    });

    // Edit attachment form
    $('#editAttachmentForm').on('submit', function(e) {
        e.preventDefault();
        updateAttachment();
    });
}

function setupDragAndDrop() {
    const uploadArea = document.getElementById('uploadArea');
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        uploadArea.addEventListener(eventName, () => uploadArea.classList.remove('dragover'), false);
    });

    uploadArea.addEventListener('drop', handleDrop, false);
    uploadArea.addEventListener('click', () => document.getElementById('fileInput').click());

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    }
}

function handleFileSelection(input) {
    handleFiles(input.files);
}

function handleFiles(files) {
    Array.from(files).forEach(file => {
        if (validateFile(file)) {
            addFileToPreview(file);
        }
    });
    updateFileInput();
}

function validateFile(file) {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'text/plain'
    ];

    if (file.size > maxSize) {
        toastr.error(`File "${file.name}" is too large (max 10MB)`);
        return false;
    }

    if (!allowedTypes.includes(file.type)) {
        toastr.error(`File "${file.name}" has unsupported format`);
        return false;
    }

    return true;
}

function addFileToPreview(file) {
    const index = fileIndex++;
    selectedFiles.push({ file: file, index: index });

    const fileList = document.getElementById('fileList');
    const filePreviewArea = document.getElementById('filePreviewArea');
    
    filePreviewArea.style.display = 'block';
    const fileIcon = getFileIcon(file.type);
    const fileSize = formatFileSize(file.size);

    const fileItem = document.createElement('div');
    fileItem.className = 'col-md-6 mb-3';
    fileItem.innerHTML = `
        <div class="file-item" data-file-index="${index}">
            <button type="button" class="btn btn-sm btn-outline-danger remove-file" onclick="removeFile(${index})">
                <i class="fas fa-times"></i>
            </button>
            
            <div class="row">
                <div class="col-3">
                    <div class="file-icon text-center">${fileIcon}</div>
                </div>
                <div class="col-9">
                    <h6 class="mb-1" title="${file.name}">${file.name.length > 20 ? file.name.substring(0, 20) + '...' : file.name}</h6>
                    <small class="text-muted">${fileSize}</small>
                </div>
            </div>
            
            <div class="mt-3">
                <div class="mb-2">
                    <label class="form-label small">Document Type</label>
                    <select class="form-select form-select-sm" name="attachment_types[]">
                        <option value="other">Other Document</option>
                        <option value="license">Business License</option>
                        <option value="agreement">Agreement</option>
                        <option value="certificate">Certificate</option>
                        <option value="registration">Registration Document</option>
                        <option value="tax_document">Tax Document</option>
                        <option value="bank_statement">Bank Statement</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small">Description</label>
                    <textarea class="form-control form-control-sm" name="attachment_descriptions[]" 
                              rows="2" placeholder="Brief description (optional)"></textarea>
                </div>
            </div>
        </div>
    `;

    fileList.appendChild(fileItem);
    updateFileCount();
}

function removeFile(index) {
    selectedFiles = selectedFiles.filter(item => item.index !== index);
    document.querySelector(`[data-file-index="${index}"]`).parentElement.remove();
    updateFileInput();
    updateFileCount();
    
    if (selectedFiles.length === 0) {
        document.getElementById('filePreviewArea').style.display = 'none';
    }
}

function clearFiles() {
    selectedFiles = [];
    document.getElementById('fileList').innerHTML = '';
    document.getElementById('filePreviewArea').style.display = 'none';
    document.getElementById('fileInput').value = '';
    updateFileCount();
}

function updateFileInput() {
    const fileInput = document.getElementById('fileInput');
    const dt = new DataTransfer();
    
    selectedFiles.forEach(item => {
        dt.items.add(item.file);
    });
    
    fileInput.files = dt.files;
}

function updateFileCount() {
    document.getElementById('fileCount').textContent = selectedFiles.length;
}

function getFileIcon(mimeType) {
    if (mimeType.startsWith('image/')) {
        return '<i class="fas fa-image text-info"></i>';
    } else if (mimeType === 'application/pdf') {
        return '<i class="fas fa-file-pdf text-danger"></i>';
    } else if (mimeType.includes('word')) {
        return '<i class="fas fa-file-word text-primary"></i>';
    } else if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) {
        return '<i class="fas fa-file-excel text-success"></i>';
    } else {
        return '<i class="fas fa-file text-secondary"></i>';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function uploadFiles() {
    if (selectedFiles.length === 0) {
        toastr.error('Please select files to upload');
        return;
    }

    const formData = new FormData();
    const form = document.getElementById('uploadForm');
    
    // Add CSRF token
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Add files and their metadata
    selectedFiles.forEach((item, index) => {
        formData.append('attachments[]', item.file);
    });
    
    // Add types and descriptions
    const types = form.querySelectorAll('select[name="attachment_types[]"]');
    const descriptions = form.querySelectorAll('textarea[name="attachment_descriptions[]"]');
    
    types.forEach(select => {
        formData.append('attachment_types[]', select.value);
    });
    
    descriptions.forEach(textarea => {
        formData.append('attachment_descriptions[]', textarea.value);
    });

    showLoading();

    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments`,
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
}

function loadAttachments() {
    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments`,
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
    const emptyState = $('#emptyState');
    
    if (!attachments || attachments.length === 0) {
        container.hide();
        emptyState.show();
        return;
    }
    
    emptyState.hide();
    container.show();
    
    let html = '';
    
    if (viewMode === 'grid') {
        html = '<div class="row">';
        attachments.forEach(attachment => {
            html += renderAttachmentGrid(attachment);
        });
        html += '</div>';
    } else {
        html = '<div class="list-group list-group-flush">';
        attachments.forEach(attachment => {
            html += renderAttachmentList(attachment);
        });
        html += '</div>';
    }
    
    container.html(html);
}

function renderAttachmentGrid(attachment) {
    const fileIcon = getFileIcon(attachment.file_type);
    return `
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card attachment-card" data-attachment-id="${attachment.id}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="file-icon me-2">${fileIcon}</div>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                    data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="${attachment.download_url}" target="_blank">
                                    <i class="fas fa-download me-2"></i>Download</a></li>
                                ${attachment.preview_url ? `<li><button class="dropdown-item" onclick="previewAttachment(${attachment.id})">
                                    <i class="fas fa-eye me-2"></i>Preview</button></li>` : ''}
                                <li><button class="dropdown-item text-warning" onclick="editAttachment(${attachment.id})">
                                    <i class="fas fa-edit me-2"></i>Edit Details</button></li>
                                ${!attachment.is_verified ? `<li><button class="dropdown-item text-success" onclick="verifyAttachment(${attachment.id})">
                                    <i class="fas fa-check me-2"></i>Verify</button></li>` : ''}
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item text-danger" onclick="deleteAttachment(${attachment.id})">
                                    <i class="fas fa-trash me-2"></i>Delete</button></li>
                            </ul>
                        </div>
                    </div>
                    
                    <h6 class="card-title mb-1" title="${attachment.file_name}">
                        ${attachment.file_name.length > 25 ? attachment.file_name.substring(0, 25) + '...' : attachment.file_name}
                    </h6>
                    
                    <p class="small text-muted mb-2">
                        <span class="badge bg-light text-dark document-type-badge">${attachment.document_type_text}</span><br>
                        <small>${attachment.file_size_formatted}</small>
                    </p>
                    
                    ${attachment.description ? `<p class="small text-muted mb-2">${attachment.description.substring(0, 50)}${attachment.description.length > 50 ? '...' : ''}</p>` : ''}
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">${new Date(attachment.created_at).toLocaleDateString()}</small>
                        ${attachment.is_verified ? '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Verified</span>' 
                                                 : '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Pending</span>'}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function renderAttachmentList(attachment) {
    const fileIcon = getFileIcon(attachment.file_type);
    return `
        <div class="list-group-item attachment-list-item" data-attachment-id="${attachment.id}">
            <div class="row align-items-center">
                <div class="col-auto">
                    <div class="file-icon">${fileIcon}</div>
                </div>
                <div class="col">
                    <h6 class="mb-1">${attachment.file_name}</h6>
                    <p class="mb-1">
                        <span class="badge bg-light text-dark document-type-badge me-2">${attachment.document_type_text}</span>
                        <small class="text-muted">${attachment.file_size_formatted}</small>
                    </p>
                    ${attachment.description ? `<p class="mb-0 small text-muted">${attachment.description}</p>` : ''}
                </div>
                <div class="col-auto">
                    ${attachment.is_verified ? '<span class="badge bg-success me-2"><i class="fas fa-check me-1"></i>Verified</span>' 
                                             : '<span class="badge bg-warning me-2"><i class="fas fa-clock me-1"></i>Pending</span>'}
                    <small class="text-muted me-3">${new Date(attachment.created_at).toLocaleDateString()}</small>
                </div>
                <div class="col-auto">
                    <div class="btn-group btn-group-sm">
                        <a href="${attachment.download_url}" class="btn btn-outline-info" target="_blank" title="Download">
                            <i class="fas fa-download"></i>
                        </a>
                        ${attachment.preview_url ? `<button class="btn btn-outline-secondary" onclick="previewAttachment(${attachment.id})" title="Preview">
                            <i class="fas fa-eye"></i>
                        </button>` : ''}
                        <button class="btn btn-outline-warning" onclick="editAttachment(${attachment.id})" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        ${!attachment.is_verified ? `<button class="btn btn-outline-success" onclick="verifyAttachment(${attachment.id})" title="Verify">
                            <i class="fas fa-check"></i>
                        </button>` : ''}
                        <button class="btn btn-outline-danger" onclick="deleteAttachment(${attachment.id})" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function toggleView(mode) {
    viewMode = mode;
    $('.btn-group .btn').removeClass('active');
    $(`button[onclick="toggleView('${mode}')"]`).addClass('active');
    loadAttachments();
}

function refreshAttachments() {
    loadAttachments();
    toastr.info('Attachments refreshed');
}

function editAttachment(attachmentId) {
    // Find attachment data
    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const attachment = response.attachments.find(a => a.id === attachmentId);
                if (attachment) {
                    $('#editAttachmentForm select[name="document_type"]').val(attachment.document_type);
                    $('#editAttachmentForm textarea[name="description"]').val(attachment.description || '');
                    $('#editAttachmentForm').attr('data-attachment-id', attachmentId);
                    $('#editAttachmentModal').modal('show');
                }
            }
        }
    });
}

function updateAttachment() {
    const form = $('#editAttachmentForm');
    const attachmentId = form.attr('data-attachment-id');
    
    showLoading();
    
    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments/${attachmentId}`,
        method: 'PUT',
        data: form.serialize(),
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                $('#editAttachmentModal').modal('hide');
                loadAttachments();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error updating attachment');
        }
    });
}

function verifyAttachment(attachmentId) {
    if (!confirm('Are you sure you want to verify this attachment?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments/${attachmentId}/verify`,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                loadAttachments();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error verifying attachment');
        }
    });
}

function deleteAttachment(attachmentId) {
    if (!confirm('Are you sure you want to delete this attachment? This action cannot be undone.')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments/${attachmentId}`,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                toastr.success(response.message);
                loadAttachments();
                updateAttachmentCount();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            hideLoading();
            toastr.error('Error deleting attachment');
        }
    });
}

function previewAttachment(attachmentId) {
    // Find attachment data for preview
    $.ajax({
        url: `/admin/buyers/${buyerId}/attachments`,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                const attachment = response.attachments.find(a => a.id === attachmentId);
                if (attachment && attachment.preview_url) {
                    $('#downloadFromPreview').attr('href', attachment.download_url);
                    
                    if (attachment.is_image) {
                        $('#previewContent').html(`<img src="${attachment.preview_url}" class="img-fluid" alt="${attachment.file_name}">`);
                    } else if (attachment.is_pdf) {
                        $('#previewContent').html(`<iframe src="${attachment.preview_url}" width="100%" height="500px" frameborder="0"></iframe>`);
                    } else {
                        $('#previewContent').html(`<p class="text-muted">Preview not available for this file type.</p>`);
                    }
                    
                    $('#previewModal').modal('show');
                } else {
                    toastr.error('Preview not available for this file');
                }
            }
        }
    });
}

function updateAttachmentCount(count = null) {
    if (count === null) {
        // Reload to get current count
        $.ajax({
            url: `/admin/buyers/${buyerId}/attachments`,
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

function showBulkUploadModal() {
    toastr.info('Use the upload form above to upload multiple files at once');
}

// Loading helpers
function showLoading() {
    // You can implement a loading spinner here
    $('body').append('<div id="loadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.5); z-index: 9999;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>');
}

function hideLoading() {
    $('#loadingOverlay').remove();
}
</script>
@endpush