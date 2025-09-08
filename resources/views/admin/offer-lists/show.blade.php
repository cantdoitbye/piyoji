@extends('admin.layouts.app')

@section('title', 'Offer List Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title">Offer List Details</h3>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <a href="{{ route('admin.offer-lists.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                                <a href="{{ route('admin.offer-lists.edit', $offerList->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <!-- Basic Information -->
                        <div class="col-md-6">
                            <h5>Basic Information</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Date:</th>
                                    <td>{{ $offerList->date ? $offerList->date->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Garden Name:</th>
                                    <td>
                                        {{ $offerList->garden_name }}
                                        @if($offerList->garden)
                                            <small class="badge badge-success ml-2">Mapped to: {{ $offerList->garden->garden_name }}</small>
                                        @else
                                            <small class="badge badge-warning ml-2">Not Mapped</small>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Grade:</th>
                                    <td><span class="badge badge-info">{{ $offerList->grade }}</span></td>
                                </tr>
                                <tr>
                                    <th>For:</th>
                                    <td><span class="badge badge-primary">{{ $offerList->for_text }}</span></td>
                                </tr>
                                <tr>
                                    <th>Type:</th>
                                    <td>
                                        @if($offerList->type)
                                            <span class="badge badge-secondary">{{ $offerList->type }}</span>
                                        @else
                                            <span class="text-muted">Not specified</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Inv PreTx:</th>
                                    <td><span class="badge badge-dark">{{ $offerList->inv_pretx_text }}</span></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Additional Details -->
                        <div class="col-md-6">
                            <h5>Additional Details</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Device ID:</th>
                                    <td>{{ $offerList->device_id ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>AWR No:</th>
                                    <td>{{ $offerList->awr_no ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Inv No:</th>
                                    <td>{{ $offerList->inv_no ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Key:</th>
                                    <td>{{ $offerList->key ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Upload Name:</th>
                                    <td>{{ $offerList->name_of_upload ?: 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>D/O Packing:</th>
                                    <td>{{ $offerList->d_o_packing ? $offerList->d_o_packing->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Weight & Package Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Weight & Package Information</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-primary"><i class="fas fa-boxes"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Packages</span>
                                            <span class="info-box-number">{{ $offerList->pkgs ?: '0' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-success"><i class="fas fa-weight"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Net1</span>
                                            <span class="info-box-number">{{ number_format($offerList->net1 ?: 0, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-balance-scale"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total KGs</span>
                                            <span class="info-box-number">{{ number_format($offerList->ttl_kgs ?: 0, 3) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parties Information -->
                    @php $parties = $offerList->parties; @endphp
                    @if(count($parties) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Parties ({{ count($parties) }})</h5>
                            <div class="row">
                                @foreach($parties as $index => $party)
                                    <div class="col-md-3 mb-2">
                                        <span class="badge badge-outline-secondary">
                                            Party {{ $index + 1 }}: {{ $party }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Timestamps -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Record Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="15%">Created At:</th>
                                    <td>{{ $offerList->created_at ? $offerList->created_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>{{ $offerList->updated_at ? $offerList->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection