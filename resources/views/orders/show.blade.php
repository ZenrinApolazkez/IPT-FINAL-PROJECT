@extends('layouts.app', ['title' => 'Receipt '.$order->order_number])

@section('content')
    <div class="page-heading no-print">
        <div>
            <h1>Laundry receipt</h1>
            <p>Receipt and transaction history for order {{ $order->order_number }}.</p>
        </div>
        @unless (auth()->user()->isCustomer())
            <button class="button secondary" type="button" onclick="window.print()">Print receipt</button>
        @endunless
    </div>

    <section class="panel receipt">
        <div class="receipt-head">
            <div>
                <h2>Centro Laundry</h2>
                <span class="muted">Laundry service receipt</span>
            </div>
            <div>
                <span class="detail-label">Order number</span>
                <span class="detail-value">{{ $order->order_number }}</span>
            </div>
            <div class="receipt-total">
                <span class="detail-label">Total</span>
                ₱{{ number_format((float) $order->total_amount, 2) }}
            </div>
        </div>

        <div class="receipt-details">
            <div><span class="detail-label">Customer</span><span class="detail-value">{{ $order->customer->name }}</span></div>
            <div><span class="detail-label">Drop-off</span><span class="detail-value">{{ $order->created_at->format('M j, Y g:i A') }}</span></div>
            <div><span class="detail-label">Weight</span><span class="detail-value">{{ number_format((float) $order->weight, 2) }} kg</span></div>
            <div><span class="detail-label">Service</span><span class="detail-value">{{ $order->service_type === 'full' ? 'Full — washed, dried, folded & bagged' : 'Partial — washed & dried, unfolded and not bagged' }}{{ $order->press_service ? ' & pressed' : '' }}</span></div>
            <div><span class="detail-label">Order status</span><span class="status {{ $order->status }}">{{ str($order->status)->replace('_', ' ')->title() }}</span></div>
            <div><span class="detail-label">Expected ready</span><span class="detail-value">{{ $order->due_at?->format('M j, Y g:i A') ?? 'To be confirmed' }}</span></div>
        </div>

        <div class="table-wrap">
            <table>
                <thead><tr><th>Description</th><th>Rate</th><th>Amount</th></tr></thead>
                <tbody>
                    <tr>
                        <td>Wash &amp; dry — {{ $order->service_type === 'full' ? 'full service' : 'partial service' }} ({{ number_format((float) $order->weight, 2) }} kg)</td>
                        <td>₱{{ number_format((float) $order->wash_price_per_kg, 2) }}/kg</td>
                        <td>₱{{ number_format((float) $order->weight * (float) $order->wash_price_per_kg, 2) }}</td>
                    </tr>
                    @if ($order->service_type === 'full')
                        <tr>
                            <td>Full service — folding and bagging ({{ number_format((float) $order->weight, 2) }} kg)</td>
                            <td>₱{{ number_format((float) $order->full_service_addon_per_kg, 2) }}/kg</td>
                            <td>₱{{ number_format((float) $order->weight * (float) $order->full_service_addon_per_kg, 2) }}</td>
                        </tr>
                    @endif
                    @if ($order->press_service)
                        <tr>
                            <td>Press service ({{ number_format((float) $order->weight, 2) }} kg)</td>
                            <td>₱{{ number_format((float) $order->press_price_per_kg, 2) }}/kg</td>
                            <td>₱{{ number_format((float) $order->weight * (float) $order->press_price_per_kg, 2) }}</td>
                        </tr>
                    @endif
                    <tr><td colspan="2"><strong>Total</strong></td><td><strong>₱{{ number_format((float) $order->total_amount, 2) }}</strong></td></tr>
                    <tr><td colspan="2">Payments received</td><td>₱{{ number_format((float) $paidAmount, 2) }}</td></tr>
                    <tr><td colspan="2"><strong>Balance due</strong></td><td><strong>₱{{ number_format($order->balance, 2) }}</strong></td></tr>
                </tbody>
            </table>
        </div>

        @if ($order->payments->isNotEmpty())
            <div style="margin-top: 20px">
                <h2>Payment history</h2>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date</th><th>Method</th><th>Reference</th><th>Received by</th><th>Amount</th></tr></thead>
                        <tbody>
                            @foreach ($order->payments as $payment)
                                <tr>
                                    <td>{{ $payment->paid_at->format('M j, Y g:i A') }}</td>
                                    <td>{{ str($payment->method)->replace('_', ' ')->title() }}</td>
                                    <td>{{ $payment->reference ?: '—' }}</td>
                                    <td>{{ $payment->receiver->name }}</td>
                                    <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if (! auth()->user()->isCustomer())
            <div class="no-print stack" style="margin-top: 22px; padding-top: 18px; border-top: 1px solid #e6ecef;">
                @if (! in_array($order->status, ['completed', 'cancelled'], true))
                    <form method="POST" action="{{ route('orders.status', $order) }}" class="actions">
                        @csrf
                        @method('PATCH')
                        <div class="field">
                            <label for="status">Update laundry status</label>
                            <select id="status" name="status" required>
                                @foreach ($order->availableTransitions() as $status)
                                    <option value="{{ $status }}">{{ str($status)->replace('_', ' ')->title() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button class="button" type="submit">Update status</button>
                    </form>
                @endif

                @if ($order->balance > 0)
                    <form method="POST" action="{{ route('orders.payments.store', $order) }}" class="form-grid">
                        @csrf
                        <div class="field">
                            <label for="amount">Payment amount (balance ₱{{ number_format($order->balance, 2) }})</label>
                            <input id="amount" name="amount" type="number" min="0.01" max="{{ number_format($order->balance, 2, '.', '') }}" step="0.01" required>
                        </div>
                        <div class="field">
                            <label for="method">Payment method</label>
                            <select id="method" name="method" required>
                                <option value="cash">Cash</option>
                                <option value="gcash">GCash</option>
                                <option value="card">Card</option>
                                <option value="bank_transfer">Bank transfer</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="reference">Reference <span class="muted">(optional)</span></label>
                            <input id="reference" name="reference" maxlength="120">
                        </div>
                        <div style="display: flex; align-items: end"><button class="button" type="submit">Record payment</button></div>
                    </form>
                @endif
            </div>
        @endif
    </section>
@endsection
