@extends('admin.layouts.app')

@section('title', 'Import Offer Lists')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title">Import Offer Lists from Excel</h3>
                        </div>
                        <div class="col-auto">
                            <a href="{{ route('admin.offer-lists.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('import_errors') && count(session('import_errors')) > 0)
                    <div class="alert alert-warning">
                        <h5>Import completed with some errors:</h5>
                        <ul class="mb-0">
                            @foreach(array_slice(session('import_errors'), 0, 10) as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                            @if(count(session('import_errors')) > 10)
                                <li class="text-muted">... and {{ count(session('import_errors')) - 10 }} more errors</li>
                            @endif
                        </ul>
                    </div>
                    @endif

                    <!-- Instructions -->
                    <div class="alert alert-info">
                        <h5><i class="fas fa-info-circle"></i> Import Instructions:</h5>
                        <ul class="mb-0">
                            <li>Download the template file first and fill in your data</li>
                            <li>Supported formats: Excel (.xlsx, .xls) and CSV (.csv)</li>
                            <li>Maximum file size: 5MB</li>
                            <li>Garden names will be automatically mapped if they exist in the system</li>
                            <li>Existing offers with same Garden Name, Grade, and Date will be updated</li>
                            <li>Date format should be DD/MM/YYYY or similar standard formats</li>
                        </ul>
                    </div>

                    <!-- Download Template -->
                    <div class="mb-4">
                        <h5>Step 1: Download Template</h5>
                        <a href="{{ route('admin.offer-lists.template') }}" class="btn btn-info">
                            <i class="fas fa-download"></i> Download Excel Template
                        </a>
                    </div>

                    <!-- Upload Form -->
                    <div class="mb-4">
                        <h5>Step 2: Upload Completed File</h5>
                        <form action="{{ route('admin.offer-lists.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="form-group">
                                <label for="file">Select Excel/CSV File</label>
                                <input type="file" 
                                       name="file" 
                                       id="file" 
                                       class="form-control-file @error('file') is-invalid @enderror"
                                       accept=".xlsx,.xls,.csv"
                                       required>
                                @error('file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Import Offer Lists
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Expected Columns -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Expected Excel Columns</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Required Columns:</strong>
                                    <ul>
                                        <li><code>Date</code> - Offer date</li>
                                        <li><code>GARDEN</code> - Garden name</li>
                                        <li><code>GRADE</code> - Tea grade</li>
                                        <li><code>FOR</code> - GTPP or GTFP</li>
                                        <li><code>INV_PRETX</code> - C, EX, or PR</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <strong>Optional Columns:</strong>
                                    <ul>
                                        <li><code>DeviceID</code> - Device identifier</li>
                                        <li><code>N_AWR_NO</code> - AWR number</li>
                                        <li><code>INV_NO</code> - Invoice number</li>
                                        <li><code>PARTY_1</code> to <code>PARTY_10</code> - Party names</li>
                                        <li><code>PKGS</code> - Number of packages</li>
                                        <li><code>NET1</code> - Net weight 1</li>
                                        <li><code>TTL_KGS</code> - Total weight in KG</li>
                                        <li><code>D_O_PACKING</code> - Date of packing</li>
                                        <li><code>TYPE</code> - BROKENS, FANNINGS, or D</li>
                                        <li><code>Key</code> - Unique key</li>
                                        <li><code>NameOfUpload</code> - Upload name</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const fileSize = file.size / 1024 / 1024; // Convert to MB
        if (fileSize > 5) {
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            e.target.value = '';
        }
    }
});
</script>
@endsection