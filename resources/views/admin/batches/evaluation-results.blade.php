{{-- resources/views/admin/batches/evaluation-results.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Batch Evaluation Results - ' . $batch->batch_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Batch Evaluation Results</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.batches.index') }}">Batches</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.batches.show', $batch->id) }}">{{ $batch->batch_number }}</a></li>
                    <li class="breadcrumb-item active">Evaluation Results</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.batches.show', $batch->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Batch
            </a>
            <a href="{{ route('admin.batches.evaluation-form', $batch->id) }}" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i>Edit Evaluation
            </a>
        </div>
    </div>

    <!-- Batch & Evaluation Summary -->
    <div class="row mb-4">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Evaluation Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Batch Number:</td>
                                    <td class="text-primary">{{ $batch->batch_number }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Batch Date:</td>
                                    <td>{{ $batch->batch_date->format('M d, Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Total Samples:</td>
                                    <td>{{ $evaluation->total_samples }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Evaluation Date:</td>
                                    <td>{{ $evaluation->evaluation_date->format('M d, Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <td class="fw-bold">Status:</td>
                                    <td><span class="badge {{ $evaluation->status_badge_class }}">{{ $evaluation->status_label }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Started By:</td>
                                    <td>{{ $evaluation->evaluationStartedBy->name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Completed By:</td>
                                    <td>{{ $evaluation->evaluationCompletedBy->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Completed At:</td>
                                    <td>{{ $evaluation->evaluation_completed_at ? $evaluation->evaluation_completed_at->format('M d, Y H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overall Score Card -->
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Overall Result</h5>
                </div>
                <div class="card-body text-center">
                    @php
                        $averageScores = $evaluation->average_scores;
                        $totalScore = $averageScores['c_score'] + $averageScores['t_score'] + $averageScores['s_score'] + $averageScores['b_score'];
                    @endphp
                    
                    <h2 class="display-4 mb-0 
                        @if($totalScore >= 300) text-success
                        @elseif($totalScore >= 200) text-warning
                        @else text-danger
                        @endif">
                        {{ number_format($totalScore, 1) }}
                    </h2>
                    <p class="text-muted">/ 400</p>
                    
                    <span class="badge fs-6 
                        @if($totalScore >= 300) bg-success
                        @elseif($totalScore >= 200) bg-warning
                        @else bg-danger
                        @endif">
                        {{ $evaluation->batch_acceptance }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Average Scores Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Average Scores Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-info mb-1">{{ number_format($averageScores['c_score'], 1) }}</h4>
                                <small class="text-muted">Color (C)</small>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-info" style="width: {{ $averageScores['c_score'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-success mb-1">{{ number_format($averageScores['t_score'], 1) }}</h4>
                                <small class="text-muted">Taste (T)</small>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" style="width: {{ $averageScores['t_score'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-warning mb-1">{{ number_format($averageScores['s_score'], 1) }}</h4>
                                <small class="text-muted">Strength (S)</small>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-warning" style="width: {{ $averageScores['s_score'] }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border rounded p-3">
                                <h4 class="text-danger mb-1">{{ number_format($averageScores['b_score'], 1) }}</h4>
                                <small class="text-muted">Body (B)</small>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div class="progress-bar bg-danger" style="width: {{ $averageScores['b_score'] }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Individual Tester Results -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Individual Tester Results</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Tester</th>
                            <th>Samples</th>
                            <th class="text-center">C Score</th>
                            <th class="text-center">T Score</th>
                            <th class="text-center">S Score</th>
                            <th class="text-center">B Score</th>
                            <th class="text-center">Total</th>
                            <th>Color Shade</th>
                            <th>Brand</th>
                            <th class="text-center">Result</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluation->testerEvaluations as $testerEval)
                        <tr>
                            <td>
                                <strong>{{ $testerEval->tester_name }}</strong>
                                @if($testerEval->testerPoc->designation)
                                <br><small class="text-muted">{{ $testerEval->testerPoc->designation }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $testerEval->total_samples }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-info">{{ $testerEval->c_score }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-success">{{ $testerEval->t_score }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-warning">{{ $testerEval->s_score }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-danger">{{ $testerEval->b_score }}</span>
                            </td>
                            <td class="text-center">
                                <strong class="
                                    @if($testerEval->total_score >= 300) text-success
                                    @elseif($testerEval->total_score >= 200) text-warning
                                    @else text-danger
                                    @endif">
                                    {{ $testerEval->total_score }}
                                </strong>
                                <small class="text-muted">/400</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $testerEval->color_shade }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $testerEval->brand }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $testerEval->result_badge_class }}">
                                    {{ $testerEval->evaluation_result }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $testerEval->remarks ?: 'NORMAL' }}</small>
                            </td>
                        </tr>
                        @endforeach
                        
                        @if($evaluation->testerEvaluations->isEmpty())
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                <br>No tester evaluations found.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Overall Remarks -->
    @if($evaluation->overall_remarks)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-comment me-2"></i>Overall Remarks</h5>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $evaluation->overall_remarks }}</p>
        </div>
    </div>
    @endif

    <!-- Statistics Summary -->
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-success">{{ $evaluation->testerEvaluations->where('evaluation_result', 'Accepted')->count() }}</h5>
                    <small class="text-muted">Accepted Evaluations</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-warning">{{ $evaluation->testerEvaluations->where('evaluation_result', 'Normal')->count() }}</h5>
                    <small class="text-muted">Normal Evaluations</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="text-danger">{{ $evaluation->testerEvaluations->where('evaluation_result', 'Rejected')->count() }}</h5>
                    <small class="text-muted">Rejected Evaluations</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.display-4 {
    font-size: 3rem;
    font-weight: 300;
    line-height: 1.2;
}

.progress {
    background-color: #e9ecef;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.badge {
    font-size: 0.875em;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
}
</style>
@endpush