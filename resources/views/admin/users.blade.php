@extends('layouts.app', ['title' => 'Customers and staff'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>Customers &amp; staff</h1>
            <p>Manage customer records and staff access. Deactivating an account preserves its laundry history.</p>
        </div>
    </div>

    <section class="panel" style="margin-bottom: 20px;">
        <h2>Create a staff account</h2>
        <form method="POST" action="{{ route('admin.staff.store') }}" class="stack">
            @csrf
            <div class="form-grid">
                <div class="field"><label for="staff-name">Full name</label><input id="staff-name" name="name" value="{{ old('name') }}" required></div>
                <div class="field"><label for="staff-username">Username</label><input id="staff-username" name="username" value="{{ old('username') }}" required></div>
                <div class="field"><label for="staff-email">Email address</label><input id="staff-email" name="email" type="email" value="{{ old('email') }}" required></div>
                <div class="field"><label for="staff-phone">Mobile number <span class="muted">(optional)</span></label><input id="staff-phone" name="phone" type="tel" value="{{ old('phone') }}"></div>
                <div class="field"><label for="staff-password">Temporary password</label><input id="staff-password" name="password" type="password" autocomplete="new-password" required></div>
                <div class="field"><label for="staff-password-confirmation">Confirm password</label><input id="staff-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div>
            </div>
            <div><button class="button" type="submit">Create staff account</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="toolbar">
            <h2>Customer directory and staff access</h2>
            <form method="GET" action="{{ route('admin.users.index') }}">
                <div class="field">
                    <label for="search">Search</label>
                    <input id="search" name="search" value="{{ request('search') }}" placeholder="Name, username, email">
                </div>
                <div class="field">
                    <label for="role">Account type</label>
                    <select id="role" name="role">
                        <option value="">All</option>
                        <option value="customer" @selected(request('role') === 'customer')>Customers</option>
                        <option value="staff" @selected(request('role') === 'staff')>Staff</option>
                    </select>
                </div>
                <button class="button secondary" type="submit">Search</button>
            </form>
        </div>
        @if ($users->isEmpty())
            <div class="empty-state">No accounts match your search.</div>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Account</th><th>Type</th><th>Phone</th><th>Access</th><th>Update account</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($users as $account)
                            <tr>
                                <td>
                                    <strong>{{ $account->name }}</strong><br>
                                    <span class="muted">{{ $account->username }} &middot; {{ $account->email }}</span>
                                </td>
                                <td>{{ str($account->role)->title() }}</td>
                                <td>{{ $account->phone ?: '—' }}</td>
                                <td><span class="status {{ $account->is_active ? 'completed' : 'cancelled' }}">{{ $account->is_active ? 'Active' : 'Inactive' }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.update', $account) }}" class="form-grid">
                                        @csrf
                                        @method('PUT')
                                        <input name="name" aria-label="Name" value="{{ $account->name }}" required>
                                        <input name="username" aria-label="Username" value="{{ $account->username }}" required>
                                        <input name="email" aria-label="Email" type="email" value="{{ $account->email }}" required>
                                        <input name="phone" aria-label="Phone" type="tel" value="{{ $account->phone }}">
                                        <input name="password" aria-label="New password (optional)" type="password" placeholder="New password (optional)" autocomplete="new-password">
                                        <input name="password_confirmation" aria-label="Confirm new password" type="password" placeholder="Confirm new password" autocomplete="new-password">
                                        <button class="button secondary small" type="submit">Save</button>
                                    </form>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.users.toggle', $account) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="button {{ $account->is_active ? 'danger' : 'secondary' }} small" type="submit">
                                            {{ $account->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination">{{ $users->links() }}</div>
        @endif
    </section>
@endsection
