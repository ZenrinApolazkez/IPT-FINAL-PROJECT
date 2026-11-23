@extends('layouts.app', ['title' => 'Laundry records'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Laundry records</h1>
            <p>{{ auth()->user()->isCustomer() ? 'Check your current laundry and past transactions.' : 'Track each drop-off from check-in through pickup.' }}</p>
        </div>
        @if (! auth()->user()->isCustomer())
            <a class="button" href="{{ route('orders.create') }}">Record drop-off</a>
        @endif
    </div>

    <section class="panel">
        <div class="toolbar">
            <h2>{{ auth()->user()->isCustomer() ? 'My laundry' : 'Active and completed orders' }}</h2>
            <form method="GET" action="{{ route('orders.index') }}">
                <div class="field">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\LaundryOrder::STATUSES as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="button secondary" type="submit">Filter</button>
            </form>
        </div>
        @if ($orders->isEmpty())
            <div class="empty-state">No laundry records match this view.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            @unless (auth()->user()->isCustomer())
                                <th>Customer</th>
                            @endunless
                            <th>Drop-off</th>
                            <th>Weight</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Balance</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td><a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                @unless (auth()->user()->isCustomer())
                                    <td>{{ $order->customer->name }}</td>
                                @endunless
                                <td>{{ $order->created_at->format('M j, Y') }}</td>
                                <td>{{ number_format((float) $order->weight, 1) }} kg</td>
                                <td>{{ $order->service_type === 'full' ? 'Full' : 'Partial' }}</td>
                                <td><span class="status {{ $order->status }}">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                <td>₱{{ number_format((float) $order->total_amount, 2) }}</td>
                                <td>₱{{ number_format($order->balance, 2) }}</td>
                                <td><a href="{{ route('orders.show', $order) }}">Receipt</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $orders->links() }}</div>
        @endif
    </section>
@endsection
