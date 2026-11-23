<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use App\Models\Appointment;
use App\Models\LaundryOrder;
use App\Models\LaundrySetting;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $isCustomer = $user->isCustomer();

        $orders = LaundryOrder::query()
            ->with(['customer', 'payments'])
            ->when($isCustomer, fn ($query) => $query->where('customer_id', $user->id))
            ->latest()
            ->limit(8)
            ->get();

        return view('dashboard', [
            'orders' => $orders,
            'settings' => LaundrySetting::current(),
            'pendingAppointments' => $user->role === 'customer'
                ? $user->appointments()->where('status', 'pending')->count()
                : Appointment::query()->where('status', 'pending')->count(),
            'activeOrders' => LaundryOrder::query()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->when($isCustomer, fn ($query) => $query->where('customer_id', $user->id))
                ->count(),
            'readyOrders' => LaundryOrder::query()
                ->where('status', 'ready_for_pickup')
                ->when($isCustomer, fn ($query) => $query->where('customer_id', $user->id))
                ->count(),
            'todayRevenue' => $isCustomer ? null : Payment::query()
                ->whereDate('paid_at', today())
                ->sum('amount'),
            'unreadNotifications' => ActivityNotification::query()
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
