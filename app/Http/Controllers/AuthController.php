<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $this->startHumanVerification($request);

        return view('login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'human_check' => ['nullable', 'in:1'],
        ]);

        $user = User::query()->where('username', $credentials['username'])->first();

        if (! $user || $user->isCustomer()) {
            $startedAt = $request->session()->pull('login_human_verification_started_at');
            $elapsedSeconds = is_int($startedAt) ? now()->timestamp - $startedAt : 0;
            $humanCheckComplete = ($credentials['human_check'] ?? null) === '1';

            if (! $humanCheckComplete || $elapsedSeconds < 4 || $elapsedSeconds > 300) {
                $this->startHumanVerification($request);

                return back()
                    ->withErrors(['human_check' => 'Please complete the human verification before signing in.'])
                    ->onlyInput('username');
            }
        }

        if (! Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            if (! $user || $user->isCustomer()) {
                $this->startHumanVerification($request);
            }

            return back()
                ->withErrors(['username' => 'The username or password is incorrect.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        if (! $request->user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['username' => 'This account has been deactivated. Contact the laundry administrator.'])
                ->onlyInput('username');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showRegistration(): View
    {
        return view('register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'min:3', 'max:32', Rule::unique('users', 'username')],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create($validated);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('status', 'Your customer account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function startHumanVerification(Request $request): void
    {
        $request->session()->put('login_human_verification_started_at', now()->timestamp);
    }
}
