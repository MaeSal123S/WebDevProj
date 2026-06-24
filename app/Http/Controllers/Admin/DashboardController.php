<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\RepairOrder;
use App\Models\ServiceType;
use App\Models\Appointment;
use App\Models\Supply;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Basic counts
        $customerCount     = Customer::count();
        $vehicleCount      = Vehicle::count();
        $repairOrderCount  = RepairOrder::count();
        $serviceTypeCount  = ServiceType::count();

        // Appointment counts
        $pendingAppointments   = Appointment::where('status', 'pending')->count();
        $confirmedAppointments = Appointment::where('status', 'confirmed')->count();
        $todayAppointments     = Appointment::whereDate('appointment_date', today())->count();

        // Revenue this month
        $revenueThisMonth = RepairOrder::with('serviceTypes')
            ->whereMonth('date_of_service', now()->month)
            ->whereYear('date_of_service', now()->year)
            ->get()
            ->sum(function ($order) {
                return $order->serviceTypes->sum(function ($st) {
                    return $st->predetermined_hours * $st->book_rate;
                });
            });

        // Revenue last month
        $revenueLastMonth = RepairOrder::with('serviceTypes')
            ->whereMonth('date_of_service', now()->subMonth()->month)
            ->whereYear('date_of_service', now()->subMonth()->year)
            ->get()
            ->sum(function ($order) {
                return $order->serviceTypes->sum(function ($st) {
                    return $st->predetermined_hours * $st->book_rate;
                });
            });

        // Orders this month vs last month
        $ordersThisMonth = RepairOrder::whereMonth('date_of_service', now()->month)
            ->whereYear('date_of_service', now()->year)
            ->count();

        $ordersLastMonth = RepairOrder::whereMonth('date_of_service', now()->subMonth()->month)
            ->whereYear('date_of_service', now()->subMonth()->year)
            ->count();

        // Most availed service type
        $mostAvailed = DB::table('repair_item')
            ->join('service_type', 'repair_item.service_type_id', 'service_type.service_type_id')
            ->select('service_type.service_type_name', DB::raw('COUNT(*) as count'))
            ->groupBy('service_type.service_type_id', 'service_type.service_type_name')
            ->orderBy('count', 'desc')
            ->first();

        // Low stock supplies
        $lowStockSupplies = Supply::where('current_stock', '<=', DB::raw('minimum_stock'))
            ->orderBy('supply_name')
            ->get();

        // Recent repair orders
        $recentOrders = RepairOrder::with(['customer', 'vehicle'])
            ->orderBy('order_no', 'desc')
            ->take(5)
            ->get();

        // Recent audit logs
        $recentLogs = AuditLog::with('user')
            ->orderBy('timestamp', 'desc')
            ->take(5)
            ->get();

        // Today's appointments
        $todayAppointmentList = Appointment::with(['customer', 'serviceType', 'advisor'])
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time', 'asc')
            ->get();

        // Monthly orders chart data (last 6 months)
        $monthlyOrders = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);
            return [
                'month' => $month->format('M Y'),
                'count' => RepairOrder::whereMonth('date_of_service', $month->month)
                    ->whereYear('date_of_service', $month->year)
                    ->count(),
            ];
        });

        // Appointment status breakdown
        $appointmentStatus = [
            'pending'   => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'cancelled' => Appointment::where('status', 'cancelled')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
        ];

        return view('admin.dashboard', compact(
            'customerCount',
            'vehicleCount',
            'repairOrderCount',
            'serviceTypeCount',
            'pendingAppointments',
            'confirmedAppointments',
            'todayAppointments',
            'revenueThisMonth',
            'revenueLastMonth',
            'ordersThisMonth',
            'ordersLastMonth',
            'mostAvailed',
            'lowStockSupplies',
            'recentOrders',
            'recentLogs',
            'todayAppointmentList',
            'monthlyOrders',
            'appointmentStatus'
        ));
    }
}