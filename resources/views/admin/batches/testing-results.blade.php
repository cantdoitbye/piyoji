{{-- resources/views/admin/batches/testing-results.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Testing Results - ' . $batch->batch_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-chart-line me-2"></i>Testing Results
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.batches.index') }}">Batches</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.batches.show', $batch->id) }}">{{ $batch->batch_number }}</a></li>
                            <li class="breadcrumb-item active">Testing Results</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-success me-2" onclick="exportResults()">
                        <i class="fas fa-download me-1"></i>Export Results
                    </button>
                    <a href="{{ route('admin.batches.show', $batch->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Batch
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Summary -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Testing Session Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Batch ID:</strong> {{ $batch->batch_number }}
                            </div>
                            <div class="mb-3">
                                <strong>Session Initiated:</strong> 
                                {{ $testingSession->session_started_at ? $testingSession->session_started_at->format('d/m/Y H:i') : 'Not recorded' }}
                            </div>
                            <div class="mb-3">
                                <strong>Session Completed:</strong> 
                                {{ $testingSession->session_completed_at ? $testingSession->session_completed_at->format('d/m/Y H:i') : 'Not completed' }}
                            </div>
                            <div class="mb-3">
                                <strong>Duration:</strong> 
                                @if($testingSession->session_started_at && $testingSession->session_completed_at)
                                    {{ $testingSession->session_started_at->diffForHumans($testingSession->session_completed_at, true) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Total Samples:</strong> {{ $statistics['total_samples'] }}
                            </div>
                            <div class="mb-3">
                                <strong>Completed Samples:</strong> {{ $statistics['completed_samples'] }}
                            </div>
                            <div class="mb-3">
                                <strong>Initiated By:</strong> {{ $testingSession->initiatedBy->name ?? 'Unknown' }}
                            </div>
                            <div class="mb-3">
                                <strong>Testers:</strong>
                                @if(is_array($testingSession->testers))
                                    @foreach($testingSession->testers as $tester)
                                        <span class="badge bg-secondary me-1">{{ $tester['name'] ?? 'Unknown' }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No testers recorded</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Average Scores</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h4 mb-1 text-primary">{{ round($statistics['average_c_score'] ?? 0, 1) }}</div>
                                <small class="text-muted">C Score</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2">
                                <div class="h4 mb-1 text-info">{{ round($statistics['average_t_score'] ?? 0, 1) }}</div>
                                <small class="text-muted">T Score</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="h4 mb-1 text-warning">{{ round($statistics['average_s_score'] ?? 0, 1) }}</div>
                                <small class="text-muted">S Score</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded p-2">
                                <div class="h4 mb-1 text-success">{{ round($statistics['average_b_score'] ?? 0, 1) }}</div>
                                <small class="text-muted">B Score</small>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-3 pt-3 border-top">
                        <div class="h3 mb-1 text-dark">
                            {{ round((($statistics['average_c_score'] ?? 0) + ($statistics['average_t_score'] ?? 0) + ($statistics['average_s_score'] ?? 0) + ($statistics['average_b_score'] ?? 0)) / 4, 1) }}
                        </div>
                        <small class="text-muted">Overall Average</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sample Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-table me-2"></i>Sample-wise Results</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="toggleTesterDetails">
                                <i class="fas fa-eye me-1"></i>Show Tester Details
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="resultsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>Sample #</th>
                                    <th>Sample ID</th>
                                    <th>Sample Name</th>
                                    <th>C Score</th>
                                    <th>T Score</th>
                                    <th>S Score</th>
                                    <th>B Score</th>
                                    <th>Overall</th>
                                    <th>Grade</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sampleResults as $result)
                                <tr data-sample-id="{{ $result->sample_id }}">
                                    <td>
                                        <span class="badge bg-primary">{{ $result->sample_sequence }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $result->sample->sample_id ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $result->sample->sample_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="score-badge bg-primary">{{ $result->average_scores['c_score'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="score-badge bg-info">{{ $result->average_scores['t_score'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="score-badge bg-warning">{{ $result->average_scores['s_score'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <span class="score-badge bg-success">{{ $result->average_scores['b_score'] ?? 0 }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">{{ $result->overall_average ?? 0 }}</strong>
                                    </td>
                                    <td>
                                        @php
                                            $overall = $result->overall_average ?? 0;
                                            $grade = $overall >= 80 ? 'A' : ($overall >= 70 ? 'B' : ($overall >= 60 ? 'C' : 'D'));
                                            $gradeClass = $overall >= 80 ? 'success' : ($overall >= 70 ? 'warning' : ($overall >= 60 ? 'info' : 'danger'));
                                        @endphp
                                        <span class="badge bg-{{ $gradeClass }}">{{ $grade }}</span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info view-details-btn" 
                                                data-result-id="{{ $result->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Tester Details Row (Initially Hidden) -->
                                <tr class="tester-details-row d-none" data-sample-id="{{ $result->sample_id }}">
                                    <td colspan="10">
                                        <div class="bg-light p-3 rounded">
                                            <h6 class="mb-3"><i class="fas fa-users me-2"></i>Individual Tester Scores</h6>
                                            <div class="row">
                                                @if(is_array($result->tester_results))
                                                    @foreach($result->tester_results as $testerIndex => $testerResult)
                                                    @php
                                                        $testerInfo = (is_array($testingSession->testers) && isset($testingSession->testers[$testerIndex])) 
                                                                    ? $testingSession->testers[$testerIndex] 
                                                                    : ['name' => 'Unknown Tester'];
                                                    @endphp
                                                    <div class="col-md-6 col-lg-4 mb-3">
                                                        <div class="card card-body">
                                                            <h6 class="card-title mb-2">{{ $testerInfo['name'] ?? 'Unknown Tester' }}</h6>
                                                            <div class="row g-2 text-center">
                                                                <div class="col-3">
                                                                    <div class="small text-muted">C</div>
                                                                    <div class="fw-bold">{{ $testerResult['c_score'] ?? 0 }}</div>
                                                                </div>
                                                                <div class="col-3">
                                                                    <div class="small text-muted">T</div>
                                                                    <div class="fw-bold">{{ $testerResult['t_score'] ?? 0 }}</div>
                                                                </div>
                                                                <div class="col-3">
                                                                    <div class="small text-muted">S</div>
                                                                    <div class="fw-bold">{{ $testerResult['s_score'] ?? 0 }}</div>
                                                                </div>
                                                                <div class="col-3">
                                                                    <div class="small text-muted">B</div>
                                                                    <div class="fw-bold">{{ $testerResult['b_score'] ?? 0 }}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                    <div class="col-12">
                                                        <p class="text-muted">No tester results available</p>
                                                    </div>
                                                @endif
                                            </div>
                                            @if($result->sample_remarks)
                                            <div class="mt-3">
                                                <strong>Sample Remarks:</strong>
                                                <p class="mb-0 text-muted">{{ $result->sample_remarks }}</p>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0">Next Actions</h6>
                            <small class="text-muted">Choose what to do with the tested batch</small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-success me-2" onclick="markBatchCompleted()">
                                <i class="fas fa-check-circle me-1"></i>Mark Batch as Completed
                            </button>
                            <button type="button" class="btn btn-outline-warning me-2" onclick="requestRetesting()">
                                <i class="fas fa-redo me-1"></i>Request Retesting
                            </button>
                            <button type="button" class="btn btn-outline-info" onclick="generateReport()">
                                <i class="fas fa-file-pdf me-1"></i>Generate Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sample Details Modal -->
<div class="modal fade" id="sampleDetailsModal" tabindex="-1" aria-labelledby="sampleDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sampleDetailsModalLabel">
                    <i class="fas fa-vial me-2"></i>Sample Testing Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="sampleDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.score-badge {
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-weight: bold;
    min-width: 40px;
    display: inline-block;
    text-align: center;
}

.tester-details-row {
    background-color: #f8f9fa;
}

.card-body {
    font-size: 0.9rem;
}

.table th {
    white-space: nowrap;
}

.table td {
    vertical-align: middle;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle tester details
    document.getElementById('toggleTesterDetails').addEventListener('click', function() {
        const detailsRows = document.querySelectorAll('.tester-details-row');
        const isHidden = detailsRows[0].classList.contains('d-none');
        
        detailsRows.forEach(row => {
            if (isHidden) {
                row.classList.remove('d-none');
            } else {
                row.classList.add('d-none');
            }
        });
        
        this.innerHTML = isHidden ? 
            '<i class="fas fa-eye-slash me-1"></i>Hide Tester Details' : 
            '<i class="fas fa-eye me-1"></i>Show Tester Details';
    });

    // View sample details
    document.querySelectorAll('.view-details-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const resultId = this.dataset.resultId;
            loadSampleDetails(resultId);
        });
    });
});

function loadSampleDetails(resultId) {
    const modal = new bootstrap.Modal(document.getElementById('sampleDetailsModal'));
    const content = document.getElementById('sampleDetailsContent');
    
    content.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
    modal.show();
    
    // Here you would fetch detailed information about the sample
    // For now, we'll just show a placeholder
    setTimeout(() => {
        content.innerHTML = `
            <div class="alert alert-info">
                <h6>Sample Details for Result ID: ${resultId}</h6>
                <p>Detailed sample information would be loaded here...</p>
            </div>
        `;
    }, 500);
}

function exportResults() {
    // Implement export functionality
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Exporting...';
    
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-download me-1"></i>Export Results';
        alert('Export functionality would be implemented here');
    }, 2000);
}

function markBatchCompleted() {
    if (confirm('Are you sure you want to mark this batch as completed? This action cannot be undone.')) {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
        
        // Implement batch completion logic here
        fetch(`{{ route('admin.batches.update-status', $batch->id) }}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                status: 'completed'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessMessage('Batch marked as completed successfully');
                setTimeout(() => {
                    window.location.href = '{{ route("admin.batches.index") }}';
                }, 1500);
            } else {
                showErrorMessage(data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Mark Batch as Completed';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorMessage('Error updating batch status');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Mark Batch as Completed';
        });
    }
}

function requestRetesting() {
    if (confirm('Request retesting for this batch? This will create a new testing session.')) {
        alert('Retesting functionality would be implemented here');
    }
}

function generateReport() {
    const btn = event.target;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Generating...';
    
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf me-1"></i>Generate Report';
        alert('Report generation functionality would be implemented here');
    }, 2000);
}

function showSuccessMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-success alert-dismissible fade show position-fixed';
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}

function showErrorMessage(message) {
    const alert = document.createElement('div');
    alert.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alert.innerHTML = `
        <i class="fas fa-exclamation-triangle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alert);
    
    setTimeout(() => {
        if (alert.parentNode) {
            alert.parentNode.removeChild(alert);
        }
    }, 5000);
}
</script>
@endpush