@extends('advisor.layouts.app')
@section('content')

<div class="section-title">Overview</div>
<div class="stat-grid" style="grid-template-columns: repeat(2, 1fr);">
    <div class="stat-card c1">
        <div class="icon-wrap"><i class="ti ti-clipboard-list"></i></div>
        <div class="stat-val">{{ $advisorOrderCount }}</div>
        <div class="stat-lbl">My Repair Orders</div>
    </div>
    <div class="stat-card c2">
        <div class="icon-wrap"><i class="ti ti-calendar"></i></div>
        <div class="stat-val">{{ $advisorTodayCount }}</div>
        <div class="stat-lbl">Orders Today</div>
    </div>
</div>

<div class="panel" style="margin-top:12px">
    <div class="panel-header">
        <div class="panel-title">My recent repair orders</div>
        <a href="{{ route('advisor.repair_orders.index') }}" class="panel-link">View all</a>
    </div>
    @forelse($recentOrders as $order)
    <div class="order-row">
        <div class="order-dot"></div>
        <div class="order-info">
            <div class="order-id">#ORD-{{ str_pad($order->order_no, 3, '0', STR_PAD_LEFT) }}</div>
            <div class="order-meta">
                {{ $order->customer->first_name }} {{ $order->customer->last_name }} —
                {{ $order->vehicle->plate_number }}
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