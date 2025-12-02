<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        
        /* CSS Variables - PT Timah Blue Theme */
        :root {
            --timah-blue: #3e5c74;
            --timah-blue-dark: #2d4454;
            --timah-blue-light: #e8eef3;
            --timah-gold: #c5a572;
            --timah-grey: #6c757d;
            --success: #28a745;
            --info: #0dcaf0;
            --warning: #ffc107;
            --danger: #dc3545;
            --white: #ffffff;
            --dark: #2c3e50;
            --glass: rgba(255,255,255,0.98);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, var(--timah-blue-light) 0%, #f5f7fa 100%);
            color: var(--dark);
            min-height: 100vh;
        }
        
        /* Topbar Header */
        .topbar {
            background: linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(255,255,255,0.95) 100%);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(62,92,116,0.15);
            height: 75px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2.5rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 3px solid var(--timah-blue);
        }
        
        .topbar-logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .topbar-logo img {
            height: 50px;
            transition: transform 0.3s ease;
        }

        .topbar-logo img:hover {
            transform: scale(1.05);
        }
        
        .topbar-logo h4 {
            background: linear-gradient(135deg, var(--timah-blue) 0%, var(--timah-blue-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 1.35rem;
            letter-spacing: -0.5px;
        }
        
        .topbar-nav {
            display: flex;
            gap: 0.5rem;
            list-style: none;
        }
        
        .topbar-nav a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            padding: 0.7rem 1.25rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .topbar-nav a:hover {
            background: linear-gradient(135deg, rgba(62,92,116,0.1) 0%, rgba(45,68,84,0.1) 100%);
            transform: translateY(-2px);
        }
        
        .topbar-nav a.active {
            background: linear-gradient(135deg, var(--timah-blue) 0%, var(--timah-blue-dark) 100%);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(62,92,116,0.3);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .topbar-clock {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            padding: 0.5rem 1rem;
            background: rgba(62,92,116,0.08);
            border-radius: 10px;
            border: 1px solid rgba(62,92,116,0.15);
        }

        .clock-time {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--timah-blue);
            font-variant-numeric: tabular-nums;
        }

        .clock-date {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--timah-grey);
            margin-top: 0.1rem;
        }
        
        .topbar-profile {
            position: relative;
        }
        
        .profile-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.5);
        }
        
        .profile-toggle:hover {
            background: rgba(62,92,116,0.08);
            transform: translateY(-2px);
        }
        
        .profile-name {
            font-weight: 600;
            color: var(--dark);
            font-size: 0.95rem;
        }
        
        .profile-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--timah-blue) 0%, var(--timah-blue-dark) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(62,92,116,0.3);
        }
        
        .profile-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: var(--glass);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(62,92,116,0.2);
            border-radius: 12px;
            min-width: 220px;
            display: none;
            border: 1px solid rgba(62,92,116,0.1);
            overflow: hidden;
            animation: dropdownSlide 0.3s ease;
        }

        @keyframes dropdownSlide {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .profile-dropdown.show {
            display: block;
        }
        
        .profile-dropdown a,
        .profile-dropdown button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.9rem 1.25rem;
            color: var(--dark);
            text-decoration: none;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            font-family: 'Poppins', inherit;
        }
        
        .profile-dropdown a:hover,
        .profile-dropdown button:hover {
            background: linear-gradient(135deg, rgba(62,92,116,0.08) 0%, rgba(45,68,84,0.08) 100%);
            padding-left: 1.5rem;
        }

        .profile-dropdown button {
            border-top: 1px solid rgba(62,92,116,0.1);
            color: var(--danger);
        }
        
        .main-content {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 150px);
        }
        
        .card {
            background: var(--glass);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border-radius: 14px;
            box-shadow: 0 10px 28px rgba(62,92,116,0.1);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(62,92,116,0.08);
        }
        
        .card-header {
            padding: 1.25rem 1.5rem;
            background: linear-gradient(135deg, rgba(62,92,116,0.08) 0%, rgba(45,68,84,0.08) 100%);
            border-bottom: 2px solid rgba(62,92,116,0.15);
            font-weight: 700;
            color: var(--dark);
            font-size: 1.1rem;
            border-radius: 14px 14px 0 0;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .badge-primary { 
            background: linear-gradient(135deg, var(--timah-blue) 0%, var(--timah-blue-dark) 100%);
            color: var(--white); 
        }
        .badge-secondary { 
            background: linear-gradient(135deg, var(--timah-grey) 0%, #8a909b 100%);
            color: var(--white); 
        }
        .badge-success { 
            background: linear-gradient(135deg, var(--success) 0%, #4bd08f 100%);
            color: var(--white); 
        }
        .badge-info { 
            background: linear-gradient(135deg, var(--info) 0%, #7be6ff 100%);
            color: var(--white); 
        }
        .badge-warning { 
            background: linear-gradient(135deg, var(--warning) 0%, #ffd86b 100%);
            color: #000; 
        }
        .badge-danger { 
            background: linear-gradient(135deg, var(--danger) 0%, #ff6b7a 100%);
            color: var(--white); 
        }
        
        .btn {
            display: inline-block;
            padding: 0.65rem 1.25rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            font-family: 'Poppins', inherit;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--timah-blue) 0%, var(--timah-blue-dark) 100%);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(62,92,116,0.25);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(62,92,116,0.35);
        }
        
        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #4bd08f 100%);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(40,167,69,0.2);
        }
        
        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(40,167,69,0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #ff6b7a 100%);
            color: var(--white);
            box-shadow: 0 4px 12px rgba(220,53,69,0.2);
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(220,53,69,0.3);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--timah-blue);
            color: var(--timah-blue);
        }

        .btn-outline-primary:hover {
            background: var(--timah-blue);
            color: var(--white);
        }

        .btn-outline-warning {
            background: transparent;
            border: 2px solid var(--warning);
            color: var(--warning);
        }

        .btn-outline-warning:hover {
            background: var(--warning);
            color: #000;
        }

        .btn-outline-secondary {
            background: transparent;
            border: 2px solid var(--timah-grey);
            color: var(--timah-grey);
        }

        .btn-outline-secondary:hover {
            background: var(--timah-grey);
            color: var(--white);
        }
        
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th,
        .table td {
            padding: 0.9rem 1rem;
            text-align: left;
        }
        
        .table th {
            background: linear-gradient(90deg, var(--timah-blue-dark) 0%, var(--timah-blue) 100%);
            color: var(--white);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table th:first-child {
            border-radius: 10px 0 0 0;
        }

        .table th:last-child {
            border-radius: 0 10px 0 0;
        }
        
        .table tbody tr {
            transition: all 0.2s ease;
            border-bottom: 1px solid rgba(62,92,116,0.08);
        }

        .table tbody tr:hover {
            background: rgba(62,92,116,0.05);
            transform: translateX(3px);
        }

        .table-responsive {
            overflow-x: auto;
        }
        
        .form-group {
            margin-bottom: 1.25rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
            font-size: 0.9rem;
        }
        
        .form-control, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(62,92,116,0.2);
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Poppins', inherit;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: var(--timah-blue);
            box-shadow: 0 0 0 0.2rem rgba(62,92,116,0.15);
            background: white;
        }
        
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            font-weight: 500;
            border: 2px solid;
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
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-color: rgba(40,167,69,0.3);
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-color: rgba(220,53,69,0.3);
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border-color: rgba(255,193,7,0.3);
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border-color: rgba(23,162,184,0.3);
        }

        .alert ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        
        .footer {
            text-align: center;
            padding: 2rem;
            color: rgba(62,92,116,0.7);
            font-size: 0.9rem;
            background: rgba(255,255,255,0.5);
            border-top: 1px solid rgba(62,92,116,0.1);
        }

        @media (max-width: 992px) {
            .topbar {
                padding: 0 1rem;
                height: auto;
                min-height: 75px;
                flex-wrap: wrap;
            }

            .topbar-nav {
                order: 3;
                width: 100%;
                justify-content: center;
                margin-top: 0.5rem;
                flex-wrap: wrap;
            }

            .topbar-clock {
                display: none;
            }

            .main-content {
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .topbar-logo h4 {
                font-size: 1.1rem;
            }

            .topbar-logo img {
                height: 40px;
            }

            .profile-name {
                display: none;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <div class="topbar">
        <div class="topbar-logo">
            <img src="{{ asset('images/logo-timah.png') }}" alt="PT Timah" onerror="this.style.display='none'">
            <h4>Traceability LTJ</h4>
        </div>
        
        <nav>
            <ul class="topbar-nav">
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </a></li>
                        <li><a href="{{ route('batches.index') }}" class="{{ request()->routeIs('batches.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i>Batch
                        </a></li>
                        <li><a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>Users
                        </a></li>
                        <li><a href="{{ route('admin.partners.index') }}" class="{{ request()->routeIs('admin.partners.*') ? 'active' : '' }}">
                            <i class="bi bi-building"></i>Partners
                        </a></li>
                        <li><a href="{{ route('admin.product-codes.index') }}" class="{{ request()->routeIs('admin.product-codes.*') ? 'active' : '' }}">
                            <i class="bi bi-upc-scan"></i>Products
                        </a></li>
                    @elseif(auth()->user()->isAdmin())
                        <li><a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>Dashboard
                        </a></li>
                        <li><a href="{{ route('batches.index') }}" class="{{ request()->routeIs('batches.*') ? 'active' : '' }}">
                            <i class="bi bi-box-seam"></i>Batch
                        </a></li>
                    @elseif(auth()->user()->isOperator())
                        <li><a href="{{ route('scan.index') }}" class="{{ request()->routeIs('scan.index') ? 'active' : '' }}">
                            <i class="bi bi-house-door"></i>Home
                        </a></li>
                        <li><a href="{{ route('scan.checkout') }}" class="{{ request()->routeIs('scan.checkout') ? 'active' : '' }}">
                            <i class="bi bi-box-arrow-right"></i>Check-Out
                        </a></li>
                        <li><a href="{{ route('scan.checkin') }}" class="{{ request()->routeIs('scan.checkin') ? 'active' : '' }}">
                            <i class="bi bi-box-arrow-in-down"></i>Check-In
                        </a></li>
                    @endif
                    
                    <li><a href="{{ route('traceability.search') }}">
                        <i class="bi bi-search"></i>Traceability
                    </a></li>
                @endauth
            </ul>
        </nav>

        <div class="topbar-right">
            <div class="topbar-clock">
                <div class="clock-time" id="currentTime">00:00:00</div>
                <div class="clock-date" id="currentDate">Loading...</div>
            </div>
        
            <div class="topbar-profile">
                <div class="profile-toggle" onclick="toggleProfileDropdown()">
                    <span class="profile-name">{{ auth()->user()->name ?? 'Guest' }}</span>
                    <div class="profile-avatar">
                        {{ substr(auth()->user()->name ?? 'G', 0, 1) }}
                    </div>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <a href="{{ route('profile') }}">
                        <i class="bi bi-person-circle"></i>
                        Account Settings
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ session('error') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
    </div>
    
    <div class="footer">
        <i class="bi bi-c-circle me-1"></i>
        Copyright © Traceability System - PT Timah {{ date('Y') }}
    </div>
    
    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        document.addEventListener('click', function(event) {
            const profile = document.querySelector('.topbar-profile');
            if (!profile.contains(event.target)) {
                document.getElementById('profileDropdown').classList.remove('show');
            }
        });
        
        function updateClock() {
            const now = new Date();
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            const dayName = days[now.getDay()];
            const day = now.getDate();
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();
            const dateString = `${dayName}, ${day} ${monthName} ${year}`;
            
            document.getElementById('currentTime').textContent = timeString;
            document.getElementById('currentDate').textContent = dateString;
        }
        
        updateClock();
        setInterval(updateClock, 1000);
        
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>