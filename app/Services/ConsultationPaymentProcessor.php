<?php

namespace App\Services;

use App\Models\ConsultationPayment;
use Illuminate\Support\Facades\DB;

class ConsultationPaymentProcessor
{
    public function markCaptured(ConsultationPayment $payment, string $paymentId, array $providerResponse): void
    {
        DB::transaction(function () use ($payment, $paymentId, $providerResponse): void {
            $lockedPayment = ConsultationPayment::query()->lockForUpdate()->findOrFail($payment->id);

            if ($lockedPayment->status === 'captured') {
                return;
            }

            $lockedPayment->update([
                'payment_id' => $paymentId,
                'status' => 'captured',
                'failure_reason' => null,
                'provider_response' => $providerResponse,
                'paid_at' => now(),
            ]);

            $user = $lockedPayment->user()->lockForUpdate()->firstOrFail();
            $wasUnpaid = ! $user->consultation_fee_paid_at;

            $user->update([
                'consultation_fee_paid_at' => $user->consultation_fee_paid_at ?: now(),
                'sales_status' => 'consultation_paid',
            ]);

            if ($wasUnpaid) {
                $user->leadActivities()->create([
                    'event' => 'Consultation paid',
                    'notes' => 'Customer paid Rs. 99 through Razorpay.',
                ]);
            }
        });
    }
}
