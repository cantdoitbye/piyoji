@extends('admin.layouts.app')

@section('title', 'Sample Evaluation')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Sample Evaluation</h1>
            <p class="text-muted">Module 2.2 - Tea Sample Tasting & Scoring</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-outline-secondary">
                <i class="fas fa-eye me-1"></i> View Sample
            </a>
            <a href="{{ route('admin.samples.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Samples
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8">
            <!-- Sample Information Card -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-flask me-2"></i>Sample: {{ $sample->sample_name }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Sample ID:</td>
                                    <td>{{ $sample->sample_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Batch ID:</td>
                                    <td>{{ $sample->batch_id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Weight:</td>
                                    <td>{{ $sample->sample_weight ? $sample->sample_weight . ' kg' : 'Not specified' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Arrival Date:</td>
                                    <td>{{ $sample->arrival_date->format('d M Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td class="fw-bold">Seller:</td>
                                    <td>{{ $sample->seller->seller_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tea Estate:</td>
                                    <td>{{ $sample->seller->tea_estate_name }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Current Status:</td>
                                    <td><span class="badge {{ $sample->status_badge_class }}">{{ $sample->status_label }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Evaluation Status:</td>
                                    <td><span class="badge {{ $sample->evaluation_status === 'completed' ? 'bg-success' : 'bg-warning' }}">{{ $sample->evaluation_status_label }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    @if($sample->remarks)
                        <div class="mt-3">
                            <h6 class="fw-bold">Remarks:</h6>
                            <p class="text-muted mb-0">{{ $sample->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Evaluation Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Tasting Evaluation</h5>
                </div>
                <div class="card-body">
                    @if($sample->evaluation_status === 'completed')
                        <!-- Show existing evaluation -->
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-1"></i>
                            This sample has already been evaluated on {{ $sample->evaluated_at->format('d M Y H:i') }} 
                            by {{ $sample->evaluatedBy->name }}.
                        </div>
                        
                        <div class="row g-4">
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Aroma Score</h6>
                                        <h3 class="text-primary">{{ $sample->aroma_score }}/10</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Liquor Score</h6>
                                        <h3 class="text-info">{{ $sample->liquor_score }}/10</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Appearance Score</h6>
                                        <h3 class="text-warning">{{ $sample->appearance_score }}/10</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center border-success">
                                    <div class="card-body">
                                        <h6 class="card-title">Overall Score</h6>
                                        <h3 class="text-success">{{ $sample->overall_score }}/10</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if($sample->evaluation_comments)
                            <div class="mt-4">
                                <h6 class="fw-bold">Evaluation Comments:</h6>
                                <p class="text-muted">{{ $sample->evaluation_comments }}</p>
                            </div>
                        @endif
                    @else
                        <!-- Evaluation form -->
                        <form action="{{ route('admin.samples.store-evaluation', $sample->id) }}" method="POST" id="evaluationForm">
                            @csrf
                            
                            <div class="row g-4">
                                <!-- Aroma Score -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-primary text-white text-center">
                                            <h6 class="mb-0"><i class="fas fa-nose me-1"></i> Aroma Score</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <label for="aroma_score" class="form-label">Rate the aroma (0-10)</label>
                                            <input type="range" class="form-range mb-3" id="aroma_score" name="aroma_score" 
                                                   min="0" max="10" step="0.1" value="{{ old('aroma_score', 5) }}" 
                                                   oninput="updateScoreDisplay('aroma')">
                                            <div class="score-display">
                                                <span id="aromaDisplay" class="h4 text-primary">5.0</span>/10
                                            </div>
                                            <input type="hidden" id="aroma_score_hidden" name="aroma_score" value="5.0">
                                            @error('aroma_score')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Liquor Score -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-info text-white text-center">
                                            <h6 class="mb-0"><i class="fas fa-tint me-1"></i> Liquor Score</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <label for="liquor_score" class="form-label">Rate the liquor (0-10)</label>
                                            <input type="range" class="form-range mb-3" id="liquor_score" name="liquor_score" 
                                                   min="0" max="10" step="0.1" value="{{ old('liquor_score', 5) }}" 
                                                   oninput="updateScoreDisplay('liquor')">
                                            <div class="score-display">
                                                <span id="liquorDisplay" class="h4 text-info">5.0</span>/10
                                            </div>
                                            <input type="hidden" id="liquor_score_hidden" name="liquor_score" value="5.0">
                                            @error('liquor_score')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Appearance Score -->
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header bg-warning text-white text-center">
                                            <h6 class="mb-0"><i class="fas fa-eye me-1"></i> Appearance Score</h6>
                                        </div>
                                        <div class="card-body text-center">
                                            <label for="appearance_score" class="form-label">Rate the appearance (0-10)</label>
                                            <input type="range" class="form-range mb-3" id="appearance_score" name="appearance_score" 
                                                   min="0" max="10" step="0.1" value="{{ old('appearance_score', 5) }}" 
                                                   oninput="updateScoreDisplay('appearance')">
                                            <div class="score-display">
                                                <span id="appearanceDisplay" class="h4 text-warning">5.0</span>/10
                                            </div>
                                            <input type="hidden" id="appearance_score_hidden" name="appearance_score" value="5.0">
                                            @error('appearance_score')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Overall Score Display -->
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <div class="card border-success">
                                        <div class="card-header bg-success text-white text-center">
                                            <h5 class="mb-0"><i class="fas fa-star me-1"></i> Overall Score (Auto-calculated)</h5>
                                        </div>
                                        <div class="card-body text-center">
                                            <h1 id="overallScore" class="text-success">5.0</h1>
                                            <p class="mb-0">
                                                <span id="scoreStatus" class="badge bg-warning">Average</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Evaluation Comments -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <label for="evaluation_comments" class="form-label">Evaluation Comments</label>
                                    <textarea class="form-control @error('evaluation_comments') is-invalid @enderror" 
                                              id="evaluation_comments" name="evaluation_comments" rows="4" 
                                              placeholder="Enter detailed comments about the sample tasting...">{{ old('evaluation_comments') }}</textarea>
                                    @error('evaluation_comments')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Detailed notes about taste, texture, color, and overall impression</small>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('admin.samples.show', $sample->id) }}" class="btn btn-secondary">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success" id="submitBtn">
                                            <i class="fas fa-check me-1"></i> Submit Evaluation
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-xl-4">
            <!-- Evaluation Guidelines -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Evaluation Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <small>
                            <strong>Module 2.2 - Sample Evaluation Process:</strong><br>
                            Rate each aspect from 0-10 where:<br>
                            • 8-10: Excellent<br>
                            • 6-7.9: Good<br>
                            • 4-5.9: Average<br>
                            • 0-3.9: Poor
                        </small>
                    </div>
                    
                    <h6 class="small fw-bold mb-2">Aroma Evaluation:</h6>
                    <ul class="small mb-3">
                        <li>Intensity and complexity</li>
                        <li>Freshness and clarity</li>
                        <li>Characteristic tea notes</li>
                    </ul>
                    
                    <h6 class="small fw-bold mb-2">Liquor Evaluation:</h6>
                    <ul class="small mb-3">
                        <li>Taste balance and strength</li>
                        <li>Mouthfeel and body</li>
                        <li>Aftertaste quality</li>
                    </ul>
                    
                    <h6 class="small fw-bold mb-2">Appearance Evaluation:</h6>
                    <ul class="small mb-0">
                        <li>Color and clarity</li>
                        <li>Leaf quality and uniformity</li>
                        <li>Overall visual appeal</li>
                    </ul>
                </div>
            </div>

            <!-- Scoring Scale -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Scoring Scale</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-2">
                            <div class="border rounded p-2 bg-success text-white">
                                <small class="fw-bold">8.0 - 10.0</small><br>
                                <small>Excellent</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="border rounded p-2 bg-primary text-white">
                                <small class="fw-bold">6.0 - 7.9</small><br>
                                <small>Good</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="border rounded p-2 bg-warning text-white">
                                <small class="fw-bold">4.0 - 5.9</small><br>
                                <small>Average</small>
                            </div>
                        </div>
                        <div class="col-6 mb-2">
                            <div class="border rounded p-2 bg-danger text-white">
                                <small class="fw-bold">0.0 - 3.9</small><br>
                                <small>Poor</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Previous Evaluations -->
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Evaluations</h6>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted">
                        <i class="fas fa-clipboard-check fa-2x mb-2"></i>
                        <p class="small">Recent evaluations from this seller will appear here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize score displays
    updateScoreDisplay('aroma');
    updateScoreDisplay('liquor');
    updateScoreDisplay('appearance');
    calculateOverallScore();
});

function updateScoreDisplay(type) {
    const slider = document.getElementById(type + '_score');
    const display = document.getElementById(type + 'Display');
    const hidden = document.getElementById(type + '_score_hidden');
    
    const value = parseFloat(slider.value);
    display.textContent = value.toFixed(1);
    hidden.value = value.toFixed(1);
    
    // Update color based on score
    let colorClass = '';
    if (value >= 8) colorClass = 'text-success';
    else if (value >= 6) colorClass = 'text-primary';
    else if (value >= 4) colorClass = 'text-warning';
    else colorClass = 'text-danger';
    
    display.className = 'h4 ' + colorClass;
    
    // Calculate overall score
    calculateOverallScore();
}

function calculateOverallScore() {
    const aromaScore = parseFloat(document.getElementById('aroma_score').value);
    const liquorScore = parseFloat(document.getElementById('liquor_score').value);
    const appearanceScore = parseFloat(document.getElementById('appearance_score').value);
    
    const overallScore = ((aromaScore + liquorScore + appearanceScore) / 3).toFixed(1);
    
    document.getElementById('overallScore').textContent = overallScore;
    
    // Update status badge
    const statusBadge = document.getElementById('scoreStatus');
    let badgeClass = '';
    let statusText = '';
    
    if (overallScore >= 8) {
        badgeClass = 'badge bg-success';
        statusText = 'Excellent - Approved';
    } else if (overallScore >= 6) {
        badgeClass = 'badge bg-primary';
        statusText = 'Good - Approved';
    } else if (overallScore >= 4) {
        badgeClass = 'badge bg-warning';
        statusText = 'Average - Review Required';
    } else {
        badgeClass = 'badge bg-danger';
        statusText = 'Poor - Rejected';
    }
    
    statusBadge.className = badgeClass;
    statusBadge.textContent = statusText;
}

// Form submission confirmation
document.getElementById('evaluationForm')?.addEventListener('submit', function(e) {
    const overallScore = parseFloat(document.getElementById('overallScore').textContent);
    
    let confirmMessage = `Are you sure you want to submit this evaluation?\n\nOverall Score: ${overallScore}/10\n`;
    
    if (overallScore >= 7) {
        confirmMessage += 'This sample will be APPROVED for buyer assignment.';
    } else {
        confirmMessage += 'This sample will be REJECTED due to low score.';
    }
    
    if (!confirm(confirmMessage)) {
        e.preventDefault();
        return false;
    }
    
    // Disable submit button to prevent double submission
    document.getElementById('submitBtn').disabled = true;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
});

// Auto-save functionality (optional)
let autoSaveTimer;
function autoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(function() {
        // Save current evaluation state to localStorage
        const evaluationData = {
            sample_id: {{ $sample->id }},
            aroma_score: document.getElementById('aroma_score').value,
            liquor_score: document.getElementById('liquor_score').value,
            appearance_score: document.getElementById('appearance_score').value,
            evaluation_comments: document.getElementById('evaluation_comments').value,
            timestamp: new Date().toISOString()
        };
        
        localStorage.setItem('evaluation_draft_' + {{ $sample->id }}, JSON.stringify(evaluationData));
    }, 2000);
}

// Load auto-saved data if available
const savedData = localStorage.getItem('evaluation_draft_' + {{ $sample->id }});
if (savedData) {
    try {
        const data = JSON.parse(savedData);
        const timeDiff = new Date() - new Date(data.timestamp);
        
        // If saved data is less than 1 hour old, ask if user wants to restore
        if (timeDiff < 3600000) {
            if (confirm('Found unsaved evaluation data. Would you like to restore it?')) {
                document.getElementById('aroma_score').value = data.aroma_score;
                document.getElementById('liquor_score').value = data.liquor_score;
                document.getElementById('appearance_score').value = data.appearance_score;
                document.getElementById('evaluation_comments').value = data.evaluation_comments;
                
                updateScoreDisplay('aroma');
                updateScoreDisplay('liquor');
                updateScoreDisplay('appearance');
            }
        }
    } catch (e) {
        console.error('Error loading saved data:', e);
    }
}

// Add auto-save listeners
document.getElementById('aroma_score')?.addEventListener('input', autoSave);
document.getElementById('liquor_score')?.addEventListener('input', autoSave);
document.getElementById('appearance_score')?.addEventListener('input', autoSave);
document.getElementById('evaluation_comments')?.addEventListener('input', autoSave);

// Clear auto-saved data on successful submission
document.getElementById('evaluationForm')?.addEventListener('submit', function() {
    localStorage.removeItem('evaluation_draft_' + {{ $sample->id }});
});
</script>
@endpush