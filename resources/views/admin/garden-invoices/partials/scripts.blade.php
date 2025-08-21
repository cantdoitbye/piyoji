{{-- resources/views/admin/garden-invoices/partials/scripts.blade.php --}}

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize any additional components
    initializeInvoiceFilters();
    initializeStatusActions();
});

// Initialize invoice filters
function initializeInvoiceFilters() {
    // Auto-submit form on filter change
    $('#status, #date_from, #date_to').on('change', function() {
        $(this).closest('form').submit();
    });
    
    // Clear filters button
    $('.btn-clear-filters').on('click', function() {
        const form = $(this).closest('form');
        form.find('input[type="text"], input[type="date"], select').val('');
        form.submit();
    });
}

// Initialize status action handlers
function initializeStatusActions() {
    // Finalize invoice confirmation
    $('form[action*="/finalize"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to finalize this invoice? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Cancel invoice confirmation
    $('form[action*="/cancel"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to cancel this invoice?')) {
            e.preventDefault();
        }
    });
    
    // Delete invoice confirmation
    $('form[action*="/destroy"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to delete this invoice? This action cannot be undone and will delete all associated samples.')) {
            e.preventDefault();
        }
    });
}

// Bulk actions for invoices
function handleBulkActions() {
    const selectedInvoices = $('.invoice-checkbox:checked');
    const action = $('#bulk-action').val();
    
    if (selectedInvoices.length === 0) {
        showToast('Please select at least one invoice', 'warning');
        return;
    }
    
    if (!action) {
        showToast('Please select an action', 'warning');
        return;
    }
    
    const invoiceIds = selectedInvoices.map(function() {
        return $(this).val();
    }).get();
    
    switch(action) {
        case 'export':
            exportSelectedInvoices(invoiceIds);
            break;
        case 'print':
            printSelectedInvoices(invoiceIds);
            break;
        case 'finalize':
            bulkFinalizeInvoices(invoiceIds);
            break;
        default:
            showToast('Invalid action selected', 'error');
    }
}

// Export selected invoices
function exportSelectedInvoices(invoiceIds) {
    showToast('Exporting selected invoices...', 'info');
    
    // Create a form and submit it for export
    const form = $('<form>', {
        method: 'POST',
        action: '#'
    }).append(
        $('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }),
        $('<input>', { type: 'hidden', name: 'invoice_ids', value: JSON.stringify(invoiceIds) })
    );
    
    $('body').append(form);
    form.submit();
    form.remove();
}

// Print selected invoices
function printSelectedInvoices(invoiceIds) {
    if (invoiceIds.length > 5) {
        if (!confirm('You are about to print ' + invoiceIds.length + ' invoices. Continue?')) {
            return;
        }
    }
    
    // Open print preview for each invoice
    invoiceIds.forEach(function(id) {
        const printUrl = `{{ route("admin.gardens.invoices.show", [$garden, ":id"]) }}`.replace(':id', id) + '?print=1';
        window.open(printUrl, '_blank');
    });
}

// Bulk finalize invoices
function bulkFinalizeInvoices(invoiceIds) {
    if (!confirm('Are you sure you want to finalize ' + invoiceIds.length + ' invoice(s)? This action cannot be undone.')) {
        return;
    }
    
    showToast('Finalizing invoices...', 'info');
    
    $.ajax({
        url: '#',
        method: 'POST',
        data: {
            invoice_ids: invoiceIds,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                location.reload(); // Refresh the page to show updated statuses
            } else {
                showToast(response.message, 'error');
            }
        },
        error: function(xhr) {
            const message = xhr.responseJSON?.message || 'Error processing request';
            showToast(message, 'error');
        }
    });
}

// Select all invoices checkbox
function toggleSelectAll(checkbox) {
    const isChecked = checkbox.checked;
    $('.invoice-checkbox').prop('checked', isChecked);
    updateBulkActionsVisibility();
}

// Update bulk actions visibility
function updateBulkActionsVisibility() {
    const selectedCount = $('.invoice-checkbox:checked').length;
    const bulkActions = $('.bulk-actions');
    
    if (selectedCount > 0) {
        bulkActions.show();
        $('.selected-count').text(selectedCount);
    } else {
        bulkActions.hide();
    }
}

// Individual checkbox change handler
$(document).on('change', '.invoice-checkbox', function() {
    updateBulkActionsVisibility();
    
    // Update select all checkbox state
    const totalCheckboxes = $('.invoice-checkbox').length;
    const checkedCheckboxes = $('.invoice-checkbox:checked').length;
    const selectAllCheckbox = $('#select-all');
    
    if (checkedCheckboxes === 0) {
        selectAllCheckbox.prop('indeterminate', false);
        selectAllCheckbox.prop('checked', false);
    } else if (checkedCheckboxes === totalCheckboxes) {
        selectAllCheckbox.prop('indeterminate', false);
        selectAllCheckbox.prop('checked', true);
    } else {
        selectAllCheckbox.prop('indeterminate', true);
    }
});

// Quick search functionality
function quickSearchInvoices() {
    const searchTerm = $('#quick-search').val().toLowerCase();
    const rows = $('.invoice-row');
    
    rows.each(function() {
        const row = $(this);
        const searchableText = row.find('.searchable').text().toLowerCase();
        
        if (searchableText.includes(searchTerm)) {
            row.show();
        } else {
            row.hide();
        }
    });
    
    // Update visible count
    const visibleRows = $('.invoice-row:visible').length;
    $('.visible-count').text(visibleRows);
}

// Real-time search with debounce
let searchTimeout;
$('#quick-search').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(quickSearchInvoices, 300);
});

// Status filter quick buttons
function filterByStatus(status) {
    if (status === '') {
        $('.invoice-row').show();
    } else {
        $('.invoice-row').hide();
        $(`.invoice-row[data-status="${status}"]`).show();
    }
    
    updateVisibleCount();
    
    // Update active button
    $('.status-filter-btn').removeClass('active');
    $(`.status-filter-btn[data-status="${status}"]`).addClass('active');
}

// Update visible count
function updateVisibleCount() {
    const visibleRows = $('.invoice-row:visible').length;
    $('.visible-count').text(visibleRows);
}

// Invoice statistics modal
function showInvoiceStatistics() {
    const modal = $('#statisticsModal');
    
    // Fetch statistics
    $.ajax({
        url: '#',
        method: 'GET',
        success: function(data) {
            updateStatisticsModal(data);
            modal.modal('show');
        },
        error: function() {
            showToast('Error loading statistics', 'error');
        }
    });
}

// Update statistics modal content
function updateStatisticsModal(data) {
    const content = `
        <div class="row">
            <div class="col-md-6">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0">Invoice Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="h4 text-warning">${data.draft_invoices}</div>
                                <small>Draft</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-success">${data.finalized_invoices}</div>
                                <small>Finalized</small>
                            </div>
                            <div class="col-4">
                                <div class="h4 text-danger">${data.cancelled_invoices}</div>
                                <small>Cancelled</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border-success">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">Weight & Package Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Total Weight:</strong> ${data.total_weight} kg
                        </div>
                        <div class="mb-2">
                            <strong>Total Packages:</strong> ${data.total_packages}
                        </div>
                        <div class="mb-2">
                            <strong>Total Samples:</strong> ${data.total_samples}
                        </div>
                        <div>
                            <strong>Average per Invoice:</strong> ${(data.total_weight / data.total_invoices).toFixed(2)} kg
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('#statisticsContent').html(content);
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = `
        top: 20px; 
        right: 20px; 
        z-index: 9999; 
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;
    
    const iconClass = type === 'success' ? 'fa-check-circle' : 
                     type === 'error' ? 'fa-exclamation-circle' : 
                     type === 'warning' ? 'fa-exclamation-triangle' :
                     'fa-info-circle';
    
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas ${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 4000);
}

// Print invoice function
function printInvoice(invoiceId) {
    const printUrl = `{{ route("admin.gardens.invoices.show", [$garden, ":id"]) }}`.replace(':id', invoiceId) + '?print=1';
    window.open(printUrl, '_blank');
}

// Refresh invoice list
function refreshInvoiceList() {
    showToast('Refreshing invoice list...', 'info');
    location.reload();
}

// Export all invoices
function exportAllInvoices() {
    showToast('Preparing export...', 'info');
    
    const form = $('<form>', {
        method: 'POST',
        action: '#'
    }).append(
        $('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' })
    );
    
    $('body').append(form);
    form.submit();
    form.remove();
}

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl + N for new invoice
    if (e.ctrlKey && e.key === 'n') {
        e.preventDefault();
        window.location.href = '{{ route("admin.gardens.invoices.create", $garden) }}';
    }
    
    // Ctrl + R for refresh
    if (e.ctrlKey && e.key === 'r') {
        e.preventDefault();
        refreshInvoiceList();
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        $('#quick-search').val('').trigger('input');
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    
    .invoice-row {
        transition: background-color 0.2s ease;
    }
    
    .invoice-row:hover {
        background-color: #f8f9fa;
    }
    
    .bulk-actions {
        display: none;
        animation: slideInRight 0.3s ease-out;
    }
    
    .status-filter-btn.active {
        background-color: #0d6efd;
        color: white;
    }
    
    #quick-search {
        transition: box-shadow 0.2s ease;
    }
    
    #quick-search:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
`;
document.head.appendChild(style);

// Initialize everything when document is ready
$(document).ready(function() {
    console.log('Garden Invoice Scripts loaded successfully');
    
    // Show keyboard shortcuts help
    console.log('Keyboard shortcuts: Ctrl+N (New Invoice), Ctrl+R (Refresh), Esc (Clear Search)');
});
</script>
@endpush