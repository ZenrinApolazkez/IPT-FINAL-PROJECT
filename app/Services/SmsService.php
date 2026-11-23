<?php

namespace App\Services;

use App\Models\ActivityNotification;
use App\Models\LaundryOrder;
use App\Models\SmsMessage;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function notify(
        User $user,
        string $title,
        string $message,
        ?string $url = null,
        ?LaundryOrder $order = null,
        bool $sendSms = false,
    ): ?string {
        ActivityNotification::query()->create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'url' => $url,
        ]);

        $apiKey = config('services.semaphore.key');

        if (! $sendSms || ! $user->phone || ! $apiKey) {
            return null;
        }

        $smsMessage = SmsMessage::query()->create([
            'user_id' => $user->id,
            'laundry_order_id' => $order?->id,
            'recipient' => $user->phone,
            'message' => $message,
            'status' => 'pending',
        ]);

        try {
            $parameters = [
                'apikey' => $apiKey,
                'number' => $user->phone,
                'message' => $message,
            ];
            $senderName = config('services.semaphore.sender_name');

            if (is_string($senderName) && $senderName !== '') {
                $parameters['sendername'] = $senderName;
            }

            $response = Http::asForm()
                ->timeout(10)
                ->post('https://api.semaphore.co/api/v4/messages', $parameters)
                ->throw();

            $providerResponse = $response->json();
            $providerMessage = is_array($providerResponse) ? ($providerResponse[0] ?? null) : null;

            if (! is_array($providerMessage) || ! isset($providerMessage['message_id'])) {
                $smsMessage->update([
                    'status' => 'failed',
                    'error_message' => 'Semaphore returned an unexpected response.',
                ]);

                Log::warning('Laundry SMS provider returned an unexpected response.', [
                    'sms_message_id' => $smsMessage->id,
                    'user_id' => $user->id,
                ]);

                return 'The in-app notification was saved, but SMS delivery could not be confirmed. Check the SMS log.';
            }

            $providerStatus = strtolower((string) ($providerMessage['status'] ?? 'queued'));
            $deliveryStatus = in_array($providerStatus, ['queued', 'sent'], true) ? $providerStatus : 'failed';

            $smsMessage->update([
                'status' => $deliveryStatus,
                'provider_reference' => (string) $providerMessage['message_id'],
                'error_message' => $deliveryStatus === 'failed' ? 'Semaphore reported status: '.$providerStatus : null,
            ]);

            if ($deliveryStatus === 'failed') {
                Log::warning('Laundry SMS provider reported a failed message.', [
                    'sms_message_id' => $smsMessage->id,
                    'user_id' => $user->id,
                    'provider_status' => $providerStatus,
                ]);

                return 'The in-app notification was saved, but the SMS provider reported a delivery failure. Check the SMS log.';
            }

            return null;
        } catch (ConnectionException|RequestException $exception) {
            $smsMessage->update([
                'status' => 'failed',
                'error_message' => mb_substr($exception->getMessage(), 0, 1000),
            ]);

            Log::warning('Laundry SMS delivery failed.', [
                'sms_message_id' => $smsMessage->id,
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            return 'The in-app notification was saved, but SMS delivery failed. Check the SMS log or provider configuration.';
        }
    }
}
