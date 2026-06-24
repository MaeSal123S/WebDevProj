@extends('admin.layouts.app')
@section('content')

<!-- STAT CARDS ROW 1 -->
<div class="section-title">Overview</div>
<div class="stat-grid">
    <div class="stat-card c1">
        <div class="icon-wrap"><i class="ti ti-users"></i></div>
        <div class="stat-val">{{ $customerCount }}</div>
        <div class="stat-lbl">Customers</div>
    </div>
    <div class="stat-card c2">
        <div class="icon-wrap"><i class="ti ti-car"></i></div>
        <div class="stat-val">{{ $vehicleCount }}</div>
        <div class="stat-lbl">Vehicles</div>
    </div>
    <div class="stat-card c3">
        <div class="icon-wrap"><i class="ti ti-clipboard-list"></i></div>
        <div class="stat-val">{{ $repairOrderCount }}</div>
        <div class="stat-lbl">Repair Orders</div>
    </div>
    <div class="stat-card c4">
        <div class="icon-wrap"><i class="ti ti-tools"></i></div>
        <div class="stat-val">{{ $serviceTypeCount }}</div>
        <div class="stat-lbl">Service Types</div>
    </div>
</div>

<!-- STAT CARDS ROW 2 -->
<div class="stat-grid" style="margin-top:0">
    <div class="stat-card c1">
        <div class="icon-wrap"><i class="ti ti-currency-peso"></i></div>
        <div class="stat-val">₱{{ number_format($revenueThisMonth, 2) }}</div>
        <div class="stat-lbl">Revenue this month</div>
        @if($revenueLastMonth > 0)
        <div style="font-size:11px; margin-top:4px; color: {{ $revenueThisMonth >= $revenueLastMonth ? '#0f6e56' : '#993c1d' }}">
            {{ $revenueThisMonth >= $revenueLastMonth ? '▲' : '▼' }}
            ₱{{ number_format(abs($revenueThisMonth - $revenueLastMonth), 2) }} vs last month
        </div>
        @endif
    </div>
    <div class="stat-card c2">
        <div class="icon-wrap"><i class="ti ti-clipboard-check"></i></div>
        <div class="stat-val">{{ $ordersThisMonth }}</div>
        <div class="stat-lbl">Orders this month</div>
        @if($ordersLastMonth > 0)
        <div style="font-size:11px; margin-top:4px; color: {{ $ordersThisMonth >= $ordersLastMonth ? '#0f6e56' : '#993c1d' }}">
            {{ $ordersThisMonth >= $ordersLastMonth ? '▲' : '▼' }}
            {{ abs($ordersThisMonth - $ordersLastMonth) }} vs last month
        </div>
        @endif
    </div>
    <div class="stat-card c3">
        <div class="icon-wrap"><i class="ti ti-calendar"></i></div>
        <div class="stat-val">{{ $todayAppointments }}</div>
        <div class="stat-lbl">Appointments today</div>
    </div>
    <div class="stat-card c4">
        <div class="icon-wrap"><i class="ti ti-star"></i></div>
        <div class="stat-val" style="font-size:14px;">{{ $mostAvailed->service_type_name ?? '—' }}</div>
        <div class="stat-lbl">Most availed service</div>
    </div>
</div>

<!-- LOW STOCK ALERT -->
@if($lowStockSupplies->count() > 0)
<div class="low-stock-alert">
    <i class="ti ti-alert-triangle"></i>
    <strong>Low Stock Alert!</strong>
    {{ $lowStockSupplies->count() }} item(s) running low:
    {{ $lowStockSupplies->pluck('supply_name')->join(', ') }}
    <a href="{{ route('admin.inventory.index') }}"
       style="margin-left:8px; color:#854f0b; font-weight:500;">View Inventory →</a>
</div>
@endif

<!-- CHARTS ROW -->
<div class="bottom-grid" style="margin-bottom:12px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Monthly repair orders (last 6 months)</div>
        </div>
        <canvas id="ordersChart" height="120"></canvas>
    </div>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Appointment status breakdown</div>
        </div>
        <canvas id="appointmentChart" height="120"></canvas>
    </div>
</div>

<!-- BOTTOM PANELS -->
<div class="bottom-grid" style="margin-bottom:12px;">
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Today's appointments</div>
            <a href="{{ route('admin.appointments.index') }}" class="panel-link">View all</a>
        </div>
        @forelse($todayAppointmentList as $apt)
        <div class="order-row">
            <div class="order-dot" style="background:#4f46e5"></div>
            <div class="order-info">
                <div class="order-id">
                    {{ $apt->customer->first_name ?? '—' }} {{ $apt->customer->last_name ?? '' }}
                </div>
                <div class="order-meta">
                    {{ $apt->serviceType->service_type_name ?? '—' }} —
                    {{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}
                </div>
            </div>
            <span class="status-badge status-{{ $apt->status }}">
                {{ ucfirst($apt->status) }}
            </span>
        </div>
        @empty
        <div class="empty-state">
            <i class="ti ti-calendar"></i>
            <p>No appointments today</p>
        </div>
        @endforelse
    </div>

    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Recent repair orders</div>
            <a href="{{ route('admin.repair_orders.index') }}" class="panel-link">View all</a>
        </div>
        @forelse($recentOrders as $order)
        <div class="order-row">
            <div class="order-dot"></div>
            <div class="order-info">
                <div class="order-id">
                    #ORD-{{ str_pad($order->order_no, 3, '0', STR_PAD_LEFT) }}
                </div>
                <div class="order-meta">
                    {{ $order->customer->first_name ?? '—' }} {{ $order->customer->last_name ?? '' }}
                    — {{ $order->vehicle->plate_number ?? '—' }}
                </div>
            </div>
            <div class="order-date">{{ $order->date_of_service }}</div>
        </div>
        @empty
        <div class="empty-state">
            <i class="ti ti-clipboard-list"></i>
            <p>No repair orders yet</p>
        </div>
        @endforelse
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Recent activity</div>
        <a href="{{ route('admin.audit.index') }}" class="panel-link">View all</a>
    </div>
    @forelse($recentLogs as $log)
    <div class="order-row">
        @php
            $badgeClass = match($log->action) {
                'INSERT'        => 'badge-insert',
                'UPDATE'        => 'badge-update',
                'DELETE'        => 'badge-delete',
                'LOGIN'         => 'badge-login',
                'LOGOUT'        => 'badge-logout',
                'LOGIN_FAILED'  => 'badge-failed',
                'LOGIN_LOCKED'  => 'badge-locked',
                'LOGIN_BLOCKED' => 'badge-locked',
                'PASSWORD_RESET'=> 'badge-reset',
                default         => 'badge-update'
            };
        @endphp
        <span class="action-badge {{ $badgeClass }}">{{ $log->action }}</span>
        <div class="order-info">
            <div class="order-id">{{ $log->changes }}</div>
            <div class="order-meta">by {{ $log->user->username ?? '—' }}</div>
        </div>
        <div class="order-date" style="font-size:11px;color:#999;">
            {{ \Carbon\Carbon::parse($log->timestamp)->format('M d, h:i A') }}
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="ti ti-file-text"></i>
        <p>No activity yet</p>
    </div>
    @endforelse
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Monthly orders chart
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(ordersCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($monthlyOrders->pluck('month')) !!},
        datasets: [{
            label: 'Repair Orders',
            data: {!! json_encode($monthlyOrders->pluck('count')) !!},
            backgroundColor: 'rgba(192,57,43,0.75)',
            borderColor: '#e74c3c',
            borderWidth: 1,
            borderRadius: 6,
            hoverBackgroundColor: '#e74c3c',
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1e1e1e',
                titleColor: '#fff',
                bodyColor: '#ccc',
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
            }
        },
        scales: {
            x: {
                ticks: { color: '#888', font: { size: 11 } },
                grid: { color: 'rgba(255,255,255,0.05)' },
            },
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1, color: '#888', font: { size: 11 } },
                grid: { color: 'rgba(255,255,255,0.05)' },
            }
        }
    }
});

// Appointment status chart
const aptCtx = document.getElementById('appointmentChart').getContext('2d');
new Chart(aptCtx, {
    type: 'doughnut',
    data: {
        labels: ['Pending', 'Confirmed', 'Cancelled', 'Completed'],
        datasets: [{
            data: [
                {{ $appointmentStatus['pending'] }},
                {{ $appointmentStatus['confirmed'] }},
                {{ $appointmentStatus['cancelled'] }},
                {{ $appointmentStatus['completed'] }}
            ],
            backgroundColor: ['#b86e00', '#0e6b38', '#7a1515', '#3d2480'],
            borderColor:     ['#ffc14d', '#4ddb8a', '#ff6b6b', '#b388ff'],
            borderWidth: 2,
            hoverBackgroundColor: ['#d4820a', '#15a857', '#a81c1c', '#5c35b0'],
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { size: 12 },
                    color: '#ccc',
                    padding: 16,
                    usePointStyle: true,
                    pointStyleWidth: 10,
                }
            },
            tooltip: {
                backgroundColor: '#1e1e1e',
                titleColor: '#fff',
                bodyColor: '#ccc',
                borderColor: 'rgba(255,255,255,0.1)',
                borderWidth: 1,
            }
        }
    }
});
</script>
@endsection