<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DiditOtpService
{
    public function sendCode(string $phoneNumber, ?string $ip = null, ?string $userAgent = null): array
    {
        $payload = [
            'phone_number' => $phoneNumber,
            'options' => [
                'code_size' => (int) config('services.didit.code_size', 6),
                'locale' => config('services.didit.locale', 'en-US'),
                'preferred_channel' => config('services.didit.channel', 'sms'),
            ],
            'signals' => array_filter([
                'ip' => $ip,
                'device_platform' => 'web',
                'user_agent' => $userAgent,
            ]),
        ];

        $response = $this->client()->post('/v3/phone/send/', $payload);

        if (! $response->successful()) {
            throw new RuntimeException($this->extractError($response->json()) ?: 'Unable to send OTP right now.');
        }

        $data = $response->json();
        $status = $data['status'] ?? null;

        if (! in_array($status, ['Success', 'Retry'], true)) {
            throw new RuntimeException($this->extractError($data) ?: 'OTP could not be sent to this number.');
        }

        return $data;
    }

    public function checkCode(string $phoneNumber, string $code): bool
    {
        $response = $this->client()->post('/v3/phone/check/', [
            'phone_number' => $phoneNumber,
            'code' => $code,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException($this->extractError($response->json()) ?: 'Unable to verify OTP right now.');
        }

        return $response->json('status') === 'Approved';
    }

    private function client(): PendingRequest
    {
        $apiKey = config('services.didit.api_key');

        if (! $apiKey) {
            throw new RuntimeException('OTP service is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.didit.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders(['x-api-key' => $apiKey])
            ->timeout(15);
    }

    private function extractError(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        if (isset($payload['detail']) && is_string($payload['detail'])) {
            return $payload['detail'];
        }

        if (isset($payload['error']) && is_string($payload['error'])) {
            return $payload['error'];
        }

        foreach ($payload as $messages) {
            if (is_array($messages) && isset($messages[0]) && is_string($messages[0])) {
                return $messages[0];
            }
        }

        return null;
    }
}
