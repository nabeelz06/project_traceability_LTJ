<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* Animated background circles */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.1);
            animation: float 6s ease-in-out infinite;
        }

        body::before {
            width: 300px;
            height: 300px;
            top: -100px;
            right: -100px;
            animation-delay: 0s;
        }

        body::after {
            width: 400px;
            height: 400px;
            bottom: -150px;
            left: -150px;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }
        
        .login-container {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 450px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .login-header .logo-wrapper {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            box-shadow: 0 10px 30px rgba(13,110,253,0.3);
        }

        .login-header .logo-wrapper i {
            font-size: 2.5rem;
            color: white;
        }
        
        .login-header img {
            height: 70px;
            margin-bottom: 1.5rem;
        }
        
        .login-header h1 {
            color: #0b2545;
            font-size: 1.85rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .login-header p {
            color: rgba(11,37,69,0.6);
            font-size: 0.95rem;
            font-weight: 400;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #0b2545;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid rgba(11,37,69,0.1);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', inherit;
            background: rgba(255,255,255,0.9);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13,110,253,0.1);
            background: white;
        }

        .form-control::placeholder {
            color: rgba(11,37,69,0.4);
        }
        
        .form-check {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 1.5rem;
        }

        .form-check input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #0d6efd;
        }

        .form-check label {
            margin: 0;
            color: #0b2545;
            font-weight: 500;
            cursor: pointer;
        }
        
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', inherit;
            box-shadow: 0 6px 20px rgba(13,110,253,0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(13,110,253,0.4);
            background: linear-gradient(135deg, #0b5ed7 0%, #2a8fef 100%);
        }

        .btn-login:active {
            transform: translateY(-1px);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 1.25rem;
        }
        
        .forgot-password a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .forgot-password a:hover {
            color: #0b5ed7;
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            color: #721c24;
            border: 2px solid rgba(220,53,69,0.2);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #155724;
            border: 2px solid rgba(25,135,84,0.2);
        }

        .alert i {
            margin-right: 0.5rem;
        }

        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(11,37,69,0.1);
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
            color: rgba(11,37,69,0.5);
            font-size: 0.85rem;
            font-weight: 600;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
                margin: 1rem;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-wrapper">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>Welcome!</h1>
            <p>PT Timah REE Traceability System</p>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif
        
        @if(session('status'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i>
                {{ session('status') }}
            </div>
        @endif
        
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="email">
                    <i class="bi bi-envelope me-1"></i> Email Address
                </label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="your.email@example.com" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="password">
                    <i class="bi bi-lock me-1"></i> Password
                </label>
                <input type="password" id="password" name="password" class="form-control" 
                       placeholder="Enter your password" required>
            </div>
            
            <div class="form-check">
                <input type="checkbox" id="remember" name="remember" value="1">
                <label for="remember">Remember me on this device</label>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Sign In
            </button>
            
            <div class="forgot-password">
                <a href="{{ route('password.request') }}">
                    <i class="bi bi-key me-1"></i>
                    Forgot your password?
                </a>
            </div>
        </form>
    </div>
</body>
</html>