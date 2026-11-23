<?php

namespace App\Http\Controllers;

use App\Models\LaundrySetting;
use App\Models\SmsMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', [
            'settings' => LaundrySetting::current(),
            'smsConfigured' => (bool) config('services.semaphore.key'),
            'smsMessages' => SmsMessage::query()->with('user')->latest()->limit(20)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'wash_price_per_kg' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'press_price_per_kg' => ['required', 'numeric', 'gte:0', 'max:100000'],
            'full_service_addon_per_kg' => ['required', 'numeric', 'gte:0', 'max:100000'],
        ]);

        $settings = LaundrySetting::current();
        $settings->update([
            ...$validated,
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Laundry rates updated. New orders will use these rates.');
    }
}
