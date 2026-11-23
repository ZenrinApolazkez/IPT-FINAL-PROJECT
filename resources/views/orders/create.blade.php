@extends('layouts.app', ['title' => 'Record drop-off'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Record a laundry drop-off</h1>
            <p>Check in the customer’s laundry and create a price-based receipt.</p>
        </div>
    </div>

    <section class="panel" style="max-width: 820px">
        <form method="POST" action="{{ route('orders.store') }}" class="stack">
            @csrf
            <div class="form-grid">
                <div class="field full">
                    <label for="customer_id">Customer</label>
                    <select id="customer_id" name="customer_id" required>
                        <option value="">Choose a customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $appointment?->customer_id) == $customer->id)>
                                {{ $customer->name }} ({{ $customer->username }})
                            </option>
                        @endforeach
                    </select>
                </div>
                @if ($appointment)
                    <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                    <input type="hidden" name="service_type" value="{{ $appointment->service_type }}">
                    <div class="rule-note field full">
                        Approved booking: {{ $appointment->scheduled_at->format('M j, Y g:i A') }},
                        estimated {{ number_format((float) $appointment->estimated_weight, 1) }} kg.
                        Service: {{ $appointment->service_type === 'full' ? 'Full — folded and bagged' : 'Partial — unfolded and not bagged' }}.
                    </div>
                @else
                    <div class="field full">
                        <label for="service_type">Laundry service</label>
                        <select id="service_type" name="service_type" required>
                            <option value="partial" @selected(old('service_type', 'partial') === 'partial')>
                                Partial — washed and dried, returned unfolded and without a bag (₱{{ number_format((float) $settings->wash_price_per_kg, 2) }}/kg)
                            </option>
                            <option value="full" @selected(old('service_type') === 'full')>
                                Full — washed, dried, folded, and bagged (+₱{{ number_format((float) $settings->full_service_addon_per_kg, 2) }}/kg)
                            </option>
                        </select>
                        @error('service_type') <span class="field-error">{{ $message }}</span> @enderror
                    </div>
                @endif
                <div class="field">
                    <label for="weight">Actual weight (kg)</label>
                    <input id="weight" name="weight" type="number" min="0.1" max="500" step="0.1" value="{{ old('weight', $appointment?->estimated_weight) }}" required>
                    @error('weight') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="due_at">Expected ready date and time <span class="muted">(optional)</span></label>
                    <input id="due_at" name="due_at" type="datetime-local" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" value="{{ old('due_at') }}">
                </div>
                @if ($appointment)
                    <input type="hidden" name="press_service" value="{{ $appointment->press_service ? 1 : 0 }}">
                    <div class="field full rule-note">
                        Press service: {{ $appointment->press_service ? 'Included' : 'Not selected' }}.
                    </div>
                @else
                    <div class="field full">
                        <label class="check-field" for="press_service">
                            <input id="press_service" name="press_service" type="checkbox" value="1" @checked(old('press_service'))>
                            Include press service (+₱{{ number_format((float) $settings->press_price_per_kg, 2) }}/kg)
                        </label>
                    </div>
                @endif
                <div class="field full">
                    <label for="notes">Order notes <span class="muted">(optional)</span></label>
                    <textarea id="notes" name="notes" maxlength="1000">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div class="rule-note">
                Partial service starts at ₱{{ number_format((float) $settings->wash_price_per_kg, 2) }}/kg.
                Full service adds ₱{{ number_format((float) $settings->full_service_addon_per_kg, 2) }}/kg for folding and bagging.
                Press service adds ₱{{ number_format((float) $settings->press_price_per_kg, 2) }}/kg. Prices are saved on each order as a snapshot.
            </div>
            <div class="actions">
                <button class="button" type="submit">Create laundry record</button>
                <a class="button secondary" href="{{ route('orders.index') }}">Cancel</a>
            </div>
        </form>
    </section>
@endsection
