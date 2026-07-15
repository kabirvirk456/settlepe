<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RazorpayService
{
    /** @return array<string, mixed> */
    public function createOrder(array $payload): array
    {
        return $this->send(fn (PendingRequest $client) => $client->post('/orders', $payload));
    }

    /** @return array<string, mixed> */
    public function fetchPayment(string $paymentId): array
    {
        return $this->send(fn (PendingRequest $client) => $client->get('/payments/'.$paymentId));
    }

    public function verifyCheckoutSignature(string $orderId, string $paymentId, string $signature): bool
    {
        $secret = $this->keySecret();
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $secret);

        return hash_equals($expected, $signature);
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        $secret = (string) config('services.razorpay.webhook_secret');

        if ($secret === '') {
            throw new RuntimeException('Razorpay webhook secret is not configured.');
        }

        return hash_equals(hash_hmac('sha256', $payload, $secret), $signature);
    }

    /** @return array<string, mixed> */
    private function send(callable $request): array
    {
        try {
            $response = $request($this->client());
        } catch (ConnectionException $exception) {
            throw new RuntimeException('Razorpay could not be reached. Please try again.', previous: $exception);
        }

        $this->ensureSuccessful($response);
        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('Razorpay returned an invalid response.');
        }

        return $json;
    }

    private function client(): PendingRequest
    {
        $keyId = (string) config('services.razorpay.key_id');

        if ($keyId === '') {
            throw new RuntimeException('Razorpay credentials are not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.razorpay.base_url'), '/'))
            ->withBasicAuth($keyId, $this->keySecret())
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('services.razorpay.timeout', 15));
    }

    private function keySecret(): string
    {
        $secret = (string) config('services.razorpay.key_secret');

        if ($secret === '') {
            throw new RuntimeException('Razorpay credentials are not configured.');
        }

        return $secret;
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $description = $response->json('error.description') ?: $response->json('error.reason');

        throw new RuntimeException(is_string($description) && $description !== ''
            ? 'Razorpay request failed: '.$description
            : 'Razorpay request failed. Please try again.');
    }
}
