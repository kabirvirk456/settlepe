<?php

namespace App\Http\Controllers;

use App\Models\ConsultationPayment;
use App\Services\ConsultationPaymentProcessor;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, RazorpayService $razorpay, ConsultationPaymentProcessor $processor): JsonResponse
    {
        $rawPayload = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');

        try {
            $validSignature = $signature !== '' && $razorpay->verifyWebhookSignature($rawPayload, $signature);
        } catch (RuntimeException) {
            return response()->json(['message' => 'Webhook is not configured.'], 503);
        }

        if (! $validSignature) {
            return response()->json(['message' => 'Invalid webhook signature.'], 400);
        }

        $event = json_decode($rawPayload, true);

        if (! is_array($event)) {
            return response()->json(['message' => 'Invalid webhook payload.'], 400);
        }

        if (! in_array($event['event'] ?? null, ['payment.captured', 'order.paid'], true)) {
            return response()->json(['received' => true]);
        }

        $providerPayment = data_get($event, 'payload.payment.entity');

        if (! is_array($providerPayment)
            || ! is_string($providerPayment['id'] ?? null)
            || $providerPayment['id'] === ''
            || ($providerPayment['status'] ?? null) !== 'captured') {
            return response()->json(['received' => true]);
        }

        $payment = ConsultationPayment::where('order_id', $providerPayment['order_id'] ?? null)->first();

        if (! $payment) {
            return response()->json(['received' => true]);
        }

        if (($providerPayment['amount'] ?? null) !== $payment->amount
            || strtoupper((string) ($providerPayment['currency'] ?? '')) !== $payment->currency
            || ($providerPayment['captured'] ?? false) !== true) {
            return response()->json(['message' => 'Payment details do not match the order.'], 422);
        }

        $processor->markCaptured($payment, (string) $providerPayment['id'], $providerPayment);

        return response()->json(['received' => true]);
    }
}
