@extends('admin.layouts.app')

@section('title', 'Offer Lists')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="card-title">Offer Lists Management</h3>
                        </div>
                        <div class="col-auto">
                            <div class="btn-group">
                                <a href="{{ route('admin.offer-lists.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add New Offer
                                </a>
                                <a href="{{ route('admin.offer-lists.import.form') }}" class="btn btn-success">
                                    <i class="fas fa-upload"></i> Import Excel
                                </a>
                                <a href="{{ route('admin.offer-lists.template') }}" class="btn btn-info">
                                    <i class="fas fa-download"></i> Download Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Offers</span>
                                    <span class="info-box-number">{{ $statistics['total'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">This Month</span>
                                    <span class="info-box-number">{{ $statistics['current_month'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-weight"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Weight (KG)</span>
                                    <span class="info-box-number">{{ number_format($statistics['total_weight'] ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-tree"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Unique Gardens</span>
                                    <span class="info-box-number">{{ $statistics['unique_gardens'] ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.offer-lists.index') }}" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search..." 
                                       value="{{ $filters['search'] ?? '' }}">
                            </div>
                            <div class="col-md-2">
                                <select name="garden_id" class="form-control">
                                    <option value="">All Gardens</option>
                                    @foreach($gardens as $garden)
                                        <option value="{{ $garden->id }}" 
                                                {{ ($filters['garden_id'] ?? '') == $garden->id ? 'selected' : '' }}>
                                            {{ $garden->garden_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="grade" class="form-control">
                                    <option value="">All Grades</option>
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade }}" 
                                                {{ ($filters['grade'] ?? '') == $grade ? 'selected' : '' }}>
                                            {{ $grade }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" 
                                       value="{{ $filters['date_from'] ?? '' }}" placeholder="Date From">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" 
                                       value="{{ $filters['date_to'] ?? '' }}" placeholder="Date To">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-primary btn-block">Filter</button>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Garden Name</th>
                                    <th>Grade</th>
                                    <th>Type</th>
                                    <th>Packages</th>
                                    <th>Total KGs</th>
                                    <th>Parties</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offerLists as $offerList)
                                <tr>
                                    <td>{{ $offerList->date ? $offerList->date->format('d/m/Y') : '' }}</td>
                                    <td>
                                        {{ $offerList->garden_name }}
                                        @if($offerList->garden)
                                            <small class="text-muted">(Mapped)</small>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info">{{ $offerList->grade }}</span></td>
                                    <td>
                                        @if($offerList->type)
                                            <span class="badge badge-secondary">{{ $offerList->type }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $offerList->pkgs }}</td>
                                    <td>{{ number_format($offerList->ttl_kgs ?? 0, 2) }}</td>
                                    <td>
                                        @php $parties = $offerList->parties; @endphp
                                        @if(count($parties) > 0)
                                            {{ implode(', ', array_slice($parties, 0, 3)) }}
                                            @if(count($parties) > 3)
                                                <small class="text-muted">... +{{ count($parties) - 3 }} more</small>
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.offer-lists.show', $offerList->id) }}" 
                                               class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.offer-lists.edit', $offerList->id) }}" 
                                               class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.offer-lists.destroy', $offerList->id) }}" 
                                                  style="display: inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this offer?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No offer lists found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $offerLists->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection