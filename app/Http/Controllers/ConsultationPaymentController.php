<?php

namespace App\Http\Controllers;

use App\Models\ConsultationPayment;
use App\Services\ConsultationPaymentProcessor;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class ConsultationPaymentController extends Controller
{
    public function createOrder(Request $request, RazorpayService $razorpay): JsonResponse
    {
        $user = $request->user();
        abort_unless($user?->isCustomer(), 403);

        if ($user->consultation_fee_paid_at) {
            return response()->json([
                'already_paid' => true,
                'redirect_url' => route('dashboard'),
            ]);
        }

        $amount = (int) config('services.razorpay.consultation_amount', 9900);
        $currency = strtoupper((string) config('services.razorpay.currency', 'INR'));
        $receipt = 'consult-'.$user->id.'-'.Str::lower(Str::random(16));
        $payment = $user->consultationPayments()->create([
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => $receipt,
            'status' => 'creating',
        ]);

        try {
            $order = $razorpay->createOrder([
                'amount' => $amount,
                'currency' => $currency,
                'receipt' => $receipt,
                'notes' => [
                    'service' => 'consultation',
                    'customer_id' => (string) $user->id,
                ],
            ]);

            if (! isset($order['id'])
                || ($order['amount'] ?? null) !== $amount
                || strtoupper((string) ($order['currency'] ?? '')) !== $currency) {
                throw new RuntimeException('Razorpay returned an invalid order.');
            }

            $payment->update([
                'order_id' => (string) $order['id'],
                'status' => 'created',
                'provider_response' => $order,
            ]);
        } catch (RuntimeException $exception) {
            $payment->update(['status' => 'failed', 'failure_reason' => $exception->getMessage()]);

            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'key' => (string) config('services.razorpay.key_id'),
            'order_id' => $payment->order_id,
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'name' => 'Settle Pe',
            'description' => 'One-time CRIF consultation review',
            'prefill' => [
                'name' => $user->name,
                'email' => str_ends_with($user->email, '@otp.settlepe.test') ? '' : $user->email,
                'contact' => $user->mobile,
            ],
            'theme' => ['color' => '#10223f'],
        ]);
    }

    public function verify(Request $request, RazorpayService $razorpay, ConsultationPaymentProcessor $processor): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
            'razorpay_order_id' => ['required', 'string', 'max:100'],
            'razorpay_signature' => ['required', 'string', 'size:64'],
        ]);
        $user = $request->user();
        abort_unless($user?->isCustomer(), 403);

        $payment = $user->consultationPayments()
            ->where('order_id', $validated['razorpay_order_id'])
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'The Razorpay order was not found.'], 404);
        }

        if ($payment->status === 'captured') {
            return response()->json(['verified' => true, 'redirect_url' => route('dashboard')]);
        }

        if (! $razorpay->verifyCheckoutSignature($payment->order_id, $validated['razorpay_payment_id'], $validated['razorpay_signature'])) {
            $payment->update(['status' => 'verification_failed', 'failure_reason' => 'Checkout signature mismatch.']);

            return response()->json(['message' => 'Payment verification failed. No consultation was activated.'], 422);
        }

        try {
            $providerPayment = $razorpay->fetchPayment($validated['razorpay_payment_id']);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        if (! $this->matchesCapturedPayment($payment, $validated['razorpay_payment_id'], $providerPayment)) {
            return response()->json([
                'message' => 'Payment is not captured yet. If money was deducted, confirmation will update automatically.',
            ], 409);
        }

        $processor->markCaptured($payment, $validated['razorpay_payment_id'], $providerPayment);

        return response()->json(['verified' => true, 'redirect_url' => route('dashboard')]);
    }

    private function matchesCapturedPayment(ConsultationPayment $payment, string $expectedPaymentId, array $providerPayment): bool
    {
        return ($providerPayment['id'] ?? null) === $expectedPaymentId
            && ($providerPayment['order_id'] ?? null) === $payment->order_id
            && ($providerPayment['amount'] ?? null) === $payment->amount
            && strtoupper((string) ($providerPayment['currency'] ?? '')) === $payment->currency
            && ($providerPayment['status'] ?? null) === 'captured'
            && ($providerPayment['captured'] ?? false) === true;
    }
}
