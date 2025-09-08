{{-- resources/views/admin/batches/_tester_evaluation_form.blade.php --}}
<div class="tester-card p-3 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0"><i class="fas fa-user me-2"></i>Tester {{ (int)$index + 1 }}</h6>
        @if((int)$index > 0)
        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeTester({{ $index }})">
            <i class="fas fa-times"></i>
        </button>
        @endif
    </div>
    
    <div class="row">
        <!-- Tester Selection -->
        <div class="col-lg-6 col-md-12 mb-3">
            <label class="form-label">Select Tester <span class="text-danger">*</span></label>
            <select name="testers[{{ $index }}][tester_poc_id]" class="form-select" required>
                <option value="">Choose Tester...</option>
                @if(isset($testers) && $testers->count() > 0)
                    @foreach($testers as $tester)
                    <option value="{{ $tester->id }}" 
                        {{ (old("testers.{$index}.tester_poc_id") ?? ($testerEval->tester_poc_id ?? '')) == $tester->id ? 'selected' : '' }}>
                        {{ $tester->poc_name }} @if($tester->designation) - {{ $tester->designation }} @endif
                    </option>
                    @endforeach
                @else
                    <!-- Static test data -->
                    <option value="1" {{ (int)$index == 0 ? 'selected' : '' }}>Anindya Ray - Senior Tester</option>
                    <option value="2" {{ (int)$index == 1 ? 'selected' : '' }}>Rohan Ghosh - Quality Controller</option>
                    <option value="3" {{ (int)$index == 2 ? 'selected' : '' }}>Gaurav Shah - Tea Expert</option>
                    <option value="4">Rajesh Kumar - Lead Tester</option>
                    <option value="5">Priya Sharma - Junior Tester</option>
                @endif
            </select>
        </div>

        <!-- Total Samples -->
        <div class="col-lg-6 col-md-12 mb-3">
            <label class="form-label">Total Samples Evaluated <span class="text-danger">*</span></label>
            <input type="number" name="testers[{{ $index }}][total_samples]" 
                   class="form-control" min="1" max="100" 
                   value="{{ old("testers.{$index}.total_samples") ?? ($testerEval->total_samples ?? ($index == 0 ? '68' : ($index == 1 ? '70' : '50'))) }}" 
                   placeholder="Enter number of samples" required>
        </div>
    </div>

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
                    <input type="number" id="c_score_{{ $index }}" name="testers[{{ $index }}][c_score]" 
                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                           value="{{ old("testers.{$index}.c_score") ?? ($testerEval->c_score ?? ($index == 0 ? '70' : ($index == 1 ? '68' : '65'))) }}" 
                           onchange="calculateTotalScore({{ $index }})" required>
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
                    <input type="number" id="t_score_{{ $index }}" name="testers[{{ $index }}][t_score]" 
                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                           value="{{ old("testers.{$index}.t_score") ?? ($testerEval->t_score ?? ($index == 0 ? '68' : ($index == 1 ? '70' : '72'))) }}"
                           onchange="calculateTotalScore({{ $index }})" required>
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
                    <input type="number" id="s_score_{{ $index }}" name="testers[{{ $index }}][s_score]" 
                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                           value="{{ old("testers.{$index}.s_score") ?? ($testerEval->s_score ?? ($index == 0 ? '0' : ($index == 1 ? '68' : '75'))) }}"
                           onchange="calculateTotalScore({{ $index }})" required>
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
                    <input type="number" id="b_score_{{ $index }}" name="testers[{{ $index }}][b_score]" 
                           class="form-control score-input text-center fw-bold" min="0" max="100" 
                           value="{{ old("testers.{$index}.b_score") ?? ($testerEval->b_score ?? ($index == 0 ? '0' : ($index == 1 ? '70' : '80'))) }}"
                           onchange="calculateTotalScore({{ $index }})" required>
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

    <!-- Additional Fields -->
    <div class="row">
        <!-- Color Shade -->
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label class="form-label fw-bold">Color Shade</label>
            <select name="testers[{{ $index }}][color_shade]" class="form-select">
                <option value="RED" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? 'RED')) == 'RED' ? 'selected' : '' }}>RED</option>
                <option value="GOLDEN" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? '')) == 'GOLDEN' ? 'selected' : '' }}>GOLDEN</option>
                <option value="AMBER" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? '')) == 'AMBER' ? 'selected' : '' }}>AMBER</option>
                <option value="DARK" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? '')) == 'DARK' ? 'selected' : '' }}>DARK</option>
            </select>
        </div>

        <!-- Brand -->
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label class="form-label fw-bold">Brand</label>
            <select name="testers[{{ $index }}][brand]" class="form-select">
                <option value="WB" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? 'WB')) == 'WB' ? 'selected' : '' }}>WB</option>
                <option value="PREMIUM" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? '')) == 'PREMIUM' ? 'selected' : '' }}>PREMIUM</option>
                <option value="STANDARD" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? '')) == 'STANDARD' ? 'selected' : '' }}>STANDARD</option>
                <option value="EXPORT" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? '')) == 'EXPORT' ? 'selected' : '' }}>EXPORT</option>
            </select>
        </div>

        <!-- Remarks -->
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label class="form-label fw-bold">Remarks</label>
            <input type="text" name="testers[{{ $index }}][remarks]" class="form-control" 
                   value="{{ old("testers.{$index}.remarks") ?? ($testerEval->remarks ?? 'NORMAL') }}" 
                   placeholder="Enter remarks">
        </div>
    </div>

    <!-- Total Score Display -->
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info border-0 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <strong class="fs-5">Total Score: </strong>
                        <span id="total_score_{{ $index }}" class="fw-bold fs-4 text-primary">0</span>
                        <span class="text-muted">/ 400</span>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <strong class="fs-5">Status: </strong>
                        <span id="status_{{ $index }}" class="badge bg-secondary fs-6">Pending</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
        grid-template-columns: repeat(6, 1fr);
        gap: 4px;
    }
    
    .number-btn {
        min-width: 30px;
        height: 30px;
        font-size: 10px;
    }
    
    .tester-card {
        margin-bottom: 20px;
    }
}

@media (max-width: 576px) {
    .number-grid {
        grid-template-columns: repeat(5, 1fr);
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
        grid-template-columns: repeat(11, 1fr);
        max-width: 350px;
    }
    
    .number-btn {
        min-width: 30px;
        height: 30px;
    }
}
</style>

<script>
// Enhanced calculation function with better error handling
if (typeof calculateTotalScore === 'undefined') {
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
            
            if (cDisplay) cDisplay.textContent = cScore;
            if (tDisplay) tDisplay.textContent = tScore;
            if (sDisplay) sDisplay.textContent = sScore;
            if (bDisplay) bDisplay.textContent = bScore;
            
            if (totalElement) {
                totalElement.textContent = total;
            }
            
            // Update score result display (C-T-S-B format)
            const scoreResultElement = document.getElementById(`score_result_${indexStr}`);
            if (scoreResultElement) {
                scoreResultElement.textContent = `${cScore}-${tScore}-${sScore}-${bScore}`;
            }
            
            // Update status badge with animation
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
            
            // Update number grid selections
            updateNumberGridSelections(indexStr, { c: cScore, t: tScore, s: sScore, b: bScore });
            
        } catch (error) {
            console.error('Error calculating total score:', error);
        }
    }
    
    function updateNumberGridSelections(index, scores) {
        ['c', 't', 's', 'b'].forEach(category => {
            const buttons = document.querySelectorAll(`[data-category="${category}"][data-index="${index}"] .number-btn`);
            buttons.forEach(btn => {
                btn.classList.remove('selected');
                if (parseInt(btn.textContent) === scores[category]) {
                    btn.classList.add('selected');
                }
            });
        });
    }
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalScore('{{ $index }}');
});
</script>