<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            animation: pulse 4s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            border-radius: 50%;
            bottom: -80px;
            left: -80px;
            animation: pulse 4s ease-in-out infinite 2s;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .error-container {
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 3.5rem 3rem;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
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

        .error-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #e74a3b 0%, #ff6b7a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 10px 30px rgba(231,74,59,0.3);
            animation: shake 1s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px) rotate(-5deg); }
            75% { transform: translateX(5px) rotate(5deg); }
        }

        .error-icon i {
            font-size: 3rem;
            color: white;
        }

        h1 {
            color: #e74a3b;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: -1px;
        }

        .error-code {
            font-size: 1.2rem;
            color: rgba(11,37,69,0.6);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        p {
            color: rgba(11,37,69,0.7);
            font-size: 1.05rem;
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        .btn-home {
            display: inline-block;
            padding: 0.9rem 2rem;
            background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(13,110,253,0.3);
        }

        .btn-home:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(13,110,253,0.4);
        }

        .btn-home i {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <div class="error-code">ERROR 500</div>
        <h1>Server Error</h1>
        <p>Something went wrong on our server. We're working to fix the issue. Please try again later or contact support if the problem persists.</p>
        <a href="{{ route('dashboard') }}" class="btn-home">
            <i class="bi bi-house-door"></i>
            Back to Dashboard
        </a>
    </div>
</body>
</html>