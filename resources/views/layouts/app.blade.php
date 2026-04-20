{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Attendance System')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Maps API -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBwifbQioUakGApIrcxcQjWOT9Y2a1Vubk&libraries=places"></script>
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            border: none;
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 600;
            padding: 15px 20px;
        }
        
        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }
        
        .btn-primary:hover {
            background-color: #3554d1;
            border-color: #3554d1;
        }
        
        .clock-button {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            font-size: 24px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .clock-button.clock-in {
            background-color: #28a745;
            border-color: #28a745;
        }
        
        .clock-button.clock-out {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .clock-button:hover {
            transform: scale(1.05);
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .status-present { background-color: #d4edda; color: #155724; }
        .status-absent { background-color: #f8d7da; color: #721c24; }
        .status-late { background-color: #fff3cd; color: #856404; }
        .status-half-day { background-color: #ffe5b4; color: #856404; }
        
        .location-status {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @media (max-width: 768px) {
            .clock-button {
                width: 150px;
                height: 150px;
                font-size: 18px;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <i class="fas fa-clock"></i> Attendance System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        @if(auth()->user()->hasRole('super-admin') || auth()->user()->hasRole('admin'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.employees') }}">
                                    <i class="fas fa-users"></i> Employees
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.locations') }}">
                                    <i class="fas fa-map-marker-alt"></i> Locations
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.attendance.report') }}">
                                    <i class="fas fa-calendar-check"></i> Attendance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.gps.reports') }}">
                                    <i class="fas fa-satellite"></i> GPS Reports
                                </a>
                            </li>
                        @elseif(auth()->user()->hasRole('manager'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.team.attendance') }}">
                                    <i class="fas fa-users"></i> Team Attendance
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('manager.team.gps') }}">
                                    <i class="fas fa-satellite"></i> GPS Tracking
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('employee.dashboard') }}">
                                    <i class="fas fa-tachometer-alt"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('employee.history') }}">
                                    <i class="fas fa-history"></i> History
                                </a>
                            </li>
                        @endif
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>