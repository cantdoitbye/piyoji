@extends('admin.layouts.app')

@section('title', 'Batch Evaluation - ' . $batch->batch_number)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Batch Evaluation</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.batches.index') }}">Batches</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.batches.show', $batch->id) }}">{{ $batch->batch_number }}</a></li>
                    <li class="breadcrumb-item active">Evaluation</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.batches.show', $batch->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Batch
            </a>
        </div>
    </div>

    <!-- Batch Information -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Batch Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Batch Number:</strong>
                            <p class="text-primary">{{ $batch->batch_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Batch Date:</strong>
                            <p>{{ $batch->batch_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Total Samples:</strong>
                            <p class="text-info">{{ $batch->total_samples }}</p>
                        </div>
                        <div class="col-md-3">
                            <strong>Status:</strong>
                            <span class="badge {{ $batch->status_badge_class }}">{{ $batch->status_label }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Form -->
    <form action="{{ route('admin.batches.store-evaluation', $batch->id) }}" method="POST" id="evaluationForm">
        @csrf
        
        <!-- Testers Section -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-users me-2"></i>Tester Evaluations</h5>
                <button type="button" class="btn btn-primary btn-sm" onclick="addTester()">
                    <i class="fas fa-plus me-1"></i>Add Tester
                </button>
            </div>
            <div class="card-body">
                <div id="testersContainer">
                    @if($evaluation && $evaluation->testerEvaluations->count() > 0)
                        @foreach($evaluation->testerEvaluations as $index => $testerEval)
                        <div class="tester-evaluation mb-4" data-index="{{ $index }}">
                            @include('admin.batches._tester_evaluation_form', [
                                'index' => $index,
                                'testerEval' => $testerEval,
                                'testers' => $testers
                            ])
                        </div>
                        @endforeach
                    @else
                        <div class="tester-evaluation mb-4" data-index="0">
                            @include('admin.batches._tester_evaluation_form', [
                                'index' => 0,
                                'testerEval' => null,
                                'testers' => $testers
                            ])
                        </div>
                    @endif
                </div>
                
                @if($testers->isEmpty())
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No testers found. Please add testers in POC management first.
                </div>
                @endif
            </div>
        </div>

        <!-- Overall Remarks -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-comment me-2"></i>Overall Remarks</h5>
            </div>
            <div class="card-body">
                <textarea name="overall_remarks" class="form-control" rows="3" placeholder="Enter overall batch evaluation remarks">{{ old('overall_remarks', $evaluation->overall_remarks ?? '') }}</textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="row">
            <div class="col-12">
                <div class="text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-check me-2"></i>Complete Evaluation
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Tester Template (Hidden) -->
<div id="testerTemplate" style="display: none;">
    @include('admin.batches._tester_evaluation_form', [
        'index' => '__INDEX__',
        'testerEval' => null,
        'testers' => $testers
    ])
</div>

@endsection

@push('styles')
<style>
.score-input {
    text-align: center;
    font-weight: bold;
}

.score-controls {
    display: flex;
    align-items: center;
    gap: 5px;
}

.score-btn {
    width: 30px;
    height: 30px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.tester-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    transition: border-color 0.3s;
}

.tester-card:hover {
    border-color: #007bff;
}

.number-grid {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 5px;
    margin-top: 10px;
}

.number-btn {
    aspect-ratio: 1;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.number-btn:hover {
    background: #f8f9fa;
}

.number-btn.selected {
    background: #007bff;
    color: white;
    border-color: #007bff;
}
</style>
@endpush

@push('scripts')
<script>
let testerIndex = {{ $evaluation && $evaluation->testerEvaluations->count() > 0 ? $evaluation->testerEvaluations->count() : 1 }};

function addTester() {
    const template = document.getElementById('testerTemplate').innerHTML;
    const newTesterHtml = template.replace(/__INDEX__/g, testerIndex);
    
    const newTesterDiv = document.createElement('div');
    newTesterDiv.className = 'tester-evaluation mb-4';
    newTesterDiv.setAttribute('data-index', testerIndex);
    newTesterDiv.innerHTML = newTesterHtml;
    
    document.getElementById('testersContainer').appendChild(newTesterDiv);
    testerIndex++;
}

function removeTester(index) {
    const testerDiv = document.querySelector(`[data-index="${index}"]`);
    if (testerDiv && document.querySelectorAll('.tester-evaluation').length > 1) {
        testerDiv.remove();
    }
}

function updateScore(category, index, value) {
    const input = document.getElementById(`${category}_score_${index}`);
    const currentValue = parseInt(input.value) || 0;
    const newValue = Math.max(0, Math.min(100, currentValue + value));
    input.value = newValue;
    updateScoreDisplay(category, index, newValue);
}

function updateScoreDisplay(category, index, value) {
    const display = document.getElementById(`${category}_display_${index}`);
    if (display) {
        display.textContent = value;
    }
    
    // Update number grid selection
    const buttons = document.querySelectorAll(`[data-category="${category}"][data-index="${index}"] .number-btn`);
    buttons.forEach(btn => {
        btn.classList.remove('selected');
        if (parseInt(btn.textContent) === value) {
            btn.classList.add('selected');
        }
    });
}

function selectScore(category, index, value) {
    const input = document.getElementById(`${category}_score_${index}`);
    input.value = value;
    updateScoreDisplay(category, index, value);
}

function incrementScore(category, index, amount = 1) {
    updateScore(category, index, amount);
}

function decrementScore(category, index, amount = 1) {
    updateScore(category, index, -amount);
}

// Initialize score displays on page load
document.addEventListener('DOMContentLoaded', function() {
    const scoreInputs = document.querySelectorAll('.score-input');
    scoreInputs.forEach(input => {
        const [category, , index] = input.id.split('_');
        const value = parseInt(input.value) || 0;
        updateScoreDisplay(category, index, value);
    });
});

// Form validation
document.getElementById('evaluationForm').addEventListener('submit', function(e) {
    const testers = document.querySelectorAll('.tester-evaluation');
    let valid = true;
    let errors = [];
    
    testers.forEach((tester, idx) => {
        const testerSelect = tester.querySelector('select[name$="[tester_poc_id]"]');
        const samplesInput = tester.querySelector('input[name$="[total_samples]"]');
        
        if (!testerSelect.value) {
            valid = false;
            errors.push(`Tester ${idx + 1}: Please select a tester`);
        }
        
        if (!samplesInput.value || parseInt(samplesInput.value) <= 0) {
            valid = false;
            errors.push(`Tester ${idx + 1}: Please enter valid number of samples`);
        }
    });
    
    if (!valid) {
        e.preventDefault();
        alert('Please fix the following errors:\n' + errors.join('\n'));
    }
});
</script>
@endpush