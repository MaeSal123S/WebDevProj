<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AutoRepair | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        body { background: #f2f2f2; }
    </style>
</head>
<body>
<div class="app-shell">
    @include('admin.layouts.sidebar')
    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div class="page-title">{{ $pageTitle ?? 'Dashboard' }}</div>
                <div class="breadcrumb-text">Home / {{ $pageTitle ?? 'Dashboard' }}</div>
            </div>
            <div class="topbar-right">
                <div class="icon-btn">
                    <i class="ti ti-bell"></i>
                </div>
                <div class="icon-btn">
                    <i class="ti ti-settings"></i>
                </div>
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