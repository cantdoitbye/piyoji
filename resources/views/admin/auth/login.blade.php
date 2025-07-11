<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pioji Tea Admin - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c5530;
            --primary-dark: #1e3a22;
            --secondary-color: #4a7c59;
            --accent-color: #8fbc8f;
            --danger-color: #ef4444;
            --text-muted: #64748b;
            --dark-color: #1e293b;
            --border-color: #e2e8f0;
            --tea-green: #2c5530;
            --tea-light: #8fbc8f;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #2c5530 0%, #4a7c59 50%, #8fbc8f 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(44, 85, 48, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            min-height: 600px;
            display: flex;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--tea-green) 0%, var(--secondary-color) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="25" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="25" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            animation: float 30s infinite linear;
        }

        .tea-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .brand-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .brand-company {
            font-size: 1.2rem;
            font-weight: 500;
            margin-bottom: 1rem;
            text-align: center;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.8;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            position: relative;
            z-index: 2;
        }

        .feature-list li {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .feature-list i {
            margin-right: 1rem;
            font-size: 1.2rem;
            opacity: 0.8;
            color: var(--tea-light);
        }

        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .login-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }

        .login-subtitle {
            color: var(--text-muted);
            font-size: 1rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(44, 85, 48, 0.1);
            background: white;
        }

        .input-group {
            position: relative;
        }

        .input-group-text {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            z-index: 10;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(44, 85, 48, 0.3);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            margin-right: 0.5rem;
            accent-color: var(--primary-color);
        }

        .form-check-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .alert {
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 1.5rem;
            border: none;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .loading-spinner {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .tea-leaves {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            opacity: 0.1;
            color: white;
            animation: sway 6s ease-in-out infinite;
        }

        .tea-leaves:nth-child(2) {
            top: 40%;
            left: 20px;
            animation-delay: -2s;
        }

        .tea-leaves:nth-child(3) {
            bottom: 20px;
            left: 50%;
            animation-delay: -4s;
        }

        @keyframes float {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }

        @keyframes sway {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(5deg); }
            75% { transform: rotate(-5deg); }
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
                margin: 10px;
            }
            
            .login-left {
                padding: 40px 20px;
                min-height: 300px;
            }
            
            .login-right {
                padding: 40px 20px;
            }
            
            .brand-title {
                font-size: 2rem;
            }
            
            .login-title {
                font-size: 1.5rem;
            }

            .tea-leaves {
                display: none;
            }
        }

        .company-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="tea-leaves"><i class="fas fa-leaf"></i></div>
            <div class="tea-leaves"><i class="fas fa-seedling"></i></div>
            <div class="tea-leaves"><i class="fas fa-spa"></i></div>
            
            <div class="tea-icon">
                <i class="fas fa-leaf"></i>
            </div>
            <div class="company-badge">B2B Tea Trading Platform</div>
            <h1 class="brand-title">Pioji Tea</h1>
            <div class="brand-company">Limited</div>
            <p class="brand-subtitle">Professional Tea Trading Management System</p>
            <ul class="feature-list">
                <li><i class="fas fa-check"></i> Seller & Buyer Management</li>
                <li><i class="fas fa-check"></i> Sample Evaluation & Tracking</li>
                <li><i class="fas fa-check"></i> Contract & Pricing Management</li>
                <li><i class="fas fa-check"></i> Dispatch & Logistics</li>
                <li><i class="fas fa-check"></i> Commission & Reports</li>
            </ul>
        </div>
        <div class="login-right">
            <div class="login-header">
                <h2 class="login-title">Welcome Back</h2>
                <p class="login-subtitle">Sign in to your admin account</p>
            </div>
            
            @if ($errors->any())
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                </div>
            @endif
            
            <form method="POST" action="{{ route('admin.login') }}" id="loginForm">
                @csrf
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope me-2 text-muted"></i>Email Address
                    </label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                           name="email" value="{{ old('email') }}" placeholder="Enter your email address" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock me-2 text-muted"></i>Password
                    </label>
                    <div class="input-group">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Enter your password" required>
                        <span class="input-group-text" onclick="togglePassword()">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </span>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember" 
                           {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Remember me for 30 days</label>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <span id="loginText">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In to Dashboard
                    </span>
                    <div class="loading-spinner" id="loginSpinner">
                        <div class="spinner-border spinner-border-sm text-light"></div>
                    </div>
                </button>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Secure admin access for authorized personnel only
                </small>
            </div>

            @if(config('app.env') === 'local')
            {{-- <div class="mt-4 p-3 bg-light rounded">
                <small class="text-muted">
                    <strong>Demo Credentials:</strong><br>
                    Email: admin@piojitea.com<br>
                    Password: password123
                </small>
            </div> --}}
            @endif
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Show loading state
        function showLoading(show) {
            const loginText = document.getElementById('loginText');
            const loginSpinner = document.getElementById('loginSpinner');
            const submitButton = document.querySelector('#loginForm button[type="submit"]');
            
            if (show) {
                loginText.style.display = 'none';
                loginSpinner.style.display = 'block';
                submitButton.disabled = true;
            } else {
                loginText.style.display = 'block';
                loginSpinner.style.display = 'none';
                submitButton.disabled = false;
            }
        }

        // Handle form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            // Basic client-side validation
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }
            
            showLoading(true);
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (!alert.classList.contains('alert-danger')) {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }
            });
        }, 5000);

        // Add some interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add focus effects to form inputs
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                    this.parentElement.style.transition = 'transform 0.3s ease';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html>