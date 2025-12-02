<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - {{ config('app.name') }}</title>
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
            background: linear-gradient(135deg, #1B8057 0%, #146644 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
            animation: float 6s ease-in-out infinite;
        }

        body::before {
            width: 400px;
            height: 400px;
            top: -150px;
            right: -100px;
        }

        body::after {
            width: 500px;
            height: 500px;
            bottom: -200px;
            left: -150px;
            animation-delay: 3s;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-30px) scale(1.05); }
        }
        
        .forgot-container {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
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
        
        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .forgot-header img {
            height: 70px;
            margin-bottom: 1.5rem;
            filter: drop-shadow(0 4px 12px rgba(27,128,87,0.2));
        }
        
        .forgot-header h1 {
            color: #1B8057;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .forgot-header p {
            color: rgba(44,62,80,0.7);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }
        
        .form-control {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid rgba(27,128,87,0.15);
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            font-family: 'Poppins', inherit;
            background: rgba(255,255,255,0.9);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #1B8057;
            box-shadow: 0 0 0 4px rgba(27,128,87,0.1);
            background: white;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #1B8057 0%, #146644 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', inherit;
            box-shadow: 0 6px 20px rgba(27,128,87,0.3);
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(27,128,87,0.4);
        }
        
        .back-to-login {
            text-align: center;
            margin-top: 1.25rem;
        }
        
        .back-to-login a {
            color: #1B8057;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .back-to-login a:hover {
            color: #146644;
            text-decoration: underline;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
            color: #721c24;
            border: 2px solid rgba(220,53,69,0.2);
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid rgba(40,167,69,0.2);
        }

        @media (max-width: 480px) {
            .forgot-container {
                padding: 2rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <img src="{{ asset('images/logo-timah.png') }}" alt="PT Timah">
            <h1>Forgot Password?</h1>
            <p>Masukkan email Anda dan kami akan mengirimkan link untuk reset password</p>
        </div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif
        
        @if(session('status'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('status') }}
            </div>
        @endif
        
        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label class="form-label" for="email">
                    <i class="bi bi-envelope me-1"></i> Email Address
                </label>
                <input type="email" id="email" name="email" class="form-control" 
                       placeholder="your.email@example.com" value="{{ old('email') }}" required autofocus>
            </div>
            
            <button type="submit" class="btn-submit">
                <i class="bi bi-send me-2"></i>
                Send Reset Link
            </button>
            
            <div class="back-to-login">
                <a href="{{ route('login') }}">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>