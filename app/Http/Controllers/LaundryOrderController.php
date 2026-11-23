<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LaundryOrder;
use App\Models\LaundrySetting;
use App\Models\Payment;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaundryOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $this->visibleOrders($request)
            ->with(['customer', 'payments'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', ['orders' => $orders]);
    }

    public function create(Request $request): View
    {
        $appointment = null;

        if ($request->filled('appointment')) {
            $appointment = Appointment::query()
                ->with('customer')
                ->where('status', 'approved')
                ->findOrFail($request->integer('appointment'));
        }

        return view('orders.create', [
            'customers' => User::query()->where('role', 'customer')->where('is_active', true)->orderBy('name')->get(),
            'settings' => LaundrySetting::current(),
            'appointment' => $appointment,
        ]);
    }

    public function store(Request $request, SmsService $smsService): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'customer')->where('is_active', true)],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
            'weight' => ['required', 'numeric', 'gt:0', 'max:500'],
            'service_type' => ['required', Rule::in(['partial', 'full'])],
            'press_service' => ['sometimes', 'boolean'],
            'due_at' => ['nullable', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $settings = LaundrySetting::current();
        $weight = (float) $validated['weight'];

        $order = DB::transaction(function () use ($request, $validated, $settings, $weight): LaundryOrder {
            $appointment = null;
            $serviceType = $validated['service_type'];
            $pressService = $request->boolean('press_service');

            if (! empty($validated['appointment_id'])) {
                $appointment = Appointment::query()->lockForUpdate()->findOrFail($validated['appointment_id']);

                if ($appointment->status !== 'approved'
                    || (int) $appointment->customer_id !== (int) $validated['customer_id']
                    || $appointment->laundryOrder()->exists()) {
                    abort(422, 'The selected appointment is not available for this customer.');
                }

                $appointment->update(['status' => 'fulfilled']);
                $serviceType = $appointment->service_type;
                $pressService = $appointment->press_service;
            }

            $fullServiceAddon = $serviceType === 'full' ? (float) $settings->full_service_addon_per_kg : 0;
            $total = round($weight * (
                (float) $settings->wash_price_per_kg
                + $fullServiceAddon
                + ($pressService ? (float) $settings->press_price_per_kg : 0)
            ), 2);

            return LaundryOrder::query()->create([
                'order_number' => 'CL-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
                'customer_id' => $validated['customer_id'],
                'appointment_id' => $appointment?->id,
                'received_by' => $request->user()->id,
                'weight' => $weight,
                'press_service' => $pressService,
                'service_type' => $serviceType,
                'wash_price_per_kg' => $settings->wash_price_per_kg,
                'press_price_per_kg' => $settings->press_price_per_kg,
                'full_service_addon_per_kg' => $fullServiceAddon,
                'total_amount' => $total,
                'status' => 'received',
                'due_at' => $validated['due_at'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        $smsWarning = $smsService->notify(
            $order->customer,
            'Laundry received',
            'Order '.$order->order_number.' was received. Total: ₱'.number_format((float) $order->total_amount, 2).'.',
            route('orders.show', $order),
            $order,
            sendSms: false,
        );

        return redirect()
            ->route('orders.show', $order)
            ->with('status', 'Laundry order created.')
            ->with('warning', $smsWarning);
    }

    public function show(Request $request, LaundryOrder $laundryOrder): View
    {
        abort_unless(
            ! $request->user()->isCustomer() || (int) $laundryOrder->customer_id === (int) $request->user()->id,
            404,
        );

        return view('orders.show', [
            'order' => $laundryOrder->load(['customer', 'receiver', 'payments.receiver', 'appointment']),
            'paidAmount' => $laundryOrder->payments()->sum('amount'),
        ]);
    }

    public function updateStatus(Request $request, LaundryOrder $laundryOrder, SmsService $smsService): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(LaundryOrder::STATUSES)],
        ]);

        if (! in_array($validated['status'], LaundryOrder::TRANSITIONS[$laundryOrder->status] ?? [], true)) {
            return back()->withErrors(['status' => 'That status change is not allowed. Follow the laundry workflow.']);
        }

        if (! in_array($validated['status'], $laundryOrder->availableTransitions(), true)) {
            return back()->withErrors(['status' => 'That status change is not allowed for this service.']);
        }

        $laundryOrder->update(['status' => $validated['status']]);

        $smsWarning = $smsService->notify(
            $laundryOrder->customer,
            'Laundry status update',
            'Order '.$laundryOrder->order_number.' is now '.str_replace('_', ' ', $validated['status']).'.',
            route('orders.show', $laundryOrder),
            $laundryOrder,
            sendSms: $validated['status'] === 'ready_for_pickup',
        );

        return back()
            ->with('status', 'Order status updated.')
            ->with('warning', $smsWarning);
    }

    public function storePayment(Request $request, LaundryOrder $laundryOrder): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'method' => ['required', Rule::in(['cash', 'gcash', 'card', 'bank_transfer'])],
            'reference' => ['nullable', 'string', 'max:120'],
        ]);

        $wasAdded = DB::transaction(function () use ($request, $validated, $laundryOrder): bool {
            $lockedOrder = LaundryOrder::query()->lockForUpdate()->findOrFail($laundryOrder->id);
            $paidAmount = (float) $lockedOrder->payments()->sum('amount');
            $balance = round((float) $lockedOrder->total_amount - $paidAmount, 2);
            $amount = round((float) $validated['amount'], 2);

            if ($amount > $balance) {
                return false;
            }

            Payment::query()->create([
                'laundry_order_id' => $lockedOrder->id,
                'received_by' => $request->user()->id,
                'amount' => $amount,
                'method' => $validated['method'],
                'reference' => $validated['reference'] ?? null,
                'paid_at' => now(),
            ]);

            return true;
        });

        if (! $wasAdded) {
            return back()->withErrors(['amount' => 'Payment cannot exceed the remaining balance.']);
        }

        return back()->with('status', 'Payment recorded.');
    }

    private function visibleOrders(Request $request): Builder
    {
        return LaundryOrder::query()
            ->when(
                $request->user()->isCustomer(),
                fn (Builder $query) => $query->where('customer_id', $request->user()->id),
            )
            ->when(
                $request->filled('status') && in_array($request->query('status'), LaundryOrder::STATUSES, true),
                fn (Builder $query) => $query->where('status', $request->query('status')),
            );
    }
}
