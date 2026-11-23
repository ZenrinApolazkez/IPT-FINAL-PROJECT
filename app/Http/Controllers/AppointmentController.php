<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\LaundrySetting;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $appointments = Appointment::query()
            ->with(['customer', 'reviewer', 'laundryOrder', 'preferredStaff'])
            ->when($user->isCustomer(), fn ($query) => $query->where('customer_id', $user->id))
            ->latest('scheduled_at')
            ->paginate(12);

        return view('appointments.index', [
            'appointments' => $appointments,
            'staffMembers' => User::query()
                ->where('role', 'staff')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'settings' => LaundrySetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'preferred_staff_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('role', 'staff')->where('is_active', true),
            ],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'estimated_weight' => ['required', 'numeric', 'gt:0', 'max:500'],
            'service_type' => ['required', Rule::in(['partial', 'full'])],
            'press_service' => ['sometimes', 'boolean'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $request->user()->appointments()->create([
            ...$validated,
            'press_service' => $request->boolean('press_service'),
        ]);

        return back()->with('status', 'Appointment requested. You will be notified after it is reviewed.');
    }

    public function review(Request $request, Appointment $appointment, SmsService $smsService): RedirectResponse
    {
        if ($appointment->status !== 'pending') {
            return back()->withErrors(['appointment' => 'Only pending appointments can be reviewed.']);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
            'reviewed_by' => $request->user()->id,
        ]);

        $statusLabel = $validated['status'] === 'approved' ? 'approved' : 'declined';
        $smsWarning = $smsService->notify(
            $appointment->customer,
            'Appointment '.$statusLabel,
            'Your Centro Laundry appointment for '.$appointment->scheduled_at->format('M j, Y g:i A').' was '.$statusLabel.'.',
            route('appointments.index'),
            sendSms: false,
        );

        return back()
            ->with('status', 'Appointment '.$statusLabel.'.')
            ->with('warning', $smsWarning);
    }

    public function convertToOrder(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, ['admin', 'staff'], true), 403);

        if ($appointment->status !== 'approved' || $appointment->laundryOrder()->exists()) {
            return back()->withErrors(['appointment' => 'Only approved appointments without an order can be checked in.']);
        }

        return redirect()->route('orders.create', ['appointment' => $appointment->id]);
    }
}
