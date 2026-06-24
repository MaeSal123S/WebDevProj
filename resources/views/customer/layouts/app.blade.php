<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Customer Portal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="app-shell">
    {{-- Sidebar --}}
    <div class="sidebar">
        <div class="sidebar-brand">
            <div class="logo">
                <div class="logo-icon"><i class="ti ti-car"></i></div>
                <div class="logo-text">
                    AutoRepair
                    <span>Customer Portal</span>
                </div>
            </div>
        </div>

        <div class="nav-section">
            <div class="nav-label">Main</div>
            <a href="{{ route('customer.dashboard') }}"
               class="nav-item {{ request()->routeIs('customer.dashboard') ? 'active' : '' }}">
                <i class="ti ti-layout-dashboard"></i> Dashboard
            </a>
            <a href="{{ route('customer.appointments.index') }}"
               class="nav-item {{ request()->routeIs('customer.appointments.*') ? 'active' : '' }}">
                <i class="ti ti-calendar"></i> My Appointments
            </a>
            <a href="{{ route('customer.profile') }}"
               class="nav-item {{ request()->routeIs('customer.profile') ? 'active' : '' }}">
                <i class="ti ti-user"></i> My Profile & Vehicles
            </a>
        </div>

        <div class="sidebar-footer">
            <div class="user-pill">
                <div class="avatar">
                    {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
                </div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->username }}</div>
                    <div class="user-role">Customer</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Logout">
                        <i class="ti ti-logout" style="font-size:18px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Main content --}}
    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="page-title">{{ $pageTitle ?? 'Dashboard' }}</div>
                <div class="breadcrumb-text">Home / {{ $pageTitle ?? 'Dashboard' }}</div>
            </div>
            <div class="topbar-right">
                <div class="icon-btn"><i class="ti ti-bell"></i></div>
            </div>
        </div>
        <div class="content">
            @if(session('success'))
                <div class="alert-success">
                    <i class="ti ti-circle-check"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert-error">
                    <i class="ti ti-circle-x"></i>
                    {{ session('error') }}
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@yield('scripts')
</body>
</html>
