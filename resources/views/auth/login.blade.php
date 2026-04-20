{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }
        
        .card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            border: none;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 30px;
            border: none;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .card-header p {
            margin: 10px 0 0;
            opacity: 0.9;
        }
        
        .card-body {
            padding: 40px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .form-control {
            border-left: none;
            padding: 12px;
        }
        
        .form-control:focus {
            box-shadow: none;
            border-color: #ced4da;
        }
        
        .input-group:focus-within {
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            border-radius: 0.375rem;
        }
        
        .input-group:focus-within .input-group-text {
            border-color: #86b7fe;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .demo-credentials {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 10px;
            font-size: 12px;
        }
        
        .demo-credentials h6 {
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .demo-credentials p {
            margin: 5px 0;
        }
        
        @media (max-width: 768px) {
            .card-body {
                padding: 25px;
            }
            
            .login-container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock fa-3x mb-3"></i>
                <h3>Attendance System</h3>
                <p>Employee Attendance with GPS Tracking</p>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                {{-- <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="Enter your email" required autofocus>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Enter your password" required>
                        </div>
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </form> --}}

                <form method="POST" action="{{ route('login') }}">
    @csrf
    
    <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-envelope"></i>
            </span>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" value="{{ old('email') }}" 
                   placeholder="Enter your email" required autofocus>
        </div>
        @error('email')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-lock"></i>
            </span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                   id="password" name="password" placeholder="Enter your password" required>
        </div>
        @error('password')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>
    
    <div class="form-group form-check">
        <input type="checkbox" class="form-check-input" id="remember" name="remember" 
               {{ old('remember') ? 'checked' : '' }}>
        <label class="form-check-label" for="remember">Remember Me</label>
    </div>
    
    <button type="submit" class="btn btn-primary btn-login">
        <i class="fas fa-sign-in-alt me-2"></i> Login
    </button>
</form>
                
                <div class="demo-credentials">
                    <h6><i class="fas fa-info-circle"></i> Demo Credentials</h6>
                    <p><strong>Super Admin:</strong> superadmin@example.com / password</p>
                    <p><strong>Admin:</strong> admin@example.com / password</p>
                    <p><strong>Manager:</strong> manager@example.com / password</p>
                    <p><strong>Employee:</strong> employee@example.com / password</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>