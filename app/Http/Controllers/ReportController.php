<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LaundryOrder;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $month = $validated['month'] ?? now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', $month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $payments = Payment::query()->whereBetween('paid_at', [$start, $end]);
        $orders = LaundryOrder::query()->whereBetween('created_at', [$start, $end]);

        return view('admin.reports', [
            'month' => $month,
            'revenue' => (clone $payments)->sum('amount'),
            'paymentCount' => (clone $payments)->count(),
            'orderCount' => (clone $orders)->count(),
            'completedCount' => (clone $orders)->where('status', 'completed')->count(),
            'statusCounts' => (clone $orders)
                ->select('status')
                ->selectRaw('COUNT(*) as order_count')
                ->groupBy('status')
                ->get()
                ->pluck('order_count', 'status'),
            'statuses' => LaundryOrder::STATUSES,
            'appointmentCount' => Appointment::query()->whereBetween('created_at', [$start, $end])->count(),
            'dailyRevenue' => (clone $payments)
                ->selectRaw('DATE(paid_at) as day, SUM(amount) as total, COUNT(*) as transaction_count')
                ->groupByRaw('DATE(paid_at)')
                ->orderBy('day')
                ->get(),
            'recentPayments' => Payment::query()
                ->with(['order.customer', 'receiver'])
                ->whereBetween('paid_at', [$start, $end])
                ->latest('paid_at')
                ->limit(20)
                ->get(),
        ]);
    }
}
