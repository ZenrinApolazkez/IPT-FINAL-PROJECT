@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1>
            <p>
                @if (auth()->user()->isCustomer())
                    Keep an eye on your laundry and upcoming appointments.
                @else
                    Here is what is happening at your laundry shop today.
                @endif
            </p>
        </div>
        @if (auth()->user()->isCustomer())
            <a class="button" href="{{ route('appointments.index') }}">Book an appointment</a>
        @else
            <a class="button" href="{{ route('orders.create') }}">Record drop-off</a>
        @endif
    </div>

    <section class="stats-grid" aria-label="Laundry summary">
        <article class="stat-card">
            <span class="stat-label">Active orders</span>
            <strong class="stat-value">{{ $activeOrders }}</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">{{ auth()->user()->isCustomer() ? 'My upcoming requests' : 'Appointments to review' }}</span>
            <strong class="stat-value">{{ $pendingAppointments }}</strong>
        </article>
        <article class="stat-card">
            <span class="stat-label">Ready for pickup</span>
            <strong class="stat-value">{{ $readyOrders }}</strong>
        </article>
        @unless (auth()->user()->isCustomer())
            <article class="stat-card">
                <span class="stat-label">Payments today</span>
                <strong class="stat-value">₱{{ number_format((float) $todayRevenue, 2) }}</strong>
            </article>
        @endunless
    </section>

    <div class="dashboard-grid">
        <section class="panel">
            <div class="toolbar">
                <h2>Recent laundry records</h2>
                <a href="{{ route('orders.index') }}">View all</a>
            </div>
            @if ($orders->isEmpty())
                <div class="empty-state">No laundry records yet. They will appear here as soon as an order is checked in.</div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Order</th>
                                @unless (auth()->user()->isCustomer())
                                    <th>Customer</th>
                                @endunless
                                <th>Received</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                                <tr>
                                    <td><a href="{{ route('orders.show', $order) }}">{{ $order->order_number }}</a></td>
                                    @unless (auth()->user()->isCustomer())
                                        <td>{{ $order->customer->name }}</td>
                                    @endunless
                                    <td>{{ $order->created_at->format('M j, g:i A') }}</td>
                                    <td><span class="status {{ $order->status }}">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                    <td>₱{{ number_format((float) $order->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <aside class="panel">
            <h2>Notifications</h2>
            @if ($unreadNotifications->isEmpty())
                <div class="empty-state">You are all caught up.</div>
            @else
                <div class="notice-list">
                    @foreach ($unreadNotifications as $notification)
                        <div class="notice">
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button type="submit">
                                    <strong>{{ $notification->title }}</strong>
                                    <p>{{ $notification->message }}</p>
                                    <time datetime="{{ $notification->created_at->toIso8601String() }}">{{ $notification->created_at->diffForHumans() }} &middot; open</time>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
            @if (auth()->user()->isCustomer())
                <div class="rule-note">
                    Partial service starts at ₱{{ number_format((float) $settings->wash_price_per_kg, 2) }}/kg.
                    Full service adds ₱{{ number_format((float) $settings->full_service_addon_per_kg, 2) }}/kg for folding and bagging.
                    Press service adds ₱{{ number_format((float) $settings->press_price_per_kg, 2) }}/kg.
                </div>
            @endif
        </aside>
    </div>
@endsection
