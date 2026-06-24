<?php

namespace App\Http\Controllers\Advisor;

use App\Http\Controllers\Controller;
use App\Models\RepairOrder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $advisorId = Auth::user()->advisor_id;

        $advisorOrderCount = RepairOrder::where('advisor_id', $advisorId)->count();

        $advisorTodayCount = RepairOrder::where('advisor_id', $advisorId)
            ->whereDate('date_of_service', today())
            ->count();

        $recentOrders = RepairOrder::with(['customer', 'vehicle'])
            ->where('advisor_id', $advisorId)
            ->orderBy('order_no', 'desc')
            ->take(5)
            ->get();

        return view('advisor.dashboard', compact(
            'advisorOrderCount',
            'advisorTodayCount',
            'recentOrders'
        ));
    }
}