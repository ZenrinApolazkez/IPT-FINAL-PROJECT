<?php

namespace Tests\Feature;

use App\Models\SmsMessage;
use App\Models\LaundryOrder;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sms_is_sent_and_recorded_when_provider_is_configured(): void
    {
        config(['services.semaphore.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.semaphore.co/api/v4/messages' => Http::response([
                ['message_id' => 'provider-123'],
            ], 200),
        ]);
        $user = User::factory()->create(['phone' => '+639123456789']);

        $warning = app(SmsService::class)->notify($user, 'Laundry ready', 'Your order is ready.', sendSms: true);

        $this->assertNull($warning);
        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $user->id,
            'title' => 'Laundry ready',
        ]);
        $this->assertDatabaseHas('sms_messages', [
            'user_id' => $user->id,
            'recipient' => '+639123456789',
            'status' => 'queued',
            'provider_reference' => 'provider-123',
        ]);
        Http::assertSent(fn ($request): bool => $request['number'] === '+639123456789'
            && $request['message'] === 'Your order is ready.'
            && $request['apikey'] === 'test-key');
    }

    public function test_failed_sms_delivery_is_recorded_and_reported_without_losing_in_app_notification(): void
    {
        config(['services.semaphore.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.semaphore.co/api/v4/messages' => Http::response(['error' => 'invalid key'], 401),
        ]);
        $user = User::factory()->create(['phone' => '+639123456789']);

        $warning = app(SmsService::class)->notify($user, 'Laundry ready', 'Your order is ready.', sendSms: true);

        $this->assertNotNull($warning);
        $this->assertDatabaseHas('activity_notifications', [
            'user_id' => $user->id,
            'title' => 'Laundry ready',
        ]);
        $this->assertSame('failed', SmsMessage::query()->sole()->status);
    }

    public function test_sms_is_sent_only_when_an_order_becomes_ready_for_pickup(): void
    {
        config(['services.semaphore.key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake([
            'https://api.semaphore.co/api/v4/messages' => Http::response([
                ['message_id' => 'ready-provider-123'],
            ], 200),
        ]);
        $customer = User::factory()->create(['phone' => '+639123456789']);
        $staff = User::factory()->create();
        $staff->role = 'staff';
        $staff->save();
        $order = LaundryOrder::query()->create([
            'order_number' => 'CL-READY-000001',
            'customer_id' => $customer->id,
            'received_by' => $staff->id,
            'weight' => 2,
            'press_service' => false,
            'service_type' => 'full',
            'wash_price_per_kg' => 26,
            'press_price_per_kg' => 35,
            'full_service_addon_per_kg' => 0,
            'total_amount' => 52,
            'status' => 'received',
        ]);

        $this->post(route('login.store'), [
            'username' => $staff->username,
            'password' => 'password',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('orders.store'), [
            'customer_id' => $customer->id,
            'weight' => 1,
            'service_type' => 'partial',
        ])->assertRedirect();
        $this->assertSame(0, SmsMessage::query()->count());

        $this->patch(route('orders.status', $order), ['status' => 'washing'])->assertRedirect();
        $this->assertSame(0, SmsMessage::query()->count());

        $order->update(['status' => 'folding']);
        $this->patch(route('orders.status', $order), ['status' => 'ready_for_pickup'])->assertRedirect();

        $this->assertDatabaseHas('sms_messages', [
            'user_id' => $customer->id,
            'laundry_order_id' => $order->id,
            'status' => 'queued',
            'provider_reference' => 'ready-provider-123',
        ]);
        Http::assertSent(fn ($request): bool => $request['number'] === '+639123456789'
            && str_contains($request['message'], 'ready for pickup'));
    }
}
