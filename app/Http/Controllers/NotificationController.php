<?php

namespace App\Http\Controllers;

use App\Models\ActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, ActivityNotification $activityNotification): RedirectResponse
    {
        abort_unless((int) $activityNotification->user_id === (int) $request->user()->id, 404);

        $activityNotification->update(['read_at' => now()]);

        return $activityNotification->url
            ? redirect()->to($activityNotification->url)
            : back();
    }
}
