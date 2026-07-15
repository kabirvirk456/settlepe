<?php

namespace Tests\Unit;

use App\Services\CrifReportSummaryService;
use PHPUnit\Framework\TestCase;

class CrifReportSummaryServiceTest extends TestCase
{
    public function test_it_extracts_score_and_tradelines_from_a_crif_response(): void
    {
        $summary = (new CrifReportSummaryService)->summarize([
            'data' => [
                'bureau' => ['credit_score' => '718'],
                'tradelines' => [
                    [
                        'member_name' => 'Example Bank',
                        'account_type' => 'Personal Loan',
                        'sanctioned_amount' => '₹5,00,000',
                        'current_balance' => '125000',
                        'days_past_due' => '42',
                    ],
                    [
                        'lenderName' => 'Card Issuer',
                        'product' => 'Credit Card',
                        'creditLimit' => 100000,
                        'outstandingBalance' => 22000,
                        'dpd' => 0,
                    ],
                ],
            ],
        ]);

        $this->assertSame(718, $summary['score']);
        $this->assertSame([
            [
                'bank_name' => 'Example Bank',
                'product' => 'Personal Loan',
                'principal_amount' => 500000,
                'remaining_amount' => 125000,
                'dpd' => 42,
                'status' => 'overdue',
                'opened_on' => null,
            ],
            [
                'bank_name' => 'Card Issuer',
                'product' => 'Credit Card',
                'principal_amount' => 100000,
                'remaining_amount' => 22000,
                'dpd' => 0,
                'status' => 'active',
                'opened_on' => null,
            ],
        ], $summary['accounts']);
    }

    public function test_it_returns_an_empty_summary_instead_of_fabricating_data(): void
    {
        $this->assertSame(
            ['score' => null, 'accounts' => []],
            (new CrifReportSummaryService)->summarize(['success' => true, 'data' => []]),
        );
    }

    public function test_it_understands_the_native_crif_credit_report_shape(): void
    {
        $summary = (new CrifReportSummaryService)->summarize([
            'data' => ['credit_report' => [
                'SCORES' => ['SCORE' => ['SCORE-VALUE' => '681']],
                'RESPONSES' => ['RESPONSE' => [[
                    'LOAN-DETAILS' => [
                        'CREDIT-GUARANTOR' => 'Native CRIF Bank',
                        'ACCT-TYPE' => 'Credit Card',
                        'DISBURSED-AMT' => '200000',
                        'CURRENT-BAL' => '75000',
                        'COMBINED-PAYMENT-HISTORY' => 'Jun:2026,030/SMA|May:2026,000/STD',
                    ],
                ]]],
            ]],
        ]);

        $this->assertSame(681, $summary['score']);
        $this->assertSame('Native CRIF Bank', $summary['accounts'][0]['bank_name']);
        $this->assertSame(200000, $summary['accounts'][0]['principal_amount']);
        $this->assertSame(75000, $summary['accounts'][0]['remaining_amount']);
        $this->assertSame(30, $summary['accounts'][0]['dpd']);
    }
}
