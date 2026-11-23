@extends('layouts.app', ['title' => 'Appointments'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Appointments</h1>
            <p>
                @if (auth()->user()->isCustomer())
                    Request a laundry drop-off time and follow its approval status.
                @else
                    Review booking requests and check customers in when they arrive.
                @endif
            </p>
        </div>
    </div>

    @if (auth()->user()->isCustomer())
        <section class="panel" style="max-width: 760px; margin-bottom: 20px;">
            <h2>Request an appointment</h2>
            <form method="POST" action="{{ route('appointments.store') }}" class="stack">
                @csrf
                <div class="form-grid">
                    <div class="field full">
                        <label for="preferred_staff_id">Preferred staff member</label>
                        <select id="preferred_staff_id" name="preferred_staff_id" required>
                            <option value="">Choose a staff member</option>
                            @foreach ($staffMembers as $staffMember)
                                <option value="{{ $staffMember->id }}" @selected((string) old('preferred_staff_id') === (string) $staffMember->id)>
                                    {{ $staffMember->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('preferred_staff_id') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="scheduled_at">Preferred drop-off date and time</label>
                        <input id="scheduled_at" name="scheduled_at" type="datetime-local" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" value="{{ old('scheduled_at') }}" required>
                        @error('scheduled_at') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field">
                        <label for="estimated_weight">Estimated laundry weight (kg)</label>
                        <input id="estimated_weight" name="estimated_weight" type="number" min="0.1" max="500" step="0.1" value="{{ old('estimated_weight') }}" required>
                        @error('estimated_weight') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label for="service_type">Laundry service</label>
                        <select id="service_type" name="service_type" required>
                            <option value="partial" @selected(old('service_type', 'partial') === 'partial')>
                                Partial service — washed and dried, returned unfolded and without a bag (₱{{ number_format((float) $settings->wash_price_per_kg, 2) }}/kg)
                            </option>
                            <option value="full" @selected(old('service_type') === 'full')>
                                Full service — washed, dried, folded, and bagged (+₱{{ number_format((float) $settings->full_service_addon_per_kg, 2) }}/kg)
                            </option>
                        </select>
                        @error('service_type') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="field full">
                        <label class="check-field" for="press_service">
                            <input id="press_service" name="press_service" type="checkbox" value="1" @checked(old('press_service'))>
                            Include press service (+₱{{ number_format((float) $settings->press_price_per_kg, 2) }}/kg)
                        </label>
                    </div>
                    <div class="field full">
                        <label for="customer_note">Notes for the laundry team <span class="muted">(optional)</span></label>
                        <textarea id="customer_note" name="customer_note" maxlength="1000">{{ old('customer_note') }}</textarea>
                    </div>
                </div>
                <div class="rule-note">
                    Partial service starts at ₱{{ number_format((float) $settings->wash_price_per_kg, 2) }}/kg.
                    Full service adds ₱{{ number_format((float) $settings->full_service_addon_per_kg, 2) }}/kg for folding and bagging.
                    Final price is based on the actual weight when your laundry is checked in.
                </div>
                <div id="price-estimate" class="rule-note" aria-live="polite">Estimated total: Enter your estimated weight.</div>
                <div><button class="button" type="submit">Request appointment</button></div>
            </form>
        </section>
    @endif

    <section class="panel">
        <h2>{{ auth()->user()->isCustomer() ? 'My appointment history' : 'Appointment requests' }}</h2>
        @if ($appointments->isEmpty())
            <div class="empty-state">No appointments to show.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Requested time</th>
                            @unless (auth()->user()->isCustomer())
                                <th>Customer</th>
                                <th>Contact</th>
                            @endunless
                            <th>Preferred staff</th>
                            <th>Estimated load</th>
                            <th>Service</th>
                            <th>Estimated total</th>
                            <th>Status</th>
                            <th>Notes</th>
                            @if (auth()->user()->isAdmin() || auth()->user()->isStaff())
                                <th>Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($appointments as $appointment)
                            <tr>
                                <td>{{ $appointment->scheduled_at->format('M j, Y g:i A') }}</td>
                                @unless (auth()->user()->isCustomer())
                                    <td>{{ $appointment->customer->name }}</td>
                                    <td>{{ $appointment->customer->phone ?: $appointment->customer->email }}</td>
                                @endunless
                                <td>{{ $appointment->preferredStaff?->name ?? '—' }}</td>
                                <td>{{ number_format((float) $appointment->estimated_weight, 1) }} kg</td>
                                <td>
                                    {{ $appointment->service_type === 'full' ? 'Full — folded & bagged' : 'Partial — unfolded, no bag' }}
                                    @if ($appointment->press_service)
                                        <br><span class="muted">Includes press service</span>
                                    @endif
                                </td>
                                <td>
                                    ₱{{ number_format(
                                        (float) $appointment->estimated_weight
                                        * (
                                            (float) $settings->wash_price_per_kg
                                            + ($appointment->service_type === 'full' ? (float) $settings->full_service_addon_per_kg : 0)
                                            + ($appointment->press_service ? (float) $settings->press_price_per_kg : 0)
                                        ),
                                        2,
                                    ) }}
                                </td>
                                <td><span class="status {{ $appointment->status }}">{{ str($appointment->status)->title() }}</span></td>
                                <td>{{ $appointment->customer_note ?: '—' }}</td>
                                @if (auth()->user()->isAdmin() || auth()->user()->isStaff())
                                    <td>
                                        <div class="actions">
                                            @if ($appointment->status === 'pending')
                                                <form method="POST" action="{{ route('appointments.review', $appointment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button class="button small" type="submit">Approve</button>
                                                </form>
                                                <form method="POST" action="{{ route('appointments.review', $appointment) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button class="button secondary small" type="submit">Decline</button>
                                                </form>
                                            @elseif ($appointment->status === 'approved' && ! $appointment->laundryOrder)
                                                <form method="POST" action="{{ route('appointments.check-in', $appointment) }}">
                                                    @csrf
                                                    <button class="button small" type="submit">Check in laundry</button>
                                                </form>
                                            @else
                                                <span class="muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $appointments->links() }}</div>
        @endif
    </section>
    @if (auth()->user()->isCustomer())
        <script>
            const weightInput = document.getElementById('estimated_weight');
            const pressInput = document.getElementById('press_service');
            const serviceInput = document.getElementById('service_type');
            const priceEstimate = document.getElementById('price-estimate');
            const washPrice = {{ Illuminate\Support\Js::from((float) $settings->wash_price_per_kg) }};
            const pressPrice = {{ Illuminate\Support\Js::from((float) $settings->press_price_per_kg) }};
            const fullServiceAddon = {{ Illuminate\Support\Js::from((float) $settings->full_service_addon_per_kg) }};

            function updatePriceEstimate() {
                const weight = Number.parseFloat(weightInput.value);
                const serviceRate = washPrice
                    + (serviceInput.value === 'full' ? fullServiceAddon : 0)
                    + (pressInput.checked ? pressPrice : 0);

                priceEstimate.textContent = Number.isFinite(weight) && weight > 0
                    ? `Estimated total: ₱${(weight * serviceRate).toFixed(2)}. Final price is based on actual drop-off weight.`
                    : 'Estimated total: Enter your estimated weight.';
            }

            weightInput.addEventListener('input', updatePriceEstimate);
            pressInput.addEventListener('change', updatePriceEstimate);
            serviceInput.addEventListener('change', updatePriceEstimate);
            updatePriceEstimate();
        </script>
    @endif
@endsection
