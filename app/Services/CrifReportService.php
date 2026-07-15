<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CrifReportService
{
    /** @return array<string, mixed> */
    public function requestReport(array $applicant): array
    {
        return $this->send('/creditBureauCRIF', (string) config('services.crif.request_api_code'), $applicant);
    }

    /** @return array<string, mixed> */
    public function authenticate(string $reportId, string $orderId, string $answers, ?string $remark = null): array
    {
        return $this->send('/creditBureauCRIFUserAuth', (string) config('services.crif.auth_api_code'), [
            'report_id' => $reportId,
            'order_id' => $orderId,
            'auth_answers' => $answers,
            'remark' => $remark,
        ]);
    }

    /** @return array{report_id: string, order_id: string} */
    public function identifiers(array $response): array
    {
        $response = $this->expandJsonStrings($response);
        $reportId = $this->valueForKeys($response, ['reportid', 'reportidentifier', 'reportnumber', 'reportno']);
        $orderId = $this->valueForKeys($response, ['orderid', 'orderidentifier', 'ordernumber', 'orderno']);

        if ($reportId === null || $orderId === null) {
            throw new RuntimeException('CRIF did not return the report identifiers required for authentication.');
        }

        return ['report_id' => $reportId, 'order_id' => $orderId];
    }

    public function authenticationPrompt(array $response): ?string
    {
        $response = $this->expandJsonStrings($response);

        foreach (Arr::dot($response) as $key => $value) {
            $normalized = preg_replace('/[^a-z0-9]/', '', strtolower((string) last(explode('.', $key))));

            if (in_array($normalized, ['question', 'authquestion', 'authenticationquestion'], true) && is_scalar($value)) {
                return trim((string) $value) ?: null;
            }
        }

        return null;
    }

    public function containsCompletedReport(array $response): bool
    {
        $response = $this->expandJsonStrings($response);

        foreach (Arr::dot($response) as $key => $value) {
            $leafKey = $this->normalizeKey((string) last(explode('.', $key)));

            if ($leafKey === 'reportid'
                && is_scalar($value)
                && trim((string) $value) !== '') {
                $hasReportId = true;
            }

            $normalizedPath = $this->normalizeKey($key);

            if ((str_contains($normalizedPath, 'creditreport') && $leafKey === 'scorevalue')
                || str_contains($normalizedPath, 'creditreportresponsesresponse')) {
                $hasReportContent = true;
            }
        }

        return ($hasReportId ?? false) && ($hasReportContent ?? false);
    }

    /** @return array<string, mixed> */
    private function send(string $path, string $apiCode, array $payload): array
    {
        try {
            $response = $this->client($apiCode)->post($path, $payload);
        } catch (ConnectionException $exception) {
            throw new RuntimeException('The CRIF service could not be reached. Please try again.', previous: $exception);
        }

        $this->ensureSuccessful($response);

        $json = $response->json();

        if (! is_array($json)) {
            throw new RuntimeException('The CRIF service returned an invalid response.');
        }

        $this->ensureAcceptedByBureau($json);

        return $json;
    }

    private function client(string $apiCode): PendingRequest
    {
        $domain = (string) config('services.crif.domain_name');
        $authKey = (string) config('services.crif.auth_key');

        if ($domain === '' || $authKey === '') {
            throw new RuntimeException('CRIF credentials are not configured.');
        }

        return Http::baseUrl(rtrim((string) config('services.crif.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'domain_name' => $domain,
                'authkey' => $authKey,
                'apicode' => $apiCode,
            ])
            ->timeout((int) config('services.crif.timeout', 30));
    }

    private function ensureSuccessful(Response $response): void
    {
        if ($response->successful()) {
            return;
        }

        $message = $response->json('message') ?: $response->json('error');

        throw new RuntimeException(is_string($message) && $message !== ''
            ? 'CRIF request failed: '.$message
            : 'CRIF request failed. Please verify the details and try again.');
    }

    private function ensureAcceptedByBureau(array $response): void
    {
        $success = $response['success'] ?? null;
        $status = $response['status'] ?? null;
        $failed = $success === false
            || $success === 0
            || $status === false
            || $status === 0
            || (is_string($status) && in_array(strtolower($status), ['error', 'failed', 'failure'], true));

        if (! $failed) {
            return;
        }

        $message = $response['message'] ?? $response['error'] ?? null;

        throw new RuntimeException(is_string($message) && trim($message) !== ''
            ? 'CRIF request failed: '.trim($message)
            : 'CRIF rejected the request. Please verify the details and try again.');
    }

    private function valueForKeys(array $response, array $wantedKeys): ?string
    {
        foreach (Arr::dot($response) as $key => $value) {
            $normalized = preg_replace('/[^a-z0-9]/', '', strtolower((string) last(explode('.', $key))));

            if (in_array($normalized, $wantedKeys, true) && is_scalar($value) && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function expandJsonStrings(array $node): array
    {
        foreach ($node as $key => $value) {
            if (is_array($value)) {
                $node[$key] = $this->expandJsonStrings($value);

                continue;
            }

            if (! is_string($value) || ! in_array(substr(ltrim($value), 0, 1), ['{', '['], true)) {
                continue;
            }

            $decoded = json_decode($value, true);

            if (is_array($decoded)) {
                $node[$key] = $this->expandJsonStrings($decoded);
            }
        }

        return $node;
    }

    private function normalizeKey(string $key): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($key));
    }
}
