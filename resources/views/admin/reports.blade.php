@extends('layouts.app', ['title' => 'Reports'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Laundry reports</h1>
            <p>Monthly service and payment summary for the selected period.</p>
        </div>
        <div class="actions no-print">
            <button class="button secondary" type="button" onclick="window.print()">Print report</button>
            <form method="GET" action="{{ route('reports') }}" class="inline-form">
            <div class="field"><label for="month">Month</label><input id="month" name="month" type="month" value="{{ $month }}"></div>
            <button class="button" type="submit">View report</button>
            </form>
        </div>
    </div>

    <section class="stats-grid">
        <article class="stat-card"><span class="stat-label">Payments received</span><strong class="stat-value">₱{{ number_format((float) $revenue, 2) }}</strong></article>
        <article class="stat-card"><span class="stat-label">Laundry orders</span><strong class="stat-value">{{ $orderCount }}</strong></article>
        <article class="stat-card"><span class="stat-label">Completed orders</span><strong class="stat-value">{{ $completedCount }}</strong></article>
        <article class="stat-card"><span class="stat-label">Appointments</span><strong class="stat-value">{{ $appointmentCount }}</strong></article>
    </section>

    <section class="panel" style="margin-bottom: 18px;">
        <h2>Order status breakdown</h2>
        <p class="muted">Current statuses of orders created in {{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->format('F Y') }}.</p>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Status</th><th>Orders</th></tr></thead>
                <tbody>
                    @foreach ($statuses as $status)
                        <tr>
                            <td>{{ str($status)->replace('_', ' ')->title() }}</td>
                            <td>{{ $statusCounts->get($status, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel" style="margin-bottom: 18px;">
        <h2>Daily revenue</h2>
        @if ($dailyRevenue->isEmpty())
            <div class="empty-state">No payments were recorded during this month.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Transactions</th><th>Payments received</th></tr></thead>
                    <tbody>
                        @foreach ($dailyRevenue as $day)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($day->day)->format('M j, Y') }}</td>
                                <td>{{ $day->transaction_count }}</td>
                                <td>₱{{ number_format((float) $day->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel">
        <h2>Recent payment transactions</h2>
        @if ($recentPayments->isEmpty())
            <div class="empty-state">No transactions recorded for this month.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Paid at</th><th>Order</th><th>Customer</th><th>Method</th><th>Received by</th><th>Amount</th></tr></thead>
                    <tbody>
                        @foreach ($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at->format('M j, Y g:i A') }}</td>
                                <td><a href="{{ route('orders.show', $payment->order) }}">{{ $payment->order->order_number }}</a></td>
                                <td>{{ $payment->order->customer->name }}</td>
                                <td>{{ str($payment->method)->replace('_', ' ')->title() }}</td>
                                <td>{{ $payment->receiver->name }}</td>
                                <td>₱{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
