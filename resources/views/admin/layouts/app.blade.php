<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Tea Trading ERP</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        :root {
            --primary-color: #2c5530;
            --secondary-color: #4a7c59;
            --accent-color: #8fbc8f;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --dark-color: #343a40;
            --light-color: #f8f9fa;
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .main-content {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 20px;
            padding: 30px;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-color);
            transition: transform 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .stats-card .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .table th {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(44, 85, 48, 0.25);
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            border-color: #ced4da;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            color: #495057;
        }

        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color) !important;
        }

        .dropdown-item:hover {
            background-color: var(--accent-color);
        }

        .breadcrumb {
            background-color: transparent;
            padding: 0;
        }

        .breadcrumb-item.active {
            color: var(--primary-color);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            border: none;
        }

        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .loading {
            display: none;
        }

        .loading.show {
            display: inline-block;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
                transition: margin-left 0.3s ease;
            }
            
            .sidebar.show {
                margin-left: 0;
            }
            
            .main-content {
                margin: 10px;
                padding: 15px;
            }
        }

        .collapsible-menu {
    background-color: #155724;
    color: white;
    border: none;
    width: 100%;
    text-align: left;
    padding: 12px 15px;
    font-size: 14px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.collapsible-menu:hover {
    background-color: #377245;
}

.submenu {
    background-color: #495057;
}

.submenu .nav-link {
    padding-left: 25px;
    font-size: 13px;
}

.submenu .nav-link:hover {
    background-color: #6c757d;
}

.collapse-icon {
    transition: transform 0.3s ease;
}

.menu-icon {
    width: 20px;
    text-align: center;
    margin-right: 8px;
}
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Top Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-leaf me-2"></i>Tea Trading ERP
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                        {{ auth()->user()->name ?? 'Admin' }}
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.logout') }}" 
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                            <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-lg-2 d-lg-block sidebar collapse" id="sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>

                        <li class="nav-item">
    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
        <span>User Management</span>
    </h6>
</li>

<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
        <i class="fas fa-users"></i>
        System Users
    </a>
</li>
                        
                        {{-- <li class="nav-item">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                                <span>Master Data</span>
                            </h6>
                        </li>

                           <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.pocs.*') ? 'active' : '' }}" 
                               href="{{ route('admin.pocs.index') }}">
                                <i class="fas fa-user-tie"></i>
                                POC Master
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.teas.*') ? 'active' : '' }}" 
                               href="{{ route('admin.teas.index') }}">
                                <i class="fas fa-leaf"></i>
                                Tea Master
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.gardens.*') ? 'active' : '' }}" 
                               href="{{ route('admin.gardens.index') }}">
                                <i class="fas fa-seedling"></i>
                                Garden Master
                            </a>
                        </li>

                          <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.billing-companies.*') ? 'active' : '' }}" 
                               href="{{ route('admin.billing-companies.index') }}">
                                <i class="fas fa-seedling"></i>
                                Billing Company
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}" 
                               href="{{ route('admin.sellers.index') }}">
                                <i class="fas fa-store"></i>
                                Sellers
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.buyers.*') ? 'active' : '' }}" 
                               href="{{ route('admin.buyers.index') }}">
                                <i class="fas fa-users"></i>
                                Buyers
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}" 
                               href="{{ route('admin.couriers.index') }}">
                                <i class="fas fa-shipping-fast"></i>
                                Courier Services
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.logistics.index')}}">
                                <i class="fas fa-truck"></i>
                                Logistic Companies
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.contracts.index')}}">
                                <i class="fas fa-file-contract"></i>
                                Contracts
                            </a>
                        </li> --}}

                        <!-- Collapsible Master Data Menu -->
                    <li class="nav-item mt-3">
                        <button class="collapsible-menu" type="button" data-bs-toggle="collapse" 
                                data-bs-target="#masterDataMenu" aria-expanded="true" aria-controls="masterDataMenu">
                            <span>
                                <i class="fas fa-database menu-icon"></i>
                                Master Data
                            </span>
                            <i class="fas fa-chevron-up collapse-icon"></i>
                        </button>
                        <div class="collapse show" id="masterDataMenu">
                            <ul class="nav flex-column submenu">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.pocs.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.pocs.index') }}">
                                        <i class="fas fa-user-tie menu-icon"></i>
                                        POC Master
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.teas.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.teas.index') }}">
                                        <i class="fas fa-leaf menu-icon"></i>
                                        Tea Master
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.gardens.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.gardens.index') }}">
                                        <i class="fas fa-seedling menu-icon"></i>
                                        Garden Master
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.billing-companies.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.billing-companies.index') }}">
                                        <i class="fas fa-building menu-icon"></i>
                                        Billing Company
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.sellers.index') }}">
                                        <i class="fas fa-store menu-icon"></i>
                                        Sellers
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.buyers.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.buyers.index') }}">
                                        <i class="fas fa-users menu-icon"></i>
                                        Buyers
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.couriers.*') ? 'active' : '' }}" 
                                       href="{{ route('admin.couriers.index') }}">
                                        <i class="fas fa-shipping-fast menu-icon"></i>
                                        Courier Services
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.logistics.index') }}">
                                        <i class="fas fa-truck menu-icon"></i>
                                        Logistic Companies
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('admin.contracts.index') }}">
                                        <i class="fas fa-file-contract menu-icon"></i>
                                        Contracts
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                        
                        <li class="nav-item">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                                <span>Sample Management</span>
                            </h6>
                        </li>

                        <li class="nav-item">
    <a href="{{ route('admin.offer-lists.index') }}" class="nav-link">
        <i class="fas fa-list"></i> Offer Lists
    </a>
</li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.samples.index') }}">
                                <i class="fas fa-flask"></i>
                                Testing
                            </a>
                        </li>
                        
                        {{-- <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.samples.pending-evaluations') }}">
                                <i class="fas fa-clipboard-check"></i>
                                Sample Evaluation
                            </a>
                        </li> --}}
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-user-tag"></i>
                                Buyer Assignment
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                                <span>Dispatch & Sales</span>
                            </h6>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-paper-plane"></i>
                                Sample Dispatch
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-comments"></i>
                                Buyer Feedback
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-file-invoice"></i>
                                Dispatch Advice
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-light">
                                <span>Reports</span>
                            </h6>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i>
                                Sales Reports
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-percentage"></i>
                                Commission Reports
                            </a>
                        </li>
                        
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i>
                                Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-lg-10 ms-sm-auto px-0">
                <div class="main-content">
                    <!-- Breadcrumb -->
                    @if(!request()->routeIs('admin.dashboard'))
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            @yield('breadcrumb')
                        </ol>
                    </nav>
                    @endif

                    <!-- Page Header -->
                    <div class="page-header">
                        <div class="row align-items-center">
                            <div class="col">
                                <h1 class="h2 mb-0">@yield('title', 'Dashboard')</h1>
                                <p class="mb-0">@yield('subtitle', 'Manage your tea trading operations')</p>
                            </div>
                            <div class="col-auto">
                                @yield('header-actions')
                            </div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Main Content Area -->
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="d-flex justify-content-center align-items-center h-100">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Toastr -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        // Global CSRF setup for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };

        // Initialize Select2
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });
        });

        // Loading overlay functions
        function showLoading() {
            $('#loadingOverlay').show();
        }

        function hideLoading() {
            $('#loadingOverlay').hide();
        }

        // Global AJAX error handler
        $(document).ajaxError(function(event, xhr, settings, thrownError) {
            hideLoading();
            if (xhr.status === 419) {
                toastr.error('Session expired. Please refresh the page.');
            } else if (xhr.status === 500) {
                toastr.error('Server error occurred. Please try again.');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Confirm deletion
        function confirmDelete(message = 'Are you sure you want to delete this item?') {
            return confirm(message);
        }

        // Format numbers
        function formatNumber(num) {
            return new Intl.NumberFormat('en-IN').format(num);
        }

        // Format currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-IN', {
                style: 'currency',
                currency: 'INR'
            }).format(amount);
        }

        document.addEventListener('DOMContentLoaded', function() {
    const collapseButton = document.querySelector('.collapsible-menu');
    const collapseTarget = document.querySelector('#masterDataMenu');
    
    if (collapseButton && collapseTarget) {
        collapseTarget.addEventListener('show.bs.collapse', function () {
            collapseButton.querySelector('.collapse-icon').style.transform = 'rotate(0deg)';
        });
        
        collapseTarget.addEventListener('hide.bs.collapse', function () {
            collapseButton.querySelector('.collapse-icon').style.transform = 'rotate(180deg)';
        });
    }
});
    </script>

    @stack('scripts')
</body>
</html>