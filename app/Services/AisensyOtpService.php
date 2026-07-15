<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AisensyOtpService
{
    public function sendCode(string $phoneNumber, string $name = 'Applicant'): array
    {
        $code = $this->generateCode();
        $payload = [
            'apiKey' => config('services.aisensy.api_key'),
            'campaignName' => config('services.aisensy.campaign_name'),
            'destination' => $this->normalizeDestination($phoneNumber),
            'userName' => $name,
            'templateParams' => $this->templateParams($code),
            'source' => config('services.aisensy.source', 'settle-pe-login'),
            'media' => (object) [],
            'buttons' => $this->buttons($code),
            'carouselCards' => [],
            'location' => (object) [],
            'attributes' => (object) [],
            'paramsFallbackValue' => [
                'FirstName' => config('services.aisensy.first_name_fallback', 'user'),
            ],
        ];

        $response = $this->client()->post((string) config('services.aisensy.send_path'), array_filter(
            $payload,
            fn ($value) => $value !== null && $value !== '',
        ));

        if (! $response->successful()) {
            throw new RuntimeException($this->extractError($response->json()) ?: 'Unable to send WhatsApp OTP right now.');
        }

        return [
            'request_id' => $response->json('submitted_message_id')
                ?? $response->json('messageId')
                ?? $response->json('id')
                ?? 'aisensy-otp',
            'code' => $code,
        ];
    }

    private function generateCode(): string
    {
        $fixedOtp = config('services.aisensy.fixed_otp');

        if (app()->environment('testing') && $fixedOtp && preg_match('/^[0-9]{6}$/', (string) $fixedOtp)) {
            return (string) $fixedOtp;
        }

        return (string) random_int(100000, 999999);
    }

    /**
     * @return array<int, string>
     */
    private function templateParams(string $code): array
    {
        $params = (array) config('services.aisensy.template_params', ['otp']);

        return array_map(
            fn (string $param): string => $param === 'otp' ? $code : $param,
            $params,
        );
    }

    /**
     * AiSensy's authentication template uses the dynamic URL button value as
     * the one-time code (the same shape as the campaign API example).
     *
     * @return array<int, array<string, mixed>>
     */
    private function buttons(string $code): array
    {
        if (! config('services.aisensy.otp_button_enabled', true)) {
            return [];
        }

        return [[
            'type' => 'button',
            'sub_type' => 'url',
            'index' => 0,
            'parameters' => [[
                'type' => 'text',
                'text' => $code,
            ]],
        ]];
    }

    private function normalizeDestination(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?: '';

        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        return $digits;
    }

    private function client(): PendingRequest
    {
        if (! config('services.aisensy.api_key') || ! config('services.aisensy.campaign_name')) {
            throw new RuntimeException('WhatsApp OTP service is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.aisensy.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.aisensy.timeout', 15));
    }

    private function extractError(mixed $payload): ?string
    {
        if (! is_array($payload)) {
            return null;
        }

        foreach (['message', 'error', 'detail'] as $key) {
            if (isset($payload[$key]) && is_string($payload[$key])) {
                return $payload[$key];
            }
        }

        return null;
    }
}
