<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0781bd">
    <title>{{ $title ?? 'Laundry portal' }} | Centro Laundry</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            color: #24292c;
            background: #f5f8fa;
            font-synthesis: none;
            text-rendering: optimizeLegibility;
            -webkit-font-smoothing: antialiased;
        }

        * { box-sizing: border-box; }
        body { min-width: 320px; min-height: 100vh; margin: 0; }
        button, input, select, textarea { font: inherit; }
        a { color: #057bb5; }
        .topbar { display: flex; min-height: 64px; align-items: center; justify-content: space-between; gap: 20px; padding: 10px max(24px, calc((100% - 1180px) / 2)); background: #0781bd; color: #fff; }
        .brand { display: flex; align-items: center; gap: 12px; color: #fff; font-size: 19px; text-decoration: none; white-space: nowrap; }
        .brand-mark { display: grid; width: 38px; height: 38px; place-items: center; border: 2px solid #fff; background: #fff; color: #0781bd; }
        .brand-mark svg { width: 27px; height: 27px; }
        .top-actions { display: flex; align-items: center; gap: 16px; }
        .user-name { font-size: 13px; font-weight: 700; }
        .user-role { display: block; margin-top: 3px; color: #d7effa; font-size: 10px; text-align: right; text-transform: uppercase; letter-spacing: .08em; }
        .logout-form { margin: 0; }
        .logout-button { padding: 8px 12px; border: 1px solid rgba(255,255,255,.55); border-radius: 4px; background: transparent; color: #fff; font-size: 12px; font-weight: 700; cursor: pointer; }
        .nav-wrap { background: #fff; border-bottom: 1px solid #e3eaee; }
        .nav { display: flex; width: min(100% - 48px, 1180px); min-height: 48px; align-items: center; gap: 6px; margin: 0 auto; overflow-x: auto; }
        .nav a { flex: 0 0 auto; padding: 15px 12px 13px; border-bottom: 2px solid transparent; color: #59676e; font-size: 12px; font-weight: 700; text-decoration: none; white-space: nowrap; }
        .nav a:hover, .nav a.active { border-bottom-color: #0781bd; color: #056d9f; }
        .nav-badge { display: inline-flex; min-width: 18px; height: 18px; align-items: center; justify-content: center; margin-left: 5px; padding: 0 5px; border-radius: 999px; background: #b43e3e; color: #fff; font-size: 10px; font-weight: 700; line-height: 1; }
        main { width: min(100% - 48px, 1180px); margin: 0 auto; padding: 32px 0 56px; }
        .page-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 18px; margin-bottom: 22px; }
        .page-heading h1 { margin: 0; color: #202b30; font-size: clamp(24px, 3vw, 32px); letter-spacing: -.035em; }
        .page-heading p { margin: 8px 0 0; color: #6d7a80; font-size: 13px; line-height: 1.6; }
        .button { display: inline-flex; min-height: 39px; align-items: center; justify-content: center; gap: 7px; padding: 9px 14px; border: 0; border-radius: 4px; background: #0781bd; color: #fff; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .button:hover { background: #066fa4; color: #fff; }
        .button.secondary { border: 1px solid #d6e0e5; background: #fff; color: #3a4b53; }
        .button.danger { background: #b43e3e; }
        .button.small { min-height: 32px; padding: 7px 10px; font-size: 11px; }
        .panel, .stat-card { border: 1px solid #e2e9ed; border-radius: 7px; background: #fff; box-shadow: 0 3px 14px rgba(24, 55, 70, .035); }
        .panel { padding: 22px; }
        .panel h2 { margin: 0 0 16px; color: #25343a; font-size: 16px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 22px; }
        .stat-card { padding: 18px; }
        .stat-label { color: #728087; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
        .stat-value { display: block; margin-top: 9px; color: #0781bd; font-size: 26px; font-weight: 700; letter-spacing: -.035em; }
        .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.55fr) minmax(250px, .75fr); gap: 18px; }
        .table-wrap { width: 100%; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 11px 12px; border-bottom: 1px solid #e5ebee; color: #78858b; font-size: 10px; text-transform: uppercase; letter-spacing: .07em; white-space: nowrap; }
        td { padding: 13px 12px; border-bottom: 1px solid #eef1f3; color: #435159; font-size: 12px; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: 0; }
        .status { display: inline-block; padding: 5px 8px; border-radius: 99px; background: #e8f4fa; color: #126d98; font-size: 10px; font-weight: 700; white-space: nowrap; }
        .status.pending, .status.received { background: #fff2d6; color: #8a6013; }
        .status.rejected, .status.cancelled { background: #fbe8e8; color: #9c3434; }
        .status.completed, .status.approved, .status.ready_for_pickup, .status.fulfilled { background: #e5f4e9; color: #2b7040; }
        .muted { color: #78858b; }
        .empty-state { padding: 30px 16px; color: #78858b; font-size: 13px; text-align: center; }
        .stack { display: grid; gap: 16px; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
        .field { display: grid; gap: 7px; }
        .field.full { grid-column: 1 / -1; }
        .field label { color: #35444a; font-size: 12px; font-weight: 700; }
        .field input, .field select, .field textarea { width: 100%; min-height: 41px; padding: 9px 11px; border: 1px solid #d6e0e5; border-radius: 4px; outline: none; background: #fff; color: #29383e; font-size: 13px; }
        .field textarea { min-height: 88px; resize: vertical; }
        .field input:focus, .field select:focus, .field textarea:focus { border-color: #0781bd; box-shadow: 0 0 0 3px rgba(7, 129, 189, .12); }
        .field-error { color: #b43e3e; font-size: 11px; }
        .check-field { display: flex; align-items: center; gap: 8px; color: #435159; font-size: 12px; }
        .check-field input { accent-color: #0781bd; }
        .alert { margin-bottom: 16px; padding: 12px 14px; border-radius: 4px; font-size: 12px; line-height: 1.5; }
        .alert.success { border: 1px solid #c5e5ce; background: #eef9f0; color: #28643a; }
        .alert.warning { border: 1px solid #f0dfb6; background: #fff9e9; color: #795b1a; }
        .alert.error { border: 1px solid #eccaca; background: #fff0f0; color: #963c3c; }
        .alert ul { margin: 0; padding-left: 18px; }
        .inline-form { display: inline-flex; align-items: center; gap: 7px; margin: 0; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
        .notice-list { display: grid; gap: 0; }
        .notice { padding: 13px 0; border-bottom: 1px solid #edf1f3; }
        .notice:last-child { border-bottom: 0; }
        .notice form { margin: 0; }
        .notice button { display: block; width: 100%; padding: 0; border: 0; background: none; color: inherit; text-align: left; cursor: pointer; }
        .notice strong { display: block; margin-bottom: 5px; color: #324149; font-size: 12px; }
        .notice p { margin: 0; color: #728087; font-size: 11px; line-height: 1.55; }
        .notice time { display: block; margin-top: 6px; color: #9aa4a9; font-size: 10px; }
        .toolbar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; }
        .toolbar form { display: flex; flex-wrap: wrap; align-items: end; gap: 9px; }
        .toolbar .field { min-width: 150px; }
        .pagination { display: flex; justify-content: center; gap: 7px; margin-top: 18px; font-size: 12px; }
        .pagination a, .pagination span { padding: 7px 10px; border: 1px solid #dce5e9; border-radius: 4px; background: #fff; color: #0876a9; text-decoration: none; }
        .pagination span[aria-current] { border-color: #0781bd; background: #0781bd; color: #fff; }
        .receipt { max-width: 760px; margin: 0 auto; }
        .receipt-head { display: flex; justify-content: space-between; gap: 20px; padding-bottom: 18px; border-bottom: 1px solid #e6ecef; }
        .receipt-head h2 { margin: 0 0 6px; }
        .receipt-total { color: #0781bd; font-size: 23px; font-weight: 700; text-align: right; white-space: nowrap; }
        .receipt-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; padding: 18px 0; }
        .detail-label { display: block; margin-bottom: 5px; color: #849097; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; }
        .detail-value { color: #34434a; font-size: 13px; font-weight: 700; }
        .rule-note { padding: 12px; border-radius: 4px; background: #f3f8fa; color: #58676e; font-size: 11px; line-height: 1.6; }
        .footer-note { margin-top: 24px; color: #8b969c; font-size: 10px; text-align: center; }
        @media (max-width: 760px) {
            .topbar { min-height: 58px; padding: 9px 18px; }
            .brand { font-size: 16px; }
            .user-name { display: none; }
            .top-actions { gap: 10px; }
            .nav { width: calc(100% - 24px); }
            main { width: calc(100% - 32px); padding-top: 23px; }
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .stat-card { padding: 14px; }
            .stat-value { font-size: 22px; }
            .dashboard-grid { grid-template-columns: 1fr; }
            .panel { padding: 17px; }
        }
        @media (max-width: 480px) {
            .topbar { padding-right: 12px; padding-left: 12px; }
            .brand { gap: 8px; font-size: 14px; }
            .brand-mark { width: 33px; height: 33px; }
            .brand-mark svg { width: 23px; height: 23px; }
            .logout-button { padding: 7px 9px; }
            main { width: calc(100% - 24px); }
            .page-heading { flex-direction: column; align-items: stretch; }
            .page-heading > .button { align-self: flex-start; }
            .form-grid { grid-template-columns: 1fr; }
            .field.full { grid-column: auto; }
            .receipt-head { flex-direction: column; }
            .receipt-total { text-align: left; }
        }
        @media print {
            .topbar, .nav-wrap, .no-print, .alert, .footer-note { display: none !important; }
            body { background: #fff; }
            main { width: 100%; padding: 0; }
            .panel { border: 0; box-shadow: none; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    @php($currentUser = auth()->user())
    <header class="topbar">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="Centro Laundry dashboard">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 32 32" fill="none">
                    <circle cx="16" cy="16" r="13" stroke="currentColor" stroke-width="2"/>
                    <rect x="10" y="8" width="12" height="17" rx="2" fill="currentColor"/>
                    <circle cx="16" cy="17" r="4.3" stroke="#fff" stroke-width="1.7"/>
                    <path d="M12.5 11h7M12.5 13h1" stroke="#fff" stroke-width="1.3" stroke-linecap="round"/>
                </svg>
            </span>
            <span>Centro Laundry</span>
        </a>
        <div class="top-actions">
            <div>
                <span class="user-name">{{ $currentUser->name }}</span>
                <span class="user-role">{{ $currentUser->role }}</span>
            </div>
            <form class="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button" type="submit">Sign out</button>
            </form>
        </div>
    </header>
    <div class="nav-wrap">
        <nav class="nav" aria-label="Main navigation">
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('orders.*') ? 'active' : '' }}" href="{{ route('orders.index') }}">Laundry records</a>
            <a class="{{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                Appointments
                @if (($pendingAppointmentCount ?? 0) > 0)
                    <span class="nav-badge" aria-label="{{ $pendingAppointmentCount }} pending appointment requests">{{ $pendingAppointmentCount }}</span>
                @endif
            </a>
            <a class="{{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.edit') }}">My profile</a>
            @if ($currentUser->isAdmin() || $currentUser->isStaff())
                <a class="{{ request()->routeIs('reports') ? 'active' : '' }}" href="{{ route('reports') }}">Reports</a>
            @endif
            @if ($currentUser->isAdmin())
                <a class="{{ request()->routeIs('admin.users.*', 'admin.staff.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Customers &amp; staff</a>
                <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Settings</a>
                <a class="{{ request()->routeIs('admin.backups.*') ? 'active' : '' }}" href="{{ route('admin.backups.index') }}">Backups</a>
            @endif
        </nav>
    </div>
    <main>
        @if (session('status'))
            <div class="alert success" role="status">{{ session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert warning" role="status">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
        <p class="footer-note">Centro Laundry &middot; Clean clothes, clear records.</p>
    </main>
</body>
</html>
