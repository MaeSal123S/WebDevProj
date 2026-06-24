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
        <a href="{{ route('admin.dashboard') }}"
            class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="ti ti-layout-dashboard"></i> Dashboard
        </a>

        @if($currentUser->hasPermission('customer', 'view'))
        <a href="{{ route('admin.customers.index') }}"
            class="nav-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
            <i class="ti ti-users"></i> Customers
        </a>
        @endif

        @if($currentUser->hasPermission('vehicle', 'view'))
        <a href="{{ route('admin.vehicles.index') }}"
            class="nav-item {{ request()->routeIs('admin.vehicles.*') ? 'active' : '' }}">
            <i class="ti ti-car"></i> Vehicles
        </a>
        @endif

        @if($currentUser->hasPermission('repair_order', 'view'))
        <a href="{{ route('admin.repair_orders.index') }}"
            class="nav-item {{ request()->routeIs('admin.repair_orders.*') ? 'active' : '' }}">
            <i class="ti ti-clipboard-list"></i> Repair Orders
        </a>
        @endif

        @if($currentUser->hasPermission('appointment', 'view'))
        <a href="{{ route('admin.appointments.index') }}"
            class="nav-item {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
            <i class="ti ti-calendar"></i> Appointments
        </a>
        <a href="{{ route('admin.calendar') }}"
            class="nav-item {{ request()->routeIs('admin.calendar') ? 'active' : '' }}">
            <i class="ti ti-calendar-event"></i> Calendar
        </a>
        @endif

        <div class="nav-label">Maintenance</div>

        @if($currentUser->hasPermission('service_type', 'view'))
        <a href="{{ route('admin.service_types.index') }}"
            class="nav-item {{ request()->routeIs('admin.service_types.*') ? 'active' : '' }}">
            <i class="ti ti-tools"></i> Service Types
        </a>
        @endif

        @if($currentUser->hasPermission('inventory', 'view'))
        <a href="{{ route('admin.inventory.index') }}"
            class="nav-item {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
            <i class="ti ti-package"></i> Inventory
        </a>
        @endif

        @if($currentUser->hasPermission('service_advisor', 'view'))
        <a href="{{ route('admin.advisors.index') }}"
            class="nav-item {{ request()->routeIs('admin.advisors.*') ? 'active' : '' }}">
            <i class="ti ti-id-badge"></i> Advisors
        </a>
        @endif

        <div class="nav-label">System</div>

        @if($currentUser->hasPermission('users', 'view'))
        <a href="{{ route('admin.users.index') }}"
            class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="ti ti-user-cog"></i> User Management
        </a>
        @endif

        @if($currentUser->hasPermission('audit_log', 'view'))
        <a href="{{ route('admin.audit.index') }}"
            class="nav-item {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
            <i class="ti ti-clipboard-data"></i> Audit Logs
        </a>
        @endif

        @if($currentUser->hasPermission('login_log', 'view'))
        <a href="{{ route('admin.login_logs.index') }}"
            class="nav-item {{ request()->routeIs('admin.login_logs.*') ? 'active' : '' }}">
            <i class="ti ti-shield-lock"></i> Login Logs
        </a>
        @endif

        @if($currentUser->hasPermission('database', 'view'))
        <a href="{{ route('admin.database.index') }}"
            class="nav-item {{ request()->routeIs('admin.database.*') ? 'active' : '' }}">
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
                <div class="user-role">Administrator</div>
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