{{-- resources/views/admin/batches/sample-testing.blade.php --}}
@extends('admin.layouts.app')

@section('title', 'Sample Testing - ' . $batch->batch_number)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-flask me-2"></i>Sample Testing
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.batches.index') }}">Batches</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.batches.show', $batch->id) }}">{{ $batch->batch_number }}</a></li>
                            <li class="breadcrumb-item active">Sample Testing</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <a href="{{ route('admin.batches.show', $batch->id) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Batch
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Testing Progress</h6>
                        <span class="badge bg-primary">{{ $testingSession->progress }}% Complete</span>
                    </div>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $testingSession->progress }}%" 
                             aria-valuenow="{{ $testingSession->progress }}" 
                             aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-3">
                            <small class="text-muted">Current Sample</small>
                            <div class="fw-bold">{{ $testingSession->current_sample_index + 1 }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Samples</small>
                            <div class="fw-bold">{{ $testingSession->total_samples }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Completed</small>
                            <div class="fw-bold">{{ $testingSession->completedResults->count() }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Remaining</small>
                            <div class="fw-bold">{{ $testingSession->pendingResults->count() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Sample Testing -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-vial me-2"></i>
                            Sample {{ $currentSampleResult->sample_sequence }} of {{ $testingSession->total_samples }}
                        </h5>
                        <span class="badge bg-warning">Testing in Progress</span>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Sample Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Sample Details</h6>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <strong>Sample ID:</strong> {{ $currentSampleResult->sample->sample_id }}<br>
                                        <strong>Sample Name:</strong> {{ $currentSampleResult->sample->sample_name }}<br>
                                        <strong>Batch Position:</strong> {{ $currentSampleResult->sample_sequence }}
                                    </div>
                                    <div class="col-sm-6">
                                        <strong>Seller:</strong> {{ $currentSampleResult->sample->seller->seller_name ?? 'N/A' }}<br>
                                        <strong>Weight:</strong> {{ $currentSampleResult->sample->sample_weight }}kg<br>
                                        <strong>Arrival Date:</strong> {{ $currentSampleResult->sample->arrival_date->format('d/m/Y') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Testing Form -->
                    <form id="sampleTestingForm">
                        @csrf
                        <input type="hidden" name="sample_result_id" value="{{ $currentSampleResult->id }}">
                        
                        <!-- Testers Section -->
                        <div class="mb-4">
                            <h6><i class="fas fa-users me-2"></i>Tester Evaluations</h6>
                            <div id="testersContainer">
                                @foreach($testingSession->testers as $index => $tester)
                                <div class="tester-card p-3 mb-4" data-tester-index="{{ $index }}">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user me-2"></i>{{ $tester['name'] }}
                                            @if(!empty($tester['designation']))
                                                <small class="text-muted">({{ $tester['designation'] }})</small>
                                            @endif
                                        </h6>
                                        <span class="badge bg-secondary">Tester {{ $index + 1 }}</span>
                                    </div>
                                    
                                    <input type="hidden" name="tester_results[{{ $index }}][tester_id]" value="{{ $tester['id'] }}">
                                    
                                    <!-- Score Sections -->
                                    <div class="row">
                                        <!-- C Score (Color/Appearance) -->
                                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-4">
                                            <label class="form-label fw-bold">C Score (Color) <span class="text-danger">*</span></label>
                                            <div class="score-section">
                                                <div class="score-controls d-flex align-items-center justify-content-center mb-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn me-2" 
                                                            onclick="decrementScore('c', {{ $index }})">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" id="c_score_{{ $index }}" name="tester_results[{{ $index }}][c_score]" 
                                                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                                                           value="0" onchange="calculateTotalScore({{ $index }})" required>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn ms-2" 
                                                            onclick="incrementScore('c', {{ $index }})">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <span class="ms-3 fw-bold text-primary fs-5" id="c_display_{{ $index }}">0</span>
                                                </div>
                                                
                                                <!-- Responsive Number Grid -->
                                                <div class="number-grid-container">
                                                    <div class="number-grid" data-category="c" data-index="{{ $index }}">
                                                        @for($i = 0; $i <= 100; $i += 10)
                                                        <button type="button" class="number-btn" onclick="selectScore('c', {{ $index }}, {{ $i }})">{{ $i }}</button>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- T Score (Taste) -->
                                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-4">
                                            <label class="form-label fw-bold">T Score (Taste) <span class="text-danger">*</span></label>
                                            <div class="score-section">
                                                <div class="score-controls d-flex align-items-center justify-content-center mb-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn me-2" 
                                                            onclick="decrementScore('t', {{ $index }})">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" id="t_score_{{ $index }}" name="tester_results[{{ $index }}][t_score]" 
                                                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                                                           value="0" onchange="calculateTotalScore({{ $index }})" required>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn ms-2" 
                                                            onclick="incrementScore('t', {{ $index }})">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <span class="ms-3 fw-bold text-success fs-5" id="t_display_{{ $index }}">0</span>
                                                </div>
                                                
                                                <div class="number-grid-container">
                                                    <div class="number-grid" data-category="t" data-index="{{ $index }}">
                                                        @for($i = 0; $i <= 100; $i += 10)
                                                        <button type="button" class="number-btn" onclick="selectScore('t', {{ $index }}, {{ $i }})">{{ $i }}</button>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- S Score (Strength) -->
                                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-4">
                                            <label class="form-label fw-bold">S Score (Strength) <span class="text-danger">*</span></label>
                                            <div class="score-section">
                                                <div class="score-controls d-flex align-items-center justify-content-center mb-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn me-2" 
                                                            onclick="decrementScore('s', {{ $index }})">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" id="s_score_{{ $index }}" name="tester_results[{{ $index }}][s_score]" 
                                                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                                                           value="0" onchange="calculateTotalScore({{ $index }})" required>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn ms-2" 
                                                            onclick="incrementScore('s', {{ $index }})">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <span class="ms-3 fw-bold text-warning fs-5" id="s_display_{{ $index }}">0</span>
                                                </div>
                                                
                                                <div class="number-grid-container">
                                                    <div class="number-grid" data-category="s" data-index="{{ $index }}">
                                                        @for($i = 0; $i <= 100; $i += 10)
                                                        <button type="button" class="number-btn" onclick="selectScore('s', {{ $index }}, {{ $i }})">{{ $i }}</button>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- B Score (Body/Liquor) -->
                                        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-4">
                                            <label class="form-label fw-bold">B Score (Body) <span class="text-danger">*</span></label>
                                            <div class="score-section">
                                                <div class="score-controls d-flex align-items-center justify-content-center mb-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn me-2" 
                                                            onclick="decrementScore('b', {{ $index }})">
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                    <input type="number" id="b_score_{{ $index }}" name="tester_results[{{ $index }}][b_score]" 
                                                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                                                           value="0" onchange="calculateTotalScore({{ $index }})" required>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm score-btn ms-2" 
                                                            onclick="incrementScore('b', {{ $index }})">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                    <span class="ms-3 fw-bold text-danger fs-5" id="b_display_{{ $index }}">0</span>
                                                </div>
                                                
                                                <div class="number-grid-container">
                                                    <div class="number-grid" data-category="b" data-index="{{ $index }}">
                                                        @for($i = 0; $i <= 100; $i += 10)
                                                        <button type="button" class="number-btn" onclick="selectScore('b', {{ $index }}, {{ $i }})">{{ $i }}</button>
                                                        @endfor
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Fields Row 1 -->
                                    <div class="row mb-3">
                                        <!-- Total Samples -->
                                        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">Total Samples Evaluated <span class="text-danger">*</span></label>
                                            <input type="number" name="tester_results[{{ $index }}][total_samples]" 
                                                   class="form-control" min="1" max="100" 
                                                   value="1" placeholder="Enter number" required>
                                        </div>

                                        <!-- Color Shade -->
                                        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">Color Shade</label>
                                            <select name="tester_results[{{ $index }}][color_shade]" class="form-select">
                                                <option value="RED" selected>RED</option>
                                                <option value="GOLDEN">GOLDEN</option>
                                                <option value="AMBER">AMBER</option>
                                                <option value="DARK">DARK</option>
                                                <option value="LIGHT">LIGHT</option>
                                                <option value="BRIGHT">BRIGHT</option>
                                            </select>
                                        </div>

                                        <!-- Brand -->
                                        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">Brand</label>
                                            <select name="tester_results[{{ $index }}][brand]" class="form-select">
                                                <option value="WB" selected>WB</option>
                                                <option value="PREMIUM">PREMIUM</option>
                                                <option value="STANDARD">STANDARD</option>
                                                <option value="EXPORT">EXPORT</option>
                                                <option value="DUST">DUST</option>
                                                <option value="FANNING">FANNING</option>
                                            </select>
                                        </div>

                                        <!-- Tester Remarks -->
                                        <div class="col-lg-3 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">Tester Remarks</label>
                                            <input type="text" name="tester_results[{{ $index }}][remarks]" class="form-control" 
                                                   value="NORMAL" placeholder="Enter remarks">
                                        </div>
                                    </div>

                                    <!-- Tea Evaluation Categories Row 2 -->
                                    <div class="row mb-3">
                                        <!-- LEAF Grade -->
                                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">LEAF Grade</label>
                                            <select name="tester_results[{{ $index }}][leaf_grade]" class="form-select">
                                                <option value="">Select Leaf Grade</option>
                                                <option value="A">A</option>
                                                <option value="B+">B+</option>
                                                <option value="BB+">BB+</option>
                                                <option value="B">B</option>
                                                <option value="BB-">BB-</option>
                                                <option value="B-">B-</option>
                                                <option value="C">C</option>
                                                <option value="L.Brown">L.Brown</option>
                                                <option value="Brown">Brown</option>
                                                <option value="Choppy">Choppy</option>
                                            </select>
                                        </div>

                                        <!-- REPORT -->
                                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">REPORT</label>
                                            <select name="tester_results[{{ $index }}][report]" class="form-select">
                                                <option value="">Select Report</option>
                                                <option value="ML">ML</option>
                                                <option value="ML_">ML_</option>
                                                <option value="COL">COL</option>
                                                <option value="COL_">COL_</option>
                                                <option value="BR">BR</option>
                                                <option value="BR_">BR_</option>
                                                <option value="PREF">PREF</option>
                                            </select>
                                        </div>

                                        <!-- CH-DEFECT -->
                                        <div class="col-lg-4 col-md-6 col-sm-12 mb-3">
                                            <label class="form-label fw-bold">CH-DEFECT</label>
                                            <select name="tester_results[{{ $index }}][ch_defect]" class="form-select">
                                                <option value="">Select CH-Defect</option>
                                                <option value="Good CH">Good CH</option>
                                                <option value="Raw">Raw</option>
                                                <option value="Green">Green</option>
                                                <option value="Smoky">Smoky</option>
                                                <option value="HighFired">HighFired</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Total Score Display -->
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="alert alert-info border-0 shadow-sm">
                                                <div class="row align-items-center">
                                                    <div class="col-md-4">
                                                        <strong class="fs-5">Total Score: </strong>
                                                        <span id="total_score_{{ $index }}" class="fw-bold fs-4 text-primary">0</span>
                                                        <span class="text-muted">/ 400</span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <strong class="fs-5">Score Result: </strong>
                                                        <span id="score_result_{{ $index }}" class="fw-bold font-monospace text-info">0-0-0-0</span>
                                                    </div>
                                                    <div class="col-md-4 text-md-end">
                                                        <strong class="fs-5">Status: </strong>
                                                        <span id="status_{{ $index }}" class="badge bg-secondary fs-6">Pending</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Sample Remarks -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Sample Remarks (Optional)</label>
                            <textarea name="sample_remarks" class="form-control" rows="3" 
                                      placeholder="Enter any specific remarks for this sample..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                <i class="fas fa-save me-1"></i>Save Draft
                            </button>
                            <button type="submit" class="btn btn-success btn-lg" disabled>
                                <i class="fas fa-exclamation-triangle me-1"></i>Complete All Scores
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Session Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Session Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Batch:</strong> {{ $batch->batch_number }}
                    </div>
                    <div class="mb-2">
                        <strong>Session Started:</strong> 
                        {{ $testingSession->session_started_at ? $testingSession->session_started_at->format('d/m/Y H:i') : 'Not started' }}
                    </div>
                    <div class="mb-2">
                        <strong>Initiated By:</strong> {{ $testingSession->initiatedBy->name }}
                    </div>
                    <div>
                        <strong>Testers:</strong>
                        <ul class="list-unstyled mt-1 mb-0">
                            @foreach($testingSession->testers as $tester)
                            <li><i class="fas fa-user text-muted me-1"></i>{{ $tester['name'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sample Navigation -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-list me-2"></i>Sample Progress</h6>
                </div>
                <div class="card-body">
                    <div class="sample-navigation" style="max-height: 400px; overflow-y: auto;">
                        @foreach($testingSession->sampleResults as $result)
                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 rounded 
                                    {{ $result->id === $currentSampleResult->id ? 'bg-primary text-white' : 
                                       ($result->status === 'completed' ? 'bg-light' : '') }}">
                            <span>
                                @if($result->status === 'completed')
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                @elseif($result->id === $currentSampleResult->id)
                                    <i class="fas fa-arrow-right me-1"></i>
                                @else
                                    <i class="fas fa-circle text-muted me-1"></i>
                                @endif
                                Sample {{ $result->sample_sequence }}
                            </span>
                            <small>{{ $result->sample->sample_id }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Enhanced Responsive Styles */
.tester-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    background: #ffffff;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tester-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.score-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    height: 100%;
}

.score-input {
    width: 80px;
    font-size: 18px;
    font-weight: bold;
    border: 2px solid #dee2e6;
    border-radius: 8px;
}

.score-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.score-btn {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #6c757d;
    transition: all 0.2s ease;
}

.score-btn:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    transform: scale(1.1);
}

.number-grid-container {
    max-width: 100%;
    overflow-x: auto;
    padding: 5px;
}

.number-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(35px, 1fr));
    gap: 6px;
    max-width: 100%;
    margin: 0 auto;
}

.number-btn {
    aspect-ratio: 1;
    min-width: 35px;
    height: 35px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.number-btn:hover {
    background: #e9ecef;
    border-color: #007bff;
    transform: translateY(-2px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.number-btn.selected {
    background: #007bff;
    color: white;
    border-color: #007bff;
    transform: scale(1.1);
    box-shadow: 0 2px 8px rgba(0,123,255,0.4);
}

.sample-navigation {
    font-size: 0.9rem;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .score-controls {
        justify-content: center !important;
        margin-bottom: 15px;
    }
    
    .score-input {
        width: 70px;
        font-size: 16px;
    }
    
    .score-btn {
        width: 30px;
        height: 30px;
    }
    
    .number-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }
    
    .number-btn {
        min-width: 35px;
        height: 35px;
        font-size: 11px;
    }
    
    .tester-card {
        margin-bottom: 20px;
    }
}

@media (max-width: 576px) {
    .number-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 4px;
    }
    
    .score-section {
        padding: 10px;
    }
    
    .alert {
        text-align: center;
    }
    
    .col-md-6 {
        margin-bottom: 10px;
    }
}

/* High DPI Screens */
@media (min-width: 1200px) {
    .number-grid {
        grid-template-columns: repeat(6, 1fr);
        max-width: 300px;
        gap: 10px;
    }
    
    .number-btn {
        min-width: 40px;
        height: 40px;
        font-size: 14px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Score manipulation functions
function adjustScore(testerIndex, scoreType, adjustment) {
    const input = document.querySelector(`input[name="tester_results[${testerIndex}][${scoreType}]"]`);
    let currentValue = parseInt(input.value) || 0;
    let newValue = currentValue + adjustment;
    
    // Ensure value stays within bounds
    newValue = Math.max(0, Math.min(100, newValue));
    
    selectScore(scoreType, testerIndex, newValue);
}

function selectScore(scoreType, testerIndex, value) {
    const input = document.querySelector(`input[name="tester_results[${testerIndex}][${scoreType}]"]`);
    input.value = value;
    
    // Update number grid selection
    const testerCard = input.closest('.tester-card');
    const numberButtons = testerCard.querySelectorAll(`[data-category="${scoreType}"] .number-btn`);
    numberButtons.forEach(btn => {
        btn.classList.remove('selected');
        if (parseInt(btn.textContent.trim()) == value) {
            btn.classList.add('selected');
        }
    });
    
    // Calculate total score for this tester
    calculateTotalScore(testerIndex);
}

function incrementScore(scoreType, testerIndex) {
    adjustScore(testerIndex, scoreType, 1);
}

function decrementScore(scoreType, testerIndex) {
    adjustScore(testerIndex, scoreType, -1);
}

// Enhanced calculation function
function calculateTotalScore(index) {
    try {
        const indexStr = String(index);
        
        const cScore = parseInt(document.getElementById(`c_score_${indexStr}`)?.value) || 0;
        const tScore = parseInt(document.getElementById(`t_score_${indexStr}`)?.value) || 0;
        const sScore = parseInt(document.getElementById(`s_score_${indexStr}`)?.value) || 0;
        const bScore = parseInt(document.getElementById(`b_score_${indexStr}`)?.value) || 0;
        
        const total = cScore + tScore + sScore + bScore;
        
        // Update displays
        const cDisplay = document.getElementById(`c_display_${indexStr}`);
        const tDisplay = document.getElementById(`t_display_${indexStr}`);
        const sDisplay = document.getElementById(`s_display_${indexStr}`);
        const bDisplay = document.getElementById(`b_display_${indexStr}`);
        const totalElement = document.getElementById(`total_score_${indexStr}`);
        const statusElement = document.getElementById(`status_${indexStr}`);
        const scoreResultElement = document.getElementById(`score_result_${indexStr}`);
        
        if (cDisplay) cDisplay.textContent = cScore;
        if (tDisplay) tDisplay.textContent = tScore;
        if (sDisplay) sDisplay.textContent = sScore;
        if (bDisplay) bDisplay.textContent = bScore;
        
        if (totalElement) {
            totalElement.textContent = total;
        }
        
        // Update score result display (C-T-S-B format)
        if (scoreResultElement) {
            scoreResultElement.textContent = `${cScore}-${tScore}-${sScore}-${bScore}`;
        }
        
        // Update status badge
        if (statusElement) {
            if (total >= 300) {
                statusElement.textContent = 'Accepted';
                statusElement.className = 'badge bg-success fs-6';
            } else if (total >= 200) {
                statusElement.textContent = 'Normal';
                statusElement.className = 'badge bg-warning fs-6';
            } else {
                statusElement.textContent = 'Rejected';
                statusElement.className = 'badge bg-danger fs-6';
            }
        }
        
        // Validate overall form
        validateForm();
        
    } catch (error) {
        console.error('Error calculating total score:', error);
    }
}

// Form validation
function validateForm() {
    const allInputs = document.querySelectorAll('input[type="number"][required]');
    let allValid = true;
    
    allInputs.forEach(input => {
        const value = parseInt(input.value);
        if (isNaN(value) || value < 0 || value > 100) {
            allValid = false;
        }
    });
    
    const submitBtn = document.querySelector('button[type="submit"]');
    submitBtn.disabled = !allValid;
    
    if (allValid) {
        submitBtn.innerHTML = '<i class="fas fa-arrow-right me-1"></i>Complete & Next Sample';
        submitBtn.classList.remove('btn-secondary');
        submitBtn.classList.add('btn-success');
    } else {
        submitBtn.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Complete All Scores';
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-secondary');
    }
}

// Save draft functionality
function saveDraft() {
    const formData = new FormData(document.getElementById('sampleTestingForm'));
    
    // Show saving indicator
    const draftBtn = event.target;
    const originalText = draftBtn.innerHTML;
    draftBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';
    draftBtn.disabled = true;
    
    // Here you can implement draft saving logic
    setTimeout(() => {
        draftBtn.innerHTML = '<i class="fas fa-check me-1"></i>Saved';
        setTimeout(() => {
            draftBtn.innerHTML = originalText;
            draftBtn.disabled = false;
        }, 1000);
    }, 1000);
}

// Form submission
document.getElementById('sampleTestingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Processing...';
    
    const formData = new FormData(this);
    const data = {};
    
    // Convert FormData to JSON
    for (let [key, value] of formData.entries()) {
        if (key.includes('[') && key.includes(']')) {
            // Handle nested arrays
            const matches = key.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
            if (matches) {
                const [, arrayName, index, field] = matches;
                if (!data[arrayName]) data[arrayName] = [];
                if (!data[arrayName][index]) data[arrayName][index] = {};
                data[arrayName][index][field] = value;
            }
        } else {
            data[key] = value;
        }
    }
    
    fetch(`{{ route('admin.batches.store-sample-testing', $batch->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.has_next) {
                // Show success message and reload for next sample
                showSuccessMessage(data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                // All samples completed, redirect to results
                showSuccessMessage(data.message);
                setTimeout(() => {
                    window.location.href = data.results_url;
                }, 2000);
            }
        } else {
            showErrorMessage(data.message);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error submitting form:', error);
        showErrorMessage('Error submitting test results. Please try again.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

// Helper functions for messages
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

// Initialize form validation on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize calculations for all testers
    @foreach($testingSession->testers as $index => $tester)
        calculateTotalScore({{ $index }});
    @endforeach
    
    validateForm();
    
    // Add event listeners to all number inputs
    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            const matches = this.name.match(/tester_results\[(\d+)\]\[(\w+_score)\]/);
            if (matches) {
                calculateTotalScore(testerIndex);
            }
        });
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl + Enter to submit form
    if (e.ctrlKey && e.key === 'Enter') {
        e.preventDefault();
        const submitBtn = document.querySelector('button[type="submit"]');
        if (!submitBtn.disabled) {
            submitBtn.click();
        }
    }
    
    // Ctrl + S to save draft
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        saveDraft();
    }
});
</script>
@endpush