@extends('customer.layouts.app')
@php $pageTitle = 'Dashboard'; @endphp

@section('content')
<div class="section-title">Welcome back, {{ $customer->first_name }}!</div>

{{-- Stat cards --}}
<div class="stat-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="stat-card c1">
        <div class="icon-wrap"><i class="ti ti-calendar-clock"></i></div>
        <div class="stat-val">{{ $pending }}</div>
        <div class="stat-lbl">Pending</div>
    </div>
    <div class="stat-card c2">
        <div class="icon-wrap"><i class="ti ti-calendar-check"></i></div>
        <div class="stat-val">{{ $confirmed }}</div>
        <div class="stat-lbl">Confirmed</div>
    </div>
    <div class="stat-card c3">
        <div class="icon-wrap"><i class="ti ti-circle-check"></i></div>
        <div class="stat-val">{{ $completed }}</div>
        <div class="stat-lbl">Completed</div>
    </div>
</div>

{{-- Upcoming appointments --}}
<div class="panel">
    <div class="panel-header">
        <div class="panel-title">Upcoming Appointments</div>
        <a href="{{ route('customer.appointments.index') }}" class="panel-link">
            View all / Book new
        </a>
    </div>
    @forelse($upcoming as $apt)
    <div class="order-row">
        <div class="order-dot" style="background:#4f46e5"></div>
        <div class="order-info">
            <div class="order-id">
                @forelse($apt->serviceTypes as $st)
                    <span class="service-badge">{{ $st->service_type_name }}</span>
                @empty
                    {{ $apt->serviceType->service_type_name ?? '—' }}
                @endforelse
            </div>
            <div class="order-meta">
                {{ $apt->vehicle->plate_number ?? '—' }} &mdash;
                {{ \Carbon\Carbon::parse($apt->appointment_time)->format('h:i A') }}
                @if($apt->advisor)
                    &mdash; Advisor: {{ $apt->advisor->first_name }} {{ $apt->advisor->last_name }}
                @endif
            </div>
        </div>
        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:4px;">
            <span class="status-badge status-{{ $apt->status }}">{{ ucfirst($apt->status) }}</span>
            <div class="order-date">{{ \Carbon\Carbon::parse($apt->appointment_date)->format('M d, Y') }}</div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <i class="ti ti-calendar"></i>
        <p>No upcoming appointments</p>
        <a href="{{ route('customer.appointments.index') }}" class="btn-primary" style="margin-top:12px; display:inline-flex;">
            <i class="ti ti-plus"></i> Book an Appointment
        </a>
    </div>
    @endforelse
</div>
@endsection
