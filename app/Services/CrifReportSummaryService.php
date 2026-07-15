<?php

namespace App\Services;

use Illuminate\Support\Arr;

class CrifReportSummaryService
{
    private const SCORE_KEYS = ['creditscore', 'bureauscore', 'crifscore', 'scorevalue', 'score'];

    private const LENDER_KEYS = ['membername', 'lendername', 'bankname', 'institutionname', 'subscribername', 'creditgrantor', 'creditguarantor'];

    private const PRODUCT_KEYS = ['accounttype', 'accounttypecode', 'accttype', 'loantype', 'creditfacility', 'product', 'accountcategory'];

    private const PRINCIPAL_KEYS = ['highcredit', 'sanctionedamount', 'disbursedamount', 'disbursedamt', 'originalamount', 'principalamount', 'creditlimit'];

    private const BALANCE_KEYS = ['currentbalance', 'currentbal', 'outstandingbalance', 'remainingamount', 'balance'];

    private const DPD_KEYS = ['dayspastdue', 'dpd', 'overduedays'];

    /** @return array{score: int|null, accounts: array<int, array{bank_name: string, product: string, principal_amount: int, remaining_amount: int, dpd: int, status: string, opened_on: string|null}>} */
    public function summarize(?array $response): array
    {
        if (! $response) {
            return ['score' => null, 'accounts' => []];
        }

        $response = $this->expandJsonStrings($response);

        return [
            'score' => $this->extractScore($response),
            'accounts' => $this->extractAccounts($response),
        ];
    }

    private function extractScore(array $response): ?int
    {
        foreach (Arr::dot($response) as $key => $value) {
            if (! is_scalar($value) || ! in_array($this->normalizeKey($this->leafKey($key)), self::SCORE_KEYS, true)) {
                continue;
            }

            $score = $this->number($value);

            if ($score !== null && $score >= 300 && $score <= 900) {
                return $score;
            }
        }

        return null;
    }

    /** @return array<int, array{bank_name: string, product: string, principal_amount: int, remaining_amount: int, dpd: int, status: string, opened_on: string|null}> */
    private function extractAccounts(array $response): array
    {
        $accounts = [];
        $this->findAccountLists($response, $accounts);

        return array_values(collect($accounts)
            ->unique(fn (array $account) => implode('|', $account))
            ->values()
            ->all());
    }

    private function findAccountLists(array $node, array &$accounts): void
    {
        if (array_is_list($node)) {
            foreach ($node as $item) {
                if (is_array($item) && ($account = $this->normalizeAccount($item))) {
                    $accounts[] = $account;
                }
            }
        }

        foreach ($node as $value) {
            if (is_array($value)) {
                $this->findAccountLists($value, $accounts);
            }
        }
    }

    /** @return array{bank_name: string, product: string, principal_amount: int, remaining_amount: int, dpd: int, status: string, opened_on: string|null}|null */
    private function normalizeAccount(array $item): ?array
    {
        $values = [];

        foreach (Arr::dot($item) as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $values[$this->normalizeKey($this->leafKey($key))] ??= $value;
            }
        }

        $lender = $this->firstText($values, self::LENDER_KEYS);
        $product = $this->firstText($values, self::PRODUCT_KEYS);
        $principal = $this->firstNumber($values, self::PRINCIPAL_KEYS);
        $balance = $this->firstNumber($values, self::BALANCE_KEYS);

        if ($lender === null || ($principal === null && $balance === null)) {
            return null;
        }

        $principal ??= $balance;
        $balance ??= $principal;
        $dpd = $this->extractDpd($values);
        $overdueAmount = $this->firstNumber($values, ['overdueamount', 'overdueamt']) ?? 0;
        $rawStatus = strtolower($this->firstText($values, ['accountstatus', 'status']) ?? '');
        $closedDate = $this->firstText($values, ['closeddate', 'dateclosed']);

        $status = match (true) {
            $dpd > 0 || $overdueAmount > 0 => 'overdue',
            $closedDate !== null || str_contains($rawStatus, 'closed') => 'closed',
            default => 'active',
        };

        return [
            'bank_name' => $lender,
            'product' => $product ?? 'Credit account',
            'principal_amount' => max(0, $principal),
            'remaining_amount' => max(0, $balance),
            'dpd' => $dpd,
            'status' => $status,
            'opened_on' => $this->firstText($values, ['openeddate', 'dateopened', 'disburseddate', 'disburseddt']),
        ];
    }

    private function extractDpd(array $values): int
    {
        $dpd = $this->firstNumber($values, self::DPD_KEYS);

        if ($dpd !== null) {
            return max(0, $dpd);
        }

        $history = $this->firstText($values, ['combinedpaymenthistory', 'paymenthistory']);

        if ($history && preg_match('/(?<!\d)(\d{3})\/(?:STD|SMA|SUB|DBT|LSS|XXX)/i', $history, $matches)) {
            return (int) $matches[1];
        }

        return 0;
    }

    private function firstText(array $values, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($values[$key]) && is_scalar($values[$key]) && trim((string) $values[$key]) !== '') {
                return trim((string) $values[$key]);
            }
        }

        return null;
    }

    private function firstNumber(array $values, array $keys): ?int
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $values) && ($number = $this->number($values[$key])) !== null) {
                return $number;
            }
        }

        return null;
    }

    private function number(mixed $value): ?int
    {
        if (! is_scalar($value)) {
            return null;
        }

        $normalized = preg_replace('/[^0-9.-]/', '', (string) $value);

        return $normalized !== '' && is_numeric($normalized) ? (int) round((float) $normalized) : null;
    }

    private function leafKey(string $key): string
    {
        $parts = explode('.', $key);

        return (string) end($parts);
    }

    private function normalizeKey(string $key): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($key));
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
}
