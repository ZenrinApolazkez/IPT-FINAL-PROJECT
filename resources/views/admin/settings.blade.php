@extends('layouts.app', ['title' => 'Laundry settings'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Laundry settings</h1>
            <p>Manage service prices and check SMS delivery configuration.</p>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="panel">
            <h2>Service pricing</h2>
            <form method="POST" action="{{ route('admin.settings.update') }}" class="stack">
                @csrf
                @method('PUT')
                <div class="field">
                    <label for="wash_price_per_kg">Wash &amp; dry base rate (partial service, ₱ per kg)</label>
                    <input id="wash_price_per_kg" name="wash_price_per_kg" type="number" min="0.01" step="0.01" value="{{ old('wash_price_per_kg', $settings->wash_price_per_kg) }}" required>
                </div>
                <div class="field">
                    <label for="press_price_per_kg">Press service add-on (₱ per kg)</label>
                    <input id="press_price_per_kg" name="press_price_per_kg" type="number" min="0" step="0.01" value="{{ old('press_price_per_kg', $settings->press_price_per_kg) }}" required>
                </div>
                <div class="field">
                    <label for="full_service_addon_per_kg">Full service folding and bagging add-on (₱ per kg)</label>
                    <input id="full_service_addon_per_kg" name="full_service_addon_per_kg" type="number" min="0" step="0.01" value="{{ old('full_service_addon_per_kg', $settings->full_service_addon_per_kg) }}" required>
                </div>
                <div class="rule-note">Prices on existing orders do not change when rates are edited. New orders use the latest configured rates.</div>
                <div><button class="button" type="submit">Save rates</button></div>
            </form>
        </section>

        <section class="panel">
            <h2>Semaphore SMS</h2>
            <p>Provider status:
                <span class="status {{ $smsConfigured ? 'completed' : 'pending' }}">{{ $smsConfigured ? 'API key configured' : 'Not configured' }}</span>
            </p>
            <div class="rule-note">
                SMS is only sent when a customer has a mobile number and an API key is configured. Add your provider key to <code>SEMAPHORE_API_KEY</code> in your local <code>.env</code>, then clear cached configuration. API keys are never shown in this page.
            </div>
            <p class="muted" style="font-size: 11px; line-height: 1.6;">Without a configured key, appointment and order updates remain available in the customer’s in-app notifications.</p>
        </section>
    </div>

    <section class="panel" style="margin-top: 18px;">
        <h2>Recent SMS delivery log</h2>
        @if ($smsMessages->isEmpty())
            <div class="empty-state">No SMS messages have been sent yet.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Created</th><th>Recipient</th><th>Customer</th><th>Message</th><th>Status</th><th>Result</th></tr></thead>
                    <tbody>
                        @foreach ($smsMessages as $sms)
                            <tr>
                                <td>{{ $sms->created_at->format('M j, g:i A') }}</td>
                                <td>{{ $sms->recipient }}</td>
                                <td>{{ $sms->user?->name ?: '—' }}</td>
                                <td>{{ $sms->message }}</td>
                                <td><span class="status {{ in_array($sms->status, ['queued', 'sent'], true) ? 'completed' : ($sms->status === 'failed' ? 'cancelled' : 'pending') }}">{{ str($sms->status)->title() }}</span></td>
                                <td>{{ $sms->error_message ?: ($sms->provider_reference ?: '—') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
