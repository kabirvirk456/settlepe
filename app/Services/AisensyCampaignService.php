<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AisensyCampaignService
{
    /**
     * @param  array<int, string>  $templateParams
     */
    public function send(string $campaignName, string $phoneNumber, string $name, array $templateParams): array
    {
        $response = $this->client()->post((string) config('services.aisensy.send_path'), [
            'apiKey' => config('services.aisensy.api_key'),
            'campaignName' => $campaignName,
            'destination' => $this->normalizeDestination($phoneNumber),
            'userName' => $name,
            'templateParams' => $templateParams,
            'source' => config('services.aisensy.source', 'new-landing-page form'),
            'media' => (object) [],
            'buttons' => [],
            'carouselCards' => [],
            'location' => (object) [],
            'attributes' => (object) [],
            'paramsFallbackValue' => ['FirstName' => 'user'],
        ]);

        if (! $response->successful()) {
            throw new RuntimeException($response->json('message') ?: 'Unable to send WhatsApp campaign.');
        }

        return (array) $response->json();
    }

    private function normalizeDestination(string $phoneNumber): string
    {
        $digits = preg_replace('/\D+/', '', $phoneNumber) ?: '';

        return strlen($digits) === 10 ? '91'.$digits : $digits;
    }

    private function client(): PendingRequest
    {
        if (! config('services.aisensy.api_key')) {
            throw new RuntimeException('WhatsApp service is not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.aisensy.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.aisensy.timeout', 15));
    }
}
