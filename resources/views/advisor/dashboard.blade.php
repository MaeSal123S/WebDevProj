@extends('advisor.layouts.app')
@section('content')

{{-- STAT CARDS --}}
<div class="section-title">Overview</div>
<div class="stat-grid">
    <div class="stat-card c1">
        <div class="icon-wrap"><i class="ti ti-clipboard-list"></i></div>
        <div class="stat-val">{{ $advisorOrderCount }}</div>
        <div class="stat-lbl">My Repair Orders</div>
    </div>
    <div class="stat-card c2">
        <div class="icon-wrap"><i class="ti ti-calendar-check"></i></div>
        <div class="stat-val">{{ $advisorTodayCount }}</div>
        <div class="stat-lbl">Orders Today</div>
    </div>
    <div class="stat-card c3">
        <div class="icon-wrap"><i class="ti ti-clock"></i></div>
        <div class="stat-val">{{ $pendingAppointments }}</div>
        <div class="stat-lbl">Pending Appointments</div>
    </div>
    <div class="stat-card c4">
        <div class="icon-wrap"><i class="ti ti-calendar-event"></i></div>
        <div class="stat-val">{{ $todayAppointmentCount }}</div>
        <div class="stat-lbl">Appointments Today</div>
    </div>
</div>

{{-- TODAY'S + UPCOMING APPOINTMENTS --}}
<div class="bottom-grid" style="margin-bottom:14px;">

    {{-- Today's appointments --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Today's appointments</div>
            <a href="{{ route('advisor.appointments.index') }}" class="panel-link">View all</a>
        </div>
        @forelse($todayAppointments as $apt)
        <div class="order-row">
            <div class="order-dot" style="background:var(--red);"></div>
            <div class="order-info">
                <div class="order-id">
                    {{ $apt->customer->first_name ?? '—' }} {{ $apt->customer->last_name ?? '' }}
                </div>
                <div class="order-meta">
                    @forelse($apt->serviceTypes as $st)
                        {{ $st->service_type_name }}{{ !$loop->last ? ', ' : '' }}
                    @empty
                        {{ $apt->serviceType->service_type_name ?? '—' }}
                    @endforelse
                    &mdash; {{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}
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

    {{-- Upcoming appointments --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">Upcoming appointments</div>
            <a href="{{ route('advisor.appointments.index') }}" class="panel-link">View all</a>
        </div>
        @forelse($upcomingAppointments as $apt)
        <div class="order-row">
            <div class="order-dot" style="background:#1a5f8a;"></div>
            <div class="order-info">
                <div class="order-id">
                    {{ $apt->customer->first_name ?? '—' }} {{ $apt->customer->last_name ?? '' }}
                </div>
                <div class="order-meta">
                    @forelse($apt->serviceTypes as $st)
                        {{ $st->service_type_name }}{{ !$loop->last ? ', ' : '' }}
                    @empty
                        {{ $apt->serviceType->service_type_name ?? '—' }}
                    @endforelse
                    &mdash; {{ \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y') }}
                </div>
            </div>
            <span class="status-badge status-{{ $apt->status }}">
                {{ ucfirst($apt->status) }}
            </span>
        </div>
        @empty
        <div class="empty-state">
            <i class="ti ti-calendar"></i>
            <p>No upcoming appointments</p>
        </div>
        @endforelse
    </div>

</div>

{{-- RECENT REPAIR ORDERS --}}
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">My recent repair orders</div>
        <a href="{{ route('advisor.repair_orders.index') }}" class="panel-link">View all</a>
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
                &mdash; {{ $order->vehicle->plate_number ?? '—' }}
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

@endsection
