@php $currentUser = App\Models\User::find(Auth::id()); @endphp

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <div class="logo-icon">
                <i class="ti ti-car"></i>
            </div>
            <div class="logo-text">
                AutoRepair
                <span>Management System</span>
            </div>
        </div>
    </div>

    <div class="nav-section">
        <div class="nav-label">Main</div>
        <a href="{{ route('advisor.dashboard') }}"
            class="nav-item {{ request()->routeIs('advisor.dashboard') ? 'active' : '' }}">
            <i class="ti ti-layout-dashboard"></i> Dashboard
        </a>

        <div class="nav-label">Maintenance</div>

        @if($currentUser->hasPermission('customer', 'view'))
        <a href="{{ route('advisor.customers.index') }}"
            class="nav-item {{ request()->routeIs('advisor.customers.*') ? 'active' : '' }}">
            <i class="ti ti-users"></i> Customers
        </a>
        @endif

        @if($currentUser->hasPermission('vehicle', 'view'))
        <a href="{{ route('advisor.vehicles.index') }}"
            class="nav-item {{ request()->routeIs('advisor.vehicles.*') ? 'active' : '' }}">
            <i class="ti ti-car"></i> Vehicles
        </a>
        @endif

        @if($currentUser->hasPermission('repair_order', 'view'))
        <a href="{{ route('advisor.repair_orders.index') }}"
            class="nav-item {{ request()->routeIs('advisor.repair_orders.*') ? 'active' : '' }}">
            <i class="ti ti-clipboard-list"></i> Repair Orders
        </a>
        @endif

        @if($currentUser->hasPermission('appointment', 'view'))
        <a href="{{ route('advisor.appointments.index') }}"
            class="nav-item {{ request()->routeIs('advisor.appointments.*') ? 'active' : '' }}">
            <i class="ti ti-calendar"></i> Appointments
        </a>
        @endif

        @if($currentUser->hasPermission('audit_log', 'view') || $currentUser->hasPermission('login_log', 'view') || $currentUser->hasPermission('database', 'view'))
        <div class="nav-label">System</div>
        @endif

        @if($currentUser->hasPermission('audit_log', 'view'))
        <a href="{{ route('advisor.audit.index') }}"
            class="nav-item {{ request()->routeIs('advisor.audit.*') ? 'active' : '' }}">
            <i class="ti ti-clipboard-data"></i> Audit Logs
        </a>
        @endif

        @if($currentUser->hasPermission('login_log', 'view'))
        <a href="{{ route('advisor.login_logs.index') }}"
            class="nav-item {{ request()->routeIs('advisor.login_logs.*') ? 'active' : '' }}">
            <i class="ti ti-shield-lock"></i> Login Logs
        </a>
        @endif

        @if($currentUser->hasPermission('database', 'view'))
        <a href="{{ route('advisor.database.index') }}"
            class="nav-item {{ request()->routeIs('advisor.database.*') ? 'active' : '' }}">
            <i class="ti ti-database"></i> View Database
        </a>
        @endif

    </div>

    <div class="sidebar-footer">
        <div class="user-pill">
            <div class="avatar">
                {{ strtoupper(substr(Auth::user()->username, 0, 2)) }}
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->username }}</div>
                <div class="user-role">Service Advisor</div>
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