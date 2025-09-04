{{-- resources/views/admin/batches/_tester_evaluation_form.blade.php --}}
<div class="tester-card p-3">
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
        <div class="col-md-6 mb-3">
            <label class="form-label">Select Tester <span class="text-danger">*</span></label>
            <select name="testers[{{ $index }}][tester_poc_id]" class="form-select" required>
                <option value="">Choose Tester...</option>
                @foreach($testers as $tester)
                <option value="{{ $tester->id }}" 
                    {{ (old("testers.{$index}.tester_poc_id") ?? ($testerEval->tester_poc_id ?? '')) == $tester->id ? 'selected' : '' }}>
                    {{ $tester->poc_name }} @if($tester->designation) - {{ $tester->designation }} @endif
                </option>
                @endforeach
            </select>
        </div>

        <!-- Total Samples -->
        <div class="col-md-6 mb-3">
            <label class="form-label">Total Samples Evaluated <span class="text-danger">*</span></label>
            <input type="number" name="testers[{{ $index }}][total_samples]" 
                   class="form-control" min="1" max="100" 
                   value="{{ old("testers.{$index}.total_samples") ?? ($testerEval->total_samples ?? '') }}" 
                   placeholder="Enter number of samples" required>
        </div>
    </div>

    <!-- Score Sections -->
    <div class="row">
        <!-- C Score (Color/Appearance) -->
        <div class="col-md-3 mb-3">
            <label class="form-label">C Score (Color) <span class="text-danger">*</span></label>
            <div class="score-controls mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="decrementScore('c', {{ $index }})">-</button>
                <input type="number" id="c_score_{{ $index }}" name="testers[{{ $index }}][c_score]" 
                       class="form-control score-input mx-2" min="0" max="100" 
                       value="{{ old("testers.{$index}.c_score") ?? ($testerEval->c_score ?? 0) }}" 
                       onchange="calculateTotalScore({{ $index }})" required>
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="incrementScore('c', {{ $index }})">+</button>
                <span class="ms-2 fw-bold text-primary" id="c_display_{{ $index }}">0</span>
            </div>
            <div class="number-grid" data-category="c" data-index="{{ $index }}">
                @for($i = 0; $i <= 100; $i += 10)
                <button type="button" class="number-btn" onclick="selectScore('c', {{ $index }}, {{ $i }})">{{ $i }}</button>
                @endfor
            </div>
        </div>

        <!-- T Score (Taste) -->
        <div class="col-md-3 mb-3">
            <label class="form-label">T Score (Taste) <span class="text-danger">*</span></label>
            <div class="score-controls mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="decrementScore('t', {{ $index }})">-</button>
                <input type="number" id="t_score_{{ $index }}" name="testers[{{ $index }}][t_score]" 
                       class="form-control score-input mx-2" min="0" max="100" 
                       value="{{ old("testers.{$index}.t_score") ?? ($testerEval->t_score ?? 0) }}"
                       onchange="calculateTotalScore({{ $index }})" required>
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="incrementScore('t', {{ $index }})">+</button>
                <span class="ms-2 fw-bold text-primary" id="t_display_{{ $index }}">0</span>
            </div>
            <div class="number-grid" data-category="t" data-index="{{ $index }}">
                @for($i = 0; $i <= 100; $i += 10)
                <button type="button" class="number-btn" onclick="selectScore('t', {{ $index }}, {{ $i }})">{{ $i }}</button>
                @endfor
            </div>
        </div>

        <!-- S Score (Strength) -->
        <div class="col-md-3 mb-3">
            <label class="form-label">S Score (Strength) <span class="text-danger">*</span></label>
            <div class="score-controls mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="decrementScore('s', {{ $index }})">-</button>
                <input type="number" id="s_score_{{ $index }}" name="testers[{{ $index }}][s_score]" 
                       class="form-control score-input mx-2" min="0" max="100" 
                       value="{{ old("testers.{$index}.s_score") ?? ($testerEval->s_score ?? 0) }}"
                       onchange="calculateTotalScore({{ $index }})" required>
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="incrementScore('s', {{ $index }})">+</button>
                <span class="ms-2 fw-bold text-primary" id="s_display_{{ $index }}">0</span>
            </div>
            <div class="number-grid" data-category="s" data-index="{{ $index }}">
                @for($i = 0; $i <= 100; $i += 10)
                <button type="button" class="number-btn" onclick="selectScore('s', {{ $index }}, {{ $i }})">{{ $i }}</button>
                @endfor
            </div>
        </div>

        <!-- B Score (Body/Liquor) -->
        <div class="col-md-3 mb-3">
            <label class="form-label">B Score (Body) <span class="text-danger">*</span></label>
            <div class="score-controls mb-2">
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="decrementScore('b', {{ $index }})">-</button>
                <input type="number" id="b_score_{{ $index }}" name="testers[{{ $index }}][b_score]" 
                       class="form-control score-input mx-2" min="0" max="100" 
                       value="{{ old("testers.{$index}.b_score") ?? ($testerEval->b_score ?? 0) }}"
                       onchange="calculateTotalScore({{ $index }})" required>
                <button type="button" class="btn btn-outline-secondary btn-sm score-btn" 
                        onclick="incrementScore('b', {{ $index }})">+</button>
                <span class="ms-2 fw-bold text-primary" id="b_display_{{ $index }}">0</span>
            </div>
            <div class="number-grid" data-category="b" data-index="{{ $index }}">
                @for($i = 0; $i <= 100; $i += 10)
                <button type="button" class="number-btn" onclick="selectScore('b', {{ $index }}, {{ $i }})">{{ $i }}</button>
                @endfor
            </div>
        </div>
    </div>

    <!-- Additional Fields -->
    <div class="row">
        <!-- Color Shade -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Color Shade</label>
            <select name="testers[{{ $index }}][color_shade]" class="form-select">
                <option value="RED" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? 'RED')) == 'RED' ? 'selected' : '' }}>RED</option>
                <option value="GOLDEN" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? '')) == 'GOLDEN' ? 'selected' : '' }}>GOLDEN</option>
                <option value="AMBER" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? '')) == 'AMBER' ? 'selected' : '' }}>AMBER</option>
                <option value="DARK" {{ (old("testers.{$index}.color_shade") ?? ($testerEval->color_shade ?? '')) == 'DARK' ? 'selected' : '' }}>DARK</option>
            </select>
        </div>

        <!-- Brand -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Brand</label>
            <select name="testers[{{ $index }}][brand]" class="form-select">
                <option value="WB" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? 'WB')) == 'WB' ? 'selected' : '' }}>WB</option>
                <option value="PREMIUM" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? '')) == 'PREMIUM' ? 'selected' : '' }}>PREMIUM</option>
                <option value="STANDARD" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? '')) == 'STANDARD' ? 'selected' : '' }}>STANDARD</option>
                <option value="EXPORT" {{ (old("testers.{$index}.brand") ?? ($testerEval->brand ?? '')) == 'EXPORT' ? 'selected' : '' }}>EXPORT</option>
            </select>
        </div>

        <!-- Remarks -->
        <div class="col-md-4 mb-3">
            <label class="form-label">Remarks</label>
            <input type="text" name="testers[{{ $index }}][remarks]" class="form-control" 
                   value="{{ old("testers.{$index}.remarks") ?? ($testerEval->remarks ?? 'NORMAL') }}" 
                   placeholder="Enter remarks">
        </div>
    </div>

    <!-- Total Score Display -->
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <strong>Total Score: </strong>
                <span id="total_score_{{ $index }}" class="fw-bold">0</span> / 400
                <span class="ms-3">
                    <strong>Status: </strong>
                    <span id="status_{{ $index }}" class="badge bg-secondary">Pending</span>
                </span>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate total score function - moved outside to avoid duplicates
if (typeof calculateTotalScore === 'undefined') {
    function calculateTotalScore(index) {
        // Ensure index is treated as a string for DOM queries
        const indexStr = String(index);
        
        const cScore = parseInt(document.getElementById(`c_score_${indexStr}`)?.value) || 0;
        const tScore = parseInt(document.getElementById(`t_score_${indexStr}`)?.value) || 0;
        const sScore = parseInt(document.getElementById(`s_score_${indexStr}`)?.value) || 0;
        const bScore = parseInt(document.getElementById(`b_score_${indexStr}`)?.value) || 0;
        
        const total = cScore + tScore + sScore + bScore;
        
        const totalElement = document.getElementById(`total_score_${indexStr}`);
        const statusElement = document.getElementById(`status_${indexStr}`);
        
        if (totalElement) {
            totalElement.textContent = total;
        }
        
        // Update status badge
        if (statusElement) {
            if (total >= 300) {
                statusElement.textContent = 'Accepted';
                statusElement.className = 'badge bg-success';
            } else if (total >= 200) {
                statusElement.textContent = 'Normal';
                statusElement.className = 'badge bg-warning';
            } else {
                statusElement.textContent = 'Rejected';
                statusElement.className = 'badge bg-danger';
            }
        }
    }
}

// Initialize total score calculation for this specific index
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalScore('{{ $index }}');
});
</script>