@extends('layouts.app', ['title' => 'My profile'])

@section('content')
    <div class="page-heading">
        <div>
            <h1>My profile</h1>
            <p>Update your contact details. Your role is managed by the laundry administrator.</p>
        </div>
    </div>

    <section class="panel" style="max-width: 720px">
        <form method="POST" action="{{ route('profile.update') }}" class="stack">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="username">Username</label>
                    <input id="username" name="username" value="{{ old('username', $user->username) }}" autocomplete="username" required>
                    @error('username') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                    @error('email') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="phone">Mobile number <span class="muted">(for SMS updates)</span></label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                    @error('phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="current_password">Current password <span class="muted">(required to change password)</span></label>
                    <input id="current_password" name="current_password" type="password" autocomplete="current-password">
                    @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="password">New password <span class="muted">(optional)</span></label>
                    <input id="password" name="password" type="password" autocomplete="new-password">
                    @error('password') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password">
                </div>
            </div>
            <div><button class="button" type="submit">Save profile</button></div>
        </form>
    </section>
@endsection
